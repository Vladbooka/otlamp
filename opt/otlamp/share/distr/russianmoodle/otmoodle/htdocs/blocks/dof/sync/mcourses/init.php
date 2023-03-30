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


/** Класс для работы с курсами moodle
 * 
 */
class dof_sync_mcourses implements dof_sync
{
    /**
     * @var dof_control $dof - содержит методы ядра деканата
     */
    protected $dof;
    
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
     * 
     * @param string $old_version
     *            - версия установленного в системе плагина
     * @return boolean Может надо возвращать массив с названиями таблиц и
     *         результатами их создания/изменения?
     *         чтобы потом можно было распечатать сообщения о результатах
     *         обновления
     * @access public
     */
    public function upgrade($oldversion)
    {
        if ( $oldversion < 2018022012 )
        {
            // бекапы хранились не по стандарту
            // перемещение в правильную файловую зону
            $this->dof->modlib('filestorage')->replace_files_to_new_filearea('programmitem_coursetemplate', 'im_programmitems_programmitem_coursetemplate');
        }
        
        return true;
    }
    
    /** 
     * Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        return 2018030516;
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
        return 'mcourses';
    }
    /** 
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('modlib' =>  array('ama' => 2009101500));
    }
    /** 
     * Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return [
            ['plugintype' => 'storage',  'plugincode' => 'programmitems', 'eventcode' => 'coursedata_verification_requested'],
            ['plugintype' => 'storage',  'plugincode' => 'programmitems', 'eventcode' => 'coursedata_accepted'],
            ['plugintype' => 'storage',  'plugincode' => 'programmitems', 'eventcode' => 'coursedata_declined'],
            ['plugintype' => 'storage',  'plugincode' => 'programmitems', 'eventcode' => 'insert'],
            ['plugintype' => 'storage',  'plugincode' => 'programmitems', 'eventcode' => 'update'],
            ['plugintype' => 'storage',  'plugincode' => 'programmitems', 'eventcode' => 'delete_backupfile'],
            ['plugintype' => 'storage',  'plugincode' => 'cstreams', 'eventcode' => 'insert'],
            ['plugintype' => 'storage',  'plugincode' => 'cstreams', 'eventcode' => 'update']
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
     * Проверяет полномочия на совершение действий
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     * @access public
     */
    public function is_access($do, $objid = NULL, $userid = NULL)
    {
        // Используем функционал из $DOFFICE
        return $this->dof->is_access($do, NULL, $userid);
    }
    
