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
 * Хелпер
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otcourselogic\apanel;

require_once($CFG->libdir. '/filelib.php');

use context_course;
use core_course_list_element;
use html_writer;
use moodle_url;
use stdClass;

class helper
{
    /**
     * Получение всех типов экшнов
     *
     * @return string[]
     */
    public static function get_actions_type()
    {
        return [
            'send_message',
            'write_profile_field',
            'enrol_to_course',
            'unenrol_from_course'
        ];
    }
    
    /**
     * Получение всех доступных типов экшнов
     * 
     * @param context_course $context
     *
     * @return string[]
     */
    public static function get_available_actions_type($context)
    {
        static $processed_types = [];
        
        if ( ! empty($processed_types) )
        {
            return $processed_types;
        }
        
        $types = self::get_actions_type();
        foreach ( $types as $type )
        {
            $action = self::get_action_object($type);
            $action->set_context($context);
            if ( $action->is_access() )
            {
                $processed_types[] = $type;
            }
        }
            
        return $processed_types;
    }
    
    /**
     * Получение доступных типов событий
     * 
     * @return string[]
     */
    public static function get_event_types()
    {
        return [
            'otcourselogic_activate',
            'otcourselogic_deactivate'
        ];
    }
    
    /**
     * Получение процессора
     *
     * @return stdClass
     */
    public static function get_processor($processor_id)
    {
        global $DB;
        return $DB->get_record('otcourselogic_processors', ['id' => $processor_id]);
    }
    
    /**
     * Получение обработчиков
     *
     * @return array
     */
    public static function get_processors($otcourselogicid, $only_active = false)
    {
        global $DB;
        if ( $only_active )
        {
            return $DB->get_records('otcourselogic_processors', ['otcourselogicid' => intval($otcourselogicid), 'status' => 1], 'id DESC');
        } else
        {
            return $DB->get_records('otcourselogic_processors', ['otcourselogicid' => intval($otcourselogicid)], 'id DESC');
        }
    }
    
    /**
     * Получение экшена
     *
     * @return stdClass
     */
    public static function get_action($action_id)
    {
        global $DB;
        return $DB->get_record('otcourselogic_actions', ['id' => $action_id]);
    }
    
    /**
     * Получение экшенов
     *
     * @return array
     */
    public static function get_actions($processordid, $only_active = false)
    {
        global $DB;
        if ( $only_active )
        {
            return $DB->get_records('otcourselogic_actions', ['processorid' => $processordid, 'status' => 1], 'sortorder ASC');
        } else
        {
            return $DB->get_records('otcourselogic_actions', ['processorid' => $processordid], 'sortorder ASC');
        }
    }
    
    /**
     * Получение объекта экшна
     * 
     * @param string $type
     * @param bool $include_all_path
     * @param context_course $course_context
     *
     * @return action_base | null
     */
    public static function get_action_object($type, $include_all_path = false, $course_context = null)
    {
        $obj = null;
        
        if ( empty($include_all_path) )
        {
            if ( in_array($type, self::get_actions_type()) )
            {
                $classname = "mod_otcourselogic\apanel\actions\\$type\\" . $type;
                $obj =  new $classname();
            }
        } else
        {
            $obj = new $type();
        }
        
        // Установка контекста
        $obj->set_context($course_context);
        
        return $obj;
    }
    
    /**
     * Получение контактов курса
     * 
     * @param stdClass $course
     *
     * @return string[]
     */
    public static function get_course_contacts($course)
    {
        $contacts = [];
        
        if ( ! empty($course ))
        {
            // Получение курса
            $course = new core_course_list_element($course);
            
            if ( $course->has_course_contacts() )
            {// Есть контакты курса
                $users = $course->get_course_contacts();
                
                // Заполнение информации о контактах курса
                foreach ( $users as $userid => $userinfo )
                {
                    $contacts[$userid] = "{$userinfo['username']}({$userinfo['rolename']})";
                }
                
            }
        }
        
        return $contacts;
    }
    
    /**
     * Получение основных полей профиля
     *
     * @return string[]
     */
    public static function get_user_profile_fields()
    {
        return [
            'city',
            'country',
            'lang',
            'url',
            'idnumber',
            'institution',
            'department',
            'phone1',
            'phone2',
            'address',
            'firstnamephonetic',
            'lastnamephonetic',
            'middlename',
            'alternatename'
        ];
    }
    
