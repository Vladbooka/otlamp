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
 * This file defines the quiz grades table.
 *
 * @package   quiz_otoverview
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/attemptsreport_table.php');


/**
 * This is a table subclass for displaying the quiz grades report.
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_otoverview_table extends quiz_attempts_report_table {
    
    protected $regradedqs = array();

    /**
     * Флаг, обозначающий, что необходимо вернуть только данные в виде массива без печати/экспорта
     * @var bool
     */
    protected $onlygetdata = false;
    
    /**
     * Идентификаторы попыток, которые нас интересуют
     * @var array
     */
    protected $questionattemptsids = [];
    
    /**
     * Контекст блока "Комментарий преподавателя"
     * 
     * @var context_block
     */
    protected $blockcontext = null;
    
    /**
     * Объект блока
     * @var block_quiz_teacher_feedback
     */
    protected $block = null;
    
    /**
     * Массив строк таблицы
     * @var array
     */
    public $data = [];
    
    /**
     * Установка флага
     * @param bool $status
     */
    public function set_flag_onlygetdata(bool $status)
    {
        $this->onlygetdata = $status;
    }
    
    /**
     * Установка идентификаторов попыток
     * @param bool $status
     */
    public function set_questionattemptsids(array $attemptsids)
    {
        $this->questionattemptsids = $attemptsids;
    }

    /**
     * Constructor
     * @param object $quiz
     * @param context $context
     * @param string $qmsubselect
     * @param quiz_otoverview_options $options
     * @param \core\dml\sql_join $groupstudentsjoins
     * @param \core\dml\sql_join $studentsjoins
     * @param array $questions
     * @param moodle_url $reporturl
     */
    public function __construct($quiz, $context, $qmsubselect,
            quiz_otoverview_options $options, \core\dml\sql_join $groupstudentsjoins,
            \core\dml\sql_join $studentsjoins, $questions, $reporturl) {
        parent::__construct('mod-quiz-report-overview-report', $quiz , $context,
                $qmsubselect, $options, $groupstudentsjoins, $studentsjoins, $questions, $reporturl);
        // получение блока
        $this->blockcontext = find_block_in_quiz($context);
        if ( ! empty($this->blockcontext) )
        {
            $this->block = block_instance_by_id($this->blockcontext->instanceid);
        }
    }

    public function build_table() {
        global $DB;

        if (!$this->rawdata) {
            return;
        }

        $this->strtimeformat = str_replace(',', ' ', get_string('strftimedatetime'));
        if ( $this->onlygetdata )
        {
            if ($this->rawdata instanceof \Traversable && !$this->rawdata->valid()) {
                return;
            }
            
            foreach ($this->rawdata as $row) {
                if ( ! in_array($row->attempt, $this->questionattemptsids) )
                {
                    continue;
                }
                $this->data[$row->attempt] = $this->format_row($row);
            }
            if (!empty($this->groupstudentsjoins->joins)) {
                $sql = "SELECT DISTINCT u.id
                      FROM {user} u
                    {$this->groupstudentsjoins->joins}
                     WHERE {$this->groupstudentsjoins->wheres}";
                    $groupstudents = $DB->get_records_sql($sql, $this->groupstudentsjoins->params);
                    if ($groupstudents) {
                        $this->data['averagegroups'] = $this->get_row_from_keyed($this->compute_average_row(get_string('groupavg', 'grades'), $this->groupstudentsjoins));
                    }
            }
            
            if (!empty($this->studentsjoins->joins)) {
                $sql = "SELECT DISTINCT u.id
                      FROM {user} u
                    {$this->studentsjoins->joins}
                     WHERE {$this->studentsjoins->wheres}";
                    $students = $DB->get_records_sql($sql, $this->studentsjoins->params);
                    if ($students) {
                        $this->data['averageusers'] = $this->get_row_from_keyed($this->compute_average_row(get_string('overallaverage', 'grades'), $this->studentsjoins));
                    }
            }
        } else 
        {
            parent::build_table();
            
            // End of adding the data from attempts. Now add averages at bottom.
            $this->add_separator();
            
            if (!empty($this->groupstudentsjoins->joins)) {
                $sql = "SELECT DISTINCT u.id
                      FROM {user} u
                    {$this->groupstudentsjoins->joins}
                     WHERE {$this->groupstudentsjoins->wheres}";
                    $groupstudents = $DB->get_records_sql($sql, $this->groupstudentsjoins->params);
                    if ($groupstudents) {
                        $this->add_average_row(get_string('groupavg', 'grades'), $this->groupstudentsjoins, 'otoverview-averagegroups');
                    }
            }
            
            if (!empty($this->studentsjoins->joins)) {
                $sql = "SELECT DISTINCT u.id
                      FROM {user} u
                    {$this->studentsjoins->joins}
                     WHERE {$this->studentsjoins->wheres}";
                    $students = $DB->get_records_sql($sql, $this->studentsjoins->params);
                    if ($students) {
                        $this->add_average_row(get_string('overallaverage', 'grades'), $this->studentsjoins, 'otoverview-averageusers');
                    }
            }
        }
    }

    
    /**
     * This method actually directly echoes the row passed to it now or adds it
     * to the download. If this is the first row and start_output has not
     * already been called this method also calls start_output to open the table
     * or send headers for the downloaded.
     * Can be used as before. print_html now calls finish_html to close table.
     *
     * @param array $row a numerically keyed row of data to add to the table.
     * @param string $classname CSS class name to add to this row's tr tag.
     * @return bool success.
     */
    function add_data($row, $classname = '', $ajax = false) {
        if (!$this->setup) {
            return false;
        }
        if (!$this->started_output) {
            $this->start_output();
        }
        if ($this->exportclass!==null) {
            if ($row === null) {
                $this->exportclass->add_seperator();
            } else {
                $this->exportclass->add_data($row);
            }
        } else {
            $this->print_row($row, $classname);
        }
        return true;
    }
    

    /**
     * Calculate the average overall and question scores for a set of attempts at the quiz.
     *
     * @param string $label the title ot use for this row.
     * @param \core\dml\sql_join $usersjoins to indicate a set of users.
     * @return array of table cells that make up the average row.
     */
    public function compute_average_row($label, \core\dml\sql_join $usersjoins) {
        global $DB;

        list($fields, $from, $where, $params) = $this->base_sql($usersjoins);
        $record = $DB->get_record_sql("
                SELECT AVG(quizaouter.sumgrades) AS grade, COUNT(quizaouter.sumgrades) AS numaveraged
                  FROM {quiz_attempts} quizaouter
                  JOIN (
                       SELECT DISTINCT quiza.id
                         FROM $from
                        WHERE $where
                       ) relevant_attempt_ids ON quizaouter.id = relevant_attempt_ids.id
                ", $params);
        $record->grade = quiz_rescale_grade($record->grade, $this->quiz, false);
        if ($this->is_downloading()) {
            $namekey = 'lastname';
        } else {
            $namekey = 'fullname';
        }
        $averagerow = array(
            $namekey       => $label,
            'sumgrades'    => $this->format_average($record),
            'feedbacktext' => strip_tags(quiz_report_feedback_for_grade(
                                         $record->grade, $this->quiz->id, $this->context))
        );

        if ($this->options->slotmarks) {
            $dm = new question_engine_data_mapper();
            $qubaids = new qubaid_join("{quiz_attempts} quizaouter
                  JOIN (
                       SELECT DISTINCT quiza.id
                         FROM $from
                        WHERE $where
                       ) relevant_attempt_ids ON quizaouter.id = relevant_attempt_ids.id",
                    'quizaouter.uniqueid', '1 = 1', $params);
            $avggradebyq = $dm->load_average_marks($qubaids, array_keys($this->questions));

            $averagerow += $this->format_average_grade_for_questions($avggradebyq);
        }

        return $averagerow;
    }

    /**
     * Add an average grade row for a set of users.
     *
     * @param string $label the title ot use for this row.
     * @param \core\dml\sql_join $usersjoins (joins, wheres, params) for the users to average over.
     */
    protected function add_average_row($label, \core\dml\sql_join $usersjoins, $class = '') {
        $averagerow = $this->compute_average_row($label, $usersjoins);
        $this->add_data_keyed($averagerow, $class);
    }

    /**
     * Helper userd by {@link add_average_row()}.
     * @param array $gradeaverages the raw grades.
     * @return array the (partial) row of data.
     */
    protected function format_average_grade_for_questions($gradeaverages) {
        $row = array();

        if (!$gradeaverages) {
            $gradeaverages = array();
        }

        foreach ($this->questions as $question) {
            if (isset($gradeaverages[$question->slot]) && $question->maxmark > 0) {
                $record = $gradeaverages[$question->slot];
                $record->grade = quiz_rescale_grade(
                        $record->averagefraction * $question->maxmark, $this->quiz, false);

            } else {
                $record = new stdClass();
                $record->grade = null;
                $record->numaveraged = 0;
            }

            $row['qsgrade' . $question->slot] = $this->format_average($record, true);
        }

        return $row;
    }

    /**
     * Format an entry in an average row.
     * @param object $record with fields grade and numaveraged.
     * @param bool $question true if this is a question score, false if it is an overall score.
     * @return string HTML fragment for an average score (with number of things included in the average).
     */
    protected function format_average($record, $question = false) {
        if (is_null($record->grade)) {
            $average = '-';
        } else if ($question) {
            $average = quiz_format_question_grade($this->quiz, $record->grade);
        } else {
            $average = quiz_format_grade($this->quiz, $record->grade);
        }

        if ($this->download) {
            return $average;
        } else if (is_null($record->numaveraged) || $record->numaveraged == 0) {
            return html_writer::tag('span', html_writer::tag('span',
                    $average, array('class' => 'average')), array('class' => 'avgcell'));
        } else {
            return html_writer::tag('span', html_writer::tag('span',
                    $average, array('class' => 'average')) . ' ' . html_writer::tag('span',
                    '(' . $record->numaveraged . ')', array('class' => 'count')),
                    array('class' => 'avgcell'));
        }
    }

    protected function submit_buttons() {
        if (has_capability('mod/quiz:regrade', $this->context)) {
            echo '<input type="submit" class="btn btn-secondary m-r-1" name="regrade" value="' .
                    get_string('regradeselected', 'quiz_otoverview') . '"/>';
        }
        parent::submit_buttons();
    }
    
    public function col_sumgrades($attempt) {
        if ($attempt->state != quiz_attempt::FINISHED) {
            return '-';
        }

        $grade = quiz_rescale_grade($attempt->sumgrades, $this->quiz);
        if ($this->is_downloading()) {
            return $grade;
        }

        if (isset($this->regradedqs[$attempt->usageid])) {
            $newsumgrade = 0;
            $oldsumgrade = 0;
            foreach ($this->questions as $question) {
                if (isset($this->regradedqs[$attempt->usageid][$question->slot])) {
                    $newsumgrade += $this->regradedqs[$attempt->usageid]
                            [$question->slot]->newfraction * $question->maxmark;
                    $oldsumgrade += $this->regradedqs[$attempt->usageid]
                            [$question->slot]->oldfraction * $question->maxmark;
                } else {
                    $newsumgrade += $this->lateststeps[$attempt->usageid]
                            [$question->slot]->fraction * $question->maxmark;
                    $oldsumgrade += $this->lateststeps[$attempt->usageid]
                            [$question->slot]->fraction * $question->maxmark;
                }
            }
            $newsumgrade = quiz_rescale_grade($newsumgrade, $this->quiz);
            $oldsumgrade = quiz_rescale_grade($oldsumgrade, $this->quiz);
            $grade = html_writer::tag('del', $oldsumgrade) . '/' .
                    html_writer::empty_tag('br') . $newsumgrade;
        }
        return html_writer::link(new moodle_url('/mod/quiz/review.php',
                array('attempt' => $attempt->attempt)), $grade,
                array('title' => get_string('reviewattempt', 'quiz')));
    }

    /**
     * @param string $colname the name of the column.
     * @param object $attempt the row of data - see the SQL in display() in
     * mod/quiz/report/otoverview/report.php to see what fields are present,
     * and what they are called.
     * @return string the contents of the cell.
     */
    public function other_cols($colname, $attempt) {
        if (!preg_match('/^qsgrade(\d+)$/', $colname, $matches)) {
            return null;
        }
        $slot = $matches[1];

        $question = $this->questions[$slot];
        if (!isset($this->lateststeps[$attempt->usageid][$slot])) {
            return '-';
        }

        $stepdata = $this->lateststeps[$attempt->usageid][$slot];
        $state = question_state::get($stepdata->state);

        if ($question->maxmark == 0) {
            $grade = '-';
        } else if (is_null($stepdata->fraction)) {
            if ($state == question_state::$needsgrading) {
                $grade = get_string('requiresgrading', 'question');
            } else {
                $grade = '-';
            }
        } else {
            $grade = quiz_rescale_grade(
                    $stepdata->fraction * $question->maxmark, $this->quiz, 'question');
        }

        if (isset($this->regradedqs[$attempt->usageid][$slot])) {
            $gradefromdb = $grade;
            $newgrade = quiz_rescale_grade(
                    $this->regradedqs[$attempt->usageid][$slot]->newfraction * $question->maxmark,
                    $this->quiz, 'question');
            $oldgrade = quiz_rescale_grade(
                    $this->regradedqs[$attempt->usageid][$slot]->oldfraction * $question->maxmark,
                    $this->quiz, 'question');

            $grade = html_writer::tag('del', $oldgrade) . '/' .
                    html_writer::empty_tag('br') . $newgrade;
        }
        
        $data = $this->make_review_link($grade, $attempt, $slot);
        if ($this->is_downloading()) {
            return strip_tags($data);
        }

        return $data;
    }
    
    /**
     * Convenience method to call a number of methods for you to display the
     * table.
     */
    function out($pagesize, $useinitialsbar, $downloadhelpbutton='') {
        global $DB;
        if (!$this->columns) {
            $onerow = $DB->get_record_sql("SELECT {$this->sql->fields} FROM {$this->sql->from} WHERE {$this->sql->where}",
            $this->sql->params, IGNORE_MULTIPLE);
            //if columns is not set then define columns as the keys of the rows returned
            //from the db.
            $this->define_columns(array_keys((array)$onerow));
            $this->define_headers(array_keys((array)$onerow));
        }
        $this->setup();
        $class = ! empty($this->attributes['class']) ? $this->attributes['class'] : '';
        $this->set_attribute('class', $class . ' otoverview-table');
        $this->query_db($pagesize, $useinitialsbar);
        $this->build_table();
        $this->close_recordset();
        if ( !$this->onlygetdata )
        {
            
            $this->finish_output();
        }
    }
    
    /**
     * TO DEL
     * Load any extra data after main query. At this point you can call {@link get_qubaids_condition} to get the condition that
     * limits the query to just the question usages shown in this report page or alternatively for all attempts if downloading a
     * full report.
     */
    protected function load_extra_data() {
        $this->lateststeps = $this->load_question_latest_steps();
    }
    
    /**
     * Make a link to review an individual question in a popup window.
     *
     * @param string $data HTML fragment. The text to make into the link.
     * @param object $attempt data for the row of the table being output.
     * @param int $slot the number used to identify this question within this usage.
     */
    public function make_review_link($data, $attempt, $slot) {
        global $OUTPUT;
        
        $flag = '';
        if ($this->is_flagged($attempt->usageid, $slot)) {
            $flag = $OUTPUT->pix_icon('i/flagged', get_string('flagged', 'question'),
                    'moodle', array('class' => 'questionflag'));
        }
        
        $qa = quiz_attempt::create($attempt->attempt);
        // получение попытки прохождения слота
        $questionattempt = $qa->get_question_attempt($slot);
        $opts = $qa->get_display_options(true);
        
        $feedbackimg = '';
        $state = $this->slot_state($attempt, $slot);
        if ($state->is_finished() && $state != question_state::$needsgrading) {
            $feedbackimg = $this->icon_for_fraction($this->slot_fraction($attempt, $slot));
        }
        $stateclass = '';
        $output = html_writer::tag('span', $feedbackimg . html_writer::tag('span',
                $data, array('class' => $state->get_state_class(true))) . $flag, array('class' => 'que'));
    
        $reviewparams = array('attempt' => $attempt->attempt, 'slot' => $slot);
        if (isset($attempt->try)) {
            $reviewparams['step'] = $this->step_no_for_try($attempt->usageid, $slot, $attempt->try);
        }
        $feedback = block_quiz_teacher_feedback_get_feedback($questionattempt->get_database_id());
        if ( ! empty($opts->manualcommentlink) )
        {
            $stateclass = $questionattempt->get_state() instanceof question_state_needsgrading ? 'teacher-action-required' : '';
            $url = new moodle_url($opts->manualcommentlink, $reviewparams);
            return $OUTPUT->action_link(
                    $url, 
                    $output,
                    new popup_action('click', $url, 'commentquestion', ['width' => 600, 'height' => 800]),
                    ['title' => get_string('commentormark', 'question'), 'class' => 'otoverview_action_popup ' . $stateclass, 'act' => 'commentquestion']);
        } elseif ( !empty($this->block) && 
                !$qa->is_finished() && 
                ($data == '-' || ($this->block->is_slot_under_controlled($questionattempt) && empty($feedback->completed))) )
        {
            if ( $this->block->is_slot_should_be_graded($questionattempt) &&
                    $status = $this->block->get_slot_status($questionattempt) )
            {
                $url = new moodle_url('/mod/quiz/report/otoverview/comment.php', $reviewparams);
                if ( $status === block_quiz_teacher_feedback::RESPONSE_STATUS_SHOULD_BE_CONFIRMED )
                {
                    $stateclass = 'teacher-action-required';
                    // необходимо оценить
                    return $OUTPUT->action_link(
                            $url,
                            get_string('commentormark', 'quiz_otoverview'),
                            new popup_action('click', $url, 'commentquestion', ['width' => 600, 'height' => 800]),
                            ['title' => get_string('commentormark', 'quiz_otoverview'), 'class' => 'otoverview_action_popup ' . $stateclass, 'act' => 'commentquestion']);
                } elseif ( $status === block_quiz_teacher_feedback::RESPONSE_STATUS_SHOULD_BE_RECONFIRMED )
                {
                    if ( ! empty($feedback) )
                    {
                        $feedbackimg = '';
                        if ( strlen($feedback->grade) )
                        {
                            $feedbackimg = $this->icon_for_fraction($feedback->grade);
                            $data = quiz_rescale_grade($feedback->grade * $this->questions[$slot]->maxmark, $this->quiz, 'question');
                        }
                        if ( $feedback->needsgrading )
                        {
                            $info = get_string('regradecommentormark', 'quiz_otoverview', $data);
                            $stateclass = 'teacher-action-required';
                        } else 
                        {
                            $info = get_string('waitinguseranswer', 'quiz_otoverview', $data);
                        }
                        $output = html_writer::tag(
                                'span',
                                $feedbackimg . html_writer::tag('span', $info) . $flag,
                                ['class' => '']);
                        // необходимо переоценить
                        return $OUTPUT->action_link(
                                $url,
                                $output,
                                new popup_action('click', $url, 'commentquestion', ['width' => 800, 'height' => 900]),
                                ['title' => $info, 'class' => 'otoverview_action_popup ' . $stateclass, 'act' => 'commentquestion']);
                    }
                } elseif ( $status === block_quiz_teacher_feedback::RESPONSE_STATUS_CONFIRMED  )
                {
                    if ( ! empty($feedback) )
                    {
                        $feedbackimg = '';
                        if ( strlen($feedback->grade) )
                        {
                            $feedbackimg = $this->icon_for_fraction($feedback->grade);
                            $data = quiz_rescale_grade($feedback->grade * $this->questions[$slot]->maxmark, $this->quiz, 'question');
                        } else
                        {
                            $feedbackimg = $this->icon_for_fraction(1);
                            $data = get_string('confirmed', 'quiz_otoverview');
                        }
                        $output = html_writer::tag(
                                'span',
                                $feedbackimg . html_writer::tag('span', $data) . $flag,
                                ['class' => 'que']);
                        return $OUTPUT->action_link(
                                $url,
                                $output,
                                new popup_action('click', $url, 'commentquestion', ['width' => 800, 'height' => 900]),
                                ['title' => $data, 'class' => 'otoverview_action_popup', 'act' => 'commentquestion']);
                    }
                }
            }
        }
        
        $url = new moodle_url('/mod/quiz/reviewquestion.php', $reviewparams);
        return $OUTPUT->action_link(
                $url, 
                $output,
                new popup_action('click', $url, 'reviewquestion', ['height' => 600, 'width' => 800]),
                ['title' => get_string('reviewresponse', 'quiz'), 'class' => 'otoverview_action_popup', 'act' => 'reviewquestion']);
    }

    public function col_regraded($attempt) {
        if ($attempt->regraded == '') {
            return '';
        } else if ($attempt->regraded == 0) {
            return get_string('needed', 'quiz_otoverview');
        } else if ($attempt->regraded == 1) {
            return get_string('done', 'quiz_otoverview');
        }
    }
    
    protected function update_sql_after_count($fields, $from, $where, $params) {
        $fields .= ", COALESCE((
                                SELECT MAX(qqr.regraded)
                                  FROM {quiz_overview_regrades} qqr
                                 WHERE qqr.questionusageid = quiza.uniqueid
                          ), -1) AS regraded";
        if ($this->options->onlyregraded) {
            $where .= " AND COALESCE((
                                    SELECT MAX(qqr.regraded)
                                      FROM {quiz_overview_regrades} qqr
                                     WHERE qqr.questionusageid = quiza.uniqueid
                                ), -1) <> -1";
        }
        return [$fields, $from, $where, $params];
    }

    protected function requires_latest_steps_loaded() {
        return $this->options->slotmarks;
    }

    protected function is_latest_step_column($column) {
        if (preg_match('/^qsgrade([0-9]+)/', $column, $matches)) {
            return $matches[1];
        }
        return false;
    }

    protected function get_required_latest_state_fields($slot, $alias) {
        return "$alias.fraction * $alias.maxmark AS qsgrade$slot";
    }

    public function query_db($pagesize, $useinitialsbar = true) {
        parent::query_db($pagesize, $useinitialsbar);

        if ($this->options->slotmarks && has_capability('mod/quiz:regrade', $this->context)) {
            $this->regradedqs = $this->get_regraded_questions();
        }
    }

    /**
     * Get all the questions in all the attempts being displayed that need regrading.
     * @return array A two dimensional array $questionusageid => $slot => $regradeinfo.
     */
    protected function get_regraded_questions() {
        global $DB;

        $qubaids = $this->get_qubaids_condition();
        $regradedqs = $DB->get_records_select('quiz_overview_regrades',
                'questionusageid ' . $qubaids->usage_id_in(), $qubaids->usage_id_in_params());
        return quiz_report_index_by_keys($regradedqs, array('questionusageid', 'slot'));
    }
}
