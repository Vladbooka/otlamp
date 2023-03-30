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
 * Рендер
 *
 * @package    local_crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_crw\output;
defined('MOODLE_INTERNAL') || die();

use core_course_renderer;
use renderable;
require_once ($CFG->dirroot . '/course/renderer.php');

class renderer extends core_course_renderer implements renderable {
    
    public function get_category_data(category $category)
    {
        return $category->export_for_template($this);
    }
    
    public function get_course_data(course $course)
    {
        return $course->export_for_template($this);
    }
    
    protected function get_template_prefix(course $course)
    {
        // Шаблон отображения описательной страницы, настроенный в курсе
        $coursepagetemplate = local_crw_get_course_config($course->get_course_id(), 'coursepage_template');
        
        
        // Если шаблон отображения должен наследоваться из категории - получим значение
        if ($coursepagetemplate == 'inherit' || $coursepagetemplate === false)
        {
            $category = $course->get_course_category();
            $coursepagetemplate = local_crw_get_category_config($category['id'], 'coursepage_template');
        }
        
        // Если шаблон отображения должен наследоваться из базовых настроек витрины - получим значение
        if ($coursepagetemplate == 'inherit' || $coursepagetemplate === false)
        {
            $coursepagetemplate = get_config('local_crw', 'coursepage_template');
        }
        
        // Если шаблон отображения по какой-то причине не найден, делаем фолбэк на базовые шаблон
        if (empty($coursepagetemplate))
        {
            $coursepagetemplate = 'base';
        }
        
        return $coursepagetemplate;
    }
    
    public function render_coursepage(course $course) {
        
        return $this->render_from_template(
            'local_crw/' . $this->get_template_prefix($course) . '_coursepage',
            $this->get_course_data($course)
        );
        
    }
}