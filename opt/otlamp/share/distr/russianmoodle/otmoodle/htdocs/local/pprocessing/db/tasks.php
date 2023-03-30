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
 * Список задач плагина в планировщике
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// List of tasks.
$tasks = [
    [
        'classname' => 'local_pprocessing\task\asap',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    ],
    [
        'classname' => 'local_pprocessing\task\hourly',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    ],
    [
        'classname' => 'local_pprocessing\task\daily',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '0',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    ],
    [
        'classname' => 'local_pprocessing\task\weekly',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '0',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '0'
    ],
    [
        'classname' => 'local_pprocessing\task\monthly',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '0',
        'day' => '1',
        'month' => '*',
        'dayofweek' => '*'
    ]
];
