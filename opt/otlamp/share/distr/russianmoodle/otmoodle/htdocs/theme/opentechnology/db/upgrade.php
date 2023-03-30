<?php
use theme_opentechnology\profilemanager;
use theme_opentechnology\profilesettings;

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
 * Тема СЭО 3KL. Процесс обновления плагина.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function theme_opentechnology_rename_config($oldname, $newname)
{
    global $DB;

    $profilemanager = profilemanager::instance();
    foreach($profilemanager->get_profiles() as $profile)
    {
        $oldsettingname = $profile->get_setting_name($oldname);
        $newsettingname = $profile->get_setting_name($newname);
        $DB->set_field('config_plugins', 'name', $newsettingname, [
            'plugin' => 'theme_opentechnology',
            'name' => $oldsettingname
        ]);
    }
}

function theme_opentechnology_delete_config($name)
{
    global $DB;

    $profilemanager = profilemanager::instance();
    foreach($profilemanager->get_profiles() as $profile)
    {
        $settingname = $profile->get_setting_name($name);
        $DB->delete_records('config_plugins', [
            'plugin' => 'theme_opentechnology',
            'name' => $settingname
        ]);
    }
}

function theme_opentechnology_clone_config_value($source, $destination)
{
    global $DB;

    $profilemanager = profilemanager::instance();
    foreach($profilemanager->get_profiles() as $profile)
    {
        $sourcesettingname = $profile->get_setting_name($source);
        $sourcesettingvalue = $DB->get_field('config_plugins', 'value', [
            'plugin' => 'theme_opentechnology',
            'name' => $sourcesettingname
        ]);

        $destinationsettingname = $profile->get_setting_name($destination);
        $newconfig = $DB->get_record('config_plugins', [
            'plugin' => 'theme_opentechnology',
            'name' => $destinationsettingname
        ], '*', IGNORE_MULTIPLE);

        if (empty($newconfig))
        {
            $newconfig = new stdClass();
            $newconfig->plugin = 'theme_opentechnology';
            $newconfig->name = $destinationsettingname;
            $newconfig->value = $sourcesettingvalue;
            $DB->insert_record('config_plugins', $newconfig);
        } else {
            $newconfig->value = $sourcesettingvalue;
            $DB->update_record('config_plugins', $newconfig);
        }
    }
}

/**
 * Устанавливает для конфига, если он пуст, значение из другого конфига
 * требуется, если в новой версии, логика формирования дефолтного значения изменилась и
 * при обновлении надо сохранить ранее настроенный внешний вид
 *
 * @param string $name - название изменяемого конфига
 * @param string $source - название конфига, из которого следует взять значение в качестве дефолтного,
 *                          null, если требуется указать конкретное значение через параметр value
 * @param string $value - значение, которое необходимо установить (в случае, если не клонируем и $source=null)
 */
function theme_opentechnology_config_set_default($name, $source=null, $value='')
{
    global $DB;

    $profilemanager = profilemanager::instance();
    foreach($profilemanager->get_profiles() as $profile)
    {

        $destinationsettingname = $profile->get_setting_name($name);
        $newconfig = $DB->get_record('config_plugins', [
            'plugin' => 'theme_opentechnology',
            'name' => $destinationsettingname
        ], '*', IGNORE_MULTIPLE);

        if (empty($newconfig) || empty($newconfig->value))
        {// производим изменения только если настройка не была задана, то есть работало какое-то дефолтное поведение

            if (!is_null($source))
            {
                $sourcesettingname = $profile->get_setting_name($source);
                $sourcesettingvalue = $DB->get_field('config_plugins', 'value', [
                    'plugin' => 'theme_opentechnology',
                    'name' => $sourcesettingname
                ]);
            } else
            {
                $sourcesettingvalue = $value;
            }

            if (!empty($sourcesettingvalue))
            {
                if (empty($newconfig))
                {
                    $newconfig = new stdClass();
                    $newconfig->plugin = 'theme_opentechnology';
                    $newconfig->name = $destinationsettingname;
                    $newconfig->value = $sourcesettingvalue;
                    $DB->insert_record('config_plugins', $newconfig);
                } else {
                    $newconfig->value = $sourcesettingvalue;
                    $DB->update_record('config_plugins', $newconfig);
                }
            }
        }
    }
}


