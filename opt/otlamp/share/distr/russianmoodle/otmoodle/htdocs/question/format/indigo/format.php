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
 * Формат импорта вопросов из приложения Indigo.
 *
 * @package    qformat
 * @subpackage indigo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

/**
 * Правила оформления файла импорта
 * Блоки вопросов отделяются друг от друга пустыми строками. 
 * В первой строке располагается текст вопроса. Содержимое последующих строк зависит от типа вопроса.
 * 
 * 1. Выбор одного варианта ответа. Правильный ответ помечается символом звездочка *
 * 2. Выбор нескольких вариантов ответа. Правильные ответы помечаются символом решетка #
 * 3. Ввод ответа с клавиатуры. Правильный ответ обрамляется в квадратные скобки [ответ]. 
 *    Тип ввода определяется автоматически (числовой или текстовый). 
 *    Для чисел с плавающей точкой в качестве разделителя дробной части может использоваться точка или запятая.
 * 4. Установка соответствия. В каждой строке указывается пара «фиксированная строка = вариант ответа». 
 *    Если варианты ответа повторяются, то они будут считаться одним вариантом ответа. 
 *    Если необходимо, чтобы число вариантов ответа было больше числа фиксированных строк, 
 *    то необходимо использовать ввод оставшихся вариантов ответа без фиксированных строк: «= вариант ответа».
 * 5. Расстановка в нужном порядке. 
 * Элементы упорядочивания размещаются в правильной последовательности без использования каких-то специальных символов.
 * 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qformat_indigo extends qformat_default 
{

    public function provide_import() 
    {
        return true;
    }

    public function provide_export() 
    {
        return false;
    }

    public function export_file_extension() 
    {
        return '.txt';
    }

    protected function commentparser($answer, $defaultformat) 
    {
        $ans = $this->parse_text_with_format(trim($answer), $defaultformat);
        $feedback = array('text' => '', 'format' => $defaultformat, 'files' => array());
        return array($ans, $feedback);
    }

    protected function escapedchar_pre($string) 
    {
        // Replaces escaped control characters with a placeholder BEFORE processing.

        $escapedcharacters = array("\\:",    "\\#",    "\\=",    "\\{",    "\\}",    "\\~",    "\\n"  );
        $placeholders      = array("&&058;", "&&035;", "&&061;", "&&123;", "&&125;", "&&126;", "&&010");

        $string = str_replace("\\\\", "&&092;", $string);
        $string = str_replace($escapedcharacters, $placeholders, $string);
        $string = str_replace("&&092;", "\\", $string);
        return $string;
    }

    protected function escapedchar_post($string) 
    {
        // Replaces placeholders with corresponding character AFTER processing is done.
        $placeholders = array("&&058;", "&&035;", "&&061;", "&&123;", "&&125;", "&&126;", "&&010");
        $characters   = array(":",     "#",      "=",      "{",      "}",      "~",      "\n"  );
        $string = str_replace($placeholders, $characters, $string);
        return $string;
    }

    protected function check_answer_count($min, $answers, $text) 
    {
        $countanswers = count($answers);
        if( $countanswers < $min ) 
        {
            $this->error(get_string('importminerror', 'qformat_indigo'), $text);
            return false;
        }

        return true;
    }

    protected function parse_text_with_format($text, $defaultformat = FORMAT_MOODLE) 
    {
        $result = array(
            'text' => $text,
            'format' => $defaultformat,
            'files' => array(),
        );
        if (strpos($text, '[') === 0) {
            $formatend = strpos($text, ']');
            $result['format'] = $this->format_name_to_const(mb_substr($text, 1, $formatend - 1));
            if ($result['format'] == -1) {
                $result['format'] = $defaultformat;
            } else {
                $result['text'] = mb_substr($text, $formatend + 1);
            }
        }
        $result['text'] = trim($this->escapedchar_post($result['text']));
        return $result;
    }

    public function readquestion($lines) 
    {
        global $CFG;
        // Given an array of lines known to define a question in this format, this function
        // converts it into a question object suitable for processing and insertion into Moodle.

        $questiontext = $answertext = '';
        $answers = [];
        $question = $this->defaultquestion();
        $comment = null;
        
        // REMOVED COMMENTED LINES and IMPLODE.
        foreach($lines as $key => $line) 
        {
            $line = trim($line);
        }

        $text = trim(implode("\n", $lines));

        if( $text == '' ) 
        {
            return false;
        }

        // Substitute escaped control characters with placeholders.
        $text = $this->escapedchar_pre($text);

        // Question name parser.
        if( isset($lines[0]) )
        {
            $questionname = $lines[0];
            $question->name = $questiontext = $this->clean_question_name($this->escapedchar_post($questionname));
            $answertext = trim(mb_substr($text, mb_strlen($lines[0]))); // Remove name from text.
        } else 
        {
            $question->name = false;
        }

        if( count($lines) <= 1 )
        {
            $this->error(get_string('emptyanswerserror', 'qformat_indigo'), $text);
            return false;
        }

        // Get questiontext format from questiontext.
        $text = $this->parse_text_with_format($questiontext);
        $question->questiontextformat = $text['format'];
        $question->questiontext = $text['text'];

        // Set question name if not already set.
        if( $question->name === false ) 
        {
            $question->name = $this->create_default_question_name($question->questiontext, get_string('questionname', 'question'));
        }

        // Determine question type.
        $question->qtype = null;

        // Give plugins first try.
        // Plugins must promise not to intercept standard qtypes
        // MDL-12346, this could be called from lesson mod which has its own base class =(.
        if (method_exists($this, 'try_importing_using_qtypes')
                && ($tryquestion = $this->try_importing_using_qtypes($lines, $question, $answertext))) {
            return $tryquestion;
        }
        
        unset($lines[0]);

        if( ! empty($lines) )
        {
            $match = true;
            foreach($lines as $line)
            {
                if( $line{0} == '*' )
                {
                    $question->qtype = 'multichoice';
                    $question->single = 1;
                    $match = false;
                    break;
                } elseif( $line{0} == '#' )
                {
                    $question->qtype = 'multichoice';
                    $question->single = 0;
                    $match = false;
                    break;
                } elseif( $line{0} == '[' && mb_substr($line, -1, 1) == ']' )
                {
                    $question->qtype = 'shortanswer';
                    $match = false;
                    break;
                } elseif( strpos($line, '= ') !== false )
                {
                    $match = $match && true;
                } else
                {
                    $match = false;
                }
            }
            if( ! isset($question->qtype) )
            {
                if( $match )
                {
                    $question->qtype = 'match';
                } else
                {
                    $question->qtype = 'ordering';
                }
            }
        }

        if( ! isset($question->qtype) ) 
        {
            $indigoqtypenotset = get_string('indigoqtypenotset', 'qformat_indigo');
            $this->error($indigoqtypenotset, $text);
            return false;
        }

        switch ($question->qtype) 
        {
            case 'multichoice':
                $question = $this->add_blank_combined_feedback($question);

                if( empty($question->single) )
                {
                    $countcorrectanswers = 0;
                    foreach($lines as $line)
                    {
                        if( $line{0} == '#' )
                        {
                            $countcorrectanswers++;
                        }
                    }
                }
                $answers = array_merge($answers, $lines);
                $countanswers = count($answers);

                if( ! $this->check_answer_count(2, $answers, $text) ) 
                {
                    return false;
                }

                foreach($answers as $key => $answer) 
                {

                    // Determine answer weight.
                    if( $answer[0] == '*' ) 
                    {
                        $answerweight = 1;
                        $answer = mb_substr($answer, 1);

                    } elseif( $answer[0] == '#' ) 
                    {// Check for properly formatted answer weight.
                        $answerweight = 1/$countcorrectanswers;
                        $answer = mb_substr($answer, 1);
                    } else 
                    {// Default, i.e., wrong answer.
                        $answerweight = 0;
                    }
                    list($question->answer[$key], $question->feedback[$key]) =
                            $this->commentparser($answer, $question->questiontextformat);
                    $question->fraction[$key] = $answerweight;
                }
                
                return $question;

            case 'match':
                $question = $this->add_blank_combined_feedback($question);

                $answers = array_merge($answers, $lines);

                if( ! $this->check_answer_count(2, $answers, $text) ) 
                {
                    return false;
                }

                foreach($answers as $key => $answer) 
                {
                    if( mb_strpos($answer, "= " ) === false) 
                    {
                        $this->error(get_string('indigomatchingformat', 'qformat_indigo'), $answer);
                        return false;
                    }

                    $marker = mb_strpos($answer, '= ');
                    $question->subquestions[$key] = $this->parse_text_with_format(
                            mb_substr($answer, 0, $marker), $question->questiontextformat);
                    $question->subanswers[$key] = trim($this->escapedchar_post(
                            mb_substr($answer, $marker + 2)));
                }
                
                return $question;

            case 'shortanswer':
                // Shortanswer question.
                $answers = array_merge($answers, $lines);
                $answer = reset($answers);
                $answer = mb_substr($answer, 1, mb_strlen($answer) - 2);

                if( ! $this->check_answer_count(1, $answers, $text) ) 
                {
                    return false;
                }

                // Answer weight.
                $answerweight = 1;
                
                list($answer, $question->feedback[0]) = $this->commentparser(
                    $answer, $question->questiontextformat);
                
                $question->answer[0] = $answer['text'];
                $question->fraction[0] = $answerweight;
                
                return $question;
                
            case 'ordering':
                if( ! $this->is_qtype_ordering_installed() )
                {
                    return false;
                }
                
                require_once($CFG->dirroot . '/question/type/ordering/question.php');
                
                $answers = array_merge($answers, $lines);
                
                if( ! $this->check_answer_count(2, $answers, $text) )
                {
                    return false;
                }
                
                foreach($answers as $key => $answer)
                {
                    $question->answer[$key] = $answer;
                    $question->answerformat[$key] = FORMAT_MOODLE;
                    $question->fraction[$key] = 1; // Will be reset later in save_question_options().
                    $question->feedback[$key] = '';
                    $question->feedbackformat[$key] = FORMAT_MOODLE;;
                }
                $question->layouttype = qtype_ordering_question::LAYOUT_VERTICAL;
                $question->gradingtype = qtype_ordering_question::GRADING_ALL_OR_NOTHING;
                $question->selecttype = qtype_ordering_question::SELECT_ALL;
                $question->showgrading = 1;
                $question->selectcount = 0;
                $question->numberingstyle = 'none';
                
                // Check that the required feedback fields exist.
                $this->check_ordering_combined_feedback($question);
                
                return $question;

            default:
                $this->error(get_string('indigonovalidquestion', 'qformat_indigo'), $text);
                return false;
        }
    }

    /**
     * @param int $format one of the FORMAT_ constants.
     * @return string the corresponding name.
     */
    protected function format_name_to_const($format) 
    {
        if( $format == 'moodle' ) 
        {
            return FORMAT_MOODLE;
        } else if( $format == 'html' ) 
        {
            return FORMAT_HTML;
        } else if( $format == 'plain' ) 
        {
            return FORMAT_PLAIN;
        } else if( $format == 'markdown' ) 
        {
            return FORMAT_MARKDOWN;
        } else {
            return -1;
        }
    }
    
    /**
     * return an "empty" question
     * Somewhere to specify question parameters that are not handled
     * by import but are required db fields.
     * This should not be overridden.
     * @return object default question
     */
    protected function defaultquestion() 
    {
        global $CFG;
        static $defaultshuffleanswers = null;
        if (is_null($defaultshuffleanswers)) {
            $defaultshuffleanswers = get_config('quiz', 'shuffleanswers');
        }
    
        $question = new stdClass();
        $question->shuffleanswers = $defaultshuffleanswers;
        $question->defaultmark = 1;
        $question->image = "";
        $question->usecase = 0;
        $question->multiplier = array();
        $question->questiontextformat = FORMAT_MOODLE;
        $question->generalfeedback = '';
        $question->generalfeedbackformat = FORMAT_MOODLE;
        $question->correctfeedback = '';
        $question->partiallycorrectfeedback = '';
        $question->incorrectfeedback = '';
        $question->answernumbering = 'abc';
        $question->penalty = 0.0000000;
        $question->length = 1;
    
        // this option in case the questiontypes class wants
        // to know where the data came from
        $question->export_process = true;
        $question->import_process = true;
    
        return $question;
    }
    
    /**
     * Check that the required feedback fields exist
     *
     * @param object $question
     */
    private function check_ordering_combined_feedback(&$question) 
    {
        foreach(['correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback'] as $field) 
        {
            if (empty($question->$field)) {
                $question->$field = ['text' => '', 'format' => FORMAT_MOODLE, 'itemid' => 0, 'files' => null];
            }
        }
    }
    
    /**
     * Проверить установлен ли плагин ordering
     *
     * @return boolean
     */
    private function is_qtype_ordering_installed()
    {
        $installlist = core_plugin_manager::instance()->get_installed_plugins('qtype');
        if ( array_key_exists('ordering', $installlist) )
        {
            return true;
        }
    
        return false;
    }
}
