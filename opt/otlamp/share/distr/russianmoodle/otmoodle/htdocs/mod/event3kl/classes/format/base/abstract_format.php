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

namespace mod_event3kl\format\base;

use mod_event3kl\event3kl;
use mod_event3kl\session;
use mod_event3kl\session_member;

defined('MOODLE_INTERNAL') || die();

/**
 * Абстрактный класс формата занятия
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class abstract_format {

    /**
     * название формата
     */
    public function get_display_name() {
        return get_string($this->get_code() . '_format_display_name', 'mod_event3kl');
    }

    /**
     * возвращает короткий код текущего формата, основываясь на классе
     */
    public function get_code() {
        return (new \ReflectionClass($this))->getShortName();
    }

    /**
     * Привести сессии к состоянию, соответствующему настройкам занятия
     * @param event3kl $event3kl
     */
    public function actualize_sessions(event3kl $event3kl) {

    }


}