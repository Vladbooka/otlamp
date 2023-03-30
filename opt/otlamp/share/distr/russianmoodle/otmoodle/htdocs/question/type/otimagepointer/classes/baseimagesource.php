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
 * Тип вопроса Объекты на изображении. Базовый класс источника основного изображения.
 *
 * Реализует функции источников основного изображения
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
use qtype_otimagepointer_question;
use question_attempt;
use question_display_options;
use stdClass;
use stored_file;

class baseimagesource implements ibaseimagesource
{
    /**
     * МЕТОДЫ ПОЛУЧЕНИЯ ИНФОРМАЦИИ ОБ ПЛАГИНЕ
     */
    
    /**
     * Получить имя типа вопроса
     *
     * @return string
     */
    public static function get_qtype_plugin_name()
    {
        return 'qtype_otimagepointer';
    }
    
    /**
     * Получить локализованное имя источника
     *
     * @return string
     */
    public static function get_local_name()
    {
        return get_string(self::get_plugin_name().'_name', 'qtype_otimagepointer');
    }
    
    /**
     * Получить имя плагина источника
     *
     * @return string
     */
    public static function get_plugin_name()
    {
        debugging('You should include get_plugin_name method', DEBUG_DEVELOPER);
        
        return 'imagesource_base';
    }
    
    /**
     * Функция обработки пользовательских файлов, принадлежащих плагину.
     *
     * @param stdClass $course - Объект текущего курса
     * @param stdClass $cm - Объект текущего модуля курса
     * @param stdClass $context - Текущий контекст
     * @param string $filearea - Зона, в которой хранится запрашиваемый файл
     * @param array $args - Дополнительные аргументы файла
     * @param bool $forcedownload - Формат отправки файла пользователю(отображение или загрузка)
     * @param array $options - Дополнительные опции подготовки файла
     *
     * @return
     */
    public static function pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = [])
    {
        global $CFG;
    
        // Подключение библиотеки
        require_once($CFG->libdir . '/questionlib.php');
        
        // Передача управления центральному файловому обработчику вопросов
        question_pluginfile(
            $course,
            $context,
            'qtype_otimagepointer',
            $filearea,
            $args,
            $forcedownload,
            $options
        );
    }
    
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
    public function editform_definition($editform, $mform)
    {
        debugging('You should include editform_definition method', DEBUG_DEVELOPER);
    }
    
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
    public function editform_set_data(&$question, $editform, $mform)
    {
        debugging('You should include editform_set_data method', DEBUG_DEVELOPER);
    }
    
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
    public function editform_validation(&$errors, $editform, $mform, $data, $files)
    {
        debugging('You should include editform_validation method', DEBUG_DEVELOPER);
    }
    
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
    public function process_save_question($qtype, $formdata)
    {
        debugging('You should include process_save_question method', DEBUG_DEVELOPER);
        
        return false;
    }
    
    /**
     * Процесс удаления вопроса
     *
     * @param int $questionid - ID удаляемого вопроса
     * @param int $contextid - ID текущего контекста
     *
     * @return void
     */
    public function process_delete_question($questionid, $contextid)
    {
        debugging('You should include process_delete_question method', DEBUG_DEVELOPER);
    }
    
    /**
     * Процесс перемещения файлов вопроса
     *
     * @param int $questionid - ID удаляемого вопроса
     * @param int $oldcontextid - ID текущего контекста
     * @param int $newcontextid - ID нового контекста
     *
     * @return void
     */
    public function process_move_files($questionid, $oldcontextid, $newcontextid)
    {
        debugging('You should include process_move_files method', DEBUG_DEVELOPER);
    }
    
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
    public function question_get_image(question_attempt $qa)
    {
        return null;
    }
    
    /**
     * Проверка наличия базового изображения для указанной попытки прохождения
     *
     * @param question_attempt $qa - Текущая попытка прохождения вопроса
     *
     * @return bool
     */
    public function question_has_image(question_attempt $qa)
    {
        return false;
    }
    
    /**
     * Получить блок источника для отображения в формулировке вопроса
     * 
     * @param question_attempt $qa - Текущая попытка прохождения вопроса
     * @param question_display_options $options - Опции отображения вопроса
     * 
     * @return string - HTML-код источника изображения для отображения в формулировке вопроса
     */
    public function question_formulation(question_attempt $qa,
        question_display_options $options)
    {
        // Блок источника пуст
        return '';
    }
    
    /**
     * Получить токен доступа для функций работы с базовым изображением
     *
     * @param int $qubaid - ID набора вопросов для использования в модуле курса
     * @param int $slot - Номер слота с вопросом
     *
     * @return string|null - Токен для проверки доступа или
     *                      null в случае невозможности построения ключа
     */
    public function get_access_token($qubaid, $slot)
    {
        global $USER;
    
        if ( empty($USER->sesskey) || empty($USER->id) )
        {// Ошибка получения идентификатора сессии
            return null;
        }
    
        // Построение токена
        $token = $qubaid.'_'.$slot.'_'.$USER->id.'_'.$USER->sesskey;
        $token = sha1($token);
    
        return $token;
    }
    
    /**
     * Проверить достоверность токена доступа к станице захвата изображения с веб-камеры
     *
     * @param int $qubaid - ID набора вопросов для использования в модуле курса
     * @param int $slot - Номер слота с вопросом
     * @param string $token - Токен авторизации
     *
     * @return bool - Результат проверки
     */
    public function verify_access_token($qubaid, $slot, $token)
    {
        $gentoken = $this->get_access_token($qubaid, $slot);
    
        return ( $gentoken === $token );
    }
    
    /**
     * Проверить целостность ответа
     *
     * @param qtype_otimagepointer_question $question - Объект текущего вопроса
     * @param array $response - Ответ пользователя
     *
     * @return bool - Результат проверки
     */
    public function is_complete_response(qtype_otimagepointer_question $question, array $response)
    {
        return false;
    }
}