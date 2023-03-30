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
 * Логическая группа. Абстрактный класс
 *
 * @package    local_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_opentechnology\availability_condition;

abstract class abstract_logical_group {

    protected $items = [];

    abstract public function execute();

    public function __construct() {
        foreach(func_get_args() as $arg) {
            $this->items[] = $arg;
        }
    }

    public static function get_code() {
        return (new \ReflectionClass(get_called_class()))->getShortName();
    }

    public static function get_displayname() {
        return get_string('logical_group_'.static::get_code(), 'local_opentechnology');
    }

    public static function get_description() {
        return get_string('logical_group_'.static::get_code().'_desc', 'local_opentechnology');
    }

    public static function get_user_description($a=null) {
        return get_string('logical_group_'.static::get_code().'_userdesc', 'local_opentechnology', $a);
    }

    public static function get_class_info() {
        $classinfo = [
            'fullclassname' => get_called_class(),
            'code' => strtolower(static::get_code()),
            'displayname' => static::get_displayname(),
            'description' => static::get_description(),
            'user_description' => static::get_user_description(),
        ];
        return $classinfo;
    }

    public static function get_classes_info() {
        $classesinfo = [];
        $namespace = 'availability_condition\\logical_group';
        $classes = \core_component::get_component_classes_in_namespace('local_opentechnology', $namespace);
        foreach(array_keys($classes) as $fullclassname) {
            if (method_exists($fullclassname, 'get_class_info')) {
                $classinfo = call_user_func([$fullclassname, 'get_class_info']);
                $classesinfo[$classinfo['code']] = $classinfo;
            }
        }
        return $classesinfo;
    }
}