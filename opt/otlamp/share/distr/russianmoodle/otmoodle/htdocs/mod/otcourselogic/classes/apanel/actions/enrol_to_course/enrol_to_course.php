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
 * Экшн записи на курс
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otcourselogic\apanel\actions\enrol_to_course;

use MoodleQuickForm;
use context_course;
use core_course_category;
use dml_exception;
use html_table;
use html_table_row;
use html_writer;
use moodle_exception;
use stdClass;
use mod_otcourselogic\apanel\action_base;
use mod_otcourselogic\apanel\helper;
use mod_otcourselogic\apanel\forms\process_action_form;
use mod_otcourselogic\apanel\actions\enrol_to_course\helpers\enrol;

class enrol_to_course extends action_base
{
    /**
     * Доступные роли
     * 
     * @var array
     */
    protected $roles = [];
    
    /**
     * Проверка прав
     * 
     * @param context_course $context
     * 
     * @return bool
     */
    public function is_access_custom($context)
    {
        return ( has_capability('enrol/manual:unenrol', $context) && has_capability('enrol/manual:enrol', $context) );
    }
    
    /**
     * Проверка прав
     *
     * @param context_course $context
     * 
     * @throws moodle_exception
     *
     * @return void
     */
    public function require_access_custom($context)
    {
        require_capability('enrol/manual:unenrol', $context);
        require_capability('enrol/manual:enrol', $context);
    }
    
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
        
        try 
        {
            $course = get_course($options->course);
        } catch ( dml_exception $e )
        {
            $this->remove();
            helper::redirect(helper::get_processor($this->get_record()->processor)->otcourselogicid);
        }
        
        // Курс
        $row = new html_table_row();
        $row->cells[] = get_string('action_enrol_to_course_course', 'mod_otcourselogic');
        $row->cells[] = $course->fullname;
        $table->data[] = $row;

        // Перезапись на курс
        $row = new html_table_row();
        $row->cells[] = get_string('action_enrol_to_course_reenrol','mod_otcourselogic');
        $row->cells[] = ((! empty($options->reenrol)) ? get_string('yes') : get_string('no'));
        $table->data[] = $row;
        
        // Роль
        $this->roles = get_default_enrol_roles($this->course_context);
        $row = new html_table_row();
        $row->cells[] = get_string('action_enrol_to_course_role','mod_otcourselogic');
        $row->cells[] = $this->roles[$options->role];
        $table->data[] = $row;
        
        // Восстановление оценок
        $row = new html_table_row();
        $row->cells[] = get_string('action_enrol_to_course_recover','mod_otcourselogic');
        $row->cells[] = ((! empty($options->recover)) ? get_string('yes') : get_string('no'));
        $table->data[] = $row;
        
        // Очистить модули
        $row = new html_table_row();
        $row->cells[] = get_string('action_enrol_to_course_clear','mod_otcourselogic');
        $row->cells[] = ((! empty($options->clear)) ? get_string('yes') : get_string('no'));
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
        return enrol::enrol_to_course($instance, $userid, $course, $this->record, $pool);
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
                get_string('action_enrol_to_course_active', 'mod_otcourselogic'),
                $options
                );
        
        // Селект курса
        $courses = get_courses();
        $categories = $coursesselect = [];
        
        // Курс сайта
        unset($courses[SITEID]);
        foreach($courses as $course)
        {
            if ( $this->is_access_custom(context_course::instance($course->id)) )
            {
                $categories[$course->category] = core_course_category::get($course->category)->name;
                $coursesselect[$course->category][$course->id] = $course->fullname;
            }
        }
        // Выбор курса
        $sel =& $mform->addElement('hierselect', 'select_course', get_string('action_enrol_to_course_course', 'mod_otcourselogic'));
        $sel->setOptions([$categories, $coursesselect]);
        // Записываемая роль
        $this->roles = get_default_enrol_roles($this->course_context);
        $mform->addElement('select', 'select_role', get_string('action_enrol_to_course_role','mod_otcourselogic'), $this->roles);
        
        // Перезаписать на курс, если подписка существует
        $mform->addElement('selectyesno', 'select_reenrol', get_string('action_enrol_to_course_reenrol','mod_otcourselogic'));
        $mform->addHelpButton('select_reenrol', 'action_enrol_to_course_reenrol', 'mod_otcourselogic');
        
        // Восстановить оценки
        $mform->addElement('selectyesno', 'select_recover', get_string('action_enrol_to_course_recover','mod_otcourselogic'));
        $mform->addHelpButton('select_recover', 'action_enrol_to_course_recover', 'mod_otcourselogic');
        
        // Очистить попытки
        $mform->addElement('selectyesno', 'select_clear', get_string('action_enrol_to_course_clear','mod_otcourselogic'));
        $mform->addHelpButton('select_clear', 'action_enrol_to_course_clear', 'mod_otcourselogic');
        $mform->disabledIf('select_clear', 'select_recover', 'eq', 1);
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
            // Очистка данных
            if ( isset($options->course) )
            {
                // Селект курса
                $courses = get_courses();
                $cat = $courses[$options->course]->category;
                $defaults['select_course'] = [$cat, $options->course];
            }
            // Перезапись на курс
            if ( isset($options->reenrol) )
            {
                $defaults['select_reenrol'] = $options->reenrol;
            }
            // Очистка попыток
            if ( isset($options->role) )
            {
                $defaults['select_role'] = $options->role;
            }
            // Восстановление оценок
            if ( isset($options->recover) )
            {
                $defaults['select_recover'] = $options->recover;
            }
            // Очистка попыток
            if ( isset($options->clear) )
            {
                $defaults['select_clear'] = $options->clear;
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
        
        if ( empty($data['select_course'][1]) )
        {
            $errors['select_course'] = get_string('action_enrol_to_course_empty_course', 'mod_otcourselogic');
        } else 
        {
            $this->require_access_custom(context_course::instance($data['select_course'][1]));
        }
        
        if ( ! empty($data['select_role']) && ! array_key_exists($data['select_role'], $this->roles) )
        {
            $errors['select_role'] = get_string('action_enrol_to_course_invalid_role', 'mod_otcourselogic');
        }
        
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
        $options->course = intval($formdata->select_course[1]);
        $options->reenrol = intval($formdata->select_reenrol);
        $options->recover = intval($formdata->select_recover);
        if ( ! empty($formdata->select_clear) )
        {
            $options->clear = intval($formdata->select_clear);
        }
        $options->role = intval($formdata->select_role);
        
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
