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
 * менеджер уведомлений
 *
 * @package    modlib
 * @subpackage messager
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_modlib_messager implements dof_plugin_modlib
{
    /**
     * провайдер срочных уведомлений
     * 
     * @var string
     */
    const MESSAGE_PROVIDER_URGENT = 'urgent_notifications';
    
    /**
     * провайдер несрочных уведомлений
     *
     * @var string
     */
    const MESSAGE_PROVIDER_NOTURGENT = 'noturgent_notifications';
    
    /**
     * провайдер обычных уведомлений
     *
     * @var string
     */
    const MESSAGE_PROVIDER_ORDINARY = 'ordinary_notifications';
    
    /**
     * провайдер уведомлений с ограниченной актуальностью (всплывашки в деканате)
     *
     * @var string
     */
    const MESSAGE_PROVIDER_DOF = 'dof_notifications';
    
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * массив констант провайдеров
     * 
     * @var array
     */
    protected static $message_providers = null;
    
    /**
     * Конструктор
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
        
        if ( is_null(static::$message_providers) )
        {
            // установка провайдеров
            $reflection = new ReflectionClass(get_class($this));
            static::$message_providers = array_flip($reflection->getConstants());
        }
    }
    
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
        return 'neon_a';
    }
    
    /** 
     * Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'modlib';
    }
    /** 
     * Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
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
        return [
            'sync' => [
                'messager' => 2018041000,
            ]
        ];
    }
    /** 
     * Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return [];
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
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function catch_event($gentype,$gencode,$eventcode,$intvar,$mixedvar)
    {
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
    
    /**
     * получение дефолтного провайдера
     * 
     * @param string $ptype
     * @param string $pcode
     * @param string $code
     * 
     * @return string|false
     */
    public function get_default_message_provider($ptype, $pcode, $code)
    {
        if ( ! $this->dof->plugin_exists($ptype, $pcode) )
        {
            // неизвестный плагин
            return false;
        }
        if ( ! method_exists($this->dof->plugin($ptype, $pcode), 'registered_notification_types') )
        {
            // метод отсутствует
            return false;
        }
        
        // получение уведомлений
        $providers = $this->dof->plugin($ptype, $pcode)->registered_notification_types();
        if ( ! array_key_exists($code, $providers) )
        {
            // переданный код отсутствует в списке объявленных
            return false;
        }
        if ( ! array_key_exists($providers[$code], static::$message_providers) )
        {
            // неизвестный код провайдера
            return false;
        }
        
        return $providers[$code];
    }
    
    /**
     * метод отправки уведомлений
     * 
     * @param string $ptype
     * @param string $pcode
     * @param string $code
     * @param int $touserid
     * @param stdClass $message
     * 
     * @return bool
     */
    public function message_send($ptype, $pcode, $code, $touserid, $message)
    {
        // получение дефолтного провайдера
        $defaultmessageprovider = $this->get_default_message_provider($ptype, $pcode, $code);
        if ( empty($defaultmessageprovider) )
        {
            // переданные параметры не прошли валидацию
            return false;
        }
        $personto = $this->dof->storage('persons')->get($touserid);
        if ( empty($personto->mdluser) )
        {
            return false;
        }
        if ( ! $this->dof->modlib('ama')->user(false)->is_exists($personto->mdluser) )
        {
            return false;
        }
        
        // дополнение данных объекта сообщения
        $message->userfrom = $this->dof->modlib('ama')->user(false)->get_noreply_user();
        $message->userto = $this->dof->modlib('ama')->user($personto->mdluser)->get();
        $message->component = 'block_dof';
        $message->name = $defaultmessageprovider;
        
        return $this->dof->sync('messager')->message_send($message);
    }
}

