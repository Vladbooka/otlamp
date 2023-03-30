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
 * Событие отработки хендлера
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otcourselogic\event;

use stdClass;

defined('MOODLE_INTERNAL') || die();

class action_execution_ended extends \core\event\base {

    /**
     * Create the event.
     * 
     * @param stdClass $context
     * @param stdClass $instance
     * @param int $userid
     * @param stdClass $action
     * @return \core\event\base
     */
    public static function create_event(stdClass $context, stdClass $instance, $userid, $action) {
        $other = array();
        $other['action'] = json_encode($action);
        $data = array(
                'context' => $context,
                'objectid' => $instance->id,
                'other' => $other,
                'relateduserid' => $userid
        );
        $event = self::create($data);     
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
        $this->data['objecttable'] = 'otcourselogic';
    }
    /**
     * Получить имя события
     *
     * @return string
     */
    public static function get_name()
    {
        return get_string('event_action_execution_ended_title', 'mod_otcourselogic');
    }
    
    /**
     * Получить описание события
     *
     * @return string
     */
    public function get_description()
    {
        return get_string('event_action_execution_ended_desc', 'mod_otcourselogic');
    }
}
