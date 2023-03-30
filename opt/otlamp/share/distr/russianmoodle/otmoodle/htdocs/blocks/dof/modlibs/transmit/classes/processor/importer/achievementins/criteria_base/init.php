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

class dof_modlib_transmit_processor_importer_achievementins_criteria_base extends dof_modlib_transmit_processor_base
{
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_required = ['departmentid' => null, 'achievementinsid' => null, 'value' => null, 'criterianum' => null];
    
    /**
     * Исходящие данные
     *
     * @var array
     */
    public static $slots_output = ['criteriasavedflag' => null];
    
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
        // Поиск экземпляра достижения
        $instance = $dof->storage('achievementins')->get($input['achievementinsid']);
        if ( ! $instance )
        {
            return [];
        }
        
        // Распаковка данных по критериям
        $instance->data = unserialize($instance->data);
        
        // Установка значения критерия
        $instance->data['simple'.$input['criterianum'].'_value'] = $input['value'];
        
        // Запаковка данных по критериям
        $instance->data = serialize($instance->data);
        
        // Сохранение пользовательского достижения
        $id = $dof->storage('achievementins')->save($instance);

        if ( $id )
        {// Успешное сохранение
            // Запись в лог
            $logger->addlog(
                null,
                'update',
                'achievementins',
                $id,
                'success',
                (array)$instance->data
            );
            // Добавление идентификатора в результирующий массив
            return ['criteriasavedflag' => $id];
        } else
        {// Ошибки
            // Запись в лог
            $logger->addlog(
                null,
                'update',
                'achievementins',
                $id,
                'error',
                (array)$instance->data
            );
            return ['criteriasavedflag' => null];
        }
        return [];
    }
}
