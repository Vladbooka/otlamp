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
 * Тип подстановки - пользователь
 *
 * @package    local_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_opentechnology\availability_condition\replacement;

use local_opentechnology\availability_condition\abstract_replacement;

class replacement_string extends abstract_replacement {

    public static function get_code() {
        return 'string';
    }

    public static function get_top_context_level() {
        return CONTEXT_SYSTEM;
    }

    public static function get_properties() {
        return [];
    }

    public static function get_icon_code() {
        return 'keyboard-o';
    }

    public function get_property_value(string $property) {
        throw new \Exception('Properties are not supported by this replacement object');
    }


}