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
 * Задача по полнению хранилищ первичными данными (local_learninghistory_cm и local_learninghistory_module)
*
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learninghistory\task;

use context_course;
use local_learninghistory\local\utilities;
use local_learninghistory\activetime;
use local_learninghistory\attempt\attempt_base;
use local_learninghistory\attempt\mod\attempt_mod_quiz;
use local_learninghistory\attempt\mod\attempt_mod_assign;

require_once($CFG->libdir . '/datalib.php');
require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->libdir . '/grade/grade_item.php');
require_once($CFG->libdir . '/grade/constants.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/xmldb/xmldb_table.php');

defined('MOODLE_INTERNAL') || die();

class init_tables_data extends \core\task\scheduled_task
{
    /**
     * Получить имя задачи
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('task_init_tables_data_title', 'local_learninghistory');
    }

    /**
     * Исполнение задачи
     */
    public function execute()
    {
        global $DB;
        $dbman = $DB->get_manager();
        $table_local_learninghistory_cm = new \xmldb_table('local_learninghistory_cm');
        $table_local_learninghistory_module = new \xmldb_table('local_learninghistory_module');
        // Проверим, существуют ли таблицы, которые мы планируем заполнять
        if ( $dbman->table_exists($table_local_learninghistory_cm) && $dbman->table_exists($table_local_learninghistory_module) )
        {
            $users = $cms = $userll = [];
            // Подготавливаем параметры
            $cm_params = [
                'status' => 'active'
            ];
            $module_params = [
                'status' => 'active'
            ];
            // Получим курсы по системе
            $courses = get_courses();
            if( ! empty($courses) )
            {
                foreach($courses as $course)
                {
                    $context = context_course::instance($course->id, IGNORE_MISSING);
                    // Получим пользователей, записанных на курс
                    $users = get_enrolled_users($context);
                    // Получим модули курса
                    $cms = utilities::get_course_mods($course->id);
                    if( ! empty($cms) )
                    {
                        $completion = new \completion_info($course);
                        foreach($cms as $cm)
                        {
                            $params = [
                                'courseid' => $course->id,
                                'itemtype' => 'mod',
                                'itemmodule' => $cm->modname,
                                'iteminstance' => $cm->instance,
                                'itemnumber' => 0
                            ];
                            $grade_item = new \grade_item($params);
                            if( ! empty($users) )
                            {
                                foreach($users as $user)
                                {
                                    // Получим подписку пользователя
                                    if( empty($userll[$course->id][$user->id]) )
                                    {
                                        $userll[$course->id][$user->id] = utilities::get_learninghistory_snapshot_actual($course->id, $user->id);
                                    }
                                    if( $userll[$course->id][$user->id] === false )
                                    {
                                        continue;
                                    }
                                    // Получим оценку пользователя за модуль курса
                                    if( ! empty($grade_item) )
                                    {
                                        $final = $grade_item->get_final($user->id);
                                        if( ! empty($final) )
                                        {
                                            $cm_params['finalgrade'] = $final->finalgrade;
                                        }
                                    }
                                    
                                    // Получим отметку о выполнении модуля курса
                                    $current = $completion->get_data($cm, false, $user->id);
                                    $cm_params['completion'] = $current->completionstate;
            
                                    // Получаем текущую попытку элемента
                                    if( in_array($cm->modname, activetime::get_mods_supported_attempts()) )
                                    {
                                        $classname = 'local_learninghistory\attempt\mod\attempt_mod_' . $cm->modname;
                                    } else
                                    {
                                        $classname = 'local_learninghistory\attempt\attempt_base';
                                    }
                                    $attemptmod = new $classname($cm->id, $user->id);
                                    $attempt = $attemptmod->get_current_attemptnumber();
                                    if( $attempt === false )
                                    {
                                        $attempt = $attemptmod->get_last_attemptnumber();
                                        if( $attempt === false )
                                        {
                                            $attempt = $attemptmod->get_possible_first_attemptnumber();
                                        }
                                    }
                                    $cm_params['attemptnumber'] = $attempt;
            
                                    // Добавим запись в таблицу local_learninghistory_cm
                                    utilities::set_learninghistory_cm_snapshot($cm->id, $userll[$course->id][$user->id]->id, $user->id, $cm_params);
                                }
                            }
                            // Добавим запись в таблицу local_learninghistory_module
                            utilities::set_learninghistory_module_snapshot($cm->id, $module_params);
                        }
                    }
                }
            }
        }
    }
}