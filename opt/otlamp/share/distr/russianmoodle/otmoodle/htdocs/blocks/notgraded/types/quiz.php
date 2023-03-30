<?php
/*
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Файл отвечающий за обработку элементов курса типа quiz(тесты)
 */

class block_notgraded_quiz extends block_notgraded_base_element
{
    /** 
     * Возвращает все непроверенные задания типа "эссе" для курса
     * 
     * @see block_notgraded_base_element::get_notgraded_elements()
     * @return array
     */
    protected function get_notgraded_elements($timefrom = null, $timeto = null)
    {
        global $CFG, $DB;
        
        // Подключаем библиотеку работы с тестами
		require_once ("{$CFG->dirroot}/mod/quiz/locallib.php");
        // Получаем все задания курса
		$quizes = $this->get_course_instances();
		
		// Получаем пользователей курса
		if ( ! $userids = $this->get_course_users() )
		{// Пользователей нет, проверять некого
		    return array();
		}
        // Получим все Эссе курса, сгруппированных по модулям теста
		$course_essays = $this->only_essay($quizes);
		// Результирующий массив для хранения всех неоцененных Эссе
		$data = array();
		
		// Обрабатываем каждый из наборов эссе в каждом модуле курса
		foreach( $course_essays as $quiz_id => $questions )
		{
			// Для каждого из заданий эссе произведем действия
		    foreach ( $questions as $q )
		    {    
		        $select = '';
		        $sarray = array();
		        
		        // Получим неоцененные попытки по каждому из заданий эссе
		        $attempts = $DB->get_records_sql(
		                'SELECT qa.* '.
						'FROM '.$CFG->prefix.'quiz_attempts qa '.
						'WHERE	quiz ='. $quiz_id .'
							AND qa.timefinish > 0 
							AND qa.userid IN ('.$userids.') 
		                    AND qa.preview = 0 
		        ');
		        if ($attempts)
		        {//если есть ответ на quiz с нужным вопросом';
		            $str = '';//хранит данные из $data в виде строки
		            foreach ($attempts as $attempt)
		            {
		                if ( $attempt->sumgrades === NULL )
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
	 * возвращает истину если попытка ответа на вопрос оценена и 
	 * ложь если нет
	 * @param $attempt object содержит инфо о конкретной попытке ответа на вопрос с id question_id
	 * @param $question_id int id вопроса, на который пытались ответить
	 * @return bool 
	 */
	private function is_graded($attempt, $question_id)
	{
		global $CFG,$DB;
		if ($CFG->version <= 2006080400)
		{// для версии 1.6
			$manual = '';
		}
		else
		{// для версии 1.7 и позднее 
			$manual = 'manual';
		}
		//получаем инфо о последнем ответе на нужный нам вопрос
		$state = $DB->get_record_sql("SELECT state.id, state.event, sess.".$manual."comment 
								FROM {$CFG->prefix}question_states state, 
									 {$CFG->prefix}question_sessions sess
								WHERE sess.newest = state.id 
									AND sess.attemptid = $attempt->uniqueid 
									AND sess.questionid = $question_id");
		if ($state)
		{//если инфо есть - проверяем проверен ли ответ
			if (!$manual)
			{//для moodle версии 1.6
			    $gradedevents = explode(',', QUESTION_EVENTS_GRADED);
				return in_array($state->event, $gradedevents) OR $state->comment;
			}
			else
			{//для moodle версии 1.7 и позже
				$gradedevents = explode(',', QUESTION_EVENTS_GRADED);
                return in_array($state->event, $gradedevents) OR $state->manualcomment;
			}
		}
		else
		{
			// Нельзя вызывать фатальную ошибку в блоке отображения
			// return error('Could not find question state');
			// Тихо возвращаем, что все хорошо
			return true;
		}
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
        $obj->type = get_string('essay', 'quiz');
        //получаем инфо о вопросе
        $question = $DB->get_record('question', array('id'=>$questionid));
        if ( ! $question )
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
        if ( $user = $DB->get_record('user', array('id'=>$attempt->userid)) )
        {//получаем имя студента
            $obj->student = fullname($user);
        }
        //дата сдачи эссе
        //$obj->time = date("d.m.y", $attempt->timemodified);
        $obj->time = $attempt->timemodified;
        return $obj;
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
        
        if ( empty($quizes) )
        {// Массив пуст
            return array();
        }
        // Готовим результирующий массив
		$all_question = array();
		foreach ($quizes as $quiz)
		{// Для каждого из экземпляров модулей тестов
		    // Получим все вопросы теста
            $questions = $this->get_questions($quiz->id);
            // Отфильтруе тест, оставив в нем только Эссе
            foreach ( $questions as $id => $question ) 
            {
                if ($question->qtype != 'essay') 
                {// Вопрос не типа Эссе
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
                              ORDER BY slot.slot", array($quizid));
            return $questions;

        }
    
    /** Вернуть тип задания из таблицы modules
     * 
     * @return string
     */
    protected function get_instance_name()
    {
        return 'quiz';
    }
}
?>