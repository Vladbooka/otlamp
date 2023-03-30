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

use Exception;
use otcomponent_yaml\Yaml;
use otcomponent_customclass\parsers\form\parser as formparser;

class utils
{
    /**
     * Парсинг строки в объекты
     *
     * @param string $yamlstring
     *
     * @return result
     */
    public static function parse($yamlstring = '')
    {
        $result = new result();
        try
        {
            // парсинг разметки
            $arr = Yaml::parse($yamlstring, yaml::PARSE_OBJECT);
        } catch (Exception $e)
        {
            // в разметке есть ошибки, вернем пустой результат
            return $result;
        }
        if (!is_array($arr) || !array_key_exists('class', $arr) || !is_array($arr['class']))
        {
            return $result;
        }
        
        // парсинг формы
        $result->set_form(formparser::parse($arr['class']));
        
        return $result;
    }
}