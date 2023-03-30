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
 * Роутер статусов для договоров на обучение
 *
 * @package    workflow
 * @subpackage contracts
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_workflow_contracts implements dof_workflow
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
        return $this->dof->storage('acl')->save_roles($this->type(), $this->code(), $this->acldefault());
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
        
        if ( $oldversion < 2018071100 )
        {
            $contracts = $this->dof->storage('contracts')->get_records(['status' => 'studreg']);
            if( ! empty($contracts) )
            {
                foreach($contracts as $contract)
                {
                    $updateobject = new stdClass();
                    $updateobject->id = $contract->id;
                    $updateobject->status = 'clientsign';
                    $result = $this->dof->storage('contracts')->update($updateobject);
                    
                    if( $result )
                    {
                        // Запись в историю изменения статусов
                        $this->dof->storage('statushistory')->change_status(
                            $this->get_storage(),
                            $contract->id,
                            'clientsign',
                            'studreg'
                        );
                        
                        // объект для отправки события
                        $statusobj      = new stdClass();
                        $statusobj->old = 'studreg';
                        $statusobj->new = 'clientsign';
                        
                        // посылаем событие о смене статуса
                        $this->dof->send_event('workflow', 'contracts', 'changestatus', $contract->id, $statusobj);
                    }
                }
            }
            
        }
        return $this->dof->storage('acl')->save_roles($this->type(), $this->code(), $this->acldefault());
    }

    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        return 2018071100;
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
        return 'guppy_a';
    }

    /** Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'workflow';
    }

    /** Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'contracts';
    }

    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array(
                'storage'       => array('contracts'  => 2008103100,
                'persons'       => 2008101600,
                'statushistory' => 2009060100,
                'acl'           => 2011082200),
                'sync'          => array('personstom' => 2009043000)
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
    public function is_setup_possible_list($oldversion = 0)
    {
        return array('storage' => array('acl' => 2011040504));
    }

    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return [
            [
                'plugintype' => 'storage',
                'plugincode' => 'contracts',
                'eventcode' => 'insert'
            ],
            [
                'plugintype' => 'storage',
                'plugincode' => 'contracts',
                'eventcode' => 'activate_request'
            ]
        ];
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
    public function is_access($do, $objid = null, $userid = null, $depid = null)
    {
        if ( $this->dof->is_access('datamanage') OR $this->dof->is_access('admin') 
             OR $this->dof->is_access('manage') )
        {// Открыть доступ для администраторов
            return true;
        }
        
        // Получение ID текущей персоны, для которой производится проверка прав
        $currentpersonid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        
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
        
        // Формирование параметров для проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $currentpersonid, $depid);
         
        // Производим проверку
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// Право есть
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
        if ( !$this->is_access($do, $objid, $userid, $depid) )
        {
            $notice = "{$this->code()}/{$do} (block/dof/{$this->type()}/{$this->code()}: {$do})";
            if ( $objid )
            {
                $notice.=" id={$objid}";
            }
            $this->dof->print_error('nopermissions', '', $notice);
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
    public function catch_event($gentype, $gencode, $eventcode, $intvar, $mixedvar)
    {
        if ( $gentype === 'storage' AND $gencode === $this->get_storage() )
        {
            switch ($eventcode)
            {
                case 'insert':
                    // Отлавливаем добавление нового объекта
                    // Инициализируем плагин
                    return $this->init($intvar);
                    break;
                case 'activate_request':
                    // Выполнение перевода в активный статус
                    $this->change($intvar, 'new');
                    $this->change($intvar, 'clientsign');
                    $this->change($intvar, 'wesign');
                    $this->change($intvar, 'work');
                    break;
            }
        }
        return true;
    }

    /** Запустить обработку периодических процессов
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

    /** Обработать задание, отложенное ранее в связи с его длительностью
     * @param string $code - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function todo($code, $intvar, $mixedvar)
    {
        return true;
    }

    // **********************************************
    // Методы, предусмотренные интерфейсом workflow
    // **********************************************
    /** Возвращает код справочника, в котором хранятся отслеживаемые объекты
     * @return string
     * @access public
     */
    public function get_storage()
    {
        return 'contracts';
    }

    /** Возвращает массив всех состояний,   
     * в которых может находиться экземпляр объекта,
     * обрабатываемый этим плагином
     * @return array
     * @access public
     */
    public function get_list()
    {
        return array(
            'tmp'        => $this->dof->get_string('status:tmp', 'contracts', NULL, 'workflow'),
            'new'        => $this->dof->get_string('status:new', 'contracts', NULL, 'workflow'),
            'clientsign' => $this->dof->get_string('status:clientsign', 'contracts', NULL, 'workflow'),
            'wesign'     => $this->dof->get_string('status:wesign', 'contracts', NULL, 'workflow'),
            'work'       => $this->dof->get_string('status:work', 'contracts', NULL, 'workflow'),
            'frozen'     => $this->dof->get_string('status:frozen', 'contracts', NULL, 'workflow'),
            'archives'   => $this->dof->get_string('status:archives', 'contracts', NULL, 'workflow'),
            'cancel'     => $this->dof->get_string('status:cancel', 'contracts', NULL, 'workflow'));
    }

    /** Возвращает массив метастатусов
     * @param string $type - тип списка метастатусов
     *               'active' - активный 
     *               'actual' - актуальный
     *               'real' - реальный
     *               'junk' - мусорный
     * @return array
     */
    public function get_meta_list($type)
    {
        switch ( $type )
        {
            case 'active':
                return array('work' => $this->dof->get_string('status:work', $this->code(), NULL, 'workflow'));
            case 'actual':
                return array(
                    'new'        => $this->dof->get_string('status:new', $this->code(), NULL, 'workflow'),
                    'clientsign' => $this->dof->get_string('status:clientsign', $this->code(), NULL, 'workflow'),
                    'wesign'     => $this->dof->get_string('status:wesign', $this->code(), NULL, 'workflow'),
                    'work'       => $this->dof->get_string('status:work', $this->code(), NULL, 'workflow'),
                    'frozen'     => $this->dof->get_string('status:frozen', $this->code(), NULL, 'workflow'));
            case 'real':
                return array(
                    'new'        => $this->dof->get_string('status:new', $this->code(), NULL, 'workflow'),
                    'clientsign' => $this->dof->get_string('status:clientsign', $this->code(), NULL, 'workflow'),
                    'wesign'     => $this->dof->get_string('status:wesign', $this->code(), NULL, 'workflow'),
                    'work'       => $this->dof->get_string('status:work', $this->code(), NULL, 'workflow'),
                    'frozen'     => $this->dof->get_string('status:frozen', $this->code(), NULL, 'workflow'),
                    'archives'   => $this->dof->get_string('status:archives', $this->code(), NULL, 'workflow'));
            case 'junk':
                return array('tmp'    => $this->dof->get_string('status:tmp', $this->code(), NULL, 'workflow'),
                    'cancel' => $this->dof->get_string('status:cancel', $this->code(), NULL, 'workflow'));
            default:
                dof_debugging('workflow/' . $this->code() . ' get_meta_list.This type of metastatus does not exist', DEBUG_DEVELOPER);
                return array();
        }
    }

    /**
     * Получить список статусов, находясь в которых контракты считаются неактуальными
     * @return array
     */
    public function get_list_unactual()
    {
        $all      = $this->get_list();
        $unactual = $this->get_list_actual();
        return array_diff_key($all, $unactual); // Удаляем неактивные элементы
    }

    /**
     * Получить список статусов, находясь в которых контракты считаются актуальными (все, кроме tmp и cancel)
     * @return array
     */
    public function get_list_actual()
    {
        $all = $this->get_list();
        // Удаляем неактивные элементы
        // tmp считается неактуальным, поскольку это черновик, который еще не подтвержден создателем
        unset($all['tmp']);
        unset($all['cancel']);
        return $all;
    }

    /**
     * Получить список статусов, актуальных для выбора периодов обучения 
     * (все, кроме tmp, new, clientsign, wesign)
     * @return array
     */
    public function get_list_actual_age()
    {
        $all = $this->get_list();
        // Удаляем неактивные элементы
        unset($all['tmp']);
        unset($all['new']);
        unset($all['clientsign']);
        unset($all['wesign']);
        return $all;
    }

    /** Возвращает имя статуса
     * @param string status - название состояния
     * @return string
     * @access public
     */
    public function get_name($status)
    {
        $list = $this->get_list();
        return $list[$status];
    }

    /** Возвращает массив состояний,
     * в которые может переходить объект 
     * из текущего состояния  
     * @param int id - id объекта
     * @return mixed array - массив возможных состояний или false
     * @access public
     */
    public function get_available($id)
    {
        // Получаем объект
        if ( !$obj = $this->dof->storage($this->get_storage())->get($id) )
        {
            // Объект не найден
            return false;
        }
        $list = $this->get_list();
        // Определяем возможные состояния в зависимости от текущего статуса
        switch ( $obj->status )
        {
            // Неподтвержденный
            case 'tmp':
                return array('new' => $this->get_name('new'), 'cancel' => $this->get_name('cancel'));
                break;
            // Новый
            case 'new':
                return array('clientsign' => $this->get_name('clientsign'), 'cancel' => $this->get_name('cancel'));
                break;
            // Подписан клиентом
            case 'clientsign':
                return array('wesign' => $this->get_name('wesign'), 'cancel' => $this->get_name('cancel'));
                break;
            // Подписан с нашей стороны
            case 'wesign':
                return array('work' => $this->get_name('work'), 'frozen' => $this->get_name('frozen'), 'archives' => $this->get_name('archives'));
                break;
            // В работе
            case 'work':
                return array('frozen' => $this->get_name('frozen'), 'archives' => $this->get_name('archives'));
                break;
            // Приостановлен
            case 'frozen':
                return array('work' => $this->get_name('work'), 'archives' => $this->get_name('archives'));
                break;
            // Расторжен и переведен в архив
            case 'archives':
                return array();
                break;
            // Отменен
            case 'cancel':
                return array();
                break;
            default:
                return array('new' => $this->get_name('new'));
                break;
        }
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
        
        // Дополнительные действия, в зависимости от статуса, в который мы переходим
        switch ( $newstatus )
        {
            case 'new':
                if ( empty($object->studentid) OR empty($object->clientid) )
                {// если ученик или клиент не заполнен - сменять статус нельзя
                    return false;
                }
                break;
            case 'clientsign':
                break;
            case 'work':
                // Объект для обновления статуса
                $updateobject = new stdClass();
                $updateobject->id = $id;
                $updateobject->status = $newstatus;
            
                // Изменение статуса
                $result = $storage->update($updateobject);
                
                if ( $result )
                {// если статус сменился
                    $sbcs = $this->dof->storage('programmsbcs')->get_records([
                        'contractid' => $object->id, 
                        'status' => 'application'
                    ]);
                    if ( ! empty($sbcs) )
                    {// если у контракта есть подписки на программы
                        foreach ( $sbcs as $sbc )
                        {// сменим им статус на запланированы
                            if ( $object->status != 'frozen' )
                            {// если из frozen в work то application оставляем в application
                                $$result = $result & $this->dof->workflow('programmsbcs')->change($sbc->id, 'plan');
                            }
                            if ( isset($sbc->agenum) AND ( (isset($sbc->agroupid) AND $sbc->edutype == 'group') OR $sbc->edutype == 'individual') )
                            {// если у подписки указаны стартовый периоди параллель,
                                // а у групповых подписок группа, то сменим статус на активный
                                $result = $result & $this->dof->workflow('programmsbcs')->change($sbc->id, 'active');
                            }
                        }
                    }
                } else
                {// если нет, то плохо
                    return false;
                }
                if ( !$result )
                {// если что-то пошло не так, откатим изменения назад
                    $storage->update($object);
                }

                // Запись в историю изменения статусов
                $this->dof->storage('statushistory')->change_status(
                    $this->get_storage(),
                    $id,
                    $newstatus,
                    $object->status,
                    $options
                );
                
                // объект для отправки события
                $statusobj      = new stdClass();
                $statusobj->old = $object->status;
                $statusobj->new = $newstatus;
                $this->dof->send_event('workflow', 'contracts', 'changestatus', $id, $statusobj);
                return $result;
                break;
            case 'archives':
                // Помечаем ученика как несинхронизируемого с moodle
                $student = $this->dof->storage('persons')->get($object->studentid);
                // Если не передано опций, или в опциях не просят оставить пользователя
                if ( is_array($options) and ( !isset($options['muserkeep']) OR ! $options['muserkeep']) and $object->studentid )
                {// Удаляем ученика из Moodle, если он не встречается в других активных договорах
                    if ( !$this->dof->storage('contracts')->is_person_used($object->studentid, $object->id) )
                    {// Рассинхронизируем персону и пользователя
                        $this->dof->sync('personstom')->unsync($student, false);
                        //объект для обновления статуса персоны
                        $updateobject = new stdClass();
                        $updateobject->id = $object->studentid;
                        $updateobject->status = 'deleted';
                        $this->dof->storage('persons')->update($updateobject);
                    }
                }
                // переводим подписки на программы в статус неуспешно завершенная (failed)
                // ищем все подписки на данный контракт
                if ( $listsb = $this->dof->storage('programmsbcs')->get_programmsbcs_by_contractid_ids($id) )
                {// в каждой подписке переводим статус в ОТМЕНЕН
                    foreach ( $listsb as $idsb )
                    {
                        $statussbsc = $this->dof->storage('programmsbcs')->get($idsb)->status;
                        if ( $statussbsc == 'plan' OR $statussbsc == 'application' )
                        {// статус plan и application в canceled
                            $this->dof->workflow('programmsbcs')->change($idsb, 'canceled');
                        } elseif ( $statussbsc == 'active' OR $statussbsc == 'suspend'
                                OR $statussbsc == 'condactive' OR $statussbsc == 'onleave' )
                        {// статусы active и suspend в failed
                            $this->dof->workflow('programmsbcs')->change($idsb, 'failed');
                        }
                    }
                }
                break;
            case 'cancel':
                // Помечаем ученика как несинхронизируемого с moodle
                $student = $this->dof->storage('persons')->get($object->studentid);
                // Если не передано опций, или в опциях не просят оставить пользователя
                if ( is_array($options) and ( !isset($options['muserkeep']) OR ! $options['muserkeep']) and $object->studentid )
                {
                    // Удаляем ученика из Moodle, если он не встречается в других активных договорах
                    if ( !$this->dof->storage('contracts')->is_person_used($object->studentid, $object->id) )
                    {
                        // Рассинхронизируем персону и пользователя
                        $this->dof->sync('personstom')->unsync($student, false);
                        //объект для обновления статуса персоны
                        $updateobject = new stdClass();
                        $updateobject->id = $object->studentid;
                        $updateobject->status = 'deleted';
                        $this->dof->storage('persons')->update($updateobject);
                    }
                }
                // переводим подписки на программы в статус отменен
                // ищем все подписки на данный контракт
                if ( $listsb = $this->dof->storage('programmsbcs')->get_programmsbcs_by_contractid_ids($id) )
                {// в каждой подписке переводим статус в ОТМЕНЕН
                    foreach ( $listsb as $idsb )
                    {
                        $this->dof->workflow('programmsbcs')->change($idsb, 'canceled');
                    }
                }
                break;
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
            
            // объект для отправки события
            $statusobj      = new stdClass();
            $statusobj->old = $object->status;
            $statusobj->new = $newstatus;
            
            // посылаем событие о смене статуса
            return (bool)$this->dof->send_event('workflow', 'contracts', 'changestatus', $id, $statusobj);
        } else 
        {
            return false;
        }
    }

    /** Инициализируем состояние объекта
     * @param int id - id экземпляра
     * @return boolean true - удалось инициализировать состояние объекта 
     * false - не удалось перевести в указанное состояние
     * @access public
     */
    public function init($id)
    {
        // Получаем объект из contracts
        if ( !$obj = $this->dof->storage($this->get_storage())->get($id) )
        {
            // Объект не найден
            return false;
        }
        // Меняем статус
        $obj         = new stdClass();
        $obj->id     = intval($id);
        $obj->status = 'tmp';
        //$obj->statusdate = time();
        return $this->dof->storage($this->get_storage())->update($obj);
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
        $result               = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->personid     = $personid;
        $result->departmentid = $depid;
        if ( is_null($depid) )
        {// подразделение не задано - берем текущее
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        $result->objectid = $objectid;
        if ( !$objectid )
        {// если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        } else
        {// если указан - то установим подразделение
            $result->departmentid = $this->dof->storage($this->code())->get_field($objectid, 'departmentid');
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
                        has_right($acldata->plugintype,   $acldata->plugincode,
                                  $acldata->code,         $acldata->personid,
                                  $acldata->departmentid, $acldata->objectid);
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
                'manager'
            ]
        ];

        return $a;
    }

    // **********************************************
    // Собственные методы
    // **********************************************
    /** Конструктор
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
    
    /**
     * Проверить, возможно ли изменение параметров контракта 
     * 
     * @param int $id - id из таблицы contracts
     * @param stdClass $new - объект с новыми полями
     * @return bool - true, если возможно
     */
    public function is_change($id, stdClass $new)
    {
        if ( empty($id) )
        {// Если не передан ID
            return true;
        }
        if ( !$contract = $this->dof->storage($this->get_storage())->get($id) )
        {
            return false;
        }
        // Студента нельзя изменить, если договор на обучение актуален
        if ( isset($new->studentid) AND $contract->studentid != $new->studentid )
        {
            $actualstatuses = $this->get_meta_list('actual');
            if ( array_key_exists($contract->status, $actualstatuses) )
            {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Активация нового договора
     * 
     * @param int $id
     * 
     * @return bool
     */
    public function activate_new($id)
    {
        if ( $this->dof->workflow('contracts')->change($id, 'new') &&
                $this->dof->workflow('contracts')->change($id, 'clientsign') &&
                $this->dof->workflow('contracts')->change($id, 'wesign') &&
                $this->dof->workflow('contracts')->change($id, 'work') )
        {
            return true;
        }
        
        return false;
    }
}
