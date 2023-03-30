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


require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');
require_once($CFG->dirroot . '/mod/quiz/renderer.php');

require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');
require_once($CFG->dirroot . '/mod/quiz/report/default.php');
require_once($CFG->dirroot . '/mod/quiz/report/attemptsreport.php');
require_once($CFG->dirroot . '/mod/quiz/report/responses/report.php');
require_once($CFG->dirroot . '/mod/quiz/report/responses/responses_options.php');
require_once($CFG->dirroot . '/mod/quiz/report/responses/responses_form.php');
require_once($CFG->dirroot . '/mod/quiz/report/responses/last_responses_table.php');
require_once($CFG->dirroot . '/mod/quiz/report/responses/first_or_all_responses_table.php');

require_once($CFG->dirroot . '/report/mods_data/locallib.php');

class report_mods_data_quiz extends quiz_attempts_report 
{
    /**
     * string constant used for the options, means best attempts of users
     * @var string
     */
    const BEST = 'best';
    
    /**
     * Дата начала периода, за который необходимо выбрать попытки прохождения
     * @var int timestamp
     */
    protected $startdate;
    
    /**
     * Дата конца периода, за который необходимо выбрать попытки прохождения
     * @var int timestamp
     */
    protected $enddate;
    
    /**
     * Add all the grade and feedback columns, if applicable, to the $columns
     * and $headers arrays.
     * @param object $quiz the quiz settings.
     * @param bool $usercanseegrades whether the user is allowed to see grades for this quiz.
     * @param array $columns the list of columns. Added to.
     * @param array $headers the columns headings. Added to.
     * @param bool $includefeedback whether to include the feedbacktext columns
     */
    protected function add_grade_columns($quiz, $usercanseegrades, &$columns, &$headers, $includefeedback = true) {
        if ($usercanseegrades) {
            $columns[] = 'sumgrades';
            $headers[] = get_string('grade', 'quiz') . '/' .
                quiz_format_grade($quiz, $quiz->grade);
        }
    
        $columns[] = 'feedbacktext';
        $headers[] = get_string('feedback', 'quiz');
    }

