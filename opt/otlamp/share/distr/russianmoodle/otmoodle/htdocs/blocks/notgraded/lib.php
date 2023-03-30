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
@raise_memory_limit('10000M');
/**
 * Класс для получения массива объектов с непроверенными заданиями
 *  в одном курсе или массива курсов с непроверенными заданиями
 *
 */
class block_notgraded_items
{
    /**
     * Хранит id курса с которым работаем
     * @var int
     */
    private $courseid;
    private $userid;
    private $viewall;
    
    /**
     * Инициализатор класса
     * @param int $courseid - id курса в котором ищем непроверенные задания
     * @return void
     */
    public function __construct($courseid = null, $userid = null, $viewall = 0)
    {
        global $COURSE, $USER;
        if ( is_null($courseid) )
        {//если курс не передан - берем текущий
            $courseid = $COURSE->id;
        }
        if( $userid === null )
        {
            $userid = $USER->id;
        }
        $this->courseid = $courseid;
        $this->userid = $userid;
        $this->viewall = $viewall;
    }

    /**
     * Получить текущую группу курса
     *
     * @return int|null - id группы или null в случае ошибки
     */
    private function get_current_group()
    {
        global $CFG,$DB, $USER;
        //получаем курс из базы данных - чтобы не портить глобальную переменную
        if ( ! $course = $DB->get_record('course', array('id'=>$this->courseid)) )
        {// не удалось получить курс
            print_error('no_course_in_base');
        }
             
        // получаем текущую группу курса
        $currentgroup = groups_get_course_group($course, true);
        if ( ! $currentgroup )
        {// для правильной работы функций moodle
            $currentgroup = NULL;
        }
        
        return $currentgroup;
    }

    /**
     * Получаем все непроверенные задания курса
     * @param $groupmode - если в курсе включен режим групп и
     * определена текущая группа, искать задания только членов
     * текущей группы ($groupmode = true) или все задания ($groupmode = false)
     * @param int $from - метка времени с которой надо искать непроверенные задания
     * @param int $to - метка времени, до которой надо искать непроверенные задания
     * @return array массив объектов с информацией о непроверенных заданиях
     */
    public function get_course_all_items($groupmode = true, $from = null, $to = null)
    {
        if ( $groupmode )
        {//режим групп надо учесть - получим текущую группу
            $groupid = $this->get_current_group();
        }else
        {//текущей группы нет
            $groupid = null;
        }
        
        //получим типы элементов курса, среди
        //которых надо искать непроверенные задания
        $types = $this->get_course_element_types();
        $items = array();
        foreach ( $types as $type )
        {// получаем непроверенные задания
            $typeitems = $this->get_all_onetypes_items_course(
                         $type, $groupid, $from, $to);
            $items = array_merge($items, $typeitems);
        }
        
        return $items;
    }
    
    /**
     * Возвращает массив элементов курса,
     * которые могут обрабатываться этим блоком
     *
     * @return array
     */
    protected function get_course_element_types()
    {
        global $CFG;
        $type = [];
        if( file_exists($CFG->dirroot.'/mod/assign/lib.php') )
        {
            $type[] = 'assign';
        }
        if( file_exists($CFG->dirroot.'/mod/quiz/lib.php') )
        {
            $type[] = 'quiz';
        }
        return $type;
    }
    
    /**
     * Возвращает все непроверенные задания одного типа
     * @param string  $type - тип задания
     * @param int $groupid - номер группы
     * @param int $timefrom - метка времени с которой надо искать непроверенные задания
     * @param int $timeto - метка времени, до которой надо искать непроверенные задания
     * @return array массив объектов с информацией о непроверенных заданиях какого-то одного типа
     */
    protected function get_all_onetypes_items_course($type, $groupid=null,
                                                $timefrom=null, $timeto=null)
    {
        // объявляем переменную для итогового результата
        $result = '';
        if ( ! $type  )
        {// неправильный формат передаваемых данных
            return $result;
        }
        switch ($type)
        {// обрабатываем все поддерживаемые типы модулей
            // тип "задание" moodle 2.3
            case 'assign':
            //получаем все assignments текущего курса
            return $this->iterate_element_type('assign', $groupid);
            break;
            // тип "эссе"
            case 'quiz':
            // получаем все эссе текущего курса
            return $this->iterate_element_type('quiz', $groupid);
            break;
            // тип формата неизвестен
            default: return $result;
        }
        // возвращаем результат
        return $result;
    }
    
