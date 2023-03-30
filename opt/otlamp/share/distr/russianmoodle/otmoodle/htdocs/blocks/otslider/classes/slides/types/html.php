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
 * Слайдер изображений. Класс слайда с изображением.
 *
 * @package    block
 * @subpackage otslider
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

class html extends slidebase
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
        return 'html';
    }

    /**
     * Получить локализованное название типа слайда
     *
     * @return string
     */
    public static function get_name()
    {
        return get_string('slide_html_name', 'block_otslider');
    }

    /**
     * Получить локализованное описание типа слайда
     *
     * @return string
     */
    public static function get_description()
    {
        return get_string('slide_html_descripton', 'block_otslider');
    }


    /**
     * Получение опции с HTML-кодом слайда
     *
     * @return string
     */
    public function get_slide_htmlcode_option()
    {
        global $DB;

        // Описание заголовка
        $record = $DB->get_record(
            'block_otslider_slide_options',
            ['slideid' => $this->record->id, 'name' => 'htmlcode']
        );

        if( ! empty($record) )
        {
            return $record;
        } else
        {
            $htmlcodeoption = new stdClass();
            $htmlcodeoption->id = null;
            $htmlcodeoption->data = '';
            return $htmlcodeoption;
        }

    }
    /**
     *
     * {@inheritDoc}
     * @see \block_otslider\slides\base::get_slide_options()
     */
    public function get_slide_options () {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');
        $htmlcodeoption = $this->get_slide_htmlcode_option();
        $htmlcodeoption->data = file_rewrite_pluginfile_urls(
            $htmlcodeoption->data,
            'pluginfile.php',
            \context_system::instance()->id,
            'block_otslider',
            'public',
            $htmlcodeoption->id
            );
        return $htmlcodeoption;
    }

    /**
     * Сохранение HTML-кода слайда
     *
     * @param string $htmlcode - HTML-код слайда
     *
     * @return void
     */
    public function set_slide_htmlcode($htmlcode)
    {
        global $DB;

        $data = new stdClass();
        $data->slideid = $this->record->id;
        $data->name = 'htmlcode';
        $data->data = (string)$htmlcode;
        if ( $record = $DB->get_record('block_otslider_slide_options', ['slideid' => $this->record->id, 'name' => 'htmlcode']) )
        {// Обновление заголовка
            $data->id = $record->id;
            $DB->update_record('block_otslider_slide_options', $data);
        } else
        {// Добавление заголовка
            $DB->insert_record('block_otslider_slide_options', $data);
            $record = $DB->get_record('block_otslider_slide_options', ['slideid' => $this->record->id, 'name' => 'htmlcode']);
        }
        return $record;
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
        // Опции редатора
        $this->editoroptions = [
            'noclean' => true,
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => 0,
            'context' => \context_system::instance()
        ];

        // Получаем запись, если есть. Если нет, вернется объект с null-идентификатором
        $htmlcoderecord = $this->get_slide_htmlcode_option();
        $htmlcoderecord->dataformat = FORMAT_HTML;

        // Подготовленные для редактора данные
        // создается зона для загрузки черовиков файлов во время работы с редактором
        $prepareddata = file_prepare_standard_editor(
            $htmlcoderecord,
            'data',
            $this->editoroptions,
            \context_system::instance(),
            'block_otslider',
            'public',
            $htmlcoderecord->id
        );

        // В текущей ситуации, название поля в форме и поля в таблице БД отличаются
        // Подменяем номенклатуру БД номенклатурой формы
        // Цель - корректная работа с файлами в редакторе
        foreach($prepareddata as $key=>$value)
        {
            if ( substr($key, 0, 4) === 'data' )
            {
                $newkey = $prefix . '_htmlcode' . substr($key, 4);
                $prepareddata->$newkey = $value;
                unset($prepareddata->$key);
            }
        }


        // HTML-код изображения
        $mform->addElement(
            'editor',
            $prefix.'_htmlcode_editor',
            get_string('slide_html_formsave_htmlcode_label', 'block_otslider'),
            null,
            $this->editoroptions
        );

        // Установка подготовленных значений для редактора
        $formsave->set_data($prepareddata);
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
        // Сохранение описания
        $fieldname = $prefix.'_htmlcode_editor';
        $htmlcodedata = $formdata->$fieldname;

        // Сохраняем то, что пришло из формы как есть
        // (на случай, если запись создается и необходимо получить ее идентификатор)
        $record = $this->set_slide_htmlcode($htmlcodedata['text']);


        if ( ! empty($record) )
        {// Теперь постобработка данных

            // В текущей ситуации, название поля в форме и поля в таблице БД отличаются
            // Подменяем номенклатуру формы номенклатурой БД для постобработки данных из редактора
            // Цель - корректная работа с файлами в редакторе
            foreach($formdata as $key=>$value)
            {
                if ( substr($key, 0, strlen($prefix.'_htmlcode')) === $prefix . '_htmlcode' )
                {
                    $newkey ='data' . substr($key, strlen($prefix.'_htmlcode'));
                    $formdata->$newkey = $value;
                    unset($formdata->$key);
                }
            }

            // Постобработка данных:
            // сохранение черновиков файлов,
            // замена адресов файлов в тексте редактора
            $postupdated = file_postupdate_standard_editor(
                $formdata,
                'data',
                $this->editoroptions,
                \context_system::instance(),
                'block_otslider',
                'public',
                $record->id
            );

            // Сохранение обновленного значения
            $this->set_slide_htmlcode($postupdated->data);
        }
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

        // Попытка удаления всех опций слайда
        try
        {
            $DB->delete_records('block_otslider_slide_options', ['slideid' => $this->record->id]);
        } catch ( dml_exception $e )
        {// Ошибка удаления слайда
            throw new exception_slide('slide_html_delete_error_options', 'block_otslider', '', null, $e->getMessage());
        }
    }
}