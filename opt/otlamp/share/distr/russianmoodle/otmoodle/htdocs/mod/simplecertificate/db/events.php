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
 * Список отлавливаемых событий
 *
 * @package    mod_simplecertificate
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\core\event\user_graded',
        'callback' => '\mod_simplecertificate\event_handler::handle_core_user_graded',
    ],
    [
        'eventname' => '\mod_otcourselogic\event\state_switched',
        'callback' => '\mod_simplecertificate\event_handler::handle_mod_otcourselogic_state_switched',
    ],
    [
        'eventname' => '\core\event\course_viewed',
        'callback' => '\mod_simplecertificate\event_handler::handle_core_course_viewed',
    ],
    [
        'eventname' => '\mod_quiz\event\course_module_viewed',
        'callback' => '\mod_simplecertificate\event_handler::handle_mod_quiz_course_module_viewed',
    ],
];
