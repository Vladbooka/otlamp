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
 * Блок "Поделиться ссылкой"
 * 
 * @package    block
 * @subpackage block_otshare
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_block_otshare_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();
    
    if ( $oldversion < 2017060220 ) {
        // Создание таблицы
        $table = new xmldb_table('block_otshare_shared_data');
        if ( ! $dbman->table_exists($table) )
        {
            // Создаем поля
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED);
            $table->add_field('data', XMLDB_TYPE_TEXT);
            $table->add_field('hash', XMLDB_TYPE_CHAR, '12');

            // Создание первичного ключа
            $table->add_key('id', XMLDB_KEY_PRIMARY, ['id']);
            
            // Создание индексов
            $table->add_index('iuserid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
            $table->add_index('itimecreated', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);
            $table->add_index('ihash', XMLDB_INDEX_NOTUNIQUE, ['hash']);

            // Создание таблицы
            $dbman->create_table($table);
        }
    }

    return true;
}