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
 * Обмен данных с внешними источниками. Создание/Обновление подразделение
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_processor_importer_departments_base extends dof_modlib_transmit_processor_base
{
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_required = [
        'code' => null
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
        'leaddepid' => null,
        'activate' => null,
        'simulation' => null
    ];
    
    /**
     * Исходящие данные
     *
     * @var array
     */
    public static $slots_output = [
        'savedid' => null
    ];
    
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
        $save = new stdClass();
        $save->code = $input['code'];

        // Проверка необходимости сохранения
        $saverequired = false;
        if ( isset($input['id']) )
        {
            $save->id = $input['id'];
            $itemcode = $dof->storage('departments')->get_field($input['id'], 'code');
            if ( $itemcode != $input['code'] )
            {// Код изменен
                $saverequired = true;
            }
        }
        if ( isset($input['name']) )
        {
            $save->name = $input['name'];
            $saverequired = true;
        }
        if ( isset($input['leaddepid']) )
        {
            $save->leaddepid = $input['leaddepid'];
            $saverequired = true;
        }
        if ( isset($input['description']) )
        {
            $save->description = $input['description'];
            $saverequired = true;
        }
        
        if ( ! $saverequired )
        {// Сохранение не требуется
            if ( isset($input['id']) )
            {
                return ['savedid' => (int)$input['id']];  
            }
            return ['savedid' => null];
        }
        
        
        // Опции сохранения
        $saveoptions = [];
        if ( isset($input['simulation']) )
        {
            $saveoptions['silent'] = (bool)$input['simulation'];
        }
        // Активация подразделения
        if ( ! empty($input['activate']) )
        {
            $saveoptions['activate'] = (bool)$input['activate'];
        }
        
        // Сохранение данных персоны
        try
        {
            // Сохранение подразделения
            $id = $dof->storage('departments')->save($save, $saveoptions);
            if ( empty($save->id) )
            {
                $logger->addlog(
                    null,
                    'insert',
                    'departments',
                    $id,
                    'success',
                    (array)$save
                );
            } else
            {
                $logger->addlog(
                    null,
                    'update',
                    'departments',
                    $id,
                    'success',
                    (array)$save
                );
            }
            
            return ['savedid' => $id];
        } catch ( dof_exception_dml $e )
        {
            if ( empty($save->id) )
            {
                $logger->addlog(
                    null,
                    'insert',
                    'departments',
                    null,
                    'error',
                    (array)$save,
                    $dof->get_string($e->errorcode, 'departments', null, 'storage')
                );
            } else
            {
                $logger->addlog(
                    null,
                    'update',
                    'departments',
                    $save->id,
                    'error',
                    (array)$save,
                    $dof->get_string($e->errorcode, 'departments', null, 'storage')
                );
            }
        }
        return [];
    }
}
