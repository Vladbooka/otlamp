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
 * Форма работы с картами (обработчиками)
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otcourselogic\apanel\forms;

require_once($CFG->dirroot.'/lib/formslib.php');
use context_course;
use moodleform;
use stdClass;
use mod_otcourselogic\apanel\helper;
use mod_otcourselogic\apanel\action_base;

class process_action_form extends moodleform
{
    /**
     * Запись логики курса
     * 
     * @var stdClass
     */
    protected $instance = null;
    
    /**
     * Редирект при успешном сохранении
     * 
     * @var string
     */
    protected $success_url = null;

    /**
     * Объект экшна
     * 
     * @var action_base
     */
    protected $action = null;
    
    /**
     * Выполняемое действие над обработчиком
     * 
     * @var string
     */
    protected $actionid = null;
    
    /**
     * Идентификатор обработчика
     * 
     * @var int
     */
    protected $processorid = null;
    
    /**
     * Выбранный тип
     * 
     * @var string
     */
    protected $type = null;
    
    /**
     * Инстанс курса
     * 
     * @var stdClass
     */
    protected $course = null;
    
    /**
     * Объявление полей процессора
     *
     * @return void
     */
    protected function definition()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform = &$this->_form;
        
        // Установка параметров
        $this->success_url = $this->_customdata->success_url;
        $this->instance = $this->_customdata->instance;
        $this->processorid = $this->_customdata->processorid;
        $this->actionid = $this->_customdata->action;
        $this->type = $this->_customdata->type;
        $this->course = $this->_customdata->course;
        
        $action_record = null;
        if ( ! empty($this->_customdata->actionid) )
        {
            $action_record = helper::get_action($this->_customdata->actionid);
        }
        
        $course_context = context_course::instance($this->course->id);
        $actions = helper::get_available_actions_type($course_context);
        $select_options = [];
        foreach ( $actions as $value )
        {
            $select_options[$value] = get_string('action_' . $value, 'mod_otcourselogic');
        }
        
        if ( empty($this->type) && empty($action_record) )
        {
            // Добавление заголовка
            $mform->addElement('header', 'main_header', get_string('form_action_main_header', 'mod_otcourselogic'));
            $mform->addElement('select', 'select_type', get_string('form_action_type', 'mod_otcourselogic'), $select_options);
            if ( ! empty($this->type) )
            {
                $mform->setDefault('select_type', $this->type);
            }
            $mform->addElement('submit', 'submit', get_string('form_action_show_action', 'mod_otcourselogic'));
        } else 
        {
            $mform->addElement('hidden', 'select_type', $this->type);
            $mform->setType('select_type', PARAM_RAW_TRIMMED);
        }
        
        if ( ! empty($this->type) || ! empty($action_record) )
        {
            if ( ! empty($action_record->type) )
            {
                $this->action = helper::get_action_object($action_record->type, true);
                $this->action->set_record($action_record);
                $this->action->set_context($course_context);
                $this->action->require_access();
                
                // Обработка экшнов
                if ( ! empty($this->_customdata->action) )
                {
                    switch ( $this->_customdata->action )
                    {
                        case 'delete':
                            $this->action->remove();
                            redirect($this->success_url);
                            break;
                            
                        case 'change_status':
                            $this->action->toggle_status();
                            redirect($this->success_url);
                            break;
                            
                        default:
                            break;
                    }
                }
                
            } else 
            {
                $this->action = helper::get_action_object($this->type);
                $this->action->set_context($course_context);
            }
            
            // Данные для класса экшна
            $data = new stdClass();
            $data->course = $this->course;
            $data->processorid = $this->processorid;
            
            // Установка данных в экшн
            $this->action->set_data($data);
            
            // Добавление заголовка
            $mform->addElement('header', 'main_header_action', get_string('form_action_main_header_action', 'mod_otcourselogic'));
            
            // Передача формы обработчику
            $this->action->definition($mform);
            
            $this->add_action_buttons(true, get_string('save', 'mod_otcourselogic'));
        }
    }
    
    /**
     * Заполнение формы данными
     *
     * @return void
     */
    public function definition_after_data()
    {
        if ( ! empty($this->action) )
        {
            
            $this->action->definition_after_data($this->_form);
        }
    }
    
    /**
     * Валидация формы
     *
     * @return array
     */
    public function validation($data, $files)
    {
        // Массив ошибок
        $errors = [];
        
        if ( ! empty($this->action) )
        {
            $errors = array_merge($errors, $this->action->validation($this->_form, $data, $files));
        }
        
        return $errors;
    }
    
    /**
     * Сохранение данных из формы
     *
     * @return void
     */
    public function process()
    {
        if ( $this->is_cancelled() )
        {
            redirect($this->success_url);
        }
        if ( $this->is_submitted() && 
                $this->is_validated() &&
                $formdata = $this->get_data() )
        {
            if ( ! empty($formdata->submitbutton) )
            {
                $this->action->process($this, $this->_form, $formdata);
            }
        }
    }
    
    /**
     * Возвращение ссылки
     * 
     * @return string
     */
    public function get_success_url()
    {
        return $this->success_url;
    }
}
