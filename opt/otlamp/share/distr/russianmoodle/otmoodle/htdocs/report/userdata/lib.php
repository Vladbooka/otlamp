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
 * Отчет о пользовательских данных. Функции lib API
 *
 * @package    report
 * @subpackage userdata
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * This function extends the course navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $user
 * @param stdClass $course The course to object for the report
 */
function report_userdata_extend_navigation_course($navigation, $course, $context) 
{
    global $CFG;
    
    $system = context_system::instance();
    if ( has_capability('report/userdata:view_enrolled', $context) || has_capability('report/userdata:view', $system) ) 
    {
        $url = new moodle_url('/report/userdata/index.php', ['courseid'=>$course->id]);
        $navigation->add(get_string('pluginname','report_userdata'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}