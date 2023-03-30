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
 * API работы с модулями курса
 *
 * @package    modlib
 * @subpackage ama
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение дополнительных библиотек
require_once('class.ama_course_section.php');
require_once('class.ama_course_instance_simplecertificate.php');
require_once('class.ama_course_instance_assignment.php');
require_once('class.ama_course_instance_resource.php');
require_once('class.ama_course_instance_assign.php');
require_once('class.ama_course_instance_quiz.php');
require_once('class.ama_course_instance_subcourse.php');

class ama_course_instance
{

    /**
     * @var int - ID курса
     */
    protected $courseid;
    
    /**
     * @var int - ID модуля
     */
    protected $cmid;
    
    /**
     * @var array - Дополнительные опции
     */
    protected $options;
    
    /**
     * @var object Объект экземпляра
     */
    protected $cm = NULL;
    
    protected $groupid = null;

    /**
     * Конструктор класса
     *
     * @param int|bool $courseid - ID курса
     * @param int|bool $cmid - ID модуля курса
     * @param array $options - Дополнительные опции
     */
    public function __construct($courseid = false, $cmid = false, $options = [])
    {
        $this->courseid = $courseid;
        $this->cmid = $cmid;
        $this->options = $options;
        
        $cm = NULL;
        if ( ! empty($cmid) )
        {// Получение модуля курса
            $cm = get_coursemodule_from_id( '', $cmid);
            if ( empty($cm) )
            {
                $cm = NULL;
            }
        }
        $this->cm = $cm;
        $this->groupid = $this->get_current_group();
    }
    
    /**
     * Получить текущую группу курса
     *
     * @return int|null - id группы или null в случае ошибки
     */
    protected function get_current_group()
    {
        global $CFG, $DB;
        //получаем курс из базы данных - чтобы не портить глобальную переменную
        if( ! $course = $DB->get_record('course', ['id' => $this->courseid]) )
        {// не удалось получить курс
            return null;
        }
    
        // получаем текущую группу курса
        $currentgroup = groups_get_course_group($course, true);
        if( ! $currentgroup )
        {// для правильной работы функций moodle
            $currentgroup = null;
        }
    
        return $currentgroup;
    }

    /**
     * Установить экземпляр модуля
     *
     * @param $cm - Экземпляр модуля
     */
    public function set_cm($cm)
    {
        $this->cm = $cm;
        if ( isset($cm->id) )
        {
            $this->id = $cm->id;
        } else
        {
            $this->id = false;
        }
    }
    
    /**
     * Вернуть экземпляр модуля
     */
    public function get_cm()
    {
        return $this->cm;
    }
    
    /** Удаляет экземпляр модуля из системы
     * @access public
     * @return bool true - удаление прошло успешно
     * false - в иных случаях
     */
    public function delete()
    {
        $returnvalue = (bool) false;

        return (bool) $returnvalue;
    }

    /** Сохраняет экземпляр модуля в БД
     * @access public
     * @param string $name - название экземпляра модуля
     * @param array $options - информация, наполняющая экземпляр модуля
     * @return int id модуля в БД или false
     */
    public function save($name, $options = NULL)
    {
        $returnvalue = (int) 0;

        return (int) $returnvalue;
    }

    /** Возвращает информацию "по умолчанию" для наполнения модуля
     * @access public
     * @param array $options - если параметры, заменяющие значения по умолчанию
     * @return array информация, наполняющая экземпляр модуля
     */
    public function template($obj = null)
    {
        $returnvalue = array();

        return (array) $returnvalue;
    }

    /**
     * Получить объект менеджера по ID экземпляра модуля
     *
     * @param array $options - Дополнительные опции
     */
    public function get_manager($options = [])
    {
        if ( isset($this->cm->modname) )
        {
            $class = 'ama_course_instance_'.$this->cm->modname;
            if ( ! class_exists($class) )
            {// Класс работы с модулем не найден
                return NULL;
            }
            
            $modulemanager = new $class($this->cm);
        } else
        {
            $modulemanager = NULL;
        }
    
        return $modulemanager;
    }
    
