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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
namespace local_pprocessing\processor\handler;

use local_pprocessing\container;
use local_pprocessing\processor\condition;
use local_pprocessing\logger;

defined('MOODLE_INTERNAL') || die();

/**
 * Efficiently retrieves the $course (stdclass) and $cm (cm_info) objects, given
 * an instance id or record and module name.
 *
 * @package local
 * @subpackage pprocessing
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_cm_by_instanceid extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        global $DB;
        $instanceid = $this->get_required_parameter('instanceid');
        $modulename = $this->get_required_parameter('modulename');
        $sql = "SELECT cm.*, m.name as modname 
                  FROM {course_modules} cm
             LEFT JOIN {modules} m
                    ON cm.module=m.id
                 WHERE cm.instance = :instanceid AND m.name = :modulename";
        return $DB->get_record_sql($sql, ['instanceid' => $instanceid, 'modulename' => $modulename]);
    }
}

