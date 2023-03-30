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
 * Класс типа субплагинов Витрины
 *
 * Объявляет новый тип плагинов Moodle.
 * 
 * @package local
 * @subpackage crw
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_crw\plugininfo;

use core\plugininfo\base, moodle_url, part_of_admin_tree, admin_settingpage, core_component;

defined('MOODLE_INTERNAL') || die();

class crw extends base 
{
    
    /**
     * Получить список всех включенных субплагинов Витрины
     * 
     * @return array|null - Массив субплагинов или NULL если таких нет
     */
    public static function get_enabled_plugins() 
    {
        // Готовим массив отключенных субплагинов
        $disabledsubplugins = array();
        // Получим список отключенных субплагинов
        $config = get_config('local_crw', 'disabledsubplugins');
        
        if ( ! empty($config) ) 
        {// Отключенные субплагины есть
            // Сформируем массив отключенных субплагинов
            $config = explode(',', $config);
            foreach ( $config as $subplugin ) 
            {
                // Очистим от пробелов
                $subplugin = trim($subplugin);
                
                if ( $subplugin !== '' ) 
                {// Добавим имя субплагина в массив
                    $disabledsubplugins[$subplugin] = $subplugin;
                }
            }
        }

        // Готовим массив включенных субплагинов
        $enabled = array();
        // Получим все установленные субплагины
        $installed = core_component::get_plugin_list('crw');
        
        foreach ( $installed as $plugin => $fulldir ) 
        {
            if (isset($disabledsubplugins[$plugin])) 
            {// Субплагин отключен
                continue;
            }
            // Добавим субплагин в список включенных
            $enabled[$plugin] = $plugin;
        }
        // Вернем список включенных субплагинов
        return $enabled;
    }

    /**
     * Поддержка удаления
     */
    public function is_uninstall_allowed() 
    {
        return true;
    }

    /**
     * Возвращает страницу управления субплагином
     * 
     * @return moodle_url
     */
    public static function get_manage_url() {
        return new moodle_url('/admin/settings.php', array('section'=>'crw_settings'));
    }

    /**
     * Получить имя страницы настроек субплагина
     * 
     * @return string - имя страницы настроек субплагина
     */
    public function get_settings_section_name() {
        return 'crw_'.$this->name.'_settings';
    }

    /**
     * Добавить страницу настроек субплагина
     * 
     */
    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig, $parentcategory = null) 
    {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;
        $ADMIN = $adminroot;
        $plugininfo = $this;

        if ( ! $this->is_installed_and_upgraded()) 
        {// Субплагин не установлен
            return null;
        }

        if ( ! $hassiteconfig or ! file_exists($this->full_path('settings.php'))) 
        {
            return null;
        }
        
        // Получим имя страницы настроек субплагина
        $section = $this->get_settings_section_name();
        // Объявим страницу настроек субплагина
        $settings = new admin_settingpage(
                $section, 
                $this->displayname, 
                'moodle/site:config', 
                $this->is_enabled() === false
        );
        // Добавим настройки субплагина на объявленную страницу
        include($this->full_path('settings.php')); // This may also set $settings to null.

        if ($settings) 
        {// Добавим страницу настроек субплагина
            $ADMIN->add($parentnodename, $settings);
        }
    }
}
