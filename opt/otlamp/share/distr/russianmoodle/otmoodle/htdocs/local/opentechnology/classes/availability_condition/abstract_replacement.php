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
 * Абстрактный тип подстановки
 *
 * @package    local_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_opentechnology\availability_condition;

abstract class abstract_replacement {

    private $contextid;

    abstract public static function get_top_context_level();
    abstract public static function get_icon_code();
    /**
     * @return replacement_property[]
     */
    abstract public static function get_properties();
    abstract public function get_property_value(string $property);

    public function __construct($contextid) {
        if (!self::is_supported_context($contextid)) {
            throw new \Exception('Passed context is not supported by replacement');
        }
        $this->contextid = $contextid;
    }


    public function validate_property(string $property) {
        if (!array_key_exists($property, self::get_properties_info())) {
            throw new \Exception('Unknown property');
        }
    }

    public static function is_supported_context($contextid) {

        $currentcontext = \context::instance_by_id($contextid);
        foreach ($currentcontext->get_parent_contexts(true) as $context) {
            if ($context->contextlevel == static::get_top_context_level()) {
                return true;
            }
        }
        return false;
    }

    public static function get_code() {
        return (new \ReflectionClass(get_called_class()))->getShortName();
    }

    public static function get_displayname() {
        return get_string('replacement_'.static::get_code(), 'local_opentechnology');
    }

    public static function get_properties_info() {
        $propertiesinfo = [];
        foreach (static::get_properties() as $property) {
            $code = $property->getCode();
            $propertiesinfo[$code] = [
                'code' => $code,
                'displayname' => $property->getDisplayname()
            ];
        }
        return $propertiesinfo;
    }

    public static function get_class_info() {
        $classinfo = [
            'fullclassname' => get_called_class(),
            'code' => strtolower(static::get_code()),
            'displayname' => static::get_displayname(),
            'icon' => static::get_icon_code(),
            'properties' => static::get_properties_info()
        ];
        return $classinfo;
    }

    public static function get_classes_info() {
        $classesinfo = [];
        $namespace = 'availability_condition\\replacement';
        $classes = \core_component::get_component_classes_in_namespace('local_opentechnology', $namespace);
        foreach(array_keys($classes) as $fullclassname) {
            if (method_exists($fullclassname, 'get_class_info')) {
                $classinfo = call_user_func([$fullclassname, 'get_class_info']);
                $classesinfo[$classinfo['code']] = $classinfo;
            }
        }
        return $classesinfo;
    }

    public static function get_classes_info_by_contextid($contextid) {
        $classesinfo = self::get_classes_info();
        foreach($classesinfo as $c => $classinfo) {
            if (!array_key_exists('fullclassname', $classinfo)) {
                unset($classesinfo[$c]);
            }
            if (!method_exists($classinfo['fullclassname'], 'is_supported_context')) {
                unset($classesinfo[$c]);
            }
            if (!call_user_func([$classinfo['fullclassname'], 'is_supported_context'], $contextid)) {
                unset($classesinfo[$c]);
            }
        }
        return $classesinfo;
    }

    public static function make_replacement($value, $contextid) {

        $re = '/{([^\.^}]+)\.([^}]+)}/';
        if (preg_match($re, $value, $matches)) {
            $replacementcode = $matches[1];
            $replacementprop = $matches[2];
            $replacements = abstract_replacement::get_classes_info_by_contextid($contextid);
            if (array_key_exists($replacementcode, $replacements)) {
                try {
                    $fullclassname = $replacements[$replacementcode]['fullclassname'];
                    $replacement = new $fullclassname($contextid);
                    $value = $replacement->get_property_value($replacementprop);
                } catch(\Exception $ex) {}
            }
        }

        return $value;
    }

    public static function prepare_displayname($value, $contextid) {

        $re = '/{([^\.^}]+)\.([^}]+)}/';
        if (preg_match($re, $value, $matches)) {
            $replacementcode = $matches[1];
            $replacementprop = $matches[2];
            $replacements = abstract_replacement::get_classes_info_by_contextid($contextid);
            if (array_key_exists($replacementcode, $replacements)) {
                $replacementdname = $replacements[$replacementcode]['displayname'];
                $properties = $replacements[$replacementcode]['properties'];

                if (array_key_exists($replacementprop, $properties) &&
                    array_key_exists('displayname', $properties[$replacementprop])) {

                    return $replacementdname.' "'.$properties[$replacementprop]['displayname'].'"';
                }
            }
        }

        return $value;
    }

}