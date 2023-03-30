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

class attempt_mod_assign extends attempt_base
{
    protected $objecttable = 'assign';
    protected $modname = 'assign';
    protected $possiblefirstattemptnumber = 0;
    
    public function get_current_attemptnumber()
    {
        $submissions = $this->get_all_submissions();
        if( ! empty($submissions) )
        {
            $lastsubmission = array_pop($submissions);
            if( (bool)$lastsubmission->latest && in_array($lastsubmission->status, ['reopened', 'draft']) )
            {
                return (int)$lastsubmission->attemptnumber;
            }
        }
        return false;
    }
    
    public function get_last_attemptnumber()
    {
        $submissions = $this->get_all_submissions(['submitted']);
        if( ! empty($submissions) )
        {
            $lastsubmission = array_pop($submissions);
            if( $lastsubmission->status == 'submitted' )
            {
                return (int)$lastsubmission->attemptnumber;
            }
        }
        return false;
    }
    
    protected function get_all_submissions($statuses = [], $timecreated = null) {
        global $DB;
    
        $params = [];
        $select = '';
    
        if ($this->get_instance()->teamsubmission) {
            $groupid = 0;
            $group = $this->get_submission_group();
            if ($group) {
                $groupid = $group->id;
            }
    
            $select .= 'assignment=:assignment AND groupid=:groupid AND userid=:userid';
            // Params to get the group submissions.
            $params = [
                'assignment' => $this->get_instance()->id, 
                'groupid' => $groupid, 
                'userid' => 0
            ];
        } else {
            // Params to get the user submissions.
            $select .= 'assignment=:assignment AND userid=:userid';
            $params = [
                'assignment' => $this->get_instance()->id, 
                'userid' => $this->userid
            ];
        }
        
        if( ! empty($statuses) )
        {
            if( is_string($statuses) )
            {
                $statuses = [$statuses];
            }
            list($insql, $statusparams) = $DB->get_in_or_equal($statuses, SQL_PARAMS_NAMED);
            $select .= ' AND status ' . $insql;
            $params = array_merge($params, $statusparams);
        }
        
        if( ! is_null($timecreated) )
        {
            $select .= ' AND timecreated <= :timecreated';
            $params['timecreated'] = $timecreated;
        }
        
        // Return the submissions ordered by attempt.
        $submissions = $DB->get_records_select('assign_submission', $select, $params, 'attemptnumber ASC');
    
        return $submissions;
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
    
    protected function get_submission_group()
    {
        $groups = $this->get_all_groups();
        if (count($groups) != 1) {
            $return = false;
        } else {
            $return = array_pop($groups);
        }
    }
    
    protected function get_all_groups()
    {
        global $CFG;
        if( file_exists($CFG->libdir . '/grouplib.php') )
        {
            require_once($CFG->libdir . '/grouplib.php');
            $grouping = $this->get_instance()->teamsubmissiongroupingid;
            return groups_get_all_groups($this->course->id, $this->userid, $grouping);
        }
        return [];
    }
    
    public function get_attempt_linked_log($logtimecreated)
    {
        $attempts = $this->get_all_submissions([], $logtimecreated);
        if( ! empty($attempts) )
        {
            return array_pop($attempts)->attemptnumber;
        } else 
        {
            return $this->get_possible_first_attemptnumber();
        }
    }
}