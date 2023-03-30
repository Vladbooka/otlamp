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
 * Класс задачи по крону
 *
 * @package    fixlocallearninghistorymodule
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_fixlocallearninghistorymodule\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Class добавления статусов к сессиям у которых этого стануча небыло. 
 *
 */
class fix_local_learninghistory_module extends \core\task\adhoc_task {
    /**
     * Запускает задачу по исправлению таблицы local_learninghistory_module
     */
    public function execute() {
        global $DB;
        // ЭТАП 1 обработаем все записи со статусом null (дубликат записей)
        $records = $DB->get_records('local_learninghistory_module', ['status' => null], 'id DESC');
        if (! empty($records)) {
            $sql = "cmid =:cmid
                    AND courseid =:courseid
                    AND status IS NOT NULL
                    AND status <> 'renamed'";
            foreach ($records as $record) {
                $params = [
                    'cmid'     => $record->cmid,
                    'courseid' => $record->courseid,
                ];
                // выберем дупликаты
                $recordstodel = array_filter($records, function($rec) use ($record){
                    if ($record->cmid == $rec->cmid &&
                        $record->courseid == $rec->courseid)
                    {
                        return true;
                    }
                    return false;
                });
                $recordtodelprev = new \stdClass;
                $listdel = [];
                $i = 0;
                $recordrenamed = false;
                // удалим элементы чтобы больше их не перебирать
                foreach ($recordstodel as $recordtodel) {
                    if (isset($recordtodelprev->id) && $recordtodelprev->name != $recordtodel->name) {
                        // установим статус переименована из предыдущей итерации
                        $recordtodelprev->status = 'renamed';
                        $DB->update_record('local_learninghistory_module', $recordtodelprev);
                        // удалим предыдущую запись из списка к удалению
                        unset($listdel[$i-1]);
                        $recordrenamed = true;
                        // Лог
                        mtrace('Status for local learninghistory module id: ' . $recordtodelprev->id . ' [Renamed]');
                    } else {
                        unset($records[$recordtodel->id]);
                    }
                    $listdel[$i] = $recordtodel->id;
                    $i++;
                    $recordtodelprev = $recordtodel;
                }
                if ($recordrenamed) {
                    continue;
                }
                if (! $DB->record_exists_select('local_learninghistory_module', $sql, $params)) {
                    // так-как записи с нормальным статусом не существует попробем найти ее в course_modules
                    if ($data = $DB->get_record(
                        'course_modules',
                        [
                        'id'     => $record->cmid,
                        'course' => $record->courseid,
                        ],
                        'deletioninprogress'
                        ))
                    {
                        // запись в course_modules есть установим статус
                        $record->status = $data->deletioninprogress ? 'archive' : 'active';
                        $DB->update_record('local_learninghistory_module', $record);
                        // лог
                        mtrace('Status for local learninghistory module id: ' . $record->id . ' ['.$record->status.']');
                        if(($key = array_search($record->id, $listdel)) !== false){
                            unset($listdel[$key]);
                        }
                    } 
                }
                if (! empty($listdel)) {
                    // удаляем запись
                    $DB->delete_records_list('local_learninghistory_module', 'id', $listdel);
                    // лог
                    mtrace('Records l.l. history module ids: ' . implode(', ', $listdel) . ' [Deleted]');
                }
            }   
        }
        // ЭТАП 2 обработаем все записи со статусом active (не сменился статус на archive)
        $records = $DB->get_recordset('local_learninghistory_module', ['status' => 'active'], 'id');
        if ($records->valid()) {
            foreach ($records as $record) {
                if ( ! $DB->record_exists(
                    'course_modules',
                    [
                        'id'     => $record->cmid,
                        'course' => $record->courseid,
                        'deletioninprogress' => 0
                    ]
                    ))
                {
                    // запись в course_modules удалена или отсутствует установим статус archive
                    $record->status = 'archive';
                    $DB->update_record('local_learninghistory_module', $record);
                    // Лог
                    mtrace('Status for local learninghistory module id: ' . $record->id . ' [Archive]');
                }   
            }
        }
    }
}
