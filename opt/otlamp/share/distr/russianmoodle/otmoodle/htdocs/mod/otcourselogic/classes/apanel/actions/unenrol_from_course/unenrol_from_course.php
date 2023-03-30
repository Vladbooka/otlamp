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
 * Экшн отписки от курса
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otcourselogic\apanel\actions\unenrol_from_course;

use MoodleQuickForm;
use moodle_exception;
use stdClass;
use mod_otcourselogic\apanel\action_base;
use mod_otcourselogic\apanel\helper;
use mod_otcourselogic\apanel\forms\process_action_form;
use mod_otcourselogic\apanel\actions\unenrol_from_course\helpers\unenrol;

class unenrol_from_course extends action_base
{
    /**
     * Исполнение обработчика
     *
     * @param stdClass $data
     *
     * @return bool
     */
    public function execute_handler($userid, stdClass $instance, stdClass $course, &$pool)
    {
        return unenrol::unenrol_to_course($instance, $userid, $course, $this->record, $pool);
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
                get_string('action_unenrol_from_course_active', 'mod_otcourselogic'),
                $options
                );
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
            $defaults = [];
            $options = unserialize(base64_decode($this->record->options));
            
            // Включение
            if ( isset($this->record->status) )
            {
                $defaults['isactive'] = (int)$this->record->status;
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
