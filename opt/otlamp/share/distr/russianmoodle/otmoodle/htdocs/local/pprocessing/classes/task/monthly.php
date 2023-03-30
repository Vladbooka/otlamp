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
 * Задача планировщика запланированная на ежемесячное исполнение
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_pprocessing\task;

defined('MOODLE_INTERNAL') || die();

class monthly extends \core\task\scheduled_task 
{
    /**
     * {@inheritDoc}
     * @see \core\task\scheduled_task::get_name()
     */
    public function get_name() 
    {
        return get_string('task_monthly', 'local_pprocessing');
    }
    
    /**
     * {@inheritDoc}
     * @see \core\task\task_base::execute()
     */
    public function execute() 
    {
        // отправка ежечасного события 
        $event = \local_pprocessing\event\monthly_executed::create();
        $event->trigger();
    }
}