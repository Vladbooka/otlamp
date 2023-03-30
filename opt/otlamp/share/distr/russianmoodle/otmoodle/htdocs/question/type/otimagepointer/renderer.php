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
 * Тип вопроса Объекты на изображении. Рендер вопроса.
 *
 * @package    qtype
 * @subpackage otimagepointer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Класс рендера вопроса
 * 
 */
class qtype_otimagepointer_renderer extends qtype_renderer 
{
    /**
     * Получить блок источника для отображения в формулировке вопроса
     * 
     * @param question_attempt $qa - Текущая попытка прохождения вопроса
     * @param question_display_options $options - Опции отображения вопроса
     * 
     * @return string - HTML-код источника изображения для отображения в формулировке вопроса
     */
    private function imagesource_question_formulation(question_attempt $qa,
            question_display_options $options )
    {
        // Получение текущего вопроса
        $question = $qa->get_question();
        // Получение источника базового изображения вопроса
        $imagesource = $question->get_imagesource();
        
        // Получение блока источника изображения вопроса
        return $imagesource->question_formulation($qa, $options);
    }
    
    /**
     * Получить блок изображения для вопроса
     *
     * @param question_attempt $qa - Текущая попытка прохождения вопроса
     * @param question_display_options $options - Опции отображения вопроса
     *
     * @return string - HTML-код источника изображения для отображения в формулировке вопроса
     */
    private function image_block( question_attempt $qa,
        question_display_options $options )
    {
        // Получение текущего вопроса
        $question = $qa->get_question();
        // ID попытки прохождения вопроса
        $qaid = $qa->get_database_id();
        // Источник изображения
        $imagesource = $question->get_imagesource();
        
        // Имя для поля ответа 
        $fieldname_answer_baseimage = $qa->get_qt_field_name('answer_baseimage');
        $fieldname_baseimage_pathnamehash = $qa->get_qt_field_name('answer_baseimage_pathnamehash');
        
        // Значение поля ответа
        $answer = $qa->get_last_qt_var('answer');
        $answer_baseimage_contenthash = $qa->get_last_qt_var('answer_baseimage');
        $answer_baseimage_pathnamehash = $qa->get_last_qt_var('answer_baseimage_pathnamehash');
        
        if ( $answer_baseimage_contenthash === null )
        {// Изображение для ответа пользователя не определено
            // Запись в качестве ответа текущего источника изображения
            $baseimage = $imagesource->question_get_image($qa);
            if ( $baseimage )
            {
                $answer_baseimage_contenthash = $baseimage->get_contenthash();
            }
        }
        
        
        if ( $answer_baseimage_pathnamehash === null )
        {// Изображение для ответа пользователя не определено
            // Запись в качестве ответа текущего источника изображения
            $baseimage = $imagesource->question_get_image($qa);
            if ( $baseimage )
            {
                $answer_baseimage_pathnamehash = $baseimage->get_pathnamehash();
            }
        }
        
        $html = '';
        
        // Обертка
        if ( $question->has_image($qa) )
        {// Изображение найдено
            $html .= html_writer::start_div('image_editing', ['id' => 'image_editing_'.$qaid]);
        } else
        {// Изображение не найдено
            $html .= html_writer::start_div('image_editing hidden', ['id' => 'image_editing_'.$qaid]);
        }
        
        if ( ! $options->readonly )
        {
            // Блок для вывода базового изображения
            $html .= html_writer::img(
                $question->get_image_url($qa),
                '',
                [
                    'id' => 'qtype_otimagepointer_baseimage_'.$qaid,
                    'class' => 'qtype_otimagepointer_baseimage'
                ]
            );
            // Редактор изображения
            $html .= $this->editor_block($qa, $options);
        } else 
        {// Отображение совмещенного ответа
            
            // Генерация имени результирующего изображения
            $filename = sha1($answer_baseimage_contenthash . $answer);
            $file = $question->get_responsefile($filename);
            
            if ( $file )
            {
                $url = moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
                );
                
                $html .= html_writer::img(
                    $url,
                    '',
                    [
                        'id' => 'qtype_otimagepointer_baseimage_'.$qaid,
                        'class' => 'qtype_otimagepointer_baseimage'
                    ]
                );
            }
        }
        
        
        $html .= html_writer::end_div();
        
        // Поле с контентхэшем
        $html .= html_writer::empty_tag(
            'input',
            [
                'type' => 'hidden',
                'id' => 'qtype_otimagepointer_baseimage_ch_'.$qaid,
                'name' => $fieldname_answer_baseimage,
                'value' => (string)$answer_baseimage_contenthash
            ]
        );
        
        // Поле с хэшем пути
        $html .= html_writer::empty_tag(
            'input',
            [
                'type' => 'hidden',
                'id' => 'qtype_otimagepointer_baseimage_pathnamehash_ch_'.$qaid,
                'name' => $fieldname_baseimage_pathnamehash,
                'value' => (string)$answer_baseimage_pathnamehash
            ]
        );
        
