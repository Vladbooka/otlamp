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
 * Роутер статусов очередей логов
 *
 * @package    workflow
 * @subpackage logs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_workflow_logs implements dof_workflow
{
    /**
     * Объект деканата для доступа к общим методам
     * 
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Конструктор
     * 
     * @param dof_control $dof - это $DOF, объект с методами ядра деканата
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
    
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
        return $this->dof->storage('acl')->save_roles($this->type(), $this->code(), $this->acldefault());
    }
    /** 
     * Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * 
     * @param string $old_version - версия установленного в системе плагина
     * 
     * @return boolean
     */
    public function upgrade($oldversion)
    {
        return $this->dof->storage('acl')->save_roles($this->type(), $this->code(), $this->acldefault());
    }
    /** 
     * Возвращает версию установленного плагина
     * 
     * @return string
     */
    public function version()
    {
        return 2017092500;
    }
    /** 
     * Возвращает версии интерфейса Деканата, 
     * с которыми этот плагин может работать
     * 
     * @return string
     */
    public function compat_dof()
    {
        return 'aquarium';
    }

    /** 
     * Возвращает версии стандарта плагина этого типа, 
     * которым этот плагин соответствует
     * 
     * @return string
     */
    public function compat()
    {
        return 'guppy_a';
    }
    
    /** 
     * Возвращает тип плагина
     * 
     * @return string 
     */
    public function type()
    {
        return 'workflow';
    }
    /** 
     * Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * 
     * @return string
     */
    public function code()
    {
        return 'logs';
    }
    /** 
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * 
     * @return array
     */
    public function need_plugins ()
    {
        return [
                'storage' => [
                        'logs' => 2017070500,
                        'acl' => 2011082200,
                        'persons' => 2017000000
                ]
        ];
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
    public function is_setup_possible_list ($oldversion = 0)
    {
        return [
                'storage' => [
                        'acl' => 2011040504
                ]
        ];
    }
    /** 
     * Список обрабатываемых плагином событий 
     * 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     */
    public function list_catch_events ()
    {
        return [
                [
                    'plugintype' => 'storage',
                    'plugincode' => 'logs',
                    'eventcode' => 'insert'
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
        return false;
    }
    
    /** Проверяет полномочия на совершение действий
     * 
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя, полномочия которого проверяются
     * 
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     *              false - доступ запрещен
     * 
     */
    public function is_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
        if ( $this->dof->is_access('datamanage') || $this->dof->is_access('admin') || $this->dof->is_access('manage'))
        {// Полный доступ
            return true;
        }
        
        // Получим ID пользователя
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        
        // Получаем все нужные параметры для функции проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid, $depid); 
        
        // Проверка
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// право есть заканчиваем обработку
            return true;
        } 
        
        return false;
    }
    
    /** Требует наличия полномочия на совершение действий
     * 
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя, полномочия которого проверяются
     * 
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
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
    public function catch_event($gentype, $gencode, $eventcode, $intvar, $mixedvar)
    {
        if ( $gentype==='storage' AND $gencode === 'logs' AND $eventcode === 'insert' )
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
    public function cron($loan, $messages)
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
    public function todo($code, $intvar, $mixedvar)
    {
        return true;
    }
    // **********************************************
    // Методы, предусмотренные интерфейсом workflow
    // **********************************************
    
    /** Возвращает код справочника, в котором хранятся отслеживаемые объекты
     * 
     * @return string
     * @access public
     */
    public function get_storage()
    {
        return 'logs';
    }
    
    /** 
     * Возвращает массив всех состояний, в которых может находиться событие
     * 
     * @return array - Массив статусов
     * @access public
     */
    public function get_list()
    {
        return [
            'active'  => $this->dof->get_string('status:active', 'logs', NULL, 'workflow'),
            'finished' => $this->dof->get_string('status:finished', 'logs', NULL, 'workflow')
        ];
    }
    
    /**
     * Возвращает массив метастатусов
     *
     * @param string $type - тип списка метастатусов
     *
     * @return array - Массив статусов
     */
    public function get_meta_list($type)
    {
        switch ( $type )
        {
            case 'active':
                return [
                    'active'  => $this->dof->get_string('status:active', 'logs', NULL, 'workflow')
                ];
            case 'actual':
                return [
                    'active'  => $this->dof->get_string('status:active', 'logs', NULL, 'workflow'),
                    'finished'  => $this->dof->get_string('status:finished', 'logs', NULL, 'workflow')
                ];
            case 'real':
                return [
                    'finished'  => $this->dof->get_string('status:finished', 'logs', NULL, 'workflow')
                ];
            case 'junk':
                return [];
            default:
                dof_debugging('workflow/'.$this->code().' get_meta_list.This type of metastatus does not exist', DEBUG_DEVELOPER);
                return [];
        }
    }
    
    /** 
     * Возвращает локализованное имя статуса
     * 
     * @param string status - Состояние события
     * 
     * @return string - Имя с учетом языка
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
     * Возвращает массив состояний, в которые может переходить событие 
     * из текущего состояния  
     * 
     * @param int id - id объекта
     * 
     * @return mixed array - массив возможных состояний или false
     */
    public function get_available($id)
    {
        // Получаем объект из ages
        if ( ! $obj = $this->dof->storage('logs')->get($id) )
        {
            // Объект не найден
            return false;
        }
        // Определяем возможные состояния в зависимости от текущего статуса
        $statuses = [];
        switch ( $obj->status )
        {
            case 'active':
                $statuses['finished'] = $this->get_name('finished');
                break;
            case 'finished':
                $statuses['finished'] = $this->get_name('deleted');
                break;
            default:
                $statuses['finished'] = $this->get_name('active');
                break;
        }
        
        return $statuses;
        
    }

    /**
     * Переводит экземпляр объекта с указанным id в переданное состояние
     * 
     * @param int id - id экземпляра объекта
     * @param string newstatus - название состояния, в которое переводится объект
     * @param array options - массив дополнительных опций
     * 
     * @return boolean  true - удалось перевести в указанное состояние,
     *                  false - не удалось перевести в указанное состояние
     * @access public
     */
    public function change($id, $newstatus, $options=null)
    {
        $id = intval($id);
        $storage = $this->dof->storage($this->get_storage());
        
        if ( ! $object = $storage->get($id) )
        { // Не удалось получить объект
            return false;
        }

        // Получим доступные для перехода статусы
        $list = $this->get_available($id);
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
     * 
     * @param int id - id экземпляра
     * 
     * @return boolean true - удалось инициализировать состояние объекта 
     *                 false - не удалось перевести в указанное состояние
     */
    public function init($id)
    {
        // Получаем объект из справочника
        if ( ! $obj = $this->dof->storage('logs')->get($id) )
        {// Объект не найден
            return false;
        }
        
        // Меняем статус
        $obj = new stdClass();
        $obj->id = intval($id);
        $obj->status = 'active';

        return $this->dof->storage('logs')->update($obj);
    }
    
    // **********************************************
    //       Методы для работы с полномочиями
    // **********************************************  
    
    /** Получить список параметров для фунции has_hight()
     * 
     * @return object - список параметров для фунции has_hight()
     * 
     * @param string $action - совершаемое действие
     * @param int $objectid - id объекта над которым совершается действие
     * @param int $personid
     * 
     * @return stdClass
     */
    protected function get_access_parametrs($action, $objectid, $personid, $depid = null)
    {
        $result = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->personid     = $personid;
        $result->objectid     = $objectid;
        $result->departmentid = $depid;
        if ( is_null($depid) )
        {// подразделение не задано - берем текущее
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        if ( ! $objectid )
        {// если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        }
        
        return $result;
    }    

    /** 
     * Проверить права через плагин acl.
     * Функция вынесена сюда, чтобы постоянно не писать длинный вызов и не перечислять все аргументы
     * 
     * @param object $acldata - объект с данными для функции storage/acl->has_right() 
     * 
     * @return bool
     */
    protected function acl_check_access_paramenrs($acldata)
    {
        return $this->dof->storage('acl')->
                    has_right($acldata->plugintype, $acldata->plugincode, $acldata->code, 
                              $acldata->personid, $acldata->departmentid, $acldata->objectid);
    }    
        
    /** Возвращает стандартные полномочия доступа в плагине
     * @return array
     *  a[] = array( 'code'  => 'код полномочия',
     *               'roles' => array('student' ,'...');
     */
    public function acldefault()
    {
        $a = [];
        
        $a['changestatus'] = [
                'roles' => [
                    'manager',
                    'teacher',
                    'methodist'
                ]
        ]; 
        
        return $a;
    }
}
?>