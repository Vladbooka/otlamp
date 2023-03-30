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
 * Модуль Логика курса. Класс работы с интерфейсом панели управления
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otcourselogic\apanel;

use context_course;
use moodle_url;
use stdClass;
use html_writer;
use mod_otcourselogic\apanel\forms\process_action_form;
use mod_otcourselogic\apanel\forms\process_card_form;

class renderer
{
    protected $db;
    
    protected $cm = null;
    
    protected $instance = null;
    
    /**
     * Инстант курса
     * 
     * @var stdClass
     */
    protected $course = null;
    
    /**
     * Контекст курса
     * 
     * @var context_course
     */
    protected $course_context = null;

    /**
     * Проверка готовности рендера к работе
     * 
     * @return boolean
     */
    protected function is_set()
    {
        return (! empty($this->course) && ! empty($this->cm) && ! empty($this->instance));
    }
    
    /**
     * Получение строки экшна
     * 
     * @param stdClass $processor
     * @param stdClass $action
     * 
     * @return string
     */
    protected function get_action_row($processor, $action)
    {
        global $OUTPUT;
        
        // Получение объекта экшна
        $action_obj = helper::get_action_object($action->type, true, $this->course_context);
        $action_obj->set_record($action);
        $action_obj->set_context($this->course_context);
        
        $actions_row = '';
        
        // Хинт
        $hint_info = $action_obj->get_hint();
        if ( ! empty($hint_info) )
        {
            $img = html_writer::img($OUTPUT->image_url('help'), '', ['class' => 'iconsmall']);
            $info = html_writer::div($hint_info, 'dropblock', ['style' => 'display: none;']);
            $actions_row .= html_writer::span($img . $info, 'otcourselogic_action_hint');
        }
        
        // Кнопка редактирования
        $url = new moodle_url('/mod/otcourselogic/apanel/process_action.php', ['instance' => $this->instance->id, 'processor' => $processor->id, 'action' => 'update', 'actionid' => $action->id]);
        $img = html_writer::img($OUTPUT->image_url('t/edit'), '', ['class' => 'iconsmall']);
        $actions_row .= html_writer::link($url, $img, ['class' => 'otcourselogic_action_edit']);
        
        // Кнопка смены статуса
        $url = new moodle_url('/mod/otcourselogic/apanel/process_action.php', ['instance' => $this->instance->id, 'processor' => $processor->id, 'action' => 'change_status', 'actionid' => $action->id]);
        $img = html_writer::img(
                ((! empty($action->status)) ? $OUTPUT->image_url('t/hide') : $OUTPUT->image_url('t/show')),
                '',
                ['class' => 'iconsmall']
                );
        $actions_row .= html_writer::link($url, $img, ['class' => 'otcourselogic_action_change_status']);
        
        // Кнопка удаления
        $url = new moodle_url('/mod/otcourselogic/apanel/process_action.php', ['instance' => $this->instance->id, 'processor' => $processor->id, 'action' => 'delete', 'actionid' => $action->id]);
        $img = html_writer::img($OUTPUT->image_url('t/delete'), '', ['class' => 'iconsmall']);
        $actions_row .= html_writer::link($url, $img, ['class' => 'otcourselogic_action_delete']);
        
        $html_action = html_writer::start_div('otcourselogic_card_row otcourselogic_row_sortable', ['data-id' => $action->id]);
        $html_action .= html_writer::div(get_string(str_replace('\\', '_', $action->type), 'mod_otcourselogic'), 'otcourselogic_action_name');
        $html_action .= html_writer::div($actions_row, 'otcourselogic_actions_action');
        $html_action .= html_writer::end_div();
        
        return $html_action;
    }
    
