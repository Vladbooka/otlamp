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
 * Панель управления доступом в СДО
 * 
 * Наблюдатель плагина
 * 
 * @package    local_authcontrol
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_authcontrol;

defined('MOODLE_INTERNAL') || die();

require_once ($CFG->dirroot . '/local/authcontrol/lib.php');

/**
 * Класс обработки событий
 */
class observer 
{
    /**
     * Обработка события входа пользователя в СДО
     * 
     * @param \core\event\user_loggedin $data - Объект события
     * 
     * @return void
     */
    public static function user_loggedin(\core\event\user_loggedin $data)
    {
        global $CFG;
        if ( $data->userid > 0 && ! is_siteadmin() &&
             isset($CFG->otserialsaasonlinelimit) && 
             $CFG->otserialsaasonlinelimit > 0 )
        {// Установлен лимит на число пользователей онлайн
            $usersonline = local_authcontrol_count_users_online();
            if ( $usersonline > $CFG->otserialsaasonlinelimit )
            {// Онлайн пользователей больше чем позволяет конфиг
                require_logout();
                redirect('/local/authcontrol/user_limit_exceeded.php');
            }
        }
        
        // Получение состояния подсистемы слежения
        $status = get_config('local_authcontrol', 'authcontrol_select');
        if ( ! empty($status) )
        {// Подсистема слежения включена
            
            // Получение данных события
            $info = $data->get_data();
            if ( isset($info['userid']) && ! empty($info['userid']) )
            {// Указан идентификатор пользователя, вошедшего в СДО
                $userid = $info['userid'];
                if ( ! is_siteadmin($userid) )
                {// Пользователь не является администратором СДО
                    // Подключение контроллера доступа
                    local_authcontrol_check_user($userid);
                }
            }
        }
    }
}
