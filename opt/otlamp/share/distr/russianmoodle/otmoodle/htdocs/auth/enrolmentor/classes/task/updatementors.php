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

namespace auth_enrolmentor\task;

use \auth_enrolmentor\helper;

class updatementors extends \core\task\adhoc_task {
    
    /**
     * Получить имя задачи
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('updatementors_task_title', 'auth_enrolmentor');
    }
    
    public function execute() {
        
        global $DB;
        
        if (!is_enabled_auth('enrolmentor')) {
            mtrace(get_string('enrolmentor_disabled','auth_enrolmentor'));
            return;
        }
        
        $users = $DB->get_recordset('user', ['deleted' => 0], '', 'id');
        if ($users->valid()) {
            foreach ($users as $user)
            {
                helper::role_assignment_process($user->id, 'u', true);
            }
        }
    }
}