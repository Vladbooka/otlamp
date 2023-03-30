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
 * @package   crw_courses_list_squares
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace crw_courses_list_squares\output;

defined('MOODLE_INTERNAL') || die();

// Подключаем основную библиотеку плагина
require_once($CFG->dirroot . '/local/crw/plugins/courses_list_squares/lib.php');

use renderable;
use templatable;
use moodle_url;
use stdClass;
use crw_courses_list_squares;
use renderer_base;

/**
 * Class to prepare a block of courses.
 *
 * @package   crw_courses_list_squares
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class catcourses_block implements templatable, renderable {

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
        $context = new stdClass();
        $plugin = new crw_courses_list_squares('courses_list_squares');
        $context->classes = $plugin->get_courseblock_classes();
        $context->blocks = $plugin->get_blocks($this->options);
        $context->fakeblocks = $plugin->get_fakeblocks(count($context->blocks));        

        return $context;
    }
}
