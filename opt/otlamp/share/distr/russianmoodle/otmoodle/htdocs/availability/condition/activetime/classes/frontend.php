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
 * @package    availability_activetime
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_activetime;
 
defined('MOODLE_INTERNAL') || die();
 
class frontend extends \core_availability\frontend {
    /**
     * @var array Cached init parameters
     */
    protected $cacheparams = [];

    /**
     * @var string IDs of course, cm, and section for cache (if any)
     */
    protected $cachekey = '';
    
    protected function get_javascript_strings() 
    {
        return [
            'option_min',
            'label_min',
            'option_max',
            'label_max',
            'error_backwardrange',
            'error_invalidnumber'
        ];
    }
 
    protected function get_javascript_init_params($course, \cm_info $cm = null,
            \section_info $section = null) 
    {
        $options = [];
        $timeoptions = [
            1 => get_string('sec'),
            60 => get_string('min'),
            3600 => get_string('hours'),
            86400 => get_string('days'),
            604800 => get_string('weeks')
        ];
        $options['timeoptions'] = (object)$timeoptions;
        return [$options];
    }
 
    protected function allow_add($course, \cm_info $cm = null,
            \section_info $section = null) 
    {
        return true;
    }
}