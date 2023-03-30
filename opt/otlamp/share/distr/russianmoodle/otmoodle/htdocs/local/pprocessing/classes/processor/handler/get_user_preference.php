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
use local_pprocessing\logger;

defined('MOODLE_INTERNAL') || die();

/**
 * Получение user_preference пользователя по названию
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_user_preference extends base
{
    
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        // уникальный код сценария
        $userid = $container->read('user.id');
        $logcode = $this->get_type()."__".$this->get_code();
        // проверка конфигов
        if ( empty($userid) )
        {
            // данных недостаточно для выполнения операции
            // запись в лог
            logger::write_log('processor', $logcode, 'error', ['userid' => $userid], 'user not found');
            return false;
        }
        
        if (!array_key_exists('name', $this->config))
        {
            // данных недостаточно для выполнения операции
            // запись в лог
            logger::write_log('processor', $logcode, 'error', [], 'user preference name required');
            return false;
        }
        
        $userpref = get_user_preferences($this->config['name'], null, $userid);
        $container->write('user.preferences.'.$this->config['name'], $userpref);
        return $userpref;
    }
}

