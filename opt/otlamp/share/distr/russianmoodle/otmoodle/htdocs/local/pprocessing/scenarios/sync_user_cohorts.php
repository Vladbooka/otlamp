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

// Сценарий синхронизации пользователя с глобальными группами
$cohortsmanagemode = get_config('local_pprocessing', 'cohorts_manage_mode');
if( empty($cohortsmanagemode) )
{
    $cohortsmanagemode = 'disable';
}
switch($cohortsmanagemode)
{// В зависимости от выбранного режима слушаем разный список событий
    case 'enable':
        $events = [
            '\core\event\user_created',
            '\core\event\user_updated',
            '\core\event\user_deleted',
            '\core\event\cohort_member_removed'
        ];
        break;
    case 'disable':
    default:
        $events = [
            '\core\event\user_created',
            '\core\event\user_updated',
            '\core\event\user_deleted',
            '\core\event\cohort_member_added',
            '\core\event\cohort_member_removed'
        ];
        break;
}
$userfield = get_config('local_pprocessing', 'user_cohorts');
if (strpos($userfield, 'profile_field_') !== false)
{
    $userfield = str_replace('profile_field_', 'profile.', $userfield);
}
$userfield = 'user.'.$userfield;

$scenarios['sync_user_cohorts'] = [
    'events' => $events,
    'processors' => [
        [
            'type' => 'handler',
            'code' => 'get_user',
            'params' => [
                'userid' => [
                    'source_type' => 'container',
                    'source_value' => 'event.relateduserid'
                ]
            ],
            'result_variable' => 'user',
        ],
        [
            'type' => 'handler',
            'code' => 'stop_scenario_execution',
            'preconditions' => [
                [
                    'code' => 'is_empty',
                    'config' => ['invert_result' => true],
                    'params' => [
                        'value' => '$VAR.user'
                    ]
                ],
                [
                    'code' => 'is_right',
                    'params' => [
                        'check' => '$VAR.user.deleted',
                        'operator' => '=',
                        'value' => '1',
                    ],
                ]
            ]
        ],
        [
            'type' => 'handler',
            'code' => 'explode',
            'params' => [
                'value' => '$VAR.'.$userfield,
                'divider' => ','
            ],
            'result_variable' => 'usercohorts',
        ],
        [
            'type' => 'filter',
            'code' => 'cohort',
            'config' => [
                'conditions' => [
                    'type' => 'cohort',
                    'field' => get_config('local_pprocessing', 'cohort_identifier'),
                    'operator' => 'NOT IN',
                    'value' => '$VAR.usercohorts'
                ]
            ]
        ],
        [
            'type' => 'handler',
            'code' => 'get_cohorts'
        ],
        [
            'type' => 'handler',
            'code' => 'remove_from_cohorts',
            'config' => ['cohorts_manage_mode' => $cohortsmanagemode],
            'composite_key_fields' => ['user.id', 'cohort.id', 'cohort.manage']
        ],
        [
            'type' => 'filter',
            'code' => 'cohort',
            'config' => [
                'conditions' => [
                    'type' => 'cohort',
                    'field' => get_config('local_pprocessing', 'cohort_identifier'),
                    'operator' => 'IN',
                    'value' => '$VAR.usercohorts'
                ]
            ]
        ],
        [
            'type' => 'handler',
            'code' => 'get_cohorts'
        ],
        [
            'type' => 'handler',
            'code' => 'add_to_cohorts',
            'config' => ['cohorts_manage_mode' => $cohortsmanagemode],
            'composite_key_fields' => ['user.id', 'cohort.id', 'cohort.manage']
        ]
    ]
];