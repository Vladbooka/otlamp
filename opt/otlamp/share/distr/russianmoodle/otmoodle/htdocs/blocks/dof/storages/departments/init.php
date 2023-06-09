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

/** Справочник подразделений учебного заведения
 * 
 */
class dof_storage_departments extends dof_storage implements dof_storage_config_interface
{

    /**
     * @var dof_control
     */
    protected $dof;
    
    public $zerotimezone = 'Europe/London';

    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    /** Устанавливает плагин в fdo
     * @return bool
     */
    public function install()
    {
        // Устанавливаем таблицы
        if ( !parent::install() )
        {
            return false;
        }
        $obj = new stdClass();
        $obj->name = 'Company';
        $obj->code = 'home';
        $obj->managerid = 1;
        $obj->leaddepid = 0;
        $obj->zone = 99;
        $obj->path = 1;
        $obj->depth = 0;
        $this->insert($obj);
        return $this->dof->storage('acl')->save_roles($this->type(), $this->code(), $this->acldefault());
    }

    /** 
     * Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $oldversion - версия установленного в системе плагина
     * @return boolean
     * @access public
     */
    public function upgrade($oldversion)
    {
        global $DB;
        
        $dbman = $DB->get_manager();
        
        $result = true;
        $table = new xmldb_table($this->tablename());
        if ( $oldversion < 2017022800 )
        {
            // Добавление поля описания
            $field = new xmldb_field(
                'description',
                XMLDB_TYPE_TEXT, 
                'small', 
                null, 
                null, 
                null, 
                null, 
                'code'
            ); 
            if ( !$dbman->field_exists($table, $field) )
            {// Поле не найдено
                // Добавить поле
                $dbman->add_field($table, $field);           
            }                    
        }
        return $result;
    }

    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        return 2018020700;
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
        return 'paradusefish';
    }

    /** Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'storage';
    }

    /** Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'departments';
    }

    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('addresses' => 2009050700, 
                                      'acl'       => 2011040504,
                                      'config'    => 2011080900));
    }

    /** Определить, возможна ли установка плагина в текущий момент
     * Эта функция одинакова абсолютно для всех плагинов и не содержит в себе каких-либо зависимостей
     * @TODO УДАЛИТЬ эту функцию при рефакторинге. Вместо нее использовать наследование
     * от класса dof_modlib_base_plugin 
     * @see dof_modlib_base_plugin::is_setup_possible()
     * 
     * @param int $oldversion [optional] - старая версия плагина в базе (если плагин обновляется)
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
     * @param int $oldversion [optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion = 0)
    {
        return array('storage' => array('acl'    => 2011040504,
                                        'config' => 2011080900));
    }

    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return array();
    }

    /** Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
        // Просим запускать крон не чаще раза в 15 минут
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
    public function is_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
        if ( $this->dof->is_access('datamanage') OR $this->dof->is_access('admin')
                OR $this->dof->is_access('manage') )
        {// манагеру можно все
            return true;
        }
        // получаем id пользователя в persons
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        // получаем все нужные параметры для функции проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid, $depid);
        // проверка
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// право есть заканчиваем обработку
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

    /** Конструктор
     * @param dof_control $dof - объект с методами ядра деканата
     * @access public
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
    }

    /** Возвращает название таблицы без префикса (mdl_)
     * @return text
     * @access public
     */
    public function tablename()
    {
        // Имя таблицы, с которой работаем
        return 'block_dof_s_departments';
    }

    /** Переопределение функции вставки записи в таблицу - для произведения дополнительных
     * операций с данными до или после вставки
     * 
     * @param object $dataobject - объект с данными для вставки
     * @param bool $quiet [optional]- не генерировать событий
     * @return mixed bool false если операция не удалась или id вставленной записи
     */
    public function insert($dataobject, $quiet = false, $bulk=false, $options = [])
    {
        if ( !$id = parent::insert($dataobject, $quiet) )
        {// вставка объекта не удалась
            return false;
        }
        // получаем только что вставленный в базу объект
        $oldobj = $this->get($id);
        if ( $oldobj->code )
        {// если код был уже указан - значит все хорошо
            return $id;
        }
        // Если код записи не указан - то заменим его на id
        $newobj = new stdClass();
        $newobj->id = $id;
        $newobj->code = 'id' . $id;

        // добавляем код к созданной записи и возвращаем результат
        // @todo проверить результат вставки и записать ошибку в лог если это не удалось
        $this->update($newobj);
        return $id;
    }
    
    // ***********************************************************
    //       Методы для работы с полномочиями и конфигурацией
    // ***********************************************************     

    /** Получить список параметров для фунции has_right()
     * 
     * @return object - список параметров для фунции has_right()
     * @param string $action - совершаемое действие
     * @param int $objectid - id объекта над которым совершается действие
     * @param int $personid - id из таблицы persons
     * @param int $depid - id из таблицы departments
     */
    protected function get_access_parametrs($action, $objectid, $personid, $depid = null)
    {
        $result = new stdClass();
        $result->plugintype = $this->type();
        $result->plugincode = $this->code();
        $result->code = $action;
        $result->personid = $personid;
        $result->departmentid = $depid;
        if ( is_null($depid) )
        {// подразделение не задано - берем текущее
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        $result->objectid = $objectid;
        if ( !$objectid )
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
      
    /** Задаем права доступа для объектов этого хранилища
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = array();
        // TODO определить datamanager
        $a['view']   = array('roles'=>array('manager','methodist'));
        $a['edit']   = array('roles'=>array('manager'));
        $a['use']    = array('roles'=>array('manager','methodist'));
        $a['create'] = array('roles'=>array('manager'));
        $a['delete'] = array('roles'=>array());
        // права учителя для его конкретных подразделений
        $a['view/mydep'] = array('roles' => array('teacher', 'methodist'));
        $a['edit/mydep'] = array('roles' => array('teacher', 'methodist'));

        return $a;
    }

    /** Функция получения настроек для плагина
     *  
     */
    public function config_default($code = null)
    {
        // плагин включен и используется
        $config = array();
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'enabled';
        $obj->value = '1';
        $config[$obj->code] = $obj;
        // Максимально разрешенное количество объектов этого типа в базе
        // (указывается индивидуально для каждого подразделения)
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'objectlimit';
        $obj->value = '-1';
        $config[$obj->code] = $obj;
        
        return $config;
    }

    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /**
     * Получить название подразделения
     *
     * @param stdClass|int $item - Подразделение, или ID подразделения
     *
     * @return string|null
     */
    public function get_name($item)
    {
        if ( isset($item->name) )
        {// Передан объект
            return format_string((string)$item->name);
        }
        
        // Получение данных по ID поля
        $name = $this->get_field((int)$item, 'name');
        if ( $name === false )
        {// Ошибка получения имени
            return null;
        }
        return format_string((string)$name);
    }
    
    /** Выводит список всех структурных подразделений в алфавитном порядке
     * @return array - список подразделений
     * @access public
     */
    public function departments_list()
    {// получим список всех подразделений
        $select = "(status <> 'deleted' OR status IS NULL)";
        $list = $this->get_records_select($select);
        //$list = $this->get_list();
        if ( $right = $this->get_right_dep() )
        {
            $depart = [];
            foreach ( $list as $data )
            {// сформируем из них массив - id подразделения=>его имя
                if ( in_array('view', $right[$data->id]) OR in_array('view/mydep', $right[$data->id]) )
                {
                    $depart[$data->id] = $data->name . ' [' . $data->code . ']';
                }
            }
            // отсортируем по алфавиту и вернем
            asort($depart);
            return $depart;
        }
        return array();
    }

    /** 
     * Получить список всех дочерних подразделений
     * 
     * @param int $departmentid - ID родительского подразделения
     * @param array $options - Массив параметров обработки
     *      ['statuses'] => Массив статусов возвращаемых подразделений
     * 
     * @return array - Массив дочерних подразделений 
     */
    public function get_departments($departmentid = 0, $options = [])
    {
        // Формирование параметров получения подразделений
        $select = '';
        if ( empty($departmentid) )
        {// Корень
            $select .= ' path LIKE :path ';
            $params = ['path' => '%' ];
        } else
        {// Относительно подразделения
            $select .= ' ( path LIKE :path1 OR path LIKE :path2 ) ';
            $params = ['path1' => '%/'.(int)$departmentid.'/%', 'path2' => (int)$departmentid.'/%'];
        }
        if ( ! empty($options['statuses']) )
        {// Передан массив статусов
            $statuses = implode("', '", $options['statuses']);
            $select .= " AND status IN ('{$statuses}') ";
        }
        
        $list = $this->get_records_select($select, $params, 'path ASC');

        return $list;
    }
    
    /**
     * Получить массив траектории до целевого подразделения
     *
     * @param int $id - ID целевого подразделения
     * @param array $options - Массив дополнительных параметров
     *				['inverse'] - Инвертирование итоговой траектории
     * @return array - Массив подразделений от коренного до целевого
     */
    public function get_departmentstrace($id, $options = [] )
    {
        // Результирующий массив
        $tree = [];
        // Защита от зацикливания
        $stop = 100;
        do {
            $stop--;
            // Получение подразделения
            $department = $this->get($id);
            if ( empty($department) )
            {// Остановка поиска
                $stop = 0;
            } else
            {// Подразделение найдено
                $tree[] = $department;
                if ( ! empty($department->leaddepid) )
                {// Указан родитель
                    $id = $department->leaddepid;
                } else
                {// Родитель не указан
                    $stop = 0;
                }
            }
        } while ( $stop > 0 );
    
        if ( empty($options['inverse']) )
        {
            // Инвертирование массива
            $tree = array_reverse($tree, false);
        }
    
        return $tree;
    }
    
    /** Выводит список всех структурных подразделений 
     * кроме статуса DELETED
     * @return array - список подразделений
     * @access public
     */
    public function get_list_no_deleted($sort = null)
    {// получим список всех подразделений
        $select = "(status <> 'deleted' OR status IS NULL)";
        $list = $this->get_records_select($select, null, $sort);
        // персона, которая вошла в деканат
        if ( $this->dof->is_access('manage') )
        {// для манагера выводится весь список
            return $list;
        }
        if ( $right = $this->get_right_dep() )
        {// есть персона - ищем на неё права
            foreach ( $list as $id => $obj )
            {
                if ( !in_array('view', $right[$obj->id]) AND ! in_array('view/mydep', $right[$obj->id]) )
                {// нет прав - удалим
                    unset($list[$id]);
                }
            }
            return $list;
        }
        return array();
    }

    /** Выводит список всех дочерних структурных подразделений указанного подразделения
     * @param int $id - id подразделения, к которому хотим получить список подчиненных
     * @param int $depth - глубина, для которой выводим список подразделений и их дочек
     * @param string $path - путь, для 
     * @param $select
     * @param $space
     * @param bool $code - выводит только код(без названия-используется для блока слева подразделении)
     * @param bool $accesscheck - проверять ли права при составлении списка подразделений?
     *                              true - проверять
     *                              false - не проверять
     * @return array - список дочерних подразделений
     */
    public function departments_list_subordinated($id = null, $depth = '0', $path = null, 
                $select = false, $space = '', $code = false, $accesscheck = true)
    {
        // получим права
        if ( $accesscheck AND ! $right = $this->get_right_dep() AND ! $this->dof->is_access('manage') )
        {// прав нет - даже пыжится не стоит
            return array();
        }
        // получим список всех дочерних подразделений
        if ( !is_null($id) )
        {// передан id - переписываем путь и глубину по нему
            $path = $this->get_field($id, 'path');
            // для поиска дочерних глубину родителя увеличим на + 1
            $depth = $this->get_field($id, 'depth') + 1;
        }
        // формируем sql-запрос
        $sql = "(status <> 'deleted' OR status IS NULL) ";
        if ( !is_null($depth) )
        {// указана глубина - добавим ее к поиску
            $sql .= " AND depth =" . $depth;
        }
        if ( !is_null($path) )
        {// указан путь - добавим его к поиску
            if ( is_null($depth) )
            {// глубины нет - ищем жесткий путь
                $sql .= " AND path ='" . $path . "'";
            } else
            {// если нет - ищем родителя с дочками
                $sql .= " AND (path ='" . $path . "' OR path LIKE '" . $path . "/%')";
            }
        }

        $departs = array();
        // Запросим данные из кеша
        static $cache = null;
        $list = false;
        if( is_null($cache) )
        {
            $cache = $this->dof->get_cache('storage', 'departments', 'subordinateddeps');
        }
        $key = md5($sql);
        if( $cache !== false )
        {
            $list = $cache->get($key);
        }
        if( $list === false )
        {// Если кеш не заполнен - лезем в базу
            $list = $this->get_records_select($sql, null, 'code ASC');
            // И заполняем кеш
            if( $cache !== false )
            {
                $cache->set($key, $list);
            }
        }
        if ( !empty($list) )
        {// если не пуст
            //asort($list);
            foreach ( $list as $data )
            {// сформируем из них массив
                if ( $select )
                {// для select-списков - одномерный';
                    if ( !$accesscheck OR
                            $this->dof->is_access('manage') OR
                            in_array('view/mydep', $right[$data->id]) OR
                            in_array('view', $right[$data->id]) )
                    {// есть право (или проверка прав отключена) - добавляем в список ';
                        if ( !$code )
                        {
                            $departs[$data->id] = $space . $data->name . ' [' . $data->code . ']';
                        } else
                        {
                            $departs[$data->id] = $space . $data->code;
                        }
                    }
                    $departs += $this->departments_list_subordinated(null, $depth + 1 , $data->path, $select, 
                                '&nbsp;&nbsp;'.$space, $code, $accesscheck);

                } else
                {// структуированный массив';
                    // TODO на этот массви нет проверки прав. сделать в будущем    
                    // есть право - добавляем в список 
                    $data->departments = $this->departments_list_subordinated($data->id, null, null, $select, $space, $code, $accesscheck);
                    $departs[$data->id] = $data;
                }
            }
            // вывод нуля - для отображения блока слева подразделений
            if ( $code AND ( ($this->dof->is_access('manage') OR ($code AND isset($right[0])) AND 
                 ( in_array('view/mydep', $right[$data->id]) OR in_array('view', $right[$data->id])) ) OR ! $accesscheck) )
            {
                if ( isset($right[0]) )
                {
                    $departs[0] = $right[0];
                } elseif ( $this->dof->is_access('manage') )
                {
                    $departs[0] = 0;
                }
            }
            return $departs;
        } else
        {// если пуст - вернем пустой массив
            return array();
        }
    }

    /** Возвращает количество периодов
     * 
     * @param string $select - запрос, для подсчета кол подразделений 
     * @return int количество найденных записей
     */
    public function get_numberof_departments($select)
    {
        //формируем запрос
        return $this->count_records_select($select);
    }

    /** Возвращает объект департамента по умолчанию
     * @return object
     */
    public function get_default()
    {
        return $this->get($this->get_default_id());
    }

    /** Возвращает id департамента по умолчанию
     * @return int
     */
    public function get_default_id()
    {
        return key($this->get_records_select('depth = 0 AND (status<> \'deleted\' OR status IS NULL)', array(), '', 'id', 0, 1));
    }

    /** Проверяет уникальность кодового названия
     * @param string $code
     * @return bool true если запись не уникальна
     */
    public function is_code_notunique($code)
    {
        return $this->is_exists(array('code' => $code));
    }

    /** Возвращает путь подразделения
     * @param int $id - id подразделения, к которому находим путь
     * @return int - глубина подразделения
     * @access public
     */
    public function get_path_for_department($id, $chpath = '')
    {
        // (используется для рекурсии, не указывать во избежании неправильного пути)
        // составим конец пути
        $chpath = $id . $chpath;
        // получим родительское подразделение
        $leaddepid = $this->get_field($id, 'leaddepid');
        if ( $leaddepid == 0 )
        {// это родитель - вернем путь
            return $chpath;
        }
        return $this->get_path_for_department($leaddepid, '/' . $chpath);
    }

    /** Возвращает глубину вложенности подразделения
     * @param int $id - id подразделения, к которому находим глубину
     * @return int - глубина подразделения
     * @access public
     */
    public function get_depth_for_department($id, $depth = 0)
    {
        // (используется для рекурсии, не указывать во избежании неправильного подсчета)
        // получим родительское подразделение
        $leaddepid = $this->get_field($id, 'leaddepid');
        if ( $leaddepid == 0 )
        {// это родитель - вернем путь
            return $depth;
        }
        // продолжаем искать родителя
        return $this->get_depth_for_department($leaddepid, $depth + 1);
    }

    /** Возвращает "путь" через запятые
     * @param int $id - id подразделения, которого возвращаем
     * @return string $path - путь подразделения через запятую
     * @access public
     */
    public function change_path_department($id)
    {
        if ( $path = $this->get_field($id, 'path') )
        {// заменяем '/' на ','
            return str_replace('/', ',', $path);
        } else
        {//  неудача
            return '';
        }
    }

    /** Меняет родительское подразделение всем подразделениям
     * с указанным родительским подразделением
     * 
     * @param int $oldid - id старого подразделения
     * @param int $newid - id нового подразделения
     * @return bool - true, если всё правильно, false, если возникли ошибки
     */
    public function change_subdepartment($oldid, $newid)
    {
        if ( !$list = $this->get_records(array('leaddepid' => $oldid)) )
        {
            return false;
        }
        $flag = true;
        foreach ( $list as $record )
        {
            $obj = new stdClass();
            $obj->id = $record->id;
            $obj->leaddepid = $newid;
            // Обновляем объект, посылая при этом событие, чтобы обновить глубину и путь
            $flag = ( $flag AND $this->update($obj) );
        }
        return $flag;
    }

    /** Обновляет путь и глубину указанного подразделения
     * и всех, кто ниже
     * 
     * @param int $path - путь по которому обновляем
     * @return bool - true, если всё правильно, false, если возникли ошибки
     */
    public function update_depth_path($path)
    {
        $num = 0;
        $flag = true;
        while ( $list = $this->get_records_select(" path LIKE '" . $path . "/%'
                  AND ( status <> 'deleted' OR status IS NULL)", null, '', '*', $num, 100) )
        {// Учитывая возможности сервака, будем брать записи из справочника по частям
            foreach ( $list as $record )
            {// запустим обновление самих себя
                $flag = ( $flag AND $this->update($record) );
            }
            $num += 100;
        }
        return $flag;
    }

    /** Возвращает список подразделений, в которых ползователю
     *  можно работать
     * @param int $personid - id персоны, по умочанию null
     * @param bool $returnemptydeps - флаг необходимости вернуть пустой массив прав подразделения, если прав нет
     * @return array - массив, ключ которого - id подразделения
     *  					   значения(array) - список прав(view, edit)
     */
    public function get_right_dep($personid = null, $returnemptydeps = true)
    {
        // не передали персону - определим её
        if ( !$personid )
        {// не передали персону
            $personid = $this->dof->storage('persons')->get_bu();
            if ( $personid )
            {
                $personid = $personid->id;
            } else
            {
                return false;
            }
        }

        $acl = $this->dof->storage('acl')->prefix() .
                $this->dof->storage('acl')->tablename();
        $aclwarrantagents = $this->dof->storage('aclwarrantagents')->prefix() .
                $this->dof->storage('aclwarrantagents')->tablename();
        // запрос для извлечения прав
        $aclobj = $this->dof->storage('acl')->get_right_person($personid);
        // запрос подразделений
        $sql = "(status <> 'deleted' OR status IS NULL) ORDER BY depth";
        $depobj = $this->get_records_select($sql);

        $a = array();
        $path = array();
        // перебираем полученные данные
        if ( $aclobj )
        {// есть НЕКИЕ права - перебираем
            // создадим структуру наших прав
            foreach ( $depobj as $dep )
            {
                $a[$dep->id] = array();
                // массив с ПУТЁМ
                $path[$dep->id] = explode("/", $dep->path);
                // устанавливаем указатель в конец
                end($path[$dep->id]);
                $mas = $path[$dep->id];
                // удаляем последний елемент
                unset($mas[key($path[$dep->id])]);
                $path[$dep->id] = $mas;
            }
            // запишем первоначальные права
            foreach ( $aclobj as $acl )
            {
                if ( $acl->departmentid == 0 )
                {// для всех подразделений
                    foreach ( $a as $depid => $value )
                    {// допишем значение
                        $value[$acl->code] = $acl->code;
                        $a[$depid] = $value;
                    }
                    $value[$acl->code] = $acl->code;
                    $a[0] = $value;
                } else
                {// допишем значение
                    $b = $a[$acl->departmentid];
                    $b[$acl->code] = $acl->code;
                    $a[$acl->departmentid] = $b;
                }
            }
            // далее перебираем массив наш
            // в который уже есть первоначальная вставка прав

            foreach ( $a as $depid => $value )
            {
                if ( $depid == 0 )
                {
                    continue;
                }
                foreach ( $path[$depid] as $element )
                {// берем путь и объединяем значения массивов
                    if( isset($a[$element]) )
                    {// Если данный элемент еще не удален из массива
                        $a[$depid] = array_merge($a[$depid], $a[$element]);
                    }
                }
                if( empty($a[$depid]) && ! $returnemptydeps )
                {// Если собранный массив прав по подразделению пустой и передан флаг, запрещающий возврат пустых элемент конечного массива прав, - удалим его
                    unset($a[$depid]);
                }
            }
            return $a;
        } else
        {// нет прав - cкажем об этом
            return false;
        }
    }
    
    /**
     * Получить временную зону подразделения
     * 
     * @param int $departmentid
     * @return float - временная зона подразделения. 99 - временная зона сервера
     */
    public function get_timezone($departmentid)
    {
        if ( $departmentid === 0 )
        {// Выбраны все подразделения, возвращаем часовой пояс сервера
            return (float) 99;
        }
        
        // Получим подразделение по $departmentid
        $department = $this->get($departmentid);
        
        if ( empty($department) )
        {// Ошибка при получении подразделения
            return (float) 99;
        }
        return $department->zone;
    }
    

    /**
     * Сохранить подразделение в системе
     *
     * @param string|stdClass|array $departmentdata - Данные подразделения(название или комплексные данные)
     * @param array $options - Массив дополнительных параметров
     *
     * @return bool|int - false в случае ошибки или ID подразделения в случае успеха
     *
     * @throws dof_exception_dml - В случае ошибки
     */
    public function save($departmentdata = null, $options = [])
    {
        // Нормализация данных
        try {
            $normalized_data = $this->normalize($departmentdata, $options);
        } catch ( dof_exception_dml $e )
        {
            throw new dof_exception_dml('error_save_'.$e->errorcode);
        }
        
        // Сохранение данных
        if ( isset($normalized_data->id) && $this->is_exists($normalized_data->id) )
        {// Обновление записи
            $department = $this->update($normalized_data);
            if ( empty($department) )
            {// Обновление не удалось
                throw new dof_exception_dml('error_save_department');
            } else
            {// Обновление удалось
                $this->dof->send_event('storage', 'departments', 'item_saved', (int)$normalized_data->id);
                if( ! empty($options['activate']) )
                {// Требуется активация объекта
                    $this->dof->send_event('storage', 'departments', 'activate_request', (int)$normalized_data->id);
                }
                return $normalized_data->id;
            }
        } else
        {// Создание записи
            $departmentid = $this->insert($normalized_data);
            if ( ! $departmentid )
            {// Добавление не удалось
                throw new dof_exception_dml('error_save_department');
            } else
            {// Добавление удалось
                $this->dof->send_event('storage', 'departments', 'item_saved', (int)$departmentid);
                if( ! empty($options['activate']) )
                {// Требуется активация объекта
                    $this->dof->send_event('storage', 'departments', 'activate_request', (int)$departmentid);
                }
                
                return $departmentid;
            }
        }
        return false;
    }
    
    /**
     * Нормализация данных подразделения
     *
     * Формирует объект подразделения на основе переданных данных. В случае критической ошибки
     * или же если данных недостаточно, выбрасывает исключение.
     *
     * @param string|stdClass|array $departmentdata - Данные подразделения(название или комплексные данные)
     * @param array $options - Опции работы
     *
     * @return stdClass - Нормализовализованный Объект подразделения
     * @throws dof_exception_dml - Исключение в случае критической ошибки или же недостаточности данных
     */
    public function normalize($departmentdata, $options = [])
    {
        // Нормализация входных данных
        if ( is_object($departmentdata) || is_array($departmentdata) )
        {// Комплексные данные
            $departmentdata = (object)$departmentdata;
        } elseif ( is_string($departmentdata) )
        {// Передан email
            $departmentdata = new stdClass();
            $departmentdata->name = $departmentdata;
        } else
        {// Неопределенные данные
            throw new dof_exception_dml('invalid_data');
        }
    
        // Нормализация идентификатора
        if ( isset($departmentdata->id) && (int)$departmentdata->id < 1)
        {
            unset($departmentdata->id);
        }
        
        // Проверка входных данных
        if ( empty($departmentdata) )
        {// Данные не переданы
            throw new dof_exception_dml('empty_data');
        }
        
        if ( ( ! isset($departmentdata->id) || (int)$departmentdata->id < 1 ) &&
            ( empty($departmentdata->code) )
            )
        {// Невозможно определить подразделение
            throw new dof_exception_dml('department_undefined');
        }
        
        if ( isset($departmentdata->id) )
        {// Проверка на существование
            if ( ! $this->get($departmentdata->id) )
            {// Подразделение не найдено
                throw new dof_exception_dml('department_not_found');
            }
        }
    
        // Создание объекта для сохранения
        $saveobj = clone $departmentdata;
    
        // Обработка входящих данных и построение объекта подразделения
        if ( isset($saveobj->id) && $this->is_exists($saveobj->id) )
        {// Подразделение уже содержится в системе
            // Удаление автоматически генерируемых полей
            unset($saveobj->status);
            unset($saveobj->path);
            unset($saveobj->depth);
        } else
        {// Новое подразделение
    
            // АВТОЗАПОЛНЕНИЕ ПОЛЕЙ
            if ( empty($saveobj->name) )
            {// Установка названия по умолчанию
                $saveobj->name = '';
            }
            if ( empty($saveobj->code) )
            {// Установка кода по умолчанию
                $saveobj->code = '';
            }
            if ( empty($saveobj->description) )
            {// Установка описания по умолчанию
                $saveobj->description = '';
            }
            if ( empty($saveobj->managerid) )
            {// Установка руководителя по умолчанию
                $saveobj->managerid = 0;
            }
            if ( empty($saveobj->leaddepid) )
            {// Установка родительского подразделения по умолчанию
                $saveobj->leaddepid = 0;
            }
            if ( empty($saveobj->addressid) )
            {// Установка адреса по умолчанию
                $saveobj->addressid = null;
            }
            if ( empty($saveobj->zone) )
            {// Установка часового пояса по умолчанию
                $saveobj->zone = 99;
            }
    
            // АВТОМАТИЧЕСКИ ГЕНЕРИРУЕМЫЕ ПОЛЯ
            if ( ! $this->dof->plugin_exists('workflow', 'departments') )
            {// Плагин статусов подразделений не активен, установка статуса по умолчанию
                $saveobj->status = 'active';
            } else
            {// Статус назначается в плагине статусов
                unset($saveobj->status);
            }
        }
    
        // НОРМАЛИЗАЦИЯ ПОЛЕЙ
        // Нормализация названия
        if ( isset($saveobj->name) )
        {
            $saveobj->name = (string)$saveobj->name;
        }
        // Нормализация кода
        if ( isset($saveobj->code) )
        {
            $saveobj->code = (string)$saveobj->code;
        }
        // Нормализация описания
        if ( isset($saveobj->description) )
        {
            $saveobj->description = (string)$saveobj->description;
        }

        // ВАЛИДАЦИЯ ДАННЫХ
        if ( isset($saveobj->addressid) )
        {
            if ( ! $this->dof->storage('addresses')->is_exists((int)$saveobj->addressid) )
            {
                $saveobj->addressid = null;
            }
        }
        if ( isset($saveobj->managerid) )
        {
            if ( ! $this->dof->storage('persons')->is_exists((int)$saveobj->managerid) )
            {
                $saveobj->managerid = 0;
            }
        }
        if ( isset($saveobj->leaddepid) )
        {
            if ( ! $this->is_exists((int)$saveobj->leaddepid) )
            {
                $saveobj->leaddepid = 0;
            }

            if ( isset($saveobj->id) )
            {// Проверка обновления родительского подразделения
                if ( $saveobj->id == $saveobj->leaddepid )
                {
                    throw new dof_exception_dml('leaddepartment_equal');
                }
                // @todo разрешить проблему переноса в нижестоящее подразделение
                $subdepartments = $this->get_departments($saveobj->id);
                if ( array_key_exists((int)$saveobj->leaddepid, $subdepartments) )
                {
                    throw new dof_exception_dml('leaddepartment_children');
                }
            }
        }
        
    
        return $saveobj;
    }
    
    /**
     * Вернуть массив с настройками или одну переменную
     * 
     * @param $key - переменная
     * @return mixed
     */
    public function get_cfg($key=null)
    {
        // Возвращает параметры конфигурации
        include_once ($this->dof->plugin_path($this->type(),$this->code(),'/cfg/departments.php'));
        if (empty($key))
        {
            return $storage_departments;
        }else
        {
            if( !empty($storage_departments[$key]) )
            {
                return $storage_departments[$key];
            } else
            {
                return false;
            }
        }
    }
    /**
     * Получить подразделение пользователя по умолчанию.
	 * Будет возвращен идентификатор первого найденного подразделения, в котором у пользователя есть права.
	 * Используется для корректного перенаправления пользователя в деканат или для проверки прав деканата в интерфейсах мудл.
     * 
     * @return int идентификатор подразделения
     */
    public function get_user_default_department()
    {
        $depid = optional_param('departmentid', null, PARAM_INT);
        if ( ! isset($depid) )
        {
            // По умолчанию пользователь будет переопределен в 0 подразделение
            $depid = 0;
            
            if ( $this->dof->plugin_exists('storage', 'persons')
                AND $right = $this->get_right_dep() )
            {
                foreach ( $right as $id => $value)
                {// определим , куда его сразу впустить
                    if ( in_array('view', $value) OR in_array('view/mydep', $value))
                    {
                        $depid = $id;
                        // нашли первое - дальне нет смысла продолжать
                        break;
                    }
                }
            }
            if ( empty($depid) && $person = $this->dof->storage('persons')->get_bu() )
            {
                // У пользователя отсутствуют права,
                // Попытаемся получить персону в деканате и перенаправить в зарегистрированное подразделение
                if ( ! empty($person->departmentid) )
                {
                    $depid = $person->departmentid;
                }
            }
        }
        return $depid;
    }
    
}
?>