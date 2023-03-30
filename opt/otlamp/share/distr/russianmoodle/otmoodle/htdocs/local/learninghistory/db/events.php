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
 * Add event handlers for the learninghistory
 *
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    // Обрабатываем просмотр элементов курса, наивысший приоритет, т.к. последующие события должны работать с уже существующими записями
    [
        'eventname' => '*',
        'callback' => '\local_learninghistory\observer::catch_all',
        'priority' => 9999
    ],
    // Обрабатываем события подписки на курс
    [
        'eventname' => '\core\event\user_enrolment_created',
        'callback' => '\local_learninghistory\observer::user_enrolment_created'
    ],
    [
        'eventname' => '\core\event\user_enrolment_updated',
        'callback' => '\local_learninghistory\observer::user_enrolment_updated'
    ],
    [
        'eventname' => '\core\event\user_enrolment_deleted',
        'callback' => '\local_learninghistory\observer::user_enrolment_deleted'
    ],
    // Обрабатываем события удаления, завершения, курса
    [
        'eventname' => '\core\event\course_completed',
        'callback' => '\local_learninghistory\observer::course_completed'
    ],
    [
        'eventname' => '\mod_quiz\event\attempt_started',
        'callback' => '\local_learninghistory\observer::attempt_started'
    ],
    [
        'eventname' => '\core\event\course_module_updated',
        'callback' => '\local_learninghistory\observer::course_module_updated'
    ],
    [
        'eventname' => '\core\event\course_module_deleted',
        'callback' => '\local_learninghistory\observer::course_module_deleted'
    ],
    [
        'eventname' => '\core\event\course_module_created',
        'callback' => '\local_learninghistory\observer::course_module_created'
    ],
    [
        'eventname' => '\mod_assign\event\add_attempt',
        'callback' => '\local_learninghistory\observer::add_attempt'
    ],
    [
        'eventname' => '\core\event\user_graded',
        'callback' => '\local_learninghistory\observer::user_graded'
    ],
    [
        'eventname' => '\core\event\course_module_completion_updated',
        'callback' => '\local_learninghistory\observer::course_module_completion_updated'
    ],
    [
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'callback' => '\local_learninghistory\observer::attempt_submitted'
    ],
    [
        'eventname' => '\core\event\grade_deleted',
        'callback' => '\local_learninghistory\observer::grade_deleted'
    ]
];
