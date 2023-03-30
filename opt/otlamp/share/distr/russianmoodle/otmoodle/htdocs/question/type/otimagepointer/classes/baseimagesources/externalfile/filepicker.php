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

require_once($CFG->libdir.'/formslib.php');

use moodleform;
use html_writer;
use question_engine;

class filepicker extends moodleform
{
    /**
     * Попытка прохождения вопроса
     * 
     * @var \question_attempt
     */
    protected $qa = null;
    
    /**
     * Название плагина
     *
     * @var int
     */
    private $plugin_name = 'qtype_otimagepointer';
    
    /**
     * Название типа источника изображения
     *
     * @var int
     */
    private $local_name = 'imagesource_externalfile';
    
    /**
     * Обьявление полей формы
     */
    public function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавление свойств
        $this->qa = $this->_customdata->qa;
        
        // Заголовок формы
        $title = get_string('imagesource_externalfile_quiz_title', 'qtype_otimagepointer');
        $mform->addElement(
            'header',
            'header',
            $title
        );
        $mform->setExpanded('header', true, true);
        
        // Загрузчик файла изображения
        $options = [
            'subdirs' => 0, 
            'maxbytes' => null, 
            'areamaxbytes' => null, 
            'maxfiles' => 1,
            'accepted_types' => ['.png', '.jpg', '.jpeg', '.gif']
        ];
        $mform->addElement(
            'filemanager',
            'filepicker_load',
            '',
            null,
            $options
        );
        
        // Кнопка сохранения
        $mform->addElement(
            'submit', 
            'submit', 
            get_string('imagesource_externalfile_save_button', 'qtype_otimagepointer')
        );
    }
    
    public function definition_after_data()
    {
        // Инициализация файлового загрузчика
        if ( ! empty($this->qa) )
        {
            $qaid = $this->qa->get_database_id();
            if ( ! empty($qaid) )
            {
                $draftitemid = file_get_submitted_draft_itemid('filepicker_load');
                
                // Получение текущего набора вопросов
                $quba = question_engine::load_questions_usage_by_activity($this->qa->get_usage_id());
                // Получение идентификатора первого шага по попытке
                $firststepid = $this->qa->get_step(0)->get_id();
    
                // Привязка временной файловой зоны пользователя к полю формы
                $options = [
                    'subdirs' => 0,
                    'maxbytes' => null,
                    'areamaxbytes' => null,
                    'maxfiles' => 1,
                    'accepted_types' => ['.png', '.jpg', '.jpeg', '.gif']
                ];
                
                file_prepare_draft_area(
                    $draftitemid,
                    $quba->get_owning_context()->id,
                    'question',
                    'response_user_baseimage',
                    $firststepid,
                    $options
                );
            }
        }
    }
    
    /**
     * Проверка данных формы
     */
    function validation($data, $files)
    {
        // Массив ошибок
        $errors = parent::validation($data, $files);
        
        // Проверка наличия файла изображения
        $draftareaid = $data['filepicker_load'];
        if ( ! $this->file_uploaded($draftareaid) )
        {// Ошибка загрузки файла
            $errors['filepicker_load'] = get_string('editform_imagesource_externalfile_error_nofile', 'qtype_otimagepointer');
        }
        return $errors;
    }
    
    /**
     * Проверка загрузки файла
     *
     * @param int $draftitemid - ID зоны загрузки
     *
     * @return boolean
     */
    private function file_uploaded($draftitemid)
    {
        // Получение файлов в драфтзоне
        $draftareafiles = file_get_drafarea_files($draftitemid);
        // Поиск требуемых файлов в зоне
        if ( isset($draftareafiles->list) )
        {
            foreach ( $draftareafiles->list as $file )
            {
                if ( isset($file->image_width) && isset($file->image_height) )
                {// Найдено изображение
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Получение загруженного файла
     *
     * @return stored_file - Загруженный файл
     */
    private function get_file()
    {
        // Инициализация файлового менеджера
        $fs = get_file_storage();
        
        // Получение текущего набора вопросов
        $quba = question_engine::load_questions_usage_by_activity($this->qa->get_usage_id());
        // Получение идентификатора первого шага по попытке
        $firststepid = $this->qa->get_step(0)->get_id();
        
        $files = (array)$fs->get_area_files(
            $quba->get_owning_context()->id,
            'question',
            'response_user_baseimage',
            $firststepid
        );
        
        foreach ( $files as $file )
        {
            if ( $file->is_valid_image() )
            {
                return $file;
            }
        }
        return null;
    }
    
    public function process()
    {
        global $PAGE;
        
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        if ( $this->is_submitted() && confirm_sesskey() && $formdata = $this->get_data() )
        {
            if ( ! empty($formdata->filepicker_load) )
            {
                // Инициализация файлового менеджера
                $fs = get_file_storage();
                
                // Получение ID попытки прохождения вопроса
                $qaid = $this->qa->get_database_id();
                
                if ( ! empty($qaid) )
                {
                    // Получение текущего набора вопросов
                    $quba = question_engine::load_questions_usage_by_activity($this->qa->get_usage_id());
                    // Получение идентификатора первого шага по попытке
                    $firststepid = $this->qa->get_step(0)->get_id();
                    
                    // Очистка зоны источника для сохранения нового изображения
                    $fs->delete_area_files(
                        $quba->get_owning_context()->id,
                        'question',
                        'response_user_baseimage',
                        $firststepid
                    );
                    
                    // Перенести файл из временной зоны в постоянное хранилище
                    $options = [
                        'subdirs' => 0,
                        'maxbytes' => null,
                        'areamaxbytes' => null,
                        'maxfiles' => 1,
                        'accepted_types' => ['.png', '.jpg', '.jpeg', '.gif']
                    ];
                    file_save_draft_area_files(
                        $formdata->filepicker_load,
                        $quba->get_owning_context()->id,
                        'question',
                        'response_user_baseimage',
                        $firststepid,
                        $options
                    );
                    
                    // Получение сохраненного файла
                    $baseimage = $this->get_file();
                    
                    // Обновим хэш базового изображения ответа пользователя и отобразим в редакторе загруженное изображение
                    if ( ! empty($baseimage) )
                    {
                        // Постпроцесс
                        $PAGE->requires->yui_module(
                            'moodle-qtype_otimagepointer-imagesource_externalfile-saveprocess',
                            'Y.Moodle.qtype_otimagepointer.imagesource_externalfile.saveprocess.savepostprocess',
                            [$qaid, $baseimage->get_contenthash(), $baseimage->get_pathnamehash()]
                        );
                    }
                }
            }
            
        }
    }
}

