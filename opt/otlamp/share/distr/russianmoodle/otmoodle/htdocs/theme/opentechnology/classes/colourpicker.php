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

/**
 * Тема СЭО 3KL. Улучгенное поле выбора цвета для настроек темы.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_opentechnology;

use admin_setting_configcolourpicker;

defined('MOODLE_INTERNAL') || die;

class colourpicker extends admin_setting_configcolourpicker 
{
    /**
     * Процесс сохранения настройки
     * 
     * @param string $data - Выбранный цвет
     * 
     * @return void
     */
    public function write_setting($data)
    {
        if ( $data == '' )
        {
            // Очистка цвета
            return ($this->config_write($this->name, $data) ? '' : get_string('errorsetting', 'admin'));
        } else 
        {
            // Нормализация цвета
            if ( substr($data, 0, 1) == '#' )
            {// Передан HEX
                $data = str_replace('#', '', $data);
                if ( strlen($data) == 3 )
                {
                    $data = str_repeat(substr($data, 0, 1), 2).
                    str_repeat(substr($data, 1, 1), 2).
                    str_repeat(substr($data, 2, 1), 2);
                }
                $data = '#'.$data;
            }
            return parent::write_setting($data);
        }
    }
}