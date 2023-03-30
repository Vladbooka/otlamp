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
 * Блок Надо проверить. Веб-сервисы
 *
 * @package    block_notgraded
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// We defined the web service functions to install.
$functions = [
    'block_notgraded_get_count' => [
        'classname'   => 'block_notgraded\external',
        'methodname'  => 'get_count',
        'classpath'   => '',
        'description' => 'Returns count of user\'s notgraded items',
        'type'        => 'read',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => []
    ],
    'block_notgraded_get_count_after_connection_closed' => [
        'classname'   => 'block_notgraded\external',
        'methodname'  => 'get_count_after_connection_closed',
        'classpath'   => '',
        'description' => 'Count user\'s notgraded items if need',
        'type'        => 'write',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => []
    ]
];