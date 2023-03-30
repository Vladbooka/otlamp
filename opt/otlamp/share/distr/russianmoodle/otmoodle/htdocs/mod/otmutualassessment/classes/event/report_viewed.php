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
 * The mod_otmutualassessment course module viewed event.
 *
 * @package    mod_otmutualassessment
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otmutualassessment\event;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use context_module;

/**
 * The mod_otmutualassessment course module viewed event class.
 *
 * @package    mod_otmutualassessment
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_viewed extends \core\event\course_module_viewed {
    /**
     * Create instance of event.
     *
     * @since Moodle 2.7
     *
     * @param stdClass $book
     * @param context_module $context
     * @return course_module_viewed
     */
    public static function create_from_otmutualassessment(stdClass $otmutualassessment, context_module $context) {
        $data = [
            'context' => $context,
            'objectid' => $otmutualassessment->id
        ];
        /** @var course_module_viewed $event */
        $event = self::create($data);
        $event->add_record_snapshot('otmutualassessment', $otmutualassessment);
        return $event;
    }
    
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'otmutualassessment';
    }
    
    public static function get_objectid_mapping() {
        return array('db' => 'otmutualassessment', 'restore' => 'otmutualassessment');
    }
}
