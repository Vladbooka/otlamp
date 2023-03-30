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
 * Блок списка категорий в виде ссылок с иконками. Настройки.
 * 
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) 
{
// Заголовок - Настройки блока категорий
    $name = 'crw_categories_list_icons/title';
    $title = get_string('settings_title','crw_categories_list_icons');
    $description = get_string('settings_title_desc','crw_categories_list_icons');
    $setting = new admin_setting_heading($name, $title, $description);
    $settings->add($setting);
    
    // Отображение заголовка
    $settings->add(new admin_setting_configselect(
        'crw_categories_list_icons/display_title', 
        get_string('settings_display_title', 'crw_categories_list_icons'), 
        get_string('settings_display_title_desc', 'crw_categories_list_icons'), 
        1, 
        array(
            '0' => get_string('no', 'local_crw'),
            '1' => get_string('yes', 'local_crw')
        )
    ));
    
    // Число категорий в ряду
    $name = 'crw_categories_list_icons/inline';
    $title = get_string('settings_inline','crw_categories_list_icons');
    $description = get_string('settings_inline_desc','crw_categories_list_icons');
    $default = 6;
    $choices = array(
            1  => "1",
            2  => "2",
            3  => "3",
            4  => "4",
            5  => "5",
            6  => "6",
            7  => "7",
            8  => "8"
    );
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);
    
    // Файл иконки
    $name = 'crw_categories_list_icons/iconfile';
    $title = get_string('settings_iconfile', 'crw_categories_list_icons');
    $description = get_string('settings_iconfile_desc', 'crw_categories_list_icons');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'icon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
    
    // Якоря вместо ссылок
    $settings->add(new admin_setting_configselect(
        'crw_categories_list_icons/anchor_enabled', 
        get_string('settings_anchor_enabled', 'crw_categories_list_icons'), 
        get_string('settings_anchor_enabled_desc', 'crw_categories_list_icons'), 
        0, 
        array(
            '0' => get_string('no', 'local_crw'),
            '1' => get_string('yes', 'local_crw')
        )
    ));
    
    // Включить кнопку Назад
    $settings->add(new admin_setting_configselect(
        'crw_categories_list_icons/enable_back_button', 
        get_string('settings_enable_back_button', 'crw_categories_list_icons'), 
        get_string('settings_enable_back_button_desc', 'crw_categories_list_icons'), 
        0, 
        array(
            '0' => get_string('no', 'local_crw'),
            '1' => get_string('yes', 'local_crw')
        )
    ));
    // Файл иконки кнопки Назад
    $name = 'crw_categories_list_icons/back_button_iconfile';
    $title = get_string('settings_back_button_iconfile', 'crw_categories_list_icons');
    $description = get_string('settings_back_button_iconfile_desc', 'crw_categories_list_icons');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'iconback');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
}
