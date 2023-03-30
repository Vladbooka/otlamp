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

// подключение интерфейса настроек
require_once($DOF->plugin_path('storage','config','/config_default.php'));

class dof_im_crm implements dof_plugin_im, dof_storage_config_interface
{
    /**
     * @var dof_control
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    /**
     * Метод, реализующий инсталяцию плагина в систему
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
    /**
     * Метод, реализующий обновление плагина в системе
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
    /**
     * Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        return 2014120100;
    }
    /**
     * Возвращает версии интерфейса Деканата,
     * с которыми этот плагин может работать
     * @return string
     * @access public
     */
    public function compat_dof()
    {
        return 'aquarium';
    }

    /**
     * Возвращает версии стандарта плагина этого типа,
     * которым этот плагин соответствует
     * @return string
     * @access public
     */
    public function compat()
    {
        return 'angelfish';
    }

    /**
     * Возвращает тип плагина
     * @return string
     * @access public
     */
    public function type()
    {
        return 'im';
    }
    /**
     * Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'crm';
    }
    /**
     * Возвращает список плагинов,
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('tags'      => 2014120000,
                                      'tasks'     => 2014120000,
                                      'taglinks'  => 2014120000,
                                      'comments'  => 2014120000,
                                      'acl'       => 2011040504
                                      ),
                     'workflow'=>array('tags'     => 2014120000,
                                       'tasks'    => 2014120000,
                                       'taglinks' => 2014120000,
                                       )
         );
    }
    /**
     * Определить, возможна ли установка плагина в текущий момент
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
    /**
     * Получить список плагинов, которые уже должны быть установлены в системе,
     * и без которых начать установку или обновление невозможно
     *
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion=0)
    {
        return array('storage'=>array('tags'      => 2014120000,
                                      'tasks'     => 2014120000,
                                      'taglinks'  => 2014120000,
                                      'comments'  => 2014120000,
                                      'acl'       => 2011040504
                                      ),
                     'workflow'=>array('tags'     => 2014120000,
                                       'tasks'    => 2014120000,
                                       'taglinks' => 2014120000,
                                       )
         );
    }
    /**
     * Список обрабатываемых плагином событий
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
       return array();
    }
    /**
     * Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
       return 600;
    }
    /**
     * Проверяет полномочия на совершение действий
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта,
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя в Moodle, полномочия которого проверяются
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

        // проверка
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// право есть заканчиваем обработку
            return true;
        }
        return false;

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
            $notice = "crm/{$do} (block/dof/im/inventory: {$do})";
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
        return true;
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
        if ( $loan == 3 )
        {
            mtrace("Перелинковка тегов начата");
            $this->rescan_tags();
            mtrace("Перелинковка тегов закончена");
        }
        if ( $loan == 3 )
        {
            mtrace("Проверка задач по дедлайну начата");
            $this->check_tasks_deadline();
            mtrace("Проверка задач по дедлайну окончена");
        }
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
    public function todo($code,$intvar,$mixedvar)
    {
        return true;
    }
    /**
     * Конструктор
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
    /**
     * Возвращает текст для отображения в блоке на странице dof
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string - html-код содержимого блока
     */
    function get_block($name, $id = 1)
    {
        $rez = '';
        switch ($name)
        {
            case 'page_main_name':
               $rez = "<a href='{$this->dof->url_im($this->code(),'/index.php')}'>"
                .$this->dof->get_string('page_main_name')."</a>";
        }
        return $rez;
    }
    /** Возвращает html-код, который отображается внутри секции
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string  - html-код содержимого секции секции
     */
    function get_section($name, $id = 1)
    {
        $rez = '';

        return $rez;
    }

    /**
     * Получить список параметров для фунции has_hight()
     * @todo завести дополнительные права в плагине storage/persons и storage/contracts
     * и при редактировании контракта или персоны обращаться к ним
     *
     * @return object - список параметров для фунции has_hight()
     * @param string $action - совершаемое действие
     * @param int $objectid - id объекта над которым совершается действие
     * @param int $personid
     */
    protected function get_access_parametrs($action, $objectid=0, $personid)
    {
        $result = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->personid     = $personid;
        $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        $result->objectid     = $objectid;

        return $result;
    }

    /**
     * Проверить права через плагин acl.
     * Функция вынесена сюда, чтобы постоянно не писать длинный вызов и не перечислять все аргументы
     *
     * @return bool
     * @param object $acldata - объект с данными для функции storage/acl->has_right()
     */
    protected function acl_check_access_paramenrs($acldata)
    {
        return $this->dof->storage('acl')->
                    has_right($acldata->plugintype, $acldata->plugincode, $acldata->code,
                              $acldata->personid, $acldata->departmentid, $acldata->objectid);
    }

    /**
     * Задаем права доступа
     *
     * @return array
     */
    public function acldefault()
    {
        $a = array();
        return $a;
    }

    /** Функция получения настроек для плагина
     *
     */
    public function config_default($code=null)
    {
        $config = array();
        // Пагинация линков
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'taglinks_paging';
        $obj->value = '100';
        $config[$obj->code] = $obj;
        return $config;
    }


    /**
     * Получить URL к собственным файлам плагина
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


    // **********************************************
    //              Собственные методы
    // **********************************************

    /**
     * Возвращает вкладки навигации плагина
     *
     * @param string $activetab1 - какая вкладка первого уровня активна в данный момент
     * @param string $activetab2 - какая вкладка второго уровня активна в данный момент
     * @param array $addvars - массив параметров GET(подразделение)
     * @return смешанную строку
     */
    public function print_tab($addvars, $activetab1 = '', $activetab2 = '')
    {
    // Готовим вкладки
    $tabsblock = '';

    // Если не передана активная вкладка первого уровня
    if ( ! $activetab1 )
    {
        // Устанолвим на теги
        $activetab1 = 'tags';
    }
        // Если не передана активная вкладка второго уровня
        if ( ! $activetab2 )
        {
            if ($activetab1 == 'tags')
            {
                // Если активна вкладка теги, то показываем все теги
                $activetab2 = 'alltags';
            } else
            {
                // Если активна вкладка задачи, то показываем задачи для персоны
                $activetab2 = 'tasksforme';
            }
        }

        // Соберем данные для вкладок первого уровня
        $tabs = array();

        // Теги
        $link = $this->dof->url_im($this->code(),'/tags/alltags.php',$addvars);
        $text = $this->dof->get_string('persons', $this->code());
        $tabs[] = $this->dof->modlib('widgets')->create_tab('tags', $link, $text, NULL, true);
        // Задачи
        $link = $this->dof->url_im($this->code(),'/tasks/index.php',$addvars);
        $text = $this->dof->get_string('tasks', $this->code());
        $tabs[] = $this->dof->modlib('widgets')->create_tab('tasks', $link, $text, NULL, true);
        // Готовим для вывода вкладки первого уровня
        $tabsblock = $this->dof->modlib('widgets')->print_tabs($tabs, $activetab1, NULL, NULL, true);

        // Соберем данные для вкладок второго уровня

        // Обнулим массив вкладок
        $tabs = array();

        switch ($activetab1)
        {
            case 'tags' :
                // Все теги
                $link = $this->dof->url_im('crm','/tags/alltags.php',$addvars);
                $text = $this->dof->get_string('alltags', $this->code());
                $tabs[] = $this->dof->modlib('widgets')->create_tab('alltags', $link, $text, NULL, true);
                // Мои теги
                $link = $this->dof->url_im('crm','/tags/mytags.php',$addvars);
                $text = $this->dof->get_string('mytags', $this->code());
                $tabs[] = $this->dof->modlib('widgets')->create_tab('mytags', $link, $text, NULL, true);
                break;
            case 'tasks' :
                // Задачи для меня
                $link = $this->dof->url_im('crm','/tasks/index.php',$addvars);
                $text = $this->dof->get_string('tasksforme', $this->code());
                $tabs[] = $this->dof->modlib('widgets')->create_tab('tasksforme', $link, $text, NULL, true);
                // Задачи выданные мной
                $link = $this->dof->url_im('crm','/tasks/mytasks.php',$addvars);
                $text = $this->dof->get_string('mytasks', $this->code());
                $tabs[] = $this->dof->modlib('widgets')->create_tab('mytasks', $link, $text, NULL, true);
                // Не назначенные задачи
                $link = $this->dof->url_im('crm','/tasks/freetasks.php',$addvars);
                $text = $this->dof->get_string('freetasks', $this->code());
                $tabs[] = $this->dof->modlib('widgets')->create_tab('freetasks', $link, $text, NULL, true);
                // Слежу за
                $link = $this->dof->url_im('crm','/tasks/watchtasks.php',$addvars);
                $text = $this->dof->get_string('watchtasks', $this->code());
                $tabs[] = $this->dof->modlib('widgets')->create_tab('watchtasks', $link, $text, NULL, true);
                break;
        }

        $tabsblock .=   '<div>'.
                            $this->dof->modlib('widgets')->print_tabs($tabs, $activetab2, NULL, NULL, true).
                        '</div>';

        return $tabsblock;
    }

