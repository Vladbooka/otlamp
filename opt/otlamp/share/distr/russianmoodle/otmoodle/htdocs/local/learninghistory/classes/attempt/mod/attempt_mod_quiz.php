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

namespace local_learninghistory\attempt\mod;

use local_learninghistory\attempt\attempt_base;

class attempt_mod_quiz extends attempt_base
{
    protected $objecttable = 'quiz';
    protected $modname = 'quiz';
    protected $possiblefirstattemptnumber = 1;
    
    public function get_current_attemptnumber()
    {
        global $CFG;
        if( file_exists($CFG->dirroot . '/mod/quiz/lib.php') )
        {
            require_once($CFG->dirroot . '/mod/quiz/lib.php');
            $attempts = quiz_get_user_attempts($this->cm->instance, $this->userid, 'unfinished');
            if( ! empty($attempts) )
            {
                $currentattempt = array_pop($attempts);
                return (int)$currentattempt->attempt;
            }
        }
        return false;
    }
    
    public function get_last_attemptnumber()
    {
        global $CFG;
        if( file_exists($CFG->dirroot . '/mod/quiz/lib.php') )
        {
            require_once($CFG->dirroot . '/mod/quiz/lib.php');
            $attempts = quiz_get_user_attempts($this->cm->instance, $this->userid, 'all');
            if( ! empty($attempts) )
            {
                $currentattempt = array_pop($attempts);
                return (int)$currentattempt->attempt;
            }
        }
        return false;
    }
    
    public function get_instance()
    {
        global $DB;
        if( isset($this->instance) )
        {
            return $this->instance;
        }
        $params = ['id' => $this->cm->instance];
        $instance = $DB->get_record($this->objecttable, $params, '*', MUST_EXIST);
        $this->instance = $instance;
        return $instance;
    }
    
    public function get_attempt_linked_log($logtimecreated) {
        global $DB;
        
        $params = [
            'quizid' => $this->cm->instance,
            'userid' => $this->userid,
            'preview' => 0,
            'timestart' => $logtimecreated,
            'timefinish' => $logtimecreated
        ];
        $select = 'quiz = :quizid AND userid = :userid AND preview = :preview AND timestart <= :timestart AND (timefinish >= :timefinish OR timefinish = 0)';
    
        $attempts = $DB->get_records_select('quiz_attempts', $select, $params, 'quiz, attempt ASC');
        if( ! empty($attempts) )
        {
            return array_pop($attempts)->attempt;
        } else
        {// Лог попал между попытками
            return false;
        }
    }
}