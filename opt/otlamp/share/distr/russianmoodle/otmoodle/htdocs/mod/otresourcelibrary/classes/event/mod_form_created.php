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
 * The mod_assign submission viewed event.
 *
 * @package    mod_assign
 * @copyright  2014 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otresourcelibrary\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_otresourcelibrary submission viewed event class.
 */
class mod_form_created extends \core\event\base {
    /**
     * 
     * {@inheritDoc}
     * @see \core\event\base::create()
     */
    public static function form_created($otresourcelibrary) {
        global $USER;
        $data = [];
        $data['objectid'] = $otresourcelibrary->id;  // the id of the object specified in class name
        $data['context'] = \context_module::instance($otresourcelibrary->coursemodule); // the context of this event
        $data['other'] = json_encode($otresourcelibrary);  //the other data describing the event, can not contain objects
        $data['relateduserid'] = $USER->id;  //the id of user which is somehow related to this event
        return parent::create($data);
    }
    /**
     * Init method.
     */
    protected function init() {
        $this->data['objecttable'] = 'otresourcelibrary';
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('mod_form_created', 'mod_otresourcelibrary');
    }
}
