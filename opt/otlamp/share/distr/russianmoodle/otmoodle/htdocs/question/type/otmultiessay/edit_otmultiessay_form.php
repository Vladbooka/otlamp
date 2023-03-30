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
 * Тип вопроса Мульти-эссе. Класс формы сохранения экземпляра вопроса.
 *
 * @package    qtype
 * @subpackage otmultiessay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Форма сохранения экземпляра вопроса
 * 
 */
class qtype_otmultiessay_edit_form extends question_edit_form 
{
    /** Number of answers in question by default */
    const NUM_ANS_DEFAULT = 3;
    
    /** Minimal number of answers to show */
    const NUM_ANS_MIN = 3;
    
    /** Number of answers to add on demand */
    const NUM_ANS_ADD = 3;
    
    public function qtype()
    {
        return 'otmultiessay';
    }
    
    /**
     * Returns answer repeats count
     *
     * @param object $question
     * @return int
     */
    protected function get_answer_repeats($question) {
        if (isset($question->id)) {
            $repeats = count($question->options->innerquestion);
        } else {
            $repeats = self::NUM_ANS_DEFAULT;
        }
        if ($repeats < self::NUM_ANS_MIN) {
            $repeats = self::NUM_ANS_MIN;
        }
        return $repeats;
    }
    
    protected function definition_inner($mform) 
    {
        $mform = $this->_form;
        $qtype = question_bank::get_qtype('otmultiessay');
        // Cache this plugins name.
        $plugin = 'qtype_otmultiessay';
        
        $elements = [];
        $options = [];
        
        $mform->addElement('static', 'hiddenfield');
        $mform->setDefault('hiddenfield', '');
        $mform->setType('hiddenfield', PARAM_ALPHA);
        
        // Шапка формы
        $name = 'questionheader';
        $label = get_string($name, $plugin);
        $elements[] = $mform->createElement('header', $name, $label);
        $options[$name] = ['expanded' => false];
        
        // Отображать или нет вопрос
        $name = 'enablequestion';
        $label = get_string($name, $plugin);
        $elements[] = $mform->createElement('selectyesno', $name, $label);
        
        // Внутренний вопрос
        $name = 'innerquestion';
        $label = get_string($name, $plugin);
        $elements[] = $mform->createElement('editor', $name, $label,
            ['rows' => 10],  array_merge($this->editoroptions, ['maxfiles' => 0]));

        // Опции отзыва
        $name = 'responseoptions';
        $label = get_string($name, $plugin);
        $elements[] = $mform->createElement('static', $name, $label);
        
        // Формат ответа
        $name = 'responseformat';
        $label = get_string($name, $plugin);
        $elements[] =  $mform->createElement('select', $name, $label, $qtype->response_formats());
        
        // Требование ввода текста
        $name = 'responserequired';
        $label = get_string($name, $plugin);
        $elements[] = $mform->createElement('select', $name, $label, $qtype->response_required_options());
        $mform->disabledIf('responserequired', 'responseformat', 'eq', 'noinline');
        
        // Размер поля ответа, в линиях
        $name = 'responsefieldlines';
        $label = get_string($name, $plugin);
        $elements[] = $mform->createElement('select', $name, $label, $qtype->response_sizes());
        $mform->disabledIf('responsefieldlines', 'responseformat', 'eq', 'noinline');
        
        // Разрешение вложений
        $name = 'attachments';
        $label = get_string($name, $plugin);
        $elements[] = $mform->createElement('select', $name, $label, $qtype->attachment_options());
        
        // Обязательность вложений
        $name = 'attachmentsrequired';
        $label = get_string($name, $plugin);
        $elements[] = $mform->createElement('select', $name, $label, $qtype->attachments_required_options());
        $mform->disabledIf('attachmentsrequired', 'attachments', 'eq', 0);
        
        // Шаблон отзыва (шапка)
        $name = 'responsetemplateheader';
        $label = get_string($name, $plugin);
        $elements[] = $mform->createElement('static', 'responsetemplateheader', $label);
        
        // Шаблон ответа
        $name = 'responsetemplate';
        $label = get_string($name, $plugin);
        $elements[] = $mform->createElement('editor', 'responsetemplate', $label,
            ['rows' => 10],  array_merge($this->editoroptions, ['maxfiles' => 0]));
        
        // Информация для оценивающих (шапка)
        $name = 'graderinfoheader';
        $label = get_string($name, $plugin);
        $elements[] = $mform->createElement('static', $name, $label);
        
        // Информация для оценивающих
        $name = 'graderinfo';
        $label = get_string($name, $plugin);
        $elements[] = $mform->createElement('editor', 'graderinfo', $label,
            ['rows' => 10], $this->editoroptions);
        
        // Добавление дополнительных внутренних вопросов
        $repeats = $this->get_answer_repeats($this->question);
        $label = get_string('addmoreanswers', $plugin, self::NUM_ANS_ADD); // Button text.
        $this->repeat_elements($elements, $repeats, $options, 'countanswers', 'addanswers', self::NUM_ANS_ADD, $label);
        
        if (optional_param('addanswers', 0, PARAM_RAW)) {
            $repeats += self::NUM_ANS_ADD;
        } 
    }

