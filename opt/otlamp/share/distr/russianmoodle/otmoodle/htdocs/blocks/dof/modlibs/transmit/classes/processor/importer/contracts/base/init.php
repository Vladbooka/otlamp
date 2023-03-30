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
 * Обмен данных с внешними источниками. Создание/Обновление договора
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_processor_importer_contracts_base extends dof_modlib_transmit_processor_base
{
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_required = ['personid' => null, 'num' => null];
    
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_input = [
        'contractid' => null,
        'activate_contract' => null,
        'parentid' => null,
        'sellerid' => null,
        'curatorid' => null,
        'startdate' => null,
        'notice' => null,
        'departmentid' => null
    ];
    
    /**
     * Исходящие данные
     *
     * @var array
     */
    public static $slots_output = ['contractid' => null];
    
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
        // Заполняем объект персоны для добавления/обновления информации
        $contract_obj = new stdClass();
        
        // Номер контракта
        $contract_obj->num = $input['num'];
        
        // Идентификатор студента
        $contract_obj->studentid = intval($input['personid']);

        if ( isset($input['parentid']) )
        {// Идентификатор законного представителя
            $contract_obj->clientid = intval($input['parentid']);
        } else 
        {
            $contract_obj->clientid = $contract_obj->studentid;    
        }
        
        if ( isset($input['curatorid']) )
        {// Идентификатор куратора
            $contract_obj->curatorid = intval($input['curatorid']);
        }
        
        if ( isset($input['sellerid']) )
        {// Идентификатор менеджера
            $contract_obj->sellerid = intval($input['sellerid']);
        }
        
        if ( isset($input['notice']) )
        {// Заметка
            $contract_obj->notice = $input['notice'];
        }

        if ( isset($input['startdate']) )
        {// Дата подписания
            
            $contract_obj->date = intval($input['startdate']);
        }
        
        if ( isset($input['departmentid']) )
        {// Дата подписания
            $contract_obj->departmentid = intval($input['departmentid']);
        }
        
        $action = 'insert';
        if ( ! empty($input['contractid']) )
        {// Дата подписания
            $action = 'update';
            $contract_obj->id = intval($input['contractid']);
        }
        
        // Сохранение договора
        $id = $dof->storage('contracts')->save($contract_obj);
        
        if ( $id )
        {
            if ( ! empty($input['activate_contract']) )
            {
                // активация договора
                if ( $dof->workflow('contracts')->activate_new($id) )
                {
                    // Запись в лог
                    $logger->addlog(
                            null,
                            $action,
                            'contracts',
                            $id,
                            'success',
                            ['contractid' => $id],
                            $dof->get_string('contract_success_change_status', 'transmit', null, 'modlib')
                            );
                } else 
                {
                    // Запись в лог
                    $logger->addlog(
                            null,
                            $action,
                            'contracts',
                            $id,
                            'error',
                            ['contractid' => $id],
                            $dof->get_string('contract_cannot_change_status', 'transmit', null, 'modlib')
                            );
                }
            }
            
            // Запись в лог
            $logger->addlog(
                null,
                $action,
                'contracts',
                $id,
                'success',
                (array)$contract_obj
            );
            
            return ['contractid' => $id];
        } else 
        {
            // Запись в лог
            $logger->addlog(
                null,
                $action,
                'contracts',
                $id,
                'error',
                (array)$contract_obj
            );
            return ['contractid' => null];
        }
        return [];
    }
}
