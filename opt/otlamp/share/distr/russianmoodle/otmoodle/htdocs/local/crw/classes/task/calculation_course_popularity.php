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
 * Задача расчета популярности курса
*
* @package    local_crw
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

namespace local_crw\task;

use Exception;

require_once($CFG->dirroot . '/local/crw/locallib.php');

defined('MOODLE_INTERNAL') || die();

class calculation_course_popularity extends \core\task\scheduled_task
{
    /**
     * Получить имя задачи
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('task_calculation_course_popularity_title', 'local_crw');
    }

    /**
     * Исполнение задачи
     */
    public function execute()
    {
        try {
            $type = get_config('local_crw', 'course_popularity_type');
            if (empty($type)) {
                $type = 'unique_course_view';
            }
            local_crw_calculation_course_popularity($type);
        } catch(Exception $e)
        {
            mtrace($e->getMessage(), 2);
        }
    }
}