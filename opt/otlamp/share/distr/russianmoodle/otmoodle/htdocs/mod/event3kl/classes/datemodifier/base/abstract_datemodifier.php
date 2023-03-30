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

namespace mod_event3kl\datemodifier\base;

defined('MOODLE_INTERNAL') || die();

/**
 * Абстрактный класс модификатора даты
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class abstract_datemodifier {
    protected $config=[];

    public function get_config() {
        return $this->config;
    }

    abstract public static function instance_from_config($config);

    /**
     * название модификатора даты
     */
    public static function get_display_name() {
        return get_string(self::get_code() . '_datemodifier_display_name', 'mod_event3kl');
    }

    /**
     * возвращает короткий код текущего модификатора даты, основываясь на классе
     */
    public static function get_code() {
        return (new \ReflectionClass(get_called_class()))->getShortName();
    }
}
