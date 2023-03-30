<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
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

// подключение интерфейса настроек
require_once($DOF->plugin_path('storage', 'config', '/config_default.php'));

/**
 * Справочик классификаторов
 *
 * @package    storage
 * @subpackage taxonomies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_storage_taxonomies extends dof_storage implements dof_storage_config_interface
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
     * Конструктор
     * 
     * @param dof_control $dof - объект с методами ядра деканата
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
    }
    
    /**
     * Метод, реализующий инсталяцию плагина в систему
     * Создает или модифицирует существующие таблицы в БД
     * и заполняет их начальными значениями
     *
     * @return boolean
     */
    public function install()
    {
        if ( ! parent::install() )
        {
            return false;
        }
        
        global $DB;
        $dbman = $DB->get_manager();
        
        $result = true;
        $table = new xmldb_table($this->tablename());
        if ( $dbman->table_exists($table) )
        {
            $edulevels = $this->get_records(['code' => 'programmtypes', 'type' => 'edulevel']);
            if ( empty($edulevels) )
            {

                $record = new stdClass();
                $record->code = 'programmtypes';
                $record->name = 'Уровень образования';
                $record->type = 'edulevel';
                $record->canbedeleted = 0;
                $classificatorid = $this->save($record);
            }
        }
        
        return $this->dof->storage('acl')->save_roles($this->type(), $this->code(), $this->acldefault());
    }   
    
    /**
     * Метод, реализующий обновление плагина в системе.
     * Создает или модифицирует существующие таблицы в БД
     *
     * @param string $old_version
     *            - Версия установленного в системе плагина
     *            
     * @return boolean
     */
    public function upgrade($oldversion)
    {
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());// возвращаем результат обновления
    }
     
    /**
     * Возвращает версию установленного плагина
     *
     * @return int - Версия плагина
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
        return 2018051618;
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
        return 'paradusefish';
    }
    
    /** 
     * Возвращает тип плагина
     * 
     * @return string 
     */
    public function type()
    {
        return 'storage';
    }
    
    /**
     * Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * 
     * @return string
     */
    public function code()
    {
        return 'taxonomies';
    }
    
    /** 
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * 
     * @return array
     */
    public function need_plugins()
    {
		return [
                'storage' => [
                        'acl' => 2016071500
                ]
        ];
    }
    
    /** 
     * Определить, возможна ли установка плагина в текущий момент
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
    
    /** 
     * Получить список плагинов, которые уже должны быть установлены в системе,
     * и без которых начать установку или обновление невозможно
     * 
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion=0)
    {
        return [
                'storage' => [
                        'acl' => 2016071500
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
        return [];
    }
    
    /** Требуется ли запуск cron в плагине
     * 
     * @return bool
     */
    public function is_cron()
    {
        return false;
    }
    
    /**
     * Проверяет полномочия на совершение действий
     *
     * @param string $do
     *            - идентификатор действия, которое должно быть совершено
     * @param int $objid
     *            - идентификатор экземпляра объекта,
     *            по отношению к которому это действие должно быть применено
     * @param int $userid
     *            - идентификатор пользователя Moodle, полномочия которого проверяются
     *            
     * @return bool true - можно выполнить указанное действие по
     *         отношению к выбранному объекту
     *         false - доступ запрещен
     */
    public function is_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
        if ($this->dof->is_access('datamanage') or $this->dof->is_access('admin') or
             $this->dof->is_access('manage'))
        { // Открыть доступ для менеджеров
            return true;
        }
        
        // Получаем ID персоны, с которой связан данный пользователь
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        
        // Дополнительные проверки прав
        switch ($do)
        {
            
            default:
                
                break;
        }
        
        // Формируем параметры для проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid, $depid);
        // Производим проверку
        if ($this->acl_check_access_parametrs($acldata))
        { // Право есть
            return true;
        }
        return false;
    }
    
	/** 
	 * Требует наличия полномочия на совершение действий
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     *      по отношению к которому это действие должно быть применено
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
        // Ничего не делаем, но отчитаемся об "успехе"
        return true;
    }
    
    /** 
     * Запустить обработку периодических процессов
     * 
     * @param int $loan - нагрузка (1 - только срочные, 2 - нормальный режим, 3 - ресурсоемкие операции)
     * @param int $messages - количество отображаемых сообщений (0 - не выводить,1 - статистика,
     *  2 - индикатор, 3 - детальная диагностика)
     *  
     * @return bool - true в случае выполнения без ошибок
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
     * 
     * @return bool - true в случае выполнения без ошибок
     */
    public function todo($code,$intvar,$mixedvar)
    {
        return true;
    }

    /** Возвращает название таблицы без префикса (mdl_)
     * 
     * @return text
     */
    public function tablename()
    {
        // Имя таблицы, с которой работаем
        return 'block_dof_s_taxonomies';
    }
    
    // **********************************************
    //       Методы для работы с полномочиями
    // **********************************************    
    
    /** 
     * Получить список параметров для фунции has_hight()
     * 
     * @param string $action - совершаемое действие
     * @param int $objectid - id объекта над которым совершается действие
     * @param int $personid
     * 
     * @return stdClass - список параметров для фунции has_hight()
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
        $result->objectid = $objectid;
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
    protected function acl_check_access_parametrs($acldata)
    {
        return $this->dof->storage('acl')->
                    has_right($acldata->plugintype, $acldata->plugincode, $acldata->code, 
                              $acldata->personid, $acldata->departmentid, $acldata->objectid);
    }  
    
    /** Возвращает стандартные полномочия доступа в плагине
     * 
     * @return array
     *  a[] = ['code'  => 'код полномочия',
     * 				 'roles' => ['student' ,'...'];
     */
    public function acldefault()
    {
        $a = [];
        
        $a['view'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        
        return $a;
    }   
    
    /** 
     * Функция получения настроек для плагина
     *  
     *  @param string $code
     *  
     *  @return array
     */
    public function config_default($code = null)
    {
        // Массив конфигов
        $config = [];
        
        return $config;
    }      
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /**
     * Сохранение
     *
     * @param string|stdClass|array $data
     * @param array $options
     *
     * @return int | false
     *
     * @throws dof_exception_dml
     */
    public function save($data = null, $options = [])
    {
        // Нормализация данных
        try {
            $normalized_data = $this->normalize($data, $options);
        } catch ( dof_exception_dml $e )
        {
            throw new dof_exception_dml('error_save_'.$e->errorcode);
        }
    
        // Сохранение данных
        if ( isset($normalized_data->id) && $this->is_exists($normalized_data->id) )
        {// Обновление записи
            $item = $this->update($normalized_data);
            if ( empty($item) )
            {// Обновление не удалось
                throw new dof_exception_dml('error_save_classificator');
            } else
            {// Обновление удалось
                $this->dof->send_event('storage', 'classificator', 'item_saved', (int)$normalized_data->id);
                return $normalized_data->id;
            }
        } else
        {// Создание записи
            $itemid = $this->insert($normalized_data);
            if ( ! $itemid )
            {// Добавление не удалось
                throw new dof_exception_dml('error_save_classificator');
            } else
            {// Добавление удалось
                $this->dof->send_event('storage', 'classificator', 'item_saved', (int)$itemid);
                return $itemid;
            }
        }
    }
    
    /**
     * Нормализация данных
     *
     * @param string|stdClass|array $data
     * @param array $options
     *
     * @return stdClass
     *  
     * @throws dof_exception_dml
     */
    public function normalize($data, $options = [])
    {
        // Нормализация входных данных
        if ( is_object($data) || is_array($data) )
        {// Комплексные данные
            $data = (object)$data;
        } elseif ( is_string($data) )
        {// Передано название
            $name = $data;
            $data = new stdClass();
            $data->name = $name;
        } else
        {// Неопределенные данные
            throw new dof_exception_dml('invalid_data');
        }
    
        // Нормализация идентификатора
        if ( isset($data->id) && $data->id < 1)
        {
            unset($data->id);
        }
        // Проверка входных данных
        if ( empty($data) )
        {// Данные не переданы
            throw new dof_exception_dml('empty_data');
        }
        
        if ( isset($data->id) )
        {// Проверка на существование
            if ( ! $this->get($data->id) )
            {
                throw new dof_exception_dml('classificator_not_found');
            }
            if ( ! empty($data->code) && $this->get_record(['code' => $data->code]) )
            {
                unset($data->code);
            }
        }
        
        if ( empty($data->id) )
        {
            if ( empty($data->code) || empty($data->name) || empty($data->type) )
            {
                throw new dof_exception_dml('classificator_empty');
            }
        }
        
        // Создание объекта для сохранения
        $saveobj = clone $data;
    
        // НОРМАЛИЗАЦИЯ ПОЛЕЙ
        if ( isset($saveobj->name) )
        {
            $saveobj->name = mb_strimwidth($saveobj->name, 0, 255);
        }
        // НОРМАЛИЗАЦИЯ ПОЛЕЙ
        if ( isset($saveobj->description) )
        {
            $saveobj->description = mb_strimwidth($saveobj->description, 0, 255);
        }
        if ( isset($saveobj->code) )
        {
            $saveobj->code = mb_strimwidth($saveobj->code, 0, 50);
        }
        if ( isset($saveobj->type) )
        {
            $saveobj->type = mb_strimwidth($saveobj->type, 0, 50);
        }
        if ( property_exists($saveobj, 'canbedeleted') )
        {
            if ( ! empty($saveobj->canbedeleted) )
            {
                $saveobj->canbedeleted = 1;
            } else
            {
                $saveobj->canbedeleted = 0;
            }
        } else
        {
            $saveobj->canbedeleted = 1;
        }
    
        return $saveobj;
    }
}
