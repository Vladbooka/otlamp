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
 * Модуль Логика курса. Процесс обновления плагина.
*
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use mod_otcourselogic\info;
use core\notification;

/**
 * Обновление плагина
 */
function xmldb_otcourselogic_upgrade($oldversion = 0)
{
    global $DB;
    $types = info::get_message_types();
    $roles = info::get_roles();
    $objs = $deliveryoptions = [];
    $success = true;
    
    $dbman = $DB->get_manager();
    
    if( $oldversion < 2016100201 )
    {
        $table = new xmldb_table('otcourselogic');
        $field = new xmldb_field('deliveryoptions', XMLDB_TYPE_TEXT, null, null, null, null, null, 'redirecturl');
        if( $dbman->field_exists($table, $field) )
        {
            $objs = $DB->get_records('otcourselogic', [], '', 'id, deliveryoptions');
            if( ! empty($objs) )
            {
                foreach($objs as $obj)
                {
                    $deliveryoptions = unserialize($obj->deliveryoptions);
                    foreach($types as $type)
                    {
                        foreach($roles as $role)
                        {
                            $msgtype = $role . '_fullmessage';
                            if( is_string($deliveryoptions->$type->$msgtype) )
                            {
                                $deliveryoptions->$type->$msgtype = [
                                        'text' => $deliveryoptions->$type->$msgtype,
                                        'format' => FORMAT_HTML
                                ];
                            }
                        }
                    }
                    $deliveryoptions = serialize($deliveryoptions);
                    $dataobject = new stdClass();
                    $dataobject->id = $obj->id;
                    $dataobject->deliveryoptions = $deliveryoptions;
                    $success = $success && $DB->update_record('otcourselogic', $dataobject);
                }
            }
        }
        // otcourselogic savepoint reached
        upgrade_mod_savepoint($success, 2016100201, 'otcourselogic');
    }
    if( $oldversion < 2017032303 )
    {
        $table = new xmldb_table('otcourselogic');
        
        $field = new xmldb_field('activatingdelay', XMLDB_TYPE_INTEGER, 10, null, null, null, 0);
        if( ! $dbman->field_exists($table, $field) )
        {
            $dbman->add_field($table, $field);
        }
        
        $table = new xmldb_table('otcourselogic_state');
        $field = new xmldb_field('preactive', XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
        if( ! $dbman->field_exists($table, $field) )
        {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('preactivelastchange', XMLDB_TYPE_INTEGER, 12, XMLDB_UNSIGNED, null, null, null);
        if( ! $dbman->field_exists($table, $field) )
        {
            $dbman->add_field($table, $field);
        }
        
        $oldrecords = $DB->get_records('otcourselogic_state');
        if ( ! empty($oldrecords) )
        {
            foreach($oldrecords as $oldrecord)
            {
                $recordtoupdate = new stdClass();
                $recordtoupdate->id = $oldrecord->id;
                $recordtoupdate->preactive = $oldrecord->active;
                $recordtoupdate->preactivelastchange = $oldrecord->activelastchange;
                $DB->update_record('otcourselogic_state', $recordtoupdate);
            }
        }
    }
    if( $oldversion < 2017090501 )
    {
        $table = new xmldb_table('otcourselogic');
        
        $field = new xmldb_field('grading', XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, null, null, 0);
        if( ! $dbman->field_exists($table, $field) )
        {
            $dbman->add_field($table, $field);
        }
    }
    
    if( $oldversion < 2017120400 )
    {
        $time = time();
        
        // Данные для миграции
        $records = $DB->get_records('otcourselogic');
        
        $table = new xmldb_table('otcourselogic_processors_s');
        
        $fields = [];
        $fields[] = new xmldb_field('id', XMLDB_TYPE_INTEGER, 20, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $fields[] = new xmldb_field('processorid', XMLDB_TYPE_INTEGER, 20, null, XMLDB_NOTNULL);
        $fields[] = new xmldb_field('userid', XMLDB_TYPE_INTEGER, 20);
        $fields[] = new xmldb_field('lastexecutiontime', XMLDB_TYPE_INTEGER, 20);
        $fields[] = new xmldb_field('passeddelay', XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED);
        
        $keys = [];
        $keys[] = new xmldb_key('id', XMLDB_KEY_PRIMARY, ['id']);
        
        $indexes = [];
        $indexes[] = new xmldb_index('userfind', null, ['processorid, userid']);
        
        $table->setFields($fields);
        $table->setKeys($keys);
        $table->setIndexes($indexes);
        
        if ( ! $dbman->table_exists($table) )
        {
            $dbman->create_table($table);
        }
        
        $table = new xmldb_table('otcourselogic_processors');
        
        // Поля
        $fields = [];
        $fields[] = new xmldb_field('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $fields[] = new xmldb_field('otcourselogicid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $fields[] = new xmldb_field('periodic', XMLDB_TYPE_INTEGER, 20);
        $fields[] = new xmldb_field('delay', XMLDB_TYPE_INTEGER, 20);
        $fields[] = new xmldb_field('options', XMLDB_TYPE_TEXT);
        $fields[] = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $fields[] = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $fields[] = new xmldb_field('status', XMLDB_TYPE_INTEGER, 1);
        
        // Ключи
        $keys = [];
        $keys[] = new xmldb_key('id', XMLDB_KEY_PRIMARY, ['id']);
        
        // Индексы
        $indexes = [];
        $indexes[] = new xmldb_index('otcourselogicid', XMLDB_INDEX_NOTUNIQUE, ['otcourselogicid, status']);
        
        $table->setFields($fields);
        $table->setKeys($keys);
        $table->setIndexes($indexes);
        
        // Создаем таблицу
        if ( ! $dbman->table_exists($table) )
        {
            $dbman->create_table($table);
        }
        
        // Добавим таблицу для экшнов
        $table = new xmldb_table('otcourselogic_actions');
        
        // Поля
        $fields = [];
        $fields[] = new xmldb_field('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $fields[] = new xmldb_field('processorid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $fields[] = new xmldb_field('type', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $fields[] = new xmldb_field('sortorder', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
        $fields[] = new xmldb_field('options', XMLDB_TYPE_TEXT);
        $fields[] = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $fields[] = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $fields[] = new xmldb_field('status', XMLDB_TYPE_INTEGER, 1);
        
        // Ключи
        $keys = [];
        $keys[] = new xmldb_key('id', XMLDB_KEY_PRIMARY, ['id']);
        
        // Индексы
        $indexes = [];
        $indexes[] = new xmldb_index('processorid', XMLDB_INDEX_NOTUNIQUE, ['processorid, status, sortorder']);
        
        $table->setFields($fields);
        $table->setKeys($keys);
        $table->setIndexes($indexes);
        
        // Создаем таблицу
        if ( ! $dbman->table_exists($table) )
        {
            $dbman->create_table($table);
        }
        
        // Добавим таблицу для логов обработчиков
        $table = new xmldb_table('otcourselogic_logs');
        
        // Поля
        $fields = [];
        $fields[] = new xmldb_field('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $fields[] = new xmldb_field('userid', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $fields[] = new xmldb_field('otcourselogicid', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $fields[] = new xmldb_field('objectid', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $fields[] = new xmldb_field('type', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $fields[] = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $fields[] = new xmldb_field('status', XMLDB_TYPE_INTEGER, 1);
        $fields[] = new xmldb_field('info', XMLDB_TYPE_TEXT);
        
        // Ключи
        $keys = [];
        $keys[] = new xmldb_key('id', XMLDB_KEY_PRIMARY, ['id']);
        
        // Индексы
        $indexes = [];
        $indexes[] = new xmldb_index('logsfind', XMLDB_INDEX_NOTUNIQUE, ['otcourselogicid, type, userid']);
        
        $table->setFields($fields);
        $table->setKeys($keys);
        $table->setIndexes($indexes);
        
        // Создаем таблицу
        if ( ! $dbman->table_exists($table) )
        {
            $dbman->create_table($table);
        }
        
        // Внесение изменений в таблицу otcourselogic_state
        $table = new xmldb_table('otcourselogic_state');
        $field = new xmldb_field('status', XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
        if( ! $dbman->field_exists($table, $field) )
        {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('changetime', XMLDB_TYPE_INTEGER, 12, XMLDB_UNSIGNED, null, null, null);
        if( ! $dbman->field_exists($table, $field) )
        {
            $dbman->add_field($table, $field);
        }
        
        // Миграция
        if ( ! empty($records) )
        {
            foreach ( $records as $record )
            {
                $data = unserialize($record->deliveryoptions);
                if ( ! empty($data) )
                {
                    // Учет инверсии логики курса
                    if ( $record->activecond == 'active' )
                    {
                        $inversion = 0;
                        $states = [
                            'otcourselogic_activate' => 'otcourselogic_activate',
                            'otcourselogic_deactivate' => 'otcourselogic_deactivate'
                        ];
                    } else
                    {
                        $inversion = 1;
                        $states = [
                                'otcourselogic_activate' => 'otcourselogic_deactivate',
                                'otcourselogic_deactivate' => 'otcourselogic_activate'
                        ];
                    }
                    
                    // Создание обработчика, срабатывающего на активацию
                    $record_processor_active = new stdClass();
                    $record_processor_active->otcourselogicid = $record->id;
                    $record_processor_active->options = serialize(['on' => $states['otcourselogic_activate']]);
                    $record_processor_active->status = $data->activate->isactive;
                    $record_processor_active->timecreated = $time;
                    $record_processor_active->timemodified = $time;
                    $record_processor_active->periodic = 0;
                    $record_processor_active->delay = $record->activatingdelay;
                    $record_processor_active->id = $DB->insert_record('otcourselogic_processors', $record_processor_active);
                    
                    // Создание трех экшнов отправки уведомлений для студента/учителя/куратора
                    $record_action = new stdClass();
                    $record_action->processorid = $record_processor_active->id;
                    $record_action->type = 'mod_otcourselogic\apanel\actions\send_message\send_message';
                    $record_action->status = 1;
                    $record_action->timecreated = $time;
                    $record_action->timemodified = $time;
                    
                    $options = new stdClass();
                    $options->isactive = 1;
                    $types = ['teacher', 'student', 'curator'];
                    foreach ( $types as $type )
                    {
                        $options->recipient = $type;
                        $options->fullmessage = $data->activate->{$type . '_fullmessage'};
                        $options->shortmessage = $data->activate->{$type . '_shortmessage'};
                        $options->sender = $data->activate->{$type . '_sender'};
                        if ( ! empty($data->activate->{$type . '_sender_teacher'}) )
                        {
                            $options->sender_user = $data->activate->{$type . '_sender_teacher'};
                        }
                        $record_action->options = serialize($options);
                        $DB->insert_record('otcourselogic_actions', $record_action);
                    }
                    
                    // Создание обработчика, срабатывающего на деактивацию
                    $record_processor_deactive = new stdClass();
                    $record_processor_deactive->otcourselogicid = $record->id;
                    $record_processor_deactive->options = serialize(['on' => $states['otcourselogic_deactivate']]);
                    $record_processor_deactive->status = $data->deactivate->isactive;
                    $record_processor_deactive->timecreated = $time;
                    $record_processor_deactive->timemodified = $time;
                    $record_processor_deactive->periodic = 0;
                    $record_processor_deactive->delay = 0;
                    $record_processor_deactive->id = $DB->insert_record('otcourselogic_processors', $record_processor_deactive);
                    
                    // Создание трех экшнов отправки уведомлений для студента/учителя/куратора
                    $record_action = new stdClass();
                    $record_action->processorid = $record_processor_deactive->id;
                    $record_action->type = 'mod_otcourselogic\apanel\actions\send_message\send_message';
                    $record_action->status = 1;
                    $record_action->timecreated = $time;
                    $record_action->timemodified = $time;
                    
                    $options = new stdClass();
                    $options->isactive = 1;
                    foreach ( $types as $type )
                    {
                        $options->recipient = $type;
                        $options->fullmessage = $data->deactivate->{$type . '_fullmessage'};
                        $options->shortmessage = $data->deactivate->{$type . '_shortmessage'};
                        $options->sender = $data->deactivate->{$type . '_sender'};
                        if ( ! empty($data->deactivate->{$type . '_sender_teacher'}) )
                        {
                            $options->sender_user = $data->deactivate->{$type . '_sender_teacher'};
                        }
                        $record_action->options = serialize($options);
                        $DB->insert_record('otcourselogic_actions', $record_action);
                    }
                    
                    $periodic = 8600;
                    $status = 0;
                    if ( ! empty($record->deliveryperiod) )
                    {
                        $status = 1;
                        $periodic = $record->deliveryperiod;
                    }
                    
                    // Создание обработчика, срабатывающего периодические задачи
                    $record_processor_periodic = new stdClass();
                    $record_processor_periodic->otcourselogicid = $record->id;
                    $record_processor_periodic->options = serialize(['on' => $states['otcourselogic_activate']]);
                    $record_processor_periodic->status = $status;
                    $record_processor_periodic->timecreated = $time;
                    $record_processor_periodic->timemodified = $time;
                    $record_processor_periodic->periodic = $periodic;
                    $record_processor_periodic->delay = $record->activatingdelay;
                    $record_processor_periodic->id = $DB->insert_record('otcourselogic_processors', $record_processor_periodic);
                    
                    // Создание трех экшнов отправки уведомлений для студента/учителя/куратора
                    $record_action = new stdClass();
                    $record_action->processorid = $record_processor_periodic->id;
                    $record_action->type = 'mod_otcourselogic\apanel\actions\send_message\send_message';
                    $record_action->status = 1;
                    $record_action->timecreated = $time;
                    $record_action->timemodified = $time;
                    
                    $options = new stdClass();
                    $options->isactive = 1;
                    foreach ( $types as $type )
                    {
                        $options->recipient = $type;
                        $options->fullmessage = $data->periodic->{$type . '_fullmessage'};
                        $options->shortmessage = $data->periodic->{$type . '_shortmessage'};
                        $options->sender = $data->periodic->{$type . '_sender'};
                        if ( ! empty($data->periodic->{$type . '_sender_teacher'}) )
                        {
                            $options->sender_user = $data->periodic->{$type . '_sender_teacher'};
                        }
                        $record_action->options = serialize($options);
                        $DB->insert_record('otcourselogic_actions', $record_action);
                    }
                    
                    $records_states = $DB->get_records('otcourselogic_state', ['instanceid' => $record->id]);
                    if ( ! empty($records_states) )
                    {
                        if ( ! empty($record->activatingdelay) )
                        {
                            foreach ( $records_states as $record_state )
                            {
                                $record_state->changetime = $record_state->preactivelastchange;
                                if ( $record_state->preactive == 1 && $record_state->active == 1 )
                                {
                                    // Отсрочка обработчика на активацию
                                    $new_processor_state_record = new stdClass();
                                    $new_processor_state_record->passeddelay = 1;
                                    $new_processor_state_record->userid = $record_state->userid;
                                    $new_processor_state_record->processorid = $record_processor_active->id;
                                    $DB->insert_record('otcourselogic_processors_s', $new_processor_state_record);
                                    
                                    // Отсрочка периодического обработчика на активацию
                                    $new_processor_state_record->processorid = $record_processor_periodic->id;
                                    $new_processor_state_record->lastexecutiontime = $record_state->lastdeliver;
                                    $DB->insert_record('otcourselogic_processors_s', $new_processor_state_record);
                                } else
                                {
                                    // Периодический
                                    $new_processor_state_record = new stdClass();
                                    $new_processor_state_record->passeddelay = 0;
                                    $new_processor_state_record->userid = $record_state->userid;
                                    $new_processor_state_record->processorid = $record_processor_periodic->id;
                                    $new_processor_state_record->lastexecutiontime = $record_state->lastdeliver;
                                    $DB->insert_record('otcourselogic_processors_s', $new_processor_state_record);
                                }
                                
                                if ( $inversion )
                                {
                                    $record_state->status = (int)!$record_state->preactive;
                                } else
                                {
                                    $record_state->status = $record_state->preactive;
                                }
                                $DB->update_record('otcourselogic_state', $record_state);
                            }
                        } else 
                        {
                            foreach ( $records_states as $record_state )
                            {
                                $record_state->changetime = $record_state->preactivelastchange;
                                
                                $new_processor_state_record = new stdClass();
                                $new_processor_state_record->userid = $record_state->userid;
                                $new_processor_state_record->processorid = $record_processor_periodic->id;
                                $new_processor_state_record->lastexecutiontime = $record_state->lastdeliver;
                                $new_processor_state_record->passeddelay = 0;
                                $DB->insert_record('otcourselogic_processors_s', $new_processor_state_record);
                                
                                if ( $inversion )
                                {
                                    $record_state->status = (int)!$record_state->preactive;
                                } else
                                {
                                    $record_state->status = $record_state->preactive;
                                }
                                $DB->update_record('otcourselogic_state', $record_state);
                            }
                        }
                    }
                }
            }
        }
        
        // Таблица otcourselogic
        $table = new xmldb_table('otcourselogic');
        $field = new xmldb_field('deliveryperiod');
        if ( $dbman->field_exists($table, $field) )
        {
            $dbman->drop_field($table, $field);
        }
        $field = new xmldb_field('deliveryoptions');
        if ( $dbman->field_exists($table, $field) )
        {
            $dbman->drop_field($table, $field);
        }
        $field = new xmldb_field('activecond');
        if ( $dbman->field_exists($table, $field) )
        {
            $dbman->drop_field($table, $field);
        }
        $field = new xmldb_field('activatingdelay');
        if ( $dbman->field_exists($table, $field) )
        {
            $dbman->drop_field($table, $field);
        }
        $field = new xmldb_field('protect', XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, null, null, 0);
        if( ! $dbman->field_exists($table, $field) )
        {
            $dbman->add_field($table, $field);
        }
        
        // Таблица otcourselogic_state
        $table = new xmldb_table('otcourselogic_state');
        $field = new xmldb_field('lastdeliver');
        if( $dbman->field_exists($table, $field) )
        {
            $dbman->drop_field($table, $field);
        }
        $field = new xmldb_index('iactive', XMLDB_INDEX_NOTUNIQUE, ['active']);
        if( $dbman->index_exists($table, $field) )
        {
            $dbman->drop_index($table, $field);
        }
        $field = new xmldb_field('active');
        if( $dbman->field_exists($table, $field) )
        {
            $dbman->drop_field($table, $field);
        }
        $field = new xmldb_field('activelastchange');
        if( $dbman->field_exists($table, $field) )
        {
            $dbman->drop_field($table, $field);
        }
        $field = new xmldb_field('preactive');
        if( $dbman->field_exists($table, $field) )
        {
            $dbman->drop_field($table, $field);
        }
        $field = new xmldb_field('preactivelastchange');
        if( $dbman->field_exists($table, $field) )
        {
            $dbman->drop_field($table, $field);
        }
    }
    
    if ( $oldversion < 2017121900 )
    {
        $table = new xmldb_table('otcourselogic_actions');
        $field = new xmldb_field('sortorder', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
        if( ! $dbman->field_exists($table, $field) )
        {
            $dbman->add_field($table, $field);
        }
        
        $records = $DB->get_records('otcourselogic_actions', []);
        foreach ( $records as $record )
        {
            $newrec = new stdClass();
            $newrec->id = $record->id;
            $newrec->sortorder = $record->id;
            $DB->update_record('otcourselogic_actions', $newrec);
        }
    }

    if ( $oldversion < 2020052900 ) {
        // Исправим и закодируем данные в base64 таблицы otcourselogic_actions
        $records = $DB->get_records('otcourselogic_actions', null, '', 'id, options');
        foreach ( $records as $record ) {
            $haschanges = false;
            set_error_handler(
                function ($errno, $errstr) {
                    switch ($errno) {
                        case E_NOTICE:
                            // Ловим нотисы и выбрасываем свое исключение
                            throw new moodle_exception('custom_e_notice');
                            break; 
                        default:
                            // Передаем обработку стандартному хендлеру php
                            return false;
                            break;
                    }
                }
            );
            try {
                unserialize($record->options);
            } catch (moodle_exception $e) {
                $fixeddata = preg_replace_callback(
                    '/(?<=^|\{|;)s:(\d+):\"(.*?)\";(?=[asbdiO]\:\d|N;|\}|$)/s',
                    function($m){
                        return 's:' . strlen($m[2]) . ':"' . $m[2] . '";';
                    },
                    $record->options
                    );
                if ($fixeddata != $record->options) {
                    // Что-то изменилось
                    $haschanges = true;
                    $fixeddata = base64_encode($fixeddata);
                    core\notification::warning('Corrupted data corrected
                        in table "otcourselogic_actions" id' . $record->id);
                } else {
                    core\notification::error('Serialized data is damaged but could not be fixed
                        in table "otcourselogic_actions" id' . $record->id);
                } 
            }
            restore_error_handler();
            // Запишем данные обратно но уже base64
            $data = new stdClass();
            $data->id = $record->id;
            $data->options = $haschanges ? $fixeddata : base64_encode($record->options);
            $DB->update_record('otcourselogic_actions', $data);
        }
        
        // Закодируем данные в base64 таблицы otcourselogic_processors
        $records = $DB->get_records('otcourselogic_processors', null, '', 'id, options');
        foreach ( $records as $record ) {
            // Запишем данные обратно но уже base64
            $record->options = base64_encode($record->options);
            $DB->update_record('otcourselogic_processors', $record);
        }
    }
    return $success;
}