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
 * Синхронизация портфолио. Класс плагина.
 *
 * @package    sunс
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_sync_achievements implements dof_sync
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
     * Метод, реализующий инсталяцию плагина в систему
     * Создает или модифицирует существующие таблицы в БД
     * и заполняет их начальными значениями
     *
     * @return boolean
     */
    public function install()
    {
        return true;
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
        return true;
    }
    
    /**
     * Возвращает версию установленного плагина
     * 
     * @return int - Версия плагина
     */
    public function version()
    {
		return 2018041700;
    }
    
    /** 
     * Возвращает версии интерфейса Деканата, с которыми этот плагин может работать
     * 
     * @return string
     */
    public function compat_dof()
    {
        return 'aquarium_bc';
    }

    /**
     * Возвращает версии стандарта плагина этого типа, которым этот плагин соответствует
     * 
     * @return string
     */
    public function compat()
    {
        return 'ancistrus';
    }
    
    /** 
     * Возвращает тип плагина
     * 
     * @return string 
     */
    public function type()
    {
        return 'sync';
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
            'modlib' => [
                'plagiarism'      => 2016041300,
                'nvg'             => 2008060300,
                'widgets'         => 2009050800
            ],
            'storage' => [
                'persons'         => 2015012000,
                'config'          => 2011080900,
                'acl'             => 2011040504
            ],
        ];
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
            'modlib' => [
                'plagiarism'      => 2016041300,
                'nvg'             => 2008060300,
                'widgets'         => 2009050800
            ],
            'storage' => [
                'persons'         => 2015012000,
                'config'          => 2011080900,
                'acl'             => 2011040504
            ],
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
    
    /** 
     * Требуется ли запуск cron в плагине
     * 
     * @return bool
     */
    public function is_cron()
    {
        return true;
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
    public function is_access($do, $objid = NULL, $userid = NULL)
    {
        // Получаем ID персоны, с которой связан данный пользователь 
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);

        $depid = NULL;
        
        switch ( $do )
        {// Определяем дополнительные параметры в зависимости от запрашиваемого права
            default:
                break;
        }
        
        if ( $this->dof->is_access('datamanage') OR
             $this->dof->is_access('admin') OR
             $this->dof->is_access('manage')
           )
        {// Полный доступ для администраторов Moodle
            return true;
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
    public function catch_event($gentype,$gencode,$eventcode,$id,$mixedvar)
    {
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
        if ( $loan == 2 )
        {
            // проверка прохождения курсов существующих неподтвержденных достижений
            $this->process_coursecompletion_achievementins();
        } elseif ( $loan == 3 )
        {
            // фиксация автоматических шаблонов
            $this->process_autocoursecompletion_achievements();
        }
        
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
        
        // Список доступных для портфолио плагинов плагиаризма
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'plagiarism_plugins';
        $obj->value = 'apru';
        $config[$obj->code] = $obj;
    
        return $config;
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
     * @param dof_control $dof - объект ядра деканата
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
    
    // **********************************************
    // Собственные методы
    // **********************************************
    
    /**
     * Получить кода плагинов плагиаризма, доступных в текущем подразделении
     * 
     * @param array $options - Массив дополнительных данных
     *              'departmentid' - Переорпеделение ID подразделения
     */
    public function get_plagiarism_plugins_code($options = [])
    {
        if ( ! isset($options['departmentid']) )
        {// Текущее подразделение
            $options['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        }
        
        $codes = [];
        // Получение включенных плагинов плагиаризма
        $plugins = $this->dof->modlib('plagiarism')->plugins;
        
        // Получение доступных в текущем подразделении плагинов плаигаризма
        $availableplugins = $this->dof->storage('config')->
            get_config_value('plagiarism_plugins', 'sync', 'achievements', $options['departmentid']);
        if ( ! empty($availableplugins) )
        {
            $availableplugins = explode(',', $availableplugins);
            foreach ( $availableplugins as $plugin )
            {
                if ( isset($plugins[$plugin]) )
                {
                    $codes[$plugin] = $plugin;
                }
            }
        }
        
        return $codes;
    }
    
    /**
     * Получить локализованное имя плагина плагиаризма
     */
    public function get_plagiarism_plugin_name($code)
    {
        // Получение включенных плагинов плагиаризма
        $plugins = $this->dof->modlib('plagiarism')->plugins;
        if ( isset($plugins[$code]) )
        {// Плагин найден
            return $plugins[$code]->get_name();
        }
        return '';
    }
    
    /**
     * Добавить файл в индекс указанного плагина плагиаризма
     * 
     * @param string $plugincode - Код плагна
     * @param string $pathnamehash - Хэш пути файла
     */
    public function plagiarism_add_to_index_file($plugincode, $pathnamehash)
    {
        global $DB;
        $plugins = $this->dof->modlib('plagiarism')->plugins;
        if ( isset($plugins[$plugincode]) )
        {// Плагин найден
            $record = $plugins[$plugincode]->get_file_info($pathnamehash, []);
            if( $record )
            {
                $options['additional'] = ['uploadoptions' => ['AddToIndex' => true]];
                $plugins[$plugincode]->update_file($record, $options);
            } else 
            {
                $options['additional'] = ['disable_check' => true, 'uploadoptions' => ['AddToIndex' => true]];
                $plugins[$plugincode]->add_file($pathnamehash, $options);
            } 
        }
    }
    
    /**
     * Создает файл из строки
     * @param stdClass $filerecord объект с параметрами файла
     * @param string $content строка, из которой нужно создать файл
     */
    public function create_file_from_string($filerecord, $content)
    {
        // Получим объект файлового хранилища
        $fs = get_file_storage();
        return $fs->create_file_from_string($filerecord, $content);
    }
    
    /**
     * Создание файлов, содержащих текст задания и сохранение их очередь на отправку в Антиплагиат
     * @param ama_course_instance_assign $assign_instance объект класса для работы с модулем assign
     * @param stdClass $user объект пользователя
     * @param stdClass $submission объект отправки задания
     * @param string $component компонент
     * @param string $filearea файловая зона
     * @return file_storage|null возвращает файл (уже сохраненный или новый созданный)
     */
    public function add_text_to_apru_queue($assign_instance, $user, $submission, $component, $filearea)
    {
        $storedfile = null;
        $cm = $assign_instance->get_cm();
    
        // Получим текст, отправленный в задании
        $content = $assign_instance->get_text_by_submissionid($submission->id);
    
        if( ! empty($content) )
        { // Посмотрим, сохранялись ли уже тексты из задания в виде файлов
            $file = $assign_instance->get_text_from_apru_queue(
                $user->id,
                $component,
                $filearea,
                $submission->id
                );
            if( $file )
            { // Если сохранялись, получим id последнего и для текущего выставим id на единицу больше
                $file = array_shift($file);
                $count = str_replace($cm->id . '_' . $user->id . '_text_', '', $file->filename);
                $count = (int) str_replace('.txt', '', $count);
                $count ++;
            } else
            { // Если не было сохранений, запишем файл с id == 0
                $count = 0;
            }
            if( empty($file) || (! empty($file) && $file->contenthash != sha1($content)) )
            { // Если сохраняем файл впервые или же предыдущий файл не отличается по содержанию
                // подготовим данные для сохранения файла
                $filerecord = new \stdClass();
    
                $filerecord->contextid = $assign_instance->context->id;
                $filerecord->component = $component;
                $filerecord->filearea = $filearea;
                $filerecord->itemid = $submission->id;
                $filerecord->sortorder = 0;
                $filerecord->filepath = '/';
                $filerecord->filename = $cm->id . '_' . $user->id . '_text_' . $count . '.txt';
                $filerecord->timecreated = time();
                $filerecord->timemodified = time();
                $filerecord->userid = $user->id;
                $filerecord->source = null;
                $filerecord->author = fullname($user);
                $filerecord->license = 'allrightsreserved';
                $filerecord->status = 0;
                // и сохраним файл
                $storedfile = $this->dof->sync('achievements')->create_file_from_string($filerecord, $content);
            } else
            { // Если файл не изменился, вернем последний сохраненный файл
                $storedfile = $this->dof->modlib('filestorage')->get_file_instance($file);
            }
        }
        return $storedfile;
    }
    
    public function process_add_to_apru_index($assign_instance, $userid, $submission)
    {
        $pathnamehashes = [];
        // Получение файла, созданного из текста задания
        $user = $this->dof->modlib('ama')
            ->user($userid)
            ->get();
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
                foreach($pathnamehashes as $pathnamehash) 
                {
                    $this->dof->sync('achievements')->plagiarism_add_to_index_file($plugincode, $pathnamehash);
                }
            }
        }
    }
    
    /**
     * проверка прохождения курсов существующих достижений
     * 
     * @return void
     */
    public function process_coursecompletion_achievementins()
    {
        // массив достижений
        $achievements = [];
        
        // получение всех достижений типа прохождение курса
        // в недоступном статусе или в статусе требования актуализации
        $sql = 'SELECT achin.* FROM {block_dof_s_achievementins} as achin
            LEFT JOIN {block_dof_s_achievements} as ach ON ach.id = achin.achievementid
            WHERE achin.status IN (\'notavailable\', \'suspend\', \'wait_completion\') AND ach.type = \'coursecompletion\'';
        $achievementins = $this->dof->storage('achievementins')->get_records_sql($sql);
        foreach ( $achievementins as $achievementin )
        {
            if ( $achievementin->status == 'suspend' )
            {
                // если надо актуализировать, меняем статуса 
                // на notavailable или available
                // проверка подтверждения
                $this->dof->storage('achievementins')->update_achievementin_state($achievementin->id, false);
            } else 
            {
                // если статус wait_completion или notavailable
                // то сначала проверим, пройден ли курс, только затем переведем в нужный статус
                if ( $this->dof->storage('achievementins')->is_completely_confirmed($achievementin->id) )
                {
                    // проверка подтверждения
                    $this->dof->storage('achievementins')->update_achievementin_state($achievementin->id, false);
                }
            }
        }
    }
    
    /**
     * проверка прохождения курсов с включенной настройкой автоматической фиксации
     * 
     * @return void
     */
    public function process_autocoursecompletion_achievements()
    {
        // прохождения курсов пользователей
        $userscoursecompletions = [];
        
        // пользователи деканата
        $dofpersons = [];
        
        // получение шаблонов с автоматической фиксацией
        $achievements = $this->dof->modlib('achievements')->get_manager('achievements')->get_autocoursecompletion_achievements();
        foreach ( $achievements as $achievement )
        {
            // целевые курсы
            $targetcourses = [];
            $data = unserialize($achievement->data);
            
            // установка целевых курсов
            $targetcourses = ! empty($data['coursecompletion_data']['allowed_courses']) ? $data['coursecompletion_data']['allowed_courses'] : [];
            
            // инициализация соединения с реестром синхронизации
            $connect = $this->dof->storage('sync')->createConnect(
                'storage',
                'achievementins',
                'achievement_' . $achievement->id,
                'modlib',
                'ama',
                'coursecompletion');
            
            // получение категории шаблона
            $cat = $this->dof->storage('achievementcats')->get($achievement->catid);
            
            // Получаем пользователей, которые могут использовать шаблон
            $personscanuseachievementids = [];
            $personscanuseachievement = $this->dof->storage('acl')->get_persons_acl_by_code('im', 'achievements', 'achievement/use', $cat->departmentid, $achievement->id);
            foreach($personscanuseachievement as $pacl)
            {
                $personscanuseachievementids[$pacl->id] = $pacl->id;
            }
            ksort($personscanuseachievementids);
            
            // Получаем пользователей, которые могут использовать категорию шаблона
            $personscanusecategoryids = [];
            $personscanusecategory = $this->dof->storage('acl')->get_persons_acl_by_code('im', 'achievements', 'category/use', $cat->departmentid, $cat->id);
            foreach($personscanusecategory as $pacl)
            {
                $personscanusecategoryids[$pacl->id] = $pacl->id;
            }
            ksort($personscanusecategoryids);
            
            // получаем всех пользователей, которым доступен этот шаблон
            $persons = array_intersect_key($personscanuseachievementids, $personscanusecategoryids);
            foreach ( $persons as $person )
            {
                if ( ! array_key_exists($person, $dofpersons) )
                {
                    $dofpersons[$person] = $this->dof->storage('persons')->get($person);
                }
                if ( empty($dofpersons[$person]->mdluser) || ! $this->dof->modlib('ama')->user(false)->is_exists($dofpersons[$person]->mdluser) )
                {
                    // без moodle пользователя нет смысла что-либо искать
                    continue;
                }
                if ( ! array_key_exists($person, $userscoursecompletions) )
                {
                    $userscoursecompletions[$person] = $this->dof->modlib('ama')->course_completion(false)->get_course_completions(null, [$dofpersons[$person]->mdluser]);
                }
                foreach ( $userscoursecompletions[$person] as $completion )
                {
                    if ( ! empty($targetcourses) && ! in_array($completion->course, $targetcourses) )
                    {
                        continue;
                    }
                    
                    // внешний идентификатор
                    $upid = $completion->id;
                    
                    // внутренний идентификатор
                    $formeddownid = $achievement->id . '_' . $person;
                    
                    // получение записи из реестра синхронизации
                    $syncrecord = $connect->getSync(['upid' => $upid, 'downid' => $formeddownid]);
                    if ( ! empty($syncrecord) )
                    {
                        continue;
                    }
                    
                    $hashobject = new stdClass();
                    $hashobject->userid = $completion->userid;
                    $hashobject->courseid = $completion->course;
                    $hashobject->timecompleted = $completion->timecompleted;
                    $hashobject->achievementid = $achievement->id;
                    $hashobject->personid = $person;
                    
                    // вычисления хэша данных внешнего объекта
                    $uphash = $this->dof->storage('sync')->makeHash($hashobject);
                    
                    // объект для создания достижения
                    $achievementinobj = new stdClass();
                    $achievementinobj->achievementid = $achievement->id;
                    $achievementinobj->userid = $person;
                    $achievementinobj->data = serialize(['courseid' => $completion->course, 'coursename' => $this->dof->modlib('ama')->course($completion->course)->get()->fullname]);
                    $downid = $this->dof->storage('achievementins')->save($achievementinobj);
                    if ( $downid )
                    {// операция прошла успешно
                        $opt = $achievementinobj;
                        
                        // фиксация результата в реестре
                        $syncresult = $connect->updateDown($upid, 'create', $uphash, $formeddownid, '', $achievementinobj, false);
                    }
                }
            }
        }
    }
}
