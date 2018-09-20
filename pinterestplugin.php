<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
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
namespace mod_msocial\connector;

use mod_msocial\kpi_info;
use msocial\msocial_plugin;
use mod_msocial\social_user;
use DirkGroenen\Pinterest\Models\Board;
use DirkGroenen\Pinterest\Models\Collection;
use DirkGroenen\Pinterest\Pinterest;

defined('MOODLE_INTERNAL') || die();
global $CFG;

/** library class for social network pinterest plugin extending social plugin base class
 *
 * @package msocialconnector_pinterest
 * @copyright 2017 Juan Pablo de Castro {@email jpdecastro@tel.uva.es}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later */
class msocial_connector_pinterest extends msocial_connector_plugin {
    const CONFIG_PRSEARCH = 'search';
    const CONFIG_PRBOARD = 'board';
    const CONFIG_PRBOARDNAME = 'boardname';
    const CONFIG_MIN_WORDS = 'minwords';
    const MODE_USER = 'user';
    const MODE_BOARD = 'board';
    private $saves = [];
    private $comments = [];
    private $mode = self::MODE_BOARD;
    private $apiapproved = true;

    /** Get the name of the plugin
     *
     * @return string */
    public function get_name() {
        return get_string('pluginname', 'msocialconnector_pinterest');
    }

    /**
     *
     * {@inheritDoc}
     * @see \msocial\msocial_plugin::can_harvest()
     */
    public function can_harvest() {
        if ($this->get_connection_token() == null) {
            return false;
        }
        $igsearch = $this->get_config(self::CONFIG_PRSEARCH);
        switch ($this->mode) {
            case self::MODE_BOARD:
                $boards = $this->get_config(self::CONFIG_PRBOARD);
                return ($this->is_enabled() && $this->get_connection_token() !== null && $boards);
            case self::MODE_USER:
                return $this->is_enabled() && $igsearch != null;
            default:
                return false;
        }
    }
    /**
     * {@inheritdoc}
     *
     * @see \msocial\msocial_plugin::calculate_kpis() */
    public function calculate_kpis($users, $kpis = []) {
        $kpis = parent::calculate_kpis($users, $kpis);
        foreach ($kpis as $kpi) {
            if (isset($this->comments[$kpi->userid])) {
                $kpi->prcomments = $this->prcomments[$kpi->userid];
            }
            if (isset($this->saves[$kpi->userid])) {
                $kpi->saves = $this->saves[$kpi->userid];
            }
        }
        // Max.
        $maxcomments = 0;
        $maxsaves = 0;
        foreach ($kpis as $kpi) {
            if (isset($kpi->prcomments)) {
                $maxcomments = max([$maxcomments, $kpi->prcomments]);
            }
            if (isset($kpi->saves)) {
                $maxsaves = max([$maxsaves, $kpi->saves]);
            }
        }
        foreach ($kpis as $kpi) {
            $kpi->max_prcomments = $maxcomments;
            $kpi->max_saves = $maxsaves;
        }
        return $kpis;
    }

