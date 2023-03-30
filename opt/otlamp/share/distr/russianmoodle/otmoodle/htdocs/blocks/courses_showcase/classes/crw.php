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
 * Блок Витрина курсов
 *
 * @package    block
 * @subpackage courses_showcase
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_courses_showcase;

class crw
{
    protected static $subplugins = [];
    /**
     * Получить сабплагины витрины
     *
     * @return array - Массив доступных плагинов
     */
    public static function get_crw_subplugins($type=null)
    {
        global $CFG;
        
        if (empty(self::$subplugins))
        {
            $subpluginnames = \core_plugin_manager::instance()->get_installed_plugins('crw');
            foreach(array_keys($subpluginnames) as $subpluginname)
            {
                $filename = $CFG->dirroot . '/local/crw/plugins/' . $subpluginname . '/lib.php';
                if( file_exists($filename) )
                {
                    require_once($filename);
                    $subpluginclassname = 'crw_'.$subpluginname;
                    if(class_exists($subpluginclassname))
                    {
                        $subplugin = new $subpluginclassname($subpluginname);
                        if (!array_key_exists($subplugin->get_type(), self::$subplugins))
                        {
                            self::$subplugins[$subplugin->get_type()] = [];
                        }
                        self::$subplugins[$subplugin->get_type()][$subpluginname] = $subplugin;
                    }
                }
            }
        }
        
        if (!is_null($type))
        {
            if (array_key_exists($type, self::$subplugins))
            {
                return self::$subplugins[$type];
            } else
            {
                return [];
            }
            
        } else {
            return self::$subplugins;
        }
    }
}