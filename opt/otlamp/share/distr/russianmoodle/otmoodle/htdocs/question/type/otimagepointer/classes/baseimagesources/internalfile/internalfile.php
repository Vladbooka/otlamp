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
 * Тип вопроса Объекты на изображении. Источник изображения - внутренний файл.
 * 
 * Файл изображения, загружаемый учителем при настройке вопроса
 *
 * @package    qtype
 * @subpackage otimagepointer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_otimagepointer\baseimagesources\internalfile;

defined('MOODLE_INTERNAL') || die();

use qtype_otimagepointer\baseimagesource;
use html_writer;
use stdClass;
use context_user;
use question_attempt;
use question_display_options;
use qtype_otimagepointer_question;

class internalfile extends baseimagesource
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
        return 'imagesource_internalfile';
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
        // Заголовок блока настроек
        $mform->addElement(
            'html',
            html_writer::tag('h5', self::get_local_name())
        );
    
        // Загрузчик файла изображения
        $fieldname = self::get_plugin_name();
        $mform->addElement(
            'filepicker',
            $fieldname,
            get_string('editform_'.$fieldname.'_label', 'qtype_otimagepointer'),
            null
        );
        
        $mform->disabledIf($fieldname, 'imagesource', 'neq', self::get_plugin_name());
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
        // Инициализация файлового загрузчика
        $fieldname = self::get_plugin_name();
        $draftitemid = file_get_submitted_draft_itemid($fieldname);
        $itemid = null;
        if ( ! empty($question->id) )
        {
            $itemid = (int)$question->id;
        }
        file_prepare_draft_area(
            $draftitemid,
            $editform->context->id,
            self::get_qtype_plugin_name(),
            $fieldname,
            $itemid,
            [
                'accepted_types' => ['.png', '.jpg', '.jpeg', '.gif'],
                'maxbytes' => 0,
                'maxfiles' => 1,
                'subdirs' => 0,
            ]
        );
        
        // Привязка поля к драфтзоне
        $question->$fieldname = $draftitemid;
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
        // Проверка наличия файла изображения
        $fieldname = self::get_plugin_name();
        if ( ! $editform::file_uploaded($data[$fieldname]) )
        {// Ошибка загрузки файла
            $errors[$fieldname] = get_string('editform_'.$fieldname.'_error_nofile', 'qtype_otimagepointer');
        }
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
        global $DB, $USER;
        
        // Инициализация файлового менеджера
        $fs = get_file_storage();
        // Пользовательский контекст
        $usercontext = context_user::instance($USER->id);
        
        // Получение поля формы с файлом изображения
        $fieldname = self::get_plugin_name();
        
        // Получение списка файлов, загруженных в рамках формы
        $draftfiles = $fs->get_area_files(
            $usercontext->id, 
            'user', 
            'draft', 
            $formdata->$fieldname, 
            'id'
        );
        
        // Обработка загруженных файлов
        if ( count($draftfiles) >= 2 ) 
        {// Данные по пользовательской зоне валидны
            // Очистка зоны источника для сохранения нового изображения
            $fs->delete_area_files(
                $formdata->context->id, 
                self::get_qtype_plugin_name(), 
                self::get_plugin_name(), 
                $formdata->id
            );
            // Сохранение нового изображения в зону источника
            return file_save_draft_area_files(
                $formdata->$fieldname,
                $formdata->context->id,
                self::get_qtype_plugin_name(),
                self::get_plugin_name(),
                $formdata->id,
                [
                    'accepted_types' => ['web_image'],
                    'maxbytes' => 0,
                    'maxfiles' => 1,
                    'subdirs' => 0,
                ]
            );
        }
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
        // Получение менеджера файлов
        $fs = get_file_storage();
        
        // Удаление сохраненного базового изображения
        $fs->delete_area_files(
            $contextid, 
            self::get_qtype_plugin_name(),
            self::get_plugin_name(),
            $questionid
        );
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
        // Получение менеджера файлов
        $fs = get_file_storage();

        // Перемещение базового изображения
        $fs->move_area_files_to_new_context(
            $oldcontextid,
            $newcontextid, 
            self::get_qtype_plugin_name(),
            self::get_plugin_name(), 
            $questionid
        );
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
        // Получение менеджера файлов
        $fs = get_file_storage();
        // Получение текущего экземпляра вопроса
        $question = $qa->get_question();
        
        // Получение базового изображения по вопросу
        $files = $fs->get_area_files(
            $question->contextid,  
            self::get_qtype_plugin_name(),
            self::get_plugin_name(), 
            $question->id, 
            'id'
        );
        
        if ( $files ) 
        {// Файлы найдены
            foreach ( $files as $file ) 
            {
                if ( $file->is_directory() ) 
                {// Файл является директорией
                    continue;
                }
                if ( $file->get_content() == null ) 
                {// Изображение пусто
                    return null;
                }
                return $file;
            }
        }
        
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
        return (bool)$this->question_get_image($qa);
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
            
            // Получение базового изображения по вопросу
            $files = (array)$fs->get_area_files(
                $question->contextid,  
                self::get_qtype_plugin_name(),
                self::get_plugin_name(), 
                $question->id, 
                'id'
            );
            
            // Поиск внутреннего файла
            foreach ( $files as $file )
            {
                if ( $file->is_valid_image() )
                {// Файл является изображением
                    if ( $file->get_contenthash() === $response['answer_baseimage'] && 
                         $file->get_pathnamehash() === $response['answer_baseimage_pathnamehash'] )
                    {// Изображение валидно
                        return true;
                    }
                }
            }
        }
        return false;
    }
}