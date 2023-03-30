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

namespace local_pprocessing;

use core\event\base as base_event;
use local_pprocessing\stub as stub;

defined('MOODLE_INTERNAL') || die();

/**
 * Класс распределитель входящих событий
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class resolver
{
    /**
     * События, которые для себя мы помечаем особым флагом
     * В остальном это moodle
     *
     * @var array
     */
    protected static $events_sources = [
        'block_dof' => 'dof',
        'mod_otcourselogic' => 'otcourselogic'
    ];
    
    /**
     * Масив определения источников
     *
     * @return array
     */
    protected static function get_events_sources()
    {
        return static::$events_sources;
    }
    
     /**
      * Метод обработки события
      *
      * @param base_event $event
      *
      * @return void
      */
    public static function resolve_event(\core\event\base $event)
    {
        // определение источника
        if ( array_key_exists($event->component, static::get_events_sources()) )
        {
            $source = static::get_events_sources()[$event->component];
        } else
        {
            $source = 'moodle';
        }
        
        $data = $event->get_data();
        // получение активных сценариев, срабатывающих на событие
        $scenarios = stub::get_active_scenarios($source, $event);
        if( ! empty($scenarios) )
        {
            // формирование контейнера
            $container = new container();
            
            $container->write('event', $data, true, true);
        
            if ( ! empty($data['other']) )
            {
                $container->write('data', $data['other'], true, true);
            }
            if ( ! empty($data['relateduserid']) )
            {
                $container->write('userid', $data['relateduserid'], true, true);
                
            } elseif (! empty($data['userid']))
            {
                $container->write('userid', $data['userid'], true, true);
            }
            if ( ! empty($data['courseid']) )
            {
                $container->write('courseid', $data['courseid'], true, true);
            }
            if ( ! empty($data['objecttable']) && ! empty($data['objectid']) )
            {
                $container->write('objecttable', $data['objecttable'], true, true);
                $container->write('objectid', $data['objectid'], true, true);
            }
            if ($event->eventname == '\\local_pprocessing\\event\\iteration_initialized' &&
                !empty($data['other']['iterate_item_var_name']))
            {
                
                logger::write_log(
                    'scenario',
                    'resolver',
                    'debug',
                    [
                        'tail iterate_item_var_name' => $data['other']['iterate_item_var_name'],
                        'iterate_item' => $data['other']['iterate_item'],
                    ],
                    'iterate item data'
                );
                $container->write($data['other']['iterate_item_var_name'], $data['other']['iterate_item'], true, true);
            }
            // запуск обработки сценариев
            executor::go($source, $scenarios, $container);
        }
    }
}

