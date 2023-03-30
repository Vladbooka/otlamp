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
use core_user;

defined('MOODLE_INTERNAL') || die();

/**
 * Получить полное имя пользователя
 *
 * @package local
 * @subpackage pprocessing
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_user_fullname extends base
{
    protected function validate_parameter($name, $value) {
        if ($name == 'array') {
            return is_array($value);
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        $user = $this->get_required_parameter('user');
        if (is_number($user)) {
            $user = core_user::get_user($user);
        }
        return fullname($user);
    }
}

