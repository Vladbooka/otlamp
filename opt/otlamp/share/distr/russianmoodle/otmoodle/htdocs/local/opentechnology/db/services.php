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
 * Объявление сервисов
 *
 * @package    local_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = [
    'local_opentechnology_get_ac_logical_groups' => [
        'classname'   => 'local_opentechnology\availability_condition\services',
        'methodname'  => 'get_logical_groups',
        'classpath'   => '',
        'description' => 'Returns json-encoded logical groups data',
        'type'        => 'read',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => [],
        'loginrequired' => false
    ],
    'local_opentechnology_get_ac_comparison_operators' => [
        'classname'   => 'local_opentechnology\availability_condition\services',
        'methodname'  => 'get_comparison_operators',
        'classpath'   => '',
        'description' => 'Returns json-encoded comparison operators data',
        'type'        => 'read',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => [],
        'loginrequired' => false
    ],
    'local_opentechnology_get_ac_replacements' => [
        'classname'   => 'local_opentechnology\availability_condition\services',
        'methodname'  => 'get_replacements',
        'classpath'   => '',
        'description' => 'Returns json-encoded replacements data',
        'type'        => 'read',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => [],
        'loginrequired' => false
    ],
];