    /**
     * Получение основных полей профиля для макроподстановки
     *
     * @return string[]
     */
    public static function get_macrosubstitution_fields()
    {
        return [
            'city',
            'country',
            'lang',
            'url',
            'idnumber',
            'institution',
            'department',
            'phone1',
            'phone2',
            'address',
            'firstnamephonetic',
            'lastnamephonetic',
            'middlename',
            'alternatename',
            'firstname',
            'lastname',
            'email',
            'description',
            'username'
        ];
    }
    
    /**
     * Получение кастомных полей профиля
     *
     * @return string[]
     */
    public static function get_custom_user_profile_fields()
    {
        global $DB;
        
        $customfields = [];
        if ( $proffields = $DB->get_records('user_info_field') )
        {
            foreach ( $proffields as $proffield )
            {
                $customfields[] = 'profile_field_' . $proffield->shortname;
            }
        }
        
        return $customfields;
    }
    
    /**
     * Получение объектов кастомных полей профиля
     *
     * @return string[]
     */
    public static function get_custom_user_profile_fields_with_code()
    {
        global $DB;
        
        $customfields = [];
        if ( $proffields = $DB->get_records('user_info_field') )
        {
            foreach ( $proffields as $proffield )
            {
                $customfields['profile_field_' . $proffield->shortname] = $proffield->name . ' [profile_field_' . $proffield->shortname  . ']';
            }
        }
        
        return $customfields;
    }
    
    /**
     * Получение всех полей профиля
     *
     * @return string[]
     */
    public static function get_all_fields()
    {
        return array_merge(self::get_user_profile_fields(), self::get_custom_user_profile_fields());
    }
    
    /**
     * Опции периодичности
     *
     * @return array
     */
    public static function get_periodic_units()
    {
        return [
                604800 => get_string('weeks'),
                86400 => get_string('days'),
                3600 => get_string('hours'),
                60 => get_string('minutes'),
                1 => get_string('seconds'),
        ];
    }
    
    /**
     * Конвертирование timestamp в периодичную запись (180 = 3 мин.)
     *
     * @param int $seconds
     *
     * @return string
     */
    public static function get_periodic_string($seconds = 0)
    {
        if ( $seconds == 0 )
        {
            return get_string('seconds', 'mod_otcourselogic', $seconds . ' ' . get_string('seconds'));
        }
        foreach ( self::get_periodic_units() as $unit => $notused )
        {
            if (fmod($seconds, $unit) == 0)
            {
                return get_string('seconds', 'mod_otcourselogic', $seconds / $unit . ' ' . $notused);
            }
        }
        
        return (string)$seconds;
    }
    
    /**
     * Конвертирование timestamp в периодичную запись (180 = 3 мин.)
     *
     * @param int $seconds
     *
     * @return string
     */
    public static function get_delay_string($seconds = 0)
    {
        if ( $seconds == 0 )
        {
            return get_string('seconds', 'mod_otcourselogic', $seconds . ' ' . get_string('seconds'));
        }
        foreach ( self::get_periodic_units() as $unit => $notused )
        {
            if (fmod($seconds, $unit) == 0)
            {
                return get_string('delay', 'mod_otcourselogic', $seconds / $unit . ' ' . $notused);
            }
        }
        
        return (string)$seconds;
    }
    
    /**
     * Получение состояния выполнения обработчика для пользователя
     *
     * @param int $processorid
     * @param stdClass $user_state
     * @param boolean $create_if_not_exists
     *
     * @return stdClass | false
     */
    public static function get_processor_user_state($processorid, $user_state, $create_if_not_exists = false)
    {
        global $DB;
        
        $record = $DB->get_record(
                'otcourselogic_processors_s',
                ['processorid' => $processorid, 'userid' => $user_state->userid],
                '*',
                IGNORE_MISSING
                );
        
        if ( empty($record) && ! empty($create_if_not_exists) )
        {
            $record = new stdClass();
            $record->processorid = $processorid;
            $record->userid = $user_state->userid;
            $record->lastexecutiontime = 0;
            $record->passeddelay = 0;
            $record->id = self::save_processor_state($record);
        }
        
        return $record;
    }
    
