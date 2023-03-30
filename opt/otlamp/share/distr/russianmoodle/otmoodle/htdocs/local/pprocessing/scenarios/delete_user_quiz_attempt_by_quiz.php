<?php

defined('MOODLE_INTERNAL') || die();

$scenarios['delete_user_quiz_attempt_by_quiz'] = [
    'status' => true,
    'processors' => [
        [
            // Запускаем процедуру удаления попытки, если она не лучшая
            'type' => 'handler',
            'code' => 'delete_quiz_attempt',
            'preconditions' => [
                [
                    'code' => 'is_right',
                    'params' => [
                        'check' => '$VAR.attempt.id',
                        'operator' => '<>',
                        'value' => '$VAR.best_user_attempt.id',
                    ],
                ]
            ],
            'params' => [
                'attempt' => [
                    'source_type' => 'container_export',
                    'source_value' => 'attempt'
                ],
                'quiz' => [
                    'source_type' => 'container_export',
                    'source_value' => 'quiz'
                ],
                // Так как мы не удаляем попытки, влияющие на оценку по тесту, нет необходимости обновлять оценки
                'needupdategrades' => [
                    'source_type' => 'static',
                    'source_value' => false
                ]
            ],
        ],
    ]
];