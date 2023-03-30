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
 * Витрина курсов. Настройки.
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig)
{// Имеются прова на конфигурирование плагина
    $category = new admin_category(
            'localcrw',
            get_string('pluginname', 'local_crw')
            );
    // Добавим категорию настроек
    $ADMIN->add('localplugins', $category);

    // Объявляем страницу настроек плагина
    $settings = new admin_settingpage('crw_settings', get_string('settings_general', 'local_crw'));

    if ($ADMIN->fulltree)
    {// Требуется подгрузка страницы настроек

        // Подключим класс формирования панели управления субплагинами
        require_once(__DIR__.'/adminlib.php');
        require_once($CFG->dirroot.'/local/crw/lib.php');
        require_once($CFG->dirroot . '/local/crw/classes/plugin.php');
        // Добавми панель управления субплагинами
        $settings->add(new crw_subplugins_settings());

    // Заголовок - Общие настройки плагина
        $name = 'local_crw/title_general';
        $title = get_string('settings_title_general','local_crw');
        $description = get_string('settings_title_general_desc','local_crw');
        $setting = new admin_setting_heading($name, $title, $description);
        $settings->add($setting);

        // Сформируем массив доступных субплагинов
        $plugins = array('' => get_string('settings_plugins_empty', 'local_crw'));
        $subplugins = core_component::get_plugin_list('crw');
        foreach( $subplugins as $pluginname => $path )
        {
            $plugins[$pluginname] = get_string('pluginname', "crw_$pluginname");
        }

    // Слоты на странице витрины
        $name = 'local_crw/slots_cs_header';
        $title = get_string('settings_slots_cs_header', 'local_crw');
        $description = get_string('settings_slots_cs_header_desc', 'local_crw');
        $setting = new admin_setting_configselect($name, $title, $description, NULL, $plugins);
        $setting->set_updatedcallback('purge_all_caches');
        $settings->add($setting);

        $name = 'local_crw/slots_cs_top';
        $title = get_string('settings_slots_cs_top', 'local_crw');
        $description = get_string('settings_slots_cs_top_desc', 'local_crw');
        $setting = new admin_setting_configselect($name, $title, $description, NULL, $plugins);
        $setting->set_updatedcallback('purge_all_caches');
        $settings->add($setting);

        $name = 'local_crw/slots_cs_bottom';
        $title = get_string('settings_slots_cs_bottom', 'local_crw');
        $description = get_string('settings_slots_cs_bottom_desc', 'local_crw');
        $setting = new admin_setting_configselect($name, $title, $description, NULL, $plugins);
        $setting->set_updatedcallback('purge_all_caches');
        $settings->add($setting);

        // Лимит курсов на одной странице
        $name = 'local_crw/courses_pagelimit';
        $title = get_string('settings_courses_pagelimit', 'local_crw');
        $description = get_string('settings_courses_pagelimit_desc', 'local_crw');
        $default = 24;
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $settings->add($setting);

        // Отображение инструмента для выбора количества отображаемых на странице курсов
        $name = 'local_crw/display_pagelimit_change_tool';
        $title = get_string('settings_display_pagelimit_change_tool', 'local_crw');
        $description = get_string('settings_display_pagelimit_change_tool_desc', 'local_crw');
        $choices = [
            0b000 => get_string('settings_display_pagelimit_change_tool_nowhere', 'local_crw'),
            0b001 => get_string('settings_display_pagelimit_change_tool_top', 'local_crw'),
            0b010 => get_string('settings_display_pagelimit_change_tool_bottom', 'local_crw'),
            0b011 => get_string('settings_display_pagelimit_change_tool_topbottom', 'local_crw'),
        ];
        $default = 0b000;
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $settings->add($setting);

        // Отображение пейджинга
        $name = 'local_crw/display_paging';
        $title = get_string('settings_display_paging', 'local_crw');
        $description = get_string('settings_display_paging_desc', 'local_crw');
        $choices = [
            0b000 => get_string('settings_display_paging_nowhere', 'local_crw'),
            0b001 => get_string('settings_display_paging_top', 'local_crw'),
            0b010 => get_string('settings_display_paging_bottom', 'local_crw'),
            0b011 => get_string('settings_display_paging_topbottom', 'local_crw'),
        ];
        $default = 0b010;
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $settings->add($setting);

        // Отображение статистики отображенных курсов
        $name = 'local_crw/display_statistics';
        $title = get_string('settings_display_statistics', 'local_crw');
        $description = get_string('settings_display_statistics_desc', 'local_crw');
        $choices = [
            0b000 => get_string('settings_display_statistics_nowhere', 'local_crw'),
            0b001 => get_string('settings_display_statistics_top', 'local_crw'),
            0b010 => get_string('settings_display_statistics_bottom', 'local_crw'),
            0b011 => get_string('settings_display_statistics_topbottom', 'local_crw'),
        ];
        $default = 0b000;
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $settings->add($setting);

        // Загрузка курсов по ajax
        $choices = [
            1 => get_string('yes', 'local_crw'),
            0 => get_string('no', 'local_crw')
        ];
        $settings->add(
            new admin_setting_configselect(
                'local_crw/ajax_courses_flow',
                get_string('settings_ajax_courses_flow', 'local_crw'),
                get_string('settings_ajax_courses_flow_desc', 'local_crw'),
                0,
                $choices
            )
        );

        // Автоматически загружать курсы при достижении конца ленты
        $choices = [
            1 => get_string('yes', 'local_crw'),
            0 => get_string('no', 'local_crw')
        ];
        $settings->add(
            new admin_setting_configselect(
                'local_crw/ajax_courses_flow_autoload',
                get_string('settings_ajax_courses_flow_autoload', 'local_crw'),
                get_string('settings_ajax_courses_flow_autoload_desc', 'local_crw'),
                0,
                $choices
            )
        );


        // Отображать курсы вложенных категорий
        $choices = [
            1 => get_string('yes', 'local_crw'),
            0 => get_string('no', 'local_crw')
        ];
        $settings->add(new admin_setting_configselect(
                'local_crw/display_invested_courses',
                get_string('settings_display_invested_courses', 'local_crw'),
                get_string('settings_display_invested_courses_desc', 'local_crw'),
                1,
                $choices
            )
        );

        // Отображение страницы дополнительного описания курса
        $choices = [
            1 => get_string('settings_show_course_info_page_for_all_users', 'local_crw'),
            2 => get_string('settings_redirect_all_enrolled_users', 'local_crw'),
            3 => get_string('settings_hide_course_info_page', 'local_crw')
        ];
        $name = 'local_crw/course_info_view';
        $title = get_string('settings_course_info_view_title', 'local_crw');
        $description = get_string('settings_course_info_view_desc', 'local_crw');
        $setting = new admin_setting_configselect($name, $title, $description, 0, $choices);
        $setting->set_updatedcallback('purge_all_caches');
        $settings->add($setting);

        // Отображать контакты курса
        $choices = [
            0 => get_string('no', 'local_crw'),
            1 => get_string('yes', 'local_crw')
        ];
        $settings->add(new admin_setting_configselect(
            'local_crw/hide_course_contacts',
            get_string('settings_hide_course_contacts_title', 'local_crw'),
            get_string('settings_hide_course_contacts_desc', 'local_crw'),
            0,
            $choices
        ));

        // Отображать галерею курса
        $choices = [
            0 => get_string('no', 'local_crw'),
            1 => get_string('yes', 'local_crw')
        ];
        $settings->add(new admin_setting_configselect(
            'local_crw/hide_course_gallery',
            get_string('settings_hide_course_gallery_title', 'local_crw'),
            get_string('settings_hide_course_gallery_desc', 'local_crw'),
            0,
            $choices
        ));

        // Главная категория витрины
        $categories = \core_course_category::make_categories_list();
        $choices = [ 0 => get_string('settings_main_catid_not_set', 'local_crw') ] + $categories;
        $settings->add(new admin_setting_configselect(
                'local_crw/main_catid',
                get_string('settings_main_catid', 'local_crw'),
                get_string('settings_main_catid_desc', 'local_crw'),
                0,
                $choices
            )
        );

        // Настройка отображения категорий, расположенных в ветках дерева, отличного от главной категории витрины
        $settings->add(new admin_setting_configcheckbox(
            'local_crw/display_not_nested',
            get_string('display_not_nested_title', 'local_crw'),
            get_string('display_not_nested_desc', 'local_crw'),
            0
        ));

        // Настройка структуры доп.полей курса
        $settings->add(new admin_setting_configtextarea(
            'local_crw/custom_course_fields',
            get_string('settings_custom_course_fields_title', 'local_crw'),
            get_string('settings_custom_course_fields_desc', 'local_crw'),
            ''
        ));

        // Отображение настраиваемых полей курса
        $choices = [
            0 => get_string('no', 'local_crw'),
            1 => get_string('yes', 'local_crw')
        ];
        $settings->add(new admin_setting_configselect(
            'local_crw/custom_fields_view',
            get_string('settings_custom_fields_view_title', 'local_crw'),
            get_string('settings_custom_fields_view_desc', 'local_crw'),
            1,
            $choices
        ));

        $allsorttypes = local_crw_get_all_default_sort_types();
        $settings->add(new admin_setting_configmultiselect(
            'local_crw/course_sort_types',
            get_string('settings_course_sort_types', 'local_crw'),
            get_string('settings_course_sort_types_desc', 'local_crw'),
            array_keys($allsorttypes),
            $allsorttypes
        ));

        // Сортировка курсов
        $sorttypes = local_crw_get_allowed_basic_sort_types();
        reset($sorttypes);
        $settings->add(new admin_setting_configselect(
            'local_crw/course_sort_type',
            get_string('settings_course_sort_type', 'local_crw'),
            get_string('settings_course_sort_type_desc', 'local_crw'),
            key($sorttypes),
            $sorttypes
        ));

        // Направление сортировки
        $sortdirections = [
            'ASC' => get_string('courses_sort_direction_asceding', 'local_crw'),
            'DESC' => get_string('courses_sort_direction_desceding', 'local_crw')
        ];

        $settings->add(new admin_setting_configselect(
            'local_crw/course_sort_direction',
            get_string('settings_course_sort_direction', 'local_crw'),
            get_string('settings_course_sort_direction_desc', 'local_crw'),
            'ASC',
            $sortdirections
        ));

        $settings->add(new admin_setting_configselect(
            'local_crw/course_popularity_type',
            get_string('settings_course_popularity_type', 'local_crw'),
            get_string('settings_course_popularity_type_desc', 'local_crw'),
            'unique_course_view',
            ['unique_course_view' => get_string('popularity_unique_course_view', 'local_crw')]
        ));


        $settings->add(new admin_setting_configselect(
            'local_crw/coursepage_template',
            get_string('settings_coursepage_template', 'local_crw'),
            get_string('settings_coursepage_template_desc', 'local_crw'),
            'base',
            local_crw_get_coursepage_templates()
        ));

        // Переопределение навигации в хлебных крошках
        $settings->add(new admin_setting_configselect(
            'local_crw/override_navigation',
            get_string('settings_override_navigation', 'local_crw'),
            get_string('settings_override_navigation_desc', 'local_crw'),
            1,
            $choices
        ));

        // Настройка для скрытия узла "Курсы" (отображается неподписанным имеющим доступ) или
        // узла "Мои курсы" (отображается подписанным в курс пользователям) из хлебных крошек.
        // Разработана по заказу клиента.
        // Во время разработки появилась теория, что эта настройка полезна только когда на главной в любом виде
        // имеется витрина (через конфиг или блок), а если её там нет, то убирать узел не стоит, он идентичен
        // узлу "Витрина курсов", через которую только, по всей видимости, пользователь и может добраться до курса.
        // В текущей реализации скрывается всегда по настройке.
        $settings->add(new admin_setting_configcheckbox(
            'local_crw/remove_courses_nav_node',
            get_string('settings_remove_courses_nav_node', 'local_crw'),
            get_string('settings_remove_courses_nav_node_desc', 'local_crw'),
            0
        ));

    }
        // Добавим страницу основных нестроек в меню администратора
        $ADMIN->add('localcrw', $settings);

        // Добавим страницы настроек для всех субплагинов
        foreach ( core_plugin_manager::instance()->get_plugins_of_type('crw') as $plugin )
        {
            $plugin->load_settings($ADMIN, 'localcrw', $hassiteconfig, $category);
        }
}

// У плагина нет стандартной страницы настроек, вернем NULL
$settings = NULL;