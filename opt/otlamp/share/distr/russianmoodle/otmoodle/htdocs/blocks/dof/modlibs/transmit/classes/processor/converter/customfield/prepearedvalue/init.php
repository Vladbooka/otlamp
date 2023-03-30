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
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_processor_converter_customfield_prepearedvalue extends dof_modlib_transmit_processor_base
{
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_required = ['id' => null, 'value' => null];
    
    /**
     * Необязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_input = [ 'currentdepartmentid' => null];
    
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
        // Получение дополнительного поля
        $customfield = $dof->storage('customfields')->get($input['id']);
        if ( ! empty($customfield) )
        {// Поле найдено
            $customfield = $dof->modlib('formbuilder')->
                init_customfield_by_item($customfield);
            
            $currentdepartmentid = $customfield->get_customfield()->departmentid;
            if ( ! empty($input['departmentid']) )
            {
                $currentdepartmentid = $input['departmentid'];
            }
            switch ( $customfield::type() )
            {
                case 'text' :
                    // Валидация текста
                    $errors = $customfield->validate_data($input['value'], null);
                    if ( empty($errors) )
                    {
                        return ['prepearedvalue' => $input['value']];
                    }
                    break;
                case 'select' :
                    // Получение значения выпадающего поля по выбранным данным
                    $option = $customfield->get_selectoption($input['value']);
                    
                    if ( isset($option) )
                    {// Значение получено
                        // Валидация значения
                        $errors = $customfield->validate_data($option, null);
                        if ( empty($errors) )
                        {
                            return ['prepearedvalue' => $option];
                        }
                    }
                    break;
                case 'date' :
                    
                    $timezone = $dof->storage('departments')->
                        get_timezone($currentdepartmentid);
                    
                    // Получение метки времени для даты во временной зоне сервера
                    date_default_timezone_set(core_date::get_user_timezone($timezone));
                    $timestamp = strtotime($input['value']);
                    core_date::set_default_server_timezone();
                    if ( $timestamp )
                    {// Коррекция даты с учетом временной зоны подразделения
                        $cfvalue = dof_usergetdate($timestamp);
                        // Конвертация даты для допполя
                        $cfvalue = [
                            'year' => $cfvalue['year'],
                            'month' => $cfvalue['mon'],
                            'day' => $cfvalue['mday'],
                            'hours' => $cfvalue['hours'],
                            'minutes' => $cfvalue['minutes'],
                            'seconds' => $cfvalue['seconds'],
                            'timezone' => $timezone
                        ];
                        // Валидация значения
                        $errors = $customfield->validate_data($cfvalue, null);
                        if ( empty($errors) )
                        {
                            return ['prepearedvalue' => serialize($cfvalue)];
                        }
                    }
                    break;
                case 'file' :
                    
                    // Копирование файла в пользовательскую драфтовую зону
                    $draftitemid = $filemanager->file_copy_to_draft($input['value']);
                    
                    // Валидация значения
                    $errors = $customfield->validate_data($draftitemid, null);
                    if ( empty($errors) )
                    {
                        return ['prepearedvalue' => $draftitemid];
                    }
                    break;
            }
        }
        return [];
    }
}
