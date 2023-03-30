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
 * Плагин определения заимствований Антиплагиат. Класс события плагиаризма.
 *
 * @package    plagiarism
 * @subpackage apru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_apru\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Изменение статуса нахождения в индексе
 *
 * Событие создается при изменении статуса нахождения в индексе у документа
 *
 * @package    plagiarism
 * @subpackage apru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class set_indexed_status extends \core\event\base 
{
    /**
     * Установка базовых свойств события
     */
    protected function init() 
    {
        $this->data['objecttable'] = 'plagiarism_apru_files';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['action'] = 'index_status_changed';
        $this->data['target'] = 'file';
    }

    /**
     * Получить имя события
     *
     * @return string
     */
    public static function get_name() 
    {
        return get_string('event_set_indexed_status_title', 'plagiarism_apru');
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description() 
    {
        return get_string('event_set_indexed_status_desc', 'plagiarism_apru', $this->objectid);
    }

    /**
     * Custom validations
     *
     * @throws \coding_exception in case of any problems.
     */
    protected function validate_data() 
    {
        parent::validate_data();

        if ( ! isset($this->objectid)) 
        {
            throw new \coding_exception('The \'objectid\' must be set.');
        }
    }
}

