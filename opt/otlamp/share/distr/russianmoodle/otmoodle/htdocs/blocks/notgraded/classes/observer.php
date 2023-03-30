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
 * Обозреватель событий для плагина block_notgraded
 * 
 * @package    block
 * @subpackage notgraded
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_notgraded;
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/blocks/notgraded/lib.php');

/**
 * Обработчик событий для block_notgraded
 */
class observer 
{
    
    /**
     * Задание. Работа представлена.
     * @param \mod_assign\event\assessable_submitted $event объект события
     */
    public static function mod_assign_assessable_submitted(\mod_assign\event\assessable_submitted $event)
    {
        global $DB;

        $cacheupdatemode = get_config('block_notgraded','cache_update_mode');
        if ( ! empty($cacheupdatemode) && (int)$cacheupdatemode==1 )
        {
            // Получение контекста контекст
            $context = \context::instance_by_id($event->contextid);
            
            // Получение объекта задания
            $assign = new \assign($context, false, false);
    
            // Получение настроек задания
            $assigninstance = $assign->get_instance();
            
            $course = $assign->get_course();
            if( ! empty($course) )
            {
                $bngc = new \block_notgraded_gradercache();
                $bngc->update_course_cache($course->id);
            }
        }
    }
    
    /**
     * Задание. Представленный ответ был оценен
     * @param \mod_assign\event\submission_graded $event объект события
     */
    public static function mod_assign_submission_graded(\mod_assign\event\submission_graded $event)
    {
        global $DB;

        $cacheupdatemode = get_config('block_notgraded','cache_update_mode');
        if ( ! empty($cacheupdatemode) && (int)$cacheupdatemode==1 )
        {
            // Получение контекста контекст
            $context = \context::instance_by_id($event->contextid);
            
            // Получение объекта задания
            $assign = new \assign($context, false, false);
    
            $course = $assign->get_course();
            if( ! empty($course) )
            {
                $bngc = new \block_notgraded_gradercache();
                $bngc->update_course_cache($course->id);
            }
        }
    }
    
    /**
     * Тест. Попытка завершена и отправлена на оценку
     * @param \mod_quiz\event\attempt_submitted $event объект события
     */
    public static function mod_quiz_attempt_submitted(\mod_quiz\event\attempt_submitted $event)
    {
        global $DB;

        $cacheupdatemode = get_config('block_notgraded','cache_update_mode');
        if ( ! empty($cacheupdatemode) && (int)$cacheupdatemode==1 )
        {
            $course  = $DB->get_record('course', array('id' => $event->courseid));
            $attemptrow = $event->get_record_snapshot('quiz_attempts', $event->objectid);
            $quizrow    = $event->get_record_snapshot('quiz', $attemptrow->quiz);
            $cm      = get_coursemodule_from_id('quiz', $event->get_context()->instanceid, $event->courseid);
    
            
            if ($course && $quizrow && $cm && $attemptrow)
            {
                $attempt = new \quiz_attempt($attemptrow, $quizrow, $cm, $course);
                $slots = $attempt->get_slots();
                foreach($slots as $slot)
                {
                    // $attempt->get_question_type_name($slot) == 'essay'
                    if( \question_engine::is_manual_grade_in_range($attempt->get_uniqueid(), $slot) )
                    {
                        $bngc = new \block_notgraded_gradercache();
                        $bngc->update_course_cache($course->id);
                        break;
                    }
                }
            }
        }
    }
    
    /**
     * Тест. Вопрос оценен вручную
     * @param \mod_quiz\event\question_manually_graded $event объект события
     */
    public static function mod_quiz_question_manually_graded(\mod_quiz\event\question_manually_graded $event)
    {
        global $DB;

        $cacheupdatemode = get_config('block_notgraded','cache_update_mode');
        if ( ! empty($cacheupdatemode) && (int)$cacheupdatemode==1 )
        {
            if( $DB->record_exists('course', [
                'id' => $event->courseid
            ]) )
            {
                $bngc = new \block_notgraded_gradercache();
                $bngc->update_course_cache($event->courseid);
            }
        }
    }
    


    /**
     * Ядро. Роль назначена
     * @param \core\event\role_assigned $event объект события
     */
    public static function core_role_assigned(\core\event\role_assigned $event)
    {
        global $DB;

        $cacheupdatemode = get_config('block_notgraded','cache_update_mode');
        
        if ( ! empty($cacheupdatemode) && (int)$cacheupdatemode==1 )
        {
            if( $DB->record_exists('user', [
                'id' => $event->relateduserid
            ]) )
            {
                $bngc = new \block_notgraded_gradercache($event->relateduserid);
                $bngc->update_cache();
            }
        }
    }

    /**
     * Ядро. Назначение роли снято
     * @param \core\event\role_unassigned $event объект события
     */
    public static function core_role_unassigned(\core\event\role_unassigned $event)
    {
        global $DB;
        
        $cacheupdatemode = get_config('block_notgraded','cache_update_mode');
        
        if ( ! empty($cacheupdatemode) && (int)$cacheupdatemode==1 )
        {
            if( $DB->record_exists('user', [
                'id' => $event->relateduserid
            ]) )
            {
                $bngc = new \block_notgraded_gradercache($event->relateduserid);
                $bngc->update_cache();
            }
        }
    }
}