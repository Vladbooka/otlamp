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
 * Справочик причин отсуствия на занятии
 *
 * @package    storage
 * @subpackage schabsenteeism
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_storage_schabsenteeism extends dof_storage implements dof_storage_config_interface
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
        return $this->dof->storage('acl')->save_roles($this->type(), $this->code(), 
            $this->acldefault());
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
        return true;
    }
     
    /**
     * Возвращает версию установленного плагина
     *
     * @return int - Версия плагина
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
        return 2017092900;
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
        return 'schabsenteeism';
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
                        'schevents' => 2016071500,
                        'persons' => 2017030200,
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
        return 'block_dof_s_schabsenteeism';
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
        
        // Создавать причину отсутствия
        $a['create'] = [
            'roles' => [
                'manager'
            ]
        ];
        // Создавать причину отсутствия для личного использования
        $a['create/owner'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher'
            ]
        ];
        // Редактировать причину отсутствия
        $a['edit'] = [
            'roles' => [
                'manager'
            ]
        ];
        // Редактировать причину отсутствия для личного использования
        $a['edit/owner'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher'
            ]
        ];
        // Видеть полные данные причины отсутствия
        $a['view'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher'
            ]
        ];
        // Видеть полные данные причины отсутствия для личного использования
        $a['view/owner'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher'
            ]
        ];
        // Видеть краткие данные причины отсутствия
        $a['viewdesk'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher'
            ]
        ];
        // Видеть краткие данные причины собственного отсутствия 
        $a['viewdesk/my'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student',
                'user'
            ]
        ];
        // Видеть краткие данные личных причин отсутствия
        $a['viewdesk/owner'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student',
                'user'
            ]
        ];
        // Использовать причины отсутствия
        $a['use'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher'
            ]
        ];
        // Использовать личные причины отсутствия
        $a['use/owner'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher'
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
     * Получить краткое название причины отсутствия
     *
     * @param int|stdClass $schabsenteeism - Причина отсутствия или ID
     *
     * @return null|string - Краткое имя или null в случае ошибки
     */
    public function get_shortname($schabsenteeism)
    {
        // Получение объекта
        if ( ! is_object($schabsenteeism) )
        {
            $schabsenteeism = $this->get((int)$schabsenteeism);
            if ( empty($schabsenteeism) )
            {// Объект не найден
                return null;
            }
        }
    
        // Получение краткого имени
        $stringvars = new stdClass();
        $stringvars->name = $schabsenteeism->name;
        return $this->dof->get_string('schabsenteeism_shortname', 'schabsenteeism', $stringvars, 'storage');
    }
    
    /**
     * Получить типы причин отсутствия
     *
     * @return array - Список типов
     */
    public function get_types()
    {
        return [
            0 => $this->dof->get_string('schabsenteeism_explained', 'schabsenteeism', null, 'storage'),
            1 => $this->dof->get_string('schabsenteeism_unexplained', 'schabsenteeism', null, 'storage')
        ];
    }
    
    /**
     * Получить локализованный тип причины отсутствия
     * (Уважительная\Неуважительная)
     *
     * @param int|stdClass $schabsenteeism - Причина отсутствия или ID
     *
     * @return null|string - Краткое имя или null в случае ошибки
     */
    public function get_localized_type($schabsenteeism)
    {
        // Получение объекта
        if ( ! is_object($schabsenteeism) )
        {
            $schabsenteeism = $this->get((int)$schabsenteeism);
            if ( empty($schabsenteeism) )
            {// Объект не найден
                return null;
            }
        }
    
        $stringvars = new stdClass();
        if ( $schabsenteeism->unexplained == 1 )
        {// Неуважительная причина
            return $this->dof->get_string('schabsenteeism_unexplained', 'schabsenteeism', $stringvars, 'storage');
        } else 
        {// Уважительная причина
            return $this->dof->get_string('schabsenteeism_explained', 'schabsenteeism', $stringvars, 'storage');
        }
    }
    
    /**
     * Получить локализованные данные о владельце
     * (Публичный\ФИО)
     *
     * @param int|stdClass $schabsenteeism - Причина отсутствия или ID
     *
     * @return null|string - Краткое имя или null в случае ошибки
     */
    public function get_localized_owning($schabsenteeism)
    {
        // Получение объекта
        if ( ! is_object($schabsenteeism) )
        {
            $schabsenteeism = $this->get((int)$schabsenteeism);
            if ( empty($schabsenteeism) )
            {// Объект не найден
                return null;
            }
        }
    
        $stringvars = new stdClass();
        if ( ! empty($schabsenteeism->ownerid) )
        {// Указан владелец
            $fullname = $this->dof->storage('persons')->get_fullname($schabsenteeism->ownerid);
            if ( empty($fullname) )
            {
                return $this->dof->get_string('schabsenteeism_owning_notfound', 'schabsenteeism', $stringvars, 'storage'); 
            }
            return $fullname;
        } else
        {// Публичная причина
            return $this->dof->get_string('schabsenteeism_owning_public', 'schabsenteeism', $stringvars, 'storage');
        }
    }
    
    /**
     * Сохранить причину
     *
     * @param string|stdClass|array $data - Данные причины отсутствия(название или комплексные данные)
     * @param array $options - Массив дополнительных параметров
     *
     * @return int - false в случае ошибки или ID причины в случае успеха
     *
     * @throws dof_exception_dml - В случае ошибки
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
            $schabsenteeism = $this->update($normalized_data);
            if ( empty($schabsenteeism) )
            {// Обновление не удалось
                throw new dof_exception_dml('error_save_schabsenteeism');
            } else
            {// Обновление удалось
                $this->dof->send_event('storage', 'schabsenteeism', 'item_saved', (int)$normalized_data->id);
                return $normalized_data->id;
            }
        } else
        {// Создание записи
            $schabsenteeismid = $this->insert($normalized_data);
            if ( ! $schabsenteeismid )
            {// Добавление не удалось
                throw new dof_exception_dml('error_save_schabsenteeism');
            } else
            {// Добавление удалось
                $this->dof->send_event('storage', 'schabsenteeismid', 'item_saved', (int)$schabsenteeismid);
                return $schabsenteeismid;
            }
        }
    }
    
    /**
     * Нормализация данных причины отсутствия
     *
     * Формирует объект причины на основе переданных данных. В случае критической ошибки
     * или же если данных недостаточно, выбрасывает исключение.
     *
     * @param string|stdClass|array $data - Данные причины отсутствия(название или комплексные данные)
     * @param array $options - Опции работы
     *
     * @return stdClass - Нормализовализованный Объект причины
     * 
     * @throws dof_exception_dml - Исключение в случае критической ошибки или же недостаточности данных
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
            {// Причина не найдена
                throw new dof_exception_dml('schabsenteeism_not_found');
            }
        }
    
        // Создание объекта для сохранения
        $saveobj = clone $data;
    
        // Обработка входящих данных и построение объекта
        if ( isset($saveobj->id) && $this->is_exists($saveobj->id) )
        {// Причина уже содержится в системе
            // Удаление автоматически генерируемых полей
            unset($saveobj->status);
        } else
        {// Новая причина
    
            // АВТОЗАПОЛНЕНИЕ ПОЛЕЙ
            if ( ! isset($saveobj->name) || empty($saveobj->name) )
            {// Установка названия по умолчанию
                $saveobj->name = '';
            }
            if ( ! isset($saveobj->ownerid) || (int)$saveobj->ownerid < 0 )
            {// Установка владельца по умолчанию
                $saveobj->ownerid = 0;
            }
            if ( ! isset($saveobj->unexplained) || (int)$saveobj->unexplained < 0 )
            {// Установка типа по умолчанию
                $saveobj->unexplained = 0;
            }
        }
    
        // НОРМАЛИЗАЦИЯ ПОЛЕЙ
        if ( isset($saveobj->name) )
        {// Указано название
            $saveobj->name = mb_strimwidth($saveobj->name, 0, 255);
        }
        if ( isset($saveobj->unexplained) )
        {// Указано название
            $saveobj->unexplained = (int)$saveobj->unexplained;
        }
        if ( isset($saveobj->ownerid) )
        {// Указано название
            $saveobj->ownerid = (int)$saveobj->ownerid;
        }
    
        // ВАЛИДАЦИЯ ДАННЫХ
        // Проверки типа
        if ( isset($saveobj->unexplained) )
        {
            $types = $this->get_types();
            if ( ! isset($types[(int)$saveobj->unexplained]) )
            {
                throw new dof_exception_dml('notvalid_type');
            }
            
        }
        
        // Проверки владельца
        if ( ! empty($saveobj->ownerid) )
        {
            if ( ! $this->dof->storage('persons')->is_exists($saveobj->ownerid) )
            {
                throw new dof_exception_dml('notvalid_ownerid');
            }
        }
    
        return $saveobj;
    }
    
    /**
     * Обработка AJAX-запросов из форм
     *
     * @param string $querytype - тип запроса
     * @param int $objectid - id объекта с которым производятся действия
     * @param array $data - дополнительные данные пришедшие из json-запроса
     * 
     * @return array
     */
    public function widgets_field_variants_list($querytype, $depid, $data)
    {
        switch ( $querytype )
        {
            // Формируем список для autocomplete
            // Список причин
            case 'reasons_for_absence':
                return $this->widgets_reasons_list($depid, $data);
                
            default: 
                return [];
        }
    }
    
    /**
     * Получить список причин
     *
     * @param int $departmenid - подразделение (пока не используется)
     * @param string $name - первые несколько букв названия причины
     * 
     * @return array массив объектов для AJAX-элемента dof_autocomplete
     */
    protected function widgets_reasons_list($depid, $fullname)
    {
        $fullname = clean_param($fullname, PARAM_TEXT);
        
        $select = " ( name LIKE '%" . $fullname . "%'
        		OR id LIKE '%" . $fullname . "%' )";
        if ( ! $list = $this->get_records_select($select, null, ' name ASC', 'id, name', 0, 15) )
        {// Нет причин с такими данными
            return [];
        }
        
        // Формируем массив объектов нужной структуры для dof_autocomplete
        $result = [];
        
        foreach ( $list as $reason )
        {
            if ( $this->is_access('use', $reason->id) )
            {//если есть право - добавляем запись
                $obj = new stdClass;
                $obj->id = $reason->id;
                $obj->name = $reason->name . ' [' . $reason->id . ']';
                
                // Кладем в массив результатов
                $result[$reason->id] = $obj;
            }
        }
        
        return $result;
    }
    
    /**
     * Обработчик добавления причины
     * 
     * @param string $fieldname - название поля
     * @param string $reason - название причины
     * 
     * @return int|false - id причины
     */
    public function handle_reason($fieldname, $reason)
    {
        $value = $this->dof->modlib('widgets')->get_extvalues_autocomplete($fieldname, $reason);
        $obj = new stdClass;
        
        switch ( $value['do'] )
        {
            // Создаем новую запись (Уважительная)
            case "create_explained":
                $obj->name = $value['name'];
                $obj->unexplained = 0;
                if ( $this->dof->storage('schabsenteeism')->is_exists(['name' => $value['name']]) )
                {// Нашли вдруг такую запись, вернем ID
                    return $this->dof->storage('schabsenteeism')->get_record(['name' => $value['name']], 'id', IGNORE_MULTIPLE)->id;
                }
                return $this->dof->storage('schabsenteeism')->insert($obj);
            // Создаем новую запись (Неуважительная)
            case "create_unexplained":
                $obj->name = $value['name'];
                $obj->unexplained = 1;
                if ( $this->dof->storage('schabsenteeism')->is_exists(['name' => $value['name']]) )
                {// Нашли вдруг такую запись, вернем ID
                    return $this->dof->storage('schabsenteeism')->get_record(['name' => $value['name']], 'id', IGNORE_MULTIPLE)->id;
                }
                return $this->dof->storage('schabsenteeism')->insert($obj);
            // Выбор существующей причины
            case "choose":
                if ( ! $this->dof->storage('schabsenteeism')->is_exists($value['id']) ||
                         ! $this->dof->storage('schabsenteeism')->is_access('use', $value['id']) )
                { // Прав нет
                    return false;
                }
                return $value['id'];
            default:
                dof_debugging('autocomplete returned error', DEBUG_DEVELOPER);
                return null;
        }
    }
    
    /**
     * Получить причины отсутствия
     *
     * @param string $metastatus - метастатус
     *
     * @return array|false
     */
    public function get_reasons_by_metastatus($metastatus = 'real')
    {
        // Получаем статусы
        $statuses = array_keys($this->dof->workflow('schabsenteeism')->get_meta_list('real'));
        
        // Получаем причины
        $list = $this->dof->storage('schabsenteeism')->get_records(['status' => $statuses]);
        
        if ( ! empty($list) )
        {
            return $list;
        } else 
        {
            return false;
        }
    }
    
    /**
     * Получить название причины отсутствия
     *
     * @param stdClass|int $reason - Причина отсутствия, или ID причины
     *
     * @return string|null
     */
    public function get_name($reason)
    {
        if ( isset($reason->name) )
        {// Передан объект причины
            $name = (string)$reason->name;
        } else 
        {// Получение названия
            // Получение данных по ID причины
            $name = $this->get_field((int)$reason, 'name');
            if ( $name === false )
            {// Ошибка получения названия
                return null;
            }
        }
    
        return format_text((string)$name);
    }
} 
?>