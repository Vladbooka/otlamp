<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Слайдер. Класс слайда со списком.
 *
 * @package    block_otslider
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_otslider\slides\types;

use stdClass;
use dml_exception;
use MoodleQuickForm;
use html_writer;
use block_otslider\slides\base as slidebase;
use block_otslider\slides\formsave as formsave;
use block_otslider\exception\slide as exception_slide;

class listitems extends slidebase
{
    private $editoroptions;
    private $preparedhtmlcode;
    
    /**
     * Получить код типа слайда
     *
     * @return string
     */
    public static function get_code()
    {
        return 'listitems';
    }
    
    /**
     * Получить локализованное название типа слайда
     *
     * @return string
     */
    public static function get_name()
    {
        return get_string('slide_listitems_name', 'block_otslider');
    }
    
    /**
     * Получить локализованное описание типа слайда
     *
     * @return string
     */
    public static function get_description()
    {
        return get_string('slide_listitems_descripton', 'block_otslider');
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \block_otslider\slides\base::get_slide_options()
     */
    public function get_slide_options () {
        $slideoptions = new \stdClass();
        $slideoptions->title = $this->get_slide_title();
        $slideoptions->items = $this->get_slide_items();
        $slideoptions->rendermode = $this->get_slide_rendermode();
			$image = new \stdClass();
			$image->contextid = \context_system::instance()->id;
            $image->component = 'block_otslider';
            $image->filearea = 'public';
            $image->itemid = $this->record->id;
		$slideoptions->image = $image;
        return $slideoptions;
    }
    
    /**
     * Получение списка
     *
     * @return string
     */
    public function get_slide_items()
    {
        global $DB;
        
        // Заголовок
        $itemssetting = $DB->get_field(
            'block_otslider_slide_options',
            'data',
            ['slideid' => $this->record->id, 'name' => 'items']
            );
        
        if ( $itemssetting !== false && ! is_null($itemssetting) )
        {
            return $itemssetting;
        }
        
        return '';
    }
    
    /**
     * Сохранение списка
     *
     * @param string $items - элементы списка
     *
     * @return void
     */
    public function set_slide_items($items)
    {
        global $DB;
        
        if( is_null($items) )
        {
            $items = '';
        }
        
        $data = new stdClass();
        $data->data = (string)$items;
        if ( $record = $DB->get_record('block_otslider_slide_options', ['slideid' => $this->record->id, 'name' => 'items']) )
        {// Обновление заголовка
            $data->id = $record->id;
            $DB->update_record('block_otslider_slide_options', $data);
        } else
        {// Добавление заголовка
            $data->slideid = $this->record->id;
            $data->name = 'items';
            $DB->insert_record('block_otslider_slide_options', $data);
        }
    }
    
    
    /**
     * Получение способа отображения списка
     *
     * @return string
     */
    public function get_slide_rendermode()
    {
        global $DB;
        
        // Заголовок
        $rendermode = $DB->get_field(
            'block_otslider_slide_options',
            'shortdata',
            ['slideid' => $this->record->id, 'name' => 'rendermode']
            );
        
        if ( $rendermode !== false && ! is_null($rendermode) )
        {
            return $rendermode;
        }
        
        return 'checkboxes';
    }
    
    /**
     * Сохранение способа отображения списка
     *
     * @param string $rendermode - способ отображения списка
     *
     * @return void
     */
    public function set_slide_rendermode($rendermode)
    {
        global $DB;
        
        if( is_null($rendermode) )
        {
            $rendermode = 'checkboxed';
        }
        
        $data = new stdClass();
        $data->shortdata = (string)$rendermode;
        if ( $record = $DB->get_record('block_otslider_slide_options', ['slideid' => $this->record->id, 'name' => 'rendermode']) )
        {// Обновление заголовка
            $data->id = $record->id;
            $DB->update_record('block_otslider_slide_options', $data);
        } else
        {// Добавление заголовка
            $data->slideid = $this->record->id;
            $data->name = 'rendermode';
            $DB->insert_record('block_otslider_slide_options', $data);
        }
    }
    
    
    /**
     * Получение заголовка
     *
     * @return string
     */
    public function get_slide_title()
    {
        global $DB;
        
        // Заголовок
        $title = $DB->get_field(
            'block_otslider_slide_options',
            'shortdata',
            ['slideid' => $this->record->id, 'name' => 'title']
        );
        
        if ( $title !== false && ! is_null($title) )
        {
            return $title;
        }
        
        return '';
    }
    
    /**
     * Сохранение заголовка
     *
     * @param string $title - заголовок
     *
     * @return void
     */
    public function set_slide_title($title)
    {
        global $DB;
        
        if( is_null($title) )
        {
            $title = '';
        }
        
        $data = new stdClass();
        $data->shortdata = (string)$title;
        if ( $record = $DB->get_record('block_otslider_slide_options', ['slideid' => $this->record->id, 'name' => 'title']) )
        {// Обновление заголовка
            $data->id = $record->id;
            $DB->update_record('block_otslider_slide_options', $data);
        } else
        {// Добавление заголовка
            $data->slideid = $this->record->id;
            $data->name = 'title';
            $DB->insert_record('block_otslider_slide_options', $data);
        }
    }
    
    /**
     * Добавление полей в форму сохранения слайда
     *
     * @param formsave $formsave - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     * @param string $prefix - Префикс элементов формы
     *
     * @return void
     */
    public function saveform_definition($formsave, $mform, $prefix)
    {
        // Изображение слайда
        $mform->addElement(
            'filemanager',
            $prefix.'_background',
            get_string('slide_listitems_formsave_background_label', 'block_otslider'),
            null,
            ['maxfiles' => 1, 'accepted_types' => ['image']]
        );
        
        // Заголовок
        $mform->addElement(
            'text',
            $prefix.'_title',
            get_string('slide_listitems_formsave_title_label', 'block_otslider')
        );
        $mform->setType($prefix.'_title', PARAM_TEXT);
        
        // Список
        $mform->addElement(
            'textarea',
            $prefix.'_items',
            get_string('slide_listitems_formsave_items_label', 'block_otslider'),
            ['rows' => '10', 'cols' => '50']
        );
        
        
        // Тип отображения списка
        $choices = [
            'checkboxes' => get_string('slide_listitems_formsave_rendermode_checkboxes', 'block_otslider'),
            'blocksgrid' => get_string('slide_listitems_formsave_rendermode_blocks_by_grid', 'block_otslider')
        ];
        $mform->addElement(
            'select',
            $prefix.'_rendermode',
            get_string('slide_listitems_formsave_rendermode_label', 'block_otslider'),
            $choices
        );
    }
    
    /**
     * Предварительная обработка полей формы сохранения слайда
     *
     * Организация заполнения полей данными
     *
     * @param formsave $formsave - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     * @param string $prefix - Префикс элементов формы
     *
     * @return void
     */
    public function saveform_set_data($formsave, $mform, $prefix)
    {
        // Поиск пустой пользовательской драфтзоны для подключения к файлпикеру
        $draftitemid = file_get_submitted_draft_itemid($prefix.'_background');
        // Загрузка в пользовательскую зону изображения слайдера
        file_prepare_draft_area(
            $draftitemid,
            \context_system::instance()->id,
            'block_otslider',
            'public',
            $this->record->id
        );
        // Привязка файлпикера к пользовательской драфтзоне
        $mform->setDefault($prefix.'_background', $draftitemid);
        
        // Установка заголовка
        $mform->setDefault($prefix.'_title', $this->get_slide_title());
        
        // Установка значения списка
        $mform->setDefault($prefix.'_items', $this->get_slide_items());
        
        // Установка выбранного способа отображения
        $mform->setDefault($prefix.'_rendermode', $this->get_slide_rendermode());
    }
    
    /**
     * Валидация полей формы сохранения слайда
     *
     * @param array $errors - Массив ошибок валидации
     * @param formsave $saveform - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     * @param array $data - Данные формы сохранения
     * @param array $files - Загруженные файлы формы сохранения
     * @param string $prefix - Префикс элементов формы
     *
     * @return void
     */
    public function saveform_validation($errors, $saveform, $mform, $data, $files, $prefix)
    {
        
    }
    
    /**
     * Процесс сохранения слайда
     *
     * @param formsave $saveform - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     * @param stdClass $formdata - Данные формы сохранения
     * @param string $prefix - Префикс элементов формы
     *
     * @return void
     */
    public function saveform_process($saveform, $mform, $formdata, $prefix)
    {
        // Сохранение изображения
        $fieldname = $prefix.'_background';
        file_save_draft_area_files(
            $formdata->$fieldname,
            \context_system::instance()->id,
            'block_otslider',
            'public',
            $this->record->id,
            ['maxfiles' => 1, 'accepted_types' => ['image']]
            );
        
        // Сохранение заголовка
        $fieldname = $prefix.'_title';
        $this->set_slide_title($formdata->$fieldname);
        
        // Сохранение списка
        $fieldname = $prefix.'_items';
        $this->set_slide_items($formdata->$fieldname);
        
        // Сохранение способа отображения списка
        $fieldname = $prefix.'_rendermode';
        $this->set_slide_rendermode($formdata->$fieldname);
        
    }
    
    /**
     * Процесс удаления данных слайда
     *
     * @return void
     *
     * @throws exception_slide - В случае ошибок при удалении данных слайда
     */
    public function process_delete()
    {
        global $DB;
        
        // Получение менеджера файлов
        $fs = get_file_storage();
        
        // Удаление изображения слайда
        $fs->delete_area_files(
            \context_system::instance()->id,
            'block_otslider',
            'public',
            $this->record->id
        );
        
        // Попытка удаления всех опций слайда
        try
        {
            $DB->delete_records('block_otslider_slide_options', ['slideid' => $this->record->id]);
        } catch ( dml_exception $e )
        {// Ошибка удаления слайда
            throw new exception_slide('slide_listitems_delete_error_options', 'block_otslider', '', null, $e->getMessage());
        }
    }
}