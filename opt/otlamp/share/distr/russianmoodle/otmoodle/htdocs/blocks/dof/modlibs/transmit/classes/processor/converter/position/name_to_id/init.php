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
 * Обмен данных с внешними источниками. Конвертер названия должности в идентификатор
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_processor_converter_position_name_to_id extends dof_modlib_transmit_processor_base
{
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_required = ['posname' => null];
    
    /**
     * Необязательные входящие данные
     *
     * @var array
     */
    public static $slots_input = ['departmentid' => null];
    
    /**
     * Исходящие данные
     *
     * @var array
     */
    public static $slots_output = ['id' => null];
    
    /**
     * Запуск обработчика
     *
     * @param array $input - Входящие данные
     * @param dof_control $dof - Контроллер Электронного Деканата
     * @param dof_storage_logs_queuetype_base $logger - Очередь логов
     * @param dof_modlib_transmit_source_filemanager $filemanager - Менеджер файлов
     *
     * @return array - Исходящие данные
     */
    public static function execute($input, $dof, $logger, $filemanager)
    {
        // получение актуальных статусов
        $statuses = array_keys($dof->workflow('positions')->get_meta_list('actual'));
        
        // ициниализация массива должностей
        $positions = $processingpositions = [];
        
        if ( ! empty($input['departmentid']) )
        {
            // поиск родительских достижений
            $departments = array_reverse($dof->storage('departments')->get_departmentstrace($input['departmentid']));
            
            // ищем должности по удаленности от текущего
            foreach ( $departments as $department )
            {
                // получение должности
                $processingpositions = $dof->storage('positions')->get_records(['name' => $input['posname'], 'departmentid' => $department->id, 'status' => $statuses]);
                if ( empty($processingpositions) )
                {
                    continue;
                }
                $counter = count($processingpositions);
                if ( $counter === 1 )
                {
                    // нашли должность
                    $positions = $processingpositions;
                } else 
                {
                    // найдено более двух должностей
                    $logger->addlog(
                            null,
                            'get',
                            'appointments',
                            null,
                            'error',
                            [],
                            $dof->get_string('converter_position_name_to_id_duplicate', 'transmit', $counter, 'modlib')
                            );
                    return [];  
                }
            }
        } else
        {
            // получение должности
            $positions = $dof->storage('positions')->get_records(['name' => $input['posname'], 'status' => $statuses]);
        }
        
        if ( count($positions) === 1 )
        {
            // нашли ровно одну должность
            // бросим идентификатор в пулл
            return ['id' => key($positions)];
        }
        
        // не нашли нужную должность
        return [];
    }
}