        return $html;
    }
    
    /**
     * Получить блок изображения для вопроса
     *
     * @param question_attempt $qa - Текущая попытка прохождения вопроса
     * @param question_display_options $options - Опции отображения вопроса
     *
     * @return string - HTML-код источника изображения для отображения в формулировке вопроса
     */
    private function editor_block( question_attempt $qa,
        question_display_options $options )
    {
        global $OUTPUT, $PAGE;
        
        // Получение текущего вопроса
        $question = $qa->get_question();
        // ID попытки прохождения вопроса
        $qaid = $qa->get_database_id();
        // Получение идентификатора контейнера для рисования
        $canvas_id = uniqid();
        // Радиус по умолчанию
        $radius = 4;
    
        $readonly_сanvas = '';
        
        // Получение сохраненного пользовательского ответа
        $answer = $qa->get_last_qt_var('answer');

        // Имя поля с пользовательским ответом
        $field_answer = $qa->get_qt_field_name('answer');
        
        $html = '';
        
        // Блок рисования
        $html .= html_writer::start_div('qtype_otimagepointer_canvas_wrapper');
        $html .= html_writer::start_div('qtype_otimagepointer_id_'.$qaid,
            [
                'data-canvas-instance-id' => $canvas_id
            ]
        );
        
        // Слой рисования
        $html .= '<canvas
                id="qtype_otimagepointer_canvas_'.$qaid.'"
                class="qtype_otimagepointer_canvas'.$readonly_сanvas.'"
            ></canvas>';
        
        if ($options->readonly)
        {
            
            $readonly_сanvas = ' readonly-canvas';
        
            // Иницализация редактора
            $this->page->requires->yui_module(
                'moodle-qtype_otimagepointer-form',
                'Y.Moodle.qtype_otimagepointer.initializer.init',
                [
                    $qaid,
                    $canvas_id
                ],
                null,
                true
            );
        } else
        {
            // Получение языкового менеджера
            $stringmanager = get_string_manager();
            // Получение языковых переменных плагина
            $translations = $stringmanager->
                load_component_strings('qtype_otimagepointer', current_language());
            
            // Загрузка языковых переменных в JS обработчик
            foreach (array_keys($translations) as $string)
            {
                $PAGE->requires->string_for_js($string, 'qtype_otimagepointer');
            }
            
            // Иницализация редактора
            $this->page->requires->yui_module(
                'moodle-qtype_otimagepointer-form',
                'Y.Moodle.qtype_otimagepointer.initializer.init',
                [
                    $qaid,
                    $canvas_id
                ],
                null,
                true
            );
            
            // Панель инструментов
            $html .= html_writer::start_div('qtype_otimagepointer_canvas_actions');
            // Сброс изменений
            $html .= html_writer::img(
                $OUTPUT->image_url('clear', 'qtype_otimagepointer'),
                get_string('tool_clear', 'qtype_otimagepointer'),
                ['class' => 'tool tool_static qtype_otimagepointer_clear']
            );
            // Отменить
            $html .= html_writer::img(
                $OUTPUT->image_url('undo', 'qtype_otimagepointer'),
                get_string('tool_undo', 'qtype_otimagepointer'),
                ['class' => 'tool tool_history qtype_otimagepointer_undo']
            );
            // Вернуть
            $html .= html_writer::img(
                $OUTPUT->image_url('redo', 'qtype_otimagepointer'),
                get_string('tool_redo', 'qtype_otimagepointer'),
                ['class' => 'tool tool_history qtype_otimagepointer_redo']
            );
            // Карандаш
            $html .= html_writer::img(
                $OUTPUT->image_url('pencil', 'qtype_otimagepointer'),
                get_string('tool_pencil', 'qtype_otimagepointer'),
                ['class' => 'tool tool_drawing qtype_otimagepointer_pencil']
            );
            // Стрелка
            $html .= html_writer::img(
                $OUTPUT->image_url('arrow', 'qtype_otimagepointer'),
                get_string('tool_arrow', 'qtype_otimagepointer'),
                ['class' => 'tool tool_drawing qtype_otimagepointer_arrow']
            );
            // Прямоугольник
            $html .= html_writer::img(
                $OUTPUT->image_url('rectangle', 'qtype_otimagepointer'),
                get_string('tool_rectangle', 'qtype_otimagepointer'),
                ['class' => 'tool tool_drawing qtype_otimagepointer_rectangle']
            );
            // Стерка
            $html .= html_writer::img(
                $OUTPUT->image_url('eraser', 'qtype_otimagepointer'),
                get_string('tool_eraser', 'qtype_otimagepointer'),
                ['class' => 'tool tool_drawing qtype_otimagepointer_eraser']
            );
            // Выбор цвета
            $colorpicker_id = 'qtype_otimagepointer_colorpicker_'.$qaid;
            $PAGE->requires->js_init_call('M.util.init_colour_picker', [$colorpicker_id, false]);
            $html .= html_writer::start_div('qtype_otimagepointer_colorpicker_wrapper');
            $html .= html_writer::div(
                '',
                'tool tool_static tool_dropdown qtype_otimagepointer_colorpicker',
                [
                    'id' => $colorpicker_id.'_preview'
                ]
            );
            $html .= html_writer::start_div('form-colourpicker');
            $html .= html_writer::div(
                $OUTPUT->pix_icon('i/loading', get_string('loading', 'admin'), 'moodle', ['class'=>'loadingicon']), 
                'admin_colourpicker clearfix'
            );
            $html .= html_writer::empty_tag(
                'input', 
                [ 
                    'type' => 'text' ,
                    'id' => $colorpicker_id, 
                    'name'=>'Цвет', 
                    'value'=>'#000', 
                    'size'=>'12'
                    
                ]
            );
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
            
            // Выбор размера кисти
            $radiusselector_id = 'qtype_otimagepointer_radiusselector_'.$qaid;
            $html .= html_writer::start_div('qtype_otimagepointer_raduisselector_wrapper');
            $html .= html_writer::div(
                $radius,
                'tool tool_static tool_dropdown  qtype_otimagepointer_radusselector',
                [
                    'id' => $radiusselector_id.'_preview'
                ]
            );
            $html .= html_writer::start_div('form-radiusselector');
            $html .= html_writer::empty_tag(
                'input',
                [
                    'type' => 'range' ,
                    'id' => $radiusselector_id,
                    'name'=>'Размер кисти',
                    'value' => $radius,
                    'min' => '1',
                    'max' => '50',
                    'step' => '1'
                ]
            );
            $html .= html_writer::end_tag('div');
            $html .= html_writer::end_tag('div');
            
            $html .= html_writer::end_div();
        }
        
        // Поле с ответом пользователя
        $html .= html_writer::tag('textarea', $answer,
            [
                'class' => 'qtype_otimagepointer_textarea',
                'name' => $field_answer,
                'id' => 'qtype_otimagepointer_textarea_id_'.$qaid
            ]
        );
        
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();
    
        return $html;
    }
    
    /**
     * Генерация HTML-кода для  отображения вопроса
     * 
     * @param question_attempt $qa - Текущая попытка прохождения вопроса
     * @param question_display_options $options - Опции отображения вопроса
     * 
     * @return string - HTML-код вопроса
     */
    public function formulation_and_controls(question_attempt $qa,
        question_display_options $options)
    {
        global $CFG;
    
        // Получение текущего вопроса
        $question = $qa->get_question();
        // ID попытки прохождения вопроса
        $qaid = $qa->get_database_id();

        // Вывод
        $html = '';
        
        // Ответ пользователя
        $answer = $qa->get_last_qt_var('answer');
        $answer_combined_image = $qa->get_last_qt_var('answer_combned');
        
        // ОТОБРАЖЕНИЕ
        // Текст вопроса
        $questiontext = $question->format_questiontext($qa);
        $html .= html_writer::tag('div', $questiontext);
        
        // HTML-Блок источника изображения
        $html .= $this->imagesource_question_formulation($qa, $options);

        // Блок изображения с редактором
        $html .= html_writer::start_div('image_editing_wrapper');
        $html .= $this->image_block($qa, $options);
        $html .= html_writer::end_div();
        
        $html = html_writer::div(
            $html, 
            'qtype_otimagepointer_wrapper',
            [
                'id' => 'qattempt_'.$qaid 
            ]
        );
        
        if ( $qa->get_state() == question_state::$invalid ) 
        {
            $html .= html_writer::nonempty_tag('div',
                $question->get_validation_error(
                    ['answer' => $answer]
                ),
                ['class' => 'validationerror']
            );
        }
        
        return $html;
    }
    
    protected static function get_url_for_image(question_attempt $qa, $filearea, $itemid = 0) 
    {
        $question = $qa->get_question();
        $qubaid = $qa->get_usage_id();
        $slot = $qa->get_slot();
        $fs = get_file_storage();
        if ($filearea == 'bgimage') {
            $itemid = $question->id;
        }
        $componentname = $question->qtype->plugin_name();
        $draftfiles = $fs->get_area_files($question->contextid, $componentname,
            $filearea, $itemid, 'id');
        if ($draftfiles) {
            foreach ($draftfiles as $file) {
                if ($file->is_directory()) {
                    continue;
                }
                $url = moodle_url::make_pluginfile_url($question->contextid, $componentname,
                    $filearea, "$qubaid/$slot/{$itemid}", '/',
                    $file->get_filename());
                return $url->out();
            }
        }
        return null;
    }
}