    /**
     * Получить список всех непроверенных элементов курса определенного типа
     * @param string  $type - тип задания
     * @param int $groupid - ID группы, если null, то все группы
     * @param int $timefrom - метка времени с которой надо искать непроверенные задания
     * @param int $timeto - метка времени, до которой надо искать непроверенные задания
     * @return array массив объектов с информацией о непроверенных заданиях какого-то одного типа
     */
    protected function iterate_element_type($type, $groupid=null, $timefrom=null, $timeto=null)
    {
        global $CFG;
        // Подключение библиотек деканата
        if( file_exists($CFG->dirroot . '/blocks/dof/locallib.php') )
        {
            require_once($CFG->dirroot . '/blocks/dof/locallib.php');
            global $DOF;
            $instance_object = $DOF->modlib('ama')->course($this->courseid)->get_instance_object($type, false, false);
            $notgraded_list = $instance_object->get_notgraded_list($timefrom, $timeto, (bool)$this->viewall, $this->userid, $groupid);
            return $notgraded_list;
        } else
        {
            return [];
        }
    }

    /**
     * Возвращает список курсов с количеством непроверенных заданий
     * @return array
     */
    protected function get_notgraded_courses()
    {
        if( ! empty($this->viewall) && has_capability('block/notgraded:viewall', context_system::instance()) )
        {// Если право на просмотр всех заданий и требуется просмотреть все
            $courses = get_courses(null,null,'c.id as id, c.fullname as fullname, c.visible, c.category');
        } else
        {// Если права нет или нужно просмотреть только свои
            $courses = $this->get_enrolled_courses();
        }
        if ( empty($courses) )
        {//не нашли курсы
            return false;
        }
        $ngcourses = array();
        foreach ( $courses as $course )
        {//ищем курсы с непроверенными заданиями
            $context = context_course::instance($course->id);
            if ( ! has_capability('mod/assign:grade', $context, $this->userid) &&
                ! has_capability('mod/quiz:grade', $context, $this->userid) )
            {// У пользователя нет прав на оценку работ в этом курсе
                continue;
            }
            $ci = new $this($course->id, null, $this->viewall);
            $notgraded = $ci->get_course_all_items(false);
            if ( ! empty($notgraded) )
            {//есть непроверенные задания';
                $ngcourses[$course->id]['count'] = count($notgraded);
                $ngcourses[$course->id]['name'] = ($course->fullname);
            }
            unset($ci);
        }
        return $ngcourses;
    }
    
    /**
     * Получить курсы, на которые подписан пользователь
     * @param string $fields поля таблицы курсов, которые необходимо получить с префиксом c.
     * @TODO добавить проверки на доступность курса и активность подписки
     */
    protected function get_enrolled_courses($fields = 'c.id as id, c.fullname as fullname')
    {
        global $DB;
        if( empty($this->userid) )
        {
            return [];
        }
        $sql = 'SELECT ' . $fields . '
            FROM {course} as c
            JOIN {enrol} as e ON e.courseid=c.id
            JOIN {user_enrolments} as ue ON ue.enrolid=e.id
            WHERE ue.userid=?
            GROUP BY c.id, c.fullname';
        $params[] = $this->userid;
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * Форматирует список курсов с непроверенными заданиями
     * для функции print_table() в таблице курсов
     *
     * @return object
     */
    public function get_data_for_table()
    {
        global $CFG;
        // создаем объект для будущей таблицы
        $table = new html_table();
        $table->head = array('№', get_string('course','block_notgraded'),
                get_string('notgraded_total', 'block_notgraded', '<br>'));
        $table->align = array('left', 'center', 'center');
        $table->size = array('10%', '70%', '20%');
        $table->width = '60%';
        // объявляем массив для данных таблицы
        $table->data = array();
        // получаем список курсов, с указанием того, есть ли в них непроверенные задания
        if ( ! $courses = $this->get_notgraded_courses() )
        {// не получили список курсов - вернем пустую таблицу
            $table->data[] = array('', '<b>'.get_string('all_courses_graded', 'block_notgraded').'</b>', '');
            return $table;
        }
        $i = 1;
        foreach ( $courses as $courseid => $notgraded )
        {// перебираем все курсы реестра, узнаем их названия и записывем их в таблицу
            if ( $notgraded )
            {// в курсе есть непроверенные задания - добавим его в список
                // узнаем полное имя курса, и записываем его в таблицу
                $coursename = $notgraded['name'];
                // добавим ссылку на курс, чтобы его можно было сразу просмотреть
                $courselink    = '<a href="'.$CFG->wwwroot.'/blocks/notgraded/workslist.php?courseid='.$courseid.'">'.$coursename.'</a>';
                $table->data[] = array($i, $courselink, '<b>'.$notgraded['count'].'</b>');
                $i++;
            }
        }
        // возвращаем объект таблицы с данными
        return $table;
    }
    
    /**
     * Проверка на наличие прав, подтверждающих, что пользователь может выставлять оценки за интересные нам (подсчитываемые) элементы
     *
     * @param int $userid - идентификатор пользователя
     * @param int $courseid - идентификатор курса, в котором проверяется наличие прав
     *
     * @return boolean
     */
    public function user_has_grade_capability()
    {
        // Контекст курса
        $context = context_course::instance($this->courseid);
        
        // Права, подтверждающие, что пользователь может выставлять оценки за интересные нам подсчитываемые элементы
        $capabilities = [
            'mod/assign:grade',
            'mod/quiz:grade'
        ];
        
        // Если есть хотя бы одно право, результаты из этого курса интересны
        return has_any_capability($capabilities, $context, $this->userid);
    }
    
    /**
     * Вернуть массив курсов, где есть работы, которые необходимо проверить.
     * Если пользователь не имеет прав на оценивание работ ни в одном из курсов,
     * то возвращает false
     *
     * @param $userid - ID пользователя
     */
    public function get_nograted_items_count($userid=null)
    {
        global $USER;
        
        if( $userid === null )
        {
            $userid = $USER->id;
        }
        // Получим список курсов, на которые подписан пользователь
        $courses = enrol_get_all_users_courses($userid);
        
        // Переменная для проверки пользователя на возможность оценивания курсов
        $isteacher = false;
        $ngcourses = [];
        foreach ( $courses as $course )
        {
            
            $ci = new $this($course->id, $userid);
            
            if (!$ci->user_has_grade_capability())
            {
                continue;
            }
            
            // У пользователя есть права на оценку курса
            $isteacher = true;
            
            // Получаем непроверенные работы
            $notgraded = $ci->get_course_all_items(false);
            if ( ! empty($notgraded) )
            {// Есть непроверенные задания
                $ngcourses[$course->id]['count'] = count($notgraded);
                $ngcourses[$course->id]['name'] = ($course->fullname);
            }
            // Уничтожим объект
            unset($ci);
        }
        
        if ( ! $isteacher )
        {// Пользователь не являтся учителем ни в одной из своих курсов
            return false;
        }
        // Вернем массив с неоцененными ответами
        return $ngcourses;
    }
    
}




/**
 * Класс для работы с пользовательским кэшем
 */
class block_notgraded_gradercache
{
    private $userid;

