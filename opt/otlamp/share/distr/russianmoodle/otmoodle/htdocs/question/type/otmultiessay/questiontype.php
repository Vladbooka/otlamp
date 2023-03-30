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
 * Тип вопроса Мульти-эссе. Класс типа вопроса.
 *
 * @package    qtype
 * @subpackage qtype_otmultiessay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');

/**
 * Класс типа вопроса
 * 
 */
class qtype_otmultiessay extends question_type 
{
    protected static $defaultoptions = [
        'innerquestion' => ['text' => '', 'format' => '1'],
        'responseformat' => 'editor',
        'responserequired' => '1',
        'responsefieldlines' => '5',
        'attachments' => '0',
        'attachmentsrequired' => '0',
        'graderinfo' => ['text' => '', 'format' => '1'],
        'responsetemplate' => ['text' => '', 'format' => '1'],
        'enablequestion' => '0'
    ];
    /**
     * Поддержка ручного оценивания вопроса
     * 
     * @return bool
     */
    public function is_manual_graded() 
    {
        return true;
    }

    /**
     * Используемые файловые зоны в ответе
     * 
     * @return array - Массив файловых зон
     */
    public function response_file_areas() 
    {
        global $DB;
        $fileareas = [];
        if( ! empty($this->questionid) )
        {
            $question = $DB->get_record('question_otmultiessay',
                ['question' => $this->questionid], '*', MUST_EXIST);
            if( ! empty($question->innerquestion) )
            {
                $question->innerquestion = unserialize($question->innerquestion);
                foreach($question->innerquestion as $key => $innerquestion)
                {
                    $fileareas[] = 'attachments_' . $key;
                    $fileareas[] = 'answer_' . $key;
                }
            }
        }
        return $fileareas;
    }
    
    /**
     * @return array the different response formats that the question type supports.
     * internal name => human-readable name.
     */
    public function response_formats() {
        return array(
            'editor' => get_string('formateditor', 'qtype_otmultiessay'),
            'editorfilepicker' => get_string('formateditorfilepicker', 'qtype_otmultiessay'),
            'plain' => get_string('formatplain', 'qtype_otmultiessay'),
            'monospaced' => get_string('formatmonospaced', 'qtype_otmultiessay'),
            'noinline' => get_string('formatnoinline', 'qtype_otmultiessay'),
        );
    }
    
    /**
     * @return array the choices that should be offerd when asking if a response is required
     */
    public function response_required_options() {
        return array(
            1 => get_string('responseisrequired', 'qtype_otmultiessay'),
            0 => get_string('responsenotrequired', 'qtype_otmultiessay'),
        );
    }
    
    /**
     * @return array the choices that should be offered for the input box size.
     */
    public function response_sizes() {
        $choices = array();
        for ($lines = 5; $lines <= 40; $lines += 5) {
            $choices[$lines] = get_string('nlines', 'qtype_otmultiessay', $lines);
        }
        return $choices;
    }
    
    /**
     * @return array the choices that should be offered for the number of attachments.
     */
    public function attachment_options() {
        return array(
            0 => get_string('no'),
            1 => '1',
            2 => '2',
            3 => '3',
            -1 => get_string('unlimited'),
        );
    }
    
    /**
     * @return array the choices that should be offered for the number of required attachments.
     */
    public function attachments_required_options() {
        return array(
            0 => get_string('attachmentsoptional', 'qtype_otmultiessay'),
            1 => '1',
            2 => '2',
            3 => '3'
        );
    }
    
    /**
     * Сохраняет опции вопроса
     * {@inheritDoc}
     * @see question_type::save_question_options()
     */
    public function save_question_options($formdata) {
        global $DB;
        $realkey = 0;
        $innerquestiontext = $innerquestionformat = $responseformat = 
        $responserequired = $responsefieldlines = $attachments = 
        $attachmentsrequired = $graderinfo = $graderinfoformat = 
        $responsetemplatetext = $responsetemplateformat = $enablequestion = [];
        $context = $formdata->context;
    
        $options = $DB->get_record('question_otmultiessay', ['question' => $formdata->id]);
        if (!$options) {
            $options = new stdClass();
            $options->question = $formdata->id;
            $options->id = $DB->insert_record('question_otmultiessay', $options);
        }

        foreach($formdata->innerquestion as $key => $val)
        {
            if( ! self::is_default_options($formdata, $options, $key) )
            {
                $innerquestiontext[$realkey] = $val['text'];
                $innerquestionformat[$realkey] = $val['format'];
                $responseformat[$realkey] = $formdata->responseformat[$key];
                $responserequired[$realkey] = $formdata->responserequired[$key];
                $responsefieldlines[$realkey] = $formdata->responsefieldlines[$key];
                $attachments[$realkey] = $formdata->attachments[$key];
                $attachmentsrequired[$realkey] = $formdata->attachmentsrequired[$key];
                $graderinfo[$realkey] = $this->import_or_save_files($formdata->graderinfo[$key],
                    $context, 'qtype_otmultiessay', 'graderinfo_' . $realkey, $formdata->id);
                $graderinfoformat[$realkey] = $formdata->graderinfo[$key]['format'];
                $responsetemplatetext[$realkey] = $formdata->responsetemplate[$key]['text'];
                $responsetemplateformat[$realkey] = $formdata->responsetemplate[$key]['format'];
                $enablequestion[$realkey] = $formdata->enablequestion[$key];
                $realkey++;
            }            
        }
        $options->innerquestion = serialize($innerquestiontext);
        $options->innerquestionformat = serialize($innerquestionformat);
        $options->responseformat = serialize($responseformat);
        $options->responserequired = serialize($responserequired);
        $options->responsefieldlines = serialize($responsefieldlines);
        $options->attachments = serialize($attachments);
        $options->attachmentsrequired = serialize($attachmentsrequired);
        $options->graderinfo = serialize($graderinfo);
        $options->graderinfoformat = serialize($graderinfoformat);
        $options->responsetemplate = serialize($responsetemplatetext);
        $options->responsetemplateformat = serialize($responsetemplateformat);
        $options->enablequestion = serialize($enablequestion);
        
        $DB->update_record('question_otmultiessay', $options);
    }
    
