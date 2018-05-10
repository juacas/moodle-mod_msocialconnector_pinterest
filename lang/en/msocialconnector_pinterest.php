<?php
// This file is part of PinterestCount activity for Moodle http://moodle.org/
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
/* ***************************
 * Module developed at the University of Valladolid
 * Designed and directed by Juan Pablo de Castro at telecommunication engineering school
 * Copyright 2017 onwards EdUVaLab http://www.eduvalab.uva.es
 * @author Juan Pablo de Castro
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package msocial
 * *******************************************************************************
 */
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$string['pluginname'] = 'Pinterest Connector';
$string['search'] = 'String to search for in Pinterest Tags';
$string['search_empty'] = 'Search term absent. Configure it in this activity <a href="../../course/modedit.php?update={$a->cmid}&return=1">settings</a>.';
$string['search_help'] = 'If specified, only media published with the specific tag is collected for analysis.';
$string['searchingby'] = 'Searching for tag: "{$a}"';
$string['harvest'] = 'Search Pinterest groups for student activity';

$string['no_pinterest_name_advice'] = 'Unlinked from Pinterest.';
$string['no_pinterest_name_advice2_when_api_approved'] = '{$a->userfullname} is not linked to Pinterest. Register using Pinterest clicking in ' .
        '<a href="{$a->url}">Pinterest login</a>';
$string['no_pinterest_name_advice2'] = '{$a->userfullname} is not linked to Pinterest. Post some content following the instructions of the activity and tell your teacher what is your username in Pinterest.';
$string['linkstudentsmanually'] = '<b>WARNING Auto-linking is not available for Pinterest.</b> Browse the stranger users of Pinterest in any visualization and click on their bracketed names to link them to your students.';
$string['module_connected_pinterest'] = 'Module connected with Pinterest as user "{$a}" ';
$string['module_connected_pinterest_usermode'] = 'Module searching Pinterest individually by users.';
$string['module_not_connected_pinterest'] = 'Module disconnected from Pinterest. It won\'t work until a Pinterest account is linked again.';
$string['selectthisboard']  = 'Select board to include in the analysis';
$string['prboard'] = 'Selected boards';
// SETTINGS.
$string['pinterest_app_id'] = 'client_id';
$string['config_app_id'] = 'client_id according to PinterestAPI (<a href="https://developers.pinterest.com/docs/getting-started/introduction/" target="_blank" >https://developers.pinterest.com/docs/getting-started/introduction/</a>)';
$string['pinterest_app_secret'] = 'client_secret';
$string['config_app_secret'] = 'client_secret according to PinterestAPI (<a href="https://developers.pinterest.com/docs/getting-started/introduction/" target="_blank" >https://developers.pinterest.com/docs/getting-started/introduction/</a>)';
$string['problemwithpinterestaccount'] = 'Recent attempts to get the posts resulted in an error. Try to reconnect Pinterest with your user. Message: {$a}';

$string['kpi_description_prpins'] = 'Published pins';
$string['kpi_description_prcomments'] = 'Received comments';
$string['kpi_description_saves'] = 'Saved pins';