    /**
     * Получение строки для создания экшна
     *
     * @param stdClass $processor
     *
     * @return string
     */
    protected function get_action_row_create($processor)
    {
        $actions_types = helper::get_available_actions_type($this->course_context);
        $select_options = [];
        foreach ( $actions_types as $value )
        {
            $select_options[$value] = get_string('action_' . $value, 'mod_otcourselogic');
        }
        
        $url = new moodle_url('/mod/otcourselogic/apanel/process_action.php', ['instance' => $this->instance->id, 'processor' => $processor->id, 'action' => 'create']);
        $html_action = html_writer::start_div('otcourselogic_card_row');
        $html_action .= html_writer::start_div('otcourselogic_action_create');
        $html_action .= html_writer::start_tag('form', ['action' => $url->out(false)]);
        $html_action .= html_writer::input_hidden_params($url);
        $html_action .= html_writer::select($select_options, 'select_type', '', ['' => get_string('choose_action', 'mod_otcourselogic')], ['name' => 'select_type', 'class' => 'otcourselogic_action_create_select']);
        $html_action .= html_writer::tag('input', '', ['type' => 'submit', 'value' => '+', 'class' => 'btn btn-primary my-0 mx-1']);
        $html_action .= html_writer::end_tag('form');
        $html_action .= html_writer::end_div();
        $html_action .= html_writer::end_div();
        
        return $html_action;
    }
    
    /**
     * Конструктор
     * 
     * @return void
     */
    public function __construct()
    {
        global $DB;
        $this->db = $DB;
    }
    
    /**
     * Устанока данных для работы рендера
     * 
     * @param array $cm_data
     * 
     * @return void
     */
    public function set_data($cm_data)
    {
        $cm_info = $cm_data[1];
        
        // Заполнение данными
        $this->cm = $cm_info->get_course_module_record(true);
        $this->course = $cm_data[0];
        $this->course_context = context_course::instance($this->course->id);
        $this->instance = $this->db->get_record('otcourselogic', ['id' => $this->cm->instance]);
    }
    
