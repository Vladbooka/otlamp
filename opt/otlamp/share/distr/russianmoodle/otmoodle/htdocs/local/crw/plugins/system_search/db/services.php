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
 * Витрина курсов. Сервисы
 *
 * @package     local_crw
 * @subpackage  system_search
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = [
    'crw_system_search_show_hints' => [
        'classname'   => 'crw_system_search\external',
        'methodname'  => 'show_hints',
        'classpath'   => '',
        'description' => 'Search for hints',
        'type'        => 'read',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => [],
        'loginrequired' => false
    ],
    'crw_system_search_find_by_custom_field' => [
        'classname'   => 'crw_system_search\external',
        'methodname'  => 'find_by_custom_field',
        'classpath'   => '',
        'description' => 'Search for courses by custom fields',
        'type'        => 'read',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => [],
        'loginrequired' => false
    ],
    'crw_system_search_ajax_filter_no_auth' => [
        'classname'   => 'crw_system_search\external',
        'methodname'  => 'ajax_filter_no_auth',
        'classpath'   => '',
        'description' => 'Search by no authorized users',
        'type'        => 'read',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => [],
        'loginrequired' => false
    ]
];