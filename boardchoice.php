<?php
// This file is part of MSocial activity for Moodle http://moodle.org/
//
// MSocial for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// MSocial for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
/*
 * **************************
 * Module developed at the University of Valladolid
 * Designed and directed by Juan Pablo de Castro at telecommunication engineering school
 * Copyright 2017 onwards EdUVaLab http://www.eduvalab.uva.es
 * @author Juan Pablo de Castro
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package msocial
 * *******************************************************************************
 */
use mod_msocial\connector\msocial_connector_pinterest;
use DirkGroenen\Pinterest\Pinterest;

require_once("../../../../config.php");
require_once('../../locallib.php');
require_once('../../classes/msocialconnectorplugin.php');
require_once('pinterestplugin.php');
require_once('vendor/autoload.php');
global $CFG;
$id = required_param('id', PARAM_INT); // MSocial module instance.
$action = optional_param('action', 'select', PARAM_ALPHA);
$type = optional_param('type', 'connect', PARAM_ALPHA);
$cm = get_coursemodule_from_id('msocial', $id);
$course = get_course($cm->course);
require_login($course);
$context = context_module::instance($id);
$msocial = $DB->get_record('msocial', array('id' => $cm->instance), '*', MUST_EXIST);
$plugin = new msocial_connector_pinterest($msocial);
require_capability('mod/msocial:manage', $context);

if ($action == 'selectboard') {
    $thispageurl = new moodle_url('/mod/msocial/connector/pinterest/boardchoice.php', array('id' => $id, 'action' => 'select'));
    $PAGE->set_url($thispageurl);
    $PAGE->set_title(format_string($cm->name));
    $PAGE->set_heading($course->fullname);
    // Print the page header.
    echo $OUTPUT->header();
    $modinfo = course_modinfo::instance($course->id);
    $clientid = get_config("msocialconnector_pinterest", "appid");
    $clientsecret = get_config("msocialconnector_pinterest", "appsecret");
    $pr = new Pinterest($clientid, $clientsecret);
    $token = $plugin->get_connection_token();
    $pr->auth->setOAuthToken($token->token);
    /** @var \DirkGroenen\Pinterest\Models\Collection $boards */
    $boards = $pr->users->getMeBoards(['fields' => 'id,name,url,image,description,created_at,counts,reason']);

    $selectedboards = $plugin->get_config(msocial_connector_pinterest::CONFIG_PRBOARD);
    if ($selectedboards) {
        $selectedboards = explode(',', $selectedboards);
    } else {
        $selectedboards = [];
    }
    $allboards = $boards->all();
    if (count($allboards) > 0) {
        $table = new \html_table();
        $table->head = ['Boards', get_string('description')];
        $data = [];

        $out = '<form method="GET" action="' . $thispageurl->out_omit_querystring(true) . '" >';
        $out .= '<input type="hidden" name="id" value="' . $id . '"/>';
        $out .= '<input type="hidden" name="action" value="setboards"/>';
        /** @var \DirkGroenen\Pinterest\Models\Board $board */
        foreach ($allboards as $board) {

            $row = new \html_table_row();
            // Use instance id instead of cmid... get_coursemodule_from_instance('forum',
            // $board->id)->id;.
            $boardid = json_encode(['id' => $board->id, 'name' => $board->name, 'url' => $board->url]);
            $boardurl = $board->url;
            $boardimg = $board->image['60x60'];
            $info = \html_writer::img($boardimg['url'], $board->name) . $board->name;
            $linkinfo = \html_writer::link($boardurl, $info);
            $selected = array_search($board->id, $selectedboards) !== false;
            $checkbox = \html_writer::checkbox('board[]', $boardid, $selected, $linkinfo);
            $row->cells = [$checkbox, $board->description];
            $table->data[] = $row;
        }
        $out .= \html_writer::table($table);
        $out .= '<input type="hidden" name="totalboards" value="' . count($allboards) . '"/>';
        $out .= '<input type="submit">';
        $out .= '</form>';
        echo $out;
    }
} else if ($action == 'setboards') {
    $boards = required_param_array('board', PARAM_RAW);
    $totalboards = required_param('totalboards', PARAM_INT);
    $thispageurl = new moodle_url('/mod/msocial/connector/pinterest/boardchoice.php', array('id' => $id, 'action' => 'select'));
    $PAGE->set_url($thispageurl);
    $PAGE->set_title(get_string('selectthisboard', 'msocialconnector_pinterest'));
    $PAGE->set_heading($course->fullname);
    // Print the page header.
    echo $OUTPUT->header();
    // Save the configuration.
    $boardids = [];
    $boardnames = [];
    foreach ($boards as $boardstruct) {
        $parts = json_decode($boardstruct);
        $boardid = $parts->id;
        $boardids[] = $boardid;
        unset($parts->id);
        $boardnames[$boardid] = $parts;
    }
    $plugin->set_config(msocial_connector_pinterest::CONFIG_PRBOARD, implode(',', $boardids));
    $plugin->set_config(msocial_connector_pinterest::CONFIG_PRBOARDNAME, json_encode($boardnames));
    $boardlinks = $plugin->render_board_links();
    echo get_string('prboard', 'msocialconnector_pinterest') . ': "' . implode(', ', $boardlinks);
    echo $OUTPUT->continue_button(new moodle_url('/mod/msocial/view.php', array('id' => $id)));
} else {
    print_error("Bad action code");
}
echo $OUTPUT->footer();