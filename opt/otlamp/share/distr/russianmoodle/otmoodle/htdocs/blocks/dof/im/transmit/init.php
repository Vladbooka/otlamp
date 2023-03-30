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

/**
 * Интерфейс управления обменом данными
 *
 * @package    im
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_im_transmit implements dof_plugin_im
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
    
    /** Конструктор
     * @param dof_control $dof - идентификатор действия, которое должно быть совершено
     * @access public
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
    
    public function install()
    {
        return true;
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
        return true;
    }
    
    /** Метод, реализующий удаление плагина в системе  
	 * @return bool
	 */
	public function uninstall()
	{
		return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),array());
	}
	
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        return 2019090300;
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
        return 'transmit';
    }
    
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return [
                'modlib' => [
                        'nvg' => 2008060300,
                        'widgets' => 2009050800
                ],
                'storage' => [
                        'persons' => 2017000000,
                        'acl' => 2011041800,
                        'logs' => 2017070300
                ]
        ];
    }
    
    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
       return [];
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
        if ( $this->dof->is_access('datamanage') || $this->dof->is_access('admin') || $this->dof->is_access('manage') )
        {// манагеру можно все
            return true;
        }  
        // получаем id пользователя в persons
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        // получаем все нужные параметры для функции проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid);   
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
            $notice = "{$this->code()}/{$do} (block/dof/{$this->type()}/{$this->code()}: {$do})";
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
        return true;
    }
    
    /**
     * Получить настройки для плагина
     *
     * @param string $code
     *
     * @return object[]
     */
    public function config_default($code = NULL)
    {
        $config = [];
        
        // Лимит обрабатываемых записей в режиме симуляции
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'simulation_limit';
        $obj->value = '0';
        $config[$obj->code] = $obj;
    
        return $config;
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

    // **********************************************
    // Методы, предусмотренные интерфейсом im
    // **********************************************
    /** Возвращает текст для отображения в блоке на странице dof
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string - html-код содержимого блока
     */
    function get_block($name, $id = 1)
    {
        return '';
    }
    /** Возвращает html-код, который отображается внутри секции
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string  - html-код содержимого секции секции
     */
    function get_section($name, $id = 0)
    {
        return '';
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
        if ( is_null($depid) )
        {// подразделение не задано - берем текущее
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        $result->objectid     = $objectid;
        if ( ! $objectid )
        {// если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
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
     * Сформировать вкладки перехода между интрефейсами
     *
     * @param string $tabname - Название вкладки
     * @param array $addvars - Массив GET-параметорв
     *
     * @return string - HTML-код вкладок
     */
    public function render_tabs($tabname, $addvars = [])
    {
        // Вкладки
        $tabs = [];
        
        // Главная страница
        $link = $this->dof->url_im($this->code(), '/index.php', $addvars);
        $text = $this->dof->get_string('tab_main', $this->code());
        $tabs[] = $this->dof->modlib('widgets')->create_tab('main', $link, $text, null, false);
        
        // Импорт данных
        $link = $this->dof->url_im($this->code(), '/import/index.php', $addvars);
        $text = $this->dof->get_string('tab_import', $this->code());
        $tabs[] = $this->dof->modlib('widgets')->create_tab('import', $link, $text, null, false);
        
        // Экспорт данных
        $link = $this->dof->url_im($this->code(), '/export/index.php', $addvars);
        $text = $this->dof->get_string('tab_export', $this->code());
        $tabs[] = $this->dof->modlib('widgets')->create_tab('export', $link, $text, null, false);
        
        // Список сохраненных пакетов настроек
        if ( $this->dof->is_access('admin') )
        {
            $link = $this->dof->url_im($this->code(), '/pack/index.php', $addvars);
            $text = $this->dof->get_string('tab_pack', $this->code());
            $tabs[] = $this->dof->modlib('widgets')->create_tab('pack', $link, $text, null, false);
        }
        
        // Формирование блока вкладок
        return $this->dof->modlib('widgets')->print_tabs($tabs, $tabname, null, null, true);
    }
    
    public function display_pack($packrecord, $options=[])
    {
        $html = '';
        
        if ( !isset($packrecord->id) || !$this->dof->is_access('admin') )
        {
            return '';
        }
        
        /**
         * @var dof_modlib_transmit_pack $pack
         */
        $pack = $this->dof->modlib('transmit')->get_pack($packrecord);
        if ($pack!==false)
        {
            // Тип импорта
            $typeclass = ' '.$pack->get_transmit_type();
            // Активен ли пакет
            $activeclass = $pack->is_active() ? ' active' : '';
            
            // Наименование пакета
            $packname = dof_html_writer::div($pack->get_name(), 'packname');
            // Описание пакета
            $packdescription= dof_html_writer::div($pack->get_description(), 'packdescription');
            // Сводная информация по пакету
            $packsummary = dof_html_writer::div($packname . $packdescription, 'packsummary');
            
            
            // TODO Добавить проверку прав!!!
            
            // Принудительный запуск пакета на исполнение (прямо сейчас)
            $packtoolexecute = '';
            // Остановить периодическое исполнение пакета
            $packtoolsuspend = '';
            // Добавить в список периодически исполняемых пакетов
            $packtoolactivate = '';
            // Удалить пакет
            $packtooldelete = '';
            
            if ( $this->dof->is_access('admin') )
            {
                $packtoolexecute = dof_html_writer::div(
                    $this->dof->get_string('pack_tool_execute', 'transmit'), 
                    'packtool packtoolexecute',
                    ['data-action' => 'execute']);
            }
            if ( $this->dof->is_access('admin') )
            {// Есть права управлять статусами пакетов
                
                if ($pack->is_available_status('suspended'))
                {
                    $packtoolsuspend = dof_html_writer::div(
                        $this->dof->get_string('pack_tool_suspend', 'transmit'),
                        'packtool packtoolsuspend',
                        ['data-action' => 'suspend']);
                }
                
                if ($pack->is_available_status('active'))
                {
                    $packtoolactivate = dof_html_writer::div(
                        $this->dof->get_string('pack_tool_activate', 'transmit'), 
                        'packtool packtoolactivate',
                        ['data-action' => 'activate']);
                }
                
                if ($pack->is_available_status('deleted'))
                {
                    $packtooldelete = dof_html_writer::div(
                        $this->dof->get_string('pack_tool_delete', 'transmit'), 
                        'packtool packtooldelete',
                        ['data-action' => 'delete']);
                }
            }
            
            // Инструменты для управления пакетом
            $packtools = dof_html_writer::div(
                $packtoolexecute . $packtoolactivate . $packtoolsuspend . $packtooldelete, 'packtools');
            $packtoolswrapper = dof_html_writer::div($packtools, 'packtools-wrapper', [
                'data-label' => $this->dof->get_string('pack_tools', 'transmit')
            ]);
            
            // HMTL-код для отображения пакета
            $html .= dof_html_writer::div($packsummary . $packtoolswrapper,
                'pack' . $typeclass . $activeclass, [
                    'id' => 'pack_' . $pack->get_id(),
                    'data-id' => $pack->get_id()
                ]);
        }
        
        if (!empty($options['returnhtml']))
        {
            return $html;
        } else
        {
            echo $html;
        }
    }
    
    public function display_pack_list($statuses=[], $options=[])
    {
        $html = dof_html_writer::div($this->dof->get_string('transmitpacks_description', 'transmit'), 
            'transmitpacks-description');
        $packshtml = [];
        
        
        if ( $this->dof->is_access('admin') )
        {
            $conditions = [];
            if (!empty($statuses))
            {
                $conditions['status'] = $statuses;
            }
            
            $packrecords = $this->dof->storage('transmitpacks')->get_records($conditions, '-sortorder DESC');
            if (!empty($packrecords))
            {
                foreach($packrecords as $packrecord)
                {
                    $packshtml[] = $this->display_pack($packrecord, ['returnhtml' => true]);
                }
            }
        }
        
        if (empty($packshtml))
        {
            $html .=  dof_html_writer::div($this->dof->get_string('transmitpacks_not_found', 'transmit'),
                'transmitpacks-not-found');
        } else
        {
            $html .= implode('', $packshtml);
        }
        
        if (!empty($options['returnhtml']))
        {
            return dof_html_writer::div($html, 'transmitpacks');
        } else
        {
            echo dof_html_writer::div($html, 'transmitpacks');
        }
    }
}