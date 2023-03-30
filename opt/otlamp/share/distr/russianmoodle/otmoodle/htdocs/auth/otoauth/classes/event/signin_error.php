<?php
// This file is not a part of Moodle - http://moodle.org/
// This is a none core contributed module.
//
// This is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// The GNU General Public License
// can be see at <http://www.gnu.org/licenses/>.

/**
 * Плагин аутентификации OTOAuth. Событие ошибки входа через внешний сервис.
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_otoauth\event;

defined('MOODLE_INTERNAL') || die();

class signin_error extends \core\event\base 
{

    /**
     * Получить описание события
     *
     * @return string
     */
    public function get_description() 
    {
        return get_string('event_signin_error_desc', 'auth_otoauth');
    }

    /**
     * Получить название события
     *
     * @return string
     */
    public static function get_name() 
    {
        return get_string('event_signin_error_name', 'auth_otoauth');
    }

    /**
     * Инициализация события
     *
     * @return void
     */
    protected function init() 
    {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->context = \context_system::instance();
    }
}