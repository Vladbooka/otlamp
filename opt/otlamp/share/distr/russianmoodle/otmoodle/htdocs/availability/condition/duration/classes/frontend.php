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
 * @package    availability_duration
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_duration;
 
defined('MOODLE_INTERNAL') || die();
 
class frontend extends \core_availability\frontend {
    /**
     * @var array Cached init parameters
     */
    protected $cacheparams = array();

    /**
     * @var string IDs of course, cm, and section for cache (if any)
     */
    protected $cachekey = '';
    
    protected function get_javascript_strings() {
        return [
            'setcoursedate',
            'setenrollmentdate',
            'setunenrollmentdate',
            'setduration',
            'setcourselogicactivatedate',
            'setcourselastaccessdate',
            'durationmeasurew',
            'durationmeasured',
            'durationmeasureh',
            'durationmeasurem'
        ];
    }
 
    protected function get_javascript_init_params($course, \cm_info $cm = null,
            \section_info $section = null) {
        // Use cached result if available. The cache is just because we call it
        // twice (once from allow_add) so it's nice to avoid doing all the
        // print_string calls twice.
        $cachekey = $course->id . ',' . ($cm ? $cm->id : '') . ($section ? $section->id : '');
        if ($cachekey !== $this->cachekey) {
            // Get list of activities on course which have completion values,
            // to fill the dropdown.
            $context = \context_course::instance($course->id);
            $cms = [];
            $modinfo = get_fast_modinfo($course);
            foreach ($modinfo->cms as $id => $othercm)
            {
                // Add each course-module if it has completion turned on and is not
                // the one currently being edited.
                if ($othercm->modname=='otcourselogic' && (empty($cm) || $cm->id != $id))
                {
                    $otclcm = new \stdClass();
                    $otclcm->cminstance = $othercm->instance;
                    $otclcm->name = format_string($othercm->name, true, [
                        'context' => $context
                    ]);
                    $cms[] = $otclcm;
                }
            }

            $this->cachekey = $cachekey;
            $this->cacheinitparams = [
                [
                    'otcourselogics' => $cms
                ]
            ];
        }
        return $this->cacheinitparams;
    }
 
    protected function allow_add($course, \cm_info $cm = null,
            \section_info $section = null) {
        return true;
    }
}