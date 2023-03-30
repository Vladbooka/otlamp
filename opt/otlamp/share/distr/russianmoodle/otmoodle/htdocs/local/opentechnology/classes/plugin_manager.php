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

namespace local_opentechnology;

use core_plugin_manager;


class plugin_manager
{
    public static function delete_plugin($component, $callback=null, $params=[])
    {
        global $CFG;
        // удаление плагина
        $pluginman = core_plugin_manager::instance();
        
        if ($pluginman->can_uninstall_plugin($component)) {
            $pluginfo = $pluginman->get_plugin_info($component);
        
            uninstall_plugin($pluginfo->type, $pluginfo->name);
        
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
        }
        // Удаление папки плагина
        if ($pluginman->is_plugin_folder_removable($component)) {
            //заново получаем информацию, чтобы убедиться, что из базы плагин удален, осталось только удалить папку с плагином
            $pluginfo = $pluginman->get_plugin_info($component);
            
            if (!is_null($pluginfo) && is_null($pluginfo->versiondb) && strpos($pluginfo->rootdir, $CFG->dirroot) == 0) {
        
                $pluginman->remove_plugin_folder($pluginfo);
        
                // после успешного удаления папки плагина, необходимо удалить информацию об этом плагине в текущем запросе
                // инициализация списка плагинов использует синглтон паттерн, в который невозможно вклиниться (инициализация происходит 1 раз за запрос)
                // из-за этого используется рефлекция для очистки массива списка плагина
                $component = new \ReflectionClass('core_component');
                $purgecachesmethod = $component->getMethod('fill_all_caches');
                if (!empty($purgecachesmethod)) {
                    $purgecachesmethod->setAccessible(true);
                    $purgecachesmethod->invoke(null);
                    $purgecachesmethod->setAccessible(false);
                }
            }
        }
        // Удаляем записи в любом случае, ничего страшного в этом нет, т.к. плагин все равно подлежит удалению
        // В противном случае, если записи остались, будут возникать ошибки
        if (!is_null($callback) && is_callable($callback) && is_array($params)) {
            call_user_func_array($callback, $params);
        }
    }
    
    public static function delete_all_block_instances($blockname)
    {
        global $DB;
        
        $instances = $DB->get_records('block_instances', ['blockname' => $blockname]);
        foreach($instances as $instance)
        {
            blocks_delete_instance($instance);
        }
    }
}