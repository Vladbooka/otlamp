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
 * Блок списка категорий в виде плиток. Класс плагина.
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class crw_categories_list_universal extends local_crw_plugin
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
    public function display($options = array() )
    {
        global $PAGE;
        $renderer = $PAGE->get_renderer('crw_categories_list_universal');
        $categorieslist = new \crw_categories_list_universal\output\categories_list($options);
        return $renderer->render_categories_list($categorieslist);
    }
    
    
    /**
     * Получение списка кодов реализованных шаблонов описательной страницы курса
     * @return string[]
     */
    function get_categories_templates_codes()
    {
        global $CFG;
        
        $templatecodes = [];
        
        $path = $CFG->dirroot . '/local/crw/plugins/categories_list_universal/templates/*_categories_list.mustache';
        foreach(glob($path) as $templatepath)
        {
            $templatecodes[] = basename($templatepath, '_categories_list.mustache');
        }
        
        return $templatecodes;
    }
    
    function get_categories_templates()
    {
        // получение имеющихся шаблонов
        $templatescodes = $this->get_categories_templates_codes();
        
        // формирование имен шаблонов
        $templatesnames = array_map(function($templatecode){
            if (get_string_manager()->string_exists('template_code_'.$templatecode, 'crw_categories_list_universal')) {
                return get_string('template_code_'.$templatecode, 'crw_categories_list_universal');
            }
            return $templatecode;
        }, $templatescodes);
            
        return array_combine($templatescodes, $templatesnames);
    }
}
