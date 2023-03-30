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
 * Блок история обучения. Настройки.
 *
 * @package    block
 * @subpackage mylearninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/opentechnology/classes/admin_setting/button.php');

if ($ADMIN->fulltree)
{
    $blockname = 'block_mylearninghistory';

    // Заголовок - настройки для "я изучаю"
    $configname = 'header_learning';
    $name = $blockname.'/'.$configname;
    $visiblename = get_string('config_'.$configname, $blockname);
    $description = get_string('config_'.$configname.'_desc', $blockname);
    $setting = new admin_setting_heading($name, $visiblename, $description);
    $settings->add($setting);

        // Выводить оценку для раздела "я изучаю"
        $configname = 'learning_grade';
        $name = $blockname.'/'.$configname;
        $visiblename = get_string('config_'.$configname, $blockname);
        $description = get_string('config_'.$configname.'_desc', $blockname);
        $setting = new admin_setting_configcheckbox($name, $visiblename, $description, 1);
        $settings->add($setting);
        
        // Выводить количество освоенных компетенций в курсе для раздела "я изучаю"
        $configname = 'learning_competencies';
        $name = $blockname.'/'.$configname;
        $visiblename = get_string('config_'.$configname, $blockname);
        $description = get_string('config_'.$configname.'_desc', $blockname);
        $setting = new admin_setting_configcheckbox($name, $visiblename, $description, 1);
        $settings->add($setting);
        
        // Выводить статус завершения для раздела "я изучаю"
        $configname = 'learning_progress';
        $name = $blockname.'/'.$configname;
        $visiblename = get_string('config_'.$configname, $blockname);
        $description = get_string('config_'.$configname.'_desc', $blockname);
        $setting = new admin_setting_configcheckbox($name, $visiblename, $description, 1);
        $settings->add($setting);
        
        // Выводить сведения о подписке для раздела "я изучаю"
        $configname = 'learning_enroldata';
        $name = $blockname.'/'.$configname;
        $visiblename = get_string('config_'.$configname, $blockname);
        $description = get_string('config_'.$configname.'_desc', $blockname);
        $setting = new admin_setting_configcheckbox($name, $visiblename, $description, 1);
        $settings->add($setting);
        
        // Показывать максимальный итоговый балл по курсу
        $configname = 'max_grade';
        $name = $blockname.'/'.$configname;
        $visiblename = get_string('config_'.$configname, $blockname);
        $description = get_string('config_'.$configname.'_desc', $blockname);
        $setting = new admin_setting_configcheckbox($name, $visiblename, $description, 1);
        $settings->add($setting);
        
        // Переключение режима отображения изучаемых курсов
        $configname = 'view_type';
        $name = $blockname.'/'.$configname;
        $visiblename = get_string('config_'.$configname, $blockname);
        $description = get_string('config_'.$configname.'_desc', $blockname);
        $setting = new admin_setting_configcheckbox($name, $visiblename, $description, 0);
        $settings->add($setting);
        
        // Группировать по
        $configname = 'learning_group_by';
        $name = $blockname.'/'.$configname;
        $visiblename = get_string('config_'.$configname, $blockname);
        $description = get_string('config_'.$configname.'_desc', $blockname);
        $groupbyoptions = [
            '0' => get_string('config_'.$configname.'_nothing', $blockname),
            '1' => get_string('config_'.$configname.'_category_name', $blockname)
        ];
        $setting = new admin_setting_configselect($name, $visiblename, $description, 0, $groupbyoptions);
        $settings->add($setting);
        
        // На какие страницы перенаправлять пользователя по ссылкам
        $configname = 'learning_course_link_url';
        $name = $blockname.'/'.$configname;
        $visiblename = get_string('config_'.$configname, $blockname);
        $description = get_string('config_'.$configname.'_desc', $blockname);
        $courselinkurls = [
            'course' => get_string('config_'.$configname.'_course', $blockname),
            'crw' => get_string('config_'.$configname.'_crw', $blockname)
        ];
        $setting = new admin_setting_configselect($name, $visiblename, $description, 'course', $courselinkurls);
        $settings->add($setting);
        
        // Вариант отображения оценок
        $configname = 'learning_grade_view';
        $name = $blockname.'/'.$configname;
        $visiblename = get_string('config_'.$configname, $blockname);
        $description = get_string('config_'.$configname.'_desc', $blockname);
        $views = [
            'overflowhidden' => get_string('config_'.$configname.'_overflowhidden', $blockname),
            'overflowauto' => get_string('config_'.$configname.'_overflowauto', $blockname)
        ];
        $setting = new admin_setting_configselect($name, $visiblename, $description, 'course', $views);
        $settings->add($setting);
        
        // Настройка фильтрации курсов
        $configname = 'learning_courses_filter';
        $name = $blockname.'/'.$configname;
        $visiblename = get_string('config_'.$configname, $blockname);
        $description = get_string('config_'.$configname.'_desc', $blockname);
        $frontendhandler = 'block_mylearninghistory/courses_filter';
        $setting = new \local_opentechnology\admin_setting\button($name, $visiblename, $description, $frontendhandler);
        $setting->set_dialogue_header($visiblename);
        $setting->set_button_data([
            'buttontext' => get_string('config_learning_courses_filter_button', 'block_mylearninghistory'),
            'buttonclasses' => ['btn', 'btn-secondary', 'learning-courses-filter-button']
        ]);
        $setting->set_init_options([
            'class' => 'coursesfilter',
            'config' => $configname,
            'methodname' => 'block_mylearninghistory_get_courses_filter_form'
        ]);
        $settings->add($setting);
        
        // Настройка правил фильтрации курсов
        $configname = 'learning_courses_filter_rules';
        $name = $blockname.'/'.$configname;
        $visiblename = get_string('config_'.$configname, $blockname);
        $description = get_string('config_'.$configname.'_desc', $blockname);
        $frontendhandler = 'block_mylearninghistory/courses_filter';
        $setting = new \local_opentechnology\admin_setting\button($name, $visiblename, $description, $frontendhandler);
        $setting->set_dialogue_header($visiblename);
        $setting->set_button_data([
            'buttontext' => get_string('config_learning_courses_filter_rules_button', 'block_mylearninghistory'),
            'buttonclasses' => ['btn', 'btn-secondary', 'learning-courses-filter-rules-button']
        ]);
        $setting->set_init_options([
            'class' => 'coursesfilterrules',
            'config' => $configname,
            'methodname' => 'block_mylearninghistory_get_courses_filter_rules_form'
        ]);
        $settings->add($setting);
    

    // Заголовок - настройки для "я преподаю"
    $configname = 'header_teaching';
    $name = $blockname.'/'.$configname;
    $visiblename = get_string('config_'.$configname, $blockname);
    $description = get_string('config_'.$configname.'_desc', $blockname);
    $setting = new admin_setting_heading($name, $visiblename, $description);
    $settings->add($setting);
    
        // Выводить количество подписанных на курс для раздела "я преподаю"
        $configname = 'teaching_enrolscount';
        $name = $blockname.'/'.$configname;
        $visiblename = get_string('config_'.$configname, $blockname);
        $description = get_string('config_'.$configname.'_desc', $blockname);
        $setting = new admin_setting_configcheckbox($name, $visiblename, $description, 1);
        $settings->add($setting);
        
        // Выводить сведения о подписке для раздела "я преподаю"
        $configname = 'teaching_enroldata';
        $name = $blockname.'/'.$configname;
        $visiblename = get_string('config_'.$configname, $blockname);
        $description = get_string('config_'.$configname.'_desc', $blockname);
        $setting = new admin_setting_configcheckbox($name, $visiblename, $description, 1);
        $settings->add($setting);
        
        // Группировать по родительской категории
        $configname = 'teaching_group_by';
        $name = $blockname.'/'.$configname;
        $visiblename = get_string('config_'.$configname, $blockname);
        $description = get_string('config_'.$configname.'_desc', $blockname);
        $groupbyoptions = [
            '0' => get_string('config_'.$configname.'_nothing', $blockname),
            '1' => get_string('config_'.$configname.'_category_name', $blockname)
        ];
        $setting = new admin_setting_configselect($name, $visiblename, $description, 0, $groupbyoptions);
        $settings->add($setting);
        
        // На какие страницы перенаправлять пользователя по ссылкам
        $configname = 'teaching_course_link_url';
        $name = $blockname.'/'.$configname;
        $visiblename = get_string('config_'.$configname, $blockname);
        $description = get_string('config_'.$configname.'_desc', $blockname);
        $courselinkurls = [
            'course' => get_string('config_'.$configname.'_course', $blockname),
            'crw' => get_string('config_'.$configname.'_crw', $blockname)
        ];
        $setting = new admin_setting_configselect($name, $visiblename, $description, 'course', $courselinkurls);
        $settings->add($setting);
        
        // Настройка фильтрации курсов
        $configname = 'teaching_courses_filter';
        $name = $blockname.'/'.$configname;
        $visiblename = get_string('config_'.$configname, $blockname);
        $description = get_string('config_'.$configname.'_desc', $blockname);
        $frontendhandler = 'block_mylearninghistory/courses_filter';
        $setting = new \local_opentechnology\admin_setting\button($name, $visiblename, $description, $frontendhandler);
        $setting->set_dialogue_header($visiblename);
        $setting->set_button_data([
            'buttontext' => get_string('config_teaching_courses_filter_button', 'block_mylearninghistory'),
            'buttonclasses' => ['btn', 'btn-secondary', 'teaching-courses-filter-button']
        ]);
        $setting->set_init_options([
            'class' => 'coursesfilter',
            'config' => $configname,
            'methodname' => 'block_mylearninghistory_get_courses_filter_form'
        ]);
        $settings->add($setting);
        
        // Настройка правил фильтрации курсов
        $configname = 'teaching_courses_filter_rules';
        $name = $blockname.'/'.$configname;
        $visiblename = get_string('config_'.$configname, $blockname);
        $description = get_string('config_'.$configname.'_desc', $blockname);
        $frontendhandler = 'block_mylearninghistory/courses_filter';
        $setting = new \local_opentechnology\admin_setting\button($name, $visiblename, $description, $frontendhandler);
        $setting->set_dialogue_header($visiblename);
        $setting->set_button_data([
            'buttontext' => get_string('config_teaching_courses_filter_rules_button', 'block_mylearninghistory'),
            'buttonclasses' => ['btn', 'btn-secondary', 'teaching-courses-filter-rules-button']
        ]);
        $setting->set_init_options([
            'class' => 'coursesfilterrules',
            'config' => $configname,
            'methodname' => 'block_mylearninghistory_get_courses_filter_rules_form'
        ]);
        $settings->add($setting);
    
}