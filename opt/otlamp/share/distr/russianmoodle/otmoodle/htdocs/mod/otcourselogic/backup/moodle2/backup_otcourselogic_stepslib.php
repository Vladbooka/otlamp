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

class backup_otcourselogic_activity_structure_step extends backup_activity_structure_step 
{
    /**
     * {@inheritDoc}
     * @see backup_structure_step::define_structure()
     */
    protected function define_structure() 
    {
        // Флаг настройки бэкапа с пользовательскими данными
        $userinfo = $this->get_setting_value('userinfo');
        
        // Define each element separated
        $otcourselogic = new backup_nested_element('otcourselogic', ['id'],
                [
                    'name',
                    'checkperiod',
                    'catchstatechange',
                    'catchcourseviewed',
                    'studentshide',
                    'redirectmessage',
                    'redirecturl',
                    'timecreated',
                    'timemodified',
                    'completionstate',
                    'grading',
                    'protect'
                ]);
        
        // Обработчики
        $processors = new backup_nested_element('processors');
        $processor = new backup_nested_element('processor', ['id'],
                [
                    'otcourselogicid',
                    'periodic',
                    'delay',
                    'options',
                    'timecreated',
                    'timemodified',
                    'status'
                ]);

        // Экшны
        $actions = new backup_nested_element('actions');
        $action = new backup_nested_element('action', ['id'],
                [
                    'processorid',
                    'type',
                    'sortorder',
                    'options',
                    'timecreated',
                    'timemodified',
                    'status'
                ]);

        // Установка дерева зависимостей
        $otcourselogic->add_child($processors);
        $processors->add_child($processor);
        $processor->add_child($actions);
        $actions->add_child($action);
        
        if ( $userinfo )
        {
            // Состояния пользователей
            $states = new backup_nested_element('states');
            $state = new backup_nested_element('state', ['id'],
                    [
                        'instanceid',
                        'userid',
                        'status',
                        'changetime',
                        'lastcheck'
                    ]);
            
            $otcourselogic->add_child($states);
            $states->add_child($state);
            $state->set_source_table('otcourselogic_state', ['instanceid' => backup::VAR_PARENTID], 'id ASC');
            $state->annotate_ids('user', 'userid');
            
            // Состояния обработчиков для пользователей
            $processors_states = new backup_nested_element('processors_states');
            $processors_state = new backup_nested_element('processors_state', ['id'],
                    [
                        'processorid',
                        'userid',
                        'lastexecutiontime',
                        'passeddelay'
                    ]);
            
            $processor->add_child($processors_states);
            $processors_states->add_child($processors_state);
            $processors_state->set_source_table('otcourselogic_processors_s', ['processorid' => backup::VAR_PARENTID], 'id ASC');
            $processors_state->annotate_ids('user', 'userid');
            
            // Логи
            $logs = new backup_nested_element('logs');
            $log = new backup_nested_element('log', ['id'],
                    [
                        'userid',
                        'otcourselogicid',
                        'objectid',
                        'status',
                        'type',
                        'timecreated',
                        'info'
                    ]);
            
            $otcourselogic->add_child($logs);
            $logs->add_child($log);
            $log->set_source_table('otcourselogic_logs', ['otcourselogicid' => backup::VAR_PARENTID], 'id ASC');
            $log->annotate_ids('user', 'userid');
        }

        // Источники записей
        $otcourselogic->set_source_table('otcourselogic', ['id' => backup::VAR_ACTIVITYID]);
        $processor->set_source_table('otcourselogic_processors', ['otcourselogicid' => backup::VAR_PARENTID], 'id ASC');
        $action->set_source_table('otcourselogic_actions', ['processorid' => backup::VAR_PARENTID], 'sortorder ASC');
        
        return $this->prepare_activity_structure($otcourselogic);
    }
}

