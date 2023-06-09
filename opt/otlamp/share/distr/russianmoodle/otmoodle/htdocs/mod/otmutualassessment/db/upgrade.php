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

defined('MOODLE_INTERNAL') || die();

function xmldb_otmutualassessment_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2020061800) {
        $table = new xmldb_table('otmutualassessment');
        $field = new xmldb_field('options', XMLDB_TYPE_TEXT, 'medium', XMLDB_UNSIGNED, null, null, null, 'completionsetgrades');
        
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        upgrade_mod_savepoint(true, 2020061800, 'otmutualassessment');
    }
    return true;
}