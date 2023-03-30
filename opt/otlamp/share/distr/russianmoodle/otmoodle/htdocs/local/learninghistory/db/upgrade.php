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
 * Апдейт структуры БД
 *
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_local_learninghistory_upgrade($oldversion) 
{
    global $CFG, $DB, $USER;

    $dbman = $DB->get_manager();

    if ( $oldversion < 2017112000 ) 
    {
        // Добавление поля максимальной оценки за курс
        $table = new xmldb_table('local_learninghistory');
        
        $field = new xmldb_field('coursefinalgrade', XMLDB_TYPE_NUMBER, '10,5');
        if ( ! $dbman->field_exists($table, $field) )
        {
            $dbman->add_field($table, $field);
        }
        
        $field = new xmldb_field('coursecompletion', XMLDB_TYPE_INTEGER, '1');
        if ( ! $dbman->field_exists($table, $field) )
        {
            $dbman->add_field($table, $field);
        }
    }
    
    if ( $oldversion < 2017112200 )
    {
        // Добавление поля максимальной оценки за курс
        $table = new xmldb_table('local_learninghistory');
    
        $field = new xmldb_field('activetime', XMLDB_TYPE_INTEGER, '10');
        if ( ! $dbman->field_exists($table, $field) )
        {
            $dbman->add_field($table, $field);
        }
    
        $field = new xmldb_field('atlastupdate', XMLDB_TYPE_INTEGER, '10');
        if ( ! $dbman->field_exists($table, $field) )
        {
            $dbman->add_field($table, $field);
        }
        
        $table = new xmldb_table('llhistory_properties');
        if ( ! $dbman->table_exists($table) )
        {// Создать таблицу
            // Добавление полей к таблице
            $table->add_field('id',         XMLDB_TYPE_INTEGER, '10',     true,  XMLDB_NOTNULL, XMLDB_SEQUENCE );
            $table->add_field('courseid',   XMLDB_TYPE_INTEGER, '10',     false, XMLDB_NOTNULL, null           );
            $table->add_field('name',       XMLDB_TYPE_CHAR,    '30',     false, XMLDB_NOTNULL, null           );
            $table->add_field('value',      XMLDB_TYPE_TEXT,    'medium', false, XMLDB_NOTNULL, null           );
            
            // Добавление ключей
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            
            // Добавление индексов
            $table->add_index('courseid', XMLDB_INDEX_NOTUNIQUE, ['courseid']);
            $table->add_index('name', XMLDB_INDEX_NOTUNIQUE, ['name']);
            
            // Создание таблицы
            $dbman->create_table($table);
        }
    }
    
    if( $oldversion < 2018032000 )
    {
        $table = new xmldb_table('local_learninghistory_cm');
        if ( ! $dbman->table_exists($table) )
        {// Создать таблицу
            // Добавление полей к таблице
            $table->add_field('id',            XMLDB_TYPE_INTEGER, '10',   true,  XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('llid',          XMLDB_TYPE_INTEGER, '10',   false, XMLDB_NOTNULL, null,           0,    'id');
            $table->add_field('cmid',          XMLDB_TYPE_INTEGER, '10',   false, XMLDB_NOTNULL, null,           0,    'llid');
            $table->add_field('contextid',     XMLDB_TYPE_INTEGER, '10',   false, XMLDB_NOTNULL, null,           0,    'cmid');
            $table->add_field('userid',        XMLDB_TYPE_INTEGER, '10',   false, XMLDB_NOTNULL, null,           0,    'contextid');
            $table->add_field('attemptnumber', XMLDB_TYPE_INTEGER, '10',   false, XMLDB_NOTNULL, null,           0,    'userid');
            $table->add_field('activetime',    XMLDB_TYPE_INTEGER, '10',   false, null,          null,           null, 'attemptnumber');
            $table->add_field('atlastupdate',  XMLDB_TYPE_INTEGER, '10',   false, null,          null,           null, 'activetime');
            $table->add_field('status',        XMLDB_TYPE_CHAR,    '32',   false, null,          null,           null, 'atlastupdate');
            $table->add_field('completion',    XMLDB_TYPE_INTEGER, '1',    false, null,          null,           null, 'status');
            $table->add_field('finalgrade',    XMLDB_TYPE_FLOAT,   '10,5', false, null,          null,           null, 'completion');
            $table->add_field('timecreated',   XMLDB_TYPE_INTEGER, '10',   false, null,          null,           null, 'finalgrade');
            $table->add_field('timemodified',  XMLDB_TYPE_INTEGER, '10',   false, null,          null,           null, 'timecreated');
            
            // Добавление ключей
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            
            // Добавление индексов
            $table->add_index('alluserhistory', XMLDB_INDEX_NOTUNIQUE, ['userid','status','attemptnumber','atlastupdate']);
            $table->add_index('userhistorybycm', XMLDB_INDEX_NOTUNIQUE, ['cmid','userid','status','attemptnumber','atlastupdate']);
            $table->add_index('userhistorybycontext', XMLDB_INDEX_NOTUNIQUE, ['contextid','userid','status','attemptnumber','atlastupdate']);
            
            // Создание таблицы
            $dbman->create_table($table);
        }
        
        $table = new xmldb_table('local_learninghistory_module');
        if ( ! $dbman->table_exists($table) )
        {// Создать таблицу
            // Добавление полей к таблице
            $table->add_field('id',            XMLDB_TYPE_INTEGER, '10',   true,  XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('cmid',          XMLDB_TYPE_INTEGER, '10',   false, XMLDB_NOTNULL, null,           0,    'id');
            $table->add_field('courseid',      XMLDB_TYPE_INTEGER, '10',   false, XMLDB_NOTNULL, null,           0,    'cmid');
            $table->add_field('section',       XMLDB_TYPE_INTEGER, '10',   false, XMLDB_NOTNULL, null,           0,    'courseid');
            $table->add_field('status',        XMLDB_TYPE_CHAR,    '32',   false, null,          null,           null, 'section');
            $table->add_field('name',          XMLDB_TYPE_CHAR,    '255',  false, null,          null,           null, 'status');
            $table->add_field('modname',       XMLDB_TYPE_CHAR,    '255',  false, null,          null,           null, 'name');
            $table->add_field('timecreated',   XMLDB_TYPE_INTEGER, '10',   false, null,          null,           null, 'modname');
            $table->add_field('timemodified',  XMLDB_TYPE_INTEGER, '10',   false, null,          null,           null, 'timecreated');
            
            // Добавление ключей
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            
            // Добавление индексов
            $table->add_index('cmbystatus', XMLDB_INDEX_NOTUNIQUE, ['courseid','cmid','status']);
            
            // Создание таблицы
            $dbman->create_table($table);
        }
    }
    
    if ($oldversion < 2021031600) {
        // Добавление новых полей для возможности хранения оценок за модули курсов и отслеживания условий на момент выставления оценки
        $table = new xmldb_table('local_learninghistory_cm');
        if ($dbman->table_exists($table)) {
            $field = new xmldb_field('rawgrade', XMLDB_TYPE_FLOAT, '10,5', null, null, null, null, 'completion');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
            $result = $dbman->field_exists($table, $field);
            $field = new xmldb_field('rawgrademax', XMLDB_TYPE_FLOAT, '10,5', null, XMLDB_NOTNULL, null, 100.00000, 'rawgrade');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
            $result = $result && $dbman->field_exists($table, $field);
            $field = new xmldb_field('rawgrademin', XMLDB_TYPE_FLOAT, '10,5', null, XMLDB_NOTNULL, null, 0.00000, 'rawgrademax');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
            $result = $result && $dbman->field_exists($table, $field);
            $field = new xmldb_field('rawscaleid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'rawgrademin');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
            $result = $result && $dbman->field_exists($table, $field);
            $field = new xmldb_field('scalesnapshot', XMLDB_TYPE_TEXT, null, null, null, null, null, 'rawscaleid');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
            $result = $result && $dbman->field_exists($table, $field);
            if ($result) {
                // Если все создалось, добавим задачу на заполнение данных
                $task = new \local_learninghistory\task\fill_data();
                $customdata = new stdClass();
                $customdata->method = 'fill_rawgrade_data';
                $task->set_custom_data($customdata);
                $task->set_userid($USER->id);
                $task->set_component('local_learninghistory');
                \core\task\manager::queue_adhoc_task($task);
            }
        }
        $table = new xmldb_table('local_learninghistory');
        if ($dbman->table_exists($table)) {
            // Добавление новых полей для полного и краткого наименования курса
            $field = new xmldb_field('coursefullname', XMLDB_TYPE_CHAR, '254', null, null, null, null, 'courseid');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
            $result = $dbman->field_exists($table, $field);
            $field = new xmldb_field('courseshortname', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'coursefullname');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
            $result = $result && $dbman->field_exists($table, $field);
            if ($result) {
                // Если все создалось, добавим задачу на заполнение данных
                $task = new \local_learninghistory\task\fill_data();
                $customdata = new stdClass();
                $customdata->method = 'fill_course_data';
                $task->set_custom_data($customdata);
                $task->set_userid($USER->id);
                $task->set_component('local_learninghistory');
                \core\task\manager::queue_adhoc_task($task);
            }
        }
    }

    return true;
}
