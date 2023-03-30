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
 * История обучения. Веб-сервисы
 *
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// We defined the web service functions to install.
$functions = [
    'plagiarism_apru_add_to_index' => [
        'classname'   => 'plagiarism_apru\external',
        'methodname'  => 'add_to_index',
        'classpath'   => '',
        'description' => 'Add document to antiplagiat index database' ,
        'type'        => 'update',
        'capabilities' => 'plagiarism/apru:enableindexstatus',
        'ajax'        => true,
        'services'    => []
    ],
    'plagiarism_apru_remove_from_index' => [
        'classname'   => 'plagiarism_apru\external',
        'methodname'  => 'remove_from_index',
        'classpath'   => '',
        'description' => 'Remove document from antiplagiat index database' ,
        'type'        => 'update',
        'capabilities' => 'plagiarism/apru:disableindexstatus',
        'ajax'        => true,
        'services'    => []
    ],
];