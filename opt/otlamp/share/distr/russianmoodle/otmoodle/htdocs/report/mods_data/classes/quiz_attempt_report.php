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
 * Класс для формирования формы с данными по попытке в pdf формате.
 *
 * @package    report_mods_data
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mods_data;

require_once($CFG->dirroot . '/report/mods_data/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/report/mods_data/classes/lib/mpdf-7.1.6/src/Mpdf.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->libdir . '/completionlib.php');

use dml_missing_record_exception;
use dml_multiple_records_exception;
use stdClass;
use html_writer;
use html_table;
use html_table_row;
use html_table_cell;
use quiz_attempt;
use context_module;
use Mpdf;
use \core\session\manager as session_manager;
use moodle_url;

defined('MOODLE_INTERNAL') || die;

class quiz_attempt_report
{
    /**
     * Объект попытки прохождения
     * @var stdClass
     */
    private $attempt;
    /**
     * Хранилище попыток прохождения
     * @var string
     */
    private $table = 'quiz_attempts';
    /**
     * Объект для работы с pdf
     * @var \Mpdf\Mpdf
     */
    private $pdf;
    /**
     * Разделитель для строк вида "ключ-значение"
     * @var string
     */
    private $delimiter = ':';
    /**
     * Объект dof_control
     * @var dof_control
     */
    private $dof;
    /**
     * Формат отображения времени
     * @var string
     */
    private $strtimeformat;
    /**
     * Формат отчета
     * @var string
     */
    private $format = 'pdf';
    /**
     * Готовый html-код отчета
     * @var string
     */
    private $html;
    /**
     * Стили для ячеек таблицы отчета
     * @var string
     */
    private $cellstyles;
    /**
     * Стили для заголовочных ячеек таблицы отчета
     * @var string
     */
    private $headercellstyles;
    /**
     * Имя файла для скачивания
     * @var string
     */
    private $filename;
    /**
     * Количество неправильных ответов
     * @var int
     */
    private $gradedwrong;
    /**
     * Объект курса
     * @var stdClass
     */
    private $course;
    /**
     * Объект модуля курса
     * @var stdClass
     */
    private $cm;
    /**
     * Отметка о выполнении элемента COMPLETION_INCOMPLETE|COMPLETION_COMPLETE|COMPLETION_UNKNOWN|COMPLETION_IGNORE
     * @var int
     */
    private $completionstate;
    /**
     * Объект пользователя с кастомными полями
     * @var stdClass
     */
    private $user;
    
    /**
     * Магический метод для получения приватных переменных
     * @param string $name
     * @return mixed|NULL
     */
    public function __get($name)
    {
        if( isset($this->$name) )
        {
            return $this->$name;
        }
        return null;
    }
    
    /**
     * Конструктор класса
     * @param int $id идентификатор попытки прохождения элемента
     * @param string $format формат запрашиваемого отчета
     * @return quiz_attempt_report|NULL
     */
    public function __construct($id, $format = 'pdf')
    {
        // Инициализация данных
        try
        {
            $attempt = $this->get_attempt($id);
        } catch(dml_missing_record_exception $e)
        {
            $a = new stdClass();
            $a->id = $id;
            debugging(get_string('attempt_not_found', 'report_mods_data', $a));
            return null;
        } catch(dml_multiple_records_exception $e)
        {
            debugging('Error: mdb->get_record() found more than one record!');
        }
        $this->attempt = $attempt;
        $this->quiz = $this->get_quiz();
        $this->dof = report_mods_data_get_dof();
        $this->strtimeformat = str_replace(',', ' ', get_string('strftimedatetime'));
        $this->format = $format;
        $this->headercellstyles = 'vertical-align: middle; border: 1px solid black; border-collapse: collapse; padding: 10px; text-align: center;';
        $this->cellstyles = 'vertical-align: middle; border: 1px solid black; border-collapse: collapse; padding: 10px;';
        $this->filename = 'quiz_attempt_report'.date('m.d.y').'.pdf';
        $this->gradedwrong = 0;
        list($this->course, $cminfo) = get_course_and_cm_from_instance($this->quiz->id, 'quiz');
        $this->cm = $cminfo->get_course_module_record(true);
        $this->completionstate = report_mods_data_get_attempt_completion($this->attempt->id, 'quiz', $this->course, $this->cm, $this->attempt->userid);
    }
    
    /**
     * Генерирует отчет
     */
    public function make_report()
    {
        $this->set_user();
        switch($this->format)
        {
            case 'html':
                $this->create_html();
                break;
            case 'pdf':
            default:
                $this->pdf = new \Mpdf\Mpdf();
                $this->create_pdf();
                break;
        }
    }
    
    /**
     * Отправляет pdf отчет
     */
    public function send_pdf()
    {
        if( isset($this->pdf) )
        {
            $this->pdf->Output($this->filename, 'D');
        }
    }
    
    /**
     * Отправляет html отчет
     * @return string
     */
    public function send_html()
    {
        return $this->html;
    }
    
    /**
     * Получить попытку прохождения элемента
     * @param int $id идентификатор попытки прохождения
     * @return mixed|stdClass|false
     */
    protected function get_attempt($id)
    {
        global $DB;
        return $DB->get_record($this->table, ['id' => $id], '*', MUST_EXIST);
    }
    
    /**
     * Создать pdf отчет
     */
    protected function create_pdf()
    {
        $this->create_html();
        session_manager::write_close();
        $this->pdf->WriteHTML($this->html);
    }
    
    /**
     * Создать html отчет
     */
    protected function create_html()
    {
        $html = '';
        if( $this->format == 'html' )
        {
            $url = new moodle_url('/report/mods_data/quiz_attempt.php', ['id' => $this->attempt->id, 'format' => 'pdf']);
            $html .= html_writer::link($url, get_string('quiz_attempt_pdf_report_link_title', 'report_mods_data'), ['class' => 'btn btn-primary']);
            $url = new moodle_url('/report/mods_data/index.php', ['id' => $this->course->id]);
            $html .= html_writer::link($url, get_string('back_to_main_report_link_title', 'report_mods_data'), ['class' => 'btn btn-primary']);
        }
        $html .= $this->get_profile_info();
        $html .= $this->get_quiz_info();
        $html .= $this->get_attempt_table();
        $html .= $this->get_quiz_results();
        $html .= $this->get_report_bottom();
        $this->html = $html;
    }
    
    /**
     * Установить пользователя
     */
    protected function set_user()
    {
        if( isset($this->attempt) )
        {
            $this->user = $this->get_user();
        }
    }
    
    /**
     * Получить объект пользователя с кастомными полями
     * @return mixed|stdClass|false|NULL
     */
    protected function get_user()
    {
        global $DB;
        if( $user = $DB->get_record('user', ['id' => $this->attempt->userid]) )
        {
            profile_load_custom_fields($user);
            return $user;
        }
        return null;
        
    }
    
    /**
     * Получить секцию отчета с данными по пользователю
     * @return string
     */
    private function get_profile_info()
    {
        $content = '';
        if( ! is_null($this->user) )
        {
            $content .= html_writer::tag('h2', fullname($this->user));
            $fields = get_config('report_mods_data', 'quiz_attempt_user_fields');
            if( ! empty($fields) )
            {
                $fields = explode(',', $fields);
                foreach($fields as $field)
                {
                    if( strpos($field, 'profile_field_') === false )
                    {
                        $content .= html_writer::tag('p', get_user_field_name($field) . $this->delimiter . ' ' . $this->user->$field);
                    } else
                    {
                        $shortname = substr($field, 14);
                        $name = $this->dof->modlib('ama')->user(false)->get_user_custom_field($shortname)->name;
                        if( isset($this->user->profile[$shortname]) )
                        {
                            $content .= html_writer::tag('p', $name . $this->delimiter . ' ' . $this->user->profile[$shortname]);
                        } else
                        {
                            $content .= html_writer::tag('p', $name . $this->delimiter);
                        }
                    }
                     
                }
            }
        }
        return $content;
    }
    
    /**
     * Получить секция отчет с данными по элементу
     * @return string
     */
    private function get_quiz_info()
    {
        $content = '';
        $content .= html_writer::tag('p', get_string('quiz_name_caption', 'report_mods_data') . $this->delimiter . ' ' . $this->quiz->name);
        $content .= html_writer::tag('p', get_string('attempt_date_caption', 'report_mods_data') . $this->delimiter . ' ' . userdate($this->attempt->timestart, $this->strtimeformat));
        return $content;
    }
    
    /**
     * Получить объект элемента
     * @return mixed|stdClass|false
     */
    private function get_quiz()
    {
        global $DB;
        return $DB->get_record('quiz', ['id' => $this->attempt->quiz]);
    }
    
    /**
     * Получить таблицу с данными по попытке прохождения элемента
     * @return string
     */
    private function get_attempt_table()
    {
        global $PAGE;
        $table = new html_table();
        $table->attributes = ['class' => 'quiz_attempt_individual_report', 'style' => 'border-collapse: collapse; margin-bottom: 20px;'];
        $table->data[] = $this->get_attempt_table_caption();
        $quizattempt = new quiz_attempt($this->attempt, $this->quiz, $this->cm, $this->course);
        $slots = $quizattempt->get_slots();
        $count = 0;
        foreach($slots as $slot)
        {
            $count++;
            $tablerow = new html_table_row();
            $tablerow->style = 'border-collapse: collapse;';
            $tablecell = new html_table_cell($count);
            $tablecell->style = $this->cellstyles;
            $tablerow->cells[] = $tablecell;
            $options = $quizattempt->get_display_options(true);
            $options->context = context_module::instance($this->cm->id);
            $quba = \question_engine::load_questions_usage_by_activity($quizattempt->get_uniqueid());
            $qa = $quba->get_question_attempt($slot);
            $qtoutput = $qa->get_question()->get_renderer($PAGE);
            $q = $qtoutput->formulation_and_controls($qa, $options);
            //замена ссылок на картинки (если они картинки)
            $q = preg_replace_callback("/<a\shref=\"([^\"]*)\">(.*)<\/a>/siU", function ($matches) {
                $fgc_context = stream_context_create([
                    'http' => [
                        'header' => 'Cookie: ' . $_SERVER['HTTP_COOKIE'] . "\r\n"
                    ]
                ]);
                //убедимся, что ссылка является картинкой
                $image = file_get_contents($matches[1], false, $fgc_context);
                if(imagecreatefromstring((string)$image))
                {
                    return "<img src=\"".$matches[1]."\" alt=\"\" title=\"\" />";
                }
                return $matches[0];
            }, $q);
            preg_match_all('#<div\sclass="qtext">(.*)<\/div>#Uis', $q, $matches);
            $tablecell = new html_table_cell();
            $tablecell->style = $this->cellstyles;
            if( ! empty($matches) )
            {
                $tablecell->text = '';
                foreach($matches[0] as $match)
                {
                    $tablecell->text .= $match;
                }
            } else 
            {
                $tablecell->text = '';
            }
            $tablerow->cells[] = $tablecell;
            preg_match_all('#<div\sclass="ablock">(.*)<\/div>#is', $q, $matches);
            $tablecell = new html_table_cell();
            $tablecell->style = $this->cellstyles;
            if( ! empty($matches) )
            {
                $tablecell->text = '';
                foreach($matches[0] as $match)
                {
                    $tablecell->text .= $match;
                }
                
            } else
            {
                $tablecell->text = '';
            }
            $tablerow->cells[] = $tablecell;
            $tablecell = new html_table_cell($quba->get_question_state_string($slot, true));
            $tablecell->style = $this->cellstyles;
            $tablerow->cells[] = $tablecell;
            $table->data[] = $tablerow;
            
            if( (string)$quba->get_question_state($slot) == 'gradedwrong' )
            {
                $this->gradedwrong++;
            }
        }
        return html_writer::table($table);
    }
    
    /**
     * Получить заголовки таблицы с данными по попытке прохождения элемента
     * @return html_table_row
     */
    private function get_attempt_table_caption()
    {
        $tablerow = new html_table_row();
        $tablecell = new html_table_cell(get_string('question_number_table_caption', 'report_mods_data'));
        $tablecell->style = $this->headercellstyles;
        $tablerow->cells[] = $tablecell;
        $tablecell = new html_table_cell(get_string('question_name_table_caption', 'report_mods_data'));
        $tablecell->style = $this->headercellstyles;
        $tablerow->cells[] = $tablecell;
        $tablecell = new html_table_cell(get_string('question_answer_table_caption', 'report_mods_data'));
        $tablecell->style = $this->headercellstyles;
        $tablerow->cells[] = $tablecell;
        $tablecell = new html_table_cell(get_string('question_result_table_caption', 'report_mods_data'));
        $tablecell->style = $this->headercellstyles;
        $tablerow->cells[] = $tablecell;
        return $tablerow;
    }
    
    /**
     * Получить секция отчета со сводными данными по результатам прохождения
     * @return string
     */
    private function get_quiz_results()
    {
        $html = '';
        $a = new stdClass();
        $a->allowedmistakes = (int)get_config('report_mods_data', 'allowedmistakes');
        $a->admittedmistakes = $this->gradedwrong;
//         if( $a->allowedmistakes != -1 )
//         {
//             $html .= html_writer::tag('p', get_string('allowedmistakes', 'report_mods_data', $a));
//         }
        $html .= html_writer::tag('p', get_string('admittedmistakes', 'report_mods_data', $a));
        switch($this->completionstate)
        {
            case ATTEMPT_COMPLETION_COMPLETE:
                $html .= html_writer::tag('p', get_string('completionstate_complete', 'report_mods_data'));
                break;
            case ATTEMPT_COMPLETION_UNKNOWN:
            case ATTEMPT_COMPLETION_IGNORE:
                $html .= html_writer::tag('p', get_string('completionstate_unknown', 'report_mods_data'));
                break;
            case ATTEMPT_COMPLETION_INCOMPLETE:
            default:
                $html .= html_writer::tag('p', get_string('completionstate_incomplete', 'report_mods_data'));
                break;
        }

        return $html;
    }
    
    /**
     * Получить подвал отчета
     * @return string
     */
    private function get_report_bottom()
    {
        $a = new stdClass();
        $a->fullname = fullname($this->user);
        return get_string('quiz_attempt_report_bottom', 'report_mods_data', $a);
    }
}