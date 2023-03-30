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
namespace otcomponent_otlogger;

use core_component;

defined('MOODLE_INTERNAL') || die();

/**
 * Класс управления логированием
 *
 * @package local_opentechnology
 * @subpackage log
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class log_manager {

    /**
     * Запись лога в соответствующий получатель
     */
    public static function create_log($type, $messagedata) {

        // Проверим, включено ли логирование
        if (get_config('otcomponent_otlogger', 'log_enabled')) {
            
            // Получаем список доступных ресиверов для лога указанного типа
            $receivers = static::get_enabled_log_receivers_for_log_type($type);
            // Пишем лог через доступные ресиверы
            foreach ($receivers as $receiverclass){
                $classname = "\\otcomponent_otlogger\\receiver\\" . $receiverclass;
                $receiver = new $classname($type, $messagedata);
                // Записываем лог соответствующим получателем
                $receiver->create_log();
            } 
        }
    }

    /**
     * Получить список плагинов, поддерживающих логирование
     *
     * @return array список плагинов, содержащий значения типа: plugintype_pluginname
     */
    public static function get_logger_supporting_plugins() {

        // Для плагинов, поддерживающих логирование, в либе должна быть определена и возвращать true
        // функция plugintype_pluginname_get_otlog_supported_keys
        $plugins = get_plugins_with_function('otlogger_supported', 'lib.php');
        foreach ($plugins as $plugintype => $pluginitems) {
            foreach ($pluginitems as $pluginname => $function) {
                if ($function()) {
                    $pluginnames[] = $plugintype . '_' . $pluginname;
                }
            }
        }
        return $pluginnames;
    }

    /**
     * Получить список классов логов для плагина
     *
     * @param string $pluginname
     *            имя плагина вида plugintype_pluginname
     * @return array список классов лога для плагина
     */
    private static function get_plugin_available_log_classes($pluginname) {

        return core_component::get_component_classes_in_namespace($pluginname, 'logtype');
    }

    /**
     * Получить список типов логов для плагина
     *
     * @param string $pluginname
     *            имя плагина вида plugintype_pluginname
     * @return array список логтайпов для плагина
     */
    public static function get_plugin_available_log_types($pluginname) {

        $classes = self::get_plugin_available_log_classes($pluginname);
        $logtypes = [];
        foreach ($classes as $classname => $classpath) {
            $logtypes[] = $classname::get_logtype();
        }
        return $logtypes;
    }

    /**
     * Получить список кодов логов для логтайпа
     *
     * @param string $plugin
     *            имя плагина, поддерживающего логтайп вида plugintype_pluginname
     * @param string $logtype
     *            - logtype class fullname
     * @return array список логкодов для логтайпа
     */
    public static function get_logtype_available_log_keys($plugin, $logtypeclass) {

        if (! class_exists($logtypeclass)) {
            throw new \coding_exception('Incorrect logtype classname');
        }
        
        $logkeys = $logtypeclass::get_available_keys();
        
        return $logkeys;
    }

    /**
     * Получить список кодов логов для плагина
     *
     * @param string $pluginname
     *            имя плагина вида plugintype_pluginname
     * @return array вида $logkeys[$logtype] = array $logkeys - список логкодов для плагина
     */
    public static function get_plugin_available_log_keys($pluginname) {

        $classes = self::get_plugin_available_log_classes($pluginname);
        $logkeys = [];
        foreach ($classes as $classname => $classpath) {
            $logtype = $classname::get_logtype();
            // Получаем список доступных кодов лога
            $logkeys[$logtype] = self::get_logtype_available_log_keys($pluginname, $classname);
        }
        return $logkeys;
    }

    /**
     * Получить список доступных кодов лога для всех плагинов, поддерживающих логирование
     *
     * @return array $logkeys[$plugin][$logtype] = array $logkeys
     */
    public static function get_available_log_keys() {

        $plugins = self::get_logger_supporting_plugins();
        $log_keys = [];
        foreach ($plugins as $plugin) {
            $log_keys[$plugin] = self::get_plugin_available_log_keys($plugin);
        }
        return $log_keys;
    }

    /**
     * Получить список доступных получателей лога
     *
     * @return array список имен получателей логов
     */
    public static function get_available_log_receivers() {

        $classes = core_component::get_component_classes_in_namespace('otcomponent_otlogger', 'receiver');
        $receivers = [];
        foreach ($classes as $classname => $classpath) {

            preg_match('/([a-z]|(_))+$/', $classname, $matches);
            $receivers[] = $matches[0];
        }
        return $receivers;
    }

    private static function get_enabled_log_receivers_for_log_type($key) {

        // Получаем возможные конфигурации логирования
        $configurations = explode(',', get_config('otcomponent_otlogger', 'log_configurations'));
        $receivers = [];
    
        foreach ($configurations as $configuration) {
            
            // Включено ли логирование по конфигурации
            if (! get_config('otcomponent_otlogger', $configuration . '_enabled')){
                continue;
            }
            // Для каждой конфигурации проверяем, выбран ли для неё код.
            
            $filters = [];
            $filter =  get_config('otcomponent_otlogger', $configuration . '_filter');
            
            if ($filter){
                $filters = explode(',', $filter);
            }

            // Если фильтра нет - выбраны все коды
            if ((empty ($filters)) || (in_array($key, $filters))) {
                
                $receiver = get_config('otcomponent_otlogger', $configuration . '_receiver');
                
                // Добавляем ресивер, если еще не добавлен
                $receivers = array_merge($receivers, array(
                    $receiver
                ));
            }
        }
        
        return $receivers;
    }
}