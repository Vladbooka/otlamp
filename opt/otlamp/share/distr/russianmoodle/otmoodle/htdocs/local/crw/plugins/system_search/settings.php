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
 * Плагин поиска курсов. Настройки.
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/local/crw/locallib.php');
require_once($CFG->dirroot . '/local/crw/plugins/system_search/lib.php');

// Добавление категории для настроек поиска
$category = new admin_category(
    'crw_system_search_category',
    get_string('crw_system_search_category', 'crw_system_search')
);
$parentcategory->add($parentcategory->name, $category);

// страница с общими настройками сабплагина поиска
$settingsf = new admin_settingpage('crw_system_search_settings', get_string('crw_system_search_settings', 'crw_system_search'));
$settingss = new crw_system_search_admin_settingspage_tabs('crw_system_search_filters_settings', get_string('crw_system_search_filters_settings', 'crw_system_search'));
$hintssettings = new admin_settingpage('crw_system_search_hints_settings', get_string('crw_system_search_hints_settings', 'crw_system_search'));

if ($ADMIN->fulltree)
{
    ////////////////////////////////////////////
    // страница общих настроек поиска
    ////////////////////////////////////////////
    
        // Формат отображения результатов поиска
        $settingsf->add(
            new admin_setting_configselect(
                'crw_system_search/settings_query_string_role',
                get_string('settings_query_string_role','crw_system_search'),
                get_string('settings_query_string_role_desc','crw_system_search'),
                'name',
                [
                    'name' =>  get_string('settings_query_string_role_name', 'crw_system_search'),
                    'hints' =>  get_string('settings_query_string_role_hints', 'crw_system_search'),
                    'none' => get_string('settings_query_string_role_none', 'crw_system_search'),
                ]
            )
        );
        
        // Внешний вид
        $name = 'crw_system_search/settings_style';
        $title = get_string('settings_style','crw_system_search');
        $description = get_string('settings_style_desc','crw_system_search');
        $setting = new admin_setting_configselect($name, $title, $description, 'default', [
            'default' => get_string('settings_style_default', 'crw_system_search'),
            'minimalism' => get_string('settings_style_minimalism', 'crw_system_search')
        ]);
        $settingsf->add($setting);
        
    
        // Заголовок - Настройки поиска курсов
        $name = 'crw_system_search/crw_system_search_title';
        $title = get_string('settings_title','crw_system_search');
        $description = get_string('settings_title_desc','crw_system_search');
        $setting = new admin_setting_heading($name, $title, $description);
        $settingsf->add($setting);
        
    
        // Описание формы поиска
        $name = 'crw_system_search/settings_formdescription';
        $title = get_string('settings_formdescription','crw_system_search');
        $description = get_string('settings_formdescription_desc','crw_system_search');
        $default = get_string('searchform_description', 'crw_system_search');
        $setting = new admin_setting_configtextarea($name, $title, $description, $default);
        $settingsf->add($setting);
        
        
        // Только расширенный поиск (всегда отображать в развернутом виде)
        $name = 'crw_system_search/settings_fullsearch_only';
        $title = get_string('settings_fullsearch_only','crw_system_search');
        $description = get_string('settings_fullsearch_only_desc','crw_system_search');
        $setting = new admin_setting_configcheckbox($name, $title, $description, '0');
        $settingsf->add($setting);
        
        // Скрыть кнопку очистки формы
        $name = 'crw_system_search/settings_hide_reset_button';
        $title = get_string('settings_hide_reset_button','crw_system_search');
        $description = get_string('settings_hide_reset_button_desc','crw_system_search');
        $setting = new admin_setting_configcheckbox($name, $title, $description, '0');
        $settingsf->add($setting);
        
        // Выполнять поиск без перезагрузки страницы (результаты будут отображаться под поисковыми фильтрами, работает только если в браузере включен js)
        $name = 'crw_system_search/settings_ajax_search';
        $title = get_string('settings_ajax_search','crw_system_search');
        $description = get_string('settings_ajax_search_desc','crw_system_search');
        $setting = new admin_setting_configcheckbox($name, $title, $description, '0');
        $settingsf->add($setting);
        
        // Отображать результаты на странице, где был выполнен поиск
        $name = 'crw_system_search/settings_display_results_inplace';
        $title = get_string('settings_display_results_inplace','crw_system_search');
        $description = get_string('settings_display_results_inplace_desc','crw_system_search');
        $setting = new admin_setting_configcheckbox($name, $title, $description, '0');
        $settingsf->add($setting);
        
        $renderers = [];
        foreach(array_keys(local_crw_get_plugin_type_courses_list()) as $subpluginname)
        {
            $renderers[$subpluginname] = get_string('pluginname', 'crw_' . $subpluginname);
        }
        // Формат отображения результатов поиска
        $settingsf->add(new admin_setting_configselect(
            'crw_system_search/search_result_renderer',
            get_string('setting_search_result_renderer','crw_system_search'),
            get_string('setting_search_result_renderer_desc','crw_system_search'),
            'courses_list_tiles',
            $renderers
            )
        );
        
        
        // Формат отображения результатов поиска
        $settingsf->add(
            new admin_setting_configselect(
                'crw_system_search/settings_single_result_redirect',
                get_string('settings_single_result_redirect','crw_system_search'),
                get_string('settings_single_result_redirect_desc','crw_system_search'),
                'id_specified',
                [
                    'never' =>  get_string('single_result_redirect_never', 'crw_system_search'),
                    'always' =>  get_string('single_result_redirect_always', 'crw_system_search'),
                    'id_specified' =>  get_string('single_result_redirect_id_specified', 'crw_system_search')
                ]
            )
        );
        
        
        // Отображать настройку сортировки в списке фильтров
        $name = 'crw_system_search/settings_display_sorter';
        $title = get_string('settings_display_sorter','crw_system_search');
        $description = get_string('settings_display_sorter_desc','crw_system_search');
        $setting = new admin_setting_configcheckbox($name, $title, $description, '0');
        $settingsf->add($setting);
        
        
    ////////////////////////////////////////////
    // Настройки фильтров поиска
    ////////////////////////////////////////////
        
        
        ////////////////////////////////////////////
        // Основные фильтры
        ////////////////////////////////////////////
        
        $filtertabgeneral = new admin_settingpage('crw_system_search_filtertab_general', get_string('crw_system_search_filtertab_general', 'crw_system_search'));
        
        $choices = array(
            0 => get_string('no'),
            1 => get_string('yes')
        );
        // Отображать поиск по дате начала курса
        $filtertabgeneral->add(new admin_setting_configselect(
                'crw_system_search/settings_displayfilter_datestart',
                get_string('settings_displayfilter_datestart','crw_system_search'),
                get_string('settings_displayfilter_datestart_desc','crw_system_search'),
                1,
                $choices
            )
        );
        // Отображать поиск по стоимости курса
        $filtertabgeneral->add(new admin_setting_configselect(
                'crw_system_search/settings_displayfilter_cost',
                get_string('settings_displayfilter_cost','crw_system_search'),
                get_string('settings_displayfilter_cost_desc','crw_system_search'),
                1,
                $choices
            )
        );
        // Отображать поиск по контактам курса
        $filtertabgeneral->add(new admin_setting_configselect(
                'crw_system_search/settings_displayfilter_coursecontacts',
                get_string('settings_displayfilter_coursecontacts','crw_system_search'),
                get_string('settings_displayfilter_coursecontacts_desc','crw_system_search'),
                0,
                $choices
            )
        );
        // Отображать поиск по тегам
        $filtertabgeneral->add(new admin_setting_configselect(
                'crw_system_search/settings_displayfilter_tags',
                get_string('settings_displayfilter_tags','crw_system_search'),
                get_string('settings_displayfilter_tags_desc','crw_system_search'),
                0,
                $choices
            )
        );
        
        // Поиск по тегам курсов
        $name = 'crw_system_search/settings_exclude_standard_tags';
        $title = get_string('settings_exclude_standard_tags','crw_system_search');
        $description = get_string('settings_exclude_standard_tags_desc','crw_system_search');
        $coursecollection = core_tag_area::get_collection('core', 'course');
        $tagcloud = core_tag_collection::get_tag_cloud($coursecollection, true);
        $tags = $tagcloud->export_for_template($OUTPUT)->tags;
        $choices = [];
        if( ! empty($tags) )
        {
            foreach($tags as $tag)
            {
                $tagrecord = core_tag_tag::get_by_name($coursecollection, $tag->name);
                if( ! empty($tagrecord) )
                {
                    $choices[$tagrecord->id] =$tagrecord->get_display_name(true);
                }
            }
            $setting = new admin_setting_configmulticheckbox($name, $title, $description, [], $choices);
            $filtertabgeneral->add($setting);
        }
        
        // Логика поиска курсов по тегам
        $name = 'crw_system_search/settings_tagfilter_logic';
        $title = get_string('settings_tagfilter_logic','crw_system_search');
        $description = get_string('settings_tagfilter_logic_desc','crw_system_search');
        $choices = [
            '0' => get_string('settings_tagfilter_logic_or','crw_system_search'),
            '1' => get_string('settings_tagfilter_logic_and','crw_system_search'),
        ];
        $setting = new admin_setting_configselect($name, $title, $description, '0', $choices);
        $filtertabgeneral->add($setting);
        
        $settingss->add($filtertabgeneral);
        
        ////////////////////////////////////////////
        // Фильтры по кастомным полям
        ////////////////////////////////////////////
        
        $filtertabcustom = new admin_settingpage('crw_system_search_filtertab_custom', get_string('crw_system_search_filtertab_custom', 'crw_system_search'));
        
        $filtertabcustom->add(new admin_setting_heading(
            'crw_system_search/settings_filter_customfields_heading',
            get_string('settings_filter_customfields_heading', 'crw_system_search'),
            get_string('settings_filter_customfields_heading_desc', 'crw_system_search')
        ));
        
        
        foreach(local_crw_get_custom_fields() as $fieldname => $cffield)
        {
            if (!crw_system_search_is_custom_field_searchable($cffield))
            {
                continue;
            }
            
            $yamlfield = \otcomponent_yaml\Yaml::dump([$fieldname => $cffield]);
            
            $filtertabcustom->add(new admin_setting_configcheckbox(
                'crw_system_search/settings_filter_customfield__'.$fieldname,
                $cffield['label'],
                html_writer::tag('PRE', $yamlfield),
                false
            ));
        }
        
        $settingss->add($filtertabcustom);
        
        
    ////////////////////////////////////////////
    // Настройка подсказок сквозного поиска
    ////////////////////////////////////////////
    
        $hintssettings->add(
            new admin_setting_heading(
                'crw_system_search/hints_settings_info',
                get_string('hints_settings_info','crw_system_search'),
                get_string('hints_settings_info_desc','crw_system_search')
            )
        );
        
        $name = 'crw_system_search/hints_settings_results_count';
        $title = get_string('hints_settings_results_count','crw_system_search');
        $description = get_string('hints_settings_results_count_desc','crw_system_search');
        $hintssettings->add(new admin_setting_configtext($name, $title, $description, 5, PARAM_INT));
    
        $name = 'crw_system_search/hints_settings_area_gs_crw_course';
        $title = get_string('hints_settings_area_gs_crw_course','crw_system_search');
        $description = get_string('hints_settings_area_gs_crw_course_desc','crw_system_search');
        $hintssettings->add(new admin_setting_configcheckbox($name, $title, $description, 1));
        
        $name = 'crw_system_search/hints_settings_area_gs_crw_course_contacts';
        $title = get_string('hints_settings_area_gs_crw_course_contacts','crw_system_search');
        $description = get_string('hints_settings_area_gs_crw_course_contacts_desc','crw_system_search');
        $hintssettings->add(new admin_setting_configcheckbox($name, $title, $description, 1));
        
        $name = 'crw_system_search/hints_settings_area_gs_crw_course_tags';
        $title = get_string('hints_settings_area_gs_crw_course_tags','crw_system_search');
        $description = get_string('hints_settings_area_gs_crw_course_tags_desc','crw_system_search');
        $hintssettings->add(new admin_setting_configcheckbox($name, $title, $description, 1));
        
        $name = 'crw_system_search/hints_settings_area_gs_crw_course_tagcollection_custom1';
        $title = get_string('hints_settings_area_gs_crw_course_tagcollection_custom1','crw_system_search');
        $description = get_string('hints_settings_area_gs_crw_course_tagcollection_custom1_desc','crw_system_search');
        $hintssettings->add(new admin_setting_configcheckbox($name, $title, $description, 1));
        
        $name = 'crw_system_search/hints_settings_area_gs_crw_course_tagcollection_custom2';
        $title = get_string('hints_settings_area_gs_crw_course_tagcollection_custom2','crw_system_search');
        $description = get_string('hints_settings_area_gs_crw_course_tagcollection_custom2_desc','crw_system_search');
        $hintssettings->add(new admin_setting_configcheckbox($name, $title, $description, 1));
        
        $name = 'crw_system_search/hints_settings_area_coursecontacts';
        $title = get_string('hints_settings_area_coursecontacts','crw_system_search');
        $description = get_string('hints_settings_area_coursecontacts_desc','crw_system_search');
        $hintssettings->add(new admin_setting_configcheckbox($name, $title, $description, 1));
        
        $name = 'crw_system_search/hints_settings_area_tags';
        $title = get_string('hints_settings_area_tags','crw_system_search');
        $description = get_string('hints_settings_area_tags_desc','crw_system_search');
        $hintssettings->add(new admin_setting_configcheckbox($name, $title, $description, 1));
        
        $name = 'crw_system_search/hints_settings_area_tagcollection_custom1';
        $title = get_string('hints_settings_area_tagcollection_custom1','crw_system_search');
        $description = get_string('hints_settings_area_tagcollection_custom1_desc','crw_system_search');
        $hintssettings->add(new admin_setting_configcheckbox($name, $title, $description, 1));
        
        $name = 'crw_system_search/hints_settings_area_tagcollection_custom2';
        $title = get_string('hints_settings_area_tagcollection_custom2','crw_system_search');
        $description = get_string('hints_settings_area_tagcollection_custom2_desc','crw_system_search');
        $hintssettings->add(new admin_setting_configcheckbox($name, $title, $description, 1));
}

////////////////////////////////////////////
// добавление страниц настроек в раздел
////////////////////////////////////////////

$category->add($category->name, $settingsf);
$category->add($category->name, $settingss);
$category->add($category->name, $hintssettings);

$settings = null;
