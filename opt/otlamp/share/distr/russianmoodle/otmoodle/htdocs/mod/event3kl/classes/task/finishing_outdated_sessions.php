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
 * Задача завершения сессий, чье максимально допустимое время жизни истекло
 *
 * @package    mod_event3kl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_event3kl\task;

use mod_event3kl\session;

class finishing_outdated_sessions extends \core\task\scheduled_task
{
    public function get_name() {
        return get_string('finishing_outdated_sessions', 'mod_event3kl');
    }

    public function execute() {

        $lifetime = get_config('mod_event3kl', 'session_lifetime');
        if ($lifetime === false) {
            $lifetime = 86400;
        }

        // если время старта меньше, чем указанная ниже, значит сессия запущена слишком давно
        $outdate = time()-$lifetime;
        $select = ' (overridenstartdate IS NULL AND startdate < $1) OR
                    (overridenstartdate IS NOT NULL AND overridenstartdate < $2)';
        $outdatedsessions = session::get_records_select($select, [$outdate, $outdate]);

        mtrace('Found ' . count($outdatedsessions) . ' outdated sessions');

        foreach($outdatedsessions as $outdatedsession) {
            if (!$outdatedsession->try_finish()) {
                mtrace('Session [id=' . $outdatedsession->get('id') . '] was not finished');
            }
        }

    }

}