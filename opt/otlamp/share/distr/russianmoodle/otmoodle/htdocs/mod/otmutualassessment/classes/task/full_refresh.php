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
 * Задача обновления оценок модуля
 *
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otmutualassessment\task;

require_once($CFG->dirroot . '/mod/otmutualassessment/locallib.php');

defined('MOODLE_INTERNAL') || die();

use \core\task\adhoc_task;
use context_module;

class full_refresh extends adhoc_task
{
    /**
     * Получить имя задачи
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('task_full_refresh_title', 'mod_otmutualassessment');
    }
    
    /**
     * Исполнение задачи
     */
    public function execute()
    {
        try {
            mod_otmutualassessment_execute_task($this);
        } catch(\Exception $e)
        {
            mtrace($e->getMessage(), 2);
        }
    }
}