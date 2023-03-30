<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

/**
 * Обмен данных с внешними источниками. Класс стратегии импорта учебного плана
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_strategy_cstreams extends dof_modlib_transmit_strategy_base
{
    /**
     * Список полей, доступных для импорта
     *
     * @var array
     */
    public static $importfields = [
        
        // Данные по подразделению
        'department_id' => ['type' => PARAM_INT],
        'department_code' => ['type' => PARAM_RAW_TRIMMED],
        // Данные учебного периода
        'age_id' => ['type' => PARAM_INT],
        // Данные дисциплины
        'programmitem_id' => ['type' => PARAM_INT],
        'programmitem_code' => ['type' => PARAM_RAW_TRIMMED],
        // Email преподавателя
        'teacher_email' => ['type' => PARAM_EMAIL],
        // ФИО преподавателя
        'teacher_lastname' => ['type' => PARAM_RAW_TRIMMED],
        'teacher_firstname' => ['type' => PARAM_RAW_TRIMMED],
        'teacher_middlename' => ['type' => PARAM_RAW_TRIMMED],
        // ID назначения на должность
        'teacher_appointemnt_id' => ['type' => PARAM_INT],
        // Данные учебного процесса
        'cstream_id' => ['type' => PARAM_INT],
        'cstream_name' => ['type' => PARAM_RAW_TRIMMED],
        'cstream_description' => ['type' => PARAM_RAW_TRIMMED],
        'cstream_begindate' => ['type' => PARAM_RAW_TRIMMED],
        'cstream_enddate' => ['type' => PARAM_RAW_TRIMMED],
        'cstream_hoursweek' => ['type' => PARAM_RAW_TRIMMED],
        // Список ФИО обучающихся
        'cpassed_fullname_list' => ['type' => PARAM_RAW_TRIMMED],
        'cpassed_fullname_list_delimiter' => ['type' => PARAM_RAW_TRIMMED],
        'cpassed_fullname_list_fullnameformat' => ['type' => PARAM_RAW_TRIMMED],
    ];
    
    /**
     * Пул валидаторов
     *
     * @var array
     */
    protected $validators = [
        
        // Валидатор числовых данных
        'validator_general_numeric' => [
            [
                'required_slots' => ['numeric' => 'department_id'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['numeric' => 'department_id_valid']
            ],
            [
                'required_slots' => ['numeric' => 'age_id'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['numeric' => 'age_id_valid']
            ],
            [
                'required_slots' => ['numeric' => 'programmitem_id'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['numeric' => 'programmitem_id_valid']
            ],
            [
                'required_slots' => ['numeric' => 'teacher_appointemnt_id'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['numeric' => 'teacher_appointemnt_id_valid']
            ],
            [
                'required_slots' => ['numeric' => 'cstream_id'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['numeric' => 'cstream_id_valid']
            ],
            [
                'required_slots' => ['numeric' => 'cstream_hoursweek'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['numeric' => 'cstream_hoursweek_valid']
            ]
        ],
        'validator_general_string' => [
            [
                'required_slots' => ['string' => 'department_code'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'department_code_valid']
            ],
            [
                'required_slots' => ['string' => 'programmitem_code'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'programmitem_code_valid']
            ],
            [
                'required_slots' => ['string' => 'teacher_lastname'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'teacher_lastname_valid']
            ],
            [
                'required_slots' => ['string' => 'teacher_firstname'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'teacher_firstname_valid']
            ],
            [
                'required_slots' => ['string' => 'teacher_middlename'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'teacher_middlename_valid']
            ],
            [
                'required_slots' => ['string' => 'cstream_name'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'cstream_name_valid']
            ],
            [
                'required_slots' => ['string' => 'cstream_description'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'cstream_description_valid']
            ],
            [
                'required_slots' => ['string' => 'cpassed_fullname_list'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'cpassed_fullname_list_valid']
            ],
            [
                'required_slots' => ['string' => 'cpassed_fullname_list_delimiter'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'cpassed_fullname_list_delimiter_valid']
            ],
            [
                'required_slots' => ['string' => 'cpassed_fullname_list_fullnameformat'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'cpassed_fullname_list_fullnameformat_valid']
            ],
            [
                'required_slots' => ['string' => '/cpassed([0-9]*)_firstname/m'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'cpassed$1_firstname_valid']
            ],
            [
                'required_slots' => ['string' => '/cpassed([0-9]*)_lastname/m'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'cpassed$1_lastname_valid']
            ],
            [
                'required_slots' => ['string' => '/cpassed([0-9]*)_middlename/m'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'cpassed$1_middlename_valid']
            ]
        ],
        'validator_general_date' => [
            [
                'required_slots' => ['date' => 'cstream_begindate'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['date' => 'cstream_begindate_valid']
            ],
            [
                'required_slots' => ['date' => 'cstream_enddate'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['date' => 'cstream_enddate_valid']
            ]
        ],
        'validator_general_email' => [
            [
                'required_slots' => ['email' => 'teacher_email'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['email' => 'teacher_email_valid']
            ]
        ],
        'validator_department_id_exists' => [
            // Проверка наличия подразделения по ID
            [
                'required_slots' => ['department_id' => 'department_id_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['department_id' => 'department_id_exists']
            ]
        ],
        'validator_age_id_exists' => [
            // Проверка наличия учебного периода по ID
            [
                'required_slots' => ['id' => 'age_id_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['id' => 'age_id_exists']
            ]
        ],
        'validator_programmitem_id_exists' => [
            // Проверка наличия учебного периода по ID
            [
                'required_slots' => ['id' => 'programmitem_id_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['id' => 'programmitem_id_exists']
            ]
        ],
        // Проверка расположения подразделения
        'validator_department_is_upward' => [
            [
                'required_slots' => [
                    'id' => 'departmentid_prepeared', 
                    'upperid' => 'age_department_id'
                ],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['trueresult' => 'age_department_rightpos']
            ],
            [
                'required_slots' => [
                    'id' => 'departmentid_prepeared',
                    'upperid' => 'programmitem_department_id'
                ],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['trueresult' => 'programmitem_department_rightpos']
            ]
        ],
        'validator_general_numeric_equal' => [
            [
                'required_slots' => [
                    'numeric1' => 'departmentid_prepeared',
                    'numeric2' => 'age_department_id'
                ],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['trueresult' => 'age_department_rightpos']
            ],
            [
                'required_slots' => [
                    'numeric1' => 'departmentid_prepeared',
                    'numeric2' => 'programmitem_department_id'
                ],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['trueresult' => 'programmitem_department_rightpos']
            ]
        ],
        // Валидатор присутствия назначения на должность
        'validator_appointment_id_exists' => [
            [
                'required_slots' => ['id' => 'teacher_appointemnt_id_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['id' => 'teacher_appointemnt_id_exists'],
            ]
        ],
        // Валидатор назначения на должность для указанной дисциплины
        'validator_appointment_id_teach_programmitem' => [
            [
                'required_slots' => [
                    'appointmentid' => 'teacher_appointemnt_id_exists',
                    'programmitemid' => 'programmitemid_prepeared',
                ],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['appointmentid' => 'appointmentid_prepeared']
            ]
        ],
        // Валидатор учебного процесса
        'validator_cstream_id_exists' => [
            [
                'required_slots' => ['id' => 'cstream_id_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['id' => 'cstream_id_exists']
            ]
        ]
    ];
    
    /**
     * Пул конвертеров
     *
     * @var array
     */
    protected $converters = [
        // Конвертер кода подразделения в идентификатор
        'converter_department_code_to_id' => [
            // Конвертация кода подразделения в ID
            [
                'required_slots' => ['code' => 'department_code_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['id' => 'department_id_exists']
            ]
        ],
        // Проверка наличия подразделения
        'converter_general_incomparestatic' => [
            // Текущее подразделение указано непосредственно в данных
            [
                'required_slots' => ['data' => 'department_id_exists'],
                'input_slots' => ['comparedata' => 'department_id_exists'],
                'static_slots' => ['comparevalue' => null],
                'output_slots' => ['data' => 'departmentid_prepeared']
            ],
            // Проверка наличия даты
            [
                'required_slots' => ['data' => 'cstream_begindate_converted_valid'],
                'input_slots' => ['comparedata' => 'cstream_begindate_converted_valid'],
                'static_slots' => ['comparevalue' => 0],
                'output_slots' => ['data' => 'cstream_begindate_prepeared']
            ],
            [
                'required_slots' => ['data' => 'cstream_enddate_converted_valid'],
                'input_slots' => ['comparedata' => 'cstream_enddate_converted_valid'],
                'static_slots' => ['comparevalue' => 0],
                'output_slots' => ['data' => 'cstream_enddate_prepeared']
            ]
        ],
        'converter_general_comparestatic' => [
            // Текущее подразделение устанавливается маской
            [
                'required_slots' => ['data' => '__departmentid'],
                'input_slots' => ['comparedata' => 'departmentid_prepeared'],
                'static_slots' => ['comparevalue' => null],
                'output_slots' => ['data' => 'departmentid_prepeared']
            ],
            [
                'required_slots' => ['data' => 'age_id_exists'],
                'input_slots' => ['comparedata' => 'age_department_rightpos'],
                'static_slots' => ['comparevalue' => true],
                'output_slots' => ['data' => 'ageid_prepeared']
            ],
            [
                'required_slots' => ['data' => 'programmitem_id_exists'],
                'input_slots' => ['comparedata' => 'programmitem_department_rightpos'],
                'static_slots' => ['comparevalue' => true],
                'output_slots' => ['data' => 'programmitemid_prepeared']
            ]
        ],
        'converter_programmitem_code_to_id' => [
            // Конвертация кода подразделения в ID
            [
                'required_slots' => ['code' => 'programmitem_code_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['id' => 'programmitem_id_exists']
            ]
        ],
        // Получение подразделения учебного периода
        'converter_age_department_id' => [
            [
                'required_slots' => ['id' => 'age_id_exists'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['id' => 'age_department_id']
            ]
        ],
        // Получение подразделения дисциплины
        'converter_programmitem_department_id' => [
            [
                'required_slots' => ['id' => 'programmitem_id_exists'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['id' => 'programmitem_department_id']
            ]
        ],
        'converter_appointment_email_to_id' => [
            [
                'required_slots' => [
                    'email' => 'teacher_email_valid',
                    'programmitemid' => 'programmitemid_prepeared'
                ],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['appointmentid' => 'appointmentid_prepeared']
            ]
        ],
        'converter_appointment_lastname_to_id' => [
            [
                'required_slots' => [
                    'lastname' => 'teacher_lastname_valid',
                    'programmitemid' => 'programmitemid_prepeared'
                ],
                'input_slots' => [
                    'firstname' => 'teacher_firstname_valid',
                    'middlename' => 'teacher_middlename_valid',
                ],
                'static_slots' => [],
                'output_slots' => ['appointmentid' => 'appointmentid_prepeared']
            ]
        ],
        // Конвертер начальной и конечной даты учебного периода
        'converter_general_date' => [
            [
                'required_slots' => ['date' => 'cstream_begindate_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['date' => 'cstream_begindate_converted_valid']
            ],
            [
                'required_slots' => ['date' => 'cstream_enddate_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['date' => 'cstream_enddate_converted_valid']
            ]
        ],
        // Разбиение поля на несколько полей
        'converter_general_exploder' => [
            [
                'required_slots' => [
                    'data' => 'cpassed_fullname_list_valid',
                    'delimiter' => 'cpassed_fullname_list_delimiter'
                ],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['explodeddata' => 'cpassed$1_fullname']
            ]
        ],
        // Разбиение ФИО
        'converter_person_fullname' => [
            [
                'required_slots' => [
                    'fullnameformat' => 'cpassed_fullname_list_fullnameformat_valid',
                    'fullname' => '/cpassed([0-9]*)_fullname/m'
                ],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => [
                    'firstname'=> 'cpassed$1_firstname', 
                    'lastname' => 'cpassed$1_lastname', 
                    'middlename' => 'cpassed$1_middlename'
                ]
            ]
        ],
        // Поиск подписки на программу для указанной персоны
        'converter_programmsbc_fullname_to_id' => [
            [
                'required_slots' => [
                    'lastname' => '/cpassed([0-9]*)_lastname/m',
                    'programmitemid' => 'programmitemid_prepeared'
                ],
                'input_slots' => [
                    'firstname' => 'cpassed$1_firstname_valid',
                    'middlename' => 'cpassed$1_middlename_valid',
                ],
                'static_slots' => [],
                'output_slots' => [
                    'id'=> 'programmsbc$1_id'
                ]
            ]
        ],
    ];

    /**
     * Пул импортеров
     *
     * @var array
     */
    protected $importers = [
        
        // Импорт учебного процесса
        'importer_cstreams_base' => [
            [
                'required_slots' => [
                    'programmitemid' => 'programmitemid_prepeared',
                    'ageid' => 'ageid_prepeared',
                    'appointmentid' => 'appointmentid_prepeared',
                    'departmentid' => 'departmentid_prepeared'
                ],
                'input_slots' => [
                    'id' => 'cstream_id_exists',
                    'name' => 'cstream_name_valid',
                    'description' => 'cstream_description_valid',
                    'begindate' => 'cstream_begindate_prepeared',
                    'enddate' => 'cstream_enddate_prepeared',
                    'hoursweek' => 'cstream_hoursweek_valid',
                    'simulation' => 'simulation'
                ],
                'static_slots' => [],
                'output_slots' => [
                    'id' => 'cstream_id_saved'
                ]
            ]
        ],
        'importer_cpassed_enrol' => [
            [
                'required_slots' => [
                    'programmsbcid' => '/programmsbc([0-9]*)_id/m',
                    'cstreamid' => 'cstream_id_saved'
                ],
                'input_slots' => [
                    'simulation' => 'simulation'
                ],
                'static_slots' => [],
                'output_slots' => [
                    'id' => 'cpassed$1_id_enrolled'
                ]
            ]
        ]
    ];
}

