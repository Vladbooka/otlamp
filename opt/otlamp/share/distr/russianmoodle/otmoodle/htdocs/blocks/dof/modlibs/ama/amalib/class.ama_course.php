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

require_once(dirname(realpath(__FILE__)) . "/../../../../../config.php");

global $CFG;

//Все в этом файле написано на php5.
//Проверяем совместимость с ПО сервера
if ( 0 > version_compare(PHP_VERSION, '5') )
{
    die('This file was generated for PHP 5');//если ниже php5, то кончаем работу
}
// Подключаем базовый класс
require_once(dirname(realpath(__FILE__)).'/class.ama_base.php');
//Подключаем класс для работы с содержанием курса
require_once(dirname(realpath(__FILE__)).'/class.ama_course_content.php');
//Подключаем класс для работы с метакурсом
require_once(dirname(realpath(__FILE__)).'/class.ama_course_metacourse.php');
//Подключаем класс для работы с ролями членов курса
require_once(dirname(realpath(__FILE__)).'/class.ama_course_roles.php');
//Подключаем класс для работы с группами
require_once(dirname(realpath(__FILE__)).'/class.ama_course_groups.php');
require_once(dirname(realpath(__FILE__)) . '/../../../../../course/lib.php');

/** Класс для работы с курсом
 * @access public
 */
class ama_course extends ama_base
{
	/** Проверяет существование курса
	 * Проверяет существование в таблице записи с указанным id
	 * и возвращает true или false
	 * @return bool
	 */
	public function is_exists($id=null)
	{
	    global $DB;
		if (is_null($id))
		{
			$this->require_real();
			$id = $this->get_id();
		}
        if ( ama_utils_is_intstring($id) )
        {// переланный id курса является числом, все нормально
            $id = intval($id);
        }else
        {// переданный id не является числом - вернем всесто него 0
            $id = 0;
        }
        
		return $DB->record_exists('course', array('id' => intval($id)));
	}
	/** Проверить, является ли эта страница - главной страницей
	 * и возвращает true или false
	 * @return bool
	 */
	public function is_mainpage($id=null)
	{
		if (is_null($id))
		{
			$this->require_real();
			$id = $this->get_id();
		}
		return intval($id) == SITEID;
	}
	/** Курс существует и не является мета-курсом
	 * и возвращает true или false
	 * @return bool
	 */
	public function is_course($id=null)
	{
		return $this->is_exists($id) AND intval($id) != SITEID;
	}
	
	/**
	 * Курс существует и не является мета-курсом
	 * и возвращает true или false
	 *
	 * @return bool
	 */
	public function is_user_coursecompletion($userid = null)
	{
	    $this->require_real();
          
	    $sql = "SELECT u.id FROM {user} as u
                LEFT JOIN {course_completions} as cc ON u.id = cc.userid
                INNER JOIN {course} as c ON cc.course = c.id
                WHERE (c.id = :courseid) AND (u.deleted = 0) AND (u.id = :userid) AND (cc.timecompleted IS NOT NULL)";
	    global $DB;
	    return (bool)$DB->get_records_sql($sql, ['courseid' => $this->get()->id, 'userid' => $userid]);
	}
	
	/** Создает объект и возвращает его id
     * @param mixed $obj - параметры объекта или null для параметров по умолчанию
	 * @return mixed
	 */
    public function create($obj=null)
    {
        global $DB;
	    // Пропускаем объект через шаблон
		$course = $this->template($obj);

		//Добиваемся уникальности короткого имени
		$shortname = $course->shortname;
		$i=1;
        
		while ( $DB->get_record('course', array('shortname' => $course->shortname)) )
		{
			$course->shortname = $shortname.$i;
			++$i;
		}
		// Создаем курс в БД
		$rec = create_course($course);//записываем его в БД
		if(is_object($rec))
		{
			return $rec->id;
		}else
		{
			return false;
		}
    }
    /** Возвращает информацию по умолчанию о курсе
     * Это значения полей по умолчанию для таблицы _course
     * @access protected
     * @param stdClass $data - массив значений, которые переопределяют
     * соответствующие параметры по умолчанию
     * @return object параметры по умолчанию для нового курса
     */
    public function template($data = NULL)
    {
		global $CFG;
        // Default courseinfo
		$course = new stdClass();
		$course->category = $CFG->defaultrequestcategory;
		$course->sortorder = 100;
		$course->password = '';
		$course->fullname = 'Новый курс';
		$course->shortname = 'new'.date('ymdhis',time()).substr(md5($_SERVER['REMOTE_ADDR'].$_SERVER['REMOTE_PORT'].microtime()), 0, 2);
		$course->idnumber = '';
		$course->summary = '';
		$course->format = 'topics';
		$course->showgrades = '1';
		$course->modinfo = '';
		$course->newsitems = '5';
		$course->teacher = 'Преподаватель';
		$course->teachers = 'Преподаватели';
		$course->student = 'Учащийся';
		$course->students = 'Учащиеся';
		$course->guest = 0;
		$course->startdate = time();
		$course->enrolperiod = 0;
		$course->numsections = 10;
		$course->marker = 0;
		$course->maxbytes = 1024 * 500;
		$course->showreports = 0;
		$course->visible = 1;
		$course->hiddensections = 0;
		$course->groupmode = 0;
		$course->groupmodeforce = 0;
		$course->defaultgroupingid = 0;
		$course->lang = '';
		$course->theme = '';
		$course->cost = '';
		$course->currency = 'RUR';
		$course->timecreated = time();
		$course->timemodified = time();
		$course->metacourse = 0;
		$course->requested = 0;
		$course->restrictmodules = 0;
		$course->expirynotify = 0;
		$course->expirythreshold = 0;
		$course->notifystudents = 0;
		$course->enrollable = 1;
		$course->enrolstartdate = 0;
		$course->enrolenddate = 0;
		$course->enrol = '';
		$course->defaultrole = 0;
		$course = $course;
		// Implict couseinfo
		if (!is_null($data))
		{
			foreach ($data as $key=>$val)
			{
				$course->$key = $val;
			}
		}
		return $course;
    }

    /** Возвращает информацию о курсе из БД
     * @access public
     * @return object массив типа параметр=>значение
     */
    public function get()
    {
        global $DB;
		$this->require_real();
        return $DB->get_record('course', array('id' => $this->get_id()));
    }
    /** Обновляет информацию о курсе в БД
     * @access public
     * @param array $metainfo - индексированный массив типа параметр=>значение
     * с информацией для записи в таблицу
     * @param bool $replace - false - надо обновить запись курса
     * true - записать новую информацию в курс
     * @return mixed id курса или false
     */
    public function update($metainfo, $replace = false)
    {
		$this->require_real();
		if ($replace !== true)
		{
			// Merge new data with old data
			$old = $this->get();
			$metainfo = (object) array_merge((array)$old,(array)$metainfo);
		}
        // Reset course id
		$metainfo->id = $this->get_id();
        if (update_course($metainfo))
        {
			return $this->get_id();
        }else{
			return false;
        }
    }
    
    /**
     * Получить объект сайта
     *
     * @return stdClass
     */
    public function get_site()
    {
        return get_site();
    }

    /** Удаляет запись о курсе из таблици _course
     * @access public
     * @return bool true - удаление прошло успешно
     * false в противном случае
     */
    public function delete()
    {
        global $DOF;
        
		$this->require_real();
		if(!$course = $this->get())
		{	// Course already absent
			return true;
		}
		// Фиксируем в логах
		$DOF->add_to_log('modlib', 'ama', 'delete_course', "view.php?id={$this->get_id()}", "$course->fullname (ID {$this->get_id()})");
		
		// Delete course without feedback
		$returnvalue = delete_course($this->get_id(),false);
		 //update course count in catagories
		fix_course_sortorder();
		// Reset course id from this object
		$this->set_id(false);
        return $returnvalue;
    }

    
    /** Проверяет, является ли курс метакурсом
     * @access public
     * @return bool true - текущий курс является метакурсом
     * false - в иных случаях
     *
     * @todo выяснить есть ли в Moodle 2 метакурсы
     */
    public function is_metacourse()
    {
        global $DB;
		$this->require_real();
        return false; //(bool) $DB->get_field('course','metacourse', array('id' => $this->get_id()));
    }

    /** Делает текущий курс метакурсом
     * @access public
     * @return bool true - текущий курс стал метакурсом
     * false - в иных случаях
     *
     * @todo выяснить есть ли в Moodle 2 метакурсы
     */
    public function set_metacouse()
    {
        global $DB;
		$this->require_real();
        return true;//(bool) $DB->set_field('course','metacourse',1, array('id' => $this->get_id()) );
    }

    /** Сделать текущий (мета)курс обычным курсом
     * @access public
     * @return bool - текущий курс - обычный курс
     * false - в иных случаях
     *
     * @todo выяснить есть ли в Moodle 2 метакурсы
     */
    public function unset_metacourse()
    {
        global $DB;
		$this->require_real();
        return true;//(bool) $DB->set_field('course','metacourse',0,array('id',$this->get_id()) );
    }

