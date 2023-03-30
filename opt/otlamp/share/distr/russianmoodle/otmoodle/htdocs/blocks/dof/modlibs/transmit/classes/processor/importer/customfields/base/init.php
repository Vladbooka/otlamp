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
 * Обмен данных с внешними источниками. Импорт данных в допполя
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_processor_importer_customfields_base extends dof_modlib_transmit_processor_base
{
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_required = [
        'objectid' => null,
        'customfieldid' => null,
        'customfieldvalue' => null
    ];
    
    /**
     * Исходящие данные
     *
     * @var array
     */
    public static $slots_output = ['customfieldvaluesaved' => null];
    
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
        // Получение поля
        $customfield = $dof->storage('customfields')->get($input['customfieldid']);
        if ( $customfield )
        {// Дополнительное поле найдено
            $customfield = $dof->modlib('formbuilder')->
                init_customfield_by_item($customfield);
            
            // Тип поля
            $cftype = $customfield::type();
            // Код хранилища
            $cflinkcode = $customfield->get_customfield()->linkpcode;
            // Код дополнительного поля
            $cfcode = $customfield->get_customfield()->code;
            
            // Формирование данных для отчета
            $a = new stdClass();
            $a->{$cfcode . '_field'} = $cfcode;
            $a->{$cfcode . '_value'} = $input['customfieldvalue'];
            
            try
            {
                $value = $input['customfieldvalue'];
                if ( $cftype == 'date' )
                {
                    $value = unserialize($value);
                }
                
                // Попытка сохранения значения допполя
                $customfield->save_data($input['objectid'], $value);
                
                $logger->addlog(
                    null,
                    'update',
                    'customfields',
                    null,
                    'success',
                    (array)$a,
                    $dof->get_string('customfield_import_success', 'transmit', $a, 'modlib')
                );
                
                return ['customfieldvaluesaved' => '1'];
            } catch ( Exception $e )
            {// Запись в лог об ошибке
                $logger->addlog(
                    null,
                    'update',
                    'customfields',
                    null,
                    'error',
                    (array)$a,
                    $dof->get_string('customfield_import_error', 'transmit', $a, 'modlib')
                );
            }
        }
        return [];
    }
}
