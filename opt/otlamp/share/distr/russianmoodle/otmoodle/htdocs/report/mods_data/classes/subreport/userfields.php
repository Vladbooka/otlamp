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
 * Блок объединения отчетов. Класс формирования данных по пользовательским полям
 * 
 * @package    block
 * @subpackage reports_union
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class report_mods_data_userfields
{
    /**
     * Конструктор
     */
    public function __construct() {}
    
    public function add_subreport_headers($uniondata, &$report)
    {
        if ( ! empty($uniondata) )
        {
            $report['header1']['userfields'] = [
                'name' => get_string('userfields_title', 'report_mods_data')
            ];
            foreach ( $uniondata as $fieldname => $val )
            {// Добавление названий полей
                $ufname = get_user_field_name($fieldname);
                $report['header2']['userfields'][$fieldname] = $ufname;
            }
        }
    }

    public function add_userdata(&$data, &$uniondata, $userid)
    {
        global $DB;
        // Получение пользовательских полей
        $userfields = $DB->get_record('user', ['id' => $userid]);
        if ( ! empty($userfields) && is_object($userfields) )
        {// Получены данные по пользователю
            // Добавление полей в результирующий массив
            $data = array_intersect_key((array)$userfields, $uniondata);
        }
    }
}