    // **********************************************
    //              Блок "задачи"
    // **********************************************



    /**
     * Получить строку сортировки из параметров
     *
     * @return string - фрагмент SQL запроса
     */
    public function get_sort_params()
    {
        // параметры для сортировки
        $srt = optional_param('sort','date',PARAM_TEXT);
        $ord = optional_param('ord','ASC',PARAM_TEXT);
        switch ($srt)
        {
            case 'aprs' :
                $sort = 'assignedpersonid';
                break;
            case 'pprs' :
                $sort = 'purchaserpersonid';
                break;
            case 'ptask' :
                $sort = 'parentid';
                break;
            case 'status' :
                $sort = 'status';
                break;
            case 'date' :
                $sort = 'date';
                break;
            case 'acdate' :
                $sort = 'actualdate';
                break;
            case 'dldate' :
                $sort = 'deadlinedate';
                break;
            default :
                $sort = 'date';
                break;
        }
        return  ' '.$sort.' '.$ord.' ';
    }

    /**
     * Вывести универсальную таблицу задач
     *
     * @param array $items - массив объектоа
     * @param string $page - имя страницы
     * @param array $addvars - массив GET параметров
     * @param number $limitfrom - смещение
     *
     */
    public function print_table_task($items, $page, $addvars = null, $limitfrom = 0 )
    {
        // Готовим таблицу
        $table = new stdClass();
        $table->tablealign = 'center';
        $table->width = '100%';
        $table->align = array('center','left','left','left','center','center','center','center','center');
        $table->head = $this->get_table_task_head($page, $addvars);
        $table->data = array();

        // Cчётчик
        if ( empty($addvars['limitfrom']) )
        {
            $i = 1;
        } else
        {
            $i = $addvars['limitfrom'];
            // Удалим, чтобы он не передавался дальше
            unset($addvars['limitfrom']);
        }

        // Для добавления классов к строкам
        $rows = 0;
        foreach ( $items as $item )
        {
            // Если есть доступ к задачае
            if ( $this->dof->storage('tasks')->is_access('view/owner', $item->id) )
            {
                // из каждой записи об создадим строку таблицы
                $row = array();

                // Счетчик
                $row[] = $i;

                // Название
                $addvars['taskid'] = $item->id;
                $row[] = '<a href="'.$this->dof->url_im('crm','/tasks/task.php',$addvars).'"
                             title="'.$this->dof->get_string('go_to_task', 'crm').'">'.
                                $item->title.
                         '</a>';

                // Кому поручена
                if ($item->assignedpersonid)
                {
                    // Если не системная
                    $row[] = $this->dof->storage('persons')->get_fullname($item->assignedpersonid);
                } else
                {
                    $row[] = $this->dof->get_string('no_assign_task', 'crm');
                }

                // Кем поручена
                if ($item->purchaserpersonid)
                {// Если не систкмная
                    $row[] = $this->dof->storage('persons')->get_fullname($item->purchaserpersonid);
                } else
                {
                    $row[] = $this->dof->get_string('system_name', 'crm');
                }

                // Заголовок задачи - родителя
                if ($item->parentid)
                {// Если родитель есть
                    $addvars['taskid'] = $item->parentid;
                    // Если есть доступ к задачае
                    if ( $this->dof->storage('tasks')->is_access('view/owner', $item->parentid) )
                    {
                        $row[] = '<a href="'.$this->dof->url_im('crm','/tasks/task.php',$addvars).'"
                                     title="'.$this->dof->get_string('go_to_task', 'crm').'">'.
                                         $this->dof->storage('tasks')->get($item->parentid)->title.
                                 '</a>';
                    } else
                    {
                        $row[] = $this->dof->storage('tasks')->get($item->parentid)->title;
                    }

                } else
                {// Если родителя нет
                    $row[] = '';
                }

                // Статус задачи
                $row[] = $this->dof->workflow('tasks')->get_name($item->status);

                // В зависимости от статуса добавляем класс
                switch ( $item->status )
                {
                    case 'completed':
                        $table->rowclasses[$rows] = 'completed';
                        break;
                    case 'suspend':
                        $table->rowclasses[$rows] = 'suspend';
                        break;
                    case 'failed':
                        $table->rowclasses[$rows] = 'failed';
                        break;
                    case 'deleted':
                        $table->rowclasses[$rows] = 'deleted';
                        break;
                }

                // Дата создания
                $row[] = date('m-d-Y h:i', $item->date);

                // Дата актуализации
                $row[] = date('m-d-Y h:i', $item->actualdate);

                // Дата дедлайна
                $row[] =  date('m-d-Y h:i', $item->deadlinedate);

                // Добавляем строку в таблицу
                $table->data[] = $row;

                // Увеличиваем счетчики
                $i++;
                $rows++;
            }
        }
        // Печатаем таблицу
        $this->dof->modlib('widgets')->print_table($table);
    }

