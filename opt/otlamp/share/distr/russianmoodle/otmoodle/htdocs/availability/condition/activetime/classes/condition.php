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
 * Condition main class.
 *
 * @package    availability_activetime
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_activetime;

use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Condition main class.
 *
 * @package availability_duration
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition {
    
    /**
     * Опции для поля duration
     * @var unknown
     */
    protected $timeoptions;
    
    /**
     * Максимальное значение времени обучения в курсе (в единицах)
     * @var unknown
     */
    protected $max;
    
    /**
     * Минимальное значение времени обучения в курсе (в единицах)
     * @var unknown
     */
    protected $min;
    
    /**
     * Единицы измерения максимального значения времени обучения в курсе
     * @var unknown
     */
    protected $time_max;
    
    /**
     * Единицы измерения минимального значения времени обучения в курсе
     * @var unknown
     */
    protected $time_min;
    
    /**
     * Флаг ограничения доступа (разрешено/запрещено)
     * @var unknown
     */
    protected $allow;
 
    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode
     * @throws \coding_exception If invalid data structure.
     */
    public function __construct($structure) {
        $this->type = $structure->type;

        $this->timeoptions = [
            1 => get_string('sec'),
            60 => get_string('min'),
            3600 => get_string('hours'),
            86400 => get_string('days'),
            604800 => get_string('weeks')
        ];
        // Get min and max.
        if( empty($structure->min) ) 
        {
            $this->min = null;
        } else if( is_float($structure->min) || is_int($structure->min) ) 
        {
            $this->min = $structure->min;
        }
        
        if( empty($structure->max) ) 
        {
            $this->max = null;
        } else if( is_float($structure->max) || is_int($structure->max) ) 
        {
            $this->max = $structure->max;
        }
        
        if( ! empty($structure->time_max) )
        {
            $this->time_max = $structure->time_max;
        } else 
        {
            $this->time_max = null;
        }
        
        if( ! empty($structure->time_min) )
        {
            $this->time_min = $structure->time_min;
        } else
        {
            $this->time_min = null;
        }
    }
 
    public function save() {
        $options = [];
        $options['type'] = $this->type;
        if( ! is_null($this->min) )
        {
            $options['min'] = $this->min;
        }
        if( ! is_null($this->max) )
        {
            $options['max'] = $this->max;
        }
        if( ! is_null($this->time_min) )
        {
            $options['time_min'] = $this->time_min;
        }
        if( ! is_null($this->time_max) )
        {
            $options['time_max'] = $this->time_max;
        }
        return (object)$options;
    }
    
    /**
     * Returns a JSON object which corresponds to a condition of this type.
     *
     * Intended for unit testing, as normally the JSON values are constructed
     * by JavaScript code.
     *
     * @param int $cmid Course-module id of other activity
     * @param int $expectedcompletion Expected completion value (COMPLETION_xx)
     * @return stdClass Object representing condition
     */
    public static function get_json($cmid, $expectedcompletion) {
        return (object)array(
            'type' => 'activetime', 
            'cm' => (int)$cmid,
            'min' => $this->min,
            'max' => $this->max,
            'time_min' => $this->time_min,
            'time_max' => $this->time_max,
            'e' => (int)$expectedcompletion
        );
    }
 
    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
        global $DB;
        $allow = false;
        $course = $info->get_course();
        $activeenrols = $DB->get_records('local_learninghistory', [
            'courseid' => $course->id, 
            'userid' => $userid, 
            'status' => 'active'
        ]);
        if( ! empty($activeenrols) )
        {
            $activeenrol = array_shift($activeenrols);
            $allow = (is_null($this->min) || $activeenrol->activetime >= $this->min * $this->time_min) &&
                (is_null($this->max) || $activeenrol->activetime < $this->max * $this->time_max);
        }
        
        if ($not) {
            $allow = !$allow;
        }
        $this->allow = $allow;
        return $allow;
    }
 
    public function get_description($full, $not, \core_availability\info $info) {
        $course = $info->get_course();
        // String depends on type of requirement. We are coy about
        // the actual numbers, in case grades aren't released to
        // students.
        $value = '';
        if (is_null($this->min) && is_null($this->max)) {
            $string = 'any';
        } else if (is_null($this->max)) {
            $string = 'min';
            $value = (string)$this->min . ' ' . $this->timeoptions[(int)$this->time_min];
        } else if (is_null($this->min)) {
            $string = 'max';
            $value = (string)$this->max . ' ' . $this->timeoptions[(int)$this->time_max];
        } else {
            $string = 'range';
        }
        if ($not) {
            // The specific strings don't make as much sense with 'not'.
            if ($string === 'any') {
                $string = 'notany';
            } else {
                $string = 'notgeneral';
            }
        }
        return get_string('requires_' . $string, 'availability_activetime', $value);
    }
 
    protected function get_debug_string() {
        return $this->allow ? 'YES' : 'NO';
    }
    
    public function update_after_restore($restoreid, $courseid, \base_logger $logger, $name) {
        global $DB;
        $rec = \restore_dbops::get_backup_ids_record($restoreid, 'course_module', $this->cmid);
        if (!$rec || !$rec->newitemid) {
            // If we are on the same course (e.g. duplicate) then we can just
            // use the existing one.
            if ($DB->record_exists('course_modules',
                array('id' => $this->cmid, 'course' => $courseid))) {
                    return false;
                }
                // Otherwise it's a warning.
                $this->cmid = 0;
                $logger->process('Restored item (' . $name .
                    ') has availability condition on module that was not restored',
                    \backup::LOG_WARNING);
        } else {
            $this->cmid = (int)$rec->newitemid;
        }
        return true;
    }
    
    public function update_dependency_id($table, $oldid, $newid) {
        if ($table === 'course_modules' && (int)$this->cmid === (int)$oldid) {
            $this->cmid = $newid;
            return true;
        } else {
            return false;
        }
    }
}
?>