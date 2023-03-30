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
/*
 * Файл отвечающий за обработку модулей типа "задание"
 */
require_once('element.php');
/** Класс для анализа элементов типа "задание"
 * @todo добавить функцию быстрой проверки заданий
 * 
 */
class block_notgraded_assign extends block_notgraded_base_element
{
    /** Получить неотсортированный массив непроверенных заданий
     * 
     * @return array
     */
    protected function get_notgraded_elements()
    {
        global $CFG,$DB;
        // получаем все задания курса
        $assignments = $this->get_course_instances();
        // переводим название задания на русский
        $strassignment = get_string('modulename', $this->get_instance_name());
		// собираем id пользователей в массив
        if ( ! $userids = $this->get_course_users() )
        {//пользователей нет - значит и заданий нет
            return [];
        }
        // будет хранить результат
		$result = [];
		
		// Указана группа
		if ( $this->groupid )
		{
		    $currentgroup = $this->groupid;
		} else
		{
		    $currentgroup = 0;
		}
		
		foreach ($assignments as $assignment)
		{//среди всех заданий ищем непроверенные
		    if ( $assignment->teamsubmission )
		    {// Если групповой режим, пропускаем
		        continue;
		    }
		    
		    $course = get_course($assignment->course);
		    $context = context_module::instance($assignment->coursemodule);
		    $modinfo = get_fast_modinfo($course);
		    $cm = $modinfo->get_cm($context->instanceid);

		    list($esql, $params) = get_enrolled_sql($context, 'mod/assign:submit', $currentgroup, true);
		   
		    $params['assignid'] = $assignment->id;
		    $params['submitted'] = 'submitted';
		    
		    $sql = 'SELECT s.userid, s.timemodified
                   FROM {assign_submission} s
                   LEFT JOIN {assign_grades} g ON
                        s.assignment = g.assignment AND
                        s.userid = g.userid AND
                        g.attemptnumber = s.attemptnumber
                   JOIN(' . $esql . ') e ON e.id = s.userid
                   WHERE
                        s.latest = 1 AND
                        s.assignment = :assignid AND
                        s.timemodified IS NOT NULL AND
                        s.status = :submitted AND
                        (s.timemodified >= g.timemodified OR g.timemodified IS NULL OR g.grade IS NULL)';
		    $records = $DB->get_records_sql($sql, $params);
		    if ( ! empty($records) )
		    {
                $num = 0;
                foreach ( $records as $record )
                {
                    $element = new stdClass();
                    //перевод названия модуля на местный язык
                    $element->type = $strassignment;
                    if ( $this->groupid )
                    {// если указана конкретная группа - то покажем задания только для нее
                        $element->name    = '<a title="'.$strassignment.'" href="'.$CFG->wwwroot.
                        '/mod/assign/view.php?id='.$assignment->coursemodule.'&group='.$this->groupid.'">'.
                        $assignment->name.'</a>';
                    } else
                    {// группа не указана - покажем просто ссылку на просмотр выполненных заданий
                        $element->name    = '<a title="'.$strassignment.'" href="'.$CFG->wwwroot.
                        '/mod/assign/view.php?id='.$assignment->coursemodule.'&action=grading'.'">'.
                        $assignment->name.'</a>';
                    }
                    if ( false )
                    {// @todo вставить сюда проверку настройки "разрешить быструю проверку"
                        $element->name .= $this->get_fast_check_link($record->userid, $num, $assignment->coursemodule);
                    }
                    
                    // ФИО ученика
                    $element->student = fullname($DB->get_record('user', ['id'=>$record->userid]));
                    // время выполнения задания
                    //$element->time    = date("d.m.y", $val->timemodified);
                    $element->time    = $record->timemodified;
                    // добавляем непроверенный элемент к итоговому массиву
                    $result[] = $element;
                }
		    }
		}
        $this->elements = $result;
		return $result;
    }
    
    /** Получить ссылку на быструю проверку задания
     * @todo добавить эту функцию в код, когда она будет готова
     * 
     * @return 
     * @param object $userid
     * @param object $offset
     * @param object $assignmentid
     */
    protected function get_fast_check_link($userid, $offset, $assignmentid)
    {
        global $CFG;
        '<a onclick="'.$this->get_checknow_onclick($val->userid, $num, $assignment->coursemodule).
                        '" href="'.$CFG->wwwroot.
						'/mod/assignment/submissions.php?id='.$assignment->coursemodule.
                        '&userid='.$val->userid.'&mode=single&offset='.$num.'">a</a>';
    }
    
    /** Получить JS для открытия окна для проверки задания
     * 
     * @todo подключить эти функцию когда будет готова быстрая проверка
     * @param int $userid 
     * @param int $offset 
     * @param int $assignmentid 
     * @return string
     */
    protected function get_checknow_onclick($userid, $offset, $assignmentid)
    {
       return "this.target='grade{$userid}';".
       "return openpopup('/mod/assignment/submissions.php?id={$assignmentid}&userid={$userid}&mode=single&offset={$offset}',".
       "'grade{$userid}', 'menubar=0,location=0,scrollbars,resizable,width=780,height=600', 0);";
    }
    
    /** Вернуть тип задания из таблицы modules
     * 
     * @return string
     */
    protected function get_instance_name()
    {
        return 'assign';
    }
}
?>