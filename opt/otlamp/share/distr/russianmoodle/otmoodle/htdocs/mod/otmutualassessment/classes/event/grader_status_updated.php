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
 * otmutualassessment 'grader_status_updated' event handler
 *
 * @package    mod
 * @subpackage otmutualassessment
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otmutualassessment\event;

use stdClass;
use context_module;

defined('MOODLE_INTERNAL') || die();

class grader_status_updated extends \core\event\base {
    /**
     * Return localised event name
     * @return string
     */
    public static function get_name()
    {
        return get_string('grader_status_updated', 'mod_otmutualassessment');
    }

    /**
     * Create the event from course record.
     *
     * @param stdClass $status объект статуса оценщика
     * @param string $oldtstatus предыдущий статус оценщика
     * @param context_module $context контекст модуля курса
     * @return \mod_otmutualassessment\event\grader_status_updated
     */
    public static function create_from_otmutualassessment(stdClass $status, $oldtstatus, $context) {
        $other = [];
        $other['oldstatus'] = $oldtstatus;
        $other['newstatus'] = $status->status;
        $other['groupid'] = $status->groupid;
        $data = [
            'relateduserid' => $status->userid,
            'context' => $context,
            'objectid' => $status->id,
            'other' => $other,
        ];
        $event = self::create($data);
        $event->add_record_snapshot('otmutualassessment_statuses', $status);
        return $event;
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'otmutualassessment_statuses';
    }
}
