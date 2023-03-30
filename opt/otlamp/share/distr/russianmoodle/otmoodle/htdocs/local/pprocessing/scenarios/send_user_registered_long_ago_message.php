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

$scenarios['send_user_registered_long_ago_message'] = [
    'status' => true,
    'events' => ['\local_pprocessing\event\iteration_initialized'],
    'processors' => [
        [
            'type' => 'handler',
            'code' => 'get_user',
            'preconditions' => [
                [
                    'code' => 'is_empty',
                    'params' => [
                        'value' => '$VAR.user'
                    ]
                ]
            ],
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
            'code' => 'send_message',
            'composite_key_fields' => ['user.id'],
            'config' => [
                'messagesubject' => get_config('local_pprocessing', 'user_registered_long_ago__message_subject'),
                'messagefull' => get_config('local_pprocessing', 'user_registered_long_ago__message_full'),
                'messageshort' => get_config('local_pprocessing', 'user_registered_long_ago__message_short')
            ]
        ]
    ]
];
