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
 * Блок Витрина курсов
 *
 * @package    block
 * @subpackage courses_showcase
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_courses_showcase\viewtype;


class crw_default
{
    protected $block;
    
    public function __construct(\block_courses_showcase $block)
    {
        $this->block = $block;
    }
    
    protected function get_showcase_properties()
    {
        return [];
    }
    
    /**
     * Дополнить форму редактирования блока
     * @param \MoodleQuickForm $mform
     */
    public function extend_edit_form(&$mform)
    {
        
    }
    
    public function get_showcase()
    {
        global $CFG;
        
        // Подключение библиотеки витрины
        require_once($CFG->dirroot .'/local/crw/lib.php');
        
        // Получение плагина витрины
        $showcase = new \local_crw($this->get_showcase_properties());
        
        // Отображение витрины
        return $showcase->display_showcase(['return_html' => true]);
    }
}