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
 * Настраиваемые формы
 *
 * @package    local_opentechnology
 * @subpackage otcomponent_customclass
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace otcomponent_customclass;


use otcomponent_customclass\parsers\form\customform;

class result
{
    /**
     * Форма
     * 
     * @var customform
     */
    protected $form = null;
    
    /**
     * Проверка сущесвования формы
     * 
     * @return boolean
     */
    public function is_form_exists()
    {
        return ! is_null($this->form);
    }
    
    /**
     * Получение формы из результата
     * 
     * @return customform
     */
    public function get_form()
    {
        return $this->form;
    }
    
    /**
     * Установка формы
     * 
     * @param customform $form
     */
    public function set_form(customform $form)
    {
        $this->form = $form;
    }
}