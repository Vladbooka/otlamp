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

require_once $DOF->plugin_path('storage','config','/config_default.php');

/**
 * Справочник категорий достижений
 * 
 * @package    storage
 * @subpackage achievementcats
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_storage_achievementcats 
        extends dof_storage 
        implements dof_storage_config_interface
{
    /**
     * Объект деканата для доступа к общим методам
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
        if ( ! parent::install() )
        {
            return false;
        }
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
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
        global $CFG, $DB;
        $result = true;
        // Методы для установки таблиц из xml
        require_once($CFG->libdir.'/ddllib.php');
        
        $manager = $DB->get_manager();
        $table = new xmldb_table($this->tablename());
        if ( $oldversion < 2015102000 )
        {// Добавление поля подразделения
            $field = new xmldb_field(
                    'departmentid', 
                    XMLDB_TYPE_INTEGER, 
                    '10', 
                    XMLDB_UNSIGNED, 
                    FALSE, 
                    FALSE, 
                    '0'
            );
            
            if ( ! $manager->field_exists($table, $field) )
            {// Поле не установлено
                $manager->add_field($table, $field);
            }
            
            // Добавление индекса к полю
            $index = new xmldb_index(
                    'departmentid', 
                    XMLDB_INDEX_NOTUNIQUE,
                    ['departmentid']
            );
            if ( ! $manager->index_exists($table, $index))
            {// Индекс не установлен
                $manager->add_index($table, $index);
            }
        }
        if ( $oldversion < 2016111100 )
        { // Добавление поля участия в рейтинге
            $field = new xmldb_field('affectrating', XMLDB_TYPE_INTEGER, '1', FALSE, TRUE, FALSE, '1');
            
            if ( ! $manager->field_exists($table, $field) )
            { // Поле не установлено
                $manager->add_field($table, $field);
            }
            
            // Добавление индекса к полю
            $index = new xmldb_index('affectrating', XMLDB_INDEX_NOTUNIQUE, [
                'affectrating'
            ]);
            if ( ! $manager->index_exists($table, $index) )
            { // Индекс не установлен
                $manager->add_index($table, $index);
            }
        }
        
        return $result && $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    
    /**
     * Возвращает версию установленного плагина
     * 
     * @return int - Версия плагина
     */
    public function version()
    {
		return 2020022800;
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
     * 
     * Оно должно быть уникально среди плагинов этого типа
     * 
     * @return string
     */
    public function code()
    {
        return 'achievementcats';
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
		                'config'      => 2011080900,
		                'acl'         => 2011041800,
		                'departments' => 2015102000
		       ],
		       'storage' => [
		                'departments' => 2014092201
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
		                'config'      => 2011080900,
		                'acl'         => 2011041800,
		                'departments' => 2015102000
		       ],
		       'storage' => [
		                'departments' => 2014092201
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
        // Пока событий не обрабатываем
        return array();
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
    public function is_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
        if ( $this->dof->is_access('datamanage') OR 
             $this->dof->is_access('admin') OR 
             $this->dof->is_access('manage') 
        )
        {// Открыть доступ для менеджеров
            return true;
        }
        
        // Получаем ID персоны, с которой связан данный пользователь 
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        
        // Дополнительные проверки прав
        switch ( $do )
        {
            // Возможность использования раздела пользователем
            case 'use' :
                // Получение категории
                $category = $this->dof->storage('achievementcats')->get($objid);
                if ( ! empty($category) )
                {// Раздел найден
                    
                    if ( $this->dof->plugin_exists('workflow', 'achievementcats') )
                    {// Маршруты статусов определены
                        
                        // Получение активных статусов раздела
                        $statuses = $this->dof->workflow('achievementcats')->get_meta_list('active');
                        
                        // Получение траектории разделов
                        $cattree = $this->dof->storage('achievementcats')->get_categorytree($category->id);
                        
                        if ( ! empty($cattree) )
                        {// Траектория установлена
                            
                            // Проверка активности каждого раздела траектории
                            foreach ( $cattree as $level => $catitem )
                            {
                                if ( ! array_key_exists($catitem->status , $statuses) )
                                {// Статус раздела не является активным
                                    return false;
                                }
                            }
                        }
                    }
                }
                break;
            case 'use:any':
                // Тут пишем костыль для проверки права, т.к. в текущей архитектуре корректно такое право не проверить
                /**
                 * @todo добавить архитектурное решение
                 * Архитектурное решение (технический долг):
                 * В справочник прав (dof_acl) поле "targetdeps" (целевые подразделения), которое задает параметры для определения списка подразделений, на которые будет действовать это право (если не указан конкретный id).
                 * Варианты:
                 * - Текущее и дочерние (по умолчанию) - так все работает сейчас.
                 * - Только текущее (без дочерних)
                 * - Только дочерние (без текущего).
                 * - Иерархия подразделений (все дочерние, текущее и его родители, без "братьев и племянников"). (Дима называет это веткой)
                 * - Дерево подразделений (все подразделения текущего дерева. Но если есть второе дерево с другим корнем, на него не распраняется).
                 * - Все подразделения.
                 */
                if (is_null($depid)) {
                    if ($departments = $this->dof->storage('departments')->get_records(['status' => 'active'])) {
                        // Получим все подразделения и передадим их на проверку права
                        foreach ($departments as $department) {
                            if (is_null($department->id)) {
                                continue;
                            }
                            if ($this->is_access($do, $objid, $userid, $department->id)) {
                                return true;
                            }
                        }
                        return false;
                    }
                } else {
                    break;
                }
            default:
                break;
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
    public function catch_event($gentype,$gencode,$eventcode,$intvar,$mixedvar)
    {
        // Ничего не делаем, но отчитаемся об "успехе"
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
    public function cron($loan,$messages)
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
   
    /** 
     * Возвращает название таблицы без префикса (mdl_)
     * @return text
     * @access public
     */
    public function tablename()
    {
        // Имя таблицы, с которой работаем
        return 'block_dof_s_achievementcats';
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

    /** 
     * Проверить права через плагин acl.
     * 
     * Функция вынесена сюда, чтобы постоянно не писать 
     * длинный вызов и не перечислять все аргументы
     * 
     * @param object $acldata - объект с данными для функции storage/acl->has_right() 
     * 
     * @return bool
     */
    protected function acl_check_access_paramenrs($acldata)
    {
        return $this->dof->storage('acl')->
                    has_right(
                            $acldata->plugintype, 
                            $acldata->plugincode, 
                            $acldata->code, 
                            $acldata->personid, 
                            $acldata->departmentid, 
                            $acldata->objectid
        );
    }    
      
    /** 
     * Задаем права доступа для объектов этого хранилища
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = array();
        
        $a['view']   = ['roles' => [
                'manager'
        ]];
        $a['edit']   = ['roles' => [
                'manager'
        ]];
        $a['create'] = ['roles' => [
                'manager'
        ]];
        $a['use'] = [
            'roles' => [
                'root',
                'teacher',
                'manager',
                'student',
                'methodist',
                'parent',
                'user'
            ]
        ];
        // Право использовать любой раздел достижений
        $a['use:any'] = [
            'roles' => [
                'root',
                'teacher',
                'manager',
                'student',
                'methodist',
                'parent',
                'user'
            ]
        ];
       
        return $a;
    }

    /** 
     * Функция получения настроек для плагина 
     */
    public function config_default($code=null)
    {
        $config = [];
        
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'default_achievementcat';
        $obj->value = '0';
        $config[$obj->code] = $obj;
        
        return $config;
    }       
    
    // **********************************************
    //              Собственные методы
    // ********************************************** 
    
    /**
     * Сохранить раздел
     * 
     * @param object $object - Объект раздела
     *                  Обязательные поля:
     *                  ->name - Имя раздела
     *                  ->departmentid - Подразделение раздела
     *                  Необязательные поля
     *                  ->parentid - ID родительского раздела
     *                  ->sortorder - Вес сортировки
     *                  
     * @param array $options - Массив дополнительных параметров
     * 
     * @return bool|int - false в случае ошибки или ID раздела в случае успеха
     */
    public function save( $object = null, $options = array() )
    {
        // Проверка входных данных
        if ( empty($object) || ! is_object($object) )
        {// Проверка не пройдена
            return false;
        }
        
        // Создаем объект для сохранения
        $saveobj = clone $object;
        // Убираем автоматически генерируемые поля
        unset($saveobj->status);
        
        // Нормализация значений
        if ( isset($saveobj->parentid) && $saveobj->parentid < 0 )
        {// Сброс значения
            $saveobj->parentid = 0;
        }
        if ( $saveobj->parentid > 0 )
        {// Проверка на существование родителя
            if ( ! $this->get($saveobj->parentid) )
            {// Родитель не нйден - сброс значения
                $saveobj->parentid = 0;
            }
        }
        
        if ( isset($saveobj->id) && $saveobj->id > 0 )
        {// Обновление записи
            // Получим запись из БД
            $oldobject = $this->get($saveobj->id);
            if ( empty($oldobject) )
            {// Запись не найдена
                return false;
            }
            
            // Обновляем запись
            $res = $this->update($saveobj);
            if ( empty($res) )
            {// Обновление не удалось
                return false;
            } else
            {// Обновление удалось
                return $saveobj->id;
            }
        } else
        {// Создание записи
            // Убираем автоматически генерируемые поля
            unset($saveobj->id);
            
            // Добавляем дату создания
            $saveobj->createdate = time();
            // Сортировка
            $saveobj->sortorder = $this->get_sortorder($saveobj);
            
            // Добавляем запись
            $res = $this->insert($saveobj);
            if ( empty($res) )
            {// Добавление не удалось
                return false;
            } else
            {// Добавление удалось
                return $res;
            }
        }
    }
    
    /**
     * Сформировать вес раздела для сортировки
     * 
     * @param stdClass $category - Объект раздела 
     * 
     * @return int - Вес раздела
     */
    private function get_sortorder($category)
    {
        // Значение по умолчаниюs
        $defaultsortorder = 1;
        
        // Получение максимального веса
        $maxsortorder = (int)$this->get_field([
            'parentid' => $category->parentid
        ], 'MAX(sortorder)');
        
        if( ! empty($maxsortorder) )
        {// Получен максимальный вес
            // Установка веса раздела
            return (int)$maxsortorder + 1;
        }
        
        // Вес по-умочанию
        return $defaultsortorder;
    }
    
    /**
     * Получить полное значение веса сортировки от корневого раздела
     *
     * @param int $id - ID раздела
     * 
     * @return string - Полное значение веса сортировки
     */
    public function get_sortorder_fullpath($id)
    {
        $sortorderfullpath = [];
        
        // Получение массива траектории до целевого раздела
        $categorytree = (array)$this->get_categorytree($id);
        
        foreach( $categorytree as $achievementcat )
        {
            // Составление массив весов сортировки
            $sortorderfullpath[] = $achievementcat->sortorder;
        }
        return implode('-', $sortorderfullpath);
    }
    
    /**
     * Получить массив траектории до целевого раздела
     *
     * @param int $id - ID целевого раздела
     *
     * @param array $options - Массив дополнительных параметров
     *
     * @return array - Массив разделов от коренного до целевого
     */
    public function get_categorytree( $id, $options = [] )
    {
        // Результирующий массив
        $tree = [];
        // Защита от зацикливания
        $stop = 100;
        do {
            $stop--;
            // Получение раздела
            $category = $this->get($id);
            if ( empty($category) )
            {// Остановка поиска
                $stop = 0;
            } else
            {// Раздел найден
                $tree[] = $category;
                if ( ! empty($category->parentid) )
                {// Указан родитель
                    $id = $category->parentid;
                } else 
                {// Родитель не указан
                    $stop = 0;
                }
            }
        } while ( $stop > 0 );
        
        // Инвертирование массива
        $tree = array_reverse($tree, false);
        
        return $tree;
    }
    
    /**
     * Получить массив дочерних разделов
     *
     * @param int $id - ID родительского раздела
     *
     * @param array $options - Массив дополнительных параметров
     *              ['statuses'] - Массив допустимых статусов в формате ['active', 'notactive', ...]
     *              ['departmentid'] - Подразделение, в котором находятся категории
     *              ['levels'] - Уровень вложенности, которым должен ограничиваться результат ()
     *                                  0 - переданный ID текущего родителя,
     *                                  1 - ближайшие потомки
     *                                  ... 
     * @return array - Массив разделов
     */
    public function get_categories($id, $options = [])
    {
        $categories = [];
        
        if ( isset($options['levels']) && $options['levels'] < 1 )
        {// Уровень вложенности нулевой
            return $categories;
        }
        // Формирование параметров
        $params = ['parentid' => $id];
        if ( isset($options['statuses']) && is_array($options['statuses']))
        {// Переданы допустимые статусы
            // Установка фильтрации по статусу
            $params['status'] = $options['statuses'];
        }
        if ( isset($options['departmentid']) )
        {// Передано подразделение
            if( ! empty($options['exclude_subdepartments']) )
            {
                $department = $this->dof->storage('departments')->get((int)$options['departmentid']);
                if( ! empty($department) )
                {
                    $params['departmentid'] = [$department->id];
                }
            } else
            {
                // Получение всех активных дочерних подразделений
                $statuses = $this->dof->workflow('departments')->get_meta_list('active');
                $statuses = array_keys($statuses);
                $departments = $this->dof->storage('departments')->get_departments($options['departmentid'], ['statuses' => $statuses]);
                $departments[$options['departmentid']] = NULL;
                $departments = array_keys($departments);
                // Установка фильтрации по подразделениям
                $params['departmentid'] = $departments;
            }
        }

        // Получение разделов
        $items = $this->get_records($params, 'sortorder ASC');
        
        if ( empty($items) )
        {// Разделы не найдены
            return $categories;
        }
        // Добавление текущих разделов 
        $categories = $categories + $items;

        foreach ( $items as $category )
        {// Получение дочерних разделов
            if ( isset($options['levels']) )
            {// Уровень вложенности передан
                $options['levels']--;
            }
            $children = $this->get_categories($category->id, $options);
            $categories = $categories + $children;
        }
        return $categories;
    }
    

    /**
     * Получить список разделов
     *
     * @param $id - ID родительского раздела
     * @param $level - Уровень вложенности списка разделов
     *
     * @return stdClass - Объект с данными состава разделов
     */
    public function get_categories_list($id = 0, $level = 0, $options = NULL)
    {
        $categories = [];
    
        $conditions = [
            'parentid' => $id
        ];

        if( ! empty($options['metalist']) )
        {// Фильтрация разделов по статусам
            $statuses = $this->dof->workflow('achievementcats')->get_meta_list($options['metalist']);
            $statuses = array_keys($statuses);
            $conditions['status'] = $statuses;
        }
        
        if( ! empty($options['affectrating']) )
        {// Фильтрация по участию в рейтинге
            $conditions['affectrating'] = $options['affectrating'];
        }
        
        if( ! empty($options['departmentid']) )
        {// Фильтрация по подразделению
            $conditions['departmentid'] = $options['departmentid'];
        }
        
        $sortorder = '';
        if( ! empty($options['sortorder']) )
        {//указан порядок сортировки
            $sortorder = ' '.$options['sortorder'].' ';
        }
        
        // Получим cписок дочерних элементов       
        $children = $this->dof->storage('achievementcats')->
            get_records($conditions, $sortorder, 'id, name');

        if ( ! empty($children) )
        {// Сформируем массив
            // Получим отступ
            $shift = str_pad('', $level, '-');
    
            foreach ( $children as $cat )
            {
                // Сформируем элемент раздела
                $categories[$cat->id] = $shift.$cat->name;
                
                if( empty($options['maxdepth']) || 
                    ( ! empty($options['maxdepth']) && $options['maxdepth'] < ($level+1)) )
                {
                    // Добавим к исходному массиву дочерние элементы
                    $categories += $this->get_categories_list($cat->id, $level + 1, $options);
                }
            }
        }
    
        return $categories;
    }
    
    /**
     * Право использовать хотя бы какую-нибудь категорию
     * 
     * @param int $userid - идентификатор пользователя
     * @param int $depid - идентификатор подразделения
     * @return boolean
     */
    public function is_access_use_any($userid, $depid)
    {
        $categories = $this->get_categories(0);
        foreach($categories as $category)
        {
            if ( $this->is_access('use',$category->id, $userid, $depid) )
            {
                return true;
            }
        }
        return false;
    }
    
    /**
     * @see dof_im_achievements_usersfilter_userform (Скопирован метод для публичного использования)
     * Получить массив доступных разделов
     *
     * Разделы, которые не доступны для пользователя,
     *  но среди своих дочерних имеют доступные, возвращаются со строковым ключем
     *
     * @param int $parentcat - Раздел, от которого начинается сбор массива
     *
     * @return array - Массив для добавления в select список
     */
    public function get_categories_select_options($parentcat = 0, $options = [])
    {
        $available = [];
        
        if ( ! isset($options['_level']) )
        {// Системная опция уровня вложенности
            // Стандартный разделитель
            $options['_level'] = 0;
        }
        
        $subcats = [];
        
        // Разделы текущего уровня
        $achievementcats = (array)$this->dof->storage('achievementcats')->
        get_categories_list($parentcat, 0, [
            'metalist' => 'active',
            'maxdepth'=>'1',
            'sortorder' => 'sortorder ASC, id ASC'
        ]
                );
        $delimiter = str_repeat('-', $options['_level']);
        $options['_level']++;
        // Добавление дочерних разделов
        foreach ( $achievementcats as $achievementcatid => $name )
        {
            $subcats = $subcats + $this->get_categories_select_options($achievementcatid, $options);
        }
        
        // Доступность раздела в рейтинге
        $affectrating = (bool)$this->dof->storage('achievementcats')->
        get_field($parentcat, 'affectrating');
        
        if ( ( ! empty($subcats) || $affectrating ) && $parentcat )
        {// Раздел доступен в рейтинге, или же у него есть дочерние разделы
            
            // Формирование ключа
            $key = (integer)$parentcat;
            if ( ! $affectrating )
            {// Раздел скрыт - строка
                // Для сохранения строкового типа ключа требуется добавить в начале 0
                $key = '0'.(string)$key;
            }
            
            // Формирование значения
            $keyval = $this->dof->get_string('form_default_achievementcat_catnotfound', 'achievements', $parentcat);
            if ( $parentcat == 0 )
            {// Все объекты
                $keyval = $this->dof->get_string('form_default_achievementcat_choose', 'achievements');
            } else
            {// Получение имени раздела
                $achievementcat = $this->dof->storage('achievementcats')->get($parentcat);
                if ( ! empty($achievementcat) )
                {
                    $keyval = $achievementcat->name;
                }
            }
            $available = $available + [$key => $delimiter.$keyval];
        }
        $available = $available + $subcats;
        
        return $available;
    }
    /** Возвращает настройку категории достижений или ее ближайшего родителя у когорого настройка задана
     *
     * @param string $catid - ид категории
     * @param string $configcode - код настройки
     * @return object
     */
    public function get_config($catid, $configcode)
    {
        $result = false;
        $cache = $this->dof->get_cache('storage', $this->code(), 'configs');
        $key = md5($configcode . $catid);
        if ($cache !== false) {
            $result = $cache->get($key);
        }
        if ($result === false) {
            $conditions = [
                'plugintype' => 'storage',
                'plugincode' => 'achievementcats',
                'objectid' => $catid,
                'code' => $configcode
            ];
            if (!$result = $this->dof->storage('cov')->get_records($conditions)) {
                $cattreearray = [];
                // Получим массив идентификаторов разделов по траектории от корневого до текущего раздела
                foreach ($this->get_categorytree($catid) as $cat) {
                    $cattreearray[] = $cat->id;
                }
                if (!empty($cattreearray)) {
                    list($cattree, $params) = $this->get_in_or_equal($cattreearray);
                    $where = "plugintype = 'storage' AND plugincode = 'achievementcats'";
                    $where .= " AND code = '" . $configcode . "' AND objectid " . $cattree;
                    // Выберем настройки разделов где ид раздела содержится в массиве траекторий
                    $parentcatresult = $this->dof->storage('cov')->get_records_select($where, $params);
                    // Далее нам требуется отсортировать массив настроек по категориям согласно траектории.
                    // Чтобы получить правильный порядок от текущей категории до корневой требуется отсортировать
                    // по массиву траектории, на mysql есть решение ORDER BY FIELD но оно не поддерерживается PG
                    if (is_array($parentcatresult) && !empty($parentcatresult)) {
                        $sort = array_flip($cattreearray);
                        // Отсартируем выборку согласно траектории
                        usort($parentcatresult, function($a,$b) use($sort){
                            return $sort[$a->objectid] - $sort[$b->objectid];
                        });
                        // Возмем последний элемент, это и будет ближайший родитель текущей категории 
                        // у которого задана передаваемая в метод настройка
                        end($parentcatresult);
                        $result = [];
                        $result[key($parentcatresult)] = $parentcatresult[key($parentcatresult)];
                    } else {
                        $result = $parentcatresult;
                    }
                }
            }
            if( $cache !== false )
            {
                $cache->set($key, $result);
            }
        }
        if (!is_array($result)) {
            return null;
        }
        foreach($result as $res) {
            return $res;
        }
    }
    
    /** Получить только значение указанной настройки
     *
     * @param string $catid - ид категории
     * @param string $configcode - код настройки
     * @return string - значение настройки
     */
    public function get_config_value($catid, $configcode)
    {
        $config = $this->get_config($catid, $configcode);
        if (!is_object($config)) {
            return false;
        }
        return $config->value;
    }
    /**
     * Очищает кеш категорий
     * 
     * @return boolean
     */
    public function del_categories_config_cache()
    {
        $cache = $this->dof->get_cache('storage', $this->code(), 'configs');
        if ($cache !== false) {
            return $cache->purge();
        }
        return false;
    }
}   
?>