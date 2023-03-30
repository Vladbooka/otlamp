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
 * Базовый класс экшена
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otcourselogic\apanel;

use MoodleQuickForm;
use context;
use context_course;
use stdClass;
use moodle_exception;
use mod_otcourselogic\apanel\forms\process_action_form;
use mod_otcourselogic\event\action_execution_ended as axe;

abstract class action_base
{
    /**
     * Установка записи
     * 
     * @var stdClass
     */
    protected $record = null;
    
    /**
     * Данные модуля
     * 
     * @var stdClass
     */
    protected $data = [];
    
    /**
     * Идентификатор обработчика
     * 
     * @var int
     */
    protected $processorid = null;
    
    /**
     * Редирект при успешном сохранении
     *
     * @var string
     */
    protected $success_url = null;
    
    /**
     * Контекст курса
     * 
     * @var context_course
     */
    protected $course_context = null;

    /**
     * Код экшена
     * 
     * @return string
     */
    public function get_code()
    {
        return static::class;
    }
    
    /**
     * Запись экшна
     *
     * @return string
     */
    public function get_record()
    {
        return $this->record;
    }
    
    /**
     * Получение информации для хинта
     *
     * @return string
     */
    public function get_hint()
    {
        return '';
    }
    
    /**
     * Получение информации о формируемых полях
     *
     * @return string
     */
    public function get_generated_fields()
    {
        return '';
    }
    
    /**
     * Исполнение экшна
     * 
     * @param int $userid
     * @param stdClass $instance
     * @param stdClass $course
     * @param array $pool
     * @param object $action
     * 
     * @return boolean
     */
    public function execute($userid, stdClass $instance, stdClass $course, &$pool, $action)
    {
        if ($this->execute_handler($userid, $instance, $course, $pool)) {
            // Событие отработки хендлера
            $context = \context_course::instance($course->id);
            $event = axe::create_event($context, $instance, $userid, $action);
            $event->trigger();
            return true;
        }
        return false;
    }
    
    /**
     * Проверка прав
     *
     * @return bool
     */
    public function is_access()
    {
        return true;
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
    }
    
    /**
     * Удаление экшна
     *
     * @param stdClass $data
     *
     * @return bool
     */
    public function remove()
    {
        if ( ! empty($this->record) )
        {
            return helper::remove_action($this->record->id);
        }
        
        return true;
    }
    
    /**
     * Установка записи экшна
     * 
     * @param stdClass $record
     */
    public function set_record(stdClass $record)
    {
        $this->record = $record;
    }
    
    /**
     * Установка контекст
     *
     * @param context $context
     */
    public function set_context($context)
    {
        $this->course_context = $context;
    }
    
    /**
     * Смена статуса
     *
     * @return bool
     */
    public function toggle_status()
    {
        if ( ! empty($this->record) )
        {
            return helper::change_status_action($this->record->id);
        }
        
        return false;
    }
    
    /**
     * Установка данных
     * 
     * @param stdClass $data
     * 
     * @return void
     */
    public function set_data($data)
    {
        // Валидация
        if ( empty($data->processorid) )
        {
            throw new moodle_exception('invalid_processor');
        }
        
        if ( ! empty($data->course) )
        {
            $this->data['course'] = $data->course;
        }
        
        $this->processorid = $data->processorid;
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
    }
}
