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
 * Класс способа указания даты - абсолютная дата
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class absolute extends abstract_datemode implements datemode_interface {

    protected function get_maintained_modifiers($localized = false) {
        if ($localized) {
            return ['set_date' => get_string('set_date_absolute_datemode', 'mod_event3kl')];
        }
        return ['set_date'];
    }

    protected function supports_repeat() {
        return false;
    }

}