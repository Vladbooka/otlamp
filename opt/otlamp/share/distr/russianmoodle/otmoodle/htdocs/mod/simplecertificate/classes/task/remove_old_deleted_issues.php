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
 * Исполнение периодических обработчиков и обработчиков с отсрочкой
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_simplecertificate\task;

class remove_old_deleted_issues extends \core\task\scheduled_task
{
    /**
     * Получить имя задачи
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('task_remove_old_deleted_issues', 'mod_simplecertificate');
    }

    /**
     * Исполнение задачи
     *
     * @return void
     */
    public function execute()
    {
        global $DB;

        $lifetime = get_config('simplecertificate', 'certlifetime');

        if ($lifetime > 0) {

            $month = 2629744;
            $timenow = time();
            $delta = $lifetime * $month;
            $timedeleted = $timenow - $delta;

            $DB->delete_records_select('simplecertificate_issues', 'timedeleted <= ?', array($timedeleted));

        }

    }
}

