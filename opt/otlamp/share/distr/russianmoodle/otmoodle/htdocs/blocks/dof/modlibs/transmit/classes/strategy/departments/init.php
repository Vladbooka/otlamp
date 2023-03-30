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
 * Обмен данных с внешними источниками. Стратегия обмена подразделений.
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_strategy_departments extends dof_modlib_transmit_strategy_base
{
    /**
     * Список полей, доступных для импорта
     *
     * @var array
     */
    public static $importfields = [
        
        // Данные по подразделению
        'department_id' => ['type' => PARAM_INT],
        'department_name' => ['type' => PARAM_RAW_TRIMMED],
        'department_code' => ['type' => PARAM_RAW_TRIMMED],
        'department_description' => ['type' => PARAM_RAW_TRIMMED],
        'department_leaddepid' => ['type' => PARAM_INT],
        'department_leaddepcode' => ['type' => PARAM_RAW_TRIMMED],
        'department_activate' => ['type' => PARAM_BOOL, 'option' => true]
    ];
    
    /**
     * Пул валидаторов
     *
     * @var array
     */
    protected $validators = [
        // Валидатор числового значения
        'validator_general_numeric' => [
            [
                'required_slots' => ['numeric' => 'department_id'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['numeric' => 'department_id_valid']
            ],
            [
                'required_slots' => ['numeric' => 'department_leaddepid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['numeric' => 'department_leaddepid_valid']
            ]
        ],
        // Валидатор строкового значения
        'validator_general_string' => [
            [
                'required_slots' => ['string' => 'department_name'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'department_name_valid']
            ],
            [
                'required_slots' => ['string' => 'department_code'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'department_code_valid']
            ],
            [
                'required_slots' => ['string' => 'department_leaddepcode'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'department_leaddepcode_valid']
            ],
            [
                'required_slots' => ['string' => 'department_description'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'department_description_valid']
            ],
            [
                'required_slots' => ['string' => 'programmitem_code'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['string' => 'programmitem_code_valid']
            ]
        ],
        // Валидатор булевого значения
        'validator_general_bool' => [
            [
                'required_slots' => ['bool' => 'department_activate'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['bool' => 'department_activate_valid']
            ],
        ],
        // Валидатор идентификатора подразделения
        'validator_department_id_exists' => [
            // Проверка наличия подразделения по ID
            [
                'required_slots' => ['department_id' => 'department_id_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['department_id' => 'department_id_exists']
            ],
            // Проверка наличия подразделения по ID
            [
                'required_slots' => ['department_id' => 'department_leaddepid_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['department_id' => 'department_leaddepid_exists']
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
            ],
            [
                'required_slots' => ['code' => 'department_leaddepcode_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['id' => 'department_leaddepid_exists']
            ]
        ],
        'converter_department_id_to_code' => [
            // Конвертация ID подразделения в код
            [
                'required_slots' => ['code' => 'department_id_exists'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['id' => 'department_code_valid']
            ]
        ]
    ];

    /**
     * Пул импортеров
     *
     * @var array
     */
    protected $importers = [
        
        // Импорт подразделения
        'importer_departments_base' => [
            [
                'required_slots' => [
                    'code' => 'department_code_valid'
                ],
                'input_slots' => [
                    'id' => 'department_id_exists',
                    'name' => 'department_name_valid',
                    'description' => 'department_description_valid',
                    'leaddepid' => 'department_leaddepid_exists',
                    'activate' => 'department_activate_valid',
                    'simulation' => 'simulation'
                ],
                'static_slots' => [],
                'output_slots' => [
                    'id' => 'department_id_savedid'
                ]
            ]
        ]
    ];
}

