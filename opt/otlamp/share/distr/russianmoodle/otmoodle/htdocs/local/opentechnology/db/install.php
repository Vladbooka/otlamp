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
 * Скрипт установки плагина
 *
 * @package    local_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/opentechnology/classes/otserial.php');


function xmldb_local_opentechnology_install() {
    global $DB, $OUTPUT, $CFG;


    $pluginman = core_plugin_manager::instance();
    $pluginfo = $pluginman->get_plugin_info('tool_otserial');
    
    if ( ! empty($pluginfo) )
    {
        if ( $pluginman->can_uninstall_plugin($pluginfo->component) )
        {
            uninstall_plugin($pluginfo->type, $pluginfo->name);
    
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            
            //заново получаем информацию, чтобы убедиться, что из базы плагин удален, осталось только удалить папку с плагином
            $pluginfo = $pluginman->get_plugin_info('tool_otserial');
            if( $pluginman->is_plugin_folder_removable($pluginfo->component) )
            {
                if ( is_null($pluginfo->versiondb) && strpos($pluginfo->rootdir, $CFG->dirroot) == 0)
                {
                    $pluginman->remove_plugin_folder($pluginfo);
                    
                    // после успешного удаления папки плагина и папки плагина (tool_otserial), необходимо удалить информацию об этом плагине в текущем запросе 
                    // инициализация списка плагинов использует синглтон паттерн, в который невозможно вклиниться (инициализация происходит 1 раз за запрос)
                    // из-за этого используется рефлекция для очистки массива списка плагина
                    $component = new ReflectionClass('core_component');
                    $purgecachesmethod = $component->getMethod('fill_all_caches');
                    if ( ! empty($purgecachesmethod) )
                    {
                        $purgecachesmethod->setAccessible(true);
                        $purgecachesmethod->invoke(null);
                        $purgecachesmethod->setAccessible(false);
                    }
                }
            }
        }
    }
    
    $otapi = new \local_opentechnology\otserial();
    $result = $otapi->issue_serial_and_get_data();
    if (isset($result['response']) && !empty($result['message']))
    {
        echo $OUTPUT->notification($result['message'], \core\output\notification::NOTIFY_SUCCESS);
        
    } else if(!isset($result['response']))
    {
        echo $OUTPUT->notification($result['message']??'Unknown error', \core\output\notification::NOTIFY_ERROR);
    }
    return true;
}