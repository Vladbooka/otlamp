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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Тема СЭО 3KL. Веб-сервисы.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = [
    'theme_opentechnology_set_collapsiblesection_state' => [
        'classname'   => 'theme_opentechnology\external',
        'methodname'  => 'set_collapsiblesection_state',
        'classpath'   => '',
        'description' => 'Set user collapse state of blind section',
        'type'        => 'write',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => []
    ],

    'theme_opentechnology_send_spelling_mistake' => [
        'classname'   => 'theme_opentechnology\external',
        'methodname'  => 'send_spelling_mistake',
        'classpath'   => '',
        'description' => 'Send spelling mistake',
        'type'        => 'write',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => []
    ],

    'theme_opentechnology_get_login_form' => [
        'classname'   => 'theme_opentechnology\external',
        'methodname'  => 'get_login_form',
        'classpath'   => '',
        'description' => 'Returns login form data ready to render',
        'type'        => 'read',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => [],
        'loginrequired' => false
    ],

    'theme_opentechnology_get_dock_icon' => [
        'classname'   => 'theme_opentechnology\external',
        'methodname'  => 'get_dock_icon',
        'classpath'   => '',
        'description' => 'Returns docked block icon url',
        'type'        => 'read',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => [],
        'loginrequired' => false
    ]
];