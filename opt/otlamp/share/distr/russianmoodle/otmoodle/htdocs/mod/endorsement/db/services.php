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
 * @package    local_crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = [
    'mod_endorsement_get_statuses' => [
        'classname'   => 'mod_endorsement\external',
        'methodname'  => 'get_statuses',
        'classpath'   => '',
        'description' => 'Returns array of endorsements statuses',
        'type'        => 'read',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => []
    ],
    'mod_endorsement_set_status' => [
        'classname'   => 'mod_endorsement\external',
        'methodname'  => 'set_status',
        'classpath'   => '',
        'description' => 'Set status to endorsement item',
        'type'        => 'write',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => []
    ],
];