    /**
     * Получение логов пользователя
     *
     * @param int $otcourselogicid
     * @param int $userid
     *
     * @return []
     */
    public static function get_user_processors_logs($otcourselogicid, $userid)
    {
        global $DB;
        
        $logs = [];
        
        $records = $DB->get_records('otcourselogic_logs', ['otcourselogicid' => $otcourselogicid, 'userid' => $userid], 'timecreated DESC');
        
        if ( ! empty($records) )
        {
            foreach ( $records as $logobj )
            {
                $sm = get_string_manager(true);
                if ( $sm->string_exists($logobj->type, 'mod_otcourselogic') )
                {
                    $type = get_string($logobj->type, 'mod_otcourselogic');
                } else
                {
                    $type = $logobj->type;
                }
                if ( ! empty($logobj->timecreated) )
                {
                    $time = date('d-m-Y H:i:s', $logobj->timecreated);
                } else 
                {
                    $time = '';
                }
                if ( ! empty($logobj->status) )
                {
                    $status = get_string('success', 'mod_otcourselogic');
                } else 
                {
                    $status = get_string('fail', 'mod_otcourselogic');
                }
                
                $logs[] = [
                    $type,
                    $logobj->objectid,
                    $time,
                    $logobj->info,
                    $status
                ];
            }
        }
        
        return $logs;
    }
    
    /**
     * Сохранение обработчика
     *
     * @param stdClass $record
     *
     * @return bool
     */
    public static function save_processor(stdClass $record)
    {
        if ( empty($record->otcourselogicid) )
        {
            return false;
        }
        
        global $DB;
        
        $processed_record = new stdClass();
        $processed_record->otcourselogicid = $record->otcourselogicid;
        $processed_record->status = ( ! empty($record->status) ? 1 : 0 );
        $processed_record->periodic = intval($record->periodic);
        $processed_record->options = base64_encode(serialize($record->options));
        if ( property_exists($record, 'delay') )
        {
            $processed_record->delay = intval($record->delay);
        }
        if ( ! empty($record->id) )
        {
            // Обновление записи
            $processed_record->id = $record->id;
            $processed_record->timemodified = time();
            
            return (bool)$DB->update_record('otcourselogic_processors', $processed_record);
        } else
        {
            $time = time();
            $processed_record->timecreated = $time;
            $processed_record->timemodified = $time;
            
            // Создание записи
            return (bool)$DB->insert_record('otcourselogic_processors', $processed_record);
        }
    }
    
    /**
     * Сохранение экшна
     *
     * @param stdClass $record
     *
     * @return bool
     */
    public static function save_action(stdClass $record)
    {
        if ( empty($record->processorid) )
        {
            return false;
        }
        
        global $DB;
        
        $processed_record = new stdClass();
        $processed_record->processorid = $record->processorid;
        if ( property_exists($record, 'type') )
        {
            $processed_record->type = $record->type;
        }
        if ( property_exists($record, 'options') )
        {
            $processed_record->options = $record->options;
        }
        if ( property_exists($record, 'status') )
        {
            $processed_record->status = ( ! empty($record->status) ? 1 : 0 );
        }
        if ( property_exists($record, 'sortorder') )
        {
            $processed_record->sortorder = intval($record->sortorder);
        }
        
        // Текущее время
        $time = time();
        
        // Обновление даты изменения обработчика
        $processor_record = new stdClass();
        $processor_record->id = $processed_record->processorid;
        self::update_processor_timemodified($processor_record);
        
        if ( ! empty($record->id) )
        {
            // Обновление записи
            $processed_record->id = $record->id;
            $processed_record->timemodified = $time;
            
            return (bool)$DB->update_record('otcourselogic_actions', $processed_record);
        } else
        {
            if ( empty($processed_record->sortorder) )
            {
                $processed_record->sortorder = 0;
            }
            
            // Создание записи
            $processed_record->timecreated = $time;
            $processed_record->timemodified = $time;
               
            // Создание записи
            return (bool)$DB->insert_record('otcourselogic_actions', $processed_record);
        }
    }
    
    /**
     * Обновление состояния обработчика для пользователя
     *
     * @param int $processorid
     * @param stdClass $user_state
     *
     * @return stdClass | false
     */
    public static function refresh_processor_user_state($processorid, $user_state)
    {
        global $DB;
        
        $record = $DB->get_record(
                'otcourselogic_processors_s',
                ['processorid' => $processorid, 'userid' => $user_state->userid],
                '*',
                IGNORE_MISSING
                );
        
        if ( empty($record) )
        {
            $record = new stdClass();
            $record->processorid = $processorid;
            $record->userid = $user_state->userid;
            $record->lastexecutiontime = 0;
        }
        $record->passeddelay = 0;
        
        $record->id = self::save_processor_state($record);
        
        return $record;
    }
    
