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
 * Плагин "Надо проверить". Обновление.
 *
 * @package    block
 * @subpackage notgraded
 * @category   upgrade
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_block_notgraded_upgrade( $oldversion = 0 ) 
{
    global $CFG, $DB;
    $result = true;
    $dbman = $DB->get_manager();
    if( $oldversion < 2017030300 )
    {
        $table = new xmldb_table('block_notgraded_cache');
        if ( ! $dbman->table_exists($table) )
        {// Создать таблицу

            // Добавление полей к таблице
            $table->add_field('id',             XMLDB_TYPE_INTEGER, '10', true, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->add_field('graderid',       XMLDB_TYPE_INTEGER, '10', true, XMLDB_NOTNULL, null,            0   );
            $table->add_field('courseid',       XMLDB_TYPE_INTEGER, '10', true, XMLDB_NOTNULL, null,            0   );
            $table->add_field('countnotgraded', XMLDB_TYPE_INTEGER, '10', true, XMLDB_NOTNULL, null,            0   );
            $table->add_field('lastupdate',     XMLDB_TYPE_INTEGER, '10', true, XMLDB_NOTNULL, null,            0   );
            
            // Добавление ключей
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            
            // Добавление индексов
            $table->add_index('graderscourse', XMLDB_INDEX_UNIQUE, [
                'graderid',
                'courseid'
            ]);
            $table->add_index('countnotgraded', XMLDB_INDEX_NOTUNIQUE, ['countnotgraded']);
            $table->add_index('lastupdate', XMLDB_INDEX_NOTUNIQUE, ['lastupdate']);
            
            // Создание таблицы
            $dbman->create_table($table);
        }
    }
    if( $oldversion < 2017031301 )
    {        
        $table = new xmldb_table('block_notgraded_cache');
        
        if ( $dbman->table_exists($table) )
        {
            // очистка кэша
            $DB->delete_records('block_notgraded_cache');
            
            // Удаление старого индекса
            $index = new xmldb_index('graderscourse', XMLDB_INDEX_UNIQUE, [
                'graderid',
                'courseid'
            ]);
            if ( $dbman->index_exists($table, $index))
            {// Индекс есть, удаляем
                $dbman->drop_index($table, $index);
            }
            
            // удаление поля с курсом
            $field = new xmldb_field('courseid');
            if($dbman->field_exists($table, $field))
            {
                $dbman->drop_field($table, $field);
            }
            
            // снятие дефолтного значения для оценивающего учителя
            $field = new xmldb_field('graderid',       XMLDB_TYPE_INTEGER, '10', true, XMLDB_NOTNULL, null );
            if($dbman->field_exists($table, $field))
            {
                $dbman->change_field_default($table, $field);
            }
            
            // добавление уникального индекса для оценивающего учителя
            $index = new xmldb_index('graderid', XMLDB_INDEX_UNIQUE, ['graderid']);
            $dbman->add_index($table, $index);

            // переименование таблицы
            $dbman->rename_table($table, 'block_notgraded_gradercache');
        }
    }
    return $result;
}
