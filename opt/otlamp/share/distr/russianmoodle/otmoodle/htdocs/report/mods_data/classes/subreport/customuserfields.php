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
 * Блок объединения отчетов. Класс формирования данных по кастомным пользовательским полям
*
* @package    block
* @subpackage reports_union
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

require_once($CFG->dirroot.'/report/mods_data/locallib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

defined('MOODLE_INTERNAL') || die();

class report_mods_data_customuserfields
{
    /**
     * Объект dof
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Конструктор
     */
    public function __construct() 
    {
        $this->dof = report_mods_data_get_dof();
    }

    public function add_subreport_headers($uniondata, &$report)
    {
        if ( ! empty($uniondata) )
        {
            $report['header1']['customuserfields'] = [
                'name' => get_string('customuserfields_title', 'report_mods_data')
            ];
            foreach ( $uniondata as $fieldname => $val )
            {// Добавление названий полей
                if( is_null($this->dof) )
                {
                    continue;
                } else 
                {
                    $field = $this->dof->modlib('ama')->user(false)->get_user_custom_field($fieldname);
                    $report['header2']['customuserfields'][$fieldname] = $field->name;
                }
            }
        }
    }

    public function add_userdata(&$data, &$uniondata, $userid)
    {
        global $DB;
        // Получение пользовательских полей
        $customuserfields = profile_user_record($userid);
        /**
         * @todo Сделать обработку поля типа datetime
         */
        if ( ! empty($customuserfields) && is_object($customuserfields) )
        {// Получены данные по пользователю
            // Добавление полей в результирующий массив
            $data = array_intersect_key((array)$customuserfields, $uniondata);
        }
    }
}