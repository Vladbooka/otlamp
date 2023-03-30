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

// сценарий удаления пользователей,
// зарегистрированных два месяца назад и до сих пор не зашедших в систему
$scenarios['user_registered_long_ago_deleting'] = [
    'events' => ['\local_pprocessing\event\daily_executed'],
    'processors' => [
        [
            'type' => 'handler',
            'code' => 'strtotime',
            'params' => [
                'time' => '-2 months'
            ],
            'result_variable' => 'longago_timestamp'
        ],
        [
            'type' => 'filter',
            'code' => 'user',
            'config' => [
                'conditions' => [
                    'AND' => [
                        [
                            'type' => 'user_main_field',
                            'field' => 'confirmed',
                            'operator' => '>=',
                            'value' => 0
                        ],
                        [
                            'type' => 'user_main_field',
                            'field' => 'firstaccess',
                            'operator' => '=',
                            'value' => 0
                        ],
                        [
                            'type' => 'user_main_field',
                            'field' => 'deleted',
                            'operator' => '=',
                            'value' => 0
                        ],
                        [
                            'type' => 'user_main_field',
                            'field' => 'timecreated',
                            'operator' => '<=',
                            'value' => '$VAR.longago_timestamp'
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
                'scenario' => 'user_deletion',
                'trigger_event' => true,
                'iterate_item_var_name' => 'user'
            ]
        ]
    ]
];