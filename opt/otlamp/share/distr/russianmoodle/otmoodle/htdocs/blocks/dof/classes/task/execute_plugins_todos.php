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
 * Запланированная задача по выполнению заданий (todo) в плагинах
 *
 * @package    block
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_dof\task;

use core\task\scheduled_task;

class execute_plugins_todos extends scheduled_task
{

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('execute_plugins_todos', 'block_dof');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute()
    {
        require_once (dirname(realpath(__FILE__)) . '/../../locallib.php');
        
        global $CFG, $COURSE, $USER, $DB, $DOF;
        
        $result = true;
        
        // Исполняем задания
        dof_mtrace(1, "Load todo`s: ");
        $loan = dof_get_loan();
        //  Получаем список неисполненных заданий
        $todos = $DB->get_records_select('block_dof_todo',
            "exdate=0 AND tododate<" . time() . " AND loan<={$loan}");
        // Избегаем ошибки обработки пустых списков
        if ( ! empty($todos) )
        {
            foreach ( $todos as $todo )
            {
                // Предварительно отмечаем событие, как исполненное
                $todo2 = new \stdClass();
                $todo2->id = $todo->id;
                $todo2->exdate = time();
                $DB->update_record('block_dof_todo', $todo2);
                // Запускаем задание
                $todo->mixedvar = unserialize($todo->mixedvar);
                if ( ! is_object($todo->mixedvar) )
                {
                    $todo->mixedvar = new \stdClass();
                }
                $todo->mixedvar->personid = $todo->personid;
                $filedata = new \stdClass();
                $filedata->plugintype = $todo->plugintype;
                $filedata->plugincode = $todo->plugincode;
                $filedata->filename = $todo->todocode . '/' . $todo->id;
                $DOF->mtrace(1, "Todo: {$todo->plugintype}/{$todo->plugincode}/{$todo->todocode} ", '',
                true, $filedata);
                if ( $DOF->plugin($todo->plugintype, $todo->plugincode)->todo($todo->todocode,
                    $todo->intvar, $todo->mixedvar) )
                {
                    // Обновляем время завершения исполнения
                    $todo2->exdate = time();
                    $DB->update_record('block_dof_todo', $todo2);
                    $DOF->mtrace(1, " [ok]", "\n", true, $filedata);
                } else
                {
                    // Помечаем снова как неисполненное
                    $todo2->exdate = 0;
                    $DB->update_record('block_dof_todo', $todo2);
                    $DOF->mtrace(1, " [error]", true, $filedata);
                    $result = false;
                }
                // Помечаем задание как исполненное
            }
        }
        
        return $result;
    }
}
