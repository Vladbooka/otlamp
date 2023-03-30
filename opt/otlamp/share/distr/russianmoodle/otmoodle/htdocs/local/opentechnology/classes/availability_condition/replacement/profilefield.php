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
use local_opentechnology\availability_condition\replacement_property;
require_once($CFG->dirroot . '/local/opentechnology/locallib.php');

class profilefield extends abstract_replacement {

    public static function get_top_context_level() {
        return CONTEXT_SYSTEM;
    }

    public static function get_properties() {
        $properties = [];
        $dof = local_opentechnology_get_dof();
        if (!is_null($dof)) {
            /** @var \ama_user $amauser */
            $amauser = $dof->modlib('ama')->user(false);
            $profilefields = $amauser->get_user_custom_fields();
            if (!empty($profilefields)) {
                foreach ($profilefields as $profilefield) {
                    $property = new replacement_property($profilefield->shortname, $profilefield->name);
                    $properties[$property->getCode()] = $property;
                }
            }
        }
        return $properties;
    }

    public static function get_icon_code() {
        return 'user-plus';
    }

    public function get_property_value(string $property) {
        global $USER;
        $this->validate_property($property);

        $profile = profile_user_record($USER->id, false);
        if (!property_exists($profile, $property)) {
            throw new \Exception('Property not exists');
        }
        return $profile->{$property};
    }

}