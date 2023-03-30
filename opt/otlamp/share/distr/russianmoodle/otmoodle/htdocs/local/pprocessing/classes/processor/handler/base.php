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

use stdClass;
use local_pprocessing\container;
use local_pprocessing\processor\base as base_processor;
use local_pprocessing\composite_key;

defined('MOODLE_INTERNAL') || die();

/**
 * Базовый класс обработчика
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base extends base_processor
{
    use composite_key;
        
    /**
     * Конвертация ключей массив с добавлением .user дефиса
     *
     * @param stdClass|array $user
     */
    protected static function convert_user($user)
    {
        $puser = [];
        foreach ( (array)$user as $k => $v )
        {
            $puser['user.' . $k] = $v;
        }
        
        // добавление полного имени
        $puser['user.fullname'] = fullname($user);
        
        return $puser;
    }
    
    /**
     * Метод обратный методу convert_user
     * @param array $puser
     * @return stdClass
     */
    protected static function build_user($puser)
    {
        $user = new stdClass();
        foreach($puser as $k => $v)
        {
            $property = explode('.', $k)[1];
            $user->$property = $v;
        }
        return $user;
    }
    
    
    /**
     * Выполнение обработчика
     *
     * @param container $container
     *
     * @return mixed
     */
    protected function execution_process(container $container)
    {
    }
    
    public function get_type()
    {
        return 'handler';
    }
}

