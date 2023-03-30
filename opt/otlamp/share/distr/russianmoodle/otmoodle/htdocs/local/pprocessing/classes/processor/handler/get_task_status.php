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

namespace local_pprocessing\processor\handler;

use local_pprocessing\container;

defined('MOODLE_INTERNAL') || die();

/**
 * получение статуса мудловского таска (disabled / enabled)
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_task_status extends base
{
    
    protected function validate_parameter($name, $value)
    {
        switch($name)
        {
            case 'task':
                return is_string($value);
            default:
                return false;
        }
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execution_process()
     */
    protected function execution_process(container $container)
    {
        global $DB;
        
        $classname = $this->get_required_parameter('task');
        
        $task = $DB->get_record('task_scheduled', ['classname' => $classname]);
        
        if (empty($task) || $task->disabled)
        {
            return 'disabled';
            
        } else
        {
            return 'enabled';
        }
    }
}

