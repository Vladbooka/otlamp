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


/**
 * Универсальная панель управления. Сервисы
 *
 * @package    local_otcontrolpanel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = [
    'local_otcontrolpanel_get_config_data' => [
        'classname'   => 'local_otcontrolpanel\external',
        'methodname'  => 'get_config_data',
        'classpath'   => '',
        'description' => 'Returns json-encoded possible config data',
        'type'        => 'read',
        'capabilities' => 'local/otcontrolpanel:config,local/otcontrolpanel:config_my',
        'ajax'        => true,
        'services'    => [],
        'loginrequired' => true
    ],
    'local_otcontrolpanel_save_config' => [
        'classname'   => 'local_otcontrolpanel\external',
        'methodname'  => 'save_config',
        'classpath'   => '',
        'description' => 'saves config',
        'type'        => 'write',
        'capabilities' => 'local/otcontrolpanel:config,local/otcontrolpanel:config_my',
        'ajax'        => true,
        'services'    => [],
        'loginrequired' => true
    ],
    'local_otcontrolpanel_restore_default_config' => [
        'classname'   => 'local_otcontrolpanel\external',
        'methodname'  => 'restore_default_config',
        'classpath'   => '',
        'description' => 'deletes config from user mcov',
        'type'        => 'write',
        'capabilities' => 'local/otcontrolpanel:config,local/otcontrolpanel:config_my',
        'ajax'        => true,
        'services'    => [],
        'loginrequired' => true
    ]
];