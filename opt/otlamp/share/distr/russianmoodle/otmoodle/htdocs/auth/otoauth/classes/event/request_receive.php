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
 * Плагин аутентификации OTOAuth. Событие получения ответа от сервера авторизации.
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_otoauth\event;

use moodle_url;
use context_system;

defined('MOODLE_INTERNAL') || die();

class request_receive extends \core\event\base
{
    
    /**
     * Получить описание события
     *
     * @return string
     */
    public function get_description()
    {
        return get_string('event_request_receive_desc', 'auth_otoauth');
    }
    
    /**
     * Получить название события
     *
     * @return string
     */
    public static function get_name()
    {
        return get_string('event_request_receive_name', 'auth_otoauth');
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
        $this->context = context_system::instance();
    }
    /**
     * Get URL related to the action.
     *
     * @return moodle_url|null
     */
    public function get_url() {
        return new moodle_url('/auth/otoauth/redirect.php');
    }
}