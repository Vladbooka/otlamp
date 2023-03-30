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
 * Витрина курсов. Обновление.
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot .'/local/crw/lib.php');
/**
 * Обновление плагина
 *
 * @param int $oldversion - Старая версия плагина
 * @return bool - Результат
 */
function xmldb_local_crw_upgrade($oldversion)
{
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion <= 2015072800)
    {
        $table = new xmldb_table('course_properties');
        if ( $dbman->table_exists($table) )
        {// Переименовать таблицу
            $dbman->rename_table($table, 'crw_course_properties');
        }

        $table = new xmldb_table('crw_category_properties');
        if ( ! $dbman->table_exists($table) )
        {// Создать таблицу
            // Добавление полей к таблице
            $table->add_field('id',         XMLDB_TYPE_INTEGER, '10',     true,  XMLDB_NOTNULL, XMLDB_SEQUENCE );
            $table->add_field('categoryid', XMLDB_TYPE_INTEGER, '10',     false, XMLDB_NOTNULL, null           );
            $table->add_field('name',       XMLDB_TYPE_CHAR,    '30',     false, XMLDB_NOTNULL, null           );
            $table->add_field('svalue',     XMLDB_TYPE_CHAR,    '255',    false, XMLDB_NOTNULL, null           );
            $table->add_field('value',      XMLDB_TYPE_TEXT,    'medium', false, XMLDB_NOTNULL, null           );

            // Добавление ключей
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

            // Добавление индексов
            $table->add_index('categoryid', XMLDB_INDEX_NOTUNIQUE, array('categoryid'));
            $table->add_index('name', XMLDB_INDEX_NOTUNIQUE, array('name'));
            $table->add_index('svalue', XMLDB_INDEX_NOTUNIQUE, array('svalue'));

            // Создание таблицы
            $dbman->create_table($table);
        }
    }
    if ($oldversion < 2016033100)
    {
        //обновление данных в БД в связи с изменением настроек
        local_crw_fix_config_changes(2016033100);
    }
    if( $oldversion < 2016121902 )
    {
        //обновление данных в БД в связи с изменением настроек
        //много версий назад была обновлена настройка для наклеек на плитки, но апгрейд так и не был написан
        local_crw_fix_config_changes(2016121902);
    }
    if( $oldversion < 2017081600 )
    {
        $table = new xmldb_table('crw_course_categories');
        if ( ! $dbman->table_exists($table) )
        {
            // Добавление полей к таблице
            $table->add_field('id',         XMLDB_TYPE_INTEGER, '10',     true,  XMLDB_NOTNULL, XMLDB_SEQUENCE );
            $table->add_field('courseid',   XMLDB_TYPE_INTEGER, '10',     false, XMLDB_NOTNULL, null           );
            $table->add_field('categoryid', XMLDB_TYPE_INTEGER, '10',     false, XMLDB_NOTNULL, null           );

            // Добавление ключей
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

            // Добавление индексов
            $table->add_index('coursecat', XMLDB_INDEX_UNIQUE, ['courseid','categoryid']);

            // Создание таблицы
            $dbman->create_table($table);
        }
    }

    if($oldversion < 2018111500)
    {
        global $CFG;
        // удаление плагина crw_system_skills
        $pluginman = core_plugin_manager::instance();

        if ($pluginman->can_uninstall_plugin('crw_system_skills')) {
            $pluginfo = $pluginman->get_plugin_info('crw_system_skills');

            uninstall_plugin($pluginfo->type, $pluginfo->name);

            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
        }
        if ($pluginman->is_plugin_folder_removable('crw_system_skills')) {
            //заново получаем информацию, чтобы убедиться, что из базы плагин удален, осталось только удалить папку с плагином
            $pluginfo = $pluginman->get_plugin_info('crw_system_skills');

            if (!is_null($pluginfo) && is_null($pluginfo->versiondb) && strpos($pluginfo->rootdir, $CFG->dirroot) == 0) {

                $pluginman->remove_plugin_folder($pluginfo);

                // после успешного удаления папки плагина и папки плагина (crw_system_skills), необходимо удалить информацию об этом плагине в текущем запросе
                // инициализация списка плагинов использует синглтон паттерн, в который невозможно вклиниться (инициализация происходит 1 раз за запрос)
                // из-за этого используется рефлекция для очистки массива списка плагина
                $component = new ReflectionClass('core_component');
                $purgecachesmethod = $component->getMethod('fill_all_caches');
                if (!empty($purgecachesmethod)) {
                    $purgecachesmethod->setAccessible(true);
                    $purgecachesmethod->invoke(null);
                    $purgecachesmethod->setAccessible(false);
                }
            }
        }
        foreach(['slots_cs_header', 'slots_cs_top', 'slots_cs_bottom'] as $settingname)
        {
            $setting = get_config('local_crw', $settingname);
            if ($setting == 'system_skills') {
                unset_config($settingname, 'local_crw');
            }
        }
    }

    if ($oldversion < 2018122400) {

        $table = new xmldb_table('crw_feedback');
        if ( ! $dbman->table_exists($table) )
        {
            // Добавление полей к таблице
            $table->add_field('id',             XMLDB_TYPE_INTEGER, '10',       null, XMLDB_NOTNULL,    XMLDB_SEQUENCE  );
            $table->add_field('contextid',      XMLDB_TYPE_INTEGER, '10',       null, XMLDB_NOTNULL,    null            );
            $table->add_field('component',      XMLDB_TYPE_CHAR,    '255',      null, null,             null            );
            $table->add_field('commentarea',    XMLDB_TYPE_CHAR,    '255',      null, XMLDB_NOTNULL,    null            );
            $table->add_field('itemid',         XMLDB_TYPE_INTEGER, '10',       null, XMLDB_NOTNULL,    null            );
            $table->add_field('content',        XMLDB_TYPE_TEXT,    'medium',   null, XMLDB_NOTNULL,    null            );
            $table->add_field('format',         XMLDB_TYPE_INTEGER, '2',        null, XMLDB_NOTNULL,    null,           0);
            $table->add_field('userid',         XMLDB_TYPE_INTEGER, '10',       null, XMLDB_NOTNULL,    null            );
            $table->add_field('status',         XMLDB_TYPE_CHAR,    '8',        null, XMLDB_NOTNULL,    null            );
            $table->add_field('acceptor',       XMLDB_TYPE_INTEGER, '10',       null, null,             null            );
            $table->add_field('timeaccepted',   XMLDB_TYPE_INTEGER, '10',       null, null,             null            );
            $table->add_field('timecreated',    XMLDB_TYPE_INTEGER, '10',       null, XMLDB_NOTNULL,    null            );

            // Добавление ключей
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_key('fk_user', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

            // Добавление индексов
            $table->add_index('ix_concomitem', XMLDB_INDEX_NOTUNIQUE, array('contextid', 'commentarea', 'itemid'));

            // Создание таблицы
            $dbman->create_table($table);
        }
    }

    // Повторное удаление плагина для инсталляций, где возникли ошибки во время обновления
    if ($oldversion < 2019091600) {
        global $CFG;
        // удаление плагина crw_system_skills
        $pluginman = core_plugin_manager::instance();

        if ($pluginman->can_uninstall_plugin('crw_system_skills')) {
            $pluginfo = $pluginman->get_plugin_info('crw_system_skills');

            uninstall_plugin($pluginfo->type, $pluginfo->name);

            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
        }
        if ($pluginman->is_plugin_folder_removable('crw_system_skills')) {
            //заново получаем информацию, чтобы убедиться, что из базы плагин удален, осталось только удалить папку с плагином
            $pluginfo = $pluginman->get_plugin_info('crw_system_skills');

            if (!is_null($pluginfo) && is_null($pluginfo->versiondb) && strpos($pluginfo->rootdir, $CFG->dirroot) == 0) {

                $pluginman->remove_plugin_folder($pluginfo);

                // после успешного удаления папки плагина и папки плагина (crw_system_skills), необходимо удалить информацию об этом плагине в текущем запросе
                // инициализация списка плагинов использует синглтон паттерн, в который невозможно вклиниться (инициализация происходит 1 раз за запрос)
                // из-за этого используется рефлекция для очистки массива списка плагина
                $component = new ReflectionClass('core_component');
                $purgecachesmethod = $component->getMethod('fill_all_caches');
                if (!empty($purgecachesmethod)) {
                    $purgecachesmethod->setAccessible(true);
                    $purgecachesmethod->invoke(null);
                    $purgecachesmethod->setAccessible(false);
                }
            }
        }
        foreach(['slots_cs_header', 'slots_cs_top', 'slots_cs_bottom'] as $settingname)
        {
            $setting = get_config('local_crw', $settingname);
            if ($setting == 'system_skills') {
                unset_config($settingname, 'local_crw');
            }
        }
    }

    if ($oldversion < 2019091800) {
        $table = new xmldb_table('crw_course_properties');
        $field = new xmldb_field('sortvalue', XMLDB_TYPE_INTEGER, '10', null, null, null);
        if (!$dbman->field_exists($table, $field) )
        {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2020012800) {

        $table = new xmldb_table('crw_course_properties');
        if ($dbman->field_exists($table, 'name'))
        {
            $index = new xmldb_index('name', XMLDB_INDEX_NOTUNIQUE, ['name']);
            if ($dbman->index_exists($table, $index))
            {
                $dbman->drop_index($table, $index);
            }
            $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '255', false, XMLDB_NOTNULL, false);
            $dbman->change_field_precision($table, $field);
            $dbman->add_index($table, $index);
        }

        $table = new xmldb_table('crw_category_properties');
        if ($dbman->field_exists($table, 'name'))
        {
            $index = new xmldb_index('name', XMLDB_INDEX_NOTUNIQUE, ['name']);
            if ($dbman->index_exists($table, $index))
            {
                $dbman->drop_index($table, $index);
            }
            $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '255', false, XMLDB_NOTNULL, false);
            $dbman->change_field_precision($table, $field);
            $dbman->add_index($table, $index);
        }
    }

    if ($oldversion < 2020121400) {

        $config = $DB->get_record('config_plugins', [
            'plugin' => 'local_crw',
            'name' => 'settings_override_breadcrumb_navigation'
        ], 'id', IGNORE_MULTIPLE);
        if (!empty($config))
        {
            $config->name = 'settings_override_navigation';
            $DB->update_record('config_plugins', $config);
        }

        $config = $DB->get_record('config_plugins', [
            'plugin' => 'local_crw',
            'name' => 'settings_override_breadcrumb_navigation_desc'
        ], 'id', IGNORE_MULTIPLE);
        if (!empty($config))
        {
            $config->name = 'settings_override_navigation_desc';
            $DB->update_record('config_plugins', $config);
        }
    }

    return true;
}