    /**
     * Получает опции вопроса
     * {@inheritDoc}
     * @see question_type::get_question_options()
     */
    public function get_question_options($question) {
        global $DB;
        $question->options = $DB->get_record('question_otmultiessay',
            ['question' => $question->id], '*', MUST_EXIST);
        $question->options->innerquestion = unserialize($question->options->innerquestion);
        $question->options->innerquestionformat = unserialize($question->options->innerquestionformat);
        $question->options->responseformat = unserialize($question->options->responseformat);
        $question->options->responserequired = unserialize($question->options->responserequired);
        $question->options->responsefieldlines = unserialize($question->options->responsefieldlines);
        $question->options->attachments = unserialize($question->options->attachments);
        $question->options->attachmentsrequired = unserialize($question->options->attachmentsrequired);
        $question->options->graderinfo = unserialize($question->options->graderinfo);
        $question->options->graderinfoformat = unserialize($question->options->graderinfoformat);
        $question->options->responsetemplate = unserialize($question->options->responsetemplate);
        $question->options->responsetemplateformat = unserialize($question->options->responsetemplateformat);
        $question->options->enablequestion = unserialize($question->options->enablequestion);
        parent::get_question_options($question);
    }
    
    /**
     * Инициализация формы вопроса
     * {@inheritDoc}
     * @see question_type::initialise_question_instance()
     */
    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        // Сохраним в объекте id вопроса, чтобы можно было сформировать в response_file_areas файловые зоны
        $this->questionid = $question->id;
        $question->innerquestion = $questiondata->options->innerquestion;
        $question->innerquestionformat = $questiondata->options->innerquestionformat;
        $question->responseformat = $questiondata->options->responseformat;
        $question->responserequired = $questiondata->options->responserequired;
        $question->responsefieldlines = $questiondata->options->responsefieldlines;
        $question->attachments = $questiondata->options->attachments;
        $question->attachmentsrequired = $questiondata->options->attachmentsrequired;
        $question->graderinfo = $questiondata->options->graderinfo;
        $question->graderinfoformat = $questiondata->options->graderinfoformat;
        $question->responsetemplate = $questiondata->options->responsetemplate;
        $question->responsetemplateformat = $questiondata->options->responsetemplateformat;
        $question->enablequestion = $questiondata->options->enablequestion;
    }
    
    /**
     * Перемещение файлов в другой контекст
     * {@inheritDoc}
     * @see question_type::move_files()
     */
    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $fs = get_file_storage();
        $fs->move_area_files_to_new_context($oldcontextid,
            $newcontextid, 'qtype_otmultiessay', 'graderinfo', $questionid);
    }
    
    /**
     * Удаление файлов
     * {@inheritDoc}
     * @see question_type::delete_files()
     */
    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $fs = get_file_storage();
        $fs->delete_area_files($contextid, 'qtype_otmultiessay', 'graderinfo', $questionid);
    }
    
    protected static function is_default_options($formdata, $options, $key)
    {
        if( isset($options->innerquestion) )
        {
            $innerquestion = unserialize($options->innerquestion);
        } 
        if( ! isset($innerquestion[$key]) )
        {
            if( $formdata->innerquestion[$key]['text'] === self::$defaultoptions['innerquestion']['text'] && 
                $formdata->innerquestion[$key]['format'] === self::$defaultoptions['innerquestion']['format'] &&
                $formdata->responseformat[$key] === self::$defaultoptions['responseformat'] &&
                $formdata->responserequired[$key] === self::$defaultoptions['responserequired'] &&
                $formdata->responsefieldlines[$key] === self::$defaultoptions['responsefieldlines'] &&
                $formdata->attachments[$key] === self::$defaultoptions['attachments'] &&
                $formdata->attachmentsrequired[$key] === self::$defaultoptions['attachmentsrequired'] &&
                $formdata->graderinfo[$key]['text'] === self::$defaultoptions['graderinfo']['text'] &&
                $formdata->graderinfo[$key]['format'] === self::$defaultoptions['graderinfo']['format'] &&
                $formdata->responsetemplate[$key]['text'] === self::$defaultoptions['responsetemplate']['text'] &&
                $formdata->responsetemplate[$key]['format'] === self::$defaultoptions['responsetemplate']['format'] &&
                $formdata->enablequestion[$key] === self::$defaultoptions['enablequestion']
            )
            {
                return true;
            }
        }
        return false;
    }
}