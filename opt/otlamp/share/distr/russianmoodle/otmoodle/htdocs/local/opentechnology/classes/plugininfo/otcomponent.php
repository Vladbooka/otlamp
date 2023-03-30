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
 * Информация о сабплагинх типа компонента
 *
 * @package    local_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_opentechnology\plugininfo;

use core\plugininfo\base;
use core_plugin_manager;
use part_of_admin_tree;
use admin_settingpage;
use admin_category;

defined('MOODLE_INTERNAL') || die();


class otcomponent extends base 
{
    /**
     * Список включенных сабплагинов типа "компонента"
     * 
     * @return array|null
     */
    public static function get_enabled_plugins() 
    {
        $enabled = [];
        $plugins = core_plugin_manager::instance()->get_installed_plugins('otcomponent');
        if ( ! $plugins ) 
        {
            return $enabled;
        }
        foreach ($plugins as $plugin => $version) 
        {
            $enabled[$plugin] = $plugin;
        }
        return $enabled;
    }
    
    /**
     * Возможность удаления сабплагинов
     * 
     * {@inheritDoc}
     * @see \core\plugininfo\base::is_uninstall_allowed()
     */
    public function is_uninstall_allowed() 
    {
        return true;
    }
    
    public function get_settings_section_name() {
        return 'otcomponent'.$this->name.'settings';
    }
    
    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE; // In case settings.php wants to refer to them.
        $ADMIN = $adminroot; // May be used in settings.php.
        $plugininfo = $this; // Also can be used inside settings.php.
        
        if (!$this->is_installed_and_upgraded()) {
            return;
        }
        
        if (!$hassiteconfig or !file_exists($this->full_path('settings.php'))) {
            return;
        }
        
        $section = $this->get_settings_section_name();
        $settings = new admin_settingpage($section, $this->displayname, 'moodle/site:config', $this->is_enabled() === false);
        
        $category = new admin_category($this->name, $this->displayname);
        $ADMIN->add('localopentechnology', $category);
        
        include($this->full_path('settings.php')); // This may also set $settings to null.
        
        if ($settings) {
            $ADMIN->add($parentnodename, $settings);
        }
    }
}
