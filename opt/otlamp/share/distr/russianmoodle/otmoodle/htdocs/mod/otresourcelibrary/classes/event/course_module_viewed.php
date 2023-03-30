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

namespace mod_otresourcelibrary\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_otresourcelibrary course module viewed event class.
 */
class course_module_viewed extends \core\event\course_module_viewed {
    /**
     * Create instance of event.
     *
     * @since Moodle 2.7
     *
     * @param \stdClass $otresourcelibrary
     * @param \context_module $context
     * @return course_module_viewed
     */
    public static function create_from_otresourcelibrary(\stdClass $otresourcelibrary, \context_module $context) {
        $data = array(
            'context' => $context,
            'objectid' => $otresourcelibrary->id
        );
        /** @var course_module_viewed $event */
        $event = self::create($data);
        $event->add_record_snapshot('otresourcelibrary', $otresourcelibrary);
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
        $this->data['objecttable'] = 'otresourcelibrary';
    }

    public static function get_objectid_mapping() {
        return array('db' => 'otresourcelibrary', 'restore' => 'otresourcelibrary');
    }
}
