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

// Отправка уведомления с паролем пользователю, полученому из события
$scenarios['send_user_password'] = [
    'status' => true,
    'events' => ['\local_pprocessing\event\iteration_initialized'],
    'processors' => [
        [
            'type' => 'handler',
            'code' => 'get_user',
            'preconditions' => [
                [
                    'code' => 'is_empty',
                    'params' => [
                        'value' => '$VAR.user'
                    ]
                ]
            ],
            'params' => [
                'userid' => [
                    'source_type' => 'container',
                    'source_value' => 'userid'
                ]
            ],
            'result_variable' => 'user',
        ],
        [
            'type' => 'handler',
            'code' => 'generate_code',
            'config' => [
                'custompolicy' => get_config('local_pprocessing', 'send_user_password_additional_password_settings'),
                'maxlen' => get_config('local_pprocessing', 'send_user_password_p_maxlen'),
                'numbers' => get_config('local_pprocessing', 'send_user_password_p_numnumbers'),
                'symbols' => get_config('local_pprocessing', 'send_user_password_p_numsymbols'),
                'lowerletters' => get_config('local_pprocessing', 'send_user_password_p_lowerletters'),
                'upperletters' => get_config('local_pprocessing', 'send_user_password_p_upperletters'),
            ],
            'result_variable' => 'generated_code'
        ],
        // сохраним пароль в базу данных
        [
            'type' => 'handler',
            'code' => 'save_password',
            'params' => [
                'password' => [
                    'source_type' => 'container',
                    'source_value' => 'generated_code'
                ],
                'password_type' => [
                    'source_type' => 'static',
                    'source_value' => 'plaintext'
                ]
            ]
        ],
        [
            'type' => 'handler',
            'code' => 'unset_user_preference',
            'config' => [
                'create_password',
            ]
        ],
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
                    'source_value' => 'send_user_password_auth_forcepasswordchange'
                ]
            ],
            'result_variable' => 'authforcepasswordchange'
        ],
        [
            'type' => 'handler',
            'code' => 'set_contariner_value',
            'preconditions' => [
                [
                    'code' => 'is_empty',
                    'params' => [
                        'value' => '$VAR.authforcepasswordchange'
                    ]
                ]
            ],
            'params' => [
                'value' => [
                    'source_type' => 'static',
                    'source_value' => null
                ],
                'var_name' => [
                    'source_type' => 'static',
                    'source_value' => 'authforcepasswordchange'
                ]
            ]
        ],
        [
            'type' => 'handler',
            'code' => 'set_user_preference',
            'params' => [
                'name' => [
                    'source_type' => 'static',
                    'source_value' => 'auth_forcepasswordchange'
                ],
                'value' => [
                    'source_type' => 'container',
                    'source_value' => 'authforcepasswordchange'
                ]
            ]
        ],
        [
            'type' => 'handler',
            'code' => 'send_message',
            'config' => [
                'messagesubject' => get_config('local_pprocessing', 'send_user_password_message_subject'),
                'messagefull' => get_config('local_pprocessing', 'send_user_password_message_full'),
                'messageshort' => get_config('local_pprocessing', 'send_user_password_message_short'),
                'message_name' => 'service_messages'
            ]
        ]
    ]
];