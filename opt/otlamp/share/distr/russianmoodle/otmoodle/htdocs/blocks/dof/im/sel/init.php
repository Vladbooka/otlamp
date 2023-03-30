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
 * Панель управления приемной комиссии. Класс плагина.
 *
 * @package    im
 * @subpackage sel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($DOF->plugin_path('storage', 'config', '/config_default.php'));

class dof_im_sel implements dof_plugin_im,  dof_storage_config_interface 
{
    /**
     * Объект деканата для доступа к общим методам
     * 
     * @var dof_control
     */
    protected $dof;
    
// **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    
    /** 
     * Метод, реализующий инсталяцию плагина в систему
     * 
     * Создает или модифицирует существующие таблицы в БД
     * и заполняет их начальными значениями
     * 
     * @return boolean
     */
    public function install()
    {
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    
    /** 
     * Метод, реализующий обновление плагина в системе.
     * Создает или модифицирует существующие таблицы в БД
     * 
     * @param string $old_version - Версия установленного в системе плагина
     * 
     * @return boolean
     */
    public function upgrade($oldversion)
    {
        // Обновление прав доступа
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());  
    }
    
    /** 
     * Метод, реализующий удаление плагина в системе.
     * 
     * @return boolean
     */
    public function uninstall()
    {
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(), []);
    }
    
    /**
     * Возвращает версию установленного плагина
     *
     * @return int - Версия плагина
     */
    public function version()
    {
        return 2017032000;
    }
    
    /**
     * Возвращает версии интерфейса Деканата, с которыми этот плагин может работать
     *
     * @return string
     */
    public function compat_dof()
    {
        return 'aquarium_bcd';
    }

    /**
     * Возвращает версии стандарта плагина этого типа, которым этот плагин соответствует
     * 
     * @return string
     */
    public function compat()
    {
        return 'angelfish';
    }
    
    /** 
     * Возвращает тип плагина
     * 
     * @return string 
     */
    public function type()
    {
        return 'im';
    }
    
    /** 
     * Возвращает короткое имя плагина
     * 
     * Оно должно быть уникально среди плагинов этого типа
     * 
     * @return string
     */
    public function code()
    {
        return 'sel';
    }
    
    /**
     * Возвращает список плагинов, без которых этот плагин работать не может
     *
     * @return array
     */
    public function need_plugins()
    {
        return [
            'im' => [
                'persons'      => 2016053100
            ],
            'modlib' => [
                'ig'           => 2016060900,
                'nvg'          => 2016050400,
                'widgets'      => 2016050500
            ],
            'storage' => [
                'config'          => 2012042500,
                'departments'     => 2016012100,
                'organizations'   => 2012102500,
                'persons'         => 2016060900,
                'contracts'       => 2016060900,
                'acl'             => 2012042500,
                'workplaces'      => 2012102500,
                'metacontracts'   => 2012102500,
                'orders'          => 2009052500
            ],
            'workflow' => [
                'contracts'       => 2015020200,
                'departments'     => 2011082200,
                'persons'         => 2015012000,
            ]
        ];
    }
    
    /** 
     * Определить, возможна ли установка плагина в текущий момент
     * Эта функция одинакова абсолютно для всех плагинов и не содержит в себе каких-либо зависимостей
     * @TODO УДАЛИТЬ эту функцию при рефакторинге. Вместо нее использовать наследование
     * от класса dof_modlib_base_plugin 
     * 
     * @see dof_modlib_base_plugin::is_setup_possible()
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * 
     * @return bool 
     *              true - если плагин можно устанавливать
     *              false - если плагин устанавливать нельзя
     */
    public function is_setup_possible($oldversion = 0)
    {
        return dof_is_plugin_setup_possible($this, $oldversion);
    }

    /**
     * Получить список плагинов, которые уже должны быть установлены в системе,
     * и без которых начать установку или обновление невозможно
     *
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     *
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion = 0)
    {
        return [
            'im' => [
                'persons'      => 2016053100
            ],
            'modlib' => [
                'ig'           => 2016060900,
                'nvg'          => 2016050400,
                'widgets'      => 2016050500
            ],
            'storage' => [
                'config'          => 2012042500,
                'departments'     => 2016012100,
                'organizations'   => 2012102500,
                'persons'         => 2016060900,
                'contracts'       => 2016060900,
                'acl'             => 2012042500,
                'workplaces'      => 2012102500,
                'metacontracts'   => 2012102500,
                'orders'          => 2009052500
            ],
            'workflow' => [
                'contracts'       => 2015020200,
                'departments'     => 2011082200,
                'persons'         => 2015012000,
            ]
        ];
    }
    
    /**
     * Список обрабатываемых плагином событий
     *
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     */
    public function list_catch_events()
    {
        return [
            [
                'plugintype' => 'im',
                'plugincode' => 'persons',
                'eventcode'  => 'persondata'
            ],
            [
                'plugintype' => 'im',
                'plugincode' => 'obj',
                'eventcode'  => 'get_object_url'
            ],
            [
                'plugintype' => 'im',
                'plugincode' => 'my',
                'eventcode'  => 'info'
            ]
        ];
    }
    
    /** 
     * Требуется ли запуск cron в плагине
     * 
     * @return bool
     */
    public function is_cron()
    {
       return 1;
    }
    
    /**
     * Запустить обработку периодических процессов
     *
     * @param int $loan - нагрузка (
     *              1 - только срочные,
     *              2 - нормальный режим,
     *              3 - ресурсоемкие операции
     *        )
     * @param int $messages - количество отображаемых сообщений (
     *              0 - не выводить,
     *              1 - статистика,
     *              2 - индикатор,
     *              3 - детальная диагностика
     *        )
     *
     * @return bool - true в случае выполнения без ошибок
     */
    public function cron($loan, $messages)
    {
        $result = true;
        if ( $loan == 2 )
        {// Генерация отчетов и исполнение приказов в соответствие с общим расписанием Деканата
            mtrace("Executed orders started");
            $result = $result && $this->dof->storage('orders')->generate_orders($this->type(), $this->code());
            
            mtrace("Generated reports started");
            $result = $result && $this->dof->storage('reports')->generate_reports($this->type(), $this->code());
        }
        return $result;
    }
    
    /** 
     * Проверяет полномочия на совершение действий
     * 
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     *                     по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя Moodle, полномочия которого проверяются
     * 
     * @return bool 
     *              true - можно выполнить указанное действие по 
     *                     отношению к выбранному объекту
     *              false - доступ запрещен
     */
    public function is_access($do, $objectid = null, $userid = null)
    {
        if ( $this->dof->is_access('datamanage') OR 
             $this->dof->is_access('admin') OR 
             $this->dof->is_access('manage') 
           )
        {// Открыть доступ для менеджеров
            return true;
        } 
              
        // Получаем ID персоны, с которой связан данный пользователь 
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        // Формируем параметры для проверки прав
        $acldata = $this->get_access_parametrs($do, $objectid, $personid);

        switch ( $do )
        {// Определяем дополнительные параметры в зависимости от запрашиваемого права
            default:
                break;
        }
        
        if ( dof_strbeginfrom($do, 'setstatus') !== false )
        {// для всех остальных случаев просто проверяем право менять статус
            $acldata->code = 'changestatus';
        }
        
        // Производим проверку
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// Право есть
            return true;
        } 
        
        if ( $acldata->code == 'view' )
        {// если нет права view - то проверим права view/seller и view/parent
            if ( $acldata->objectid )
            {// если запрашивается право на просмотр конкретного договора -
                // то проверим - является ли пользователь законным представителем или куратором
        
                // если указан - то получим контракт (с другими типами объектов мы в этом плагине не работаем)
                $object = $this->dof->storage('contracts')->get($objectid);
        
                if ( $userid == $object->clientid )
                {// пользователь является законным представителем
                    $acldata->code = 'view/parent';
                    if ( $this->acl_check_access_paramenrs($acldata) )
                    {// законным представителям разрешено просматривать договоры
                        return true;
                    }
                }
                if ( $userid == $object->sellerid )
                {// пользователь является куратором
                    $acldata->code = 'view/seller';
                    if ( $this->acl_check_access_paramenrs($acldata) )
                    {// законным представителям разрешено просматривать договоры
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /** 
	 * Требует наличия полномочия на совершение действий
	 * 
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     *                     по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя Moodle, полномочия которого проверяются
     * 
     * @return bool 
     *              true - можно выполнить указанное действие по 
     *                     отношению к выбранному объекту
     *              false - доступ запрещен
     */
    public function require_access($do, $objid = null, $userid = null)
    {
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $notice = "sel/{$do} (block/dof/im/sel: {$do})";
            if ($objid){$notice.=" id={$objid}";}
            $this->dof->print_error('nopermissions','',$notice);
        }
    }
    
    /**
     * Обработать событие
     *
     * @param string $gentype - тип модуля, сгенерировавшего событие
     * @param string $gencode - код модуля, сгенерировавшего событие
     * @param string $eventcode - код задания
     * @param int $intvar - дополнительный параметр
     * @param mixed $mixedvar - дополнительные параметры
     *
     * @return bool - true в случае выполнения без ошибок
     */
    public function catch_event($gentype, $gencode, $eventcode, $intvar, $mixedvar)
    {
        $result = '';
        
        if ( $gentype == 'im' AND $gencode == 'persons' AND $eventcode == 'persondata' )
        {// отобразить все подписки персоны
            
            if ( $table = $this->get_table_contracts($intvar) )
            {// у нас есть хотя бы один договор - выводим заголовок
                $heading = $this->dof->get_string('title', $this->code());
                $result .= $this->dof->modlib('widgets')->print_heading($heading, '', 2, 'main', true);
                $result .= $table;
            }
            
            return $result;
        }
        
        if ( $gentype == 'im' AND $gencode == 'obj' AND $eventcode == 'get_object_url' )
        {
            if ( $mixedvar['storage'] == 'contracts' )
            {
                if ( isset($mixedvar['action']) AND $mixedvar['action'] == 'view' )
                {// Получение ссылки на просмотр объекта
                    $params = array('id' => $intvar);
                    if ( isset($mixedvar['urlparams']) AND is_array($mixedvar['urlparams']) )
                    {
                        $params = array_merge($params, $mixedvar['urlparams']);
                    }
                    return $this->url('/contracts/view.php', $params);
                }
            }
        }
            
        if ( $gentype == 'im' AND $gencode == 'my' AND $eventcode == 'info' )
        {
            $sections = array();
            if ( $this->get_section('my_contracts', $intvar) )
            {// если в секции "моя нагрузка" есть данные - выведем секцию
                $sections[] = [
                    'im'=>$this->code(),
                    'name'=>'my_contracts',
                    'id'=>$intvar, 
                    'title'=>$this->dof->get_string('title', $this->code())
                ];
            }
            if ( $this->get_section('my_contracts_client', $intvar) )
            {// если в секции "моя нагрузка" есть данные - выведем секцию
                $sections[] = [
                    'im'=>$this->code(),
                    'name'=>'my_contracts_client',
                    'id'=>$intvar, 
                    'title'=>$this->dof->get_string('ward_contracts', $this->code())
                ];
            }

            return $sections;

        }
        return false;
    }
    
    /**
     * Обработать задание, отложенное ранее в связи с его длительностью
     * 
     * @param string $code - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * 
     * @return bool - true в случае выполнения без ошибок
     */
    public function todo($code,$intvar,$mixedvar)
    {
        return true;
    }
    
    /** 
     * Конструктор
     * 
     * @param dof_control $dof - объект с методами ядра деканата
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
    }
    
    /**
     * Получить настройки для плагина
     *
     * @param unknown $code
     *
     * @return array - Массив настроек плагина
     */
    public function config_default($code = null)
    {
        // Плагин включен и используется
        $config = [];
    
        // Включить/отключить плагин
        $config = [];
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'enabled';
        $obj->value = '1';
        $config[$obj->code] = $obj;
        
        // Регион в форме регистрации пользователя
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'defaultregion';
        $obj->value = 'RU-MOW';
        $config[$obj->code] = $obj;
        
        // Обязательность заполнения удостоверения личности для ЗП
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'requiredclpasstype';
        $obj->value = '0';
        $config[$obj->code] = $obj;
        
        // Обязательность заполнения email для ЗП
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'requiredclientemail';
        $obj->value = '0';
        $config[$obj->code] = $obj;
        
        // Обязательность заполнения удостоверения личности для студента
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'requiredstpasstype';
        $obj->value = '0';
        $config[$obj->code] = $obj;
        
        // Обязательность заполнения отчества для ЗП
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'requiredclmiddlename';
        $obj->value = '0';
        $config[$obj->code] = $obj;
        
        // Обязательность заполнения отчества для студента
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'requiredstmiddlename';
        $obj->value = '0';
        $config[$obj->code] = $obj;
        
        return $config;
    }
    
    // **********************************************
    // Методы, предусмотренные интерфейсом im
    // **********************************************
    
    /**
     * Возвращает текст для отображения в блоке на странице dof
     *
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     *
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
            default:
                break;
        }
        return $result;
    }
    
    /**
     * Возвращает html-код, который отображается внутри секции
     *
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     *
     * @return string  - html-код содержимого секции секции
     */
    public function get_section($name, $id = 0)
    {
        $result = '';
    
        // Инициализируем генератор HTML
        $this->dof->modlib('widgets')->html_writer();
    
        switch ($name)
        {
            case "my_contracts":
                $person = $this->dof->storage('persons')->get((int)$id);
                if( !empty($person) )
                {// Персона есть в деканате
                    $result = $this->get_table_contracts($person->id);
                }
                break;
            case "my_contracts_client";
                $person = $this->dof->storage('persons')->get((int)$id);
                if( !empty($person) )
                {// Персона есть в деканате
                    $result = $this->get_table_contracts_client($person->id);
                }
                break;
            default:
                break;
        }
        return $result;
    }

    // **********************************************
    //       Методы для работы с полномочиями
    // **********************************************
    
    /**
     * Получить список параметров для фунции has_hight()
     *
     * @return object - список параметров для фунции has_hight()
     * @param string $action - совершаемое действие
     * @param int $objectid - id объекта над которым совершается действие
     * @param int $personid
     */
    protected function get_access_parametrs($action, $objectid, $personid, $depid = null)
    {
        $result = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->personid     = $personid;
        $result->departmentid = $depid;
        $result->objectid     = $objectid;
    
        if ( is_null($depid) )
        {// Подразделение не задано - ищем в GET/POST
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        
        if ( ! $objectid )
        {// Если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        } else 
        {// Переопределение подразделения
            $contractdepid = $this->dof->storage('contracts')->get_field((int)$objectid, 'departmentid');
            if ( $contractdepid )
            {
                $result->departmentid = $contractdepid;
            }
        }
    
        return $result;
    }
    
    /**
     * Проверить права через плагин acl.
     *
     * Функция вынесена сюда, чтобы постоянно не писать
     * длинный вызов и не перечислять все аргументы
     *
     * @param object $acldata - объект с данными для функции storage/acl->has_right()
     *
     * @return bool
     */
    protected function acl_check_access_paramenrs($acldata)
    {
        return $this->dof->storage('acl')->
        has_right(
            $acldata->plugintype,
            $acldata->plugincode,
            $acldata->code,
            $acldata->personid,
            $acldata->departmentid,
            $acldata->objectid
        );
    }
    
    /** 
     * Сформировать права доступа для интерфейса
     * 
     * @return array - Массив с данными по правам доступа
     */
    public function acldefault()
    {
        $a = [];
        
        // Право просмотра договора менеджером
        $a['view/seller'] = [
            'roles' => [
                'manager'
            ]
        ];
        
        // Право просмотра договора законным представителем
        $a['view/parent'] = [
            'roles' => [
                'parent'
            ]
        ];
        
        // Право создания договора
        $a['openaccount'] = [
            'roles' => [
                'manager'
            ]
        ];
        
        // Право работы с платежной подсистемой договоров
        $a['payaccount'] = [
            'roles' => [
                'manager'
            ]
        ];

        // Право на смену статуса
        $a['changestatus'] = [
            'roles' => [
                'manager'
            ]
        ];
        
        return $a;
    }
    
    /**
     * Получить URL к собственным файлам плагина
     *
     * @param string $adds[optional] - фрагмент пути внутри папки плагина
     *                                 начинается с /. Например '/index.php'
     * @param array $vars[optional] - параметры, передаваемые вместе с url
     *
     * @return string - путь к папке с плагином
     */
    public function url($adds='', $vars=array())
    {
        return $this->dof->url_im($this->code(), $adds, $vars);
    }
    
    /**
     * Отображение уведомлений о результатах действий пользователей
     *
     * Формирует стек уведомлений на основе имеющихся GET-параметров
     *
     * @return void
     */
    public function messages()
    {
        // ПОЛЬЗОВАТЕЛЬСКАЯ ЧАСТЬ
        $masscomplete = optional_param('massactions_contracts_status_complete', '', PARAM_BOOL);
        if ( $masscomplete === 1 )
        {// Сообщение об успешном исполнении массовой задачи
            $this->dof->messages->add(
                $this->dof->get_string('message_massactions_contracts_status_suссess', 'sel'),
                'message'
            );
        } elseif ( $masscomplete === 0 )
        {// Сообщение об ошибки исполнении массовой задачи
            $this->dof->messages->add(
                $this->dof->get_string('message_massactions_contracts_status_error', 'sel'),
                'error'
            );
        }
        
        $masscomplete = optional_param('massactions_contracts_department_complete', '', PARAM_BOOL);
        if ( $masscomplete === 1 )
        {// Сообщение об успешном исполнении массовой задачи
            $this->dof->messages->add(
                $this->dof->get_string('message_massactions_contracts_department_suссess', 'sel'),
                'message'
            );
        } elseif ( $masscomplete === 0 )
        {// Сообщение об ошибки исполнении массовой задачи
            $this->dof->messages->add(
                $this->dof->get_string('message_massactions_contracts_department_error', 'sel'),
                'error'
            );
        }
    }
    
    /**
     * Сформировать общие GET параметры для плагина
     *
     * @param array &$addvars - Ссылка на массив GET-параметров
     *
     * @return void
     */
    public function get_plugin_addvars(&$addvars)
    {
        // Текущее подразделение
        if ( ! isset($addvars['departmentid']) )
        {// Не установлено текущее подразделение
            $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        }
        
        // Лимит записей на странице
        $baselimitnum = (int)$this->dof->modlib('widgets')->get_limitnum_bydefault($addvars['departmentid']);
        $limitnum = optional_param('limitnum', $baselimitnum, PARAM_INT);
        if ( $limitnum < 1 )
        {// Нормализация
            $limitnum = $baselimitnum;
        }
        $addvars['limitnum'] = $limitnum;
    }
    
    /** 
     * Получить таблицу с договорами студента
     * 
     * @param int $personid - ID персоны
     * 
     * @return string - HTML-код таблицы
     */
    public function get_table_contracts($personid)
    {
        require_once($this->dof->plugin_path('im', 'sel', '/locallib.php') );
        
        $result = '';
        // Получение договоров
        $conditions = [
            'studentid' => $personid,
            'status'    => ['work', 'frozen']
        ];
        $contracts = (array)$this->dof->storage('contracts')->get_records($conditions);
        
        if ( $contracts )
        {// Подготовка данных о договоре
            $result .= imseq_show_contracts($contracts, [], null, true);
        }
        
        return $result;
    }
    
    /**
     * Получить таблицу с договорами клиента
     *
     * @param int $personid - ID персоны
     *
     * @return string - HTML-код таблицы
     */
    public function get_table_contracts_client($personid)
    {
        require_once($this->dof->plugin_path('im', 'sel', '/locallib.php') );
    
        $result = '';
        // Получение договоров
        $conditions = [
            'clientid' => $personid,
            'status'    => ['work', 'frozen']
        ];
        $contracts = (array)$this->dof->storage('contracts')->get_records($conditions);
    
        if ( $contracts )
        {// Подготовка данных о договоре
            $result .= imseq_show_contracts($contracts, [], null, true);
        }
    
        return $result;
    }
    
    /**
     * Получить таблицу со списком договоров
     *
     * @param array $contracts - Список договоров
     * @param string $url - URL перехода
     * @param array $addvars - GET-параметры страницы
     *
     * @return null|dof_im_cpassed_listeditor_form - Объект формы или null
     */
    public function form_listeditor($contracts, $url, $addvars = [])
    {	
    	// Подключение библиотеки форм интерфейса
    	require_once($this->dof->plugin_path('im', 'sel', '/contracts/form.php'));
    
    	// Сформировать дополнительные данные
    	$customdata = new stdClass();
    	$customdata->contracts = $contracts;
    	$customdata->addvars = $addvars;
    	$customdata->dof = $this->dof;
    	
    	// Генерация формы
    	$form = new sel_listeditor_form($url, $customdata);
    	
    	return $form;
    }
	
	/**
	 * Возвращает объект приказа
	 *
	 * @param string $code
	 * @param integer  $id
	 * @return dof_storage_orders_baseorder
	 */
	public function order($code, $id = NULL)
	{
		require_once($this->dof->plugin_path('im','sel','/orders/contracts_status/init.php'));
		require_once($this->dof->plugin_path('im','sel','/orders/contracts_department/init.php'));
		switch ( $code )
		{
			case 'contracts_status':
				$order = new dof_im_sel_order_contracts_status($this->dof);
				if ( ! is_null($id) )
				{
					if ( ! $order->load($id) )
					{
						// Не найден
							
						return false;
					}
				}
				// Возвращаем объект
				return $order;
				break;
				
			case 'contracts_department':
				$order = new dof_im_sel_order_contracts_department($this->dof);
				if ( ! is_null($id) )
				{
					if ( ! $order->load($id) )
					{
						// Не найден
							
						return false;
					}
				}
				// Возвращаем объект
				return $order;
					break;
					
			default:
				break;
		}
		return false;
	}
	
	/**
	 * Возвращает объект отчета
	 *
	 * @param string $code
	 * @param integer  $id
	 * @return dof_storage_orders_baseorder
	 */
	public function report($code, $id = NULL)
	{
		return $this->dof->storage('reports')->report($this->type(), $this->code(), $code, $id);
	}
	
	/**
	 * Формирование блока информации по договору
	 * 
	 * @param int|stdClass $contract - Объект или ID договора
	 * @param array $addvars - GET-параметры для формирования ссылок
	 * 
	 * @return string - HTML-код блока
	 */
	public function block_contract_info($contract, $addvars = [])
	{
	    $html = '';
	    
	    // Нормализация входных данных
        if ( is_object($contract) || is_array($contract) )
        {
            $contract = (object)$contract;
        } else
        {
            $contract = $this->dof->storage('contracts')->get((int)$contract);
        }
        $addvars = (array)$addvars;

        // Валидация
        if ( empty($contract) )
        {
            return $html;
        }
        
        // Базовые параметры
        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        $person = $this->dof->storage('persons')->get_bu(null, true);
        
	    // Формирование таблицы
	    $table = new stdClass();
	    $table->data = [];
	    $table->tablealign = 'center';
	    $table->align = ['left', 'left'];
	    $table->wrap = ['',''];
	    $table->cellpadding = 5;
	    $table->cellspacing = 0;
	    $table->width = '600';
	    $table->size = ['200px','400px'];
	    
	    // Номер договора
	    $table->data[] = [$this->dof->get_string('num', 'sel'), $contract->num];
	    
	    // Дата заключения договора
	    $date = dof_userdate($contract->date, '%d.%m.%Y', $usertimezone, false);
	    $table->data[] = [$this->dof->get_string('date', 'sel'), $date];
	    
	    // Подразделение договора
	    $departname = (string)$this->dof->storage('departments')->get_field($contract->departmentid, 'name');
	    $table->data[] = [$this->dof->get_string('department', 'sel'), $departname];
	    
	    // Заметки по договору
	    $table->data[] = [$this->dof->get_string('notes', 'sel'), nl2br(htmlspecialchars($contract->notes))];
	    
	    // Персоны по договору
	    $sellerfullname = $this->dof->storage('persons')->get_fullname($contract->sellerid);
	    $table->data[] = [$this->dof->get_string('seller', 'sel'), $sellerfullname];
	    $curatorfullname = $this->dof->storage('persons')->get_fullname($contract->curatorid);
	    $table->data[] = [$this->dof->get_string('curator', 'sel'), $curatorfullname];
	    
	    // Дата регистрации договора
	    $date = dof_userdate($contract->adddate, '%d.%m.%Y %H:%M', $usertimezone, false);
	    $table->data[] = [$this->dof->get_string('adddate', 'sel'), $date];
	    
	    // Данные по метаконтракту
	    $metacontractnum = '';
	    if ( ! empty($contract->metacontractid))
	    {
	        $metacontractnum = $this->dof->storage('metacontracts')->get_field($contract->metacontractid, 'num');
	    }
	    $table->data[] = [$this->dof->get_string('metacontract', 'sel'), $metacontractnum];
	    
	    // Статус договора
	    $table->data[] = [
	        $this->dof->get_string('status', 'sel'), 
	        $this->dof->workflow('contracts')->get_name($contract->status)
	    ];
	    
	    // Меню статусов
	    $available = array_keys($this->dof->workflow('contracts')->get_available($contract->id));
	    $available = $this->dof->storage('acl')->get_usable_statuses_select(
	        'workflow',
	        'contracts',
	        $available,
	        $addvars['departmentid'],
	        $person->id,
	        $contract->id
	    );
	    
	    if ( is_array($available) AND ! empty($available) )
	    {// формируем строку для отображения меню
	        $status_menu = "<form name=\"edit_obj\" method=\"post\" class=\"form-inline\" action=\"".$this->dof->url_im('sel',"/contracts/setstatus.php", $addvars)."\">".PHP_EOL;
	        $status_menu .= "<input type=\"hidden\" name=\"id\" value=\"{$contract->id}\" />".PHP_EOL;
	        $status_menu .= '<select name="status" class="form-control form-control-sm mr-1">'.PHP_EOL;
    	    foreach ($available as $key=>$status)
    	    {
    	        $status_menu .= '<option value="'.$key.'">'.$status.'</option>'.PHP_EOL;
    	    }
    	    $status_menu .= '</select>'.PHP_EOL;
    	    $status_menu .= "<input type=\"submit\" name=\"save\" value=\"{$this->dof->get_string('save', 'sel')}\" class=\"btn btn-secondary btn-sm\" />".PHP_EOL;
	    
    	    if (isset($available['archives']) OR isset($available['cancel']) )
    	    {
    	        $status_menu .= dof_html_writer::div('', 'col-12 px-0') . PHP_EOL;
    	        $inputattrs = [
    	            'type' => 'checkbox',
    	            'name' => 'muserkeep',
    	            'id' => 'muserkeep',
    	            'value' => '1',
    	            'class' => 'form-check-input'
    	        ];
    	        $input = dof_html_writer::empty_tag('input', $inputattrs);
    	        $text = $this->dof->get_string('keepuserwhenarchives', 'sel');
    	        $label = dof_html_writer::tag('label', $text, ['for' => 'muserkeep', 'class' => 'form-check-label']);
    	        $check = dof_html_writer::div($input . $label, 'form-check');
    	        $group = dof_html_writer::div($check, 'form-group');
    	        $status_menu .= $group.PHP_EOL;
    	    }
	        $status_menu .= '</form>'.PHP_EOL;
	       
	        $table->data[] = [$this->dof->get_string('setstatus', 'sel'), $status_menu];
	    }
	    
	    return $this->dof->modlib('widgets')->print_table($table, true);
	}
}
?>