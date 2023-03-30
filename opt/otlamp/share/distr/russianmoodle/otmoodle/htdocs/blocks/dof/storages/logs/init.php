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

/**
 * Хранилище очередей логов Деканата.
 * 
 * Очередь логов представляет собой историю определенного действия в Деканате, 
 * например процесса импорта данных.
 *
 * @package    storage
 * @subpackage logs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_storage_logs extends dof_storage
{
    /**
     * Контроллер деканата
     * 
     * @var dof_control
     */
    protected $dof;

    /**
     * Типы очередей
     *
     * @var array
     */
    protected $queuetypes = null;
    
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
        if (! parent::install())
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
        global $CFG, $DB;
        $result = true;
        // Методы для установки таблиц из xml
        require_once ($CFG->libdir . '/ddllib.php');
        
        $manager = $DB->get_manager();
        $table = new xmldb_table($this->tablename());
                
        return $result &&
             $this->dof->storage('acl')->save_roles($this->type(), $this->code(), 
                $this->acldefault());
    }

    /**
     * Возвращает версию установленного плагина
     *
     * @return int - Версия плагина
     */
    public function version()
    {
        return 2017100500;
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
        return 'logs';
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
                'acl'    => 2016071500,
                'config' => 2012042500
            ]
        ];
    }

    /**
     * Определить, возможна ли установка плагина в текущий момент
     * Эта функция одинакова абсолютно для всех плагинов и не содержит в себе каких-либо зависимостей
     * 
     * @todo УДАЛИТЬ эту функцию при рефакторинге. Вместо нее использовать наследование
     *       от класса dof_modlib_base_plugin
     *      
     * @see dof_modlib_base_plugin::is_setup_possible()
     * @param int $oldversion[optional]
     *            - старая версия плагина в базе (если плагин обновляется)
     *            или 0 если плагин устанавливается
     *            
     * @return bool true - если плагин можно устанавливать
     *         false - если плагин устанавливать нельзя
     */
    public function is_setup_possible($oldversion = 0)
    {
        return dof_is_plugin_setup_possible($this, $oldversion);
    }

    /**
     * Получить список плагинов, которые уже должны быть установлены в системе,
     * и без которых начать установку или обновление невозможно
     *
     * @param int $oldversion[optional]
     *            - старая версия плагина в базе (если плагин обновляется)
     *            или 0 если плагин устанавливается
     *            
     * @return array массив плагинов, необходимых для установки
     *         Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion = 0)
    {
        return [
            'storage' => [
                'acl'    => 2016071500,
                'config' => 2012042500
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
        return [];
    }

    /**
     * Требуется ли запуск cron в плагине
     *
     * @return bool
     */
    public function is_cron()
    {
        // Запуск требуется
        return true;
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
        if ($this->acl_check_access_parameters($acldata))
        { // Право есть
            return true;
        }
        return false;
    }

    /**
     * Требует наличия полномочия на совершение действий
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
    public function require_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
        if (! $this->is_access($do, $objid, $userid, $depid))
        {
            $notice = "{$this->code()}/{$do} (block/dof/{$this->type()}/{$this->code()}: {$do})";
            if ($objid)
            {
                $notice .= " id={$objid}";
            }
            $this->dof->print_error('nopermissions', '', $notice);
        }
    }

    /**
     * Обработать событие
     *
     * @param string $gentype
     *            - тип модуля, сгенерировавшего событие
     * @param string $gencode
     *            - код модуля, сгенерировавшего событие
     * @param string $eventcode
     *            - код задания
     * @param int $intvar
     *            - дополнительный параметр
     * @param mixed $mixedvar
     *            - дополнительные параметры
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
     * @param int $loan
     *            - нагрузка (
     *            1 - только срочные,
     *            2 - нормальный режим,
     *            3 - ресурсоемкие операции
     *            )
     * @param int $messages
     *            - количество отображаемых сообщений (
     *            0 - не выводить,
     *            1 - статистика,
     *            2 - индикатор,
     *            3 - детальная диагностика
     *            )
     *            
     * @return bool - true в случае выполнения без ошибок
     */
    public function cron($loan, $messages)
    {
        
        
        return true;
    }

    /**
     * Обработать задание, отложенное ранее в связи с его длительностью
     *
     * @param string $code
     *            - код задания
     * @param int $intvar
     *            - дополнительный параметр
     * @param mixed $mixedvar
     *            - дополнительные параметры
     *            
     * @return bool - true в случае выполнения без ошибок
     */
    public function todo($code, $intvar, $mixedvar)
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
     * 
     * @return text
     * @access public
     */
    public function tablename()
    {
        // Имя таблицы, с которой работаем
        return 'block_dof_s_logs';
    }
    
    // ***********************************************************
    //       Методы для работы с полномочиями и конфигурацией
    // ***********************************************************     

    /**
     * Получить список параметров для фунции has_right()
     *
     * @param string $action
     *            - совершаемое действие
     * @param int $objectid
     *            - id объекта над которым совершается действие
     * @param int $personid           
     * 
     * @return stdClass - список параметров для фунции has_right()
     */
    protected function get_access_parametrs($action, $objectid, $personid, $depid = null)
    {
        $result = new stdClass();
        $result->plugintype = $this->type();
        $result->plugincode = $this->code();
        $result->code = $action;
        $result->personid = $personid;
        $result->departmentid = $depid;
        $result->objectid = $objectid;
        
        if (is_null($depid))
        { // Подразделение не задано - ищем в GET/POST
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        if (! $objectid)
        { // Если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
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
     * @param stdClass $acldata
     *            - объект с данными для функции storage/acl->has_right()
     *            
     * @return bool
     */
    protected function acl_check_access_parameters($acldata)
    {
        return $this->dof->storage('acl')->has_right(
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
        $a = [];
        
        $a['create'] = [
            'roles' => [
                'manager'
            ]
        ];
        $a['view'] = [
            'roles' => [
                'manager'
            ]
        ];
        
        return $a;
    }

    /**
     * Функция получения настроек для плагина
     */
    public function config_default($code = null)
    {
        $config = [];
        
        return $config;
    }
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /**
     * Получить список доступных типов очередей
     *
     * @return array - Массив типов очередей
     */
    protected function get_queuetypes()
    {
        global $CFG;
        
        if ( $this->queuetypes === null )
        {// Получение списка очередей
            
            $this->queuetypes = [];
            
            // Валидация пути до типов очередей
            $dir = $this->dof->plugin_path('storage', 'logs', '/classes/queuetype/');
            if ( is_dir($dir) )
            {
                // Поиск полей
                foreach ( (array)scandir($dir) as $typedir )
                {
                    if ( $typedir == '.' || $typedir == '..' )
                    {
                        continue;
                    }
                    
                    if ( is_dir($dir.'/'.$typedir) )
                    {// Папка с классом типа очереди
                        
                        $path = $dir.'/'.$typedir.'/init.php';
                        if ( file_exists($path) )
                        {// Класс дополнительного поля найден
                            require_once($path);
                            
                            // Название класса дополнительного поля
                            $classname = 'dof_storage_logs_queuetype_'.$typedir;
                            if ( class_exists($classname) )
                            {// Класс дополнительного поля найден
                                $this->queuetypes[$typedir] = $classname;
                            }
                        }
                    }
                }
            }
        }
        return $this->queuetypes;
    }
    
    /**
     * Создание очереди логов
     *
     * @param string $ptype - Тип плагина
     * @param string $pcode - Код плагина
     * @param string $subcode - Сабкод плагина
     * @param string $objectid - Идентификатор объекта
     * @param string $config - Конфигурация очереди
     * @param string $logtype - Тип очереди логов
     *
     * @return dof_storage_logs_queuetype_base
     */
    public function create_queue($ptype, $pcode, $subcode, $objectid = null, $logtype = null, $config = null)
    {
        if ( empty($ptype) || empty($pcode) || empty($subcode) )
        {// Недостаточно данных
            return false;
        }
        
        if ( $logtype == null )
        {// Установка типа очереди
            $types = $this->get_queuetypes();
            $logtype = key($types);
        } elseif ( ! array_key_exists($logtype, $this->get_queuetypes()) )
        {// Неподдерживаемый тип очереди
            return false;
        }
        
        // Создание записи очереди
        $queue = new stdClass();
        $queue->ptype = (string)$ptype;
        $queue->pcode = (string)$pcode;
        $queue->subcode = (string)$subcode;
        $queue->objid = (int)$objectid;
        $queue->logtype = (string)$logtype;
        $queue->config = $config;
        
        $id = $this->save($queue);
        if ( $id )
        {// Запись успешно сохранена
            
            // Инициализация очереди логов
            return $this->init_from_id($id);
        }
    }
    
    /**
     * Инициализация очереди логов
     *
     * @param int $id - Идентификатор очереди логов
     *
     * @return dof_storage_logs_queuetype_base
     */
    public function init_from_id($id)
    {
        $data = $this->get($id);
        if ( empty($data->logtype) )
        {// Идентификатор не найден
            return; 
        }

        
        if ( ! array_key_exists($data->logtype, $this->get_queuetypes()) )
        {// Тип очереди не найден
            return;
        }
        
        $types = $this->get_queuetypes();
        $classname = $types[$data->logtype];
        return new $classname($this->dof, $data);
    }
    
    /**
     * Сохранить запись лога
     *
     * @param string|stdClass|array $logdata
     * @param array $options - Массив дополнительных параметров
     *
     * @return false|int
     *
     * @throws dof_exception_dml
     */
    public function save($logdata = null, $options = [])
    {
        // Нормализация данных
        try {
            $normalized_data = $this->normalize($logdata, $options);
        } catch ( dof_exception_dml $e )
        {
            throw new dof_exception_dml('error_save_'.$e->errorcode);
        }
        
        // Сохранение данных
        if ( isset($normalized_data->id) && $this->is_exists($normalized_data->id) )
        {// Обновление записи
            $log = $this->update($normalized_data);
            if ( empty($log) )
            {// Обновление не удалось
                throw new dof_exception_dml('error_save_log');
            } else
            {// Обновление удалось
                $this->dof->send_event('storage', 'logs', 'item_saved', (int)$normalized_data->id);
                return $normalized_data->id;
            }
        } else
        {// Создание записи
            $logid = $this->insert($normalized_data);
            if ( ! $logid )
            {// Добавление не удалось
                throw new dof_exception_dml('error_save_log');
            } else
            {// Добавление удалось
                $this->dof->send_event('storage', 'logs', 'item_saved', (int)$logid);
                return $logid;
            }
        }
        return false;
    }
    
    /**
     * Нормализация данных записи лога
     *
     * @param string|stdClass|array $logdata
     * @param array $options - Опции работы
     *
     * @return stdClass - Нормализовализованный Объект персоны
     * @throws dof_exception_dml - Исключение в случае критической ошибки или же недостаточности данных
     */
    public function normalize($logdata, $options = [])
    {
        // Нормализация входных данных
        if ( is_object($logdata) || is_array($logdata) )
        {// Комплексные данные
            $logdata = (object)$logdata;
        } else
        {// Неопределенные данные
            throw new dof_exception_dml('invalid_data');
        }
        
        if ( empty($logdata->ptype) ||
                empty($logdata->pcode) ||
                empty($logdata->subcode) ||
                empty($logdata->logtype) )
        {// Неопределенные данные
            throw new dof_exception_dml('invalid_data');
        }
        
        // Нормализация идентификатора
        if ( isset($logdata->id) && $logdata->id < 1)
        {
            unset($logdata->id);
        }
        // Проверка входных данных
        if ( empty($logdata) )
        {// Данные не переданы
            throw new dof_exception_dml('empty_data');
        }
        
        if ( isset($logdata->id) )
        {// Проверка на существование
            if ( ! $this->get($logdata->id) )
            {// Лог не найден
                throw new dof_exception_dml('log_not_found');
            }
        }
        
        // Создание объекта для сохранения
        $saveobj = clone $logdata;
        
        // Обработка входящих данных и построение объекта dлога
        if ( isset($saveobj->id) && $this->is_exists($saveobj->id) )
        {// Персона уже содержится в системе
            // Удаление автоматически генерируемых полей
            unset($saveobj->status);
            unset($saveobj->timestart);
            unset($saveobj->timeend);
        } else
        {// Новая запись лога
            // АВТОЗАПОЛНЕНИЕ ПОЛЕЙ
            // Установка даты создания
            $saveobj->timestart = time();
            
            // Сброс времени завершения
            unset($saveobj->timeend);
            
            if ( ! isset($saveobj->personid) || empty($saveobj->personid) )
            {// Установка идентификатора персоны, если он пустой
                $saveobj->personid = $this->dof->storage('persons')->get_bu()->id;
            }
            if ( isset($saveobj->config) && ! is_object($saveobj->config) )
            {
                $saveobj->config = null;
            }
        }
        
        // ОБРАБОТКА ДАННЫХ
        // Сериализация конфига
        if ( isset($saveobj->config) )
        {
            $saveobj->config = serialize($saveobj->config);
        }
        
        return $saveobj;
    }
    
    /**
     * Получить фрагмент списка логов для вывода таблицы
     *
     * @param int $limitfrom
     * @param int $limitnum
     * @param string $sort
     *
     * @return bool|stdClass массив записей из базы, или false в случае ошибки
     */
    public function get_listing($limitfrom = null, $limitnum = null, $sort = '', $meta_status = 'real')
    {
        $select = '';
        if ( ! is_null($limitnum) AND $limitnum <= 0 )
        {// количество записей на странице может быть
            //только положительным числом
            $limitnum = $this->dof->modlib('widgets')->get_limitnum_bydefault();
        }
        if ( ! is_null($limitfrom) AND $limitfrom < 0 )
        {//отрицательные значения номера просматриваемой записи недопустимы
            $limitfrom = 0;
        }
        
        // Получим доступные статуса по метастатусу
        $statuses = $this->dof->workflow('logs')->get_meta_list($meta_status);
        if ( empty($statuses) )
        {
            return false;
        } else
        {
            // Формируем селект дял получения причин в том статусе, в котором запросили
            $statuses = array_keys($statuses);
            $select .= "status IN ('" . implode("','", $statuses) . "')";
        }
        
        return $this->dof->storage('logs')->get_records_select($select, null, $sort, '*', $limitfrom, $limitnum);
    }
}
?>