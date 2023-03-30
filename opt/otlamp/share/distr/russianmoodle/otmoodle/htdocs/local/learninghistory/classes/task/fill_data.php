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
 * Задача заполнения хранилищ данными
 *
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learninghistory\task;

defined('MOODLE_INTERNAL') || die();

use \core\task\adhoc_task;
use \local_learninghistory\fill;

class fill_data extends adhoc_task
{
    /**
     * Получить имя задачи
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('task_fill_data_title', 'local_learninghistory');
    }
    
    /**
     * Исполнение задачи
     */
    public function execute()
    {
        try {
            $manager = new fill();
            $customdata = $this->get_custom_data();
            if (! isset($customdata->method)) {
                return;
            }
            if (method_exists($manager, $customdata->method)) {
                $manager->{$customdata->method}();
            }
        } catch(\Exception $e)
        {
            mtrace($e->getMessage(), 2);
        }
    }
}