    /**
     * Возвращает объект оценки по модулю
     *
     * @return ama_grade
     */
    public function grades()
    {
        require_once(dirname(realpath(__FILE__)).'/class.ama_grade.php');
        
        // Возвращаем экземпляр класса
        return new ama_grade($this->cm->course, $this->cm);
    }
    

    /////////////////////////////////////////////
    //    Методы для работы блока notgraded    //
    /////////////////////////////////////////////
    
    /**
     * Получить неотсортированный список непроверенных учителем элементов из курса
    *
    * @return массив объектов с непроверенными заданиями
    * @param int $timeform[optional] - начало временного периода, за который нужно запрашивать задания
    * @param int $timeto[optional] - конец временного периода, за который нужно запрашивать задания
    * @param bool $viewall - флаг на получение всех заданий или только своих
    * @param int $userid - ID пользователя (проверяющего)
    * @param int $groupid - ID группы, если null, то все группы
    */
    public function get_notgraded_list($timeform = null, $timeto = null, $viewall = false, $userid = null, $groupid = null)
    {
        // извлекаем все необходимые элементы
        $elements = $this->get_manager()->get_notgraded_elements($timeform, $timeto, $viewall, $userid, $groupid);
        // форматируем и возвращаем их
        return $this->format_elements($elements);
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
    protected function format_elements($elements)
    {
        $result = [];
        foreach($elements as $element)
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
        if( ! is_object($element) )
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
    
    /** Получить список модулей курса в зависимости от типа задания
     * @todo сделать нормальную проверку показывать/скрывать видимые элементы
     *
     * @return array - массив объектов из таблицы course_modules
     */
    protected function get_course_instances()
    {
        global $DB;
    
        if( ! $course = $DB->get_record('course', ['id' => $this->courseid]) )
        {// не удалось получить курс
            return [];
        }
        //получаем все модули текущего курса
        if( ! $instances = get_all_instances_in_course($this->get_manager()->get_instance_name(), $course) )
        {// нет ни одного модуля такого типа
            return [];
        }
        $result = [];
        if(true)
        {// заготовка для проверки "удалять или не удалять не видимые ученикам элементы"
            // нужно удалить все невидимые элементы
            foreach($instances as $id => $instance)
            {
                if( ! $instance->visible )
                {
                    continue;
                }
                $result[$id] = $instance;
            }
        } else
        {// оставляем все как есть
            $result = $instances;
        }
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
        global $CFG, $DB, $DOF;
        require_once($CFG->dirroot.'/enrol/externallib.php');
    
        $result = array();
    
        // получаем роли, которые можно оценивать
        if ( ! $roles = get_graded_roles() )
        {// нет ролей, подлежащих оцениванию - оценивать некого
            return $result;
        }
    
        // получаем id тех пользователей, оценки для которых нам не нужны
        $excludedusers = array();
        // добавляем в список гостя и админа
        $excludedusers[] = 1;
        $admins = get_admins();
        foreach($admins as $admin)
        {
            $excludedusers[] = $admin;
        }
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
        $ctxjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = u.id AND ctx.contextlevel = " . CONTEXT_USER . ")";
        $sqlparams['courseid'] = $this->courseid;
        $sql = "SELECT u.* $ctxselect
        FROM {user} u $ctxjoin
        WHERE u.id IN ($enrolledsql)
        ORDER BY u.id ASC";
        $users = $DB->get_recordset_sql($sql, $enrolledparams);
    
        if( ! $users )
        {
            return false;
        }
    
        // получаем системный контекст и контекст курса для проверки прав
    
        $systemcontext = context_system::instance();
    
        foreach($users as $userid => $user)
        {//удаляем из списка пользователей тех кто может проверять задания
            if( ! $DOF->modlib('ama')->user($userid)->is_graded_user($coursecontext->id, $systemcontext->id, $roles) )
            {// пользователь не подлежит оцениванию - пропускаем
                continue;
            }
            $result[$userid] = $userid;
        }
        return implode($result, ',');
    }
}

?>