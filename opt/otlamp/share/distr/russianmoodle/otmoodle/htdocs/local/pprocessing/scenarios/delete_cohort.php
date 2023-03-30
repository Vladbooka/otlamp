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

$scenarios['delete_cohort'] = [
    'status' => true,
    'events' => ['\local_pprocessing\event\iteration_initialized'],
    'processors' => [
        [// записываем в переменную cohort.id идентификатор из mcov_cohort.objid, при условии, что он там есть
        // всё это - заготовка под возможность использовать этот же сценарий позднее
        // когда триггером будут другие эвенты или запуск будет осуществлён другими родительскими сценариями
            'type' => 'handler',
            'code' => 'get_container_value',
            'preconditions' => [
                [
                    'code' => 'is_empty',
                    'config' => ['invert_result' => true],
                    'params' => [
                        'value' => '$VAR.mcov_cohort.objid'
                    ]
                ]
            ],
            'params' => [
                'varname' => [
                    'source_type' => 'static',
                    'source_value' => 'mcov_cohort.objid'
                ]
            ],
            'result_variable' => 'cohortid',
        ],
        [// останавливаем сценарий, если в cohort.id ничего нет
            'type' => 'handler',
            'code' => 'stop_scenario_execution',
            'preconditions' => [
                [
                    'code' => 'is_empty',
                    'params' => [
                        'value' => '$VAR.cohortid'
                    ]
                ]
            ]
        ],
        [// получаем ГГ по идентификатору
            'type' => 'handler',
            'code' => 'get_cohort',
            'params' => [
                'id' => [
                    'source_type' => 'container',
                    'source_value' => 'cohortid'
                ]
            ],
            'result_variable' => 'cohort'
        ],
        [// останавливаем сценарий, если в cohort false (группы уже нет, но в базе остались ее кастомные свойства)
            'type' => 'handler',
            'code' => 'stop_scenario_execution',
            'preconditions' => [
                [
                    'code' => 'is_empty',
                    'params' => [
                        'value' => '$VAR.cohort'
                    ]
                ]
            ]
        ],
        [// удаляем ГГ по объекту
            'type' => 'handler',
            'code' => 'delete_cohort',
            'params' => [
                'cohort' => [
                    'source_type' => 'container',
                    'source_value' => 'cohort'
                ]
            ]
        ]
    ]
];