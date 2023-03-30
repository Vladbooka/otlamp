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
 * Сводка по пользователям. Таск на сбор статистики
 *
 * @package    report
 * @subpackage ot_usersoverview
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_mods_data\task;

use report_mods_data\report_helper;

class collectdata extends \core\task\scheduled_task
{
    /**
     * Получить имя задачи
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('task_collectdata_title', 'report_mods_data');
    }
    
    /**
     * Исполнение задачи
     *
     * @return void
     */
    public function execute()
    {
        $enablecron = get_config('report_mods_data', 'enablecron');
        if ( ! empty($enablecron)  )
        {
            // сброс кешей
            report_helper::purgecaches();
            
            // сбор всех данных
            report_helper::collectcache();
        }
    }
}

