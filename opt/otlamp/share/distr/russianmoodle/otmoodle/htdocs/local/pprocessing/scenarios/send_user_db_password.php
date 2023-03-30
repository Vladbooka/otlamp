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

// Отправка уведомления с паролем из внешней базы данных пользователю, полученому из события

$scenarios['send_user_db_password'] = [
    'status' => true,
    'events' => ['\local_pprocessing\event\iteration_initialized'],
    'processors' => [
        // получим пользователя из итератора
        // предполагается, что данный сценарий может быть вызван кем угодно
        // и этот кто угодно может не передать объект пользователя
        // в этом случае, мы ищем пользователя по userid, взятому из события (формируется в local_pprocessing\resolver::resolve_event())
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
        // получим настройки внешней базы данных
        [
            'type' => 'handler',
            'code' => 'get_db_connection_config',
            'params' => [
                'code' => 'auth_db'
            ],
            'result_variable' => 'dbconnection'
        ],
        [
            'type' => 'handler',
            'code' => 'get_plugin_config',
            'params' => [
                'plugin' => 'auth_db'
            ],
            'result_variable' => 'config_auth_db'
        ],
        // получим пароль из внешней базы данных по настройкам
        [
            'type' => 'handler',
            'code' => 'db_get_record_field',
            'params' => [
                'connection' => '$VAR.dbconnection',
                'table_name' => '$VAR.config_auth_db.table',
                'field' => '$VAR.config_auth_db.fieldpass',
                'conditions' => [
                    'AND' => [
                        [
                            'field' => '$VAR.config_auth_db.fielduser',
                            'operator' => '=',
                            'value' => '$VAR.user.username'
                        ],
                        [
                            'field' => '$VAR.config_auth_db.fieldpass',
                            'operator' => '<>',
                            'value' => ''
                        ]
                    ]
                ]
            ],
            'result_variable' => 'extdbpassworld',
        ],
        // проверим пароль на валидность
        [
            'type' => 'handler',
            'code' => 'stop_scenario_execution',
            'preconditions' => [
                [
                    'code' => 'is_string_empty',
                    'params' => [
                        'value' => '$VAR.extdbpassworld'
                    ]
                ]
            ]
        ],
        // получим из конфига тип сохраняемого пароля
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
                    'source_value' => 'send_user_db_password_password_type'
                ]
            ],
            'result_variable' => 'passwordtype'
        ],
        // сохраним пароль в базу данных
        [
            'type' => 'handler',
            'code' => 'save_password',
            'params' => [
                'password' => [
                    'source_type' => 'container',
                    'source_value' => 'extdbpassworld'
                ],
                'password_type' => [
                    'source_type' => 'container',
                    'source_value' => 'passwordtype'
                ]
            ]
        ],
        // установим в контейнер вместо пароля * если он в md5
        [
            'type' => 'handler',
            'code' => 'set_container_values',
            'preconditions' => [
                [
                    'code' => 'is_right',
                    'params' => [
                        'check' => '$VAR.passwordtype',
                        'operator' => '=',
                        'value' => 'md5',
                    ],
                ]
            ],
            'params' =>  [
                'vars' => [
                    'extdbpassworld' => '*****'
                ]
            ]
        ],
        // уберем из user preference параметр отвечающий за необходимость создать пароль
		[
            'type' => 'handler',
            'code' => 'unset_user_preference',
            'config' => [
                'create_password',
            ]
        ],
        // получим настройку принудительной смены пароля
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
                    'source_value' => 'send_user_db_password_auth_forcepasswordchange'
                ]
            ],
            'result_variable' => 'authforcepasswordchange'
        ],
        // установим в контейнер null если параметр принудительной смены пароля пуст
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
        // установим в user preference параметр отвечающий за принудительную смену пароля
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
        // получим настройку отвечабщую за отправку пароля
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
                    'source_value' => 'send_new_user_db_passwords_send_message'
                ]
            ],
            'result_variable' => 'db_passwords_send_message'
        ],
        [// останавливаем сценарий, если не активна настройка отправки сообщений
            'type' => 'handler',
            'code' => 'stop_scenario_execution',
            'preconditions' => [
                [
                    'code' => 'is_empty',
                    'params' => [
                        'value' => '$VAR.db_passwords_send_message'
                    ]
                ]
            ]
        ],
        // отправим сообщение
        [
            'type' => 'handler',
            'code' => 'send_message',
            'config' => [
                'messagesubject' => get_config('local_pprocessing', 'send_user_db_password_message_subject'),
                'messagefull' => get_config('local_pprocessing', 'send_user_db_password_message_full'),
                'messageshort' => get_config('local_pprocessing', 'send_user_db_password_message_short'),
                'message_name' => 'service_messages'
            ]
        ]
    ]
];