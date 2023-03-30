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
 * Блок комментарий преподавателя.
 *
 * @package     block_quiz_teacher_feedback
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = [
    'block_quiz_teacher_feedback_get_feedback' => [
        'classname'   => 'block_quiz_teacher_feedback\ajax\feedback',
        'methodname'  => 'get_data',
        'classpath'   => '',
        'description' => 'Returns feedback to question',
        'type'        => 'read',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => []
    ],
    'block_quiz_teacher_feedback_get_questions_list' => [
        'classname'   => 'block_quiz_teacher_feedback\ajax\questions',
        'methodname'  => 'get_questions_list',
        'classpath'   => '',
        'description' => 'Returns questions list',
        'type'        => 'read',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => []
    ],
    'block_quiz_teacher_feedback_switch_request_status' => [
        'classname'   => 'block_quiz_teacher_feedback\ajax\request',
        'methodname'  => 'switch_request_status',
        'classpath'   => '',
        'description' => 'Switch request status',
        'type'        => 'write',
        'capabilities' => '',
        'ajax'        => true,
        'services'    => []
    ]
];