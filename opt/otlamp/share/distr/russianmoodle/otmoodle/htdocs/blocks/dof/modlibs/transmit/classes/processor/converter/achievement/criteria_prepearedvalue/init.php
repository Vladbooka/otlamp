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
 * Обмен данных с внешними источниками. Конвертер критериев
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_processor_converter_achievement_criteria_prepearedvalue extends dof_modlib_transmit_processor_base
{
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_required = ['achievementid' => null, 'value' => null, 'criterianum' => null, 'departmentid' => null];
    
    
    /**
     * Исходящие данные
     *
     * @var array
     */
    public static $slots_output = ['prepearedvalue' => null];
    
    
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
        // Получение шаблона
        $achievement = $dof->storage('achievements')->get($input['achievementid']);
        if ( $achievement )
        {
            // Критерии достижения
            $criterias = unserialize($achievement->data);

            if ( isset($criterias['simple_data'][(int)$input['criterianum']]) )
            {
                $criteria = $criterias['simple_data'][(int)$input['criterianum']];
                
                $currentdepartmentid = $input['departmentid'];

                switch ( $criteria->type )
                {
                    case 'text' :
                        return ['prepearedvalue' => clean_param($input['value'], PARAM_RAW_TRIMMED)];
                    case 'select' :
                        // Получение значения выпадающего поля по выбранным данным

                        foreach ( $criteria->options as $key => $option )
                        {
                            if ( trim($option->name) === trim($input['value']) )
                            {
                                return ['prepearedvalue' => $key];
                            }
                        }
                        break;
                    case 'data' :
                        
                        $timezone = $dof->storage('departments')->
                            get_timezone($currentdepartmentid);
                        
                        // Получение метки времени для даты во временной зоне сервера
                        date_default_timezone_set(core_date::get_user_timezone($timezone));
                        $timestamp = strtotime($input['value']);
                        core_date::set_default_server_timezone();
                        if ( $timestamp )
                        {// Коррекция даты с учетом временной зоны подразделения
                            return ['prepearedvalue' => $timestamp];
                        }
                        break;
                    case 'file' :
                        
                        $achievementitemid = $dof->modlib('filestorage')->get_new_itemid();
                        $file = $filemanager->file_copy(
                            $input['value'],
                            'public',
                            '/'.pathinfo($input['value'])['basename'],
                            $achievementitemid
                        );
                        if ( $file )
                        {// Внешний файл был скопирован из источника в зону пользовательякого достижения
                            $itemid = $file->get_itemid();
                            if ( ! empty($itemid) )
                            {
                                // Добавление в результирующий массив
                                return ['prepearedvalue' => $itemid];
                            }
                        }
                        break;
                }
            }
        }
        return [];
    }
}
