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
 * Настраиваемые поля. Локальные функции
 *
 * @package    local_mcov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mcov;

class helper {

    private static $component = 'local_mcov';

    public static function get_setting_default_args($settingcode)
    {
        return [
            'name' => static::$component.'/'.$settingcode,
            'displayname' => get_string('settings_'.$settingcode, static::$component),
            'description' => get_string('settings_'.$settingcode.'_desc', static::$component)
        ];
    }

    public static function add_navigation_node_next_to(\navigation_node $addnode, \navigation_node $nextto)
    {
        $beforekey = $nextto->key;
        $siblings = $nextto->get_siblings();
        if ($siblings)
        {
            foreach($siblings as $sibling)
            {
                if (!is_null($beforekey) && $sibling->key == $beforekey)
                {
                    // найдена текущая нода - обнулляемся, либо следующая наша, либо (если не будет следующей) - можно и в конец
                    $beforekey = null;
                    continue;
                }
                if (is_null($beforekey))
                {
                    // найдена нода, следующая за текущей
                    $beforekey = $sibling->key;
                    break;
                }
            }
        }

        // Добавление новой ноды
        $nextto->parent->add_node($addnode, $beforekey);
    }

    /**
     * Получить подписчиков на событие
     * @return array of entitycodes
     */
    public static function get_event_subscribers($event) {
        $result = [];
        foreach(self::get_entities_classnames() as $entitycode => $classname) {
            if ($classname::is_subscribed($event)) {
                $result[] = $entitycode;
            }
        }
        return $result;
    }

    /**
     * Получить все объявленные сущности
     * @return \local_mcov\entity[]
     */
    public static function get_entities()
    {
        $entities = [];
        foreach(self::get_entities_classnames() as $entitycode => $classname) {
            $entities[$entitycode] = new $classname($entitycode);
        }
        return $entities;
    }

    public static function get_entity($entitycode) {
        $classnames = self::get_entities_classnames();
        if (array_key_exists($entitycode, $classnames)) {
            $classname = $classnames[$entitycode];
            return new $classname($entitycode);
        }
    }

    public static function get_entities_classnames() {

        global $CFG;
        $entitiesclassnames = [];
        $entitypath = $CFG->dirroot . '/local/mcov/classes/entity';
        foreach(glob($entitypath.'/*.php') as $filepath)
        {
            $code = basename($filepath, '.php');
            require_once($filepath);
            $classname = '\\local_mcov\\entity\\' . $code;
            if (class_exists($classname))
            {
                $entitiesclassnames[$code] = $classname;
            }
        }
        return $entitiesclassnames;
    }
}