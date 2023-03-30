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
 * Вебсервис журнала
 *
 * @package    im
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../lib.php');
require_once($CFG->libdir . '/weblib.php');
require_once($DOF->plugin_path('im','journal','/group_journal/libform.php'));

class dof_external_api_plugin extends dof_external_api_plugin_base
{
    /**
     * Получение html кода журнала оценок
     *
     * @param int $cstream_id
     * @param array $addvars
     *
     * @return string - html код журнала
     */
    public static function get_grades_table($cstream_id = null, $addvars = [])
    {
        GLOBAL $DOF;
        
        $html = '';
        
        // Для подключения классов таблиц
        $DOF->im('journal');
        
        $grades_table = new dof_im_journal_tablecstreamgrades($DOF, $cstream_id, $addvars);
        
        $html .= $grades_table->render();
        
        return $html;
    }
    
    /**
     * Получение html кода журнала занятий
     *
     * @param int $cstream_id
     * @param array $addvars
     *
     * @return string - html код
     */
    public static function get_themplans_table($cstream_id = null, $addvars = [])
    {
        GLOBAL $DOF;
        
        $html = '';
        
        // Для подключения классов таблиц
        $DOF->im('journal');
        
        $themplans = new dof_im_journal_tabletemplans($DOF, $cstream_id, $addvars);
        
        $html .= $themplans->render();
        
        return $html;
    }
    
    /**
     * Cохранение оценок
     *
     * @param int $cstream
     * @param int $department
     * @param array $grades
     *
     * @return bool - статус
     */
    public static function save_grades($cstream = null, $plan = null, $department = null, $grades = [])
    {
        GLOBAL $DOF;
        
        $status = false;

        if ( ! empty($grades) && ! empty($cstream) )
        {
            if ( $DOF->modlib('journal')
                ->get_manager('lessonprocess')
                ->save_students_grades($cstream, $plan, $department, $grades) )
            {
                $status = true;
            }
        }
        
        return $status;
    }
    
    /**
     * Cохранение отметок о присутствии (перекличка)
     *
     * @param int $cstream
     * @param int $event
     * @param int $department
     * @param array $presence
     *
     * @return bool - статус
     */
    public static function save_presence($eventid = null, $presencedata = [], $department = null)
    {
        GLOBAL $DOF;
        
        $result = true;

        if( is_array($presencedata) && ! empty($eventid) )
        {
            $schpresences = [];
            foreach($presencedata as $presenceinfo)
            {
                $schpresence = new stdClass();
                $schpresence->eventid = $eventid;
                $schpresence->personid = $presenceinfo['personid'];
                $schpresence->present = $presenceinfo['present'];
                if( (bool)$presenceinfo['present'] )
                {// учащийся присутствовал, причины присутствия не указываются
                    // не сбрасываем для отсутствующих, так как причина могла быть указана ранее через другую форму
                    $schpresence->reasonid = 0;
                }
                $schpresences[$presenceinfo['personid']] = $schpresence;
            }
            
            if( ! empty($schpresences) )
            {
                // Сохранение посещаемости
                $result = $DOF->modlib('journal')
                    ->get_manager('lessonprocess')
                    ->save_students_presence($eventid, $schpresences, $department);
                
                // Смена статуса
                $result = $DOF->modlib('journal')
                    ->get_manager('lessonprocess')
                    ->schevent_complete($eventid, $department) && $result;
            }
        }
        return $result;
    }
    
    /**
     * Cохранение планов
     *
     * @param int $plan
     * @param string $passed
     * @param string $homework
     * @param string $homeworktime
     * 
     * @return bool - статус
     */
    public static function save_plan($plan = null, $passed = null, $homework = null, $homeworktime = null)
    {
        // Нормализация
        if ( empty($plan) )
        {
            return false;
        }
        
        GLOBAL $DOF;
        
        $status = false;
        
        // Данные
        $data = new stdClass();
        if ( ! empty($passed) )
        {
            $data->name = $passed;
        }
        if ( ! empty($homework) )
        {
            $data->homework = $homework;
        }
        if ( ! empty($homeworktime) )
        {
            $data->homeworkhours = $homeworktime;
        }
        
        if ( $DOF->modlib('journal')
                ->get_manager('lessonprocess')
                ->save_plan($plan, $data) )
        {
            $status = true;
        }
        
        return $status;
    }
    
    /**
     * Получение списка категорий 
     * 
     * @return array
     */
    public static function get_coursecats()
    {
        global $DOF;
        
        $result = [];
        
        $coursecats = $DOF->modlib('ama')->category(false)->search_by_name();
        if (!empty($coursecats))
        {
            foreach($coursecats as $coursecat)
            {
                $result[$coursecat->id] = $coursecat->name;
            }
        }
        return $result;
    }
    
    /**
     * Получение списка курсов по категории
     *
     * @return array
     */
    public static function get_courses($coursecatid)
    {
        global $DOF;
        
        $result = [];
        
        $courses = $DOF->modlib('ama')->category($coursecatid)->get_courses();
        if ( ! empty($courses) )
        {
            foreach($courses as $course)
            {
                $result[$course->id] = $course->fullname;
            }
        }
        
        return $result;
    }
    
    /**
     * Получение грейд итемов курса
     * 
     * @param int $courseid
     * 
     * @return array
     */
    public static function get_gradeitems($courseid)
    {
        global $DOF;
        return $DOF->modlib('journal')->get_manager('lessonprocess')->get_gradeitems_for_lesson($courseid);
    }
    
    /**
     * Смена доступности к элементу
     * 
     * @param int $cstreamid
     * @param int $planid
     * @param bool $newstatus
     * 
     * @return bool
     */
    public static function change_access_to_mdlgradeitem($cstreamid, $planid, $newstatus)
    {
        if ( empty($cstreamid) || empty($planid) )
        {
            return false;
        }
        global $DOF;
        return $DOF->modlib('journal')->get_manager('lessonprocess')->save_students_access_to_mldarea($cstreamid, $planid, [], $newstatus);
    }
    
    /**
     * Смена доступности к элементу
     *
     * @param int $cstreamid
     * @param int $planid
     * @param bool $newstatus
     *
     * @return bool
     */
    public static function change_user_access_to_mdlgradeitem($cstreamid, $planid, $newstatus, $cpassedid)
    {
        if ( empty($cstreamid) || empty($planid) )
        {
            return false;
        }
        global $DOF;
        $cpassed = $DOF->storage('cpassed')->get_record(['id' => $cpassedid]);
        if ( empty($cpassed) )
        {
            return false;
        }
        return $DOF->modlib('journal')->get_manager('lessonprocess')->save_students_access_to_mldarea($cstreamid, $planid, [$cpassed], $newstatus);
    }
    
    /**
     * Принудительная синхронизации оценок КТ с оценками оцениваемого элемента Moodle
     * 
     * @param int $planid
     */
    public static function force_plan_sync($planid)
    {
        if ( empty($planid) )
        {
            return false;
        }
        // проверка права на выставление оценок
        global $DOF;
        return $DOF->modlib('journal')->get_manager('lessonprocess')->sync_plan_grades($planid);
    }
}
