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
use moodle_exception;
use moodleform;
use stdClass;
use mod_otcourselogic\apanel\helper;

class process_card_form extends moodleform
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
     * Выполняемое действие над обработчиком
     * 
     * @var string
     */
    protected $action = null;
    
    /**
     * Запись обработчика
     * 
     * @var stdClass
     */
    protected $record = null;
    
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
        
        if ( ! empty($this->_customdata->processorid) )
        {
            $this->record = helper::get_processor($this->_customdata->processorid);
            
            // Обработка действий
            if ( ! empty($this->record) && ! empty($this->_customdata->action) )
            {
                switch ( $this->_customdata->action )
                {
                    case 'delete':
                        helper::remove_processor($this->record->id);
                        redirect($this->success_url);
                        break;
                        
                    case 'change_status':
                        helper::change_status_processor($this->record->id);
                        redirect($this->success_url);
                        break;

                    default:
                        break;
                }
            }
        }
        
        // Добавление заголовка
        $mform->addElement('header', 'main_header', get_string('form_processor_main_header', 'mod_otcourselogic'));
        
        // Статус обработчика
        $select_options = [
            0 => get_string('no'),
            1 => get_string('yes')
        ];
        $mform->addElement('select', 'select_status', get_string('form_processor_status', 'mod_otcourselogic'), $select_options);
        
        // Период отсрочки активации
        $mform->addElement('duration', 'activating_delay', get_string('form_processor_delay', 'mod_otcourselogic'));
        $mform->setDefault('activating_delay', 0);
        $mform->addHelpButton('activating_delay', 'form_processor_delay', 'mod_otcourselogic');
        
        // Режим
        $select_options = [
            0 => get_string('mode_onetime_short', 'mod_otcourselogic'),
            1 => get_string('mode_periodic_short', 'mod_otcourselogic')
        ];
        $mform->addElement('select', 'select_mode', get_string('form_processor_type', 'mod_otcourselogic'), $select_options);
        
        // Интервал
        $mform->addElement('duration', 'select_period', get_string('form_processor_periodic', 'mod_otcourselogic'));
        $mform->disabledIf('select_period', 'select_mode', 'noteq', 1);
        
        // Условие срабатывания
        $select_options = [
            'otcourselogic_activate' => get_string('form_processor_otcourselogic_activate', 'mod_otcourselogic'),
            'otcourselogic_deactivate' => get_string('form_processor_otcourselogic_deactivate', 'mod_otcourselogic'),
        ];
        $mform->addElement('select', 'select_activate', get_string('form_processor_depend_activate', 'mod_otcourselogic'), $select_options);
        
        $this->add_action_buttons(true, get_string('save', 'mod_otcourselogic'));
    }
    
    /**
     * Заполнение формы данными
     *
     * @return void
     */
    public function definition_after_data()
    {
        if ( ! empty($this->record) )
        {
            $mform = $this->_form;
            
            $defaults = [];
            $options = unserialize(base64_decode($this->record->options));
            
            if ( array_key_exists('on', $options) )
            {
                $defaults['select_activate'] = $options['on'];
            }
            if ( empty($this->record->periodic) )
            {
                $defaults['select_mode'] = 0;
                $defaults['select_period'] = 0;
            } else 
            {
                $defaults['select_mode'] = 1;
                $defaults['select_period'] = $this->record->periodic;
            }
            if ( isset($this->record->status) )
            {
                $defaults['select_status'] = $this->record->status;
            }
            if ( isset($this->record->delay) )
            {
                $defaults['activating_delay'] = $this->record->delay;
            }
            
            $mform->setDefaults($defaults);
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
            // Формирование объекта для сохранения
            $record = new stdClass();
            $record->otcourselogicid = $this->instance->id;
            $record->status = $formdata->select_status;
            $record->delay = $formdata->activating_delay;
            if ( empty($formdata->select_mode) )
            {
                $record->periodic = 0;
            } else
            {
                $record->periodic = $formdata->select_period;
            }
            $options = [
                'on' => $formdata->select_activate
            ];
            $record->options = $options;
            
            if ( ! empty($this->record->id) )
            {
                // Обновление записи
                $record->id = $this->record->id;
            }
            
            // Сохранение записи
            if ( helper::save_processor($record) )
            {
                // Успех
                redirect($this->success_url);
            } else
            {
                throw new moodle_exception('otcourselogic_error_save_card');
            }
        }
    }
}
