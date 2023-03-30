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
 * Класс для работы с доступом в СДО через плагин local_authcontrol
 *
 * @package    block_dof
 * @subpackage sync_authcontrol
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_sync_authcontrol implements dof_sync
{
    /**
     * @var dof_control $dof - содержит методы ядра деканата
     */
    protected $dof;
    
    /**
     * @var $cfg - массив настроек плагина
     */
    protected $cfg;
    
    /**
     * Конструктор
     * @param dof_control $dof - это $DOF - методы ядра деканата
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
        return 2018082400;
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
        return 'ancistrus';
    }
    
    /** 
     * Возвращает тип плагина
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
     * @return string
     * @access public
     */
    public function code()
    {
        return 'authcontrol';
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
        ];
    }
    /** 
     * Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return [
        ];
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
     * Получить настройки для плагина
     *
     * @param string $code
     *
     * @return object[]
     */
    public function config_default($code = NULL)
    {
        return [];
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
    
    /** Проверить права через плагин acl.
     * Функция вынесена сюда, чтобы постоянно не писать длинный вызов и не перечислять все аргументы
     *
     * @return bool
     * @param object $acldata - объект с данными для функции storage/acl->has_right()
     */
    protected function acl_check_access_paramenrs($acldata)
    {
        return $this->dof->storage('acl')->has_right(
            $acldata->plugintype, 
            $acldata->plugincode, 
            $acldata->code,
            $acldata->userid, 
            $acldata->departmentid, 
            $acldata->objectid
        );
    }
    
    /**
     * Сформировать права доступа для интерфейса
     *
     * @return array - Массив с данными по правам доступа
     */
    public function acldefault()
    {
        $a = [];
        
        return $a;
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
        if ( $this->dof->is_access('datamanage') OR
            $this->dof->is_access('admin') OR
            $this->dof->is_access('manage')
            )
        {// Открыть доступ для администраторов
            return true;
        }
        
        // Получение ID текущей персоны, для которой производится проверка прав
        $currentpersonid = (int)$this->dof->storage('persons')->get_by_moodleid_id($userid);
        
        // Дополнительные проверки прав
        switch ( $do )
        {
            default:
                break;
        }
        
        // Формируем параметры для проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $currentpersonid, $depid);
        
        // Производим проверку
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// Право есть
            return true;
        }
        return false;
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
    public function catch_event($gentype,$gencode,$eventcode,$id,$mixedvar)
    {
        switch( $gentype.'__'.$gencode.'__'.$eventcode )
        {
            default: 
                break;
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
    public function todo($code, $intvar, $mixedvar)
    {
        return true;
    }
    
    // **********************************************
    // Собственные методы
    // **********************************************
    
    /**
     * Проверка включения контроля доступа в СДО
     *
     * @return bool
     */
    public function is_control_active()
    {
        return ! empty(get_config('local_authcontrol', 'authcontrol_select'));
    }
    
    /**
     * Смена доступа к модулю/курсу для группы пользователей
     *
     * @param []int
     * @param dof_sync_authcontrol_access_area $accessarea
     * @param bool $newstatus
     *
     * @return bool
     */
    public function switch_access($userids, dof_sync_authcontrol_access_area $accessarea, $newstatus = false)
    {
        global $DB, $CFG;
        if ( ! file_exists($CFG->dirroot . '/local/authcontrol/lib.php') )
        {
            return false;
        }
        require_once $CFG->dirroot . '/local/authcontrol/lib.php';
        if ( ! is_array($userids) )
        {
            return false;
        }
        if ( ! $accessarea->is_formed() )
        {
            return false;
        }
        
        
        $courseid = null;
        $cmid = null;
        if ( $accessarea->get_area() == 'course' )
        {
            $courseid = $accessarea->get_objid();
        } else 
        {
            $cmid = $accessarea->get_objid();
            $cm = $DB->get_record('course_modules', ['id' => $cmid]);
            $courseid = $cm->course;
            
            if ( ! $newstatus )
            {
                // если закрываем доступ, то проверим, открыт ли доступ к элементу
                $newuserids = [];
                $records = local_authcontrol_get_context_info_data($courseid, $userids);
                foreach ( $records as $record )
                {
                    if ( $record->moduleid == $cm->id )
                    {
                        $newuserids[] = $record->userid;
                    }
                }
                $userids = $newuserids;
            }
        }
        
        
        return local_authcontrol_save_access_info($courseid, $userids, $cmid, $newstatus);
    }
}