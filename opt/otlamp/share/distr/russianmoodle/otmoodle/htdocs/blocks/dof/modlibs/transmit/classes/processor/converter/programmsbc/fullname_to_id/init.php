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
 * Обмен данных с внешними источниками. Конвертер кода дисциплины в идентификатор
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_processor_converter_programmsbc_fullname_to_id extends dof_modlib_transmit_processor_base
{
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_required = ['lastname' => null, 'programmitemid' => null];
    
    /**
     * Необязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_input = ['firstname' => null, 'middlename' => null];
    
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
        // Получение программы обучения
        $programmid = $dof->storage('programmitems')->get_field((int)$input['programmitemid'], 'programmid');
        // Плавающие учебные планы
        $flowagenums = $dof->storage('programms')->get_field($programmid, 'flowagenums');
        // Параллель предмета
        $pitemagenum = $dof->storage('programmitems')->get_field((int)$input['programmitemid'], 'agenum');
        
        // Поиск всех персон с указанным именем
        $params = ['lastname' => $input['lastname']];
        if ( $input['firstname'] )
        {// Указано имя
            $params['firstname'] = $input['firstname'];
        }
        if ( $input['middlename'] )
        {// Указано имя
            $params['firstname'] = $input['middlename'];
        }
        
        $persons = (array)$dof->storage('persons')->get_records($params, '', 'id');
        if ( count($persons) > 1 )
        {// Установлена неоднозначность в персонах
            $logger->addlog(
                'error',
                'get',
                'persons',
                null,
                'error',
                (array)$params,
                $dof->get_string('converter_programmsbc_fullname_to_id_error_multiple_persons', 'transmit', $params, 'modlib')
            );
            return [];
        }
        foreach ( $persons as $personid => $person )
        {
            // Поиск подходящей подписки на программу
            $programmsbcs = $dof->storage('programmsbcs')->get_programmsbcs_by_personid($personid);
            foreach ( $programmsbcs as $programmsbcid => $programmsbc )
            {
                if ( $programmsbc->programmid != $programmid )
                {// Подписка на другую программу
                    continue;
                }
                if ( $flowagenums || $pitemagenum == 0 || $programmsbc->agenum == $pitemagenum )
                {// Подписка найдена
                    return ['id' => $programmsbcid];
                }
            }
        }
        return [];
    }
}
