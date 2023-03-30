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
require_once($DOF->plugin_path('storage','config','/config_default.php'));

/** Справочник учебных программ
 *
 */
class dof_storage_programms extends dof_storage implements dof_storage_config_interface
{
    /**
     * @var dof_control
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    
    public function install()
    {
        if ( ! parent::install() )
        {
            return false;
        }
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    
    /** Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * @access public
     */
    public function upgrade($oldversion)
    {
        global $DB;
        $dbman = $DB->get_manager();
        $table = new xmldb_table($this->tablename());
        $result = true;
        if ($oldversion < 2013040905)
        {// добавим поля billingtext, billingrules
            $field = new xmldb_field('billingtext', XMLDB_TYPE_TEXT, 'big', null, false, null, null, 'ahours');
            if ( !$dbman->field_exists($table, $field) )
            {// поле еще не установлено
                $dbman->add_field($table, $field);
            }
            $field = new xmldb_field('billingrules', XMLDB_TYPE_TEXT, 'big', null, false, null, null, 'billingtext');
            if ( !$dbman->field_exists($table, $field) )
            {// поле еще не установлено
                $dbman->add_field($table, $field);
            }
        }
        if ($oldversion < 2014041700)
        {// добавим поле flowagenums
            $field = new xmldb_field('flowagenums', XMLDB_TYPE_INTEGER, 2, XMLDB_UNSIGNED, false, null, null, 'billingrules');
            if ( !$dbman->field_exists($table, $field) )
            {// поле еще не установлено
                $dbman->add_field($table, $field);
            }
        }
        if ($oldversion < 2014111800)
        {// добавим поле flowagenums
            $field = new xmldb_field('edulevel', XMLDB_TYPE_INTEGER, 2, XMLDB_UNSIGNED, false, null, 7, 'flowagenums');
            if ( !$dbman->field_exists($table, $field) )
            {// поле еще не установлено
                $dbman->add_field($table, $field);
            }
        }
        return $result && $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());// возвращаем результат обновления
     }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
        return 2017030200;
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
        return 'programms';
    }
    /** Возвращает список плагинов,
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('departments' => 2009040800,
                                      'acl'         => 2011041800,
                                      'config'      => 2011080900));
    }
    /** Определить, возможна ли установка плагина в текущий момент
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
    /** Получить список плагинов, которые уже должны быть установлены в системе,
     * и без которых начать установку или обновление невозможно
     *
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion=0)
    {
        return array('storage'=>array('acl'=>2011040504,
                                      'config'=> 2011080900));
    }
    /** Список обрабатываемых плагином событий
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        // Пока событий не обрабатываем
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
        if ( ! $this->is_access($do, $objid, $userid, $depid) )
        {
            $notice = "{$this->code()}/{$do} (block/dof/{$this->type()}/{$this->code()}: {$do})";
            if ($objid){$notice.=" id={$objid}";}
            $this->dof->print_error('nopermissions','',$notice);
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
    public function catch_event($gentype,$gencode,$eventcode,$intvar,$mixedvar)
    {
        // Ничего не делаем, но отчитаемся об "успехе"
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
        return 'block_dof_s_programms';
    }
    
    /** Переопределение функции вставки записи в таблицу - для произведения дополнительных
     * операций с данными до или после вставки
     *
     * @param object $dataobject - объект с данными для вставки
     * @param bool $quiet[optional]- не генерировать событий
     * @return mixed bool false если операция не удалась или id вставленной записи
     */
    public function insert($dataobject, $quiet = false, $bulk = false, $options = [])
    {
        if ( ! $id = parent::insert($dataobject, $quiet) )
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
        $newobj       = new stdClass();
        $newobj->id   = $id;
        $newobj->code = 'id'.$id;
        
        // добавляем код к созданной записи и возвращаем результат
        // @todo проверить результат вставки и записать ошибку в лог если это не удалось
        $this->update($newobj);
        return $id;
    }

    // ***********************************************************
    //       Методы для работы с полномочиями и конфигурацией
    // ***********************************************************
    
    /** Получить список параметров для фунции has_hight()
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
        if ( is_null($depid) )
        {// подразделение не задано - берем текущее
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        $result->objectid  = $objectid;
        if ( ! $objectid )
        {// если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        }else
        {// если указан - то установим подразделение
            $result->departmentid = $this->dof->storage($this->code())->get_field($objectid, 'departmentid');
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
        
        $a['view']   = array('roles'=>array('manager','methodist'));
        $a['edit']   = array('roles'=>array('manager'));
        $a['create'] = array('roles'=>array('manager'));
        $a['delete'] = array('roles'=>array());
        $a['use']    = array('roles'=>array('manager','methodist'));
        
        return $a;
    }
    
    /** Функция получения настроек для плагина
     *
     */
    public function config_default($code=null)
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
     * Реализация механизма импорта данных с поддержкой формирования отчета.
     *
     * Производит попытку поиска объекта на основе переданных данных и если находит -
     * возвращает этот объект.
     * Отдельно обрабатывает случаи, когда объект не был найден или
     * когда было найдено несколько объектов.
     *
     * @param mixed $data - Набор данных, на основе которых производится поиск
     *          В зависимости от типа данные интерпретируются следующим образом:
     *              int - ID программы
     *              string - код программы
     *              array|stdClass - Комплексные данные
     *          При передачи комплексных данных можно также указать данные по
     *          зависимым элементам, что приведет к их обработке.
     *              Пример: $data->person->email = useremail
     * @param array|null $report - Ссылка для формирования отчета по объекту
     *          Стандарт отчета:
     *          'action'         (string) - Действие, которое произведено с объектом (get|save)
     *          'error'          (string) - Ошибка для отображения пользователю
     *          'notice'         (string) - Уведомление для отображения пользователю
     *          'additional' (array|null) - Дополнительные данные по отчету
     *          'errortype'      (string) - Код ошибки
     *          'object'  (stdClass|null) - Объект хранилища
     *          'subreports'      (array) - Массив отчетов по работе над зависимыми элементами
     * @param int $options - Массив дополнительных опций обработки
     *      ['simulation'] (bool) Процесс симуляции, в этом режиме не происходит добавление элементов в БД,
     *                            но формируются все имеющиеся ошибки процесса. Полезно для предварительной
     *                            валидации данных перед добавлением в систему
     *      ['departmentid'] (int) - Значению по умолчанию для объекта, если в нем не указано подразделение.
     *      ['notexist_action] (string) - Действие , если запись на основе переданных данных
     *                            не найдена в системе. Варианты действий: error
     *                            По умолчанию вариант error
     *      ['multiple_action] (string) - Действие , если найдено несколько записей
     *                                    на основе переданных данных
     *                            Варианты действий: error|first|last
     *                            По умолчанию вариант error
     *      ['reportcode'] (string) - Код отчета. По умолчанию programm.
     *
     * @return stdClass - Объект, полученный на основе переданных данных
     */
    public function import($data, &$report = null, $options = [])
    {
        // НОРМАЛИЗАЦИЯ ВХОДЯЩИХ ДАННЫХ
        if ( ! empty($data) )
        {// Данные переданы
            // Нормализация данных по объекту
            if ( is_int($data) )
            {// ID объекта
                $data = ['id' => $data];
            } elseif ( is_string($data) )
            {// Код объекта
                $data = ['code' => $data];
            } else
            {// Комплексные данные
                $data = (array)$data;
            }
        }
        // НОРМАЛИЗАЦИЯ ОПЦИЙ ИМПОРТА
        if ( ! isset($options['simulation']) )
        {// Процесс симуляции отключен
            $options['simulation'] = false;
        }
        if ( ! isset($options['departmentid']) )
        {// Установка подразделения по умолчанию
            $options['departmentid'] = $this->dof->storage('departments')->get_default_id();
        }
        if ( ! isset($options['notexist_action']) )
        {// Действие по умолчанию, если объект не найден
            $options['notexist_action'] = 'error';
        }
        if ( ! isset($options['multiple_action']) )
        {// Действие по умолчанию, если найдено несколько объектов
            $options['multiple_action'] = 'error';
        }
        if ( ! isset($options['reportcode']) || empty($options['reportcode']) )
        {// Код отчета по умолчанию
            $options['reportcode'] = 'programm';
        }
    
        // ОПРЕДЕЛЕНИЕ БАЗОВЫХ ДАННЫХ ДЛЯ ПРОЦЕССА ИМПОРТА
        $importobject = null;
        // Текущий отчет
        $subreport = [
            'action'     => 'get',
            'error'      => null,
            'notice'     => null,
            'additional' => null,
            'errortype'  => null,
            'object'     => null,
            'subreports' => []
        ];
    
        if ( empty($data) )
        {// Данные не переданы
            $subreport['error'] = 'error_import_empty_data';
            $subreport['errortype'] = 'data_validation';
        } else
        {// Данные определены
            // ПОИСК ОБЪЕКТОВ ПО УНИКАЛЬНЫМ ПОЛЯМ ПЕРЕДАННЫХ ДАННЫХ
            $params = [];
            $objects = [];
            // Формирование статусов для фильтрации
            if ( $this->dof->plugin_exists('workflow', 'programms') )
            {
                $statuses = (array)$this->dof->workflow('programms')->get_meta_list('real');
                $statuses = array_keys($statuses);
            } else
            {
                $statuses = ['draft', 'available', 'notavailable', 'archive'];
            }
            $params['status'] = $statuses;
            // Поиск программ по ID
            if ( isset($data['id']) && ! empty($data['id']) )
            {
                $params['id'] = $data['id'];
                $programms = $this->get_records($params);
                $objects = $programms + $objects;
                unset($params['id']);
            }
            // Поиск программ по коду
            if ( isset($data['code']) && ! empty($data['code']) )
            {
                $params['code'] = $data['code'];
                $programms = $this->get_records($params);
                $objects = $programms + $objects;
                unset($params['code']);
            }
    
            // ОБРАБОТКА НАЙДЕННЫХ ОБЪЕКТОВ
            if ( empty($objects) )
            {// Объекты не найдены
                // Исполнение действия в зависимости от настроек
                switch ( $options['notexist_action'] )
                {
                    case 'error' :
                    default :
                        $subreport['error'] = 'error_import_programm_not_found';
                        $subreport['errortype'] = 'notexist';
                        break;
                }
            }
            if ( count($objects) > 1 )
            {// Найдено несколько программ
                // Исполнение действий в соответствии с настройками
                switch ( $options['multiple_action'] )
                {
                    // Вернуть первый элемент массива
                    case 'first' :
                        $importobject = reset($objects);
                        break;
                        // Вернуть последний элемент массива
                    case 'last' :
                        $importobject = end($objects);
                        break;
                        // Ошибка
                    case 'error' :
                    default :
                        $subreport['error'] = 'error_import_programm_multiple_found';
                        $subreport['errortype'] = 'multiple';
                        $subreport['additional'] = array_keys($objects);
                        break;
                }
            }
            if ( count($objects) == 1 )
            {// Объект найден
                $importobject = current($objects);
            }
    
            // ЗАВЕРШЕНИЕ ОБРАБОТКИ
            $subreport['object'] = $importobject;
        }
    
        if ( $report !== null )
        {// Требуется отчет о работе
            $report[$options['reportcode']] = $subreport;
        }
        unset($subreport);
    
        return $importobject;
    }
    
    /**
     * Получить список программ c учетом параметров
     *
     * @param array|stdClass $filter - Параметры поиска программ
     * @param array $options - Массив параметров обработки
     *              bool|array['strict_filter'] - Строгое соответствие фильтрам
     *                         или массив параметров с персональными настройками
     *                    bool['returnids'] - Вернуть только массив идентификаторов
     *
     * @return array - Массив программ
     */
    public function get_programms_by_filter($filter = [], $options = [])
    {
        // Нормализация фильтра
        $filter = (array)$filter;
        if ( ! isset($options['strict_filter']) )
        {// Установка нестрогой фильтрации(с учетом фрагментов)
            $options['strict_filter'] = false;
        }
        $strict = $options['strict_filter'];
        
        // Формирование параметров получения программ
        $select = [];
        $params = [];
        
        // ID программы
        if ( isset($filter['id']) )
        {// Требуется найти программы по имени
            $select[] = ' id = :id ';
            $params = ['id' => (int)$filter['id'] ];
        }
        // Имя программы
        if ( isset($filter['name']) )
        {// Требуется найти программы по имени
            $select[] = ' name LIKE :name ';
            if ( $strict === true || ( isset($strict['name']) && $strict['name'] === true ) )
            {// Указано строгое соответствие фильтра
                $params = ['name' => (string)$filter['name'] ];
            } else
            {// Поиск по фрагменту имени
                $params = ['name' => '%'.(string)$filter['name'].'%' ];
            }
        }
        // Код программы
        if ( isset($filter['code']) )
        {// Требуется найти программы по коду
            $select[] = ' code LIKE :code ';
            if ( $strict === true || ( isset($strict['code']) && $strict['code'] === true ) )
            {// Указано строгое соответствие фильтра
                $params = ['code' => (string)$filter['code'] ];
            } else
            {// Поиск по фрагменту кода программы
                $params = ['code' => '%'.(string)$filter['code'].'%' ];
            }
        }
        // Описание программы
        if ( isset($filter['about']) )
        {// Требуется найти программы по коду
            $select[] = ' about LIKE :about ';
            if ( $strict === true || ( isset($strict['about']) && $strict['about'] === true ) )
            {// Указано строгое соответствие фильтра
                $params = ['about' => (string)$filter['about'] ];
            } else
            {// Поиск по фрагменту кода программы
                $params = ['about' => '%'.(string)$filter['about'].'%' ];
            }
        }
        // По статусу
        if ( isset($filter['status']) )
        {// Требуется найти программы по коду
            if ( is_array($filter['status']) )
            {// Передан массив статусов
                if ( is_string(key($filter['status'])) )
                {// Нормализация переданного массивав статусов
                    $filter['status'] = array_keys($filter['status']);
                }
                foreach ( $filter['status'] as &$status )
                {
                    $status = "'".$status."'";
                }
                $statuses = implode(',', $filter['status']);
                $select[] = ' status IN ('.$statuses.') ';
            }
            if ( is_string($filter['status']) )
            {// Передан единичный статус
                $select[] = ' status LIKE :status ';
                $params = ['status' => (string)$filter['status'] ];
            }
        }
        
        // Поиск программ
        $select = implode(' AND ', $select);
        $fields = '*';
        if ( isset($options['returnids']) && $options['returnids'] )
        {
            $fields = 'id';
        }
        $list = $this->get_records_select($select, $params, '', $fields);
    
        return $list;
    }
    
    /** Возвращает максимальное количество семестров по всем программам
     *
     * @param int $departmentid подразделение, по которому выбираем
     * @return int максимальное число семестров
     */
    public function get_max_agenums($departmentid = null)
    {
        $sql = 'SELECT MAX(agenums) as max FROM ' . $this->prefix(). $this->tablename();
        if ( !empty($departmentid) )
        {
            if ( !is_int_string($departmentid) )
            {
                return false;
            } else
            {
                $sql .= ' WHERE departmentid = ' . $departmentid;
            }
        }
        $records = $this->get_records_sql($sql);
        if ( empty($records) )
        {
            return false;
        }
        $current = current($records);
        return $current->max;
    }
    
    /** Получить список всех учебных программ для
     * подстановки в поле "select" в форме
     * @return
     */
    public function get_menu_programms_list()
    {// получим список всех программ
        $list = $this->get_programms_list(null, null);
        $programms = array();
        if ( $list )
        {// если программы есть
            foreach ($list as $data)
            {// сформируем из них массив - id программы=>его имя
                $programms[$data->id]=$data->name;
            }
        }
        return $programms;
    }
 
    public function get_programms_list($departmentid = null, $status = null)
    {
        //формируем фрагмент запроса по подразделению
        $seldep = $this->query_part_select('departmentid',$departmentid);
        //формируем фрагмент запроса по статусу
        $selstatus = $this->query_part_select('status',$status);
        //объединяем оба фрагмента
        if ( $seldep AND $selstatus )
        {//оба фрагмента надо использовать в фильтрации
            $select = $seldep.' AND '.$selstatus;
        }else
        {//использовать надо только один фрагмент
            $select = $seldep.$selstatus;
        }
        return $this->get_records_select($select, null,'name ASC');
    }
    
    /** Получить фрагмент списка учебных периодов для вывода таблицы
     *
     * @return array массив записей из базы, или false в случае ошибки
     * @param int $limitfrom - начиная с какой записи просматривается фрагмент списка записей
     * @param int $limitnum - сколько записей нужно извлечь из базы
     * @param object $conds - SQL-код с дополнительными условиями, если потребуется
     * (sql-параметр ORDER BY)
     */
    public function get_listing($conds=array(), $limitfrom = null, $limitnum = null, $sort='', $fields='*', $countonly=false)
    {
        if ( $limitnum <= 0 AND ! is_null($limitnum) )
        {// количество записей на странице может быть
            //только положительным числом
            $limitnum = $this->dof->modlib('widgets')->get_limitnum_bydefault();
        }
        if ( $limitfrom < 0 AND ! is_null($limitfrom))
        {//отрицательные значения номера просматриваемой записи недопустимы
            $limitfrom = 0;
        }
        //формируем строку запроса
        $select = $this->get_select_listing($conds);
        
        $recordscount = $this->dof->storage('programms')->count_records_select($select);
        
        
        if ( $recordscount < $limitfrom )
        {// если количество записей в базе меньше,
            //чем порядковый номер записи, которую надо показать
            //покажем последнюю страницу
            $limitfrom = $recordscount;
        }
        
        //определяем порядок сортировки
        $sort = 'name ASC, departmentid ASC, status ASC';
        // возвращаем ту часть массива записей таблицы, которую нужно
        return $this->dof->storage('programms')->get_records_select($select,null, $sort, '*', $limitfrom, $limitnum);
    }
    
    /**
     * Возвращает фрагмент sql-запроса после слова WHERE
     * @param int $departmentid - id подразделения
     * @param string $status - название статуса
     * @return string
     */
    public function get_select_listing($inputconds)
    {
        // создадим массив для фрагментов sql-запроса
        $selects = array();
        $conds = fullclone($inputconds);
        if ( isset($conds->name) AND ($conds->name <> '') )
        {// для имени используем шаблон LIKE
            $selects[] = " name LIKE '%".$conds->name."%' ";
            // убираем имя из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->name);
        }
        if ( isset($conds->code) AND ($conds->code <> '') )
        {// создадим запрос для кода, чтоб он обрабатывал все знаки
            $selects[] = " code= '".$conds->code."'";
            // убираем код из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->code);
        }
        // теперь создадим все остальные условия
        foreach ( $conds as $name=>$field )
        {
            if ( $field )
            {// если условие не пустое, то для каждого поля получим фрагмент запроса
                $selects[] = $this->dof->storage('programms')->query_part_select($name,$field);
            }
        }
        //формируем запрос
        if ( empty($selects) )
        {// если условий нет - то вернем пустую строку
            return '';
        }elseif ( count($selects) == 1 )
        {// если в запросе только одно поле - вернем его
            return current($selects);
        }else
        {// у нас несколько полей - составим запрос с ними, включив их всех
            return implode($selects, ' AND ');
        }
    }
    
    /**
     * Обработка AJAX-запросов из форм
     *
     * @param string $querytype - Тип запроса
     * @param int $depid - ID объекта с которым производятся действия
     * @param string $data - Введенные данные в текстовое поле,
     *      на основе которых требуется произвести автодополнение
     * @param int $objectid - ID объекта, данные которого редактируются
     * @param array $additionaldata - Дополнительные данные из json-запроса
     *
     * @return array
     */
    public function widgets_field_variants_list($querytype, $depid, $data, $objectid, $additionaldata)
    {
        switch ( $querytype )
        {
            // Поиск соответствий с учетом дополнительных параметров
            case 'find_programm_with_params' :
                return $this->ajax_find_programm_with_params('name', $data, $depid, $additionaldata);
            // Вернуть список параллелей для программы
            case 'select_programm_agenum_with_params' :
                
                if ( (string)$data === (int)(string)$data )
                {// Передан номер параллели
                    $data = (int)$data;
                } else
                {// Параллель не передана - вывод всего списка
                    $data = null;
                }
                if ( isset($additionaldata['programmid']) )
                {// Указан ID программы
                    $objectid = (int)$additionaldata['programmid'];
                }
                return $this->ajax_find_programm_agenums_with_params((int)$objectid, $data);
            default :
                return [ 0 => $this->dof->modlib('ig')->igs('choose')];
        }
    }
    
    /**
     * Найти программы, подходящие по данным
     *
     * @param string $returnfield - Имя поля для формирования результирующего массива
     * @param string $data - Данные, на основе которых будет производиться поиск
     *                  ID, Полное и короткое имена программы
     * @param int $departmentid - ID подразделения
     * @param array $additionaldata - Дополнительные данные программы для фильтрации подходящих
     *
     * @return array - Массив подходящих программ в виде ['ID' => $returnfield]
     */
    private function ajax_find_programm_with_params($returnfield, $data, $departmentid = 0, $additionaldata = null)
    {
        $select = '';
        $params = [];
        
        // Поиск подходящих программ по основным данным
        $params['name'] = '%'.$data.'%';
        $params['code'] = '%'.$data.'%';
        $select .= ' ( name LIKE :name OR code LIKE :code ';
        if ( (int)$data > 0 )
        {// Передан идентификатор
            $params['id'] = (int)$data;
            $select .= 'OR id = :id';
        }
        $select .= ' ) ';
        
        if ( $this->dof->plugin_exists('workflow', 'programms') )
        {// Найден плагин статусов
            $statuses = $this->dof->workflow('programms')->get_meta_list('actual');
            $statuses = "'".implode("','", array_keys($statuses))."'";
            $select .= ' AND status IN ('.$statuses.') ';
        }
        if ( $departmentid > 0 )
        {// Учет подразделения
            $select .= ' AND departmentid = :departmentid ';
            $params['departmentid'] = $departmentid;
        }

        if ( isset($additionaldata['agenum']) )
        {// Учет требуемого числа параллелей
            $select .= ' AND agenums >= :minagenum ';
            $params['minagenum'] = (int)$additionaldata['agenum'];
        }
        
        $return = [];
        $result = $this->get_records_select($select, $params, $returnfield.' ASC ', 'id, '.$returnfield);
        foreach ( (array)$result as $id => $programm )
        {
            $value = new stdClass();
            $value->id = $id;
            $value->name = $programm->$returnfield;
            // Формирование массива по автозаполнению
            $return[$id] = $value;
        }
        return $return;
    }
    
    /**
     * Сформировать список параллелей на основе данных о программе
     *
     * @param int $id - ID программы для формирования списка
     * @param int $data - Номер выбранной параллели.
     *     Если указано, будет возвращен список только с этой параллелью(программа должна поддерживать эту параллель)
     *
     * @return array - Массив параллелей в виде [agenum => agenum]
     */
    public function ajax_find_programm_agenums_with_params($id, $havingagenum = null)
    {
        // Получение программы
        $programm = $this->get((int)$id);
        if ( empty($programm) )
        {// Программа не найдена
            return [];
        }
        
        if ( is_int($havingagenum) )
        {// Указана выбранная параллель
            if ( $programm->agenums >= $havingagenum && $havingagenum >= 0 )
            {// Вернуть только указанную параллель
                $value = new stdClass();
                $value->id = $havingagenum;
                $value->name = $havingagenum;
                return [$havingagenum => $value];
            }
        }
        
        // Вернуть полный список
        $return = [];
        for ( $num = 0; $num <= $programm->agenums; $num++ )
        {
            $value = new stdClass();
            $value->id = $num;
            $value->name = $num;
            $return[$num] = $value;
        }
        return $return;
    }

    /**
     * Сохранить программу в системе
     *
     * @param string|stdClass|array $programmdata - Данные программы(название или комплексные данные)
     * @param array $options - Массив дополнительных параметров
     *
     * @return bool|int - false в случае ошибки или ID программы в случае успеха
     *
     * @throws dof_exception_dml - В случае ошибки
     */
    public function save($programmdata = null, $options = [])
    {
        // Нормализация данных
        try {
            $normalized_data = $this->normalize($programmdata, $options);
        } catch ( dof_exception_dml $e )
        {
            throw new dof_exception_dml('error_save_'.$e->errorcode);
        }
    
        // Сохранение данных
        if ( isset($normalized_data->id) && $this->is_exists($normalized_data->id) )
        {// Обновление записи
            $programm = $this->update($normalized_data);
            if ( empty($programm) )
            {// Обновление не удалось
                throw new dof_exception_dml('error_save_programm');
            } else
            {// Обновление удалось
                $this->dof->send_event('storage', 'programm', 'item_saved', (int)$normalized_data->id);
                return $normalized_data->id;
            }
        } else
        {// Создание записи
            $programmid = $this->insert($normalized_data);
            if ( ! $programmid )
            {// Добавление не удалось
                throw new dof_exception_dml('error_save_programm');
            } else
            {// Добавление удалось
                $this->dof->send_event('storage', 'programms', 'item_saved', (int)$programmid);
                if( ! empty($options['activate']) )
                {// Требуется активация объекта
                    $this->dof->send_event('storage', 'programms', 'activate_request', (int)$programmid);
                }
                return $programmid;
            }
        }
        return false;
    }
    
    /**
     * Нормализация данных программы
     *
     * Формирует объект программы на основе переданных данных. В случае критической ошибки
     * или же если данных недостаточно, выбрасывает исключение.
     *
     * @param string|stdClass|array $programmdata - Данные программы (название или комплексные данные)
     * @param array $options - Опции работы
     *
     * @return stdClass - Нормализовализованный Объект программы
     * @throws dof_exception_dml - Исключение в случае критической ошибки или же недостаточности данных
     */
    public function normalize($programmdata, $options = [])
    {
        // Нормализация входных данных
        if ( is_object($programmdata) || is_array($programmdata) )
        {// Комплексные данные
            $programmdata = (object)$programmdata;
        } elseif ( is_string($programmdata) )
        {// Передано название
            $programmdata = new stdClass();
            $programmdata->name = $programmdata;
        } else
        {// Неопределенные данные
            throw new dof_exception_dml('invalid_data');
        }
        
        // Нормализация идентификатора
        if ( isset($programmdata->id) && (int)$programmdata->id < 1)
        {
            unset($programmdata->id);
        }
        // Проверка входных данных
        if ( empty($programmdata) )
        {// Данные не переданы
            throw new dof_exception_dml('empty_data');
        }
        
        if ( ( ! isset($programmdata->id) || (int)$programmdata->id < 1 ) &&
             ( empty($programmdata->name) )
           )
        {// Не указано наименование программы
            throw new dof_exception_dml('create_without_name');
        }
        
        if ( ( ! isset($programmdata->id) || (int)$programmdata->id < 1 ) &&
             ( empty($programmdata->departmentid) )
           )
        {// Не указано подразделение
            throw new dof_exception_dml('create_without_department');
        }
        
        if ( isset($programmdata->id) )
        {// Проверка на существование
            if ( ! $this->get($programmdata->id) )
            {// Программа не найдена
                throw new dof_exception_dml('programm_not_found');
            }
        }
    
        // Создание объекта для сохранения
        $saveobj = clone $programmdata;
    
        // Обработка входящих данных и построение объекта программы
        if ( isset($saveobj->id) && $this->is_exists($saveobj->id) )
        {// Программа уже содержится в системе
            // Удаление автоматически генерируемых полей
            unset($saveobj->status);
        } else
        {// Новая программы
            // АВТОЗАПОЛНЕНИЕ ПОЛЕЙ
            if ( empty($saveobj->name) )
            {// Установка названия по умолчанию
                $saveobj->name = '';
            }
            if ( empty($saveobj->code) )
            {// Установка кода по умолчанию
                $saveobj->code = '';
            }
            if ( empty($saveobj->about) )
            {// Установка поля "описание" по умолчанию
                $saveobj->about = '';
            }
            if ( empty($saveobj->departmentid) )
            {// Установка подразделения по умолчанию
                $saveobj->departmentid = 0;
            }
            if ( empty($saveobj->notice) )
            {// Установка заметок по умолчанию
                $saveobj->notice = '';
            }
            if ( empty($saveobj->billingtext) )
            {// Установка цены по умолчанию
                $saveobj->billingtext = '';
            }
            if ( empty($saveobj->agenums) )
            {// Установка количества семестров по умолчанию
                $saveobj->agenums = 1;
            }
            if ( empty($saveobj->duration) )
            {// Установка длительности в днях по умолчанию
                $saveobj->duration = 0;
            }
            if ( empty($saveobj->ahours) )
            {// Установка длительности в ак.часах по умолчанию
                $saveobj->ahours = 0;
            }
            if ( empty($saveobj->flowagenums) )
            {// Использование плавающих учебных планов по умолчанию
                $saveobj->flowagenums = 0;
            }
            if ( empty($saveobj->edulevel) )
            {// Установка уровня образования по умолчанию
                $saveobj->edulevel = 1;
            }
    
            // АВТОМАТИЧЕСКИ ГЕНЕРИРУЕМЫЕ ПОЛЯ
            if ( ! $this->dof->plugin_exists('workflow', 'programms') )
            {// Плагин статусов программы не активен, установка статуса по умолчанию
                $saveobj->status = 'available';
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
        // Нормализация описание
        if ( isset($saveobj->about) )
        {
            $saveobj->about = (string)$saveobj->about;
        }
        // Нормализация заметки
        if ( isset($saveobj->notice) )
        {
            $saveobj->notice = (string)$saveobj->notice;
        }
        // Нормализация  количество параллелей (семестров)
        if ( isset($saveobj->agenums) )
        {
            $saveobj->agenums = (int)$saveobj->agenums;
        }
        // Нормализация длительность в днях
        if ( isset($saveobj->duration) )
        {
            $saveobj->duration = (int)$saveobj->duration;
        }
        // Нормализация длительность в ак. часах
        if ( isset($saveobj->ahours) )
        {
            $saveobj->ahours = (int)$saveobj->ahours;
        }
        // Нормализация цена
        if ( isset($saveobj->billingtext) )
        {
            $saveobj->billingtext = (string)$saveobj->billingtext;
        }
        // Нормализация плавающих учебных планов
        if ( isset($saveobj->flowagenums) )
        {
            $saveobj->flowagenums = (int)$saveobj->flowagenums;
        }
        // Нормализация уровня образования
        if ( isset($saveobj->edulevel) )
        {
            $saveobj->edulevel = (int)$saveobj->edulevel;
        }
        // Нормализация подразделения
        if( isset($saveobj->departmentid) )
        {
            if( ! $this->dof->storage('departments')->is_exists((int)$saveobj->departmentid) )
            {
                $saveobj->departmentid = (int)$saveobj->departmentid;
            }
        }
    
        return $saveobj;
    }
}
?>