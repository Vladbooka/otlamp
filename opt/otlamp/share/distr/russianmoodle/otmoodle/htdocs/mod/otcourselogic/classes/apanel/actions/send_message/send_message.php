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
 * Экшн отправки сообщения
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otcourselogic\apanel\actions\send_message;

use MoodleQuickForm;
use html_table;
use html_table_row;
use html_writer;
use moodle_exception;
use stdClass;
use mod_otcourselogic\apanel\action_base;
use mod_otcourselogic\apanel\helper;
use mod_otcourselogic\apanel\forms\process_action_form;
use mod_otcourselogic\apanel\actions\send_message\helpers\message_sender;

class send_message extends action_base
{
    /**
     * Получение информации для хинта
     *
     * @return string
     */
    public function get_hint()
    {
        $html = '';
        if ( empty($this->record) )
        {
            return $html;
        }
        
        $options = unserialize(base64_decode($this->record->options));

        $table = new html_table();

        // Получатель
        $row = new html_table_row();
        $row->cells[] = get_string('action_send_message_recipient', 'mod_otcourselogic');
        $row->cells[] = get_string($options->recipient, 'mod_otcourselogic');
        $table->data[] = $row;
        
        // Отправитель
        $row = new html_table_row();
        $row->cells[] = get_string('action_send_message_sender', 'mod_otcourselogic');
        $row->cells[] = get_string($options->sender, 'mod_otcourselogic');
        
        $table->data[] = $row;
        
        $html .= html_writer::table($table);
        
        return $html;
    }
    
    /**
     * Исполнение обработчика
     *
     * @param stdClass $data
     *
     * @return bool
     */
    public function execute_handler($userid, stdClass $instance, stdClass $course, &$pool)
    {
        global $DB;

        // Отправка сообщения
        message_sender::sending_messages($instance, $userid, $course, $this->record, $pool);
        
        return true;
    }
    
    /**
     * Объявление полей процессора
     *
     * @param MoodleQuickForm $mform
     *
     * @return void
     */
    public function definition(MoodleQuickForm $mform)
    {
        // Получение преподавателей
        $coursecontacts = helper::get_course_contacts($this->data['course']);
        
        // Включить условие отправки
        $options = [
            1 => get_string('yes'),
            0 => get_string('no')
        ];
        $mform->addElement(
                'select',
                'isactive',
                get_string('action_send_message_active', 'mod_otcourselogic'),
                $options
                );
        
        // Кому отправляем сообщение
        $recepients = [
            'teacher' => get_string('teacher', 'mod_otcourselogic'),
            'curator' => get_string('curator', 'mod_otcourselogic'),
            'student' => get_string('student', 'mod_otcourselogic')
        ];
        $mform->addElement('select', 'recipient', get_string('action_send_message_recipient', 'mod_otcourselogic'), $recepients);
        
        // Полное сообщение
        $mform->addElement(
                'editor',
                'fullmessage',
                get_string('action_send_message_fullmessage', 'mod_otcourselogic')
                );
        $mform->setType('fullmessage', PARAM_RAW_TRIMMED);
        $mform->setDefault('fullmessage', '');
        $mform->addHelpButton('fullmessage', 'macro_send_message', 'mod_otcourselogic');
        
        // Краткое сообщение
        $mform->addElement(
                'textarea',
                'shortmessage',
                get_string('action_send_message_shortmessage', 'mod_otcourselogic')
                );
        $mform->setType('shortmessage', PARAM_RAW_TRIMMED);
        $mform->setDefault('shortmessage', '');
        $mform->addHelpButton('shortmessage', 'macro_send_message', 'mod_otcourselogic');
        
        // Отправитель
        $options = [
            'sender' => get_string('student', 'mod_otcourselogic'),
            'teacher' => get_string('teacher', 'mod_otcourselogic'),
            'admin' => get_string('admin', 'mod_otcourselogic')
        ];
        $mform->addElement(
                'select',
                'sender',
                get_string('action_send_message_sender', 'mod_otcourselogic'),
                $options
                );
        $mform->setDefault('sender', 'admin');
        
        // Выбор пользователя
        $mform->addElement(
                'select',
                'sender_user',
                get_string('action_send_message_sender_user', 'mod_otcourselogic'),
                $coursecontacts
                );
        $mform->disabledIf('sender_user', 'sender', 'neq', 'teacher');
    }
    
    /**
     * Заполнение формы данными
     *
     * @param MoodleQuickForm $mform
     *
     * @return void
     */
    public function definition_after_data(MoodleQuickForm $mform)
    {
        // Опции рассылки
        if ( ! empty($this->record) )
        {
            $options = unserialize(base64_decode($this->record->options));
            
            // Включение
            if ( isset($this->record->status) )
            {
                $defaults['isactive'] = (int)$this->record->status;
            }
            // Получатель
            if ( isset($options->recipient) )
            {
                $defaults['recipient'] = $options->recipient;
            }
            // Полное сообщение
            if ( isset($options->fullmessage) )
            {
                $defaults['fullmessage'] = $options->fullmessage;
            }
            // Краткое сообщение
            if ( isset($options->shortmessage) )
            {
                $defaults['shortmessage'] = $options->shortmessage;
            }
            // Отправитель
            if ( isset($options->sender) )
            {
                $defaults['sender'] = $options->sender;
            }
            // Пользователь
            if ( isset($options->sender_user) )
            {
                $defaults['sender_user'] = $options->sender_user;
            }
            
            $mform->setDefaults($defaults);
        }
    }
    
    /**
     * Валидация формы
     *
     * @param MoodleQuickForm $mform
     * @param array $data
     * @param array $files
     *
     * @return array
     */
    public function validation(MoodleQuickForm $mform, $data, $files)
    {
        // Массив ошибок
        $errors = [];
        
        return $errors;
    }
    
    /**
     * Сохранение данных из формы
     *
     * @param MoodleQuickForm $mform
     * @param stdClass $formdata
     *
     * @return void
     */
    public function process(process_action_form $form, MoodleQuickForm $mform, $formdata)
    {
        // Объект сохранения экшна
        $save_record = new stdClass();
        
        // Опции отправки уведомлений
        $options = new stdClass();
        
        $options->isactive = (bool)$formdata->isactive;
        
        $options->recipient  = $formdata->recipient;
        $options->fullmessage = $formdata->fullmessage;
        $options->shortmessage  = $formdata->shortmessage;
        $options->sender = $formdata->sender;
        if ( $options->sender == 'teacher' && isset($formdata->sender_user) )
        {
            $options->sender_user = $formdata->sender_user;
        }
        
        $save_record->options = base64_encode(serialize($options));
        $save_record->status = (bool)$formdata->isactive;
        $save_record->type = $this->get_code();
        $save_record->processorid = $this->processorid;
        
        if ( ! empty($this->record) )
        {
            $save_record->id = $this->record->id;
        }

        // Сохранение записи
        if ( helper::save_action($save_record) )
        {
            // Успех
            redirect($form->get_success_url());
        } else
        {
            throw new moodle_exception('otcourselogic_error_save_action');
        }
    }
}
