<?php
require_once(dirname(realpath(__FILE__)).'/../../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
defined('MOODLE_INTERNAL') || die();

$scenarios['get_user_attempts_by_quiz'] = [
    'status' => true,
    'processors' => [
        [
            // останавливаем сценарий, если оценка за тест рассчитывается как среднее значение
            'type' => 'handler',
            'code' => 'stop_scenario_execution',
            'preconditions' => [
                [
                    'code' => 'is_right',
                    'params' => [
                        'check' => '$VAR.quiz.grademethod',
                        'operator' => '=',
                        'value' => QUIZ_GRADEAVERAGE,
                    ],
                ]
            ]
        ],
        [
            // Получаем список пользователей, имеющих попытки прохождения теста
            'type' => 'handler',
            'code' => 'get_recordset_sql',
            'params' => [
                'sql' => [
                    'source_type' => 'static',
                    'source_value' => 'SELECT u.id
                                         FROM {user} u
                                    LEFT JOIN {quiz_attempts} qa
                                           ON u.id=qa.userid
                                        WHERE qa.quiz = :quiz
                                          AND qa.userid IS NOT NULL
                                     GROUP BY u.id'
                ],
                'params' => [
                    'source_type' => 'static',
                    'source_value' => [
                        'quiz' => [
                            'source_type' => 'container',
                            'source_value' => 'quiz.id'
                        ]
                    ]
                ]
            ],
            'result_variable' => 'rs_user'
        ],
        [
            // По каждому пользователю запускаем итератор удаления попыток тестирования
            'type'   => 'iterator',
            'code'   => 'iterator_based',
            'config' => [
                'scenario' => 'delete_user_quiz_attempts_by_quiz',
                'iterate_item_var_name' => 'user'
            ],
            'params' => [
                'mtrace' => [
                    'source_type' => 'static',
                    'source_value' => [
                        'identifier' => 'user_mtrace',
                        'properties' => [
                            'id'
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
                    'source_value' => 'rs_user'
                ],
            ]
        ]
    ]
];