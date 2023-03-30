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
 * Запланированная задача по выполнению обработки периодических процессов (cron) в плагинах
 *
 * @package    block
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_dof\task;

use core\task\scheduled_task;

class execute_plugins_cron extends scheduled_task
{

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('execute_plugins_cron', 'block_dof');
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
        
        // Исполняем cron
        dof_mtrace(1, "Load plugins cron: ");
        // Получеем список модулей, для которых нужно запускать крон
        $plugins = $DB->get_records_select('block_dof_plugins', 
            "cron>0 AND (lastcron IS NULL OR (lastcron+cron)<" . time() . ")");
        if( ! empty($plugins) )
        {
            foreach ( $plugins as $plugin )
            {
                dof_mtrace(1, "Cron: {$plugin->type}/{$plugin->code} ", '');
                // Предварительно помечаем задание, как исполненное
                // (Уменьшаем вероятность запуска двух процессов)
                $plugin2 = new \stdClass();
                $plugin2->lastcron = time();
                $plugin2->id = $plugin->id;
                $DB->update_record('block_dof_plugins', $plugin2);
                // Исполняем задание и проверяем результат
                if ( $DOF->plugin($plugin->type, $plugin->code)->cron(dof_get_loan(), 3) )
                {
                    if ( (time() - $plugin2->lastcron) > 30 )
                    {
                        // Обновляем время завершения исполнения
                        $plugin2->lastcron = time();
                        $DB->update_record('block_dof_plugins', $plugin2);
                    }
                    dof_mtrace(1, " [ok]");
                } else
                {
                    dof_mtrace(1, " [error]");
                    $result = false;
                }
            }
        }
        return $result;
    }
}
