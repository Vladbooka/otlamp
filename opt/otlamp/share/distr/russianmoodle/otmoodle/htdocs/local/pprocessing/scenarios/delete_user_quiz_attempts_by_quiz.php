<?php

defined('MOODLE_INTERNAL') || die();

$scenarios['delete_user_quiz_attempts_by_quiz'] = [
    'status' => true,
    'processors' => [
        [
            // Готовим запрос для получения списка всех попыток пользователя в тесте
            'type' => 'handler',
            'code' => 'compose_sql_conditions',
            'params' => [
                'conditions' => [
                    'AND' => [
                        [
                            'field' => 'userid',
                            'operator' => '=',
                            'value' => '$VAR.user.id'
                        ],
                        [
                            'field' => 'quiz',
                            'operator' => '=',
                            'value' => '$VAR.quiz.id'
                        ],
                    ]
                ],
            ],
            'result_variable' => 'all_user_attempts_conditions'
        ],
        [
            // Получаем все попытки пользователя в тесте
            'type' => 'handler',
            'code' => 'get_quiz_attempts',
            'params' => [
                'sqlconds' => '$VAR.all_user_attempts_conditions',
            ],
            'result_variable' => 'all_user_attempts'
        ],
        [
            'type' => 'handler',
            'code' => 'count',
            'params' => [
                'value' => '$VAR.all_user_attempts'
            ],
            'result_variable' => 'all_user_attempts_count'
        ],
        [
            // останавливаем сценарий, если попытка одна или нет попыток
            'type' => 'handler',
            'code' => 'stop_scenario_execution',
            'preconditions' => [
                [
                    'code' => 'is_right',
                    'params' => [
                        'check' => 1,
                        'operator' => '>=',
                        'value' => '$VAR.all_user_attempts_count',
                    ],
                ]
            ]
        ],
        [
            'type' => 'handler',
            'code' => 'strtotime',
            'params' => [
                'time' => '$VAR.absolutedate'
            ],
            'result_variable' => 'absolutedate_timestamp'
        ],
        [
            // Готовим запрос для получение списка попыток пользователя в тесте на удаление
            'type' => 'handler',
            'code' => 'compose_sql_conditions',
            'params' => [
                'conditions' => [
                    'AND' => [
                        [
                            'OR' => [
                                [
                                    'AND' => [
                                        [
                                            'field' => 'state',
                                            'operator' => '=',
                                            'value' => quiz_attempt::FINISHED
                                        ],
                                        [
                                            'field' => 'timefinish',
                                            'operator' => '>',
                                            'value' => 0
                                        ],
                                        [
                                            'field' => 'timefinish',
                                            'operator' => '<',
                                            'value' => '$VAR.absolutedate_timestamp'
                                        ],
                                    ],
                                ],
                                [
                                    'field' => 'state',
                                    'operator' => '=',
                                    'value' => quiz_attempt::ABANDONED
                                ],
                            ]
                        ],
                        [
                            'field' => 'quiz',
                            'operator' => '=',
                            'value' => '$VAR.quiz.id'
                        ],
                        [
                            'field' => 'userid',
                            'operator' => '=',
                            'value' => '$VAR.user.id'
                        ],
                    ]
                ],
            ],
            'result_variable' => 'user_attempts_conditions'
        ],
        [
            // Получаем список попыток пользователя в тесте на удаление
            'type' => 'handler',
            'code' => 'get_recordset_select',
            'params' => [
                'table' => [
                    'source_type' => 'static',
                    'source_value' => 'quiz_attempts'
                ],
                'sqlconds' => '$VAR.user_attempts_conditions',
            ],
            'result_variable' => 'rs_user_attempt'
        ],
        [
            // останавливаем сценарий, если нет попыток подлежащих удалению
            'type' => 'handler',
            'code' => 'stop_scenario_execution',
            'preconditions' => [
                [
                    'code' => 'is_empty',
                    'params' => [
                        'value' => '$VAR.rs_user_attempt'
                    ]
                ]
            ]
        ],
        [
            // Получаем лучшую попытку пользователя в тесте
            'type' => 'handler',
            'code' => 'get_best_user_attempt_by_quiz',
            'params' => [
                'attempts' => [
                    'source_type' => 'container_export',
                    'source_value' => 'all_user_attempts'
                ],
                'quiz' => [
                    'source_type' => 'container_export',
                    'source_value' => 'quiz'
                ]
            ],
            'result_variable' => 'best_user_attempt'
        ],
        [
            // Запускаем итератор для удаления каждой попытки теста, кроме лучшей
            'type'   => 'iterator',
            'code'   => 'iterator_based',
            'params' => [
                'rs' => [
                    'source_type' => 'container',
                    'source_value' => 'rs_user_attempt'
                ],
                'mtrace' => [
                    'source_type' => 'static',
                    'source_value' => [
                        'identifier' => 'attempt_mtrace',
                        'properties' => [
                            'id',
                            'quiz'
                        ]
                    ]
                ]
            ],
            'config' => [
                'scenario' => 'delete_user_quiz_attempt_by_quiz',
                'iterate_item_var_name' => 'attempt'
            ]
        ],
        [
            // Закрываем итератор
            'type'   => 'handler',
            'code'   => 'recordset_close',
            'params' => [
                'rs' => [
                    'source_type' => 'container_export',
                    'source_value' => 'rs_user_attempt'
                ],
            ]
        ]
    ]
];