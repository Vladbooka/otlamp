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
 * Задача обновления времени изучения курса
 *
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learninghistory\task;

require_once($CFG->dirroot . '/local/learninghistory/lib.php');

defined('MOODLE_INTERNAL') || die();

class activetime_refresh extends \core\task\adhoc_task
{
    /**
     * Получить имя задачи
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('task_activetime_refresh_title', 'local_learninghistory');
    }
    
    /**
     * Исполнение задачи
     */
    public function execute()
    {
        try {
            local_learninghistory_check_activetime(true);
        } catch(\Exception $e)
        {
            mtrace($e->getMessage());
        }
    }
}