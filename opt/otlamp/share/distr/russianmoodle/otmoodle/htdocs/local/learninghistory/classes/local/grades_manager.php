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
 * Менеджер пользовательских оценок для плагина local_learninghistory
 * 
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learninghistory\local;

require_once($CFG->libdir . '/grade/constants.php');
require_once($CFG->libdir . '/grade/grade_grade.php');
require_once($CFG->libdir . '/grade/grade_item.php');

use grade_grade;
use grade_item;

defined('MOODLE_INTERNAL') || die();

/**
 * Класс для вычисления оценок пользователя в курсе (текущих, истории)
 */
class grades_manager {
    
    /**
     * Номер курса главной страницы
     * @var int 
     */
    private $siteid;
    
    public function __construct() {
        global $SITE;
        $this->siteid = $SITE->id;
        
    }
    
    /**
     * Получить текущие оценки по курсу
     * 
     * @param type $course
     */
    public static function get_actual_grades($courseid, $userid = 0) {
        global $DB;
        if (empty($userid)) {
            global $USER;
            $userid = $USER->id;
        }
        $gradeparams = [
            'courseid' => $courseid,
        ];
        $gradeitems = $DB->get_records('grade_items', $gradeparams);
        if (empty($gradeitems)) {
            return false;
        }
        foreach ($gradeitems as $id => $gradeitem) {

            $gradeparams = ['itemid'=>$gradeitem->id,'userid'=>$userid];
            if (!$gradegrade = grade_grade::fetch($gradeparams)) {
                $gradegrade = new grade_grade();
                $gradegrade->userid = $userid;
                $gradegrade->itemid = $gradeitem->id;
            }

            $gradegrade->load_grade_item();
            $gradeval = $gradegrade->finalgrade;
            if ($gradeval !== false) {
                $gradeitems[$id]->finalgrade = $gradeval;
            } else {
                $gradeitems[$id]->finalgrade = null;
            }
        }
        return $gradeitems;
    }

    /**
     * Получить историю оценок
     * 
     * @param type $course
     */
    protected function get_history_grades($course, $userid = 0) {
    }

    /**
     * Получить итоговую оценку пользователя по курсу
     * 
     * @param unknown $courseid
     * @param string $userid
     * @param array $options - дополнительные опции:
     *        allowhistory - Дополнительный поиск по истории оценок
     *          false - по умолчанию, поиск только по текущим оценкам
     *          true - расширение зоны поиска итоговой оценки до истории
     *        startdate - Начальная дата, с которой необходимо производить поиск
     *          int - timestamp , левая граница интервала
     *                             
     *                           
     * @return false - при ошибке, 
     *         NULL  - если оценка не найдена
     *         float - итоговая оценка пользователя по курсу
     */
    public static function get_user_finalgrade($courseid, $userid = NULL, $options = array() ) 
    {
        global $DB;
        // Переопределим пользователя как текущего , если не передан целевой
        if (empty($userid)) 
        {
            global $USER;
            $userid = $USER->id;
        }
        
        // Обработка дополнительных опций
        if ( ! isset($options['allowhistory']) )
        {// Значение не установлено
            $options['allowhistory'] = false;
        }
        
        // Параметры поиска в оценках
        $gradeparams = [
            'courseid' => $courseid,
            'itemtype' => 'course'
        ];
        // Получим оцениваемый курс
        $gradeitem = $DB->get_record('grade_items', $gradeparams);
        if ( ! empty($gradeitem) ) 
        {// найден курс
            // Получим оценку по пользователю в курсе
            if ( ! $gradegrade = grade_grade::fetch( array(
                    'itemid' => $gradeitem->id, 
                    'userid' => $userid
            ))) 
            {// Не смогли получить данные
                $gradegrade = new grade_grade();
                $gradegrade->userid = $userid;
                $gradegrade->itemid = $gradeitem->id;
            }
            
            // Получим оценку
            $gradegrade->load_grade_item();
            // Определим итоговую оценку
            $gradeval = $gradegrade->finalgrade;
            
            if ( isset($gradeval) && is_string($gradeval) ) 
            {// Итоговая оценка имеется
                return floatval($gradeval);
            }
        }
        
        if ( $options['allowhistory'] )
        {//ищем в истории, если установлена настройка
            // Получим оцениваемый курс
            //@todo: проверить логику. Необходимо ли здесь получить какую-то конкретную запись
            $gradeitemhistory = $DB->get_record('grade_items_history', $gradeparams ,'*', IGNORE_MULTIPLE);
            if ( ! empty($gradeitemhistory) )
            {// найден курс
                $select = '';
                $params['itemid'] = $gradeitemhistory->oldid;
                $select .= ' itemid = :itemid ';
                $params['userid'] = $userid;
                $select .= ' AND userid = :userid ';
                if ( isset($options['startdate']) )
                {
                    $params['timemodified'] = $options['startdate'];
                    $select .= ' AND timemodified > :timemodified ';
                }
                $history = $DB->get_records_select('grade_grades_history', $select, $params, 'timemodified DESC');
                foreach( $history as $item )
                {
                    if ( is_string($item->finalgrade) )
                    {
                        return floatval($item->finalgrade);
                    }
                }
            }
        }
        
        // Не нашли данных - оценки нет
        return NULL;
    }
    
    public static function get_course_finalgrade($course, $userid = 0) {
        global $DB;
        if (empty($userid)) {
            global $USER;
            $userid = $USER->id;
        }
        $gradeparams = [
                'courseid' => $course->id,
                'itemtype' => 'course'
        ];
        $gradeitem = $DB->get_record('grade_items', $gradeparams);
        if (empty($gradeitem)) {
            return false;
        }
        if (! $gradegrade = grade_grade::fetch(array('itemid'=>$gradeitem->id,'userid'=>$userid))) {
            $gradegrade = new grade_grade();
            $gradegrade->userid = $userid;
            $gradegrade->itemid = $gradeitem->id;
        }
    
        $gradegrade->load_grade_item();
        $gradeval = $gradegrade->finalgrade;
        if ($gradeval !== false) {
            return $gradeval;
        }
        return false;
    
    }
    
    /**
     * Получение максимальной оценки за курс
     * 
     * @param int $courseid
     * @param number $userid
     * 
     * @return number
     */
    public static function get_max_course_finalgrade($courseid) 
    {
        global $DB;
        $gradeparams = [
            'courseid' => $courseid,
            'itemtype' => 'course'
        ];
        if ( $gradeitem = $DB->get_record('grade_items', $gradeparams) )
        {
            return $gradeitem->grademax;
        }
        
        return false;
    }
}
