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
 * Contains class used to render the block of square courses.
 *
 * @package    crw_courses_list_universal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace crw_courses_list_universal\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;

/**
 * Renderer class for the block of square courses.
 *
 * @package    crw_courses_list_universal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    protected function get_template_prefix($courseslist)
    {
        $template = false;
        
        // Получение текущей категории
        $categoryid = $courseslist->get_current_category_id();
        
        // Получение переопределения шаблона для категории
        if (!is_null($categoryid))
        {
            $template = local_crw_get_category_config($categoryid, 'courselist_template');
        }
        
        // Получение оригинальной настройки плагина, если не была переопределена
        if ($template == 'inherit' || $template === false)
        {
            $template = get_config('crw_courses_list_universal', 'template');
        }
        
        // Фолбэк к базовой настройке, если по какой-то причине не удалось получить настройку плагина
        if ($template === false)
        {
            $template = 'base';
        }
        
        return $template;
    }
    
    public function get_courses_list_data(\crw_courses_list_universal\output\courses_list $courseslist) {
        return $courseslist->export_for_template($this);
    }
    
    /**
     * Renders the block of square courses.
     *
     * @param \crw_courses_list_universal\output\courses_list $block
     * @return string html for the page
     */
    public function render_courses_list(\crw_courses_list_universal\output\courses_list $courseslist) {
        
        return $this->render_from_template(
            'crw_courses_list_universal/' . $this->get_template_prefix($courseslist) . '_courses_list',
            $this->get_courses_list_data($courseslist)
        );
    }
    
}