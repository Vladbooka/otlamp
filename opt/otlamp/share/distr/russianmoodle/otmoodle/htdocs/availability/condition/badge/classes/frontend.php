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
 * Условие показа по наличию значка. Класс фронтенда.
 *
 * @package    availability_badge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_badge;
 
defined('MOODLE_INTERNAL') || die();
 
class frontend extends \core_availability\frontend 
{
 
    protected function get_javascript_strings() 
    {
        return [
            'holdbadge',
            'site',
            'course'
        ];
    }
 
    protected function get_javascript_init_params($course, \cm_info $cm = null,
            \section_info $section = null) 
    {
        global $CFG, $DB;
        require_once($CFG->libdir . '/badgeslib.php');
        
        // Получение значков
        $badges = array_merge(\badges_get_badges(BADGE_TYPE_SITE, 0, '', '', 0, 0),\badges_get_badges(BADGE_TYPE_COURSE, 0, '', '', 0, 0));
        
        // Формирование выпадающего списка значков
        foreach ( $badges as $k => &$v )
        {
            if( ! is_null($badges[$k]->courseid) )
            {
                $course = $DB->get_record('course', array('id' => $badges[$k]->courseid), 'fullname');
                $badges[$k]->coursename = $course->fullname;
            } else
            {
                $badges[$k]->coursename = '';
            }
        }
        return [$badges];
    }
 
    /**
     * Возможность добавления условия
     * 
     */
    protected function allow_add($course, \cm_info $cm = null, \section_info $section = null) 
    {
        global $CFG;
        require_once($CFG->libdir . '/badgeslib.php');
        
        // Получение значков
        $badges = array_merge(\badges_get_badges(BADGE_TYPE_SITE),\badges_get_badges(BADGE_TYPE_COURSE));
        
        if ( empty($badges) )
        {// Значков не найдено
            return false;
        }
        // Значки найдены
        return true;
    }
}