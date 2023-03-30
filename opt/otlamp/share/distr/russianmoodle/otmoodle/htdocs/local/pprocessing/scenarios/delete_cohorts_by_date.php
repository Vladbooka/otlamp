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

$scenarios['delete_cohorts_by_date'] = [
    'events' => ['\local_pprocessing\event\daily_executed'],
    'processors' => [
        [
            'type' => 'handler',
            'code' => 'get_plugin_config',
            'params' => [
                'plugin' => [
                    'source_type' => 'static',
                    'source_value' => 'local_pprocessing'
                ],
                'name' => [
                    'source_type' => 'static',
                    'source_value' => 'delete_cohorts_by_date__deldate'
                ]
            ],
            'result_variable' => 'mcov_deldate_prop'
        ],
        [// останавливаем сценарий, если кастомное поле, которое должно содержать дату - не настрено
            'type' => 'handler',
            'code' => 'stop_scenario_execution',
            'preconditions' => [
                [
                    'code' => 'is_empty',
                    'params' => [
                        'value' => '$VAR.mcov_deldate_prop'
                    ]
                ]
            ]
        ],
        [
            'type' => 'handler',
            'code' => 'get_plugin_config',
            'params' => [
                'plugin' => [
                    'source_type' => 'static',
                    'source_value' => 'local_mcov'
                ],
                'name' => [
                    'source_type' => 'static',
                    'source_value' => 'cohort_yaml'
                ]
            ],
            'result_variable' => 'mcov_cohort_config'
        ],
        [// останавливаем сценарий, если конфигурация кастомных полей для ГГ не сохранена
            'type' => 'handler',
            'code' => 'stop_scenario_execution',
            'preconditions' => [
                [
                    'code' => 'is_empty',
                    'params' => [
                        'value' => '$VAR.mcov_cohort_config'
                    ]
                ]
            ]
        ],
        [
            'type' => 'handler',
            'code' => 'strtotime',
            'result_variable' => 'timenow'
        ],
        [
            'type' => 'handler',
            'code' => 'compose_sql_conditions',
            'params' => [
                'conditions' => [
                    'AND' => [
                        [
                            'field' => 'prop',
                            'operator' => '=',
                            'value' => '$VAR.mcov_deldate_prop'
                        ],
                        [
                            'field' => 'value',
                            'operator' => '<=',
                            'value' => '$VAR.timenow'
                        ],
                    ]
                ]
            ],
            'result_variable' => 'mcovconditions'
        ],
        [
            'type' => 'handler',
            'code' => 'get_pub_mcovs',
            'params' => [
                'entity' => 'cohort',
                'sqlconds' => '$VAR.mcovconditions',
            ],
            'result_variable' => 'mcov_cohort'
        ],
        [// останавливаем сценарий, если группы по запросу не найдены
            'type' => 'handler',
            'code' => 'stop_scenario_execution',
            'preconditions' => [
                [
                    'code' => 'is_empty',
                    'params' => [
                        'value' => '$VAR.mcov_cohort'
                    ]
                ]
            ]
        ],
        [
            'type' => 'handler',
            'code' => 'get_container_value',
            'params' => [
                'varname' => [
                    'source_type' => 'static',
                    'source_value' => 'mcov_cohort'
                ]
            ]
        ],
        [
            'type'   => 'iterator',
            'code'   => 'event_based',
            'config' => [
                'scenario' => 'delete_cohort',
                'trigger_event' => false,
                'iterate_item_var_name' => 'mcov_cohort'
            ]
        ]
    ]
];