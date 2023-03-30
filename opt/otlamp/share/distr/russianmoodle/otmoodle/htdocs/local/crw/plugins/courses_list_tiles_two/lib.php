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

defined('MOODLE_INTERNAL') || die();

/**
 * Блок списка курсов в виде плиток. Класс плагина.
 * 
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class crw_courses_list_tiles_two extends local_crw_plugin 
{
    
    protected $type = CRW_PLUGIN_TYPE_COURSES_LIST;
    
    /**
     * Сформировать html блока
     *
     * @param int $id - ID категории курсов
     * @param array $options - Дополнительные опции
     * 
     * @deprecated - Устаревший метод
     * 
     * @return string - HTML-код блока
     */
    public function outputhtml($id = 0, $options = array() )
    {
        global $CFG;
        require_once($CFG->dirroot .'/local/crw/plugins/courses_list_tiles_two/renderer.php');
        $renderer = new crw_courses_list_tiles_two_renderer();
        return $renderer->get_block($id, $options);
    }
    
    /**
     * Сформировать html блока
     *
     * @param int $id - ID категории курсов
     * @param array $options - Дополнительные опции
     *
     * @return string - HTML-код блока
     */
    public function display($options = array() )
    {
        global $CFG;
        require_once($CFG->dirroot .'/local/crw/plugins/courses_list_tiles_two/renderer.php');
        $renderer = new crw_courses_list_tiles_two_renderer();
        return $renderer->display($options);
    }
}
