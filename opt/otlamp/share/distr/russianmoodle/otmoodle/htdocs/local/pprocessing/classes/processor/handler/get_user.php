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
use local_pprocessing\processor\exception;
include_once $CFG->dirroot.'/user/profile/lib.php';

defined('MOODLE_INTERNAL') || die();

/**
 * Получение данных пользователя по идентификатору
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_user extends base
{
    protected function validate_parameter($name, $value)
    {
        switch($name)
        {
            case 'userid':
                return is_numeric($value);
            default:
                return false;
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        $userid = $this->get_required_parameter('userid');
        
        // TODO: реализовать через вызов get_users для того, чтобы начать поддерживать доп.фильтрацию по другим полям
        
        // результат выполнения обработчика
        $result = null;
        // получение пользователя с кастомными полями
        $user = core_user::get_user($userid);
        profile_load_custom_fields($user);
        if (! empty($user))
        {
            $user->fullname = fullname($user);
            $result = $user;
        }
        return $result;
    }
}

