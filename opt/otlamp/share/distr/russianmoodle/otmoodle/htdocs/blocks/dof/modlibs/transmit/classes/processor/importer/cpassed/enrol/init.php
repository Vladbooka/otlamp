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

class dof_modlib_transmit_processor_importer_cpassed_enrol extends dof_modlib_transmit_processor_base
{
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_required = [
        'programmsbcid' => null, 
        'cstreamid' => null
    ];
    
    /**
     * Необязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_input = [
        'simulation' => null
    ];
   
    /**
     * Исходящие данные
     *
     * @var array
     */
    public static $slots_output = [
        'id' => null
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
        // Запись на дисциплину
        $cpassedid = $dof->storage('cpassed')->
            sign_student_on_cstream($input['cstreamid'], $input['programmsbcid']);
        
        if ( $cpassedid === false || $cpassedid === true )
        {// Ошибка записи на учебный процесс
            $logger->addlog(
                null,
                'insert',
                'cpassed',
                null,
                'error',
                ['cstreamid' => $input['cstreamid'], 'programmsbcid' => $input['programmsbcid']]
            );
            return ['id' => null];
        }
        
        // Успешная запись
        $logger->addlog(
            null,
            'insert',
            'cpassed',
            $cpassedid,
            'success',
            ['cstreamid' => $input['cstreamid'], 'programmsbcid' => $input['programmsbcid']]
        );
        return ['id' => $cpassedid];
    }
}
