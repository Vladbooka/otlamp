<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
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
/*
 * Файл содержащий базовый класс для анализируемых\проверяемых элементов курса
 */

/** Базовый класс для всех анализируемых\проверяемых элементов курса
 * @todo сделать настройку "показывать или не показывать скрытые задания", перенести 
 * получение всех элементов через get_all_instances_in_course('assignment', $course); в этот класс
 */
abstract class block_notgraded_base_element
{
    /**
     * @var array - неотсортированный массив всех непроверенных элементов (объекты), или пустой массив
     * если непроверенных элементов такого типа в курсе нет
     * Формат массива:
     * [] => object
     *             ->type - название типа элемента (на нужном языке)
     *             ->name - ссылка на страницу проверки задания
     *             ->student - ФИО ученика выполнившего задание
     *             ->time - время выполнения задания в формате unixtime
     */
    protected $elements;
    /**
     * @var int - id курса для которого извлекаются элементы
     */
    protected $courseid;
    /**
     * @var int - id группы в курсе для которой извлекаются элементы
     */
    protected $groupid;
    
    /** 
     * Конструктор класса. Определяет все базовае значения для класса.
     * 
     * @param int $courseid - id курса для которого получаются элементы
     * @param int $groupid[optional] - id группы для которой получаются элементы
     */
    function __construct($courseid, $groupid=null)
    {
        $this->courseid = $courseid;
        $this->groupid  = $groupid;
        $this->elements = array();
    }
    
    /** 
     * Получить неотсортированный список непроверенных учителем элементов из курса 
     * 
     * @return массив объектов с непроверенными заданиями
     * @param int $timeform[optional] - начало временного периода, за который нужно запрашивать задания
     * @param int $timeto[optional] - конец временного периода, за который нужно запрашивать задания
     */
    public function get_list($timeform=null, $timeto=null)
    {
        // извлекаем все необходимые элементы
        $this->elements = $this->get_notgraded_elements($timeform, $timeto);
        // форматируем и возвращаем их
        return $this->format_elements();
    }
    
    /** 
     * Записать в поле объекта $this->elements неотсортированный массив объектов 
     * непроверенных заданий
     * 
     * @return array список непроверенных заданий
     * Формат массива:
     * [] => object
     *             ->type - название типа элемента (на нужном языке)
     *             ->name - название самого задания (элемента курса), являющееся ссылкой на него
     *             ->student - ФИО ученика выполнившего задание
     *             ->time - время выполнения задания в формате unixtime
     */
    abstract protected function get_notgraded_elements();
    
    /** Вернуть тип задания из таблицы modules
     * 
     * @return string
     */
    abstract protected function get_instance_name();
    
    /** Получить список модулей курса в зависимости от типа задания
     * @todo сделать нормальную проверку показывать/скрывать видимые элементы
     * 
     * @return array - массив объектов из таблицы course_modules
     */
    protected function get_course_instances()
    {
        global $DB;
        
        if ( ! $course = $DB->get_record('course', array('id'=>$this->courseid)) )
        {// не удалось получить курс
            return array();
        }
        //получаем все модули текущего курса
		if ( ! $instances = get_all_instances_in_course($this->get_instance_name(), $course) )
        {// нет ни одного модуля такого типа
            return array();
        }
        $result = array();
        if (true)
        {// заготовка для проверки "удалять или не удалять не видимые ученикам элементы"
            // нужно удалить все невидимые элементы
            foreach ( $instances as $id => $instance )
            {
                if ( ! $instance->visible )
                {
                    continue;
                }
                $result[$id] = $instance;
            }
        }else
        {// оставляем все как есть
            $result = $instances;
        }
        return $result;
    }
    
    /** 
     * Отформатировать элементы в соответствии с настройками оформления блока
     * Эта функция переопределяется только в случае, если необходимо дополнительное 
     * форматирование для всех элементов этого типа
     * @return array Формат массива:
     *     [] => object
     *                  time - время выполнения задания в формате unixtime
     *                  name -> html-код объекта
     * 
     * @todo корректно проработать использование css-стилей
     * @todo добавить раскраску в зависимости от того, насколько долго задание не проверено
     */
    protected function format_elements()
    {
        $result = array();
        foreach ($this->elements as $element)
        {// перебираем все найденные элементы (если они есть)
            $res = new stdClass();
            $res->time = $element->time;
            $res->name = $this->format_element_html($element);
            $result[] = $res;
        }
        // возвращаем или пустой, или заполенный массив, но не false и не null
        return $result;
    }
    
    /** 
     * Разметить html-тегами отдельный элемент
     * 
     * @return string - html-код элемента или пустая строка в случае ошибки
     * @param object $element - элемент для форматирования
     */
    protected function format_element_html($element)
    {
        // подгодавливаем переменную
        $result = '';
        if ( ! is_object($element) )
        {// неверный формат передаваемых данных
            return $result;
        }
        // форматируем блок для вывода
        //$result .= '<div style=" border-width:thin; border-style: dotted; margin: 3px; ">';
        $result .= '<div>'.$element->type.': ';
        $result .= $element->name.'</div>';
        $result .= '<div>'.$element->student.'</div>';
        //$result .= '<div style="">'.$element->time.'</div>';
        //$result .= '</div>';
        // возвращаем отформатированную строку
        return $result;
    }
    
