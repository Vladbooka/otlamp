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

/** Классы/академические группы
 * 
 */
class dof_im_agroups implements dof_plugin_im
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
        return 2017101616;
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
        return 'agroups';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('modlib'  => array('nvg'     => 2008060300,
                                        'widgets' => 2009050800),
                     'storage' => array('persons'     => 2009060400,
                                        'departments' => 2009040800,
                                        'ages'        => 2009050600,
                                        'agroups'     => 2009011601,
                                        'acl'         => 2011040504,//,
                                        'metacontracts' => 2012101500));
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
    public function is_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
        if ( $this->dof->is_access('datamanage') OR $this->dof->is_access('admin') 
             OR $this->dof->is_access('manage') )
        {// манагеру можно все
            return true;
        }
        // получаем id пользователя в persons
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        // получаем все нужные параметры для функции проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid, $depid);   
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
            $notice = "agroups/{$do} (block/dof/im/agroups: {$do})";
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
            if ( $mixedvar['storage'] == 'agroups' )
            {
                if ( isset($mixedvar['action']) AND $mixedvar['action'] == 'view' )
                {// Получение ссылки на просмотр объекта
                    $params = array('agroupid' => $intvar);
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
    /** Обработать задание, отложенное ранее в связи с его длительностью
     * @param string $code - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function todo($code,$intvar,$mixedvar)
    {
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
                $path = $this->dof->url_im('agroups','/index.php',$addvars);
//                $rez .= "<a href=\"{$path}\">".$this->dof->get_string('title', 'ages').'</a>';
//                $rez .= "<br />";
                if ( $this->is_access('view', null, null, $addvars['departmentid']) )
                {//может видеть все группы
                    $path = $this->dof->url_im('agroups','/list.php',$addvars);
                }
                //ссылка на список групп
                $result .= "<a href=\"{$path}\">".$this->dof->get_string('list', 'agroups').'</a>';
                if ( $this->storage('agroups')->is_access('create') )
                {//может создавать период - покажем ссылку
                    $result .= "<br />";
                    $path = $this->dof->url_im('agroups','/edit.php',$addvars);
                    $result .= "<a href=\"{$path}\">".$this->dof->get_string('new', 'agroups').'</a>';
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
    protected function get_access_parametrs($action, $objectid, $userid, $depid = null)
    {
        $result = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->userid       = $userid;
        $result->departmentid = $depid;
        $result->objectid     = $objectid;
        if ( is_null($depid) )
        {// подразделение не задано - берем текущее
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        if ( ! $objectid )
        {// если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        }else
        {// если указан - то установим подразделение
            $result->departmentid = $this->dof->storage('agroups')->get_field($objectid, 'departmentid');
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
        $a['exportstudents']    = array('roles'=>array('manager'));
        // права на исключение и добавление учеников в группу
        $a['addstudents']       = array('roles'=>array('manager'));
        $a['removestudents']    = array('roles'=>array('manager'));
        
        // Просмотр рейтинга по академической группе
        $a['view:rtreport/rating_agroup'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        
        return $a;
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
     * информации об учебной группе
     * @param stdClass $obj - запись из таблицы
     * @return mixed string html-код или false в случае ошибки
     */
    public function show($obj,$conds)
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
     * информации об учебной группе
     * @param int $id - id записи из таблицы
     * @return mixed string html-код или false в случае ошибки
     */
    public function show_id($id,$conds)
    {
        if ( ! is_int_string($id) )
        {//входные данные неверного формата 
            return false;
        }
        if ( ! $obj = $this->dof->storage('agroups')->get($id) )
        {// период не найден
            return false;
        } 
        return $this->show($obj,$conds);
    }
    
    /**
     * Возвращает html-код отображения 
     * информации о нескольких группах
     * @param array $list - массив записей 
     * периодов, которые надо отобразить 
     * @return mixed string в string html-код или false в случае ошибки
     */
    public function showlist($list,$conds)
    {
        if ( ! is_array($list))
        {// переданны данные неверного формата
            return false;
        }
        $data = array();
        // заносим данные в таблицу
        foreach ($list as $obj)
        {   
            $data[] = $this->get_string_table($obj,$conds);
        }

        // выводим таблицу на экран
        return $this->print_table($data);
    }
    
    /**
     * Возвращает форму создания/редактирования с начальными данными
     * @param int $id - id записи, значения 
     * которой устанавливаются в поля формы по умолчанию
     * @return moodle quickform object
     */
    public function form($id = NULL)
    {
        global $USER;
        // устанавливаем начальные данные
        if (isset($id) AND ($id <> 0) )
        {// id передано
            $agroup = $this->dof->storage('agroups')->get($id); 
            $agroup->department = $agroup->departmentid;
            unset($agroup->departmentid);
        }else
        {// id не передано
            $agroup = $this->form_new_data();
        }
        if ( isset($USER->sesskey) )
        {//сохраним идентификатор сессии
            $agroup->sesskey = $USER->sesskey;
        }else
        {//идентификатор сессии не найден
            $agroup->sesskey = 0;
        }
        $customdata = new stdClass;
        $customdata->agroup = $agroup;
        $customdata->dof    = $this->dof;
        // подключаем методы вывода формы
        $form = new dof_im_agroups_edit_form(null,$customdata);
        // очистим статус, чтобы не отображался как в БД
        unset($agroup->status);
        // заносим значения по умолчению
        $form->set_data($agroup); 
        // возвращаем форму
        return $form;
    }
    
    /**
     * Возвращает заготовку для формы создания группы
     * @return stdclassObject
     */
    private function form_new_data()
    {
        $group = new stdClass();
        $group->id = 0;
        $group->name = '';
        $group->code = '';
        $group->progages[0] = 0;
        $group->progages[1] = 0;
        $group->department = optional_param('departmentid', 0, PARAM_INT);
        return $group;
    }
    
   /** Возвращает html-код таблицы
     * @param array $date - данные в таблицу
     * @return string - html-код или пустая строка
     */
    private function print_table($date)
    {
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->size = array ('100px','150px','150px','200px','150px','100px');
        $table->align = array ("center","center","center","center","center","center","center");
        // шапка таблицы
        $table->head =  $this->get_fields_description();
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
    private function get_fields_description()
    {
        return array($this->dof->get_string('actions','agroups'),
                     $this->dof->get_string('name','agroups'),
                     $this->dof->get_string('code','agroups'),
                     $this->dof->get_string('programm','agroups'),
                     $this->dof->get_string('department','agroups'),
                     $this->dof->get_string('agenum','agroups'),
                     $this->dof->get_string('metacontract','sel'),
                     $this->dof->get_string('salfactor','agroups','<br>'),
                     $this->dof->get_string('status','agroups')); 
    }
    
    /** Возвращает массив для вставки в таблицу
     * @param object $obj
     * @return array
     */
    private function get_string_table($obj,$conds)
    {
        // для ссылок вне плагина
        $conds = (array) $conds;
        $outconds = array();
        $outconds['departmentid'] = $conds['departmentid'];
        $department = $this->dof->storage('departments')->get_field($obj->departmentid,'name').' <br>['.
                      $this->dof->storage('departments')->get_field($obj->departmentid,'code').']';
        $progname = $this->dof->storage('programms')->get_field($obj->programmid,'name').' <br>['.
                    $this->dof->storage('programms')->get_field($obj->programmid,'code').']';
        if ( ! $agenum = $this->dof->storage('agroups')->get_field($obj->id,'agenum') )
        {//номера периода нет - выведем пустую строчку
            $agenum = '';
        }
        //если есть метаконтракт
        $metacontractid = $this->dof->storage('agroups')->get_field($obj->id,'metacontractid');
        $metacontractnum = '';
        if ( !empty($metacontractid) AND $metacontract = $this->dof->storage('metacontracts')->get($metacontractid,'num'))
        {
            $metacontractnum = $metacontract->num;       
        }
        
        //получаем ссылки на картинки
        //редактировать группу
        $imgedit = '<img src="'.$this->dof->url_im('agroups', '/icons/edit.png').'"
            alt="'.$this->dof->get_string('edit', 'agroups').'" title="'.$this->dof->get_string('edit', 'agroups').'">';
        //просмотреть ее параметры
        $imgview = '<img src="'.$this->dof->url_im('agroups', '/icons/view.png').'" 
            alt="'.$this->dof->get_string('view', 'agroups').'" title="'.$this->dof->get_string('view', 'agroups').'">';
        //просмотреть список членов
        $imgmembers = '<img src="'.$this->dof->url_im('agroups', '/icons/group.gif').'" 
            alt="'.$this->dof->get_string('members', 'agroups').'" title="'.$this->dof->get_string('members', 'agroups').'">';
        //просмотреть список активных предмето-потоков
        $imgactivecstreams = '<img src="'.$this->dof->url_im('agroups', '/icons/active_cstreams.png').'" 
            alt="'.$this->dof->get_string('view_active_cstreams', 'agroups').'" title="'.$this->dof->get_string('view_active_cstreams', 'agroups').'">';
        //просмотреть список всех предмето потоков
        $imgallcstreams = '<img src="'.$this->dof->url_im('agroups', '/icons/all_cstreams.png').'" 
            alt="'.$this->dof->get_string('view_all_cstreams', 'agroups').'" title="'.$this->dof->get_string('view_all_cstreams', 'agroups').'">';
        // синхронизировать группу с учебными потоками
        $imgsync = '<img src="'.$this->dof->url_im('agroups', '/icons/sync.png').'" 
            alt="'.$this->dof->get_string('sync_agroup', 'agroups').'" title="'.
                   $this->dof->get_string('sync_agroup', 'agroups').'">';
        // Массовая рассылка сообщений
        $imggroupmessage = '<img src="'.$this->dof->url_im('agroups', '/icons/sync.png').'"
            alt="'.$this->dof->get_string('group_message', 'agroups').'" title="'.$this->dof->get_string('group_message', 'agroups').'">';
        // добавляем ссылку
        $link = '';
        if ( $this->dof->storage('agroups')->is_access('edit', $obj->id) )
        {//покажем ссылку на страницу редактирования
            $link .= '<a id="edit_agroup_'.$obj->id.'" href='.$this->dof->url_im('agroups','/edit.php?agroupid='.
            $obj->id,$conds).'>'.$imgedit.'</a>&nbsp;';
        }
        if ( $this->dof->storage('agroups')->is_access('view', $obj->id) )
        {//покажем ссылку на страницу просмотра
            $link .= '<a id="view_agroup_'.$obj->id.'" href='.$this->dof->url_im('agroups','/view.php?agroupid='.
            $obj->id,$conds).'>'.$imgview.'</a>&nbsp;';
        }
        if ( $this->dof->storage('programmsbcs')->is_access('view') )
        {//покажем ссылку на страницу просмотра 
            $link .= '<a id="list_agroup_persons_'.$obj->id.'" href='.$this->dof->url_im('programmsbcs','/list_persons.php?agroupid='.
            $obj->id.'&programmid='.$obj->programmid,$outconds).'>'.$imgmembers.'</a>&nbsp;';
        }
        if ( $this->dof->storage('cstreams')->is_access('view') )
        {//покажем ссылку на страницу просмотра активных потоков
            $link .= '<a id="list_agroup_cstreams_'.$obj->id.'" href='.$this->dof->url_im('cstreams','/list.php?agroupid='.
            $obj->id.'&status=active',$outconds).'>'.$imgactivecstreams.'</a>&nbsp;';
        }
        if ( $this->dof->storage('cstreams')->is_access('view') )
        {//покажем ссылку на страницу просмотра всех предмето-потоков группы
            $link .= '<a id="list_agroup_agenums_'.$obj->id.'" href='.$this->dof->url_im('cstreams','/list_agenum.php?agroupid='.
            $obj->id,$outconds).'>'.$imgallcstreams.'</a>&nbsp;';
        }
        if ( $this->dof->storage('cstreams')->is_access('create') )
        {//покажем ссылку на страницу массового создания потоков
            if ( $ages = $this->dof->storage('ages')->get_records(array()) )
            {// если есть периоды на которые еще не создано потоков
                $imgallcstreams = '<img src="'.$this->dof->url_im('agroups', '/icons/create_cstreams.png').'" 
                        alt="'.$this->dof->get_string('create_cstream_for_group', 'agroups').'" title="'.
                        $this->dof->get_string('create_cstream_for_group', 'agroups').'">';
                //добавляем ссылку
                $link .= '<a id="create_cstreams_for_agroup_'.$obj->id.'" href='.$this->dof->url_im('cstreams','/create_cstreams_forgroup.php?agroupid='.
                $obj->id,$outconds).'>'.$imgallcstreams.'</a>&nbsp;';
            }
        }
        if ( $this->dof->storage('agroups')->is_access('edit', $obj->id) )
        {//покажем ссылку на синхронизацию группы
            // @todo проставить более продуманные права доступа, либо завести собственную категорию
            // прав для синхронизации
            $link .= '<a id="sync_agroup_'.$obj->id.'" href='.$this->dof->url_im('agroups','/view.php?agroupsyncid='.
            $obj->id,$outconds).'>'.$imgsync.'</a>';
        }
        if ( $this->dof->storage('agroups')->is_access('view') )
        {//покажем ссылку на страницу просмотра истории обучения группы
            $img = '<img src="'.$this->dof->url_im('agroups', '/icons/history.png').'" 
            alt="'.$this->dof->get_string('history_group', 'agroups').'" title="'.
                   $this->dof->get_string('history_group', 'agroups').'">';
            $link .= ' <a id="view_agroup_learninghistory_'.$obj->id.'" href='.$this->dof->url_im('agroups','/history.php?agroupid='.
            $obj->id,$outconds).'>'.$img.'</a>&nbsp;';
        }
        if ( $this->dof->storage('learningplan')->is_access('edit') )   
        {
            $img = '<img src="'.$this->dof->url_im('agroups', '/icons/editplan.png').'" 
            alt="'.$this->dof->get_string('edit_learningplan', 'agroups').'" title="'.
                   $this->dof->get_string('edit_learningplan', 'agroups').'">';
            $link .= ' <a href='.$this->dof->url_im('learningplan','/index.php?type=agroup&agroupid='.
            $obj->id,$conds).'>'.$img.'</a>&nbsp;';
        }
        if ( $this->dof->storage('agroups')->is_access('view') )
        {
            $link .= '<a id="group_message_'.$obj->id.'" href='.$this->dof->url_im('agroups','/message.php?agroupid='.
            $obj->id,$outconds).'>'.$imggroupmessage.'</a>';
        }
        if ( $this->dof->storage('agroups')->is_access('view_itoggrades') )
        {
            $itoggradesvars = $outconds;
            $itoggradesvars['agroupid'] = $obj->id;
            $link .= $this->dof->modlib('ig')->icon_plugin('journal', 'im', 'agroups', 
                $this->dof->url_im('agroups', '/itoggrades/', $itoggradesvars), 
                [
                    'title' => $this->dof->get_string('agroup_itog_grades', 'agroups')
                ]
            );
        }
        if ( $this->is_access('vview:rtreport/rating_agroup') )
        {
            $variables = ['agroupid' => $obj->id, 'type' => 'rating_agroup', 'departmentid' => $conds['departmentid'], 'pt' => $this->type(), 'pc' => $this->code()];
            $link .= '&nbsp' . $this->dof->modlib('ig')->icon(
                    'viewrating',
                    $this->dof->url_im('rtreport', '/index.php', $variables),
                    [
                        'title' => $this->dof->get_string('agroup_view_rating', 'agroups'),
                        'style' => 'width:16px'
                    ]
                    );
        }
        return array($link,$obj->name,$obj->code,$progname,$department,$agenum,
            $metacontractnum,$obj->salfactor);
    }

    /** Возвращает список учебных групп по заданным критериям 
     * 
     * @return array массив записей из базы, или false в случае ошибки
     * @param int $limitfrom - начиная с какой записи просматривается фрагмент списка записей
     * @param int $limitnum - сколько записей нужно извлечь из базы
     * @param object $conds - список параметров для выборки периодов 
     */
    public function get_listing($limitfrom, $limitnum, $conds=null)
    {
        dof_debugging('im/agroups get_listing.Метод перенесен в storage', DEBUG_DEVELOPER);
        if ( ! $conds )
        {// если список периодов не передан - то создадим объект, чтобы не было ошибок
            $conds = new stdClass();
        }
        if ( $limitnum <= 0 )
        {// количество записей на странице может быть 
            //только положительным числом
            $limitnum = $this->dof->modlib('widgets')->get_limitnum_bydefault(); 
        }
        if ( $limitfrom < 0 )
        {//отрицательные значения номера просматриваемой записи недопустимы
            $limitfrom = 0;
        }
        $countselect = $this->get_select_listing($conds);
        // посчитаем общее количество записей, которые нужно извлечь
        $recordscount = $this->dof->storage('agroups')->get_numberof_agroups($countselect);
        if ( $recordscount < $limitfrom )
        {// если количество записей в базе меньше, 
            //чем порядковый номер записи, которую надо показать  
            //покажем последнюю страницу
            $limitfrom = $recordscount;
        }
        //формируем строку запроса
        $select = $this->get_select_listing($conds);
        //определяем порядок сортировки
        $sort = 'name ASC, departmentid ASC, programmid ASC, status ASC';
        // возвращаем ту часть массива записей таблицы, которую нужно
        return $this->dof->storage('agroups')->get_list_select($select, $sort, '*', $limitfrom, $limitnum);
    }
    
    /**
     * Возвращает фрагмент sql-запроса после слова WHERE
     * @param object $inputconds - список полей с условиями запроса в формате "поле_БД->значение" 
     * @return string
     */
    public function get_select_listing($inputconds)
    {
        dof_debugging('im/agroups get_select_listing.Метод перенесен в storage', DEBUG_DEVELOPER);
        // создадим массив для фрагментов sql-запроса
        $selects = array();
        $conds = fullclone($inputconds);
        if ( isset($conds->nameorcode) AND strlen(trim($conds->nameorcode)) )
        {// для имени используем шаблон LIKE
            $selects[] = "( name LIKE '%".$conds->nameorcode."%' OR code='".$conds->nameorcode."')";
            // убираем имя из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->nameorcode);
        }
        // теперь создадим все остальные условия
        foreach ( $conds as $name=>$field )
        {
            if ( $field )
            {// если условие не пустое, то для каждого поля получим фрагмент запроса
                $selects[] = $this->dof->storage('agroups')->query_part_select($name,$field);
            }
        }
        //формируем запрос
        if ( empty($selects) )
        {// если условий нет - то вернем пустую строку
            return '';
        }elseif ( count($selects) == 1 )
        {// если в запросе только одно поле - вернем его
            return current($selects);
        }else
        {// у нас несколько полей - составим запрос с ними, включив их всех
            return implode($selects, ' AND ');
        }
    }
    
    /** Получить список учеников, которых можно добавить в группу
     * @return array - массив записей из таблицы persons и programmsbcs, пригодный для подстановки в select
     *                 в формате 'programmsbcid' => 'ФИО'
     * 
     * @param int $agroupid - id академической группы в таблице agroups
     */
    public function get_students_to_add($agroupid)
    {
        if ( ! $agroup = $this->dof->storage('agroups')->get($agroupid) )
        {// указанной группы нет в базе
            return array();
        }
        
        // группа есть, получаем список учеников, которые потенциально могут быть в нее записаны
        // У них должны совпадать с группой параллель и программа
        // получаем подписки учеников, входящих в эту группу
        $conditions = array();
        $conditions['programmid'] = $agroup->programmid;
        $conditions['agenum']     = $agroup->agenum;
        $conditions['status']     = array('application', 'plan', 'active', 'condactive', 'suspend');
        
        // получаем список подписок, отсортированный по группам
        $programmsbcs = $this->dof->storage('programmsbcs')->
                            get_listing($conditions, null,null, 'agroup');
        // оставим в списке только те объекты, на использование которых есть право
        $permissions  = array(array('plugintype'=>'storage', 'plugincode'=>'programmsbcs', 'code'=>'use'));
        $programmsbcs = $this->dof->storage('acl')->get_acl_filtered_list($programmsbcs, $permissions);
        
        if ( ! $programmsbcs )
        {
            return array();
        }
        
        // получаем учеников, которые уже записаны в эту группу
        $groupstudents    = $this->get_students_to_remove($agroupid);
        // убираем учеников которые уже записаны в группу из общего списка учеников,
        // и остаются только те, кто может быть записан в группу, но еще не записан
        $programmstudents = array_diff_key($programmsbcs, $groupstudents);
        if ( empty($programmstudents) )
        {
            return array();
        }
        // разбиваем список учеников по группам, чтобы вывести их в таком виде в select
        $oldagroupid = '';
        $groups = array();
        foreach ( $programmstudents as $studentobj )
        {
            $studentobj->agroupid = intval($studentobj->agroupid);
            if ( $studentobj->agroupid == $oldagroupid AND ! empty($groups) )
            {// просматриваем учеников предыдущей группы
                $oldagroup = $groups[$oldagroupid];
                $oldagroup->options[$studentobj->id] = $this->dof->storage('persons')->get_fullname($studentobj->studentid);
            }else
            {// начали просматривать учеников новой группы
                $agroup = new stdClass();
                $agroup->name = $this->dof->storage('agroups')->get_field($studentobj->agroupid, 'name');
                
                if ( ! $agroup->name )
                {// если название группы не найдено - значит ученик обучался без группы
                    $agroup->name = $this->dof->get_string('no_group', $this->code());
                }
                // создаем список учеников для группы
                $agroup->options = array();
                $agroup->options[$studentobj->id] = $this->dof->storage('persons')->get_fullname($studentobj->studentid);
                // добавляем новую группу в список
                $groups[$studentobj->agroupid] = $agroup;
                // сортируем учеников старой группы
                if ( isset($groups[$oldagroupid]) )
                {
                    $oldagroup = $groups[$oldagroupid];
                    asort($oldagroup->options); 
                    $groups[$oldagroupid] = $oldagroup;
                }
                // запоминаем новый id группы
                $oldagroupid = $studentobj->agroupid;
            }
        }
        
        return array_values($groups);
    }
    
    /** Получить список учеников, которые уже находятся в группе
     * @return array - массив записей из таблиц persons и programmsbcs, пригодный для подстановки в select
     *                 в формате 'programmsbcid' => 'ФИО'
     * 
     * @param int $agroupid - id академической группы в таблице agroups
     */
    public function get_students_to_remove($agroupid)
    {
        if ( ! $agroupid )
        {
            return array();
        }
        // получаем подписки учеников, входящих в эту группу
        $conditions = array();
        $conditions['agroupid'] = $agroupid;
        $conditions['status']   = array('application', 'plan', 'active', 'condactive', 'suspend');
        if ( ! $programmsbcs = $this->dof->storage('programmsbcs')->
                            get_listing($conditions, null,null, "sortname") )
        {// в группе нет учеников
            return array();
        }
        // делаем из массива объектов из базы - массив для select-элемента
        return $this->transform_programmsbcs_into_options($programmsbcs);
    }
    
    /** 
     * Получить список учеников в группе
     * @param int $agroupid - id академической группы в таблице agroups
     * 
     * @return array
     */
    public function get_students_sbc($agroupid)
    {
        if ( ! $agroupid )
        {
            return array();
        }
        // получаем подписки учеников, входящих в эту группу
        $conditions = array();
        $conditions['agroupid'] = $agroupid;
        $conditions['status']   = array('application', 'plan', 'active', 'condactive', 'suspend');
        if ( ! $programmsbcs = $this->dof->storage('programmsbcs')->
                get_listing($conditions, null,null, "sortname") )
        {// в группе нет учеников
            return array();
        }
        // делаем из массива объектов из базы - массив для select-элемента
        return $programmsbcs;
    }
    
    /** Преобразовать массив объектов из  функции get_listing в вид, пригодный для вставки
     * в двусторонний select-список. Используется функциями get_students_to_remove и get_students_to_add
     * 
     * @param array $programmsbcs - массив объектов из таблицы programmsbcs, с присоединенной таблицей persons
     * @return array
     */
    protected function transform_programmsbcs_into_options($programmsbcs)
    {
        $result = array();
        if ( empty($programmsbcs) )
        {
            return $result;
        }
        // все полученные подписки на программы приводим к пригодному для отображения select-ом виду
        foreach ( $programmsbcs as $id=>$programmsbc )
        {
            $statusname = $this->dof->workflow('programmsbcs')->get_name($programmsbc->status);
            switch ( $programmsbc->status )
            {// для учеников с пиостановленной подпиской или условно переведенных - укажем 
                // рядом с ФИО в скобках статус
                case 'condactive':
                    $result[$id] = $this->dof->storage('persons')->get_fullname($programmsbc->studentid).' ('.$statusname.')';
                break;
                case 'suspend':
                    $result[$id] = $this->dof->storage('persons')->get_fullname($programmsbc->studentid).' ('.$statusname.')';
                break;
                // для всех остальных отобразим только ФИО
                default: $result[$id] = $this->dof->storage('persons')->get_fullname($programmsbc->studentid);
            }
        }
        
        return $result;
    }
    
    /** Обработать подписку или отписку учеников группы
     * @param string $action - действие, которое нужно произвести с учениками: 
     *                         add (добавить в группу)
     *                         remove (исключить из группы)
     * @param array  $students - массив, пришедший из POST
     * @param int    $agroupid - id группы в которую будут записываться/отписыватьяс ученики
     * 
     * @return bool
     */
    public function process_addremove_students($action, $students, $agroupid)
    {
        $result = true;
        $sbcids = array();
        if ( ! is_array($students) OR ! $this->dof->storage('agroups')->is_exists($agroupid) )
        {// неправильный формат данных
            return false;
        }
        if ( empty($students) )
        {// список учеников пуст - ничего делать не надо
            return true;
        }
        
        // проверяем список учеников и извлекаем id подписок
        foreach ( $students as $sbcid )
        {
            if ( is_numeric($sbcid) )
            {// пропускаем из POST только числовые ключи массива
                $sbcids[] = (int)$sbcid;
            }
        }
        // производим подписку/отписку учеников с проверенными id
        foreach ( $sbcids as $sbcid )
        {
            $programmsbc = new stdClass();
            $programmsbc->id = $sbcid;
            // если ученик раньше учился в другой группе - узнаем ее id
            $oldagroupid = intval($this->dof->storage('programmsbcs')->get_field($sbcid, 'agroupid'));
            if ( $action == 'add' )
            {// ученик добавляется в группу
                $programmsbc->agroupid = $agroupid;
                if ( ( ! $this->is_access('addstudents', $agroupid) AND
                     ( ! $oldagroupid OR $this->is_access('removestudents', $oldagroupid) ) )  )
                {// нет права удалять ученика из старой или добавлять в новую группу
                    // @todo убрать использование права manage после окончательного
                    // перехода на новую систему полномочий
                    $result = false;
                    continue;
                } 
            }elseif ( $action == 'remove' )
            {// ученик удаляется из группы
                $programmsbc->agroupid = null;
                if ( ! $this->is_access('removestudents', $agroupid) )
                {// нет права удалять учеников из этой группы
                    // @todo убрать использование права manage после окончательного
                    // перехода на новую систему полномочий
                    $result = false;
                    continue;
                }
            }
            
            // обновляем подписку ученика, все нужные действия произойдут автоматически, через события
            $result = $result && (bool)$this->dof->storage('programmsbcs')->update($programmsbc);
        }
        
        return $result;
    }
    
    /** Получить сообщение о результате подписки/отписки учеников в группу, размеченное html-тегами
     * 
     * @param string $action - add - ученики были добавлены в группу
     *                         remove ученики быле удалены из группы
     * @param bool $result - результат выполненной операции
     */
    public function get_addremove_students_result_message($action, $result)
    {
        // определяем, какими цветами будем раскрашивать успешное и неуспешное сообщение
        $successcss = 'color:green;';
        $failurecss = 'color:red;';
        $basecss    = 'text-align:center;font-weight:bold;margin-left:auto;margin-right:auto;';
        if ( $action == 'add' )
        {// ученики добавлялись в группу
            if ( $result )
            {// учеников удалось записать в группу
                $css      = $successcss;
                $stringid = 'add_students_to_group_success';
            }else
            {// учеников не удалось записать в группу
                $css      = $failurecss;
                $stringid = 'add_students_to_group_failure';
            }
        }elseif ( $action == 'remove' )
        {// ученики удалялись из группы
            if ( $result )
            {// учеников удалось удалить из группы
                $css      = $successcss;
                $stringid = 'remove_students_from_group_success';
            }else
            {// учеников не удалось удалить из группы
                $css      = $failurecss;
                $stringid = 'remove_students_from_group_failure';
            }
        }
        // получаем текст сообщения
        $text = $this->dof->get_string($stringid, $this->code());
        // оформляем сообшение css-стилями
        return '<p style="'.$basecss.$css.'">'.$text.'</p>';
    }
    
    /**
     * Возвращает объект приказа
     *
     * @param string $code - код приказа
     * @param integer  $id - id приказа в таблице orders
     * @return dof_storage_orders_baseorder|dof_im_agroups_order_change_status
     */
    public function order($code, $id = NULL)
    {
        require_once($this->dof->plugin_path('im','agroups','/orders/change_status/init.php'));
        switch ($code)
        {
            case 'change_status':
                $order = new dof_im_agroups_order_change_status($this->dof);
                if ( ! is_null($id))
                {// нам передали id, загрузим приказ
                    if ( ! $order->load($id))
                    {// Не найден
                        return false;
                    }
                }
                // Возвращаем объект
                return $order;
            break;
        }
    }

    /** Возвращает html-код таблицы для истории группы
     * @param int agroupid - id группы 
     * @return string - html-код или пустая строка
     */
    public function print_table_history($agroupid)
    {
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        //$table->size = array ('100px','150px','150px','200px','150px','100px');
        $table->align = array ("center","center");
        // шапка таблицы
        $table->head =  array($this->dof->get_string('age', 'agroups'),
                              $this->dof->get_string('agenum', 'agroups'));
        // заносим данные в таблицу   
        if ( ! $history = $this->dof->storage('agrouphistory')->get_records(array('agroupid'=>$agroupid)) )
        {
            return '<div align=\'center\'>'.$this->dof->get_string('no_history_group', 'agroups').'</div>';
        }else
        {
            $data = array();
            foreach ( $history as $ahistory )
            {
                $agename = $this->dof->storage('ages')->get_field($ahistory->ageid, 'name');
                $data[] = array($agename, $ahistory->agenum );
            }
        }
        $table->data = $data;
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    
    /** Получить html-ссылку на просмотр группы
     * @param int id - id группы в таблице agroups
     * @param bool $withcode - добавлять или не добавлять код в конце
     * 
     * @return string html-строка со ссылкой на группу или пустая строка в случае ошибки
     */
    public function get_html_link($id, $withcode=false, $addvars=null)
    {
        if ( ! $addvars )
        {
            $addvars = array();
        }
        if ( ! $name = $this->dof->storage('agroups')->get_field($id, 'name') )
        {
            return '';
        }
        if ( $withcode )
        {
            $code = $this->dof->storage('agroups')->get_field($id, 'code');
            $name = $name.' ['.$code.']';
        }
        return '<a href="'.$this->dof->url_im($this->code(), 
                    '/view.php', array('agroupid' => $id)+$addvars).'">'.$name.'</a>';
    }

    /**
     * Сбор данных для ведомости итоговых оценок по группе
     *
     * @param $agroupid -
     *            идентификатор академической группы
     * @return boolean|stdClass - объект с данными для отчета или false в случае ошибки
     */
    public function get_itog_grades_data($agroupid)
    {
        // получим академическую группу
        $agroup = $this->dof->storage('agroups')->get($agroupid);
        if (empty($agroup))
        {
            return false;
        }
        
        // Получим подписки на программы, входящие в состав группы
        $programmsbcs = $this->get_students_sbc($agroup->id);
        // массив подписок на дисциплины
        $cpasseds = [];
        foreach ($programmsbcs as $programmsbc)
        { // для всех выбранных подписок на программы
            $pbcscpasseds = $this->dof->storage('cpassed')->get_records(
                [
                    'programmsbcid' => $programmsbc->id
                ]);
            if (! empty($pbcscpasseds))
            {
                $cpasseds += $pbcscpasseds;
            }
        }
        
        if (empty($cpasseds))
        {
            return false;
        }
        
        $result = [];
        
        // массив для сбора, группировки, сортировки данных
        $grouppeddata = [];
        
        foreach ($cpasseds as $cpassed)
        {
            // получим дисциплину
            $programmitem = $this->dof->storage('programmitems')->get(
                $cpassed->programmitemid);
            if(empty($programmitem))
            {
                continue;
            }
            
            if ( $cpassed->status == 'reoffset' )
            {
                $age = new stdClass();
                $age->id = 'reoffset';
                
                $cstream = new stdClass();
                $cstream->id = 'reoffset_'.$programmitem->id;
                $cstream->teacherid = '&nbsp;';
                $cstream->hours = $programmitem->hours;
                
                $programmitem->agenum = 'reoffset';
            } else
            {
            
                // получим период
                $age = $this->dof->storage('ages')->get($cpassed->ageid);
                if(empty($age))
                {
                    continue;
                }
    
                $cstream = $this->dof->storage('cstreams')->get($cpassed->cstreamid);
                if(empty($cstream))
                {
                    continue;
                }
            }
            
            // если оценка еще не получена, вместо даты выведем значение по умолчанию
            $gradetime = "?";
            if (! empty($cpassed->orderid))
            { // имеется приказ о выставленной оценке
              // получим приказ
                $order = $this->dof->storage('orders')->get($cpassed->orderid);
                // заопмним дату оценки
                $gradetime = $order->exdate;
            }
            
            // составной ключ для группировки по параллели/периоду
            $agenumagepair = $programmitem->agenum . "_" . $age->id;
            if (empty($grouppeddata[$agenumagepair]))
            {
                $grouppeddata[$agenumagepair] = [
                    'age' => $age,
                    'agenum' => $programmitem->agenum,
                    'items' => []
                ];
            }
            
            // составной ключ для учебного процесса/ведомости оценок(приказ)
            $cstreamorderpair = $cstream->id . "_" . $cpassed->orderid;
            
            if (empty(
                $grouppeddata[$agenumagepair]['items'][$programmitem->controltypeid][$cstreamorderpair]))
            { // учебного процесса еще нет в группированных данных
                $grouppeddata[$agenumagepair]['items'][$programmitem->controltypeid][$cstreamorderpair] = [
                    'cstream' => $cstream,
                    'programmitem' => $programmitem,
                    'gradetime' => $gradetime,
                    'cpasseds' => []
                ];
            }
            // добавляем в нужную ячейку массива подписки на дисциплины
            $grouppeddata[$agenumagepair]['items'][$programmitem->controltypeid][$cstreamorderpair]['cpasseds'][$cpassed->programmsbcid] = $cpassed;
        }
        
        $teachernames = [];
        $contracts = [];
        $studentnames = [];
        
        // сортируем данные по параллели/периоду
        ksort($grouppeddata);
        foreach ($grouppeddata as $agenumagepair => $agenumagedata)
        {
            if (empty($result[$agenumagepair]))
            {
                $result[$agenumagepair] = [
                    'agenum' => $agenumagedata['agenum'],
                    'age' => $agenumagedata['age'],
                    'grades' => [],
                    'items' => []
                ];
            }
            $grade = null;
            
            // сортируем данные по типу итогового контроля
            ksort($agenumagedata['items']);
            foreach ($agenumagedata['items'] as $controltypeid => $cstreamsdata)
            {
                // сортируем данные по учебным процессам/ведомостям оценок(приказам)
                ksort($cstreamsdata);
                foreach ($cstreamsdata as $cstreamdata)
                {
                    // данные для учебных процессов
                    $item = new stdClass();
                    $item->controltypeid = $controltypeid;
                    $item->controltypespan = count($cstreamsdata);
                    $item->cstream = $cstreamdata['cstream'];
                    $item->programmitem = $cstreamdata['programmitem'];
                    $item->gradetime = $cstreamdata['gradetime'];
                    
                    if (empty($teachernames[$cstreamdata['cstream']->teacherid]))
                    { // ФИО препода нет в кэшируемых полях - добавим
                        $teachername = $this->dof->storage('persons')->get_fullname(
                            $cstreamdata['cstream']->teacherid);
                        if ( empty($teachername) )
                        {
                            $teachername = '-';
                        }
                        $teachernames[$cstreamdata['cstream']->teacherid] = $teachername;
                    }
                    $item->teachername = $teachernames[$cstreamdata['cstream']->teacherid];
                    
                    foreach ($programmsbcs as $programmsbc)
                    { // подписки на программу группы в определенном порядке
                        
                        if (empty($contracts[$programmsbc->contractid]))
                        { // Учебного договора нет в кэшируемых полях - добавим
                            $contract = $this->dof->storage('contracts')->get(
                                $programmsbc->contractid);
                            if (! empty($contract))
                            {
                                $contracts[$programmsbc->contractid] = $contract;
                            } else
                            {
                                $contract = "[" . $programmsbc->contractid . "]";
                            }
                        }
                        // получим договор студента
                        $studentcontract = $this->dof->storage('contracts')->get(
                            $programmsbc->contractid);
                        
                        if (empty($studentnames[$studentcontract->studentid]))
                        { // Учебного договора нет в кэшируемых полях - добавим
                            $student = $this->dof->storage('persons')->get_fullname(
                                $studentcontract->studentid);
                            if (! empty($student))
                            {
                                $studentnames[$studentcontract->studentid] = $student;
                            } else
                            {
                                $studentnames[$studentcontract->studentid] = "[" .
                                     $studentcontract->studentid . "]";
                            }
                        }
                        // получим ФИО студента
                        $studentname = $studentnames[$studentcontract->studentid];
                        
                        if (! empty($cstreamdata['cpasseds'][$programmsbc->id]))
                        { // имеется подписка на дисциплину
                            $cpassed = $cstreamdata['cpasseds'][$programmsbc->id];
                            $grade = $cpassed->grade;
                            if (empty($cpassed->grade))
                            { // ? оценки нет, но ожидается, что еще появится
                                $grade = $this->dof->get_string(
                                    'agroup_itog_grades_waiting_grade', 
                                    'agroups');
                                $cpassed->grade = $this->dof->get_string(
                                    'agroup_itog_grades_waiting_grade', 
                                    'agroups');
                            }
                            
                            if ($this->dof->storage('cpassed')->get_record(
                                [
                                    'repeatid' => $cpassed->id
                                ]))
                            { // () оценка есть, но она уже была изменена (через ведомость пересдачи)
                                $grade = null;
                                $cpassed->grade = $this->dof->get_string(
                                    'agroup_itog_grades_regraded_grade', 
                                    'agroups', $cpassed->grade);
                            }
                            
                            if (empty($cpassed->agroupid))
                            { // * индивидуальное обучение, без группы
                                $grade = $cpassed->grade;
                                $cpassed->grade = $this->dof->get_string(
                                    'agroup_itog_grades_personal_grade', 
                                    'agroups', $cpassed->grade);
                            }
                        } else
                        { // обучение не запланировано - поставим прочерк в оценке
                            $cpassed = new stdClass();
                            $cpassed->id = '';
                            $cpassed->grade = $this->dof->get_string(
                                'agroup_itog_grades_no_grade', 'agroups');
                            $grade = null;
                        }
                        $item->itemdata[] = [
                            'cpassed' => $cpassed,
                            'psbc' => $programmsbc,
                            'studentname' => $studentname
                        ];

                        if (empty($result[$agenumagepair]['grades'][$grade]) &&
                             $grade !== null)
                        {
                            $result[$agenumagepair]['grades'][$grade] = 0;
                        }
                        if ($grade !== null)
                        {
                            $result[$agenumagepair]['grades'][$grade] ++;
                        }
                    }
                    $result[$agenumagepair]['items'][] = $item;
                }
                ksort($result[$agenumagepair]['grades']);
            }
        }
        return $result;
    }

    /**
     *
     * @param $data -
     *            объект с данными для отчета, сформированный методом get_itog_grades_data
     * @param $options -
     *            дополнительные настройки в формате
     *            ['format'] - формат отчета, по умолчанию html
     * @return string - сформированный отчет
     */
    public function render_itog_grades($data, $options = [])
    {
        if (empty($data))
        {
            $this->dof->messages->add($this->dof->get_string('agroup_itog_grades_nodata','agroups'),'message');
            return '';
        }
        
        if (empty($options['format']))
        { // формат отчета не передан, по умолчанию возвращаем html
            $options['format'] = 'html';
        }
        
        // результат отчета
        $result = '';
        
        switch ($options['format'])
        {
            case 'html':
                $aftertables = '';
                
                $this->dof->modlib('nvg')->add_css('im', 'agroups', 
                    '/itoggrades/style.css');
                
                // отчет требуется в формате html
                foreach ($data as $agenumagepair => $items)
                {
                    // начинаем формировать таблицу
                    $table = new html_table();
                    // массив для сбора табличных данных
                    $tablecells = [];
                    // порядковый номер столбцов с дисциплинами
                    $itemnum = 0;
                    
                    // Заголовок. Отчетность
                    $controltypeslabelcell = new html_table_cell(
                        $this->dof->get_string('agroup_itog_grades_report', 
                            'agroups'));
                    $controltypeslabelcell->colspan = 2;
                    $tablecells[0][$itemnum] = $controltypeslabelcell;
                    
                    // Заголовок. Название дисциплины
                    $pitemnamelabelcell = new html_table_cell(
                        $this->dof->get_string('agroup_itog_grades_pitemname', 
                            'agroups'));
                    $pitemnamelabelcell->colspan = 2;
                    $tablecells[1][$itemnum] = $pitemnamelabelcell;
                    
                    // Заголовок. Количество часов
                    $hourslabelcell = new html_table_cell(
                        $this->dof->get_string('agroup_itog_grades_hours', 
                            'agroups'));
                    $hourslabelcell->colspan = 2;
                    $tablecells[2][$itemnum] = $hourslabelcell;
                    
                    // Заголовок. Дата
                    $datelabelcell = new html_table_cell(
                        $this->dof->get_string('agroup_itog_grades_date', 
                            'agroups'));
                    $datelabelcell->colspan = 2;
                    $tablecells[3][$itemnum] = $datelabelcell;
                    
                    // Заголовок. Преподаватель
                    $teachernamelabelcell = new html_table_cell(
                        $this->dof->get_string('agroup_itog_grades_teacher', 
                            'agroups'));
                    $teachernamelabelcell->colspan = 2;
                    $tablecells[4][$itemnum] = $teachernamelabelcell;
                    
                    $itemnum ++;
                    
                    // последний отрисованный тип итогового контроля
                    $lastcontroltypeid = null;
                    
                    foreach ($items['items'] as $item)
                    {
                        // начинается новый столбец с дисциплиной
                        $itemnum ++;
                        
                        if ($lastcontroltypeid != $item->controltypeid)
                        { // сменился тип итогового контроля, выведем новую ячейку с нужным colspan
                            $lastcontroltypeid = $item->controltypeid;
                            $controltype = $this->dof->modlib('refbook')->get_st_total_control_name(
                                $item->controltypeid);
                            $controltypecell = new html_table_cell($controltype);
                            $controltypecell->colspan = $item->controltypespan;
                            if (! isset($controltypecell->attributes['class']))
                            {
                                $controltypecell->attributes['class'] = "";
                            }
                            $controltypecell->attributes['class'] .= " dof_agroups_itoggrades_tdgroupped ";
                            $tablecells[0][$itemnum] = $controltypecell;
                        }
                        // название дисциплины
                        $tablecells[1][$itemnum] = new html_table_cell(
                            $item->programmitem->name);
                        // количество часов
                        $tablecells[2][$itemnum] = new html_table_cell(
                            $item->cstream->hours);
                        // дата закрытия ведомости
                        $gradedate = $gradedatetime = $item->gradetime;
                        if ((int) $item->gradetime > 0)
                        {
                            // дата в требуемом отчетом формате ДД.ММ.ГГГГ
                            $gradedate = date("d.m.Y", $item->gradetime);
                            // полная дата для отображения в подсказке (ведомости от одной даты в разное время могут быть)
                            $gradedatetime = userdate($item->gradetime);
                        }
                        $datecell = new html_table_cell($gradedate);
                        $datecell->attributes['title'] = $gradedatetime;
                        $tablecells[3][$itemnum] = $datecell;
                        
                        // ФИО преподавателя
                        $tablecells[4][$itemnum] = new html_table_cell(
                            $item->teachername);
                        // количество строк, занятое в заголовках таблицы
                        $headerrownums = 4;
                        // порядковый номер строки с подпиской студента группы
                        $psbcrowindex = 0;
                        foreach ($item->itemdata as $data)
                        {
                            // подписка на дисциплину
                            $cpassed = $data['cpassed'];
                            if (! empty($data['studentname']) && empty(
                                $tablecells[$headerrownums + $psbcrowindex + 1][0]))
                            { // есть данные по студенту, но еще нет его имени
                              
                                // порядковый номер студента в таблице
                                $studentindex = new html_table_cell(
                                    $psbcrowindex + 1);
                                if (! isset($studentindex->attributes['class']))
                                {
                                    $studentindex->attributes['class'] = "";
                                }
                                $studentindex->attributes['class'] .= " dof_agroups_itoggrades_studentindex ";
                                $tablecells[$headerrownums + $psbcrowindex + 1][0] = $studentindex;
                                
                                // ФИО студента
                                $studentnamecell = new html_table_cell(
                                    $data['studentname']);
                                if (! isset(
                                    $studentnamecell->attributes['class']))
                                {
                                    $studentnamecell->attributes['class'] = "";
                                }
                                $studentnamecell->attributes['class'] .= " dof_agroups_itoggrades_studentname ";
                                $tablecells[$headerrownums + $psbcrowindex + 1][1] = $studentnamecell;
                            }
                            // оценка студента
                            $tablecells[$headerrownums + $psbcrowindex ++ + 1][$itemnum] = new html_table_cell(
                                $cpassed->grade);
                        }
                    }
                    
                    // табличные данные сформированы
                    $table->data = $tablecells;
                    
                    // количество строк, занятых в основной части таблицы (без итогов)
                    $summarytable = new html_table();
                    $summarytablecells = [];
                    $summarytablecells[0][0] = new html_table_cell(
                        $this->dof->get_string(
                            'agroup_itog_grades_summarytable_grade', 'agroups'));
                    $summarytablecells[0][1] = new html_table_cell(
                        $this->dof->get_string(
                            'agroup_itog_grades_summarytable_counter', 'agroups'));
                    $counter = 1;
                    foreach ($items['grades'] as $grade => $gradecounter)
                    {
                        $summarytablecells[$counter][0] = new html_table_cell(
                            $grade);
                        $summarytablecells[$counter++][1] = new html_table_cell(
                            $gradecounter);
                    }
                    $summarytable->data = $summarytablecells;
                    
                    if( $items['agenum'] == 'reoffset' )
                    { // перезачеты отображать в самом конце
                        $agenumlabel = $this->dof->get_string('agroup_itog_grades_reoffset_pitems', 
                            'agroups');
                        $aftertables .= dof_html_writer::start_div(
                            'dof_agroups_itoggrades_table');
                        // добавляем в вывод заголовок для сформированной таблицы параллели/периода
                        $aftertables .= dof_html_writer::div(
                            $agenumlabel, 
                            'dof_agroups_itoggrades_agenumage_header');
                        // добавляем в вывод таблицу
                        $aftertables .= dof_html_writer::table($table, '');
                        // добавляем в вывод заголовок для сформированной таблицы c итогами
                        $aftertables .= dof_html_writer::div('Итого', 
                            'dof_agroups_itoggrades_summarytable_header');
                        // добавляем в вывод таблицу
                        $aftertables .= dof_html_writer::table($summarytable, 
                            '');
                        $aftertables .= dof_html_writer::end_div();
                    } else if( $items['agenum'] == 0 )
                    { // нулевой семестр (дисцилины доступные для всех параллелей) будем отображать в самом конце
                        $agenumlabel = $this->dof->get_string('optional_pitems', 
                            'programmitems');
                        $aftertables .= dof_html_writer::start_div(
                            'dof_agroups_itoggrades_table');
                        // добавляем в вывод заголовок для сформированной таблицы параллели/периода
                        $aftertables .= dof_html_writer::div(
                            $agenumlabel . ". " . $items['age']->name, 
                            'dof_agroups_itoggrades_agenumage_header');
                        // добавляем в вывод таблицу
                        $aftertables .= dof_html_writer::table($table, '');
                        // добавляем в вывод заголовок для сформированной таблицы c итогами
                        $aftertables .= dof_html_writer::div('Итого', 
                            'dof_agroups_itoggrades_summarytable_header');
                        // добавляем в вывод таблицу
                        $aftertables .= dof_html_writer::table($summarytable, 
                            '');
                        $aftertables .= dof_html_writer::end_div();
                    } else
                    {
                        $agenumlabel = $this->dof->get_string('parallel', 
                            'programmitems') . ' ' . $items['agenum'];
                        $result .= dof_html_writer::start_div(
                            'dof_agroups_itoggrades_table');
                        // добавляем в вывод заголовок для сформированной таблицы параллели/периода
                        $result .= dof_html_writer::div(
                            $agenumlabel . ". " . $items['age']->name, 
                            'dof_agroups_itoggrades_agenumage_header');
                        // добавляем в вывод таблицу
                        $result .= dof_html_writer::table($table, '');
                        // добавляем в вывод заголовок для сформированной таблицы c итогами
                        $result .= dof_html_writer::div(
                            $this->dof->get_string(
                                'agroup_itog_grades_summarytable_summary', 
                                'agroups'), 
                            'dof_agroups_itoggrades_summarytable_header');
                        // добавляем в вывод таблицу
                        $result .= dof_html_writer::table($summarytable, '');
                        $result .= dof_html_writer::end_div();
                    }
                }
                $result .= $aftertables;
                
                //печать таблицы с легендой
                $legendtable = new html_table();
                $legendtable->data = [
                    [
                        $this->dof->get_string('agroup_itog_grades_no_grade',
                            'agroups'),
                        $this->dof->get_string(
                            'agroup_itog_grades_no_grade_description', 'agroups')
                    ],
                    [
                        $this->dof->get_string(
                            'agroup_itog_grades_waiting_grade', 'agroups'),
                        $this->dof->get_string(
                            'agroup_itog_grades_waiting_grade_description',
                            'agroups')
                    ],
                    [
                        $this->dof->get_string(
                            'agroup_itog_grades_regraded_grade', 'agroups', ''),
                        $this->dof->get_string(
                            'agroup_itog_grades_regraded_grade_description',
                            'agroups')
                    ],
                    [
                        $this->dof->get_string(
                            'agroup_itog_grades_personal_grade', 'agroups', ''),
                        $this->dof->get_string(
                            'agroup_itog_grades_personal_grade_description',
                            'agroups')
                    ]
                ];
                $result .= dof_html_writer::start_div(
                    'dof_agroups_itoggrades_table');
                $result .= dof_html_writer::div(
                    $this->dof->get_string('agroup_itog_grades_legend',
                        'agroups'), 'dof_agroups_itoggrades_legend_header');
                    $result .= dof_html_writer::table($legendtable);
                    $result .= dof_html_writer::end_div();
                break;
        }
        
        return $result;
    }
    
    /**
     * Получение данных для вывода рейтинга по академической группе
     *
     * @param integer $cstream_id
     *
     * @return stdClass | bool
     */
    public function get_agroup_grades($agroup_id = null, $users = [])
    {
        if ( empty($agroup_id) )
        {
            return false;
        }
        
        // Получение объекта группы
        $agroup = $this->dof->storage('agroups')->get_record(['id' => $agroup_id]);
        if ( empty($agroup) )
        {
            return false;
        }
        
        $available_disciplines = $this->dof->storage('programmitems')->get_records(['programmid' => $agroup->programmid, 'agenum' => $agroup->agenum], '', 'id, name');
        if ( empty($available_disciplines) )
        {
            return false;
        }
        
        // Получение связей с учебными процессами
        $cstream_links = $this->dof->storage('cstreamlinks')->get_agroup_cstreamlink($agroup_id);
        if ( empty($cstream_links) )
        {
            return false;
        }
        
        // Объект результата
        $result_final = new stdClass();
        $result_final->agroup_id = $agroup_id;
        $result_final->agroup = $agroup;
        $result_final->users = [];
        $result_final->cstreams = [];
        $result_final->sum_grades = 0;
        
        // Получение подписок
        $list = $this->dof->storage('programmsbcs')->get_listing(['programmid' => $agroup->programmid, 'agenum' => $agroup->agenum, 'agroupid' => $agroup_id]);
        
        foreach ( $list as $sbc )
        {
            $result_final->users[$sbc->studentid] = new stdClass();
            $result_final->users[$sbc->studentid]->grades = [];
            $result_final->users[$sbc->studentid]->percents = [];
            $result_final->users[$sbc->studentid]->final_grade = 0;
            $result_final->users[$sbc->studentid]->final_grade_percent = 0;
            $result_final->users[$sbc->studentid]->user = $this->dof->storage('persons')->get_record(['id' => $sbc->studentid]);
        }
        
        foreach ( $cstream_links as $cstream_link )
        {
            $cstream = $this->dof->storage('cstreams')->get_record(['id' => $cstream_link->cstreamid]);
            if ( ! empty($cstream) && ! empty($available_disciplines[$cstream->programmitemid]) )
            {
                // Формирование информации по учебному процессу
                $cstream_info = new stdClass();
                $cstream_info->programmitem = $available_disciplines[$cstream->programmitemid];
                $cstream_info->grades = $this->dof->im('cstreams')->get_cstream_grades($cstream->id);
                
                // Имя учителя
                $teacher_name = $this->dof->get_string('rtreport_agroup_teacher_name_empty', 'rtreport');
                $teacher = $this->dof->storage('appointments')->get_person_by_appointment($cstream->appointmentid);
                if ( ! empty($teacher) )
                {
                    $teacher_name = $this->dof->storage('persons')->get_fullname($teacher->id);
                }
                $cstream->teacher_name = $teacher_name;
                $cstream_info->cstream = $cstream;
                
                // Добавление учебного процесса в отфильтрованный пулл
                $result_final->cstreams[$cstream->id] = $cstream_info;
                
                // Подсчет суммарного балла по всем учебным процессам
                $result_final->sum_grades = number_format($result_final->sum_grades + $cstream_info->grades->max_grade_average, 1);
                
                foreach ( $cstream_info->grades->users as $grade )
                {
                    if ( array_key_exists($grade->userid, $result_final->users) )
                    {
                        // Подсчет суммарного балла по всем учебным процессам для пользователей
                        $result_final->users[$grade->userid]->final_grade = number_format($result_final->users[$grade->userid]->final_grade + $grade->grade, 1);
                        
                        // Сбор пользователей и принадлежащие им оценки
                        $result_final->users[$grade->userid]->grades[$cstream->id] = $grade->grade;
                        
                        // Сбор пользователей и принадлежащие им проценты балла
                        $result_final->users[$grade->userid]->percents[$cstream->id] = $grade->percent;
                    }
                }
            }
        }
        
        // Посчитаем общий средний балл студента по в отношении всех учебных процессов
        if ( ! empty( $result_final->sum_grades) )
        {
            foreach ( $result_final->users as &$user )
            {
                if ( $result_final->sum_grades > 0 )
                {
                    $user->final_grade_percent = number_format($user->final_grade / $result_final->sum_grades * 100, 2);
                } else
                {
                    $user->final_grade_percent = 0;
                }
            }
        }
        
        return $result_final;
    }
}
?>