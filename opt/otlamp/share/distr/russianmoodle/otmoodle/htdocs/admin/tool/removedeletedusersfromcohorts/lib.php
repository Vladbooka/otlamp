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
 * Local lib code
 *
 * @package    tool
 * @subpackage removedeletedusersfromcohorts
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function tool_removedeletedusersfromcohorts_execute()
{
    global $DB;
    // выбиреаем пользователей со статусом удален содержвшихся в глобальной группе
    $sql = 'SELECT cm.id, cm.userid, u.firstname, u.lastname
              FROM {cohort_members} cm
        INNER JOIN {user} u
                ON u.id = cm.userid
             WHERE u.deleted = 1';
    $records = $DB->get_recordset_sql($sql);
    if ($records->valid()) {
        $i = 0;
        foreach ($records as $record) {
            // удаляем пользователя
            $DB->delete_records('cohort_members', ['id' => $record->id]);
            // формируем строку о пользователе
            $record->userstring = $record->firstname . ' ' . $record->lastname . ' ' . ' ( ' . $record->userid . ' )';
            \core\notification::info(get_string('deleted_successfully', 'tool_removedeletedusersfromcohorts', $record));
            $i++;
        }
        \core\notification::info(get_string('count_records', 'tool_removedeletedusersfromcohorts').$i);
    }else{
        \core\notification::info(get_string('not_found_records', 'tool_removedeletedusersfromcohorts'));
    }
}