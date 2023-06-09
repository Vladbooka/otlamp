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
 * Обозреватель событий для плагина local_learninghistory
 * 
 * @package    block
 * @subpackage rate_course
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_rate_course;


defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for mod_forum.
 */
class observer {

    
    /**
     * Удаление данных блока после удаления курса
     * 
     * @param \core\event\course_deleted $event
     * 
     * @return boolean
     */
    public static function course_deleted(\core\event\course_deleted $event) {
        global $DB;
        $result = $DB->delete_records('block_rate_course',[
            'course'=>$event->courseid
        ]);
        return $result;
    }
}
