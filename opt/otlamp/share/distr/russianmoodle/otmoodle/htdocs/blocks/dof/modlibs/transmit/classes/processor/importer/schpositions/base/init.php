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
 * Обмен данных с внешними источниками. Базовый импортер вакансии 
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_processor_importer_schpositions_base extends dof_modlib_transmit_processor_base
{
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_required = [
        'positionid' => null,
        'departmentid' => null
    ];
    
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_input = [
        'generate' => null
    ];
    
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
        if ( ! empty($input['generate']) )
        {
            // создание объекта вакансии
            $schpositionnew = new stdClass();
            $schpositionnew->worktime = 40;
            $schpositionnew->departmentid = $input['departmentid'];
            $schpositionnew->positionid = $input['positionid'];
            
            // добавление вакансии
            $id = $dof->storage('schpositions')->insert($schpositionnew);
            
            if ( ! empty($id) )
            {
                $logger->addlog(
                        null,
                        'insert',
                        'schpositions',
                        $id,
                        'success',
                        (array)$schpositionnew
                        );
                
                // инициализация обработчика статусов
                $dof->workflow('schpositions')->init($id);
                
                // Перевод статуса в активный
                $dof->workflow('schpositions')->change($id, 'active');
                
                return ['id' => $id];
            } else
            {
                $logger->addlog(
                        null,
                        'insert',
                        'schpositions',
                        $id,
                        'error',
                        (array)$schpositionnew
                        );
            }
        }

        return [];
    }
}