    /**
     * Получение блоков с информацией
     * @return string
     */
    public function get_cards()
    {
        if ( ! $this->is_set() )
        {
            return '';
        }
        
        // Создание обработчиков, если их нет
        helper::create_empty_processors($this->cm->instance);
     
        global $OUTPUT;
        
        // Дефолтные параметры
        $html = '';
        $html_processors = '';
        
        $processors = helper::get_processors($this->cm->instance);
        foreach ( $processors as $processor )
        {
            // HTML код обработчика
            $html_actions = '';
            
            $actions = helper::get_actions($processor->id);
            foreach ( $actions as $action )
            {
                // Получение HTML кода строки с экшном
                $html_actions .= $this->get_action_row($processor, $action);
            }
            // Строка для создания экшна
            $html_actions .= $this->get_action_row_create($processor);
            
            // Информация о обработчике
            $processor_info = '';
            
            // Данные обработчика
            $data = unserialize(base64_decode($processor->options));
            if ( $data['on'] == 'otcourselogic_activate' )
            {
                $processor_info .= html_writer::div(get_string('otcourselogic_activate', 'mod_otcourselogic'), 'otcourselogic_regular');
            } else
            {
                $processor_info .= html_writer::div(get_string('otcourselogic_deactivate', 'mod_otcourselogic'), 'otcourselogic_regular');
            }
            if ( ! empty($processor->periodic) )
            {
                $processor_info .= html_writer::div(get_string('mode_periodic', 'mod_otcourselogic'));
                $processor_info .= html_writer::div(helper::get_periodic_string($processor->periodic));
            } else 
            {
                $processor_info .= html_writer::div(get_string('mode_onetime', 'mod_otcourselogic'));
            }
            if ( ! empty($processor->delay) )
            {
                $processor_info .= html_writer::div(helper::get_delay_string($processor->delay));
            }
            if ( empty($processor->status) )
            {
                $processor_info .= html_writer::div(get_string('is_disabled', 'mod_otcourselogic'), 'otcourselogic_status_disabled');
            }
            
            $actions = '';
            // Кнопка редактирования карты
            $url = new moodle_url('/mod/otcourselogic/apanel/process_card.php', ['instance' => $this->instance->id, 'processor' => $processor->id, 'action' => 'update']);
            $img = html_writer::img($OUTPUT->image_url('t/edit'), '', ['class' => 'iconsmall']);
            $actions .= html_writer::link($url, $img, ['class' => 'otcourselogic_action_edit']);
            
            // Кнопка смены статуса
            $url = new moodle_url('/mod/otcourselogic/apanel/process_card.php', ['instance' => $this->instance->id, 'processor' => $processor->id, 'action' => 'change_status']);
            $img = html_writer::img(
                    ((! empty($processor->status)) ? $OUTPUT->image_url('t/hide') : $OUTPUT->image_url('t/show')), 
                    '', 
                    ['class' => 'iconsmall']
            );
            $actions .= html_writer::link($url, $img, ['class' => 'otcourselogic_action_delete']);
            
            // Кнопка удаления карты
            $url = new moodle_url('/mod/otcourselogic/apanel/process_card.php', ['instance' => $this->instance->id, 'processor' => $processor->id, 'action' => 'delete']);
            $img = html_writer::img($OUTPUT->image_url('t/delete'), '', ['class' => 'iconsmall']);
            $actions .= html_writer::link($url, $img, ['class' => 'otcourselogic_action_delete']);
            
            $actions = html_writer::div($actions, 'otcourselogic_actions');
            $timemodifiedheader = html_writer::div(get_string('timemodifiedheader', 'mod_otcourselogic'), 'otcourselogic_timemodifiedheader');
            $timemodified = html_writer::div(date('d-m-Y H:i', $processor->timemodified), 'otcourselogic_timemodified');
            
            // Сбор всего html кода одной карты
            $html_processor = html_writer::start_div('otcourselogic_card', ['data-id' => $processor->id]);
            $html_processor .= html_writer::div($processor_info . $actions . $timemodifiedheader . $timemodified, 'otcourselogic_card_row_first');
            $html_processor .= $html_actions;
            $html_processor .= html_writer::end_div();
            
            $html_processors .= $html_processor;
        }
        
        // Формирование ссылки
        $url = new moodle_url('/mod/otcourselogic/apanel/process_card.php', ['instance' => $this->instance->id, 'action' => 'create']);
        
        // Создание новой карты
        $html_processor = html_writer::start_div('otcourselogic_card otcourselogic_card_create');
        $html_processor .= html_writer::link($url, html_writer::div('+', 'otcourselogic_card_create_action'));
        $html_processor .= html_writer::end_div();
        $html_processors .= $html_processor;
        
        $html .= html_writer::start_div('otcourselogic_card_wrapper');
        $html .= html_writer::div($html_processors);
        $html .= html_writer::end_div();
        
        return $html;
    }
    
    /**
     * Форма работы с обработчиками
     * 
     * @param array $options
     * 
     * @return \mod_otcourselogic\apanel\forms\process_card_form
     */
    public function get_form_process_card($options)
    {
        $customdata = new stdClass();
        $customdata->success_url = $options->success_url;
        $customdata->instance = $this->instance;
        $customdata->action = $options->action;
        $customdata->processorid = $options->processorid;
        
        return new process_card_form($options->current_url , $customdata);
    }
    
    /**
     * Форма работы с экшенами
     *
     * @param array $options
     *
     * @return \mod_otcourselogic\apanel\forms\process_card_form
     */
    public function get_form_process_action($options)
    {
        $customdata = new stdClass();
        $customdata->success_url = $options->success_url;
        $customdata->instance = $this->instance;
        $customdata->action = $options->action;
        $customdata->actionid = $options->actionid;
        $customdata->processorid = $options->processorid;
        $customdata->type = $options->type;
        $customdata->course = $this->course;
        
        return new process_action_form($options->current_url , $customdata);
    }
}