    /**
     * Обновление последнего исполнения экшенов
     *
     * @param stdClass $record
     *
     * @return bool | int
     */
    public static function save_processor_state(stdClass $record)
    {
        if ( empty($record->processorid) || empty($record->userid) )
        {
            return false;
        }
        
        global $DB;
        
        $processed_record = new stdClass();
        $processed_record->processorid = intval($record->processorid);
        $processed_record->userid = intval($record->userid);
        $processed_record->lastexecutiontime = intval($record->lastexecutiontime);
        if ( property_exists($record, 'passeddelay') )
        {
            if ( ! empty($record->passeddelay) )
            {
                $processed_record->passeddelay = 1;
            } else
            {
                $processed_record->passeddelay = 0;
            }
        }
        
        if ( ! empty($record->id) )
        {
            // Обновление состояния
            $processed_record->id = $record->id;
            return (bool)$DB->update_record('otcourselogic_processors_s', $processed_record);
        } else
        {
            // Создания состояния
            return $DB->insert_record('otcourselogic_processors_s', $processed_record);
        }
    }
    
    /**
     * Сохранение лога процессора
     *
     * @param stdClass $processorrecord
     * @param int $userid
     * @param bool $status
     *
     * @return bool | int
     */
    public static function save_processor_log(stdClass $processorrecord, $userid = 0, $status = true)
    {
        if ( empty($processorrecord->id) || empty($userid) )
        {
            return false;
        }
        
        global $DB;
        
        $processedrecord = new stdClass();
        $processedrecord->otcourselogicid = intval($processorrecord->otcourselogicid);
        $processedrecord->userid = intval($userid);
        $processedrecord->timecreated = time();
        $processedrecord->status = (int)$status;
        $processedrecord->type = 'processor';
        $processedrecord->info = (string)get_string('log_info_execute_processor', 'mod_otcourselogic');
        $processedrecord->objectid = $processorrecord->id;
        
        // Создания записи лога
        return $DB->insert_record('otcourselogic_logs', $processedrecord);
    }
    
    /**
     * Обновление состояния
     *
     * @param stdClass $state_obj
     *
     * @return bool
     */
    public static function update_state(stdClass $state_obj)
    {
        if ( empty($state_obj->id) )
        {
            return false;
        }
        
        global $DB;
        
        $processed_record = new stdClass();
        $processed_record->id = $state_obj->id;
        if ( property_exists($state_obj, 'lastexecutiontime') )
        {
            $processed_record->lastexecutiontime = intval($state_obj->lastexecutiontime);
        }
        if ( property_exists($state_obj, 'passeddelay') )
        {
            if ( ! empty($state_obj->passeddelay) )
            {
                $processed_record->passeddelay = 1;
            } else 
            {
                $processed_record->passeddelay = 0;
            }
        }
        
        return (bool)$DB->update_record('otcourselogic_processors_s', $processed_record);
    }
    
    /**
     * Обновление даты изменения обработчика
     *
     * @param stdClass $record
     *
     * @return bool
     */
    public static function update_processor_timemodified(stdClass $record)
    {
        if ( empty($record->id) )
        {
            return false;
        }
        
        global $DB;
        
        $processed_record = new stdClass();
        $processed_record->id = $record->id;
        $processed_record->timemodified = time();
            
        return (bool)$DB->update_record('otcourselogic_processors', $processed_record);
    }
    
    /**
     * Удаление процессора
     * 
     * @param int $processor_id
     *
     * @return bool
     */
    public static function remove_processor($processor_id)
    {
        global $DB;
        
        $result = true;
        if ( $DB->record_exists('otcourselogic_processors', ['id' => $processor_id]) )
        {
            $result = $DB->delete_records('otcourselogic_processors', ['id' => $processor_id]);
        }
        
        // Удаление экшенов обработчика
        $actions = self::get_actions($processor_id);
        foreach ( $actions as $action )
        {
            $result = $result && self::remove_action($action->id);
        }
        
        return $result;
    }
    