    /**
     * Сформировать данные
     * 
     * @param id модуля теста $cmid
     * @param unknown $report
     */
    public function add_subreport($cmid, &$report, $exportformat, $users = [], $completion = 'all', $attempts = 'all', $startdate = null, $enddate = null, $attemptsinperiod = null)
    {
        global $CFG,$DB,$PAGE;
        
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
        $options = new report_mods_data_quiz_responses_options('responses', $quiz, $cm, $course);
        $options->showqtext = 1;
        $options->showresponses = 1;
        $options->showright = 1;
        switch($attempts)
        {
            case 'best':
                $options->attempts = self::BEST;
                break;
            case 'all':
            default:
                $options->attempts = self::ALL_WITH;
                break;
        }
        $options->startdate = $startdate;
        $options->enddate = $enddate;
        $options->attemptsinperiod = $attemptsinperiod;
        
        
        //$groupmode = groups_get_activity_groupmode($cm);
        $hasquestions = quiz_has_questions($quiz->id);
        $hasstudents = $students && (!$currentgroup || $groupstudents);
        
        // Формирование отчета
        $table = new report_mods_data_quiz_table($quiz, $this->context, $this->qmsubselect,
                $options, $groupstudents, $students, $questions, $options->get_url());
        $types = [
            'xls' => 'excel',
            'html' => null,
            'pdf' => null
        ];
        $table->is_downloading($types[$exportformat]);
        if ( $hasquestions && ($hasstudents || $options->attempts == self::ALL_WITH || $options->attempts == self::BEST))
        {
            // Добавление шапки
            if ( ! isset($report['header1'][$cmid]) )
            {// Добавление названия экземпляра в заголовок
                $report['header1'][$cmid] = [
                    'coursename' => $course->fullname,
                    'name' => $cm->name
                ];
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
            $columns[] = 'attempt_completion';
            $headers[] = get_string('attempt_completion_header', 'report_mods_data');
            foreach ($questions as $id => $question) {
                $columns[] = 'question' . $id;
                $headers[] = get_string('questionx', 'question', $question->number);
                if ( $exportformat == 'xls' )
                {
                    $columns[] = 'response' . $id;
                    $headers[] = get_string('responsex', 'quiz_responses', $question->number);
                    $columns[] = 'right' . $id;
                    $headers[] = get_string('rightanswerx', 'quiz_responses', $question->number);
                }
                $columns[] = 'state' . $id;
                $headers[] = get_string('statex', 'report_mods_data', $question->number);
            }

            $reportdata = $table->get_reportdata($columns, $headers , $allowed);
            if ( ! empty($reportdata) )
            {// Данные получены
                $max = [];
                foreach ( $reportdata as $attemptitem )
                {
                    // Проверка, что пользователь не удален
                    $user = $DB->get_record('user', ['id' => $attemptitem['userid'], 'deleted' => 0]);
                    
                    list($course, $cm) = get_course_and_cm_from_cmid($cmid);
                    $completionstate = report_mods_data_get_attempt_completion($attemptitem['attempt'], 'quiz', $course, $cm, $attemptitem['userid']);
                    switch($completionstate)
                    {
                        case ATTEMPT_COMPLETION_COMPLETE:
                            if( $completion == 'notcompleted' )
                            {
                                continue;
                            }
                            break;
                        case ATTEMPT_COMPLETION_UNKNOWN:
                            break;
                        case ATTEMPT_COMPLETION_IGNORE:
                            continue;
                            break;
                        case ATTEMPT_COMPLETION_INCOMPLETE:
                        default:
                            if( $completion == 'completed' )
                            {
                                continue;
                            }
                            break;
                    }
                    
                    if( ! empty($user) && ( (! empty($users) && in_array($attemptitem['userid'], $users)) || empty($users) ) )
                    {//если указан userid, то только по нему собираем данные
                        if( $exportformat != 'xls' )
                        {
                            foreach($attemptitem as $field=>$value)
                            {
                                if( strpos($field, 'question') !== false )
                                {
                                    $slot = substr($field, 8);
                                    $attemptobj = quiz_attempt::create($attemptitem['attempt']);
                                    $options = $attemptobj->get_display_options(true);
                                    $options->context = context_module::instance($cmid);
                                    
                                    $quba = question_engine::load_questions_usage_by_activity($attemptobj->get_uniqueid());
                                    $qa = $quba->get_question_attempt($slot);
                                    $qtoutput = $qa->get_question()->get_renderer($PAGE);
                                    $q = $qtoutput->formulation_and_controls($qa, $options);
                                    
                                    //замена ссылок на картинки (если они картинки)
                                    $attemptitem[$field] = preg_replace_callback("/<a\shref=\"([^\"]*)\">(.*)<\/a>/siU", function ($matches) {
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
                                }
                            }
                        }
                    
                        $userid_report = $attemptitem['userid'];
                        if ( ! isset($report['users'][$userid_report]) )
                        {// Не создан слот для пользователя
                            $report['users'][$userid_report] = [];
                        }
                        if ( ! isset($report['users'][$userid_report][$course->id]) )
                        {// Не создан слот для модуля
                            $report['users'][$userid_report][$course->id] = [
                                'info' => $course,
                                'cms' => []
                            ];
                        }
                        if( ! isset($max[$attemptitem['userid']][$cmid]) )
                        {
                            $max[$attemptitem['userid']][$cmid] = new stdClass();
                            $max[$attemptitem['userid']][$cmid]->sumgrades = null;
                        }
                        if( $attemptitem['sumgrades'] > $max[$attemptitem['userid']][$cmid]->sumgrades )
                        {
                            $max[$attemptitem['userid']][$cmid]->sumgrades = $attemptitem['sumgrades'];
                            $max[$attemptitem['userid']][$cmid]->id = $attemptitem['attempt'];
                            $max[$attemptitem['userid']][$cmid]->attempt = $attemptitem;
                        }
                        switch($completionstate)
                        {
                            case ATTEMPT_COMPLETION_COMPLETE:
                                $report['users'][$userid_report][$course->id]['cms']['completed'][$cmid]['all'][$attemptitem['attempt']] = $attemptitem;
                                $report['users'][$userid_report][$course->id]['cms']['completed'][$cmid]['best'] = [$max[$attemptitem['userid']][$cmid]->id => $max[$attemptitem['userid']][$cmid]->attempt];
                                $report['users'][$userid_report][$course->id]['cms']['all'][$cmid]['all'][$attemptitem['attempt']] = $attemptitem;
                                $report['users'][$userid_report][$course->id]['cms']['all'][$cmid]['best'] = [$max[$attemptitem['userid']][$cmid]->id => $max[$attemptitem['userid']][$cmid]->attempt];
                                break;
                            case ATTEMPT_COMPLETION_INCOMPLETE:
                                $report['users'][$userid_report][$course->id]['cms']['notcompleted'][$cmid]['all'][$attemptitem['attempt']] = $attemptitem;
                                $report['users'][$userid_report][$course->id]['cms']['notcompleted'][$cmid]['best'] = [$max[$attemptitem['userid']][$cmid]->id => $max[$attemptitem['userid']][$cmid]->attempt];
                                $report['users'][$userid_report][$course->id]['cms']['all'][$cmid]['all'][$attemptitem['attempt']] = $attemptitem;
                                $report['users'][$userid_report][$course->id]['cms']['all'][$cmid]['best'] = [$max[$attemptitem['userid']][$cmid]->id => $max[$attemptitem['userid']][$cmid]->attempt];
                                break;
                            case ATTEMPT_COMPLETION_UNKNOWN:
                                $report['users'][$userid_report][$course->id]['cms']['completed'][$cmid]['all'][$attemptitem['attempt']] = $attemptitem;
                                $report['users'][$userid_report][$course->id]['cms']['completed'][$cmid]['best'] = [$max[$attemptitem['userid']][$cmid]->id => $max[$attemptitem['userid']][$cmid]->attempt];
                                $report['users'][$userid_report][$course->id]['cms']['notcompleted'][$cmid]['all'][$attemptitem['attempt']] = $attemptitem;
                                $report['users'][$userid_report][$course->id]['cms']['notcompleted'][$cmid]['best'] = [$max[$attemptitem['userid']][$cmid]->id => $max[$attemptitem['userid']][$cmid]->attempt];
                                $report['users'][$userid_report][$course->id]['cms']['all'][$cmid]['all'][$attemptitem['attempt']] = $attemptitem;
                                $report['users'][$userid_report][$course->id]['cms']['all'][$cmid]['best'] = [$max[$attemptitem['userid']][$cmid]->id => $max[$attemptitem['userid']][$cmid]->attempt];
                                break;
                        }
                        // Получение разницы между массивами
                        $diff = array_diff_key($attemptitem, $report['header2'][$cmid]);
                        //убираем ненужные поля и поля профиля (их вывод можно настроить не из этого отчета)
                        unset($diff['userid'], $diff['attempt'], $diff['lastname'], $diff['firstname'], $diff['institution'], $diff['department'], $diff['email']);
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
        }
        
        return $report;
    }
    
    public function display($quiz, $cm, $course) { return true; }
}

class report_mods_data_quiz_table extends quiz_last_responses_table 
{
    private $enablecron = null;
    
    /**
     * Constructor
     * @param object $quiz
     * @param context $context
     * @param string $qmsubselect
     * @param quiz_responses_options $options
     * @param array $groupstudents
     * @param array $students
     * @param array $questions
     * @param moodle_url $reporturl
     */
    public function __construct($quiz, $context, $qmsubselect, quiz_responses_options $options,
        $groupstudents, $students, $questions, $reporturl) 
    {
        $this->enablecron = get_config('report_mods_data', 'enablecron');
        parent::__construct($quiz, $context, $qmsubselect, $options,
            $groupstudents, $students, $questions, $reporturl);
    }
    
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
    
    /**
     * Generate the display of the start time column.
     * @param object $attempt the table row being output.
     * @return string HTML content to go inside the td.
     */
    public function col_timestart($attempt) {
        if( ! empty($this->enablecron) )
        {
            if ($attempt->attempt) {
                return $attempt->timestart;
            } else {
                return null;
            }
        } else 
        {
            if ($attempt->attempt) {
                return userdate($attempt->timestart, $this->strtimeformat);
            } else {
                return '-';
            }
        }
    }
    
    /**
     * Generate the display of the finish time column.
     * @param object $attempt the table row being output.
     * @return string HTML content to go inside the td.
     */
    public function col_timefinish($attempt) {
        if( ! empty($this->enablecron) )
        {
            if ($attempt->attempt && $attempt->timefinish) {
                return $attempt->timefinish;
            } else {
                return null;
            }
        } else 
        {
            if ($attempt->attempt && $attempt->timefinish) {
                return userdate($attempt->timefinish, $this->strtimeformat);
            } else {
                return '-';
            }
        }
    }
    
    public function other_cols($colname, $attempt) {
        if (preg_match('/^question(\d+)$/', $colname, $matches)) {
            return $this->data_col($matches[1], 'questionsummary', $attempt);
    
        } else if (preg_match('/^response(\d+)$/', $colname, $matches)) {
            return $this->data_col($matches[1], 'responsesummary', $attempt);
    
        } else if (preg_match('/^right(\d+)$/', $colname, $matches)) {
            return $this->data_col($matches[1], 'rightanswer', $attempt);
    
        } else if (preg_match('/^state(\d+)$/', $colname, $matches)) {
            $state = $this->data_col($matches[1], 'state', $attempt);
            $obj = question_state::$$state;
            return $obj->default_string($state);
        } else if (preg_match('/^attempt_completion$/', $colname, $matches)) {
            list($course, $cm_info) = get_course_and_cm_from_instance($attempt->quiz, 'quiz');
            $cm = $cm_info->get_course_module_record(true);
            $completion = report_mods_data_get_attempt_completion($attempt->attempt, 'quiz', $course, $cm, $attempt->userid);
            return report_mods_data_attempt_completion_string($completion);
        } else {
            return null;
        }
    }
    
    /**
     * Contruct all the parts of the main database query.
     * @param array $reportstudents list if userids of users to include in the report.
     * @return array with 4 elements ($fields, $from, $where, $params) that can be used to
     *      build the actual database query.
     */
    public function base_sql(\core\dml\sql_join $allowedstudentsjoins) {
        global $DB;
    
        $fields = 'DISTINCT ' . $DB->sql_concat('u.id', "'#'", 'COALESCE(quiza.attempt, 0)') . ' AS uniqueid,';
    
        if ($this->qmsubselect) {
            $fields .= "\n(CASE WHEN $this->qmsubselect THEN 1 ELSE 0 END) AS gradedattempt,";
        }
    
        $extrafields = get_extra_user_fields_sql($this->context, 'u', '',
            array('id', 'idnumber', 'firstname', 'lastname', 'picture',
                'imagealt', 'institution', 'department', 'email'));
            $allnames = get_all_user_name_fields(true, 'u');
            $fields .= '
                quiza.uniqueid AS usageid,
                quiza.id AS attempt,
                quiza.quiz,
                u.id AS userid,
                u.idnumber, ' . $allnames . ',
                u.picture,
                u.imagealt,
                u.institution,
                u.department,
                u.email' . $extrafields . ',
                quiza.state,
                quiza.sumgrades,
                quiza.timefinish,
                quiza.timestart,
                CASE WHEN quiza.timefinish = 0 THEN null
                     WHEN quiza.timefinish > quiza.timestart THEN quiza.timefinish - quiza.timestart
                     ELSE 0 END AS duration';
            // To explain that last bit, timefinish can be non-zero and less
            // than timestart when you have two load-balanced servers with very
            // badly synchronised clocks, and a student does a really quick attempt.
    
            // This part is the same for all cases. Join the users and quiz_attempts tables.
            $from = " {user} u";
            $from .= "\nLEFT JOIN {quiz_attempts} quiza ON
                                    quiza.userid = u.id AND quiza.quiz = :quizid";
            $params = array('quizid' => $this->quiz->id);
    
            if ($this->qmsubselect && $this->options->onlygraded) {
                $from .= " AND (quiza.state <> :finishedstate OR $this->qmsubselect)";
                $params['finishedstate'] = quiz_attempt::FINISHED;
            }
    
            switch ($this->options->attempts) {
                case quiz_attempts_report::ALL_WITH:
                    // Show all attempts, including students who are no longer in the course.
                    $where = 'quiza.id IS NOT NULL AND quiza.preview = 0';
                    break;
                case quiz_attempts_report::ENROLLED_WITH:
                    // Show only students with attempts.
                    $from .= "\n" . $allowedstudentsjoins->joins;
                    $where = "quiza.preview = 0 AND quiza.id IS NOT NULL AND " . $allowedstudentsjoins->wheres;
                    $params = array_merge($params, $allowedstudentsjoins->params);
                    break;
                case quiz_attempts_report::ENROLLED_WITHOUT:
                    // Show only students without attempts.
                    $from .= "\n" . $allowedstudentsjoins->joins;
                    $where = "quiza.id IS NULL AND " . $allowedstudentsjoins->wheres;
                    $params = array_merge($params, $allowedstudentsjoins->params);
                    break;
                case quiz_attempts_report::ENROLLED_ALL:
                    // Show all students with or without attempts.
                    $from .= "\n" . $allowedstudentsjoins->joins;
                    $where = "(quiza.preview = 0 OR quiza.preview IS NULL) AND " . $allowedstudentsjoins->wheres;
                    $params = array_merge($params, $allowedstudentsjoins->params);
                    break;
                case report_mods_data_quiz::BEST:
                    // Show only best attempts
                    $max = $ids = [];
                    $parameters = [$this->quiz->id];
                    $select = "quiz = ? AND preview = 0";
                    $attempts = $DB->get_records_select('quiz_attempts', $select, $parameters);
                    foreach($attempts as $attempt)
                    {
                        if( ! isset($max[$attempt->userid]) )
                        {
                            $max[$attempt->userid] = new stdClass();
                            $max[$attempt->userid]->sumgrades = null;
                            $max[$attempt->userid]->id = null;
                        }
                        if( $attempt->sumgrades > $max[$attempt->userid]->sumgrades ) 
                        {// По умолчанию будет последняя лучшая
                            $max[$attempt->userid]->sumgrades = $attempt->sumgrades;
                            $max[$attempt->userid]->id = $attempt->id;
                        }
                    }
                    if( ! empty($max) )
                    {
                        foreach($max as $ua)
                        {
                            $ids[] = $ua->id;
                        }
                        list($idsql, $idparams) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'a');
                        $params += $idparams;
                        $where = "quiza.id $idsql AND quiza.preview = 0";
                    }
                    break;
            }
            
            // Фильтрация по периоду
            if( ! is_null($this->options->startdate) && ! is_null($this->options->enddate) )
            {
                switch($this->options->attemptsinperiod)
                {
                    // Нужны только законченные попытки в периоде
                    case 'finished':
                        $periodselect = '(quiza.timefinish >= :startdate AND quiza.timefinish <= :enddate)';
                        break;
                    // нужны все попытки в периоде
                    case 'all':
                    default:
                        $periodselect = '(quiza.timestart <= :enddate AND (quiza.timefinish >= :startdate OR quiza.timefinish = 0))';
                        break;
                }
                if( empty($where) )
                {
                    $where = $periodselect;
                } else
                {
                    $where .= ' AND ' .$periodselect;
                }
                $params['startdate'] = $this->options->startdate;
                $params['enddate'] = $this->options->enddate;
            }
    
            if ($this->options->states) {
                list($statesql, $stateparams) = $DB->get_in_or_equal($this->options->states,
                    SQL_PARAMS_NAMED, 'state');
                $params += $stateparams;
                $where .= " AND (quiza.state $statesql OR quiza.state IS NULL)";
            }
    
            return array($fields, $from, $where, $params);
    }
}

/**
 * Расширение класса quiz_responses_options для возможности передать в качестве опций 
 * даты начала и конца периода, за который необходимо выбрать попытки прохождения
 *
 */
class report_mods_data_quiz_responses_options extends quiz_responses_options 
{
    /**
     * Дата начала периода, за который необходимо выбрать попытки прохождения
     * @var int timestamp
     */
    public $startdate;
    
    /**
     * Дата конца периода, за который необходимо выбрать попытки прохождения
     * @var int timestamp
     */
    public $enddate;
}

