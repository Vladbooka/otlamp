<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

/**
 * Класс для работы с модулем quiz
*
* @package    modlib
* @subpackage ama
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

global $CFG;

// Подключение библиотек
require_once(dirname(realpath(__FILE__)).'/class.ama_course_instance.php');
require_once(dirname(realpath(__FILE__)) . '/../../../../../mod/quiz/lib.php');
require_once(dirname(realpath(__FILE__)) . '/../../../../../mod/quiz/locallib.php');
require_once(dirname(realpath(__FILE__)) . '/../../../../../lib/datalib.php');
require_once(dirname(realpath(__FILE__)) . '/../../../../../lib/gradelib.php');

class ama_course_instance_quiz extends ama_course_instance
{
    protected $qtypes;
    
    public function __construct($cm)
    {
        $this->cm = $cm;
        if( isset($this->cm->id) )
        {
            $this->context = context_module::instance($this->cm->id);
        } else
        {
            $this->context = null;
        }
        if( empty($this->courseid) )
        {
            $this->courseid = $this->cm->course;
        }
        // Поддерживаемые типы вопросов
        $this->qtypes = ['essay', 'otmultiessay'];
    }
    
    /** Вернуть тип задания из таблицы modules
     *
     * @return string
     */
    public function get_instance_name()
    {
        return 'quiz';
    }
    
    /**
     * Получение инстанса теста
     *
     * @throws dml_exception
     * @return stdClass
     */
    public function get_quiz()
    {
        global $DB;
        return $DB->get_record('quiz', ['id' => $this->cm->instance], '*', MUST_EXIST);
    }
    
    /**
     * Получение попыток пользователя по текущему тесту
     *
     * @param number $userid - ID пользователя Moodle
     * @param string $status - статус попыток all|finished|unfinished
     * @param bool $includepreviews
     *
     * @return []stdClass
     */
    public function get_user_attempts($userid, $status = 'finished', $includepreviews = false)
    {
        return quiz_get_user_attempts($this->get_quiz()->id, $userid, $status, $includepreviews);
    }
    
    /**
     * Выдать новую попытку пользователю через переопределение пользователей
     *
     * @param array $usersids
     *
     * @return void
     */
    public function add_new_attempt($usersids = [])
    {
        global $DB;
        $keys = ['timeopen', 'timeclose', 'timelimit', 'password'];
        $quizinstance = $this->get_quiz();
        $context = context_module::instance($this->cm->id);
        $fromuser = get_admin();
        if ( empty($fromuser) )
        {
            GLOBAL $USER;
            $fromuser = $USER;
        }
        $accessmanager = new quiz_access_manager(quiz::create($this->cm->instance, $fromuser->id), time(), true);
        
        // проверим, что стоит ограничение на количество попыток
        if ( ! empty($quizinstance->attempts) )
        {
            foreach ($usersids as $userid)
            {
                $unfinishedattempt = $this->get_user_attempts($userid, 'unfinished');
                
                // получение попыток пользователя
                $finishedattempts = $this->get_user_attempts($userid, 'finished', true);
                $numattempts = count($unfinishedattempt) + count($finishedattempts);
                
                // последняя завершенная попытка, если она есть
                $lastfinishedattempt = end($finishedattempts);
                
                if ( ! empty($unfinishedattempt) || ! $accessmanager->is_finished($numattempts, $lastfinishedattempt) )
                {
                    // у пользователя есть незавершенная попытка, нет необходимости выдавать новую
                    continue;
                }
                
                // запуск сабплагинов access_rule, которые скажут, есть ли у пользователя возможность начать новую попытку
                // если не пустой - то не может
                $rulesinfo = $accessmanager->prevent_new_attempt($numattempts, $lastfinishedattempt);
                if ( ! empty($rulesinfo) )
                {
                    // формирование записи переопределения пользователя
                    $record = new stdClass();
                    $record->quiz = $quizinstance->id;
                    $record->userid = $userid;
                    $record->attempts = $numattempts + 1;
                    
                    // если количество попыток превысило количество
                    // допустимых попыток
                    // то создадим переопределение пользователю и добавим
                    // попытку
                    $conditions = [
                        'quiz' => $quizinstance->id,
                        'userid' => $userid,
                        'groupid' => null
                    ];
                    
                    // поиск старого переопределения
                    // перенимаем данные, которые не будем переопределять
                    // удаляем старое переопределение
                    if ( $oldoverride = $DB->get_record('quiz_overrides', $conditions) )
                    {
                        foreach ($keys as $key)
                        {
                            if ( ! property_exists($record, $key) || is_null($record->{$key}) )
                            {
                                $record->{$key} = $oldoverride->{$key};
                            }
                        }
                        // Set the course module id before calling quiz_delete_override().
                        $quizinstance->cmid = $this->cm->id;
                        quiz_delete_override($quizinstance, $oldoverride->id);
                    }
                    
                    $params = [
                        'context' => $context,
                        'other' => [
                            'quizid' => $quizinstance->id
                        ]
                    ];
                    $params['objectid'] = $DB->insert_record('quiz_overrides', $record);
                    $params['relateduserid'] = $userid;
                    
                    $event = \mod_quiz\event\user_override_created::create($params);
                    $event->trigger();
                
                    quiz_update_open_attempts(['quizid' => $quizinstance->id]);
                    quiz_update_events($quizinstance, $record);
                }
            }
        }
    }

    /////////////////////////////////////////////
    //    Методы для работы блока notgraded    //
    /////////////////////////////////////////////
    
    /**
     * Возвращает все непроверенные задания типа "эссе" для курса
     *
     * @see block_notgraded_base_element::get_notgraded_elements()
     * @return array
     */
    protected function get_notgraded_elements($timefrom = null, $timeto = null)
    {
        global $CFG, $DB;
    
        // Получаем все задания курса
        $quizes = $this->get_course_instances();
    
        // Получаем пользователей курса
        if( ! $userids = $this->get_course_users() )
        {// Пользователей нет, проверять некого
            return [];
        }
        // Получим все Эссе курса, сгруппированных по модулям теста
        $course_essays = $this->only_essay($quizes);
        // Результирующий массив для хранения всех неоцененных Эссе
        $data = [];
    
        // Обрабатываем каждый из наборов эссе в каждом модуле курса
        foreach($course_essays as $quiz_id => $questions)
        {
            // Для каждого из заданий эссе произведем действия
            foreach($questions as $q)
            {
                $select = '';
                $sarray = [];
    
                // Получим неоцененные попытки по каждому из заданий эссе
                $attempts = $DB->get_records_sql(
                    'SELECT qa.* '.
                    'FROM '.$CFG->prefix.'quiz_attempts qa '.
                    'WHERE	quiz ='. $quiz_id .'
							AND qa.timefinish > 0
							AND qa.userid IN ('.$userids.')
		                    AND qa.preview = 0
		        ');
                if( $attempts )
                {//если есть ответ на quiz с нужным вопросом';
                    $str = '';//хранит данные из $data в виде строки
                    foreach($attempts as $attempt)
                    {
                        if( $attempt->sumgrades === null )
                        {
                            $data[] = $this->get_info($q->id, $attempt);
                        }
                    }
                }
            }
        }
        return $data;
    }
    
    /**
     * Отфильтровать массив заданий. оставив лишь эссе
     *
     * Перебирает переданные экземпляры модуля quiz и оставляет
     * только вопросы типа эссе
     *
     * @param $quizes array - массив записей с информацией
     * об экземплярах модуля типа quiz
     *
     * @return $all_question array - массив, индексами которого
     * являются id экземпляра quiz, а значениями
     * массив объектов (id, qtype),
     * где id - это id вопросов типа эссе, а qtype = essay.
     */
    private function only_essay($quizes)
    {
        global $DB;
    
        if( empty($quizes) )
        {// Массив пуст
            return [];
        }
        // Готовим результирующий массив
        $all_question = [];
        foreach($quizes as $quiz)
        {// Для каждого из экземпляров модулей тестов
            // Получим все вопросы теста
            $questions = $this->get_questions($quiz->id);
            // Отфильтруе тест, оставив в нем только Эссе
            foreach ( $questions as $id => $question )
            {
                if( ! in_array($question->qtype, $this->qtypes) )
                {// Вопрос не типа Эссе или Мультиэссе
                    // Удалим его из массива
                    unset($questions[$id]);
                }
            }
            if ( ! empty($questions) )
            {// В тесте есть вопросы типа Эссе
                // Добавим данные в результирующий массив
                $all_question[$quiz->id] = $questions;
            }
        }
        // Вернем массив
        return $all_question;
    }
    
    protected function get_questions($quizid) {
        global $DB;
        $questions = $DB->get_records_sql("SELECT slot.slot, q.*, qc.contextid, slot.page, slot.maxmark
                                  FROM {quiz_slots} slot
                             LEFT JOIN {question} q ON q.id = slot.questionid
                             LEFT JOIN {question_categories} qc ON qc.id = q.category
                                 WHERE slot.quizid = ?
                              ORDER BY slot.slot", [$quizid]);
        return $questions;
    }
    
    /**
     *  формирует объект с названием вопроса (типа эссе) и
     * именами тех, чьи ответы надо проверить, а также датой ответа
     * @param array $array массив структуры quiz_id, question_id, имя_того_кто_отвечал, инфо_о_попытке
     * @return string $str строка вида "Эссе имя_эссе ФИО_ответчика дата ответа ... ФИО_ответчика дата ответа"
     */
    private function get_info($questionid, $attempt)
    {
        global $CFG,$DB;
        $obj = new stdClass();
        //название типа вопроса на родном языке
        $obj->type = get_string('essay', 'mod_quiz');
        //получаем инфо о вопросе
        $question = $DB->get_record('question', ['id' => $questionid]);
        if( ! $question )
        {//нет такого вопроса
            return false;
        }
        //ссылка на эссе
        $obj->name = "<a title=\"{$obj->type}\" href=\"{$CFG->wwwroot}/mod/quiz/report.php?".
            "mode=grading&amp;q=$attempt->quiz&amp;".
            "action=viewquestion&amp;questionid=$questionid\">".
            $question->name."</a>";
            //имя студента
            $obj->student = 'error name student';
            if( $user = $DB->get_record('user', ['id'=>$attempt->userid]) )
            {//получаем имя студента
                $obj->student = fullname($user);
            }
            //дата сдачи эссе
            //$obj->time = date("d.m.y", $attempt->timemodified);
            $obj->time = $attempt->timemodified;
            return $obj;
    }
    
    public function get_quizes_count_questions()
    {
        $result = [];
        
        // Получаем все задания курса
        $quizes = $this->get_course_instances();
        
        if (empty($quizes))
        {// Массив пуст
            return null;
        }
        
        foreach($quizes as $quiz)
        {// Для каждого из экземпляров модулей тестов
            // Получим все вопросы теста
            $questions = $this->get_questions($quiz->id);
            $result[] = count($questions);
        }
        
        return $result;
    }
}