    /** 
     * 
     * Функция получения настроек для плагина
     * 
     * @param string $code
     * 
     * return stdClass[]
     *
     */
    public function config_default($code = null)
    {
        // название переменной плагина sync/mcategories, где лежит идентификатор категории в Moodle
        // по умолчанию mdlcategoryid1, если необходимо использовать вторую ветку, вручную переименовываем в mdlcategoryid2
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'mdlcategoryvarname';
        $obj->value = 'mdlcategoryid1';
        $config[$obj->code] = $obj;
        
        // идентификатор курса в Moodle, по шаблону которого будут создаваться новые курсы Moodle для дисциплин
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'mdlcourseid_template';
        $obj->value = '0';
        $config[$obj->code] = $obj;
        
        // флаг сохранения всех версий бэкапов
        // при выключении удаляется последняя версия
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'mdlbackups_save_all';
        $obj->value = '1';
        $config[$obj->code] = $obj;
        
        return $config;
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
        global $CFG;
        switch($gentype.'__'.$gencode.'__'.$eventcode)
        {
            case 'storage__programmitems__coursedata_verification_requested':
                if ( file_exists($CFG->dirroot . '/blocks/mastercourse/locallib.php') )
                {
                    require_once($CFG->dirroot . '/blocks/mastercourse/locallib.php');
                    if( function_exists('mastercourse_verification_requested') )
                    {
                        mastercourse_verification_requested($id, $mixedvar);
                    }
                }
                break;
            case 'storage__programmitems__coursedata_accepted':
                if ( file_exists($CFG->dirroot . '/blocks/mastercourse/locallib.php') )
                {
                    require_once($CFG->dirroot . '/blocks/mastercourse/locallib.php');
                    if( function_exists('mastercourse_accepted') )
                    {
                        mastercourse_accepted($id, $mixedvar);
                    }
                }
                break;
            case 'storage__programmitems__coursedata_declined':
                if ( file_exists($CFG->dirroot . '/blocks/mastercourse/locallib.php') )
                {
                    require_once($CFG->dirroot . '/blocks/mastercourse/locallib.php');
                    if( function_exists('mastercourse_declined') )
                    {
                        mastercourse_declined($id, $mixedvar);
                    }
                }
                break;
                
            case 'storage__programmitems__insert':
            case 'storage__programmitems__update':
                
                // отливливаем создание/обновление дисциплины и устанавливаем курс Moodle
                $pitem = $this->dof->storage('programmitems')->get_record(['id' => $mixedvar['new']->id]);
                if ( ! empty($pitem) && ! empty($pitem->name) )
                {
                    $this->process_programmitem($pitem);
                }
                break;
                
            case 'storage__cstreams__insert':
            case 'storage__cstreams__update':
                
                // отливливаем создание/обновление учебного процесса и устанавливаем курс Moodle
                $cstream = $this->dof->storage('cstreams')->get_record(['id' => $mixedvar['new']->id]);
                if ( ! empty($cstream) && ! empty($cstream->name) )
                {
                    $this->process_cstream($cstream);
                }
                break;
            case 'storage__programmitems__delete_backupfile':
                
                // отливливаем событие удаление бэкап файла
                $pitem = $this->dof->storage('programmitems')->get_record(['id' => $id]);
                if ( ! empty($pitem) )
                {
                    if ( $this->delete_backupfile($mixedvar) && ($mixedvar['filename'] == ($pitem->coursetemplateversion.'.mbz')) ) 
                    {
                        $updatepitem = new stdClass();
                        $updatepitem->id = $pitem->id;
                        $updatepitem->coursetemplateversion = 0;
                        $this->dof->storage('programmitems')->update($updatepitem);
                    }
                }
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
        if ( $loan == 3 )
        {
            // восстановление сломанных дисциплин
            $pitems = $this->dof->storage('programmitems')->get_records(['mdlcourse' => -1]);
            foreach ( $pitems as $pitem )
            {
                $this->process_programmitem($pitem);
            }
            
            // восстановление сломанных учебных процессов
            $cstreams = $this->dof->storage('cstreams')->get_records(['mdlcourse' => -1]);
            foreach ( $cstreams as $cstream )
            {
                $this->process_cstream($cstream);
            }
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
    public function todo($code,$intvar,$mixedvar)
    {
        return true;
    }
    
    // **********************************************
    // Собственные методы
    // **********************************************

    /**
     * Обработка дисциплины
     *
     * @param stdClass $pitem
     *
     * @return bool
     */
    protected function process_programmitem(stdClass $pitem)
    {
        if ( (! property_exists($pitem, 'mdlcourse')) || ((int)$pitem->mdlcourse !== -1) )
        {
            // отсутствует свойство mdlcourse
            // или указан идентификатор курса Moodle
            return true;            
        }

        // объект курса Moodle
        // формируем для установки названия и категории
        $courseobj = new stdClass();
        $courseobj->fullname = $pitem->name;
        
        // получение конфига переменной категории в плагина sync/mcategories
        $mdlcatvar = $this->dof->storage('config')->get_config_value('mdlcategoryvarname', 'sync', 'mcourses', $pitem->departmentid);
        
        // получение идентификатора категории
        $categoryid = $this->dof->storage('config')->get_config_value($mdlcatvar, 'sync', 'mcategories', $pitem->departmentid);
        if ( ! empty($categoryid) )
        {
            // укажем категорию курса
            $courseobj->category = $categoryid;
        }
        
        // получение конфига шаблона курса
        $mdlcoursetemplateid = $this->dof->storage('config')->get_config_value('mdlcourseid_template', 'sync', 'mcourses', $pitem->departmentid);
        if ( ! empty($mdlcoursetemplateid) )
        {
            // клонирование курса по шаблону
            $pitem->mdlcourse = $this->dof->sync('mcourses')->clone_course($mdlcoursetemplateid, $courseobj);
        } else
        {
            // создание нового курса в мудл и привязка к дисциплине
            $pitem->mdlcourse = $this->dof->sync('mcourses')->create_course($courseobj);
        }
        
        // обновим запись дисциплины
        if ( ! empty($pitem->mdlcourse) )
        {
            $updatepitem = new stdClass();
            $updatepitem->id = $pitem->id;
            $updatepitem->mdlcourse = $pitem->mdlcourse;
            return $this->dof->storage('programmitems')->update($updatepitem, null, true);
        }
        
        return true;
    }
    
    /**
     * Обработка учебного процесса
     *
     * @param stdClass $cstream
     *
     * @return bool
     */
    protected function process_cstream(stdClass $cstream)
    {
        if ( (! property_exists($cstream, 'mdlcourse')) || ((int)$cstream->mdlcourse !== -1) )
        {
            // отсутствует свойство mdlcourse
            // или указан идентификатор курса Moodle
            return true;
        }
        
        // получение дисциплины учебного процесса
        $pitem = $this->dof->storage('programmitems')->get_record(['id' => $cstream->programmitemid]);
        
        // нет смысла выполнять действия, если у дисциплины нет курса Moodle
        if ( empty($pitem->mdlcourse) )
        {
            return true;
        }
        
        // поиск бэкапа
        $file_options = ['itemid' => $pitem->id, 'filename' => $pitem->coursetemplateversion . '.mbz', 'filearea' => 'im_programmitems_programmitem_coursetemplate'];
        if ( ! $this->dof->modlib('ama')->course($pitem->mdlcourse)->backup_exists($file_options) )
        {
            // бэкап отсутствует
            $updatecstream = new stdClass();
            $updatecstream->id = $cstream->id;
            $updatecstream->mdlcourse = null;
            return $this->dof->storage('cstreams')->update($updatecstream, null, true);
        }
        
        $course_options = [];
        $course_options['fullname'] = $cstream->name;
        
        // получение конфига переменной категории в плагина sync/mcategories
        $mdlcatvar = $this->dof->storage('config')->get_config_value('mdlcategoryvarname', 'sync', 'mcourses', $cstream->departmentid);
        
        // получение идентификатора категории
        $categoryid = $this->dof->storage('config')->get_config_value($mdlcatvar, 'sync', 'mcategories', $cstream->departmentid);
        if ( ! empty($categoryid) )
        {
            // укажем категорию курса
            $course_options['category'] = $categoryid;
        }
        
        // создание клона курса
        $mdlcourseid = $this->dof->modlib('ama')->course($pitem->mdlcourse)->restore_backup([], $file_options, $course_options);
        
        if ( empty($mdlcourseid) )
        {
            // не удалось создать клон курса
            $updatecstream = new stdClass();
            $updatecstream->id = $cstream->id;
            $updatecstream->mdlcourse = null;
            return $this->dof->storage('cstreams')->update($updatecstream, null, true);
        }
        
        // обновим запись учебного процесса
        $updatecstream = new stdClass();
        $updatecstream->id = $cstream->id;
        $updatecstream->mdlcourse = $mdlcourseid;
        return $this->dof->storage('cstreams')->update($updatecstream, null, true);
    }
    
    /** Получить объект курса moodle по его id
     * 
     * @return object|bool - объект курса или false
     * @param int $id - id курса в moodle
     */
    public function get_course($id)
    {
        if ( ! $this->course_exists_quiet($id) )
        {
            return false;
        }
        return $this->dof->modlib('ama')->course($id)->get();
    }
    
    /** Получить ссылку на курс moodle
     * 
     * @param int $id - id курса в moodle
     * @return string|bool - строка для ссылки на курс или false если такого курса нет
     */
    public function get_course_link($id)
    {
        if ( ! $this->course_exists_quiet($id) )
        {
            return false;
        }
        return $this->dof->modlib('ama')->course($id)->get_link();
    }
    
    /** Аккуратно проверить существование курса в moodle, не создавая ошибок print_error
     * 
     * @param int $courseid - id курса в moodle
     * @return bool
     *              true - если курс существует
     *              false - если курс не существует
     */
    public function course_exists_quiet($id)
    {
        if ( ! $id OR ! is_int_string($id) )
        {
            return false;
        }
        
        if ( ! $this->dof->modlib('ama')->course(false)->is_exists($id) )
        {// курс не существует в moodle. Аккуратно вернем false, не
            // прерывая работу скртипта ошибками
            return false;
        }
        
        return true;
    }
    
    /**
     * Создание бэкапа курса
     * 
     * @param int $courseid
     * @param array $backupoptions
     * @param array $fileoptions
     * 
     * @return boolean
     */
    public function create_backup($courseid, $backupoptions = [], $fileoptions = [])
    {
        if ( ! $this->course_exists_quiet($courseid) )
        {
            return false;
        }
        
        $amacourse = $this->dof->modlib('ama')->course($courseid);
        
        return $amacourse->create_backup($backupoptions, $fileoptions);
    }
    
    /**
     * Восстановление курса из бэкапа
     * 
     * @param int $courseid
     * @param array $restoreoptions
     * @param array $fileoptions
     * @param array $courseoptions
     * 
     * @return int
     */
    public function restore_backup($courseid, $restoreoptions = [], $fileoptions = [], $courseoptions = [])
    {
        if ( ! $this->course_exists_quiet($courseid) )
        {
            return false;
        }
        
        $amacourse = $this->dof->modlib('ama')->course($courseid);
        
        return $amacourse->restore_backup($restoreoptions, $fileoptions, $courseoptions);
    }
    
    /**
     * Проверка существования бэкапа курса
     * 
     * @param int $courseid
     * @param array $fileoptions
     * 
     * @return boolean
     */
    public function backup_exists($courseid, $fileoptions = [])
    {
        return $this->dof->modlib('ama')->course($courseid)->backup_exists($fileoptions);
    }
    
    /**
     * Создание курса мудл
     * 
     * @param stdClass $courseobj
     * 
     * @return int
     */
    public function create_course(stdClass $courseobj)
    {
        return $this->dof->modlib('ama')->course(false)->create($courseobj);
    }
    
    /**
     * Создание курса мудл на основе уже существующего курса
     *
     * @param int $mdlcourseid
     * @param stdClass $courseobj
     *
     * @return int | false
     */
    public function clone_course($mdlcourseid, stdClass $courseobj)
    {
        if ( ! $this->course_exists_quiet($mdlcourseid) )
        {
            return false;
        }
        
        // uniqid возвращает число в 16-ричной системе счисления, нам нужно только числовые символы
        // поэтому методом hexdec переводим число в десятичную
        $file_options = [
            'itemid' => hexdec(uniqid()),
            'filearea' => 'storage_programmitems_temp_backups'
        ];
        
        // Создание бэкапа курса
        if ( ! $this->create_backup($mdlcourseid, [], $file_options) )
        {
            return false;
        }
        
        // Восстановление курса из бэкапа
        $newmdlcourseid = $this->restore_backup($mdlcourseid, [], $file_options);
        if ( empty($newmdlcourseid) )
        {
            // Не удалось восстановить
            return false;
        }
        
        // Обновление полей нового курса
        $this->dof->modlib('ama')->course($newmdlcourseid)->update($courseobj);
        
        // Удаление файла бэкапа
        $this->dof->modlib('ama')->course($newmdlcourseid)->delete_backup_files($file_options);
        
        return $newmdlcourseid;
    }
    
    /**
     * Удаление бэкап файла
     *
     * @param array $fileoptions
     *
     * @return bool
     */
    public function delete_backupfile($fileoptions)
    {
        return $this->dof->modlib('ama')->course(false)->delete_backup_files($fileoptions);
    }
}
?>