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
 * Модуль Библиотека ресурсов.
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = [
    'mod_otresourcelibrary_insert_search_results' => [
        'classname'   => 'mod_otresourcelibrary\external',
        'methodname'  => 'insert_search_results',
        'classpath'   => '',
        'description' => 'insert search results',
        'type'        => 'update',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => []
    ],
    'mod_otresourcelibrary_insert_search_header' => [
        'classname'   => 'mod_otresourcelibrary\external',
        'methodname'  => 'insert_search_header',
        'classpath'   => '',
        'description' => 'insert search header',
        'type'        => 'update',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => []
    ],
    'mod_otresourcelibrary_insert_section_selection' => [
        'classname'   => 'mod_otresourcelibrary\external',
        'methodname'  => 'insert_section_selection',
        'classpath'   => '',
        'description' => 'insert section selection',
        'type'        => 'update',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => []
    ],
    'mod_otresourcelibrary_insert_category_selection' => [
        'classname'   => 'mod_otresourcelibrary\external',
        'methodname'  => 'insert_category_selection',
        'classpath'   => '',
        'description' => 'insert category selection',
        'type'        => 'update',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => []
    ],
    'mod_otresourcelibrary_insert_subcategories' => [
        'classname'   => 'mod_otresourcelibrary\external',
        'methodname'  => 'insert_subcategories',
        'classpath'   => '',
        'description' => 'insert subcategories',
        'type'        => 'update',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => []
    ]
];