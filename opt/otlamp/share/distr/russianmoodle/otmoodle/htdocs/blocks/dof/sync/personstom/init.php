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


/** Класс стандартных функций интерфейса
 *
 */
class dof_sync_personstom implements dof_sync
{
    protected $dof;
    
    /**
     * @var $cfg - массив настроек плагина
     */
    protected $cfg;
    
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
    
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        return 2018081500;
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
        return 'ancistrus';
    }
    
    /** Возвращает тип плагина
     * @return string
     * @access public
     */
    public function type()
    {
        return 'sync';
    }
    /** Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'personstom';
    }
    /** Возвращает список плагинов,
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('modlib'=>array('ama'=>2008100200),
                     'storage'=>array('persons'=>2008101600));

    }
    /** Список обрабатываемых плагином событий
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return [
            ['plugintype' => 'storage',  'plugincode' => 'persons', 'eventcode' => 'insert'],
            ['plugintype' => 'storage',  'plugincode' => 'persons', 'eventcode' => 'update'],
            ['plugintype' => 'workflow', 'plugincode' => 'persons', 'eventcode' => 'person_active'],
            ['plugintype' => 'workflow', 'plugincode' => 'persons', 'eventcode' => 'person_not_active'],
            ['plugintype' => 'workflow', 'plugincode' => 'cpassed', 'eventcode' => 'cpassed_active'],
            ['plugintype' => 'workflow', 'plugincode' => 'contracts', 'eventcode' => 'changestatus'],
            ['plugintype' => 'workflow', 'plugincode' => 'appointments', 'eventcode' => 'appointment_activated'],
            ['plugintype' => 'workflow', 'plugincode' => 'eagreements', 'eventcode' => 'eagreement_activated']
        ];
    }
    /** Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
        return true;
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
        // Используем функционал из $DOFFICE
        return $this->dof->is_access($do, NULL, $userid);
    }
    /** Обработать событие
     * @param string $gentype - тип модуля, сгенерировавшего событие
     * @param string $gencode - код модуля, сгенерировавшего событие
     * @param string $eventcode - код задания
     * @param int $id - дополнительный параметр
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function catch_event($gentype, $gencode, $eventcode, $id, $mixedvar)
    {
        $fullcode = $gentype."__".$gencode."__".$eventcode;
        
        if( $fullcode == 'workflow__persons__person_not_active' )
        {// Произошла деактивация персоны
            
            // Получение персоны
            $person = $this->dof->storage('persons')->get($id);
            
            if( ! empty($person) )
            {// Персона получена
                
                $keepmdluser = false;
                if ( ! empty($mixedvar->keepmdluser) )
                {
                    $keepmdluser = true;
                }
                // Рассинхронизация персоны
                return $this->unsync($person, $keepmdluser);
            }
            
            return false;
        }
        
        // обработка событий влияющих на синхронизацию
        if( in_array($fullcode, [
            'storage__persons__insert',
            'storage__persons__update',
            'workflow__persons__person_active',
            'workflow__contracts__changestatus',
            'workflow__cpassed__cpassed_active',
            'workflow__eagreements__eagreement_activated',
            'workflow__appointments__appointment_activated'
        ]) )
        {
            $options = [];
            $vars = (array)$mixedvar;
            if ( array_key_exists('options', $vars) )
            {
                $options = $vars['options'];
            }
            
            // объект персоны, по умолчанию не определен
            $syncperson = false;
            // принудительная синхронизация (даже если в персоне стоит sync2moodle == 0
            $forcesync = false;
            // разрешено ли синхронизировать персону даже если она уже синхронизирована
            $syncsynced = false;
            
            // Синхронизация запущена вручную при сохранении персоны
            if( in_array($fullcode, ['storage__persons__insert', 'storage__persons__update'])
                && ! empty($mixedvar['new']->sync2moodle) )
            {// Сохранен объект персоны с указанием необходимости синхронизации
                
                if ( isset($mixedvar['old']) && $mixedvar['new'] == $mixedvar['old'] && 
                    !isset($options['newpassword']) && !isset($options['reset_password']) )
                {// Объекты одинаковые и смена пароля не была запрошена - синхронизация не требуется
                    return true;
                }
                
                $syncperson = $mixedvar['new'];
                $forcesync = false;
                $syncsynced = true;
            }
            
            
            // Получение поводов для синхронизации
            try
            {
                $syncreason = $this->get_cfg('sync_reason');
            }
            catch( dof_exception $ex)
            {
                $syncreason = [
                    'person_is_active' => false,
                    'contract_is_active' => true,
                    'cpassed_is_active' => false,
                    'eagreeement_is_active' => true,
                    'appointment_is_active' => false
                ];
            }
            
            if( $fullcode == 'workflow__persons__person_active'
                && ! empty($syncreason['person_is_active']) )
            {// Персона активирована
                
                // Получение персоны
                $syncperson = $this->dof->storage('persons')->get($id);
                $forcesync = true;
                $syncsynced = false;
            }
            
            if( $fullcode == 'workflow__contracts__changestatus'
                && ! empty($syncreason['contract_is_active'])
                && ! empty($mixedvar->new)
                && array_key_exists($mixedvar->new, $this->dof->workflow('contracts')->get_meta_list('active')) )
            {// Договор активирован
                
                // Получение договора
                $contract = $this->dof->storage('contracts')->get($id);
                if( ! empty($contract->studentid) )
                {
                    // Получение персоны
                    $syncperson = $this->dof->storage('persons')->get($contract->studentid);
                    $forcesync = true;
                    $syncsynced = false;
                }
            }
            
            if( $fullcode == 'workflow__cpassed__cpassed_active'
                && ! empty($syncreason['cpassed_is_active']) )
            {// Подписка на учебный процесс активирована
                
                // Получение подписки на учебный процесс
                $cpassed = $this->dof->storage('cpassed')->get($id);
                if( ! empty($cpassed->studentid) )
                {
                    // получение персоны
                    $syncperson = $this->dof->storage('persons')->get($cpassed->studentid);
                    $forcesync = true;
                    $syncsynced = false;
                }
            }
            
            if( $fullcode == 'workflow__eagreements__eagreement_activated'
                    && ! empty($syncreason['eagreeement_is_active']) )
            {
                // активирован договор с сотрудником
                $eagreement = $this->dof->storage('eagreements')->get($id);
                if( ! empty($eagreement->personid) )
                {
                    // получение персоны
                    $syncperson = $this->dof->storage('persons')->get($eagreement->personid);
                    $forcesync = true;
                    $syncsynced = false;
                }
            }
            
            if( $fullcode == 'workflow__appointments__appointment_activated'
                && ! empty($syncreason['appointment_is_active']) )
            {// Подписка на учебный процесс активирована
                
                // Получение должностного назначения
                $appointment = $this->dof->storage('appointments')->get($id);
                do
                {
                    if( ! empty($appointment->eagreementid) )
                    {
                        if (is_array($syncreason['appointment_is_active']) && ! empty($syncreason['appointment_is_active']))
                        {// требуется проверить должность на соответствие списку из конфигурации
                            if (!isset($appointment->schpositionid))
                            {
                                break;
                            }
                            // Получение вакансии, на основе которой создано должностное назначение
                            // с доп.условием соответствия списку должностей
                            $conditions = [
                                'id' => $appointment->schpositionid,
                                'positionid' => $syncreason['appointment_is_active']
                            ];
                            $schpositions = $this->dof->storage('schpositions')->get_records($conditions);
                            if (empty($schpositions))
                            {
                                break;
                            }
                        }
                        
                        $eagreement = $this->dof->storage('eagreements')->get($appointment->eagreementid);
                        if (!empty($eagreement->personid))
                        {
                            // получение персоны
                            $syncperson = $this->dof->storage('persons')->get($eagreement->personid);
                            $forcesync = true;
                            $syncsynced = false;
                        }
                    }
                } while(false);
            }
            
            if( ! empty($syncperson) && ($syncsynced || empty($syncperson->sync2moodle)) )
            {// Получена персона для синхронизации и синхронизация требуется
                try
                {// Получение настройки принудительной смены логина
                    $changelogin = $this->get_cfg('autochangelogin');
                }
                catch( dof_exception $ex)
                {// По умолчанию отключена
                    $changelogin = false;
                }
                
                $this->sync($syncperson, $changelogin, $forcesync, $options);
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
    public function cron($loan,$messages)
    {
        // поддержка кастомного cron скрипта через конфиг
        if (file_exists($processfile = $this->dof->plugin_path($this->type(),$this->code(),'/cfg/cron.php')))
        {
            include $processfile;
        }
        
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
        if ( $code == 'custom' )
        {
            // поддержка кастомного cron скрипта через конфиг
            if (file_exists($processfile = $this->dof->plugin_path($this->type(),$this->code(),'/cfg/todo.php')))
            {
                include $processfile;
            }
        }
        if ($code === 'syncall')
        {
            // Нас попросили провести "очистку"
            return $this->sync_all();
        }
        return true;
    }
    // **********************************************
    // Собственные методы
    // **********************************************
    /**
     * Конструктор
     *
     * @param dof_control $dof - объект ядра деканата
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
    
    /**
     * Синхронизируем всех пользователей
     */
    public function sync_all()
    {
        // Получаем список персон
        dof_mtrace(3,"Start sync");
        $list = $this->dof->storage('persons')->get_list_synced();
        dof_mtrace(3,"Geted list ".count($list));
        foreach ($list as $person)
        {
            // Синхронизируем очередного пользователя
            $result = $this->sync($person);
            dof_mtrace(3," * Person {$person->id} result {$result}");
        }
        return true;
    }
    
    /**
     * Синхронизация персоны если это разрешено в ее настройках
     *
     * @param object $person - объект персоны деканата
     * @param bool $changelogin - замена логина на сгенерированный системой
     * @param bool $force - принудительная синхронизация
     * @param array $options опции (reset_password|...)
     * @return boolean|int - ID пользователя в moodle
     * @access public
     */
    public function sync($person, $changelogin = false, $force = false, $options = [])
    {
        if( $force )
        {// Требуется синхронизировать принудительно
            $person->sync2moodle = 1;
        }
        
        // Синхронизируем пользователя
        $mdluserid = $this->sync_person_data($person, $changelogin, $options);
        
        $this->dof->send_event('sync', 'personstom', 'sync_finished', (int)$mdluserid, $person);
                
        return $mdluserid;
    }
    
    /**
     * Провести рассинхронизацию пользователя с  Moodle
     *
     * @param object $person - объект персоны деканата
     * @param bool $muserkeep - требуется ли удалить пользователя из moodle
     *
     * @return bool - true в случае успеха и false в случае неудачной рассинхронизации
     */
    public function unsync($person, $muserkeep = false)
    {
        // Была ли персона синхронизирован ранее?
        if ( !isset($person->id) OR !$person->id
                OR !isset($person->sync2moodle) OR !$person->sync2moodle )
        {
            return false;
        }
        // Требуется ли удалить пользователя?
        if ( ! $muserkeep AND ! $this->delete_user($person) )
        {
            return false;
        }
        // Отключаем синхронизацию
        $person2 = new stdClass();
        $person2->mdluser = 0;
        $person2->sync2moodle = 0;

        return $this->dof->storage('persons')->update($person2,$person->id);
    }
    /** Удаляет пользователя из Moodle
     * @param object $person
     *
     */
    protected function delete_user($person)
    {
        if ( !isset($person->mdluser )
            OR !$person->mdluser OR!$this->dof->modlib('ama')->user(false)->is_exists($person->mdluser))
        { // пользователя уже нет
            return true;
        }else
        { // удаляем
            $this->dof->send_event($this->type(),$this->code(),'deleteuser',$person->id,array('person'=>$person));
            return $this->dof->modlib('ama')->user($person->mdluser)->delete();
        }
    }
    
    /** Синхронизировать персону с пользователем Moodle
     *
     * @param object $person - Объект персоны
     * @param bool $changelogin - Сгенерировать логин
     * @param array $options опции (reset_password|...)
     *
     */
    protected function sync_person_data($person, $changelogin = false, $options = [])
    {
        global $CFG;
        
        $personclone = fullclone($person);
        
        // Проверяем входные данные
        if ( ! is_object($personclone) || empty($personclone->id) || empty($personclone->sync2moodle) )
        {   // Не передали данные или запрещена синхронизация
            return false;
        }
        
        // Начинаем формировать данные пользователя
        $user = new stdClass();
        
        $user->firstname = (string)$personclone->firstname;
        $user->lastname = (string)$personclone->lastname;
        $user->middlename = '';
        if ( isset($personclone->middlename) )
        {
            $user->middlename = (string)$personclone->middlename;
        }
        $user->idnumber = $personclone->id;
        
        if ( ! empty($personclone->email) )
        {    // указан email
            $user->email = $personclone->email;
        }
        // Получаем адресс пользователя из справочника адресов
        if (isset($personclone->passportaddrid))
        {
            $addressid = $personclone->passportaddrid;
        } else
        {
            $addressid = $personclone->addressid;
        }
        if ( ! empty($addressid) )
        {
            $addres = $this->dof->storage('addresses')->get($addressid);
            if ( ! empty($addres->country) )
            {
                $user->country = $addres->country;
                if ($addres->city)
                {
                    // Указан город
                    $user->city = $addres->city;
                }elseif ($addres->region)
                {
                    // Город не указан - берем регион
                    $user->city = $this->dof->modlib('refbook')->region($addres->country,$addres->region);
                }
            }
        }
        
        // Номера телефонов
        if ( isset($personclone->phonehome) && ! empty($personclone->phonehome) )
        {// Установлен домашний телефон
            $user->phone1 = $personclone->phonehome;
        } else
        {// Домашний телефон не указан, проверка на наличие рабочего
            if ( isset($personclone->phonework) && ! empty($personclone->phonework) )
            {// Установлен рабочий телефон
                $user->phone1 = $personclone->phonework;
            }
        }
        if ( isset($personclone->phonecell) && ! empty($personclone->phonecell) )
        {// Мобильный телефон указан
            $user->phone2 = $personclone->phonecell;
        }

        
        // получение настройки автоматической привязки по email
        try {
            $autolink = $this->get_cfg('autolink_by_email');
        } catch( dof_exception $ex)
        {// по умолчанию отключена
            $autolink = true;
        }
        if( ! $personclone->mdluser && $autolink )
        {// Пользователь Moodle не передан - попробуем найти по email
            $userbyemail = $this->dof->modlib('ama')->user(false)->get_user_by_email($personclone->email);
            if( ! empty($userbyemail) )
            {
                // получение настройки поведения при нахождении нескольких категорий удовлетворяющих условиям
                try {
                    $autolinkdouble = $this->get_cfg('autolink_double');
                } catch( dof_exception $ex)
                {// по умолчанию не привязываем
                    $autolinkdouble = 0;
                }
                
                if( count($userbyemail) == 1 || (int)$autolinkdouble == 2 )
                {// есть один единственный подходящий пользователь или требуется привязать к любому из найденных
                    $personclone->mdluser = array_shift($userbyemail)->id;
                }
                elseif( count($userbyemail) > 1 && (int)$autolinkdouble == 0)
                {// запрещено создавать нового пользователя, если найдено несколько с одинаковым email
                    return false;
                }
                elseif( count($userbyemail) > 1 && (int)$autolinkdouble == 1
                    && ! empty($CFG->allowaccountssameemail) )
                {// требуется создать еще одного пользователя, если найдено несколько с одинаковым email
                    // (не удается определить нужного) и разрешено создавать пользователей с одинаковым email
                    $personclone->mdluser = false;
                }
            }
        }
        
        if ($personclone->mdluser)
        {    // Зарегистрированный пользователь
            // echo 'aaa2';
            if ($this->dof->modlib('ama')->user(false)->is_exists($personclone->mdluser))
            {
                // Обновляем существующего пользователя
                $curmdluser = $this->dof->modlib('ama')->user($personclone->mdluser)->get();
                
                if ($changelogin)
                {    // Нужно сменить логин
                    // Выбераем поле для логина
                    if (property_exists($personclone, 'transmit__username') 
                        && $curmdluser->username != $personclone->transmit__username
                        && !$this->dof->modlib('ama')->user(false)->get_user_by_username($personclone->transmit__username)) {
                        // Если передан username, не совпадает с текущим и не используется - меняем
                        $username = $personclone->username;
                    } else {
                        // Формируем уникальный логин из левой части емайла или транслитерацией
                        if (isset($personclone->email) AND !empty($personclone->email))
                        {
                            // Делаем логином первую часть емайла
                            $username = substr($personclone->email,0, strpos($personclone->email, '@'));
                            // $username = strstr($person->email, '@', true);
                        }elseif (empty($username) AND isset($personclone->lastname) AND !empty($personclone->lastname))
                        {
                            $username = $personclone->lastname;
                        }elseif (empty($username) AND isset($personclone->firstname) AND !empty($personclone->firstname))
                        {
                            $username = $personclone->firstname;
                        } else
                        {
                            $username = "p".$personclone->id;
                        }
                    }
                    // Делаем логин уникальным (больше не требуется, так как это происходит в ama_user)
                    // $user->username = $this->dof->modlib('ama')->user(false)->username_unique($username,true,$person->mdluser);
                    // Указываем желаемый логин - транслитерацию и уникальность добавит класс ama
                    // а префикс добавит обработчик событий
                    $user->username = $username;

                }
                
                // получение настройки разрешено ли обновлять пароль для уже существующего пользователя
                try {
                    $changepasswordallowed = $this->get_cfg('update_password_allowed');
                } catch( dof_exception $ex)
                {// по умолчанию не меняем для существующих
                    $changepasswordallowed = false;
                }
                if (!empty($changepasswordallowed) && property_exists($personclone, 'transmit__passwordmd5'))
                {
                    $user->password = $personclone->transmit__passwordmd5;
                }
                
                // получение настройки требуется ли отправлять уведомление о пароле, измененном для уже существующего пользователя
                try {
                    $changepasswordnotificate = $this->get_cfg('update_password_notification_required');
                } catch( dof_exception $ex)
                {// по умолчанию не меняем для существующих
                    $changepasswordnotificate = false;
                }
                
                // Подключаем общий (и для инсерта, и для апдейта) предобработчик
                if (file_exists($processfile = $this->dof->plugin_path($this->type(),$this->code(),'/cfg/userprocess.php')))
                {
                    include $processfile;
                }
                // Подключаем предобработчик, выполняющийся перед обновлением
                if (file_exists($processfile = $this->dof->plugin_path($this->type(),$this->code(),'/cfg/updateuserprocess.php')))
                {
                    include $processfile;
                }
                
                // Обновляем пользователя
                $mdluser = $curmdluser->id;
                $allowchange = $curmdluser->auth == 'dof' || ! empty($this->dof->storage('config')->get_config_value(
                    'allow_change_not_auth_dof_users',
                    'storage',
                    'persons',
                    $personclone->departmentid)
                );
                if ( $allowchange )
                {
                    if ( $mdluser = $this->dof->modlib('ama')->user($personclone->mdluser)->update($user) )
                    {
                        // Отправляем событие регистрации нового пользователя Moodle
                        $this->dof->send_event($this->type(),$this->code(),'updateuser',$personclone->id,array('person'=>$personclone,'user'=>$user));
                        
                        // Подключаем общий (и для инсерта, и для апдейта) постобработчик
                        if (file_exists($processfile = $this->dof->plugin_path($this->type(),$this->code(),'/cfg/userafter.php')))
                        {
                            include $processfile;
                        }
                        // Подключаем постобработчик, выполняющийся после обновления
                        if (file_exists($processfile = $this->dof->plugin_path($this->type(),$this->code(),'/cfg/updateuserafter.php')))
                        {
                            include $processfile;
                        }
                    }
                }
                
                return $mdluser;
                
            }else
            {
                // echo 'aaa5';
                // Такого пользователя не существует
                return false;
            }
        } else//if (isset($person->email) AND !empty($person->email))
        {
//             // Проверим существует ли пользователь с таким email в moodle
//             $userwithpersonemail = $this->dof->modlib('ama')->user(false)->get_user_by_email($person->email);
//             if( ! empty($userwithpersonemail) )
//             {
//                 throw new dof_exception($this->dof->get_string('user_already_exist', 'personstom', null, 'sync'));
//             }
            // Новый пользователь
            // Добавляем нового пользователя

            // Создаем шаблон
            $user = $this->dof->modlib('ama')->user(false)->template($user);
            // Пользователь подтвержден
            $user->confirmed = 1;
            $user->emailstop = 0;
            $user->autosubscribe = 1;
            $user->maildisplay = 2;
            if (property_exists($personclone, 'transmit__username')
                && !$this->dof->modlib('ama')->user(false)->get_user_by_username($personclone->transmit__username)) {
                // Передан логин и не используется
                $username = $personclone->transmit__username;
            } else {
                // Формируем уникальный логин из левой части емайла или транслитерацией
                if (isset($personclone->email) AND !empty($personclone->email))
                {
                    // Делаем логином первую часть емайла
                    $username = substr($personclone->email,0, strpos($personclone->email, '@'));
                    // $username = strstr($person->email, '@', true);
                }elseif (empty($username) AND isset($personclone->lastname) AND !empty($personclone->lastname))
                {
                    $username = $personclone->lastname;
                }elseif (empty($username) AND isset($personclone->firstname) AND !empty($personclone->firstname))
                {
                    $username = $personclone->firstname;
                } else
                {
                    $username = "p".$personclone->id;
                }
            }
            // $user->username = $this->dof->modlib('ama')->user(false)->username_unique("a-".$username,true);
            // Указываем желаемый логин - транслитерацию и уникальность добавит класс ama
            // а префикс добавит обработчик событий
            $user->username = $username;
            
            if (property_exists($personclone, 'transmit__passwordmd5'))
            {
                $user->password = $personclone->transmit__passwordmd5;
            }
            
            // получение настройки, требуется ли отправлять уведомление о пароле, сохраненном во времясоздания пользователя
            try {
                $setpasswordnotificate = $this->get_cfg('set_password_notification_required');
            } catch( dof_exception $ex)
            {// по умолчанию не меняем для существующих
                $setpasswordnotificate = false;
            }
            
            // Подключаем общий (и для инсерта, и для апдейта) предобработчик
            if (file_exists($processfile = $this->dof->plugin_path($this->type(),$this->code(),'/cfg/userprocess.php')))
            {
                include $processfile;
            }
            // Подключаем предобработчик, выполняющийся перед добавлением
            if (file_exists($processfile = $this->dof->plugin_path($this->type(),$this->code(),'/cfg/adduserprocess.php')))
            {
                include $processfile;
            }
            
            
            //echo 'aaa7'.$user->username;
            // return $this->dof->modlib('ama')->user()->update($user);
            if ($mdluser = $this->dof->modlib('ama')->user(false)->create($user))
            {
                // Флаг создания нового пользователя (будем ориентироваться на него в процедурах после создания пользователя)
                $isnewuser = true;
                // Отправляем событие регистрации нового пользователя Moodle
                // К событию прикрепляем ссылку на объекты $user и $person,
                $this->dof->send_event($this->type(),$this->code(),'adduser',$personclone->id,array('person'=>$personclone,'user'=>$user));
                
                // Подключаем общий (и для инсерта, и для апдейта) постобработчик
                if (file_exists($processfile = $this->dof->plugin_path($this->type(),$this->code(),'/cfg/userafter.php')))
                {
                    include $processfile;
                }
                // Подключаем постобработчик, выполняющийся после добавления
                if (file_exists($processfile = $this->dof->plugin_path($this->type(),$this->code(),'/cfg/adduserafter.php')))
                {
                    // По умолчанию отсылает уведомление о пароле
                    include $processfile;
                }
            }
            return $mdluser;
            
        }
    }
    /** Возвращает запись пользователя Moodle по его логину
     * @param string $username - логин пользователя
     * @return object - запись пользователя Moodle или false, если таковой не был найден
     */
    public function get_mdluser_byusername($username)
    {
        if ( ! is_string($username) )
        {// неправильный формат данных
            return false;
        }
        return $this->dof->modlib('ama')->user(false)->get_user_by_username($username);

    }
        
    /** Получить пользователя moodle по его id
     *
     * @return object|boolean - объект из таблицы mdl_user или false
     * @param int $mdluserid - id пользователя в moolde
     */
    public function get_mdluser($mdluserid)
    {
        if ( ! is_numeric($mdluserid) )
        {// неправильный формат данных
            return false;
        }
        if ( ! $this->dof->modlib('ama')->user(false)->is_exists($mdluserid) )
        {// если пользователя не существует - то мы не сможем его вернуть
            return false;
        }
        return $this->dof->modlib('ama')->user($mdluserid)->get();
    }
    
    /** Получить персону деканата по id пользователя в moodle
     *
     * @return
     * @param int $mdluserid - id пользователя в moodle
     * @param bool $create[optional] - создавать персону ли персону?
     *                 - true -  создать персону, если пользователь moodle существует, а такой персоны нет
     *                 - false - не создавать персону
     */
    public function get_person($mdluserid)
    {
        if ( ! is_numeric($mdluserid) )
        {// неправильный формат входных данных
            return false;
        }
        if ( ! $user = $this->get_mdluser($mdluserid) )
        {// пользователь moodle не существует
            return false;
        }
        if ( ! $person = $this->dof->storage('persons')->get_by_moodleid($mdluserid) )
        {// в базе нет персоны с таким mdluserid
        // не нашли, попытаемся создать
            // перепишем пользователя Moodle
            $obj = new stdClass();
            $obj->mdluser = $user->id;
            $obj->sync2moodle= 1;
            $obj->email = $user->email;
            $obj->firstname = $user->firstname;
            $obj->lastname = $user->lastname;
            $obj->addressid = null;
            if ( ! $personid = $this->dof->storage('persons')->insert($obj) )
            {// не получилось даже создать
                return false;
            }
            // создали - заберем ее из БД
            $person = $this->dof->storage('persons')->get($personid);
        }
        // сравним email
        if ( $person->email == $user->email )
        {// совпали - вернем персону
            return $person;
        }
        // что-то неправильно
        return false;
    }
    
    /** Возвращает запись персоны из деканата по его логину из Moodle
     * @param string $username - логин пользователя
     * @param bool $create - создать персону в деканате, если такова не нашлась
     * @return object - запись персоны из деканата или false, если таковой не был найден
     */
    public function get_person_byusername($username)
    {
        if ( ! $user = $this->get_mdluser_byusername($username) )
        {// не нашли
            return false;
        }
        // нaйдем персону из деканата или создадим ее
        return $this->get_person($user->id);

    }
    
    // Служебные методы
    //
    /**
     * Вернуть массив с настройками или одну переменную
     * @param $key - переменная
     * @return mixed
     */
    protected function get_cfg($key=null)
    {
        if( empty($this->cfg) )
        {
            $cfgpath = $this->dof->plugin_path($this->type(), $this->code(), '/cfg/cfg.php');
            if( ! file_exists($cfgpath) )
            {
                throw new dof_exception('no_such_cfg_file');
            }
            
            // Файл, содержащий массив с параметрами конфигурации
            include_once($cfgpath);
            $this->cfg = $sync_personstom;
        }
        
        if( is_null($key) )
        {// вернуть весь массив
            return $this->cfg;
        }
        else
        {// вернуть указанную настройку
            if( isset($this->cfg[$key]) )
            {// есть такая настройка - вернем
                return $this->cfg[$key];
            }
            else
            {// нет настройки - вернем false
                throw new dof_exception('no_such_cfg');
            }
        }
    }
    
    /** Получить часовой пояс пользователя moodle по его id
     *
     * @return string - часовой пояс в UTC или пустая строка
     * @param int $mdluserid - id пользователя в moolde
     */
    public function get_usertimezone($mdluserid = null)
    {
        global $USER;
        if ( is_null($mdluserid) )
        {   // Берем id текущего пользователя
            $mdluserid = $USER->id;
        }
        if ( ! $user = $this->get_mdluser($mdluserid) )
        {// неправильный формат данных
            return '';
        }
        return dof_usertimezone($user->timezone);
    }
    
    /** Получить дату и время с учетом часового пояса
     *
     * @return string - время с учетом часового пояса
     * @param int $date - время в unixtime
     * @param string $format - формат даты с учетом символов используемых в strftime
     * @param int $mdluserid - id пользователя в moolde
     * @param boolean $fixday - true стирает нуль перед %d
     *                          false - не стирает
     */
    public function get_userdate($date, $format = '', $mdluserid = null, $fixday = false)
    {
        global $USER;
        if ( is_null($mdluserid) )
        {   // Берем id текущего пользователя
            $mdluserid = $USER->id;
        }
        if ( ! $user = $this->get_mdluser($mdluserid) )
        {// неправильный формат данных
            return strftime($format,$date);
        }
        return dof_userdate($date,$format,$user->timezone,$fixday);
    }
    
    /** Получить дату и время с учетом часового пояса
     *
     * @return array - время с учетом часового пояса
     * @param int $date - время в unixtime
     * @param int $mdluserid - id пользователя в moolde
     */
    public function get_usergetdate($date, $mdluserid = null)
    {
        global $USER;
        if ( is_null($mdluserid) )
        {   // Берем id текущего пользователя
            $mdluserid = $USER->id;
        }
        if ( ! $user = $this->get_mdluser($mdluserid) )
        {// неправильный формат данных
            return getdate($date);
        }
        return dof_usergetdate($date,$user->timezone);
    }
    
    /** Получить дату и время с учетом часового пояса
     *
     * @return int - время с учетом часового пояса в Unixtime
     * @param int $date - время в unixtime
     * @param int $mdluserid - id пользователя в moolde
     */
    public function get_make_timestamp($hour=0, $minute=0, $second=0, $month=1, $day=1, $year=0, $mdluserid = null, $applydst=true)
    {
        global $USER;
        if ( is_null($mdluserid) )
        {   // Берем id текущего пользователя
            $mdluserid = $USER->id;
        }
        if ( ! $user = $this->get_mdluser($mdluserid) )
        {// неправильный формат данных
            return $date;
        }
        return dof_make_timestamp($year, $month, $day, $hour, $minute, $second, $user->timezone, $applydst);
    }
}
?>