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
 * Интерфейс история статусов
 *
 * @package    im
 * 
 * @package    statushistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_im_statushistory implements dof_plugin_im
{
    /**
     * Объект деканата для доступа к общим методам
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
        // Обновим права доступа
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());  
    }
    
    /**
     * Возвращает версию установленного плагина
     * 
     * @return int - Версия плагина
     */
    public function version()
    {
		return 2019042300;
    }
    
    /** 
     * Возвращает версии интерфейса Деканата, с которыми этот плагин может работать
     * 
     * @return string
     */
    public function compat_dof()
    {
        return 'aquarium';
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
        return 'statushistory';
    }
    
    /** 
     * Возвращает список плагинов, без которых этот плагин работать не может
     * 
     * @return array
     */
    public function need_plugins()
    {
        return [
                'storage' => [
                    'statushistory'    => 2019042300
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
       // Запуск не требуется
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
    public function is_access($do, $objid = NULL, $userid = NULL, $depid = null)
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
    public function require_access($do, $objid = NULL, $userid = NULL)
    {
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $notice = "statushistory/{$do} (block/dof/im/statushistory: {$do})";
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
        return false;
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
        return true;
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
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * 
     * @return string - html-код содержимого блока
     */
    public function get_block($name, $id = 1)
    {
        return '';
    }
    
    /** 
     * Возвращает html-код, который отображается внутри секции
     * 
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * 
     * @return string  - html-код содержимого секции секции
     */
    public function get_section($name, $id = 1)
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
     * Задаем права доступа для объектов этого хранилища
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = [];
        
        $a['view']   = ['roles' => [
            'manager',
            'methodist'
        ]];
        
        return $a;
    }
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /** 
     * Получить URL к собственным файлам плагина
     * 
     * @param string $adds[optional] - фрагмент пути внутри папки плагина
     *                                 начинается с /. Например '/index.php'
     * @param array $vars[optional] - параметры, передаваемые вместе с url
     * 
     * @return string - путь к папке с плагином 
     */
    public function url($adds='', $vars=[])
    {
        return $this->dof->url_im($this->code(), $adds, $vars);
    }
    
    /**
     * создает таблицу html
     * 
     * @param object $data
     * @param object $addvars
     * @return string html
     */
    public function make_table($data, $addvars) {
        
        $table = new html_table();
        $table->attributes = [
            'width' => '100%',
            'cellpadding' => '0',
            'border' => '0'
        ];
        if ( ! empty($data) )
        {
            $row = 0;
            // заголовок таблицы
            $sort = $addvars['sort'];
            $sdir = $addvars['sdir'];
            $columns = ['muserid', 'plugintype', 'plugincode', 'objectid', 'status', 'prevstatus', 'statusdate'];
            foreach($columns as $column)
            {
                //
                list($addvars['sdir'],$icon) = $this->dof->modlib('ig')->get_icon_sort($column,$sort,$sdir);
                $addvars['sort'] = $column;
                
                $cell = new html_table_cell(
                    dof_html_writer::link(
                        $this->dof->url_im('statushistory','/index.php', $addvars),
                        $this->dof->get_string($column, 'statushistory').
                        dof_html_writer::div($icon, 'sort-indicator')
                        )
                    );
             
                $cell->attributes['class'] = 'header-cell';
                $table->data[$row][] = $cell;
            }
            
            // формируем данные таблицы
            foreach ( $data as $string )
            {
                if(!empty($person = $this->dof->storage('persons')->get_bu($string->muserid))){
                    // ссылка на пользователя
                    $user = dof_html_writer::link(
                        $this->dof->url_im('persons','/view.php', ['id' => $person->id]),
                        $person->sortname
                        );
                }else{
                    $class = $this->dof->modlib('ama')->user($string->muserid);
                    $user = dof_html_writer::link(
                        '/user/view.php?id=' . $string->muserid,
                        $class->fullname()
                        );
                }
                
                // формируем дату
                $string->statusdate = date("Y-m-d H:i:s", $string->statusdate);
                
                // Языковая сторока title плагина
                if($this->dof->plugin_exists($string->plugintype, $string->plugincode)){
                    $pluginstingcode = $this->dof->get_string(
                        'title', $string->plugincode, null, $string->plugintype,
                        ['empry_result' => $string->plugincode]
                        );
                }else{
                    $pluginstingcode = $string->plugincode;
                }
                if($pluginstingcode != $string->plugincode){
                    $pluginstingcode .= dof_html_writer::tag('p', '(' . $string->plugincode . ')',
                        ['class' => 'undercode']
                        );
                }
                // Языковая сторока статуса плагина 
                if($this->dof->plugin_exists('workflow', $string->plugincode)){
                    $pluginstingstatus = $this->dof->get_string(
                        'status:' . $string->status, $string->plugincode, null, 'workflow',
                        ['empry_result' => $string->status]
                        );
                }else {
                    $pluginstingstatus = $string->status;
                }
                if($pluginstingstatus != $string->status){
                    $pluginstingstatus .= dof_html_writer::tag('p', '(' . $string->status . ')',
                        ['class' => 'undercode']
                        );
                }
                // Языковая сторока старый статуса плагина
                if($this->dof->plugin_exists('workflow', $string->plugincode)){
                    $pluginstingprevstatus = $this->dof->get_string(
                        'status:' . $string->prevstatus, $string->plugincode, null, 'workflow',
                        ['empry_result' => $string->prevstatus]
                        );
                }else{
                    $pluginstingprevstatus = $string->prevstatus;
                }
                if($pluginstingprevstatus != $string->prevstatus){
                    $pluginstingprevstatus .= dof_html_writer::tag('p', '(' . $string->prevstatus . ')',
                        ['class' => 'undercode']
                        );
                }
                // Языковая сторока тип плагина
                $pluginstingtype ='';
                $pluginstingtype .= $this->dof->get_string(
                    $string->plugintype . 's', 'admin', null, 'im',
                    ['empry_result' => $string->plugintype]
                    );
                if($pluginstingtype != $string->plugintype){
                    $pluginstingtype .= dof_html_writer::tag('p', '(' . $string->plugintype . ')',
                        ['class' => 'undercode']
                        );
                }
                $row++;
                $table->data[$row][0] = new html_table_cell($user);
                $table->data[$row][1] = new html_table_cell($pluginstingtype);
                $table->data[$row][2] = new html_table_cell($pluginstingcode);
                $table->data[$row][3] = new html_table_cell($string->objectid);
                $table->data[$row][4] = new html_table_cell($pluginstingstatus);
                $table->data[$row][5] = new html_table_cell($pluginstingprevstatus);
                $table->data[$row][6] = new html_table_cell($string->statusdate);
            }
        }
        return dof_html_writer::table($table);  
    }
}