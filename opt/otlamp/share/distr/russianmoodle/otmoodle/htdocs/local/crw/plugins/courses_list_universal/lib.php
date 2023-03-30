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
 * Блок списка курсов в виде квадратов. Класс плагина.
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class crw_courses_list_universal extends local_crw_plugin
{
    
    protected $type = CRW_PLUGIN_TYPE_COURSES_LIST;
    
    /**
     * Сформировать html блока
     *
     * @param array $options - Дополнительные опции
     * @return string - HTML-код блока
     */
    public function display($options = [] )
    {
        global $PAGE;
        $renderer = $PAGE->get_renderer('crw_courses_list_universal');
        $courseslist = new \crw_courses_list_universal\output\courses_list($options);
        return $renderer->render_courses_list($courseslist);
    }
    
    /**
     * Получение списка кодов реализованных шаблонов описательной страницы курса
     * @return string[]
     */
    function get_courselist_templates_codes()
    {
        global $CFG;
        
        $templatecodes = [];
        
        $path = $CFG->dirroot . '/local/crw/plugins/courses_list_universal/templates/*_courses_list.mustache';
        foreach(glob($path) as $templatepath)
        {
            $templatecodes[] = basename($templatepath, '_courses_list.mustache');
        }
        
        return $templatecodes;
    }
    
    function get_courselist_templates()
    {
        // получение имеющихся шаблонов
        $templatescodes = $this->get_courselist_templates_codes();
        
        // формирование имен шаблонов
        $templatesnames = array_map(function($templatecode){
            if (get_string_manager()->string_exists('template_code_'.$templatecode, 'crw_courses_list_universal')) {
                return get_string('template_code_'.$templatecode, 'crw_courses_list_universal');
            }
            return $templatecode;
        }, $templatescodes);
            
        return array_combine($templatescodes, $templatesnames);
    }
    
    
//     /**
//      * Сформировать одну плитку курса
//      *
//      * @param core_course_list_element|stdClass $course
//      *            - объект курса
//      * @param string $additionalclasses
//      *            - дополнительные классы блока
//      * @return string - HTML код плитки курса
//      */
//     protected function cs_courseblock($course, $additionalclasses = '')
//     {
//         global $CFG;
//         $block = new stdClass();
    
//         if ( $course instanceof stdClass )
//         {
//             $course = new \core_course_list_element($course);
//         }
//         $block->courseid = $course->id;
//         require_once($CFG->dirroot . '/course/renderer.php');
//         $block->coursecattypecourse = core_course_renderer::COURSECAT_TYPE_COURSE;
//         // Бэкграунд блока
//         $background = get_config('crw_courses_list_universal', 'coursebox_background');
//         $block->hasbackground = false;
//         if( ! empty($background) )
//         {
//             $block->backgroundurl = local_crw_course_image_url($course);
//             $block->hasbackground = true;
//         }
//         // Ссылка на страницу курса
//         $url = new moodle_url('/local/crw/course.php', ['id' => $course->id]);
//         $block->url = $url->out();
//         $block->name = $course->fullname;
//         // Название категории
//         $hidecoursecategoryenabled = get_config('crw_courses_list_universal', 'hide_course_category');
//         if( empty($hidecoursecategoryenabled) )
//         {
//             $block->showcoursecategory = true;
//         } else
//         {
//             $block->showcoursecategory = false;
//             $block->catname = '';
//         }
//         $category = \core_course_category::get($course->category, IGNORE_MISSING, true);
//         $block->catname = $category->name;
        
//         // Добавим требуемые навыки
//         $required_knowledge = local_crw_get_course_config($course->id, 'required_knowledge', true);
//         $block->skills = [];
//         if( ! empty($required_knowledge) )
//         {
//             foreach($required_knowledge as $item)
//             {
//                 $skill = new stdClass();
//                 $skill->name = $item->value;
//                 $block->skills[] = $skill;
//             }
//         }
        
//         // Цена курса
//         $displayprice = local_crw_get_course_config($course->id, 'display_price');
//         if ( $displayprice === false || in_array((int)$displayprice, [1,3]) )
//         {
//             $hidecoursepriceenabled = get_config('crw_courses_list_universal', 'hide_course_price');
//             $price = local_crw_get_course_price($course);
//             $classforbigint = '';
//             if( empty($price) || ! empty($hidecoursepriceenabled) )
//             {
//                 $price = '';
//             } else
//             {
//                 if( strlen($price) >= 6 )
//                 {
//                     $classforbigint = ' bigint';
//                 } elseif( strlen($price) >= 5 )
//                 {
//                     $classforbigint = ' mediumint';
//                 }
//             }
//         } else
//         {
//             $price = $classforbigint = '';
//         }
//         $block->priceclasses = 'clsq_course_price' . $classforbigint;
//         $block->price = $price;
        
//         // Кнопка "Пройти курс"
//         $hidecoursepassbuttonenabled = get_config('crw_courses_list_universal', 'hide_course_pass_button');
//         if( empty($hidecoursepassbuttonenabled) )
//         {
//             $block->showbutton = true;
//         } else
//         {
//             $block->showbutton = false;
//         }
        
//         // Собираем теги
//         $hidecoursetagsenabled = get_config('crw_courses_list_universal', 'hide_course_tags');
//         $block->tags = [];
//         $block->hastags = false;
//         if( empty($hidecoursetagsenabled) )
//         {
//             $coursetags = core_tag_tag::get_item_tags('core', 'course', $course->id);
//             if( ! empty($coursetags) )
//             {
//                 foreach($coursetags as $coursetag)
//                 {
//                     $item = new stdClass();
//                     $item->class = 'clsq_tag_' . $coursetag->id;
//                     $item->name = $coursetag->name;
//                     $block->tags[] = $item;
//                 }
//                 $block->hastags = true;
//             }
//         }
        
//         return $block;
//     }
}
