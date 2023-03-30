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
 * Блок согласования мастер-курса. Веб-сервисы
 *
 * @package    block_mastercourse
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
$functions = [
    'quiz_otoverview_get_attempts_data' => [
        'classname'   => 'quiz_otoverview\services\get_attempts_data',
        'methodname'  => 'get_attempts_data',
        'classpath'   => '',
        'description' => '',
        'type'        => 'read',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => []
    ],
];