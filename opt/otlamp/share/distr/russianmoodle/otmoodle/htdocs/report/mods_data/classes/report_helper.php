<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Сводка по пользователям. Класс хелпера.
 *
 * @package    report
 * @subpackage ot_usersoverview
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_mods_data;

require_once($CFG->dirroot . '/report/mods_data/locallib.php');
require_once($CFG->dirroot . '/report/mods_data/classes/subreport/userfields.php');
require_once($CFG->dirroot . '/report/mods_data/classes/subreport/customuserfields.php');
require_once($CFG->dirroot . '/report/mods_data/classes/subreport/dofpersonfields.php');
require_once($CFG->dirroot . '/report/mods_data/classes/report.php');
require_once($CFG->dirroot . '/report/mods_data/classes/subreport/modules/feedback.php');
require_once($CFG->dirroot . '/report/mods_data/classes/subreport/modules/quiz.php');

use cache;

class report_helper 
{
    /**
     * Объект dof
     * @var dof_control
     */
    protected static $dof;
    /**
     * Получение данных
     * 
     * @param string $field
     */
    public static function get_data($field = '')
    {
        global $DB, $CFG;
        
        $key = 'fullreportdata';
        
        // поиск в кеше готового отчета
        $reportdatacache = cache::make('report_mods_data', 'fullreportdata');
        $reportdata = $reportdatacache->get($key);
        if ( ! empty($reportdata) )
        {
            return $reportdata;
        }
        
        // массив с данными для отчета
        $rdata = [
            // Данные по пользователям
            'users' => [],
            // Заголовок первого уровня с названиями групп полей (названия модулей)
            'header1' => [],
            // Заголовок второго уровня с названиями полей
            'header2' => []
        ];
        
        $uniondata = self::get_full_uniondata();
        // Подключение класса суботчета по пользовательским полям
        $userfieldsmanager = new \report_mods_data_userfields();
        // Добавление данных в отчет
        $userfieldsmanager->add_subreport_headers($uniondata['userfields'], $rdata);
        
        // Подключение класса суботчета по кастомным пользовательским полям
        $customuserfieldsmanager = new \report_mods_data_customuserfields();
        // Добавление данных в отчет
        $customuserfieldsmanager->add_subreport_headers($uniondata['customuserfields'], $rdata);
        
        // Подключение класса суботчета по полям персоны деканата
        $dofpersonfieldsmanager = new \report_mods_data_dofpersonfields();
        // Добавление данных в отчет
        $dofpersonfieldsmanager->add_subreport_headers($uniondata['dofpersonfields'], $rdata);
        
        $report = new \report_mods_data_report($uniondata);
        
        // Получить поддерживаемые модули
        $supported_modules = report_mods_data_get_supported_modules();
        
        $courses = get_courses();
        foreach($courses as $course)
        {
            $course_info = get_fast_modinfo($course->id);
            foreach($supported_modules as $module)
            {
                if ( ! isset($module['code']) )
                {// Код модуля не передан
                    continue;
                }
                // Получение экземпляров модуля
                $instances = $course_info->get_instances_of($module['code']);
                if( ! empty($instances) )
                {// Экземпляры найдены
                    foreach($instances as $id => $cm_info)
                    {
                        $cm = $cm_info->get_course_module_record(true);
                        $modulepath = $CFG->dirroot . '/report/mods_data/classes/subreport/modules/' . $cm->modname . '.php';
                        if( file_exists( $modulepath ) )
                        {
                            require_once ($modulepath);
                            $subreportclass = 'report_mods_data_' . $cm->modname;
                            if( class_exists($subreportclass) )
                            { // Подключение класса сбора отчета
                                $subreportmanager = new $subreportclass();
                                if( method_exists($subreportmanager, 'add_subreport') )
                                { // Добавление данных отчета
                                    foreach($report->get_supported_formats() as $format)
                                    {
                                        if( ! isset($reportdata[$course->id][$cm->id][$format]) )
                                        {
                                            $reportdata[$course->id][$cm->id][$format] = $rdata;
                                        }
                                        $subreportmanager->add_subreport($cm->id, $reportdata[$course->id][$cm->id][$format], $format);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // установка кеша готового отчета
        $reportdatacache->set($key, $reportdata);
        
        return $reportdata;
    }
    
    /**
     * Сброс кеша
     * 
     * @return void
     */
    public static function purgecaches()
    {
        $cache = cache::make('report_mods_data', 'fullreportdata');
        $cache->purge();
    }
    
    /**
     * Сбор данных в кеш
     * 
     * @return void
     */
    public static function collectcache()
    {
        // сбор по всем значениям сразу
        self::get_data();
    }
    
    public static function get_full_uniondata()
    {
        $userfields = [
            'confirmed' => 1,
            'suspended' => 1,
            'username' => 1,
            'idnumber' => 1,
            'firstname' => 1,
            'lastname' => 1,
            'email' => 1,
            'icq' => 1,
            'skype' => 1,
            'yahoo' => 1,
            'aim' => 1,
            'msn' => 1,
            'phone1' => 1,
            'phone2' => 1,
            'institution' => 1,
            'department' => 1,
            'address' => 1,
            'city' => 1,
            'country' => 1,
            'timezone' => 1,
            'lastip' => 1,
            'lastnamephonetic' => 1,
            'firstnamephonetic' => 1,
            'middlename' => 1,
            'alternatename' => 1
        ];
        
        $customuserfields = [];
        self::set_dof();
        if( ! is_null(self::$dof) )
        {
            $customfields = self::$dof->modlib('ama')->user(false)->get_user_custom_fields();
        }
        if( ! empty($customfields) )
        {
            foreach($customfields as $customfield)
            {
                $customuserfields[$customfield->shortname] = 1;
            }
        }
        
        $dofpersonfields = [];
        // Получение API работы с Деканатом
        $personsapi = report_mods_data_get_dof_persons_api();
        if ( ! empty($personsapi) )
        {// API доступно
            // Получение полей персоны
            $personfields = $personsapi->get_person_fieldnames();
            
            // Удаление системных полей
            unset($personfields['id']);
            unset($personfields['sortname']);
            unset($personfields['mdluser']);
            unset($personfields['sync2moodle']);
            unset($personfields['addressid']);
            unset($personfields['status']);
            unset($personfields['adddate']);
            unset($personfields['passportaddrid']);
            unset($personfields['birthaddressid']);
            unset($personfields['departmentid']);
        }
        
        if ( ! empty($personfields) )
        {// Есть поля персоны
            foreach ( $personfields as $personfield => $personfieldname )
            {
                $dofpersonfields[$personfield] = 1;
            }
        }
        
        return [
            'userfields'       => $userfields, 
            'customuserfields' => $customuserfields, 
            'dofpersonfields'  => $dofpersonfields
        ];
    }
    
    protected static function set_dof()
    {
        self::$dof = report_mods_data_get_dof();
    }
}