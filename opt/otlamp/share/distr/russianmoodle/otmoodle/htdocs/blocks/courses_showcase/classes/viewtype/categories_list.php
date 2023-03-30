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

class categories_list extends crw_default
{
    
    /**
     * {@inheritDoc}
     * @see \block_courses_showcase\viewtype\crw_default::extend_edit_form()
     */
    public function extend_edit_form(&$mform)
    {
        $categoriesrenderers = crw::get_crw_subplugins(20);
        array_walk(
            $categoriesrenderers,
            function(&$item, $key) {
                $item = get_string('pluginname', 'crw_' . $key);
            }
        );
        
        // Способ отрисовки курсов
        $mform->addElement(
            'select',
            'config_categories_list__categories_renderer',
            get_string('categories_list__categories_renderer', 'block_courses_showcase'),
            $categoriesrenderers
        );
        $defaultrenderer = $this->block->config->categories_list__categories_renderer ?? 'categories_list_universal';
        $mform->setDefault('config_categories_list__categories_renderer', $defaultrenderer);
        
    }
    
    protected function get_showcase_properties()
    {
        $properties = [
            'forced_showcase_slots' => [
                $this->block->config->categories_list__categories_renderer ?? 'categories_list_universal'
            ]
        ];
        
        return $properties;
    }
}