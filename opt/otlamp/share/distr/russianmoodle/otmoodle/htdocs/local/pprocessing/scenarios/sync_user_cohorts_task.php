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

$scenarios['sync_user_cohorts_task'] = [
    'events' => ['\local_pprocessing\event\daily_executed'],
    'processors' => [
        [
            'type' => 'handler',
            'code' => 'get_scenario_status',
            'params' => [
                'scenario' => [
                    'source_type' => 'static',
                    'source_value' => 'sync_user_cohorts'
                ]
            ]
        ],
        [
            'type' => 'handler',
            'code' => 'stop_scenario_execution',
            'preconditions' => [
                [
                    'code' => 'is_right',
                    'params' => [
                        'check' => '$RES',
                        'operator' => '=',
                        'value' => 'disabled',
                    ],
                ]
            ]
        ],
        [
            'type' => 'filter',
            'code' => 'user',
            'config' => [
                'conditions' => [
                    'type' => 'user_main_field',
                    'field' => 'confirmed',
                    'operator' => '>=',
                    'value' => 0
                ]
            ]
        ],
        [
            'type' => 'handler',
            'code' => 'get_users'
        ],
        [
            'type' => 'handler',
            'code' => 'trigger_event_user_updated'
        ]
    ]
];