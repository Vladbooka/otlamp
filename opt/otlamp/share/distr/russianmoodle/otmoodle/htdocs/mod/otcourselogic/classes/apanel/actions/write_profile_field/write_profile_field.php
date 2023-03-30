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
 * Экшн записи в поле профиля
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otcourselogic\apanel\actions\write_profile_field;

use MoodleQuickForm;
use context_system;
use html_table;
use html_table_row;
use html_writer;
use moodle_exception;
use stdClass;
use mod_otcourselogic\apanel\action_base;
use mod_otcourselogic\apanel\helper;
use mod_otcourselogic\apanel\forms\process_action_form;
use mod_otcourselogic\apanel\actions\write_profile_field\helpers\writer;

class write_profile_field extends action_base
{
    /**
     * Проверка прав
     *
     * @return bool
     */
    public function is_access()
    {
        $systemcontext = context_system::instance();
        return has_capability('moodle/user:update', $systemcontext);
    }
    
    /**
     * Проверка прав
     *
     * @throws moodle_exception
     *
     * @return void
     */
    public function require_access()
    {
        $systemcontext = context_system::instance();
        require_capability('moodle/user:update', $systemcontext);
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
        
        $sm = get_string_manager(true);
        $options = unserialize(base64_decode($this->record->options));
        $customfields = helper::get_custom_user_profile_fields_with_code();
        
        $table = new html_table();
        
        // Поле профиля
        $row = new html_table_row();
        $row->cells[] = get_string('action_write_profile_field_active_field' , 'mod_otcourselogic');
        
        if ( $sm->string_exists($options->field, 'mod_otcourselogic') )
        {
            $row->cells[] = get_string($options->field, 'mod_otcourselogic') . " [{$options->field}]";
        } elseif ( $sm->string_exists($options->field, '') )
        {
            $row->cells[] = get_string($options->field) . " [{$options->field}]";;
        } elseif ( array_key_exists($options->field, $customfields) )
        {
            $row->cells[] = $customfields[$options->field];
        }
        $table->data[] = $row;
        
        // Шаблон
        $row = new html_table_row();
        $row->cells[] = get_string('action_write_profile_field_active_field_text_short', 'mod_otcourselogic');
        $row->cells[] = $options->text;
        $table->data[] = $row;
        
        // Формируемые поля
        $generatedfields = $this->get_generated_fields();
        if ( ! empty($generatedfields) )
        {
            $row = new html_table_row();
            $row->cells[] = get_string('actions_generated_fields', 'mod_otcourselogic');
            $row->cells[] = $generatedfields;
            $table->data[] = $row;
        }
        
        $html .= html_writer::table($table);
        
        return $html;
    }
    
    /**
     * Получение информации о формируемых полях
     *
     * @return string
     */
    public function get_generated_fields()
    {
        return "{VAR_{$this->record->id}_FIELD}";;
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
        return writer::write_to_field($instance, $userid, $course, $this->record, $pool);
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
                get_string('action_write_profile_field_active', 'mod_otcourselogic'),
                $options
                );
        
        // Список полей     
        $sm = get_string_manager(true);
        $all_fields = helper::get_user_profile_fields();
        $all_fields_processed = [];
        foreach ( $all_fields as $field )
        {
            if ( $sm->string_exists($field, 'mod_otcourselogic') )
            {
                $all_fields_processed[$field] = get_string($field, 'mod_otcourselogic') . ' [' . $field . ']'; 
            } elseif ( $sm->string_exists($field, '') )
            {
                $all_fields_processed[$field] = get_string($field) . ' [' . $field . ']'; 
            } else 
            {
                $all_fields_processed[$field] = $field;
            }
        }
        
        // Обработка кастом полей
        $all_fields_processed = array_merge($all_fields_processed, helper::get_custom_user_profile_fields_with_code());
        $mform->addElement('select', 'field', get_string('action_write_profile_field_active_field_name' , 'mod_otcourselogic'), $all_fields_processed);
        
        // Текстовое поле
        $mform->addElement('text', 'text', get_string('action_write_profile_field_active_field_text', 'mod_otcourselogic'));
        $mform->setType('text', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('text', 'macro_write_profile_field', 'mod_otcourselogic');
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
            // Поле профиля
            if ( isset($options->field) )
            {
                $defaults['field'] = $options->field;
            }
            // Текст записи в поле профиля
            if ( isset($options->text) )
            {
                $defaults['text'] = $options->text;
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
        
        $this->require_access();
        
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
        $options->field = $formdata->field;
        $options->text = htmlentities($formdata->text);
        
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
