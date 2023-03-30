<?php

defined('MOODLE_INTERNAL') || die();

$scenarios['delete_quiz_attempt'] = [
    'status' => true,
    'events' => ['\local_pprocessing\event\iteration_initialized'],
    'processors' => [
        [
            'type' => 'handler',
            'code' => 'compose_sql_conditions',
            'params' => [
                'conditions' => [
                    'AND' => [
                        [
                            'field' => 'id',
                            'operator' => '=',
                            'value' => '$VAR.quiz_attempt.quiz'
                        ],
                    ]
                ]
            ],
            'result_variable' => 'quiz_conditions'
        ],
        [
            'type' => 'handler',
            'code' => 'get_quiz',
            'params' => [
                'sqlconds' => '$VAR.quiz_conditions',
            ],
            'result_variable' => 'quizes'
        ],
        [
            'type' => 'handler',
            'code' => 'array_shift',
            'params' => [
                'array' => [
                    'source_type' => 'container',
                    'source_value' => 'quizes'
                ],
            ],
            'result_variable' => 'quiz'
        ],
        [
            'type' => 'handler',
            'code' => 'delete_quiz_attempt',
            'preconditions' => [
                [
                    'code' => 'is_quiz_best_attempt',
                    'config' => ['invert_result' => true],
                    'params' => [
                        'attempt' => [
                            'source_type' => 'container_export',
                            'source_value' => 'quiz_attempt'
                        ],
                        'quiz' => [
                            'source_type' => 'container_export',
                            'source_value' => 'quiz'
                        ],
                    ]
                ]
            ],
            'params' => [
                'attempt' => [
                    'source_type' => 'container_export',
                    'source_value' => 'quiz_attempt'
                ],
                'quiz' => [
                    'source_type' => 'container_export',
                    'source_value' => 'quiz'
                ],
            ],
        ],
    ]
];