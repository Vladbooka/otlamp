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

use context_module;

class delete_expired_shelf_life_issues extends \core\task\scheduled_task
{
    /**
     * Получить имя задачи
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('task_delete_expired_shelf_life_issues', 'mod_simplecertificate');
    }

    /**
     * Исполнение задачи
     *
     * @return void
     */
    public function execute()
    {
        global $DB, $CFG;

        require_once ($CFG->dirroot . '/mod/simplecertificate/locallib.php');

        $certificates = $DB->get_records_select('simplecertificate', 'shelflife > :zero', ['zero' => 0]);
        foreach($certificates as $certificate)
        {
            $cm = get_coursemodule_from_instance('simplecertificate', $certificate->id, $certificate->course);
            if ($cm == false)
            {
                mtrace('Couldn\'t get cm for instance '.$certificate->id);
                continue;
            }
            $contextmodule = context_module::instance($cm->id);
            $simplecertificate = new \simplecertificate($contextmodule, null, null);
            $simplecertificate->delete_expired_shelf_life_issues();
        }

    }
}

