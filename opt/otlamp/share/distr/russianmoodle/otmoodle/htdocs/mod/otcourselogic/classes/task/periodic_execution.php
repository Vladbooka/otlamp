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
 * Исполнение периодических обработчиков и обработчиков с отсрочкой
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otcourselogic\task;

global $CFG;

use stdClass;
use mod_otcourselogic\apanel\helper;

class periodic_execution extends \core\task\scheduled_task
{
    /**
     * Получить имя задачи
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('task_periodic_execution_title', 'mod_otcourselogic');
    }
    
    /**
     * Исполнение задачи
     *
     * @return void
     */
    public function execute()
    {
        global $DB, $CFG;
        $courses = [];
        $instances = $DB->get_records('otcourselogic');
        
        foreach( $instances as $instance )
        {
            $courses[$instance->course][] = $instance->id;
        }
        
        // Текущее время для проверки отсрочки
        $currenttime = time();
        
        foreach( $courses as $courseid => $instancesids )
        {
            $courseobj = get_course($courseid);
            foreach( $instancesids as $instanceid )
            {
                $cm = get_coursemodule_from_instance('otcourselogic', $instanceid, $courseid);
                if ( ! empty($cm->visible) )
                {
                    // Список пользователей текущего инстанса логики курса
                    $current_instance_users_active = $DB->get_records('otcourselogic_state', ['instanceid' => $instanceid, 'status' => 1]);
                    $current_instance_users_notactive = $DB->get_records('otcourselogic_state', ['instanceid' => $instanceid, 'status' => 0]);
                    
                    // Получение данных элемента курса
                    $current_instance = $DB->get_record('otcourselogic',['id' => $instanceid]);
                    if ( ! empty($current_instance->protect) && empty($cm->availability) )
                    {
                        // Защита от случайных срабатываний
                        continue;
                    }
                    
                    // Получение обработчиков инстанса
                    $processors = helper::get_processors($instanceid, true);
                    foreach ( $processors as $processor )
                    {
                        // Обработка периодичных обработчиков
                        if ( ! empty($processor->periodic) )
                        {
                            $options = unserialize(base64_decode($processor->options));
                            if ( $options['on'] == 'otcourselogic_activate' )
                            {
                                $currentusers = $current_instance_users_active;
                            } else
                            {
                                $currentusers = $current_instance_users_notactive;
                            }
                            
                            // Экшны обработчика
                            $actions = helper::get_actions($processor->id, true);
                            if ( ! empty($currentusers) )
                            {
                                foreach ( $currentusers as $userstateobj )
                                {
                                    $processor_user_state = helper::get_processor_user_state($processor->id, $userstateobj, true);
                                    
                                    // Проверка отсрочки, если она есть
                                    if ( ! empty($processor->delay) && ! $processor_user_state->passeddelay )
                                    {
                                        if ( ((($processor->delay + $userstateobj->changetime) <= $currenttime) ||
                                                $processor_user_state->passeddelay) )
                                        {
                                            // Обновление времени выполнения обработчика для пользователя
                                            $new_processor_user_state = new stdClass();
                                            $new_processor_user_state->id = $processor_user_state->id;
                                            $new_processor_user_state->passeddelay = 1;
                                            helper::update_state($new_processor_user_state);
                                        } else
                                        {
                                            // Отсрочка еще не прошла
                                            continue;
                                        }
                                    }
                                    
                                    // Проверка, что срок периода прошел
                                    if( ( (intval($processor_user_state->lastexecutiontime) + intval($processor->periodic) ) < $currenttime) )
                                    {
                                        $actions_pool = [];
                                        $status = true;
                                        if ( ! empty($actions) )
                                        {
                                            foreach ( $actions as $action )
                                            {
                                                $action_object = helper::get_action_object($action->type, true);
                                                $action_object->set_record($action);
                                                $status = $status && $action_object->execute($userstateobj->userid, $current_instance, $courseobj, $actions_pool, $action);
                                            }
                                        }
                                        
                                        // Сохранение записи в лог
                                        helper::save_processor_log($processor, $userstateobj->userid, $status);
                                        
                                        // Обновление времени выполнения обработчика для пользователя
                                        $new_processor_user_state = new stdClass();
                                        $new_processor_user_state->id = $processor_user_state->id;
                                        $new_processor_user_state->lastexecutiontime = time();
                                        
                                        helper::update_state($new_processor_user_state);
                                    }
                                }
                            }
                        } elseif ( ! empty($processor->delay) )
                        {
                            $options = unserialize(base64_decode($processor->options));
                            if ( $options['on'] == 'otcourselogic_activate' )
                            {
                                $currentusers = $current_instance_users_active;
                            } else
                            {
                                $currentusers = $current_instance_users_notactive;
                            }
                            
                            // Экшны обработчика
                            $actions = helper::get_actions($processor->id, true);
                            if ( ! empty($currentusers) )
                            {
                                foreach ( $currentusers as $userstateobj )
                                {
                                    $processor_user_state = helper::get_processor_user_state($processor->id, $userstateobj, true);
                                    if ( $processor_user_state->passeddelay  )
                                    {
                                        continue;
                                    }
                                    
                                    // Проверка отсрочки, если она есть
                                    if ( ($processor->delay + $userstateobj->changetime) <= $currenttime )
                                    {
                                        // Обновление времени выполнения обработчика для пользователя
                                        $new_processor_user_state = new stdClass();
                                        $new_processor_user_state->id = $processor_user_state->id;
                                        $new_processor_user_state->passeddelay = 1;
                                        helper::update_state($new_processor_user_state);
                                    } else
                                    {
                                        // отсрочка не прошла
                                        continue;
                                    }
                                    
                                    // Исполнение
                                    $actions_pool = [];
                                    $status = true;
                                    if ( ! empty($actions) )
                                    {
                                        foreach ( $actions as $action )
                                        {
                                            $action_object = helper::get_action_object($action->type, true);
                                            $action_object->set_record($action);
                                            $status = $status && $action_object->execute($userstateobj->userid, $current_instance, $courseobj, $actions_pool, $action);
                                        }
                                    }
                                    
                                    // Сохранение записи в лог
                                    helper::save_processor_log($processor, $userstateobj->userid, $status);
                                }
                            }
                        } 
                    }
                }
            }
        }
    }
}