    /** The msocial has been deleted - cleanup subplugin
     *
     * @return bool */
    public function delete_instance() {
        global $DB;
        $result = true;
        if (!$DB->delete_records('msocial_interactions', array('msocial' => $this->msocial->id, 'source' => $this->get_subtype()))) {
            $result = false;
        }
        if (!$DB->delete_records('msocial_pinterest_tokens', array('msocial' => $this->msocial->id))) {
            $result = false;
        }
        if (!$DB->delete_records('msocial_mapusers', array('msocial' => $this->msocial->id, 'type' => $this->get_subtype()))) {
            $result = false;
        }
        if (!$DB->delete_records('msocial_plugin_config', array('msocial' => $this->msocial->id, 'subtype' => $this->get_subtype()))) {
            $result = false;
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @see \msocial\msocial_plugin::get_settings() */
    public function get_settings(\MoodleQuickForm $mform) {
        $formfieldname = $this->get_form_field_name(self::CONFIG_PRSEARCH);
        $mform->addElement('text', $formfieldname, get_string("search", "msocialconnector_pinterest"), array('size' => '20'));
        $mform->setType($formfieldname, PARAM_TEXT);
        $mform->addHelpButton($formfieldname, 'search', 'msocialconnector_pinterest');
    }

    /**
     * {@inheritdoc}
     *
     * @see \msocial\msocial_plugin::data_preprocessing() */
    public function data_preprocessing(&$defaultvalues) {
        $defaultvalues[$this->get_form_field_name(self::CONFIG_PRSEARCH)] = $this->get_config(self::CONFIG_PRSEARCH);
        parent::data_preprocessing($defaultvalues);
    }

    /**
     * {@inheritdoc}
     *
     * @see \msocial\msocial_plugin::save_settings() */
    public function save_settings(\stdClass $data) {
        if (isset($data->{$this->get_form_field_name(self::CONFIG_PRSEARCH)})) {
            $this->set_config(self::CONFIG_PRSEARCH, $data->{$this->get_form_field_name(self::CONFIG_PRSEARCH)});
        }
        return true;
    }

    public function get_subtype() {
        return 'pinterest';
    }

    public function get_category() {
        return msocial_plugin::CAT_ANALYSIS;
    }

    /**
     * {@inheritdoc}
     *
     * @see \mod_msocial\connector\msocial_connector_plugin::get_icon() */
    public function get_icon() {
        return new \moodle_url('/mod/msocial/connector/pinterest/pix/pinterest_icon.png');
    }

    /**
     * @global \core_renderer $OUTPUT
     * @global \moodle_database $DB */
    public function render_header() {
        global $OUTPUT, $DB, $USER;
        $notifications = [];
        $messages = [];
        if ($this->is_enabled()) {
            $context = \context_module::instance($this->cm->id);
            list($course, $cm) = get_course_and_cm_from_instance($this->msocial->id, 'msocial');
            $id = $cm->id;
            if (has_capability('mod/msocial:manage', $context)) {

                if ($this->mode == self::MODE_BOARD) {
                    $token = $this->get_connection_token();
                    $urlconnect = new \moodle_url('/mod/msocial/connector/pinterest/connectorSSO.php',
                            array('id' => $id, 'action' => 'connect'));
                    if ($token) {
                        $username = $token->username;
                        $errorstatus = $token->errorstatus;
                        if ($errorstatus) {
                            $linkuseraction = $OUTPUT->action_link(
                                    new \moodle_url('/mod/msocial/connector/pinterest/connectorSSO.php',
                                            array('id' => $id, 'action' => 'connect')), "  <b>Relink user!!</b>");
                            $notifications[] = get_string('problemwithpinterestaccount', 'msocialconnector_pinterest', $errorstatus) . $linkuseraction;
                        } else {
                        $linkuseraction = $OUTPUT->action_link(
                                new \moodle_url('/mod/msocial/connector/pinterest/connectorSSO.php',
                                        array('id' => $id, 'action' => 'connect')), "Change user");
                        $messages[] = get_string('module_connected_pinterest', 'msocialconnector_pinterest', $username) . $linkuseraction . '/' .
                                        $OUTPUT->action_link( new \moodle_url('/mod/msocial/connector/pinterest/connectorSSO.php',
                                        array('id' => $id, 'action' => 'disconnect')), "Disconnect") . ' ';
                        }
                    } else {
                        $notifications[] = get_string('module_not_connected_pinterest', 'msocialconnector_pinterest') . $OUTPUT->action_link(
                                new \moodle_url('/mod/msocial/connector/pinterest/connectorSSO.php',
                                        array('id' => $id, 'action' => 'connect')), "Connect");
                    }
                } else { // MODE_USER.
                    $messages[] = get_string('module_connected_pinterest_usermode', 'msocialconnector_pinterest');
                }
            }
            if ($this->get_connection_token()) {
                // Check pisterest boards...
                $boards = $this->get_config(self::CONFIG_PRBOARD);
                if (trim($boards) === "") {
                    $action = '';
                    if (has_capability('mod/msocial:manage', $context)) {
                        $action = ' : ' . $OUTPUT->action_link(
                                new \moodle_url('/mod/msocial/connector/pinterest/boardchoice.php',
                                        array('id' => $id, 'action' => 'selectboard')), "Select board");
                    }
                    $notifications[] = get_string('prboard', 'msocialconnector_pinterest') . $action;
                } else {
                    $boardlinks = $this->render_board_links();
                    $action = '';
                    if (has_capability('mod/msocial:manage', $context)) {
                        $action = $OUTPUT->action_link(
                                new \moodle_url('/mod/msocial/connector/pinterest/boardchoice.php',
                                        array('id' => $id, 'action' => 'selectboard')), "Change boards");
                    }
                    $messages[] = get_string('prboard', 'msocialconnector_pinterest') . ': "' . implode(', ', $boardlinks) . '" ' .
                             $action;
                }
            }

            // Check user's social credentials.
            $socialuserids = $this->get_social_userid($USER);
            if (!$socialuserids) { // Offer to register.
                $notifications[] = $this->render_user_linking($USER, false, true);
            }
            if (!$this->isautologinapproved() && has_capability('mod/msocial:manage', $context)) {
                $messages[] = get_string('linkstudentsmanually', 'msocialconnector_pinterest', $this->msocial);
            }
        }
        return [$messages, $notifications];
    }

    public function render_board_links() {
        $boardnames = json_decode($this->get_config(self::CONFIG_PRBOARDNAME));
        $boardinfo = [];
        foreach ($boardnames as $board) {
            $boardinfo[] = \html_writer::link($board->url, $board->name);
        }
        return $boardinfo;
    }
    public function render_harvest_link() {
        global $OUTPUT;
        $harvestbutton = '';
        $id = $this->cm->id;
        $harvestbutton = $OUTPUT->action_icon(
                new \moodle_url('/mod/msocial/harvest.php', ['id' => $id, 'subtype' => $this->get_subtype()]),
                new \pix_icon('a/refresh', get_string('harvest', 'msocialconnector_pinterest')));
        return $harvestbutton;
    }
    /** Place social-network user information or a link to connect.
     *
     * @global object $USER
     * @global object $COURSE
     * @param object $user user record
     * @return string message with the linking info of the user */

    public function get_social_user_url(social_user $userid) {
        return "https://www.pinterest.com/$userid->socialname";
    }

    public function get_interaction_url(social_interaction $interaction) {
        // Pinterest uid for a comment is generated with group id and comment id.
        $parts = explode('_', $interaction->uid);
        if (count($parts) == 2) {
            $url = 'https://www.pinterest.com/pin/' . $parts[0] . '/permalink/' . $parts[1]; // TODO:
                                                                                                 // there
                                                                                                 // are
                                                                                                 // subinteractions???
        } else {
            $url = 'https://www.pinterest.com/pin/' . $parts[0];
        }

        return $url;
    }

    /**
     * {@inheritdoc}
     *
     * @see \msocial\msocial_plugin::get_kpi_list() */
    public function get_kpi_list() {
        $kpiobjs['prpins'] = new kpi_info('prpins', get_string('kpi_description_prpins', 'msocialconnector_pinterest'),
                kpi_info::KPI_INDIVIDUAL, kpi_info::KPI_CALCULATED, social_interaction::POST, '*',
                social_interaction::DIRECTION_AUTHOR);
        $kpiobjs['prcomments'] = new kpi_info('prcomments', get_string('kpi_description_prcomments', 'msocialconnector_pinterest'),
                kpi_info::KPI_INDIVIDUAL, kpi_info::KPI_CUSTOM, social_interaction::REPLY);
        $kpiobjs['saves'] = new kpi_info('saves', get_string('kpi_description_saves', 'msocialconnector_pinterest'),
                kpi_info::KPI_INDIVIDUAL, kpi_info::KPI_CUSTOM, social_interaction::REACTION);
        $kpiobjs['max_prpins'] = new kpi_info('max_prpins', null, kpi_info::KPI_AGREGATED);
        $kpiobjs['max_prcomments'] = new kpi_info('max_prcomments', null, kpi_info::KPI_AGREGATED, kpi_info::KPI_CUSTOM);
        $kpiobjs['max_saves'] = new kpi_info('max_saves', null, kpi_info::KPI_AGREGATED, kpi_info::KPI_CUSTOM);
        return $kpiobjs;
    }

    /**
     * @global $CFG
     * @return string */
    private function get_appid() {
        global $CFG;
        $appid = get_config('msocialconnector_pinterest', 'appid');
        return $appid;
    }

    /**
     * @global $CFG
     * @return string */
    private function get_appsecret() {
        global $CFG;
        $appsecret = get_config('msocialconnector_pinterest', 'appsecret');
        return $appsecret;
    }

    /**
     * {@inheritdoc}
     *
     * @global moodle_database $DB
     * @return \stdClass token record */
    public function get_connection_token() {
        global $DB;
        if ($this->msocial) {
            $token = $DB->get_record('msocial_pinterest_tokens', ['msocial' => $this->msocial->id, 'ismaster' => 1]);
        } else {
            $token = null;
        }
        return $token;
    }
    /**
     * 
     * {@inheritDoc}
     * @see \mod_msocial\connector\msocial_connector_plugin::isautologinapproved()
     */
    public function isautologinapproved() {
        return $this->apiapproved;
    }
    
    /**
     * {@inheritdoc}
     *
     * @global moodle_database $DB
     * @see msocial_connector_plugin::set_connection_token() */
    public function set_connection_token($token) {
        global $DB;
        $token->msocial = $this->msocial->id;
        if (!isset($token->ismaster)) {
            $token->ismaster = 1;
        }
        if (empty($token->errorstatus)) {
            $token->errorstatus = null;
        }
        $record = $DB->get_record('msocial_pinterest_tokens', array("msocial" => $this->msocial->id, 'userid' => $token->userid));
        if ($record) {
            $token->id = $record->id;
            $DB->update_record('msocial_pinterest_tokens', $token);
        } else {
            $DB->insert_record('msocial_pinterest_tokens', $token);
        }
    }
    /**
     *
     * {@inheritDoc}
     * @see \msocial\msocial_plugin::reset_userdata()
     */
    public function reset_userdata($data) {
        global $DB;
        $msocial = $this->msocial;
        // Forget user tokens.
        $DB->delete_records('msocial_pinterest_tokens', array('msocial' => $this->msocial->id, 'ismaster' => 0));        
        // Remove mapusers.
        $DB->delete_records('msocial_mapusers',['msocial' => $msocial->id, 'type' => $this->get_subtype()]);
        return array('component'=>$this->get_name(), 'item'=>get_string('resetdone', 'msocial',
                "MSOCIAL $msocial->id: mapusers, tokens"), 'error'=>false);
    }
    /**
     * 
     * {@inheritDoc}
     * @see \mod_msocial\connector\msocial_connector_plugin::unset_connection_token()
     */
    public function unset_connection_token() {
        global $DB;
        $DB->delete_records('msocial_pinterest_tokens', array('msocial' => $this->msocial->id, 'ismaster' => 1));
    }

    /** Obtiene el numero de reacciones recibidas en el Post, y actaliza el "score" de
     * la persona que escribio el Post
     *
     * @param mixed $pin pinterest pin. */
    protected function process_pin($pin) {
        $authorid = $pin->creator['id'];
        $postinteraction = new social_interaction();
        $postinteraction->uid = $pin->id;
        $postinteraction->nativefrom = $authorid;
        $authorname = substr($pin->creator['url'], strlen('https://www.pinterest.com/'), -1);
        $postinteraction->nativefromname = $authorname;
        $postinteraction->fromid = $this->get_userid($authorid);
        $postinteraction->rawdata = json_encode($pin);
        $date = new \DateTime($pin->created_at);
        $postinteraction->timestamp = $date;
        $postinteraction->type = social_interaction::POST;
        $postinteraction->nativetype = 'PIN';
        $message = $pin->note ? $pin->note : ''; // TODO: manage better no captions
                                                 // (images, photos, etc.)
        $postinteraction->description = $message == '' ? 'No text.' : $message;

        $this->register_interaction($postinteraction);
        // Register each reaction as an interaction...
        // $this->addScore($postname, (0.1 * sizeof($reactions)) + 1);
        return $postinteraction;
    }

    /**
     * @param mixed $reaction
     * @param social_interaction $parentinteraction */
    protected function process_reactions($reaction, $parentinteraction) {
        $nativetype = 'like';
        $reactioninteraction = new social_interaction();
        $reactuserid = $reaction->id;
        $reactioninteraction->fromid = $this->get_userid($reactuserid);
        $reactioninteraction->nativefrom = $reactuserid;
        $reactioninteraction->nativefromname = $reaction->username;
        $reactioninteraction->uid = $parentinteraction->uid . '-' . $reactioninteraction->nativefrom;
        $reactioninteraction->parentinteraction = $parentinteraction->uid;
        $reactioninteraction->nativeto = $parentinteraction->nativefrom;
        $reactioninteraction->toid = $parentinteraction->fromid;
        $reactioninteraction->nativetoname = $parentinteraction->nativefromname;
        $reactioninteraction->rawdata = json_encode($reaction);
        $reactioninteraction->timestamp = null;
        $reactioninteraction->type = social_interaction::REACTION;
        $reactioninteraction->nativetype = $nativetype;
        $this->register_interaction($reactioninteraction);
    }

    /** Classify the text as too short to be relevant
     * TODO: implement relevance logic.
     * @param string $message
     * @return boolean $ok */
    protected function is_short_comment($message) {
        $numwords = str_word_count($message, 0);
        $minwords = $this->get_config(self::CONFIG_MIN_WORDS);
        return ($numwords <= ($minwords == null ? 2 : $minwords));
    }
    /**
     *
     * {@inheritDoc}
     * @see \msocial\msocial_plugin::preferred_harvest_intervals()
     */
    public function preferred_harvest_intervals() {
        return new harvest_intervals( 12 * 3600, 0, 0, 0);
    }
    /** pinterest content are grouped by tag.
     * Searching by tag may need special permissions from pinterest
     * API sandbox mode allows to gather personal medias by user. Will need to store individual
     * tokens.
     *
     * @global moodle_database $DB
     * @return mixed $result->statuses $result->messages[]string $result->errors[]->message */
    public function harvest() {
        switch ($this->mode) {
            case self::MODE_BOARD:
                return $this->harvest_boards();
            case self::MODE_USER:
                return $this->harvest_users();
        }
    }
    /**
     * @deprecated
     * @throws \Exception
     * @return \stdClass
     */
    private function harvest_users() {
        global $DB;
        print_error('Mode not implemented!!!'); // TODO implement harvest by user in pinterest.
    }

    private function harvest_boards() {
        global $DB;
        require_once('vendor/autoload.php');
        $errormessage = null;
        $result = new \stdClass();
        $result->messages = [];
        $result->errors = [];
        // Initialize pinterest API.
        $boards = explode(',', $this->get_config(self::CONFIG_PRBOARD));
        $appid = $this->get_appid();
        $appsecret = $this->get_appsecret();
        $this->lastinteractions = [];
        try {
            $pr = new Pinterest($appid, $appsecret);
            $token = $this->get_connection_token();
            $pr->auth->setOAuthToken($token->token);
            // Query pinterest...
            $since = '';
            $lastharvest = $this->get_config(self::LAST_HARVEST_TIME);
            if ($lastharvest) {
                $since = "&since=$lastharvest";
            }
            // Iterate boards.
            foreach ($boards as $boardid) {
                $pinscoll = $pr->pins->fromBoard($boardid,
                        ['fields' => 'id,link,note,url,board,counts,created_at,creator,media,metadata,original_link']);
                if (!$pinscoll) { // Error.
                    throw new \Exception($pinscoll->message);
                }
                // Mark the token as OK...
                $DB->set_field('msocial_pinterest_tokens', 'errorstatus', null, array('id' => $token->id));
                do {
                    foreach ($pinscoll->all() as $pin) {
                        $postinteraction = $this->process_pin($pin);
                        $pinstime = new \DateTime($pin->created_at);
                        if (msocial_time_is_between($pinstime->getTimestamp(),
                                                    (int)$this->msocial->startdate,
                                                    (int)$this->msocial->enddate)) {
                            // pin->counts->saves times saved. => reaction custom kpi.
                            // pin->counts->comments. TODO: Get an example of comment model???.
                            if ($pin->counts['comments'] > 0) {
                                if (!isset($this->comments[$postinteraction->fromid])) {
                                    $this->comments[$postinteraction->fromid] = 0;
                                }
                                $this->comments[$postinteraction->fromid] += $pin->counts['comments'];
                            }
                            if ($pin->counts['saves'] > 0) {
                                if (!isset($this->saves[$postinteraction->fromid])) {
                                    $this->saves[$postinteraction->fromid] = 0;
                                }
                                $this->saves[$postinteraction->fromid] += $pin->counts['saves'];
                            }
                        }
                    }
                    $otherpage = $pinscoll->hasNextPage();
                    if ($otherpage) {
                        $response = $pr->request->execute('GET', $pinscoll->pagination['next']);

                        $pinscoll = new Collection($pr, $response, "Pin");;
                    }
                } while ($otherpage && count($pinscoll->all()) > 0);
            }
        } catch (\Exception $e) {
            $cm = $this->cm;
            $msocial = $this->msocial;
            $errormessage = "For module msocial\\connection\\pinterest: $msocial->name (id=$cm->instance) in course (id=$msocial->course) " .
            " ERROR:" . $e->getMessage();
            //" Response headers were: " . http_build_query($pr->request->getHeaders(), '', ', ');
            $result->messages[] = $errormessage;
            $result->errors[] = (object) ['message' => $errormessage];
        }

        if ($token) {
            $token->errorstatus = $errormessage;
            $this->set_connection_token($token);
            if ($errormessage) { // Marks this tokens as erroneous to warn the teacher.
                $message = "Updating token with id = $token->id with $errormessage";
                $result->errors[] = (object) ['message' => $message];
                $result->messages[] = $message;
            }
        }

        return $this->post_harvest($result);
    }
}