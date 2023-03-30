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
 * Front-end class.
 *
 * @package    availability_otcomparison
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_otcomparison;

defined('MOODLE_INTERNAL') || die();
 
class frontend extends \core_availability\frontend 
{
    
    /**
     * {@inheritDoc}
     * @see \core_availability\frontend::get_javascript_strings()
     */
    protected function get_javascript_strings() 
    {
        return [
            'choose_source',
            'choose_preprocessor',
            'choose_operator',
        ];
    }
 
    /**
     * {@inheritDoc}
     * @see \core_availability\frontend::get_javascript_init_params()
     */
    protected function get_javascript_init_params($course, \cm_info $cm = null,
            \section_info $section = null) 
    {
        global $CFG;
        
        $curdate = new \DateTime();
        
        $dateexamples = [];
        $dateexamples[] = \html_writer::div($curdate->format('Y-m-d'));
        $dateexamples[] = \html_writer::div($curdate->format('Y/m/d'));
        $dateexamples[] = \html_writer::div($curdate->format('Y-m-d H:i:s'));
        $dateexamples[] = \html_writer::div($curdate->format('c'));
        
        $result = [
            'fields' => condition::get_fields(),
            'preprocessors' => condition::get_preprocessors(),
            'operators' => condition::get_operators(),
            'date_example' => \html_writer::div(get_string('date_example', 'availability_otcomparison', implode('', $dateexamples))),
            'days_explanation' => \html_writer::div(get_string('days_explanation', 'availability_otcomparison'))
        ];
        
        return [$result];
    }
 
    /**
     * {@inheritDoc}
     * @see \core_availability\frontend::allow_add()
     */
    protected function allow_add($course, \cm_info $cm = null,
            \section_info $section = null) 
    {
        return true;
    }
}