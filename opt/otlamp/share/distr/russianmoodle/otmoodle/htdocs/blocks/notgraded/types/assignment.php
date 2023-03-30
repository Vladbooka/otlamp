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
class block_notgraded_assignment extends block_notgraded_base_element
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
            return array();
        }
        // будет хранить результат
		$result = array();
		foreach ($assignments as $assignment)
		{//среди всех заданий ищем непроверенные
			$notgraded = $DB->get_records_sql("SELECT a.userid, a.timemodified ".
							"FROM {$CFG->prefix}assignment_submissions a ". 
							"WHERE a.userid IN ($userids)
									AND a.assignment = '{$assignment->id}'
									AND (a.teacher = 0 
									OR a.timemodified > a.timemarked)");
			if ($notgraded)
			{//если есть задания без оценки, создаем ссылку на страницу выставления оценки
                $num=0;
				foreach ($notgraded as $val)
				{// создаем массив из объектов, которые будут потом форматированы
                    $num = $num + 1;
                    $element          = new stdClass();
                    //перевод названия модуля на местный язык
                    $element->type    = $strassignment;                    
                    if ( $this->groupid )
                    {// если указана конкретная группа - то покажем задания только для нее
                        $element->name    = '<a title="'.$strassignment.'" href="'.$CFG->wwwroot.
						'/mod/assignment/submissions.php?id='.$assignment->coursemodule.'&group='.$this->groupid.'">'.
                        $assignment->name.'</a>';
                    }else
                    {// группа не указана - покажем просто ссылку на просмотр выполненных заданий
                        $element->name    = '<a title="'.$strassignment.'" href="'.$CFG->wwwroot.
						'/mod/assignment/submissions.php?id='.$assignment->coursemodule.'">'.
                        $assignment->name.'</a>';
                    }
                    if ( false )
                    {// @todo вставить сюда проверку настройки "разрешить быструю проверку"
                        $element->name .= $this->get_fast_check_link($val->userid, $num, $assignment->coursemodule);
                    } 
                    
                    // ФИО ученика
                    $element->student = fullname($DB->get_record('user', array('id'=>$val->userid)));
                    // время выполнения задания
                    //$element->time    = date("d.m.y", $val->timemodified);
                    $element->time    = $val->timemodified;
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
        return 'assignment';
    }
}
?>