    /** 
     * Получить строку, в которой через запятую будут указаны id пользователей, 
     * для которых нужно извлечь задания
     * @return string - список id пользователей через запятую
     * 
     * @todo оптимизировать алгоритм выборки пользователей
     * @todo извлекать всех пользователей курса не через deprecatedlib
     */
    protected function get_course_users()
    {
        global $CFG,$DB;
        require_once($CFG->dirroot.'/enrol/externallib.php');
        
        $result = array();
        
        // получаем роли, которые можно оценивать
        if ( ! $roles = $this->get_graded_roles() )
        {// нет ролей, подлежащих оцениванию - оценивать некого
            return $result;
        }
        
        // получаем id тех пользователей, оценки для которых нам не нужны
        $excludedusers = array();
        // добавляем в список гостя и админа
        $excludedusers[] = 1;
        $excludedusers[] = 2;
        $coursecontext = context_course::instance($this->courseid);
        if ( $this->groupid )
        {// показываем только пользователей указанной группы
            // составляем sql-запрос
            // @todo составить более сложный запрос, c учетом таблицы role_assigments
            // извлекать только пользователей с оцениваемой ролью
            list($enrolledsql, $enrolledparams) = get_enrolled_sql($coursecontext, '', $this->groupid);
        }else
        {// показываем оценки всех пользователей курса
            list($enrolledsql, $enrolledparams) = get_enrolled_sql($coursecontext, '', 0, true);
        }
        // Создаем массив параметров для обращения к API Moodle
        $ctxselect = ", " . context_helper::get_preload_record_columns_sql('ctx');
        $ctxjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = u.id AND ctx.contextlevel = 'CONTEXT_USER')";
        $sqlparams['courseid'] = $this->courseid;
        $sql = "SELECT u.* $ctxselect
                  FROM {user} u $ctxjoin
                 WHERE u.id IN ($enrolledsql)
                 ORDER BY u.id ASC";
        $users = $DB->get_recordset_sql($sql, $enrolledparams);
        
        if ( ! $users )
        {
            return false;
        }
        
        // получаем системный контекст и контекст курса для проверки прав
        
        $systemcontext = context_system::instance();
        
        foreach ( $users as $userid => $user )
        {//удаляем из списка пользователей тех кто может проверять задания
            if ( ! $this->is_graded_user($userid, $coursecontext->id, $systemcontext->id, $roles) )
            {// пользователь не подлежит оцениванию - пропускаем
                continue;
            }
            $result[$userid] = $userid;
        }
        return implode($result, ',');
    }
    
    /** Получить из настроек Moodle роли, которые можно оценивать
     * 
     * @return array - массив id ролей в таблице mdl_roles
     */
    protected function get_graded_roles()
    {
        global $CFG;
        if ( empty($CFG->gradebookroles) OR ! trim($CFG->gradebookroles) )
        {// нет ролей, которые можно оценивать
            return false;
        }
        $roles = explode(",", $CFG->gradebookroles);
        if ( empty($roles) )
        {// нет ролей, которые можно оценивать
            return false;
        }
        return $roles;
    }
    
    /** Определить, подлежит ли пользователь оцениванию
     * 
     * @return bool
     * @param int $userid - id пользователя в таблице mdl_user
     * @param int $coursecontext - id контекста курса, в котором назначен пользователь
     * @param int $systemcontext - id системного контекста
     * @param array $roles - массив id ролей, подлежащих оцениванию
     */
    protected function is_graded_user($userid, $coursecontext, $systemcontext, $roles)
    {
        foreach ( $roles as $roleid )
        {
            if ( user_has_role_assignment($userid, $roleid, $systemcontext) )
            {// сначала проверяем системные роли
                return true;
            }
            if ( user_has_role_assignment($userid, $roleid, $coursecontext) )
            {// пототом проверяем роль в контексте курса
                return true;
            }
        }
        // пользователь не принадлежит ни к одной роли, подлежащей оцениванию - мы не выводим его задания
        return false;
    }
    
    /** Получить список пользователей для которых будут отображаться проверенные задания
     * (учителя, кураторы, администраторы и т. п.)
     * @deprecated эта функция не используется, пока мы не найдем хорошее решение того, 
     * как получить список всех учителей, администратором и курсаторов
     * 
     * @todo выбрать роли из глобальных (или локальных) настроек блока
     *  
     * @return array массив id пользователей в таблице user
     */
    protected function get_graded_user_ids()
    {
        //return array();
        global $CFG;
        
        $users   = array();
        
        if ( ! empty($CFG->gradebookroles) AND ! trim($CFG->gradebookroles) )
        {// есть список ролей, подлежащих оцениванию
            $gradebookroles = explode(",", $CFG->gradebookroles);
        }else
        {
            $gradebookroles = '';
        }
        //print_object($gradebookroles);
        
        if ( $this->groupid )
        {// ищем только среди пользователей группы
            $users = get_role_users($gradebookroles, $context, true, '', 'u.lastname ASC', false, $this->groupid);
        }else
        {// ищем среди всех пользователей курса
            $users = get_role_users($gradebookroles, $context, true, '', 'u.lastname ASC', false);
        }//print_object($users);
        // получаем тех пользователей, оценки для которых нам не нужны
        if ( ! $users OR empty($users) )
        {// нет пользователей - вернем пустой массив
            return array();
        }
        // оставляем только id пользователей
        $users = array_keys($users);
        return $users;
    }
}
?>