    /**
     * Отобразить задачу
     *
     * @param $item - задача, которую требуется отобразить
     * @param $addvars - GET параметры для ссылок
     * @param $actions - отображать ли кнопки действий
     */
    public function display_task($item, $addvars, $actions = true)
    {
        // Проверка доступа
        if ( $this->dof->storage('tasks')->is_access('view/owner', $item->id) )
        {
            $parentaddvars = $addvars;
            $table = new stdClass();

            $table->tablealign = 'left';
            $table->align = array('left','left');
            $table->width = '75%';
            $table->size = array('30%','70%');

            $labels = array();

            // Формируем массив действий
            $actionsarray = array();

            // Действия
            if ( $actions )
            {
                // Получаем права пользователя
                $iseditowner = $this->dof->storage('tasks')->is_access('edit/owner', $item->id);
                $iscreate = $this->dof->storage('tasks')->is_access('create', $item->id);
                $isdelete = $this->dof->storage('tasks')->is_access('delete', $item->id);
                $ischangestatus = $this->dof->workflow('tasks')->is_access('changestatus/owner', $item->id);

                // Получаем метастатус объекта
                $statuses = $this->dof->workflow('tasks')->get_meta_list('actual');
                $isactual = array_key_exists ( $item->status , $statuses );

                // Завершить задачу
                if ( $ischangestatus && $isactual )
                {
                    $addvars['action'] = 'solved';
                    $actionsarray['solved'] =
                    '<a
                        href="'.$this->dof->url_im('crm','/tasks/action.php',$addvars).'"
                        title="'.$this->dof->get_string('solved_task_title', 'crm').'">'.
                             $this->dof->get_string('solved_task', 'crm').
                    '</a>';
                }
                // Создать подзадачу, делегировать задачу
                if ( $iscreate )
                {
                    $addvars['action'] = 'children_task';
                    $actionsarray['ctask'] =
                    '<br /><a
                        href="'.$this->dof->url_im('crm','/tasks/action.php',$addvars).'"
                        title="'.$this->dof->get_string('create_child_task_title', 'crm').'">'.
                            $this->dof->get_string('create_child_task', 'crm').
                    '</a>&nbsp;&nbsp;';

                    $addvars['action'] = 'delegate';
                    $actionsarray['delegate'] =
                    '<br /><a
                        href="'.$this->dof->url_im('crm','/tasks/action.php',$addvars).'"
                        title="'.$this->dof->get_string('delegate_task_title', 'crm').'">'.
                            $this->dof->get_string('delegate_task', 'crm').
                    '</a>&nbsp;&nbsp;';
                }
                // Изменить задачу
                if ( $iseditowner && $isactual )
                {
                    $addvars['action'] = 'edit';
                    $actionsarray['change'] =
                    '<a
                        href="'.$this->dof->url_im('crm','/tasks/action.php',$addvars).'"
                        title="'.$this->dof->get_string('change_task_title', 'crm').'">'.
                            $this->dof->get_string('change_task', 'crm').
                    '</a>&nbsp;&nbsp;';
                }
                // Удалить задачу
                if ( $isdelete )
                {
                    $addvars['action'] = 'delete';
                    $actionsarray['delete'] =
                    '<a
                        href="'.$this->dof->url_im('crm','/tasks/action.php',$addvars).'"
                        title="'.$this->dof->get_string('delete_task_title', 'crm').'">'.
                            $this->dof->get_string('delete_task', 'crm').
                    '</a>';
                }

            }

            $actionslinks = '';
            foreach ( $actionsarray as $actionlink )
            {
                $actionslinks .= $actionlink;
            }

            $labels['label'] = $actionslinks;
            $labels['title']    = '<b>'.$item->title.'</b>';

            $table->head = $labels;

            // Заполняем таблицу
            $table->data = array();

            $table->data[] = array($this->dof->get_string('taskid', 'crm'), $item->id);
            // Кому поручена
            if ( $item->assignedpersonid )
            { // Если не системная
                $table->data[] =
                    array($this->dof->get_string('assignperson', 'crm'),
                          $this->dof->storage('persons')->get_fullname($item->assignedpersonid)
                    );
            } else
            {
                $table->data[] = array($this->dof->get_string('assignperson', 'crm'), $this->dof->get_string('no_assign_task', 'crm'));
            }
            // Кем поручена
            if ($item->purchaserpersonid)
            { // Если не систкмная
                $table->data[] =
                    array($this->dof->get_string('purchaserperson', 'crm'),
                        $this->dof->storage('persons')->get_fullname($item->purchaserpersonid)
                    );
            } else
            {// Если системная
                $table->data[] = array($this->dof->get_string('purchaserperson', 'crm'), $this->dof->get_string('system_name', 'crm'));
            }

            // Описание
            $table->data[] = array($this->dof->get_string('about_task', 'crm'), $item->about);
            // Решение задачи
            $table->data[] = array($this->dof->get_string('solution', 'crm'), $item->solution);
            // Родитель
            if ( $item->parentid )
            {
                $parent = $this->dof->storage('tasks')->get_record(array('id' => $item->parentid));
                if ($parent)
                {
                    if ( $this->dof->storage('tasks')->is_access('view/owner', $item->parentid) )
                    {
                        $addvars['taskid'] = $item->parentid;
                        $table->data[] = array(
                                $this->dof->get_string('parenttask', 'crm'),
                                '<a href="'.$this->dof->url_im('crm','/tasks/task.php',$addvars).'" title="'.$this->dof->get_string('go_to_task', 'crm').'">'.
                                $parent->title.'</a>'
                        );
                    } else
                    {
                        $table->data[] = array($this->dof->get_string('parenttask', 'crm'), $parent->title);
                    }
                }
            }

            // Дата создания
            $table->data[] = array($this->dof->get_string('creation_date', 'crm'), date('Y.m.d h:i:s', $item->date));
            // Дата дедлайна
            $table->data[] = array($this->dof->get_string('deadline_date', 'crm'), date('Y.m.d h:i:s', $item->deadlinedate));
            // Статус задачи
            $status = $this->dof->workflow('tasks')->get_name($item->status);

            $table->data[] = array($this->dof->get_string('status', 'crm'), $status);

            $this->dof->modlib('widgets')->print_table($table);
        }
    }

    /**
     * Отобразить задачу для делегирования
     *
     * @param object $item - объект задачи
     * @param array $addvars - массив GET параметров
     */
    public function display_delegatetask($item, $addvars)
    {
        // Если есть доступ к задачае
        if ( $this->dof->storage('tasks')->is_access('view/owner', $item->id) )
        {
            $table = new stdClass();

            // Свойства таблицы
            $table->tablealign = 'left';
            $table->align = array('left','left');

            $table->width = '75%';
            $table->size = array('30%','70%');

            $labels = array();

            // Заголовок задачи
            $addvars['action'] = 'delegate';

            $labels['label'] = '<b>'.$this->dof->get_string('task_for_delegate', 'crm').'</b>';
            $labels['title']    = '';

            $table->head = $labels;

            // Заполняем таблицу
            $table->data = array();

            $table->data[] = array($this->dof->get_string('title', 'crm'), $item->title);


            $table->data[] = array($this->dof->get_string('taskid', 'crm'), $item->id);
            // Кому поручена
            if ($item->assignedpersonid)
            { // Если не системная
                $table->data[] = array($this->dof->get_string('assignperson', 'crm'), $this->dof->storage('persons')->get_fullname($item->assignedpersonid));
            } else
            {
                $table->data[] = array($this->dof->get_string('assignperson', 'crm'), $this->dof->get_string('no_assign_task', 'crm'));
            }
            // Кем поручена
            if ($item->purchaserpersonid)
            { // Если не систкмная

                $table->data[] = array($this->dof->get_string('purchaserperson', 'crm'), $this->dof->storage('persons')->get_fullname($item->purchaserpersonid));
            } else
            {
                $table->data[] = array($this->dof->get_string('purchaserperson', 'crm'), $this->dof->get_string('system_name', 'crm'));
            }
            // Описание
            $table->data[] = array($this->dof->get_string('about_task', 'crm'), $item->about);
            // Решение задачи
            $table->data[] = array($this->dof->get_string('solution', 'crm'), $item->solution);
            // Родитель
            if ( $item->parentid )
            {
            if ($parent = $this->dof->storage('tasks')->get_record(array('id' => $item->parentid)))
                {
                    // Если есть доступ к задачае
                    if ( $this->dof->storage('tasks')->is_access('view/owner', $item->parentid) )
                    {
                        $addvars['taskid'] = $item->parentid;
                        $table->data[] = array(
                                $this->dof->get_string('parenttask', 'crm'),
                                '<a href="'.$this->dof->url_im('crm','/tasks/task.php',$addvars).'" title="'.$this->dof->get_string('go_to_task', 'crm').'">'.
                                $parent->title.'</a>'
                        );
                    } else
                    {
                        $table->data[] = array($this->dof->get_string('parenttask', 'crm'), $parent->title);
                    }
                }
            }
            // Статус задачи
            $table->data[] = array($this->dof->get_string('status', 'crm'), $item->status);
            // Дата создания
            $table->data[] = array($this->dof->get_string('creation_date', 'crm'), date('Y.m.d h:i:s', $item->date));
            // Дата дедлайна
            $table->data[] = array($this->dof->get_string('deadline_date', 'crm'), date('Y.m.d h:i:s', $item->deadlinedate));

            $this->dof->modlib('widgets')->print_table($table);
        }
    }

