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
 * Блок История обучения. Веб-сервисы.
 *
 * @package    block
 * @subpackage mylearninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = [
    'block_mylearninghistory_get_courses_filter_form' => [
        'classname'   => 'block_mylearninghistory\external',
        'methodname'  => 'get_courses_filter_form',
        'classpath'   => '',
        'description' => 'Returns courses filter form data ready to display',
        'type'        => 'read',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => [],
        'loginrequired' => true
    ],
    'block_mylearninghistory_get_courses_filter_rules_form' => [
        'classname'   => 'block_mylearninghistory\external',
        'methodname'  => 'get_courses_filter_rules_form',
        'classpath'   => '',
        'description' => 'Returns courses filter rules form data ready to display',
        'type'        => 'read',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => [],
        'loginrequired' => true
    ]
];