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
 * Тип вопроса Объекты на изображении. Класс формы сохранения экземпляра вопроса..
 *
 * @package    qtype
 * @subpackage otimagepointer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Форма сохранения экземпляра вопроса
 * 
 */
class qtype_otimagepointer_edit_form extends question_edit_form 
{  
    
    public function qtype()
    {
        return 'otimagepointer';
    }
    
    /**
     * Объявление дополнительных полей формы
     */
    protected function definition_inner($mform) 
    {
        // Получение типа текущего вопроса
        $qtype = question_bank::get_qtype('otimagepointer');

        // Заголовок блока настройки источника изображения
        $mform->addElement(
            'header', 
            'imagesource_header', 
            get_string('editform_imagesource_header', 'qtype_otimagepointer')
        );
        $mform->setExpanded('imagesource_header');
        
        // Формирование списка источников изображения
        $select = ['' => get_string('editform_imagesource_select_select', 'qtype_otimagepointer')];
        $imagesources = (array)$qtype->imagesources_get_list();
        
        foreach ( $imagesources as $code => $imagesource )
        {
            $select[$code] = $imagesource::get_local_name();
        }
        // Выбор источника изображения
        $mform->addElement(
            'select',
            'imagesource',
            get_string('editform_imagesource_label', 'qtype_otimagepointer'),
            $select
        );
        $mform->setDefault('imagesource', '');
        
        // Добавление индивидуальных полей каждого источника изображения
        foreach ( $imagesources as $code => $imagesource )
        {
            // Передача управления источнику изображения
            $imagesource->editform_definition($this, $mform);
        }
    }

    /**
     * Предварительная обработка полей формы сохранения экземпляра вопроса
     *
     * Организация заполнения полей данными
     *
     * @param object $question - Данные вопроса для заполнения полей формы
     *
     * @return object $question - Данные для заполнения
     */
    protected function data_preprocessing($question) 
    {
        $mform = $this->_form;
        // Базовый обработчик
        $question = parent::data_preprocessing($question);
        // Получение типа текущего вопроса
        $qtype = question_bank::get_qtype('otimagepointer');
        // Получение списка источников
        $imagesources = (array)$qtype->imagesources_get_list();
        
        if ( ! empty($question->options->imagesourcetype) ) 
        {
            $question->imagesource = $question->options->imagesourcetype;
        }
        
        // Заполнение данными полей источников базового изображения
        foreach ( $imagesources as $code => $imagesource )
        {
            // Заполнение полей источника изображения
            $imagesource->editform_set_data($question, $this, $mform);
        }
        
        return $question;
    }

    /**
     * Валидация полей формы сохранения экземпляра вопроса
     *
     * @param array $data - Данные формы сохранения
     * @param array $data - Загруженные файлы формы сохранения
     *
     * @return $errors - Массив ошибок
     */
    public function validation($data, $files) 
    {
        $mform = $this->_form;
        
        // Базовая валидация
        $errors = parent::validation($data, $files);
        // Получение типа текущего вопроса
        $qtype = question_bank::get_qtype('otimagepointer');
        // Получение списка источников
        $imagesources = (array)$qtype->imagesources_get_list();
        
        if ( empty($data['imagesource']) )
        {// Источник не выбран
            $errors['imagesource'] = get_string('error_editform_imagesource_empty', 'qtype_otimagepointer');
        } else 
        {// Валидация выбранного источника
            if ( ! isset($imagesources[$data['imagesource']]) )
            {// Выбранный источник не доступен
                $errors['imagesource'] = get_string('error_editform_imagesource_notfound', 'qtype_otimagepointer');
            } else 
            {// Внутренняя валидация источника изображения
                $imagesources[$data['imagesource']]->editform_validation($errors, $this, $mform, $data, $files);
            }
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
    public static function file_uploaded($draftitemid)
    {
        // Получение файлов в драфтзоне
        $draftareafiles = file_get_drafarea_files($draftitemid);
        
        // Поиск требуемых файлов в зоне
        do {
            $draftareafile = array_shift($draftareafiles->list);
        } while ( $draftareafile !== null && $draftareafile->filename == '.' );
        
        // Проверка файла
        if ( $draftareafile === null ) 
        {
            return false;
        }
        // Валидация файла
        if ( ! isset($draftareafile->image_width) && ! isset($draftareafile->image_height) )
        {
            return false;
        }
        // Файл найден
        return true;
    }
}