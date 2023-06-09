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
 * Coursepage viewed event.
 *
 * @package    local_crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_crw\event;

use core\event\base;

defined('MOODLE_INTERNAL') || die();

/**
 * Coursepage viewed event class.
 *
 * Class for event to be triggered when a coursepage is viewed.
 *
 * @property-read array $other {
 *      Extra information about the event.
 * }
 */
class coursepage_viewed extends base {

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {

        $description = "The user with id '$this->userid' viewed the coursepage with id '$this->courseid'.";

        return $description;
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventcoursepageviewed', 'local_crw');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url|null
     */
    public function get_url() {
        return (new \moodle_url('/local_crw/course.php', ['id' => $this->courseid]));
    }

    /**
     * Return the legacy event log data.
     *
     * @return array|null
     */
    protected function get_legacy_logdata() {
        if ($this->courseid == SITEID and !isloggedin()) {
            // We did not log frontpage access in older Moodle versions.
            return null;
        }
        
        return array($this->courseid, 'local_crw', 'view', 'course.php?id=' . $this->courseid, $this->courseid);
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        if ($this->contextlevel != CONTEXT_COURSE) {
            throw new \coding_exception('Context level must be CONTEXT_COURSE.');
        }
    }

    public static function get_other_mapping() {
        // No mapping required.
        return false;
    }
}
