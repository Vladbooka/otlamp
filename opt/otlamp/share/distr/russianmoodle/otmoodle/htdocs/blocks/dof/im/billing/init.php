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

class dof_im_billing implements dof_plugin_im
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
     * @param string $oldversion - версия установленного в системе плагина
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
        return 2014120000;
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
        return 'billing';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('modlib'=>array('nvg'     => 2008060300,
                                     'widgets' => 2009050800,
                                     'billing' => 2014110000
                                     ),
                     'storage'=>array('accounts'   => 2014110000,
                                      'accentryes' => 2014110000
                                     )
                    );
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
     * и без которых начать установку невозможно
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
                  'plugincode' => 'sel',
                  'eventcode'  => 'contractdata'),    
               
            array('plugintype' => 'im',
                  'plugincode' => 'persons',
                  'eventcode'  => 'persondata')
            );
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
     * @param int $userid - идентификатор пользователя в Moodle, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     * @access public
     */
    public function is_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
        // Делаем регрес к правам библиотеки биллинга
        return $this->dof->modlib('billing')->is_access($do, $objid, $userid, $depid);

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
        // Используем функционал из $DOFFICE
        //return $this->dof->require_access($do, NULL, $userid);
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $this->dof->modlib('nvg')->print_header(NVG_MODE_PORTAL);
            $notice = "billing/{$do} (block/dof/im/billing: {$do})";
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
        $result = '';
        require_once($this->dof->plugin_path('im', 'billing', '/lib.php') );
        if ( $gentype == 'im' AND $gencode == 'sel' AND $eventcode == 'contractdata' )
        {
            return $this->get_section('contractdata', $intvar);
        }
        if ( $gentype == 'im' AND $gencode == 'persons' AND $eventcode == 'persondata' )
        {
            return $this->get_section('persondata', $intvar);
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
     * @param string $name - название блока для отображения
     * @param int $id - id объекта в зависимости от запроса
     *                  Если $name = contractdata - id договора
     *                  Если $name = persondata - id пользователя
     * @return string  - html-код содержимого секции
     */
    function get_section($name, $id = 0)
    {
        switch ($name)
        {//выбираем содержание
            case 'contractdata': return $this->get_contractdata($id); break;
            case 'persondata': return $this->get_persondata($id); break;
            default: return false;
        }
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
     * @param int $personid
     */
    protected function get_access_parametrs($action, $objectid, $personid)
    {
        $result = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->personid     = $personid;
        $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
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
                              $acldata->personid, $acldata->departmentid, $acldata->objectid);
    }    

    /** Задаем права доступа для объектов этого хранилища
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = array();
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
     * Печать таблицы с краткой информацией по договору
     * @param object $information - Объект с информацией по договору
     * @param number $depid
     */
    public function get_contract_info($information, $depid = 0)
    {
        // Готовим таблицу прошедших операций
        $table = new stdClass();
        
        // Задаем свойства таблицы
        $table->cellpadding = 5;
        $table->cellspacing = 0;
        $table->width = 'auto';
        $table->data = array();
        
        // Заполняем таблицу
        $table->data[] = array( $this->dof->get_string('account_name', 'billing'), $information->account->name);
        $table->data[] = array( $this->dof->get_string('account_id', 'billing'), $information->account->id);
        $table->data[] = array( $this->dof->get_string('now_balance', 'billing'),
                                '<b>'.round( $information->nowbalance, 2).
                                $this->dof->get_string('rub', 'billing').'</b>' );
        
        // Если список операций не пуст, добавляем информацию о последней операции
        if ( ! empty($information->nowentry) )
        {
            $table->data[] = array( '<b>'.$this->dof->get_string('nowentry', 'billing').'</b>','');
            $table->data[] = array( $this->dof->get_string('nowentry_date', 'billing'),
                                date('d-m-Y', $information->nowentry->date) 
            );
            
            // Готовим параметры для передачи по ссылке
            $addvars['cid'] = $information->contract->id;
            $addvars['aid'] = $information->nowentry->id;
            $addvars['departmentid'] = $depid;
            
            $table->data[] = array('', 
                    '<a href="'.$this->dof->url_im('billing', '/accentry_detail.php', $addvars).'" 
                        title="'.$this->dof->get_string('go_to_accentry', 'billing').'">'.
                        $this->dof->get_string('go_to_accentry', 'billing').
                    '</a>'
            );
        }
        
        // Печатаем таблицу
        echo html_writer::start_tag('div', array('style' => 'float: right;'));
        $this->dof->modlib('widgets')->print_table($table);
        echo html_writer::end_tag('div');
        echo html_writer::tag('div', '', array('style' => 'clear: both;'));
    }
    
    /**
     * Печать таблицы с операциями по договору
     * @param object $information - Объект с информацией по договору
     * @param number $depid
     */
    public function get_contract_history_table($information, $depid = 0)
    {
        // Готовим таблицу прошедших операций
        $tablep = new stdClass();
        // Задаем свойства таблицы
        $tablep->tablealign = "center";
        $tablep->align = array("center", "center", "center", "center", "center", "center", "center", "center");
        $tablep->cellpadding = 5;
        $tablep->cellspacing = 0;
        $tablep->width = '100%';
        $tablep->size = array('2%', '3%','10%','10%','10%','10%','20%','20%','15%');
        // Добавляем шапку
        $labels['actions'] = $this->dof->get_string('accentry_actions', 'billing');
        $labels['id'] = $this->dof->get_string('accentry_id', 'billing');
        $labels['fromid'] = $this->dof->get_string('accentry_fromid', 'billing');
        $labels['toid'] = $this->dof->get_string('accentry_toid', 'billing');
        $labels['task'] = $this->dof->get_string('accentry_task', 'billing');
        $labels['amount'] = $this->dof->get_string('accentry_amount', 'billing');
        $labels['date'] = $this->dof->get_string('accentry_date', 'billing');
        $labels['balance'] = $this->dof->get_string('accentry_balance', 'billing');
        $labels['status'] = $this->dof->get_string('accentry_status', 'billing');
        $tablep->head = $labels;
        
        $tablep->data = array();
        
        // Готовим таблицу запланированных операций
        $tablef = new stdClass();
        // Задаем свойства таблицы
        $tablef->tablealign = "center";
        $tablef->align = array("center", "center", "center", "center", "center", "center", "center", "center");
        $tablef->cellpadding = 5;
        $tablef->cellspacing = 0;
        $tablef->width = '100%';
        $tablef->size = array('2%','3%','10%','10%','10%','10%','20%','20%','15%');

        $tablef->head = $labels;
        // Добавляем строки
        
        $tablef->data = array();
        
        // Готовим параметры для передачи по ссылке
        $addvars['cid'] = $information->contract->id;
        $addvars['departmentid'] = $depid;
       
        // Проверяем права для кнопки отмены операции
        $iswriteof = $this->is_access('create:billinwriteof');
        $isrefill = $this->is_access('create:billinrefill');
        
        // Получаем активные статусы для визуального разделения
        $activestatuses = $this->dof->workflow('accentryes')->get_meta_list('active');
        
        foreach ( $information->history as $item )
        {
            // Готовим параметры для передачи по ссылке
            $addvars['aid'] = $item->id;
            
            // Проверяем, является ли операция активной
            if ( array_key_exists($item->status, $activestatuses) )
            {// Статус активный
                $isactive = true;
            } else 
            {// Статус не активный
                $isactive = false;
            }
            
            // Получаем html код блока с допустимыми действиями для операции
            $actions = $this->get_accentry_actions($item, $addvars);
            
            // Получаем тип опреации
            $atype = $this->get_accentry_type($item, $information->account);

            $itemstatus = $this->dof->workflow('accentryes')->get_name($item->status);
            // Добавляем строку либо в таблицу проведенных операций, 
            //                  либо в таблицу запланированных
            if ( $isactive )
            {
                $style = '';
            } else 
            {
                $style = 'color: #aaa';
            }
            
            // Формируем данные таблиц
            if ( $item->date <= (integer)date('U') )
            {
                
                $tablep->data[] = array(
                        $actions,
                        '<a href="'.$this->dof->url_im('billing', '/accentry_detail.php', $addvars).'"
                            style="'.$style.'".
                            title="'.$this->dof->get_string('go_to_accentry', 'billing').'">'.
                            '['.$item->id.']'.
                        '</a>',
                        '<span style="'.$style.'">'.$item->fromid.'<span>',
                        '<span style="'.$style.'">'.$item->toid.'<span>',
                        '<span style="'.$style.'">'.$atype.'<span>',
                        '<span style="'.$style.'">'.round( $item->amount, 2).'<span>',
                        '<span style="'.$style.'">'.date('d-m-Y', $item->date).'<span>',
                        '<span style="'.$style.'">'.round($item->balance, 2).'<span>',
                        '<span style="'.$style.'">'.$itemstatus.'<span>'
                );
            } else 
            {
                $tablef->data[] = array( 
                        $actions,
                        '<a href="'.$this->dof->url_im('billing', '/accentry_detail.php', $addvars).'"
                            style="'.$style.'".
                            title="'.$this->dof->get_string('go_to_accentry', 'billing').'">'.
                            '['.$item->id.']'.
                        '</a>',
                        '<span style="'.$style.'">'.$item->fromid.'<span>',
                        '<span style="'.$style.'">'.$item->toid.'<span>',
                        '<span style="'.$style.'">'.$atype.'<span>',
                        '<span style="'.$style.'">'.round( $item->amount, 2).'<span>',
                        '<span style="'.$style.'">'.date('d-m-Y', $item->date).'<span>',
                        '<span style="'.$style.'">'.round($item->balance, 2).'<span>',
                        '<span style="'.$style.'">'.$itemstatus.'<span>'
                );
            }

        }
        
        // Прошедшие операции
        echo '<b>'.$this->dof->get_string('past_operations', 'billing').'</b>';
        
        $this->dof->modlib('widgets')->print_table($tablep);
        
        // Прошедшие операции
        echo '<br /><b>'.$this->dof->get_string('future_operations', 'billing').'</b>';
        
        $this->dof->modlib('widgets')->print_table($tablef);
    }
    
    /**
     * Печать таблицы с информацией по операции
     * 
     * @param object $accentry - Объект операции по счету
     * @param int $contractid - id контракта, по счету которого проходит операция
     */
    public function get_accentry_detail_table($accentry, $contractid, $depid)
    {
        $addvars['departmentid'] = $depid;
        $addvars['aid'] = $accentry->id;
        $addvars['cid'] = $contractid;
        $addvars['task'] = 'cancel';
        
        // Получаем активные статусы для визуального разделения
        $activestatuses = $this->dof->workflow('accentryes')->get_meta_list('active');
        
        // Проверяем, является ли операция активной
        if ( array_key_exists($accentry->status, $activestatuses) )
        {// Статус активный
            $isactive = true;
        } else 
        {// Статус не активный
            $isactive = false;
        }
        
        // Получаем id главного счета
        $mainaccentryid = $this->dof->modlib('billing')->get_main_account_id();
        
        if ( $accentry->fromid === $mainaccentryid )
        {// Пополнение
            if ( $this->is_access('create:billinrefill') && $isactive && $accentry->amount >= 0 )
            {
               $actions =
                    '<a href="'.$this->dof->url_im('billing', '/process_form.php?task=cancel', $addvars).'"
                    title="'.$this->dof->get_string('accentry_reject', 'billing').'">'.
                    $this->dof->get_string('accentry_reject', 'billing').
                    '</a>';
            } else
            {
                $actions = '';
            }
        } else 
        {// Списание
            // Проверяем, показывать ли кнопку отмены операции или нет
            if ( $this->is_access('create:billinwriteof') && $isactive && $accentry->amount >= 0 )
            {
                $actions =
                '<a href="'.$this->dof->url_im('billing', '/process_form.php?task=cancel', $addvars).'"
                    title="'.$this->dof->get_string('accentry_reject', 'billing').'">'.
                    $this->dof->get_string('accentry_reject', 'billing').
                '</a>';
            } else
            {
                $actions = '';
            }
        }

        // Готовим таблицу операции
        $table = new stdClass();
        
        // Задаем свойства таблицы
        $table->tablealign = "center";
        $table->align = array("left", "center");
        $table->cellpadding = 5;
        $table->cellspacing = 0;
        $table->width = '600px';
        $table->size = array('40%','60%');
    
        $table->data = array();
        $table->data[] = array(
                $this->dof->get_string('accentry_actions', 'billing'),
                $actions
        );
        $table->data[] = array(
                $this->dof->get_string('accentry_id', 'billing'),
                $accentry->id
        );
        $table->data[] = array(
                $this->dof->get_string('accentry_fromid', 'billing'),
                $accentry->fromid
        );
        $table->data[] = array(
                $this->dof->get_string('accentry_toid', 'billing'),
                $accentry->toid
        );
        $table->data[] = array(
                $this->dof->get_string('accentry_amount', 'billing'),
                round($accentry->amount, 2).$this->dof->get_string('rub', 'billing'),
        );
        $table->data[] = array(
                $this->dof->get_string('accentry_createdate', 'billing'),
                date('d-m-Y', $accentry->createdate)
        );
        $table->data[] = array(
                $this->dof->get_string('accentry_date', 'billing'),
                date('d-m-Y', $accentry->date)
        );
        $table->data[] = array(
                $this->dof->get_string('accentry_extentryopts', 'billing'),
                $this->options_to_html(unserialize($accentry->extentryopts))
        );
        $table->data[] = array(
                $this->dof->get_string('accentry_about', 'billing'),
                $accentry->about
        );
        $itemstatus =  $this->dof->workflow('accentryes')->get_name($accentry->status);
        $table->data[] = array(
                $this->dof->get_string('accentry_status', 'billing'),
                $itemstatus
        );
   
        $this->dof->modlib('widgets')->print_table($table);
    }
    
    /**
     * Печать таблицы с информацией по операции
     * @param int $orderid - id приказа
     * @param string $class - класс приказа
     * @param int $depid
     */
    public function get_accentry_order_detail_table($orderid, $class, $depid)
    {
        $order = $this->dof->modlib('billing')->order($class, $orderid);
        
       
        if ( ! is_object($order) )
        {
            return false;
        }
        // Готовим таблицу приказа
        $table = new stdClass();
        // Задаем свойства таблицы
        $table->tablealign = "center";
        $table->align = array("left", "center");
        $table->cellpadding = 5;
        $table->cellspacing = 0;
        $table->width = '600px';
        $table->size = array('40%','60%');
    
        $table->data = array();
        
        $table->data[] = array(
                $this->dof->get_string('order_id', 'billing'),
                $order->id
        );
        $person = $this->dof->storage('persons')->get_fullname_initials($order->ownerid);
        $table->data[] = array(
                $this->dof->get_string('order_ownerid', 'billing'),
                $person
        );
        $person = $this->dof->storage('persons')->get_fullname_initials($order->signerid);
        $table->data[] = array(
                $this->dof->get_string('order_signerid', 'billing'),
                $person
        );
        $table->data[] = array(
                $this->dof->get_string('order_date', 'billing'),
                date('d-m-Y', $order->date)
        );
        $table->data[] = array(
                $this->dof->get_string('order_signdate', 'billing'),
                date('d-m-Y', $order->signdate)
        );
        $table->data[] = array(
                $this->dof->get_string('order_exdate', 'billing'),
                date('d-m-Y', $order->exdate)
        );
        $ostatus = $this->dof->workflow('orders')->get_name($order->status);
        $table->data[] = array(
                $this->dof->get_string('order_status', 'billing'),
                $ostatus
        );
        $table->data[] = array(
                $this->dof->get_string('order_notes', 'billing'),
                $order->notes
        );
        
        $this->dof->modlib('widgets')->print_table($table);
    }
    
    
    /**
     * Печать таблицы истории по договору
     * 
     * @param int $id - id договора
     */
    private function get_contractdata($id)
    {
        // Добавляем lib.php, виджет должен быть "автономным"
        require_once('lib.php');
        $depid = $this->dof->storage('departments')->get_user_default_department();
        // Проверяем права доступа
        if ( $this->is_access('view:billing/my', $id, null, $depid) )
        {
            // Рисуем таблицу
            $table = new stdClass();
            $table->tablealign = "center";
            $table->align = array ("left","left");
            $table->wrap = array ("nowrap","");
            $table->cellpadding = 5;
            $table->cellspacing = 0;
            $table->width = '600';
            $table->size = array('200px','400px');
        
            $table->data = array();
        
            // Готовим параметры для передачи с ссылкой
            $addvars['id'] = $id;
            $addvars['departmentid'] = $this->dof->storage('departments')->get_user_default_department();
            
            // Получение баланса по договору на текущий день
            $balance = $this->dof->modlib('billing')->get_contract_balance($id, (integer)date('U'));
            
            $table->data[] = array( $this->dof->get_string('contract_balance', 'billing'), round( $balance, 2).$this->dof->get_string('rub', 'billing'));
        
            // Ссылка на детализацию
            $link = html_writer::link($this->dof->url_im('billing','/contract_detail.php',$addvars), $this->dof->get_string('contract_detail','billing'));
            $table->data[] = array('', $link);
        
            $this->dof->modlib('widgets')->print_table($table);
        }
    }
    
    /**
     * Печать таблицы договоров для конкретной персоны
     * @param int $id - id персоны
     */
    private function get_persondata($id)
    {
        // Добавляем lib.php, виджет должен быть "автономным"
        require_once('lib.php');
        
        // Готовим параметры для передачи с ссылкой
        $addvars['departmentid'] = $this->dof->storage('departments')->get_user_default_department();
        
        // Получить контракты для данной персоны
        $contracts = $this->dof->storage('contracts')->get_contracts_for_person($id);

        // Таблица Договоров
        if ( ! empty($contracts) )
        {
            echo $this->dof->modlib('widgets')->print_heading(
                    $this->dof->get_string('contract_balance', 'billing'), '', 2, 'main', true);
            // Рисуем таблицу
            $table = new stdClass();
            $table->tablealign = "center";
            $table->align = array ("left","left","left","left","left","left",);
            $table->cellpadding = 5;
            $table->cellspacing = 0;
            $table->width = '80%';
            
            $table->data = array();
            
            // Получаем заголовок
            $table->head = array(
                    $this->dof->get_string('contract_num', 'billing'),
                    $this->dof->get_string('contract_signdate', 'billing'),
                    $this->dof->get_string('contract_status', 'billing'),
                    $this->dof->get_string('account_balance', 'billing')
            );
            foreach ( $contracts as $item )
            {
            
                $account = $this->dof->modlib('billing')->get_contract_account($item->id);
                
                $addvars['id'] = $item->id;
                if ( $this->is_access('view:billing/my', $item->id, null,  $addvars['departmentid']) )
                {
                    // Получение баланса по договору на текущий день
                    $balance = $this->dof->modlib('billing')->get_contract_balance($item->id, (integer)date('U'));
                    
                    
                    $cstatus = $this->dof->workflow('contracts')->get_name($item->status);
                    // Ссылка на детализацию по договору
                    $link = html_writer::link(
                        $this->dof->url_im('billing','/contract_detail.php',$addvars), 
                        $balance.$this->dof->get_string('rub', 'billing')
                    );
                    $table->data[] = array(
                        $item->num,
                        date('d-m-Y', $item->date),
                        $cstatus,
                        $link
                    );
                }
            }
            $this->dof->modlib('widgets')->print_table($table);
        }
    }
    
    /**
     * Получить поле типа опреации
     * 
     * @param object $accentry - Объект операции
     * @param object $account - Объект счета
     * @return string - тип опреации
     */
    private function get_accentry_type( $accentry , $account)
    {
        
        if ( $accentry->amount < 0 )
        {// Возврат средств
            return $this->dof->get_string('accentry_reject_operation', 'billing');
        }
        if ( $accentry->toid === $account->id)
        {// Списание
            return $this->dof->get_string('accentry_refill', 'billing');
        }
        if ( $accentry->fromid === $account->id)
        {// Пополнение
            return $this->dof->get_string('accentry_writeof', 'billing');
        }
    }
    
    /**
     * Получить поле ссылок на возможные действия над операцией
     * 
     * @param object $accentry - Объект операции
     * @param object $addvars - Параметры для ссылок
     * @return string
     */
    private function get_accentry_actions( $accentry , $addvars)
    {
        // Получаем активные статусы 
        $activestatuses = $this->dof->workflow('accentryes')->get_meta_list('active');
        
        // В зависимости от условий возвращаем действия
        if ( array_key_exists($accentry->status, $activestatuses) && $accentry->amount >= 0 )
        {
            $actions =
            '<a href="'.$this->dof->url_im('billing', '/process_form.php?task=cancel', $addvars).'"
                title="'.$this->dof->get_string('accentry_reject', 'billing').'">'.
                         $this->dof->get_string('accentry_reject', 'billing').
            '</a>';
            
            return $actions;
        } else 
        {
            return '';
        }
    }
    
    /**
     * Рекурсивный метод формирования параметров объекта
     *
     * Предназначен для вывода html кода параметров операции
     * в удобночитаемом виде
     *
     * @param array|string $options - либо массив параметров операции, либо один параметр
     * @return $html - список опций
     */
    private function options_to_html($options = null)
    {
        // Если передана строка - возвращаем ее
        if ( is_string($options) )
        {
            return $options;
        }
        
        // Формируем блок для вывода данных
        $html = html_writer::start_tag('ul');
        
        // Если опции пусты - возвращаем пустую строку
        if ( empty($options) )
        {
            return '';
        }
        
        // Начинаем рекурсивную печать
        foreach ($options as $key => $value)
        {// Для каждого элемента массива
            if ( is_array($value) || is_object($value) )
            { // Если элемент - массив или объект
                $html .= html_writer::tag(
                        'li', 
                        $this->dof->get_string('title_'.$key, 'billing').': '.$this->options_to_html($value)
                        );
            } else
            {// Если элемент - строка или число
                $html .= html_writer::tag(
                        'li', 
                        $this->dof->get_string('title_'.$key, 'billing').': '.$value
                        );
            }
        }
        // Завершаем блок
        $html .= html_writer::end_tag('ul');
        // Возвращаем html код
        return $html;
    }
}
?>