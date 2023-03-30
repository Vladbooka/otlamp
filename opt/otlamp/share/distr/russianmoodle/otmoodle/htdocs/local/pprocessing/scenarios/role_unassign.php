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

// сценарий снятия назначения ролей
$contextlevels = [
    'system' => CONTEXT_SYSTEM,
    'coursecat' => CONTEXT_COURSECAT,
    'course' => CONTEXT_COURSE,
    'module' => CONTEXT_MODULE,
    'user' => CONTEXT_USER,
    'block' => CONTEXT_BLOCK
];
$configcontext = get_config('local_pprocessing', 'role_unassign_context');
$roleid = get_config('local_pprocessing', 'role_unassign_role');
if( ! empty($configcontext) && $configcontext != 'none' && isset($contextlevels[$configcontext]) && $roleid > 0 )
{
    $scenarios['role_unassign'] = [
        'events' => ['\local_pprocessing\event\daily_executed'],
        'processors' => [
            [
                'type' => 'filter',
                'code' => 'context',
                'config' => [
                    'contextlevel' => [
                        'operator' => '=',
                        'value' => $contextlevels[$configcontext]
                    ]
                ]
            ],
            [
                'type' => 'handler',
                'code' => 'get_contexts'
            ],
            [
                'type' => 'filter',
                'code' => 'role_assignment',
                'config' => [
                    'roleid' => [
                        'operator' => '=',
                        'value' => $roleid
                    ]
                ]
            ],
            [
                'type' => 'handler',
                'code' => 'get_role_assignments'
            ],
            [
                'type'   => 'iterator',
                'code'   => 'event_based',
                'config' => [
                    'scenario' => 'unassign',
                    'trigger_event' => true,
                    'iterate_item_var_name' => 'role_assignment'
                ]
            ]
        ]
    ];
}