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
use local_pprocessing\logger;

require_once($CFG->dirroot . '/cohort/lib.php');
defined('MOODLE_INTERNAL') || die();

/**
 * Класс обработчика запуска события обновления профиля пользователя
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class trigger_event_user_updated extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        $users = $container->export('users');
        if( empty($users) )
        {
            return;
        }
        foreach($users as $user)
        {
            $event = \core\event\user_updated::create_from_userid($user['user.id']);
            $event->trigger();
        }
    }
}