    /** Возвращает число всех членов курса
     * @access public
     * @return int общее число членов курса или ноль
     */
    public function count_courseusers()
    {
		$this->require_real();
        $returnvalue = (int) 0;

        return (int) $returnvalue;
    }

    /** Возвращает массив всех кто зарегистрирован на курсе
     * @access public
     * @return array массив id всех членов курса или false
     */
    public function get_courseusers()
    {
		$this->require_real();
        $returnvalue = array();

        return (array) $returnvalue;
    }
    
    /** Возвращает ссылку на курс Moodle
     * @access int $id - id курса Moodle
     * @return ссылку на курс
     */
    public function get_link($id=null)
    {
        global $CFG;
        if ( ! $id )
        {
            $id = $this->get_id();
        }
        return $CFG->wwwroot.'/course/view.php?id='.$id;
    }

    /** Возвращает формат курса
     * @access public
     * @return string название формата курса или false
     */
    public function get_format()
    {
		$this->require_real();
        $metainfo = $this->get();
        return (string) $metainfo->format;
    }
    /** Возвращает объект для работы с подпиской метакурса
     * @access public
     * @return object - экземпляр от ama_course_metacourse или false
     *
     * @todo выяснить есть ли в Moodle 2 метакурсы
     */
    public function metacourse()
    {
		$this->require_real();
        return $this->get();//new ama_course_metacourse($this->get_id());
    }

    /** Возвращает объект для работы с ролями курса
     * @param int $tole_id - id роли или false
     * @access public
     * @return object - экземпляр от ama_course_roles
     */
    public function role($roleid=false,$type='student')
    {
		$this->require_real();
        return new ama_course_roles($this->get_id(),$roleid,$type);
    }
    
    /**
     * Возвращает объект для работы с подписками курса
     * @param int $enrolid идентификатор инстанса подписки на курс
     * @return ama_course_enrol_manager
     */
    public function enrol_manager($enrolid)
    {
        // Подключение класса для работы с подписками курса
        require_once('class.ama_course_enrol_manager.php');
        
        // Получение ID курса
        $courseid = $this->get_id();
        
        // Создание класса работы с подписками курса
        $enrol = new ama_course_enrol_manager($courseid, $enrolid);
        
        return $enrol;
    }

    /** Объект для работы с элементами курса
     * @access public
     * @return object - экземпляр от ama_course_content или false
     */
    public function content()
    {
		$this->require_real();
		// Возвращаем экземпляр нужного класса, в зависимости от формата курса
        switch ($this->get_format())
        {
			case 'topics':
				return new ama_course_content_topics($this->get_id());
			break;
			case 'weeks':
				return new ama_course_content_weeks($this->get_id());
			break;
			case 'social':
				return new ama_course_content_social($this->get_id());
			break;
        }
    }
    
    /** Подписывает пользователя на курс на период
     * @param stdclass Object $user - обьект, содержащий все данные о пользователе (извлеченый из таблицы mdl_users)
     * @param int $start - дата начала обучения (метка времени)
     * @param int $end - дата окончания обучения (метка времени)
     * @param string $enroltype - тип подписки пользователя на курс
     * @return - true or false
     */
    function enrol_for_duration($user, $start, $end, $enroltype='email')
    {
        global $DOF;
        
        $course = $this->get($this->get_id());
        if( ! is_object($user) )
        {//пользователь в неправильном формате
            return false;
        }
        // приводим дату к полуночи
        $timestart = make_timestamp(date('Y', $start), date('m', $start), date('d', $start), 0, 0, 0);
        $timeend = make_timestamp(date('Y', $end), date('m', $end), date('d', $end), 0, 0, 0);
        //получаем роль по умолчанию для курса
        if ( $role = get_default_course_role($course) )
        {
            //получаем контекст курса
            if ( class_exists('context_course') )
            {// начиная с moodle 2.6
                $context = context_course::instance($course->id);
            }else
            {// оставим совместимость с moodle 2.5 и менее
                $context = get_context_instance(CONTEXT_COURSE, $course->id);
            }
            //подписываем пользователя на период с ролью по умолчанию
            if ( ! role_assign($role->id, $user->id, 0, $context->id, $timestart, $timeend, 0, $enroltype) )
            {//не подписали
                return false;
            }
            // force accessdata refresh for users visiting this context...
            mark_context_dirty($context->path);
            email_welcome_message_to_user($course, $user);
            
            // Фиксируем в логах
            $DOF->add_to_log('modlib', 'ama', 'enrol_for_duration', 'view.php?id='.$course->id, $user->id);
            
            return true;
        }
        return false;
    }
    
    /**
     * Функция для обращения к классу для работы с группами
     * @param int $id - id группы, к которой обращаемся, (или null чтобы создать новую)
     * @return ama_course_groups Object - обьект для работы с группами
     */
    public function group($id = null)
    {
        $courseid = $this->get_id();
        $group = new ama_course_groups($courseid, $id); // передаем в конструктор id курса в обязательном порядке
        return $group;
    }
    
    /** Возвращает список групп курса
     * @access public
     * @param int $courseid - id курса, для которого возвращается список групп
     * @return array массив id всех групп курса или false
     *
     * @todo вызов этой функции только в случае id === false кажется мне нецелесообразным. Обсудить и переделать.
     */
    public function listing($courseid)
    {
        global $DB;
        if($this->id === false)
        {
            $groups = array();
            $groups = $DB->get_records('groups', array('courseid' => $courseid));
            
            return (array) $groups;
        }
        else
        {
            return false;
        }
    }
    
    /** Функция взята из модуля ama2.php и  немного модифицирована
    * Проверяет является ли пользователь студентом текущего курса
    * @param int $userid - id пользователя Moodle
    * @return bool
    */
    function is_course_student($userid)
    {
        $userid = intval($userid);
        if(!$userid)
        {//если равен нулю
            return false;
        }
        $courses = enrol_get_users_courses($userid);//получили все курсы студента
        return array_key_exists($this->get_id(), $courses);
    }
    /** Получить список записей критериям
     *
     * @return array|bool массив записей из таблицы mdl_сurse или false
     * @param array $options - массив условий в формате 'название_поля' => 'значение'
     * @param string $sort[optional] - в каком направлении и по каким полям производится сортировка
     * @param string $fields[optional] - поля, которые надо возвратить
     * @param int $limitfrom[optional] - id, начиная с которого надо искать
     * @param int $limitnum[optional] - максимальное количество записей, которое надо вернуть
     */
    public function get_list($options=null, $sort='', $fields='*', $limitfrom=0, $limitnum=0)
    {
        global $CFG, $DB;
        $select = '';
        if ( ! is_null($options) AND ! is_array($options) )
        {// передан неправильный формат данных
            return false;
        }
        if ( ! empty($options) )
        {// если у нас есть условия - подставим мх в запрос
            foreach ( $options as $field =>$value )
            {// перебираем все условия и в цикле составляем запрос
                if ( ! $select )
                {// если это первый фрагмент запроса - то не добавляем условие AND
                    $select .= $this->query_part_select($field, $value);
                }else
                {// для второго и последующих условий - добавим
                    $select .= ' AND '.$this->query_part_select($field, $value);
                }
            }
        }
        
        return $DB->get_records_select('course',
            $select, null, $sort, $fields, $limitfrom, $limitnum);
    }
    
