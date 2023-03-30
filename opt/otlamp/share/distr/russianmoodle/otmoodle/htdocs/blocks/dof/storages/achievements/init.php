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
require_once $DOF->plugin_path('storage', 'achievements','/base.php');

/**
 * Справочник шаблонов достижений
 * 
 * @package    storage
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_storage_achievements 
        extends dof_storage 
        implements dof_storage_config_interface, dof_storage_deadline_interface
{
    /**
     * Объект деканата для доступа к общим методам
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Кэш плагина
     */
    protected $cache;
    
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
        
        $dbman = $DB->get_manager();
        $table = new xmldb_table($this->tablename());
        
        if ($oldversion < 2018032300)
        {// добавим поле scenario - сценарий использования шаблона (битмаск)
            $field = new xmldb_field(
                'scenario',
                XMLDB_TYPE_INTEGER,
                '1',
                false,
                true,
                null,
                '1',
                'data'
            );
            if ( ! $dbman->field_exists($table, $field) )
            {// поле еще не установлено
                $dbman->add_field($table, $field);
            }
        }
        
        if ($oldversion < 2018041220)
        {// добавим поле данных по уведомлениям
            $field = new xmldb_field(
                    'notificationdata',
                    XMLDB_TYPE_TEXT,
                    null,
                    null,
                    false,
                    false
                    );
            if ( ! $dbman->field_exists($table, $field) )
            {// поле еще не установлено
                $dbman->add_field($table, $field);
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
		return 2018052810;
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
        return 'achievements';
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
                'config' => 2011080900,
                'acl' => 2011041800,
                'achievementcats' => 2015090000,
                'deadline' => 2018041000
            ],
            'modlib' => [
                'messager' => 2018041000
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
        return array( 
		        'storage' => array(
		                'config'          => 2011080900,
		                'acl'             => 2011041800,
		                'achievementcats' => 2015090000
		        )
		);
    }
    
    /** 
     * Список обрабатываемых плагином событий 
     * 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     */
    public function list_catch_events()
    {
        return [
            [
                'plugintype' => 'storage',
                'plugincode' => 'achievements',
                'eventcode' => 'update'
            ]
        ];
    }
    
    /** 
     * Требуется ли запуск cron в плагине
     * 
     * @return bool
     */
    public function is_cron()
    {
        // Запуск не требуется 
        return 3600;
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
        if ( $gentype === 'storage' AND $gencode === 'achievements' AND $eventcode === 'update' )
        {
            if ( $mixedvar['new']->status == 'deleted' )
            {
                // очистка дедлайнов при удалении достижения
                $this->process_deadline($this->get($mixedvar['new']->id));
            }
        }
        
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
        $this->execute_assignment_cron();
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
        
        // Готовим кэш плагина
        $this->сache = [];
        
        // Подгрузим в кэш данные об типах достижений
        $this->сache['classes'] = $this->get_achievementtypes_list();
    }
   
    /** 
     * Возвращает название таблицы без префикса (mdl_)
     * @return text
     * @access public
     */
    public function tablename()
    {
        // Имя таблицы, с которой работаем
        return 'block_dof_s_achievements';
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
        
        $a['view']   = array('roles' => array(
                'manager'
        ));
        $a['edit']   = array('roles' => array(
                'manager'
        ));
        $a['create'] = array('roles' => array(
                'manager'
        ));
       
        return $a;
    }

    /** 
     * Функция получения настроек для плагина 
     */
    public function config_default($code=null)
    {
        $configs = [];
        
        // уведомлять модераторам в подразделении категории без иерархии
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'notificate_onlycatdep_moderators';
        $obj->value = '0';
        $configs[$obj->code] = $obj;
        
        // периодическое уведомление неподтвержденных/неодобренных достижениях
        $obj = new stdClass();
        $obj->type = 'textarea';
        $obj->code = 'notification_stat_periodic';
        $obj->value = 'Здравствуйте, {USERFULLNAME}!<br/><br/>По шаблону «{ACHIEVEMENTNAME}» имеется:<br/><br/>1) {QUANTITYNOTAPPROVAL}<br/>2) {QUANTITYNOTMODERATED}<br/>Для перехода нажмите на ссылку {URL}<br/><br/>С уважением, администрация сайта «{SITENAME}»!';
        $configs[$obj->code] = $obj;
        
        return $configs;
    }       
    
    // **********************************************
    //              Собственные методы
    // ********************************************** 
    
    /**
     * Получить краткое название достижения
     *
     * @param int|stdClass $achievement - Достижение или ID достижения
     *
     * @return null|string - Краткое название достижения или null в случае ошибки
     */
    public function get_shortname($achievement)
    {
        if ( ! is_object($achievement) )
        {// Получение достижения
            $achievement = $this->get((int)$achievement);
            if ( empty($achievement) )
            {// Достижение не найдено
                return null;
            }
        }
        
        // Получение краткого названия достижения
        $stringvars = new stdClass();
        $stringvars->name = $achievement->name;
        return $this->dof->get_string('achievement_shortname', 'achievements', $stringvars, 'storage');
    }
    
    /**
     * Сохранить достижение
     *
     * @param object $object - Объект достижения
     *                  Обязательные поля:
     *                  ->name - Имя достижеия
     *                  ->catid - ID раздела, к которому принадлежит достижение
     *                  ->type - Тип достижения
     *                  Необязательные поля:
     *                  ->sortorder - Вес достижения
     *                  ->points - Балл бостижения
     *                  ->data - Данные достижения
     *
     * @param array $options - Массив дополнительных параметров
     *
     * @return bool|int - false в случае ошибки или ID достижения в случае успеха
     */
    public function save( $object = null, $options = [] )
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
        if ( isset($saveobj->catid) )
        {
            if ( $saveobj->catid <= 0 )
            {// Категория не установлена
                return false;
            } else 
            {
                $exits = $this->dof->storage('achievementcats')->get($saveobj->catid);
                if ( empty($exits) )
                {// Раздел не найден
                    return false;
                }
            }
        } else 
        {// Раздел не установлен
            if ( ! isset($saveobj->id) )
            {// Категория не установлена во время создания шаблона
                return false;
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
            // Сортировка
            $saveobj->sortorder = $this->get_sortorder($saveobj);
            // Добавляем дату изменения
            $saveobj->changedate = time();
            
            // Обновляем запись
            $res = $this->update($saveobj);
            if ( empty($res) )
            {// Обновление не удалось
                return false;
            } else
            {// Обновление удалось
                
                // обработка дедлайна
                $this->process_deadline($this->get($saveobj->id));
                
                return $saveobj->id;
            }
        } else
        {// Создание записи
            // Убираем автоматически генерируемые поля
            unset($saveobj->id);
        
            // Добавляем дату создания
            $saveobj->createdate = time();
            // Добавляем дату изменения
            $saveobj->changedate = time();
            
            // Сортировка
            $saveobj->sortorder = $this->get_sortorder($saveobj);
    
            // Добавляем запись
            $res = $this->insert($saveobj);
            if ( empty($res) )
            {// Добавление не удалось
                return false;
            } else
            {// Добавление удалось
                
                // обработка дедлайна
                $this->process_deadline($this->get($res));
                
                return $res;
            }
        }
    }
    
    /**
     * добавление дедлайна достижению
     *
     * @param stdClass $achievement - шаблон достижения
     * @param stdClass $achievementin - достижение
     *
     * @return true
     */
    public function process_deadline(stdClass $achievement)
    {
        // удаление существующих дедлайнов для текущего достижения
        $this->dof->storage('deadline')->delete_records(['plugintype' => 'storage', 'plugincode' => 'achievements', 'objid' => $achievement->id]);
        if ( $achievement->status == 'deleted' )
        {
            // дедлайн не нужен
            return true;
        }
        $templatedata = unserialize($achievement->notificationdata);
        if ( empty($templatedata['stat_periodic']) )
        {
            // уведомление не настроено
            return true;
        }
        
        // базовые данные для записи дедлайна
        $record = new stdClass();
        $record->plugintype = 'storage';
        $record->plugincode = 'achievements';
        $record->objid = $achievement->id;
        $record->code = 'stat_periodic';
        $record->date = 0;
        $record->periodic = $templatedata['stat_periodic'];
        $this->dof->storage('deadline')->insert($record);
        
        return true;
    }
    
    /**
     * Получить значение сортировки для объекта
     *
     * @TODO - Функция формирования значения для сортировки
     * @param object $saveobj - объект раздела
     */
    private function get_sortorder($saveobj)
    {
        return 0;
    }
    
    /**
     * 
     * @param unknown $type
     */
    public function get_classname($type)
    {
        if ( isset($this->сache['classes'][$type]) )
        {// Класс найден
            return $this->сache['classes'][$type];
        }
        return null;
    }
    
    /**
     * Получить массив типов достижений
     * 
     * @param array $options - Массив дополнительных опций
     * 
     * @return array - Массив типов достижений 
     */
    public function get_achievementtypes_list($options = [])
    {
        $skip = ['.', '..'];
        $files = scandir($this->dof->plugin_path('storage', 'achievements','/classes/'));
        
        $types = array_diff($files, $skip);
        
        $result = [];
        if ( ! empty($types) )
        {
            foreach( $types as $type ) 
            {
                if ( file_exists($this->dof->plugin_path('storage', 'achievements','/classes/'.$type.'/init.php')) )
                {// Файл класса имеется
                    // Подключаем класс
                    require_once($this->dof->plugin_path('storage', 'achievements','/classes/'.$type.'/init.php'));
                    
                    if ( class_exists('dof_storage_achievements_'.$type) )
                    {// Класс имеется
                        $result[$type] = 'dof_storage_achievements_'.$type;
                    }
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Возвращает объект класса достижения
     * 
     * @param int $id - ID шаблона достижения
     * @param int $options - Дополнительные опции
     *  ['rating'] - bool Доступность подсистемы рейтинга
     *  ['moderation'] - bool Доступность подсистемы модерации
     */
    public function object($id, $additionalopts = [])
    {
        if ( $id <= 0 )
        {// Неправильные входные данные
            return null;
        }
        $achievement = $this->get($id);

        if ( ! empty($achievement) )
        {// Шаблон найден
            
            if ( isset($this->сache['classes'][$achievement->type]) )
            {// Класс найден
                $classname = $this->сache['classes'][$achievement->type];
                $options = [];
                if ( isset($additionalopts['rating_enabled']) )
                {// Добавлены данные по доступности подсистемы рейтинга 
                    $options['rating_enabled'] = $additionalopts['rating_enabled'];
                }
                if ( isset($additionalopts['moderation_enabled']) )
                {// Добавлены данные по доступности подсистемы модерации
                    $options['moderation_enabled'] = $additionalopts['moderation_enabled'];
                }
                // Получение объекта шаблона
                $object = new $classname($this->dof , $achievement, $options);
                
                return $object;
            }
        }
        return null;
    }
    
    /**
     * Выполнение заданий по крону.
     */
    public function execute_assignment_cron()
    {
        $this->delete_achievementinstances();
        $this->add_achievementinstances();
        $this->check_relevance();
    }
    
    public function add_achievementinstances()
    {
        $achievements = $assignids = $records = $checkaddedachievementins = $addedachievementinstances = $alreadyaddedachievementins = [];
        // Получим существующие в системе шаблоны достижений в статусе доступном для работы
        $achievements = $this->get_achievements_by_type('assignment');
        if( $achievements )
        {// Если шаблоны найдены
            foreach($achievements as $achievement)
            {// сформируем массив вида [assignid][][achievementid, achievementcatid]
                $data = unserialize($achievement->data);
                $assignids[$data['simple_data']['assignment']][] = ['achievementid' => $achievement->id, 'achievementcatid' => $achievement->catid];
            }
            if( ! empty($assignids) )
            {// Если массив сформировался, получим записи из assign_grades, содержащие пары значений (assignid, userid)
                // которые потенциально можно использовать для добавления достижений
                $assign_instance = $this->dof->modlib('ama')
                ->course(false)
                ->get_instance_object(
                    'assign',
                    false,
                    false
                )
                ->get_manager();
                $records = $assign_instance->get_users_which_can_add_achievement(array_keys($assignids));
                if( $records )
                {// Если данные получены
                    foreach($records as $val)
                    {// сформируем массив из пар (assignid, userid)
                        // получим id подразделения, в котором создан шаблон
                        foreach($assignids[$val->assignment] as $achiev)
                        {
                            $departmentid = $this->get_departmentid_by_catid($achiev['achievementcatid']);
                            // получим все дочерние подразделения
                            $departmentlist = $this->dof->storage('departments')->get_departments($departmentid);
                            // получим объект персоны в деканате
                            $person = $this->dof->storage('persons')->get_by_moodleid($val->userid);
                            // если персона есть в подразделении или в дочернем подразделении
                            if( isset($person->departmentid) && in_array($person->departmentid, array_merge(array_keys($departmentlist), (array)$departmentid)) )
                            {
                                // будем добавлять для нее достижения
                                $checkaddedachievementins[] = [$achievements[$achiev['achievementid']]->id, $val->userid];
                            }
                        }
                    }
                    // посмотрим какие из потенциальных достижений уже добавлены
                    $addedachievementinstances = $this->get_achievementins_already_added($checkaddedachievementins);
                    if( $addedachievementinstances )
                    {// Если есть уже добавленные достижения
                        foreach($addedachievementinstances as $addedachievementins)
                        {// сформируем массив из пар (assignid, userid) по уже добавленным достижениям
                            $alreadyaddedachievementins[] = [$addedachievementins->achievementid, $this->dof->storage('persons')->get($addedachievementins->userid)->mdluser];
                        }
                    }
                    foreach($alreadyaddedachievementins as $key => $val)
                    {// Найдем уже добавленные достижения в потенциальных
                        $foundkey = array_search($val, $checkaddedachievementins);
                        if( $foundkey !== false)
                        {// и выбросим их из потенциальных
                            unset($checkaddedachievementins[$foundkey]);
                        }
                    }
                    if( ! empty($checkaddedachievementins) )
                    {// Если остались потенциальные достижения на добавление
                        foreach($checkaddedachievementins as $val)
                        {// для каждого из них сформируем массив для записи в БД
                            $pathnamehashes = [];
                            $achievementid = $val[0];
                            $userid = $val[1];
                            $moderationid = null;
                            $timecreated = time();
                            $timechecked = 0;
                            /******    userpoints start    ******/
                            $data = unserialize($achievements[$achievementid]->data);
                            $course = $data['simple_data']['course'];
                            $assignment = $data['simple_data']['assignment'];
                            $assign_instance = $this->dof->modlib('ama')
                            ->course($course)
                            ->get_instance_object(
                                'assign',
                                $assignment,
                                $course
                                )->get_manager();
                                $points = $achievements[$achievementid]->points;
                                $consider = $data['simple_data']['consider'];
                                $grade_percentage = $assign_instance->get_grade_percentage($userid);
                                if( ! empty($consider) )
                                {// Если нужно учитывать оценку при расчете баллов за достижение
                                    $userpoint = floatval($points) * floatval(str_replace(' %', '', $grade_percentage)) / 100;
                                } else
                                {// Если не нужно учитывать оценку при расчете баллов за достижение
                                    $userpoint = floatval(str_replace(' %', '', $grade_percentage)) / 100;
                                }
                                /******    userpoints end    ******/
    
                                /******    data start    ******/
                                $category = $data['simple_data']['category'];
                                $addtoindex = $data['simple_data']['add_to_index'];
                                $significant = 0;
                                $userfiles = $assign_instance->get_files_by_userid($userid);
                                if( !empty($userfiles) )
                                {
                                    foreach($userfiles as $file)
                                    {
                                        $files[] = $file->get_itemid();
                                    }
                                } else
                                {
                                    $files = [];
                                }
                                // Получим идентификатор отправки задания (для получения текста при отправке в Антиплагиат)
                                $submission = $assign_instance->get_submission($userid);
                                if( $submission )
                                {
                                    $submissionid = $submission->id;
                                } else
                                {
                                    $submissionid = 0;
                                }
                                $data = [
                                    'category' => $category,
                                    'course' => $course,
                                    'assignment' => $assignment,
                                    'consider' => $consider,
                                    'significant' => $significant,
                                    'add_to_index' => $addtoindex,
                                    'files' => $files,
                                    'submission' => $submissionid,
                                    'grade' => (string)round(floatval(str_replace(' %', '', $grade_percentage)) / 100, 4)
                                ];
                                /******    data end    ******/
                                $status = 'available';
    
                                if( ! empty($achievementid) &&
                                    ! empty($userid) &&
                                    ! empty($timecreated) &&
                                    ! is_null($timechecked) &&
                                    ! empty($data) &&
                                    ! is_null($status)
                                    )
                                {
                                    $ainsrecord = [
                                        'achievementid' => $achievementid,
                                        'userid' => $this->dof->storage('persons')->get_by_moodleid_id($userid),
                                        'moderationid' => $moderationid,
                                        'timecreated' => $timecreated,
                                        'timechecked' => $timechecked,
                                        'userpoints' => $userpoint,
                                        'data' => serialize($data),
                                        'status' => $status
                                    ];
                                }
                                // Добавляем достижение
                                $returnedid = $this->insert_achievementins($ainsrecord);
                                if( $returnedid )
                                {
                                    if( (boolean)$data['submission'] )
                                    {// Получение файла, созданного из текста задания
                                        $user = $this->dof->modlib('ama')->user($userid)->get();
                                        $filefromtext = $this->dof->sync('achievements')->add_text_to_apru_queue(
                                            $assign_instance,
                                            $user,
                                            $submission,
                                            'apru_files',
                                            'queue_files'
                                        );
                                        if( $filefromtext )
                                        {
                                            // Получение хэша файла, созданого из текста задания
                                            $pathnamehash = $this->dof->modlib('filestorage')->get_pathnamehash($filefromtext);
                                        }
                                    }
                                    
                                    // Добавление файлов в индекс Антиплагиата
                                    if( ! empty($data['files']) )
                                    {
                                        // Получение хэшей загруженных файлов
                                        $pathnamehashes = $assign_instance->get_pathnamehashes($submission->id);
                                        if( ! empty($pathnamehash) )
                                        {
                                            $pathnamehashes[] = $pathnamehash;
                                        }
                                        if( ! empty($pathnamehashes) )
                                        { // Хэшы найдены
                                            // Получение включенных плагинов плагиаризма
                                            $plugins = $this->dof->sync('achievements')->get_plagiarism_plugins_code();
                                            foreach($plugins as $plugincode => $plugin)
                                            {
                                                if( ! empty($data['add_to_index']) )
                                                { // Требуется добавить файлы критерия в индекс
                                                    foreach($pathnamehashes as $pathnamehash)
                                                    {
                                                        $this->dof->sync('achievements')->plagiarism_add_to_index_file($plugincode, $pathnamehash);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $this->dof->mtrace(2, "Добавлено достижение с id=$returnedid\n");
                                }

                                // Убьем найденные файлы
                                $files = [];
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Ищет удаленные задания, по которым есть достижения не в метастатусе junk и переводит их в статус deleted
     */
    public function delete_achievementinstances() {
        $allachievementinswasdeleted = true;
        $achievementinsidsfordelete = $achievementidsfordelete = $ids = [];
        $assign_instance = $this->dof->modlib('ama')
        ->course(false)
        ->get_instance_object(
            'assign',
            false,
            false
        )
        ->get_manager();
        $achievementinstances = $this->dof->storage($this->code())->get_achievementins_by_type('assignment');
        if( $achievementinstances )
        {// Если нашли достижения
            foreach($achievementinstances as $achievementinsid => $achievementins)
            {// узнаем по каким заданиям эти достижения
                $data = unserialize($achievementins->data);
                $assignids[$data['simple_data']['assignment']][] = $achievementinsid;
            }
            if( ! empty($assignids) )
            {
                // получим чистый массив id задания, по которым есть достижения
                $achievementassignids = array_keys($assignids);
                // выберем по полученным id задания, которые реально есть в системе
                $assings = $assign_instance->get_assing($achievementassignids);
                if( $assings )
                {// если нашли
                    $realassignids = array_keys($assings);
                    // то на удаление пойдут те, которых в системе нет
                    $deletedassignids = array_diff($achievementassignids, $realassignids);
                } else 
                {// если не нашли, то на удаление пойдут все
                    $deletedassignids = array_keys($assignids);
                }
                if( ! empty($deletedassignids) ) 
                {
                    foreach($deletedassignids as $id) 
                    {// получим массив id достижений по id заданий
                        $achievementinsidsfordelete = array_merge($achievementinsidsfordelete, $assignids[$id]);
                    }
                    if( ! empty($achievementinsidsfordelete) ) 
                    {
                        foreach($achievementinsidsfordelete as $id) 
                        {// и удалим эти достижения
                            if( $this->dof->workflow('achievementins')->change($id, 'deleted') ) 
                            {
                                $this->dof->mtrace(2, "Статус для достижения с id=$id изменен на deleted\n");
                                $allachievementinswasdeleted = $allachievementinswasdeleted && true;
                            } else 
                            {
                                $this->dof->mtrace(2, "Не удалось сменить статус для достижения с id=$id\n");
                                $allachievementinswasdeleted = $allachievementinswasdeleted && false;
                            }
                        }
                        // получим список шаблонов достижений, которые надо удалить
                        $achievementsfordelete = $this->get_achievements_by_achievementinsid($achievementinsidsfordelete);
                        if( ! empty($achievementsfordelete) && $allachievementinswasdeleted ) 
                        {// удаляем, если смогли удалить все достижения, 
                            //чтобы в базе не болтались достижения не привязанные к шаблону
                            foreach($achievementsfordelete as $achievementfordelete) 
                            {
                                if( $this->dof->workflow('achievements')->change($achievementfordelete->achievementid, 'deleted') ) 
                                {
                                    $this->dof->mtrace(2, "Статус для шаблона достижения с id=$achievementfordelete->achievementid изменен на deleted\n");
                                } else 
                                {
                                    $this->dof->mtrace(2, "Не удалось сменить статус для шаблона достижения с id=$achievementfordelete->achievementid\n");
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // Далее запускаем процесс удаления шаблонов без достижений
        
        // Получаем список шаблонов
        $achievements = $this->get_achievements_by_type('assignment');
        if( $achievements ) 
        {
            // Получаем список заданий
            $assigns = $assign_instance->get_assigns();
            if( $assigns ) 
            {// Если есть задания
                // Получаем массив id заданий
                $assignids = array_keys($assigns);
                foreach ($achievements as $achievement) 
                {
                    // Для каждого шаблона смотрим, есть ли задание
                    $data = unserialize($achievement->data);
                    if( ! in_array($data['simple_data']['assignment'], $assignids) ) 
                    {// если нет - запишем id шаблона на удаление
                        $ids[] = $achievement->id;
                    }
                }
            } else 
            {// Если заданий нет, значит надо удалять все
                $ids = array_keys($achievements);
            }
            if( ! empty($ids) ) 
            {
                foreach($ids as $id) 
                {// Проверяем, а есть ли по шаблону достижения
                    $achievementins = $this->dof->storage('achievementins')->get_records([
                        'achievementid' => $id
                    ]);
                    if( $achievementins ) 
                    {// Если есть - не удаляем шаблон, ждем следующего крона и удаления всех достижений
                        continue;
                    } else 
                    {// Если нет - удаляем шаблон
                        if( $this->dof->workflow('achievements')->change($id, 'deleted') ) 
                        {
                            $this->dof->mtrace(2, "Статус для шаблона достижения с id=$id изменен на deleted\n");
                        } else 
                        {
                            $this->dof->mtrace(2, "Не удалось сменить статус для шаблона достижения с id=$id\n");
                        }
                    }
                }
            }
        }
    }
    
    public function check_relevance()
    {
        // Получим 100 достижений типа assignment с timechecked не выше суток от текущего момента времени
        $achievementinstances = $this->get_achievementins_by_type('assignment', 0, $this->get_limit(), time() - 60 * 60 * 24);
        if( $achievementinstances )
        {// Если такие нашлись
            foreach($achievementinstances as $id => $achievementins)
            {
                // Инициализация начальных данных
                $achievementdata = unserialize($achievementins->data);
                $achievementinsdata = unserialize($achievementins->adata);
                $assign_instance = $this->dof->modlib('ama')
                ->course($achievementdata['simple_data']['course'])
                ->get_instance_object(
                    'assign',
                    $achievementdata['simple_data']['assignment'],
                    $achievementdata['simple_data']['course']
                )
                ->get_manager();
                // Получаем id пользователя moodle
                $userid = $this->dof->storage('persons')->get($achievementins->userid)->mdluser;
                // Проверяем не сменилось ли задание в шаблоне
                $newassign = $this->check_assign_relevance($achievementdata, $achievementinsdata);
                if( $newassign )
                {// Если сменилось
                    // Проверяем не удалено ли новое задание из системы
                    $newassignobj = $assign_instance->get_assing((array)$newassign);
                    if( $newassignobj )
                    {// Если не удалено
                        // Проверим, есть ли шаблоны с таким заданием
                        $achievements = $this->get_achievements_by_assign($newassign);
                        if( empty($achievements) )
                        {// Если нет
                            // Получаем оценку по новому заданию
                            $usergrade = $assign_instance->get_user_grades($userid);
                            if( ! empty($usergrade[$userid]->rawgrade) &&
                                ! is_null($usergrade[$userid]->rawgrade) &&
                                (int)$usergrade[$userid]->rawgrade != -1
                            )
                            {// Если оценка есть
                                // Формируем данные для обновления достижения
                                $files = $assign_instance->get_files_by_userid($userid);
                                if( !empty($files) )
                                {
                                    foreach($files as $file)
                                    {
                                        $userfiles['files'][] = $file->get_itemid();
                                    }
                                }
                                // Получим идентификатор отправки задания (для получения текста при отправке в Антиплагиат)
                                $submission = $assign_instance->get_submission($userid);
                            
                                $newadata = [
                                    'category' => $achievementdata['simple_data']['category'],
                                    'course' => $achievementdata['simple_data']['course'],
                                    'assignment' => $achievementdata['simple_data']['assignment'],
                                    'consider' => $achievementdata['simple_data']['consider'],
                                    'significant' => $achievementdata['simple_data']['significant'],
                                    'add_to_index' => $achievementdata['simple_data']['add_to_index'],
                                    'files' => $userfiles,
                                    'submission' => $submission ? $submission->id : 0,
                                    'grade' => (string)round(floatval(str_replace(' %', '', $assign_instance->get_grade_percentage($userid))) / 100, 4)
                                ];
                            
                                $dataobject = new stdClass();
                                $dataobject->id = $id;
                                $dataobject->data = serialize($newadata);
                                if( ! empty($achievementdata['simple_data']['consider']) )
                                {
                                    $dataobject->userpoints = (float)$newadata['grade'] * (float)$achievementins->points;
                                } else
                                {
                                    $dataobject->userpoints = (float)$achievementins->points;
                                }
                                $dataobject->timechecked = time();
                                // Обновляем достижение
                                if( $this->dof->storage('achievementins')->update($dataobject) )
                                {
                                    $this->dof->mtrace(2, "Достижение с id=$id обновлено\n");
                                }
                                // Меняем статус
                                if( $this->dof->workflow('achievementins')->change($id, 'available') )
                                {
                                    $this->dof->mtrace(2, "Достижение с id=$id обновлено (изменен статус на available)\n");
                                }
                            } else
                            {// Если оценки нет
                                $dataobject = new stdClass();
                                $dataobject->id = $id;
                                $dataobject->timechecked = time();
                                // Обновляем достижение
                                if( $this->dof->storage('achievementins')->update($dataobject) )
                                {
                                    $this->dof->mtrace(2, "Достижение с id=$id обновлено\n");
                                }
                                // Старое достижение не актуально, т.к. сменилось задание в шаблоне
                                // удаляем его
                                if( $this->dof->workflow('achievementins')->change($id, 'deleted') )
                                {
                                    $this->dof->mtrace(2, "Достижение с id=$id обновлено (изменен статус на deleted)\n");
                                }
                            }
                        } else 
                        {// Если уже существуют шаблоны с такими заданиями
                            // Получим все достижения по этому шаблону
                            $achievementinstancesfordelete = $this->dof->storage('achievementins')->get_achievementins($achievementins->achievementid, null, ['metastatus' => 'real']);
                            if( $achievementinstancesfordelete )
                            {
                                $allachievementinswasdeleted = true;
                                foreach($achievementinstancesfordelete as $id => $achievementinsfordelete)
                                {
                                    $dataobject = new stdClass();
                                    $dataobject->id = $id;
                                    $dataobject->timechecked = time();
                                    // Обновляем достижение
                                    if( $this->dof->storage('achievementins')->update($dataobject) )
                                    {
                                        $this->dof->mtrace(2, "Достижение с id=$id обновлено\n");
                                    }
                                    // удаляем достижение
                                    if( $this->dof->workflow('achievementins')->change($id, 'deleted') )
                                    {
                                        $this->dof->mtrace(2, "Статус для достижения с id=$id изменен на deleted\n");
                                        $allachievementinswasdeleted = $allachievementinswasdeleted && true;
                                    } else
                                    {
                                        $this->dof->mtrace(2, "Не удалось сменить статус для достижения с id=$id\n");
                                        $allachievementinswasdeleted = $allachievementinswasdeleted && false;
                                    }
                                }
                                if( $allachievementinswasdeleted )
                                {// если удалили все достижения по шаблону
                                    // удалим и сам шаблон
                                    if( $this->dof->workflow('achievements')->change($achievementins->achievementid, 'deleted') )
                                    {
                                        $this->dof->mtrace(2, "Статус для шаблона достижения с id=$achievementins->achievementid изменен на deleted\n");
                                    } else
                                    {
                                        $this->dof->mtrace(2, "Не удалось сменить статус для шаблона достижения с id=$achievementins->achievementid\n");
                                    }
                                }
                            }
                        }
                    }
                } else 
                { // Если задание не изменилось
                    // Проверим, не изменилась ли оценка за задание
                    $newgrade = $this->check_grade_relevance($achievementinsdata, $userid);
                    if( $newgrade !== false ) 
                    { // Если изменилась
                      // Подготовим данные для обновления
                        $dataobject = new stdClass();
                        $dataobject->id = $id;
                        $achievementinsdata['grade'] = $newgrade;
                        $dataobject->data = serialize($achievementinsdata);
                        if( ! empty($achievementdata['simple_data']['consider']) ) 
                        {
                            $dataobject->userpoints = (float) $newgrade * (float) $achievementins->points;
                        } else 
                        {
                            $dataobject->userpoints = (float) $achievementins->points;
                        }
                        $dataobject->timechecked = time();
                        // Обновим данные
                        if( $this->dof->storage('achievementins')->update($dataobject) )
                        {
                            $this->dof->mtrace(2, "Достижение с id=$id обновлено\n");
                        }
                        // Меняем статус
                        if( $this->dof->workflow('achievementins')->change($id, 'available') )
                        {
                            $this->dof->mtrace(2, "Достижение с id=$id обновлено (изменен статус на available)\n");
                        }
                    } else 
                    {// Если не зависят от оценки
                        if( $achievementins->status == 'suspend' ) 
                        { // Если требуется актуализация
                            $dataobject = new stdClass();
                            $dataobject->id = $id;
                            $dataobject->timechecked = time();
                            // Обновим данные
                            if( $this->dof->storage('achievementins')->update($dataobject) )
                            {
                                $this->dof->mtrace(2, "Достижение с id=$id обновлено\n");
                            }
                            // Меняем статус
                            if( $this->dof->workflow('achievementins')->change($id, 'available') ) 
                            {
                                $this->dof->mtrace(2, "Достижение с id=$id обновлено (изменен статус на available)\n");
                            }
                        }
                    }
                    // Отправим в индекс Антиплагиата, если есть что отправить
                    if( ((boolean)$achievementinsdata['submission'] || 
                        ! empty($achievementinsdata['files'])) && 
                        ! empty($achievementdata['simple_data']['add_to_index']) )
                    {
                        // Получим идентификатор отправки задания (для получения текста при отправке в Антиплагиат)
                        $submission = $assign_instance->get_submission($userid);
                        $this->dof->sync('achievements')->process_add_to_apru_index($assign_instance, $userid, $submission);
                    }
                }
            }
        }  
    }
    
    /**
     * Получить все шаблоны достижений с типом $type
     * @param string $type  тип шаблона достижений
     * @return array массив объектов
     */
    public function get_achievements_by_type($type)
    {
        $additionalwhere = '';
        if( $this->dof->plugin_exists('workflow', 'achievements') )
        {
            $junkstatuses = $this->dof->workflow('achievements')->get_meta_list('junk');
        } else
        {
            $junkstatuses = ['deleted'];
        }
        $params = [$type];
        foreach($junkstatuses as $status => $name)
        {
            $params[] = $status;
        }
        $params[] = 'draft';
        $additionalwhere = str_repeat('status!=? AND ', count($junkstatuses)-1) . 'status!=? AND status!=?';
        $sql = 'SELECT * FROM {block_dof_s_achievements} a
            WHERE a.type=? AND ('  . $additionalwhere . ')';
        return $this->dof->storage($this->code())->get_records_sql($sql, $params);
    }
    
    /**
     * Добавляет достижения
     * @param array $value массив данных для вставки в block_dof_s_achievementins
     */
    public function insert_achievementins($value)
    {
        if( empty($value) )
        {
            return [];
        }
        global $DB;
        return $DB->insert_record('block_dof_s_achievementins', $value);
    }
    
    /**
     * Получить существующие достижения по id шаблона и userid в любом статусе кроме метастатусов
     * (отметим, что id шаблона и userid однозначно идентифицируют достижение, так как оно может быть только одно)
     * @param array $values массив вида [][achievementid, userid]
     */
    public function get_achievementins_already_added($values)
    {
        if( empty($values) )
        {
            return [];
        }
        foreach($values as $k => $v)
        {
            $values[$k][1] = $this->dof->storage('persons')->get_by_moodleid_id($v[1]);
        }
        global $DB;
        $additionalwhere = '';
        if( $this->dof->plugin_exists('workflow', 'achievements') )
        {
            $junkstatuses = $this->dof->workflow('achievements')->get_meta_list('junk');
        } else
        {
            $junkstatuses = ['deleted'];
        }
        $params = [];
        foreach($junkstatuses as $status => $name)
        {
            $params[] = $status;
        }
        $additionalwhere = str_repeat('status!=? AND ', count($junkstatuses)-1) . 'status!=?';
        foreach($values as $value)
        {
            $toselect[] = '(' . implode(',', $value) . ')';
        }
        $sql = 'SELECT * FROM {block_dof_s_achievementins}
            WHERE (achievementid, userid)
            IN (' . implode(',', $toselect) . ') AND (' . $additionalwhere . ')';
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * Получить id шаблонов по id достижений
     * @param array $ids массив id достижений
     */
    public function get_achievements_by_achievementinsid($ids)
    {
        if( empty($ids) )
        {
            return [];
        }
        $sql = 'SELECT id, achievementid
            FROM {block_dof_s_achievementins}
            WHERE id IN (' . implode(',', (array)$ids) . ')
            GROUP BY achievementid';
        return $this->dof->storage('achievementins')->get_records_sql($sql);
    }
    
    /**
     * 
     * @param unknown $type
     * @param number $limitfrom
     * @param number $limitnum
     * @param unknown $timechecked
     */
    public function get_achievementins_by_type($type, $limitfrom = 0, $limitnum = 0, $timechecked = null)
    {
        $additionalwhere = '';
        // Получим список достижений
        if( $this->dof->plugin_exists('workflow', 'achievements') )
        {
            $ajunkstatuses = $this->dof->workflow('achievements')->get_meta_list('junk');
        } else
        {
            $ajunkstatuses = ['deleted'];
        }
        if( $this->dof->plugin_exists('workflow', 'achievementins') )
        {
            $ainsjunkstatuses = $this->dof->workflow('achievementins')->get_meta_list('junk');
        } else
        {
            $ainsjunkstatuses = ['deleted'];
        }
        $params = [$type];
        foreach($ajunkstatuses as $status => $name)
        {
            $params[] = $status;
        }
        foreach($ainsjunkstatuses as $status => $name)
        {
            $params[] = $status;
        }
        $additionalwhere = str_repeat('a.status!=? AND ', count($ajunkstatuses)-1) . 'a.status!=? AND ' . str_repeat('ains.status!=? AND ', count($ainsjunkstatuses)-1) . 'ains.status!=?';
        if( $timechecked )
        {
            $params[] = $timechecked;
            $timechecked = ' AND ains.timechecked < ?';
        } else 
        {
            $timechecked = '';
        }
        $sql = 'SELECT ains.id, ains.achievementid, a.data, ains.data adata, a.points, ains.userid, ains.status
            FROM {block_dof_s_achievementins} ains
            JOIN {block_dof_s_achievements} a
            ON ains.achievementid=a.id
            WHERE a.type=? AND ('  . $additionalwhere . ')' . $timechecked;
        return $this->dof->storage('achievementins')->get_records_sql($sql, $params, $limitfrom, $limitnum);
    }
    
    public function check_assign_relevance($data, $adata)
    {
        if( $data['simple_data']['assignment'] == $adata['assignment'] )
        {
            return false;
        } else 
        {
            return $data['simple_data']['assignment'];
        }
    }
    
    public function check_grade_relevance($adata, $userid)
    {
        $oldgrade = $adata['grade'];
        $assign_instance = $this->dof->modlib('ama')
        ->course($adata['course'])
        ->get_instance_object(
            'assign',
            $adata['assignment'],
            $adata['course']
        )
        ->get_manager();
        $newgrade = (string)round(floatval(str_replace(' %', '', $assign_instance->get_grade_percentage($userid))) / 100, 4);
        if( $oldgrade == $newgrade )
        {
            return false;
        } else 
        {
            return $newgrade;
        }
    }
    
    protected function get_limit()
    {
        return 100;
    }
    
    public function get_achievements_by_assign($assignid)
    {
        $result = [];
        if( empty($assignid) )
        {
            return $result;
        }
        $achievements = $this->get_achievements_by_type('assignment');
        if( $achievements )
        {
            foreach($achievements as $id => $achievement)
            {
                $data = unserialize($achievement->data);
                if( $data['simple_data']['assignment'] == $assignid )
                {
                    $result[] = $achievement->id;
                }
            }
        }
        return $result;
    }
    
    /**
     * Получает id подразделения по id категории шаблона
     * @param int $catid id категории шаблона
     * @return int|false id подразделения или false в случае неудачи
     */
    public function get_departmentid_by_catid($catid)
    {
        if( empty($catid) )
        {
            return false;
        }
        $achievementcat = $this->dof->storage('achievementcats')->get_record(['id' => $catid]);
        if( $achievementcat )
        {
            return (int)$achievementcat->departmentid;
        } else 
        {
            return false;
        }
    }
    
    /**
     * Получить список разделов и шаблонов целей
     *
     * @param $id - ID - элемента-родителя
     * @param $level - Уровень вложенности
     *
     * @return object - Объект с данными состава разделов
     */
    public function get_goalsselect_list($id = 0, $level = 0, $options=[], $forpersonid = null)
    {
        $result = new stdClass();
        $result->categories = [];
        $result->achievements = [];
        
        $mediumresult = $this->get_achievementselect_list($id, $level, $options);
        foreach ( $mediumresult->achievements as $catid => $achs )
        {
            foreach ( $achs as $id => $achname )
            {
                if ( $this->dof->storage('achievementins')->is_access_goal('create', $id, $forpersonid, null, null, false) ) 
                {
                    if ( ! array_key_exists($catid, $result->categories) )
                    {
                        $result->categories[$catid] = $mediumresult->categories[$catid];
                    }
                    $result->achievements[$catid][$id] = $achname;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Получить список разделов и шаблонов
     *
     * @param $id - ID - элемента-родителя
     * @param $level - Уровень вложенности
     *
     * @return object - Объект с данными состава разделов
     */
    public function get_achievementselect_list($id = 0, $level = 0, $options = [])
    {
        global $USER;
        
        $result = new stdClass();
        $result->categories = [];
        $result->achievements = [];
                
        // Получение списка дочерних разделов
        $statuses = $this->dof->workflow('achievementcats')->get_meta_list('active');
        $statuses = array_keys($statuses);
        $subcategories = (array)$this->dof->storage('achievementcats')->
        get_records(['status' => $statuses, 'parentid' => $id], ' sortorder DESC, id DESC ', 'id, name');
        
        // Отступ
        $shift = str_pad('', $level, '-');
        
        if ( $subcategories )
        {// Разделы найдены
            
            $hassubcategories = false;
            
            // Добавление данных каждого подраздела
            foreach ( $subcategories as $subcategory )
            {
                // Получение массива дочерних разделов
                $childrens = $this->get_achievementselect_list($subcategory->id, $level + 1, $options);
                if ( ! empty($childrens->categories) )
                {// Доступные разделы найдены
                    $hassubcategories = true;
                    
                    // Добавление данных
                    $result->categories = $result->categories + $childrens->categories;
                    $result->achievements = $result->achievements + $childrens->achievements;
                }
            }
            
            
            
            // Получение текущего раздела
            $category = $this->dof->storage('achievementcats')->get($id);
            if ( $category )
            {// Раздел найден
                // Проверка на право использовать раздел
                if( $this->dof->storage('achievementcats')->is_access('use:any')
                    || $this->dof->storage('achievementcats')->is_access('use', $category->id, null, $category->departmentid) )
                {// Право использовать раздел
                    
                    // Добавление текущего раздела
                    $result->categories[$category->id] = $shift.$category->name;
                    
                    // Флаг наличия доступных шаблонов в разделе
                    $hasachievements = false;
                    
                    // Добавление шаблонов текущего раздела
                    // Получим массив статусов шаблонов
                    $astatuses = $this->dof->workflow('achievements')->get_meta_list('active');
                    $astatuses = array_keys($astatuses);
                    $catachievementsoptions = [
                        'status' => $astatuses,
                        'catid' => $category->id
                    ];
                    if( ! empty($options['scenario']) )
                    {
                        $catachievementsoptions['scenario'] = $options['scenario'];
                    }
                    $catachievements = (array)$this->dof->storage('achievements')->get_records(
                        $catachievementsoptions,
                        ' sortorder DESC, id DESC ', 'id, name'
                        );
                    
                    foreach ( $catachievements as $catachievement )
                    {
                        // Проверка прав доступа на создание достижения на основе шаблона
                        $access = $this->dof->im('achievements')->
                        is_access('achievement/use', $catachievement->id);
                        if ( $access )
                        {// Право есть
                            // Добавление шаблона в список
                            $result->achievements[$category->id][$catachievement->id] = $catachievement->name;
                        }
                    }
                    // Добавление раздела в список
                    $result->categories[$category->id] = $shift.$category->name;
                } elseif ( $hassubcategories )
                {// Доступ к использованию раздела закрыт
                    $result->categories['0'.$id] = $shift.$category->name;
                }
            } elseif ( $hassubcategories && $id > 0 )
            {// Найдены подразделы, но текущий раздел не найден
                $result->categories[$id] = $shift.$this->dof->get_string(
                    'error_achievementcat_not_found',
                    'achievements'
                    );
            }
        } else
        {// Дочерние разделы не найдены
            
            // Получение текущего раздела
            $category = $this->dof->storage('achievementcats')->get($id);
            if ( $category )
            {// Раздел найден
                
                // Проверка на право использовать раздел
                if( $this->dof->storage('achievementcats')->is_access('use:any')
                    || $this->dof->storage('achievementcats')->is_access('use', $category->id, null, $category->departmentid) )
                {// Право использовать раздел
                    
                    // Флаг наличия доступных шаблонов в разделе
                    $hasachievements = false;
                    
                    // Добавление шаблонов текущего раздела
                    // Получим массив статусов шаблонов
                    $astatuses = $this->dof->workflow('achievements')->get_meta_list('active');
                    $astatuses = array_keys($astatuses);
                    $catachievementsoptions = [
                        'status' => $astatuses,
                        'catid' => $category->id
                    ];
                    if( ! empty($options['scenario']) )
                    {
                        $catachievementsoptions['scenario'] = $options['scenario'];
                    }
                    $catachievements = (array)$this->dof->storage('achievements')->get_records(
                        $catachievementsoptions,
                        ' sortorder DESC, id DESC ', 'id, name'
                        );
                    foreach ( $catachievements as $catachievement )
                    {
                        // Проверка прав доступа на создание достижения на основе шаблона
                        $access = $this->dof->im('achievements')->is_access('achievement/use', $catachievement->id, null, $category->departmentid);
                        if ( $access )
                        {// Право есть
                            $hasachievements = true;
                            
                            // Добавление шаблона в список
                            $result->achievements[$category->id][$catachievement->id] = $catachievement->name;
                        }
                    }
                    if ( $hasachievements )
                    {// Добавление раздела в список
                        $result->categories[$category->id] = $shift.$category->name;
                    }
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Формирование маски сценария шаблона по настройкам
     *
     * @param bool $achievementfirst - разрешено добавление достижения напрямую, без формирования цели
     * @param bool $goalfirst - разрешено ли добавлять цели
     * @param bool $goalneedapproval - требует ли цель одобрения
     *
     * @return int - маска сценария шаблона
     */
    public function prepare_scenario_bitmask($achievementfirst, $goalfirst, $goalneedapproval)
    {
        $bitmask = 0;
        
        if ( ! empty($achievementfirst) )
        {
            $bitmask |= 0b0001;
        }
        if ( ! empty($goalfirst) )
        {
            $bitmask |= 0b0010;
        }
        if ( ! empty($goalneedapproval) )
        {
            $bitmask |= 0b0100;
        }
        
        return $bitmask;
    }
    
    /**
     * Проверка введенных администратором настроек шаблона: известен ли нам настроенный сценарий
     * 
     * @param bool $achievementfirst - разрешено добавление достижения напрямую, без формирования цели
     * @param bool $goalfirst - разрешено ли добавлять цели
     * @param bool $goalneedapproval - требует ли цель одобрения
     * 
     * @return boolean - известен ли системе настроенный сценарий
     */
    public function is_known_scenario($achievementfirst, $goalfirst, $goalneedapproval)
    {
        $bitmask = $this->prepare_scenario_bitmask($achievementfirst, $goalfirst, $goalneedapproval);
        
        switch($bitmask)
        {
            case 0b0001: 
                // 1
                // разрешено добавление достижений напрямую
                // запрещено добавление целей
                return true;
                break;
            case 0b0010:
                // 2
                // запрещено добавление достижений напрямую (админ хочет, чтобы все шло через цели)
                // разрешено добавление целей
                // согласование цели не требуется
                return true;
                break;
            case 0b0011:
                // 3
                // разрешено добавление достижений напрямую
                // разрешено добавление целей
                // согласование цели не требуется
                return true;
                break;
            case 0b0110:
                // 6
                // запрещено добавление достижений напрямую (потому что требуется согласование цели)
                // разрешено добавление целей
                // требуется согласование цели
                return true;
                break;
            case 0b0111:
                // 7
                // разрешено добавление достижений напрямую
                // разрешено добавление целей
                // требуется согласование цели
                //
                // странный кейс, но сделан не по ошибке, так и задумано!
                return true;
                break;
            default:
                return false;
                break;
        }
    }
    
    /**
     * Проверка возможности добавления достижения по сценарию
     * 
     * @param int $scenario
     * 
     * @return bool
     */
    public function is_achievement_add_allowed($scenario)
    {
        return (((int)$scenario & 0b00001) == 0b00001);
    }
    
    /**
     * Проверка возможности добавления цели по сценарию
     *
     * @param int $scenario
     *
     * @return bool
     */
    public function is_goal_add_allowed($scenario)
    {
        return (((int)$scenario & 0b00010) == 0b00010);
    }
    
    /**
     * Проверка требования одобрения по сценарию
     *
     * @param int $scenario
     *
     * @return bool
     */
    public function is_approval_required($scenario)
    {
        return (((int)$scenario & 0b00100) == 0b00100);
    }
    
    /**
     * обработка дедлайна
     *
     * @param string $code
     * @param int $achievementid
     *
     * @return void
     */
    public function storage_deadline_process($code, $achievementid)
    {
        $info = explode('_', $code);
        if ( empty($code[0]) || empty($code[1]) )
        {
            return true;
        }
        $achievement = $this->get($achievementid);
        if ( empty($achievement) )
        {
            return true;
        }
        
        if ( $code == 'stat_periodic' )
        {
            // отправка статистика о неодобренных и неподтвержденных достижениях
            return $this->send_stat_message($achievement);
        }
        
        return true;
    }
    
    /**
     * массив объявленных уведомлений
     *
     * @return string[]
     */
    public function registered_notification_types()
    {
        $messager = $this->dof->modlib('messager');
        return [
            // уведомления о наличии неодобренных и неподтвержденных достижениях
            'achievement_stat' => $messager::MESSAGE_PROVIDER_URGENT,
        ];
    }
    
    /**
     * отправка уведомления
     *
     * @param string $code - полный код
     * @param string $torole - user/curator
     * @param stdClass $achievementin
     * 
     * @return bool
     */
    protected function send_stat_message(stdClass $achievement)
    {
        // получение категории
        $cat = $this->dof->storage('achievementcats')->get($achievement->catid);
        if ( empty($cat) )
        {
            return true;
        }
        
        // получение информации по сайту
        $sitename = $this->dof->modlib('ama')->course(false)->get_site()->fullname;
        
        // формирование баового сообщения
        $message = new \core\message\message();
        $message->subject = $this->dof->get_string('message_subject_stat_periodic', 'achievements', null, 'storage');
        $message->fullmessageformat = FORMAT_HTML;
        
        // получение конфига с текстом уведомления
        $configtext = $this->dof->storage('config')->get_config_value('notification_stat_periodic', 'storage', 'achievements', $cat->departmentid);
        
        // маска
        // 20,21,22,23,24,25
        $mask = [2,0,1,1,1,2];
        
        // количество достижений к на подтверждение
        $quantitynotmoderated = $this->dof->storage('achievementins')->count_list(['status' => 'notavailable', 'achievementid' => $achievement->id]);
        $type = ($quantitynotmoderated%100 > 4 && $quantitynotmoderated%100 < 20) ? 2 : $mask[min($quantitynotmoderated%10, 5)];
        $string = "notavailable_" . $type;
        $quantitynotmoderatedstring = $this->dof->get_string($string, 'achievements', $quantitynotmoderated, 'storage');
        
        // количество целей, требующих одобрения
        $quantitynotapprovalc = $this->dof->storage('achievementins')->count_list(['status' => 'wait_approval', 'achievementid' => $achievement->id]);
        $type = ($quantitynotapprovalc%100 > 4 && $quantitynotapprovalc%100 < 20) ? 2 : $mask[min($quantitynotapprovalc%10, 5)];
        $string = "wait_approval_" . $type;
        $quantitynotapproval = $this->dof->get_string($string, 'achievements', $quantitynotapprovalc, 'storage');
        
        if ( empty($quantitynotapprovalc) && empty($quantitynotmoderated) )
        {
            // отсутсвуют неодобренные цели и неподтвержденные достижения
            return false;
        }
        
        // формирование строк
        $strings = [
            '{SITENAME}' => $sitename,
            '{ACHIEVEMENTNAME}' => $achievement->name,
            '{QUANTITYNOTMODERATED}' => $quantitynotmoderatedstring,
            '{QUANTITYNOTAPPROVAL}' => $quantitynotapproval,
            '{URL}' => $this->dof->url_im('achievements', '/moderator_panel.php')
        ];
        
        // конфиг, определяющий получателей
        $conf = $this->dof->storage('config')->get_config_value('notificate_onlycatdep_moderators', 'storage', 'achievements', $cat->departmentid);
        if ( ! empty($conf) )
        {
            // получают те, кто могут модерировать шаблон
            $moderators = array_replace($this->dof->storage('acl')->get_persons_acl_by_code_without_hierarchy('im', 'achievements', 'moderation', $cat->departmentid),
                    $this->dof->storage('acl')->get_persons_acl_by_code_without_hierarchy('im', 'achievements', 'achievementins/moderate_category', $cat->departmentid, $cat->id),
                    $this->dof->storage('acl')->get_persons_acl_by_code_without_hierarchy('im', 'achievements', 'achievementins/moderate_except_myself', $cat->departmentid));
        } else 
        {
            // получают те, кто могут модерировать шаблон
            $moderators = array_replace($this->dof->storage('acl')->get_persons_acl_by_code('im', 'achievements', 'moderation', $cat->departmentid),
                    $this->dof->storage('acl')->get_persons_acl_by_code('im', 'achievements', 'achievementins/moderate_category', $cat->departmentid, $cat->id),
                    $this->dof->storage('acl')->get_persons_acl_by_code('im', 'achievements', 'achievementins/moderate_except_myself', $cat->departmentid));
        }
        
        // уникальные получатели
        $uniquerecievers = [];
        if ( ! empty($moderators) )
        {
            foreach ( $moderators as $reciever )
            {
                $uniquerecievers[$reciever->id] = $reciever;
            }
        }
        foreach ( $uniquerecievers as $moderator )
        {
            $message->smallmessage = str_replace(array_merge(array_keys($strings),['{USERFULLNAME}']), array_merge(array_values($strings), [$this->dof->storage('persons')->get_fullname($moderator->id)]), $configtext);
            $message->fullmessage = text_to_html($message->smallmessage, false, false, true);
            $message->fullmessagehtml = $message->fullmessage;
            
            // отправка уведомления
            $this->dof->modlib('messager')->message_send('storage', 'achievements', 'achievement_stat', $moderator->id, $message);
        }
        
        return true;
    }
}   
