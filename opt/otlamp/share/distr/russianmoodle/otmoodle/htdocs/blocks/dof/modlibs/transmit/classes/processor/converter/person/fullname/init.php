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
 * Обмен данных с внешними источниками. Конвертер ФИО
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_processor_converter_person_fullname extends dof_modlib_transmit_processor_base
{
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_required = ['fullnameformat' => null, 'fullname' => null];
    
    /**
     * Исходящие данные
     *
     * @var array
     */
    public static $slots_output = ['firstname' => null, 'lastname' => null, 'middlename' => null];
    
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
        $firstname = null;
        $lastname = null;
        $middlename = null;
        
        // Формат ФИО
        $format = stripslashes(strtolower($input['fullnameformat']));
        
        // ФИО
        $fullname = $input['fullname'];

        // Генерация регулярного выражения
        $replacement = ['lastname', 'firstname', 'middlename'];
        $replace = ['(?P<lastname>.*)', '(?P<firstname>.*)', '(?P<middlename>.*)'];
        $regular = '/^'. str_replace($replacement, $replace, $format) . '$/';
        
        // Поиск полного совпадения ФИО по маске
        $fullnameparts = [];
        if ( preg_match($regular, $fullname, $fullnameparts) )
        {// Передано полное ФИО по маске
            
            // Генерация данных имени
            if ( isset($fullnameparts['firstname']) )
            {
                $firstname = $fullnameparts['firstname'];
            }
            if ( isset($fullnameparts['lastname']) )
            {
                $lastname = $fullnameparts['lastname'];
            }
            if ( isset($fullnameparts['middlename']) )
            {
                $middlename = $fullnameparts['middlename'];
            }
            
            // Передача данных об имени
            return [
                'firstname' => $firstname,
                'lastname' => $lastname,
                'middlename' => $middlename
            ];
        }
        
        // Поиск частичного совпадения по маске
        $lastpos = 0;
        while ( ( $lastpos = strrpos($regular, '>.*)', -1*$lastpos) ) !== false ) 
        {
            $partregular = substr($regular, 0, $lastpos).'>.*).*$/';
            if ( preg_match($partregular, $fullname, $fullnameparts) === 1 )
            {// Найдено соответствие по укороченной маске
                // Генерация данных имени
                if ( isset($fullnameparts['firstname']) )
                {
                    $firstname = $fullnameparts['firstname'];
                }
                if ( isset($fullnameparts['lastname']) )
                {
                    $lastname = $fullnameparts['lastname'];
                }
                if ( isset($fullnameparts['middlename']) )
                {
                    $middlename = $fullnameparts['middlename'];
                }
                // Передача данных об имени
                return [
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'middlename' => $middlename
                ];
            }
            $lastpos = strlen($regular) - $lastpos + 1;
        }
        return [];
    }
}
