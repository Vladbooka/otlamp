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

namespace mods_data\attemptcompletion;

require_once($CFG->dirroot . '/report/mods_data/locallib.php');

defined('MOODLE_INTERNAL') || die;

abstract class attemptcompletion_base
{
    protected $attemptid;
    protected $course;
    protected $cm;
    protected $userid;
    protected $criteria;
    
    const GRADEPASS = 'gradepass';
    const COMPLETION = 'completion';
    const GRADEPASSPRIORITY = 'gradepasspriority';
    const COMPLETIONPRIORITY = 'completionpriority';
    
    public function __construct($attemptid, $course, $cm, $userid)
    {
        $this->criteria = get_config('report_mods_data', 'attempt_completion_critetia');
        $this->attemptid = $attemptid;
        $this->course = $course;
        $this->cm = $cm;
        $this->userid = $userid;
    }
    
    abstract public function get_attempt_completion();
}