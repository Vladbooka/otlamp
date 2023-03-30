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
 * Contains class used to prepare a block of courses.
 *
 * @package   crw_courses_list_universal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace crw_categories_list_universal\output;

defined('MOODLE_INTERNAL') || die();

// Подключаем основную библиотеку плагина
require_once($CFG->dirroot . '/local/crw/plugins/categories_list_universal/lib.php');

use dml_exception;
use renderable;
use templatable;
use stdClass;
use crw_categories_list_universal;
use renderer_base;
use local_crw\output\category;

/**
 * Class to prepare a block of courses.
 *
 * @package   crw_courses_list_universal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class categories_list implements templatable, renderable {
    
    /**
     * Переданные опции отображения (см. lib.php основного плагина)
     * @var array
     */
    private $options;
    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct($options = []) {
        $this->options = $options;
    }
    
    /**
     * Подготовка данных для рендера
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        
        global $PAGE;
        
        $data = [];
        
        $data['items'] = [];
        
        $maincatid = (int)get_config('local_crw', 'main_catid');
        $currentcat = \core_course_category::get($this->options['cid'] ?? $maincatid);
        
        
        // Получить дочерние категории
        if ( ! empty($this->options['categories']) )
        {
            $categories = $this->options['categories'];
        } else
        {
            // Получим категорию
            $categories = $currentcat->get_children();
        }
        
        // Формируем опции для ссылок
        $urlopts = [];
        if (isset($this->options['searchquery']))
        {
            $urlopts['crws'] = $this->options['searchquery'];
        }
        if (isset($this->options['userid']))
        {
            $urlopts['uid'] = $this->options['userid'];
            if (isset($this->options['usercourses_add_not_active']))
            {
                $urlopts['na'] = (int)!empty($this->options['usercourses_add_not_active']);
            }
        }
        
        // собираем категории текущего левела, чтобы отобразить как обычно
        foreach ( $categories as $category )
        {
            
            $crwrenderer = $PAGE->get_renderer('local_crw');
            $categoryobj = new category($category->id, $urlopts);
            $item = $crwrenderer->get_category_data($categoryobj);
            
            if (array_key_exists('caller', $this->options) && $category->id == $this->options['caller'])
            {
                $item['in_path'] = true;
            }
            $data['items'][] = $item;
        }
        
        
        if (empty($this->options['noparents']))
        {
            // для того, чтобы была возможность отобразить категории любого предыдущего левела (хоть всё дерево)
            // формируем данные по вышестоящим левелам
            $levels = [];
            $parentcatid = $currentcat->parent;
            $currentchild = $currentcat->id;
            while ($parentcatid >= 0)
            {
                $parentoptions = fullclone($this->options);
                // идентификатор родительской категории
                $parentoptions['cid'] = $parentcatid;
                // не передаем категории, так как при экспорте их получение возможно по уже переданному идентификатору
                $parentoptions['categories'] = null;
                // добавляем кастомную опцию, чтобы не пытаться собирать родителей по каждой категории, находящейся в родительских
                $parentoptions['noparents'] = true;
                $parentoptions['caller'] = $currentchild;
                
                $catlist = new categories_list($parentoptions);
                $levels[] = $catlist->export_for_template($output);
                
                if ($parentcatid == 0)
                {
                    break;
                }
                
                $currentchild = $parentcatid;
                $parentcat = \core_course_category::get($parentcatid);
                $parentcatid = $parentcat->parent;
            }
            $i=0;
            $levels = array_reverse($levels);
            $data['levels'] = array_map(function($level) use (&$i){
                $level['index_'.$i] = true;
                $level['index'] = $i;
                $i++;
                return $level;
            }, $levels);
        
            $data['index_'.$i] = true;
            $data['index'] = $i;
            
        }
        $data['items_count'] = count($data['items']);
        
        
        return $data;
    }
}
