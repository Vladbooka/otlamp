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
 * Скрипт апгрейда
*
* @package    local_pprocessing
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

defined('MOODLE_INTERNAL') || die();


/**
 * Обновление плагина
 */
function xmldb_local_pprocessing_upgrade($oldversion = 0)
{
    global $DB;
    $dbman = $DB->get_manager();

    if( $oldversion < 2018060610 )
    {
        // таблица логов
        $table = new xmldb_table('local_pprocessing_logs');

        // поля
        $fields = [];
        $fields[] = new xmldb_field('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $fields[] = new xmldb_field('type', XMLDB_TYPE_CHAR, 64, null, XMLDB_NOTNULL);
        $fields[] = new xmldb_field('objid', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, null, null, 0 );
        $fields[] = new xmldb_field('data', XMLDB_TYPE_TEXT, 'big');
        $fields[] = new xmldb_field('comment', XMLDB_TYPE_TEXT, 'big');
        $fields[] = new xmldb_field('timestart', XMLDB_TYPE_INTEGER, 10);
        $fields[] = new xmldb_field('timeend', XMLDB_TYPE_INTEGER, 10);
        $fields[] = new xmldb_field('status', XMLDB_TYPE_CHAR, 24);

        // ключи
        $keys = [];
        $keys[] = new xmldb_key('id', XMLDB_KEY_PRIMARY, ['id']);

        // индексы
        $indexes = [];

        $table->setFields($fields);
        $table->setKeys($keys);
        $table->setIndexes($indexes);

        // создаем таблицу
        if ( ! $dbman->table_exists($table) )
        {
            $dbman->create_table($table);
        }
    }

    if( $oldversion < 2018061801 )
    {
        // таблица логов
        $table = new xmldb_table('local_pprocessing_logs');
        $field = new xmldb_field('objid', XMLDB_TYPE_CHAR, 255, null, null);
        if( $dbman->table_exists($table) && $dbman->field_exists($table, $field) )
        {
            $dbman->change_field_type($table, $field);
            $dbman->change_field_precision($table, $field);//10 - 255
            $dbman->change_field_notnull($table, $field);
            $dbman->change_field_default($table, $field);
            $dbman->rename_field($table, $field, 'code');
        }


        // таблица обработанных данных
        $table = new xmldb_table('local_pprocessing_processed');

        // поля
        $fields = [];
        $fields[] = new xmldb_field('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $fields[] = new xmldb_field('scenariocode', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $fields[] = new xmldb_field('handlercode', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $fields[] = new xmldb_field('data', XMLDB_TYPE_TEXT, 'big');
        $fields[] = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, 10);

        // ключи
        $keys = [];
        $keys[] = new xmldb_key('id', XMLDB_KEY_PRIMARY, ['id']);

        // индексы
        $indexes = [];

        $table->setFields($fields);
        $table->setKeys($keys);
        $table->setIndexes($indexes);

        // создаем таблицу
        if ( ! $dbman->table_exists($table) )
        {
            $dbman->create_table($table);
        }
    }

    if( $oldversion < 2019102900 )
    {
        // апгрейд изменений макроподстановки в настройках системы
        $toreplace = [
            'send_user_password_message_subject',
            'send_user_password_message_full',
            'send_user_password_message_short'
        ];
        foreach($toreplace as $configname)
        {
            $oldconfig = get_config('local_pprocessing', $configname);
            set_config(
                $configname,
                str_replace('%{user.newpassword}', '%{generated_code}', $oldconfig),
                'local_pprocessing'
            );
        }


        // апгрейд обработанных ранее записей, для корректной работы после изменения кодов сценариев

        $DB->set_field(
            'local_pprocessing_processed',
            'scenariocode',
            'send_user_registered_long_ago_message',
            [
                'scenariocode' => 'user_registered_long_ago'
            ]
        );

        $DB->set_field(
            'local_pprocessing_processed',
            'scenariocode',
            'send_user_registered_recently_message',
            [
                'scenariocode' => 'user_registered_recently'
            ]
        );

        $DB->set_field(
            'local_pprocessing_processed',
            'scenariocode',
            'user_deletion',
            [
                'scenariocode' => 'user_registered_long_ago_deleting'
            ]
        );


    }

    if( $oldversion < 2020062900 )
    {
        // апгрейд изменения в названии сценариев в настройках системы
        $torename = [
            'send_user_password__status' => 'send_new_user_passwords__status'
        ];
        foreach($torename as $oldname => $newname) {
            $oldconfig = get_config('local_pprocessing', $oldname);
            if ($oldconfig !== false) {
                set_config($newname, $oldconfig, 'local_pprocessing');
                unset_config($oldname, 'local_pprocessing');
            }
        }
    }

    if ( $oldversion < 2021020100 )
    {
        // local_mcov обновился и добавил публичным полям префикс pub_
        // а мы одновременно с этим обновим ранее сохраненные конфиги

        $unenroldatefield = get_config('local_pprocessing', 'unenrol_cohorts_by_date__unenroldate');
        if ($unenroldatefield !== false) {
            set_config('unenrol_cohorts_by_date__unenroldate', 'pub_'.$unenroldatefield, 'local_pprocessing');
        }

        $deldatefield = get_config('local_pprocessing', 'delete_cohorts_by_date__deldate');
        if ($deldatefield !== false) {
            set_config('delete_cohorts_by_date__deldate', 'pub_'.$deldatefield, 'local_pprocessing');
        }
    }
    
    if ($oldversion < 2021062400) {
        $config = (array)get_config('local_pprocessing');
        foreach ($config as $name => $value) {
            preg_match_all('/export_quiz_grades__(.*)/', $name, $matches);
            if (!empty($matches[0])) {
                set_config('export_grades__' . array_shift($matches[1]), $value, 'local_pprocessing');
                unset_config($name, 'local_pprocessing');
            }
        }
        if ($config = get_config('local_pprocessing', 'export_quiz_grades_schedule__status')) {
            set_config('export_grades_schedule__status', $config, 'local_pprocessing');
            unset_config('export_quiz_grades_schedule__status', 'local_pprocessing');
        }
        
    }

    return true;
}