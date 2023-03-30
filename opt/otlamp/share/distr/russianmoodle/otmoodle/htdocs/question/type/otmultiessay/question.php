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
 * Тип вопроса Мульти-эссе. Описание механизма работы вопроса.
 *
 * @package    qtype
 * @subpackage qtype_otmultiessay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Класс, описывающий логику работы вопроса в системе
 * 
 */
class qtype_otmultiessay_question extends question_with_responses 
{
    /**
     * Внутренний вопрос
     * @var string
     */
    public $innerquestion;
    
    /**
     * Формат внутреннего вопроса
     * @var string
     */
    public $innerquestionformat;
    
    /**
     * Формат ответа на внутренний вопрос
     * @var string
     */
    public $responseformat;
    
    /** @var int Indicates whether an inline response is required ('0') or optional ('1')  */
    public $responserequired;
    
    /**
     * Высота, в линиях, поля для ввода ответа на внутренний вопрос
     * @var string
     */
    public $responsefieldlines;
    
    /**
     * Разрешение вложений (0-3 - количество, -1 - без ограничений)
     * @var int
     */
    public $attachments;
    
    /** @var int The number of attachments required for a response to be complete. */
    public $attachmentsrequired;
    
    /**
     * Информация для оценивающих
     * @var string
     */
    public $graderinfo;
    
    /**
     * Формат информации для оценивающих
     * @var string
     */
    public $graderinfoformat;
    
    /**
     * Шаблон ответа на внутренний вопрос
     * @var string
     */
    public $responsetemplate;
    
    /**
     * Формат шаблона ответа на внутренний вопрос
     * @var string
     */
    public $responsetemplateformat;
    
    /**
     * Флаг отображения вопроса
     * @var int
     */
    public $enablequestion;

    /**
     * Получить движок вопроса
     * 
     * @param question_attempt $qa - Объект попытки прохождения вопроса
     * @param string $preferredbehaviour - Необходимый тип движка
     * 
     * @return question_behaviour - Объект инициализированного движка вопроса
     */
    public function make_behaviour(question_attempt $qa, $preferredbehaviour) 
    {
        return question_engine::make_behaviour('manualgraded', $qa, $preferredbehaviour);
    }
    
    /**
     * Формирует массив вида [поле формы => тип ожидаемых данных] и возвращает его
     * {@inheritDoc}
     * @see question_definition::get_expected_data()
     */
    public function get_expected_data() 
    {
        foreach($this->responseformat as $key => $responseformat)
        {
            if ($responseformat == 'editorfilepicker') {
                $expecteddata['answer_' . $key . ''] = question_attempt::PARAM_RAW_FILES;
            } else {
                $expecteddata['answer_' . $key . ''] = PARAM_RAW;
            }
            $expecteddata['answer_' . $key . 'format'] = PARAM_ALPHANUMEXT;
            if ($this->attachments[$key] != 0) {
                $expecteddata['attachments_' . $key . ''] = question_attempt::PARAM_FILES;
            }
        }
        return $expecteddata;
    }
    
    public function get_correct_response() {
        return null;
    }
    
    /**
     * Проверяет дан ли полный ответ на вопрос
     * {@inheritDoc}
     * @see question_manually_gradable::is_complete_response()
     */
    public function is_complete_response(array $response) 
    {
        // Determine if the given response has inline text and attachments.
        $hasinlineinnertext = $hasinnerattachments = $attachcounts = $meetsinlinereqs = $meetsattachmentreqs = [];
        $hasinlinetext = false;
        $attachcount = 0;
        $meetsinlinereq = $meetsattachmentreq = true;
        foreach($this->innerquestion as $key => $innerquestion)
        {
            if( isset($response['answer_' . $key . '']) )
            {
                if( ! empty($response['answer_' . $key . '']) )
                {
                    $responseanswer = trim(strip_tags($response['answer_' . $key . '']));
                } else
                {
                    $responseanswer = null;
                }
                $hasinlineinnertext[$key] = array_key_exists('answer_' . $key . '', $response) && ( ! empty($responseanswer) );
                $hasinnerattachments[$key] = array_key_exists('attachments_' . $key . '', $response)
                && $response['attachments_' . $key . ''] instanceof question_response_files;
            }
        }
        foreach($hasinlineinnertext as $key => $hasinnertext)
        {
            $hasinlinetext = $hasinlinetext || $hasinnertext;
            $meetsinlinereqs[$key] = $hasinnertext || (!$this->responserequired[$key]) || ($this->responseformat[$key] == 'noinline');
        }
        
        $hasattachments = array_key_exists('attachments', $response)
        && $response['attachments'] instanceof question_response_files;
    
        // Determine the number of attachments present.
        foreach($hasinnerattachments as $key => $hasinnerattachment)
        {
            if ($hasinnerattachment) {
                $attachcounts[$key] = count($response['attachments_' . $key . '']->get_files());
            } else {
                $attachcounts[$key] = 0;
            }
            $attachcount += $attachcounts[$key];
            $meetsattachmentreqs[] = ($attachcounts[$key] >= $this->attachmentsrequired[$key]);
        }   
    
        // Determine if we have /some/ content to be graded.
        $hascontent = $hasinlinetext || ($attachcount > 0);
    
        // Determine if we meet the optional requirements.
        foreach($meetsinlinereqs as $key => $val)
        {
            $meetsinlinereq = $meetsinlinereq && $val;
        }
        foreach($meetsattachmentreqs as $val)
        {
            $meetsattachmentreq = $meetsattachmentreq && $val;
        }
        
        // The response is complete iff all of our requirements are met.
        return $hascontent && $meetsinlinereq && $meetsattachmentreq;
    }
    
