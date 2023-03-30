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
 * Тип вопроса Объекты на изображении. Интерфейс источников основного изображения.
 * 
 * @package    qtype
 * @subpackage otimagepointer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_otimagepointer;

defined('MOODLE_INTERNAL') || die();

use MoodleQuickForm;
use qtype_otimagepointer;
use qtype_otimagepointer_edit_form;
use question_attempt;
use question_display_options;
use qtype_otimagepointer_question;
use stored_file;

interface ibaseimagesource
{
    /**
     * МЕТОДЫ ПОЛУЧЕНИЯ ИНФОРМАЦИИ ОБ ПЛАГИНЕ
     */
    
    /**
     * Получить локализованное имя источника
     *
     * @return string
     */
    public static function get_local_name();
    
    /**
     * Получить имя плагина источника
     *
     * @return string
     */
    public static function get_plugin_name();
    
    /**
     * МЕТОДЫ РАБОТЫ С ФОРМОЙ КОНФИГУРАЦИИ ВОПРОСА
     */
    
    /**
     * Добавление полей в форму конфигурации экземпляра вопроса
     *
     * @param qtype_otimagepointer_edit_form $editform - Объект формы
     * @param MoodleQuickForm $editform - Объект конструктора формы
     *
     * @return void
     */
    public function editform_definition($editform, $mform);
    
    /**
     * Предварительная обработка полей формы сохранения экземпляра вопроса
     *
     * Организация заполнения полей данными
     *
     * @param object $question - Данные вопроса для заполнения полей формы
     * @param qtype_otimagepointer_edit_form $editform - Объект формы
     * @param MoodleQuickForm $editform - Объект конструктора формы
     *
     * @return void
     */
    public function editform_set_data(&$question, $editform, $mform);
    
    /**
     * Валидация полей формы сохранения экземпляра вопроса
     *
     * @param array $errors - Массив ошибок валидации
     * @param qtype_otimagepointer_edit_form $editform - Объект формы
     * @param MoodleQuickForm $editform - Объект конструктора формы
     * @param array $data - Данные формы сохранения
     * @param array $data - Загруженные файлы формы сохранения
     *
     * @return void
     */
    public function editform_validation(&$errors, $editform, $mform, $data, $files);
    
    /**
     * МЕТОДЫ ПОДДЕРЖКИ БАЗОВЫХ ФУНКЦИЙ ВОПРОСА
     */
    
    /**
     * Процесс сохранения вопроса
     *
     * @param qtype_otimagepointer $qtype - Экземпляр типа вопроса
     * @param array $data - Загруженные файлы формы сохранения
     *
     * @return bool - Результат сохранения
     */
    public function process_save_question($qtype, $formdata);
    
    /**
     * Процесс удаления вопроса
     *
     * @param int $questionid - ID удаляемого вопроса
     * @param int $contextid - ID текущего контекста
     *
     * @return void
     */
    public function process_delete_question($questionid, $contextid);
    
    /**
     * Процесс перемещения файлов вопроса
     *
     * @param int $questionid - ID удаляемого вопроса
     * @param int $oldcontextid - ID текущего контекста
     * @param int $newcontextid - ID нового контекста
     *
     * @return void
     */
    public function process_move_files($questionid, $oldcontextid, $newcontextid);
    
    /**
     * МЕТОДЫ ПОДДЕРЖКИ ОТОБРАЖЕНИЯ ВОПРОСА И ФУНКЦИЙ РАБОТЫ СО СТУДЕНТАМИ
     */
    
    /**
     * Получить базовое изображение 
     * 
     * @param question_attempt $qa - Текущая попытка прохождения вопроса
     * 
     * @return null|stored_file - Файл основного изображения
     */
    public function question_get_image(question_attempt $qa);
    
    /**
     * Получить блок источника для отображения в формулировке вопроса
     * 
     * @param question_attempt $qa - Текущая попытка прохождения вопроса
     * @param question_display_options $options - Опции отображения вопроса
     * 
     * @return string - HTML-код источника изображения для отображения в формулировке вопроса
     */
    public function question_formulation(question_attempt $qa,
        question_display_options $options);
    
    /**
     * Проверить целостность ответа
     *
     * @param qtype_otimagepointer_question $question - Объект текущего вопроса
     * @param array $response - Ответ пользователя
     *
     * @return bool - Результат проверки
     */
    public function is_complete_response(qtype_otimagepointer_question $question, array $response);
}