    public function __construct($userid=null)
    {
        global $DB;
        if( $userid === null )
        {
            $this->userid = null;
        } else
        {
            if( $DB->record_exists('user', ['id' => (int)$userid]) )
            {
                $this->userid = (int)$userid;
            } else
            {
                throw new moodle_exception('Couldn\'t get user');
            }
        }
    }
    
    /**
     * Обновление значения пользовательского кэша
     *
     * @param bool $outdatedonly - обновление требуется только в случае, если кэша нет или он устарел
     *
     * @return stdClass - объект кэша
     */
    public function update_cache($outdatedonly=false)
    {
        global $DB;
        
        $cacherecord = $DB->get_record('block_notgraded_gradercache', [
            'graderid' => $this->userid
        ]);
        
        $cachelifetime = get_config('block_notgraded','cache_lifetime');
        if( $cachelifetime === false )
        {
            $cachelifetime = 3600;
        }
        
        if (!empty($cacherecord) && $outdatedonly && (time() - $cacherecord->lastupdate) <= $cachelifetime )
        {// кэш еще протух, не надо обновлять данные, надо вернуть как есть
            return $cacherecord;
        }
        
        
        $countnotgraded = 0;
        
        $bni = new block_notgraded_items();
        $courses = $bni->get_nograted_items_count($this->userid);
            
        if ( ! empty($courses) && is_array($courses))
        {
            foreach ( $courses as $courseid => $course )
            {// Суммирование непроверенных работ
                $countnotgraded += (int)$course['count'];
            }
        }
        


        $freshcache = new stdClass();
        $freshcache->graderid = $this->userid;
        $freshcache->countnotgraded = (int)$countnotgraded;
        $freshcache->lastupdate = time();
        
        if( ! empty($cacherecord) )
        {
            $freshcache->id = $cacherecord->id;
            $DB->update_record('block_notgraded_gradercache', $freshcache);
        } else
        {
            $DB->insert_record('block_notgraded_gradercache', $freshcache);
        }
        
        
        return $freshcache;
    }

    /**
     * Получение объекта кэша
     *
     * @param bool $updateoutdated - разрешено обновление в случае, если кэш устарел
     * @param bool $forceupdate - требуется обновление, не зависимо от того, устарел ли кэш
     * @return stdClass|boolean - объект кэша или false в случае ошибки
     */
    public function get_cache($updateoutdated=false, $forceupdate=false)
    {
        global $DB;
        
        // Принудительное обновление кэша, не зависимо от его устаревания
        if ($forceupdate)
        {
            return $this->update_cache();
        }
        
        // Получение кэша с обновлением, если устарел
        if ($updateoutdated)
        {
            return $this->update_cache(true);
        }
        
        // Получение кэша как есть, в чистом виде (может и не быть, тогда будет false)
        return $DB->get_record('block_notgraded_gradercache',[
            'graderid' => $this->userid
        ]);
        
    }
    
