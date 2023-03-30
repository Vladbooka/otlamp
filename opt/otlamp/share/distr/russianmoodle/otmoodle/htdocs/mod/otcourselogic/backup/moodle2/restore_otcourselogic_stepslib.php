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
 * @package    mod_otcourselogic
 * @subpackage backup-moodle2
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class restore_otcourselogic_activity_structure_step extends restore_activity_structure_step 
{
    /**
     * Определение структуры для восстановления
     * {@inheritDoc}
     * @see restore_structure_step::define_structure()
     */
    protected function define_structure() 
    {
        // Пути к объектам восстановления
        $paths = [];
        
        // Флаг восстановления пользовательских данных
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('otcourselogic', '/activity/otcourselogic');
        $paths[] = new restore_path_element('processor', '/activity/otcourselogic/processors/processor');
        $paths[] = new restore_path_element('action', '/activity/otcourselogic/processors/processor/actions/action');
        if ( $userinfo )
        {
            $paths[] = new restore_path_element('log', '/activity/otcourselogic/logs/log');
            $paths[] = new restore_path_element('state', '/activity/otcourselogic/states/state');
            $paths[] = new restore_path_element('processors_state', '/activity/otcourselogic/processors/processor/processors_states/processors_state');
        }
        
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Восстановление объекта логики курса
     * 
     * @param array $data
     */
    protected function process_otcourselogic($data) 
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $newitemid = $DB->insert_record('otcourselogic', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Восстановление объекта обработчика
     * 
     * @param array $data
     */
    protected function process_processor($data) 
    {
        global $DB;
        
        $data = (object)$data;
        $oldid = $data->id;
        
        $data->otcourselogicid = $this->get_new_parentid('otcourselogic');
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        
        $newitemid = $DB->insert_record('otcourselogic_processors', $data);
        $this->set_mapping('otcourselogic_processors', $oldid, $newitemid);
    }

    /**
     * Восстановление экшна
     * 
     * @param array $data
     */
    protected function process_action($data) 
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->processorid = $this->get_mappingid('otcourselogic_processors', $data->processorid);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        
        // Паттерн формируемых полей
        $pattern = '/{VAR_([1-9]*)_[a-zA-Z1-9]*}/';
        
        // Замена идентификаторов в формируемых полях
        if ( (string)$data->type === 'mod_otcourselogic\apanel\actions\send_message\send_message' )
        {
            $options = @unserialize(base64_decode($data->options));
            if ( ! empty($options->fullmessage['text']) )
            {
                $macrosubstitutions = [];
                if ( preg_match($pattern, $options->fullmessage['text'], $macrosubstitutions) === 1 )
                {
                    $step = false;
                    $changed = false;
                    foreach ( $macrosubstitutions as $index => $macro )
                    {
                        if ( $step )
                        {
                            $step = false;
                            continue;
                        }
                        
                        // Старый идентификатор формируемого поля
                        $id = $macrosubstitutions[$index+1];
                        $newid = $this->get_mappingid('otcourselogic_actions', $id);
                        if ( ! empty($newid) )
                        {
                            $changed = true;
                            $new_var = str_replace("_{$id}_", "_{$newid}_", $macro);
                            $options->fullmessage['text'] = str_replace($macro, $new_var, $options->fullmessage['text']);
                        }
                        $step = true;
                    }
                    
                    if ( $changed )
                    {
                        $data->options = base64_encode(serialize($options));
                    }
                }
            }
            if ( ! empty($options->shortmessage) )
            {
                $macrosubstitutions = [];
                if ( preg_match($pattern, $options->shortmessage, $macrosubstitutions) === 1 )
                {
                    $step = false;
                    $changed = false;
                    foreach ( $macrosubstitutions as $index => $macro )
                    {
                        if ( $step )
                        {
                            $step = false;
                            continue;
                        }
                        
                        // Старый идентификатор формируемого поля
                        $id = $macrosubstitutions[$index+1];
                        $newid = $this->get_mappingid('otcourselogic_actions', $id);
                        if ( ! empty($newid) )
                        {
                            $changed = true;
                            $new_var = str_replace("_{$id}_", "_{$newid}_", $macro);
                            $options->shortmessage = str_replace($macro, $new_var, $options->shortmessage);
                        }
                        $step = true;
                    }
                    
                    if ( $changed )
                    {
                        $data->options = base64_encode(serialize($options));
                    }
                }
            }
        } elseif ( (string)$data->type === 'mod_otcourselogic\apanel\actions\write_profile_field\write_profile_field' )
        {
            $options = @unserialize(base64_decode($data->options));
            if ( ! empty($options->text) )
            {
                $macrosubstitutions = [];
                if ( preg_match($pattern, $options->text, $macrosubstitutions) === 1 )
                {
                    $step = false;
                    $changed = false;
                    foreach ( $macrosubstitutions as $index => $macro )
                    {
                        if ( $step )
                        {
                            $step = false;
                            continue;
                        }
                        
                        // Старый идентификатор формируемого поля
                        $id = $macrosubstitutions[$index+1];
                        $newid = $this->get_mappingid('otcourselogic_actions', $id);
                        if ( ! empty($newid) )
                        {
                            $changed = true;
                            $new_var = str_replace("_{$id}_", "_{$newid}_", $macro);
                            $options->text = str_replace($macro, $new_var, $options->text);
                        }
                        $step = true;
                    }
                    
                    if ( $changed )
                    {
                        $data->options = base64_encode(serialize($options));
                    }
                }
            }
        }

        $newitemid = $DB->insert_record('otcourselogic_actions', $data);
        $this->set_mapping('otcourselogic_actions', $oldid, $newitemid);
    }
    
    /**
     * Восстановление объекта сосстояния пользователя
     *
     * @param array $data
     */
    protected function process_state($data)
    {
        global $DB;
        
        $data = (object)$data;
        $oldid = $data->id;
        
        $data->instanceid = $this->get_new_parentid('otcourselogic');
        $data->userid = $this->get_mappingid('user', $data->userid);;
        $data->changetime = $this->apply_date_offset($data->changetime);
        $data->lastcheck = $this->apply_date_offset($data->lastcheck);
        
        $newitemid = $DB->insert_record('otcourselogic_state', $data);
        $this->set_mapping('otcourselogic_state', $oldid, $newitemid);
    }
    
    /**
     * Восстановление объекта сосстояния пользователя
     *
     * @param array $data
     */
    protected function process_processors_state($data)
    {
        global $DB;
        
        $data = (object)$data;
        $oldid = $data->id;
        
        $data->processorid = $this->get_mappingid('otcourselogic_processors', $data->processorid);
        $data->userid = $this->get_mappingid('user', $data->userid);;
        $data->lastexecutiontime = $this->apply_date_offset($data->lastexecutiontime);
        
        $newitemid = $DB->insert_record('otcourselogic_processors_s', $data);
        $this->set_mapping('otcourselogic_processors_s', $oldid, $newitemid);
    }
    
    /**
     * Восстановление объекта лога
     *
     * @param array $data
     */
    protected function process_log($data)
    {
        global $DB;
        
        $data = (object)$data;
        $oldid = $data->id;

        $objectid = 0;
        if ( $data->type == 'processor' )
        {
            $objectid = $this->get_mappingid('otcourselogic_processors', $data->objectid);
        }
        
        if ( empty($objectid) )
        {
            return;
        } else 
        {
            $data->objectid = $objectid;
        }
        
        $data->otcourselogicid = $this->get_new_parentid('otcourselogic');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->status = (int)$data->status;
        
        $newitemid = $DB->insert_record('otcourselogic_logs', $data);
        $this->set_mapping('otcourselogic_logs', $oldid, $newitemid);
    }
}
