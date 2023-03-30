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
 * Задача выкачивания записей для сессий, ожидающих скачивания записей
 *
 * @package    mod_event3kl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_event3kl\task;

use mod_event3kl\session;

class process_sessions_pending_records extends \core\task\scheduled_task
{
    public function get_name() {
        return get_string('process_sessions_pending_records', 'mod_event3kl');
    }

    public function execute() {

        $sessions = session::get_records([
            'status' => 'finished',
            'pendingrecs' => 1
        ]);

        mtrace('Found ' . count($sessions) . ' sessions pending download records');

        foreach($sessions as $session) {
            if (!$session->try_download_records()) {
                mtrace('Downloading for session [id=' . $session->get('id') . '] was not complete successfully');
            }
        }

    }

}