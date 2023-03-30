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
 * @subpackage gradesintegrity
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/grade/grade_item.php");
require_once("$CFG->libdir/grade/grade_grade.php");
require_once("$CFG->libdir/grade/constants.php");

function tool_gradesintegrity_execute()
{
    global $DB, $USER;
    
    // Первый этап - ищем записи в таблицах grade_items и grade_grades, которые не связаны ни с одним модулем курса
    // Кейс, когда модуль курса удален, а записи с оценками остались в обеих таблицах
    $sql = 'SELECT gi.id, gi.iteminstance, m.name, cm.id AS cmid
              FROM {grade_grades} gg
              JOIN {grade_items} gi
                ON gi.id = gg.itemid
              JOIN {modules} m
                ON m.name = gi.itemmodule
   LEFT OUTER JOIN {course_modules} cm
                ON cm.instance = gi.iteminstance AND cm.module = m.id
             WHERE cm.id IS NULL AND gi.itemtype = \'mod\'
             GROUP BY gi.iteminstance, m.name, cm.id, gi.id
             ORDER BY m.name ASC, gi.iteminstance ASC';
    if( $records = $DB->get_records_sql($sql) )
    {
        foreach($records as $record)
        {
            $gi = new grade_item(['id' => $record->id]);
            try
            {
                $gi->delete();
                \core\notification::info(get_string('gi_deleted_successfully', 'tool_gradesintegrity', $record));
            } catch(Exception $e) 
            {
                \core\notification::error(get_string('gi_deleting_failed', 'tool_gradesintegrity', $record) . PHP_EOL . $e->getMessage());
            }
        }
    } else
    {
        \core\notification::info(get_string('not_found_records_part_one', 'tool_gradesintegrity'));
    }
    
    // Второй этап - ищем записи в таблицах grade_grades, которые не связаны ни с одной записью из grade_items
    // Кейс, когда модуль курса удален, запись в grade_items удалена, а оценки остались
    $sql = 'SELECT gg.*
              FROM {grade_grades} gg
   LEFT OUTER JOIN {grade_items} gi
                ON gi.id = gg.itemid
             WHERE gi.id IS NULL';
    if( $records = $DB->get_records_sql($sql) )
    {
        foreach($records as $record)
        {
            if( $DB->delete_records('grade_grades', ['id' => $record->id]) )
            {
                \core\notification::info(get_string('gg_deleted_successfully', 'tool_gradesintegrity', $record));
                $id = $record->id;
                unset($record->id);
                unset($record->timecreated);
                $record->action = GRADE_HISTORY_DELETE;
                $record->oldid = $id;
                // Попробуем получить source из таблицы истории grade_items_history
                if( $possiblegi = $DB->get_record(
                    'grade_items_history', 
                    [
                        'oldid' => $record->itemid, 
                        'action' => GRADE_HISTORY_DELETE
                    ], 
                    '*', 
                    IGNORE_MISSING) 
                )
                {
                    $record->source = "$possiblegi->itemtype/$possiblegi->itemmodule";
                } else 
                {
                    $record->source = null;
                }
                $record->timemodified = time();
                $record->loggeduser = $USER->id;
                $DB->insert_record('grade_grades_history', $record);
                if( $possiblegi )
                {// Пробуем выкинуть событие
                    $event = \core\event\grade_deleted::create(
                        [
                            'objectid'      => $id,
                            'context'       => \context_course::instance($possiblegi->courseid, IGNORE_MISSING),
                            'relateduserid' => $record->userid,
                            'other'         => [
                                'itemid'     => $record->itemid,
                                'overridden' => !empty($record->overridden),
                                'finalgrade' => $record->finalgrade
                            ],
                        ]
                    );
                    $event->trigger();
                }
            }
        }
    } else
    {
        \core\notification::info(get_string('not_found_records_part_two', 'tool_gradesintegrity'));
    }
}
