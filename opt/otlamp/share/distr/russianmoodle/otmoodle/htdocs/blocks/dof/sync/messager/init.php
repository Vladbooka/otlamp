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
 * Мессаджер Электронного Деканата. Класс плагина.
 *
 * @package    sync
 * @subpackage import
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_sync_messager implements dof_sync
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
        return true;
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
        return true;
    }
    
    /** 
     * Возвращает версию установленного плагина
     * 
     * @return string
     * @access public
     */
    public function version()
    {
        return 2018041000;
    }
    
    /** 
     * Возвращает версии интерфейса Деканата, 
     * с которыми этот плагин может работать
     * 
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
     * 
     * @return string
     * @access public
     */
    public function compat()
    {
        return 'ancistrus';
    }
    
    /** 
     * Возвращает тип плагина
     * 
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'sync';
    }
    
    /** 
     * Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * 
     * @return string
     * @access public
     */
    public function code()
    {
        return 'messager';
    }
    
    /** 
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array(
            'storage' => array( 'persons' => 2008101600 )
        );

    }
    /** 
     * Список обрабатываемых плагином событий 
     * 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return [
            ['plugintype' => 'im',  'plugincode' => 'agroups', 'eventcode' => 'send_message']
        ];
    }
    
    /** 
     * Требуется ли запуск cron в плагине
     * 
     * @return bool
     * @access public
     */
    public function is_cron()
    {
        return false;
    }
    
    /** 
     * Проверяет полномочия на совершение действий
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
    /** 
     * Обработать событие
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
        // Ловим событие, если пользователя синхронизируем и запись изменилась
        if ( $gentype === 'im' && $gencode === 'agroups' && $eventcode === 'send_message' && $id > 0 )
        {
            $this->notify_persons([$id], $mixedvar['message']);
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
     * Системное уведомление пользователям
     */
    public function notify_persons($personids, $notificationtext, $options=[])
    {
        global $USER, $DB;
        
        if( ! empty($options['userfrom']) )
        {// указан отправитель
            if( is_int_string($options['userfrom']) )
            {// указан конкретный пользователь moodle
                $userfrom = $DB->get_record('user', ['id' => $options['userfrom']]);
            } 
            else if( $options['userfrom'] == 'support_user' )
            {// необходимо отправить от имени техподдержки
                $userfrom = core_user::get_support_user();
            }
        }
        if( empty($userfrom) )
        {// пользователь не переопределен, используем текущего
            $userfrom = $USER;
        }
        
        $result = [];
        
        if( empty($notificationtext) )
        {// сообщение пустое
            return false;
        }
        
        foreach($personids as $personid)
        {
            // Получим персону по ID
            $person = $this->dof->storage('persons')->get($personid);
            if ( empty($person) )
            {// Не нашли персону
                $result[$personid] = false;
                continue;
            }

            // Получим пользовтаеля
            $userto = $DB->get_record('user', ['id' => $person->mdluser]);
            if ( empty($userto) )
            {// Не нашли пользователя
                $result[$personid] = false;
                continue;
            }

            // Отправляем сообщение
            $notificationresult = message_post_message($userfrom, $userto, $notificationtext, FORMAT_MOODLE);
            if ( empty($notificationresult) )
            {// Сообщение не отправилось
                $result[$personid] = false;
                continue;
            } else
            {
                $result[$personid] = [
                    'usesrfrom' => $userfrom,
                    'userto' => $userto,
                    'messageid' => $notificationresult
                ];
            }
        }
        
        return $result;
    }


    /**
     * Системное уведомление пользователям, имеющим право в подразделении
     */
    public function notify_persons_with_acl($notificationtext, $plugintype, $plugincode, $aclcode, 
        $departmentid = 0, $objectid = 0, $options=[])
    {
        if( empty($notificationtext) )
        {// не указано сообщение уведомления
            return false;
        }
        
        $allowedpersonids = [];
        
        // Получение персон с указанным правом в подразделении
        $aclpersons = $this->dof->storage('acl')->get_persons_acl_by_code(
            $plugintype,
            $plugincode,
            $aclcode,
            $departmentid,
            $objectid
        );

        foreach($aclpersons as $aclpersondata)
        {
            // Если требуется, отфильтруем полученные данные по списку переданных пользователей
            if( empty($options['allowed_persons']) || in_array($aclpersondata->id, $options['allowed_persons']) )
            {
                $allowedpersonids[] = $aclpersondata->id;
            }
        }
        
        // сохраним опции для метода уведомлений
        $notifyoptions = [];
        if( ! empty($options['userfrom']) )
        {
            $notifyoptions['userfrom'] = $options['userfrom'];
        }
        // результат уведомления персон
        return $this->notify_persons($allowedpersonids, $notificationtext, $notifyoptions);
    }
    
    /**
     * метод отправки уведомлений через стандартный механизм провайдеров Moodle
     * 
     * @example пример можно посмотреть в storage/achievementins
     * @desc не использовать напрямую, использовать modlib/messager
     * 
     * @param stdClass $message - объект сообщения
     * 
     * @return bool
     */
    public function message_send(stdClass $message) : bool
    {
        $newmessage = new \core\message\message();
        $message = (array)$message;
        foreach ($message as $varname => $value)
        {
            $newmessage->{$varname} = $value;
        }
        if ( $this->validate_message($newmessage) && message_send($newmessage) )
        {
            return true;
        }
        
        return false;
    }
    
    /**
     * валидация сообщения
     *
     * @param \core\message\message $message
     *
     * @return bool
     */
    protected function validate_message(\core\message\message $message) : bool
    {
        if ( empty($message->smallmessage) ||
                empty($message->fullmessage) ||
                empty($message->fullmessagehtml) ||
                empty($message->subject) ||
                empty($message->userfrom) ||
                empty($message->userto) ||
                empty($message->component)
                )
        {
            // сообщение не прошло валидацию
            return false;
        }
        
        return true;
    }
}
