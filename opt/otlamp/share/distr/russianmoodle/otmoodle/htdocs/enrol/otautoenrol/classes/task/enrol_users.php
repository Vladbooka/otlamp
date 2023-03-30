<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

/**
 * Блок топ-10
 *
 * @package    enrol
 * @subpackage otautoenrol
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_otautoenrol\task;

use core\task\scheduled_task;
use context_course;

class enrol_users extends scheduled_task
{
    /**
     * Получить локализованное имя таска
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('task_enrol_users', 'enrol_otautoenrol');
    }

    /**
     * Исполнение задачи
     *
     * @return void
     */
    public function execute()
    {
        global $DB, $CFG;

        $filteredusers = [];

        // Подключение файла с классом контекста курса
        require_once $CFG->libdir . "/accesslib.php";

        // Получение enrol плагина
        $plugin = enrol_get_plugin('otautoenrol');

        // Получение всех активных существующих в системе способов записи
        $instances = $DB->get_records('enrol', ['enrol' => 'otautoenrol', 'status' => 0]);

        // Буфер контекстов курсов
        $contextscourses = [];

        // Тип сервера
        $servertype = $plugin->get_config('servertype');

        if ( (intval($servertype) >= 1) && ! empty($instances) )
        {
            // Получение всех пользователей
            $users = $DB->get_records('user', ['deleted' => 0, 'suspended' => 0, 'confirmed' => 1]);
            if ( ! empty($users) )
            {
                foreach ( $instances as $instance )
                {
                    if ( ! array_key_exists($instance->courseid, $contextscourses) )
                    {
                        $contextscourses[$instance->courseid] = context_course::instance($instance->courseid);
                    }
                    // Получение подписанных пользователей
                    $enrolledusers = get_enrolled_users($contextscourses[$instance->courseid], '', 0, 'u.id');
                    if ( ! empty($enrolledusers) )
                    {
                        $plugin->check_users_to_unenrol($instance, $enrolledusers);
                    }
                    if ( $instance->customint8 == 1 )
                    {
                        $filteredusers = $users;
                    } else
                    {
                        $filteredusers = array_diff_key($users, $enrolledusers);
                    }

                    // Проверка на подписку
                    if ( ! empty($filteredusers) )
                    {
                        foreach ( $filteredusers as $user )
                        {
                            if ( $plugin->enrol_allowed($user, $instance) )
                            {
                                $plugin->enrol_user($instance, $user->id, $instance->customint3, time());
                                $plugin->process_group($instance, $user);
                            }
                            // запуск синхронизации групп с полем профиля (внутри есть проверка настроек)
                            $plugin->sync_groups_with_field($instance, $user);
                        }
                    }
                }
            }
        }
    }
}
