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
 * Модуль Логика курса. Класс задачи.
 *
 * Задача проверка состояния элементов курса
 * 
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otcourselogic\task;

global $CFG;
require_once($CFG->dirroot. '/mod/otcourselogic/classes/state_checker.php');

class state_checking extends \core\task\scheduled_task 
{
    /**
     * Получить имя задачи
     *
     * @return string
     */
    public function get_name() 
    {
        return get_string('task_state_checking_title', 'mod_otcourselogic');
    }

    /**
     * Исполнение задачи
     */
    public function execute() 
    {
        global $DB;
        $sc = new \mod_otcourselogic\state_checker();
        $instanceids = $courseids = $courseenrolledusers = $array = [];
        // Получаем все логики курса, с которыми необходимо работать (видимые, с включенной периодической проверкой)
        $sql = 'SELECT ocl.* 
                FROM {otcourselogic} ocl
                JOIN {course_modules} cm
                ON ocl.id=cm.instance
                JOIN {modules} m
                ON cm.module=m.id
                WHERE m.name=\'otcourselogic\' AND cm.visible=1 AND ocl.checkperiod IS NOT NULL';
        $ocls = $DB->get_records_sql($sql);
        // Получаем все имеющиеся состояния пользователей, которые необходимо проверить
        $sql = 'SELECT ot_s.id, ot_s.instanceid, ot_s.userid, ot.course FROM {otcourselogic_state} ot_s JOIN {otcourselogic} ot ON ot.id=ot_s.instanceid WHERE (ot_s.lastcheck+ot.checkperiod)<'.time();
        $row = $DB->get_records_sql($sql);
        foreach($row as $v)
        {// Из полученных состояний пользоователей собираем нужные нам для работы массивы
            $instanceids[$v->instanceid][] = $v->userid;
            $courseids[$v->instanceid] = $v->course;
        }
        ////////////////////////////////////////////////////////////////////
        ///   В этот блок попадут те пользователи, которые добавлены     ///
        ///   после создания логики курса (состояния которых добавлены   ///
        ///   при обработке события назначения роли пользователю)        ///
        ////////////////////////////////////////////////////////////////////
        foreach($instanceids as $instanceid => $userids)
        {// Для каждой логики курса из полученных состояний
            $cm = get_coursemodule_from_instance('otcourselogic', $instanceid, $courseids[$instanceid]);
            $instance = $DB->get_record('otcourselogic', ['id' => $instanceid]);
            if ( ! empty($instance->protect) && empty($cm->availability) )
            {
                // Защита от случайных срабатываний
                continue;
            }
            if( ! array_key_exists($courseids[$instanceid], $courseenrolledusers) )
            {// Получим подписанных на курс пользователей, делаем запрос 1 раз для курса
                $courseenrolledusers[$courseids[$instanceid]] = get_enrolled_users(\context_course::instance($courseids[$instanceid]));
            }
            // В каждом курсе соберем массив с записанными пользователями (фактически массив всех возможных состояний)
            $array[$courseids[$instanceid]][$instanceid] = new \stdClass();
            $array[$courseids[$instanceid]][$instanceid]->enrolledusers = $courseenrolledusers[$courseids[$instanceid]];
            if ( ! empty($cm->visible) )
            {// Если логика курса не скрыта
                foreach($userids as $userid)
                {// Для каждого состояния запустим проверку статуса
                    $sc->check_cm_user($instanceid, $userid);
                    // Уберем из собранного выше массива всех возможных состояний только что проверенное состояние
                    unset($array[$courseids[$instanceid]][$instanceid]->enrolledusers[$userid]);
                }
                if( empty($array[$courseids[$instanceid]][$instanceid]->enrolledusers) )
                {// Если все возможные состояния для данной логики курса удалены - уберем эту ячейку из массива
                    unset($array[$courseids[$instanceid]][$instanceid]);
                    if( empty($array[$courseids[$instanceid]]) )
                    {// Если в данном курсе не осталось возможных состояний ни для одной логики курса - удалим эту ячейку из массива
                        unset($array[$courseids[$instanceid]]);
                    }
                }
            }
            // Уберем текущую логику курса из массива для дальнейшей проверки
            unset($ocls[$instanceid]);
        }
        ////////////////////////////////////////////////////////////////////
        ///   В этот блок попадут оставшиеся пользователи, у которых     ///
        ///   еще нет состояния                                          ///
        ////////////////////////////////////////////////////////////////////
        if( ! empty($array) )
        {// Если массив возможных состояний остался не пуст
            foreach($array as $courseid => $course)
            {
                if( ! empty($course) )
                {
                    foreach($course as $instanceid => $instance)
                    {
                        $cm = get_coursemodule_from_instance('otcourselogic', $instanceid, $courseid);
                        $oclinstance = $DB->get_record('otcourselogic', ['id' => $instanceid]);
                        if ( ! empty($oclinstance->protect) && empty($cm->availability) )
                        {
                            // Защита от случайных срабатываний
                            continue;
                        }
                        if( ! empty($instance->enrolledusers) && ! empty($cm->visible) )
                        {
                            foreach($instance->enrolledusers as $user)
                            {// Запустим проверку оставшихся возможных состояний
                                $sc->check_cm_user($instanceid, $user->id);
                            }
                        }
                    }
                }
                
            }
        }
        //////////////////////////////////////////////////////////////////////
        ///   В этот блок попадут логики, у которых вообще нет состояний   ///
        ///   и которые нужно проверить                                    ///
        //////////////////////////////////////////////////////////////////////
        if( ! empty($ocls) )
        {
            foreach($ocls as $ocl)
            {
                $cm = get_coursemodule_from_instance('otcourselogic', $ocl->id, $ocl->course);
                if( ! array_key_exists($ocl->course, $courseenrolledusers) )
                {
                    $courseenrolledusers[$ocl->course] = get_enrolled_users(\context_course::instance($ocl->course));
                }
                if ( ! empty($ocl->protect) && empty($cm->availability) )
                {
                    continue;
                }
                foreach($courseenrolledusers[$ocl->course] as $user)
                {
                    $sc->check_cm_user($ocl->id, $user->id);
                }
            }
            
        }
    }
}