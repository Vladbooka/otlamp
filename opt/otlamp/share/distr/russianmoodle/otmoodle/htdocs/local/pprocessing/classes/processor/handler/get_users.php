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
use local_pprocessing\logger;
include_once $CFG->dirroot.'/user/profile/lib.php';

defined('MOODLE_INTERNAL') || die();

/**
 * Получение данных пользователей
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_users extends base
{

    /**
     * @var array - массив, поддерживаемых обработчиком префильтров
     *              (будет учитываться для отображения настройки фильтра в интерфейсе)
     */
    const supports_filters = ['user'];

    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        global $DB;

        $conditions = '1=1';
        $parameters = [];

        $userfilter = $container->export('userfilter');

        if ( ! empty($userfilter->conditions) )
        {
            $conditions = $userfilter->conditions;
        }
        if ( ! empty($userfilter->parameters) )
        {
            $parameters = $userfilter->parameters;
        }

        $sql = "SELECT u.*
                FROM {user} u
           LEFT JOIN {user_info_data} uid ON uid.userid = u.id
           LEFT JOIN {user_info_field} uif ON uid.fieldid = uif.id
           LEFT JOIN {user_preferences} up ON up.userid = u.id
               WHERE ". $conditions . "
               GROUP BY u.id";
        
        $this->debugging('Get users query', ['sql' => $sql, 'parameters' => $parameters]);
        
        $users = $DB->get_records_sql($sql, $parameters);
        $containerusers = [];
        foreach($users as $k => $user)
        {
            profile_load_custom_fields($users[$k]);
            $users[$k]->fullname = fullname($user);
            
            // оставлено для совместимости со сценариями, в которых хэндлеры по прежнему содержат циклы
            // необходимо переписать на использование итератора и избавиться от устаревшего подхода
            $containerusers[$k] = static::convert_user($users[$k]);
        }

        // кладем пользователя в пулл
        $container->write('users', $containerusers, false);
        return $users;
    }
}

