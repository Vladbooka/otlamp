<?php
// This file is not a part of Moodle - http://moodle.org/
// This is a none core contributed module.
//
// This is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// The GNU General Public License
// can be see at <http://www.gnu.org/licenses/>.

/**
 * Плагин аутентификации OTOAuth. Действия при обновлении плагина.
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_auth_otoauth_upgrade($oldversion = 0)
{
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2013111301)
    {
        $table = new xmldb_table('auth_otoauth');
        $field = new xmldb_field('remoteuserid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'service');

        $dbman->change_field_precision($table, $field);

        upgrade_plugin_savepoint(true, 2013111301, 'auth', 'otoauth');
    }
    
    if( $oldversion < 2018011900 )
    {
        // Convert info in config plugins from auth/dof to auth_dof.
        upgrade_fix_config_auth_plugin_names('otoauth');
        upgrade_fix_config_auth_plugin_defaults('otoauth');
        upgrade_plugin_savepoint(true, 2018011900, 'auth', 'otoauth');
    }
    
    if( $oldversion < 2020032300 )
    {
        $table = new xmldb_table('auth_otoauth_custom_provider');
        if (!$dbman->table_exists($table))
        {
            // Добавление полей к таблице
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', true, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->add_field('name', XMLDB_TYPE_CHAR, '255', false, XMLDB_NOTNULL, null);
            $table->add_field('code', XMLDB_TYPE_CHAR, '255', false, XMLDB_NOTNULL, null);
            $table->add_field('description', XMLDB_TYPE_TEXT, 'medium', false, XMLDB_NOTNULL, null);
            $table->add_field('config', XMLDB_TYPE_TEXT, 'medium', false, XMLDB_NOTNULL, null);
            $table->add_field('status', XMLDB_TYPE_CHAR, '255', false, XMLDB_NOTNULL, null);
            
            // Добавление ключей
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            
            // Добавление индексов
            $table->add_index('icode', XMLDB_INDEX_UNIQUE, ['code']);
            
            $dbman->create_table($table);
        }
    }
    
    return true;
}
