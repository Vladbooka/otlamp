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
 * Класс событий деканата
 */

namespace theme_opentechnology\event;

use core\event\base;
use coding_exception;

defined('MOODLE_INTERNAL') || die();

class spelling_mistake extends base 
{
    
    /**
     * Инициализация события
     *
     * @return void
     */
    protected function init() 
    {
        // Тип события
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }
    
    /**
     * Валидация данных
     *
     * @return void
     * 
     * @throws coding_exception
     */
    protected function validate_data() 
    {
        if ( empty($this->data['other']) ||
                empty($this->data['other']['url']) ||
                empty($this->data['other']['mistake']))
        {
            // валидация данных события
            throw new coding_exception('Invalid event data for spelling mistake');
        }
    }
}
