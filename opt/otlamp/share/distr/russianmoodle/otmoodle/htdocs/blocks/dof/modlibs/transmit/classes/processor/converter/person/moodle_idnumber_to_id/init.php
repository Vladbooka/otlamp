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
 * Обмен данных с внешними источниками. 
 * Конвертер индивидуального номера (idnumber) пользователя Moodle в идентификатор персоны.
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_processor_converter_person_moodle_idnumber_to_id extends dof_modlib_transmit_processor_base
{
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_required = ['idnumber' => null];
    
    /**
     * Необязательные входящие данные
     *
     * @var array
     */
    public static $slots_input = ['simulation' => null];
    
    /**
     * Статичные данные
     *
     * @var array
     */
    public static $slots_static = ['trycreatefrommoodle' => null];
    
    /**
     * Исходящие данные
     *
     * @var array
     */
    public static $slots_output = [
        'personid' => null, 
        'sync' => null
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
        $moodleusers = $dof->modlib('ama')->user(false)->get_user_by_idnumber($input['idnumber']);
        if( ! empty($moodleusers) && count($moodleusers) == 1 )
        {// есть пользователь moodle с указанным idnumber
            
            $moodleuser = array_shift($moodleusers);
            
            // получение персоны, синхронизированной с moodle, если есть
            $person = $dof->storage('persons')->get_bu($moodleuser->id);
            if( ! empty($person) && (int)$person->sync2moodle == 1 )
            {
                return ['personid' => $person->id];
            }
            
            // персона не была найдена - создадим, если требуется
            if( ! empty($input['trycreatefrommoodle']) )
            {
                $personid = $dof->storage('persons')->reg_moodleuser($moodleuser, (bool)$input['simulation']);
                if( ! empty($personid) )
                {// Удалось зарегистрировать
                    return ['personid' => $personid, 'sync' => 1];
                }
            }
        }
        
        return [];
    }
}
