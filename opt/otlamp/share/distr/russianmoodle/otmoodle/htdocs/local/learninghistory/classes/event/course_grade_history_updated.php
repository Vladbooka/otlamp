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
 * История обучения. Класс события.
 *
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learninghistory\event;

defined('MOODLE_INTERNAL') || die();

use stdClass;

/**
 * История обучения. Событие изменения истории оценки за курс.
 *
 * @package    local
 * @subpackage learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_grade_history_updated extends \core\event\base
{
    /**
     * Установка базовых свойств события
     */
    protected function init()
    {
        $this->data['crud'] = 'u';
        $this->data['objecttable'] = 'local_learninghistory';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['action'] = 'updated';
    }
    
    /**
     * Получить имя события
     *
     * @return string
     */
    public static function get_name()
    {
        return get_string('event_course_grade_history_updated_title', 'local_learninghistory');
    }
    
    /**
     * Получить описание события
     *
     * @return string
     */
    public function get_description()
    {
        $a = new stdClass();
        $a->userid = $this->relateduserid;
        return get_string('event_course_grade_history_updated_desc', 'local_learninghistory', $a);
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

