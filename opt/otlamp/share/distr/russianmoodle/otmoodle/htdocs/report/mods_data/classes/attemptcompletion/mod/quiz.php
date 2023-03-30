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

namespace mods_data\attemptcompletion\mod;

require_once($CFG->dirroot . '/report/mods_data/classes/attemptcompletion/attemptcompletion_base.php');
require_once($CFG->dirroot . '/report/mods_data/locallib.php');
require_once($CFG->libdir . '/grade/grade_item.php');
require_once($CFG->libdir . '/grade/grade_grade.php');
require_once($CFG->libdir . '/grade/constants.php');

use mods_data\attemptcompletion\attemptcompletion_base;
use grade_item;
use grade_grade;

defined('MOODLE_INTERNAL') || die;

class quiz extends attemptcompletion_base
{
    protected $modname = 'quiz';
    
    public function get_attempt_completion()
    {
        $attempt = $this->get_attempt();
        $gradeitem = new grade_item([
            'iteminstance' => $attempt->quiz,
            'itemtype' => 'mod',
            'itemmodule' => $this->modname
        ]);
        $gradegrade = new grade_grade(['itemid' => $gradeitem->id, 'userid' => $attempt->userid]);
        // Ugly hack for redeclare finalgrade
        $gradegrade->finalgrade = $attempt->sumgrades;
        $is_passed = $gradegrade->is_passed($gradeitem);
        switch($this->criteria)
        {
            case self::GRADEPASS:
                if( $is_passed )
                {
                    return ATTEMPT_COMPLETION_COMPLETE;
                } else 
                {
                    return ATTEMPT_COMPLETION_INCOMPLETE;
                }
                break;
            case self::COMPLETION:
                return $this->get_attempt_completion_by_module_completion();
                break;
            case self::GRADEPASSPRIORITY:
                if( ! is_null($is_passed) )
                {
                    if( $is_passed )
                    {
                        return ATTEMPT_COMPLETION_COMPLETE;
                    } else
                    {
                        return ATTEMPT_COMPLETION_INCOMPLETE;
                    }
                } else 
                {
                    return $this->get_attempt_completion_by_module_completion();
                }
                break;
            case self::COMPLETIONPRIORITY:
                $completion = report_mods_data_get_completion($this->course, $this->cm, $this->userid);
                switch($completion)
                {
                    case COMPLETION_UNKNOWN:
                    case COMPLETION_IGNORE:
                        if( ! is_null($is_passed) )
                        {
                            if( $is_passed )
                            {
                                return ATTEMPT_COMPLETION_COMPLETE;
                            } else
                            {
                                return ATTEMPT_COMPLETION_INCOMPLETE;
                            }
                        } else
                        {
                            return ATTEMPT_COMPLETION_UNKNOWN;
                        }
                        break;
                    case COMPLETION_COMPLETE:
                    case COMPLETION_COMPLETE_PASS:
                        return ATTEMPT_COMPLETION_COMPLETE;
                        break;
                    case COMPLETION_INCOMPLETE:
                    case COMPLETION_COMPLETE_FAIL:
                        return ATTEMPT_COMPLETION_INCOMPLETE;
                        break;
                    default:
                        return ATTEMPT_COMPLETION_UNKNOWN;
                        break;
                }
                break;
            default:
                return ATTEMPT_COMPLETION_UNKNOWN;
                break;
        }
    }
    
    private function get_attempt()
    {
        global $DB;
        return $DB->get_record('quiz_attempts', ['id' => $this->attemptid]);
    }
    
    private function get_attempt_completion_by_module_completion()
    {
        $completion = report_mods_data_get_completion($this->course, $this->cm, $this->userid);
        switch($completion)
        {
            case COMPLETION_COMPLETE:
            case COMPLETION_COMPLETE_PASS:
                return ATTEMPT_COMPLETION_COMPLETE;
                break;
            case COMPLETION_UNKNOWN:
                return ATTEMPT_COMPLETION_UNKNOWN;
                break;
            case COMPLETION_IGNORE:
                return ATTEMPT_COMPLETION_IGNORE;
                break;
            case COMPLETION_INCOMPLETE:
            case COMPLETION_COMPLETE_FAIL:
            default:
                return ATTEMPT_COMPLETION_INCOMPLETE;
                break;
        }
    }
}