    /**
     * Проверяет изменился ли текущий ответ по сравнению с предыдущим ответом
     * {@inheritDoc}
     * @see question_manually_gradable::is_same_response()
     */
    public function is_same_response(array $prevresponse, array $newresponse) {
        $result = [];
        foreach($this->innerquestion as $key => $innerquestion)
        {
            if (array_key_exists('answer_' . $key . '', $prevresponse) && 
                $prevresponse['answer_' . $key . ''] !== $this->responsetemplate[$key]) {
                $value1[$key] = (string) $prevresponse['answer_' . $key . ''];
            } else {
                $value1[$key] = '';
            }
            if (array_key_exists('answer_' . $key . '', $newresponse) && 
                $newresponse['answer_' . $key . ''] !== $this->responsetemplate[$key]) {
                $value2[$key] = (string) $newresponse['answer_' . $key . ''];
            } else {
                $value2[$key] = '';
            }
            $result[$key] = $value1[$key] === $value2[$key] && ($this->attachments[$key] == 0 ||
            question_utils::arrays_same_at_key_missing_is_blank(
                $prevresponse, $newresponse, 'attachments_' . $key));
        }
        $return = true;
        foreach($result as $val)
        {
            $return = $return && $val;
        }
        return $return;
    }
    
    /**
     * Создает резюме ответа в виде обычного текста
     * {@inheritDoc}
     * @see question_manually_gradable::summarise_response()
     */
    public function summarise_response(array $response) {
        $result = [];
        foreach($this->innerquestion as $key => $innerquestion)
        {
            if( isset($response['answer_' . $key]) ) 
            {
                $result[$key] = question_utils::to_plain_text($response['answer_' . $key],
                    $response['answer_' . $key . 'format'], ['para' => false]);
            } else 
            {
                $result[$key] = null;
            }
        }
        return implode(' ', $result);
    }
    
    /**
     * @param moodle_page the page we are outputting to.
     * @return qtype_otmultiessay_format_renderer_base the response-format-specific renderer.
     */
    public function get_format_renderer(moodle_page $page) {
        $renderers = [];
        foreach($this->responseformat as $key => $responseformat)
        {
            $renderers[] = $page->get_renderer('qtype_otmultiessay', 'format_' . $responseformat);
        }
        return $renderers;
    }
    
    /** @return the result of applying {@link format_text()} to the innerquestion text. */
    public function format_innerquestiontext() {
        $result = [];
        foreach($this->innerquestion as $key => $innerquestion)
        {
            switch($this->innerquestionformat[$key])
            {
                case 0:
                    $format = FORMAT_MOODLE;
                    break;
                case 1:
                    $format = FORMAT_HTML;
                    break;
                case 2:
                    $format = FORMAT_PLAIN;
                    break;
                case 3:
                    $format = FORMAT_WIKI;
                    break;
                case 4:
                    $format = FORMAT_MARKDOWN;
                    break;
                default:
                    $format = FORMAT_MOODLE;
                    break;
            }
            $result[$key] = format_text($innerquestion, $format);
        }
        return $result;
    }
    
    /**
     * Проверяет доступ к файлам, приложенным в задании и в информации для оценивающих
     * {@inheritDoc}
     * @see question_definition::check_file_access()
     */
    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        $arr = explode('_', $filearea);
        $key = array_pop($arr);
        if ($component == 'question' && $filearea == 'response_attachments_' . $key)
        {
            // Response attachments visible if the question has them.
            return $this->attachments[$key] != 0;
        
        } else if ($component == 'question' && $filearea == 'response_answer_' . $key)
        {
            // Response attachments visible if the question has them.
            return $this->responseformat[$key] === 'editorfilepicker';
        
        } else if ($component == 'qtype_otmultiessay' && $filearea == 'graderinfo_' . $key)
        {
            return $options->manualcomment;
        
        } else
        {
            return parent::check_file_access($qa, $options, $component,
                $filearea, $args, $forcedownload);
        }
    }
}