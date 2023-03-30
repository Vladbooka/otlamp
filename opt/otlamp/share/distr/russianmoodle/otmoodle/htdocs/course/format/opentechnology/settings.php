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
 * Плагин формата курсов OpenTechnology. Настройки плагина.
 *
 * @package    format
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) 
{
    // Список блоков для автоматического добавления в позицию side-pre
    $name = 'format_opentechnology/default_blocks_region_side_pre';
    $title = get_string('settings_default_blocks_region_side_pre_title', 'format_opentechnology');
    $description = get_string('settings_default_blocks_region_side_pre_desc', 'format_opentechnology');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);
    
    // Переназначение кода позиции side-pre
    $name = 'format_opentechnology/region_side_pre_rename';
    $title = get_string('settings_region_side_pre_rename_title', 'format_opentechnology');
    $description = get_string('settings_region_side_pre_rename_desc', 'format_opentechnology');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);
    
    // Список блоков для автоматического добавления в позицию side_post
    $name = 'format_opentechnology/default_blocks_region_side_post';
    $title = get_string('settings_default_blocks_region_side_post_title', 'format_opentechnology');
    $description = get_string('settings_default_blocks_region_side_post_desc', 'format_opentechnology');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);
    
    // Переназначение кода позиции side-post
    $name = 'format_opentechnology/region_side_post_rename';
    $title = get_string('settings_region_side_post_rename_title', 'format_opentechnology');
    $description = get_string('settings_region_side_post_rename_desc', 'format_opentechnology');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);
    
    // Выравнивание заголовка по умолчанию
    $choices = [
        'left'   => get_string('settings_caption_align_option_left', 'format_opentechnology'),
        'center' => get_string('settings_caption_align_option_center', 'format_opentechnology'),
        'right'  => get_string('settings_caption_align_option_right', 'format_opentechnology')
    ];
    $name = 'format_opentechnology/caption_align';
    $title = get_string('settings_caption_align_title', 'format_opentechnology');
    $description = get_string('settings_caption_align_desc', 'format_opentechnology');
    $default = 'left';
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);
    
    // Режим отображения по умолчанию
    $choices = [
        'format_opentechnology_base'   => get_string('settings_format_opentechnology_base', 'format_opentechnology'),
        'format_opentechnology_spoiler'   => get_string('settings_format_opentechnology_spoiler', 'format_opentechnology'),
        'format_opentechnology_accordion' => get_string('settings_format_opentechnology_accordion', 'format_opentechnology'),
        'format_opentechnology_carousel' => get_string('settings_format_opentechnology_carousel', 'format_opentechnology')
    ];
    $name = 'format_opentechnology/display_mode';
    $title = get_string('settings_display_mode_title', 'format_opentechnology');
    $description = get_string('settings_display_mode_desc', 'format_opentechnology');
    $default = 'format_opentechnology_base';
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);

    // Ширина секции по умолчанию
    $choices = [
        '100' => '100%',
        '75' => '75%',
        '66' => '66.66%',
        '50' => '50%',
        '33' => '33.33%',
        '25' => '25%'
    ];
    $name = 'format_opentechnology/section_width';
    $title = get_string('settings_section_width', 'format_opentechnology');
    $description = get_string('settings_section_width_help', 'format_opentechnology');
    $default = '100';
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);
    
    // Ширина текста с описанием секции по умолчанию
    $choices = [
        '100' => '100%',
        '75' => '75%',
        '66' => '66.66%',
        '50' => '50%',
        '33' => '33.33%',
        '25' => '25%'
    ];
    $name = 'format_opentechnology/section_summary_width';
    $title = get_string('settings_section_summary_width', 'format_opentechnology');
    $description = get_string('settings_section_summary_width_help', 'format_opentechnology');
    $default = '100';
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);

    // Добавить иконки в заголовок секции
    $choices = [
        '0' => get_string('no'),
        '1' => get_string('yes')
    ];
    $name = 'format_opentechnology/section_lastinrow';
    $title = get_string('settings_section_lastinrow', 'format_opentechnology');
    $description = get_string('settings_section_lastinrow_help', 'format_opentechnology');
    $default = '0';
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);
    
    // Режим отображения по умолчанию
    $choices = [
        'format_opentechnology_base_elements_view'   => get_string('settings_format_opentechnology_base_elements_view', 'format_opentechnology'),
        'format_opentechnology_icon_elements_view' => get_string('settings_format_opentechnology_icon_elements_view', 'format_opentechnology'),
        'format_opentechnology_base_with_badges_elements_view'   => get_string('settings_format_opentechnology_base_with_badges_elements_view', 'format_opentechnology'),
        'format_opentechnology_icon_with_badges_elements_view' => get_string('settings_format_opentechnology_icon_with_badges_elements_view', 'format_opentechnology')
    ];
    $name = 'format_opentechnology/elements_display_mode';
    $title = get_string('settings_elements_display_mode_title', 'format_opentechnology');
    $description = get_string('settings_elements_display_mode_desc', 'format_opentechnology');
    $default = 'format_opentechnology_base_elements_view';
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);
    
    // Добавить иконки в заголовок секции
    $name = 'format_opentechnology/caption_icons_enabled';
    $title = get_string('settings_caption_icons_enabled_title', 'format_opentechnology');
    $description = get_string('settings_caption_icons_enabled_desc', 'format_opentechnology');
    $default = '';
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
    $settings->add($setting);
        
    // Иконка развернутой темы в курсе по умолчанию
    $name = 'format_opentechnology/caption_icon_toggle_open';
    $title = get_string('settings_caption_icon_open_title', 'format_opentechnology');
    $description = get_string('settings_caption_icon_open_desc', 'format_opentechnology');
    $filearea = 'caption_icon_toggle_open';
    $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea);
    $settings->add($setting);
    
    // Иконка свернутой темы в курсе по умолчанию
    $name = 'format_opentechnology/caption_icon_toggle_closed';
    $title = get_string('settings_caption_icon_closed_title', 'format_opentechnology');
    $description = get_string('settings_caption_icon_closed_desc', 'format_opentechnology');
    $filearea = 'caption_icon_toggle_closed';
    $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea);
    $settings->add($setting);
}