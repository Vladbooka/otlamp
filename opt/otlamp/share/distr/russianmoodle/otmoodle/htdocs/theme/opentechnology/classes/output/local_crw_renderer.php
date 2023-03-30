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
namespace theme_opentechnology\output;
defined('MOODLE_INTERNAL') || die();

use local_crw\output\course;
use theme_opentechnology\profilemanager;
use theme_opentechnology\profiles\profile_overrides_exception;
require_once ($CFG->dirroot . '/course/renderer.php');


class local_crw_renderer extends \local_crw\output\renderer {
    
    public function get_course_data(\local_crw\output\course $course) {
        
        // Получение менеджера профилей
        $manager = profilemanager::instance();
        // Установка профиля для рендера
        $currentprofile = $manager->get_current_profile();
        
        try {
            return $currentprofile->call_overriden_renderer(
                get_class($this), __FUNCTION__, func_get_args(), $this->page, $this->target);
        } catch(profile_overrides_exception $ex) { }
        
        return call_user_func_array(['parent', __FUNCTION__], func_get_args());
    }
}