    /**
     * Смена статуса задачи и подзадач на 'Удалено'
     * @param int $taskid - id записи в таблице tasks
     * @param bool $first - флаг первого цикла из каскажа
     * return null
     */
    public function delete_task($taskid, $first = true)
    {
        if ( $first )
        {// Если происходит первый цикл - значит надо проверить права на удаление основной задачи
            if ( ! $this->dof->storage('tasks')->is_access('delete', $taskid) )
            {// Мы не можем удалять задачу
                return false;
            }
        }

        // ПОлучаем задачу
        $task = $this->dof->storage('tasks')->get($taskid);

        // Если задача еще не удалена - пробуем удалить
        if ( ! ( $task->status == 'deleted' ) )
        {
            // Пытаемся обновить
            if ( ! $this->dof->workflow('tasks')->change($taskid, 'deleted') )
            {
                // Ошибка при удалении
                $this->dof->print_error('error_change_status_task','',$taskid, 'im', 'crm');
            }
        }

        // Переходим к дочерним задачам
        $children = $this->dof->storage('tasks')->get_records(array('parentid' => $taskid), '', 'id');

        // Родитель удален, дочерних нет
        if ( empty($children) )
        {
            return true;
        }

        foreach ($children as $item)
        {
           // Пытаемся обновить
           if ( ! $this->delete_task($item->id, false) )
           {
               return false;
           }
        }
        // Все дочерние элементы обновлены
        return true;
    }


    /****************************************
     *
     * Теги
     *
    /*****************************************/

    /**
     *  Печать списка тегов
     *
     *  @param bool $real - false, если хотим напечатать все теги и
     *                      true, если хотим напечатать только реальные
     *  @param array $addvars - массив GET параметров
     *  @param int $order - сортировка, 0 - прямая, 1 - обратная
     *  @from - смещение выборки
     *  @limit - чосло тегов (0 - все теги)
     */
    public function print_list_tags($real = false, $addvars = null, $order = 0, $limitfrom = 0, $limitnum = 0)
    {
        // Формируем фильтр для получения тегов
        $filters = new stdClass();

        // Формируем массив статусов для возвращаемых тегов
        if ( $real )
        {
             $filters->status = $this->dof->workflow('tags')->get_meta_list('real');
        }
        // Сформируем подразделения
        $depid = optional_param('departmentid', 0, PARAM_INT);
        if ( $depid > 0 )
        {
            $departments = $this->dof->storage('departments')->departments_list_subordinated($depid);
            $departments[$depid] = $depid;
            // Добавим подразделения к фильтрации
            $filters->departmentid = $departments;

        }
        // Получаем выборку тегов
        $tags = $this->dof->storage('tags')->get_list_tags($filters, $order, $limitfrom, $limitnum);

        // Готовим html
        $html = html_writer::start_div('tags_block');

        // Готовим массив GET параметров для ссылок
        $somevars = $addvars;

        // Добавляем задачу, которую выполняет страница отображения
        $somevars['action'] = 'showtag';

        foreach ($tags as $tag)
        {
            if ( $this->dof->storage('tags')->is_access('view/owner', $tag->id) )
            {// Если есть доступ к тегу
                // Добавляем id тега
                $somevars['tagid'] =  $tag->id;

                // Ссылка на страницу детального описания тега
                $html .= html_writer::start_tag(
                        'a',
                        array('href' =>
                                $this->dof->url_im('crm', '/tags/action.php',$somevars)));
                if ( empty($tag->alias) )
                {// Если не указан алиас
                    $html .= html_writer::tag('span', $tag->code, array('class' => 'tagblock_p'));
                } else
                {// Если указан алиас
                    $html .= html_writer::tag('span', $tag->alias, array('class' => 'tagblock_p'));
                }
                $html .= html_writer::end_tag('a');
            } else
            {// Если нет доступа к тегу
                if ( empty($tag->alias) )
                {// Если не указан алиас
                    $html .= html_writer::tag('span', $tag->code, array('class' => 'tagblock_p closedtag'));
                } else
                {// Если указан алиас
                    $html .= html_writer::tag('span', $tag->alias, array('class' => 'tagblock_p closedtag'));
                }
            }
        }
        $html .= html_writer::end_div();

        // Печатаем данные
        print($html);
    }

    /**
     *  Печать списка приватных тегов
     *
     *  @param bool $real - false, если хотим напечатать все теги и
     *                      true, если хотим напечатать только реальные
     *  @param array $addvars - массив GET параметров
     *  @param int $order - сортировка, 0 - прямая, 1 - обратная
     *  @from - смещение выборки
     *  @limit - чосло тегов (0 - все теги)
     */
    public function print_list_mytags($real = false, $addvars = null, $order = 0, $limitfrom = 0, $limitnum = 0)
    {
        // Формируем фильтр для получения тегов
        $filters = new stdClass();

        // Формируем массив статусов для возвращаемых тегов
        if ( $real )
        {
            $filters->status = $this->dof->workflow('tags')->get_meta_list('real');
        }
        // Сформируем подразделения
        $depid = optional_param('departmentid', 0, PARAM_INT);
        if ( $depid > 0 )
        {
            $departments = $this->dof->storage('departments')->departments_list_subordinated($depid);
            $departments[$depid] = $depid;
            // Добавим подразделения к фильтрации
            $filters->departmentid = $departments;

        }

        // Получаем текущего пользователя
        $user = $this->dof->storage('persons')->get_bu();
        // Добавляем фильтрацию по пользователю
        $filters->ownerid = $user->id;

        // Получаем выборку тегов
        $tags = $this->dof->storage('tags')->get_list_tags($filters, $order, $limitfrom, $limitnum);

        // Готовим html
        $html = html_writer::start_div('tags_block');

        // Готовим массив GET параметров для ссылок
        $somevars = $addvars;

        // Добавляем задачу, которую выполняет страница отображения
        $somevars['action'] = 'showtag';

        foreach ($tags as $tag)
        {
            if ( $this->dof->storage('tags')->is_access('view/owner', $tag->id) )
            {// Если есть доступ к тегу
                // Добавляем id тега
                $somevars['tagid'] =  $tag->id;

                // Ссылка на страницу детального описания тега
                $html .= html_writer::start_tag(
                        'a',
                        array('href' =>
                                $this->dof->url_im('crm', '/tags/action.php',$somevars)));
                if ( empty($tag->alias) )
                {// Если не указан алиас
                    $html .= html_writer::tag('span', $tag->code, array('class' => 'tagblock_p'));
                } else
                {// Если указан алиас
                    $html .= html_writer::tag('span', $tag->alias, array('class' => 'tagblock_p'));
                }
                $html .= html_writer::end_tag('a');
            } else
            {// Если нет доступа к тегу
                if ( empty($tag->alias) )
                {// Если не указан алиас
                    $html .= html_writer::tag('span', $tag->code, array('class' => 'tagblock_p closedtag'));
                } else
                {// Если указан алиас
                    $html .= html_writer::tag('span', $tag->alias, array('class' => 'tagblock_p closedtag'));
                }
            }
        }
        $html .= html_writer::end_div();

        // Печатаем данные
        print($html);
    }

