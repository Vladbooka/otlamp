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
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learninghistory\local;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use completion_info;
use context_course;

require_once($CFG->libdir . '/grouplib.php');
//require_once($CFG->dirroot . '/grade/querylib.php');
//require_once($CFG->libdir . '/completionlib.php');
//require_once($CFG->libdir . '/gradelib.php');


/**
 * Класс для выполнения отслеживания по пользователю
 */
class completion_tracker extends completion_info {

    public $course_id = null;
    protected $course = null;
    protected $criteria = null;
    protected $criteriatypes = null;
    
    function __construct($course) {
        $this->course = $course;
        parent::__construct($course);
        $this->set_criteriatypes();
    }
    
    /**
     * Получить заданные типы критериев
     * 
     * @return array
     */
    public function get_criteriatypes() {
        return $this->criteriatypes;
    }
    
    /**
     * Задать типы отслеживаемых критериев (по умолчанию все)
     * 
     * @param array $types - типы, смотреть константы COMPLETION_CRITERIA_TYPE_%
     */
    public function set_criteriatypes($types = array()) {
        if (empty($types) || !is_array($types)) {
            $this->criteriatypes = array(
                COMPLETION_CRITERIA_TYPE_ACTIVITY,
                COMPLETION_CRITERIA_TYPE_COURSE,
                COMPLETION_CRITERIA_TYPE_DATE,
                COMPLETION_CRITERIA_TYPE_DURATION,
                COMPLETION_CRITERIA_TYPE_GRADE,
                COMPLETION_CRITERIA_TYPE_ROLE,
                COMPLETION_CRITERIA_TYPE_SELF,
                COMPLETION_CRITERIA_TYPE_UNENROL
            );
        } else {
            $this->criteriatypes = $types;
        }
    }
    
    /**
     * Получить список всех критериев выполнения
     * 
     * @return array список всех критериев выполнения
     */
    public function get_all_criteria() {
        // Get criteria and put in correct order
        if ($this->criteria !== null) {
            return $this->criteria;
        }
        $criteria = array();
        foreach ($this->criteriatypes as $type) {
            foreach ($this->get_criteria($type) as $criterion) {
                $criteria[] = $criterion;
            }
        }

        foreach ($this->get_criteria() as $criterion) {
            if (!in_array($criterion->criteriatype, $this->criteriatypes)) {
                $criteria[] = $criterion;
            }
        }
        return $this->criteria = $criteria;
    }
    
    /**
     * Получить данные о прохождении курса пользователем
     * 
     * @param int $userid - id из таблицы user
     * @return StdClass|boolean - объект с полями или false, если пользователь не отслеживается
     * ->criteriacount - количество критериев
     * ->percentcompleted - количество процентов выполненного (округляется до целых)
     * ->completedcriteria - количество пройденных критериев
     */
    public function get_user_completion_all($userid) {
        // Проверка студента на отслеживаемые роли в курсе
        if (!$this->is_tracked_user($userid)) {
            return false;
        }
        
        $completedcriteria = 0;
        $criteria = $this->get_all_criteria();
        foreach ($criteria as $criterion) {
            $criteriacompletion = $this->get_user_completion($userid, $criterion);
            $iscomplete = $criteriacompletion->is_complete();
            // Критерий выполнен?
            if ($iscomplete) {
                $completedcriteria++;
            }
        }
        $userdata = new stdClass();
        $userdata->criteriacount = count($criteria);
        $percentcompleted = 0;
        if (!empty($userdata->criteriacount)) {
            $percentcompleted = round(100 * $completedcriteria / $userdata->criteriacount);
        }
        $userdata->percentcompleted = $percentcompleted;
        $userdata->completedcriteria = $completedcriteria;
        return $userdata;
        
    }

}