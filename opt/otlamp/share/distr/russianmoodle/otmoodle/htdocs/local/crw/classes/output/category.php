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
 * Подготовка данных для рендеринга темплейта с данными о курсе
 *
 * @package    local_crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_crw\output;
defined('MOODLE_INTERNAL') || die();

use core_course_category;
use moodle_url;
use renderer_base;
use templatable;

require_once ($CFG->dirroot . '/lib/authlib.php');
require_once ($CFG->dirroot . '/lib/moodlelib.php');
require_once ($CFG->dirroot . '/local/crw/locallib.php');

class category implements templatable {
    
    protected $category = null;
    
    public function __construct($categoryid, $urlopts=[]) {
        $this->categoryid = $categoryid;
        $this->urlopts = $urlopts;
    }
    
    /**
     * {@inheritDoc}
     * @see templatable::export_for_template()
     */
    public function export_for_template(renderer_base $output) {
        
        $data = [];
        
        // Получим категорию
        $data['category'] = $this->get_category();
        
        
        $urlopts = ['cid' => $this->categoryid] + $this->urlopts;
        $data['crw_category_url'] = (new moodle_url('/local/crw/category.php', $urlopts))->out(false);
        
        $data['background_url'] = $this->get_image_url()->out(false);
        
        return $data;
    }
    
    protected function get_category()
    {
        if (is_null($this->category))
        {
            $this->category = core_course_category::get($this->categoryid);
        }
        return $this->category;
    }
    
    public function get_category_parent_id($categoryid)
    {
        $category = core_course_category::get($categoryid);
        if ( isset($category->parent) )
        {
            return (int)$category->parent;
        } else
        {
            return 0;
        }
    }
    
    public function get_image_url($plugindefaults=null)
    {
        // Поиск изображения в дополнительных настройках категории
        $context = \context_coursecat::instance($this->categoryid);
        $imgurl = $this->get_first_image_url($context->id, 'local_crw', 'categoryicon', $this->categoryid);
        if (!is_null($imgurl))
        {
            return $imgurl;
        }
        
        // Поиск изображения в родительских категориях
        $parentcatid = $this->get_category_parent_id($this->categoryid);
        while ($parentcatid > 0)
        {
            $context = \context_coursecat::instance($parentcatid);
            $imgurl = $this->get_first_image_url($context->id, 'local_crw', 'categoryicon', $parentcatid);
            if (!is_null($imgurl))
            {
                return $imgurl;
            }
            
            $parentcatid = $this->get_category_parent_id($parentcatid);
        }
        
        // Использование изображения для категорий по умолчанию
        if (!is_null($plugindefaults))
        {
            $imgurl = call_user_func_array([$this, 'get_first_image_url'], $plugindefaults);
            if (!is_null($imgurl))
            {
                return $imgurl;
            }
        }
        
        // Поиск изображения среди курсов категории
        $category = $this->get_category();
        if ( ! empty($category) )
        {// Категория найдена
            $courses = $category->get_courses();
            foreach ( $courses as $course )
            {
                $courseobj = new course($course);
                $imgurl = $courseobj->get_preview();
                if (!is_null($imgurl))
                {
                    return $imgurl;
                }
            }
        }
        return new moodle_url('/local/crw/assets/no-photo.gif');
    }
    
    private function get_first_image_url($contextid, $component, $filearea, $itemid=false)
    {
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, $component, $filearea, $itemid);
        
        if ( count($files) )
        {
            foreach ( $files as $file )
            {
                // Является ли файл изображением
                if (!$file->is_valid_image())
                {
                    continue;
                }
                
                return moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
                );
            }
        }
        
        return null;
    }
}
