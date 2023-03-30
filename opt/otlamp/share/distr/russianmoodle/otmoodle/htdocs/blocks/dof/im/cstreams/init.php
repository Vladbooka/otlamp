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

/** Предмето-классы
 * 
 */
class dof_im_cstreams implements dof_plugin_im
{
    /**
     * @var dof_control
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    /** Метод, реализующий инсталяцию плагина в систему
     * Создает или модифицирует существующие таблицы в БД
     * и заполняет их начальными значениями
     * @return boolean
     * Может надо возвращать массив с названиями таблиц и результатами их создания?
     * чтобы потом можно было распечатать сообщения о результатах обновления
     * @access public
     */
    public function install()
    {
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    /** Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * Может надо возвращать массив с названиями таблиц и результатами их создания/изменения?
     * чтобы потом можно было распечатать сообщения о результатах обновления
     * @access public
     */
    public function upgrade($oldversion)
    {
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());  
    }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        return 2018111400;
    }
    /** Возвращает версии интерфейса Деканата, 
     * с которыми этот плагин может работать
     * @return string
     * @access public
     */
    public function compat_dof()
    {
        return 'aquarium';
    }

    /** Возвращает версии стандарта плагина этого типа, 
     * которым этот плагин соответствует
     * @return string
     * @access public
     */
    public function compat()
    {
        return 'angelfish';
    }
    
    /** Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'im';
    }
    /** Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'cstreams';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('modlib'=>array('nvg'=>2008060300,
                                     'widgets'=>2009050800),
                     'storage'=>array('persons'=>2009060400,
                                      'departments'=>2009040800,
                                      'ages'=>2009050600,
                                      'cstreams'=>2009011601,
                                      'agroups'=>2009011601,
                                      'cstreamlinks'=>2009060900,
                                      'programmitems'=>2009060800,
                                      'acl'=>2011040504),
                     'workflow'=>array('cstreams'=>2009060800));
    }
    /** Определить, возможна ли установка плагина в текущий момент
     * Эта функция одинакова абсолютно для всех плагинов и не содержит в себе каких-либо зависимостей
     * @TODO УДАЛИТЬ эту функцию при рефакторинге. Вместо нее использовать наследование
     * от класса dof_modlib_base_plugin 
     * @see dof_modlib_base_plugin::is_setup_possible()
     * 
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * 
     * @return bool 
     *              true - если плагин можно устанавливать
     *              false - если плагин устанавливать нельзя
     */
    public function is_setup_possible($oldversion=0)
    {
        return dof_is_plugin_setup_possible($this, $oldversion);
    }
    /** Получить список плагинов, которые уже должны быть установлены в системе,
     * и без которых начать установку или обновление невозможно
     * 
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion=0)
    {
        return array('storage'=>array('acl'=>2011040504));
    }
    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
       return array(
                array('plugintype' => 'im',
                      'plugincode' => 'obj',
                      'eventcode'  => 'get_object_url'));
    }
    /** Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
       return false;
    }
    
    /** Проверяет полномочия на совершение действий
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     * @access public
     */
    public function is_access($do, $objid = NULL, $userid = NULL)
    {
        if ( $this->dof->is_access('datamanage') OR $this->dof->is_access('admin') 
             OR $this->dof->is_access('manage') )
        {// манагеру можно все
            return true;
        }        
        // получаем id пользователя в persons
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        // получаем все нужные параметры для функции проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid);  
        switch ( $do )
        {// определяем дополнительные параметры в зависимости от запрашиваемого права
            // право на просмотр своего журнала предмето-потока              
            case 'courseenrilments:tutor:sbc':
                $acldata->code = 'courseenrilments:tutor:sbc/my';
                if ( ! $this->dof->storage('schevents')->is_exists(array('teacherid'=>$personid,'cstreamid'=>$objid)) 
                            AND $personid != $this->dof->storage('cstreams')->get_field($objid,'teacherid')) 
                {// персона не учитель потока и не заменяет ни один урок из потока      
                    return false;
                }
            break;
        }
        // проверка
        return $this->acl_check_access_paramenrs($acldata);
    }
    /** Требует наличия полномочия на совершение действий
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     * @access public
     */
    public function require_access($do, $objid = NULL, $userid = NULL)
    {
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $notice = "cstreams/{$do} (block/dof/im/cstreams: {$do})";
            if ($objid){$notice.=" id={$objid}";}
            $this->dof->print_error('nopermissions','',$notice);
        }
    }
    /** Обработать событие
     * @param string $gentype - тип модуля, сгенерировавшего событие
     * @param string $gencode - код модуля, сгенерировавшего событие
     * @param string $eventcode - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function catch_event($gentype,$gencode,$eventcode,$intvar,$mixedvar)
    {
        if ( $gentype == 'im' AND $gencode == 'obj' AND $eventcode == 'get_object_url' )
        {
            if ( $mixedvar['storage'] == 'cstreams' )
            {
                if ( isset($mixedvar['action']) AND $mixedvar['action'] == 'view' )
                {// Получение ссылки на просмотр объекта
                    $params = array('cstreamid' => $intvar);
                    if ( isset($mixedvar['urlparams']) AND is_array($mixedvar['urlparams']) )
                    {
                        $params = array_merge($params, $mixedvar['urlparams']);
                    }
                    return $this->url('/view.php', $params);
                }
            }
        }
        return false;
    }
    /** Запустить обработку периодических процессов
     * @param int $loan - нагрузка (1 - только срочные, 2 - нормальный режим, 3 - ресурсоемкие операции)
     * @param int $messages - количество отображаемых сообщений (0 - не выводить,1 - статистика,
     *  2 - индикатор, 3 - детальная диагностика)
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function cron($loan,$messages)
    {
        return true;
    }
    
    /** 
     * Обработать задание, отложенное ранее в связи с его длительностью
     * @param string $code - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function todo($code, $intvar, $mixedvar)
    {
        
        switch ($code)
        {
            case 'update_cstream_cache':
                return $this->todo_update_cstream_cache($intvar); 
        }
        
        return true;
    }
    /** Конструктор
     * @param dof_control $dof - идентификатор действия, которое должно быть совершено
     * @access public
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
    // **********************************************
    // Методы, предусмотренные интерфейсом im
    // **********************************************
    /** Возвращает текст для отображения в блоке на странице dof
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string - html-код содержимого блока
     */
    public function get_block($name, $id = 1)
    {
        $result = '';

        // Инициализируем генератор HTML
        if ( !class_exists('dof_html_writer') )
        {
            $this->dof->modlib('widgets')->html_writer();
        }

        $addvars = [
            'departmentid' => $this->dof->storage('departments')->get_user_default_department()
        ];
        
        switch ($name)
        {
            case 'link':
                $result = dof_html_writer::link(
                    $this->dof->url_im($this->code(),'/index.php'),
                    $this->dof->get_string('page_main_name')
                );
                break;
            case 'main':
                $path = $this->dof->url_im('cstreams','/index.php',$addvars);
//                $rez .= "<a href=\"{$path}\">".$this->dof->get_string('title', 'ages').'</a>';
//                $rez .= "<br />";
                if ( $this->dof->storage('cstreams')->is_access('viewlist', null, null, $addvars['departmentid']) )
                {//может видеть все потоки
                    $path = $this->dof->url_im('cstreams','/list.php',$addvars);
                }
                //ссылка на список потоков
                $result .= "<a href=\"{$path}\">".$this->dof->get_string('list', 'cstreams').'</a>';
                if ( $this->dof->storage('cstreams')->is_access('create', null, null, $addvars['departmentid']) )
                {//может создавать период - покажем ссылку
                    $result .= "<br />";
                    $path = $this->dof->url_im('cstreams','/edit.php',$addvars);
                    $result .= "<a href=\"{$path}\">".$this->dof->get_string('new', 'cstreams').'</a>';
                }
            break;
        }
        return $result;
    }
    /** Возвращает html-код, который отображается внутри секции
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string  - html-код содержимого секции секции
     */
    public function get_section($name, $id = 1)
    {
        $rez = '';
        switch ($name)
        {
        }
        return $rez;
    }

    // ***********************************************************
    //       Методы для работы с полномочиями и конфигурацией
    // ***********************************************************   
    
    /** Получить список параметров для фунции has_hight()
     * @todo завести дополнительные права в плагине storage/persons и storage/contracts 
     * и при редактировании контракта или персоны обращаться к ним
     * 
     * @return object - список параметров для фунции has_hight()
     * @param string $action - совершаемое действие
     * @param int $objectid - id объекта над которым совершается действие
     * @param int $userid
     */
    protected function get_access_parametrs($action, $objectid, $userid)
    {
        $result = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->userid       = $userid;
        $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        $result->objectid     = $objectid;
        if ( ! $objectid )
        {// если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        }else
        {// если указан - то установим подразделение
            $result->departmentid = $this->dof->storage('cstreams')->get_field($objectid, 'departmentid');
        }
        
        return $result;
    }    
    /** Проверить права через плагин acl.
     * Функция вынесена сюда, чтобы постоянно не писать длинный вызов и не перечислять все аргументы
     * 
     * @return bool
     * @param object $acldata - объект с данными для функции storage/acl->has_right() 
     */
    protected function acl_check_access_paramenrs($acldata)
    {
        return $this->dof->storage('acl')->
                    has_right($acldata->plugintype, $acldata->plugincode, $acldata->code, 
                              $acldata->userid, $acldata->departmentid, $acldata->objectid);
    }         

    /** Задаем права доступа для объектов этого хранилища
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = array();
        
        $a['export']   = array('roles'=>array('manager'));
        $a['import']   = array('roles'=>array('manager'));
        $a['viewcurriculum']   = array('roles'=>array('manager','methodist'));
        $a['editcurriculum']   = array('roles'=>array('manager','methodist'));                              
        $a['courseenrilments:tutor:sbc']    = array('roles'=>array('manager')); 
        $a['courseenrilments:tutor:sbc/my'] = array('roles'=>array('manager'));    
        
        // Просмотр рейтинга по учебному процессу
        $a['view:rtreport/rating_cstream'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        
        // Просмотр нагрузки учебного процесса
        $a['view:rtreport/workloadcstream'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        
        return $a;
    }
    
    /**
     * Получить настройки для плагина
     *
     * @param string $code
     * @return object[]
     */
    public function config_default($code = NULL)
    {
        $config = [];
        
        // Обновление кэша в подразделении
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'update_cache';
        $obj->value = '0';
        $config[$obj->code] = $obj;
        
        // Включить сбор отчетов в текущем подразделении
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'cstream_workload_lesson_types';
        $obj->value = 'facetime,distance,consult,practicework,laboratorywork,kpkr,rgrrpr,referat,practice,credit,examconsult,exam,dpdr,gak,worksreviewing,examaspirant,examcandidate,aspirantleadership,controlvisit,seminar,testwork,intermediate';
        $config[$obj->code] = $obj;
        
        // При расчете среднего балла за учебный процес
        // включать ли в расчет занятие, если у слушатель отсутствует оценка
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'include_empty_grades';
        $obj->value = 0;
        $config[$obj->code] = $obj;
        
        return $config;
    }
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /** Получить URL к собственным файлам плагина
     * @param string $adds[optional] - фрагмент пути внутри папки плагина
     *                                 начинается с /. Например '/index.php'
     * @param array $vars[optional] - параметры, передаваемые вместе с url
     * @return string - путь к папке с плагином 
     * @access public
     */
    public function url($adds='', $vars=array())
    {
        return $this->dof->url_im($this->code(), $adds, $vars);
    }
    /**
     * Возвращает html-код отображения 
     * информации об учебной потоке
     * @param stdClass $obj - запись из таблицы
     * @return mixed string html-код или false в случае ошибки
     */
    public function show($obj, $conds = null)
    {
        if (! is_object($obj))
        {// переданны данные неверного формата
            return false;
        }
        $data = array();
        // заносим данные в таблицу
        $data = $this->get_string_table($obj,$conds);
        // выводим таблицу на экран
        return $this->print_single_table($data);
    }
    
    /**
     * Возвращает html-код отображения 
     * информации об учебной потоке
     * @param int $id - id записи из таблицы
     * @return mixed string html-код или false в случае ошибки
     */
    public function show_id($id,$conds = null)
    {
        if ( ! is_int_string($id) )
        {//входные данные неверного формата 
            return false;
        }
        if ( ! $obj = $this->dof->storage('cstreams')->get($id) )
        {// период не найден
            return false;
        } 
        $obj->programmid = $this->dof->storage('programmitems')->get_field($obj->programmitemid,'programmid');
        return $this->show($obj,$conds);
    }
    
    /**
     * Возвращает html-код отображения 
     * информации о нескольких потоках
     * @param массив $list - массив записей 
     * периодов, которые надо отобразить 
     * @return mixed string в string html-код или false в случае ошибки
     */
    public function showlist($list,$conds = null)
    {
        if ( ! is_array($list))
        {// переданны данные неверного формата
            return false;
        }
        $data = array();
        // заносим данные в таблицу
        foreach ($list as $obj)
        {   
            $data[] = $this->get_string_table($obj,$conds,'small');
        }

        // выводим таблицу на экран
        return $this->print_table($data,'small');
    }
    
    /**
     * Возвращает форму создания/редактирования с начальными данными
     * @param int $id - id записи, значения 
     * которой устанавливаются в поля формы по умолчанию
     * @return moodle quickform object
     */
    public function form($id = NULL, $addvars = [])
    {
        global $USER, $addvars;
        // устанавливаем начальные данные
        if (isset($id) AND ($id <> 0) )
        {// id передано
            $cstream = $this->dof->storage('cstreams')->get($id);
            $programmid = $this->dof->storage('programmitems')->get_field($cstream->programmitemid, 'programmid');
            $cstream->pitemteacher = array($programmid, $cstream->programmitemid, $cstream->appointmentid);
            $cstream->programmid = $programmid;
            $cstream->hoursweekdistance = $this->dof->storage('cstreams')->hours_int($cstream->hoursweekdistance);
            $cstream->hoursweekinternally = $this->dof->storage('cstreams')->hours_int($cstream->hoursweekinternally);
            $cstream->factor = 'sal';
            $substsalfactor = (float)$cstream->substsalfactor;
            if ( !empty($substsalfactor) )
            {// указан замещающий коэффициент
                $cstream->factor = 'substsal';
            }
        }else
        {// id не передано
            $cstream = $this->form_new_data();
        }
        if ( isset($USER->sesskey) )
        {//сохраним идентификатор сессии
            $cstream->sesskey = $USER->sesskey;
        }else
        {//идентификатор сессии не найден
            $cstream->sesskey = 0;
        }
        $customdata = new stdClass;
        $customdata->cstream = $cstream;
        $customdata->addvars = $addvars;
        $customdata->dof    = $this->dof;
        // подключаем методы вывода формы
        $form = new dof_im_cstreams_edit_form($this->dof->url_im('cstreams', 
                    '/edit.php?cstreamid='.$cstream->id,$addvars),$customdata);
        // очистим статус, чтобы не отображался как в БД
        //unset($cstream->status);
        // заносим значения по умолчению
        $form->set_data($cstream); 
        // возвращаем форму
        return $form;
    }
    
    /**
     * Возвращает заготовку для формы создания потока
     * @return stdclassObject
     */
    private function form_new_data()
    {
        $cstream = new stdClass();
        $cstream->id = 0;
        $cstream->ageid = 0;
        $cstream->programmitemid = 0;
        $cstream->begindate = null;
        $cstream->departmentid = optional_param('departmentid', 0, PARAM_INT);
        $cstream->enddate = null;
        return $cstream;
    }
    
   /** Возвращает html-код таблицы
     * @param array $date - данные в таблицу
     * @return string - html-код или пустая строка
     */
    private function print_table($date,$type = 'all')
    {
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        //$table->size = array ('100px','150px','150px','200px','150px','100px');
        $table->align = array ("center","center","center","center","center","center","center","center","center","center","center");
        
        // шапка таблицы
        $table->head = $this->get_fields_description($type);
        
        // заносим данные в таблицу     
        $table->data = $date;
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    
    /** Распечатать вертикальную таблицу для удобного отображения информации по элементу
     * 
     * @return null
     * @param object $data объект с отображаемыми значениями
     */
    private function print_single_table($data)
    {
        $table = new stdClass();
        if ( ! $data )
        {
            return '';
        }
        // получаем подписи с пояснениями
        $descriptions = $this->get_fields_description();
        foreach ( $data as $elm )
        {
            $table->data[] = array('<b>'.array_shift($descriptions).'</b>', $elm);
        }
        return $this->dof->modlib('widgets')->print_table($table, true);
    }
    
    /** Получить заголовок для списка таблицы, или список полей
     * для списка отображения одного объекта 
     * @return array
     */
    private function get_fields_description($type = 'all')
    {
        switch ( $type )
        {// выполняем необходимые действия, связанные со сменой статуса
            case 'all':
                return array($this->dof->get_string('actions','cstreams'),
                             $this->dof->get_string('age','cstreams'),
                             $this->dof->get_string('name','cstreams'),
                             $this->dof->get_string('code','cstreams'),
                             $this->dof->get_string('group','cstreams'),
                             $this->dof->get_string('programm','cstreams'),
                             $this->dof->get_string('programmitem','cstreams'),
                             $this->dof->get_string('teacher','cstreams'),
                             $this->dof->get_string('department','cstreams'),
                             $this->dof->get_string('selfenrol','cstreams'),
                             $this->dof->get_string('studentslimit','cstreams'),
                             $this->dof->get_string('description','cstreams'),
                             $this->dof->get_string('mdlgroup','cstreams'),
                             $this->dof->get_string('eduweeks','cstreams'),
                             $this->dof->get_string('hoursweekinternally','cstreams'),
                             $this->dof->get_string('hoursweekdistance','cstreams'),
                             $this->dof->get_string('begindate','cstreams'),
                             $this->dof->get_string('enddate','cstreams'),
                             $this->dof->get_string('salfactor', 'cstreams','<br>'),
                             $this->dof->get_string('substsalfactor', 'cstreams','<br>'),
                             $this->dof->get_string('calculation_formula', 'cstreams'),
                             $this->dof->get_string('calcfactor', 'cstreams','<br>'),
                             $this->dof->get_string('mdlcourse', 'cstreams'),
                             $this->dof->get_string('status','cstreams'));                  
            
            case 'small':
                return array($this->dof->get_string('actions','cstreams'),
                             $this->dof->get_string('age','cstreams'),
                             $this->dof->get_string('name','cstreams'),
                             $this->dof->get_string('code','cstreams'),
                             $this->dof->get_string('group','cstreams'),
                             $this->dof->get_string('programm','cstreams'),
                             $this->dof->get_string('programmitem','cstreams'),
                             $this->dof->get_string('teacher','cstreams'),
                             $this->dof->get_string('department','cstreams'),
                             $this->dof->get_string('salcalcfactor', 'cstreams','<br>'),
                             $this->dof->get_string('status','cstreams')); 
         }
    }
    
    /** Возвращает массив для вставки в таблицу
     * 
     * @param object $obj - объект из таблицы cstreams
     * @return array - массив, содержащий данные для отображения 1 строки таблицы расписания
     */
    private function get_string_table($obj,$conds = null,$type = 'all')
    {
        // для ссылок вне плагина
        $conds = (array) $conds;
        $outconds = array();
        $outconds['departmentid'] = $conds['departmentid'];
        if ( ! $agename = $this->dof->storage('ages')->get_field($obj->ageid, 'name') )
        {//номера периода нет - выведем пустую строчку
            $agename = '';
        }elseif( $this->dof->storage('ages')->is_access('view',$obj->ageid) ) 
        {// плкажем ссылку
            $agename = '<a href='.$this->dof->url_im('ages','/view.php?ageid='.$obj->ageid,$conds).'>'.$agename.'</a>';
        }
        if ( ! $teachername = $this->dof->storage('persons')->get_fullname($obj->teacherid) )
        {//названия программы нет - выведем пустую строчку
            $teachername = '';
        }elseif( $this->dof->storage('persons')->is_access('view',$obj->teacherid) ) 
        {// плкажем ссылку yна сотрудника
            $teachername = '<a href='.$this->dof->url_im('persons','/view.php?id='.$obj->teacherid,$conds).'>'.$teachername.'</a>';
            if ( $this->dof->storage('appointments')->is_access('view',$obj->appointmentid) )
            {
                $imgapp = '<img src="'.$this->dof->url_im('cstreams', '/icons/view-eagreement.png').'"
                    alt="'.$this->dof->get_string('appointment','cstreams').'" title="'.$this->dof->get_string('appointment','cstreams').'">';
                $teachername .= '<br><a href='.$this->dof->url_im('employees','/view_appointment.php?id='.$obj->appointmentid,$conds).'>
                '.$imgapp.'</a>';
            }
        }

        //получаем данные о подразделении
        if ( ! $department = $this->dof->storage('departments')->get($obj->departmentid) )
        {//не получили - выведем пустую строку
            $departmentname = '';
        }elseif( $this->dof->storage('departments')->is_access('view',$obj->departmentid) )
        {//получили - формируем имя
            $departmentname = '<a href='.$this->dof->url_im('departments','/view.php?departmentid='.$obj->departmentid,$conds).'>'.
                $department->name.'<br>['.$department->code.']'.'</a>';
        }else
        {// нет права на ссылку
            $departmentname = $department->name.'<br>['.$department->code.']';    
        }
        
        // Самозапись
        switch ( $obj->selfenrol )
        {
            case 0:
                $selfenrol = $this->dof->get_string('form_off_available','cstreams');
                break;
            case 1:
                $selfenrol = $this->dof->get_string('form_on_available','cstreams');
                break;
            case 2:
                $selfenrol = $this->dof->get_string('form_request_available','cstreams');
                break;
            default:
                $selfenrol = $this->dof->get_string('form_off_available','cstreams');
                break;
        }
        if ( ! empty($obj->selfenrol) )
        {
            // Количество студентов по умолчанию
            $studentslimit = $obj->studentslimit;
        } else
        {
            $studentslimit = 0;
        }
        $description = $this->dof->storage('cstreams')->get_description($obj->id);
        
        //формируем название программы
        $programm = $this->dof->storage('programms')->get($obj->programmid);
        if ( $this->dof->storage('programms')->is_access('view',$obj->programmid) )
        {
            $programmname  = '<a href='.$this->dof->url_im('programms','/view.php?programmid='.$obj->programmid,$conds).'>'.
                $programm->name.'<br>['.$programm->code.']'.'</a>';
        }else 
        {// без ссылки
            $programmname  = $programm->name.'<br>['.$programm->code.']';
        }
        
        
        //формируем название предмета
        $programmitem = $this->dof->storage('programmitems')->get($obj->programmitemid);
        if ( $this->dof->storage('programmitems')->is_access('view',$obj->programmitemid) )
        {
            $programmitemname  = '<a href='.$this->dof->url_im('programmitems','/view.php?pitemid='.$obj->programmitemid,$conds).'>'.
                $programmitem->name.'<br>['.$programmitem->code.']'.'</a>';
        }else 
        {// без ссылки
            $programmitemname  = $programmitem->name.'<br>['.$programmitem->code.']';
        }

        //получим название статуса
        if ( ! $statusname = $this->dof->workflow('cstreams')->get_name($obj->status) )
        {//статуса нет - выведем пустую строчку
            $statusname = '';
        }
        
        // формулу расчета берем из конфига
        $calc_formula = $this->dof->storage('config')->get_config_value('salfactors_calculation_formula',
                'storage', 'schevents', $obj->departmentid);
        $calc_formula = str_replace('*','*<br>',$calc_formula);
        $calc_formula = str_replace('+','+<br>',$calc_formula);
        //получаем ссылки на картинки
        $imgedit = '<img src="'.$this->dof->url_im('cstreams', '/icons/edit.png').'"
            alt="'.$this->dof->get_string('edit', 'cstreams').'" title="'.$this->dof->get_string('edit', 'cstreams').'">';
        $imgview = '<img src="'.$this->dof->url_im('cstreams', '/icons/view.png').'" 
            alt="'.$this->dof->get_string('view', 'cstreams').'" title="'.$this->dof->get_string('view', 'cstreams').'">';
        $imglink = '<img src="'.$this->dof->url_im('cstreams', '/icons/add_link.png').'" 
            alt="'.$this->dof->get_string('add_link', 'cstreams').'" title="'.$this->dof->get_string('add_link', 'cstreams').'">';
        $imggroup = '<img src="'.$this->dof->url_im('cstreams', '/icons/group.gif').'" 
            alt="'.$this->dof->get_string('list_group', 'cstreams').'" title="'.$this->dof->get_string('list_group', 'cstreams').'">';
        $imgjournal = '<img src="'.$this->dof->url_im('cstreams', '/icons/journal.png').'" 
            alt="'.$this->dof->get_string('journal', 'cstreams').'" title="'.$this->dof->get_string('journal', 'cstreams').'">';
        $imgcpassed = '<img src="'.$this->dof->url_im('cstreams', '/icons/student.png').'" 
            alt="'.$this->dof->get_string('cpassed', 'cstreams').'" title="'.$this->dof->get_string('cpassed', 'cstreams').'">';
        $imgsync = '<img src="'.$this->dof->url_im('cstreams', '/icons/sync.png').'" 
            alt="'.$this->dof->get_string('sync_cstream_with_agroups', 'cstreams').'" title="'.
                   $this->dof->get_string('sync_cstream_with_agroups', 'cstreams').'">';
        $imgtmp = '<img src="'.$this->dof->url_im('cstreams', '/icons/create_template.png').'" 
            alt="'.$this->dof->get_string('create_template_for_cstream', 'cstreams').'" title="'.
                   $this->dof->get_string('create_template_for_cstream', 'cstreams').'">';
        
        // добавляем ссылку
        $actions = '';
        if ( $this->dof->storage('cstreams')->is_access('edit', $obj->id) OR 
                 $this->dof->storage('cstreams')->is_access('edit/plan', $obj->id) )
        {//покажем ссылку на страницу редактирования
            $actions .= '<a href='.$this->dof->url_im('cstreams','/edit.php?cstreamid='.
            $obj->id,$conds).'>'.$imgedit.'</a>&nbsp;';
            // и ссылку на страницу добавления связи
            $actions .= '&nbsp;<a href='.$this->dof->url_im('cstreams','/linkagroup.php?cstreamid='.
            $obj->id,$conds).'>'.$imglink.'</a>&nbsp;';
        }
        
        // Имя учебного процесса
        $cstreamname = $this->dof->storage('cstreams')->get_name($obj);
        if ( $this->dof->storage('cstreams')->is_access('view', $obj->id) )
        {// Пользователь имеет доступ к странице просмотра
            $actions .= '<a href='.$this->dof->url_im('cstreams','/view.php?cstreamid='.
            $obj->id,$conds).'>'.$imgview.'</a>';
            $cstreamname = '<a href='.$this->dof->url_im('cstreams','/view.php?cstreamid='.
            $obj->id,$conds).'>'.$cstreamname.'</a>';
        }
        
        // Код учебного процесса
        $cstreamcode = $this->dof->storage('cstreams')->get_code($obj);
        
        if ( $this->dof->im('journal')->is_access('view_journal', $obj->id) )
        {//покажем ссылку на журнал потока
            $actions .= '&nbsp;<a href='.$this->dof->url_im('journal','/group_journal/index.php?csid='.
            $obj->id.'&showall=0',$outconds).'>'.$imgjournal.'</a>';
        }
        if ( $this->dof->storage('cpassed')->is_access('view') )
        {//покажем ссылку на список подписанных учеников
            $actions .= '&nbsp;<a href='.$this->dof->url_im('cpassed','/listeditor.php?cstreamid='.
            $obj->id,$outconds).'>'.$imgcpassed.'</a>';
        }
        if ( $this->dof->storage('cstreams')->is_access('edit', $obj->id) )
        {//покажем ссылку на пересинхронизацию потока с группами
            // @todo проставить более продуманные права доступа, либо завести собственную категорию
            // прав для синхронизации
            $actions .= '&nbsp;<a href='.$this->dof->url_im('cstreams','/view.php?cstreamsyncid='.
            $obj->id,$conds).'>'.$imgsync.'</a>';
        }
        if ( $this->dof->im('plans')->is_access('viewthemeplan',$obj->id) OR 
             $this->dof->im('plans')->is_access('viewthemeplan/my',$obj->id) )
        {// если есть право на просмотр планирования
            $actions .= '<a id="view_planning_for_cstream_'.$obj->id.'" href="'.$this->dof->url_im('plans','/themeplan/viewthemeplan.php?linktype=cstreams&linkid='.$obj->id,$outconds).'">';
            $actions .= '<img src="'.$this->dof->url_im('cstreams', '/icons/plancstream.png').'"
                alt=  "'.$this->dof->get_string('view_plancstream', 'cstreams').'" 
                title="'.$this->dof->get_string('view_plancstream', 'cstreams').'" /></a>&nbsp;';
            $actions .= '<a id="view_iutp_for_cstream_'.$obj->id.'" href="'.$this->dof->url_im('plans','/themeplan/viewthemeplan.php?linktype=plan&linkid='.$obj->id,$outconds).'">';
            $actions .= '<img src="'.$this->dof->url_im('cstreams', '/icons/iutp.png').'"
                alt=  "'.$this->dof->get_string('view_iutp', 'cstreams').'" 
                title="'.$this->dof->get_string('view_iutp', 'cstreams').'" /></a>&nbsp;';
        }
        if ( $this->dof->storage('schtemplates')->is_access('view') )
        {// пользователь может просматривать шаблоны
            $actions .= ' <a id="view_schedule_for_cstream_'.$obj->id.'" href='.$this->dof->url_im('schedule','/view_week.php?ageid='.
                    $obj->ageid.'&cstreamid='.$obj->id,$outconds).'>'.
                    '<img src="'.$this->dof->url_im('cstreams', '/icons/view_schedule.png').
                    '"alt="'.$this->dof->get_string('view_week_template_on_cstream', 'cstreams').
                    '" title="'.$this->dof->get_string('view_week_template_on_cstream', 'cstreams').'">'.'</a>';
        }
        if ( $this->dof->storage('schtemplates')->is_access('create') )
        {// пользователь может редактировать шаблон
            $actions .= ' <a id="create_schedule_for_cstream_'.$obj->id.'" href='.$this->dof->url_im('schedule','/edit.php?ageid='.
                    $obj->ageid.'&cstreamid='.$obj->id,$outconds).'>'.
                    '<img src="'.$this->dof->url_im('cstreams', '/icons/create_schedule.png').
                    '"alt="'.$this->dof->get_string('create_template_on_cstream', 'cstreams').
                    '" title="'.$this->dof->get_string('create_template_on_cstream', 'cstreams').'">'.'</a>';
        }

        // Состав потока
        $stream = $this->get_stream($obj);
        
        // курс в moodle
        $mcoursename = '';
        if ( intval($obj->mdlcourse) > 0 )
        {
            $mcourse = $this->dof->sync('mcourses')->get_course($obj->mdlcourse);
            if ( ! empty($mcourse) )
            {
                $mcourselink = $this->dof->sync('mcourses')->get_course_link($obj->mdlcourse);
                $mcoursename = '<a href="' . $mcourselink . '">' . $mcourse->fullname . '</a>';
            }
        } elseif ( ! empty($programmitem->mdlcourse) )
        {
            $mcourse = $this->dof->sync('mcourses')->get_course($programmitem->mdlcourse);
            if ( ! empty($mcourse) )
            {
                $mcourselink = $this->dof->sync('mcourses')->get_course_link($programmitem->mdlcourse);
                $mcoursename = '<a href="' . $mcourselink . '">' . $mcourse->fullname . '</a>';
            }
        }
        
        switch ( $type )
        {
            case 'all':
                return array(
                    $actions,
                    $agename,
                    $cstreamname,
                    $cstreamcode,
                    $stream,
                    $programmname,
                    $programmitemname,
                    $teachername,
                    $departmentname,
                    $selfenrol,
                    $studentslimit,
                    $description,
                    $obj->mdlgroup,
                    $obj->eduweeks,
                    $this->dof->storage('cstreams')->hours_int($obj->hoursweekinternally),
                    $this->dof->storage('cstreams')->hours_int($obj->hoursweekdistance),
                    dof_userdate($obj->begindate, '%d.%m.%y'),
                    dof_userdate($obj->enddate, '%d.%m.%y'),
                    $obj->salfactor,
                    $obj->substsalfactor,
                    $calc_formula,
                    $this->dof->storage('cstreams')->calculation_salfactor($obj),
                    $mcoursename,
                    $statusname
                );
            case 'small':
                return array(
                    $actions,
                    $agename,
                    $cstreamname,
                    $cstreamcode,
                    $stream,
                    $programmname,
                    $programmitemname,
                    $teachername,
                    $departmentname,
                    $obj->salfactor . '/' . $obj->substsalfactor,
                    $statusname
                );
        }
    }
    
    /**
     * Получает объединенный список групп учебного процесса и студентов, 
     * которые участвуют в процессе, но не входят ни в одну группу
     * @param int $cstreamid идентификатор учебного процесса
     * @return string
     */
    private function get_stream($cstream)
    {
        // Состав потока
        // Инициализация данных
        $groups = $programmsbcs = $contracts = $personsbygroup = false;
        $stream = $grouplist = $personlist = '';
        // Получаем группы учебного процесса
        $groupsbycstream = $this->dof->storage('agroups')->get_group_cstream($cstream->id);
        if( $groupsbycstream )
        {// Нормализация массива групп
            foreach($groupsbycstream as $group)
            {
                $groups[$group->id] = $group;
            }
        }
        
        // Получаем студентов учебного потока
        $cstreamstudents = $this->get_cstream_students($cstream->id);
        
        if( $groups )
        {// Если группы есть
            // Получим список ссылок на группы
            $grouplist = $this->get_group_list($groups, $cstream->departmentid);
            // Получим подписки по группам
            $programmsbcs = $this->get_programmsbcs_by_groups($groups);
            if( $programmsbcs )
            {// Если получили подписки
                // Получим список контрактов
                $contracts = $this->get_contracts_by_programmsbcs($programmsbcs);
                if( $contracts )
                {// Если получили контракты
                    // Получим список персон
                    $personsbygroup = $this->get_persons_by_contracts($contracts);
                }
            }
        }

        if( $personsbygroup && $cstreamstudents )
        {// Если есть список персон, полученный по группам учебного процесса и список студентов учебного процесса
            // Найдем разницу между ними
            $studentswithoutgroupids = array_diff(array_keys($cstreamstudents), array_keys($personsbygroup));
            if( $studentswithoutgroupids )
            {// Если разница есть - используем ее
                foreach($studentswithoutgroupids as $id)
                {
                    $studentswithoutgroup[$id] = $cstreamstudents[$id];
                }
            } else
            {// Если разницы нет - значит нет
                $studentswithoutgroup = [];
            }
        } elseif( ! $personsbygroup && $cstreamstudents )
        {// Если нет групп и есть список студентов учебного процесса
            $studentswithoutgroup = $cstreamstudents;
        } else
        {// Если есть только группы
            $studentswithoutgroup = [];
        }
        
        // Получим список ссылок на студентов, которых нет в группах
        $personlist = $this->get_person_list($studentswithoutgroup, $cstream->departmentid);
        
        // Объединим список групп и студентов, которых нет в группах
        $stream = $grouplist . ($grouplist && $personlist ? '<br>' : '') . $personlist;
        
        return $stream;
    }
    
    /**
     * Получить список ссылок на персоны
     * @param array $persons массив объектов персон
     * @param int $depid подразделение, в котором находится cstream
     * @return string html-код: список ссылок на персоны, если их не больше 5, или 
     * модальное окно со списком ссылок на персоны, если их больше 5
     */
    private function get_person_list($persons, $depid)
    {
        $personlist = [];
    
        if( $persons )
        {
            foreach($persons as $person)
            {
                if( $li = $this->get_person_link($person) )
                {
                    $personlist[] = $li;
                }
            }
        }
        if( ! empty($personlist) )
        {
            $displaymaxstreamitems = $this->dof->storage('config')->get_config_value('display_max_stream_items', 'storage', 'cstreams', $depid);
            if( count($personlist) <= (int)$displaymaxstreamitems )
            {
                return $this->dof->get_string('students', 'cstreams') . ':<br>' . implode('<br>', $personlist);
            } else 
            {
                $title = $label = $this->dof->get_string('student_list', 'cstreams');
                return $this->dof->modlib('widgets')->modal($label, implode('<br>', $personlist), $title);
            }
        } else
        {
            return '';
        }
    }
    
    /**
     * Получить ссылку на персону
     * @param stdClass $person объект персоны (запись из базы)
     * @return boolean|string
     */
    private function get_person_link($person)
    {
        if( ! is_object($person) )
        {
            return false;
        }
        if( isset($person->id) && isset($person->departmentid) )
        {
            $addvars = ['id' => $person->id, 'departmentid' => $person->departmentid];
            $url = new moodle_url('/blocks/dof/im/persons/view.php', $addvars);
            return dof_html_writer::link($url, $this->dof->storage('persons')->get_fullname($person->id));
        } else
        {
            return false;
        }
    }
    
    /**
     * Получить список подписок на программы по группам
     * @param array $groups массив объектов групп
     * @return false|array массив объектов подписок на программы
     */
    private function get_programmsbcs_by_groups($groups)
    {
        $agroupids = array_keys($groups);
        return $this->dof->storage('programmsbcs')->get_programmsbcs_by_options(['agroupids' => $agroupids]);
    }
    
    /**
     * Получить массив контрактов по подпискам на программы
     * @param array $programmsbcs массив объектов подписок на программы
     * @return false|array массив объектов контрактов
     */
    private function get_contracts_by_programmsbcs($programmsbcs)
    {
        foreach($programmsbcs as $programmbcs)
        {
            $contractids[$programmbcs->contractid] = $programmbcs->contractid;
        }
        return $this->dof->storage('contracts')->get_records(['id' => $contractids]);
    }
    
    /**
     * Получить список персон по контрактам
     * @param array $contracts массив объектов контрактов
     * @return false|array массив объектов персон
     */
    private function get_persons_by_contracts($contracts)
    {
        foreach($contracts as $contract)
        {
            $personids[$contract->studentid] = $contract->studentid;
        }
        return $this->dof->storage('persons')->get_records(['id' => $personids]);
    }
    
    /**
     * Получить студентов учебного процесса
     * @param int $cstreamid идентификатр учебного процесса
     * @return false|array массив объектов персон
     */
    private function get_cstream_students($cstreamid)
    {
        $persons = [];
        $dataobject = new stdClass();
        $dataobject->cstreamid = $cstreamid;
        $select = $this->dof->storage('cpassed')->get_select_listing($dataobject);
        $cpasseds = $this->dof->storage('cpassed')->get_records_select($select);
        if( $cpasseds )
        {
            foreach($cpasseds as $cpassed)
            {
                $persons[$cpassed->studentid] = $this->dof->storage('persons')->get($cpassed->studentid);
            }
            return $persons;
        } else 
        {
            return false;
        }
    }
    
    /**
     * Получить список ссылок на группы учебного процесса
     * @param array $groups массив объектов групп
     * @param $depid подразделение, в котором находится cstream
     * @return string
     */
    private function get_group_list($groups, $depid)
    {
        $grouplist = [];
        
        if( $groups )
        {
            foreach($groups as $group)
            {
                if( $li = $this->get_group_link($group) )
                {
                    $grouplist[] = $li;
                }
            }
        }
        if( ! empty($grouplist) )
        {
            $displaymaxstreamitems = $this->dof->storage('config')->get_config_value('display_max_stream_items', 'storage', 'cstreams', $depid);
            if( count($grouplist) <= (int)$displaymaxstreamitems )
            {
                return $this->dof->get_string('groups', 'cstreams') . ':<br>' . implode('<br>', $grouplist);
            } else 
            {
                $title = $label = $this->dof->get_string('group_list', 'cstreams');
                return $this->dof->modlib('widgets')->modal($label, implode('<br>', $grouplist), $title);
            }
        } else 
        {
            return '';
        }
    }
    
    /**
     * Получить ссылку на группу
     * @param stdClass $group объект группы (запись из базы)
     * @return string html-код ссылки на группу
     */
    private function get_group_link($group)
    {
        if( ! is_object($group) )
        {
            return false;
        }
        if( isset($group->id) && isset($group->name) && isset($group->departmentid) )
        {
            $addvars = ['agroupid' => $group->id, 'departmentid' => $group->departmentid];
            $url = new moodle_url('/blocks/dof/im/agroups/view.php', $addvars);
            return dof_html_writer::link($url, $group->name);
        } else 
        {
            return false;
        }
    }
    
    /** 
     * Получить html-код таблицы со списком учебных потоков для одной группы
     * 
     * 
     * @param int $agroupid - id группы для которой получается список потоков (таблица agroups)
     * @param array $options - Опции отображения таблицы
     *          ['status'] = string - Cтатус потоков, которые нужно вывести
     * 
     * @return string - HTML-код таблицы
     */
    public function get_table_list_agenums($agroupid, $options = [])
    {
        $result = '';
        
        // Опции работы
        if ( isset($options['status']) )
        {// Статус установлен
            $status = $options['status'];
        } else 
        {// Статус не установлен
            $status = NULL;
        }
        
        // Получение группы
        $agroup = $this->dof->storage('agroups')->get($agroupid);
        if ( empty($agroup) )
        {// Группа не получена
             $this->dof->print_error('agroup_not_found', '', null, 'im', 'cstreams');
        } elseif ( empty($agroup->agenum) )
        {// Группа еще не начала свое обучение 
            $result .= $this->get_agroup_title($agroup->name, $agroup->code, $status);
            $result .= $this->dof->modlib('widgets')->print_box('<p align="center">'.
            $this->dof->get_string('no_items_in_program', 'programmitems').'</p>', 
            'generalbox', '', true);
        } else
        {// Формируем таблицу
            // Заголовок
            $result .= $this->get_agroup_title($agroup->name, $agroup->code, $status);
            
            // определяем, какую таблицу мы должны вывести
            if ( $status == 'active' )
            {// Выводим только потоки с активным статусом
                $result .= $this->print_agenum_table($agroup->id, 0, 
                    $this->dof->get_string('active_cstreams', 'cstreams'), $status);
            }else
            {// Выводим потоки по параллелям
                // @todo выбрать параллели не по AGEID, а нормальным способом
                $ageids = $this->dof->storage('cstreams')->get_agroup_ageids($agroup->id);
                if ( $ageids )
                {// если потоки есть
                    $i = 1;
                    foreach ( $ageids as $ageid )
                    {// для каждой параллели показываем свою таблицу
                        $title   = $this->dof->get_string('parallel', 'cstreams').' '.$i;
                        // каждую таблицу заносим в общий html-результат
                        $result .= $this->print_agenum_table($agroupid, $ageid, $title);
                        $i++;
                    }
                }else
                {// Если потоков нет - выведем сообщение
                    $result .= $this->dof->modlib('widgets')->print_box('<p align="center">'.
                    $this->dof->get_string('no_items_in_program', 'programmitems').'</p>', 
                    'generalbox', '', true);
                }
            }
        }
        // возвращаем результат в виде общей строки html-кода
        return $result;
    }
    
    
    /** Получить заголовок для страницы просмотра списка предметов программы
     * @param string $title - название учебной программы
     * @return string отформатированный заголовок со всеми html-тегами
     */
    private function get_agroup_title($title, $code, $status=null)
    {
        if( $status == 'active' )
        {// нужны только подписки с активным статусом
            return $this->dof->modlib('widgets')->print_heading(
                $this->dof->get_string('active_cstream_list', 'cstreams').' &quot;'.
                $title.' ['.$code.']&quot;', 'center', 2, 'main', true);
        }
        // если нужны потоки по периодам - выведем соответствующий заголовок 
        return $this->dof->modlib('widgets')->print_heading(
            $this->dof->get_string('agroup', 'cstreams').' &quot;'.
            $title.' ['.$code.']&quot;','center', 2, 'main', true);
    }
    
    
    /** Получить таблицу со списком предметов по одному периоду
     * 
     * @param int $agroupid - id группы, для которой рисуется таблицы (таблица agroups)
     * @param int $agenum - относительный номер периода внутри программы 
     * @param string $title - заголовок таблицы
     * @param string $status - статус учебных потоков, которые нужно извлечь
     * 
     * @return string - HTML код таблицы
     */
    private function print_agenum_table($agroupid, $ageid, $title, $status=null)
    {
        global $addvars;
        
        // Объект таблицы
        $table = new stdClass();
        $table->head       = array($title, $this->dof->get_string('programmitem', 'cstreams'), $this->dof->get_string('actions', 'cstreams'));
        $table->size       = array(null, null, '100px');
        $table->align      = array('center', 'center', 'center');
        $table->width      = '80%';
        $table->tablealign = 'center';
        
        if ( ! $agcstreams = $this->get_agroup_cstreams($agroupid, $ageid, $status) OR empty($agcstreams) )
        {// не найдены предмето-классы для указанной группы 
            // или нет групп с таким статусом
            // выведем сообщение
            $table->data[] = array($this->dof->get_string('not_found_cstreams_for_agroups', 'cstreams'), '&nbsp;', '&nbsp;');
            return $this->dof->modlib('widgets')->print_table($table, true);
        }
        
        foreach ( $agcstreams as $cstream )
        {// если потоки есть, то составим таблицу из них
            //создаем переменную, в которую будем записывать код панели инсмтрументов с иконками
            $actions = '';
            
            // Дисциплина
            $programmitem = $this->dof->storage('programmitems')->get($cstream->programmitemid);
            if ( empty($programmitem) )
            {// Дисциплина не найдена
                $programmname = $this->dof->get_string('message_programmitem_not_found', 'cstreams');
            } else 
            {
                if ( $this->dof->storage('programmitems')->is_access('view', $cstream->programmitemid) )
                {// Есть право на просмотр дисциплины
                    $programmname = $programmitem->name;
                } else 
                {// Права на просмотр дисциплины нет
                    $programmname = $this->dof->get_string('message_access_denied', 'cstreams');
                }
            }
            
            $cstreamname = $cstream->name;
            // создаем панель инструментов из иконок
            if ( $this->dof->storage('cstreams')->is_access('view',$cstream->id) )
            {// если есть права на просмотр данных о потоке - то показываем ссылку на страницу просмотра
                $cstreamdata = '<a href="'.$this->dof->url_im('cstreams', '/view.php?cstreamid='.$cstream->id,$addvars).
                             '">'.$cstreamname.'</a>';
                // И добавляем иконку в панель инструментов
                $actions .= '<a href="'.$this->dof->url_im('cstreams', '/view.php?cstreamid='.$cstream->id,$addvars).'">';
                $actions .= '<img src="'.$this->dof->url_im('cstreams', '/icons/view.png').'" 
                alt=  "'.$this->dof->get_string('view', 'cstreams').'" 
                title="'.$this->dof->get_string('view', 'cstreams').'" /></a>&nbsp;';
            }else
            {// если прав нет - то показываем просто текст
                $cstreamdata = $cstreamname;
            }
            
            if ( $this->dof->storage('cstreams')->is_access('edit',$cstream->id) OR 
                 $this->dof->storage('cstreams')->is_access('edit/plan',$cstream->id) )
            {// если есть права - то покажем иконку редактирования
                $actions .= '<a href="'.$this->dof->url_im('cstreams', '/edit.php?cstreamid='.$cstream->id,$addvars).'">';
                $actions .= '<img src="'.$this->dof->url_im('cstreams', '/icons/edit.png').'"
                alt=  "'.$this->dof->get_string('edit', 'cstreams').'" 
                title="'.$this->dof->get_string('edit', 'cstreams').'" /></a>&nbsp;';
            }
            
            if ( $this->dof->im('journal')->is_access('view_journal') )
            {// если есть право на просмотр журнала - покажем его
                $actions .= '<a href="'.$this->dof->url_im('journal', 
                            '/group_journal/index.php?csid='.$cstream->id,$addvars).'">';
                $actions .= '<img src="'.$this->dof->url_im('cstreams', '/icons/journal.png').'"
                alt=  "'.$this->dof->get_string('view_journal', 'cstreams').'" 
                title="'.$this->dof->get_string('view_journal', 'cstreams').'" /></a>&nbsp;';
            }
            
            if ( $this->dof->storage('cpassed')->is_access('view') )
            {// если есть права на просмотр подписок - покажем их
                $actions .= '<a href="'.$this->dof->url_im('cpassed', '/list.php?agroupid='.$agroupid,$addvars).'">';
                $actions .= '<img src="'.$this->dof->url_im('cstreams', '/icons/cpassed.png').'"
                alt=  "'.$this->dof->get_string('view_cpassed', 'cstreams').'" 
                title="'.$this->dof->get_string('view_cpassed', 'cstreams').'" /></a>&nbsp;';
            }            
            // записываем все что получилось в таблицу
            $table->data[] = array($cstreamdata, $programmname, $actions);
        }
        if ( ! empty($table->data) )
        {// выводим на экран таблицу со всем содержимым, если она не пуста
            return $this->dof->modlib('widgets')->print_table($table, true);
        }
        // если таблица не выведена - сообщим, что нет потоков
        $table->data[] = array($this->dof->get_string('not_found_cstreams_for_agroups', 'cstreams'),'&nbsp;', '&nbsp;');
        return $this->dof->modlib('widgets')->print_table($table, true);
    }
    
    /** Получить список всех потоков для учебной программы
     * 
     * @return array массив записей из таблицы cstreams или false
     * @param object $agroup - id академической группы для которой извлекаются потоки (таблица agroups) 
     * @param object $status[optional] - статус учебных потоков. Если статус не передан - то выводятся 
     * учебные потоки с любым статусом 
     */
    private function get_agroup_cstreams($agroupid, $ageid, $status=null)
    {
        // получим id периода, в котором изучается группа, по его номеру
        if ( ! $agroup = $this->dof->storage('agroups')->is_exists($agroupid) )
        {// академическая группа не найдена
            $DOF->print_error(
                'agroup_not_found',
                '',
                null,
                'im',
                'cstreams'
            );
        }
        if ( $status == 'active' )
        {// нужны только потоки с активным статусом
            return $this->dof->storage('cstreams')->get_agroup_status_cstreams($agroupid, $status);
        }
        // нужны потоки по периодам
        return $this->dof->storage('cstreams')->get_agroup_agenum_cstreams($agroupid, $ageid);
    }
    
    /** Возвращает объект приказа
     *
     * @param string $code
     * @param integer  $id
     * @return dof_storage_orders_baseorder
     */
    public function order($code, $id = NULL)
    {
        require_once($this->dof->plugin_path('im','cstreams','/orders/change_status/init.php'));
        require_once($this->dof->plugin_path('storage','cstreams','/orders/change_teacher/init.php'));
        switch ($code)
        {
            case 'change_status':
                $order = new dof_im_cstreams_order_change_status($this->dof);
                if ( ! is_null($id) )
                {// нам передали id, загрузим приказ
                    if ( ! $order->load($id) )
                    {// Не найден
                        return false;
                    }
                }
                // Возвращаем объект
                return $order;
            break;
            case 'change_teacher':
                $order = new dof_storage_cstreams_order_change_teacher($this->dof);
                if ( ! is_null($id) )
                {// нам передали id, загрузим приказ
                    if ( ! $order->load($id) )
                    {// Не найден
                        return false;
                    }
                }
                // Возвращаем объект
                return $order;
            break;
        }
    }
    
    /** Получить список пользователей, доступных для записи на поток 
     * 
     * @return array массив записей о пользователях в зависимости от выбранного режива представления 
     * @param int $cstreamid - id учебного потока в таблице cstreams
     * @param string $mode - вид отображения списка пользователей. Допустимые значения:
     *                         groups - отобразить пользователей по группам
     *                         persons - отобразить всех доступных для записи пользователей
     */
    public function get_add_persons_list($cstreamid, $mode='persons')
    {
        if ( ! $cstream = $this->dof->storage('cstreams')->get($cstreamid) )
        {// поток не найден
            return array();
        }
        switch ( $mode )
        {// в зависимости от выбранного режима отображения генерируем разные форматы массива
            case 'groups' : return $this->get_add_persons_list_for_groups($cstream); break;
            case 'persons': return $this->get_add_persons_list_for_persons($cstream); break;
            default: return array();
        }
    }
    
    /** Получить список пользователей для добавления их в поток, в режиме отображения "для групп"
     * 
     * @return array - массив объектов вида:
     *                 $object->name - название группы
     *                 $object->options[] - массив записей пользователей 
     *                 в формате array('id в таблице programmsbcs' => 'ФИО[контракт]')
     * @param object $cstream - объект учебного потока из таблицы cstreams
     */
    private function get_add_persons_list_for_groups($cstream)
    {
        // узнаем, к какой программе принадлежит этот поток
        $programmid = $this->dof->storage('programmitems')->get_field($cstream->programmitemid, 'programmid');
        // получим все связи групп с учебным процессом
        if ( ! $cslinks = $this->dof->storage('cstreamlinks')->
                get_records(array('cstreamid'=>$cstream->id, 'agroupsync'=>array('nolink', 'notrequired'))) )
        {// поток не связан ни с одной группой
            return array();
        }
        $groups = array();
        foreach ( $cslinks as $cslink )
        {// перебираем все связи с группами и составляем список групп
            // FIXME учесть статус группы (сейчас он не учитывается только для теста)
            if ( $group = $this->dof->storage('agroups')->
                    get($cslink->agroupid) )
            {// группа нашлась - добавим ее в массив
                $groups[$group->id] = $group;
            }
        }
        
        if ( empty($groups) )
        {// не найдено ни одной группы из которой можно было бы добавить учеников
            return array();
        }
        $grouplist = array();
        foreach ( $groups as $group )
        {// перебираем все группы и для каждой получаем пользователей
            if ( $students = $this->dof->storage('programmsbcs')->
                    get_records(array('agroupid'=>$group->id, 'programmid'=>$programmid, 
                    'status'=>array('application', 'plan', 'active', 'suspend'))) )
            {// получаем все подписки учеников на этот поток
                $groupdata          = new stdClass();
                $groupdata->name    = $group->name;
                // получаем список учеников, в нужном для составления select-элемента формате
                $groupdata->options = $this->transform_students_to_options($students, $cstream);
                $grouplist[] = $groupdata;
            }
        }
        
        return $grouplist;
    }
    
    /** Получить массив, пригодный для составления select-элемента (вида "ключ" => "значение")
     * 
     * @return array
     * @param array $programmsbcs - массив записей из таблицы programmsbcs
     * @param object $cstream - объект из таблицы cstreams
     */
    private function transform_students_to_options($programmsbcs, $cstream)
    {
        $result = array();
        foreach ( $programmsbcs as $programmsbc )
        {
            // @todo учесть период подписки
            /*$sbcageid = $this->dof->storage('ages')->get_next_ageid($programmsbc->agestartid, $programmsbc->agenum);
            if ( $sbcageid != $cstream->ageid )
            {// период подписки не совпадает с периодом потока - пропускаем подписку
                continue;
            }*/
            // по каждой подписке на программу получаем контракт
            if ( ! $contract = $this->dof->storage('contracts')->get($programmsbc->contractid) )
            {// такой контракт не найден
                // @todo записать это событие в лог когда это станет возможно
                continue;
            }
            // по контракту получаем ученика
            if ( ! $person = $this->dof->storage('persons')->get($contract->studentid) )
            {// ученик не зарегестрирован - это ошибка
                // @todo записать это событие в лог когда это станет возможно
                continue;
            }
            // составляем массив для элемента select
            $result[$programmsbc->id] = $person->sortname.' ['.$contract->num.']';
        }
        // сортируем учеников по фамилии
        asort($result);
        
        return $result;
    }
    
    /** Получить список пользователей для добавления их в поток, в режиме отображения "все пользователи"
     * 
     * @return array - массив в формате array('id в таблице programmsbcs' => 'ФИО[контракт]')
     * @param object $cstream - объект учебного потока из таблицы cstreams
     * 
     * @todo оптимизировать алгоритм, чтобы он работал немного быстрее
     */
    private function get_add_persons_list_for_persons($cstream)
    {
        // узнаем, к какой программе принадлежит этот поток
        $programmid = $this->dof->storage('programmitems')->get_field($cstream->programmitemid, 'programmid');
        if ( ! $programmid )
        {// программа не найдена  - это ошибка
            // @todo записать ошибку в лог, когда это станет возможно
            return array();
        }
        $agenum = $this->dof->storage('programmitems')->get_field($cstream->programmitemid, 'agenum');
        if ( $agenum )
        {// если параллель предмета указана - то только подписки этой параллели
            $programmsbcs = $this->dof->storage('programmsbcs')->
                    get_records(array('programmid'=>$programmid, 'agenum'=>$agenum , 
                                    'status'=>array('plan', 'active', 'suspend')));
        }else
        {// параллель нулевая - выводим для всех
            $programmsbcs = $this->dof->storage('programmsbcs')->
                    get_records(array('programmid'=>$programmid,'status'=>array('plan', 'active', 'suspend')));
        }
        
        // оставим в списке только те объекты, на использование которых есть право
        $permissions  = array(array('plugintype'=>'storage', 'plugincode'=>'programmsbcs', 'code'=>'use'));
        $programmsbcs = $this->dof->storage('acl')->get_acl_filtered_list($programmsbcs, $permissions);
        
        if ( ! $programmsbcs )
        {// не найдено ни одной подходящей подписки на программу
            return array();
        }
        $students = array();
        foreach ( $programmsbcs as $programmsbc )
        {// перебираем все подписки на программы и смотрим, совпадает ли их период с периодом потока
            //$sbcageid = $this->dof->storage('ages')->get_next_ageid($programmsbc->agestartid, $programmsbc->agenum);
            //if ( $sbcageid != $cstream->ageid )
            //{// период подписки не совпадает с периодом потока - пропускаем подписку
            //    continue;
            //}
            // по каждой подписке на программу получаем контракт
            if ( ! $contract = $this->dof->storage('contracts')->get($programmsbc->contractid) )
            {// такой контракт не найден
                // @todo записать это событие в лог когда это станет возможно
                continue;
            }
            // по контракту получаем ученика
            if ( ! $person = $this->dof->storage('persons')->get($contract->studentid) )
            {// ученик не зарегестрирован - это ошибка
                // @todo записать это событие в лог когда это станет возможно
                continue;
            }
            // составляем массив для элемента select
            $students[$programmsbc->id] = $person->sortname.' ['.$contract->num.']';
            //добавим код группы если есть
            if ( $agroupcode = $this->dof->storage('agroups')->get_field($programmsbc->agroupid,'code') )
            {
                $students[$programmsbc->id] .= '['.$agroupcode.']';
            }
        }
        
        // теперь получим список учеников, которые уже записаны на поток
        $removeids = array_keys($this->get_remove_persons_list($cstream->id));
        if ( ! empty($removeids) )
        {// и вычтем их из общего количества
            foreach ( $removeids as $removeid )
            {
                if ( isset($students[$removeid]) )
                {// если такая запись есть - удалим ее из итогового массива, чтобы не записать
                    // ученика на один поток дважды
                    unset($students[$removeid]);
                }
            }
        }
         // сортируем учеников (мы не могли сделать этого раньше)
        asort($students);
        
        return $students;
    }
    
    /** Получить список пользователей, которые уже обучаются на потоке
     * 
     * @return array массив записей о пользователях в формате array('id в таблице programmsbcs' => 'ФИО[контракт]')
     * @param int $cstreamid - id учебного потока в таблице cstreams
     * @param string $mode - вид отображения списка пользователей. Допустимые значения:
     *                         groups - отобразить пользователей по группам
     *                         persons - отобразить всех доступных для записи пользователей
     *                         
     * @todo непонятно как быть со статусом reoffset для cpassed
     * @todo как определять, что записывать в поле agroup?
     */
    public function get_remove_persons_list($cstreamid)
    {
        if ( ! $cstream = $this->dof->storage('cstreams')->get($cstreamid) )
        {// поток не найден
            return array();
        }
        // получим все подписки на предмет для этого потока
        if ( ! $cpassed = $this->dof->storage('cpassed')->
                get_records(array('cstreamid'=>$cstream->id,  
                'ageid'=>$cstream->ageid, 'status'=>array('plan', 'active', 'suspend'))) )
        {// ни одной подписки не найдено - значит на этот процесс еще никто не подписан
            return array();
        }
        // возвращаем полученный по подпискам список учеников
        return $this->get_students_by_cpassed($cpassed);
    }
    
    /** Получить данные об учениках по их подпискам на предметы
     * 
     * @return array массив вида [id в таблице programmsbcs] => 'ФИО[код]'
     * @param array $cpassed - массив записей из таблицы cpassed
     */
    private function get_students_by_cpassed($cpassed)
    {
        $students = array();
        foreach ( $cpassed as $cpdata )
        {// перебираем все подписки на предметы и получаем подписки на программы
            if ( ! $programmsbc = $this->dof->storage('programmsbcs')->get($cpdata->programmsbcid) )
            {// не найдена подписка на программу
                // @todo записать это событие в лог когда это станет возможно
                continue;
            }
            // по каждой подписке на программу получаем контракт
            if ( ! $contract = $this->dof->storage('contracts')->get($programmsbc->contractid) )
            {// такой контракт не найден
                // @todo записать это событие в лог когда это станет возможно
                continue;
            }
            // по контракту получаем ученика
            if ( ! $person = $this->dof->storage('persons')->get($contract->studentid) )
            {// ученик не зарегестрирован - это ошибка
                // @todo записать это событие в лог когда это станет возможно
                continue;
            }
            // составляем массив для элемента select
            $students[$programmsbc->id] = $person->sortname.' ['.$contract->num.']';
            //добавим код группы если есть
            if ( $agroupcode = $this->dof->storage('agroups')->get_field($programmsbc->agroupid,'code') )
            {
                $students[$programmsbc->id] .= '['.$agroupcode.']';
            }
        }
        // сортируем учеников (мы не могли сделать этого раньше)
        asort($students);
        // возвращаем итоговый результат
        return $students;
    }

    /** Распечатать таблицу для отображения шаблонов группы
     * @param int $id - id группы из таблицы agroups
     * @param int $daynum[optional]  - день недели, для которого отображаются шаблоны
     * @param int $dayvar[optional] - вариант недели, для которого отображаются шаблоны
     * @return string
     */
    public function get_table_statushistory($id)
    {
        $conds = array();
        $conds['plugintype'] = 'storage';
        $conds['plugincode'] = 'cstreams';
        $conds['objectid'] = $id;
        $list = $this->dof->storage('statushistory')->get_records($conds);
        //print_object($list);
        if ( empty($list) )
        {// не нашли шаблон - плохо
            return '';
        }
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->width = '100%';
        $table->align = array("center","center","center","center","center",
                                      "center","center","center","center","center",
                                      "center","center","center");
        // шапка таблицы
        $table->head = array($this->dof->get_string('prevstatus', 'cstreams'),
                         $this->dof->get_string('nextstatus', 'cstreams'),
                         $this->dof->get_string('person_status_change', 'cstreams'),
                         $this->dof->get_string('person_status_change_date', 'cstreams'));
        // формируем данные
        $table->data = array();
        // @todo отображение берем для потока,т.к таблицы индетичны
        // если что-то изменится, потом напишем отдельный метод
        foreach ( $list as $report )
        {//для каждого шаблона формируем строку
            $prevstatus = $this->dof->workflow('cstreams')->get_name($report->prevstatus);
            $status = $this->dof->workflow('cstreams')->get_name($report->status);
            $person = $this->dof->storage('persons')->get_fullname(
                      $this->dof->storage('persons')->get_by_moodleid_id($report->muserid));
            $status_date = date("Y-m-d H-i-s", $report->statusdate);
            
            $table->data[] = array($prevstatus,$status,$person, $status_date);         
        }
        return $this->dof->modlib('widgets')->print_table($table,true);
    }

    /** Возвращает html-код формы для выбора предмета для привязки к потоку
     * @param object $url
     * @param object $list
     * @param boolean $flag - указывает на подписку обязательных программ
     * @return 
     */
    public function get_bind_form_html($url, $list, $flag=false)
    {
        if ( ! isset($list) OR ! is_array($list) OR empty($list))
        {
            return '';
        }
        $html_string = '<div class="mform" align="center"><form action="'.$url.'" method="post" ><select name="pitemid"><option value="0" selected>'.
            $this->dof->get_string('choose_programmitem','cstreams').'</option>';
        foreach($list as $key => $element)
        {
            $html_string .= '<option value="'.$key.'">'.$element.'</option>';
        }
        if ( $flag)
        {
            $html_string .='</select><input type="submit" name="bind"  value="'.$this->dof->get_string('bind','cstreams').'">
                                <br> <input type="submit" name="bindall" value="'.$this->dof->get_string('bind1','cstreams').'"></form></div>';
        }else 
        {
            $html_string .='</select><input type="submit" name="bind" value="'.$this->dof->get_string('bind','cstreams').'"></form></div>';
        }    
        
        return $html_string;
    }
    
    /** Метод, который возаращает список для автозаполнения
     * 
     * @param string $querytype - тип завпроса(поу молчанию стандарт)
     * @param string $data - строка
     * @param integer $depid - id подразделения  
     * 
     * @return array or false - запись, если есть или false, если нет
     */
    public function widgets_field_variants_list($querytype, $depid, $data, $objectid)
    {
        // в зависимости от типа, проверяем те или иные права
        switch ($querytype)
        {
            // выдать конкретной персоне косплект
            case 'list_teachers' :        
                // есть права - то посылаем запрос
                return $this->dof->storage('appointments')->widgets_field_ajax_select($querytype, $depid, $data);
            break;
            case 'list_programmitems' :        
                // есть права - то посылаем запрос
                return $this->dof->storage('programmitems')->widgets_field_ajax_select($querytype, $depid, $data);
            break;
        }
        // нет ничего
        return false;
        
    }
    
    /** 
     * Получить html-ссылку на просмотр предмето-класса
     * 
     * @param int ID - id предмето-класса в таблице programmitems
     * @param string text - текст ссылки
     * @param array addvars - GET-параметры для ссылки
     *
     * @return string html-строка со ссылкой на дисциплину или пустая строка в случае ошибки
     */
    public function get_html_link($id, $text='', $addvars = [] )
    {
        if ( empty($addvars) )
        {
            $addvars = [];
        }
        if ( !empty($text) )
        {
            $name = $text;
        }
        else 
        {
            if ( ! $name = $this->dof->storage('cstreams')->get_field($id, 'name') )
            {
                return '';
            }
        }
        
        $is_access = $this->dof->storage('cstreams')->is_access('view', $id);
        if ( $is_access )
        {// Есть доступ к просмотру
            return '<a href="'.$this->dof->url_im($this->code(),
                '/view.php', array_merge($addvars, array('cstreamid' => $id))).'">'.$name.'</a>';
        } else
        {// Доступа нет
            return '<span>'.$name.'</span>';
        }
    
    }
    
    /**
     * Получение данных для вывода рейтинга
     *
     * @param integer $cstream_id
     * @param bool $get_from_cache
     *
     * @return stdClass | bool
     */
    public function get_cstream_grades($cstream_id = null, $get_from_cache = true)
    {
        if ( empty($cstream_id) )
        {
            return false;
        }
        
        // Получение кэш хранилища
        $cache = $this->dof->get_cache('im', 'rtreport', 'cstream');
        
        if ( $get_from_cache )
        {
            // Получение данных из кэша
            $result_final = $cache->get($cstream_id);
        }
        
        if ( ! empty($result_final) )
        {
            // Кэш не пуст, вернем закэшированные данные
            return $result_final;
        } else
        {
            // Объект результата
            $result_final = new stdClass();
            $result_final->cstreamid = $cstream_id;
            $result_final->cstream = $this->dof->storage('cstreams')->get_record(['id' => $cstream_id]);
            $result_final->number_of_plans = 0;
            $result_final->users = [];
            $result_final->max_grade = 0;
            $result_final->max_grade_average = 0;
            $result_final->groups = [];
            $result_final->teacher_name = '';
            $result_final->discipline_name =  $this->dof->storage('programmitems')->get_name($result_final->cstream->programmitemid);
            
            // конфиг включения в расчет занятий при отсутствии оценки
            $includeemptygrades = $this->dof->storage('config')->get_config_value(
                            'include_empty_grades',
                            'im', 
                            'cstreams',
                            optional_param('departmentid', 0, PARAM_INT));
            
            // Получение академические групп подписки
            $groups = $this->dof->storage('agroups')->get_group_cstream($cstream_id);
            
            // Имя учителя
            $result_final->teacher_name = $this->dof->get_string('rtreport_agroup_teacher_name_empty', 'rtreport');
            $teacher = $this->dof->storage('appointments')->get_person_by_appointment($result_final->cstream->appointmentid);
            if ( ! empty($teacher) )
            {
                $result_final->teacher_name = $this->dof->storage('persons')->get_fullname($teacher->id);
            }
            
            if ( ! empty($groups) )
            {
                // Получение валидных статусов
                $statuses = array_keys($this->dof->workflow('agroups')->get_meta_list('real'));
                
                $result_final->groups = array_filter(
                        $groups,
                        function ($obj) use ($statuses)
                        {
                            if ( ! in_array($obj->status, $statuses) )
                            {
                                return false;
                            }
                            
                            return true;
                });
            }
            
            // Счетчик
            $counter = 0;
            
            if ( ! empty($cstream_id) )
            {
                // Массив пользователей с их подписками
                $users = [];
                
                // Объект учебного процесса
                $cstream = $this->dof->storage('cstreams')->get_record(['id' => $cstream_id]);
                
                // Получение всех подписок, относящихся к учебному процессу
                $cpasseds = $this->dof->storage('cpassed')->get_records(['cstreamid' => $cstream_id, 'status' => array_keys($this->dof->workflow('cpassed')->get_meta_list('real'))]);
                if ( ! empty($cpasseds) )
                {
                    // По этому учебному процессу есть подписки
                    $users = [];
                    foreach ( $cpasseds as $cpassed )
                    {
                        $users[$cpassed->studentid][] = $cpassed;
                    }
                }
                
                $current_time = time();
                
                $filtered_plans = [];
                // получение занятий
                /** @var []dof_lesson **/
                $lessons = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_lessons($cstream_id, true);
                foreach ( $lessons->get_lessons() as $lesson )
                {
                    if ( $lesson->plan_exists() && 
                            ! empty($lesson->get_plan()->estimated) && 
                            ( ($cstream->begindate + $lesson->get_plan()->reldate) <= $current_time ) && !$includeemptygrades )
                    {
                        $scale = $this->dof->modlib('journal')->get_manager('scale')->get_plan_scale($lesson->get_plan(), true);
                        $plan = $lesson->get_plan();
                        $plan->scale = $scale;
                        $result_final->max_grade += end($scale);
                        $filtered_plans[$lesson->get_plan()->id] = $plan;
                    }
                }
                
                // количество занятий
                $countercheck = count($filtered_plans);
                if ( empty($countercheck) )
                {
                    return $result_final;
                }
                $result_final->max_grade_average = number_format($result_final->max_grade/$countercheck, 1);
                
                // Получение оценок пользователя
                foreach ( $users as $user_id => $user )
                {
                    $result = [];
                    $cpasseds_plans_grades = [];
                    
                    foreach ( $user as $id => $cpassed )
                    {
                        // Получение оценок по всем контрольным точкам
                        $cpasseds_plans_grades[$cpassed->id] = new stdClass();
                        $cpasseds_plans_grades[$cpassed->id]->cpgrades = $this->dof->storage('cpgrades')->get_all_grade_student($cpassed->id);
                        $cpasseds_plans_grades[$cpassed->id]->cpassed = $cpassed;
                    }
                    foreach ( $cpasseds_plans_grades as $cp_id => $cp_plan_info )
                    {
                        // Проверка, что дата КТ попадает в промежуток подписки
                        if ( ! empty($cp_plan_info->cpgrades) )
                        {
                            foreach ( $cp_plan_info->cpgrades as $grade )
                            {
                                // Дата проведения КТ
                                // $theme_date = $grade->plan->datetheme + $grade->plan->reldate;
                                
                                // Проверка, что в момент занятия у студента была активная подписка (НЕ УДАЛЯТЬ)
                                // print_object($cp_plan_info->cpassed->begindate . '<=' .  $theme_date . '<=' . $cp_plan_info->cpassed->enddate);
                                // array_key_exists($grade->plan->id, $filtered_plans) &&
                                // ($cp_plan_info->cpassed->begindate <= $theme_date) &&
                                // ($theme_date <= $cp_plan_info->cpassed->enddate)
                                
                                if ( array_key_exists($grade->plan->id, $filtered_plans) )
                                {
                                    // Проверка, что оценка существует в шкале оценок занятия
                                    if ( array_key_exists(trim($grade->grade->grade), $filtered_plans[$grade->plan->id]->scale) )
                                    {
                                        if ( ! isset($result[$grade->plan->id]) )
                                        {
                                            $result[$grade->plan->id] = intval($filtered_plans[$grade->plan->id]->scale[trim($grade->grade->grade)]);
                                        } elseif ( intval($result[$grade->plan->id]) < intval($filtered_plans[$grade->plan->id]->scale[trim($grade->grade->grade)]) )
                                        {
                                            $result[$grade->plan->id] = intval($filtered_plans[$grade->plan->id]->scale[trim($grade->grade->grade)]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    // Параметры по умолчанию
                    $sum = 0;
                    $grade = 0;
                    $percent = 0;
                    
                    // количество занятий, по которым есть оценка
                    $countlessonstograde = 0;
                    
                    if ( ! empty($result) )
                    {
                        foreach ( $result as $id => $grade)
                        {
                            $sum += $grade;
                            $countlessonstograde++;
                        }
                        if ( $includeemptygrades )
                        {
                            if ( ! empty($countercheck) )
                            {
                                $grade = number_format($sum/$countercheck, 1);
                            }
                        } else 
                        {
                            if ( ! empty($countlessonstograde) )
                            {
                                $grade = number_format($sum/$countlessonstograde, 1);
                            }
                        }
                        if ( ! empty($result_final->max_grade_average) )
                        {
                            $percent = number_format(number_format($grade, 1) / number_format($result_final->max_grade_average, 1) * 100, 2);
                        }
                    }
                    // Формирование строки данных пользователя
                    $result_final->users[$user_id] = new stdClass();
                    $result_final->users[$user_id]->number = ++$counter;
                    $result_final->users[$user_id]->fio = $this->dof->storage('persons')->get_fullname($user_id);
                    $result_final->users[$user_id]->userid = $user_id;
                    $result_final->users[$user_id]->grade = $grade;
                    $result_final->users[$user_id]->sumgrade = $sum;
                    $result_final->users[$user_id]->percent = $percent;
                    $result_final->users[$user_id]->max_grade = $result_final->max_grade_average;
                }
                
                // Количество КТ
                $result_final->number_of_plans = count($filtered_plans);
            }
            
            // Установка кэша
            $cache->set($cstream_id, $result_final);
            
            return $result_final;
        }
    }
    
    /**
     * Получение нагрузки за учебный период
     *
     * @param integer $cstream_id
     * @param bool $get_from_cache
     *
     * @return stdClass | bool
     */
    public function get_workloadcstream($cstream_id = null)
    {
        if ( empty($cstream_id) )
        {
            return false;
        }
        
        // Объект результата
        $result_final = new stdClass();
        $result_final->cstreamid = $cstream_id;
        $result_final->cstream = $this->dof->storage('cstreams')->get_record(['id' => $cstream_id]);
        $result_final->lessons = [];
        $result_final->total_hours = 0;
        $result_final->groups = [];
        
        // Имя учителя
        $result_final->teacher_name = $this->dof->get_string('rtreport_agroup_teacher_name_empty', 'rtreport');
        $teacher = $this->dof->storage('appointments')->get_person_by_appointment($result_final->cstream->appointmentid);
        if ( ! empty($teacher) )
        {
            $result_final->teacher_name = $this->dof->storage('persons')->get_fullname($teacher->id);
        }
        $result_final->discipline_name =  $this->dof->storage('programmitems')->get_name($result_final->cstream->programmitemid);
        
        // Получение тематических разделов учебного процесса
        $plansections = $this->dof->storage('plansections')->get_theme_plan('plan', $cstream_id);
        
        // Получение типов занятий
        $lesson_types = $this->dof->modlib('refbook')->get_lesson_types();
        
        // Получение типов для отображения
        $lesson_types_to_show = explode(',',
                $this->dof->storage('config')->get_config_value(
                        'cstream_workload_lesson_types',
                        'im', 'cstreams',
                        optional_param('departmentid', 0, PARAM_INT)));
        
        // Получение занятий
        $lessons_all = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_lessons($cstream_id, true)->group_by_dates();
        
        if ( ! empty($lessons_all) )
        {// Найдены занятия
            
            foreach ( $lessons_all as $year => $months )
            {
                foreach ( $months as $month => $days )
                {
                    foreach ( $days as $day => $lessons )
                    {
                        foreach ( $lessons as $lesson )
                        {
                            if ( $lesson->event_exists() &&
                                    $lesson->plan_exists() &&
                                    in_array($lesson->get_plan()->type, $lesson_types_to_show))
                            {
                                // Формирование объекта информации по занятиям
                                $lesson_obj = new stdClass();
                                $lesson_obj->date = $lesson->get_event()->date;
                                $lesson_obj->lesson_type = $lesson->get_plan()->type;
                                $lesson_obj->lesson_lang = $lesson_types[$lesson->get_plan()->type];
                                $lesson_obj->hours = floatval($lesson->get_event()->ahours);
                                if ( ! empty($lesson->get_plan()->plansectionsid) && isset($plansections[$lesson->get_plan()->plansectionsid]) )
                                {
                                    $lesson_obj->theme = $plansections[$lesson->get_plan()->plansectionsid]->name;
                                } elseif ( ! empty($lesson->get_plan()->name) )
                                {
                                    $lesson_obj->theme = $lesson->get_plan()->name;
                                } else 
                                {
                                    $lesson_obj->theme = '';
                                }
                                
                                // Подсчет суммарного количества часов по всем занятиям
                                $result_final->total_hours += $lesson_obj->hours;
                                
                                // Добавление занятия в результирующий массив
                                $result_final->lessons[] = $lesson_obj;
                            }
                        }
                    }
                }
            }
        }
        
        // Получение академические групп подписки
        $groups = $this->dof->storage('agroups')->get_group_cstream($cstream_id);
        
        if ( ! empty($groups) )
        {
            // Получение валидных статусов
            $statuses = array_keys($this->dof->workflow('agroups')->get_meta_list('real'));
            
            $result_final->groups = array_filter(
                    $groups,
                    function ($obj) use ($statuses)
                    {
                        if ( ! in_array($obj->status, $statuses) )
                        {
                            return false;
                        }
                        
                        return true;
            });
        }
        
        return $result_final;
    }
    
    /**
     * Сбор рейтингов
     *
     * @return bool
     */
    public function collect_rtreport_cache()
    {
        $result = true;
        
        // Массив учебных процессов, по которым уже был собран рейтинг
        $completed_cstreams = [];
        
        // Получение подразделений
        $departments = $this->dof->storage('departments')->get_list_no_deleted();
        
        // Статусы учебных процессов, по которым идет сбор рейтинга
        $cstream_statuses = $this->dof->workflow('cstreams')->get_meta_list('actual');
        
        foreach ( $departments as $dep )
        {
            // Конфиг сбора рейтинга в подразделении
            $status = $this->dof->storage('config')->get_config_value('update_cache', $this->type(), $this->code(), $dep->id);
            if ( ! empty($status) )
            {
                // Собираем отчет по учебным процессам в подразделении
                $cstreams = $this->dof->storage('cstreams')->get_records(['departmentid' => $dep->id, 'status' => array_keys($cstream_statuses)]);
                
                foreach ( $cstreams as $cstream )
                {
                    if ( ! in_array($cstream->id, $completed_cstreams) )
                    {
                        $result = $result && (bool)$this->get_cstream_grades($cstream->id, false);
                        $completed_cstreams[] = $cstream->id;
                    }
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Обновление кэша учебного процесса
     *
     * @param integer $cstream_id
     *
     * @return void
     */
    public function todo_update_cstream_cache($cstream_id = null)
    {
        // времени понадобится много
        dof_hugeprocess();
        
        if ( ! empty($cstream_id) && $this->dof->storage('cstreams')->is_exists(['id' => $cstream_id]) )
        {
            return (bool)$this->get_cstream_grades($cstream_id, false);
        }
        
        return true;
    }
}