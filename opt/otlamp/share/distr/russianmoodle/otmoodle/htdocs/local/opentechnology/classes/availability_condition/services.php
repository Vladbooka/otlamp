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

namespace local_opentechnology\availability_condition;

defined('MOODLE_INTERNAL') || die();

class services extends \external_api {




    public static function get_logical_groups() {
        $logicalgroups = abstract_logical_group::get_classes_info();
        return json_encode($logicalgroups);
    }

    public static function get_logical_groups_parameters() {
        return new \external_function_parameters([]);
    }

    public static function get_logical_groups_returns() {
        return new \external_value(PARAM_RAW, 'JSONed logical groups classes info');
    }




    public static function get_comparison_operators() {
        $logicalgroups = abstract_comparison_operator::get_classes_info();
        return json_encode($logicalgroups);
    }

    public static function get_comparison_operators_parameters() {
        return new \external_function_parameters([]);
    }

    public static function get_comparison_operators_returns() {
        return new \external_value(PARAM_RAW, 'JSONed comparison operators classes info');
    }




    public static function get_replacements($contextid) {
        $params = ['contextid' => $contextid];
        list('contextid' => $contextid) = self::validate_parameters(self::get_replacements_parameters(), $params);
        $replacements = abstract_replacement::get_classes_info_by_contextid($contextid);
        return json_encode($replacements);
    }

    public static function get_replacements_parameters() {
        return new \external_function_parameters([
            'contextid' => new \external_value(PARAM_INT, 'ID of context to check conditions in', VALUE_REQUIRED),
        ]);
    }

    public static function get_replacements_returns() {
        return new \external_value(PARAM_RAW, 'JSONed replacements classes info');
    }
}