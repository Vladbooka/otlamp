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
 * Блок Прогресс по курсу. Библиотека плагина.
 *
 * @package    block_userprogress
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/grade/querylib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/gradelib.php');

/** 
 * Возвращает объект с пользовательскими данными о выполнении в отслеживаемом курсе
 * 
 * @param int $userid
 * @param object $course
 * @return object|boolean объект с данными или false, в случае ошибки
 */
function get_user_completion($userid, $course) 
{
    $context = context_course::instance($course->id);

    

    // Получить критерии прохождения курса
    $completion = new completion_info($course);

    if ( ! $completion->has_criteria() ) 
    {// Курс не имеет критериев
        return false;
    }

    $criteria = array();

    foreach ( $completion->get_criteria(COMPLETION_CRITERIA_TYPE_COURSE) as $criterion ) 
    {
        $criteria[] = $criterion;
    }

    foreach ( $completion->get_criteria(COMPLETION_CRITERIA_TYPE_ACTIVITY) as $criterion ) 
    {
        $criteria[] = $criterion;
    }

    foreach ( $completion->get_criteria(COMPLETION_CRITERIA_TYPE_GRADE) as $criterion ) 
    {
        $criteria[] = $criterion;
    }

    foreach ( $completion->get_criteria() as $criterion ) 
    {
        if ( ! in_array($criterion->criteriatype, [
                COMPLETION_CRITERIA_TYPE_COURSE, COMPLETION_CRITERIA_TYPE_ACTIVITY, COMPLETION_CRITERIA_TYPE_GRADE]) ) 
        {
            $criteria[] = $criterion;
        }
    }

    if ( ! $completion->is_tracked_user($userid) ) 
    {
        return false;
    }
    
    // Progress for each course completion criteria
    $completedcriteria = 0;
    foreach ($criteria as $criterion) {
        $criteria_completion = $completion->get_user_completion($userid, $criterion);
        $is_complete = $criteria_completion->is_complete(); // Критерий выполнен?
        if ($is_complete) {
            $completedcriteria++;
        }
    }

    $percentcompleted = round(100 * $completedcriteria / count($criteria));
    $userdata = new StdClass;
    $userdata->percentcompleted = $percentcompleted;
    
    return $userdata;

}