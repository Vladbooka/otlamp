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
 * Блок Витрина курсов
 *
 * @package    block
 * @subpackage courses_showcase
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_courses_showcase\viewtype;

use block_courses_showcase\crw as crw;

class courses_list extends crw_default
{
    
    public function extend_edit_form(&$mform)
    {
        $courserenderers = crw::get_crw_subplugins(10);
        array_walk(
            $courserenderers,
            function(&$item, $key) {
                $item = get_string('pluginname', 'crw_' . $key);
            }
        );
        
        // Способ отрисовки курсов
        $mform->addElement(
            'select',
            'config_courses_list__course_renderer',
            get_string('courses_list__course_renderer', 'block_courses_showcase'),
            $courserenderers
        );
        
        // Отображать только курсы, на которые подписан пользователь
        $mform->addElement(
            'advcheckbox',
            'config_courses_list__display_user_courses',
            get_string('courses_list__display_user_courses', 'block_courses_showcase')
        );
        
        // Отображать только курсы, на которые подписан пользователь
        $mform->addElement(
            'advcheckbox',
            'config_courses_list__user_courses_add_not_active',
            get_string('courses_list__user_courses_add_not_active', 'block_courses_showcase')
        );
        $mform->disabledIf(
            'config_courses_list__user_courses_add_not_active',
            'config_courses_list__display_user_courses'
        );
        
    }
    
    protected function get_showcase_properties()
    {
        global $USER;
        
        $properties = [
            'forced_showcase_slots' => [
                $this->block->config->courses_list__course_renderer ?? 'courses_list_squares'
            ],
            'display_invested_courses' => true
        ];
        
        if (!empty($this->block->config->courses_list__display_user_courses))
        {
            if ($this->block->is_public_profile())
            {
                $properties['userid'] = optional_param('id', $USER->id, PARAM_INT);
                
            } else
            {
                $properties['userid'] = $USER->id;
            }
            
            if (!empty($this->block->config->courses_list__user_courses_add_not_active))
            {
                $properties['user_courses_add_not_active'] = true;
            }
        }
        
        return $properties;
    }
}