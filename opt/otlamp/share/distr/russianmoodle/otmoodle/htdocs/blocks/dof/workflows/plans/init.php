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
 * Роутер статусов для тематического плана
 *
 * @package    workflow
 * @subpackage plans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_workflow_plans implements dof_workflow
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
        return 2018122900;
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
        return 'guppy_a';
    }
    
    /** 
     * Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'workflow';
    }
    /** 
     * Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'plans';
    }
    /** 
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('plans' => 2010082700,
                                      'acl'   => 2011062100));
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
    /** 
     * Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return array(array('plugintype'=>'storage','plugincode'=>'plans','eventcode'=>'insert'));
    }
    /** 
     * Требуется ли запуск cron в плагине
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
    public function require_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
        // Используем функционал из $DOFFICE
        //return $this->dof->require_access($do, NULL, $userid);
        if ( ! $this->is_access($do, $objid, $userid, $depid) )
        {
            $notice = "{$this->code()}/{$do} (block/dof/{$this->type()}/{$this->code()}: {$do})";
            if ($objid){$notice.=" id={$objid}";}
            $this->dof->print_error('nopermissions','',$notice);
        }
    }
    /** 
     * Обработать событие
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
		if ( $gentype==='storage' AND $gencode === 'plans' AND $eventcode === 'insert' )
		{
			// Отлавливаем добавление нового объекта
			// Инициализируем плагин
			return $this->init($intvar);
		}
        return true;
    }
    /** 
     * Запустить обработку периодических процессов
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
    public function todo($code,$intvar,$mixedvar)
    {
        return true;
    }
    // **********************************************
    // Методы, предусмотренные интерфейсом workflow
    // **********************************************
   	/** 
   	 * Возвращает код справочника, в котором хранятся отслеживаемые объекты
     * @return string
     * @access public
	 */
	public function get_storage()
	{
		return 'plans';
	}
    /** 
     * Возвращает массив всех состояний,   
     * в которых может находиться экземпляр объекта,
     * обрабатываемый этим плагином
     * @return array
     * @access public
     */
    public function get_list()
    {
		return [
		    'draft'    => $this->dof->get_string('status:draft', $this->get_storage(), NULL, 'workflow'),
            'active'   => $this->dof->get_string('status:active', $this->get_storage(), NULL, 'workflow'),
            'checked'  => $this->dof->get_string('status:checked', $this->get_storage(), NULL, 'workflow'),
	        'excluded' => $this->dof->get_string('status:excluded', $this->get_storage(), NULL, 'workflow'),
			'fixed'    => $this->dof->get_string('status:fixed', $this->get_storage(), NULL, 'workflow'),
		    //@todo удалить deleted
		    'deleted'  => $this->dof->get_string('status:deleted',$this->get_storage(), NULL, 'workflow'),
		    'canceled' => $this->dof->get_string('status:deleted',$this->get_storage(), NULL, 'workflow')
        ];
    }
    
    /**
     * Возвращает массив метастатусов
     *
     * @param string $type - Тип списка метастатусов
     *
     * @return array - Массив статусов
     */
    public function get_meta_list($type)
    {
        switch ( $type )
        {
            case 'active':
                return [
                    'active'   => $this->dof->get_string('status:active', $this->get_storage(), NULL, 'workflow')
                ];
            case 'actual':
                return [
        		    'draft'    => $this->dof->get_string('status:draft', $this->get_storage(), NULL, 'workflow'),
                    'active'   => $this->dof->get_string('status:active', $this->get_storage(), NULL, 'workflow'),
                    'checked'  => $this->dof->get_string('status:checked', $this->get_storage(), NULL, 'workflow'),
        			'fixed'    => $this->dof->get_string('status:fixed', $this->get_storage(), NULL, 'workflow')
                ];
            case 'real':
                return [
        		    'draft'    => $this->dof->get_string('status:draft', $this->get_storage(), NULL, 'workflow'),
                    'active'   => $this->dof->get_string('status:active', $this->get_storage(), NULL, 'workflow'),
                    'checked'  => $this->dof->get_string('status:checked', $this->get_storage(), NULL, 'workflow'),
        	        'excluded' => $this->dof->get_string('status:excluded', $this->get_storage(), NULL, 'workflow'),
        			'fixed'    => $this->dof->get_string('status:fixed', $this->get_storage(), NULL, 'workflow')
                ];
            case 'junk':
                return [
        		    'deleted'  => $this->dof->get_string('status:deleted',$this->get_storage(), NULL, 'workflow'),
        		    'canceled' => $this->dof->get_string('status:deleted',$this->get_storage(), NULL, 'workflow')
                ];
            default:
                dof_debugging('workflow/'.$this->code().' get_meta_list.This type of metastatus does not exist', DEBUG_DEVELOPER);
                return array();
        }
    }
    
    /** 
     * Возвращает имя статуса
     * @param string status - название состояния
     * @return string
     * @access public
     */
    public function get_name($status)
    {
		$list = $this->get_list();
		if ( isset($list[$status]) )
		{
		    return $list[$status];
		}
		return '';
    }
    /** 
     * Возвращает массив состояний,
     * в которые может переходить объект 
     * из текущего состояния  
     * @param int id - id объекта
     * @return mixed array - массив возможных состояний или false
     * @access public
     */
    public function get_available($id)
    {
		// Получаем объект из ages
		if ( ! $obj = $this->dof->storage($this->get_storage())->get($id) )
		{
			// Объект не найден
			return false;
		}
		$statuses = array();
		// Определяем возможные состояния в зависимости от текущего статуса
		switch ( $obj->status )
		{
			case 'active':       // переход из статуса "запланирован"
				$statuses['excluded'] = $this->get_name('excluded');
				$statuses['canceled'] = $this->get_name('deleted');
				$statuses['draft'] = $this->get_name('draft');
				$statuses['fixed'] = $this->get_name('fixed');
				$statuses['checked'] = $this->get_name('checked');
            break;
            case 'excluded':       // переход из статуса "отложено"
				$statuses['active'] = $this->get_name('active');
				$statuses['canceled'] = $this->get_name('deleted');
				
            break;
            case 'completed':       // переход из статуса "отложено"
				$statuses['active'] = $this->get_name('active');
				
            break;
            case 'checked':       // переход из статуса "отложено"
				$statuses['fixed'] = $this->get_name('active');
				$statuses['canceled'] = $this->get_name('deleted');
				
            break;
            case 'deleted':  // переход из статуса "отменен"
                $statuses['canceled'] = $this->get_name('deleted');
            break;
            case 'draft':       // переход из статуса "отложено"
				$statuses['active'] = $this->get_name('active');
				$statuses['canceled'] = $this->get_name('deleted');
            break;
            case 'fixed':       // переход из статуса "отложено"
				$statuses['active'] = $this->get_name('active');
				$statuses['canceled'] = $this->get_name('deleted');
				$statuses['excluded'] = $this->get_name('excluded');
				$statuses['draft'] = $this->get_name('draft');
            break;
            case 'canceled':  // переход из статуса "отменен"
                $statuses = array();
            break;

            default: $statuses = array('active'=>$this->get_name('active'));
		}
        
		return $statuses;
		
    }

    /**
     * Переводит экземпляр объекта с указанным id в переданное состояние
     * @param int id - id экземпляра объекта
     * @param string newstatus - название состояния, в которое переводится объект
     * @param array options - массив дополнительных опций
     * @return boolean  true - удалось перевести в указанное состояние,
     *                  false - не удалось перевести в указанное состояние
     * @access public
     */
    public function change($id, $newstatus, $options=null)
    {
        $id = intval($id);
        $storage = $this->dof->storage($this->get_storage());
        
        if (! $object = $storage->get($id))
        { // Не удалось получить объект
            return false;
        }
        if (! $list = $this->get_available($id))
        { // Ошибка получения статуса для объекта;
            return false;
        }
        if (! isset($list[$newstatus]))
        { // Переход в данный статус из текущего невозможен
            return false;
        }
        
        // Объект для обновления статуса
        $updateobject = new stdClass();
        $updateobject->id = $id;
        $updateobject->status = $newstatus;

        // Изменение статуса
        $result = $storage->update($updateobject);
        if ( $result )
        {
            // Запись в историю изменения статусов
            $this->dof->storage('statushistory')->change_status(
                $this->get_storage(),
                $id,
                $newstatus,
                $object->status,
                $options
            );
        }
        
        return $result;
    }
    /** 
     * Инициализируем состояние объекта
     * @param int id - id экземпляра
     * @return boolean true - удалось инициализировать состояние объекта 
     * false - не удалось перевести в указанное состояние
     * @access public
     */
    public function init($id)
    {
	    // Получаем объект из справочника
		if (!$obj = $this->dof->storage($this->get_storage())->get($id))
		{// Объект не найден
			return false;
		}
		// Меняем статус
		$obj1 = new stdClass();
		$obj1->id = intval($id);
		$obj1->status = 'draft';
		if ( $obj->linktype == 'cstreams' )
		{// на поток или для манагера статус сразу активный
		    $obj1->status = 'active';
		}
		return $this->dof->storage($this->get_storage())->update($obj1);
    }
    
    // **********************************************
    //       Методы для работы с полномочиями
    // **********************************************  
    
    /** Получить список параметров для фунции has_hight()
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
                              $acldata->personid, $acldata->departmentid, $acldata->objectid);
    }
    
    /** Задаем права доступа для объектов этого хранилища
     * @return array
     */
    public function acldefault()
    {
        $a = [];
        $a['changestatus']  = ['roles' => ['manager','teacher','methodist']];  
        $a['changestatus:to:canceled'] = ['roles'=> ['manager']]; 
        return $a;
    }
    
    // **********************************************
    // Собственные методы
    // **********************************************
    /** 
     * Конструктор
     * @param dof_control $dof - это $DOF
     * объект с методами ядра деканата
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
    
    /**
     * Перевод контрольной точки в ранее установленное состояние
     * 
     * @param int $id - ID контрольной точки, требующей преевода
     * @param string $metastatus - Метастатус, в который требуется вернуть элемент
     * @param array $options - Дополнительные данные обработки
     *          ['return'] => 'newstatus' - Вернуть вместо true новый статус, в который был переведен элемент
     *                        'oldstatus' - Вернуть вместо true старый статус, из которого был переведен элемент
     *          
     * @return bool - Результат работы
     */
    public function restore_status($id, $metastatus = NULL, $options = [] )
    {
        // Проверка наличия
        $element = $this->dof->storage($this->get_storage())->get($id);
        if ( ! $element )
        {// Контрольная точка не найдена
            return FALSE;
        }
    
        // Нормализация
        if ( ! isset($options['return']) )
        {
            $options['return'] = NULL;
        }
        
        // Получение маршрута смены статусов
        $trace = $this->dof->storage('statushistory')->get_records([
            'plugintype' => 'storage',
            'plugincode' => $this->get_storage(),
            'objectid' => $id], 'statusdate DESC'
        );
    
        // Получение метастатуса для возврата в предыдущее состояние
        $meta = $this->get_meta_list($metastatus);
        if ( empty($meta) )
        {// Мета статус не определен
            return FALSE;
        }
        $retstatus = 'active';
    
        if ( ! empty($trace) )
        {// Маршрут найден
            do
            {
                // Получение последней смены статуса
                $track = array_pop($trace);
                // Определение статуса перехода
                if ( ! empty($track->status) && isset($meta[$track->status]) )
                {// Статус принадлежит указанному метастатусу
                    $retstatus = $track->status;
                    // Выход из цикла поиска статуса
                    break;
                }
                if ( ! empty($track->prevstatus) && isset($meta[$track->prevstatus]) )
                {// Статус принадлежит указанному метастатусу
                    $retstatus = $track->prevstatus;
                    // Выход из цикла поиска статуса
                    break;
                }
                // Текущий трек не позволяет получить предыдущий статус КТ, переход к следующему треку
            } while ( count($trace) > 0 );
        }
    
        // Смена статуса
        $object = new stdClass();
        $object->id = intval($id);
        $object->status = $retstatus;
        if ( $update = $this->dof->storage($this->get_storage())->update($object) )
        {// Успешная смена статуса
            // Добавление в лог смены статусов
            $this->dof->storage('statushistory')->change_status(
                $this->get_storage(),
                $object->id,
                $object->status,
                $element->status,
                NULL
            );
            switch ( $options['return'] )
            {
                case 'newstatus' :
                    return $object->status;
                case 'oldstatus' :
                    return $element->status;
                default : 
                    return TRUE;
            }
        } else
        {// Смена статуса не удалась
            return FALSE;
        }
    }
}
?>