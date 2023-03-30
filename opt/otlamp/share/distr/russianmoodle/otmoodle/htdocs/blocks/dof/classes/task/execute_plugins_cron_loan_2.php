<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

/**
 * Задача по обслуживанию Деканата. Исполнение стандартных задач.
 *
 * @package    block
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_dof\task;

use core\task\scheduled_task;
use stdClass;

class execute_plugins_cron_loan_2 extends scheduled_task
{
    /**
     * Получить локализованное имя задачи
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('execute_plugins_cron_loan_2', 'block_dof');
    }

    /**
     * Исполнение задачи
     */
    public function execute()
    {
        // Подключение Деканата
        require_once(dirname(realpath(__FILE__)).'/../../locallib.php');
        
        global $CFG, $COURSE, $USER, $DB, $DOF;
        
        $result = true;
        
        // Логирование старта задачи
        dof_mtrace(1, 'Load plugins cron with loan 2: ');
        
        // Получение списка модулей Деканата
        $plugins = $DB->get_records_select(
            'block_dof_plugins', 
            "cron > 0");
        
        if( ! empty($plugins) )
        {// Плагины, требующие запуска задачи, найдены
            
            foreach ( $plugins as $plugin )
            {
                dof_mtrace(1, "Cron: {$plugin->type}/{$plugin->code} ", '');
                
                // Предварительно помечаем задание, как исполненное
                // (Уменьшаем вероятность запуска двух процессов)
                $update = new stdClass();
                $update->lastcron = time();
                $update->id = $plugin->id;
                $DB->update_record('block_dof_plugins', $update);
                
                // Исполняем задание
                if ( $DOF->plugin($plugin->type, $plugin->code)->cron(2, 3) )
                {// Задача выполнена успешно
                    if ( (time() - $update->lastcron) > 30 )
                    {
                        // Обновляем время завершения исполнения
                        $update->lastcron = time();
                        $DB->update_record('block_dof_plugins', $update);
                    }
                    dof_mtrace(1, " [ok]");
                } else
                {// Задача не выполнена
                    dof_mtrace(1, " [error]");
                    $result = false;
                }
            }
        }
        
        // Исполняем задания
        dof_mtrace(1, "Load todo`s with loan 2: ");
        //  Получаем список неисполненных заданий
        $todos = $DB->get_records_select('block_dof_todo', "exdate=0 AND tododate < " . time() . " AND loan = 2");
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