function xmldb_theme_opentechnology_upgrade($oldversion)
{
    global $DB;

    // Иинциализация менеджера БД
    $dbman = $DB->get_manager();

    if ( $oldversion < 2017041800 )
    {
        // Добавление таблицы профилей
        $table = new xmldb_table('theme_opentechnology_profile');
        $table->add_field('id',                XMLDB_TYPE_INTEGER, '10',    XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('name',              XMLDB_TYPE_CHAR,    '255',   null,           XMLDB_NOTNULL, null);
        $table->add_field('code',              XMLDB_TYPE_CHAR,    '255',   null,           XMLDB_NOTNULL, null);
        $table->add_field('description',       XMLDB_TYPE_TEXT,    'short', null,           null,          null);
        $table->add_field('descriptionformat', XMLDB_TYPE_INTEGER, '4',     XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('defaultprofile',    XMLDB_TYPE_INTEGER, '1',     XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if ( $dbman->table_exists($table) )
        {
            $dbman->drop_table($table);
        }
        $dbman->create_table($table);

        // Добавление таблицы линковок профилей
        $table = new xmldb_table('theme_opentechnology_plinks');
        $table->add_field('id',        XMLDB_TYPE_INTEGER, '10',    XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('profileid', XMLDB_TYPE_INTEGER, '10',    XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('linktype',  XMLDB_TYPE_CHAR,    '255',   null,           XMLDB_NOTNULL, null);
        $table->add_field('linkdata',  XMLDB_TYPE_TEXT,    'small', null,           null,          null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('iprofileid', XMLDB_KEY_FOREIGN, ['profileid'], 'theme_opentechnology_profile', 'id');
        if ( $dbman->table_exists($table) )
        {
            $dbman->drop_table($table);
        }
        $dbman->create_table($table);

        // Добавление контрольной точки
        upgrade_plugin_savepoint(true, 2017041800, 'theme', 'opentechnology');
    }

    if ( $oldversion < 2017081007 )
    {
        // Изменение настроек темы оформления

        // Массив замен старых настроек на новые наименования
        $configreplaces = [
            '_blindsection_state' => '_collapsiblesection_htop_state',
            '_blindregions' => '_collapsiblesection_htop',
            'color_content_' => 'color_collapsiblesection_'
        ];

        // формирование like-условия, чтобы выбрать только интересующие настройки
        $confignamelike = $DB->sql_like('name', '?', false);
        // параметры запроса
        $configparams = ['theme_opentechnology'];
        foreach($configreplaces as $oldsettingsubstring => $newsettingsubstring)
        {
            $configparams[] = '%'.$oldsettingsubstring.'%';
        }
        $configs = $DB->get_records_select(
            'config_plugins',
            'plugin=? AND ( ('.$confignamelike.') OR ('.$confignamelike.') OR ('.$confignamelike.') )',
            $configparams
        );
        if( ! empty($configs) )
        {
            foreach($configs as $config)
            {
                foreach($configreplaces as $oldsettingsubstring => $newsettingsubstring)
                {
                    if( strpos($config->name, $oldsettingsubstring) !== false )
                    {// Искомая подстрока есть в настройке
                        $newconfig = fullclone($config);
                        $newconfig->name = str_replace(
                            $oldsettingsubstring,
                            $newsettingsubstring,
                            $config->name
                        );
                        if ( $oldsettingsubstring == 'color_content_' )
                        {
                            unset($newconfig->id);
                            if( ! $DB->record_exists('config_plugins', [
                                    'plugin' => $newconfig->plugin,
                                    'name' => $newconfig->name
                                ]) )
                            {
                                // Создаем новую настройку на основе настройки цвета в контенте
                                $DB->insert_record('config_plugins', $newconfig);
                            }
                        } else
                        {
                            // Обновляем настройку на новую с замемненным названием
                            $DB->update_record('config_plugins', $newconfig);
                        }
                    }
                }
            }
        }


        // Изменение пользовательских настроек

        // формирование like-условия, чтобы выбрать только интересующие пользовательские настройки
        $userpreferencenamelike = $DB->sql_like('name', '?', false);
        // параметры запроса
        $userpreferenceparams = ['theme_opentechnology_blindsection_%_state'];
        $userpreferences = $DB->get_records_select(
            'user_preferences',
            $userpreferencenamelike,
            $userpreferenceparams
        );

        if( ! empty($userpreferences) )
        {
            foreach($userpreferences as $userpreference)
            {
                if( strpos($userpreference->name, 'theme_opentechnology_blindsection_') !== false )
                {// Искомая подстрока есть в настройке
                    $newuserpreference = fullclone($userpreference);
                    $newuserpreference->name = str_replace(
                        'theme_opentechnology_blindsection_',
                        'theme_opentechnology_collapsiblesection_htop_',
                        $userpreference->name
                    );
                    // Обновляем настройку на новую с замемненным названием
                    $DB->update_record('user_preferences', $newuserpreference);
                }
            }
        }


        // Изменение позиций блоков

        // формирование like-условия, чтобы выбрать только интересующие позиции блоков
        $blockpositionregionlike = $DB->sql_like('region', '?', false);
        // параметры запроса
        $blockpositionparams = ['blind-%'];
        $blockpositions = $DB->get_records_select(
            'block_positions',
            $blockpositionregionlike,
            $blockpositionparams
        );

        if( ! empty($blockpositions) )
        {
            foreach($blockpositions as $blockposition)
            {
                if( strpos($blockposition->region, 'blind-') !== false )
                {// Искомая подстрока есть в настройке
                    $newblockposition = fullclone($blockposition);
                    $newblockposition->region = str_replace(
                        'blind-',
                        'cs-htop-',
                        $blockposition->region
                    );
                    // Обновляем настройку на новую с замемненным названием
                    $DB->update_record('block_positions', $newblockposition);
                }
            }
        }

        // формирование like-условия, чтобы выбрать только интересующие позиции блоков
        $blockpositionregionlike = $DB->sql_like('defaultregion', '?', false);
        // параметры запроса
        $blockpositionparams = ['blind-%'];
        $blockpositions = $DB->get_records_select(
            'block_instances',
            $blockpositionregionlike,
            $blockpositionparams
        );

        if( ! empty($blockpositions) )
        {
            foreach($blockpositions as $blockposition)
            {
                if( strpos($blockposition->defaultregion, 'blind-') !== false )
                {// Искомая подстрока есть в настройке
                    $newblockposition = fullclone($blockposition);
                    $newblockposition->defaultregion = str_replace(
                        'blind-',
                        'cs-htop-',
                        $blockposition->defaultregion
                    );
                    // Обновляем настройку на новую с замемненным названием
                    $DB->update_record('block_instances', $newblockposition);
                }
            }
        }
    }
    if ($oldversion < 2018120400)
    {
        // Инициализация менеджера профилей
        $manager = theme_opentechnology\profilemanager::instance();
        $defaultprofile = $manager->get_default_profile();
        $responsive = $manager->get_theme_setting('responsive_video', $defaultprofile);
        if (!is_null($responsive))
        {
            $config = $DB->get_record('config_plugins', [
                'plugin' => 'media_videojs',
                'name' => 'limitsize'
            ], 'id', IGNORE_MULTIPLE);

            $newconfig = new stdClass();
            $newconfig->plugin = 'media_videojs';
            $newconfig->name = 'limitsize';
            $newconfig->value = (int)empty($responsive);

            if (!empty($config))
            {
                $newconfig->id = $config->id;
                $DB->update_record('config_plugins', $newconfig);
            } else
            {
                $DB->insert_record('config_plugins', $newconfig);
            }
        }
    }
    if ($oldversion < 2019080600)
    {
        $DB->delete_records('config_plugins', ['plugin' => 'theme_opentechnology', 'name' => 'header_logoimage_width']);
        $profilescode = $DB->get_records('theme_opentechnology_profile',null,'','code');
        foreach ($profilescode as $code){
            $name = $code->code . '_header_logoimage_width';
            $DB->delete_records('config_plugins', ['plugin' => 'theme_opentechnology', 'name' => $name]);
        }
    }
    if ($oldversion < 2020111300)
    {
        $theme = theme_config::load('opentechnology');
        $profilemanager = profilemanager::instance();


        foreach($profilemanager->get_profiles() as $profile)
        {
            $settingconds = [
                'plugin' => 'theme_opentechnology',
                'name' => $profile->get_setting_name('color_header_backgroundcolor')
            ];
            $settingvalue = $DB->get_field('config_plugins', 'value', $settingconds);
            if (empty($settingvalue)) {
                $DB->set_field('config_plugins', 'value', 'transparent', $settingconds);
            }
        }
        // переименуем настройку, чтобы она вписалась в общую концепцию динамически формируемых настроек
        theme_opentechnology_rename_config('color_content_mod_header_backgroundcolor', 'color_content_mod_header');
        theme_opentechnology_rename_config('color_content_mod_header_text_backgroundcolor', 'color_content_mod_header_text');

        // для навигационной панели уже была настройка - только переименуем теперь её
        theme_opentechnology_rename_config('color_breadcrumb_links_color', 'color_navbar_linkscolor_text');
        theme_opentechnology_rename_config('color_breadcrumb_links_color_hover', 'color_navbar_linkscolor_active_text');

        // переключалка юзерменю раньше не настраивалась и была прозрачной, а цвет текста зависел от фона шапки
        theme_opentechnology_config_set_default('color_header_usermenu_ddtoggle', null, 'transparent');
        theme_opentechnology_clone_config_value('color_header_backgroundcolor_text', 'color_header_usermenu_ddtoggle_text');

        // выпадающий список пользовательского меню
        theme_opentechnology_rename_config('color_header_usermenubackgroundcolor', 'color_header_usermenu_ddmenu');
        theme_opentechnology_rename_config('color_header_usermenubackgroundcolor_text', 'color_header_usermenu_ddmenu_text');
        theme_opentechnology_config_set_default('color_header_usermenu_ddmenu_active', null, 'rgba(0,0,0,0.1)');
        theme_opentechnology_config_set_default('color_header_usermenu_ddmenu_active_text', 'color_header_usermenubackgroundcolor_text');

        //
        theme_opentechnology_clone_config_value('color_header_custommenuelementscolor', 'color_header_custommenu_item');
        theme_opentechnology_rename_config('color_header_custommenuelementscolor', 'color_header_custommenu_ddtoggle');
        theme_opentechnology_clone_config_value('color_header_custommenuelementscolor_active', 'color_header_custommenu_item_active');
        theme_opentechnology_rename_config('color_header_custommenuelementscolor_active', 'color_header_custommenu_ddtoggle_active');
        theme_opentechnology_rename_config('color_header_custommenubackgroundcolor', 'color_header_custommenu_ddmenu');
        // у менюшки раньше наследовались цвета от итемов, а теперь от основных настроек выпадающих списков
        // заберем то, что должно было отнаследоваться и укажем в качестве настроек
        theme_opentechnology_config_set_default('color_header_custommenu_ddmenu', 'color_header_custommenu_item');
        theme_opentechnology_rename_config('color_header_custommenubackgroundcolor_active', 'color_header_custommenu_ddmenu_active');
        // у менюшки раньше наследовались цвета от итемов, а теперь от основных настроек выпадающих списков
        // заберем то, что должно было отнаследоваться и укажем в качестве настроек
        theme_opentechnology_config_set_default('color_header_custommenu_ddmenu_active', 'color_header_custommenu_item_active');

        theme_opentechnology_rename_config('color_dockeditems_backgroundcolor', 'color_dock_dockeditem_textview');
        // ранее по умолчанию для фона итемов в доке использовался прозрачный цвет, теперь цвет подложки
        theme_opentechnology_config_set_default('color_dock_dockeditem_textview', null, 'transparent');
        theme_opentechnology_rename_config('color_dockeditems_backgroundcolor_text', 'color_dock_dockeditem_textview_text');
        theme_opentechnology_rename_config('color_dockeditems_backgroundcolor_active', 'color_dock_dockeditem_textview_active');
        theme_opentechnology_rename_config('color_dockeditems_backgroundcolor_active_text', 'color_dock_dockeditem_textview_active_text');
        theme_opentechnology_rename_config('color_dockeditems_iconview_backgroundcolor', 'color_dock_dockeditem_iconview');
        // ранее по умолчанию для фона итемов в доке использовался прозрачный цвет, теперь цвет подложки
        theme_opentechnology_config_set_default('color_dock_dockeditem_iconview', null, 'transparent');
        theme_opentechnology_rename_config('color_dockeditems_iconview_backgroundcolor_text', 'color_dock_dockeditem_iconview_text');
        theme_opentechnology_rename_config('color_dockeditems_iconview_backgroundcolor_active', 'color_dock_dockeditem_iconview_active');
        theme_opentechnology_rename_config('color_dockeditems_iconview_backgroundcolor_active_text', 'color_dock_dockeditem_iconview_active_text');

        // получим зоны, для которых позднее перенесем значения из общих настроек по ссылкам - в каждую зону
        $colorzones = profilesettings::colorzones;
        // зону навигационной панели исключаем, так как у неё была своя настройка
        $colorzonenavbarkey = array_search('navbar', $colorzones);
        if ($colorzonenavbarkey !== false)
        {
            unset($colorzones[$colorzonenavbarkey]);
        }

        foreach(profilesettings::colorzones as $colorzone)
        {
            theme_opentechnology_clone_config_value('color_links_color', 'color_'.$colorzone.'_linkscolor_text');
            theme_opentechnology_clone_config_value('color_links_color_hover', 'color_'.$colorzone.'_linkscolor_active_text');
        }

        theme_opentechnology_delete_config('color_links_color');
        theme_opentechnology_delete_config('color_links_color_hover');

        // собираем настройки, отвечающие за действия над блоками в зонах для каждого из лейаутов
        $regionsettingnames = [];
        foreach($profilemanager->get_profiles() as $profile)
        {
            foreach($theme->layouts as $layout => $layoutdata)
            {
                foreach ($layoutdata['regions'] as $region)
                {
                    $settingname = str_replace('-', '_', 'region_'.$layout.'_'.$region);
                    $regionsettingnames[] = $profile->get_setting_name($settingname);
                }
            }
        }
        list($sqlin, $params) = $DB->get_in_or_equal($regionsettingnames, SQL_PARAMS_NAMED);
        $params['plugin'] = 'theme_opentechnology';

        // обновляем настройки, которые отображали блоки в доке на новую настройку
        $sql = 'plugin=:plugin AND name '.$sqlin.' AND (value=:autodocking OR value=:fixeddock)';
        $DB->set_field_select('config_plugins', 'value', 'dock', $sql, $params + ['autodocking'=>'autodocking', 'fixeddock'=>'fixeddock']);

        // обновляем настройки, которые отображали блоки в стандартной позиции
        $sql = 'plugin=:plugin AND name '.$sqlin.' AND (value=:enabled OR value=:disableddock)';
        $DB->set_field_select('config_plugins', 'value', 'standard', $sql, $params + ['enabled'=>'enabled', 'disableddock'=>'disableddock']);


        foreach($profilemanager->get_profiles() as $profile)
        {
            $settingconds = [
                'plugin' => 'theme_opentechnology',
                'name' => $profile->get_setting_name('main_customcss')
            ];
            $settingvalue = $DB->get_field('config_plugins', 'value', $settingconds);
            $replacemap = [
                '[[setting:selector_header_buttons]]' => '#page-header .btn',
                '[[setting:selector_header_hovered_buttons]]' => '#page-header .btn:hover',
                '[[setting:selector_header_focused_buttons]]' => '#page-header .btn:focus',
                '[[setting:selector_header_active_buttons]]' => '#page-header .btn:active',
                '[[setting:selector_content_buttons]]' => '#page-wrapper .btn',
                '[[setting:selector_content_hovered_buttons]]' => '#page-wrapper .btn:hover',
                '[[setting:selector_content_focused_buttons]]' => '#page-wrapper .btn:focus',
                '[[setting:selector_content_active_buttons]]' => '#page-wrapper .btn:active',
                '[[setting:selector_footer_buttons]]' => '#footer_wrapper .btn',
                '[[setting:selector_footer_hovered_buttons]]' => '#footer_wrapper .btn:hover',
                '[[setting:selector_footer_focused_buttons]]' => '#footer_wrapper .btn:focus',
                '[[setting:selector_footer_active_buttons]]' => '#footer_wrapper .btn:active',
                '[[setting:selector_collapsiblesection_buttons]]' => '.collapsible-section .btn',
                '[[setting:selector_collapsiblesection_hovered_buttons]]' => '.collapsible-section .btn:hover',
                '[[setting:selector_collapsiblesection_focused_buttons]]' => '.collapsible-section .btn:focus',
                '[[setting:selector_collapsiblesection_active_buttons]]' => '.collapsible-section .btn:active',
                '[[setting:selector_course_section_header]]' => 'body.path-course-view #page-wrapper .course-content > ul > li.section > .content .sectionname',
                '[[setting:selector_block_header]]' => '.block>.card-body>.card-title',
                '[[setting:selector_mod_subheader]]' => 'body.path-mod #page-wrapper div[role="main"] h3',
            ];
            $settingvalue = str_replace(array_keys($replacemap), array_values($replacemap), $settingvalue);
            $DB->set_field('config_plugins', 'value', $settingvalue, $settingconds);
        }


    }
    return true;
}