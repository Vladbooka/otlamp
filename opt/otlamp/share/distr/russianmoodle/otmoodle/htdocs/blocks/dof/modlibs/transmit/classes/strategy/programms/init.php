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
 * Обмен данных с внешними источниками. Стратегия обмена программ.
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_strategy_programms extends dof_modlib_transmit_strategy_base
{
    /**
     * Список полей, доступных для импорта
     *
     * @var array
     */
    public static $importfields = [
        
        // Данные по подразделению
        'programm_id' => ['type' => PARAM_INT],
        'programm_name' => ['type' => PARAM_RAW_TRIMMED],
        'programm_code' => ['type' => PARAM_RAW_TRIMMED],
        'programm_agenums' => ['type' => PARAM_INT]
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
                'required_slots' => ['numeric' => 'programm_id'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['numeric' => 'programm_id_valid']
            ],
            [
                'required_slots' => ['numeric' => 'programm_agenums'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['numeric' => 'programm_agenums_valid']
            ]
        ],
        // Валидатор строкового значения
        'validator_general_string' => [
            [
                'required_slots' => ['string' => 'programm_name'],
                'input_slots' => [],
                'static_slots' => ['length' => 255],
                'output_slots' => ['string' => 'programm_name_valid']
            ],
            [
                'required_slots' => ['string' => 'programm_code'],
                'input_slots' => [],
                'static_slots' => ['length' => 255],
                'output_slots' => ['string' => 'programm_code_valid']
            ]
        ],
        // Валидатор идентификатора программы
        'validator_programm_id_exists' => [
            // Проверка наличия подразделения по ID
            [
                'required_slots' => ['id' => 'programm_id_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['id' => 'programm_id_exists']
            ]
        ],
        // Валидатор доступности кода
        'validator_programm_code_free' => [
            [
                'required_slots' => ['code' => 'programm_code_valid'],
                'input_slots' => ['exclude_id' => 'programm_id_exists'],
                'static_slots' => [],
                'output_slots' => ['code' => 'programm_code_prepared']
            ]
        ]
    ];
    
    /**
     * Пул конвертеров
     *
     * @var array
     */
    protected $converters = [
        // Конвертер кода программы в идентификатор
        'converter_programm_code_to_id' => [
            // Конвертация кода в ID
            [
                'required_slots' => ['code' => 'programm_code_valid'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['id' => 'programm_id_exists']
            ]
        ],
        // Конвертер идентификатора программы в код
        'converter_programm_id_to_code' => [
            // Конвертация кода в ID
            [
                'required_slots' => ['id' => 'programm_id_exists'],
                'input_slots' => [],
                'static_slots' => [],
                'output_slots' => ['code' => 'programm_code_prepared']
            ]
        ]
    ];

    /**
     * Пул импортеров
     *
     * @var array
     */
    protected $importers = [
        
        // Импорт программы
        'importer_programms_base' => [
            [
                'required_slots' => [
                    'code' => 'programm_code_prepared'
                ],
                'input_slots' => [
                    'id' => 'programm_id_exists',
                    'name' => 'programm_name_valid',
                    'departmentid' => '__departmentid',
                    'agenums' => 'programm_agenums_valid',
                    'simulation' => 'simulation'
                ],
                'static_slots' => [],
                'output_slots' => [
                    'savedid' => 'programm_id_savedid'
                ]
            ]
        ]
    ];
}

