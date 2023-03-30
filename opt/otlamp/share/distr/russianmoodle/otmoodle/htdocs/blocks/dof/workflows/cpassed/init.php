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
 * Роутер статусов для подписок на дисциплины
 *
 * @package    workflow
 * @subpackage persons
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_workflow_cpassed implements dof_workflow
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
     * 
     * @return boolean
     */
    public function install()
    {
        return $this->dof->storage('acl')->
            save_roles($this->type(),$this->code(),$this->acldefault());
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
        return $this->dof->storage('acl')->
            save_roles($this->type(),$this->code(),$this->acldefault());
    }
    
    /**
     * Возвращает версию установленного плагина
     * 
     * @return int - Версия плагина
     */
    public function version()
    {
        return 2017070500;
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
     * 
     * Оно должно быть уникально среди плагинов этого типа
     * 
     * @return string
     */
    public function code()
    {
        return 'cpassed';
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
                'cpassed'        => 2009101900,
                'acl'            => 2011040504
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
                'cpassed'        => 2009101900,
                'acl'            => 2011040504
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
                'plugincode' => 'cpassed',
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
    public function is_access($do, $objid = null, $userid = null, $depid = null)
    {
        if ( $this->dof->is_access('datamanage') OR $this->dof->is_access('admin') 
             OR $this->dof->is_access('manage') )
        {// Открыть доступ для администраторов
            return true;
        }
        
        // Получение ID текущей персоны, для которой производится проверка прав
        $currentpersonid = (int)$this->dof->storage('persons')->get_by_moodleid_id($userid);
        
        // Получение всех возможных статусов
        $statuses = array_keys($this->get_list());
        foreach ( $statuses as $status )
        {
            
            if ( $do === 'changestatus:to:'.$status )
            {// Проверяется возможность перехода в указанный статус
                
                // Проверка глобального права переводить в любой статус
                if ( $this->is_access('changestatus', $objid, $userid, $depid) )
                {// Право дано
                    return true;
                }
            }
        }
        
        // Допольнительные условия проверки доступа
        switch ( $do )
        {
            // Право переводить из статуса "Заявка"
            case 'manage_requests' : 
                
                // Проверка текущей персоны на преподавание для подписки
                $teacherid = (int)$this->dof->storage('cpassed')->get_field($objid, 'teacherid');
                if ( $teacherid > 0 && $teacherid == $currentpersonid )
                {// Текущая персона проводит обучение по указаной подписке

                    // Проверка специального права
                    if ( $this->is_access('manage_requests/teacher', $objid, $userid, $depid) )
                    {// Право дано
                        return true;
                    }
                }
                break;
        }
        
        // Формирование параметров для проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $currentpersonid, $depid);
         
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
    public function require_access($do, $objid = null, $userid = null, $depid = null)
    {
        // Используем функционал из $DOFFICE
        //return $this->dof->require_access($do, null, $userid);
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
        if ( $gentype==='storage' AND $gencode === 'cpassed' AND $eventcode === 'insert' )
        {
            // Отлавливаем добавление нового объекта
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
        return 'cpassed';
    }
    
    /**
     * Возвращает массив всех состояний,
     * в которых может находиться экземпляр объекта, обрабатываемый этим плагином
     * 
     * @return array - Массив статусов
     */
    public function get_list()
    {
        return [
            'request'      => $this->dof->get_string('status:request','cpassed',null,'workflow'),
            'plan'         => $this->dof->get_string('status:plan','cpassed',null,'workflow'),
            'active'       => $this->dof->get_string('status:active','cpassed',null,'workflow'),
            'suspend'      => $this->dof->get_string('status:suspend','cpassed',null,'workflow'),
            'canceled'     => $this->dof->get_string('status:canceled','cpassed',null,'workflow'),
            'completed'    => $this->dof->get_string('status:completed','cpassed',null,'workflow'),
            'reoffset'     => $this->dof->get_string('status:reoffset','cpassed',null,'workflow'),
            'failed'       => $this->dof->get_string('status:failed','cpassed',null,'workflow'),
            'academicdebt' => $this->dof->get_string('status:academicdebt','cpassed',null,'workflow')
        ];
    }
    
    /**
     * Получить список начальных статусов
     *
     * @return array
     */
    public function get_initstatuses_list()
    {
        return [
            'request' => $this->dof->get_string('status:request','cpassed',null,'workflow'),
            'plan'    => $this->dof->get_string('status:plan','cpassed',null,'workflow')
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
                return [
                    'active' => $this->dof->get_string('status:active', $this->code(), null, 'workflow')
                ];
            case 'actual':
                return [
                    'request' => $this->dof->get_string('status:request',$this->code(),null, 'workflow'),
                    'plan'    => $this->dof->get_string('status:plan',$this->code(),null, 'workflow'),
                    'active'  => $this->dof->get_string('status:active',$this->code(),null, 'workflow'),
                    'suspend' => $this->dof->get_string('status:suspend',$this->code(),null, 'workflow')
                ];
            case 'real':
                return [
                    'plan'         => $this->dof->get_string('status:plan', $this->code(), null, 'workflow'),
                    'active'       => $this->dof->get_string('status:active', $this->code(), null, 'workflow'),
                    'suspend'      => $this->dof->get_string('status:suspend', $this->code(), null, 'workflow'),
                    'completed'    => $this->dof->get_string('status:completed', $this->code(), null, 'workflow'),
                    'reoffset'     => $this->dof->get_string('status:reoffset', $this->code(), null, 'workflow'),
                    'failed'       => $this->dof->get_string('status:failed', $this->code(), null, 'workflow'),
                    'academicdebt' => $this->dof->get_string('status:academicdebt', $this->code(), null, 'workflow')
                ];
            case 'junk':                
                return [
                    'canceled' => $this->dof->get_string('status:canceled',$this->code(),null,'workflow')
                ];
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
        // Получение подписки на дисциплину
        if ( ! $cpassed = $this->dof->storage('cpassed')->get($id) )
        {
            // Подписка не найдена
            return false;
        }

        // Определяем возможные состояния в зависимости от текущего статуса
        switch ( $cpassed->status )
        {
            // Переход из статуса "Заявка"
            case 'request':
                $statuses = [];
                
                // Получение статусов
                $cstreamstatus = $this->dof->storage('cstreams')->
                    get_field((int)$cpassed->cstreamid, 'status');
                $programmsbcstatus = $this->dof->storage('programmsbcs')->
                    get_field((int)$cpassed->programmsbcid, 'status');
                
                if ( $cstreamstatus === 'plan' && ( $programmsbcstatus === 'active' || $programmsbcstatus === 'condactive' ) )
                {// Подписка прилинкована к потоку и создана из подписки на программу
                    $statuses['plan'] = $this->get_name('plan');
                }
                if ( $cstreamstatus === 'active' && ( $programmsbcstatus === 'active' || $programmsbcstatus === 'condactive' ) )
                {// Подписка прилинкована к потоку и создана из подписки на программу
                    $statuses['active'] = $this->get_name('active');
                }
                $statuses['canceled'] = $this->get_name('canceled');
                return $statuses;
            // Переход из статуса "Запланировано"
            case 'plan' :
                $statuses = [];
                
                // Получение статусов
                $cstreamstatus = $this->dof->storage('cstreams')->
                    get_field((int)$cpassed->cstreamid, 'status');
                $programmsbcstatus = $this->dof->storage('programmsbcs')->
                    get_field((int)$cpassed->programmsbcid, 'status');
                
                if ( $cstreamstatus === 'active' && ( $programmsbcstatus === 'active' || $programmsbcstatus === 'condactive' ) )
                {// Подписка прилинкована к потоку и создана из подписки на программу
                    $statuses['active'] = $this->get_name('active');
                } elseif ( $cstreamstatus === 'completed' )
                {// Учебный процесс завершен
                    $statuses['active']    = $this->get_name('active');
                    $statuses['completed'] = $this->get_name('completed');
                    $statuses['failed'] = $this->get_name('failed');
                }
                $statuses['reoffset'] = $this->get_name('reoffset');
                $statuses['canceled'] = $this->get_name('canceled');
                $statuses['suspend']  = $this->get_name('suspend');
                return $statuses;
                break;
            // Переход из статуса "Идет обучение"
            case 'active' :
                return [
                    'completed' => $this->get_name('completed'), 
                    'failed' => $this->get_name('failed'),
                    'suspend' => $this->get_name('suspend'), 
                    'canceled' => $this->get_name('canceled')
                ];
                break;
            // Переход из статуса "Приостановлен"
            case 'suspend' :
                $statuses = [];
                
                // Получение статусов
                $cstreamstatus = $this->dof->storage('cstreams')->
                    get_field((int)$cpassed->cstreamid, 'status');
                $programmsbcstatus = $this->dof->storage('programmsbcs')->
                    get_field((int)$cpassed->programmsbcid, 'status');
                
                if ( $cstreamstatus === 'active' && ( $programmsbcstatus === 'active' || $programmsbcstatus === 'condactive' ) )
                {// Подписка прилинкована к потоку и создана из подписки на программу
                    $statuses['active'] = $this->get_name('active');
                } elseif ( $cstreamstatus === 'plan' && ( $programmsbcstatus === 'active' || $programmsbcstatus === 'condactive' ) )
                {// Возврат в состояние "Запланировано" 
                    $statuses['plan']  = $this->get_name('plan');
                }

                $statuses['completed'] = $this->get_name('completed');
                $statuses['canceled'] = $this->get_name('canceled');
                $statuses['failed'] = $this->get_name('failed');
                return $statuses;
            // Переход из статуса "Отменен"
            case 'canceled' :
                return [];
            // Переход из статуса "Успешно завершен"
            case 'reoffset' :
                return ['failed' => $this->get_name('failed')];
            // Переход из статуса "Успешно завершен"
            case 'completed' :
                return ['failed' => $this->get_name('failed')];
            // Переход из статуса "Неуспешно завершен"
            case 'failed' :
                return [];
            // Переход из статуса "Академическая разница"
            case 'academicdebt' :
                return [];
            // Неизвестный статус
            default: 
                return false;
        }
        return false;
    }

    /**
     * Перевод статуса подписки на дисциплину в указанную
     * 
     * @param int id - ID подписки на дисциплину
     * @param string newstatus - Целевой статус
     * @param array options - Дополнительные опции
     * 
     * @return bool - Результат перевода
     */
    public function change($id, $newstatus, $options = null)
    {
        // Нормализация входных данных
        $id = intval($id);
        $newstatus = (string)$newstatus;
        
        // Получение подписки на дисциплину
        $cpassed = $this->dof->storage($this->get_storage())->get($id);
        if ( ! $cpassed  )
        {// Подписка на дисциплину не найдена
            return false;
        }
        
        // Получение списка доступных статусов перехода
        $list = $this->get_available($id);
        if ( ! $list )
        {
            return false;
        }
        
        if ( ! isset($list[$newstatus]) )
        {// Переход в данный статус из текущего невозможен
            return false;
        }
        
        // Формирование нового объекта подписки
        $updateobject = new stdClass();
        // Код посылаемого события
        $eventcode = '';
        
        switch ( $newstatus )
        {
            // Перевод в запланированный статус
            case 'plan':
                $eventcode = 'cpassed_plan';
                break;
            // Перевод в активный статус
            case 'active' :
                if( $cpassed->status == 'plan' || $cpassed->status == 'request' )
                {// Первичная активация подписки на дисциплину
                    // Установка времени начала обучения
                    $updateobject->begindate = time();
                }
                if ( ! empty($cpassed->cstreamid) )
                {// Подписка привязана к учебному процессу
                    $enddate = $this->dof->storage('cstreams')->
                        get_field((int)$cpassed->cstreamid, 'enddate');
                    if ( ! empty($enddate) )
                    {
                        $updateobject->enddate = $enddate;
                    }
                }
                
                $eventcode = 'cpassed_active';
                break;
                
            case 'reoffset':
            case 'suspend':
                if( $cpassed->status == 'plan' )
                {
                    $updateobject->begindate = time();
                }
                
            default :
                if ( $cpassed->status == 'active' )
                {// Деактивация подписки
                    // Установка времени завершения
                    $updateobject->enddate = time();
                    
                    //Установка кода посылаемого события
                    $eventcode = 'cpassed_not_active';
                }
                break;
        }
        
        $transaction = $this->dof->storage('cpassed')->begin_transaction();
        
        // Обновляем подписку
        $updateobject->id = $id;
        $updateobject->status = $newstatus;
        
        // Изменение статуса
        $result = $this->dof->storage($this->get_storage())->update($updateobject);

        if( $result )
        {
            // Запись в историю изменения статусов
            $this->dof->storage('statushistory')->change_status(
                $this->get_storage(),
                $id,
                $newstatus,
                $cpassed->status,
                $options
            );
            
            if ( ! empty($eventcode) )
            {// Отправка события смены статуса
                $this->dof->send_event('workflow', 'cpassed', $eventcode, $id);
            }
        }
        
        // Коммит транзакции
        $this->dof->storage('cpassed')->commit_transaction($transaction);
        
        return $result;
    }
    
    /**
     * Инициализация состояния объекта
     * 
     * @param int id - id экземпляра
     * 
     * @return boolean true - удалось инициализировать состояние объекта 
     *                 false - не удалось перевести в указанное состояние
     */
    public function init($id)
    {
        // Получение объекта
        if ( ! $cpassed = $this->dof->storage('cpassed')->get($id) )
        {
            // Объект не найден
            return false;
        }
        
        // Получение списка начальных статусов
        $default_statuses = $this->get_initstatuses_list();

        // Установка статуса по умолчанию
        $update = new stdClass();
        $update->id = intval($id);

        if ( ! isset($default_statuses[$cpassed->status]) )
        {// Текущий статус добавленной записи не является начальным
            
            // Установка начального статуса на основе настроек учебного процесса
            $selfenrol = $this->dof->storage('cstreams')->get_selfenrol((int)$cpassed->cstreamid);

            $update->status = 'plan';
            if ( $selfenrol == 2 )
            {// Установлена возможность добавления заявок
                $update->status = 'request';
            }
        } else 
        {
            $update->status = $cpassed->status;
        }
        
        if ( $this->dof->storage('cpassed')->update($update) )
        {// Инициализация прошла успешно
            
            // Отправка события смены статуса
            $this->dof->send_event(
                'workflow', 
                'cpassed', 
                'status_changed', 
                $id, 
                ['old_status' => $cpassed->status, 'new_status' => $update->status]
            );
            // Изменение статуса на основе данных об подписке и предмето-классе
            return $this->postinit($id);
        }
        // Инициализация завершилась не успешно
        return false;
    }
    
    /**
     * Пост-инициализация состояния объекта
     * 
     * Коррекция статуса на основе статусов подписки на программу и предмето-класса
     * 
     * @param int id - id экземпляра
     * 
     * @return boolean true - удалось инициализировать состояние объекта 
     *                 false - не удалось перевести в указанное состояние
     */
    public function postinit($id)
    {
        // Получение объекта
        if ( ! $cpassed = $this->dof->storage('cpassed')->get($id) )
        {
            // Объект не найден
            return false;
        }
        
        // Новый статус
        $status = null;
        
        // Данные для обновления
        $update = new stdClass();
        $update->id = $id;
        
        // Получение предмето-класса
        $cstream = $this->dof->storage('cstreams')->get($cpassed->cstreamid);
        
        // Определение статусов предмето-класса и подписки на программу
        $cstreamstatus = $this->dof->storage('cstreams')->get_field($cpassed->cstreamid, 'status');
        $sbcstatus = $this->dof->storage('programmsbcs')->get_field($cpassed->programmsbcid, 'status');
        
        // Установка нового статуса в зависимости от статусов подписки и предмето-класса
        switch ( $cstreamstatus )
        {
            case 'plan':
                    switch ( $sbcstatus )
                    {
                        case 'application':
                        case 'plan':
                        case 'active':
                        case 'condactive':
                        case 'suspend':
                            $status = $cpassed->status;
                            break;
                        default: $status = 'canceled';
                    }
                break;
            case 'active':
                switch ( $sbcstatus )
                {
                    case 'application':
                    case 'plan':
                        $status = 'plan';
                        break;
                    case 'active':
                    case 'condactive':
                        $status = 'active';
                        // подписываем студента на курс
                        $this->dof->send_event('workflow', 'cpassed', 'cpassed_active', $cpassed->id);
                        $update->begindate = time();
                        // продлеваем подписку до окончания потока
                        $update->enddate = $this->dof->storage('cstreams')->get_field($cpassed->cstreamid,'enddate');
                        break;
                    case 'suspend':
                        $update->begindate = time();
                        $update->enddate = time();
                        $status = 'suspend';
                        break;
                    default: $status = 'canceled';
                }
                break;
            case 'suspend':
                switch ( $sbcstatus )
                {
                    case 'application':
                    case 'plan':
                        $status = 'plan';
                        break;
                    case 'active':
                    case 'condactive':
                    case 'suspend':
                        $update->begindate = time();
                        $update->enddate = time();
                        $status = 'suspend';
                        break;
                    default: $status = 'canceled';
                }
                break;
            case 'completed':
                // создаем в плане, иначе происходит накладка с пересдачей
                $status = 'plan';
                break;
            case 'canceled':
            default:
                if ( $cpassed->status == 'academicdebt' )
                { // Это академическая разница
                    $status = 'academicdebt';
                } else if ( empty($cpassed->cstreamid) AND empty($cpassed->ageid) )
                { // Это перезачёт из "Ведомости перезачёта оценок"
                    $status = 'reoffset';
                } else
                {
                    $status = 'canceled';
                }
        }
        
        // @TODO - Сменить update на метод change, проверить, что при этом маршруты не будут блокировать смену статуса
        $update->status = $status;
        if ( $this->dof->storage('cpassed')->update($update) )
        {// Обновление прошло успешно
            // Добавление записи об изменении статуса в историю
            $this->dof->storage('statushistory')->change_status(
                $this->get_storage(),
                intval($id), 
                $status, 
                $cpassed->status, 
                null,
                'Postinit status upgrade with cstreamstatus '.$cstreamstatus.' and programmbc status '.$sbcstatus 
            );
            
            return true;
        }
        return false;
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
        
    /** Возвращает стандартные полномочия доступа в плагине
     * @return array
     *  a[] = array( 'code'  => 'код полномочия',
     *               'roles' => array('student' ,'...');
     */
    public function acldefault()
    {
        $a = [];

        // Право переводить в любой статус
        $a['changestatus'] = [
            'roles' => [
                'manager'
            ]
        ];
        
        // Право переводить из статуса "Заявка"
        $a['manage_requests'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        
        // Право переводить из статуса "Заявка" преподавателю учебного процесса
        $a['manage_requests/teacher'] = [
            'roles' => [
                'teacher'
            ]
        ];

        return $a;
    }

    // **********************************************
    // Собственные методы
    // **********************************************

    /** 
     * Получить список статусов, которые могут попадать в итоговую ведомость 
     *
     * @param bool $showjunk - отображать мусорные статусы
     * 
     * @return array - Список статусов
     */
    public function get_register_statuses($showjunk = false)
    {
        $statuses = [
            'active'    => $this->dof->get_string('status:active', 'cpassed', null, 'workflow'),
            'suspend'   => $this->dof->get_string('status:suspend', 'cpassed', null, 'workflow'),
            'completed' => $this->dof->get_string('status:completed', 'cpassed', null, 'workflow'),
            'reoffset'  => $this->dof->get_string('status:reoffset', 'cpassed', null, 'workflow'),
            'failed'    => $this->dof->get_string('status:failed', 'cpassed', null, 'workflow')
        ];
        
        if ( ! empty($showjunk) )
        {// Отображать мусорные статусы
            $statuses += $this->get_meta_list('junk');
        }
        return $statuses;
    }
}
?>