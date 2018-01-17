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
$string['pluginname'] = 'Conector para Pinterest';
$string['search'] = 'String to search for in Pinterest Tags';
$string['search_empty'] = 'Search term absent. Configure it in this activity <a href="../../course/modedit.php?update={$a->cmid}&return=1">settings</a>.';
$string['search_help'] = 'If specified, only media published with the specific tag is collected for analysis.';
$string['searchingby'] = 'Searching for tag: "{$a}"';
$string['harvest'] = 'Buscar en Pinterest la actividad de los estudiantes';

$string['no_pinterest_name_advice'] = 'Desconectado de Pinterest.';
$string['no_pinterest_name_advice2'] = 'No se conoce la identidad de {$a->userfullname} en Pinterest. Identificarse en Pinterest con ' .
        '<a href="{$a->url}">Pinterest login</a>';
$string['module_connected_pinterest'] = 'Actividad enlazada con Pinterest con el usuario "{$a}" ';
$string['module_connected_pinterest_usermode'] = 'Actividad buscando en Pinterest usuario a usuario';
$string['module_not_connected_pinterest'] = 'Actividad desconectada de Pinterest. No funcionará hasta que se enlace con una cuenta de Pinterest';
$string['selectthisboard']  = 'Tablones en los que buscar';
$string['prboard'] = 'Seleccionar tablones';
// SETTINGS.
$string['pinterest_app_id'] = 'client_id';
$string['config_app_id'] = 'client_id according to PinterestAPI (<a href="https://developers.pinterest.com/docs/getting-started/introduction/" target="_blank" >https://developers.pinterest.com/docs/getting-started/introduction/</a>)';
$string['pinterest_app_secret'] = 'client_secret';
$string['config_app_secret'] = 'client_secret according to PinterestAPI (<a href="https://developers.pinterest.com/docs/getting-started/introduction/" target="_blank" >https://developers.pinterest.com/docs/getting-started/introduction/</a>)';
$string['problemwithpinterestaccount'] = 'Recent attempts to get the posts resulted in an error. Try to reconnect Pinterest with your user. Message: {$a}';