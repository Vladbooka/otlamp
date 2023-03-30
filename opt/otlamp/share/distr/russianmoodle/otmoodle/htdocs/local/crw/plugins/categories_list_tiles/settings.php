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
 * Блок списка категорий в виде плиток. Настройки.
 * 
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) 
{
// Заголовок - Настройки блока категорий
    $name = 'crw_categories_list_tiles/categoties_list_tiles_title';
    $title = get_string('settings_categoties_list_tiles_title','crw_categories_list_tiles');
    $description = get_string('settings_categoties_list_tiles_title_desc','crw_categories_list_tiles');
    $setting = new admin_setting_heading($name, $title, $description);
    $settings->add($setting);
    
    // Скрытие заголовка
    $choices = [
        0 => get_string('no', 'local_crw'),
        1 => get_string('yes', 'local_crw')
    ];
    $name = 'crw_categories_list_tiles/hide_cat_block_title';
    $title = get_string('settings_hide_cat_block_title_title','crw_categories_list_tiles');
    $description = get_string('settings_hide_cat_block_title_desc','crw_categories_list_tiles');
    $default = 0;
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);

    // Число плиток в одной строке
    $name = 'crw_categories_list_tiles/categoties_list_tiles_inline';
    $title = get_string('settings_categoties_list_tiles_inline','crw_categories_list_tiles');
    $description = get_string('settings_categoties_list_tiles_inline_desc','crw_categories_list_tiles');
    $default = 4;
    $choices = array(
        1 => 'xxx-large',
        2 => 'xx-large',
        3 => 'x-large',
        4 => 'large',
        5 => 'medium',
        6 => 'small',
        7 => 'x-small',
        8 => 'xx-small',
    );
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);
    
    // Иконка категорий курсов по умолчанию
    $settings->add(new admin_setting_configstoredfile(
        'crw_categories_list_tiles/categories_icon_default',
        get_string('settings_categories_icon_default', 'crw_categories_list_tiles'),
        get_string('settings_categories_icon_default_desc', 'crw_categories_list_tiles'),
        'categories_icon_default'
        )
    );
}
