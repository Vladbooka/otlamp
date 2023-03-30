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

use core_user;
use local_pprocessing\container;
include_once $CFG->dirroot.'/user/profile/lib.php';

defined('MOODLE_INTERNAL') || die();

/**
 * Получение пользователей из конфига плагина
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_config_users extends base
{
    
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        // получение списка отправителей
        $recievers = get_config('local_pprocessing', 'recievers');
        if ( ! empty($recievers) )
        {
            $recievers = explode(',', $recievers);
        } else
        {
            $recievers = [];
        }
        
        // формирование обработанного списка пользователей
        $processedrecievers = [];
        $containerrecievers = [];
        if ( empty($recievers) )
        {
            // конфиг пустой, отправляем всем админам
            foreach ( get_admins() as $reciever )
            {
                profile_load_custom_fields($reciever);
                $reciever->fullname = fullname($reciever);
                $processedrecievers[] = $reciever;
                
                // оставлено для совместимости со сценариями, в которых хэндлеры по прежнему содержат циклы
                // необходимо переписать на использование итератора и избавиться от устаревшего подхода
                $containerrecievers[] = static::convert_user($reciever);
            }
        } else
        {
            foreach ( $recievers as $reciever )
            {
                $user = core_user::get_user($reciever);
                if ( ! empty($user) )
                {// пользователь существует
                    
                    profile_load_custom_fields($user);
                    $user->fullname = fullname($user);
                    $processedrecievers[] = $user;
                    
                    // оставлено для совместимости со сценариями, в которых хэндлеры по прежнему содержат циклы
                    // необходимо переписать на использование итератора и избавиться от устаревшего подхода
                    $containerrecievers[] = static::convert_user($user);
                }
            }
        }
        
        // кладем получателей в контейнер
        $container->write('users', $containerrecievers, false);
        return $processedrecievers;
    }
}

