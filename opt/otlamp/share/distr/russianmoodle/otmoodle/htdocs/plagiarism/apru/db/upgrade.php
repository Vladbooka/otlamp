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
 * Плагин определения заимствований Антиплагиат. Скрипт обновления плагина.
 *
 * @package    plagiarism
 * @subpackage apru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_plagiarism_apru_upgrade($oldversion) 
{
    global $DB;
    // Получение менеджера работы с таблицами
    $dbman = $DB->get_manager();

    if ( $oldversion < 2016031700 ) 
    {
        $table = new xmldb_table('plagiarism_apru_files');
        $field1 = new xmldb_field('additional', XMLDB_TYPE_TEXT, 'medium', NULL, NULL, NULL, NULL, 'reporturl');

        if ( ! $dbman->field_exists($table, $field1) ) 
        {
            $dbman->add_field($table, $field1);
        }
        upgrade_plugin_savepoint(true, 2016031700, 'plagiarism', 'apru');
    }
    
    if( $oldversion < 2017051700 )
    {
        // Чиним таблицу plagiarism_apru_files от дублей записей, добавленных с другими userid
        global $DB;
        $users = $records = $pathnamehashes = $files = [];
        // Первая часть марлезонского балета - получим корректные связки pathnamehash-userid из очереди apru_files
        // Получим всех пользователей из таблицы plagiarism_apru_files с указанием contextid заданий
        $sql = "SELECT pf.id,pf.userid,c.id contextid
                  FROM {plagiarism_apru_files} pf 
                  JOIN {context} c 
                    ON c.instanceid=pf.cm 
                 WHERE c.contextlevel='" . CONTEXT_MODULE . "' 
              GROUP BY pf.userid,c.id,pf.id";
        $records = $DB->get_records_sql($sql);
        if( ! empty($records) )
        {
            foreach($records as $record)
            {// Соберем связки userid=>[contextid1,...,contextidN]
                if( (isset($users[$record->userid]) && ! in_array($record->contextid, $users[$record->userid])) ||
                    ! isset($users[$record->userid]) )
                {
                    $users[$record->userid][] = $record->contextid;
                }
            }
            if( ! empty($users) )
            {
                foreach($users as $userid => $contexts)
                {
                    foreach($contexts as $contextid)
                    {
                        // Получим объект файлового хранилища
                        $fs = get_file_storage();
                        // Получим файлы из очереди apru_files
                        $files = $fs->get_area_files($contextid, 'apru_files', 'queue_files');
                        if( ! empty($files) )
                        {
                            foreach($files as $file)
                            {
                                if( ! $file->is_directory() && $file->get_userid() == $userid )
                                {// Найдем файлы пользователя
                                    // Соберем массив хешей путей файлов в связку userid=>[pathnamehash1,...,pathnamehashN]
                                    $pathnamehashes[$userid][] = $file->get_pathnamehash();
                                }
                            }
                        }
                    }
                }
            }
        }
        // Вторая часть марлезонского балета - удалим все некорректные связки identifier-userid из таблицы plagiarism_apru_files
        if( ! empty($pathnamehashes) )
        {
            foreach($pathnamehashes as $uid => $hashes)
            {
                if( ! empty($hashes) )
                {// Удалять будем те записи, для которых userid не совпадает с текущим, но имеет такие же pathnamehash (identifier)
                    $select = "userid <> '" . $uid . "' AND identifier IN ('" . implode("','", $hashes) . "')";
                    $DB->delete_records_select('plagiarism_apru_files', $select);
                }
            }
        }
    }
    
    if ($oldversion < 2020122200) {
        // Удаляем старые не нужные настройки
        unset_config('apru_use', 'plagiarism');
        $supported_mods = ['assign'];
        foreach ($supported_mods as $mod) {
            unset_config('apru_use_mod_' . $mod, 'plagiarism');
        }
        // Замена deprecated настройки apru_use на enabled
        $old = (int) get_config('plagiarism_apru', 'apru_use');
        set_config('enabled', $old, 'plagiarism_apru');
        unset_config('apru_use', 'plagiarism_apru');
    }

    return true;
}