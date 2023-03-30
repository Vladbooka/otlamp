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

// Отправка уведомлений с паролем пользователям, загруженным в систему
$scenarios['send_new_user_passwords'] = [
    'events' => ['\local_pprocessing\event\asap_executed'],
    'processors' => [
        [
            'type' => 'handler',
            'code' => 'get_task_status',
            'params' => [
                'task' => [
                    'source_type' => 'static',
                    'source_value' => '\\core\\task\\send_new_user_passwords_task'
                ]
            ],
            'result_variable' => 'taskstatus'
        ],
        [
            'type' => 'handler',
            'code' => 'stop_scenario_execution',
            'preconditions' => [
                [
                    'code' => 'is_right',
                    'params' => [
                        'check' => '$VAR.taskstatus',
                        'operator' => '=',
                        'value' => 'enabled',
                    ],
                ]
            ]
        ],
        [
            'type' => 'filter',
            'code' => 'user',
            'config' => [
                'conditions' => [
                    'AND' => [
                        [
                            'type' => 'user_preference',
                            'field' => 'create_password',
                            'operator' => '=',
                            'value' => 1
                        ],
                        [
                            'type' => 'user_main_field',
                            'field' => 'email',
                            'operator' => '<>',
                            'value' => ''
                        ],
                        [
                            'type' => 'user_main_field',
                            'field' => 'suspended',
                            'operator' => '=',
                            'value' => 0
                        ],
                        [
                            'type' => 'user_main_field',
                            'field' => 'auth',
                            'operator' => '<>',
                            'value' => 'nologin'
                        ]
                    ]
                ]
            ]
        ],
        [
            'type' => 'handler',
            'code' => 'get_users'
        ],
        [
            'type'   => 'iterator',
            'code'   => 'event_based',
            'config' => [
                'scenario' => 'send_user_password',
                'trigger_event' => true,
                'iterate_item_var_name' => 'user'
            ]
        ]
    ]
];