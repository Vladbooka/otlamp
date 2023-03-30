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
 * @package    crw_courses_list_squares
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace crw_courses_list_squares\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;

/**
 * Renderer class for the block of square courses.
 *
 * @package    crw_courses_list_squares
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Renders the block of square courses.
     *
     * @param \crw_courses_list_squares\output\catcourses_block $block
     * @return string html for the page
     */
    public function render_catcourse_block(\crw_courses_list_squares\output\catcourses_block $block) {
        $context = $block->export_for_template($this);
        return $this->render_from_template('crw_courses_list_squares/catcourses_block', $context);
    }
}