    protected function data_preprocessing($question) {
        $mform = $this->_form;
        // Базовый обработчик
        $question = parent::data_preprocessing($question);
        // Получение типа текущего вопроса
        $qtype = question_bank::get_qtype('otmultiessay');

        if (empty($question->options)) {
            return $question;
        }

        // Сохраняем полученные из опций данные в вопросе
        foreach($question->options->innerquestion as $key => $innerquestion)
        {
            $question->innerquestion[$key] = [
                'text' => $question->options->innerquestion[$key],
                'format' => $question->options->innerquestionformat[$key]
            ];
        }
        
        $question->responseformat = $question->options->responseformat;
        $question->responserequired = $question->options->responserequired;
        $question->responsefieldlines = $question->options->responsefieldlines;
        $question->attachments = $question->options->attachments;
        $question->attachmentsrequired = $question->options->attachmentsrequired;

        foreach($question->options->graderinfo as $key => $graderinfo)
        {
            $draftid[$key] = file_get_submitted_draft_itemid('graderinfo[' . $key . ']');
            $question->graderinfo[$key] = [];
            $question->graderinfo[$key]['text'] = file_prepare_draft_area(
                $draftid[$key],           // Draftid
                $this->context->id, // context
                'qtype_otmultiessay',      // component
                'graderinfo_' . $key,       // filarea
                !empty($question->id) ? (int) $question->id : null, // itemid
                $this->fileoptions, // options
                $graderinfo // text.
            );
            $question->graderinfo[$key]['format'] = $question->options->graderinfoformat[$key];
            $question->graderinfo[$key]['itemid'] = $draftid[$key];
        }
        
        foreach($question->options->responsetemplate as $key => $responsetemplate)
        {
            $question->responsetemplate[$key] = [
                'text' => $question->options->responsetemplate[$key],
                'format' => $question->options->responsetemplateformat[$key]
            ];
        }
        
        $question->enablequestion = $question->options->enablequestion;

        return $question;
    }

    public function validation($fromform, $files) 
    {
        $errors = parent::validation($fromform, $files);

        $hasinnerquestion = false;
        foreach($fromform['innerquestion'] as $key => $innerquestion)
        {
            // Don't allow both 'no inline response' and 'no attachments' to be selected,
            // as these options would result in there being no input requested from the user.
            if( $fromform['responseformat'][$key] == 'noinline' && ! $fromform['attachments'][$key] ) 
            {
                $errors['attachments[' . $key . ']'] = get_string('mustattach', 'qtype_otmultiessay');
            }
            
            // If 'no inline response' is set, force the teacher to require attachments;
            // otherwise there will be nothing to grade.
            if( $fromform['responseformat'][$key] == 'noinline' && ! $fromform['attachmentsrequired'][$key] ) 
            {
                $errors['attachmentsrequired[' . $key . ']'] = get_string('mustrequire', 'qtype_otmultiessay');
            }
            
            // Don't allow the teacher to require more attachments than they allow; as this would
            // create a condition that it's impossible for the student to meet.
            if( $fromform['attachments'][$key] != -1 && $fromform['attachments'][$key] < $fromform['attachmentsrequired'][$key] ) 
            {
                $errors['attachmentsrequired[' . $key . ']']  = get_string('mustrequirefewer', 'qtype_otmultiessay');
            }
            
            if ( $fromform['enablequestion'][$key] )
            {
                $hasinnerquestion = true;
            }
        }
        
        if ( ! $hasinnerquestion )
        {// Нет активных вопросов
            $errors['hiddenfield']  = get_string('error_no_active_questions', 'qtype_otmultiessay');
        }
        return $errors;
    }
    
    /**
     * Resets editor format to specified
     *
     * @param object $editor
     * @param int $format
     * @return int
     */
    protected function reset_editor_format($editor, $format=FORMAT_MOODLE) {
        $value = $editor->getValue();
        $value['format'] = $format;
        $value = $editor->setValue($value);
        return $format;
    }
}