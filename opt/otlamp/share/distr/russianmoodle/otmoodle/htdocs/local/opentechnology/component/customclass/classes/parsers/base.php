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

namespace otcomponent_customclass\parsers;

require_once("$CFG->libdir/formslib.php");
require_once("$CFG->libdir/accesslib.php");

/**
 * Базовый класс парсера
 *
 * @package    local_opentechnology
 * @subpackage otcomponent_customclass
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base
{
    /**
     * Обработка данных и возврат результата
     *
     * @param array $fields
     *
     * @return mixed
     */
    protected static function execute($fields)
    {
        return '';
    }
    
    /**
     * Получение типа
     *
     * @param string $type
     *
     * @return string
     */
    public static function cast_type($type)
    {
        global $CFG;
        
        if ( array_key_exists($type, static::$cast_types) )
        {
            return static::$cast_types[$type];
        }
        
        $customfieldspath = $CFG->dirroot."/local/opentechnology/component/customclass/classes/parsers/form/element";
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($customfieldspath));
        $phpfiles = new \RegexIterator($files, '/\.php$/');
        foreach($phpfiles as $phpfile)
        {
            $elementtype = $phpfile->getBasename('.php');
            if ($elementtype == $type && class_exists('otcomponent_customclass\parsers\form\element\\' . $elementtype))
            {
                return $elementtype;
            }
        }
        
        return '';
    }
    
    /**
     * Парсинг и предобработка данных
     *
     * @param string $fields
     *
     * @return mixed
     */
    public static function parse($fields)
    {
        foreach ($fields as $fieldname => $value)
        {
            $fields[$fieldname]['type'] = static::cast_type($value['type']);
        }
        return static::execute($fields);
    }
}