    /**
     * Функция печати данных тега
     *
     * @param int $tagid - ID тега
     * @param array $addvars - массив GET параметров
     */
    public function print_tag($tagid, $addvars = null)
    {
        // Получаем объект тега
        $tagobjectdb = $this->dof->storage('tags')->get($tagid);

        if ( empty($tagobjectdb) )
        {// Тег не найден
            $this->dof->print_error('error_tag_not_found', '', null, 'im', 'crm');
        }

        // Получаем таблицу общих параметров
        $table = $this->get_tag_info($tagobjectdb, $addvars);
        // Печатаем таблицу общих параметров
        $this->dof->modlib('widgets')->print_table($table);

        // Получаем объект класса тега
        $tagobject = $this->dof->storage('tags')->tag($tagid);
        // Получаем таблицу подробной информации
        $table = $this->get_tag_options($tagobject, $tagobjectdb, $addvars);
        // Печатаем таблицу подробной информации
        $this->dof->modlib('widgets')->print_table($table);

        // Получаем таблицу дочерних тегов
        $table = $this->get_tag_childrens($tagobjectdb, $addvars);

        // Печатаем таблицу дочерних тегов
        $this->dof->modlib('widgets')->print_table($table);

        // Получаем таблицу линков
        $table = $this->get_tag_links_list($tagobject, $tagobjectdb, $addvars);

        // Печатаем таблицу линков
        $this->dof->modlib('widgets')->print_table($table);

    }

    /**
     * Запуск формирования выборки для тега
     *
     * @param int $tagid - ID тега, линки которого будут пересчитаны
     * @param int $depid - Принудительная установка ID подразделения для линка
     * @param int $timestamp - метка времени обновления тега
     *
     * @return null|bool - true, если перелинковка прошла успешно
     *              - false, если перелинковка завершилась с ошибкой
     */
    public function rescan_tag($tagid, $depid = 0, $timestamp = null)
    {
        // Меняем статус на Не доступен
        $this->dof->workflow('tags')->change($tagid, 'notavailable');

        // Время просчета линка
        if ( empty($timestamp) )
        {// Время линка не передано, установим его
            $timestamp = intval(date('U'));
        }

        // Проверка доступа, если функция запускается вручную
        if ( ! $this->dof->storage('taglinks')->is_access('edit') )
        {
            // Доступ запрещен
            return false;
        }

        // Получаем тег
        $tag = $this->dof->storage('tags')->get($tagid);
        if ( empty($tag) )
        {// Ошибка при получении тега
            return false;
        }

        // Условия, по которым тег можно начать пролинковывать сейчас
        if ( ( $tag->cronrepeate == 0 && $timestamp >= $tag->crondone + $tag->cronrepeate ) ||
                ( $tag->cronrepeate > 0 && $timestamp >= $tag->crondone + $tag->cronrepeate )
        )
        {
            // Производим пролинковку тега
            if ( $this->dof->storage('taglinks')->rescan_taglinks($tag->id, $depid) )
            {// Успешно
                // Добавляем информацию о прошедшей перелинковке
                $this->dof->storage('tags')->set_croninfo(true, $tag->id, $timestamp);
                // Меняем статус на Активный
                $this->dof->workflow('tags')->change($tag->id, 'active');
                return true;
            } else
            {// Неудачно
                // Добавляем информацию о неудачной перелинковке
                $this->dof->storage('tags')->set_croninfo(false, $tag->id, $timestamp);
                // Меняем статус на Неудачный
                $this->dof->workflow('tags')->change($tag->id, 'failed');
                return false;
            }
        }
        return null;
    }

    /**
     * Метод перелинковки всех реальных тегов
     */
    public function rescan_tags()
    {
        // Получаем текущее время для фильтрации тегов, которые в данный момент не подлежат линковке
        $timestamp = intval(date('U'));

        // Формируем список тегов для просчета
        $sql = '';
        $params = array();

        $junkstatuses = $this->dof->workflow('tags')->get_meta_list('junk');
        // Если есть мусорные статусы - добавляем фильтрацию по ним
        if (! empty($junkstatuses) )
        {
            foreach ( $junkstatuses as $status => $name )
            {
                if ( ! empty($sql) )
                {
                    $sql .= ' AND ';
                }
                // Добавляем статус счета
                $params['status'.$status] = $status;
                $sql .= 'status <> :status'.$status.'';
            }
        }

        // Добавляем фильтрацию по времени
        $statussql = false;
        if ( ! empty($sql) )
        {
            $statussql = true;
            $sql .= ' AND ( ';
        }
        $sql .= ' cron <= :cronstart AND cron >= 0 ';
        $params['cronstart'] = $timestamp;

        // Если мы открывали скобку ,надо их закрыть
        if ( $statussql )
        {
                $sql .= ' ) ';
        }

        // Получаем число задач для поддержки пагинации
        $tags = $this->dof->storage('tags')->get_records_select($sql, $params);

        foreach ( $tags as $tag )
        {
            $status = $this->rescan_tag($tag->id, 0, $timestamp);

            if ( $status === true )
            {
                mtrace('Тег ID:'.$tag->id.' пересчитал ссылки');
            }
            if ( $status === false )
            {
                mtrace('Ошибка при пересчете тега. ID:'.$tag->id);
            }
        }
    }

    /**
     * Смена статуса тега и дочерних тегов на 'Удалено'
     *
     * @param int $tagid - id записи в таблице tags
     */
    public function delete_tag($tagid, $first = true)
    {
        if ( $first )
        {// Если происходит первый цикл - значит надо проверить права на удаление основного тега
            if ( ! $this->dof->storage('tags')->is_access('delete') )
            {// Мы не можем удалять теги
                return false;
            }
        }

        // ПОлучаем тег
        $task = $this->dof->storage('tags')->get($tagid);

        // Если задача еще не удалена - пробуем удалить
        if ( ! ( $task->status === 'deleted' ) )
        {
            // Пытаемся обновить
            if ( ! $this->dof->workflow('tags')->change($tagid, 'deleted') )
            {
                // Ошибка при удалении
                $this->dof->print_error('error_change_status_tag','',$tagid, 'im', 'crm');
            }
        }

        // Переходим к дочерним тегам
        $children = $this->dof->storage('tags')->get_records(array('parentid' => $tagid), '', 'id');

        // Родитель удален, дочерних нет
        if ( empty($children) )
        {
            return true;
        }

        foreach ($children as $item)
        {
           // Пытаемся обновить
           if ( ! $this->delete_tag($item->id, false) )
           {
               return false;
           }
        }
        // Все дочерние элементы обновлены
        return true;
    }