    /**
     * Обновление кэша по всему курсу (для всех преподавателей)
     *
     * @param int $courseid - идентификатор курса
     * @param int $incrementval - если отлично от нуля, то воспринимается как значение, которое необходимо прибавить к текущему значению кэша, не выполняя полный просчет
     */
    public function update_course_cache($courseid)
    {
        if( ! empty($courseid) )
        {
            $coursegraders = [];
            $context = \context_course::instance($courseid);
            $assigngraders = (array)get_enrolled_users($context, 'mod/assign:grade', 0, 'u.*', null, 0, 0, true);
            $quizgraders = (array)get_enrolled_users($context, 'mod/quiz:grade', 0, 'u.*', null, 0, 0, true);
            
            foreach($assigngraders as $assigngrader)
            {
                if( ! in_array($assigngrader->id, $coursegraders) )
                {
                    $coursegraders[] = $assigngrader->id;
                }
            }
            foreach($quizgraders as $quizgrader)
            {
                if( ! in_array($quizgrader->id, $coursegraders) )
                {
                    $coursegraders[] = $quizgrader->id;
                }
            }
            
            foreach( $coursegraders as $k=>$graderid )
            {
                $bngc = new block_notgraded_gradercache($graderid);
                $bngc->update_cache();
            }
        }
        
    }
}

/**
 * Класс форматирования информации об одном непроверенном задании
 *
 */
class block_notgraded_format_item
{

    /**
     * Отформатировать один элемент в соответствии с настройками блока "надо проверить":
     * - заключает проверенное задание в рамку
     * - устанавливает сверху дату и время выполнения задания
     * - раскрашивает дату в зависимости от того, сколько времени задание не проверено
     * - добавляет всплывающие подсказки
     *
     * @return string
     * @param object $element - обрабатываемый элемент
     * @param bool $format - применять расщиренное форматирование или простое
     *
     */
    public function format_element($element, $format = true)
    {
        // @todo вынести все форматирование в css-стили
        // @todo добавить глобальное назначение цветов текста и фона легенды - оно должно зависеть от темы
        // @todo добавить всплывающие надписи "вчера", "сегодня" и т. п.
        $str = '';
        // приводим время к удобному виду
        if ( $element->time - time() > YEARSECS )
        {// показываем год только для прошлогодних заданий
            $stringtime = strftime('%d %b %y' ,$element->time);
        }else
        {// во всех остальных случаях имеев в виду текущий учебный год
            $stringtime = strftime('%d %b %H:%M' ,$element->time);
        }
        if ( $format )
        {
            // выводим рамку со временем
            $str .= '<fieldset title="" style=" padding: 3px; ">';
            $str .= '<legend><b>'.$stringtime.'</b></legend>';
            // раскрашиваем ячейку в нужный цвет
            $str .= '<div>';
            $str .= $element->name;
            $str .= '</div>';
            // закрываем рамку
            $str .= '</fieldset>';
        }else
        {
            $str .= '<div>';
            $str .= $element->name.' '.$stringtime;
            $str .= '</div>';
            
        }
        return $str;
    }
    
    /**
     * Получить цвет непроверенного задания в зависимости от прошедшего времени
     * Используется в случае, если в настройках блока есть устаноквка "использовать цвета"
     * @param int $time - время выполнения задания в формате unixtime
     * @return string - шестнадцатеричное значение цвета в формате RRGGBB
     * @todo закончить эту функцию
     */
    private function get_color_by_time($time)
    {
        return 'ffffff';
    }

}

function user_has_grade_capability_anywhere($userid)
{
    $cache = cache::make_from_params(cache_store::MODE_APPLICATION, 'block_notgraded', 'is_teacher');
    if ($cachedata = $cache->get($userid))
    {
        if ((time() - $cachedata) < 24 * 60 * 60)
        {// кэш свеж
            return true;
        }
    }
    
    // кэша нет (не учитель) или кэш устарел
    
    // Получим список курсов, на которые подписан пользователь
    $courses = enrol_get_all_users_courses($userid);
    foreach($courses as $course)
    {
        $bni = new block_notgraded_items($course->id, $userid);
        if ($bni->user_has_grade_capability())
        {
            // нашелся курс, где пользователь имеет право - считаем теперь его учителем
            $cache->set($userid, time());
            return true;
        }
    }
    
    // пользователь не имеет права ни в одном курсе
    $cache->delete($userid);
    return false;
}