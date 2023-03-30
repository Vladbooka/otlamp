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
 * This file keeps track of upgrades to the mastercourse block
 *
 * @package    block_mastercourse
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for the mastercourse block.
 *
 * @param int $oldversion
 */
function xmldb_block_mastercourse_upgrade($oldversion) {
    global $DB, $CFG;
    
    $dbman = $DB->get_manager();
    
    if ( $oldversion < 2019040104 ) {
        // Создание таблицы
        $table = new xmldb_table('mastercourse_publication');
        if ( ! $dbman->table_exists($table) )
        {
            // Создаем поля
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
            $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'id');
            $table->add_field('status', XMLDB_TYPE_CHAR, '25', null, XMLDB_NOTNULL, null, null, 'courseid');
            $table->add_field('statusinfo', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'status');
            $table->add_field('service', XMLDB_TYPE_CHAR, '25', null, XMLDB_NOTNULL, null, null, 'statusinfo');
            $table->add_field('lastupdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'service');
            
            // Создание первичного ключа
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            
            // Создание индексов
            $table->add_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));
            $table->add_index('status', XMLDB_INDEX_NOTUNIQUE, array('status'));
            $table->add_index('service', XMLDB_INDEX_NOTUNIQUE, array('service'));
            
            // Создание таблицы
            $dbman->create_table($table);
        }
        // Mastercourse savepoint reached.
        upgrade_block_savepoint(true, 2019040104, 'mastercourse');
    }
    if ( $oldversion < 2019073100 ) {
        // Создание таблицы
        $table = new xmldb_table('mastercourse_publication');
        if ( $dbman->table_exists($table) )
        {
            $field = new xmldb_field('statusinfo', XMLDB_TYPE_CHAR, '255');
            if( ! $dbman->field_exists($table, $field))
            {
                $dbman->add_field($table, $field);
            }
        }
        // Mastercourse savepoint reached.
        upgrade_block_savepoint(true, 2019073100, 'mastercourse');
    }

    return true;
}
