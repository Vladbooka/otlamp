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
 * Обмен данных с внешними источниками. Класс стратегии импорта контингента
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_strategy_participants extends dof_modlib_transmit_strategy_base
{
    /**
     * Список полей, доступных для импорта
     * 
     * @var array
     */
    public static $importfields = [
        
        // данные пользователя
        'user_email' => ['type' => PARAM_EMAIL],
        'department_id' => ['type' => PARAM_RAW_TRIMMED],
        'department_code' => ['type' => PARAM_RAW_TRIMMED],
        'department_name' => ['type' => PARAM_RAW_TRIMMED],
        'user_country' => ['type' => PARAM_RAW_TRIMMED],
        'user_region' => ['type' => PARAM_RAW_TRIMMED],
        'user_city' => ['type' => PARAM_RAW_TRIMMED],
        'user_manager_email' => ['type' => PARAM_RAW_TRIMMED],
        'user_manager_idnumber' => ['type' => PARAM_RAW_TRIMMED],
        
        // данные для создания должностного назначения
        // генерация вакансии
        'schposition_generate' => ['type' => PARAM_BOOL, 'option' => true],
        // код должности
        'position_code' => ['type' => PARAM_RAW_TRIMMED],
        // название должности
        'position_name' => ['type' => PARAM_RAW_TRIMMED],
        
        // Данные по договору обучения
        'student_contract_num' => ['type' => PARAM_RAW_TRIMMED],
        'student_contract_activate' => ['type' => PARAM_BOOL],
        'student_contract_date' => ['type' => PARAM_RAW_TRIMMED],
        'student_contract_notice' => ['type' => PARAM_RAW_TRIMMED],
        // Генерировать номера договоров
        'student_contract_num_generate' => ['type' => PARAM_BOOL, 'option' => true],
        
        // Данные студента по договору обучения
        'student_email' => ['type' => PARAM_EMAIL],
        'student_fullname' => ['type' => PARAM_RAW_TRIMMED],
        'student_firstname' => ['type' => PARAM_RAW_TRIMMED],
        'student_lastname' => ['type' => PARAM_RAW_TRIMMED],
        'student_middlename' => ['type' => PARAM_RAW_TRIMMED],
        'student_dateofbirth' => ['type' => PARAM_RAW_TRIMMED],
        'student_gender' => ['type' => PARAM_RAW_TRIMMED],
        'student_phonecell' => ['type' => PARAM_RAW_TRIMMED],
        // Дополнительные поля студента
        '/customfield_([0-9a-zA-Z]*)/m' => ['type' => PARAM_RAW_TRIMMED, 'displayedfieldcode' => 'customfield_[code]'],
        // Настройка "Создавать новую персону, если в системе найдена персона с аналогичным ФИО"
        'student_doublepersonfullname' => ['type' => PARAM_BOOL, 'option' => true],
        // Шаблон email для генерации, если текущий email не указан
        'student_email_generate' => ['type' => PARAM_EMAIL, 'option' => true],
        // Формат поля ФИО для разбиения на Фамилию, имя и отчество
        'student_formatfullname' => ['type' => PARAM_RAW_TRIMMED, 'option' => true],
        'student_password' => ['type' => PARAM_RAW_TRIMMED],
        'student_passwordformat' => ['type' => PARAM_RAW_TRIMMED],
        'student_department_code' => ['type' => PARAM_RAW_TRIMMED],
        'student_sync2moodle' => ['type' => PARAM_BOOL],
        'student_extid' => ['type' => PARAM_RAW_TRIMMED],
        'student_username' => ['type' => PARAM_RAW_TRIMMED],
        'student_department_code_default' => ['type' => PARAM_RAW_TRIMMED, 'option' => true],
        'student_passwordformat_default' => ['type' => PARAM_RAW_TRIMMED, 'option' => true],
        'student_sync2moodle_default' => ['type' => PARAM_BOOL, 'option' => true],
        
        // Данные законного представителя по договору обучения
        'parent_email' => ['type' => PARAM_EMAIL],
        'parent_fullname' => ['type' => PARAM_RAW_TRIMMED],
        'parent_firstname' => ['type' => PARAM_RAW_TRIMMED],
        'parent_lastname' => ['type' => PARAM_RAW_TRIMMED],
        'parent_middlename' => ['type' => PARAM_RAW_TRIMMED],
        'parent_dateofbirth' => ['type' => PARAM_RAW_TRIMMED],
        'parent_gender' => ['type' => PARAM_RAW_TRIMMED],
        // Настройка "Создавать новую персону, если в системе найдена персона с аналогичным ФИО"
        'parent_doublepersonfullname' => ['type' => PARAM_BOOL, 'option' => true],
        // Шаблон email для генерации, если текущий email не указан
        'parent_email_generate' => ['type' => PARAM_EMAIL, 'option' => true],
        // Формат поля ФИО для разбиения на Фамилию, имя и отчество
        'parent_formatfullname' => ['type' => PARAM_RAW_TRIMMED, 'option' => true],

        // Данные куратора по договору обучения
        'curator_email' => ['type' => PARAM_EMAIL],
        'curator_fullname' => ['type' => PARAM_RAW_TRIMMED],
        'curator_firstname' => ['type' => PARAM_RAW_TRIMMED],
        'curator_lastname' => ['type' => PARAM_RAW_TRIMMED],
        'curator_middlename' => ['type' => PARAM_RAW_TRIMMED],
        'curator_dateofbirth' => ['type' => PARAM_RAW_TRIMMED],
        'curator_gender' => ['type' => PARAM_RAW_TRIMMED],
        // Настройка "Создавать новую персону, если в системе найдена персона с аналогичным ФИО"
        'curator_doublepersonfullname' => ['type' => PARAM_BOOL, 'option' => true],
        // Шаблон email для генерации, если текущий email не указан
        'curator_email_generate' => ['type' => PARAM_EMAIL, 'option' => true],
        // Формат поля ФИО для разбиения на Фамилию, имя и отчество
        'curator_formatfullname' => ['type' => PARAM_RAW_TRIMMED, 'option' => true],
        
        // Данные менеджера по договору обучения
        'seller_email' => ['type' => PARAM_EMAIL],
        'seller_fullname' => ['type' => PARAM_RAW_TRIMMED],
        'seller_firstname' => ['type' => PARAM_RAW_TRIMMED],
        'seller_lastname' => ['type' => PARAM_RAW_TRIMMED],
        'seller_middlename' => ['type' => PARAM_RAW_TRIMMED],
        'seller_dateofbirth' => ['type' => PARAM_RAW_TRIMMED],
        'seller_gender' => ['type' => PARAM_RAW_TRIMMED],
        // Настройка "Создавать новую персону, если в системе найдена персона с аналогичным ФИО"
        'seller_doublepersonfullname' => ['type' => PARAM_BOOL, 'option' => true],
        // Шаблон email для генерации, если текущий email не указан
        'seller_email_generate' => ['type' => PARAM_EMAIL, 'option' => true],
        // Формат поля ФИО для разбиения на Фамилию, имя и отчество
        'seller_formatfullname' => ['type' => PARAM_RAW_TRIMMED, 'option' => true]
    ];
    
    /**
     * Список полей, доступных для экспорта
     *
     * @var array
     */
    public static $exportfields = [

        // Экспорт персон
        'student_id' => ['type' => PARAM_INT],
        'student_email' => ['type' => PARAM_EMAIL],
        'student_firstname' => ['type' => PARAM_RAW_TRIMMED],
        'student_lastname' => ['type' => PARAM_RAW_TRIMMED],
        'student_middlename' => ['type' => PARAM_RAW_TRIMMED],
        'student_dateofbirth' => ['type' => PARAM_RAW_TRIMMED],
        'student_gender' => ['type' => PARAM_RAW_TRIMMED],
        'student_mdluser' => ['type' => PARAM_INT],
        'student_departmentid' => ['type' => PARAM_INT]
    ];
    
    /**
     * Пул валидаторов
     *
     * @var array
     */
    protected $validators = [
        // Базовый валидатор чисел
        'validator_general_numeric' => [
            [
                'required_slots' => ['numeric' => 'student_person_id_saved'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['numeric' => 'student_person_id_valid']
            ],
            [
                'required_slots' => ['numeric' => 'parent_person_id_saved'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['numeric' => 'parent_person_id_valid']
            ],
            [
                'required_slots' => ['numeric' => 'seller_person_id_saved'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['numeric' => 'seller_person_id_valid']
            ],
            [
                'required_slots' => ['numeric' => 'curator_person_id_saved'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['numeric' => 'curator_person_id_valid']
            ],
            [
                'required_slots' => ['numeric' => 'department_id'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['numeric' => 'department_id_generalvalid']
            ]
        ],
        // Валидатор ID персоны
        'validator_person_id_exists' => [
            [
                'required_slots' => ['personid' => 'student_person_id'], 
                'input_slots' => [], 
                'static_slots' => [],
                'output_slots' => ['personid' => 'student_person_id_valid']
            ],
            [
                'required_slots' => ['personid' => 'parent_person_id'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['personid' => 'parent_person_id_valid']
            ],
            [
                'required_slots' => ['personid' => 'seller_person_id'], 
                'input_slots' => [], 
                'static_slots' => [],
                'output_slots' => ['personid' => 'seller_person_id_valid']
            ],
            [
                'required_slots' => ['personid' => 'curator_person_id'], 
                'input_slots' => [], 
                'static_slots' => [],
                'output_slots' => ['personid' => 'curator_person_id_valid']
            ]
        ],
        // Базовый валидатор Email
        'validator_general_email' => [
            [
                'required_slots' => ['email' => 'student_email'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['email' => 'student_email_valid'],
            ],
            [
                'required_slots' => ['email' => 'student_email_generate'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['email' => 'student_email_generate_valid']
            ],
            [
                'required_slots' => ['email' => 'parent_email'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['email' => 'parent_email_valid']
            ],
            [
                'required_slots' => ['email' => 'parent_email_generate'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['email' => 'parent_email_generate_valid']
            ],
            [
                'required_slots' => ['email' => 'seller_email'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['email' => 'seller_email_valid']
            ],
            [
                'required_slots' => ['email' => 'seller_email_generate'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['email' => 'seller_email_generate_valid'] 
            ],
            [
                'required_slots' => ['email' => 'curator_email'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['email' => 'curator_email_valid']
            ],
            [
                'required_slots' => ['email' => 'curator_email_generate'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['email' => 'curator_email_generate_valid']
            ],
            [
                'required_slots' => ['email' => 'user_email'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['email' => 'user_email_generalvalid']
            ],
            [
                'required_slots' => ['email' => 'user_manager_email'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['email' => 'user_manager_email_generalvalid']
            ]
        ],
        // Валидатор конфигутации формата ФИО
        'validator_person_fullnameformat' => [
            [
                'required_slots' => ['fullnameformat' => 'student_formatfullname'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['fullnameformat' => 'student_formatfullname_valid']
            ],
            [
                'required_slots' => ['fullnameformat' => 'parent_formatfullname'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['fullnameformat' => 'parent_formatfullname_valid']
            ],
            [
                'required_slots' => ['fullnameformat' => 'seller_formatfullname'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['fullnameformat' => 'seller_formatfullname_valid']
            ],
            [
                'required_slots' => ['fullnameformat' => 'curator_formatfullname'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['fullnameformat' => 'curator_formatfullname_valid']
            ]
        ],
        'validator_person_passwordformat' => [
            [
                'required_slots' => ['passwordformat' => 'student_passwordformat'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['passwordformat' => 'student_passwordformat_valid']
            ],
            [
                'required_slots' => ['passwordformat' => 'student_passwordformat_default'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['passwordformat' => 'student_passwordformat_default_valid']
            ],
        ],
        // Валидатор не-пустой строки
        'validator_general_string' => [
            [
                'required_slots' => ['string' => 'student_fullname'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'student_fullname_valid']
            ],
            [
                'required_slots' => ['string' => 'parent_fullname'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'parent_fullname_valid']
            ],
            [
                'required_slots' => ['string' => 'seller_fullname'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'seller_fullname_valid']
            ],
            [
                'required_slots' => ['string' => 'curator_fullname'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'curator_fullname_valid']
            ],
            [
                'required_slots' => ['string' => 'student_firstname'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'student_firstname_valid']
            ],
            [
                'required_slots' => ['string' => 'student_contract_num'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'student_contract_num_pre_valid']
            ],
            [
                'required_slots' => ['string' => 'parent_firstname'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'parent_firstname_valid']
            ],
            [
                'required_slots' => ['string' => 'seller_firstname'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'seller_firstname_valid']
            ],
            [
                'required_slots' => ['string' => 'curator_firstname'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'curator_firstname_valid']
            ],
            [
                'required_slots' => ['string' => 'student_lastname'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'student_lastname_valid']
            ],
            [
                'required_slots' => ['string' => 'parent_lastname'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'parent_lastname_valid']
            ],
            [
                'required_slots' => ['string' => 'seller_lastname'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'seller_lastname_valid']
            ],
            [
                'required_slots' => ['string' => 'curator_lastname'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'curator_lastname_valid']
            ],
            [
                'required_slots' => ['string' => 'student_middlename'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'student_middlename_valid']
            ],
            [
                'required_slots' => ['string' => 'parent_middlename'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'parent_middlename_valid']
            ],
            [
                'required_slots' => ['string' => 'seller_middlename'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'seller_middlename_valid']
            ],
            [
                'required_slots' => ['string' => 'curator_middlename'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'curator_middlename_valid']
            ],
            [
                'required_slots' => ['string' => 'student_contract_notice'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'student_contract_notice_valid']
            ],
            [
                'required_slots' => ['string' => '/customfield_([0-9a-zA-Z]*)/m'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'customfield_generalvalid_$1']
            ],
            [
                'required_slots' => ['string' => 'user_country'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'user_country_valid']
            ],
            [
                'required_slots' => ['string' => 'user_region'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'user_region_valid']
            ],
            [
                'required_slots' => ['string' => 'user_city'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'user_city_valid']
            ],
            [
                'required_slots' => ['string' => 'department_name'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'department_name_generalvalid']
            ],
            [
                'required_slots' => ['string' => 'department_code'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'department_code_generalvalid']
            ],
            [
                'required_slots' => ['string' => 'position_code'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'position_code_generalvalid']
            ],
            [
                'required_slots' => ['string' => 'position_name'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'position_name_generalvalid']
            ],
            [
                'required_slots' => ['string' => 'user_manager_idnumber'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'user_manager_idnumber_generalvalid']
            ],
            [
                'required_slots' => ['string' => 'student_password'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'student_password_valid']
            ],
            [
                'required_slots' => ['string' => 'student_extid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'student_extid_generalvalid']
            ],
        ],
        // Валидатор булевой переменной
        'validator_general_bool' => [
            [
                'required_slots' => ['bool' => 'student_doublepersonfullname'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['bool' => 'student_doublepersonfullname_valid']
            ],
            [
                'required_slots' => ['bool' => 'parent_doublepersonfullname'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['bool' => 'parent_doublepersonfullname_valid']
            ],
            [
                'required_slots' => ['bool' => 'seller_doublepersonfullname'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['bool' => 'seller_doublepersonfullname_valid']
            ],
            [
                'required_slots' => ['bool' => 'curator_doublepersonfullname'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['bool' => 'curator_doublepersonfullname_valid']
            ],
            [
                'required_slots' => ['bool' => 'student_contract_num_generate'], 
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['bool' => 'student_contract_num_generate_valid']
            ],
            [
                'required_slots' => ['bool' => 'schposition_generate'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['bool' => 'schposition_generate_valid']
            ],
            [
                'required_slots' => ['bool' => 'student_contract_activate'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['bool' => 'student_contract_activate_valid']
            ],
            [
                'required_slots' => ['bool' => 'student_sync2moodle'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['bool' => 'student_sync2moodle_valid']
            ],
            [
                'required_slots' => ['bool' => 'student_sync2moodle_default'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['bool' => 'student_sync2moodle_default_valid']
            ],
        ],
        // Валидатор булевой переменной
        'validator_general_phone' => [
            [
                'required_slots' => ['phone' => 'student_phonecell'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['phone' => 'student_phonecell_valid']
            ],
        ],
        // Валидатор пола
        'validator_person_gender' => [
            [
                'required_slots' => ['gender' => 'student_gender'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['gender' => 'student_gender_valid']
            ],
            [
                'required_slots' => ['gender' => 'parent_gender'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['gender' => 'parent_gender_valid']
            ],
            [
                'required_slots' => ['gender' => 'seller_gender'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['gender' => 'seller_gender_valid']
            ],
            [
                'required_slots' => ['gender' => 'curator_gender'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['gender' => 'curator_gender_valid']
            ]
        ],
        // Валидатор логина пользователя
        'validator_general_username' => [
            [
                'required_slots' => ['username' => 'student_username'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['username' => 'student_username_valid']
            ],
        ],
        // Существование договора на обучение
        'validator_contract_id_exists' => [
            [
                'required_slots' => ['contractid' => 'student_contract_id'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['contractid' => 'student_contract_id_valid']
            ]
        ],
        // Валидатор даты
        'validator_general_date' => [
            [
                'required_slots' => ['date' => 'student_contract_date'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['date' => 'student_contract_date_valid']
            ],
            [
                'required_slots' => ['date' => 'student_dateofbirth'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['date' => 'student_dateofbirth_valid']
            ],
            [
                'required_slots' => ['date' => 'parent_dateofbirth'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['date' => 'parent_dateofbirth_valid']
            ],
            [
                'required_slots' => ['date' => 'seller_dateofbirth'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['date' => 'seller_dateofbirth_valid']
            ],
            [
                'required_slots' => ['date' => 'curator_dateofbirth'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['date' => 'curator_dateofbirth_valid']
            ]
        ],
        // Существование подразделения
        'validator_department_id_exists' => [
            [
                'required_slots' => ['department_id' => '__departmentid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['department_id' => '__departmentid_valid']
            ],
        ],
        'validator_department_code' => [
            [
                'required_slots' => ['department_code' => 'student_department_code'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['department_code' => 'department_code_generalvalid']
            ],
            [
                'required_slots' => ['department_code' => 'student_department_code_default'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['department_code' => 'department_code_default_generalvalid']
            ],
        ],
        // Существование дополнительного поля
        'validator_customfield_fields_exist' => [
            [
                'required_slots' => [
                    'departmentid' => '__departmentid_valid', 
                    'customfieldvalue' => '/customfield_generalvalid_([0-9a-zA-Z]*)/m'
                ],
                'input_slots' => [
                    'customfieldcode' => '$1'
                ],
                'static_slots' => [],
                'output_slots' => ['customfieldcode' => 'customfield_generalvalid_$1_exist']
            ]
        ],
        // Валидатор идентификатора подразделения
        'validator_department_id_exists' => [
            // Проверка наличия подразделения по ID
            [
                'required_slots' => ['department_id' => 'department_id_generalvalid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['department_id' => 'department_id_valid']
            ]
        ]
    ];
    
    /**
     * Пул конвертеров
     *
     * @var array
     */
    protected $converters = [
        // Конвертор даты в TIMESTAMP
        'converter_general_date' => [
            [
                'required_slots' => ['date' => 'student_contract_date_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['date' => 'student_contract_date_converted_valid']
            ],
            [
                'required_slots' => ['date' => 'student_dateofbirth_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['date' => 'student_dateofbirth_converted_valid']
            ],
            [
                'required_slots' => ['date' => 'parent_dateofbirth_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['date' => 'parent_dateofbirth_converted_valid']
            ],
            [
                'required_slots' => ['date' => 'seller_dateofbirth_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['date' => 'seller_dateofbirth_converted_valid']
            ],
            [
                'required_slots' => ['date' => 'curator_dateofbirth_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['date' => 'curator_dateofbirth_converted_valid']
            ]
        ],
        'converter_general_bool_to_int' => [
            [
                'required_slots' => ['bool' => 'student_sync2moodle_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['int' => 'student_sync2moodle_converted_valid']
            ],
            [
                'required_slots' => ['bool' => 'student_sync2moodle_default_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['int' => 'student_sync2moodle_converted_valid']
            ],
        ],
        'converter_general_string' => [
            [
                'required_slots' => ['string' => 'student_passwordformat_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'student_passwordformat_converted_valid']
            ],
            [
                'required_slots' => ['string' => 'student_passwordformat_default_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'student_passwordformat_converted_valid']
            ],
        ],
        // Конвертор пола в стандартный вид
        'converter_person_gender' => [
            [
                'required_slots' => ['gender' => 'student_gender_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['gender' => 'student_gender_converted_valid']
            ],
            [
                'required_slots' => ['gender' => 'parent_gender_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['gender' => 'parent_gender_converted_valid']
            ],
            [
                'required_slots' => ['gender' => 'seller_gender_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['gender' => 'seller_gender_converted_valid']
            ],
            [
                'required_slots' => ['gender' => 'curator_gender_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['gender' => 'curator_gender_converted_valid']
            ]
        ],
        // Конвертор идентификатора персоны в EMAIL
        'converter_person_id' => [
            [
                'required_slots' => ['personid' => 'student_person_id_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['email' => 'student_email_valid']
            ],
            [
                'required_slots' => ['personid' => 'parent_person_id_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['email' => 'parent_email_valid']
            ],
            [
                'required_slots' => ['personid' => 'seller_person_id_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['email' => 'seller_email_valid']
            ],
            [
                'required_slots' => ['personid' => 'curator_person_id_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['email' => 'curator_email_valid']
            ]
        ],
        // конвертер внешнего идентификатора (extid) в идентификатор персоны
        'converter_person_extid_to_id' => [
            [
                'required_slots' => ['extid' => 'student_extid_generalvalid'],
                'input_slots' => ['simulation' => 'simulation'],
                'static_slots' => [],
                'output_slots' => ['personid' => 'student_person_id_valid']
            ]
        ],
        // Конвертор EMAIL персоны в идентификатор
        'converter_person_email' => [
            [
                'required_slots' => ['email' => 'student_email_valid'],
                'input_slots' => ['simulation' => 'simulation'],
                'static_slots' => [],
                'output_slots' => ['personid' => 'student_person_id_valid']
            ],
            [
                'required_slots' => ['email' => 'parent_email_valid'],
                'input_slots' => ['simulation' => 'simulation'],
                'static_slots' => [],
                'output_slots' => ['personid' => 'parent_person_id_valid']
            ],
            [
                'required_slots' => ['email' => 'seller_email_valid'],
                'input_slots' => ['simulation' => 'simulation'],
                'static_slots' => [],
                'output_slots' => ['personid' => 'seller_person_id_valid']
            ],
            [
                'required_slots' => ['email' => 'curator_email_valid'],
                'input_slots' => ['simulation' => 'simulation'],
                'static_slots' => [],
                'output_slots' => ['personid' => 'curator_person_id_valid']
            ],
            // конвертация Email пользователя в ID персоны
            [
                'required_slots' => ['email' => 'user_email_generalvalid'],
                'input_slots' => [
                    'departmentid' => 'department_id_valid',
                    'simulation' => 'simulation'
                ],
                'static_slots' => [
                    'trycreatefrommoodle' => true
                ],
                'output_slots' => [
                    'personid' => 'person_id_valid', 
                    'sync' => 'user_to_person_synced'
                ]
            ],
            // конвертация Email руководителя в ID персоны
            [
                'required_slots' => ['email' => 'user_manager_email_generalvalid'],
                'input_slots' => ['simulation' => 'simulation'],
                'static_slots' => [],
                'output_slots' => ['personid' => 'person_manager_id_valid']
            ]
        ],
        // конвертер индивидуального номера (idnumber) пользователя Moodle в идентификатор персоны
        'converter_person_moodle_idnumber_to_id' => [
            [
                'required_slots' => ['idnumber' => 'user_manager_idnumber_generalvalid'],
                'input_slots' => ['simulation' => 'simulation'],
                'static_slots' => [],
                'output_slots' => ['personid' => 'person_manager_id_valid']
            ]
        ],
        // Генератор EMAIL персоны
        'converter_person_generate_email' => [
            [
                'required_slots' => ['email' => 'student_email_generate_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['email' => 'student_email_valid']
            ],
            [
                'required_slots' => ['email' => 'parent_email_generate_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['email' => 'parent_email_valid']
            ],
            [
                'required_slots' => ['email' => 'seller_email_generate_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['email' => 'seller_email_valid']
            ],
            [
                'required_slots' => ['email' => 'curator_email_generate_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['email' => 'curator_email_valid']
            ]
        ],
        // Конвертер полного имени персоны в Фамилию, Имя и Отчество
        'converter_person_fullname' => [
            [
                'required_slots' => ['fullname' => 'student_fullname_valid', 'fullnameformat' => 'student_formatfullname_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['firstname' => 'student_firstname_valid', 'lastname' => 'student_lastname_valid', 'middlename' => 'student_middlename_valid']
            ],
            [
                'required_slots' => ['fullname' => 'parent_fullname_valid', 'fullnameformat' => 'parent_formatfullname_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['firstname' => 'parent_firstname_valid', 'lastname' => 'parent_lastname_valid', 'middlename' => 'parent_middlename_valid']
            ],
            [
                'required_slots' => ['fullname' => 'seller_fullname_valid', 'fullnameformat' => 'seller_formatfullname_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['firstname' => 'seller_firstname_valid', 'lastname' => 'seller_lastname_valid', 'middlename' => 'seller_middlename_valid']
            ],
            [
                'required_slots' => ['fullname' => 'curator_fullname_valid', 'fullnameformat' => 'curator_formatfullname_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['firstname' => 'curator_firstname_valid', 'lastname' => 'curator_lastname_valid', 'middlename' => 'curator_middlename_valid']
            ]
        ],
        // Конвертер пароля в хеш пароля
        'converter_person_password' => [
            [
                'required_slots' => ['password' => 'student_password_valid', 'passwordformat' => 'student_passwordformat_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['passwordmd5' => 'student_password_converted_valid']
            ],
            [
                'required_slots' => ['password' => 'student_password_valid', 'passwordformat' => 'student_passwordformat_default_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['passwordmd5' => 'student_password_converted_valid']
            ]
        ],
        // Конвертер идентификатора договора на обучение в номер 
        'converter_contract_id' => [
            [
                'required_slots' => ['contractid' => 'student_contract_id_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['contract_num' => 'student_contract_num_valid']
            ]
        ],
        // Конвертер номера договора на обучение в идентификатор
        'converter_contract_num_with_macrosubstitutions_to_num' => [
            [
                'required_slots' => [
                    'num' => 'student_contract_num_pre_valid',
                    'student_id' => 'student_person_id_valid'
                ],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['num' => 'student_contract_num_valid']
            ]
        ],
        // Конвертер номера договора на обучение в идентификатор 
        'converter_contract_num' => [
            [
                'required_slots' => ['num' => 'student_contract_num_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['contractid' => 'student_contract_id_valid']
            ]
        ],
        // Генератор номера договора
        'converter_contract_num_generate' => [
            [
                'required_slots' => ['num' => 'student_contract_num_generate_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['num' => 'student_contract_num_valid']
            ]
        ],
        // Конвертер кода дополнительного поля в ID
        'converter_customfield_code_to_id' =>
        [
            [
                'required_slots' => [
                    'departmentid' => '__departmentid_valid',
                    'customfieldvalue' => '/customfield_generalvalid_([0-9a-zA-Z]*)_exist/m'
                ],
                'static_slots' => [
                    'code' => '$1'
                ],
                'input_slots' => [],
                'output_slots' => [
                    'id' => 'customfield_$1_id_valid'
                ]
            ]
        ], 
        // Конвертер кода дополнительного поля в тип
        'converter_customfield_code_to_type' =>
        [
            [
                'required_slots' => [
                    'departmentid' => '__departmentid_valid',
                    'customfieldvalue' => '/customfield_generalvalid_([0-9a-zA-Z]*)_exist/m'
                ],
                'static_slots' => [
                    'code' => '$1'
                ],
                'input_slots' => [],
                'output_slots' => [
                    'type' => 'customfield_$1_type_valid'
                ]
            ]
        ], 
        // Подготовка значения дополнительного поля
        'converter_customfield_prepearedvalue' =>
        [
            [
                'required_slots' => [
                    'id' => '/customfield_([0-9a-zA-Z]*)_id_valid/m',
                    'value' => 'customfield_generalvalid_$1_exist'
                ],
                'input_slots' => [
                    'currentdepartmentid' => '__departmentid_valid',
                ],
                'static_slots' => [],
                'output_slots' => [
                    'prepearedvalue' => 'customfield_$1_prepearedvalue'
                ]
            ]
        ],
        // Конвертер кода подразделения в идентификатор
        'converter_department_code_to_id' => [
            // Конвертация кода подразделения в ID
            [
                'required_slots' => ['code' => 'department_code_generalvalid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['id' => 'department_id_valid']
            ],
            // Конвертация кода подразделения по умолчанию в ID
            [
                'required_slots' => ['code' => 'department_code_default_generalvalid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['id' => 'department_id_valid']
            ]
        ],
        'converter_department_id_to_code' => [
            // Конвертация ID подразделения в код
            [
                'required_slots' => ['id' => 'department_id_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['code' => 'department_code_valid']
            ]
        ],
        // конвертер названия подразделения в идентификатор
        'converter_department_name_to_id' => [
            [
                'required_slots' => [
                    'depname' => 'department_name_generalvalid'
                ],
                'input_slots' => [
                    'country' => 'user_country_valid',
                    'region' => 'user_region_valid',
                    'city' => 'user_city_valid',
                    'managerid' => 'person_manager_id_valid',
                    'personid' => 'person_id_valid'
                ],
                'static_slots' => ['returndefaultifnotfound' => true],
                'output_slots' => ['id' => 'department_id_valid']
            ]
        ],
        // конвертер кода должности в идентификатор
        'converter_position_code_to_id' => [
            [
                'required_slots' => ['code' => 'position_code_generalvalid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['id' => 'position_id_valid']
            ]
        ],
        // конвертер названия должности в идентификатор
        'converter_position_name_to_id' => [
            [
                'required_slots' => ['posname' => 'position_name_generalvalid'],
                'input_slots' => [
                    'departmentid' => 'department_id_valid'
                ],
                'static_slots' => [],
                'output_slots' => ['id' => 'position_id_valid']
            ]
        ],
    ];

    /**
     * Пул импортеров
     *
     * @var array
     */
    protected $importers = [
        // Импортер персон
        'importer_persons_base' => [
            [
                'required_slots' => [
                    'email' => 'student_email_valid'
                ],
                'static_slots' => [],
                'input_slots' => [
                    'personid' => 'student_person_id_valid',
                    'firstname' => 'student_firstname_valid',
                    'lastname' => 'student_lastname_valid',
                    'middlename' => 'student_middlename_valid',
                    'dateofbirth' => 'student_dateofbirth_converted_valid',
                    'gender' => 'student_gender_converted_valid',
                    'phonecell' => 'student_phonecell_valid',
                    'doublepersonfullname' => 'student_doublepersonfullname_valid',
                    'simulation' => 'simulation',
                    'password' => 'student_password_valid',
                    'passwordformat' => 'student_passwordformat_converted_valid',
                    'passwordmd5' => 'student_password_converted_valid',
                    'departmentid' => 'department_id_valid',
                    'sync2moodle' => 'student_sync2moodle_converted_valid',
                    'extid' => 'student_extid_generalvalid',
                    'username' => 'student_username_valid'
                ],
                'output_slots' => [
                    'personid' => 'student_person_id_saved'
                ],
            ],
            [
                'required_slots' => [
                    'email' => 'parent_email_valid'
                ],
                'static_slots' => [],
                'input_slots' => [
                    'personid' => 'parent_person_id_valid',
                    'firstname' => 'parent_firstname_valid',
                    'lastname' => 'parent_lastname_valid',
                    'middlename' => 'parent_middlename_valid',
                    'dateofbirth' => 'parent_dateofbirth_converted_valid',
                    'gender' => 'parent_gender_converted_valid',
                    'doublepersonfullname' => 'parent_doublepersonfullname_valid',
                    'simulation' => 'simulation'
                ],
                'output_slots' => [
                    'personid' => 'parent_person_id_saved'
                ],
                
            ],
            [
                'required_slots' => [
                    'email' => 'seller_email_valid'
                ],
                'static_slots' => [],
                'input_slots' => [
                    'personid' => 'seller_person_id_valid',
                    'firstname' => 'seller_firstname_valid',
                    'lastname' => 'seller_lastname_valid',
                    'middlename' => 'seller_middlename_valid',
                    'dateofbirth' => 'seller_dateofbirth_converted_valid',
                    'gender' => 'seller_gender_converted_valid',
                    'doublepersonfullname' => 'seller_doublepersonfullname_valid',
                    'simulation' => 'simulation'
                ],
                'output_slots' => [
                    'personid' => 'seller_person_id_saved'
                ],
                
            ],
            [
                'required_slots' => [
                    'email' => 'curator_email_valid'
                ],
                'static_slots' => [],
                'input_slots' => [
                    'personid' => 'curator_person_id_valid',
                    'firstname' => 'curator_firstname_valid',
                    'lastname' => 'curator_lastname_valid',
                    'middlename' => 'curator_middlename_valid',
                    'dateofbirth' => 'curator_dateofbirth_converted_valid',
                    'gender' => 'curator_gender_converted_valid',
                    'doublepersonfullname' => 'curator_doublepersonfullname_valid',
                    'simulation' => 'simulation'
                ],
                'output_slots' => [
                    'personid' => 'curator_person_id_saved'
                ],
            ],
            [
                'required_slots' => [
                    'email' => 'user_email_generalvalid'
                ],
                'static_slots' => [
                    'onlyupdate' => true
                ],
                'input_slots' => [
                    'personid' => 'person_id_valid',
                    'departmentid' => 'department_id_valid',
                    'usersycnednow' => 'user_to_person_synced',
                ],
                'output_slots' => [
                    'personid' => 'person_id_saved'
                ]
            ]
        ],
        // Импортер договора на обучение
        'importer_contracts_base' => [
            [
                'required_slots' => [
                    'num' => 'student_contract_num_valid',
                    'personid' => 'student_person_id_valid'
                ],
                'static_slots' => [],
                'input_slots' => [
                    'contractid' => 'student_contract_id_valid',
                    'activate_contract' => 'student_contract_activate_valid',
                    'parentid' => 'parent_person_id_valid',
                    'sellerid' => 'seller_person_id_valid',
                    'curatorid' => 'curator_person_id_valid',
                    'startdate' => 'student_contract_date_converted_valid',
                    'notice' => 'student_contract_notice_valid'
                ],
                'output_slots' => [
                    'contractid' => 'student_contract_id_saved'
                ]
            ]
        ],
        // Импортер дополнительных полей персоны
        'importer_customfields_base' => [
            [
                'required_slots' => [
                    'customfieldvalue' => '/customfield_([0-9a-zA-Z]*)_prepearedvalue/m',
                    'customfieldid' => 'customfield_$1_id_valid',
                    'objectid' => 'student_person_id_valid'
                ],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['customfieldvaluesaved' => 'customfield_$1_valuesaved']
            ]
        ],
        // импортер договора
        'importer_eagreements_base' => [
            [
                'required_slots' => [
                    'personid' => 'person_id_valid',
                    'departmentid' => 'department_id_valid'
                ],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['id' => 'eagreement_id_valid']
            ]
        ],
        // импортер вакансии
        'importer_schpositions_base' => [
            [
                'required_slots' => [
                    'positionid' => 'position_id_valid',
                    'departmentid' => 'department_id_valid'
                ],
                'input_slots' => [
                    'generate' => 'schposition_generate_valid',
                ],
                'static_slots' => [],
                'output_slots' => ['id' => 'schposition_id_valid']
            ]
        ],
        // импортер должностного назначения
        'importer_appointments_base' => [
            [
                'required_slots' => [
                    'personid' => 'person_id_valid',
                ],
                'input_slots' => [
                    'positionid' => 'position_id_valid',
                    'eagreementid' => 'eagreement_id_valid',
                    'schpositionid' => 'schposition_id_valid',
                    'departmentid' => 'department_id_valid',
                    'managerid' => 'person_manager_id_valid',
                    'sync_downid' => '__main_sync_downid'
                ],
                'static_slots' => [],
                'output_slots' => [
                    'id' => 'appointment_id_valid',
                    'sync_downid' => '__main_sync_downid_processed',
                    'generate_schposition' => 'schposition_generate_valid'
                ]
            ]
        ]
    ];

    /**
     * Пул экспортеров
     *
     * @var array
     */
    protected $exporters = [
        
        // Экспортер персон по идентификатору персоны
        'exporter_persons_data_by_id' => [
            [
                'required_slots' => [
                    'personid' => 'student_person_id_valid',
                ],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => [
                    'id' => 'student_id',
                    'email' => 'student_email',
                    'firstname' => 'student_firstname',
                    'lastname' => 'student_lastname',
                    'middlename' => 'student_middlename',
                    'dateofbirth' => 'student_dateofbirth',
                    'gender' => 'student_gender',
                    'mdluser' => 'student_mdluser',
                    'departmentid' => 'student_departmentid'
                ]
            ],
            [
                'required_slots' => [
                    'personid' => 'parent_person_id_valid',
                ],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => [
                    'id' => 'parent_id',
                    'email' => 'parent_email',
                    'firstname' => 'parent_firstname',
                    'lastname' => 'parent_lastname',
                    'middlename' => 'parent_middlename',
                    'dateofbirth' => 'parent_dateofbirth',
                    'gender' => 'parent_gender',
                    'mdluser' => 'parent_mdluser',
                    'departmentid' => 'parent_departmentid'
                ]
            ],
            [
                'required_slots' => [
                    'personid' => 'manager_person_id_valid',
                ],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => [
                    'id' => 'manager_id',
                    'email' => 'manager_email',
                    'firstname' => 'manager_firstname',
                    'lastname' => 'manager_lastname',
                    'middlename' => 'manager_middlename',
                    'dateofbirth' => 'manager_dateofbirth',
                    'gender' => 'manager_gender',
                    'mdluser' => 'manager_mdluser',
                    'departmentid' => 'manager_departmentid'
                ]
            ],
            [
                'required_slots' => [
                    'personid' => 'curator_person_id_valid',
                ],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => [
                    'id' => 'curator_id',
                    'email' => 'curator_email',
                    'firstname' => 'curator_firstname',
                    'lastname' => 'curator_lastname',
                    'middlename' => 'curator_middlename',
                    'dateofbirth' => 'curator_dateofbirth',
                    'gender' => 'curator_gender',
                    'mdluser' => 'curator_mdluser',
                    'departmentid' => 'curator_departmentid'
                ]
            ]
        ]
    ];
}