    /**
     * Удаление экшна
     *
     * @param int $action_id
     *
     * @return bool
     */
    public static function remove_action($action_id)
    {
        global $DB;
        
        $result = true;
        if ( $DB->record_exists('otcourselogic_actions', ['id' => $action_id]) )
        {
            $action = $DB->get_record('otcourselogic_actions', ['id' => $action_id]);
            $record_processor = new stdClass();
            $record_processor->id = $action->processorid;
            helper::update_processor_timemodified($record_processor);
            
            $result = $DB->delete_records('otcourselogic_actions', ['id' => $action_id]);
        }
        
        return $result;
    }
    
    /**
     * Смена статуса обработчика
     * 
     * @param int $processor_id
     *
     * @return bool
     */
    public static function change_status_processor($processor_id)
    {
        global $DB;
        
        if ( $record = $DB->get_record('otcourselogic_processors', ['id' => $processor_id]) )
        {
            $update_record = new stdClass();
            $update_record->id = $record->id;
            $update_record->status = !$record->status;
            $update_record->timemodified = time();
            
            return $DB->update_record('otcourselogic_processors', $update_record);
        }
        
        return false;
    }
    
    /**
     * Смена статуса экшна
     *
     * @param int $action_id
     *
     * @return bool
     */
    public static function change_status_action($action_id)
    {
        global $DB;
        
        if ( $record = $DB->get_record('otcourselogic_actions', ['id' => $action_id]) )
        {
            $record_processor = new stdClass();
            $record_processor->id = $record->processorid;
            helper::update_processor_timemodified($record_processor);
            
            $update_record = new stdClass();
            $update_record->id = $record->id;
            $update_record->status = !$record->status;
            $update_record->timemodified = time();
            
            return $DB->update_record('otcourselogic_actions', $update_record);
        }
        
        return false;
    }
    
    /**
     * Замена макроподстановок в строке
     * 
     * @param string $string
     * @param stdClass $instance - запись логики курса
     * @param stdClass $course - запись курса
     * @param stdClass $user - запись пользователя
     * 
     * @return string
     */
    public static function replace_macrosubstitutions($string = '', stdClass $instance = null, $course = null, stdClass $user = null, $pool = [])
    {
        global $CFG;
        
        // Валидация
        if ( empty($instance) || 
                empty($course) ||
                empty($instance) )
        {
            return $string;
        }
        
        // Формирование подстановок в сообщении
        $macrosubstitutionsdata = new stdClass();
        
        // Макроподстановки инстанса логики курса
        $macrosubstitutionsdata->modulename = format_string($instance->name);
        $cm = get_coursemodule_from_instance('otcourselogic', $instance->id, $instance->course);
        if ( ! empty($instance->redirectmessage) )
        {
            $url = new moodle_url('/mod/otcourselogic/message.php', ['id' => $cm->id, ]);
            $macrosubstitutionsdata->modulepage = html_writer::link($url, format_string($instance->name));
        } else
        {
            if ( ! empty($instance->redirecturl) )
            {
                $macrosubstitutionsdata->modulepage = html_writer::link($instance->redirecturl, $instance->redirecturl);
            } else
            {
                $url = new moodle_url('/course/view.php', ['id' => $instance->course]);
                $macrosubstitutionsdata->modulepage = html_writer::link($url, format_string($course->fullname));
            }
        }
    
        // Макроподстановки пользователя
        $macrosubstitutionsdata->studentfullname = fullname($user);
        
        $url = new moodle_url('/user/view.php', ['id' => $user->id, 'course' => $course->id]);
        $macrosubstitutionsdata->studentprofilelink = html_writer::link($url, fullname($user));
        
        $doflibpath = $CFG->dirroot . '/blocks/dof/locallib.php';
        if (file_exists($doflibpath)) {
            require_once($doflibpath);
            global $DOF;
            if( ! is_null($user->id) ) {
                // получил список полей
                $fields = self::get_macrosubstitution_fields();
                $customfields = $DOF->modlib('ama')->user(false)->get_user_custom_fields_list();
                // получим поля без валидации
                $ufd = $DOF->modlib('ama')->user(false)->get_user_fields($user, $fields);
                $upfd = $DOF->modlib('ama')->user(false)->get_user_profilefields($user, $customfields);
                // запишем в макроподстановки
                foreach(array_merge($ufd, $upfd) as $key => $field) {
                    $fieldkeyname = $field->shortname;
                    if (substr($key, 0, 18) == 'user_profilefield_') {
                        $fieldkeyname = 'profile_field_' . $field->shortname;
                    }
                    $macrosubstitutionsdata->$fieldkeyname =$field->displayvalue;
                }
            }
        }
        
        $macrosubstitutionsdata->currentdate = date('d-m-Y H:i:s', time());
        
        // Макроподстановки курса
        $macrosubstitutionsdata->coursefullname = format_string($course->fullname);
        
        $url = new moodle_url('/course/view.php',['id' => $course->id]);
        $macrosubstitutionsdata->courselink = html_writer::link($url, format_string($course->fullname));
        
        // Поля из пула экшнов
        if ( ! empty($pool) )
        {
            foreach ( $pool as $fieldname => $value )
            {
                if ( ! property_exists($macrosubstitutionsdata, $fieldname) )
                {
                    $macrosubstitutionsdata->{$fieldname} = $value;
                }
            }
        }
        
        // Форматирование текста
        $message = format_text($string, FORMAT_MOODLE);
        // Обработка макроподстановок
        if ( ! empty($macrosubstitutionsdata) )
        {
            if( preg_match_all('/{(.+)}/mU', $message, $matches) )
            {
                foreach($matches[1] as $key => $match)
                {
                    if( property_exists($macrosubstitutionsdata, strtolower($match)) )
                    {
                        $message = str_replace($matches[0][$key], $macrosubstitutionsdata->{strtolower($match)}, $message);
                    }
                }
            }
        }
        return $message;
    }
    