    /****************************************
     *
     * Ссылки тегов
     *
     /***************************************/

    public function print_taglink($taglinkid, $ptype, $pcode, $objectid, $departmentid, $addvars)
    {

        /* Разделение вида в зависимости от переданных данных */
        // По ID линка
        if ( ! empty($taglinkid) )
        {// Нам передан $taglinkid, значит печатаем информацию о связи и информацию об объекте

            // Проверка доступа к линку
            $this->dof->storage('taglinks')->require_access('view', $taglinkid, NULL, $departmentid);

            // Печатаем информацию о связи
            $this->print_taglink_maininfo_table($taglinkid, $addvars);

            // Печатаем информацию от объекте
            $this->print_taglink_objectinfo_table($taglinkid, $addvars);

            return true;
        }
        // По $ptype, $pcode и $objectid
        /*if ( $this->dof->plugin_exists($ptype, $pcode) )
        {// Такой плагин зарегистрирован
            if ( $ptype == 'storage' )
            {// Плагин - справочник
                $item = $this->dof->get($objectid);
                if ( empty($item) )
                {
                    $this->dof->print_error('object_not_found', null, 'storage', 'taglinks');
                }
            }

        }*/
        return true;
    }

    /**
     * Печать шапки для универсальной таблицы задач
     *
     * @param string $page - имя страницы
     * @param array $addvars - массив GET параметров для передачи по ссылкам
     *
     * @return array - массив заголовков таблиц
     */
    private function get_table_task_head($page, $addvars)
    {
        // Параметры для сортировки
        $addvars['sort'] = 'date';
        $ord = optional_param('ord','ASC',PARAM_TEXT);
        if ($ord == 'ASC')
        {
            $addvars['ord'] = 'DESC';
        } else
        {
            $addvars['ord'] = 'ASC';
        }

        //  Формируем заголовок таблицы задач
        $labels = array();

        // Номер задачи
        $labels['number'] = $this->dof->get_string('rownumsumbol', 'crm');

        // Имя задачи
        $labels['name'] = $this->dof->get_string('task_title', 'crm');

        $addvars['sort'] = 'aprs';
        // Кому поручена задача
        $labels['assignperson'] =
        '<a href="'.$this->dof->url_im('crm','/tasks/'.$page.'.php',$addvars).'"
                title="'.$this->dof->get_string('sort_by_aperson', 'crm').'">'.
                    $this->dof->get_string('assignperson', 'crm', '<br>').
                    '</a>';

        // Кем поручена задача
        $addvars['sort'] = 'pprs';
        $labels['purchaserperson'] =
        '<a href="'.$this->dof->url_im('crm','/tasks/'.$page.'.php',$addvars).'"
                title="'.$this->dof->get_string('sort_by_pperson', 'crm').'">'.
                    $this->dof->get_string('purchaserperson', 'crm', '<br>').
                    '</a>';

        // Родительская задача
        $addvars['sort'] = 'ptask';
        $labels['parenttask'] =
        '<a href="'.$this->dof->url_im('crm','/tasks/'.$page.'.php',$addvars).'"
                title="'.$this->dof->get_string('sort_by_parent_task', 'crm').'">'.
                    $this->dof->get_string('parenttask', 'crm', '<br>').
                    '</a>';

        // Статус задачи
        $addvars['sort'] = 'status';
        $labels['status'] =
        '<a href="'.$this->dof->url_im('crm','/tasks/'.$page.'.php',$addvars).'"
                title="'.$this->dof->get_string('sort_by_status', 'crm').'">'.
                    $this->dof->get_string('status', 'crm', '<br>').
                    '</a>';

        // Дата создания
        $addvars['sort'] = 'date';
        $labels['date'] =
        '<a href="'.$this->dof->url_im('crm','/tasks/'.$page.'.php',$addvars).'"
                title="'.$this->dof->get_string('sort_by_date', 'crm').'">'.
                    $this->dof->get_string('creation_date', 'crm', '<br>').
                    '</a>';

        // Дата актуализации
        $addvars['sort'] = 'acdate';
        $labels['actualdate'] =
        '<a href="'.$this->dof->url_im('crm','/tasks/'.$page.'.php',$addvars).'"
                title="'.$this->dof->get_string('sort_by_actualdate', 'crm').'">'.
                    $this->dof->get_string('actual_date', 'crm', '<br>').
                    '</a>';

        // Дедлайн
        $addvars['sort'] = 'dldate';
        $labels['deadlinedate']  =
        '<a href="'.$this->dof->url_im('crm','/tasks/'.$page.'.php',$addvars).'"
                title="'.$this->dof->get_string('sort_by_deadlinedate', 'crm').'">'.
                    $this->dof->get_string('deadline_date', 'crm', '<br>').
                    '</a>';

        return $labels;
    }

    /**
     * Формирование таблицы общей информации тега
     *
     * @param object $tag - объект тега
     * @param array $addvars - массив GET параметров
     *
     * @return object $table - таблица общей информации тега
     */
    private function get_tag_info($tag, $addvars)
    {
        //  Определяем таблицу
        $table = new stdClass();

        // Свойства таблицы
        $table->tablealign = 'left';
        $table->align = array('left','left');

        $table->width = '75%';
        $table->size = array('30%','70%');

        $labels = array();

        // Заголовок тега
        $labels['label'] = '';
        if ( $this->dof->storage('tags')->is_access('edit/owner', $tag->id) )
        {
            $addvars['action'] = 'edittag';
            $addvars['tagid'] = $tag->id;

            // Ссылка на редактирование тега
            $labels['label'] .= '<b><a
                        href="'.$this->dof->url_im('crm','/tags/action.php',$addvars).'"
                        title="'.$this->dof->get_string('edittag', 'crm').'">'.
                                    $this->dof->get_string('edittag', 'crm').'</a></b><br />';
        }
        if ( $this->dof->storage('tags')->is_access('delete') )
        {
            $addvars['action'] = 'deletetag';
            $addvars['tagid'] = $tag->id;

            // Ссылка на удаление тега
            $labels['label'] .= '<b><a
                        href="'.$this->dof->url_im('crm','/tags/action.php',$addvars).'"
                        title="'.$this->dof->get_string('deletetag', 'crm').'">'.
                                $this->dof->get_string('deletetag', 'crm').'</a></b>';
        }

        $labels['title']    = '';

        $table->head = $labels;

        // Заполняем таблицу
        $table->data = array();
        // ID тега
        $table->data[] = array($this->dof->get_string('tagid', 'crm'), $tag->id);
        // Класс тега
        $table->data[] = array($this->dof->get_string('tagclass', 'crm'), $tag->class);
        // Алиас
        $table->data[] = array($this->dof->get_string('tagalias', 'crm'), $tag->alias);
        // Код
        $table->data[] = array($this->dof->get_string('code', 'crm'), $tag->code);

        // Родитель
        if ( $tag->parentid )
        {
            if ( $parent = $this->dof->storage('tags')->get($tag->parentid) )
            {
                // Если у родителя есть Алиас, в ссылке показываем его, иначе показываем Код
                if ( $parent->alias )
                {
                    $link = $parent->alias;
                } else
                {
                    $link = $parent->code;
                }

                if ( $this->dof->storage('tags')->is_access('view/owner', $parent->id) )
                {// Доступ есть, покажем ссылку
                    // Формируем Get параметры для ссылки
                    $addvars['tagid'] = $tag->parentid;
                    $addvars['action'] = 'showtag';

                    // Выводим ссылку на родительский тег
                    $table->data[] = array(
                            $this->dof->get_string('parenttag', 'crm'),
                            '<a href="'.$this->dof->url_im('crm','/tags/action.php',$addvars).'" title="'.$this->dof->get_string('go_to_tag', 'crm').'">'.
                            $link.'</a>'
                    );
                } else
                {
                    // Выводим имя родительского тега
                    $table->data[] = array(
                            $this->dof->get_string('parenttag', 'crm'),
                            $link
                    );
                }
            }
        }
        // Требуется ли крон
        if ( $tag->cron == -1 )
        {
            $table->data[] = array($this->dof->get_string('cron', 'crm'), $this->dof->get_string('disable_cron', 'crm'));
        }
        if ( $tag->cron == 0 )
        {
            $table->data[] = array($this->dof->get_string('cron', 'crm'), $this->dof->get_string('next_cron', 'crm'));
        }
        if ( $tag->cron > 0 )
        {
            $table->data[] = array($this->dof->get_string('cron', 'crm'),
                    $this->dof->get_string('timestamp_cron', 'crm').date('d-m-Y H:m:s', $tag->cron));
        }
        // Дата последнего крона
        if ( empty($tag->crondone) )
        {
            $table->data[] = array($this->dof->get_string('crondone', 'crm'), '');
        } else
        {
            $table->data[] = array($this->dof->get_string('crondone', 'crm'), date('d-m-Y H:m:s', $tag->crondone));
        }

        // Статус крона
        $table->data[] = array($this->dof->get_string('cronstatus', 'crm'), $tag->cronstatus);
        // Периодичность повторения крона
        $table->data[] = array($this->dof->get_string('cronrepeate', 'crm'), $tag->cronrepeate);
        // О теге
        $table->data[] = array($this->dof->get_string('about', 'crm'), $tag->about);
        // Статус тега
        $status = $this->dof->workflow('tags')->get_name($tag->status);

        $table->data[] = array($this->dof->get_string('status', 'crm'), $status);

        return $table;

    }

