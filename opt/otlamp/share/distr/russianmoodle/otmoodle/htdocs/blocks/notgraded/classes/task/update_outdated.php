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
 * Задача по обновлению устаревших данных по неоцененным заданиям
 *
 * @package    block
 * @subpackage notgraded
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace block_notgraded\task;

global $CFG;
require_once($CFG->dirroot.'/blocks/notgraded/lib.php');

class update_outdated extends \core\task\scheduled_task 
{
    /**
     * Получить имя задачи
     *
     * @return string
     */
    public function get_name() 
    {
        return get_string('task_update_outdated_title', 'block_notgraded');
    }

    /**
     * Исполнение задачи
     */
    public function execute() 
    {
            $bnl = new \block_notgraded_gradercache();
            $bnl->update_course_cache();
    }
}