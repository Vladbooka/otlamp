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
 * Блок объединения отчетов. Класс формирования данных по модулю теста.
 * 
 * @package    block
 * @subpackage reports_union
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');
require_once($CFG->dirroot . '/mod/quiz/report/default.php');
require_once($CFG->dirroot . '/mod/quiz/report/attemptsreport.php');
require_once($CFG->dirroot . '/mod/quiz/report/responses/report.php');
require_once($CFG->dirroot . '/mod/quiz/report/responses/responses_options.php');
require_once($CFG->dirroot . '/mod/quiz/report/responses/responses_form.php');
require_once($CFG->dirroot . '/mod/quiz/report/responses/last_responses_table.php');
require_once($CFG->dirroot . '/mod/quiz/report/responses/first_or_all_responses_table.php');

class report_mods_data_quiz extends quiz_attempts_report 
{
    /**
     * Сформировать данные
     * 
     * @param id модуля теста $cmid
     * @param unknown $report
     */
    public function add_xls_data($cmid, &$report)
    {
        global $DB;
        if ( ! $cm = get_coursemodule_from_id('quiz', $cmid))
        {
            return $report;
        }
        if ( ! $course = $DB->get_record('course', array('id' => $cm->course))) {
            return $report;
        }
        if ( ! $quiz = $DB->get_record('quiz', array('id' => $cm->instance))) {
            return $report;
        }
        
        // Получение вопросов
        $questions = quiz_report_get_significant_questions($quiz);
        
        // Получение данных об инициализации отчета
        list($currentgroup, $students, $groupstudents, $allowed) =
            $this->init('responses', 'quiz_responses_settings_form', $quiz, $cm, $course);
        
        // Опции отчета
        $options = new quiz_responses_options('responses', $quiz, $cm, $course);
        $options->showqtext = 1;
        $options->showresponses = 1;
        $options->showright = 1;
        $options->attempts = self::ALL_WITH;
        
        //$groupmode = groups_get_activity_groupmode($cm);
        $hasquestions = quiz_has_questions($quiz->id);
        $hasstudents = $students && (!$currentgroup || $groupstudents);
        
        // Формирование отчета
        $table = new report_mods_data_quiz_table($quiz, $this->context, $this->qmsubselect,
                $options, $groupstudents, $students, $questions, $options->get_url());
        $table->is_downloading('excel');
        if ( $hasquestions && $hasstudents )
        {
            // Добавление шапки
            if ( ! isset($report['header1'][$cmid]) )
            {// Добавление названия экземпляра в заголовок
            $report['header1'][$cmid] = $cm->name;
            }
            // Подготовка массива строк для полей
            $report['header2'][$cmid] = [];
            
            $columns = [];
            $headers = [];
            $columns[] = 'userid';
            $headers[] = 'userid';
            $columns[] = 'attempt';
            $headers[] = 'attempt';
            $this->add_user_columns($table, $columns, $headers);
            $this->add_state_column($columns, $headers);
            $this->add_time_columns($columns, $headers);
            $this->add_grade_columns($quiz, $options->usercanseegrades, $columns, $headers);
            foreach ($questions as $id => $question) {
                $columns[] = 'question' . $id;
                $headers[] = get_string('questionx', 'question', $question->number);
                $columns[] = 'response' . $id;
                $headers[] = get_string('responsex', 'quiz_responses', $question->number);
                $columns[] = 'right' . $id;
                $headers[] = get_string('rightanswerx', 'quiz_responses', $question->number);
            }
            
            $reportdata = $table->get_reportdata($columns, $headers , $allowed);
            
            if ( ! empty($reportdata) )
            {// Данные получены
                foreach ( $reportdata as $item )
                {
                    if ( ! isset($item['userid']) || empty($item['userid']) )  
                    { 
                        $userid = 0;
                    } else
                    {
                        $userid = $item['userid'];
                    }
                    if ( ! isset($report['users'][$userid]) )
                    {// Не создан слот для пользователя
                        $report['users'][$userid] = [];
                    }
                    if ( ! isset($report['users'][$userid][$cmid]) )
                    {// Не создан слот для модуля
                        $report['users'][$userid][$cmid] = [];
                    }
                    // Добавление данных по ответу пользователя
                    $report['users'][$userid][$cmid][$item['attempt']] = $item;
                    
                    // Получение разницы между массивами
                    $diff = array_diff_key($item, $report['header2'][$cmid]);
                    unset($diff['userid'], $diff['attempt']);
                    if ( ! empty($diff) )
                    {
                        $fieldkeys = array_flip($columns);
                        foreach ( $diff as $fieldname => $fieldval )
                        {
                            if ( isset($fieldkeys[$fieldname]) )
                            {// Идентификатор определен
                                $report['header2'][$cmid][$fieldname] = $headers[$fieldkeys[$fieldname]];
                            }
                        }
                    }
                }
            }
        }
        
        return $report;
    }
    
    public function display($quiz, $cm, $course) { return true; }
}

class report_mods_data_quiz_table extends quiz_last_responses_table 
{
    public function get_reportdata($columns, $headers , $allowed)
    {
        $reportdata = [];
        list($fields, $from, $where, $params) = $this->base_sql($allowed);
        $this->set_sql($fields, $from, $where, $params);
        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->setup = true;
        parent::query_db(0, false);
        
        $this->strtimeformat = str_replace(',', ' ', get_string('strftimedatetime'));
        if ($this->rawdata) 
        {// Есть данные
            foreach ($this->rawdata as $row) 
            {
                $reportdata[] = $this->format_row($row);
            }
        }
        
        return $reportdata;
    }
}