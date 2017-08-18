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
/* ***************************
 * Module developed at the University of Valladolid
 * Designed and directed by Juan Pablo de Castro at telecommunication engineering school
 * Copyright 2017 onwards EdUVaLab http://www.eduvalab.uva.es
 * @author Juan Pablo de Castro
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package msocial
 * *******************************************************************************
 */
/** This file contains the settings definition for the pinterest social plugin
 *
 * @package msocial_pinterest
 * @copyright 2017 Juan Pablo de Castro {@email jpdecastro@tel.uva.es}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later */
defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_heading('mod_msocial_pinterest_header', 'pinterest API', 'Keys for pinterest API Access.'));
// TODO: Add explaination text about pinterest app id, need, and implications.
$settings->add(
        new admin_setting_configtext('msocialconnector_pinterest/appid', get_string('pinterest_app_id', 'msocialconnector_pinterest'),
                get_string('config_app_id', 'msocialconnector_pinterest'), '', PARAM_RAW_TRIMMED));
$settings->add(
        new admin_setting_configtext('msocialconnector_pinterest/appsecret', get_string('pinterest_app_secret', 'msocialconnector_pinterest'),
                get_string('config_app_secret', 'msocialconnector_pinterest'), '', PARAM_RAW_TRIMMED));
