<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
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
 * Библиотека управления загружаемыми файлами
 *
 * @package    im
 * @subpackage filestorage
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_filestorage implements dof_plugin_modlib
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
		return 2017091900;
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
        return 'neon';
    }
    
    /** 
     * Возвращает тип плагина
     * 
     * @return string 
     */
    public function type()
    {
        return 'modlib';
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
        return 'filestorage';
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
                        'widgets'         => 2009050800
                ],
                'storage' => [
                        'persons'         => 2015012000,
                        'config'          => 2011080900,
                        'acl'             => 2011040504
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
                'modlib' => [
                        'widgets'         => 2009050800
                ],
                'storage' => [
                        'persons'         => 2015012000,
                        'config'          => 2011080900,
                        'acl'             => 2011040504
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
    public function is_access($do, $objid = NULL, $userid = NULL)
    {
        // Используем функционал из $DOFFICE
        return $this->dof->is_access($do, NULL, $userid);
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
    public function require_access($do, $objid = NULL, $userid = NULL)
    {
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $code = $this->code();
            $type = $this->type();
            $notice = $code.'/'.$do.' (block/dof/'.$type.'/'.$code.': '.$do.')';
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
        return false;
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
        $this->dofcontext = context_block::instance($dof->instance->id);
    }
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /**
     * Получить новый Itemid для хранения файлов
     * 
     * @param string $area - Файловая зона
     * 
     * @return int|bool - ID свободного экземпляра файловой зоны или false в случае ошибки
     */
    public function get_new_itemid( $area = 'public' )
    {
        global $CFG;
        
        $context = context_block::instance($this->dof->instance->id);
        require_once($CFG->libdir.'/filelib.php');
        $fs = get_file_storage();
        
        // Получение случайного ID зоны
        $itemid = mt_rand(1, 999999999);
        // Счетчик попыток
        $counter = 500;
        // Флаг завершения поиска
        $complete = false;
        do {
            // Проверка наличия файлов
            $files = $fs->get_area_files($context->id, 'block_dof', $area, $itemid);
            if ( empty($files) )
            {// Файлов нет, зону можно использовать
                $complete = true;
            } else  
            {// Зона не пуста - ищем новую
                $itemid = mt_rand(1, 999999999);
                $counter--;
            }
        } while ( empty($complete) && $counter > 0 );

        if ( $counter < 1 )
        {// ID не найден
            return false;
        }
        return $itemid;
    }
    
    /**
     * Сформировать ссылки на файлы
     * 
     * @param int $itemid - ID зоны
     * @param array $options - Дополнительные параметры
     * 
     * @return string - HTML-код ссылок на файлы
     */
    public function link_files($itemid, $filearea='public', $options = [])
    {
        $links = [];
        
        // Инициализируем генератор HTML
        $this->dof->modlib('widgets')->html_writer();
        
        // Контекст деканата
        $dofcontext = context_block::instance($this->dof->instance->id);
        // Менеждер
        $fs = get_file_storage();
        
        // Получение файлов
        $files = $fs->get_area_files($dofcontext->id, 'block_dof', $filearea, $itemid);
        foreach ( $files as $file )
        {
            if ( $file->is_directory() )
            {// Пропуск директорий
                continue;
            }
            
            // Формирование ссылки на файл
            $filename = $file->get_filename();
            $url = moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
            );

            $links[] = format_text(dof_html_writer::link($url, $filename));
            
        }
        
        if( ! empty($options['return_array']) )
        {
            return $links;
        } else
        {
            return implode('',$links);
        }
    }
    
    /**
     * Формирование ссылки на файл
     * 
     * @param stored_file $file
     * 
     * @return string | void
     */
    public function make_pluginfile_url(stored_file $file, $forcedownload = false)
    {
        return moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename(),
                $forcedownload);
    }
    
    /** 
     * Подготовить файловую зону
     * 
     * Данный метод следует вызывать перед опредеделнием поля.
     * 
     * @param $name - Имя поля
     * @param $itemid - ID зоны файлов
     * 
     * @return int - ID пользовательской зоны
     */
    public function definion_filemanager($name, $itemid = null, $filearea='public', $options=[])
    {
        // Имя поля без _filemanager
        $tname = str_replace('_filemanager', '', $name);
        
        $filemanageroptions = ['maxfiles' => 1, 'subdirs' => false];
        if(!empty($options['filemanageroptions']) && is_array($options['filemanageroptions']))
        {
            $filemanageroptions = $options['filemanageroptions'];
        }
        
        $data = new stdClass();
        // Подготовка файлменеджера
        file_prepare_standard_filemanager(
                $data,
                $tname,
                $filemanageroptions,
                $this->dofcontext,
                'block_dof',
                $filearea,
                $itemid
        );
        
        if ( isset($data->$name) )
        {// Установлена зона
            return $data->$name;
        } else
        {
            return NULL;
        }
    }
    
    /**
     * Сохранить файлы из менеджера
     *
     * @param $name - Имя поля
     *
     * @return int - ID пользовательской зоны
     */
    public function process_filemanager($name, $draftitemid, $itemid = NULL, $filearea='public', $options=[])
    {
        // Имя поля без _filemanager
        $tname = str_replace('_filemanager', '', $name);
    
        if ( $itemid === null )
        {// Зона асохранения не объявлена 
            $itemid = $this->get_new_itemid($filearea);
        }
        $itemid = (int)$itemid;

        $filemanageroptions = ['maxfiles' => 1, 'subdirs' => false];
        if(!empty($options['filemanageroptions']) && is_array($options['filemanageroptions']))
        {
            $filemanageroptions = $options['filemanageroptions'];
        }
        
        $data = new stdClass();
        $data->$name = $draftitemid;
        file_postupdate_standard_filemanager(
            $data,
            $tname,
            $filemanageroptions,
            $this->dofcontext,
            'block_dof',
            $filearea,
            $itemid
        );
        
        // Плучение менеджера
        $fs = get_file_storage(); 
        $is_empty = $fs->is_area_empty($this->dofcontext->id, 'block_dof', $filearea, $itemid);
        if ( ! empty($is_empty) )
        {// В зоне нет файлов - удаление папок и очистка зоны
            $fs->delete_area_files($this->dofcontext->id, 'block_dof', $filearea, $itemid);
            return NULL;
        }
        return $itemid;
    }
    
    /**
     * Получить хэши путей файлов в указанной зоне
     *
     * @param int $itemid - ID зоны, в которую загружены файлы
     * @param array $options - Дополнительные параметры
     *
     * @return array - Массив хэшей путей файлов
     */
    public function get_pathnamehashes($itemid, $filearea='public', $options = [])
    {
        // Контекст деканата
        $dofcontext = context_block::instance($this->dof->instance->id);
        // Менеждер
        $fs = get_file_storage();
    
        // Получение файлов
        $files = $fs->get_area_files($dofcontext->id, 'block_dof', $filearea, $itemid);
        $pathnamehashes = [];
        foreach ( $files as $file )
        {
            if ( $file->is_directory() )
            {// Пропуск директорий
                continue;
            }
    
            // Формирование ссылки на файл
            $pathnamehash = $file->get_pathnamehash();
            $pathnamehashes[$pathnamehash] = $pathnamehash;
        }
    
        return $pathnamehashes;
    }

    /**
     * Удалить неактуальные файлы в файловой зоне
     * 
     * @param string $filearea - наименование файловой зоны
     * @param int $itemid - идентификатор зоны
     * @param array $actualmask - массив актуальных файлов, представленные 
     *                            в виде массива со значениями filepath, filename
     */
    public function delete_not_actual_files($filearea, $itemid, $actualmask=[])
    {
        // Контекст деканата
        $dofcontext = context_block::instance($this->dof->instance->id);
        // Менеждер
        $fs = get_file_storage();

        $files = $fs->get_area_files($dofcontext->id, 'block_dof', $filearea, $itemid);
        foreach($files as $file)
        {
            $actualfile = false;
            if( ! empty($actualmask) AND is_array($actualmask) )
            {
                foreach($actualmask as $mask)
                {
                    if ( ! empty($mask['filepath']) AND $file->get_filepath() == $mask['filepath'] AND 
                         ! empty($mask['filename']) AND $file->get_filename() == $mask['filename'] )
                    {
                        // в маске указано, что этот файл не надо трогать
                        $actualfile = true;
                        break;
                    }
                }
            }
            
            if ( ! $actualfile )
            {
                $file->delete();
            }
        }
    }
    
    /**
     * Получить файл по пути
     *
     * @param string $filepath - Относительный путь файла в зоне
     * @param int $itemid - ID зоны, в которую загружены файлы
     * @param string $filearea - Имя зоны
     *
     * @return stored_file|false - Искомый файл
     */
    public function get_file_by_path($filepath, $itemid, $filearea)
    {
        // Контекст деканата
        $dofcontext = context_block::instance($this->dof->instance->id);
        // Менеждер
        $fs = get_file_storage();
        
        // Получение файла
        $filename = basename($filepath);
        $path = str_replace($filename, '', $filepath);
        if ( substr($path, 0, 1) !== '/' )
        {
            $path = '/'.$path;
        }
        return $fs->get_file(
            $dofcontext->id, 
            'block_dof', 
            $filearea, 
            $itemid,
            $path,
            $filename
        );
    }
    
    /**
     * Получить файл по хэшу пути файла
     * 
     * @param string $pathnamehash - Хэш пути файла
     * 
     * @return null|stored_file
     */
    public function get_file_by_pathnamehash($pathnamehash)
    {
        // Менеждер
        $fs = get_file_storage();
        
        $file = $fs->get_file_by_hash($pathnamehash);
        if ( ! $file )
        {
            return null;
        }
        
        return $file;
    }
    
    /**
     * Получение массива файлов в переданной файловой зоне
     *
     * @param string $itemid
     * @param $filearea
     * @param $sort
     *
     * @return stored_file[]
     */
    public function get_files_by_filearea($filearea, $itemid, $sort = 'timecreated DESC')
    {
        // массив обработанных файлов
        $processedfiles = [];
        
        // контекст блока эд
        $dofcontext = context_block::instance($this->dof->instance->id);
        
        // файловой хранилище
        $fs = get_file_storage();
        $files = $fs->get_area_files($dofcontext->id, 'block_dof', $filearea, $itemid, $sort);
        if ( ! empty($files) )
        {
            foreach ( $files as $file )
            {
                if ( $file->get_filename() != '.' )
                {
                    $processedfiles[] = $file;
                }
            }
        }
        
        return $processedfiles;
    }
    
    /**
     * Create instance of file class from database record.
     *
     * @param stdClass $filerecord record from the files table left join files_reference table
     * @return stored_file instance of file abstraction class
     */
    public function get_file_instance($file)
    {
        $fs = get_file_storage();
        return $fs->get_file_instance($file);
    }
    
    /**
     * Returns item id of file
     * @param stored_file $file
     * @return int
     */
    public function get_itemid(stored_file $file)
    {
        return $file->get_itemid();
    }
    
    /**
     * Returns sha1 hash of all file path components sha1("contextid/component/filearea/itemid/dir/dir/filename.ext").
     *
     * @return string
     */
    public function get_pathnamehash(stored_file $file)
    {
        return $file->get_pathnamehash();
    }
    
    /**
     * Перемещение файлов из одной файловой зоны в другую
     * 
     * @param string $oldfilearea
     * @param string $newfilearea
     * 
     * @return bool
     */
    public function replace_files_to_new_filearea($oldfilearea, $newfilearea)
    {
        // получение файлового хранилища
        $fs = get_file_storage();
        
        // контекст блока эд
        $dofcontext = context_block::instance($this->dof->instance->id);
        
        // получение файлов из старой файловой зоны
        $files = $fs->get_area_files($dofcontext->id, 'block_dof', $oldfilearea);
        if ( ! empty($files) )
        {
            foreach ( $files as $file )
            {
                if ( $file->get_filename() == '.' )
                {
                    continue;
                }
                
                // подготовка нового файла
                $newfile = new stdClass();
                $newfile->contextid = $file->get_contextid();
                $newfile->component = $file->get_component();
                $newfile->filearea = $newfilearea;
                $newfile->itemid = $file->get_itemid();
                $newfile->sortorder = $file->get_sortorder();
                $newfile->mimetype = $file->get_mimetype();
                $newfile->userid = $file->get_userid();
                $newfile->source = $file->get_source();
                $newfile->author = $file->get_author();
                $newfile->license = $file->get_license();
                $newfile->status = $file->get_status();
                $newfile->filepath = $file->get_filepath();
                $newfile->filename = $file->get_filename();
                $newfile->timecreated = $file->get_timecreated();
                $newfile->timemodified = $file->get_timemodified();
                $newfile->referencefileid = $file->get_referencefileid();
                
                if ( $fs->file_exists(
                        $newfile->contextid, 
                        $newfile->component, 
                        $newfile->filearea,
                        $newfile->itemid, 
                        $newfile->filepath, 
                        $newfile->filename) )
                { 
                    // файл существует
                    $existingfile = $fs->get_file(
                            $newfile->contextid, 
                            $newfile->component,
                            $newfile->filearea, 
                            $newfile->itemid, 
                            $newfile->filepath,
                            $newfile->filename);
                    
                    $existingfile->delete();
                }
                
                // создание нового файла
                $fs->create_file_from_storedfile($newfile, $file);
                $file->delete();
            }
        }
        
        return true;
    }
    
    /**
     * Проверка доступа к указанному файлу
     * 
     * @param unknown $filearea
     * @param unknown $itemid
     * @return unknown|boolean
     */
    public function file_access($filearea, $itemid)
    {
        // Получение данных о файле
        $methodpath = explode('_', $filearea, 3);
        $fileareacode = (string)array_pop($methodpath);
        $plugincode = (string)array_pop($methodpath);
        $plugintype = (string)array_pop($methodpath);

        
        if ( ! method_exists($this->dof, $plugintype) )
        {// Неизвестный тип плагина
            return false;    
        }
        
        if ( ! in_array($plugincode, array_keys($this->dof->plugin_list($plugintype))) )
        {// Неизвестный код плагина
            return false;
        }
        
        if ( ! method_exists($this->dof->{$plugintype}($plugincode), 'file_access') )
        {// Метод доступа к файлам не найден
            return false;
        }

        return $this->dof->{$plugintype}($plugincode)->file_access($fileareacode, $itemid);
    }
    
    /**
     * Проверка существования файла
     *
     * @param string $filearea
     * @param int $itemid
     * @param string $filepath
     * @param string $filename
     * 
     * @return boolean
     */
    public function file_exists($filearea, $itemid = 0, $filepath = '/', $filename = '')
    {
        // Файловый менеджер
        $fs = get_file_storage();
        
        // Контекст деканата
        $dofcontext = context_block::instance($this->dof->instance->id);
        
        return $fs->file_exists(
                $dofcontext->id,
                'block_dof',
                $filearea,
                $itemid,
                $filepath,
                $filename
                );
    }
    
    /**
     * Распаковать архив в указанную файловую зону
     *
     * @param string $pathnamehash - Хэш пути до файла архива
     * @param string $filearea - Файловая зона
     * @param int $itemid - Идентификатор подзоны
     *
     * @return array - Массив хэшей путей файлов
     */
    public function unpack_zip($pathnamehash, $filearea, $itemid = 0)
    {
        // Получение архиватора
        $packer = get_file_packer('application/zip');
        // Менеждер
        $fs = get_file_storage();
        
        // Получение файла
        $archivefile = $fs->get_file_by_hash($pathnamehash);
        if ( empty($archivefile) )
        {// Файл не найден
            return null;
        }
        
        // Контекст деканата
        $dofcontext = context_block::instance($this->dof->instance->id);
        
        // Распаковка во временную директорию
        $archivefile->extract_to_storage(
            $packer, 
            $dofcontext->id, 
            'block_dof', 
            $filearea, 
            (int)$itemid, 
            '/'
        );
        
        // Получение всех разархивированных файлов
        $unzippedfiles = $fs->get_directory_files(
            $dofcontext->id,
            'block_dof',
            $filearea,
            (int)$itemid,
            '/',
            true
        );
        return $unzippedfiles;
    }
    
    /**
     * Получить список файлов в архиве
     *
     * @param string $pathnamehash - Хэш пути до файла архива
     *
     * @return null|array - Массив файлов в архиве
     */
    public function zip_get_listfiles($pathnamehash)
    {
        // Получение архиватора
        $packer = get_file_packer('application/zip');
        // Менеждер
        $fs = get_file_storage();
        
        // Получение файла
        $archivefile = $fs->get_file_by_hash($pathnamehash);
        if ( empty($archivefile) )
        {// Файл не найден
            return null;
        }
        
        return $archivefile->list_files($packer);
    }
    
    /**
     * Удаление файлов из файловой зоны
     *
     * @param string $filearea - Файловая зона
     * @param int $itemid - Идентификатор подзоны
     *
     * @return bool
     */
    public function delete_files_area($filearea, $itemid = null)
    {
        // Менеждер
        $fs = get_file_storage();
        // Контекст деканата
        $dofcontext = context_block::instance($this->dof->instance->id);
        
        return $fs->delete_area_files(
            $dofcontext->id,
            'block_dof',
            $filearea,
            $itemid
        );
    }
    
    /**
     * Удаление файлов из файловой зоны
     * 
     * @param int $timeleft - Прошедшее время в секундах
     * @param string $filearea - Файловая зона
     * @param int $itemid - Идентификатор подзоны
     *
     * @return array $errors - Массив ошибок удаления
     */
    public function delete_files_area_by_lifetime($timeleft, $filearea, $itemid = null)
    {
        // Менеждер
        $fs = get_file_storage();
        // Контекст деканата
        $dofcontext = context_block::instance($this->dof->instance->id);
        
        // Ошибки удаления
        $errors = [];
        
        // Метка времени для решения об удалении
        $deletetime = time() - (int)$timeleft;
        
        // Получение файлов
        $files = $fs->get_area_files(
            $dofcontext->id,
            'block_dof',
            $filearea,
            $itemid
        );
        
        foreach ( $files as $file )
        {
            if ( $file->get_timemodified() < $deletetime )
            {// Требуется удалить файл
                try {
                    $file->delete();
                } catch ( moodle_exception $e )
                {// Ошибка удаления файла
                    $errors[$file->get_pathnamehash()] = $e->getMessage();
                }
            }
        }
        return $errors;
    }
    
    /**
     * Копирование файла
     *
     * @param string $pathnamehash - Хэш пути до файла
     * @param string $filearea - Файловая зона копии файла
     * @param string $filepath - Путь до копии файла
     * @param int $itemid - Идентификатор подзоны копии файла
     *
     * @return stored_file|null
     */
    public function copy_file($pathnamehash, $filearea, $filepath, $itemid)
    {
        // Менеждер
        $fs = get_file_storage();
        
        // Получение файла по хэшу
        $currentfile = $this->get_file_by_pathnamehash($pathnamehash);
        if ( $currentfile == null )
        {// Файл для копирования не найден
            return null;
        }
        
        // Контекст деканата
        $dofcontext = context_block::instance($this->dof->instance->id);
        
        $filename = basename($filepath);
        $path = str_replace($filename, '', $filepath);
        if ( substr($path, 0, 1) !== '/' )
        {
            $path = '/'.$path;
        }
        
        // Подготовка файла для копирования
        $filerecord = new stdClass();
        $filerecord->filearea = $filearea;
        $filerecord->component = 'block_dof';
        $filerecord->contextid = $dofcontext->id;
        $filerecord->filepath = $path;
        $filerecord->filename = $filename;
        $filerecord->itemid = $itemid;
        
        try {
            return $fs->create_file_from_storedfile($filerecord, $currentfile);
        } catch ( file_exception $e )
        {// Ошибка копирования файла
            return null;
        }
    }
    
    /**
     * Копирование файла в драфтовую зону пользователя
     *
     * @param string $filearea - Файловая зона копии файла
     * @param int $itemid - Идентификатор подзоны копии файла
     *
     * @return int|false
     */
    public function copy_file_to_draftarea($filearea, $itemid)
    {
        // Контекст деканата
        $dofcontext = context_block::instance($this->dof->instance->id);
        $draftitemid = 0;
        // Копирование во временную зону
        file_prepare_draft_area(
            $draftitemid,
            $dofcontext->id, 
            'block_dof', 
            $filearea, 
            $itemid
        );
        
        return $draftitemid;
    }
}
?>