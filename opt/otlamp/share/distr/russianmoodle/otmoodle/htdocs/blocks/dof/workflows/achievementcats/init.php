<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                  
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
 * Роутер статусов для разделов достижений
 * 
 * @package    workflow
 * @subpackage achievementcats
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_workflow_achievementcats implements dof_workflow
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
        // Права доступа
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    
    /**
     * Возвращает версию установленного плагина
     * 
     * @return int - Версия плагина
     */
    public function version()
    {
		return 2017031500;
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
        return 'guppy_a';
    }

    /** Возвращает тип плагина
     * 
     * @return string 
     */
    public function type()
    {
        return 'workflow';
    }
    
    /** Возвращает короткое имя плагина
     * 
     * Оно должно быть уникально среди плагинов этого типа
     * 
     * @return string
     */
    public function code()
    {
        return 'achievementcats';
    }
    
    /** 
     * Возвращает список плагинов, без которых этот плагин работать не может
     * 
     * @return array
     */
    public function need_plugins()
    {
        return [
            'storage' => 
                [
                    'achievementcats' => 2015090000,
                    'acl'             => 2011040504
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
            'storage' => 
                [
                    'achievementcats' => 2015090000,
                    'acl'             => 2011040504
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
                'plugintype' => 'storage',
                'plugincode' => 'achievementcats',
                'eventcode'  => 'insert'
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
    public function require_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
        if ( ! $this->is_access($do, $objid, $userid, $depid) )
        {
            $notice = "{$this->code()}/{$do} (block/dof/{$this->type()}/{$this->code()}: {$do})";
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
        if ( $gentype==='storage' AND $gencode === 'achievementcats' AND $eventcode === 'insert' )
        {// Отлавливаем добавление нового объекта
            // Инициализируем плагин
            return $this->init($intvar);
        }
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
    // Методы, предусмотренные интерфейсом workflow
    // **********************************************
    
    /**
     * Возвращает код справочника, в котором хранятся отслеживаемые объекты
     * 
     * @return string
     */
    public function get_storage()
    {
        return 'achievementcats';
    }
    
    /**
     * Возвращает массив всех состояний,
     * в которых может находиться экземпляр объекта,
     * обрабатываемый этим плагином
     * 
     * @return array
     */
    public function get_list()
    {
        return [
                'available'    => $this->dof->get_string('status:available',    'achievementcats', NULL, 'workflow'),
                'notavailable' => $this->dof->get_string('status:notavailable', 'achievementcats', NULL, 'workflow'),
                'deleted'      => $this->dof->get_string('status:deleted',      'achievementcats', NULL, 'workflow')
        ];
    }
    
    /** 
     * Возвращает массив метастатусов
     * 
     * @param string $type - тип списка метастатусов
     * 
     * @return array
     */
    public function get_meta_list($type)
    {
        switch ( $type )
        {
            case 'active':   
                return array(
                    'available'    => $this->dof->get_string('status:available',    'achievementcats', NULL, 'workflow')
                );
            case 'actual':
                return array(
                    'available'    => $this->dof->get_string('status:available',    'achievementcats', NULL, 'workflow'),
                    'notavailable' => $this->dof->get_string('status:notavailable', 'achievementcats', NULL, 'workflow')
                );
            case 'real':
                return array(
                    'available'    => $this->dof->get_string('status:available',    'achievementcats', NULL, 'workflow'),
                    'notavailable' => $this->dof->get_string('status:notavailable', 'achievementcats', NULL, 'workflow')
                );
            case 'junk':                
                return array(
                    'deleted'      => $this->dof->get_string('status:deleted',      'achievementcats', NULL, 'workflow')
                );
            default:
                dof_debugging('workflow/'.$this->code().' get_meta_list.This type of metastatus does not exist', DEBUG_DEVELOPER);
                return array();
        }
    }

    /**
     * Возвращает имя статуса
     * 
     * @param string status - код состояния
     * 
     * @return string название статуса или пустую строку
     */
    public function get_name($status)
    {
        // Получим список всех статусов
        $list = $this->get_list();
        
        if ( array_key_exists($status, $list) )
        {// Код есть в массиве статусов
            // Вернем название статуса
            return $list[$status];
        }
        // Такого кода нет в массиве
        return '';
    }
    
    /**
     * Возвращает массив состояний, в которые может переходить объект 
     * из текущего состояния 
     *  
     * @param int id - id объекта
     * 
     * @return mixed array - массив возможных состояний или false
     */
    public function get_available($id)
    {
        // Получаем объект
        if ( ! $obj = $this->dof->storage('achievementcats')->get($id) )
        {
            // Объект не найден
            return false;
        }
        
        $statuses = array();
        // Определяем возможные состояния в зависимости от текущего статуса
        switch ( $obj->status )
        {
            // Доступен
            case 'available':
                $statuses['notavailable'] = $this->get_name('notavailable');
                $statuses['deleted']      = $this->get_name('deleted');
                return $statuses;
            // Скрыт
            case 'notavailable':
                $statuses['available']    = $this->get_name('available');
                $statuses['deleted']      = $this->get_name('deleted');
                return $statuses;
           // Удалено
           case 'draft':
                return $statuses;
            default: 
                return false;
        }
        return false;
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
    
        if( $result )
        {
            // Запись в историю изменения статусов
            $this->dof->storage('statushistory')->change_status(
                $this->get_storage(),
                $id,
                $newstatus,
                $object->status,
                $options
            );
            
            // Отправка события при удалении
            if ( $newstatus == 'deleted' )
            {
                $this->dof->send_event(
                    $this->type(),
                    $this->code(),
                    'changestatus',
                    $id, 
                    ['status' => $newstatus]
                );
            }
        }
        
        return $result;
    }
    
    /**
     * Инициализируем состояние объекта
     * 
     * @param int id - id экземпляра
     * 
     * @return boolean true - удалось инициализировать состояние объекта 
     *                 false - не удалось перевести в указанное состояние
     */
    public function init($id)
    {
        // Получаем объект
        if ( ! $object = $this->dof->storage('achievementcats')->get($id) )
        {// Объект не найден
            return false;
        }
        // Меняем статуc
        $obj = new stdClass();
        $obj->id = intval($id);
        $obj->status = 'available';
        
        return $this->dof->storage('achievementcats')->update($obj);
    }

    /// **********************************************
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
     * Возвращает стандартные полномочия доступа в плагине
     * 
     * @return array
     *  a[] = array( 'code'  => 'код полномочия',
     *               'roles' => array('student' ,'...');
     */
    public function acldefault()
    {
        $a = array();
        
        $a['changestatus/owner'] = array('roles' => array(
                'manager'
        ));
        
        return $a;
    }

    // **********************************************
    // Собственные методы
    // **********************************************

}
?>