    /**
     * Получаем таблицу дочерних тегов
     *
     * @param int $tagobjectdb - Объект тега из БД
     * @param array $addvars - массив GET параметров
     *
     * @return Object $table - таблица дочерних тегов
     */
    private function get_tag_childrens($tagobjectdb, $addvars)
    {
        // Формируем фильтр для получения родительских тегов
        $filters = new stdClass();

        // Добавим статусы к фильтрации
        $filters->status = $this->dof->workflow('tags')->get_meta_list('real');

        // Сформируем подразделения
        $departments = $this->dof->storage('departments')->departments_list_subordinated($tagobjectdb->departmentid);
        $departments[$tagobjectdb->departmentid] = $tagobjectdb->departmentid;
        // Добавим подразделения к фильтрации
        $filters->departmentid = $departments;

        $filters->parentid = $tagobjectdb->id;

        // Получаем выборку тегов
        $childtags = $this->dof->storage('tags')->get_list_tags($filters);

        //  Определяем таблицу
        $table = new stdClass();

        // Свойства таблицы
        $table->tablealign = 'left';
        $table->align = array('left');
        $table->width = '75%';

        // Шапка таблицы
        $labels = array();
        // Ссылка на редактирование тега
        $labels['label'] = '<b>'.$this->dof->get_string("children_tag_list", "crm").'</b>';
        $table->head = $labels;

        // Заполняем таблицу
        $table->data = array();

        // Если нет дочерних тегов - возвращаем пустую таблицу
        if ( empty($childtags) )
        {
            $table->data[] = array($this->dof->get_string('empty','crm'));
            return $table;
        }

        // Чтобы определять, что из за прав доступа пользователь не увидел ни одного дочернего тега
        $empty = true;
        // Ссылки на дочерние теги
        foreach ($childtags as $childtag)
        {
            if ( $this->dof->storage('tags')->is_access('view/owner', $childtag->id) )
            {// Если есть доступ к тегу
                // Таблица не пустая
                $empty = false;

                // Если есть Алиас, в ссылке показываем его, иначе показываем Код
                if ( $childtag->alias )
                {
                    $link = $childtag->alias;
                } else
                {
                    $link = $childtag->code;
                }

                // Формируем Get параметры для ссылки
                $addvars['tagid'] = $childtag->id;
                $addvars['action'] = 'showtag';

                // Выводим ссылку на родительский тег
                $table->data[] = array(
                        '<a href="'.$this->dof->url_im('crm','/tags/action.php',$addvars).'"
                            title="'.$this->dof->get_string('go_to_tag', 'crm').'">'.
                                $link.
                        '</a>'
                    );
            }
        }
        // Если нет отображеннных дочерних тегов - возвращаем пустую таблицу
        if ( $empty )
        {
            $table->data[] = array($this->dof->get_string('empty','crm'));
            return $table;
        }

        // Возвращаем таблицу с тегами
        return $table;
    }

    /**
     * Получаем таблицу подробной информации
     *
     * Информация генерируется классом тега
     *
     * @param Object $tagobject - Объект класса тега
     * @param Object $tagobjectdb - Объект тега из БД
     * @param array $addvars - массив GET параметров
     *
     * @return Object $table - таблица подробной информации
     */
    private function get_tag_options($tagobject, $tagobjectdb, $addvars)
    {
        // Получаем таблишу с данными об опциях тега
        $table = $tagobject->show_tag($tagobjectdb, $addvars);

        // Шапка тааблицы
        $labels = array();
        $labels['label'] = '<b>'.$this->dof->get_string("tag_options", "crm").'</b>';
        $table->head = $labels;

        // Возвращаем таблицу
        return $table;
    }

    /**
     * Получаем таблицу линков
     *
     * Информация генерируется классом тега
     *
     * @param Object $tagobject - Объект класса тега
     * @param Object $tagobjectdb - Объект тега из БД
     * @param array $addvars - массив GET параметров
     *
     * @return Object $table - таблица подробной информации
     */
    private function get_tag_links_list($tagobject, $tagobjectdb, $addvars )
    {
        /* Получение данных */

        // Получить подразделение
        $departmentid = optional_param('departmentid', 0, PARAM_INT);
        // Получаем пользователя
        $person = $this->dof->storage('persons')->get_bu();
        // ID пользователя
        if ( ! empty($person) )
        {
            $personid = $person->id;
        } else
        {
            $personid = 0;
        }

        // Начиная с какого номера записи показывать
        $limitfrom    = optional_param('limitfrom', '1', PARAM_INT);

        // Какое количество строк таблицы выводить на экран
        $taglinks_paging = intval(
                $this->dof->storage('config')->get_config_value(
                        'taglinks_paging',
                        'im',
                        'crm',
                        $departmentid,
                        $personid
                        ) );

        // Переопределяем, если у нас передано значение в GET
        $limitnum = optional_param('limitnum', $taglinks_paging, PARAM_INT);

        /* Формирование фильтра */
        $filters = new stdClass();
        // Формируем массив статусов для возвращаемых линковок
        $filters->status = $this->dof->workflow('taglinks')->get_meta_list('real');
        // ID тега, линковки которого возвращаем
        $filters->tagid = $tagobjectdb->id;
        // Сформируем подразделения, линковки которых надо получить
        if ( $departmentid > 0 )
        {// У нас выбрано подразделение, производим фильтрацию
            $departments = $this->dof->storage('departments')->departments_list_subordinated($departmentid);
            $departments[$departmentid] = $departmentid;
            // Добавим подразделения к фильтрации
            $filters->departmentid = $departments;
        }

        /* Пагинация */
        // Получаем число линковок для поддержки пагинации
        $itemscount = count($this->dof->storage('taglinks')->get_list_taglinks($filters));
        // Подключаем класс для вывода страниц
        $pages = $this->dof->modlib('widgets')->pages_navigation('crm', $itemscount, $limitnum, $limitfrom);
        // Добавляем пагинацию к GET параметрам
        $addvars['limitfrom'] = $limitfrom;
        $addvars['limitnum'] = $limitnum;
        $addvars['tagid'] = $tagobjectdb->id;
        // Выводим пагинацию
        echo $pages->get_navpages_list('/tags/action.php', $addvars).'<br />';

        /* Формирование таблицы */
        // Получаем массив линков
        $list = $this->dof->storage('taglinks')->get_list_taglinks($filters, 0, $limitfrom - 1, $limitnum);
        // Получаем таблицу линков, сгенерированную классом
        $table = $tagobject->show_taglinks_list($tagobjectdb, $list, $addvars);
        // Шапка таблицы
        $labels = array();
        $labels['label'] = '<b>'.$this->dof->get_string('tag_links', 'crm').'</b>';
        $table->head = $labels;
        // Возвращаем таблицу линковок
        return $table;
    }

