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
 * Обмен данных с внешними источниками. Класс стратегии импорта портфолио
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_strategy_achievements extends dof_modlib_transmit_strategy_base
{
    /**
     * Список полей, доступных для импорта
     *
     * @var array
     */
    public static $importfields = [
        
        // Данные по владельцу достижения
        'person_id' => ['type' => PARAM_INT],
        'person_email' => ['type' => 'email'],
        
        // Данные пользовательского достижния
        'achievement_id' => ['type' => PARAM_INT, 'option' => true],
        'achievement_update_exists' => ['type' => PARAM_BOOL, 'option' => true],
        // Критерии достижения
        '/(criteria[0-9]*)/m' => ['type' => PARAM_RAW_TRIMMED, 'displayedfieldcode' => 'criteria[num]']
    ];
    
    /**
     * Пул валидаторов
     *
     * @var array
     */
    protected $validators = [
        // Валидатор personid
        'validator_person_id_exists' => [
            // Проверка наличия владельца по ID
            [
                'required_slots' => ['personid' => 'person_id'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['personid' => 'person_id_valid']
            ]
        ],
        // Валидатор email
        'validator_general_email' => [
            // Проверка валидности email
            [
                'required_slots' => ['email' => 'person_email'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['email' => 'person_email_valid'],
            ]
        ],
        'validator_general_bool' => [
            // Флаг обновления имеющихся достижений
            [
                'required_slots' => ['bool' => 'achievement_update_exists'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['bool' => 'achievement_update_exists_valid']
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
        // Валидатор ID шаблона достижения
        'validator_achievement_id_exists' => [
            // Проверка наличия шаблона достижения по ID
            [
                'required_slots' => ['achievementid' => 'achievement_id'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['achievementid' => 'achievement_id_valid']
            ]
        ],
        // Валидатор критериев достижения
        'validator_achievement_criteria_exists' => [
            [
                'required_slots' => ['achievementid' => 'achievement_id_valid', 'criteriavalue' => '/criteria([0-9]*)/m'],
                'input_slots' => [],
                'static_slots' => ['criterianum' => '$1'],
                'output_slots' => ['criteriaexist' => 'criteria$1_exist']
            ]
        ]
    ];
    
    /**
     * Пул конвертеров
     *
     * @var array
     */
    protected $converters = [
        // Конвертер email
        'converter_person_email' => [
            // Конвертация Email владельца в ID
            [
                'required_slots' => ['email' => 'person_email_valid'],
                'input_slots' => [],
                'static_slots' => ['trycreatefrommoodle' => true],
                'output_slots' => ['personid' => 'person_id_valid']
            ]
        ],
        // Конвертер критериев
        'converter_achievement_criteria_prepearedvalue' => [
            // Конвертация абсолютного пути файла в ID
            [
                'required_slots' => [
                    'achievementid' => 'achievement_id_valid',
                    'value' => '/criteria([0-9]*)_exist/m',
                    'departmentid' => '__departmentid_valid'
                ],
                'input_slots' => [],
                'static_slots' => ['criterianum' => '$1'],
                'output_slots' => ['prepearedvalue' => 'criteria$1_prepearedvalue']
            ]
            
        ]
    ];

    /**
     * Пул импортеров
     *
     * @var array
     */
    protected $importers = [
        // Импорт достижений
        'importer_achievementins_base' => [
            [
                'required_slots' => [
                    'personid' => 'person_id_valid',
                    'achievementid' => 'achievement_id_valid'
                ],
                'input_slots' => [
                    'update_exists' => 'achievement_update_exists_valid'
                ],
                'static_slots' => [],
                'output_slots' => [
                    'achievementinsid' => 'achievementins_id_saved'
                ]
            ]
        ],
        'importer_achievementins_criteria_base' => [
            [
                'required_slots' => [
                    'achievementinsid' => 'achievementins_id_saved',
                    'departmentid' => '__departmentid_valid',
                    'value' => '/criteria([0-9]*)_prepearedvalue/m',
                ],
                'input_slots' => [],
                'static_slots' => ['criterianum' => '$1'],
                'output_slots' => [
                    'criteriasavedflag' => 'achievementins_criteria$1_saved'
                ]
            ]
        ]
    ];
}

