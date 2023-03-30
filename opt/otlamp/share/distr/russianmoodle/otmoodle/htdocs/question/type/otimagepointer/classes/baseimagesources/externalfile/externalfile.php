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
 * Тип вопроса Объекты на изображении. Источник изображения - внешний файл.
 * 
 * Файл изображения, загружаемый студентом при ответе.
 *
 * @package    qtype
 * @subpackage otimagepointer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_otimagepointer\baseimagesources\externalfile;

defined('MOODLE_INTERNAL') || die();

use qtype_otimagepointer\baseimagesource;
use html_writer;
use stdClass;
use context_user;
use question_attempt;
use question_display_options;
use qtype_otimagepointer_question;
use moodle_url;
use popup_action;
use pix_icon;
use question_engine;


class externalfile extends baseimagesource
{
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
        return 'imagesource_externalfile';
    }
    
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
    }
    
    /**
     * Предварительная обработка полей формы сохранения экземпляра вопроса
     *
     * Организация заполнения полей данными
     *
     * @param object $question - Данные вопроса
     * @param qtype_otimagepointer_edit_form $editform - Объект формы
     * @param MoodleQuickForm $editform - Объект конструктора формы
     *
     * @return void
     */
    public function editform_set_data(&$question, $editform, $mform)
    {
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
    }
    
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
    }
    
    /**
     * Получить базовое изображение
     *
     * @param question_attempt $qa - Текущая попытка прохождения вопроса
     *
     * @return null|stored_file - Файл основного изображения
     */
    public function question_get_image(question_attempt $qa)
    {
        // Подключение менеджера файлов
        $fs = get_file_storage();
        // Получение текущего набора вопросов
        $quba = question_engine::load_questions_usage_by_activity($qa->get_usage_id());
        // Получение идентификатора первого шага по попытке
        $firststepid = $qa->get_step(0)->get_id();
        
        // Получение изображения по вопросу
        $files = $fs->get_area_files(
            $quba->get_owning_context()->id,
            'question',
            'response_user_baseimage',
            $firststepid
        );
        
        foreach ($files as $file)
        {
            if ( $file->get_filename() != '.' )
            {
                return $file;
            }
        }
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
        // Подключение менеджера файлов
        $fs = get_file_storage();
        // Получение текущего набора вопросов
        $quba = question_engine::load_questions_usage_by_activity($qa->get_usage_id());
        // Получение идентификатора первого шага по попытке
        $firststepid = $qa->get_step(0)->get_id();
        
        // Получение изображения по вопросу
        $files = $fs->get_area_files(
            $quba->get_owning_context()->id,
            'question',
            'response_user_baseimage',
            $firststepid
        );
        
        // Проверим, что есть файл
        foreach ($files as $file)
        {
            if ( $file->get_filename() != '.' )
            {
                return true;
            }
        }
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
        global $OUTPUT;
        
        // Сформировать URL страницы захвата изображения
        $qubaid = $qa->get_usage_id();
        $slot = $qa->get_slot();
        $access_token = $this->get_access_token($qubaid, $slot);
        
        $url = new moodle_url(
            '/question/type/otimagepointer/classes/baseimagesources/externalfile/filechooser.php',
            [
                'quba' => $qubaid,
                'slot' => $slot,
                'token' => $access_token
            ]
        );
        $name = get_string('imagesource_externalfile_loadpage_link', 'qtype_otimagepointer');
        $html = '';
        if ( ! $options->readonly )
        {
            $html .= $OUTPUT->action_link($url, $name, new popup_action('click', $url, 'popup'),
                [
                    'class' => 'btn button'
                ],
                new pix_icon('e/insert_edit_image', $name, 'moodle', ['width' => 'auto', 'height' => '16px'])
            ); 
        }
        return $html;
    }
    
    /**
     * Проверить целостность ответа
     *
     * Ответ пользователя считается полным, если в вопросе загружено базовое изображение
     * 
     * @param qtype_otimagepointer_question $question - Объект текущего вопроса
     * @param array $response - Ответ пользователя
     *
     * @return bool - Результат проверки
     */
    public function is_complete_response(qtype_otimagepointer_question $question, array $response)
    {
        if ( ! empty($response['answer_baseimage_pathnamehash']) )
        {// Указано базовое изображение
            // Получение менеджера файлов
            $fs = get_file_storage();
            // Получение фонового изображения
            $file = $fs->get_file_by_hash($response['answer_baseimage_pathnamehash']);
            
            if ( $file && $file->is_valid_image() )
            {// Изображение найдено
                // Дополнительная проверка контента файла 
                if ( $file->get_contenthash() === $response['answer_baseimage'] )
                {// Изображение валидно
                    return true;
                }
            }
        }
        return false;
    }
}