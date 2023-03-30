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
 * Обработка отлавливаемых событий
 *
 * @package   enrol_otautoenrol
 * @category  event
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class enrol_otautoenrol_observer
{
    /**
     * Событие обновления профиля пользователя
     *
     * @param \core\event\user_updated $event
     */
    public static function user_updated(\core\event\user_updated $event)
    {
        global $DB, $CFG;

        // Подключение файла с классом контекста курса
        require_once $CFG->libdir . "/accesslib.php";

        $data = $event->get_data();
        $userid = $data['objectid'];
        $userobj = core_user::get_user($userid);

        // Буфер контекстов курсов
        $contextscourses = [];
        $plugin = enrol_get_plugin('otautoenrol');

        // Тип сервера
        $servertype = $plugin->get_config('servertype');

        if ( ! empty($plugin) && (intval($servertype) >= 2) )
        {
            $enrols = $DB->get_records('enrol', ['enrol' => 'otautoenrol', 'status' => 0]);
            if ( ! empty($enrols) )
            {
                foreach ( $enrols as $enrol )
                {
                    if ( ! array_key_exists($enrol->courseid, $contextscourses) )
                    {
                        $contextscourses[$enrol->courseid] = context_course::instance($enrol->courseid);
                    }
                    // Проверка на отписку пользователя
                    $plugin->check_users_to_unenrol($enrol, [$userobj]);

                    // Проверка на подписку
                    if ( $plugin->enrol_allowed($userobj, $enrol) )
                    {
                        $plugin->enrol_user($enrol, $userobj->id, $enrol->customint3, time());
                        $plugin->process_group($enrol, $userobj);
                    }

                    // запуск синхронизации групп с полем профиля (внутри есть проверка настроек)
                    $plugin->sync_groups_with_field($enrol, $userobj);
                }
            }
        }
    }
}
