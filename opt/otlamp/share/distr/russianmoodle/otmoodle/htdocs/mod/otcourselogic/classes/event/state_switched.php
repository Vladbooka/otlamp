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
 * Модуль Логика курса. Класс события.
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otcourselogic\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Модуль Логика курса. Событие изменения состояния элемента курса для пользователя.
 *
 * @package    mod
 * @subpackage otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class state_switched extends \core\event\base 
{
    /**
     * Установка базовых свойств события
     */
    protected function init() 
    {
        $this->data['objecttable'] = 'otcourselogic_state';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['action'] = 'switched';
    }

    /**
     * Получить имя события
     *
     * @return string
     */
    public static function get_name() 
    {
        return get_string('event_state_switched_title', 'mod_otcourselogic');
    }

    /**
     * Получить описание события
     *
     * @return string
     */
    public function get_description() 
    {
        return get_string('event_state_switched_desc', 'mod_otcourselogic', $this->objectid);
    }

    /**
     * Валидация данных события
     *
     * @throws \coding_exception - В случае ошибок
     */
    protected function validate_data() 
    {
        parent::validate_data();
    }
}

