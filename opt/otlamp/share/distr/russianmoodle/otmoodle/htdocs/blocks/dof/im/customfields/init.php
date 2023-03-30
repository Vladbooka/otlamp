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
 * Класс интерфейса кастомных полей.
 *
 */
class dof_im_customfields implements dof_plugin_im
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
        return 2017082100;
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
        return 'customfields';
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
                'nvg'          => 2017072500,
                'widgets'      => 2017052100,
                'formbuilder'  => 2017042800
            ],
            'storage' => [
                'customfields' => 2017031600,
                'cov'          => 2014032000,
                'departments'  => 2009040800,
                'acl'          => 2016071500,
                'persons'      => 2017070500
            ],
            'workflow' => [
                'customfields' => 2017012400
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
        return [
            'storage' => [
                'acl' => 2016071500
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
        return [];
    }
    
    /**
     * Требуется ли запуск cron в плагине
     *
     * @return bool
     */
    public function is_cron()
    {
        // Не требуется запуск
        return false;
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
    public function is_access($do, $objid = null, $userid = null)
    {
        if( $this->dof->is_access('datamanage') OR
            $this->dof->is_access('admin') OR
            $this->dof->is_access('manage')
        )
        {// Открыть доступ для администраторов
            return true;
        }
        
        // Получаем ID персоны, с которой связан данный пользователь
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        
        $depid = null;
        
        switch($do)
        {
            default:
                break;
        }
        
        // Формируем параметры для проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid, $depid);
        
        // Производим проверку
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// Право есть
            return true;
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
            $notice = "customfields/{$do} (block/dof/im/customfields: {$do})";
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
    public function catch_event($gentype,$gencode,$eventcode,$intvar,$mixedvar)
    {
        return true;
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
    public function cron($loan,$messages)
    {
        $result = true;
        return $result;
    }
    
    /**
     * Получить настройки для плагина
     *
     * @param unknown $code
     * @return object[]
     */
    public function config_default($code = null)
    {
        $config = [];
        return $config;
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
    
    // **********************************************
    // Методы, предусмотренные интерфейсом im
    // **********************************************
    
    /**
     * Возвращает текст для отображения в блоке на странице dof
    *
    * @param string $name - Название набора текстов для отображания
    * @param array $options - Дополнительный параметры
    *
    * @return string - HTML-код содержимого блока
    */
    public function get_block($name, $id = NULL, $options = [] )
    {
        $html = '';
        switch($name)
        {
            case 'page_main_name':
                $html = "<a href='{$this->dof->url_im('customfields','/index.php')}'>"
                    .$this->dof->get_string('page_main_name')."</a>";
            default:
                break;
        }
        return $html;
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
        return '';
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
     * Задаем права доступа для объектов
     *
     * @return array
     */
    public function acldefault()
    {
        $a = [];
        
        $a['view'] = [
            // Право управлять кастомными полями
            'roles' => [
                'manager'
            ]
        ];
        
        $a['create'] = [
            // Право создавать кастомные поля
            'roles' => [
                'manager'
            ]
        ];
        
        $a['edit'] = [
            // Право редактировать кастомные поля
            'roles' => [
                'manager'
            ]
        ];
        
        $a['delete'] = [
            // Право удалять кастомные поля
            'roles' => [
                'manager'
            ]
        ];
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
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    
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
        
        // Список дополнительных полей
        $link = $this->dof->url_im($this->code(), '/list.php', $addvars);
        $text = $this->dof->get_string('tab_list', $this->code());
        $tabs[] = $this->dof->modlib('widgets')->create_tab('list', $link, $text, null, false);

        // Формирование блока вкладок
        return $this->dof->modlib('widgets')->print_tabs($tabs, $tabname, null, null, true);
    }
    
    /**
     * Формирует таблицу со списком кастомных полей для отображения
     * @param int $departmentid ID подразделения
     * @return string таблица со списком кастомных полей
     */
    public function customfields_list($departmentid)
    {
        $customfields = [];
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->align = ["left","left"];
        $customfields = $this->dof->storage('customfields')->get_customfields($departmentid);
        if( ! empty($customfields) )
        {
            $table->head = $this->get_head_table();
            foreach($customfields as $customfield)
            {
                $table->data[] = $this->get_string_table($customfield);
            }
        }
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    
    /**
     * Формирует строку таблицы кастомных полей
     * @param stdClass $obj объект кастомного поля
     * @return array массив ячеек таблицы
     */
    private function get_string_table($obj)
    {
        $actions = '';
        $customfieldname = '';
        $editicon = $this->dof->modlib('ig')->icon('edit');
        $deleteicon = $this->dof->modlib('ig')->icon('delete');
        if( $this->is_access('edit') )
        {// Есть право на редактирование - добавим кнопку
            $url = $this->url('/edit.php', [
                'departmentid' => $obj->departmentid,
                'id' => $obj->id
            ]);
            $actions .= dof_html_writer::link($url, $editicon);
        }
        if( $this->is_access('delete') )
        {// Есть право на удаление - добавим кнопку
            $url = $this->url('/delete.php', [
                'departmentid' => $obj->departmentid,
                'id' => $obj->id,
                'step' => 'confirm',
                'sesskey' => sesskey()
            ]);
            $actions .= dof_html_writer::link($url, $deleteicon);
        }
        
        $customfieldname .= $obj->name;
        
        return [$actions, $customfieldname];
    }
    
    /**
     * ФОрмирует шапку таблицы кастомных полей
     * @return array массив ячеек заголовков таблицы
     */
    private function get_head_table()
    {
        return [
            $this->dof->get_string('actions', 'customfields'),
            $this->dof->get_string('customfieldname', 'customfields')
        ];
    }
}