    /**
     * Создание обработчиков, если их нет
     * 
     * @param int $instance
     * @param bool $redirect
     *
     * @return void
     */
    public static function create_empty_processors($instance, $redirect = false)
    {
        $processors = self::get_processors($instance);
        
        if ( empty($processors) )
        {
            // Создание обработчика периодичного
            $record_processor = new stdClass();
            $record_processor->otcourselogicid = $instance;
            $record_processor->options = ['on' => 'otcourselogic_activate'];
            $record_processor->status = 0;
            $record_processor->periodic = 86400;
            self::save_processor($record_processor);
            
            // Создание обработчика на деактивацию
            $record_processor = new stdClass();
            $record_processor->otcourselogicid = $instance;
            $record_processor->options = ['on' => 'otcourselogic_deactivate'];
            $record_processor->status = 0;
            $record_processor->periodic = 0;
            self::save_processor($record_processor);
            
            // Создание обработчика на активацию
            $record_processor = new stdClass();
            $record_processor->otcourselogicid = $instance;
            $record_processor->options = ['on' => 'otcourselogic_activate'];
            $record_processor->status = 0;
            $record_processor->periodic = 0;
            self::save_processor($record_processor);
            
            // Проверка необходимости редиректа на страницу управления действиями
            if ( $redirect )
            {
                self::redirect($instance);
            }
        }
    }
    
    /**
     * Если обработчиков нет, редирект на страницу управления действиями
     *
     * @param int $instance
     *
     * @return void
     */
    public static function check_newly_created($instance)
    {
        $processors = self::get_processors($instance);
        
        if ( empty($processors) )
        {
            self::create_empty_processors($instance, true);
        }
    }
    
    /**
     * Проверка состояний
     * 
     * @param int $instanceid
     * @param int $courseid
     *
     * @return void
     */
    public static function check_states($instanceid, $courseid)
    {
        global $DB;
        
        $update = optional_param('reset_states', false, PARAM_BOOL);
        
        if ( ! empty($update) )
        {
            $cm = get_coursemodule_from_instance('otcourselogic', $instanceid, $courseid);
            if ( ! empty($instanceid) && ! empty($cm->visible) )
            {// Идентификатор указан
                
                $instance = $DB->get_record('otcourselogic', ['id' => $instanceid]);
                if ( ! empty($instance->protect) && empty($cm->availability) )
                {
                    // Защита от случайных срабатываний
                    return true;
                }
                
                // Получение контроллера состояний
                $statechecker = otcourselogic_get_state_checker();
                
                // Обновление состояний элемента для пользователей
                $statechecker->check_cm($instanceid);
            }
        }
    }
    
    /**
     * Редирект на нужный инстанс
     * 
     * @param int $instance
     *
     * @return void
     */
    public static function redirect($instance)
    {
        $redirect_url = new moodle_url('/mod/otcourselogic/apanel/index.php', ['instance' => intval($instance)]);
        redirect($redirect_url);
    }
}
