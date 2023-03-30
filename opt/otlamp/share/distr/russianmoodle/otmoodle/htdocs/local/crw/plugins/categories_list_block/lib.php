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
 * Блок списка категорий в виде плагина Блок. Класс субплагина.
 * 
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class crw_categories_list_block extends local_crw_plugin 
{
    
    protected $type = CRW_PLUGIN_TYPE_CATEGORIES_LIST;
    
    /**
     * Сформировать html блока
     *
     * @param int $id - ID категории курсов
     * @param array $options - Дополнительные опции
     *
     * @return string - HTML-код блока
     */
    public function outputhtml($id = 0, $options = array() )
    {
        global $CFG, $PAGE;
        require_once($CFG->dirroot .'/local/crw/plugins/categories_list_block/renderer.php');
        $renderer = new crw_categories_list_block_renderer();
        $url = new moodle_url($this->get_file_url('module.js', false));
        $PAGE->requires->js($url);
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
        // Получим id категории
        if ( isset($options['cid']) )
        {
            $catid = $options['cid'];
        } else
        {
            $catid = 0;
        }
        return $this->outputhtml($catid, $options);
    }
}
