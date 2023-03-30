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

namespace mod_event3kl\datemode;

defined('MOODLE_INTERNAL') || die();

use mod_event3kl\datemode\base\datemode_interface;
use mod_event3kl\datemode\base\abstract_datemode;

/**
 * Класс способа указания даты - относительная дата (относительно даты старта курса)
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class relative_to_course extends abstract_datemode implements datemode_interface {


    /**
     * Получение точки отсчета для формирования даты относительно даты курса
     * @return string
     */
    protected function set_default_startingpoint() {
        if (!isset($this->event3kl)) {
            throw new \Exception('No enough data to detect date starting point');
        }
        $course = get_course($this->event3kl->get('course'));
        $this->startingpoint = '@'.$course->startdate;
    }

    protected function get_maintained_modifiers($localized = false) {
        if ($localized) {
            return ['add' => get_string('add_relative_to_course_datemode', 'mod_event3kl'), 
                'set_day_of_week' => get_string('set_day_of_week_relative_to_course_datemode', 'mod_event3kl')];
        }
        return ['add', 'set_day_of_week'];
    }

    protected function supports_repeat() {
        return true;
    }

}