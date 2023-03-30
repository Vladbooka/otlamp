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
 * Класс для работы с категориями Moodle (через modlib/ama)
 *
 * @package    sync
 * @subpackage mcategories
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_sync_mcategories implements dof_sync
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
        return 2018050900;
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
        return 'mcategories';
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
            'modlib' => [
                'ama' => 2009101500
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
        return [
            ['plugintype' => 'storage',  'plugincode' => 'departments', 'eventcode' => 'insert'],
            ['plugintype' => 'storage',  'plugincode' => 'departments', 'eventcode' => 'update']
        ];
    }
    /** 
     * Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
        return true;
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
        $config = [];
        
        // параллельно создаем два дерева категорий, если вдруг понадобится 
        // распределять курсы Moodle по разным категориям (опционально0
        
        // включение синхронизации первого дерева категорий
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'syncenable1';
        $obj->value = 0;
        $config[$obj->code] = $obj;
        
        // категория курсов в Moodle первого дерева категорий
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'mdlcategoryid1';
        $obj->value = '0';
        $config[$obj->code] = $obj;
        
        // включение синхронизации второго дерева категорий
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'syncenable2';
        $obj->value = 0;
        $config[$obj->code] = $obj;
        
        // категория курсов в Moodle второго дерева категорий
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'mdlcategoryid2';
        $obj->value = '0';
        $config[$obj->code] = $obj;
        
        return $config;
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
        
        // видеть электронные учебные материалы подразделения
        $a['view_category_content'] = ['roles' => []];
        
        // редактировать электронные учебные материалы подразделения
        $a['edit_category_content'] = ['roles' => []];
        
        // управлять электронными учебными материалами подразделения
        $a['manage_category_content'] = ['roles' => []];
        
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
            case 'storage__departments__insert':
                // обработка только что созданного объекта подразделения
                $this->process_department($mixedvar['new'], [], true);
                break;
                
            case 'storage__departments__update':
                // обработка только что обновленного объекта подразделения
                $this->process_department($mixedvar['new']);
                break;
                
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
        if( $loan == 3 )
        {
            $this->sync_role_assigns();
        }
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
        if ( $this->dof->plugin_exists('storage', 'departments') && ($code === 'update_department_mdlcategory_tree') && ! empty($intvar) )
        {
            // синхронизация дерева подразделения с категориями
            // перенос категории в нужные подкатегории
            $department = $this->dof->storage('departments')->get_record(['id' => intval($intvar)]);
            if ( ! empty($department) )
            {
                // получение дочерних подразделений
                $deps = $this->get_department_subdeps($department->id);
                
                // количество конфигов
                foreach ( [1,2] as $number )
                {
                    // получение конфига категории у подразделения, если не
                    $configrecordcat = (array)$this->dof->storage('config')->get_record(
                            [
                                'departmentid' => $department->id,
                                'code' => "mdlcategoryid{$number}",
                                'plugintype' => 'sync',
                                'plugincode' => 'mcategories'
                            ]);
                    if ( ! empty($configrecordcat['value']) )
                    {
                        // конфиг найден, категория указана
                        // проверка флага включения синхронизации
                        $syncenabled = (int)$this->dof->storage('config')->get_config_value("syncenable{$number}", 'sync', 'mcategories', $department->id);
                        if ( ! empty($syncenabled) )
                        {
                            foreach ( $deps as $dep )
                            {
                                $this->process_department($dep, [$number]);
                            }
                        }
                    }
                }
            }
        }
        
        return true;
    }
    
    // **********************************************
    // Собственные методы
    // **********************************************
    
    /**
     * Поиск родительскогого идентификатора категории Moodle со включенным флагом синхронизации (syncenable == 1)
     *
     * @param int $departmentid
     * @param int $number
     *
     * @return string | bool
     */
    protected function get_syncenabled_mdlparentcategory($departmentid, $number)
    {
        // получение конфига категории у подразделения, если не найдено, ищем в родительски
        // при этом подразделение 0 пропускаем
        $configrecordcat = (array)$this->dof->storage('config')->get_record(
                [
                    'departmentid' => $departmentid,
                    'code' => "mdlcategoryid{$number}",
                    'plugintype' => 'sync',
                    'plugincode' => 'mcategories'
                ]);
        if ( ! empty($configrecordcat['departmentid']) && ! empty($configrecordcat['value']) )
        {
            // проверка флага включения синхронизации
            // получение конфига флага синхронизации
            $syncenabled = (bool)$this->dof->storage('config')->get_config_value("syncenable{$number}", 'sync', 'mcategories', $departmentid);
            if ( ! empty($syncenabled) )
            {
                return $configrecordcat['value'];
            }
        }
        
        $department = $this->dof->storage('departments')->get_record(['id' => $departmentid]);
        if ( ! empty($department->leaddepid) )
        {
            return $this->get_syncenabled_mdlparentcategory($department->leaddepid, $number);
        } else
        {
            return false;
        }
    }
    
    /**
     * Перенос категории
     *
     * @param int $mdlcategoryid
     * @param int $parentcategory
     *
     * @return int | bool
     */
    protected function move_category($mdlcategoryid, $parentcategory)
    {
        // проверка существования категорий
        if ( (! $this->category_exists($mdlcategoryid)) ||
                (! $this->category_exists($parentcategory)) )
        {
            return false;
        }
        
        return $this->dof->modlib('ama')->category($mdlcategoryid)->move($parentcategory);
    }
    
    /**
     * Получение массива дочерних поразделений
     *
     * @param int $parentdepid
     *
     * @return array
     */
    protected function get_department_subdeps($parentdepid)
    {
        return $this->dof->storage('departments')->get_records(['leaddepid' => $parentdepid, 'status' => array_keys($this->dof->workflow('departments')->get_meta_list('real'))]);;
    }
    
    /**
     * Получение массив подразделений по убыванию уровня иерархии
     * 
     * @param int $parentdepid
     * @param array $options
     * 
     * @return array
     */
    protected function get_department_hierarchy($parentdepid, $options = [])
    {
        $alldeps = [];
        if ( ! isset($options['statuses']) )
        {
            $options['statuses'] = array_keys($this->dof->workflow('departments')->get_meta_list('real'));
        }
        
        // подразделения текущего уровня
        $departments = $this->dof->storage('departments')->get_records(['leaddepid' => $parentdepid, 'status' => $options['statuses']]);
        if ( ! empty($departments) )
        {
            $alldeps += $departments;
            foreach ( $departments as $dep )
            {
                $alldeps += $this->get_department_hierarchy($dep->id, $options);
            }
        }
        
        return $alldeps;
    }
    
    /**
     * Синхронизация подразделения с категорией в Moodle
     *
     * @param stdClass $department
     *
     * @return bool
     */
    protected function process_department(stdClass $department, $customcounter = [], $insert = false)
    {
        // количество конфигов
        if ( ! empty($customcounter) )
        {
            $configcount = $customcounter;
        } else 
        {
            $configcount = [1,2];
        }
        
        // из-за событийной модели, иногда прилетает подразделение с устаревшими данными, обновим запись из БД
        $department = $this->dof->storage('departments')->get_record(['id' => $department->id]);
        
        // проверка существования вышестоящего подразделения
        if ( ! empty($department->leaddepid) && ! empty($department->path) && ! empty($department->status) )
        {
            foreach ( $configcount as $number )
            {
                // поиск родительской категории по родительскому подразделению, где включен режим синхронизации
                $mdlparentcategory = $this->get_syncenabled_mdlparentcategory($department->leaddepid, $number);
                if ( ! empty($mdlparentcategory) )
                {
                    // проверка, что у текущего подразделения отсутствует
                    // категория в мудл
                    $configrecord = $this->dof->storage('config')->get_record([
                        'departmentid' => $department->id,
                        'code' => "mdlcategoryid{$number}",
                        'plugintype' => 'sync',
                        'plugincode' => 'mcategories'
                    ]);
                    // получение конфига флага синхронизации
                    $syncenabled = (int)$this->dof->storage('config')->get_config_value("syncenable{$number}", 'sync', 'mcategories', $department->id);
                    
                    if ( $syncenabled || $insert )
                    {
                        // если обрабатываем insert записи, то у него по умолчанию отнаследуется конфиг, который может быть и выключен в родителе
                        // но включен в родителе родителя, поэтому при insert не проверяем этот флаг у текущего подразделения
                        // синхронизация включена
                        if ( empty($configrecord) || empty($configrecord->value) )
                        {
                            // получение настройки автоматической привящки по названию
                            try {
                                $autolink = $this->get_cfg('autolink_depcat_by_name');
                            } catch( dof_exception $ex)
                            {// по умолчанию отключена
                                $autolink = false;
                            }
                            if( ! empty($autolink) )
                            {// включена автопривязка по названию категории
                                // получение настройки области поиска категории 
                                try {
                                    $autolinkanyparent = $this->get_cfg('autolink_depcat_any_parent');
                                } catch( dof_exception $ex)
                                {// по умолчанию только внутри указанной родительской категории
                                    $autolinkanyparent = false;
                                }
                                if( ! empty($autolinkanyparent) )
                                {// разрешено искать категорию по названию где угодно
                                    $parent = null;
                                } else 
                                {// разрешено искать категорию по названию внутри определенной категории
                                    $parent = $mdlparentcategory;
                                }
                                    
                                $categories = $this->dof->modlib('ama')->category(false)->search_by_name(
                                    $department->name, 
                                    $parent
                                );
                                if( ! empty($categories) )
                                {
                                    // получение настройки поведения при нахождении нескольких категорий удовлетворяющих условиям
                                    try {
                                        $autolinkdouble = $this->get_cfg('autolink_depcat_double');
                                    } catch( dof_exception $ex)
                                    {// по умолчанию не привязываем
                                        $autolinkdouble = 0;
                                    }
                                    if( count($categories) == 1 || (int)$autolinkdouble == 2 )
                                    {// есть одна единственная подходящая категория или требуется привязать к любой из найденных
                                        $linkcategory = array_shift($categories);
                                        
                                        $categorylinksnum = $this->count_category_links($linkcategory->id, $number);
                                        if( empty($categorylinksnum) )
                                        {// категория свободна, ни к какому подразделению не привязана
                                            $categoryid = $linkcategory->id;
                                        } 
                                        elseif ((int)$autolinkdouble != 1)
                                        {// привязать не получилось, если не указано, что можно создавать дубли, считаем ошибкой
                                            return false;
                                        }
                                    }
                                    elseif( count($categories) > 1 && (int)$autolinkdouble == 0)
                                    {// запрещено создавать новую категорию, если найдено несколько с таким названием
                                        return false;
                                    }
                                }
                            }
                            
                            if( ! isset($categoryid) )
                            {// категория не была обнаружена ранее
                                // создание категории для подразделения
                                $categoryobj = new stdClass();
                                $categoryobj->name = $department->name;
                                $categoryobj->parent = $mdlparentcategory;
                                
                                // создание категории
                                $categoryid = $this->create($categoryobj);
                            }
                            
                            if ( ! empty($categoryid) )
                            {
                                // категория успешно создана, записываем в конфиг
                                if ( ! empty($configrecord->id) )
                                {
                                    // обновляем конфиг
                                    $configrecord->value = $categoryid;
                                    $this->dof->storage('config')->update($configrecord);
                                } else
                                {
                                    // конфиг отсутствует, добавим новый
                                    $config = new stdClass();
                                    $config->departmentid = $department->id;
                                    $config->code = "mdlcategoryid{$number}";
                                    $config->plugintype = 'sync';
                                    $config->plugincode = 'mcategories';
                                    $config->type = 'text';
                                    $config->value = $categoryid;
                                    $this->dof->storage('config')->insert($config);
                                }
                                
                                // изменения произведены - получим заново запись конфига
                                $configrecord = $this->dof->storage('config')->get_record([
                                        'departmentid' => $department->id,
                                        'code' => "mdlcategoryid{$number}",
                                        'plugintype' => 'sync',
                                        'plugincode' => 'mcategories'
                                ]);
                            }
                        }
                        
                        
                        
                        if ( ! empty($configrecord) && ! empty($configrecord->value) )
                        {
                            // категория указана, проверим, что она лежит в нужной родительской категории
                            // получение записи категории текущего подразделения
                            $mdlcatrecord = $this->get_category($configrecord->value);
                                
                            $countdepratments = $this->count_category_links($configrecord->value, $number);
                            
                            if ( ! empty($mdlcatrecord) && 
                                    ! empty($mdlparentcategory) && 
                                    ($countdepratments == 1) &&
                                    ((int)$mdlparentcategory !== (int)$mdlcatrecord->parent) )
                            {
                                // категория лежит не в правильной родительской категории, переместим, если это возможно
                                $this->move_category($mdlcatrecord->id, $mdlparentcategory);
                            }
                        }
                    }
                }
                
                // обработка дочерних подразделений
                $subdeps = $this->get_department_hierarchy($department->id);
                if ( ! empty($subdeps) )
                {
                    foreach ( $subdeps as $dep )
                    {
                        $this->process_department($dep, [$number]);
                    }
                }
            }
        
        }
        
        return true;
    }
    
    protected function count_category_links($categoryid, $confignumber)
    {
        // количество подразделений, которым принадлежит категория Moodle
        // проверка на стороне php, тк в БД это поле типа text, mysql не поддерживает сравнение таких полей
        $countdepratments = 0;
        
        // проверим, что категория связана только с одним подразделение
        $configrecords = $this->dof->storage('config')->get_records([
            'code' => "mdlcategoryid{$confignumber}",
            'plugintype' => 'sync',
            'plugincode' => 'mcategories',
        ]);
        
        foreach ( $configrecords as $record )
        {
            if ( $record->value == $categoryid )
            {
                $countdepratments++;
            }
        }
        
        return $countdepratments;
    }
    
    /** 
     * Проверка на существование категории Moodle
     *
     * @param int $categoryid - id категории в moodle
     * 
     * @return bool
     */
    public function category_exists($id)
    {
        return $this->dof->modlib('ama')->category(false)->is_exists($id);
    }
    
    /**
     * Получение ссылки на категорию
     *
     * @param int $id - id категории в moodle
     *
     * @return object|false - объект курса или false
     */
    public function get_category_link($id)
    {
        if ( ! $this->category_exists($id) )
        {
            return false;
        }
        
        return $this->dof->modlib('ama')->category($id)->get_link();
    }
    
    /** 
     * Получение объект записи категории в БД
     *
     * @param int $id - id категории в moodle
     *
     * @return object|false - объект курса или false
     */
    public function get_category($id)
    {
        if ( ! $this->category_exists($id) )
        {
            return false;
        }
        
        return $this->dof->modlib('ama')->category($id)->get();
    }
    
    /**
     * Получение курсов категории
     * 
     * @param int $categoryid
     * @param array $options
     *
     * @return stdClass[]
     */
    public function get_courses($id, $options = [])
    {
        if ( ! $this->category_exists($id) )
        {
            return false;
        }
        
        return $this->dof->modlib('ama')->category($id)->get_courses($options);
    }
    
    /**
     * Получение количества курсов в категории
     *
     * @param int $categoryid
     * @param array $options
     *
     * @return int
     */
    public function get_courses_count($id, $options = [])
    {
        if ( ! $this->category_exists($id) )
        {
            return false;
        }
        
        return $this->dof->modlib('ama')->category($id)->get_courses_count($options);
    }
    
    /**
     * Создание категории мудл
     *
     * @param stdClass $categoryobj
     *
     * @return int
     */
    public function create(stdClass $categoryobj)
    {
        return $this->dof->modlib('ama')->category(false)->create($categoryobj);
    }
    
    /**
     * Обновление категории мудл
     *
     * @param stdClass $categoryobj
     *
     * @return int
     */
    public function update(stdClass $categoryobj)
    {
        if ( ! $this->category_exists($categoryobj->id) )
        {
            return false;
        }
        
        return $this->dof->modlib('ama')->category($categoryobj->id)->update($categoryobj);
    }
    
    /**
     * Удаление категории мудл
     *
     * @param stdClass $categoryobj
     *
     * @return bool
     */
    public function delete(stdClass $categoryobj)
    {
        if ( ! $this->category_exists($categoryobj->id) )
        {
            return false;
        }
        
        return $this->dof->modlib('ama')->delete($categoryobj->id)->delete();
    }
    
    
    /**
     * Получение настройки плагина
     *
     * @param $key - переменная
     * @return mixed
     */
    protected function get_cfg($key=null)
    {
        if( empty($this->cfg) )
        {
            $cfgpath = $this->dof->plugin_path($this->type(), $this->code(), '/cfg/sync_mcategories.php');
            if( ! file_exists($cfgpath) )
            {
                throw new dof_exception('no_such_cfg_file');
            }
            
            // Файл, содержащий массив с параметрами конфигурации
            include_once($cfgpath);
            $this->cfg = $sync_mcategories_cfg;
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
    
    /**
     * Получение кодов полномочий, принимающихся во внимание при синхронизации ролей
     * 
     * @return string[] - массив кодов полномочий
     */
    protected function get_syncable_acl_codes()
    {
        return [
            'view_category_content',
            'edit_category_content',
            'manage_category_content'
        ];
    }
    
    /**
     * Получение идентификатора роли по коду полномочия
     * 
     * @param string $aclcode - код полномочия
     * @return int|bool - идентификатор роли или false в случае ошибки
     */
    protected function get_syncable_acl_roleid($aclcode)
    {
        // идентификатор роли
        $roleid = false;
        
        if( in_array($aclcode, $this->get_syncable_acl_codes()) )
        {// полномочие относится к обрабатываемым
            /////////////////// получение роли, соответствующей полномочию
            $roleid = $this->dof->get_config();
        }
        
        return $roleid;
    }
    
    /**
     * Проверка, синхронизированно ли подразделение с категорией курсов Moodle
     * 
     * @param int $departmentid - идентификатор подразделения
     * @param int $syncconfignumber - номер структуры синхронизации подразделений из конфигурации
     * 
     * @return boolean
     */
    protected function is_synced_department($departmentid, $syncconfignumber)
    {
        // получение конфига категории у подразделения, если не
        $configrecordcat = (array)$this->dof->storage('config')->get_record(
            [
                'departmentid' => $departmentid,
                'code' => "mdlcategoryid{$syncconfignumber}",
                'plugintype' => 'sync',
                'plugincode' => 'mcategories'
            ]
        );
        if ( ! empty($configrecordcat['value']) )
        {// конфиг найден, категория указана
            
            // проверка флага включения синхронизации
            $syncenabled = (int)$this->dof->storage('config')->get_config_value("syncenable{$syncconfignumber}", 'sync', 'mcategories', $departmentid);
            if ( ! empty($syncenabled) )
            {// флаг синхронизации активен
                return $this->category_exists($configrecordcat['value']);
            }
        }
        return false;
    }
    
    /**
     * Получение объекта категории, синхронизированной с подразделением
     * 
     * @param int $departmentid - идентификтаор категории
     * @param int $syncconfignumber - номер структуры синхронизации подразделений из конфигурации
     * 
     * @return object|bool - объект категории или false в случае ошибки
     */
    protected function get_synced_category($departmentid, $syncconfignumber)
    {
        // получение конфига категории у подразделения, если не
        $configrecordcat = (array)$this->dof->storage('config')->get_record(
            [
                'departmentid' => $departmentid,
                'code' => "mdlcategoryid{$syncconfignumber}",
                'plugintype' => 'sync',
                'plugincode' => 'mcategories'
            ]
        );
        if ( ! empty($configrecordcat['value']) )
        {// конфиг найден, категория указана
            
            // проверка флага включения синхронизации
            $syncenabled = (int)$this->dof->storage('config')->get_config_value("syncenable{$syncconfignumber}", 'sync', 'mcategories', $departmentid);
            if ( ! empty($syncenabled) )
            {// флаг синхронизации активен
                return $this->get_category($configrecordcat['value']);
            }
        }
        return false;
    }
    
    /**
     * Синхронизировать назначение ролей в контексте категорий, синхронизированных с подразделениями
     * 
     * @param number $departmentid - идентификатор подразделения, внутри которого необходимо провести синхронизацию
     */
    protected function sync_role_assigns($departmentid = 0)
    {
        $upids = [];
        $connections = [];
        
        // получение кодов прав, по которым проводится синхронизация ролей
        $syncableaclcodes = $this->get_syncable_acl_codes();
        // получение номера структуры синхронизации подразделений с категориями для назначения ролей
        $syncconfignumber = get_config('block_dof', 'mdlcategoryid_number');
        // массив персон и категорий подразделений
        $persons = [];
        // массив категорий, синхронизированных с подразделениями
        $mdlcategories = [];
        // роли, соответствующие полномочиям
        $roles = [];
        
        foreach($syncableaclcodes as $syncableaclcode)
        {
            if( ! array_key_exists($syncableaclcode, $connections) )
            {// connection для такого полномочия еще не создавали
                // получение объекта подключения к фреймворку синхронизаций
                $connections[$syncableaclcode] = $this->dof->storage('sync')->createConnect(
                    'sync',
                    'mcategories',
                    $syncableaclcode,
                    'modlib',
                    'ama',
                    'coursecat_role_assignment'
                );
            }
            
            if( ! array_key_exists($syncableaclcode, $roles) )
            {// Получение роли по коду права
                $roles[$syncableaclcode] = get_config('block_dof', $syncableaclcode.'_role');
            }
            if( $roles[$syncableaclcode] === false || $roles[$syncableaclcode] == 'choose' )
            {// роль для полномочия не настроена - идем дальше
                continue;
            }
        }
        
        // получение персон, имеющих указанное право
        $acledpersons = $this->dof->storage('acl')->get_persons_acl_by_code(
            $this->type(),
            $this->code(),
            $syncableaclcodes,
            $departmentid
        );
        
        if( ! empty($acledpersons) )
        {// сбор данных по персонам, имеющим полномочие
            
            foreach($acledpersons as $acledperson)
            {
                // формирование дочерних подразделений, на которые также распространяется полномочие
                $leaddep = $this->dof->storage('departments')->get($acledperson->departmentid);
                $departments = [$leaddep] + $this->get_department_hierarchy($acledperson->departmentid);
                
                foreach($departments as $department)
                {
                    // получение категории по подразделению
                    if( ! array_key_exists($department->id, $mdlcategories) )
                    {
                        $mdlcategories[$department->id] = $this->get_synced_category(
                            $department->id, 
                            $syncconfignumber
                        );
                    }
                    if ( $mdlcategories[$department->id] === false )
                    {// подразделение не синхронизировано с категорией - идем дальше
                        continue;
                    }
                    
                    // получение объекта персоны
                    if ( ! array_key_exists($acledperson->id, $persons) )
                    {
                        $persons[$acledperson->id] = $this->dof->storage('persons')->get($acledperson->id);
                    }
                    if( empty($persons[$acledperson->id]) ||
                        empty($persons[$acledperson->id]->mdluser) || 
                        empty($persons[$acledperson->id]->sync2moodle) )
                    {// пользователь не синхронизирован с moodle - идем дальше
                        continue;
                    }
                    
                    
                    // формирование объекта полей, изменение которых влияет на синхронизацию
                    $hashfields = new stdClass();
                    $hashfields->personid = $acledperson->id;
                    $hashfields->mdluser = $persons[$acledperson->id]->mdluser;
                    $hashfields->roleid = $roles[$acledperson->code];
                    $hashfields->departmentid = $department->id;
                    $downhash = $this->dof->storage('sync')->makeHash($hashfields);
                    $downid = $department->id.'|'.$acledperson->id;
                    // получение записи из реестра синхронизации
                    $syncrecord = $connections[$acledperson->code]->getSync(
                        ['downid' => $downid]
                    );
                    
                    // проверка актуальности записи
                    if ( ! empty($syncrecord->downhash) && ($syncrecord->downhash == $downhash) )
                    {// запись не изменилась - переходим к следующей
                        $upids[] = $syncrecord->upid;
                        continue;
                    }
                    
                    
                    /**
                     * @var ama_user $amauser - экземпляр класса ama_user
                     */
                    $amauser = $this->dof->modlib('ama')->user($persons[$acledperson->id]->mdluser);
                    
                    if( ! empty($syncrecord) )
                    {// произошло изменение
                        
                        
                        // удаление устаревшего назначения роли
                        try 
                        {
                            $unassignresult = $amauser->unassign_role($syncrecord->upid);
                        } catch(Exception $ex)
                        {
                            $unassignresult = false;
                        }
                        if( $unassignresult !== false )
                        {
                            // удалось удалить назначение - пометим связь объектов синхроинизации 
                            $connections[$acledperson->code]->updateUp(
                                $syncrecord->downid, 
                                'delete', 
                                $syncrecord->downhash, 
                                $syncrecord->upid
                            );
                        }
                    }
                    
                    // назначение новой роли
                    try
                    {
                        $upid = $amauser->assign_role_to_category(
                            $mdlcategories[$department->id]->id,
                            $roles[$acledperson->code]
                        );
                    } catch(Exception $ex)
                    {
                        $upid = false;
                    }
                    
                    if( $upid !== false )
                    {// назначение роли удалось
                        
                        $connections[$acledperson->code]->updateUp(
                            $downid,
                            'create',
                            $downhash,
                            $upid
                        );
                            
                        
                        if( ! in_array($upid, $upids) )
                        {
                            $upids[] = $upid;
                        }
                    }
                }
            }
        }
        
        
        // удаление назначений и записей синхронизации, которые не были синхронизированы в этом полном цикле
        foreach($connections as $connection)
        {
            $listsync = $connection->listSync();
            
            foreach($listsync as $synced)
            {
                if( ! in_array($synced->upid, $upids))
                {
                    /**
                     * @var ama_user $fakeamauser - экземпляр класса ama_user без привязки к пользователю
                     */
                    $fakeamauser = $this->dof->modlib('ama')->user(false);
                    try {
                        // удаление назначения роли
                        $unassignresult = $fakeamauser->unassign_role($synced->upid);
                    } catch(Exception $ex)
                    {
                        $unassignresult = false;
                    }
                    if( $unassignresult !== false )
                    {// удалось удалить назначение роли
                        $connection->updateUp(
                            $synced->downid, 
                            'delete', 
                            $synced->downhash, 
                            $synced->upid
                        );
                    }
                }
            }
        }
    }
    
}