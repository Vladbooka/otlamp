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
 * Блок объединения отчетов. Класс формирования данных по полям персоны деканата
 * 
 * @package    block
 * @subpackage reports_union
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/report/mods_data/locallib.php');

class report_mods_data_dofpersonfields
{
    private $personapi;
    private $personfields=[];
    /**
     * Конструктор
     */
    public function __construct() 
    {
        $this->personsapi = report_mods_data_get_dof_persons_api();

        if ( is_object($this->personsapi) )
        {
            $this->personfields = $this->personsapi->get_person_fieldnames();
        }
    }
    
    public function add_subreport_headers($uniondata, &$report)
    {
        if ( ! empty($uniondata) )
        {
            $report['header1']['dofpersonfields'] = [
                'name' => get_string('dofpersonfields_title', 'report_mods_data')
            ];
            
            foreach ( $uniondata as $fieldname => $val )
            {// Добавление названий полей
                // Получение полей персоны
                if(isset($this->personfields[$fieldname]))
                {
                    $report['header2']['dofpersonfields'][$fieldname] = $this->personfields[$fieldname];
                }
            }
        }
    }
    
    public function add_userdata(&$data, &$uniondata, $userid)
    {
        if ( is_object($this->personsapi) )
        {
            // Получение данных персоны из Деканата
            $dofpersonfields = $this->personsapi->get_bu($userid);
            if ( ! empty($dofpersonfields) && is_object($dofpersonfields) )
            {// Получены данные по пользователю
                // Добавление полей в результирующий массив
                $data = array_intersect_key((array)$dofpersonfields, $uniondata);
            }
        }
    }
}