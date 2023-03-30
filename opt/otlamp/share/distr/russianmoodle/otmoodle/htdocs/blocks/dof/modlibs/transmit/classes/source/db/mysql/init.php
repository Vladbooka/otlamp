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
 * Обмен данных с внешними источниками. Класс источника типа mysql
 *
 * @package    modlib
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_source_db_mysql extends dof_modlib_transmit_source_db
{
    /**
     * Поддержка импорта
     *
     * @return bool
     */
    public static function support_import()
    {
        return true;
    }
    
    /**
     * Поддержка экспорта
     *
     * @return bool
     */
    public static function support_export()
    {
        return false;
    }
    
    /** РАБОТА С БАЗОЙ ДАННЫХ **/
    
    /**
     * Получить SQL-запрос на наличие таблицы в БД
     *
     * @return string
     */
    protected function get_sql_table_exists()
    {
        return "SELECT 1 FROM {TABLENAME} LIMIT 1";
    }
    
    /**
     * Получить SQL-запрос на наличие полей в таблице БД
     *
     * @return string
     */
    protected function get_sql_fields_exists()
    {
        return 'SHOW COLUMNS FROM {TABLENAME}';
    }
    
    /**
     * Получить SQL-запрос на получение списка полей в таблице БД
     *
     * @return string
     */
    protected function get_sql_fields_list()
    {
        return 'SHOW COLUMNS FROM {TABLENAME}';
    }
    
    /**
     * Получить SQL-запрос на получение строки из таблицы БД
     *
     * @return string
     */
    protected function get_sql_data_element($rownumber)
    {
        list($conditions, $parameters) = $this->get_sql_conditions();
        
        $sql = "SELECT *
                    FROM {TABLENAME}
                   WHERE " . implode(' AND ', $conditions) . "
                ORDER BY id DESC
                   LIMIT " . (int)$rownumber . ", 1";
                
        return [$sql, $parameters];
    }
}