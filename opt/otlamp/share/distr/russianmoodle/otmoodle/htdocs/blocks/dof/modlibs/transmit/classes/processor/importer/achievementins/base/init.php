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

class dof_modlib_transmit_processor_importer_achievementins_base extends dof_modlib_transmit_processor_base
{
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_required = ['personid' => null, 'achievementid' => null];
    
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_input = ['update_exists' => null];
    
    /**
     * Исходящие данные
     *
     * @var array
     */
    public static $slots_output = ['achievementinsid' => null];
    
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
        // Действие
        $action = 'insert';
        // Готовим объект достижения для сохранения
        $achievementin = new stdClass();
        
        // Идентификатор шаблона достижения
        $achievementin->achievementid = $input['achievementid'];
        
        // Идентификатор пользователя
        $achievementin->userid = $input['personid'];
        
        // Данные о критериях
        $achievementin->data = serialize([]);
        
        if ( ! empty($input['update_exists']) )
        {// Флаг обновление существующих достижений
            
            // Ищем последнее добавленное достижение по указанному шаблону
            $instances = $dof->storage('achievementins')->get_records(
                [
                    'achievementid' => $achievementin->achievementid,
                    'userid' => $achievementin->userid,
                    'status' => array_keys($dof->workflow('achievementins')->get_meta_list('real'))
                ],
                'id'
            );
            $instance = array_pop($instances);
            if ( $instance )
            {// Достижение для обновления найдено
                $action = 'update';
                $achievementin->id = $instance->id;
            }
        }

        // Сохранение достижения
        $id = $dof->storage('achievementins')->save($achievementin);
        if ( $id )
        {// Успешное сохранение
            // Запись в лог
            
            $logger->addlog(
                null,
                $action,
                'achievementins',
                $id,
                'success',
                (array)$achievementin
            );
            // Добавление идентификатора в результирующий массив
            return ['achievementinsid' => $id];
        } else
        {// Ошибки
            // Запись в лог
            $logger->addlog(
                null,
                $action,
                'achievementins',
                $id,
                'error',
                (array)$achievementin
            );
        }
        return [];
    }
}
