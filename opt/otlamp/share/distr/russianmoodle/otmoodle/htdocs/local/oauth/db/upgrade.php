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
 * Oauth. Обновление
 *
 * @package    local_oauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Обновление плагина
 *
 * @param int $oldversion - Старая версия плагина
 * @return bool - Результат
 */
function xmldb_local_oauth_upgrade($oldversion)
{
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if( $oldversion < 2019022800 )
    {
        $table = new xmldb_table('oauth_clients');
        $field = new xmldb_field('preapproved_scopes', XMLDB_TYPE_CHAR);
        
        // Conditionally launch add field opt_continuation
        if ($dbman->table_exists($table) && !$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
    }
    
    return true;
}
