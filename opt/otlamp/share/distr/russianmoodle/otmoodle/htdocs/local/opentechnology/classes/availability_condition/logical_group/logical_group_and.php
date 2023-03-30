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
 * Логическая группа "И"
 *
 * @package    local_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_opentechnology\availability_condition\logical_group;

use local_opentechnology\availability_condition\abstract_logical_group;

class logical_group_and extends abstract_logical_group {

    public static function get_code() {
        return 'and';
    }

    public function execute() {
        foreach($this->items as $item) {
            if (!$item) {
                return false;
            }
        }
        return true;
    }

}