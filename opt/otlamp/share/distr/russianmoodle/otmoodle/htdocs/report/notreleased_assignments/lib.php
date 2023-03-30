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
 * Public API of the notreleased_assingments report.
 *
 * @package    report
 * @subpackage notreleased_assingments
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
/**
 * This function extends the course navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course
 * @param stdClass $context
 */
function report_notreleased_assingments_extend_navigation_course($navigation, $course, $context)
{
    global $CFG;
    
    $system = context_system::instance();
    if (has_capability('report/notreleased_assingments:view', $system))
    {
        $url = new moodle_url('/report/notreleased_assingments/index.php');
        $navigation->add(get_string('pluginname','report_notreleased_assingments'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}