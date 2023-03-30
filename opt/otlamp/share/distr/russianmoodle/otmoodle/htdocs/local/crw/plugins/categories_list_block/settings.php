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
 * Блок списка категорий в виде плагина Блок. Настройки.
 * 
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) 
{
// Заголовок - Настройки блока категорий
    $name = 'crw_categories_list_block/crw_categories_list_block_title';
    $title = get_string('settings_title','crw_categories_list_block');
    $description = get_string('settings_title_desc','crw_categories_list_block');
    $setting = new admin_setting_heading($name, $title, $description);
    $settings->add($setting);
    
    // Cкрытие заголовка
    $choices = [
        0 => get_string('no', 'local_crw'),
        1 => get_string('yes', 'local_crw')
    ];
    $name = 'crw_categories_list_block/hide_cat_block_title';
    $title = get_string('settings_hide_cat_block_title_title','crw_categories_list_block');
    $description = get_string('settings_hide_cat_block_title_desc','crw_categories_list_block');
    $default = 0;
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);
    
    // Позиция блока
    $settings->add(new admin_setting_configtext(
            'crw_categories_list_block/region',
            get_string('settings_block_region','crw_categories_list_block'),
            get_string('settings_block_region_desc','crw_categories_list_block'),
            ''
        )
    );
    // Очередность блока
    $settings->add(new admin_setting_configtext(
            'crw_categories_list_block/weight',
            get_string('settings_block_weight','crw_categories_list_block'),
            get_string('settings_block_weight_desc','crw_categories_list_block'),
            '1'
    )
    );
}
