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
 * Сценарий удаления попыток тестирования, с момента завершения которых прошло больше времени, чем указано в настройках
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(realpath(__FILE__)).'/../../../config.php');
global $CFG;
require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');

$scenarios['delete_quiz_attempts_by_date'] = [
    'events' => ['\local_pprocessing\event\daily_executed'],
    'processors' => [
        [
            // Получаем относительное время, заданное настройками
            'type' => 'handler',
            'code' => 'get_plugin_config',
            'params' => [
                'plugin' => [
                    'source_type' => 'static',
                    'source_value' => 'local_pprocessing'
                ],
                'name' => [
                    'source_type' => 'static',
                    'source_value' => 'delete_quiz_attempts_by_date__relativedate'
                ]
            ],
            'result_variable' => 'relativedate'
        ],
        [
            // останавливаем сценарий, если время не задано
            'type' => 'handler',
            'code' => 'stop_scenario_execution',
            'preconditions' => [
                [
                    'code' => 'is_empty',
                    'params' => [
                        'value' => '$VAR.relativedate'
                    ]
                ]
            ]
        ],
        [
            // Готовим строку для передачи в обработчик подготовки sql-запроса
            'type' => 'handler',
            'code' => 'implode',
            'params' => [
                'pieces' => [
                    'source_type' => 'static',
                    'source_value' => [
                        '-',
                        [
                            'source_type' => 'container',
                            'source_value' => 'relativedate'
                        ],
                        'sec',
                    ]
                ],
                'glue' => [
                    'source_type' => 'static',
                    'source_value' => ' '
                ],
            ],
            'result_variable' => 'absolutedate'
        ],
        [
            // Выбираем все тесты с добавлением в запись cmid, который будет нужен в процессе удаления попыток
            'type' => 'handler',
            'code' => 'get_recordset_sql',
            'params' => [
                'sql' => [
                    'source_type' => 'static',
                    'source_value' => 'SELECT q.*, cm.id AS cmid
                                         FROM {quiz} q
                                    LEFT JOIN {course_modules} cm
                                           ON q.id = cm.instance
                                    LEFT JOIN {modules} m
                                           ON cm.module = m.id
                                        WHERE m.name = :quiz'
                ],
                'params' => [
                    'source_type' => 'static',
                    'source_value' => [
                        'quiz' => [
                            'source_type' => 'static',
                            'source_value' => 'quiz'
                        ]
                    ]
                ],
            ],
            'result_variable' => 'rs_quiz'
        ],
        [
            // Запускаем итератор для получения списка пользователей, имеющих попытки по каждому тесту
            'type'   => 'iterator',
            'code'   => 'iterator_based',
            'config' => [
                'scenario' => 'get_user_attempts_by_quiz',
                'iterate_item_var_name' => 'quiz'
            ],
            'params' => [
                'mtrace' => [
                    'source_type' => 'static',
                    'source_value' => [
                        'identifier' => 'quiz_mtrace',
                        'properties' => [
                            'id',
                            'cmid'
                        ]
                    ]
                ]
            ]
        ],
        [
            // Закрываем итератор
            'type'   => 'handler',
            'code'   => 'recordset_close',
            'params' => [
                'rs' => [
                    'source_type' => 'container_export',
                    'source_value' => 'rs_quiz'
                ],
            ]
        ]
    ]
];