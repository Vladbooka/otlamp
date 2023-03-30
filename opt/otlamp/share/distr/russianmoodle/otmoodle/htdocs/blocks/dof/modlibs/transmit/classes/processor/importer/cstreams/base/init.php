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
 * Обмен данных с внешними источниками. Создание/Обновление учебного процесса
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_processor_importer_cstreams_base extends dof_modlib_transmit_processor_base
{
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_required = [
        'programmitemid' => null, 
        'ageid' => null,
        'appointmentid' => null,
        'departmentid' => null
    ];
    
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_input = [
        'id' => null,
        'name' => null,
        'description' => null,
        'begindate' => null,
        'enddate' => null,
        'hoursweek' => null,
        'simulation' => null
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
        $saveoptions = [];
        
        // Объект учебного процесса для сохранения
        $save = new stdClass();
        $save->programmitemid = $input['programmitemid'];
        $save->ageid = $input['ageid'];
        $save->appointmentid = $input['appointmentid'];
        $save->departmentid = $input['departmentid'];
        
        // Идентификатор учебного процесса
        if ( isset($input['id']) )
        {
            $save->id = (int)($input['id']);
        }
        // Название учебного процесса
        if ( isset($input['name']) )
        {
            $save->name = (string)($input['name']);
        }
        // Описание учебного процесса
        if ( isset($input['description']) )
        {
            $save->description = (string)($input['description']);
        }
        // Дата начала учебного процесса
        if ( isset($input['begindate']) )
        {
            $save->begindate = (int)($input['begindate']);
        }
        // Дата окончания учебного процесса
        if ( isset($input['enddate']) )
        {
            $save->enddate = (int)($input['enddate']);
        }
        // Нагрузка в неделю
        if ( isset($input['hoursweek']) )
        {
            $save->hoursweek = (int)($input['hoursweek']);
        }
      
        if ( isset($input['simulation']) )
        {
            $saveoptions['silent'] = (bool)$input['simulation'];
        }
        
        // Сохранение данных
        try
        {
            $id = $dof->storage('cstreams')->save($save, $saveoptions);
            if ( empty($save->id) )
            {
                $logger->addlog(
                    null,
                    'insert',
                    'cstreams',
                    $id,
                    'success',
                    (array)$save
                );
            } else
            {
                $logger->addlog(
                    null,
                    'update',
                    'cstreams',
                    $id,
                    'success',
                    (array)$save
                );
            }
            return ['id' => $id];
        } catch ( dof_exception_dml $e )
        {
            if ( empty($save->id) )
            {
                // Ошибка сохранения персоны
                $logger->addlog(
                    null,
                    'insert',
                    'cstreams',
                    null,
                    'error',
                    (array)$save,
                    $dof->get_string($e->errorcode, 'cstreams', null, 'storage')
                );
            } else
            {
                // Ошибка сохранения персоны
                $logger->addlog(
                    null,
                    'update',
                    'cstreams',
                    null,
                    'error',
                    (array)$save,
                    $dof->get_string($e->errorcode, 'cstreams', null, 'storage')
                );
            }
            return [];
        }
    }
}
