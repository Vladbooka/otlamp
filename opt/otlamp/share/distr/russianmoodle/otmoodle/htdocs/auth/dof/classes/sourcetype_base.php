<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Класс базового источника.
 *
 * @package    auth
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_dof;

use HTML_QuickForm;

abstract class sourcetype_base
{
    /**
     * Получение списка полей внешнего источника
     * 
     * @param string $connection
     * @param string $table
     */
    abstract public function get_external_fields(string $connection, string $table);
    
    /**
     * Варлидация полей внешнего источника
     */
    abstract public function validation();
    
    /**
     * Получение языковой строки источника
     */
    abstract public static function get_name_string();
    
    /**
     * Форма настроек источника должна возврашать массив элементов формы
     * 
     * @param HTML_QuickForm $mform
     */
    abstract public function definition(HTML_QuickForm $mform);
    
    /**
     * Обработчик формы настроек источника должен вернуть 
     * массив (строка-идентификатор подключения, таблица во нешнем источнике)
     * 
     * @param array $data - поля со значениями из definition
     */
    abstract public function process($data);
    
    /**
     * Получение имени подключения по идентификатору
     * 
     * @param string $connection - строка-идентификатор подключения
     */
    abstract public static function get_cofig_name(string $connection);
    
    /**
     * Получение поля из внешнего источника
     * 
     * @param string $connection
     * @param string $table
     * @param string $field
     * @param array $conditions
     */
    abstract public function get_external_fields_data(
        string $connection, string $table, array $conditions);
}