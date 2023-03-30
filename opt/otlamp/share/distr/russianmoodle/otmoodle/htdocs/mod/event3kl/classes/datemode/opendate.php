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
 * Класс способа указания даты - свободное время (открытая дата, учащийся сам предлагает удобное время
 * и согласовывает его с преподавателем)
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class opendate extends abstract_datemode implements datemode_interface {

    protected function get_maintained_modifiers($localized = false) {
        return [];
    }

    protected function supports_repeat() {
        return false;
    }

    public static function get_suitable_formats() {
        return ['individual'];
    }

    /**
     * {@inheritDoc}
     * @see datemode_interface::get_start_date()
     */
    public function get_start_date()
    {
        // данный дейтмод определяет дату старта как NULL
        // учащийся сам должен предложить вариант преподавателю через поле offereddate сессии
        // а преподаватель должен согласовать её и тогда дата запишется в startdate и overridenstartdate сессии
        return NULL;
    }

    /**
     * {@inheritDoc}
     * @see \mod_event3kl\datemode\base\datemode_interface::mod_form_validation()
     */
    public function mod_form_validation($data, $files)
    {
        $errors = [];
        if ($data['datemode'] == 'opendate' && $data['format'] != 'individual') {
            $errors['datemode'] = get_string('error_opendate_format', 'mod_event3kl');
        }
        if ($data['datemode'] == 'relative_to_enrolment' && $data['format'] != 'individual') {
            $errors['datemode'] = get_string('error_relative_to_enrolment_format', 'mod_event3kl');
        }
        return $errors;
    }

}