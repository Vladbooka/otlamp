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
 * Блок списка курсов в виде плиток. Настройки.
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree)
{
    require_once($CFG->dirroot . '/local/crw/plugins/courses_list_universal/lib.php');
    // Заголовок - Настройки блока курсов
    $name = 'crw_courses_list_universal/settingspage_title';
    $title = get_string('settingspage_title','crw_courses_list_universal');
    $description = get_string('settingspage_title_desc','crw_courses_list_universal');
    $setting = new admin_setting_heading($name, $title, $description);
    $settings->add($setting);
    
    
    $plugin = new crw_courses_list_universal('courses_list_universal');
    $settings->add(new admin_setting_configselect(
        'crw_courses_list_universal/template',
        get_string('settings_template', 'crw_courses_list_universal'),
        get_string('settings_template_desc', 'crw_courses_list_universal'),
        'base',
        $plugin->get_courselist_templates()
    ));

}
