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
 * Синхронизация подписок в курсы Moodle. Класс плагина.
 *
 * @package    sunс
 * @subpackage courseenrolment
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_sync_courseenrolment implements dof_sync,dof_storage_config_interface
{
    /**
     * Объект деканата для доступа к общим методам
     * 
     * @var dof_control
     */
    protected $dof;
    
    /**
     * @var $logs - содержит переменные, нужные для ведения логов
     */
    protected $logs;
    
    /**
     * @var $cenrolcfg - переменная с данными из конфигурационного файла
     */
    protected $cenrolcfg;
    
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
        return true;
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
        return true;
    }
    
    /**
     * Возвращает версию установленного плагина
     *
     * @return int - Версия плагина
     */
    public function version()
    {
        return 2018082601;
    }
    
    /** 
     * Возвращает версии интерфейса Деканата, с которыми этот плагин может работать
     * 
     * @return string
     */
    public function compat_dof()
    {
        return 'aquarium_bcdefg';
    }

    /**
     * Возвращает версии стандарта плагина этого типа, которым этот плагин соответствует
     * 
     * @return string
     */
    public function compat()
    {
        return 'ancistrus';
    }
    
    /** 
     * Возвращает тип плагина
     * 
     * @return string 
     */
    public function type()
    {
        return 'sync';
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
        return 'courseenrolment';
    }
    
    /** 
     * Функция получения настроек для плагина
     *
     * @return stdClass[]
     */
    public function config_default($code=null)
    {
        $configs = [];
        
        // шаблон для формирования названия группы в курсе в Moodle
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'mdlgroup_name_template';
        $obj->value = '{TEACHER_FULLNAME_INITIALS} {AGE_NAME} {CSTREAM_NAME}';
        $configs[$obj->code] = $obj;
        
        // опция отчисления пользователей из курсов
        $obj = new stdClass();
        $obj->type = 'select';
        $obj->code = 'unenrol_mode';
        $obj->value = 'always_unenrol';
        $configs[$obj->code] = $obj;
        
        return $configs;
    }
    
    /**
     * Возвращает список плагинов, без которых этот плагин работать не может
     *
     * @return array
     */
    public function need_plugins()
    {
        return [
            'modlib' => [
                'ama'        => 2016071500,
                'plagiarism' => 2016041300,
                'nvg'        => 2008060300,
                'widgets'    => 2009050800
            ],
            'storage' => [
                'plans'         => 2011020800,
                'cstreams'      => 2011032900,
                'appointments'  => 2013110100,
                'cpassed'       => 2010123000,
                'programmitems' => 2011041406,
                'persons'       => 2015012000,
                'config'        => 2011080900,
                'acl'           => 2011040504
            ],
            'workflow' => [
                'cpassed'  => 2015011300,
                'cstreams' => 2015011300
            ],
            'im' => [
                'journal' => 2011021500
            ]
        ];
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
            'modlib' => [
                'ama'        => 2016071500,
                'plagiarism' => 2016041300,
                'nvg'        => 2008060300,
                'widgets'    => 2009050800
            ],
            'storage' => [
                'plans'         => 2011020800,
                'cstreams'      => 2011032900,
                'appointments'  => 2013110100,
                'cpassed'       => 2010123000,
                'programmitems' => 2011041406,
                'persons'       => 2015012000,
                'config'        => 2011080900,
                'acl'           => 2011040504
            ],
            'workflow' => [
                'cpassed'  => 2015011300,
                'cstreams' => 2015011300
            ],
            'im' => [
                'journal' => 2011021500
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
            // События смены статусов у cpassed и cstreams
            ['plugintype' => 'workflow', 'plugincode' => 'cpassed', 'eventcode' => 'cpassed_active'],
            ['plugintype' => 'workflow', 'plugincode' => 'cpassed', 'eventcode' => 'cpassed_not_active'],
            ['plugintype' => 'workflow', 'plugincode' => 'cstreams', 'eventcode' => 'cstreams_active'],
            ['plugintype' => 'workflow', 'plugincode' => 'cstreams', 'eventcode' => 'cstreams_passive'],
            ['plugintype' => 'workflow', 'plugincode' => 'cstreams', 'eventcode' => 'cstreams_not_active'],
            // События работы справочников
            ['plugintype'=>'storage', 'plugincode' => 'cpassed', 'eventcode' => 'insert'],
            ['plugintype'=>'storage', 'plugincode' => 'cpassed', 'eventcode' => 'delete'],
            ['plugintype'=>'storage', 'plugincode' => 'cstreams', 'eventcode' => 'insert'],
            ['plugintype'=>'storage', 'plugincode' => 'cstreams', 'eventcode' => 'update'],
            ['plugintype'=>'storage', 'plugincode' => 'cstreams', 'eventcode' => 'delete'],
            ['plugintype' => 'storage',  'plugincode' => 'cstreams', 'eventcode' => 'сhanged_name'],
            ['plugintype' => 'storage',  'plugincode' => 'persons', 'eventcode' => 'person_sync_saved']
            
        ];
    }
    
    /** 
     * Требуется ли запуск cron в плагине
     * 
     * @return bool
     */
    public function is_cron()
    {
        $interval = $this->get_cfg('sync_interval');
        return (bool)$interval;
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
    public function is_access($do, $objid = NULL, $userid = NULL)
    {
        // Получаем ID персоны, с которой связан данный пользователь 
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);

        $depid = null;
        
        switch ( $do )
        {// Определяем дополнительные параметры в зависимости от запрашиваемого права
            default:
                break;
        }
        
        if ( $this->dof->is_access('datamanage') OR
             $this->dof->is_access('admin') OR
             $this->dof->is_access('manage')
           )
        {// Полный доступ для администраторов Moodle
            return true;
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
    public function catch_event($gentype, $gencode, $eventcode, $id, $mixedvar)
    {
        try 
        {
            if ( $gentype === 'workflow' AND $gencode === 'cpassed' )
            {
                switch($eventcode)
                {
                    // Выполнение действий при активации cpassed'а
                    case 'cpassed_active':
                       // Обработчик события активации подписки на дисциплину
                       
                       $this->event_cpassed_active($id);
                       break;
                    // Выполнение действий при остановке cpassed'а
                    case 'cpassed_not_active':
                       // Обработчик события активации подписки на дисциплину
                       $this->event_cpassed_notactive($id);
                       break;
                }
            }
            if ( $gentype === 'workflow' AND $gencode === 'cstreams' )
            {
                switch($eventcode)
                {
                    // Выполнение действий при активации cstreams'а
                    case 'cstreams_active': 
                        $this->event_enrol_teacher($id);
                        break;
                    // Выполнение действий при переходе cstreams'а в неактивные статусы
                    case 'cstreams_passive': 
                        $this->mdl_delete_cstream_group($id);
                        break;
                    // Выполнение действий при остановке cstreams'а
                    case 'cstreams_not_active': 
                        $this->event_unenrol_teacher($id);
                        break;
                }
            }
            if ( $gentype === 'storage' AND $gencode === 'cpassed' )
            {
                switch($eventcode)
                {
                    // При добавлении ничего не происходит
                    case 'insert': 
                        break;
                    // Отписать при удалении подписки
                    case 'delete': 
                        $this->event_cpassed_deleted($id, $mixedvar);
                        break;
                }
            }
            if ( $gentype === 'storage' AND $gencode === 'cstreams' )
            {
                switch($eventcode)
                {
                    // При добавлении ничего не происходит
                    case 'insert': 
                        break;
                    // При добавлении ничего не происходит
                    case 'update': 
                        $this->event_update_cstream($id, $mixedvar);
                        break;
                    // Отписать при удалении подписки
                    case 'delete': 
                        $this->event_unenrol_teacher($id, $mixedvar);
                        break;
                    case 'сhanged_name':
                        
                        // отливливаем смену названия учебного процесса
                        $cstream = $this->dof->storage('cstreams')->get_record(['id' => $id]);
                        if ( ! empty($cstream) &&
                                ! empty($cstream->mdlgroup) && 
                                ! empty($cstream->mdlcourse) )
                        {
                            // обновление названия группы Moodle учебного процесса
                            $this->mdl_group_refresh_name($cstream);
                        }
                        break;
                        
                }
            }
            if ( $gentype === 'storage' && $gencode === 'persons' && $eventcode == 'person_sync_saved' )
            {// Персону только что синхронизировали 
                
                // Список активных статусов для cpassed'ов
                $cpassedactivestatuses = $this->dof->workflow('cpassed')->get_meta_list('active');
                // Список cpassed'ов персоны в активных статусах
                $cpasseds = $this->dof->storage('cpassed')->get_records([
                    'studentid' => $id,
                    'status' => array_keys($cpassedactivestatuses)
                ]);
                if( !empty($cpasseds) )
                {
                    foreach($cpasseds as $cpassed)
                    {
                        // Подписка на курс
                        $this->enrol_to_course(
                            $cpassed->programmitemid,
                            $cpassed->studentid,
                            $cpassed->cstreamid
                        );
                    }
                }
            }
        } catch ( dof_sync_courseenrolment_exception $e )
        {// Произошла ошибка
            $this->dof->add_to_log(
                'sync',
                'courseenrolment',
                $e->errorcode,
                '',
                (string)$e,
                $id
            );
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
        if ( $loan == 3 && $this->is_cron() )
        {
            return $this->sync_grades();
        }
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
        if ( $code == "fix_unenrols" )
        {
            // задача на отписки слушателей и учителей, которые ранее по каким-то причинам не были отписаны
            $this->todo_fix_unenrols();
        }
        
        return true;
    }
    
    /**
     * задача на отписку слушателей и учителей, которые ранее по каким-то причинам не были отписаны
     * 
     * @return void
     */
    protected function todo_fix_unenrols()
    {
        // отписка слушателей
        $cpassedstatuses = $this->dof->workflow('cpassed')->get_list();
        unset($cpassedstatuses['active']);
        $cpassedstatuses = array_keys($cpassedstatuses);
        $num = 0;
        while ( $cpasseds = $this->dof->storage('cpassed')->get_records(['status' => $cpassedstatuses], '', 'id', $num, 10000) )
        {
            $num += 10000;
            foreach ( $cpasseds as $cpassed )
            {
                try 
                {
                    $this->event_cpassed_notactive($cpassed->id);
                } catch(dof_exception $e)
                {
                    // пропустим
                }
            }
        }
        
        // отписка преподавателей
        $num = 0;
        $cstreamsstatuses = $this->dof->workflow('cpassed')->get_list();
        unset($cstreamsstatuses['active']);
        $cstreamsstatuses = array_keys($cstreamsstatuses);
        while ( $cstreams = $this->dof->storage('cstreams')->get_records(['status' => $cstreamsstatuses], '', 'id', $num, 10000) )
        {
            $num += 10000;
            foreach ( $cstreams as $cstream )
            {
                try
                {
                    $this->event_unenrol_teacher($cstream->id);
                } catch(dof_exception $e)
                {
                    // пропустим
                }
            }
        }
    }
    
    /**
     * Конструктор
     *
     * @param dof_control $dof - объект ядра деканата
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
        
        // Подключение классов исключений
        $url = $this->dof->plugin_path($this->type(), $this->code(), '/classes/exception.php');
        require_once($url);
    }
    
    // **********************************************
    // Собственные методы
    // **********************************************
    
    /**
     * Обработчик события активации подписки на дисциплину
     *
     * @param int $cpassedid - ID подписки на дисциплину
     *
     * @return bool - Результат обработки события
     * 
     * @throws dof_sync_courseenrolment_exception
     */
    private function event_cpassed_active($cpassedid)
    {
        // Получение подписки на дисциплину
        $cpassed = $this->dof->storage('cpassed')->get($cpassedid);
        if ( ! $cpassed )
        {
            throw new dof_sync_courseenrolment_exception(
                'error_cpassed_not_found',
                'sync_courseenrolment'
            );
        }
        // Запись студента на курс
        return $this->enrol_to_course(
            $cpassed->programmitemid, 
            $cpassed->studentid,
            $cpassed->cstreamid
        );
    }
    
    /**
     * Обработчик события деактивации подписки на дисциплину
     *
     * @param int $cpassedid - ID подписки на дисциплину
     *
     * @return bool - Результат обработки события
     *
     * @throws dof_sync_courseenrolment_exception
     */
    private function event_cpassed_notactive($cpassedid)
    {
        // Получение подписки на дисциплину
        $cpassed = $this->dof->storage('cpassed')->get($cpassedid);
        if ( ! $cpassed )
        {
            throw new dof_sync_courseenrolment_exception(
                'error_cpassed_not_found',
                'sync_courseenrolment'
            );
        }
        
        // Запись студента на курс
        return $this->unenrol_from_course(
            $cpassed->programmitemid,
            $cpassed->studentid,
            'student',
            $cpassed->cstreamid
        );
    }
    
    /**
     * Обработчик события удаления подписки на дисциплину
     *
     * @param array $deletedobj - Массив удаленных подписок на дисциплину
     *
     * @return bool - Результат обработки события
     */
    private function event_cpassed_deleted($deletedcpasseds)
    {
        // Получаем массив статусов cpassed
        $statuses = array_keys(
            $this->dof->workflow('cpassed')->get_meta_list('active')
        );
    
        $result = true;
        
        foreach ( $deletedcpasseds as $deletedcpassed )
        {// Производим отписку для каждого из элементов
    
            // Получаем курс , на который ссылалась текущая подписка(cpassed)
            $mdlcourse = $this->dof->storage('programmitems')->
                get_field($deletedcpassed->programmitemid, 'mdlcourse');
            
            // Проверка наличия аналогичных подписок на дисциплину
            $cpasseds = $this->dof->storage('cpassed')->get_records([
                'studentid' => $deletedcpassed->studentid,
                'status' => $statuses
            ]);
            if ( ! empty($cpasseds) )
            {// Есть активные дисциплины
                foreach ( $cpasseds as $cpassed )
                {
                    if ( $mdlcourse === $this->dof->storage('programmitems')->
                            get_field($cp->programmitemid, 'mdlcourse') )
                    {// Найдена активная подписка пользователя с привязкой к целевому курсу
                        // Отчисление из курса не требуется
                        continue;
                    }
                }
            }
    
            // Проверка на оставшиеся подписки
            // @todo - устаревшая проверка, изучить необходимость
            $params = [];
            $params['studentid'] = $cpassed->studentid;
            $params['programmitemid'] = $cpassed->programmitemid;
            $params['noid'] = $cpassed->id;
            $params['status'] = $statuses;
            $select = $this->dof->storage('cpassed')->get_select_listing($params);
    
            if ( ! $this->dof->storage('cpassed')->is_exists_select($select) )
            {// Если подписок на дисциплину больше нет - отписываем пользователя из курса moodle
                $result = ( $result && $this->unenrol_from_course($cpassed->programmitemid, $cpassed->studentid) );
            }
        }
        return $result;
    }
    
    /**
     * Метод для записывания преподавателя на курс moodle
     *
     * Проверяет возможность записать преподавателя и производит запись, если это возможно
     *
     * @param int $cstreamid - id записи в таблице cstreams (Учебные процессы/Предмето-класс)
     *
     * @return bool - false, если в ходе работы произошли ошибки
     *              - true, если работа завершена без ошибок
     * @access private
     */
    private function event_enrol_teacher($cstreamid)
    {
        // Получаем запись
        $cstream = $this->dof->storage('cstreams')->get($cstreamid);
    
        if ( ! is_object($cstream) )
        {// неправильный формат даных
            return false;
        }
        // Получаем ID пользователя
        $personid = $this->dof->storage('cstreams')->get_cstream_teacherid($cstream->id);
    
        // Подписываем преподавателя на курс moodle
        return $this->enrol_to_course($cstream->programmitemid, $personid, $cstream->id, 'teacher');
    }
    
    /**
     * Метод для отписывания преподавателя от курса moodle
     *
     * Проверяет возможность отписать преподавателя и производит отписку, если это возможно
     * Позволяет отписать преподавателя либо с существующего учебного процесса
     * по cstreamid, либо с удаленных учебных процессов, объекты которых переданы
     * в массиве $deletedobj
     *
     * @param int $cstreamid - id записи в таблице cstreams (Учебные процессы/Предмето-класс)
     * @param array $deletedobj - массив удаленных записей в таблице cstreams
     *
     * @return bool - false, если в ходе работы произошли ошибки
     *              - true, если работа завершена без ошибок
     * @access private
     */
    private function event_unenrol_teacher($cstreamid = NULL, $deletedobj = NULL)
    {
        $allcstreams = [];
        if ( ! empty($cstreamid) )
        {
            // получеие записи учебного процесса
            $cstream = $this->dof->storage('cstreams')->get($cstreamid);
            if ( ! empty($cstream) )
            {
                $allcstreams[] = $cstream;
            }
        }
        if ( ! empty($deletedobj) )
        {
            $allcstreams = array_merge($allcstreams, $deletedobj);
        }
        
        // Результат формируется комплексно
        $return = true;
        foreach ( $allcstreams as $cstream )
        {// Передали ID учебного процесса, пробуем отписать преподавателя
    
            if ( ! is_object($cstream) )
            {// Не передан объект
                continue;
            }
            
            // Получаем ID преподавателя
            $personid = $this->dof->storage('cstreams')->get_cstream_teacherid($cstream->id);
            if ( empty($personid) )
            {// Преподаватель не найден
                return false;
            }
    
            /*
             * Получим все учебные процессы, ссылающиеся на один и тот же курс moodle,
             * учителем в которых является одна и та же персона.(Кроме переданного в $cstreamid)
             * Если такие процессы есть, то отписывать преподавателя от курса не нужно
             */
            
            // Получим ID курса Moodle по учебному процессу
            if ( ! empty($cstream->mdlcourse) )
            {
                // Получение курса Moodle из учебного процесса
                $mdlcourse = $cstream->mdlcourse;
            } else 
            {
                // Получение курса, связанного с дисциплиной
                $mdlcourse = $this->dof->storage('programmitems')->get_field($cstream->programmitemid, 'mdlcourse');
            }
            if ( empty($mdlcourse) )
            {
                // нет курса moodle, отписывать неоткуда
                return true;
            }
            
            // Получим активные статусы дисциплин
            $statuses = array_keys($this->dof->workflow('programmitems')->get_meta_list('active'));
            // Получим все активные дисциплины, привязанные к курсу
            $programmitems = $this->dof->storage('programmitems')->get_records(array(
                'mdlcourse' => $mdlcourse,
                'status' => $statuses
            ));
            // Получим активные статусы учебных процессов
            $statusescstreams = array_keys($this->dof->workflow('cstreams')->get_meta_list('actual'));
            // Получим все активные дисциплины, привязанные к курсу
            $cstreams = $this->dof->storage('cstreams')->get_records(array(
                'mdlcourse' => $mdlcourse,
                'status' => $statusescstreams
            ));
            if ( empty($programmitems) && empty($cstreams) )
            {// Нет дисциплин, привязанных к курсу moodle
                return false;
            }
            // Переформируем массив на ключи
            $programmitems = array_keys($programmitems);
            // Получим активные статусы договоров
            $statuses = array_keys($this->dof->workflow('eagreements')->get_meta_list('active'));
            // Получим все активные договора на работу у учителя
            $personeagerments = $this->dof->storage('eagreements')->get_records(array(
                'personid' => $personid,
                'status' => $statuses
            ));
            
            if ( empty($personeagerments) )
            {// Нет договоров на работу у преподавателя
                // @TODO Определить, что надо делать в таком случае.
                return false;
            }
            // Переформируем массив на ключи
            $personeagerments = array_keys($personeagerments);
            // Получим активные статусы должостных назначений учителя
//             $statuses = array_keys($this->dof->workflow('appointments')->get_meta_list('active'));
            //Получим все активные должостные назначения учителя
            $personappointments = $this->dof->storage('appointments')->get_records(array(
                'eagreementid' => $personeagerments,
                // при назначение на учебный процесс, статус не учитывается и пользователя зачисляет на курс даже с ДН в статусе "черновик"
                //'status' => $statuses
            ));
            
            if ( empty($personappointments) )
            {// Нет назначений на должности у персоны, которая считается преподавателем
                // @TODO Определить, что надо делать в таком случае.
                return false;
            }
            // Переформируем массив на ключи
            $personappointments = array_keys($personappointments);
            // Получим активные статусы учебных процессов
            $statuses = array_keys($this->dof->workflow('cstreams')->get_meta_list('active'));
            
            // проверим, ведет ли текущий преподаватель текущий курс Moodle в других учебных процессах
            $cstreamswork = $this->dof->storage('cstreams')->get_records(array(
                'appointmentid' => $personappointments,
                'mdlcourse' => $mdlcourse,
                'status' => $statuses
            ));
            unset($cstreamswork[$cstream->id]);
            if ( ! empty($cstreamswork) )
            {
                foreach ( $cstreamswork as $cstream )
                {
                    if ( $cstream->mdlcourse == $mdlcourse )
                    {
                        // преподаватель ведет курс Moodle в другом учебном процессе
                        return false;
                    }
                }
            }
            
            // проверим учебные процессы по дисциплинам
            $cstreamspitems = $this->dof->storage('cstreams')->get_records(array(
                'appointmentid' => $personappointments,
                'programmitemid' => $programmitems,
                'status' => $statuses
            ));
            unset($cstreamspitems[$cstream->id]);
            if ( ! empty($cstreamspitems) )
            {
                return false;
            }
            $return = ($return and $this->unenrol_from_course($cstream->programmitemid, $personid, 'teacher', $cstream->id));
        }
        
        return $return;
    }
    
    /**
     * Метод для отписывания/подписывания преподавателя на курс moodle рпи изменении учебного процесса
     *
     * @param int $cstreamid - id записи в таблице cstreams (Учебные процессы/Предмето-класс)
     * @param array $mixedvar - массив со старой и обновленной записью
     *
     * @return bool - false, если в ходе работы произошли ошибки
     *              - true, если работа завершена без ошибок
     * @access private
     */
    private function event_update_cstream($cstreamid, $mixedvar = NULL)
    {
        // Получаем запись
        $cstream = $this->dof->storage('cstreams')->get($cstreamid);
    
        if ( ! is_object($cstream) )
        {// неправильный формат даных
            return false;
        }
    
        if ( $mixedvar['new']->appointmentid != $mixedvar['old']->appointmentid AND
            $mixedvar['old']->status != 'plan' )
        {// Преподаватель курса изменился
    
            if ( $mixedvar['old']->appointmentid != 0 )
            {// Отпишем старого преподавателя от курса
    
                // Получаем персону
                $person = $this->dof->storage('appointments')->
                get_person_by_appointment($mixedvar['old']->appointmentid);
                if ( empty($person) )
                {// Персона не найдена
                    return false;
                }
    
                // Проверка
                $params = array();
                $params['teacherid'] = $person->id;
                $params['programmitemid'] = $mixedvar['old']->programmitemid;
                $params['noid'] = $mixedvar['old']->id;
                $params['status'] = array_keys($this->dof->workflow('cstreams')->get_meta_list('active'));
                $select = $this->dof->storage('cstreams')->get_select_listing($params);
                if ( ! $this->dof->storage('cstreams')->is_exists_select($select) )
                {// Если потоков у преподавателя больше нет, отписываем его с курса
                    if ( ! $this->unenrol_from_course($mixedvar['old']->programmitemid, $person->id, 'teacher', $cstream->id) )
                    {// Ошибка при отписывании преподавателя
                        return false;
                    }
                }
            }
    
            if ( $mixedvar['new']->appointmentid != 0 )
            {// У нас есть новый преподаватель - запишем его на курс
                // Получаем персону
                $person = $this->dof->storage('appointments')->
                get_person_by_appointment($mixedvar['new']->appointmentid);
    
                if ( empty($person) )
                {// Персона не найдена
                    return false;
                }
                // Подписываем на курс
                if ( ! $this->enrol_to_course($mixedvar['new']->programmitemid, $person->id, $cstream->id, 'teacher') )
                {// Ошибка при подписывании преподавателя
                    return false;
                }
            }
        }
        return true;
    }
    
    /** Отписать пользователя из курса
     * 
     * @return 
     *        - true если пользователя удалось подписать
     *        - false если произошла ошибка
     * @param int $mdlcourseid - id курса в Moodle с которого отписывается пользователь
     * @param int $mdluserid - id пользователя в moodle
     * 
     * @todo добавить генерацию исключений когда появятся соответствующие классы
     */
    protected function mdl_unenrol_from_course($mdlcourseid, $mdluserid, $type = 'student', $unenrolmode = 'always_unenrol')
    {
        // отписываем пользователя из курса, используя модуль ama
        if( $this->dof->modlib('ama')->course($mdlcourseid)->get() )
        {// Если курс существует - отписываем
            try
            {
                if( ! $instance = $this->dof->modlib('ama')->course($mdlcourseid)->enrol_manager(false)->get_dof_enrol_instance() )
                {
                    return true;
                }
                $rolemanager = $this->dof->modlib('ama')->course($mdlcourseid)->role(false, $type);
                $roles = $this->dof->modlib('ama')->user($mdluserid)->get_user_roles_in_course($mdlcourseid);
                $dofrole = $rolemanager->get_id();
                if( in_array($dofrole, $roles) )
                {
                    // Лишение пользователя роли
                    $rolemanager->role_unassign($mdluserid);
                    unset($roles[$dofrole]);
                    $dofroleunassign = true;
                } else 
                {
                    $dofroleunassign = false;
                }
                
                $dofenrol = $this->dof->modlib('ama')->course($mdlcourseid)->enrol_manager($instance->id)->get_instance_manager();
                // Получение подписок пользователя
                $enrolments = $this->dof->modlib('ama')->user($mdluserid)->get_user_enrolments_in_course($mdlcourseid);
                
                switch($unenrolmode)
                {
                    case 'always_unenrol':
                        foreach($enrolments as $id => $enrolment)
                        {
                            if( $enrolment->plugin == 'dof' )
                            {
                                $this->dof->modlib('ama')->course($mdlcourseid)->enrol_manager($enrolment->enrolid)->unenrol_user($mdluserid);
                                unset($enrolments[$id]);
                                break;
                            }
                        }
                        break;
                    case 'with_manual_creation_unenrol':
                        foreach($enrolments as $id => $enrolment)
                        {
                            if( $enrolment->plugin == 'dof' )
                            {
                                unset($enrolments[$id]);
                                break;
                            }
                        }
                        if( empty($enrolments) && ! empty($roles) )
                        {// Если в курсе была только одна dof подписка и есть дополнительные роли
                            $manualenrol = $this->dof->modlib('ama')->course($mdlcourseid)->enrol_manager(false)->create_instance('manual');
                            if( ! empty($manualenrol) )
                            {// Если получили инстанс ручного способа записи - подписываем пользователя
                                if( $dofroleunassign )
                                {// Если была dof роль и мы ее сняли, то и подписываем в ней же
                                    $manualenrol->enrol_user($mdluserid, $dofrole);
                                } else 
                                {// Если не было dof роли изначально - не передаем роль
                                    $manualenrol->enrol_user($mdluserid);
                                }
                            }
                            // Убираем dof подписку
                            $dofenrol->unenrol_user($mdluserid);
                        } elseif( ! empty($enrolments) && ! empty($roles) )
                        {// Есть еще подписки отличные от dof и есть дополнительные роли
                            // Убираем dof подписку
                            $dofenrol->unenrol_user($mdluserid);
                        } elseif( ! empty($enrolments) && empty($roles) )
                        {// Есть еще подписки отличные от dof и нет ролей
                            if( $dofroleunassign )
                            {
                                // Вернем снятую роль
                                $this->dof->modlib('ama')->course($mdlcourseid)->role(false, $type)->role_assign($mdluserid);
                            }                            
                            // Убираем dof подписку
                            $dofenrol->unenrol_user($mdluserid);
                        } else
                        {
                            // Убираем dof подписку
                            $dofenrol->unenrol_user($mdluserid);
                        }
                        break;
                    default:
                        $dofenrol->unenrol_user($mdluserid);
                        break;
                }
                $this->dof->add_to_log('modlib', 'ama', 'unenrol', 'view.php?id=' . $mdlcourseid, '', $mdluserid);
            } catch ( coding_exception $e )
            {// Ошибка записи на курс
                $this->dof->add_to_log('modlib', 'ama', 'unenrol_error', 'view.php?id=' . $mdlcourseid, $e->errorcode, $mdluserid);
                return false;
            } catch ( dml_exception $e )
            {// Ошибка запроса в БД
                $this->dof->add_to_log('modlib', 'ama', 'unenrol_error', 'view.php?id=' . $mdlcourseid, $e->errorcode, $mdluserid);
                return false;
            }
            return true;
        } else 
        {// Если курса нет, пользователь уже отписан - выбросим exception
            $a = new stdClass();
            $a->courseid = $mdlcourseid;
            $a->userid = $mdluserid;
            throw new dof_sync_courseenrolment_exception(
                'unenrol_from_deleted_course', 
                '', 
                '', 
                $a, 
                $this->dof->get_string('unenrol_from_deleted_course', 'courseenrolment', $a, 'sync')
            );
        }
    }
    
    /** 
     * Записать пользователя moodle в группу moodle
     * 
     * @param int $mdlcourseid - id курса в Moodle в котором находится группа
     * @param int $mdlgroupid - id группы в курсе, куда будет записываться пользователь
     * @param int $mdluserid - id пользователя в moodle
     * 
     * @return bool
     */
    protected function mdl_add_to_group($mdlcourseid, $mdlgroupid, $mdluserid)
    {
        $exists = $this->dof->modlib('ama')->course($mdlcourseid)->
            group(false)->is_exists($mdlgroupid);
        if ( ! $exists )
        {// Указанная группа не найдена
            throw new dof_sync_courseenrolment_exception(
                'error_cstream_group_not_found',
                'sync_courseenrolment'
            );
        }
        
        return $this->dof->modlib('ama')->course($mdlcourseid)->group($mdlgroupid)->add_member($mdluserid);
    }
    
    /** 
     * Получить ID пользователя Moodle, синхронизированного с целевой персоной Деканата
     * 
     * @param int $personid - ID персоны
     * 
     * @return int - ID пользователя Moodle
     * 
     * @throws dof_sync_courseenrolment_exception - В случае ошибки получения пользователя
     */
    protected function get_mdl_userid($personid)
    {
        $mdluserid = $this->dof->storage('persons')->
            get_field((int)$personid, 'mdluser');
        if ( $mdluserid === false )
        {// Персона не найдена
            throw new dof_sync_courseenrolment_exception(
                'error_person_not_found',
                'sync_courseenrolment'
            );
        }
        
        $mdluserid = (int)trim($mdluserid);
        if ( empty($mdluserid) )
        {// Персона не синхронизирована
            throw new dof_sync_courseenrolment_exception(
                'error_person_not_syncronized',
                'sync_courseenrolment'
            );
        }
        
        return $mdluserid;
    }
    
    /** 
     * Получить ID курса Moodle, связанного с целевой дисциплиной
     * 
     * @param int $programmitemid - ID дисциплины
     * 
     * @return int - ID курса Moodle
     * 
     * @throws dof_sync_courseenrolment_exception - В случае ошибки получения курса
     */
    protected function get_mdl_course($programmitemid)
    {
        $programmitem = $this->dof->storage('programmitems')->
            get((int)$programmitemid);
            
        if ( empty($programmitem) )
        {// Дисциплина не найдена
            throw new dof_sync_courseenrolment_exception(
                'error_programmitem_not_found',
                'sync_courseenrolment',
                '',
                $programmitemid
            );
        }
        
        $courseid = (int)trim($programmitem->mdlcourse);
        if ( empty($courseid) )
        {// Курс не указан
            $name = $programmitem->name.' ['.$programmitem->id.']';
            throw new dof_sync_courseenrolment_exception(
                'error_programmitem_course_not_linked',
                'sync_courseenrolment',
                '',
                $name
            );
        }
        
        return $courseid;
    }
    
    /**
     * Получить ID группы Moodle, связанной с целевым учебным процессом
     *
     * @param int $cstreamid - ID учебного процесса
     *
     * @return int - ID группы Moodle
     *
     * @throws dof_sync_courseenrolment_exception - В случае ошибки получения группы
     */
    protected function get_mdl_group($cstreamid)
    {
        $mdlgroupid = $this->dof->storage('cstreams')->
            get_field((int)$cstreamid, 'mdlgroup');
        if ( $mdlgroupid === false )
        {// Учебный процесс не найден
            throw new dof_sync_courseenrolment_exception(
                'error_cstream_not_found',
                'sync_courseenrolment'
            );
        }
        
        $mdlgroupid = (int)trim($mdlgroupid);
        if ( empty($mdlgroupid) )
        {// Группа не связана
            throw new dof_sync_courseenrolment_exception(
                'error_cstream_coursegroup_not_linked',
                'sync_courseenrolment'
            );
        }
        
        return $mdlgroupid;
    }
    
    /**
     * Формирование названия группы Moodle
     * 
     * @return string
     */
    protected function get_mdl_group_name($cstream)
    {
        // получение шаблона названия группы
        $groupname = $this->dof->storage('config')->get_config_value(
                'mdlgroup_name_template',
                $this->type(),
                $this->code(),
                $cstream->departmentid
                );
        if ( ! empty($groupname) )
        {
            // макподстановка фио преподавателю с инициалами
            if ( (strpos($groupname, '{TEACHER_FULLNAME_INITIALS}') !== false) &&
                    ($teacherid = $this->dof->storage('cstreams')->get_cstream_teacherid($cstream->id)) )
            {
                $groupname = str_replace(
                        '{TEACHER_FULLNAME_INITIALS}',
                        $this->dof->storage('persons')->get_fullname_initials($teacherid),
                        $groupname);
            } else
            {
                // преподаватель отсутствует, заменим макроподстановку на пустую строку
                $groupname = str_replace('{TEACHER_FULLNAME_INITIALS}', '', $groupname);
            }
            
            // макподстановка названия учебного периода
            if ( (strpos($groupname, '{AGE_NAME}') !== false) &&
                    ($agename = $this->dof->storage('ages')->get_field($cstream->ageid, 'name')) )
            {
                $groupname = str_replace(
                        '{AGE_NAME}',
                        $agename,
                        $groupname);
            } else
            {
                $groupname = str_replace('{AGE_NAME}', '', $groupname);
            }
            
            // макподстановка названия учебного процесса
            if ( (strpos($groupname, '{CSTREAM_NAME}') !== false) )
            {
                $groupname = str_replace(
                        '{CSTREAM_NAME}',
                        $cstream->name,
                        $groupname);
            }
            
            // макподстановка интервала учебного процесса
            if ( (strpos($groupname, '{CSTREAM_INTERVAL}') !== false) )
            {
                $groupname = str_replace(
                        '{CSTREAM_INTERVAL}',
                        dof_userdate($cstream->begindate, '%d.%m.%Y'). ' - ' . dof_userdate($cstream->enddate, '%d.%m.%Y'),
                        $groupname);
            }
            
            // очистка от пробелов слева и справа
            $groupname = trim($groupname);
        } else
        {// на случай пустого конфига
            // название группы
            $groupname = '';
            // Формируем название группы: Учитель + период + название предмето-класса
            if ( $teacherid = $this->dof->storage('cstreams')->get_cstream_teacherid($cstream->id) )
            {// если у потока есть учитель - то запомним его имя
                $groupname .= $this->dof->storage('persons')->get_fullname_initials($teacherid);
            }
            if ( $agename = $this->dof->storage('ages')->get_field($cstream->ageid, 'name') )
            {
                $groupname .= ' '.$agename;
            }
            $groupname .= ' '.$cstream->name;
        }
        
        return $groupname;
    }
    
    /**
     * Обновление группы Moodle учебного процесса
     * 
     * @return bool
     */
    protected function mdl_group_refresh_name(stdClass $cstream)
    {
        // обновление названия
        $data = new stdClass();
        $data->name = $this->get_mdl_group_name($cstream);
        if ( ! $group = $this->dof->modlib('ama')->course($cstream->mdlcourse)->group($cstream->mdlgroup) )
        {
            return false;
        }
        if ( ! $group->update($data) )
        {
            return false;
        }
        
        return true;
    }
    
    /** Создать новую группу для потока в указанном курсе Moodle.
     * Одновремено записывает id этой группы в поток
     * @todo создавать нормальное имя группы а не название потока
     * 
     * @param int $mdlcourseid - id курса в Moodle
     * @param int $cstreamid - id учебного потока, для которого создается группа
     * 
     * @return bool|int - id созданной в Moodle группы или false в случае ошибки
     */
    protected function mdl_create_cstream_group($mdlcourseid, $cstreamid)
    {
        if ( ! $this->dof->modlib('ama')->course(FALSE)->is_exists($mdlcourseid) )
        {// аккуратно обходим API модуля ama, НЕ ДАВАЯ ЕМУ СОЗДАТЬ КУРС при проверке его существования
            // если курс не существует - не продолжаем
            return false;
        }
        
        if ( ! $cstream = $this->dof->storage('cstreams')->get($cstreamid) )
        {// проверяем, существует ли поток, для которого создается группа
            return false;
        }
        
        // получение шаблона названия группы
        $groupname = $this->get_mdl_group_name($cstream);
        
        // курс точно существует, и НЕ БУДЕТ СОЗДАН ПРИ ПОПЫТКЕ К НЕМУ ОБРАТИТЬСЯ
        // Теперь попробуем создать в нем группу и назвать ее нужным именем
        $data = new stdClass();
        $data->name = $groupname;
        if ( ! $group = $this->dof->modlib('ama')->course($mdlcourseid)->group() )
        {
            return false;
        }
        // подберем не занятое имя для группы, чтобы метод update смог ее создать.
        $i = 1;
        while ( ! $group->is_unique($data)) {
            if (substr($data->name, strlen($data->name) - strlen($cstreamid)) == $cstreamid) {
                $data->name =  $data->name . ' ' . $cstreamid . '_' . $i;
                $i++;
            } else {
                $data->name = $data->name . ' ' . $cstreamid;
            }
        }
        if ( ! $group->update($data) )
        {
            return false;
        }
        // записываем id созданной группы в поток
        $cstreamobj = new stdClass();
        $cstreamobj->id       = $cstreamid;
        $cstreamobj->mdlgroup = $group->get_id();
        if ( ! $this->dof->storage('cstreams')->update($cstreamobj) )
        {
            $mdlgroupid = false;
        }
        return $group->get_id();
    }
    
    /** Добавить пользователя в группу moodle
     *
     * @return bool
     * @param int $programmitemid - id курса в хранилище programmitems
     * @param int $cstreamid - id учебного потока в таблице cstreams, привязанного к группе moodle
     * @param int $personid - id персоны деканата в хранилище persons
     *
     * @todo добавить генерацию исключений когда появятся соответствующие классы
     */
    public function add_to_group($programmitemid,$cstreamid,$personid)
    {
        if ( ! $mdlcourseid = $this->get_mdl_course($programmitemid) )
        {// указанный курс FDO не синхронизирован с курсом Moodle
            return false;
        }
        if ( ! $mdluserid = $this->get_mdl_userid($personid) )
        {// указанный пользователь FDO не синхронизирован с Moodle
            return false;
        }
        if ( ! $mdlgroupid = $this->get_mdl_group($cstreamid) )
        {// поток не синхронизирован с группой moodle - это ошибка
            return false;
        }
        // все идентефикаторы есть - можем приступать к записи в группу
        return $this->mdl_add_to_group($mdlcourseid, $mdlgroupid, $mdluserid);
    }
    
    /** Удалить группу Moodle из курса при приостановке или завершении потока.
     * Удаляет группу и отписывает из нее всех учеников
     * 
     * @todo сделать удаление группы через ama, когда zтам появится возможность удалять группу не зная курс
     * Сейчас мы можем попытаться получить предмет, а из него курс moodle, и только потом удалять группу.
     * Однако, у нас нет гарантии что курс moodle проставлен везде, или что курс у дисциплины
     * не сменился. А id группы у нас есть, так что сейчас используем API Moodle и ждем переписывания ama
     * 
     * @param int $cstreamid - id потока, который 
     * 
     * @return bool
     */
    public function mdl_delete_cstream_group($cstreamid)
    {
        global $CFG;
        require_once($CFG->dirroot.'/group/lib.php');
        $cstream = $this->dof->storage('cstreams')->get($cstreamid);
        if ( empty($cstream->mdlgroup) )
        {// нет группы Moodle - значит и удалять ничего не нужно
            return true;
        }
        $mdlcourse = null;
        if( ! empty($cstream->mdlcourse) )
        {
            $mdlcourse = $cstream->mdlcourse;
        }
        if( empty($mdlcourse) )
        {
            $programmitem = $this->dof->storage('programmitems')->get($cstream->programmitemid);
            $mdlcourse = $programmitem->mdlcourse;
        }
        
        if( ! empty($programmitem->mdlcourse) )
        {
            if( $this->dof->modlib('ama')->course($mdlcourse)->group($cstream->mdlgroup)->is_exists($cstream->mdlgroup) )
            {// Если группа существует, удаляем ее
                if ( ! groups_delete_group($cstream->mdlgroup) )
                {
                    return false;
                }
            }
        }
        
        // удаляем группу из самого потока
        $cstreamobj = new stdClass();
        $cstreamobj->id = $cstreamid;
        $cstreamobj->mdlgroup = 0;
        return $this->dof->storage('cstreams')->update($cstreamobj);
    }
    
    /** 
     * Подписать персону на курс Moodle
     * 
     * @param int $programmitemid - id курса в хранилище programmitems
     * @param int $personid - id персоны деканата в хранилище persons
     * @param int $cstreamid  - id учебного потока в таблице cstreams, привязанного к группе moodle
     * @param int $mdlroleid - id роли прльзователя в курсе (из таблицы moodle). Роль по умолчанию - ученик.
     * @param int $timeend - время окончания обучения на курсе в формете unixtime 
     *                                 (при наступлении этой даты пользователь булет отписан с курса)
     * @param bool $hidden - записать пользователя в скрытом режиме (он не будет отображаться в
     *                                 списке пользователей для учеников и учителей курса)
     * @return bool
     * 
     * @throws dof_sync_courseenrolment_exception
     */
    public function enrol_to_course($programmitemid, $personid, $cstreamid = null, $type = 'student', 
                                    $mdlroleid = false, $timeend = 0, $hidden = false)
    {
        $cstream = $this->dof->storage('cstreams')->get_record(['id' => $cstreamid]);
        if ( ! empty($cstream->mdlcourse) )
        {
            // Получение курса Moodle из учебного процесса
            $mdlcourseid = $cstream->mdlcourse;
        }
        
        if ( empty($mdlcourseid) )
        {
            // Получение курса, связанного с дисциплиной
            $mdlcourseid = $this->get_mdl_course($programmitemid);
        }
            
        // Получение пользователя Moodle
        $mdluserid = $this->get_mdl_userid($personid);
        
        // Валидация данных
        if ( ! $this->dof->modlib('ama')->course(false)->is_exists($mdlcourseid) )
        {// Курс не найден
            throw new dof_sync_courseenrolment_exception(
                'error_person_enrol_course_not_exist',
                'sync_courseenrolment'
            );
        }
        if ( ! $this->dof->modlib('ama')->user(false)->is_exists($mdluserid) )
        {// Пользователь не найден
            throw new dof_sync_courseenrolment_exception(
                'error_person_enrol_user_not_exist',
                'sync_courseenrolment'
            );
        }
        
        // Получаем роль для записи
        $roleid = $this->dof->modlib('ama')->course($mdlcourseid)->role(false, $type)->get_id();
        if( $dofenrol = $this->dof->modlib('ama')->course($mdlcourseid)->enrol_manager(false)->create_instance('dof') )
        {// Получаем инстанс способа записи
            // Запись пользователя на курс
            $enrolresult = $dofenrol->enrol_user($mdluserid, $roleid, 0, $timeend);
        }
        
        if ( empty($enrolresult) )
        {// Ошибка записи пользователя на курс
            throw new dof_sync_courseenrolment_exception(
                'error_person_enrol',
                'sync_courseenrolment'
            );
        }
        
        if ( $type != 'teacher' )
        {
            // Дополнительная задача по включению записанного пользователя в группу
            if ( $cstreamid )
            {// Указан целевой учебный процесс
                try 
                {
                    // Получение группы, связанной с целевым учебным процессом
                    $mdlgroupid = $this->get_mdl_group($cstreamid);
                } catch ( dof_sync_courseenrolment_exception $e )
                {// Ошибка получение группы
                    $mdlgroupid = false;
                    if ( $e->errorcode == 'error_cstream_coursegroup_not_linked' )
                    {// Группа не прилинкована к учебному процессу
                        
                        // Попытка создания и прилинковки группы
                        try 
                        {
                            $mdlgroupid = $this->mdl_create_cstream_group($mdlcourseid, $cstreamid);
                        } catch ( dof_sync_courseenrolment_exception $e )
                        {// Ошибка получения группы
                            // Группа не создана
                        }
                    }
                }
                
                if ( $mdlgroupid )
                {// Запись пользователя в группу
                    $this->mdl_add_to_group($mdlcourseid, $mdlgroupid, $mdluserid);
                }
            }
        }
        
        return true;
    }
    
    /** Отписать пользователя с курса modle
     * 
     * @return bool
     * @param int $programmitemid - id курса в хранилище programmitems
     * @param int $personid - id персоны деканата в хранилище persons
     * 
     * @todo добавить генерацию исключений когда появятся соответствующие классы
     */
    public function unenrol_from_course($programmitemid, $personid, $type = 'student', $cstreamid = null)
    {
        try 
        {// Пробуем отписать пользователя
            if ( ! empty($cstreamid) )
            {
                $cstream = $this->dof->storage('cstreams')->get_record(['id' => $cstreamid]);
                if ( ! empty($cstream->mdlcourse) )
                {
                    // Получение курса Moodle из учебного процесса
                    $mdlcourseid = $cstream->mdlcourse;
                }
            }
            
            if ( empty($mdlcourseid) )
            {
                // Получение курса, связанного с дисциплиной
                $mdlcourseid = $this->get_mdl_course($programmitemid);
            }
	        // Получение пользователя Moodle
	        $mdluserid = $this->get_mdl_userid($personid);
	        
	        switch($type)
	        {// Ищем активные подписки из других учебных процессов
	            case 'student':
	                if( $this->dof->storage('cpassed')->search_active_cpasseds($mdlcourseid, $personid) )
	                {// Есть активные подписки связанные с курсом - не нужно отписывать пользователя
	                    return true;
	                }
	                break;
	            default:
	                break;
	        }
	        
	        // Получение подразделения
	        if( ! empty($cstream) )
	        {// Правильно брать из учебного процесса
	            $departmentid = $cstream->departmentid;
	        } else 
	        {// Но если не передали, берем из дисциплины
	            $departmentid = $this->dof->storage('programmitems')->get($programmitemid)->departmentid;
	        }
	        
	        // Валидация данных
	        if ( ! $this->dof->modlib('ama')->course(false)->is_exists($mdlcourseid) )
	        {// Курс не найден
    	        throw new dof_sync_courseenrolment_exception(
    	            'error_person_enrol_course_not_exist',
    	            'sync_courseenrolment'
    	        );
	        }
	        if ( ! $this->dof->modlib('ama')->user(false)->is_exists($mdluserid) )
	        {// Пользователь не найден
    	        throw new dof_sync_courseenrolment_exception(
    	            'error_person_enrol_user_not_exist',
    	            'sync_courseenrolment'
	            );
	        }
	        
	        // Получаем настройку поведения отписки пользователей при завершении учебного процесса
	        $unenrolmode = $this->dof->storage('config')->get_config_value(
	            'unenrol_mode',
	            'sync',
	            'courseenrolment',
	            $departmentid,
	            $personid
	        );
	        
	        // Процесс исключения пользователя из курса
            return $this->mdl_unenrol_from_course($mdlcourseid, $mdluserid, $type, $unenrolmode);
        } catch(dof_exception $ex)
        {// Не получилось отписать от курса 
            return false;
        }
    }
    
	/**
     * Вернуть массив с настройками или одну переменную
     * 
     * @param string $key - название искомого параметра
     * @return mixed
     */
    public function get_cfg($key=null)
    {
        if (! isset($this->cenrolcfg) OR empty($this->cenrolcfg))
        {
            if ( file_exists($cfgfile = $this->dof->plugin_path($this->type(),$this->code(),'/cfg/cfg.php')) )
            {
                include ($cfgfile);
                $this->cenrolcfg = $cenrolcfg;
            }else
            {
                return null;
            }
        }
        
        if (empty($key))
        {
            return $this->cenrolcfg;
        }else
        {
            return (@$this->cenrolcfg[$key]);
        }
    }
    
    /**
     * Синхронизирует оценки заданного в конфиге количества cstream`ов
     * 
     * Тут на просроченность не смотрим
     * 
     * @return bool успешность
     */
    public function sync_grades()
    {
        $success = true;
        
        // в этом нет необходимости, но сразу выявим ошибки, если они есть
        $this->init_logs();
        // Удалим старые (а вот это нужно и только после инициализации можно)
        $this->delete_old_logs();
        
        // Пишем в лог
        $this->log_get_str('start_sync');
        
        // Получаем из конфига кол-во синхронизируемое за один вызов метода
        $limit = $this->get_cfg('sync_cstream_at_time');
        if (!$limit)
        {
            // Пишем в лог
            $this->log_get_str('not_found_cfg_param', 'sync_cstream_at_time', true);
            return false;
        }
        
        // Получаем cstream`ы которые давно синхронизировались
        $cstreamids = $this->dof->storage('cstreams')->get_old_sync_cstreams($limit);
        if (!$cstreamids)
        {
            // Выясняем: ошибка произошла или таблица пуста
            if ( !$this->dof->storage('cstreams')->count_records_select() )
            {
                // Пишем в лог
                $this->log_get_str('table_is_empty', 'cstream');
                return true;
            }
            else
            {
                // Пишем в лог
                $this->log_get_str('error_get_from_table', 'cstream', true);
                return false;
            }
        }
        
        // По всем 
        foreach ($cstreamids as $cstream)
        {
            // Там получаем cstream заного, потому что во время исполнения
            // цикла он мог быть уже закрыт

            $synccstream = $this->sync_cstream($cstream->id);
            
            $success = $success AND $synccstream;
        }
        
        // Пишем в лог
        if ($success)
        {
            $this->log_get_str('end_sync_success');
        }
        else
        {
            $this->log_get_str('end_sync_err', null, true);
        }
        
        return $success;
    }
    
    /**
     * Синхронизация учебного процесса
     * 
     * @param int $cstreamid - ID учебного процесса
     * @param bool $closing[optional] - Флаг закрытия учебного процесса
     * @param bool $execute исполнять ли автоматически приказ или только сохранить
     * (т.е. закрываем не здесь, но значит все cpassed надо обязательно проставить)
     * @return bool
     */
    public function sync_cstream($cstreamid, $closing = false, $execute = true)
    {
        $success = true;
        
        // Пишем в лог
        $this->log_get_str('start_sync_cstream', $cstreamid);
        

        $cstream = $this->dof->storage('cstreams')->get($cstreamid);
        if (!$cstream)
        {
            // Пишем в лог
            $this->log_get_str('error_get', "cstream (id={$cstreamid})", true);
            $this->log_get_str('end_sync_cstream_err', $cstreamid, true);
            return false;
        }

        // Если он уже закрыт
        if ('active' != $cstream->status)
        {
            $this->log_get_str('cstream_is_closed', $cstream->id);
            $this->log_get_str('end_sync_cstream_success', $cstream->id);
            return true;
        }

        // Получаем programmitem
        $pitem = $this->dof->storage('programmitems')->get($cstream->programmitemid);
        if (!$pitem)
        {
            // Пишем в лог
            $this->log_get_str('error_get', "programmitem (id={$cstream->programmitemid})", true);
            $this->log_get_str('end_sync_cstream_err', $cstream->id, true);
            return false;
        }

        // смотрим, нужно ли синхронизировать
        if (!$pitem->gradesyncenabled)
        {
            // Пишем в лог
            $this->log_get_str('sync_disabled', $cstream->id);
            $this->log_get_str('end_sync_cstream_success', $cstream->id);
            return true;
        }

        $gradedata = new stdClass();
        $gradedata->id = $cstream->id;
        $gradedata->teacherid = $cstream->teacherid;
        $gradedata->ageid = $cstream->ageid;
        $gradedata->programmitemid = $cstream->programmitemid;
        $gradedata->scale = $pitem->scale;
        $gradedata->mingrade = $pitem->mingrade;
        $gradedata->grade = array();
        
        // Получаем programmitem и смотрим, нужно ли синхронизировать 
        //$cpasseds = $this->dof->storage('cpassed')->get_list('cstreamid', $cstream->id, 'status', 'active');
        $cpasseds = $this->dof->storage('cpassed')->get_records_select("status='active' AND cstreamid='{$cstream->id}'");
        if ($cpasseds)
        {
            foreach ($cpasseds as $cpassed)
            {
                $scalegrade = $this->get_scalegrade($cpassed, $pitem, $cstream);
                // Если ошибка
                if ( false === $scalegrade )
                {
                    $this->log_get_str('error_get_scalegrade', $cpassed->id, true);
                    $success = false;
                    continue;
                }
                
                // Если в любом случае нужно закрывать
                if ($closing)
                {
                    // Пишем оценку в массив оценок
                    $gradedata->grade[$cpassed->id] = $scalegrade;
                    continue;
                }
                
                // Если нет пока оценки
                if ( null === $scalegrade )
                {
                    // Если включать в ведомость без оценки
                    if ($pitem->incjournwithoutgrade)
                    {
                        $this->log_get_str('not_passed_yet_but_included', $cpassed->id);
                    }
                    else
                    {
                        // Оценку не пишем
                        $this->log_get_str('not_passed_yet', $cpassed->id);
                        continue;
                    }
                }
                else
                {
                    // Если оценка неудовлетворительна
                    if ( ! $this->dof->modlib('journal')->get_manager('scale')->is_positive_grade($scalegrade, $pitem->mingrade, $pitem->scale) )
                    {
                        // И указано, что такие в ведомость не включать
                        if ( ! $pitem->incjournwithunsatisfgrade )
                        {
                            $this->log_get_str('unsatisf_grade_not_included', $cpassed->id);
                            continue;
                        }
                    }
                }
                
                // Пишем оценку в массив оценок
                $gradedata->grade[$cpassed->id] = $scalegrade;
            }
        }
        else
        {
            $this->log_get_str('empty_cstream', $cstream->id);
        }
        
        // Ведомость будем делать, только если есть оценки, которые еще не записаны в cpassed
        if ( !empty($gradedata->grade) )
        {
            // Подключаем класс для создания ведомости
            $orderitogpath = $this->dof->plugin_path($this->type(),$this->code(),'/order_itog_grades.php');
            if ( !file_exists($orderitogpath) )
            {
                $this->log_get_str('error_open_file', $orderitogpath, true);
                $this->log_get_str('end_sync_cstream_err', $cstream->id, true);
                return false;
            }
            
            include_once($orderitogpath);
            // Создаем ведомость, там проставляется оценка и создается событие исполнения ведомости
            $orderitogobj = new dof_sync_courseenrolment_order_itog_grades($this->dof, $gradedata);
            if ( $execute )
            {// выполнить приказ
                if ( !$orderitogobj->generate_order_itog_grades() )
                {
                    $this->log_get_str('error_gen_journal', $cpassed->id, true);
                    $success = false;
                }
                else
                {
                    // После успешного исполнения приказа-ведомости отправляем событие о том,
                    // что cstream синхронизировался ведомость исполнена и можно проверить приказ
                    $this->dof->send_event($this->type(),$this->code(),'sync_cstream_completed', $cstream->id);
                }
            }else
            {// не выполнять
                if ( ! $orderid = $this->save_order_itog_grades($orderitogobj) )
                {
                    $this->log_get_str('error_gen_journal', $cpassed->id, true);
                    $success = false;
                }else
                {
                    return $orderid;
                }
            }
        }
        else
        {
            $this->log_get_str('nothing_sync_cstream', $cstream->id);
        }
        
        if ($success)
        {
            $this->log_get_str('end_sync_cstream_success', $cstream->id);
        }
        else
        {
            $this->log_get_str('end_sync_cstream_err', $cstream->id, true);
        }

        return $success;
    }
    
    public function save_order_itog_grades($orderitogobj)
    {
        if ( ! $orderobj = $orderitogobj->order_set_itog_grade() )
        {
            //ошибка формирования приказа выставления итоговых оценок
            $this->log_get_str('error_gen_journal', $orderitogobj->gradedata->id, true);
            return false;
        }
        if ( ! $orderid = $orderitogobj->save_order_itog_grade($orderobj) )
        {
            //ошибка  при сохранении приказа выставления итоговых оценок
            $this->log_get_str('error_save_journal', $orderitogobj->gradedata->id, true);
            return false;
        }
        return $orderid;
    }
    
    /**
     * Получает приведенную к шкале оценку
     * 
     * @param stdClass $cpassed
     * @param stdClass $pitem
     * @param stdClass $cstream
     * 
     * @return bool|int Приведенная к шкале оценка, false в случае ошибки, null - если нет оценки пока
     * 
     */
    public function get_scalegrade($cpassed, $pitem = null, $cstream = null)
    {
        // ищем объект cpassed
        if (!is_object($cpassed))
        {
            $cpassedid = $cpassed;
            $cpassed = $this->dof->storage('cpassed')->get($cpassed);
            if (!$cpassed)
            {
                $this->log_get_str('error_get', "cpassed (id = {$cpassedid})", true);
                return false;
            }
        }
        
        // Теперь будем искать moodleuserid
        $person = $this->dof->storage('persons')->get($cpassed->studentid);
        if (!$person)
        {
            $this->log_get_str('error_get', "person (id = {$cpassed->studentid})", true);
            return false;
        }
        
        $mdlcourseid = null;
        if (  $this->dof->modlib('ama')->course(false)->is_exists($cstream->mdlcourse) )
        {
            $mdlcourseid = $cstream->mdlcourse;
            $grade = $this->dof->modlib('ama')->course($mdlcourseid)->grade()->get_total_grade(
                    $person->mdluser, $cpassed->begindate, true);
        } elseif ( $this->dof->modlib('ama')->course(false)->is_exists($pitem->mdlcourse) )
        {
            $mdlcourseid = $pitem->mdlcourse;
            // Если альтернативный источник оценки не указан
            if (!$pitem->altgradeitem)
            {
                // Получаем оценку (не ранее указанной даты, т.е. отсекаем старые оценки)
                $grade = $this->dof->modlib('ama')->course($mdlcourseid)->grade()->get_total_grade(
                        $person->mdluser, $cpassed->begindate, true);
                
            } else
            {
                // Получаем оценку (не ранее указанной даты, т.е. отсекаем старые оценки)
                $grade = $this->dof->modlib('ama')->course($mdlcourseid)->grade()->get_last_grade(
                        $person->mdluser, $pitem->altgradeitem, $cpassed->begindate, true);
            } 
        } else
        {
            $this->log_get_str('not_found_course', $mdlcourseid, true);
            return false;
        }
        
        // Параметры для сообщения в логи
        $a = new stdClass();
        $a->courseid = $mdlcourseid;
        $a->userid = $person->mdluser;
        
        // Если ошибка
        if ( false === $grade)
        {
            $this->log_get_str('error_get_grade', $a, true);
            return false;
        }
        
        // Если оценки пока нет
        if ( null === $grade )
        {
            $this->log_get_str('not_rated', $a);
            return null;
        }
        
        // Если мы тут, значит нужно приводить оценку
        
        // получаем шкалу
        $scale = trim($pitem->scale);
        if ( !$scale )
        {
            // нет шкалы оценок - не можем выставлять оценки;
            $this->log_get_str('not_found_scale', $pitem->id, true); 
            return false;
        }

        // преобразуем шкалу в массив
        $scale = $this->dof->modlib('journal')->get_manager('scale')->get_grades_scale_str($scale);
        // интервалы
        $intervals = $this->dof->modlib('journal')->get_manager('scale')->get_programmitem_grades_conversation_options($pitem);
        // приводим оценку к шкале
        $scalegrade = $this->dof->modlib('journal')->get_manager('scale')->bring_grade_to_scale($grade, $scale, $intervals);
        
        if (false === $scalegrade)
        {
            $a = new stdClass();
            $a->grade = $grade;
            $a->pitemid = $pitem->id;
            $this->log_get_str('error_bring_to_scale', $a, true); 
            return false;
        }
        
        return $scalegrade;
    }

    //*************************************************************************
    //
    // ДАЛЕЕ МЕТОДЫ ДЛЯ ВЕДЕНИЯ ЛОГОВ
    //
    // Почему эти методы? Есть ведь error_log!
    // - Этой функцией и пользуемся. А эти методы позволяют просто вызывать log_get_str
    // и больше ни о чем не думать.
    //
    // Особенности:
    // - Ничего не нужно инициализировать
    // - Ведет файл со всеми сообщениями и отдельно файл только для ошибок
    // - при вызове метода log_get_str метод сам определяет, писать ли в новые файлы
    //   (в названии файлов даты с точностью до секунды создания) или дописывать
    //   в те, которые найдет (подробности в методе find_just_writed_logs)
    // - Набор методов позволяет удалять старые логи (в конфиге задается срок хранения)
    //
    // Почему эти методы тут, а не в отдельном классе: они используют
    // $this->code(), $this->tyep(), $this->get_cfg(). Конечно все это не проблема,
    // но пока так. А вообще не дурно бы отдельным плагином сделать.
    //
    // Далее описано, что нужно сделать чтобы использовать эти методы:
    //
    // 1. Нужно где-то - хотя не обязательно - написать следующее:
    // $this->init_logs();
    // $this->delete_old_logs();
    //
    // 2. добавить в конфиг следующие параметры:
    // shelflife_logs (int) - срок хранения логов в днях
    // log (bool) - вести ли логи
    // just_writed_delay (int) - Какая пауза (в секундах) допустима при записи
    // логов, чтобы считать что конкретный файл логов сейчас используется для
    // записи. Используется при поиске файла логов, в который в данный момент
    // происходит запись
    // 
    // 3. в класс нужно добавить переменную logs
    //
    //*************************************************************************
    
    /**
     * Инициализация логов
     * 
     * Если не требуется инициализация или логи отключены, то ничего не происходит
     * 
     * @return nothing Если возникнут проблемы, то будет ошибка ввода вывода
     */
    public function init_logs()
    {
        global $CFG;
        
        // Если логи уже инициализированны или их не нужно вести, то возвращаем
        if ( isset($this->logs) OR !$this->get_cfg('log') )
        {
            return;
        }
        
        // Ну а если мы тут, то инициализируем
        
        $this->logs = new stdClass();
        
        // Задаем формат даты для названий файлов
        $this->logs->filedateformat = "%Y%m%d%H%M%S";
        
        // Устанавливаем директорию для логов 
        $this->logs->logpath = $this->dof->plugin_path($this->type(),$this->code(),'/dat/logs');
        // $this->logs->logpath = $CFG->dataroot."/cfg/dof/{$this->type()}/{$this->code()}";
        
        // Задаем базовые имена файлов с логами (перед ними будет размещаться дата в установленном формате)
        $this->logs->baselogname = 'log.txt';
        $this->logs->baseerrorlogname = 'errorlog.txt';
        
        $this->create_logs();
    }
    
	/**
     * Создание новых логов
     * 
     * Если будут ошибки чтения/записи, то ошибка исполнения будет
     *
     * @param bool $trytofind[optional] Пытаться ли искать лог, в который писать
     * (иначе создавать новый)
     */
    protected function create_logs($trytofind = true)
    {
        global $CFG;
        
        // !!! Если тут задать %Y%m%d, то на один день будет один файл лога,
        // он будет дописываться при нескольких вызовах за сутки данного метода
        // Если менять это значение, то стоит папку логов очистить - иначе старые
        // логи (со старым форматом названия) могут перестать удаляться или
        // наоборот при первом запуске все удалятся
        
        $needcreatelog = true;
        $needcreateerrorlog = true;
        
        // Если нужно пытаться искать
        if ($trytofind)
        {
            // то ищем
            $obj = $this->find_just_writed_logs();
            
            // исли что-то нашли
            if ($obj)
            {
                // если нашли файл для всех сообщений
                if ($obj->namel)
                {
                    // то его создавать не нужно
                    $needcreatelog = false;
                    $this->logs->logfilepath = $obj->namel;
                }
                
                // если нашли файл для ошибок
                if ($obj->namee)
                {
                    $needcreateerrorlog = false;
                    $this->logs->errorlogfilepath = $obj->namee;
                }
            }
        }
        
        // А дальше создадим, что не создали и заодно проверим возможность
        // открытия созданного для записи
        
        $filedate = strftime($this->logs->filedateformat);
        $path = $this->logs->logpath;
        
        if ($needcreatelog)
        {
            $this->logs->logfilepath = $path.'/'.$filedate.$this->logs->baselogname;
        }
        
        if ($needcreateerrorlog)
        {
            $this->logs->errorlogfilepath = $path.'/'.$filedate.$this->logs->baseerrorlogname;
        }
        
        // Создаем директорию для логов, если ее нет
        if ( !file_exists($path) )
        {
            mkdir($path, $CFG->directorypermissions, true);
        }
        
        // Создаем файл для логов, если нет
        $f = fopen($this->logs->logfilepath, 'a');
        fclose($f);

        // Создаем файл для лога ошибок, если нет
        $f = fopen($this->logs->errorlogfilepath, 'a');
        fclose($f);
    }
    
    /**
     * Удаление старых логов
     * 
     * Время создания определяет по названию файла (не по реальному времени
     * модификации файла)
     * Если будут ошибки чтения/записи, то будет ошибка исполнения
     * Если в папке с логами будут другие файлы, они скорее всего будут удалены
     *
     * Срок хранения указывается в конфиге
     */
    protected function delete_old_logs()
    {
        // Надо было инициализировать
        if (!isset($this->logs))
        {
            return;
        }
        
        // Получаем пороговую дату. Все файлы, созданные раньше нее удалим
        
        // Срок годности логов в секундах (параметр из конфига в днях)
        $shelflife = $this->get_cfg('shelflife_logs') * 24 * 60 * 60;
        $dateexpire = strftime($this->logs->filedateformat, time() - $shelflife);
        
        
        // Получаем список файлов директории логов (если она есть)
        
        // Смотрим есть ли директория логов
        if ( !file_exists($this->logs->logpath) )
        {
            // Если нет, то возвращаемся
            return true;
        }

        // Удаляем файлы, созданные раньше пороговой даты
        
        $dir = opendir($this->logs->logpath);
        
        while (false !== ($file = readdir($dir)))
        {
            $fullpath = $this->logs->logpath.'/'.$file;
            if ( is_file($fullpath) )
            {
                // Выделяем дату текущего файла
                $filedate = substr($file, 0, strlen($dateexpire));
                // Удаляем если дата файла меньше срока удаления
                if ( strcmp($filedate, $dateexpire) < 0 )
                {
                    unlink($fullpath);
                }
            }
        }
        
        closedir($dir);
    }
    
    /**
     * Ищет в директории логов два файла (один для всех сообщений, второй только
     * для ошибок), которые изменялись позже всех, при условии,
     * что прошло времени не более допустимого, указанного в конфиге
     * 
     * @return object|bool Полные пути к найденному файлу или false
     * Полные пути в следующем виде: объект с полями namel (полный путь к файлу
     * для всех сообщений) и namee (полный путь к файлу для ошибок) 
     */
    protected function find_just_writed_logs()
    {
        // На всякий случай проверяем
        if (!isset($this->logs->logpath))
        {
            return false;
        }
        
        // Для корректной работы функции получения времени последнего изменения файла
        clearstatcache();
        
        $path = $this->logs->logpath;
        // Длина даты в текущем формате
        $datelength = strlen(strftime($this->logs->filedateformat, time()));
        
        // Сюда запишем имя и время доступа к файлу для всех сообщений,
        // который изменялся последним
        $lastwritedl = new stdClass();
        $lastwritedl->name = '';
        $lastwritedl->time = 0;
        
        // Сюда запишем имя и время доступа к файлу для ошибок, который
        // изменялся последним
        $lastwritede = new stdClass();
        $lastwritede->name = '';
        $lastwritede->time = 0;
        
        // Смотрим, есть ли такая папка вообще
        if ( file_exists($path) )
        {
            $files = scandir($path);
            if (!$files)
            {
                // недолго думая
                return false;
            }
            else
            {
                foreach ($files as $file)
                {
                    $fullname = $path.'/'.$file;
                    if (is_file($fullname))
                    {
                        $changetime = filemtime($fullname);
                        
                        // получаем базовое имя
                        $basename = substr($file, $datelength);
                        
                        // Если это файл для всех сообщений
                        if ( $basename == $this->logs->baselogname )
                        {
                            // Если время его изменения больше (т.е. позже)
                            if ( $changetime > $lastwritedl->time )
                            {
                                $lastwritedl->name = $fullname;
                                $lastwritedl->time = $changetime; 
                            }
                        }
                        
                        // если это файл для сообщений об ошибках
                        if ( $basename == $this->logs->baseerrorlogname )
                        {
                            // Если время его изменения больше (т.е. позже)
                            if ( $changetime > $lastwritede->time )
                            {
                                $lastwritede->name = $fullname;
                                $lastwritede->time = $changetime; 
                            }
                        }

                    }
                }
            }
        }
        
        if ( !$this->get_cfg('just_writed_delay') )
        {
            return false;
        }

        // Задаем то, что будем возвращать
        $obj = new stdClass();
        $obj->namel = null;
        $obj->namee = null;
        
        // Теперь смотрим, что нашли
        // Если с момента изменения файла прошло времени не более допустимого, указанного в конфиге,
        // то это нам подходит
        if ( time() - $lastwritedl->time <= $this->get_cfg('just_writed_delay') )
        {
            $obj->namel = $lastwritedl->name;
        }
        
        if ( time() - $lastwritede->time <= $this->get_cfg('just_writed_delay') )
        {
            $obj->namee = $lastwritede->name;
        }

        return $obj;
    }
    
	/**
     * Ведение лога синхронизации, лога ошибок, вывод сообщений на экран
     *
     * @param string $message Сообщение об ошибке
     * @param bool[optional] $error Если это сообщение об ошибке
     */
    public function log($message, $error = false)
    {
        global $CFG;
        
        // Если логи отключены, то возвращаемся
        if (!$this->get_cfg('log'))
        {
            return;
        }
        
        // А тут мы сначала запускаем следующее
        // Таким образом инициализировать логи специально посути не нужно
        $this->init_logs();
        
        $logfilepath = $this->logs->logfilepath;
        $errorlogfilepath = $this->logs->errorlogfilepath;
        
        // На данный момент это не нужно
        //if ( $CFG->debug >= DEBUG_DEVELOPER OR $this->get_cfg('debug') )
        //{
        
            $timestamp = '['.date('d.m.Y H:i:s').']: ';
            
            $message = $timestamp . $message . "\n";
            
            error_log($message, 3, $logfilepath);
            
            if ($error)
            {
                error_log($message, 3, $errorlogfilepath);
            }
            
        // }
    }
    
    /**
     * Метод log, только вместо сообщения подается строка как в get_string,
     * т.е. ищет сообщение в файлах локализации
     * 
     * @param string $message Сообщение об ошибке
     * @param mixed $a Параметры для строки из файла локализации
     * @param bool[optional] $error Если это сообщение об ошибке
     */
    public function log_get_str($messagekey, $a = null, $error = false)
    {
        $message = $this->dof->get_string($messagekey, $this->code(), $a, $this->type());
        $this->log($message, $error);
    }
    
    
}
?>