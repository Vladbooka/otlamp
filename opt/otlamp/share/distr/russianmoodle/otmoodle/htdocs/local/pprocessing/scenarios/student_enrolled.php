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

defined('MOODLE_INTERNAL') || die();

// сценарий отправки уведомления слушателю о том, что его подписали на курс
$scenarios['student_enrolled'] = [
    'events' => ['\core\event\role_assigned'],
    'processors' => [
        [// останавливаем сценарий, если перед нами не студент
            'type' => 'handler',
            'code' => 'stop_scenario_execution',
            'preconditions' => [
                [
                    'code' => 'is_student',
                    'config' => ['invert_result' => true]
                ]
            ]
        ],
        [
            'type' => 'handler',
            'code' => 'get_user',
            'params' => [
                'userid' => [
                    'source_type' => 'container',
                    'source_value' => 'userid'
                ]
            ],
            'result_variable' => 'user',
        ],
        [
            'type' => 'handler',
            'code' => 'get_course'
        ],
        [
            'type' => 'handler',
            'code' => 'send_message',
            'config' => [
                'messagesubject' => get_config('local_pprocessing', 'student_enrolled_message_subject'),
                'messagefull' => get_config('local_pprocessing', 'student_enrolled_message_full'),
                'messageshort' => get_config('local_pprocessing', 'student_enrolled_message_short')
            ]
        ]
    ]
];