    /**
     * Получить каталог курсов, разбитых по категориям
     *
     * @param bool $splitresult - разделить результат: отдельно категории, отдельно курсы
     *
     * @return array массив с информаицей о курсах и категориях
     */
    public function get_courses_catalogue($splitresult=false)
    {
        global $DB;
        
        if( $splitresult )
        {
            $coursescatalogue = [
                'courses' => [],
                'categories' => []
            ];
        } else
        {
            $coursescatalogue = [];
        }
        
        $courses = $DB->get_records_sql("
            SELECT c.id as id, c.fullname as fullname, cat.id as categoryid, cat.name as categoryname
            FROM mdl_course as c
            JOIN mdl_course_categories as cat ON cat.id=c.category
            ORDER BY cat.name, cat.id, c.fullname, c.id");
        
        if( ! empty($courses) )
        {
            if( $splitresult )
            {
                foreach($courses as $course)
                {
                    if( empty($coursescatalogue['categories'][$course->categoryid]) )
                    {
                        $coursescatalogue['categories'][$course->categoryid] = $course->categoryname;
                    }
                    if( empty($coursescatalogue['courses'][$course->categoryid][$course->id]) )
                    {
                        $coursescatalogue['courses'][$course->categoryid][$course->id] = $course->fullname;
                    }
                }
            } else
            {
                foreach($courses as $course)
                {
                    if( empty($coursescatalogue[$course->categoryid]) )
                    {
                        $coursescatalogue[$course->categoryid] = [
                            'name' => $course->categoryname,
                            'courses' => []
                        ];
                    }
                    $coursescatalogue[$course->categoryid]['courses'][$course->id] = $course->fullname;
                }
            }
        }
        
        return $coursescatalogue;
    }
    
    /**
     * Получить каталог курсов, разбитых по категориям по переданному массиву идентификаторов курсов
     *
     * @param array coursesids - массив идентификаторов курсов
     * @param bool $splitresult - разделить результат: отдельно категории, отдельно курсы
     *
     * @return array массив с информаицей о курсах и категориях
     */
    public function get_courses_catalogue_on_transfered_coursesids($coursesids = [], $splitresult = false)
    {
        if ( empty($coursesids) )
        {
            // массив переданных курсов пустой, вернем результат по всем курсам в системе
            return $this->get_courses_catalogue($splitresult);
        }
        
        global $DB;
        
        if( $splitresult )
        {
            $coursescatalogue = [
                'courses' => [],
                'categories' => []
            ];
        } else
        {
            $coursescatalogue = [];
        }
        
        $courses = $DB->get_records_sql("
            SELECT c.id as id, c.fullname as fullname, cat.id as categoryid, cat.name as categoryname
            FROM mdl_course as c
            JOIN mdl_course_categories as cat ON cat.id=c.category
            ORDER BY cat.name, cat.id, c.fullname, c.id");
        
        if( ! empty($courses) )
        {
            if( $splitresult )
            {
                foreach($courses as $course)
                {
                    if ( in_array($course->id, $coursesids) )
                    {
                        if( empty($coursescatalogue['categories'][$course->categoryid]) )
                        {
                            $coursescatalogue['categories'][$course->categoryid] = $course->categoryname;
                        }
                        if( empty($coursescatalogue['courses'][$course->categoryid][$course->id]) )
                        {
                            $coursescatalogue['courses'][$course->categoryid][$course->id] = $course->fullname;
                        }
                    }
                }
            } else
            {
                foreach($courses as $course)
                {
                    if ( in_array($course->id, $coursesids) )
                    {
                        if( empty($coursescatalogue[$course->categoryid]) )
                        {
                            $coursescatalogue[$course->categoryid] = [
                                'name' => $course->categoryname,
                                'courses' => []
                            ];
                        }
                        $coursescatalogue[$course->categoryid]['courses'][$course->id] = $course->fullname;
                    }
                }
            }
        }
        
        return $coursescatalogue;
    }
    
    /**
     * Получить cписок всех доступных курсов текущему пользователю
     *
     * @param string $categoriyd
     * @param string $sort
     * @param string $fields
     *
     * @return stdClass[]
     */
    public function get_courses_available($categoryid="all", $sort = "c.sortorder ASC", $fields = "c.id,c.fullname")
    {
        return get_courses($categoryid, $sort, $fields);
    }
    
    /**
     * Получить cписок всех курсов в системе
     *
     * @param string $categoriyd
     * @param string $sort
     * @param string $fields
     *
     * @return stdClass[]
     */
    public function get_courses_all()
    {
        global $DB;
        return $DB->get_records_sql("
            SELECT c.id as id, c.fullname as fullname, cat.id as categoryid, cat.name as categoryname
            FROM mdl_course as c
            JOIN mdl_course_categories as cat ON cat.id=c.category
            ORDER BY cat.name, cat.id, c.fullname, c.id");
    }
    
    /**
     * Возвращает фрагмент sql-запроса после слова WHERE,
     * который определяет параметры выборки
     * @param string $field - название поля
     * @param mixed $value - null, string или array
     * @return mixed string - фрагмент sql-запроса
     * если $value - null, то пустая строка
     * если $value - строка, то "поле = значение"
     * если $value - массив, то "поле IN(знач1, знач2, ... значN)"
     * если массив пуст или это не массив и не строка и не null,
     * то вернется bool false
     *
     * @todo это дублирование функции из storage_base. Нужно будет потом найти способ от него избавится.
     */
    public function query_part_select($field, $value = null)
    {
        if ( ! is_scalar($field) OR is_bool($field) )
        {//название поля неправильного типа';
            return false;
        }
        if ( is_null($value) OR ! $field )
        {//значение поля не передано';
            return '';
        }
        if ( is_scalar($value) AND ! is_bool($value) )
        {//значение только одно';
            return "{$field} = '{$value}'";
        }
        if ( is_array($value) AND ! empty($value) )
        {//значений несколько';
            $isnull = '';
            foreach ( $value as $k => $v )
            {//разберемся, что передано в массиве,
                if (is_null($v) )
                {//передан элемент null
                    //сформируем фрагмент запроса IS NULL
                    $isnull = $field.' IS NULL ';
                    //уберем null из массива во избежание ошибок
                    unset ($value[$k]);
                }elseif( is_scalar($v) )
                {//передано что надо - превращаем в строку
                    $value[$k] = '\''.$v.'\'';
                }else
                {//передано то, что не надо было передавать
                    return false;
                }
            }
            if ( empty($value) )
            {//в массиве были только элементы null
                return $isnull;
            }
            //если в массиве еще что-то осталось
            $str = implode(',',$value);
            if ( $isnull )
            {// Нужно сравнивать с null-значением
                return "({$field} IN({$str}) OR {$isnull})";
            }else
            {// не нужно сравнивать с null-значением
                return "({$field} IN({$str}))";
            }
        }else
        {//не массив или пустой массив';
            return false;
        }
        //на всякий случай, если передали нечто неизвестное';
        return false;
    }
    
    /** Объект для работы с оценками курса
     * @access public
     * @return object - экземпляр от ama_course_grade или false
     */
    public function grade()
    {
        require_once(dirname(realpath(__FILE__)).'/class.ama_grade.php');
        $this->require_real();

        // Возвращаем экземпляр класса
        return new ama_grade($this->get_id());
    }
    
    /** Получить количество непроверенных заданий в курсе
     * @todo нужно будет либо кардинально переделать эту функцию, вынеся
     * все функции для работы с элементами в соответствующие классы ama
     * либо сделать рефакторинг самого модуля ama. Я склоняюсь ко второму варианту.
     *
     * @return int
     * @param int $groupid[optional] - id группы в moodle
     *         (если нужно подсчитать только непроверенные задания указанной группы)
     * @param int $begindate[optional] - Начало периода, за который собираются данные
     * @param int $enddate[optional] - Конец периода, за который собираются данные
     */
    public function count_notgraded_elements($groupid=null, $begindate=null, $enddate=null)
    {
        $result = 0;
        
        $notgraded_assignments = $this->get_instance_object('assign', false, false)->get_manager()->get_notgraded_list($begindate, $enddate);
        $result += count($notgraded_assignments);
        $notgraded_essays = $this->get_instance_object('quiz', false, false)->get_manager()->get_notgraded_list($begindate, $enddate);
        $result += count($notgraded_essays);
        
        return $result;
    }
    
    /** Подсчитать количество выполненных работ в курсе
     *
     * @param int $userid[optional] - id ученика, для которого нужно подсчитать количество
     *                        выполненных работ. Если не указано - то будут получено
     *                        количество выполненных работ во всем курсе.
     * @param int $begindate[optional] - Начало периода, за который собираются данные
     * @param int $enddate[optional] - Конец периода, за который собираются данные
     * @return int
     */
    public function count_submitted_elements($userid=null, $begindate=null, $enddate=null)
    {
        $result = 0;
        // считаем выполненные задания
        $result += $this->count_submitted_assignments($userid, null, $begindate, $enddate);
        // считаем выполненные эссе
        $result += $this->count_submitted_essay($userid, null, $begindate, $enddate);
        
        return $result;
    }
    
    /** Подсчитать количество заданий, проверенных учителем
     *
     * @return int
     * @param object $teacherid - id учителя в moodle
     * @param int $begindate[optional] - Начало периода, за который собираются данные
     * @param int $enddate[optional] - Конец периода, за который собираются данные
     */
    public function count_graded_elements($teacherid, $begindate=null, $enddate=null)
    {
        $result = 0;
        // считаем проверенные задания
        $result += $this->count_graded_assigments($teacherid, $begindate, $enddate);
        // считаем проверенные эссе
        //$result += $this->count_graded_quiz($teacherid, $begindate, $enddate);
        
        return $result;
    }
    
    /** Подсчитать количество заданий, проверенных учителем
     *
     * @return int
     * @param int $teacherid  - id учителя в moodle
     * @param int $begindate[optional] - Начало периода, за который собираются данные
     * @param int $enddate[optional] - Конец периода, за который собираются данные
     */
    protected function count_graded_assigments($teacherid, $begindate=null, $enddate=null)
    {
        $result = 0;
        global $CFG, $DB;
        // получаем все задания курса
        $assignments = $this->get_visible_instances('assign');
        
        // если заданы ограничения по периоду - составим sql
        $datelimits = $this->create_datelimit_sql('a.timecreated', $begindate, 'a.timemodified', $enddate);
        
        foreach ($assignments as $assignment)
		{//среди всех заданий ищем непроверенные
			$result += $DB->count_records_sql("SELECT COUNT(*) ".
							"FROM {$CFG->prefix}assign_grades a ".
							"WHERE a.assignment = '{$assignment->id}'
									AND (a.grader = $teacherid ) ".$datelimits);
        }
        
        return $result;
    }
    
    /** Посчитать все эссе, проверенные учителем
     * @todo дописать эту функцию - сейчас нет времени
     *
     * @return int
     * @param int $teacherid - id учителя, проверенные задания которого ищутся
     * @param int $begindate[optional] - Начало периода, за который собираются данные
     * @param int $enddate[optional] - Конец периода, за который собираются данные
     */
    protected function count_graded_quiz($teacherid, $begindate=null, $enddate=null)
    {
        return 0;
    }
    
    /** Получить количество непроверенных заданий
     *
     * @return int
     * @param int $groupid[optional] - id группы в moodle
     *         (если нужно подсчитать только непроверенные задания указанной группы)
     * @param int $begindate[optional] - Начало периода, за который собираются данные
     * @param int $enddate[optional] - Конец периода, за который собираются данные
     */
    protected function count_notgraded_assigments($groupid=null, $begindate=null, $enddate=null)
    {
        $result = 0;
        global $CFG, $DB;
        // получаем все задания курса
        $assignments = $this->get_visible_instances('assign');
        // если заданы ограничения по периоду - составим sql
        $datelimits = $this->create_datelimit_sql('a.timecreated', $begindate, 'a.timemodified', $enddate);
        // найдем все задания
        $result = $this->count_submitted_assignments(null,$groupid);
		foreach ($assignments as $assignment)
		{//вычтем из них проверенные
			$result -= $DB->count_records_sql("SELECT COUNT(*) ".
							"FROM {$CFG->prefix}assign_grades a ".
							"WHERE a.assignment = '{$assignment->id}'".$datelimits);
        }
        
        return $result;
    }
    
    /** Получить количество непроверенных эссе
     * @todo включить эту функцию когда будет написана функция подсчета проверенных эссе
     *
     * @return int
     * @param int $groupid[optional] - id группы в moodle
     *         (если нужно подсчитать только непроверенные задания указанной группы)
     * @param int $begindate[optional] - Начало периода, за который собираются данные
     * @param int $enddate[optional] - Конец периода, за который собираются данные
     */
    protected function count_notgraded_quiz($groupid=null, $begindate=null, $enddate=null)
    {
        // @todo подсчет непроверенных эссе временно отключен для ускорения быстродействия
        // и до того момента когда будет дописана функция подсчета проверенных эссе
        return 0;
        
        
        global $CFG, $DB;
        // подключаем библиотеку работы с тестами
		require_once($CFG->dirroot . '/mod/quiz/locallib.php');
        // получаем все задания курса
		$quizes = $this->get_visible_instances('quiz');
		// собираем id пользователей в массив
        if ( ! $userids = $this->get_graded_users($groupid) )
        {//пользователей нет - значит и заданий нет
            return 0;
        }
        // получили id всех вопросов типа эссе во всех quiz
		$all_question = $this->leave_only_essay($quizes);
		//будет хранить количество всех непроверенных эссе
		$data = 0;
        
        // если заданы ограничения по периоду - составим sql
        $datelimits = $this->create_datelimit_sql('qa.timestart', $begindate, 'qa.timemodified', $enddate);
        
		foreach( $all_question as $quiz_id => $questions )
		{
		    foreach ($questions as $q)
			{//ищем неоцененные попытки ответов
				//получаем непроверенные попытки ответов на нужный вопрос
				$attempts = $DB->get_records_sql("SELECT qa.* ".
										"FROM {$CFG->prefix}quiz_attempts qa, {$CFG->prefix}question_sessions qs ".
										"WHERE	quiz = $quiz_id ".
											"AND qa.timefinish > 0 ".
											 "AND qa.userid IN ($userids) AND qa.preview = 0 ".
											 "AND qs.questionid = '$q->id'".$datelimits);
				if ($attempts)
				{//если есть ответ на quiz с нужным вопросом';
					foreach ($attempts as $attempt)
					{// перебираем все попытки ответа на вопрос, и ищем среди них неоцененные
						if ( ! $this->quiz_is_graded($attempt, $q->id))
                        {
                            $data++;
                        }
					}
				}
			}
		}
		return $data;
    }
    
    /** Подсчитать отправленные задания в курсе
     * @todo перенести в класс assignment
     *
     * @return int
     * @param int $userid[optional] - id ученика, для которого нужно подсчитать количество
     *                        выполненных работ. Если не указано - то будут получено
     *                        количество выполненных работ во всем курсе.
     * @param int $groupid[optional] - id группы в moodle
     *         (если нужно подсчитать только выполненные задания указанной группы)
     * @param int $begindate[optional] - Начало периода, за который собираются данные
     * @param int $enddate[optional] - Конец периода, за который собираются данные
     */
    protected function count_submitted_assignments($userid=null, $groupid=null,  $begindate=null, $enddate=null)
    {
        $result = 0;
        global $CFG, $DB;
        // получаем все задания курса
        $assignments = $this->get_visible_instances('assign');
		// получаем id пользователей для которых нужно искать задания
        $usercondition = $this->get_userlist_sql($userid, $groupid);
        
        // если заданы ограничения по периоду - составим sql
        $datelimits = $this->create_datelimit_sql('a.timecreated', $begindate, 'a.timemodified', $enddate);
        
		foreach ($assignments as $assignment)
		{// для каждого задания ищем ответы ученика и считаем их
			$result += $DB->count_records_sql("SELECT COUNT(*) ".
							"FROM {$CFG->prefix}assign_submission a ".
							"WHERE a.userid {$usercondition}
									AND a.assignment = '{$assignment->id}'".$datelimits);
        }
        
        return $result;
    }
    
    /**
     * Подсчитать количество отправленных эссе
     *
     *
     * @param int $userid[optional] - id ученика, для которого нужно подсчитать количество
     *                        выполненных работ. Если не указано - то будут получено
     *                        количество выполненных работ во всем курсе.
     * @param int $begindate[optional] - Начало периода, за который собираются данные
     * @param int $enddate[optional] - Конец периода, за который собираются данные
     *
     * @return int - Число отправленных эссе
     */
    protected function count_submitted_quiz($userid=null, $groupid=null, $begindate=null, $enddate=null)
    {
        $result = 0;
        global $CFG, $DB;
        // подключаем библиотеку работы с тестами
		require_once($CFG->dirroot . '/mod/quiz/locallib.php');

        // получаем все задания курса
		$quizes = $this->get_visible_instances('quiz');
		
		// получаем id пользователей для которых нужно искать задания
        $usercondition = $this->get_userlist_sql($userid);
        // получили id всех вопросов типа эссе во всех quiz
		$all_question = $this->leave_only_essay($quizes);
        
        // если заданы ограничения по периоду - составим sql
        $datelimits = $this->create_datelimit_sql('qa.timestart', $begindate, 'qa.timemodified', $enddate);
        
		foreach( $all_question as $quiz_id => $questions )
		{

		    foreach ($questions as $q)
			{//ищем попытки ответов
				$attempts = $DB->get_records_sql("SELECT qa.* ".
										"FROM {$CFG->prefix}quiz_attempts qa, {$CFG->prefix}question_sessions qs ".
										"WHERE	quiz = {$quiz_id} ".
											"AND qa.timefinish > 0 ".
											 "AND qa.userid {$usercondition} AND qa.preview = 0 ".
											 "AND qs.questionid = '{$q->id}'".$datelimits);
                if ( $attempts )
                {
                    $result += count($attempts);
                }
			}
        }
        
        return $result;
    }
    
    /**
     * Получить число отправленных эссе в курсе
     *
     * Если не указаны пользователи, подсчет ведется по всем
     *
     * @param int $userid - ID пользователя, для которого ведется подсчет Эссе.
     * @param int $groupid - ID группы, для которой ведется подсчет Эссе.
     * @param int $begindate - Начало периода, за который собираются данные
     * @param int $enddate - Конец периода, за который собираются данные
     *
     * @return int - Число отправленных эссе
     */
    public function count_submitted_essay($userid = NULL, $groupid = NULL, $begindate = NULL, $enddate = NULL)
    {
        global $CFG, $DB, $DOF;
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');
        require_once($CFG->dirroot . '/mod/quiz/lib.php');
        require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');
        
        $count = 0;
        
        // Получение всех экземпляров тестов в курсе
        $quizes = $this->get_visible_instances('quiz');
    
        if ( empty($quizes) )
        {// Тесты в курсе не найдены
            return $count;
        }
        
        // Формировние массива пользователей
        $users = [];
        if ( ! empty($userid) )
        {// Фильтрация по пользователю
            $user = $DOF->modlib('ama')->user($userid)->get();
            if ( ! empty($user) )
            {// Пользователь указан в системе
                $users[$userid] = $user;
            }
        }
        if ( ! empty($groupid) )
        {// Фильтрация по пользователям группы
            $groupusers = $this->group($groupid)->members();
            if ( ! empty($groupusers) )
            {// Пользователь указан в системе
                $users = $users + $groupusers;
            }
        }
        
        foreach ( $quizes as $quiz )
        {
            // Получение вопросов
            $significant_questions = quiz_report_get_significant_questions($quiz);
            // Вопросы типа Эссе в тесте
            $essays = [];
            if ( ! empty($significant_questions) )
            {
                foreach ( $significant_questions as $question )
                {
                    $q = $DB->get_record('question', ['id' => $question->id]);
                    if( ! empty($q) && question_bank::qtype_exists($q->qtype) )
                    {
                        $instance = question_bank::load_question_data($question->id);
                        if ( in_array($instance->qtype, ['essay', 'otmultiessay'] ) )
                        {// Вопрос типа Эссе или Мультиэссе
                            $essays[$question->id] = $instance;
                        }
                    }
                }
            }
            if ( ! empty($essays) )
            {// Вопросы типа эссе найдены
                foreach ( $users as $user )
                {
                    // Получение попыток в тесте для пользователя
                    $attempts = quiz_get_user_attempts($quiz->id, $user->id, 'finished');
                    if ( empty($attempts) )
                    {// Попыток нет
                        continue;
                    }
                    
                    foreach ( $attempts as $attempt )
                    {
                        if ( ! empty($begindate) && $attempt->timefinish < $begindate )
                        {// Фильтрация по начальной дате
                            continue;
                        }
                        if ( ! empty($enddate) && $attempt->timefinish > $enddate )
                        {// Фильтрация по конечной дате
                            continue;
                        }
                        // Добавление количества эссе в попытке
                        $count += count($essays);
                    }
                }
            }
        }
        return $count;
    }
    
    /** Получить фрагмент sql-кода для списка пользователей (или одного пользователя)
     *
     * @return string - IN-условие если id пользователя не указан
     *                  или сравнение для единичного пользователя
     * @param int $userid[optional] - id пользователя для которого надо составить SQL
     * @param int $groupid[optional] - id группы, если нужно получить условие для группы
     */
    protected function get_userlist_sql($userid=null, $groupid=null)
    {
        if ( ! $userid )
        {// если нужно собрать задания для всех пользователей курса
            // собираем id пользователей в массив
            if ( ! $userids = $this->get_graded_users($groupid) )
            {//пользователей нет - значит и заданий нет
                return 0;
            }
            // будем искать ответы нескольких пользователей
            return ' IN ('.$userids.')';
        }
        // будем искать ответы одного пользователя
        return  ' = '.$userid;
    }
    
    /** Получить sql-код для ограничения выборки по периоду
     *
     * @return array
     * @param string $createfield[optional] - поле,содержащее дату создания объекта
     * @param int    $begindate[optional]
     * @param string $modifyfield[optional] - поле, содержащее дату изменения объекта
     * @param int    $enddate[optional]
     */
    protected function create_datelimit_sql($createfield=null, $begindate=null, $modifyfield=null, $enddate=null)
    {
        // итоговый запрос
        $result = '';
        // ограничение для поля "создано"
        $create = '';
        // ограничения для поля "изменено"
        $modify = '';
        
        if ( $createfield )
        {// создаем ограничения для даты создания объекта
            if ( $begindate )
            {// для начала периода
                $create .= ' ('.$createfield .' >= '.$begindate.') ';
            }
            if ( $enddate )
            {// для конца периода
                if ( $create )
                {
                    $create .= ' AND ';
                }
                $create .= ' ('.$createfield .' <= '.$enddate.')';
            }
        }
        
        if ( $modifyfield )
        {// создаем ограничения для даты изменения объекта
            if ( $begindate )
            {// для начала периода
                $modify .= ' ('.$modifyfield .' >= '.$begindate.') ';
            }
            if ( $enddate )
            {// для конца париода
                if ( $modify )
                {
                    $modify .= ' AND ';
                }
                $modify .= ' ('.$modifyfield .' <= '.$enddate.') ';
            }
        }
        
        if ( $create AND $modify )
        {// составляем условие: дата начала или дата модификации объекта
            // должна лежать в указанном периоде
            $result = ' AND (('.$create.') OR ('.$modify.'))';
        }elseif ( $create )
        {
            $result = ' AND '.$create;
        }elseif ( $modify )
        {
            $result = ' AND '.$modify;
        }
        
        return $result;
    }
    
    /**
     * Получение всех элементов курса в виде ama-объектов
     *
     * @param int $courseid
     *
     * @throws Exception
     * @return stdClass[]
     */
    public function get_all_instance_objects($courseid=null)
    {
        if (is_null($courseid) && !$this->is_real())
        {
            throw new Exception('Course not specified');
        }
        
        if (is_null($courseid))
        {
            $courseid = $this->get_id();
        }
        
        $result = [];
        
        $cms = get_course_mods($courseid);
        if (!empty($cms))
        {
            foreach($cms as $cm)
            {
                $result[$cm->id] = $this->get_instance_object($cm->modname, $cm->instance, $courseid);
            }
        }
        return $result;
    }
    
    /**
     * Получение всех элементов курса в виде ama-объектов
     *
     * @param int $courseid
     *
     * @throws Exception
     * @return stdClass[]
     */
    public function get_all_instance_objects_ordered($courseid=null)
    {
        $result = [];
        $instances = $this->get_all_instance_objects($courseid);
        foreach($this->get_course_sections($courseid) as $section)
        {
            foreach(explode(',', $section->sequence) as $cmid)
            {
                if (array_key_exists($cmid, $instances))
                {
                    $result[$cmid] = $instances[$cmid];
                }
            }
        }
        return $result;
    }
    
    public function get_course_sections($courseid)
    {
        global $DB;
        
        if (is_null($courseid) && !$this->is_real())
        {
            throw new Exception('Course not specified');
        }
        
        if (is_null($courseid))
        {
            $courseid = $this->get_id();
        }
        
        $sections = $DB->get_records('course_sections', ['course' => $courseid], 'section ASC');
        
        return $sections;
    }
    
    /** Получить список видимых модулей курса в зависимости от типа задания
     *
     * @param string $type - тип задания
     * @return array - массив объектов из таблицы course_modules
     */
    protected function get_visible_instances($type)
    {
        global $DB;
        $result = array();
        if ( ! $course = $DB->get_record('course', array('id' => $this->get_id())) )
        {// не удалось получить курс
            return array();
        }
        //получаем все модули текущего курса
		if ( ! $instances = get_all_instances_in_course($type, $course) )
        {// нет ни одного модуля такого типа
            return array();
        }
        // нужно удалить все невидимые элементы
        foreach ( $instances as $id => $instance )
        {
            if ( ! $instance->visible )
            {
                continue;
            }
            $result[$id] = $instance;
        }
        return $result;
    }
    
    /** Получить строку, в которой через запятую будут указаны id пользователей,
     * задания которых разрешено оценивать
     * @param int $groupid - id группы Moodle, если нужно получить только пользователей определенной группы
     *
     * @return string - список id пользователей через запятую
     */
    protected function get_graded_users($groupid=0)
    {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/enrol/externallib.php');
        
        $result = [];
        
        // Создаем массив параметров для обращения к API Moodle
        $options = [];
        if ( $groupid )
        {// если нам нужны только пользователи определенной группы - укажем это
            $options[] = [
                'name'  => 'groupid',
                'value' => $groupid
            ];
        }
        // нужны только активные пользователи курса
        $options[] = [
            'name'  => 'onlyactive',
            'value' => true
        ];
        
        // получаем всех пользователей курса
        $users = core_enrol_external::get_enrolled_users($this->get_id(), $options);
        
        // получаем роли, которые можно оценивать
        if ( ! $roles = $this->get_graded_roles() )
        {// нет ролей, подлежащих оцениванию - оценивать некого
            return '';
        }
        
        $coursecontext = context_course::instance($this->get_id());
        $systemcontext = context_system::instance();
        
        foreach ( $users as $user )
        {//удаляем из списка пользователей тех кто может проверять задания
            if ( ! $this->is_graded_user($user['id'], $coursecontext->id, $systemcontext->id, $roles) )
            {// пользователь не подлежит оцениванию - пропускаем
                continue;
            }
            $result[$user['id']] = $user['id'];
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
        // пользователь не принадлежит ни к одной роли, подлежащей оцениванию - мы не считаем его задания
        return false;
    }
    
    /**
	 * перебирает переданные экземпляры модуля quiz и оставляет
	 * только вопросы только  типа эссе
	 * @param $quizes array массив записей с информацией
	 * об экземплярах модуля типа quiz
	 * @return $all_question array массив, индексами которого
	 * являются id экземпляра quiz, а значениями
	 * массив объектов (id, qtype),
	 * где id - это id вопросов типа эссе, а qtype = essay.
	 */
	protected function leave_only_essay($quizes)
	{//оставляем только нужные вопросы в каждом quiz
	    
        global $DB, $CFG;
        
        require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');
		$all_question = array();
		
		foreach ($quizes as $quiz)
		{
		    // Получение вопросов
            $significant_questions = quiz_report_get_significant_questions($quiz);
            
			if ( ! empty($significant_questions) )
			{//если есть хоть какие-нибудь вопросы
			    
			    $quiz_questions = [];
			    foreach ( $significant_questions as $item )
			    {
			        $quiz_questions[$item->id] = $item->id;
			    }
			    // Формирование списка id вопросов
			    $quiz_questions = implode("','", $quiz_questions);
			    $quiz_questions = "'".$quiz_questions."'";

				//получаем id вопроса и его тип
				$questions = $DB->get_records_select('question', "id IN ($quiz_questions)", null, 'id', 'id, qtype');
				foreach ($questions as $key=>$q)
				{//пропускаем ненужные вопросы';
					if ($q->qtype != 'essay')
					{//если это не эссе, то удаляем его';
						unset($questions[$key]);
					}
				}
				$all_question[$quiz->id] = $questions;//оставляем вопросы только типа эссе
			}
		}
	    return $all_question;
    }
    
    /**
	 * возвращает истину если попытка ответа на вопрос оценена и
	 * ложь если нет
	 * @param $attempt object содержит инфо о конкретной попытке ответа на вопрос с id question_id
	 * @param $question_id int id вопроса, на который пытались ответить
	 * @return bool
	 */
	protected function quiz_is_graded($attempt, $question_id)
	{
		global $CFG, $DB;
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
				return question_state_is_graded($state) OR $state->comment;
			}
			else
			{//для moodle версии 1.7 и позже
				return question_state_is_graded($state) OR $state->manualcomment;
			}
		}else
		{
			// Нельзя вызывать фатальную ошибку  при подсчете статистики
			// Тихо возвращаем, что все хорошо
			return true;
		}
	}

    /** Возвращает последний вход пользователя в курс
     * если пользователь не указан, то протсо послдений вход на курс кого-либо
     *
     * @param integer $userid   - id пользователя, по умолчанию 0
     * @param integer $courseid - id курса, по умолчанию 0
     * @return string
     */
    public function user_last_access($userid = 0, $courseid =  0)
    {
        global $DB;
        $userid = (int)$userid;
        $courseid = (int)$courseid;
        //Составим запрос для поиска записи
        $select = '';
        $params = [];
        if ( $userid > 0 )
        {//Если передали пользователя
            $select = 'userid = ? AND ';
            $params[] = $userid;
        }
        if ( ! $courseid )
        {//Не передали курс - берем текущий
            $courseid = $this->get_id();
        }
        
        $logreader = get_logreader();
        
        if ($logreader instanceof logstore_legacy\log\store) {
            $logtable = 'log';
            $timefield = 'time';
            $coursefield = 'course';
            $targetselect = ' AND module = \'course\'';
            $actionselect = ' AND action = \'view\'';
        } else {
            $logtable = $logreader->get_internal_log_table_name();
            $timefield = 'timecreated';
            $coursefield = 'courseid';
            $targetselect = ' AND target = \'course\'';
            $actionselect = ' AND action = \'viewed\'';
        }
        
        //Ищем по определённому курсу и берём самую последнюю запись
        $select .= $coursefield . ' = ?' . $targetselect . $actionselect;
        $params[] = $courseid;
        $list = $DB->get_records_select(
            $logtable,
            $select,
            $params,
            $timefield . ' DESC',
            $timefield,
            0,
            1
        );
        //Если смогли получить запись из бд
        if( ! empty($list) )
        {
            $record = array_shift($list);
            return ! empty($record->$timefield) ? $record->$timefield : false;
        }
        return false;
    }

	/** Получения количества логов по курсу и пользователю
     * @param $courseid- id курса, по умолчанию 0
     * @param $user - id пользователя(moodle)
     * @param $begindate - c какого момента начинать считать(по умолчанию с самого начала)
     * @param $enddatee - до какого момента начинать считать(по умолчанию до конца)
     * @return int - количества логов
     */
    public function get_count_logs($userid, $courseid = 0, $begindate = null, $enddate = null)
    {
        global $DB;
        $select = $days = '';
        $params = [];
        if ( ! $userid )
        {
            return 0;
        }
        if ( ! $courseid )
        {
            // Не запрашиваем логи если не указан id курса.
            // Это ограничение связано со скоростью быстродействия
            // Если запрашивать логи указывая параметры module и action, но не указывая courseid
            // то составной индекс в таблице mdl_log не используется, и отчеты
            // собираются очень и очень медленно
            return 0;
        }
        
        $logreader = get_logreader();
        
        if ($logreader instanceof logstore_legacy\log\store) {
            $logtable = 'log';
            $timefield = 'time';
            $coursefield = 'course';
            $targetselect = ' AND module = \'course\'';
        } else {
            $logtable = $logreader->get_internal_log_table_name();
            $timefield = 'timecreated';
            $coursefield = 'courseid';
            $targetselect = '';
        }

        // создадим условие для поиска
        $select .= $coursefield . ' = ? AND userid = ?' . $targetselect;
        array_push($params, $courseid, $userid);
        
        // дата начала
        if ( ! empty($begindate) )
        {// укажем с какой даты брать отчет
            $days .= ' AND ' . $timefield . ' > ?';
            $params[] = $begindate;
        }
        // дата конца
        if ( ! empty($enddate) )
        {// укажем с какой даты брать отчет
            $days .= ' AND ' . $timefield . ' < ?';
            $params[] = $enddate;
        }
        
        $select .= $days;
        
        return $DB->count_records_select($logtable, $select, $params);
    }
    
    /**
     * Получение логов по курсу и пользователю
     * @param int $userid - id пользователя
     * @param int $courseid - id курса, если не указано или интерпретируемо как false, то вернёт 0
     * @param int $begindate - c какого момента брать логи (по умолчанию с самого начала)
     * @param int $enddate - до какого момента брать логи (по умолчанию до конца)
     * @param string $sort -  порядок сортировки результата (валидный SQL ORDER BY параметр)
     * @param int $limitfrom - вернуть подмножество записей, начиная с указанной
     * @param int $limitnum - количество возвращаемых логов
     * @return mixed: array - массив логов (массив объектов) или int - 0
     */
    public function get_logs(
        $userid = null, $courseid = 0, $begindate = null, $enddate = null, $sort = 'timecreated ASC', $limitfrom = 0, $limitnum = 0)
    {
        global $DB;
        $selectpieces = [];
        $params = [];
        
        if (!$courseid) {
            // Не запрашиваем логи если не указан id курса.
            // Это ограничение связано со скоростью быстродействия
            // Если запрашивать логи указывая параметры module и action, но не указывая courseid
            // то составной индекс в таблице mdl_log не используется, и отчеты
            // собираются очень и очень медленно
            return 0;
        }
        
        $logreader = get_logreader();
        
        if ($logreader instanceof logstore_legacy\log\store) {
            $logtable = 'log';
            $timefield = 'time';
            $coursefield = 'course';
            $targetselect = ' AND module = \'course\'';
        } else {
            $logtable = $logreader->get_internal_log_table_name();
            $timefield = 'timecreated';
            $coursefield = 'courseid';
            $targetselect = '';
        }
        
        // создадим условие для поиска
        if (!is_null($userid)) {
            $selectpieces[] = 'userid = :userid';
        }
        $selectpieces[] = $coursefield . ' = :courseid' . $targetselect;
        
        // дата начала
        if (!empty($begindate)) {
            // укажем с какой даты брать отчет
            $selectpieces[] = $timefield . ' > :begindate';
        }
        // дата конца
        if (!empty($enddate)) {
            // укажем до какой даты брать отчет
            $selectpieces[] =  $timefield . ' < :enddate';
        }
        
        $select = implode(' AND ', $selectpieces);
        $params = [
            'userid' => $userid,
            'courseid' => $courseid,
            'begindate' => (int)$begindate,
            'enddate' => (int)$enddate,
        ];
        
        return $DB->get_records_select($logtable, $select, $params, $sort, '*', $limitfrom, $limitnum);
    }
    
	/** Получения количества входов пользователя на курс
     * @param $courseid - id курса, по умолчанию 0
     * @param $user - id пользователя(moodle)
     * @param $begindate - c какого момента начинать считать(по умолчанию с самого начала)
     * @param $enddatee - до какого момента начинать считать(по умолчанию до конца)
     * @return int $totalcount - количество входов в курс за время
     */
    public function get_log_course_num($userid, $courseid = 0, $begindate = null, $enddate = null)
    {
        global $DB;
        if ( ! $userid )
        {// нет порядок
            return false;
        }
        $logreader = get_logreader();
        
        if ($logreader instanceof logstore_legacy\log\store) {
            $logtable = 'log';
            $timefield = 'time';
            $coursefield = 'course';
            $targetselect = ' AND module = \'course\'';
            $actionselect = ' AND action = \'view\'';
        } else {
            $logtable = $logreader->get_internal_log_table_name();
            $timefield = 'timecreated';
            $coursefield = 'courseid';
            $targetselect = ' AND target = \'course\'';
            $actionselect = ' AND action = \'viewed\'';
        }
        
        if ( ! $courseid )
        {
            $courseid = $this->get_id();
        }
        $select = $days = '';
        $params = [];
             
        // создадим условие для поиска
        $select .= 'userid = ? AND ' . $coursefield . ' = ?' . $targetselect . $actionselect;
        array_push($params, $userid, $courseid);
        
        // дата начала
        if ( ! empty($begindate) )
        {// укажем с какой даты брать отчет
            $days = ' AND ' . $timefield . ' > ?';
            $params[] = $begindate;
        }
        // дата конца
        if ( ! empty($enddate) )
        {// укажем с какой даты брать отчет
            $days .= ' AND ' . $timefield . ' < ?';
            $params[] = $enddate;
        }
        $select .= $days;

        return $DB->count_records_select($logtable, $select, $params);
    }
    
	/** Получения количества ответов в форумах по персоне
     * @param $courseid- id курса, по умолчанию 0
     * @param $user - id пользователя(moodle)
     * @param $begindate - c какого момента начинать считать(по умолчанию с самого начала)
     * @param $enddatee - до какого момента начинать считать(по умолчанию до конца)
	 * @return int $totalcount - количество ответов в форумах за время
     */
    public function get_log_forum_answer($userid, $courseid = 0, $begindate = null, $enddate = null)
    {
        global $DB;
        if( ! $userid )
        {// нет порядок
            return false;
        }
        $select = $course = $days = '';
        $params = [];
        
        $logreader = get_logreader();
        
        if ($logreader instanceof logstore_legacy\log\store) {
            $logtable = 'log';
            $timefield = 'time';
            $coursefield = 'course';
            $targetselect = ' AND module = \'forum\'';
            $actionselect = ' AND (action = ? OR action = ?)';
            $add_discussion = 'add discussion';
            $add_post = 'add post';
        } else {
            $logtable = $logreader->get_internal_log_table_name();
            $timefield = 'timecreated';
            $coursefield = 'courseid';
            $nonanonymous = 'AND anonymous = 0';
            $targetselect = '';
            $actionselect = ' AND (eventname = ? OR eventname = ?)';
            $add_discussion = '\mod_forum\event\discussion_created';
            $add_post = '\mod_forum\event\post_created';
        }

        // создадим условие для поиска
        $select .= 'userid = ?' . $actionselect;
        array_push($params, $userid, $add_discussion, $add_post);
        
        if( $courseid )
        {// кол-во сообщений в данном курсе
            $course .= ' AND ' . $coursefield . ' = ?';
            $params[] = $courseid;
        }

        // дата начала
        if( ! empty($begindate) )
        {// укажем с какой даты брать отчет
            $days .= ' AND ' . $timefield . ' > ?';
            $params[] = $begindate;
        }
        // дата кoнца
        if( isset($enddate) AND $enddate )
        {// укажем с какой даты брать отчет
            $days .= ' AND ' . $timefield . ' < ?';
            $params[] = $enddate;
        }
        
        $select .= $course . $days . $targetselect;

        return $DB->count_records_select($logtable, $select, $params);
    }
    
    /**
     * Получить класс работы с модулем курса
     *
     * @return ama_course_instance Object - обьект для работы с модулем курса
     */
    public function instance($cmid = NULL)
    {
        // Подключение класса для работы с модулями курса
        require_once('class.ama_course_instance.php');
    
        // Получение ID курса
        $courseid = $this->get_id();
    
        // Создание класса работы с модулями курса
        $instance = new ama_course_instance($courseid, $cmid);
    
        return $instance;
    }
    
    /**
     * Получить объект модуля курса
     * @param string $modname имя модуля курса
     * @param int $instance идентификатор инстанса
     * @param int $courseid идентификатор курса
     * @return stdClass объект модуля курса
     */
    public function get_instance_object($modname, $instance, $courseid)
    {
        if( $instance === false || $courseid === false )
        {
            $cm = new stdClass();
            $cm->modname = $modname;
            if( $courseid )
            {
                $cm->course = $courseid;
            } else
            {
                $cm->course = $this->get_id();
            }
            
        } else
        {
            $cm = get_coursemodule_from_instance($modname, $instance, $courseid);
        }
        $instanceobj = $this->instance($instance);
        $instanceobj->set_cm($cm);
        return $instanceobj;
    }
    
    /**
     * Создание бэкапа курса
     *
     * @param array $backup_options - опции бэкапа
     * @param array $file_options - опции файла ьэкапа
     *
     * @return bool
     */
    public function create_backup($backup_options = [], $file_options = [])
    {
        global $CFG, $USER, $DB, $DOF;
        
        // Подключение библиотек для работы с бэкапом
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
        
        // Проверка существования курса перед началом работы
        $this->require_real();
        
        // Объект текущего курса в Moodle
        $current_course = $this->get();
        
        // Получение контекста курса
        $coursecontext = context_course::instance($current_course->id);
        
        // Получение контекста категории курса
        $categorycontext = context_coursecat::instance($current_course->category);
        
        // Контекст деканата
        $dofcontext = context_block::instance($DOF->instance->id);
        
        // Опции бэкапа по умолчанию
        $backupsettings = [
            'activities' => 1,
            'blocks' => 1,
            'filters' => 1,
            'users' => 0,
            'role_assignments' => 0,
            'comments' => 0,
            'userscompletion' => 0,
            'logs' => 0,
            'grade_histories' => 0
        ];
        if ( ! empty($backup_options) && is_array($backup_options) )
        {
            // Кастомные опции бэкапа
            foreach ( $backup_options as $option => $value )
            {
                if ( array_key_exists($option, $backupsettings) )
                {
                    if ( ! empty($value) )
                    {
                        $backupsettings[$option] = 1;
                    } else
                    {
                        $backupsettings[$option] = 0;
                    }
                }
            }
        }
        
        // Выполнение бэкапа от имени администратора
        $admin = get_admin();
        if ( empty($admin) )
        {
            // в системе отсутствует администратор, выполнение операции невозможно
            return false;
        }
        
        // Контроллер бэкапа
        $bc = new backup_controller(
                backup::TYPE_1COURSE,
                $current_course->id,
                backup::FORMAT_MOODLE,
                backup::INTERACTIVE_NO,
                backup::MODE_GENERAL,
                $admin->id
                );
        
        // Установка настроек бэкапа
        foreach ( $backupsettings as $name => $value )
        {
            $bc->get_plan()->get_setting($name)->set_value($value);
        }
        
        // Создание бэкапа
        $bc->execute_plan();
        $results = $bc->get_results();
        
        // Получение файла бэкапа
        $file = $results['backup_destination'];
        
        // Опции файла бэкапа
        $filearea = 'im_programmitems_programmitem_coursetemplate';
        if ( ! empty($file_options['filearea']) )
        {
            $filearea = $file_options['filearea'];
        }
        $itemid = 0;
        if ( ! empty($file_options['itemid']) )
        {
            $itemid = $file_options['itemid'];
        }
        $filepath = '/';
        if ( ! empty($file_options['filepath']) )
        {
            $filepath = $file_options['filepath'];
        }
        $filename = 'backup.mbz';
        if ( ! empty($file_options['filename']) )
        {
            $filename = $file_options['filename'];
        }
        
        // Формирование нового файла, куда будет перемещен созданный файл бэкапа
        $new_file = new stdClass();
        $new_file->contextid = $dofcontext->id;
        $new_file->component = 'block_dof';
        $new_file->filearea = $filearea;
        $new_file->itemid = $itemid;
        $new_file->sortorder = $file->get_sortorder();
        $new_file->mimetype = $file->get_mimetype();
        $new_file->userid = $file->get_userid();
        $new_file->source = $file->get_source();
        $new_file->author = $file->get_author();
        $new_file->license = $file->get_license();
        $new_file->status = $file->get_status();
        $new_file->filepath = $filepath;
        $new_file->filename = $filename;
        $new_file->timecreated = $file->get_timecreated();
        $new_file->timemodified = $file->get_timemodified();
        $new_file->referencefileid = $file->get_referencefileid();

        // Файловый менеджер
        $fs = get_file_storage();
        
        if( $fs->file_exists(
                $dofcontext->id,
                'block_dof',
                $filearea,
                $itemid,
                $filepath,
                $filename
                ) )
        {
            // Замена существующего бэкап файла
            $existingfile = $fs->get_file(
                    $dofcontext->id,
                    'block_dof',
                    $filearea,
                    $itemid,
                    $filepath,
                    $filename
                    );
            $existingfile->replace_file_with($file);
        } else
        {
            // Бэкапов нет, создание нового файла бэкапа
            $existingfile = $fs->create_file_from_storedfile($new_file, $file);
        }
        
        $file->delete();
        $bc->destroy();
        
        if ( ! empty($existingfile) )
        {
            return true;
        } else
        {
            return false;
        }
    }
    
    /**
     * Восстановление бэкапа
     *
     * @param array $restore_options
     * @param array $file_options
     * @param array $course_options
     *
     * @return false | int
     */
    public function restore_backup($restore_options = [], $file_options = [], $course_options = [])
    {
        global $DB, $USER, $DOF, $CFG;
        
        // Подключение библиотек для работы с бэкапом
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
        
        // Контекст деканата
        $dofcontext = context_block::instance($DOF->instance->id);
        
        // Опции файла бэкапа
        $filearea = 'im_programmitems_programmitem_coursetemplate';
        if ( ! empty($file_options['filearea']) )
        {
            $filearea = $file_options['filearea'];
        }
        $itemid = 0;
        if ( ! empty($file_options['itemid']) )
        {
            $itemid = $file_options['itemid'];
        }
        $filepath = '/';
        if ( ! empty($file_options['filepath']) )
        {
            $filepath = $file_options['filepath'];
        }
        $filename = 'backup.mbz';
        if ( ! empty($file_options['filename']) )
        {
            $filename = $file_options['filename'];
        }
        
        // Опции бэкапа по умолчанию
        $restoresettings = [
            'activities' => 1,
            'blocks' => 1,
            'filters' => 1,
            'users' => 0,
            'role_assignments' => 0,
            'comments' => 0,
            'userscompletion' => 0,
            'logs' => 0,
            'grade_histories' => 0
        ];
        if ( ! empty($restore_options) && is_array($restore_options) )
        {
            // Кастомные опции бэкапа
            foreach ( $restore_options as $option => $value )
            {
                if ( array_key_exists($option, $restoresettings) )
                {
                    if ( ! empty($value) )
                    {
                        $restoresettings[$option] = 1;
                    } else
                    {
                        $restoresettings[$option] = 0;
                    }
                }
            }
        }
        
        // Файловый менеджер
        $fs = get_file_storage();
        
        if( $fs->file_exists(
                $dofcontext->id,
                'block_dof',
                $filearea,
                $itemid,
                $filepath,
                $filename
                ) )
        {
            // Получение файла бэкапа
            $backup_file = $fs->get_file(
                    $dofcontext->id,
                    'block_dof',
                    $filearea,
                    $itemid,
                    $filepath,
                    $filename
                    );
        } else
        {
            // Файл бэкапа не существует
            return false;
        }
        
        // Директория разархивирования бэкап файла
        $backupbasepath = $CFG->tempdir . '/backup/' . $backup_file->get_pathnamehash();
        
        // Очистка мусора
        if ( file_exists($backupbasepath . "/moodle_backup.xml") )
        {
            fulldelete($backupbasepath);
        }
        
        // Разархивирование бэкапа
        $backup_file->extract_to_pathname(get_file_packer('application/vnd.moodle.backup'), $backupbasepath);
        
        // Создание нового курса, куда будет восстановлен бэкап
        $newcourseid = $this->create($course_options);

        // Выполнение бэкапа от имени администратора
        $admin = get_admin();
        if ( empty($admin) )
        {
            // в системе отсутствует администратор, выполнение операции невозможно
            return false;
        }
        
        // Контроллер восстановления бэкапа
        $rc = new restore_controller(
                $backup_file->get_pathnamehash(),
                $newcourseid,
                backup::INTERACTIVE_NO,
                backup::MODE_IMPORT,
                $admin->id,
                backup::TARGET_NEW_COURSE
                );
        
        foreach ( $restoresettings as $name => $value )
        {
            $setting = $rc->get_plan()->get_setting($name);
            if ( $setting->get_status() == backup_setting::NOT_LOCKED )
            {
                $setting->set_value($value);
            }
        }
        
        $result = true;
        
        if ( ! $rc->execute_precheck() )
        {
            $precheckresults = $rc->get_precheck_results();
            if ( is_array($precheckresults) && ! empty($precheckresults['errors']) )
            {
                $errorinfo = '';
                
                foreach ( $precheckresults['errors'] as $error )
                {
                    $errorinfo .= $error;
                }
                
                if ( array_key_exists('warnings', $precheckresults) )
                {
                    foreach ( $precheckresults['warnings'] as $warning )
                    {
                        $errorinfo .= $warning;
                    }
                }
                
                // Фиксируем в логах
                $DOF->add_to_log('modlib', 'ama', 'restore_backup', '', json_encode($errorinfo));
                $result = false;
            }
        }
        
        $rc->execute_plan();
        $rc->destroy();
        
        fulldelete($backupbasepath);
        
        if ( $result )
        {
            return $newcourseid;
        } else
        {
            delete_course($newcourseid);
            return false;
        }
    }
    
    /**
     * Удаление файлов бэкапа, добавленные в контекст блока ЭД
     *
     * @param array $file_options
     *
     * @return false | int
     */
    public function delete_backup_files($file_options = [])
    {
        global $DB, $USER, $DOF, $CFG;
        
        // Подключение библиотек для работы с бэкапом
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
        
        // Контекст деканата
        $dofcontext = context_block::instance($DOF->instance->id);
        
        // Опции файла бэкапа
        $filearea = 'storage_programmitems';
        if ( ! empty($file_options['filearea']) )
        {
            $filearea = $file_options['filearea'];
        }
        $itemid = 0;
        if ( ! empty($file_options['itemid']) )
        {
            $itemid = $file_options['itemid'];
        }
        $filepath = '/';
        if ( ! empty($file_options['filepath']) )
        {
            $filepath = $file_options['filepath'];
        }
        $filename = 'backup.mbz';
        if ( ! empty($file_options['filename']) )
        {
            $filename = $file_options['filename'];
        }
        
        // Файловый менеджер
        $fs = get_file_storage();
        
        if( $fs->file_exists(
                $dofcontext->id,
                'block_dof',
                $filearea,
                $itemid,
                $filepath,
                $filename
                ) )
        {
            // Получение файла бэкапа
            $backup_file = $fs->get_file(
                    $dofcontext->id,
                    'block_dof',
                    $filearea,
                    $itemid,
                    $filepath,
                    $filename
                    );
            
            // Файл существует, удаление
            $backup_file->delete();
        }
        if( $fs->file_exists(
                $dofcontext->id,
                'block_dof',
                $filearea,
                $itemid,
                $filepath,
                '.'
                ) )
        {
            // Получение пустого файла (почему-то мудл создает пустой файл при создании файлов)
            $backup_file = $fs->get_file(
                    $dofcontext->id,
                    'block_dof',
                    $filearea,
                    $itemid,
                    $filepath,
                    '.'
                    );
            
            // Файл существует, удаление
            $backup_file->delete();
        }
        
        return true;
    }
    
    /**
     * Проверка существования бэкапа
     *
     * @param array $file_options
     *
     * @return bool
     */
    public function backup_exists($file_options = [])
    {
        global $DOF;
        
        // Контекст деканата
        $dofcontext = context_block::instance($DOF->instance->id);
        
        // Опции файла бэкапа
        $filearea = 'im_programmitems_programmitem_coursetemplate';
        if ( ! empty($file_options['filearea']) )
        {
            $filearea = $file_options['filearea'];
        }
        $itemid = 0;
        if ( ! empty($file_options['itemid']) )
        {
            $itemid = $file_options['itemid'];
        }
        $filepath = '/';
        if ( ! empty($file_options['filepath']) )
        {
            $filepath = $file_options['filepath'];
        }
        $filename = 'backup.mbz';
        if ( ! empty($file_options['filename']) )
        {
            $filename = $file_options['filename'];
        }
        
        // Файловый менеджер
        $fs = get_file_storage();
        
        return (bool)$fs->file_exists(
                $dofcontext->id,
                'block_dof',
                $filearea,
                $itemid,
                $filepath,
                $filename
                );
    }
    
    public function get_context()
    {
        $course = $this->get();
        //получаем контекст курса
        if ( class_exists('context_course') )
        {// начиная с moodle 2.6
            $context = context_course::instance($course->id);
        }else
        {// оставим совместимость с moodle 2.5 и менее
            $context = get_context_instance(CONTEXT_COURSE, $course->id);
        }
        return $context;
    }
    
    /**
     * Ссылка на курс в Moodle
     *
     * @return moodle_url
     */
    public function get_course_view_url()
    {
        return new moodle_url('/course/view.php', ['id' => $this->get()->id]);
    }
    
    /**
     * Ссылка на панель управления доступом в СДО
     *
     * @return moodle_url
     */
    public function get_authcontrol_page_url()
    {
        return new moodle_url('/local/authcontrol/controlpage.php', ['id' => $this->get()->id]);
    }
    
    /**
     * Получить объект класса для чтения логов
     * @param string $reader имя нужного logreader'а
     * @return mixed|\core\log\reader
     */
    public function get_logreader($reader = '') {
        return get_logreader($reader);
    }
}

