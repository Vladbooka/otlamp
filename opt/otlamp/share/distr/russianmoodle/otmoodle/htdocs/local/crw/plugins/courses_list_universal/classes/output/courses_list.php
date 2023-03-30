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

namespace crw_courses_list_universal\output;

defined('MOODLE_INTERNAL') || die();

// Подключаем основную библиотеку плагина
require_once($CFG->dirroot . '/local/crw/plugins/courses_list_universal/lib.php');

use dml_exception;
use renderable;
use templatable;
use stdClass;
use crw_courses_list_universal;
use renderer_base;

/**
 * Class to prepare a block of courses.
 *
 * @package   crw_courses_list_universal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class courses_list implements templatable, renderable {

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
    
    public function get_current_category_id()
    {
        if (array_key_exists('cid', $this->options))
        {
            return $this->options['cid'];
        }
        return null;
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
        if (array_key_exists('courses', $this->options) && is_array($this->options['courses']))
        {
            foreach(array_keys($this->options['courses']) as $courseid)
            {
                try {
                    $courseobj = get_course($courseid);
                } catch(dml_exception $ex) {
                    continue;
                }
                
                $crwrenderer = $PAGE->get_renderer('local_crw');
                $course = new \local_crw\output\course($courseobj);
                $item = $crwrenderer->get_course_data($course);
                
                // признак скрытости курса
                $hidecourseenabled = local_crw_get_course_config($courseid, 'hide_course');
                $item['hidden'] = ($courseobj->visible == '0' || !empty($hidecourseenabled));
                
                $item['background_url'] = local_crw_course_image_url($courseobj);
                $item['crw_course_url'] = new \moodle_url('/local/crw/course.php', ['id' => $courseid]);
                
                $data['items'][] = $item;
            }
        }

        return $data;
    }
}