    /**
     * Метод проверки дедлайна у задач
     *
     * Метод сканирует все активные задачи и меняет статус у просроченных
     */
    private function check_tasks_deadline()
    {
        // Получить текущее время
        $timenow = intval(date('U'));

        // Получаем все активные статусы
        $activestatuses = $this->dof->workflow('tags')->get_meta_list('active');

        // Готовим начальные данные
        $sql = '';
        $params = array();

        // Если есть активные статусы - добавляем фильтрацию по ним
        if (! empty($activestatuses) )
        {
            foreach ( $activestatuses as $status => $name )
            {
                if ( ! empty($sql) )
                {
                    $sql .= ' OR ';
                }
                // Добавляем статус счета
                $params['status'.$status] = $status;
                $sql .= 'status = :status'.$status.'';
            }
        }

        // Получаем все активные статусы
        $tasks = $this->dof->storage('tasks')->get_records_select($sql, $params);
        if ( empty($tasks) )
        {
            return true;
        }
        // Проверяем каждую задачу
        foreach ( $tasks as $item )
        {
            if ( $item->deadlinedate > $timenow )
            {// Дедлайн превышен
                $this->dof->workflow('tasks')->change($item->id, 'failed');
            }
        }
        return true;
    }

    /**
     * Печать таблицы базовой информации о линковке тега
     *
     * @param int $taglinkid - ID линковки
     * @param array $addvars - массив GET параметров
     */
    private function print_taglink_maininfo_table($taglinkid, $addvars)
    {
        // Печатаем заголовок таблицы
        echo $this->dof->modlib('widgets')->print_heading(
                $this->dof->get_string('table_head_taglinkinfo', 'crm'), '', 2, 'main', true);

        // Получаем линковку
        $taglink = $this->dof->storage('taglinks')->get($taglinkid);

        if ( empty($taglink) )
        {// Линковка не получена
            $this->dof->print_error('error_taglink_not_found', '', null, 'im, crm');
        }

        // Формируем таблицу
        $table = new stdClass();

        // Свойства таблицы
        $table->tablealign = 'left';
        $table->align = array('left','left');
        $table->width = '75%';
        $table->size = array('30%','70%');

        // Заполняем таблицу
        $table->data = array();

        $table->data[] = array($this->dof->get_string('taglink_id', 'crm'), $taglink->id);

        $tag = $this->dof->storage('tags')->get($taglink->tagid);
        if ( ! empty($tag) )
        {// Тег найден

            // Массив параметров для ссылки на тег
            $addvars['action'] = 'showtag';
            $addvars['tagid'] = $taglink->tagid;
            // В зависимости от того, заполнен алиас или нет
            if ( empty($tag->alias) )
            {
                // Ссылка на тег
                $table->data[] =
                array($this->dof->get_string('tag', 'crm'),
                        '<a href="'.$this->dof->url_im('crm','/tags/action.php',$addvars).'"
                      title="'.$this->dof->get_string('go_to_tag', 'crm').'">'.
                        $tag->code.
                        '</a>'
                );

            } else
            {
                // Ссылка на тег
                $table->data[] =
                array($this->dof->get_string('tag', 'crm'),
                        '<a href="'.$this->dof->url_im('crm','/tags/action.php',$addvars).'"
                      title="'.$this->dof->get_string('go_to_tag', 'crm').'">'.
                        $tag->alias.
                        '</a>'
                );

            }
        }

        // Тип плагина, на который указывает линк
        $table->data[] = array($this->dof->get_string('taglink_plugintype', 'crm'), $taglink->plugintype);
        // Код плагина, на который указывает линк
        $table->data[] = array($this->dof->get_string('taglink_plugincode', 'crm'), $taglink->plugincode);
        // ID объекта, на который указывает линк
        $table->data[] = array($this->dof->get_string('taglink_objectid', 'crm'), $taglink->objectid);

        // Получаем имя подразделения
        $depatrment = $this->dof->storage('departments')->get($taglink->departmentid);
        if ( ! empty($depatrment) )
        {
            // Подразделение
            $table->data[] = array($this->dof->get_string('taglink_department', 'crm'), $depatrment->name);
        }

        // Информайия по линковке
        $table->data[] = array($this->dof->get_string('taglink_infotext', 'crm'), $taglink->infotext);

        // Дата создания
        $table->data[] = array($this->dof->get_string('taglink_createdate', 'crm'), date('d-m-Y', $taglink->date) );

        // Дата обновления
        $table->data[] = array($this->dof->get_string('taglink_updatemark', 'crm'), date('d-m-Y', $taglink->updatemark) );

        // Получаем название статуса
        $status = $this->dof->workflow('taglinks')->get_name($taglink->status);
        // Статус
        $table->data[] = array($this->dof->get_string('taglink_status', 'crm'), $status);

        // Печатаем таблицу
        $this->dof->modlib('widgets')->print_table($table);
    }

    /**
     * Печать таблицы c информацией по прилинкованному объекту
     *
     * @param int $taglinkid - ID линковки
     * @param array $addvars - массив GET параметров
     */
    private function print_taglink_objectinfo_table($taglinkid, $addvars)
    {
        // Печатаем заголовок таблицы
        echo $this->dof->modlib('widgets')->print_heading(
                $this->dof->get_string('table_head_taglinkobjectinfo', 'crm'), '', 2, 'main', true);

        // Получаем линковку
        $taglink = $this->dof->storage('taglinks')->get($taglinkid);

        // Получаем объект тега из БД
        $tagobjectdb = $this->dof->storage('tags')->get($taglink->tagid);

        // Получаем объект класса тега
        $tagobject = $this->dof->storage('tags')->tag($taglink->tagid);

        // Получаем таблишу с данными об опциях тега
        $table = $tagobject->show_taglink($tagobjectdb, $taglink, $addvars);

        // Печатаем таблицу
        $this->dof->modlib('widgets')->print_table($table);
    }
}