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
 * Класс способа указания даты - время по заявке (учащийся выбирает одну из сессий,
 * в которой имеются свободные места)
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class vacantseat extends abstract_datemode implements datemode_interface {

    protected function get_maintained_modifiers($localized = false) {
        return [];
    }

    protected function supports_repeat() {
        return false;
    }
    
    public static function get_suitable_formats() {
        return ['manual'];
    }

    /**
     * {@inheritDoc}
     * @see \mod_event3kl\datemode\base\datemode_interface::mod_form_validation()
     */
    public function mod_form_validation($data, $files)
    {
        $errors = [];
        if ($data['datemode'] == 'vacantseat' && $data['format'] != 'manual') {
            $errors['datemode'] = get_string('error_vacantseat_format', 'mod_event3kl');
        }
        return $errors;
    }

}