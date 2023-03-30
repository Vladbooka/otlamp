<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Тема СЭО 3KL. Менеджер профилей Темы.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_opentechnology;

use admin_root;
use admin_settingpage;
use admin_category;
use admin_externalpage;
use admin_setting_configselect;
use admin_setting_configtextarea;
use admin_setting_confightmleditor;
use admin_setting_configtext;
use admin_setting_heading;
use admin_setting_configstoredfile;
use admin_setting_configcheckbox;
use admin_setting_flag;
use theme_opentechnology\profiles\base;
use theme_opentechnology\profiles\standard;
use theme_opentechnology\profiles\potential;
use theme_opentechnology\links\manager as linkmanager;
use moodle_exception;
use dml_exception;
use context_system;
use stdClass;
use theme_config;
use moodle_page;
use moodle_url;

require_once($CFG->libdir.'/adminlib.php');

class profilemanager
{
    /**
     * Настройки темы оформления
     *
     * @var theme_config
     */
    public $theme_config = null;
    
    /**
     * Текущий экземпляр менеджера
     *
     * @var profilemanager
     */
    protected static $instance = null;
    
    /**
     * Список доступных профилей
     *
     * @var array
     */
    protected $profiles = [];
    
    /**
     * Реестр идентификаторов профилей
     *
     * @var array
     */
    protected $profilesids = [];
    
    /**
     * Буфер привязок профилей к страницам
     *
     * @var array
     */
    private $pageprofiles = [];
    
    
    /**
     * Текущий профиль
     *
     * @var object
     */
    private $currentprofile = null;
    
    public static $themecolors = [
        'primary',
        'secondary',
        'success',
        'danger',
        'warning',
        'info',
        'light',
        'dark',
    ];
    
    /**
     * Клонирование менеджера
     *
     * Клонирование не поддерживается данным классом
     */
    protected function __clone()
    {
    }
    
    /**
     * Конструктор
     *
     * Для инициализации менеджера необходимо использовать profilemanager::instance();
     */
    protected function __construct()
    {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/theme/opentechnology/lib.php');
        
        // Тема
        $this->theme_config = theme_config::load('opentechnology');
        
        // Инициализация стандартного профиля
        $standardprofile = new standard();
        $this->profiles[$standardprofile->get_code()] = $standardprofile;
        $this->profilesids[0] = $standardprofile;
        
        // Добавление профилей в набор
        if ( $DB->get_manager()->table_exists('theme_opentechnology_profile') )
        {
            $profiles = (array)$DB->get_records('theme_opentechnology_profile');
            foreach ( $profiles as $profileid => $profile )
            {
                $instance = new base($profileid);
                $this->profiles[$instance->get_code()] = $instance;
                $this->profilesids[$instance->get_id()] = $instance;
            }
        }
    }
    
    /**
     * Инициализация менеджера профилей
     *
     * @return profilemanager
     */
    public static function instance()
    {
        if ( self::$instance == null )
        {// Первичная инициализация менеджера
            self::$instance = new profilemanager();
        }
        return self::$instance;
    }
    
    /**
     * Получение профилей
     *
     * @return array
     */
    public function get_profiles()
    {
        return $this->profiles;
    }
    
    /**
     * Получение профиля по ID или коду
     *
     * @param int|string $idorcode - Код или идентификатор профиля
     *
     * @return base|null
     */
    public function get_profile($idorcode)
    {
        if ( ! $this->profile_exists($idorcode) )
        {// Профиль не найден
            return null;
        }
    
        if ( is_int($idorcode) )
        {// Профиль найден
            return $this->profilesids[$idorcode];
        }
        if ( is_string($idorcode) )
        {// Профиль найден
            return $this->profiles[$idorcode];
        }
        return null;
    }
    
    /**
     * Импорт профиля
     *
     * @param int|string $idorcode - Код или идентификатор профиля
     *
     * @return bool
     */
    public function import_profile($idorcode, $importsettings)
    {
        global $USER;

        $result = true;
        
        // Получение профиля
        $profile = $this->get_profile($idorcode);
        if ( $profile === null )
        {
            return false;
        }


        // Подготовка категории для сбора настроек профиля
        $profilecategory = $this->find_theme_admin_category(['theme_opentechnology_profile_'.$profile->get_code()]);
        
        // Поиск всех настроек в экспортируемой категории
        $settings = $this->get_profile_settings($profilecategory);
        
        foreach ( $settings as $settingname => $setting )
        {
            if( isset($importsettings[$settingname]) )
            {
                if ( is_a($setting, 'admin_setting_configstoredfile')
                    && ! empty($importsettings[$settingname]->file) )
                {// Требуется выгрузка хранимого файла

                    $fs = get_file_storage();
                    
                    $newcontextid = context_system::instance()->id;
                    $newcomponent = 'theme_opentechnology';
                    $newfilearea = $this->get_theme_settingfullname_filearea($settingname);
                    $newfilepath = '/';
                    
                    $file = $importsettings[$settingname]->file;
                    
                    $newfile = new stdClass();
                    $newfile->contextid = $newcontextid;
                    $newfile->component = $newcomponent;
                    $newfile->filearea = $newfilearea;
                    $newfile->filepath = $newfilepath;
                    $newfile->itemid = $file->get_itemid();
                    $newfile->sortorder = $file->get_sortorder();
                    $newfile->mimetype = $file->get_mimetype();
                    $newfile->userid = $file->get_userid();
                    $newfile->source = $file->get_source();
                    $newfile->author = $file->get_author();
                    $newfile->license = $file->get_license();
                    $newfile->status = $file->get_status();
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
                    
                    // Перенос файла в подготовленную зону для черновиков
                    $fs->create_file_from_storedfile($newfile, $file);
                    $file->delete();

                }
                // Сохранение значения настройки
                $configwriteresult = $setting->config_write($settingname, $importsettings[$settingname]->value);
                $result = $result && $configwriteresult;
            }
        }
        // Сброк кэша темы
        theme_opentechnology_purge_caches();
        return $result;
    }
    
    /**
     * Экспорт профиля
     *
     * @param int|string $idorcode - Код или идентификатор профиля
     *
     * @return void
     */
    public function export_profile($idorcode)
    {
        // Получение профиля
        $profile = $this->get_profile($idorcode);
        if ( $profile === null )
        {
            return;
        }
        
        // Подготовка категории для сбора настроек профиля
        $exportcategory = new admin_category('theme_opentechnology_exportsettings', '', true);
        // Заполнение категории настройками для экспорта профиля
        $this->admin_settingpage_add_profile_settings($exportcategory, $profile);
        
        // Поиск всех настроек в экспортируемой категории
        $settings = $this->get_profile_settings($exportcategory);
        
        // Очистка экспортной зоны темы
        $fs = get_file_storage();
        $fs->delete_area_files(
            context_system::instance()->id,
            'theme_opentechnology',
            'export'
        );
        
        // Генерация контента файла настроек
        $content = '';
        $themeconfig = $this->theme_config->settings;
        foreach ( $settings as $settingcode => $setting )
        {
            if (!isset($themeconfig->$settingcode))
            {
                continue;
            }
            if ( $profile->get_code() == 'standard' )
            {
                $settingcodenoprefix = $settingcode;
            } else
            {
                $settingcodenoprefix = substr($settingcode, strlen($profile->get_code())+1);
            }
            if ( is_a($setting, 'admin_setting_configstoredfile') )
            {// Требуется выгрузка хранимого файла
                
                // Получение файлов настройки
                $settingarea = $this->get_theme_settingfullname_filearea($settingcode);
                $files = $fs->get_area_files(
                    context_system::instance()->id,
                    'theme_opentechnology',
                    $settingarea
                );
                
                // Создание директории файлов настройки
                $fs->create_directory(
                    context_system::instance()->id,
                    'theme_opentechnology',
                    'export',
                    0,
                    '/'.$settingcodenoprefix.'/'
                );
                
                // Копирование файлов в экспортную зону
                foreach ( $files as $file )
                {
                    if ( $file->get_filename() == '.' )
                    {
                        continue;
                    }
                    $filerecord = new stdClass();
                    $filerecord->contextid = context_system::instance()->id;
                    $filerecord->component = 'theme_opentechnology';
                    $filerecord->filearea = 'export';
                    $filerecord->itemid = 0;
                    $filerecord->sortorder = 0;
                    $filerecord->filepath = '/'.$settingcodenoprefix.'/'.$file->get_filepath();
                    $filerecord->filename = $file->get_filename();
                    $filerecord->timecreated = time();
                    $filerecord->timemodified = time();
                    $fs->create_file_from_storedfile($filerecord, $file);
                }
            }

            // Формирование валидной csv-строки с помощью стандартной php-функции записи csv в файл
            $fp = fopen('php://temp', 'r+');
            // Сохранение данных во временное хранилище в формате csv
            fputcsv(
                $fp,
                [
                    $settingcodenoprefix,
                    serialize($themeconfig->$settingcode)
                ],
                ';'
            );
            rewind($fp);
            // Запись строки в нашу переменную
            $content .= fread($fp, 1048576);
            // Закрытие временного хранилища
            fclose($fp);
        }
        
        // Создание файла настроек
        $filerecord = new stdClass();
        $filerecord->contextid = context_system::instance()->id;
        $filerecord->component = 'theme_opentechnology';
        $filerecord->filearea = 'export';
        $filerecord->itemid = 0;
        $filerecord->sortorder = 0;
        $filerecord->filepath = '/';
        $filerecord->filename = 'settings';
        $filerecord->timecreated = time();
        $filerecord->timemodified = time();
        $exportfile = $fs->create_file_from_string($filerecord, $content);
        
        // Сбор файлов для архивирования
        $archivefiles = [];
        $files = (array)$fs->get_area_files(
            context_system::instance()->id,
            'theme_opentechnology',
            'export'
        );

        foreach ( $files as $f )
        {
            if ( $f->get_filename() == '.' )
            {
                continue;
            }
            $archivefiles[$f->get_filepath() . $f->get_filename()] = $f;
        }
        
        // Запаковка архива
        $packer = get_file_packer('application/zip');
        $archive = $packer->archive_to_storage(
            $archivefiles,
            context_system::instance()->id,
            'theme_opentechnology',
            'exportsettings',
            0,
            '/',
            'settings.zip'
        );
        
        // Очистка экспортной зоны
        $fs->delete_area_files(
            context_system::instance()->id,
            'theme_opentechnology',
            'export'
        );
        
        // Отправка файла с настройками пользователю
        $url = moodle_url::make_pluginfile_url(
            $archive->get_contextid(),
            $archive->get_component(),
            $archive->get_filearea(),
            $archive->get_itemid(),
            $archive->get_filepath(),
            $archive->get_filename(),
            true
        );
        redirect($url);
    }
    
    /**
     * Получение всех настроек профиля из указанной категории
     *
     * @param admin_category $profilecategory - Категория настроек
     *
     * @return array - Массив полей настроек
     */
    protected function get_profile_settings(admin_category $profilecategory)
    {
        // Подготовка
        $settings = [];
        $themesettings = $this->theme_config->settings;
        
        // Поиск настроек в каждом из элементов
        foreach ( $profilecategory->get_children() as $children )
        {
            if ( is_a($children, 'admin_category') )
            {// Обнаружена подкатегория
                
                // Поиск настроек внутри категории
                $settings = array_merge(
                    $settings,
                    $this->get_profile_settings($children)
                );
            } elseif ( is_a($children, 'admin_settingpage') )
            {// Обнаружена страница настроек
                // Добавление всех настроек страницы
                foreach ( $children->settings as $setting )
                {
                    $name = $setting->name;
                    $settings[$name] = $setting;
                }
            }
        }
        return $settings;
    }
    
    
    /**
     * Поверка наличия профиля
     *
     * @param int|string $idorcode - Код или идентификатор профиля
     *
     * @return bool
     */
    public function profile_exists($idorcode)
    {
        if ( is_int($idorcode) && isset($this->profilesids[$idorcode]) )
        {// Профиль найден
            return true;
        }
        if ( is_string($idorcode) && isset($this->profiles[$idorcode]) )
        {// Профиль найден
            return true;
        }
        return false;
    }
    
    /**
     * Получение профиля по умолчанию
     *
     * @return base
     */
    public function get_default_profile()
    {
        // Поиск профиля по умолчанию
        foreach ( $this->profiles as $profile )
        {
            if ( (bool)$profile->is_default() )
            {// Найден профиль по умолчанию
                return $profile;
            }
        }
        // Стандартный профиль по умолчанию
        return $this->get_profile('standard');
    }
    
    public function get_current_profile()
    {
        if (is_null($this->currentprofile))
        {
            // Получение привязки страницы с высшим приоритетом
            $link = linkmanager::instance()->detect_link();
            $this->currentprofile = $link ? $link->get_profile() : $this->get_default_profile();
        }
        return empty($this->currentprofile) ? $this->get_default_profile() : $this->currentprofile;
    }
    
    /**
     * @deprecated Не следует в дальнейшем использовать этот метод. Для получения текущего профиля используйте get_current_profile, который может подобрать профиль, ориентируясь не только на страницу, но и на другие параметры
     *
     * Получение профиля оформления для текущей страницы
     *
     * @param moodle_page $page - Текущая страница
     *
     * @return base - Профиль
     */
    public function get_page_profile(moodle_page $page)
    {
        global $DB;
        
        // Поиск назначения профиля в буфере
        if( $page->has_set_url() )
        {
            $bufferkey = (string)$page->url;
            
            if ( isset($this->pageprofiles[$bufferkey]) )
            {// Ключ профиля найден в буфере
                return $this->pageprofiles[$bufferkey];
            }
            
            if ( ! $DB->get_manager()->table_exists('theme_opentechnology_plinks') )
            {// Тема не инициализирована
                return $this->get_default_profile();
            }
            
            // Получение привязки страницы с высшим приоритетом
            $pagelink = linkmanager::instance()->get_page_link($page);
            
            if ( $pagelink )
            {// Привязка обнаружена
                
                // Получение профиля привязки
                $profile = $pagelink->get_profile();
                if ( $profile )
                {// Профиль обнаружен
                    // Запись информации в буфер
                    $this->pageprofiles[$bufferkey] = $profile;
                    return $profile;
                }
            }
            // Привязка не обнаружена - установка профиля по умолчанию
            $this->pageprofiles[$bufferkey] = $this->get_default_profile();
            
            return $this->pageprofiles[$bufferkey];
        } else
        {
            return $this->get_default_profile();
        }
    }
    
    /**
     * Получить список профилей
     *
     * @return string - HTML-код с плитками профилей
     */
    public function get_profile_tiles()
    {
        global $PAGE;
        
        $html = '';
        
        foreach(glob($PAGE->theme->dir . '/profiles/overrides/*/settings.zip') as $pprofilepath)
        {
            $pprofilepathparts = explode('/', $pprofilepath);
            $pprofilecode = $pprofilepathparts[count($pprofilepathparts)-2];
            if (!array_key_exists($pprofilecode, $this->profiles))
            {
                $fakeid = 'potential_'.$pprofilecode;
                $pprofile = new potential($fakeid, $pprofilecode, $pprofilepath);
                $this->profiles[$pprofilecode] = $pprofile;
                $this->profilesids[$fakeid] = $pprofile;
            }
        }
        
        // Генерация платки для каждого профиля
        foreach ( $this->profiles as $profile )
        {
            $html .= $profile->render_tile();
        }
        return $html;
    }
    
    /**
     * Проверка поддержки изменения указанным профилем
     *
     * @param int $id - Идентификатор профиля
     *
     * @return bool
     */
    public function profile_allow_edit($id)
    {
        if ( ! $profile = $this->get_profile((int)$id) )
        {// Профиль не найден
            return false;
        }
    
        return $profile->can_edit();
    }
    
    /**
     * Сохранить профиль
     *
     * @param stdClass $profile - Данные профиля
     *
     * @return base - Сохраненный профиль
     *
     * @throws moodle_exception - При ошибках сохранения
     */
    public function save_profile($profile)
    {
        global $DB;
        
        if ( ! empty($profile->id) )
        {// Обновление профиля
            
            // Формирование данных для обновления
            $update = new stdClass();
            $update->id = $profile->id;
            if ( isset($profile->name) )
            {
                $update->name = trim($profile->name);
            }
            if ( isset($profile->description) )
            {
                $update->description = (string)$profile->description;
            }
            if ( isset($profile->descriptionformat) )
            {
                $update->descriptionformat = (int)$profile->descriptionformat;
            }
            
            $DB->update_record('theme_opentechnology_profile', $update);
            $id = $update->id;
            
            // Обновление реестра менеджера
            $instance = new base($id);
            
        } else
        {// Добавление нового профиля
            
            // Формирование объекта для записи
            $insert = new stdClass();
            $insert->name = '';
            $insert->code = '';
            $insert->description = '';
            $insert->descriptionformat = 0;
            $insert->defaultprofile = 0;
            if ( isset($profile->name) )
            {
                $insert->name = trim($profile->name);
            }
            if ( isset($profile->code) )
            {
                $insert->code = trim($profile->code);
            }
            if ( isset($profile->description) )
            {
                $insert->description = (string)$profile->description;
            }
            if ( isset($profile->descriptionformat) )
            {
                $insert->descriptionformat = (int)$profile->descriptionformat;
            }
            
            // Проверка объекта
            if ( empty($insert->code) )
            {
                throw new moodle_exception('profile_save_error_code_empty');
            } elseif ( $this->profile_exists($insert->code) )
            {// Код не уникален
                throw new moodle_exception('profile_save_error_code_notunique');
            }
            
            $id = $DB->insert_record('theme_opentechnology_profile', $insert);
            
            // Обновление реестра менеджера
            $instance = new base($id);
            
            // Инициализация настроек профиля
            $themecategory = $this->find_theme_admin_category();
            $profilecategory = $this->admin_settingpage_add_profile_settings($themecategory, $instance);
            
            // Заполнение настроек для профиля значениями по умолчанию
            $settings = $this->admin_settingpage_get_settings($profilecategory);
            foreach ( $settings as $setting )
            {
                $setting->config_write($setting->name, $setting->get_defaultsetting());
            }
        }
        
        
        $update = new stdClass();
        $update->id = $id;
        if ( $profile->defaultprofile )
        {// Установка профиля по умолчанию
            // У всех профилей отключаем использование по умолчанию
            $DB->set_field('theme_opentechnology_profile', 'defaultprofile', 0);
            // Для нашего профиля - включаем использование по умолчанию
            $update->defaultprofile = 1;
        } else
        {
            // Для нашего профиля - отключаем использование по умолчанию
            $update->defaultprofile = 0;
        }
        $DB->update_record('theme_opentechnology_profile', $update);
        
        // Обновление реестра менеджера
        $instance = new base($id);
                
        $this->profiles[$instance->get_code()] = $instance;
        $this->profilesids[$instance->get_id()] = $instance;
        
        return $instance;
    }
    
    /**
     * Проверка поддержки удаления указанным профилем
     *
     * @param int $id - Идентификатор профиля
     *
     * @return bool
     */
    public function profile_allow_delete($id)
    {
        if ( ! $profile = $this->get_profile((int)$id) )
        {// Профиль не найден
            return false;
        }
        
        return $profile->can_delete();
    }
    
    /**
     * Удаление профиля
     *
     * @param int|base $profile - Профиль
     *
     * @return bool
     */
    public function delete_profile($profile)
    {
        global $DB;
        
        // Нормализация
        if ( ! is_object($profile) )
        {
            if ( ! $profile = $this->get_profile((int)$profile) )
            {// Профиль не найден
                return false;
            }
        }
        
        if ( ! $profile->can_delete() )
        {// Профиль не поддерживает удаление
            
        }
        
        $transaction = $DB->start_delegated_transaction();
        
        try
        {
            // Удаление данных профлия
            $profile->delete_profile_data();
            // Удаление профиля
            $DB->delete_records('theme_opentechnology_profile', ['id' => $profile->get_id()]);
        } catch ( moodle_exception $e )
        {
            $transaction->rollback($e);
            return false;
        } catch ( dml_exception $e )
        {
            $transaction->rollback($e);
            return false;
        }
        $transaction->allow_commit();
        
        return true;
    }
       
    /**
     * Получение настройки темы для указанного профиля
     *
     * @param string $settingname
     * @param base $profile
     *
     * @return mixed|null - Настройка
     */
    public function get_theme_setting($settingname, $profile)
    {
        // Получение имени настройки для профиля
        $settigfullname = $profile->get_setting_name($settingname);
        // Поиск настройки в профиле
        if ( isset($this->theme_config->settings->$settigfullname) )
        {// Настройка найдена
            return $this->theme_config->settings->$settigfullname;
        }
    
        // Получение профиля по умолчанию
        $defaultprofile = $this->get_default_profile();
        // Получение имени настройки для профиля по умолчанию
        $settigfullname = $defaultprofile->get_setting_name($settingname);
        // Поиск настройки в профиле по умолчанию
        if ( isset($this->theme_config->settings->$settigfullname) )
        {// Настройка найдена
            return $this->theme_config->settings->$settigfullname;
        }
    
        // Поиск настройки в стандартном профиле
        if ( isset($this->theme_config->settings->$settingname) )
        {// Настройка найдена
            return $this->theme_config->settings->$settingname;
        }
    
        // Настройка не найдена
        return null;
    }
    
    /**
     * Получение url файла для указанного профиля
     *
     * @param string $settingname
     * @param base $profile
     *
     * @return mixed|null - Настройка
     */
    public function get_theme_setting_file_url($settingname, $profile)
    {
        // Получение имени настройки для профиля
        $settigfullname = $profile->get_setting_name($settingname);
        // Получение зоны
        $filearea = $this->get_theme_setting_filearea($settingname, $profile);
    
        $fileurl = $this->theme_config->setting_file_url($settigfullname, $filearea);
        if ( ! empty($fileurl) )
        {// Файл найден
            return $fileurl;
        }
    
        return null;
    }
    
    /**
     * Получение настройки темы для указанного профиля
     *
     * @param string $settingname - Назваине настройки
     * @param base $profile - Профиль
     *
     * @return string - Имя настройки
     */
    public function get_theme_setting_name($settingname, $profile)
    {
        // Получение полного имени
        return $profile->get_setting_name($settingname);
    }
    
    /**
     * Получение имени файловой зоны указанного профиля
     *
     * @param string $settingname - Назваине настройки
     * @param base $profile - Профиль
     *
     * @return string - Имя файловой зоны
     */
    public function get_theme_setting_filearea($settingname, $profile)
    {
        $settingfullname = $profile->get_setting_name($settingname);
    
        // Имя файловой зоны по умолчанию
        return 'settings_'.$settingfullname;
    }
    
    /**
     * Получение имени файловой зоны указанного профиля
     *
     * @param string $settingfullname - Полное имя настройки c с учетом профиля
     *
     * @return string - Имя файловой зоны
     */
    public function get_theme_settingfullname_filearea($settingfullname)
    {
        // Имя файловой зоны по умолчанию
        return 'settings_'.$settingfullname;
    }
    
    /**
     * Добавление настроек профилей темы
     *
     * @param admin_category $parentcategory - Родительская категория настроек
     *
     * @return void
     */
    public function admin_settingpage_add_settings(admin_category $parentcategory)
    {
        // Добавление разделов для каждого профиля
        foreach ( $this->profiles as $profile )
        {
            $this->admin_settingpage_add_profile_settings($parentcategory, $profile);
        }
    }

    /**
     * Получить все настройки в указанном разделе
     *
     * @param admin_category|admin_settingpage $node
     *
     * @return admin_settingpage[]
     */
    protected function admin_settingpage_get_settings($node)
    {
        $return = [];
        
        if ( $node instanceof admin_category )
        {
            $entries = $node->get_children();
            foreach ( $entries as $entry )
            {
                $return = array_merge($return, $this->admin_settingpage_get_settings($entry));
            }
        } else if ( $node instanceof admin_settingpage )
        {
            $return = array_merge($return, (array)$node->settings);
        }
        
        return $return;
    }
    
    /**
     * Добавить раздел настроек профиля
     *
     * @param admin_category $parentcategory - Родительская категория настроек
     * @param base $profile - Экземпляр профиля
     *
     * @return void
     */
    protected function admin_settingpage_add_profile_settings(admin_category $parentcategory, $profile)
    {
        // Получение данных
        $id = $profile->get_id();
        $code = $profile->get_code();
        $name =  $profile->get_name();
        $context = context_system::instance();
        
        // Добавление раздела профиля
        $category = new admin_category(
            'theme_opentechnology_profile_'.$code,
            $name
        );
        $parentcategory->add($parentcategory->name, $category);
        
        
        if ( $id )
        {// Указан реальный профиль
            
            // Добавление ссылки на страницу сохранения профиля
            $url = new moodle_url(
                '/theme/opentechnology/profiles/save.php',
                ['id' => $id]
            );
            if ( get_capability_info('theme/opentechnology:profile_edit') )
            {// Право будет создано только после обновления темы
                $page = new admin_externalpage(
                    'theme_opentechnology_profile_'.$code.'_save',
                    get_string('profile_edit_title', 'theme_opentechnology'),
                    $url,
                    'theme/opentechnology:profile_edit',
                    false,
                    $context
                );
                $category->add($category->name, $page);
            }
            
            // Добавление ссылки на страницу привязок профиля
            $url = new moodle_url(
                '/theme/opentechnology/profiles/links.php',
                ['id' => $id]
            );
            if ( get_capability_info('theme/opentechnology:profile_links_view') )
            {// Право будет создано только после обновления темы
                $page = new admin_externalpage(
                    'theme_opentechnology_profile_'.$code.'_links',
                    get_string('profile_links_title', 'theme_opentechnology'),
                    $url,
                    'theme/opentechnology:profile_links_view',
                    false,
                    $context
                );
                $category->add($category->name, $page);
            }
        }
        
        // Добавление страниц настроек для профиля
        $settingsmanager = new profilesettings($profile, $category, $this);
        $settingsmanager->add_all_profile_settings();
        
        // Дополнение страницы с цветовой схемой настройками,
        // которые еще не были перенесены в класс profilesettings
//         try {
//             $pagename = 'color';
            
//             $settingspage = $settingsmanager->get_page($pagename);
            
//             $methodname = 'admin_settingpage_add_profile_settings_'.$pagename;
//             if( method_exists($this, $methodname) )
//             {
//                 $this->$methodname($settingspage, $profile);
//             }
//         } catch(\Exception $ex)
//         {
            
//         }
        
    }
    
    protected function admin_settingpage_add_profile_page($pagecode, admin_category $parentcategory, $profile)
    {
        global $ADMIN;
        if( is_null($ADMIN) )
        {
            $ADMIN = admin_get_root();
        }
        
        // Общие настройки темы
        $name = $this->get_theme_setting_name($pagecode, $profile);
        $settings = new admin_settingpage(
            'theme_opentechnology_'.$name.'_old',
            get_string('theme_opentechnology_'.$pagecode, 'theme_opentechnology').'_old',
            'theme/opentechnology:settings_edit'
        );
        
        if ($ADMIN->fulltree)
        {
            $overrides = []; //$this->get_profile_overrides_types($profile);
            array_walk($overrides, function(&$overridetype){
                $overridetype = get_string('profile_override_'.$overridetype, 'theme_opentechnology');
            });
            if( ! empty($overrides) )
            {
                $a = new \stdClass();
                $a->themedir = $this->theme_config->dir;
                $a->profilecode = $profile->get_code();
                $a->overrides = '<div>'.implode('</div><div>',$overrides).'</div>';
                $setting = new \admin_setting_configempty(
                    'profile_overrides_detected',
                    get_string('profile_overrides_detected','theme_opentechnology'),
                    get_string('profile_overrides_detected_desc','theme_opentechnology', $a)
                );
                $settings->add($setting);
            }
            
            $methodname = 'admin_settingpage_add_profile_settings_'.$pagecode;
            if( method_exists($this, $methodname) )
            {
                $this->$methodname($settings, $profile);
            }
        }
        
        $parentcategory->add($parentcategory->name, $settings);
    }
    
    /**
     * Добавить раздел основных настроек профиля
     *
     * @param admin_category $parentcategory - Родительская категория настроек
     * @param base $profile - Экземпляр профиля
     *
     * @return void
     */
//     protected function admin_settingpage_add_profile_settings_main(&$settings, $profile)
//     {
        // favicon
//         $name = $this->get_theme_setting_name('main_favicon', $profile);
//         $name = 'theme_opentechnology/'.$name;
//         $title = get_string('settings_main_favicon', 'theme_opentechnology');
//         $description = get_string('settings_main_favicon_desc', 'theme_opentechnology');
//         $filearea = $this->get_theme_setting_filearea('main_favicon', $profile);
//         $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea,0,['accepted_types' => '.ico']);
//         $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//         $settings->add($setting);
        
//         // Отображение языка
//         $name = $this->get_theme_setting_name('main_langmenu', $profile);
//         $name = 'theme_opentechnology/'.$name;
//         $title = get_string('settings_main_langmenu', 'theme_opentechnology');
//         $description = get_string('settings_main_langmenu_desc', 'theme_opentechnology');
//         $default = 0;
//         $choices = [
//             0 => get_string('settings_main_langmenu_dockpanel', 'theme_opentechnology'),
//             1 => get_string('settings_main_langmenu_default', 'theme_opentechnology'),
//             2 => get_string('settings_main_langmenu_inline', 'theme_opentechnology'),
//         ];
//         $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
//         $settings->add($setting);
        
//         // Скрытие док-панели
//         $name = $this->get_theme_setting_name('main_dock_hide', $profile);
//         $name = 'theme_opentechnology/'.$name;
//         $title = get_string('settings_main_dock_hide', 'theme_opentechnology');
//         $description = get_string('settings_main_dock_hide_desc', 'theme_opentechnology');
//         $default = 1;
//         $choices = [
//             0 => get_string('settings_main_dock_hide_never', 'theme_opentechnology'),
//             1 => get_string('settings_main_dock_hide_auto', 'theme_opentechnology')
//         ];
//         $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
//         $settings->add($setting);
        
//         // Фиксированая ширина
//         $name = $this->get_theme_setting_name('main_fixed_width', $profile);
//         $name = 'theme_opentechnology/'.$name;
//         $title = get_string('settings_main_fixed_width', 'theme_opentechnology');
//         $description = get_string('settings_main_fixed_width_desc', 'theme_opentechnology');
//         $default = 0;
//         $choices = [
//             0 => get_string('settings_main_fixed_width_disable', 'theme_opentechnology'),
//             1 => get_string('settings_main_fixed_width_enable', 'theme_opentechnology'),
//         ];
//         $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
//         $settings->add($setting);
        
//         // Авторизация в модальном окне
//         $name = $this->get_theme_setting_name('main_modal_login', $profile);
//         $name = 'theme_opentechnology/'.$name;
//         $title = get_string('settings_main_modal_login', 'theme_opentechnology');
//         $description = get_string('settings_main_modal_login_desc', 'theme_opentechnology');
//         $default = 0;
//         $choices = [
//             0 => get_string('settings_main_modal_login_disable', 'theme_opentechnology'),
//             1 => get_string('settings_main_modal_login_enable', 'theme_opentechnology'),
//         ];
//         $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
//         $settings->add($setting);

//         // Заголовки элементов док-панели
//         $name = $this->get_theme_setting_name('main_dockeditem_title', $profile);
//         $name = 'theme_opentechnology/'.$name;
//         $title = get_string('settings_main_dockeditem_title', 'theme_opentechnology');
//         $description = get_string('settings_main_dockeditem_title_desc', 'theme_opentechnology');
//         $default = 2;
//         $choices = [
//             0 => get_string('settings_main_dockeditem_title_text', 'theme_opentechnology'),
//             1 => get_string('settings_main_dockeditem_title_icon', 'theme_opentechnology'),
//             2 => get_string('settings_main_dockeditem_title_depends_on_width', 'theme_opentechnology'),
//             3 => get_string('settings_main_dockeditem_title_icon_and_text', 'theme_opentechnology'),
//             4 => get_string('settings_main_dockeditem_title_icon_and_text_if_fit', 'theme_opentechnology')
//         ];
//         $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
//         $settings->add($setting);
        
//         // Заголовки элементов док-панели
//         $name = $this->get_theme_setting_name('main_dockeditem_title_default', $profile);
//         $name = 'theme_opentechnology/'.$name;
//         $title = get_string('settings_main_dockeditem_title_default', 'theme_opentechnology');
//         $description = get_string('settings_main_dockeditem_title_default_desc', 'theme_opentechnology');
//         $default = 1;
//         $choices = [
//             0 => get_string('settings_main_dockeditem_title_default_text', 'theme_opentechnology'),
//             1 => get_string('settings_main_dockeditem_title_default_icon', 'theme_opentechnology')
//         ];
//         $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
//         $settings->add($setting);
        
//         // Используемый набор изображений
//         $name = $this->get_theme_setting_name('main_dockeditem_title_iconset', $profile);
//         $name = 'theme_opentechnology/'.$name;
//         $title = get_string('settings_main_dockeditem_title_iconset', 'theme_opentechnology');
//         $description = get_string('settings_main_dockeditem_title_iconset_desc', 'theme_opentechnology');
//         $default = '04';
//         $profilecode =$profile->get_code();
//         $choices = [
//             $profilecode => get_string('settings_main_dockeditem_title_iconset_profile', 'theme_opentechnology', $profilecode),
            
//             '01' => get_string('settings_main_dockeditem_title_iconset_01', 'theme_opentechnology'),
//             '07' => get_string('settings_main_dockeditem_title_iconset_07', 'theme_opentechnology'),
//             '08' => get_string('settings_main_dockeditem_title_iconset_08', 'theme_opentechnology'),
            
//             '02' => get_string('settings_main_dockeditem_title_iconset_02', 'theme_opentechnology'),
//             '03' => get_string('settings_main_dockeditem_title_iconset_03', 'theme_opentechnology'),
//             '04' => get_string('settings_main_dockeditem_title_iconset_04', 'theme_opentechnology'),
//             '05' => get_string('settings_main_dockeditem_title_iconset_05', 'theme_opentechnology'),
//             '06' => get_string('settings_main_dockeditem_title_iconset_06', 'theme_opentechnology'),
            
//             '09' => get_string('settings_main_dockeditem_title_iconset_09', 'theme_opentechnology'),
//             '10' => get_string('settings_main_dockeditem_title_iconset_10', 'theme_opentechnology'),
//             '11' => get_string('settings_main_dockeditem_title_iconset_11', 'theme_opentechnology'),
//             '12' => get_string('settings_main_dockeditem_title_iconset_12', 'theme_opentechnology'),
            
//             '13' => get_string('settings_main_dockeditem_title_iconset_13', 'theme_opentechnology'),
//             '14' => get_string('settings_main_dockeditem_title_iconset_14', 'theme_opentechnology'),
//             '15' => get_string('settings_main_dockeditem_title_iconset_15', 'theme_opentechnology'),
//             '16' => get_string('settings_main_dockeditem_title_iconset_16', 'theme_opentechnology'),
//             '17' => get_string('settings_main_dockeditem_title_iconset_17', 'theme_opentechnology'),
//         ];
//         $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
//         $settings->add($setting);
        
//         // Отправка орфографической ошибки в тексте
//         $name = $this->get_theme_setting_name('main_spelling_mistake', $profile);
//         $name = 'theme_opentechnology/'.$name;
//         $title = get_string('settings_main_spelling_mistake', 'theme_opentechnology');
//         $description = get_string('settings_main_spelling_mistake_desc', 'theme_opentechnology');
//         $default = 1;
//         $choices = [
//             0 => get_string('settings_main_spelling_mistake_disable', 'theme_opentechnology'),
//             1 => get_string('settings_main_spelling_mistake_enable', 'theme_opentechnology'),
//         ];
//         $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
//         $settings->add($setting);
        
//         // Дополнительный CSS подвала
//         $cssprocessor = new cssprocessor();
//         $name = $this->get_theme_setting_name('main_customcss', $profile);
//         $fullname = 'theme_opentechnology/'.$name;
//         $title = get_string('settings_main_customcss','theme_opentechnology');
//         $description = get_string('settings_main_customcss_desc', 'theme_opentechnology', $cssprocessor->customcss_description($name));
//         $setting = new admin_setting_configtextarea($fullname, $title, $description, '');
//         $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//         $settings->add($setting);
        
//         // Классы, которые надо докинуть в body
//         $name = $this->get_theme_setting_name('main_custombodyinnerclasses', $profile);
//         $name = 'theme_opentechnology/'.$name;
//         $title = get_string('settings_main_custombodyinnerclasses', 'theme_opentechnology');
//         $description = get_string('settings_main_custombodyinnerclasses', 'theme_opentechnology');
//         $default = '';
//         $setting = new admin_setting_configtext($name, $title, $description, $default);
//         $settings->add($setting);
//     }
    
    /**
     * Добавить раздел настроек адаптивности профиля
     *
     * @param admin_category $parentcategory - Родительская категория настроек
     * @param base $profile - Экземпляр профиля
     *
     * @return void
     */
    protected function admin_settingpage_add_profile_settings_responsive(&$settings, $profile)
    {
        // Адаптивность таблиц
        $name = $this->get_theme_setting_name('responsive_tables', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_responsive_tables', 'theme_opentechnology');
        $description = get_string('settings_responsive_tables_desc', 'theme_opentechnology');
        $default = 0;
        $choices = [
            0 => get_string('settings_responsive_tables_disable', 'theme_opentechnology'),
            1 => get_string('settings_responsive_tables_enable', 'theme_opentechnology'),
        ];
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $settings->add($setting);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        
        // Вертикальные заголовки таблицы отчета по оценкам
        $name = $this->get_theme_setting_name('gradereport_table', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_gradereport_table', 'theme_opentechnology');
        $description = get_string('settings_gradereport_table_desc', 'theme_opentechnology');
        $default = 0;
        $choices = [
            0 => get_string('settings_gradereport_table_disable', 'theme_opentechnology'),
            1 => get_string('settings_gradereport_table_enable', 'theme_opentechnology'),
            2 => get_string('settings_gradereport_table_user_preference', 'theme_opentechnology')
        ];
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $settings->add($setting);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
    }
    
    /**
     * Добавить раздел настроек шапки профиля
     *
     * @param admin_category $parentcategory - Родительская категория настроек
     * @param base $profile - Экземпляр профиля
     *
     * @return void
     */
    protected function admin_settingpage_add_profile_settings_header(&$settings, $profile)
    {
        global $CFG;
        
        // Заголовок - общие настройки
        $name = $this->get_theme_setting_name('header_title', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_header_title', 'theme_opentechnology');
        $description = get_string('settings_header_title_desc', 'theme_opentechnology');
        $setting = new admin_setting_heading($name, $title, $description);
        $settings->add($setting);
        
            // Прилипающая шапка
            $name = $this->get_theme_setting_name('header_sticky', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_header_sticky', 'theme_opentechnology');
            $description = get_string('settings_header_sticky_desc', 'theme_opentechnology');
            $choices = [
//                 1 => get_string('settings_header_sticky_cshtop', 'theme_opentechnology'),
//                 2 => get_string('settings_header_sticky_headertoptext', 'theme_opentechnology'),
                3 => get_string('settings_header_sticky_header', 'theme_opentechnology'),
                4 => get_string('settings_header_sticky_dockpanel', 'theme_opentechnology'),
//                 5 => get_string('settings_header_sticky_regioncontentheading', 'theme_opentechnology'),
                6 => get_string('settings_header_sticky_navbar', 'theme_opentechnology'),
            ];
            $setting = new \admin_setting_configselect_with_advanced($name, $title, $description, ['value' => 3, 'adv' => false], $choices);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);

            // Фоновое изображение
            $name = $this->get_theme_setting_name('header_backgroundimage', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_header_backgroundimage', 'theme_opentechnology');
            $description = get_string('settings_header_backgroundimage_desc', 'theme_opentechnology');
            $filearea = $this->get_theme_setting_filearea('header_backgroundimage', $profile);
            $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
        
        // Заголовок - шапка сайта
        $name = $this->get_theme_setting_name('header_top_title', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_header_top_title', 'theme_opentechnology');
        $description = get_string('settings_header_top_title_desc', 'theme_opentechnology');
        $setting = new admin_setting_heading($name, $title, $description);
        $settings->add($setting);
        
            // Текст верха шапки
            $name = $this->get_theme_setting_name('header_top_text', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_header_top_text', 'theme_opentechnology');
            $description = get_string('settings_header_top_text_desc', 'theme_opentechnology');
            $default = '';
            $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
            $settings->add($setting);
        
        /*
        // Плавающая шапка
        $name = $this->get_theme_setting_name('header_floating', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_header_floating', 'theme_opentechnology');
        $description = get_string('settings_header_floating_desc', 'theme_opentechnology');
        $default = 0;
        $choices = [
            0 => get_string('settings_header_floating_disable', 'theme_opentechnology'),
            1 => get_string('settings_header_floating_enable', 'theme_opentechnology'),
        ];
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $settings->add($setting);
        */
    
        // Заголовок - логотип
        $name = $this->get_theme_setting_name('header_logo_title', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_header_logo_title', 'theme_opentechnology');
        $description = get_string('settings_header_logo_title_desc', 'theme_opentechnology');
        $setting = new admin_setting_heading($name, $title, $description);
        $settings->add($setting);
    
            // Логотип
            $name = $this->get_theme_setting_name('header_logoimage', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_header_logoimage', 'theme_opentechnology');
            $description = get_string('settings_header_logoimage_desc', 'theme_opentechnology');
            $filearea = $this->get_theme_setting_filearea('header_logoimage', $profile);
            $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // URL логотипа
            $name = $this->get_theme_setting_name('header_logo_link', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_header_logo_link', 'theme_opentechnology');
            $description = get_string('settings_header_logo_link_desc', 'theme_opentechnology');
            $default = new moodle_url('/');
            $param = PARAM_URL;
            $setting = new admin_setting_configtext($name, $title, $description, (string)$default, $param);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
        
            // Текст логотипа
            $name = $this->get_theme_setting_name('header_logo_text', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_header_logo_text', 'theme_opentechnology');
            $description = get_string('settings_header_logo_text_desc', 'theme_opentechnology');
            $default = '';
            $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
            $settings->add($setting);
            
            // Отступы логотипа
            $name = $this->get_theme_setting_name('header_logoimage_padding', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_header_logoimage_padding', 'theme_opentechnology');
            $description = get_string('settings_header_logoimage_padding_desc', 'theme_opentechnology');
            $default = '';
            $setting = new admin_setting_configtext($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
    
        // Заголовок - блок описания
        $name = $this->get_theme_setting_name('header_text_title', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_header_text_title', 'theme_opentechnology');
        $description = get_string('settings_header_text_title_desc', 'theme_opentechnology');
        $setting = new admin_setting_heading($name, $title, $description);
        $settings->add($setting);
    
            // Текст описания
            $name = $this->get_theme_setting_name('header_text', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_header_text', 'theme_opentechnology');
            $description = get_string('settings_header_text_desc', 'theme_opentechnology');
            $default = '';
            $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
            $settings->add($setting);
            
            // Отступы блока описания
            $name = $this->get_theme_setting_name('header_text_padding', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_header_text_padding', 'theme_opentechnology');
            $description = get_string('settings_header_text_padding_desc', 'theme_opentechnology');
            $default = '';
            $setting = new admin_setting_configtext($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
    
        // Заголовок - пользовательское меню
        $name = $this->get_theme_setting_name('header_usermenu_title', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_header_usermenu_title', 'theme_opentechnology');
        $description = get_string('settings_header_usermenu_title_desc', 'theme_opentechnology');
        $setting = new admin_setting_heading($name, $title, $description);
        $settings->add($setting);

            // Добавить кнопку с ссылкой на витрину
            $name = $this->get_theme_setting_name('header_link_crw', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_header_link_crw', 'theme_opentechnology');
            $description = get_string('settings_header_link_crw_desc', 'theme_opentechnology');
            $default = 0;
            $choices = [
                0 => get_string('settings_header_link_crw_disable', 'theme_opentechnology'),
                1 => get_string('settings_header_link_crw_enable', 'theme_opentechnology'),
            ];
            $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
            $settings->add($setting);
        
            // Добавить кнопку с ссылкой на портфолио
            $name = $this->get_theme_setting_name('header_link_portfolio', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_header_link_portfolio', 'theme_opentechnology');
            $description = get_string('settings_header_link_portfolio_desc', 'theme_opentechnology');
            $default = 0;
            $choices = [
                0 => get_string('settings_header_link_portfolio_disable', 'theme_opentechnology'),
                1 => get_string('settings_header_link_portfolio_enable', 'theme_opentechnology'),
            ];
            $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
            $settings->add($setting);
        
            // Добавить кнопку-идикатор с ссылкой на сообщения
            $name = $this->get_theme_setting_name('header_link_unread_messages', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_header_link_unread_messages', 'theme_opentechnology');
            $description = get_string('settings_header_link_unread_messages_desc', 'theme_opentechnology');
            $default = 1;
            $choices = [
                0 => get_string('settings_header_link_unread_messages_disable', 'theme_opentechnology'),
                1 => get_string('settings_header_link_unread_messages_enable', 'theme_opentechnology'),
            ];
            $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
            $settings->add($setting);
            
            // Добавить кнопку поиск
            $name = $this->get_theme_setting_name('header_link_search', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_header_link_search', 'theme_opentechnology');
            $description = get_string('settings_header_link_search_desc', 'theme_opentechnology');
            $default = 1;
            $choices = [
                0 => get_string('settings_header_link_search_disable', 'theme_opentechnology'),
                1 => get_string('settings_header_link_search_enable', 'theme_opentechnology'),
            ];
            $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
            $settings->add($setting);
        
            // Отступы пользовательского меню
            $name = $this->get_theme_setting_name('header_usermenu_padding', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_header_usermenu_padding', 'theme_opentechnology');
            $description = get_string('settings_header_usermenu_padding_desc', 'theme_opentechnology');
            $default = '';
            $setting = new admin_setting_configtext($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Скрыть/показать кнопку пользовательского меню
            $name = $this->get_theme_setting_name('header_usermenu_hide_caret', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_header_usermenu_hide_caret', 'theme_opentechnology');
            $description = get_string('settings_header_usermenu_hide_caret_desc', 'theme_opentechnology');
            $default = 0;
            $choices = [
                0 => get_string('user_menu_caret_hide', 'theme_opentechnology'),
                1 => get_string('user_menu_caret_show', 'theme_opentechnology'),
            ];
            $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
        // Заголовок - персональное меню
        $name = $this->get_theme_setting_name('header_custommenu_title', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_header_custommenu_title', 'theme_opentechnology');
        $description = get_string('settings_header_custommenu_title_desc', 'theme_opentechnology');
        $setting = new admin_setting_heading($name, $title, $description);
        $settings->add($setting);
        
            // Расположение персонального меню
            $name = $this->get_theme_setting_name('header_custommenu_location', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_header_custommenu_location', 'theme_opentechnology');
            $description = get_string('settings_header_custommenu_location_desc', 'theme_opentechnology');
            $default = 0;
            $choices = [
                6 => get_string('settings_header_custommenu_location_top_left', 'theme_opentechnology'),
                7 => get_string('settings_header_custommenu_location_top_right', 'theme_opentechnology'),
                1 => get_string('settings_header_custommenu_location_above_logo', 'theme_opentechnology'),
                4 => get_string('settings_header_custommenu_location_above_usermenu', 'theme_opentechnology'),
                5 => get_string('settings_header_custommenu_location_under_logo', 'theme_opentechnology'),
                2 => get_string('settings_header_custommenu_location_under_usermenu', 'theme_opentechnology'),
                0 => get_string('settings_header_custommenu_location_bottom_left', 'theme_opentechnology'),
                8 => get_string('settings_header_custommenu_location_bottom_right', 'theme_opentechnology'),
                3 => get_string('settings_header_custommenu_location_profile_custom_position', 'theme_opentechnology')
            ];
            $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
            $settings->add($setting);
            
        // Заголовок - Док-панель
        $name = $this->get_theme_setting_name('header_dockpanel_title', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_header_dockpanel_title', 'theme_opentechnology');
        $description = get_string('settings_header_dockpanel_title_desc', 'theme_opentechnology');
        $setting = new admin_setting_heading($name, $title, $description);
        $settings->add($setting);
    
            // Текстура док-панели
            $name = $this->get_theme_setting_name('header_dockpanel_texture', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_header_dockpanel_texture', 'theme_opentechnology');
            $default = '';
            $choices = ['' => get_string('settings_header_dockpanel_texture_none', 'theme_opentechnology')];
            $description = '';
            $files = glob($CFG->dirroot."/theme/opentechnology/pix/texture/*.png");
            if ( ! empty($files) )
            {// Есть загруженные текстуры
                $description = '<ul class="media-list">';
                foreach ( $files as $file )
                {
                
                    // Получение имени файла
                    $path = explode('/', $file);
                    $path = end($path);
                    $filename = explode('.', $path);
                    $filename = reset($filename);
                    // Добавление файла в список
                    $choices[$filename] = $filename;
                    // Добавление отображения текстуры
                    $description .=
                    '<li class="media">
                                <div class="media-body">
                                    <span class="media-heading">'.$filename.'</span>
                                    <div class="media">
                                        <img class="media-object col-md-12" src="/theme/opentechnology/pix/texture/'.$path.'">
                                    </div>
                                </div>
                            </li>';
                }
                $description .= '</ul>';
            }
            $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
        
            // Добавить заголовок страницы
            $name = $this->get_theme_setting_name('header_dockpanel_header', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_header_dockpanel_header', 'theme_opentechnology');
            $description = get_string('settings_header_dockpanel_header_desc', 'theme_opentechnology');
            $default = 1;
            $choices = [
                0 => get_string('settings_header_dockpanel_header_disable', 'theme_opentechnology'),
                1 => get_string('settings_header_dockpanel_header_enable', 'theme_opentechnology'),
            ];
            $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
            $settings->add($setting);
            
            // Добавить заголовок страницы в контентную область главной страницы
            $name = $this->get_theme_setting_name('header_content_header', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_header_content_header', 'theme_opentechnology');
            $description = get_string('settings_header_content_header_desc', 'theme_opentechnology');
            $default = 0;
            $choices = [
                0 => get_string('settings_header_content_header_disable', 'theme_opentechnology'),
                1 => get_string('settings_header_content_header_enable', 'theme_opentechnology'),
            ];
            $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
            $settings->add($setting);
    }
    
    /**
     * Добавить раздел настроек подвала профиля
     *
     * @param admin_category $parentcategory - Родительская категория настроек
     * @param base $profile - Экземпляр профиля
     *
     * @return void
     */
    protected function admin_settingpage_add_profile_settings_footer(&$settings, $profile)
    {
        global $CFG;
                
        // Заголовок - подвал сайта
        $name = $this->get_theme_setting_name('footer_title', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_footer_title', 'theme_opentechnology');
        $description = get_string('settings_footer_title_desc', 'theme_opentechnology');
        $setting = new admin_setting_heading($name, $title, $description);
        $settings->add($setting);
        
        // Фоновое изображение
        $name = $this->get_theme_setting_name('footer_backgroundimage', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_footer_backgroundimage', 'theme_opentechnology');
        $description = get_string('settings_footer_backgroundimage_desc', 'theme_opentechnology');
        $filearea = $this->get_theme_setting_filearea('footer_backgroundimage', $profile);
        $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        $settings->add($setting);
        
        // Текстура рамки
        $name = $this->get_theme_setting_name('footer_border_texture', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_footer_border_texture', 'theme_opentechnology');
        $default = '';
        $choices = ['' => get_string('settings_footer_border_texture_none', 'theme_opentechnology')];
        $description = '';
        $files = glob($CFG->dirroot."/theme/opentechnology/pix/texture/*.png");
        if ( ! empty($files) )
        {// Есть загруженные текстуры
            $description = '<ul class="media-list">';
            foreach ( $files as $file )
            {
            
                // Получение имени файла
                $path = explode('/', $file);
                $path = end($path);
                $filename = explode('.', $path);
                $filename = reset($filename);
                // Добавление файла в список
                $choices[$filename] = $filename;
                // Добавление отображения текстуры
                $description .=
                '<li class="media">
                                    <div class="media-body">
                                        <span class="media-heading">'.$filename.'</span>
                                        <div class="media">
                                            <img class="media-object col-md-12" src="/theme/opentechnology/pix/texture/'.$path.'">
                                        </div>
                                    </div>
                                </li>';
            }
            $description .= '</ul>';
        }
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        $settings->add($setting);
        
        // Логотип
        $name = $this->get_theme_setting_name('footer_logoimage', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_footer_logoimage', 'theme_opentechnology');
        $description = get_string('settings_footer_logoimage_desc', 'theme_opentechnology');
        $filearea = $this->get_theme_setting_filearea('footer_logoimage', $profile);
        $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        $settings->add($setting);
        
        // Ширина логотипа
        $name = $this->get_theme_setting_name('footer_logoimage_width', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_footer_logoimage_width', 'theme_opentechnology');
        $description = get_string('settings_footer_logoimage_width_desc', 'theme_opentechnology');
        $default = 3;
        $choices = [
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
            6 => 6,
            7 => 7,
            8 => 8,
            9 => 9,
            10 => 10,
            11 => 11,
            12 => 12,
        ];
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $settings->add($setting);
        
        // Текст описания к логотипу
        $name = $this->get_theme_setting_name('footer_logoimage_text', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_footer_logoimage_text', 'theme_opentechnology');
        $description = get_string('settings_footer_logoimage_text_desc', 'theme_opentechnology');
        $default = '';
        $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
        $settings->add($setting);
        
        // Ссылки на социальные сети
        $name = $this->get_theme_setting_name('footer_social_links', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_footer_social_links', 'theme_opentechnology');
        $description = get_string('settings_footer_social_links_desc', 'theme_opentechnology');
        $default = '';
        $setting = new admin_setting_configtextarea($name, $title, $description, $default);
        $settings->add($setting);
        
        // Текст описания
        $name = $this->get_theme_setting_name('footer_text', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_footer_text', 'theme_opentechnology');
        $description = get_string('settings_footer_text_desc', 'theme_opentechnology');
        $default = '';
        $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
        $settings->add($setting);
        
        // Текст копирайта
        $name = $this->get_theme_setting_name('copyright_text', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_copyright_text', 'theme_opentechnology');
        $description = get_string('settings_copyright_text_desc', 'theme_opentechnology');
        $default = '';
        $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
        $settings->add($setting);
    }
    
    /**
     * Добавить раздел настроек слайдера профиля
     *
     * @param admin_category $parentcategory - Родительская категория настроек
     * @param base $profile - Экземпляр профиля
     *
     * @return void
     */
    protected function admin_settingpage_add_profile_settings_slider(&$settings, $profile)
    {
        // Заголовок - Слайдер
        /*
        $name = $this->get_theme_setting_name('slider_title', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_slider_title', 'theme_opentechnology');
        $description = get_string('settings_slider_title_desc', 'theme_opentechnology');
        $setting = new admin_setting_heading($name, $title, $description);
        $settings->add($setting);
        
        // Включение слайдера
        $name = $this->get_theme_setting_name('slider_enable', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_slider_enable', 'theme_opentechnology');
        $description = get_string('settings_slider_enable_desc', 'theme_opentechnology');
        $default = 0;
        $choices = [
            0 => get_string('settings_slider_enable_disable', 'theme_opentechnology'),
            1 => get_string('settings_slider_enable_beforelogin', 'theme_opentechnology'),
            2 => get_string('settings_slider_enable_afterlogin', 'theme_opentechnology'),
            3 => get_string('settings_slider_enable_always', 'theme_opentechnology'),
        ];
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $settings->add($setting);
        
        // Высота баннера
        $name = $this->get_theme_setting_name('slider_height', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_slider_height', 'theme_opentechnology');
        $description = get_string('settings_slider_height_desc', 'theme_opentechnology');
        $default = '400px';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $settings->add($setting);
        
        // Ширина баннера
        $name = $this->get_theme_setting_name('slider_width', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_slider_width', 'theme_opentechnology');
        $description = get_string('settings_slider_width_desc', 'theme_opentechnology');
        $default = '100%';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $settings->add($setting);
        
        // Время перелистывания баннера
        $name = $this->get_theme_setting_name('slider_sleep', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_slider_sleep', 'theme_opentechnology');
        $description = get_string('settings_slider_sleep_desc', 'theme_opentechnology');
        $default = '1500';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $settings->add($setting);
        
        // Файлы слайдера
        $name = $this->get_theme_setting_name('slider_images', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_slider_images', 'theme_opentechnology');
        $options = ['maxfiles' => 10];
        $filearea = $this->get_theme_setting_filearea('slider_images', $profile);
        $setting = new admin_setting_configstoredfile($name, $title, '', $filearea, 0, $options);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        $settings->add($setting);
        
        // Тип отображения изображения слайдера
        $name = $this->get_theme_setting_name('slider_background_size', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_slider_background_size', 'theme_opentechnology');
        $description = get_string('settings_slider_background_size_desc', 'theme_opentechnology');
        $default = 0;
        $choices = [
            0 => get_string('slider_background_size_cover', 'theme_opentechnology'),
            1 => get_string('slider_background_size_contain', 'theme_opentechnology'),
            2 => get_string('slider_background_size_fixed', 'theme_opentechnology')
        ];
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $settings->add($setting);*/
    }
    
    /**
     * Добавить раздел настроек главной страницы профиля
     *
     * @param admin_category $parentcategory - Родительская категория настроек
     * @param base $profile - Экземпляр профиля
     *
     * @return void
     */
    protected function admin_settingpage_add_profile_settings_homepage(&$settings, $profile)
    {
        /*
        // Заголовок - Главная страница
        $name = $this->get_theme_setting_name('homepage_title', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_homepage_title', 'theme_opentechnology');
        $description = get_string('settings_homepage_title_desc', 'theme_opentechnology');
        $setting = new admin_setting_heading($name, $title, $description);
        $settings->add($setting);
    
        // Отображение хлебных крошек на главной
        $name = $this->get_theme_setting_name('homepage_display_breadcrumbs', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_homepage_display_breadcrumbs', 'theme_opentechnology');
        $description = get_string('settings_homepage_display_breadcrumbs_desc', 'theme_opentechnology');
        $default = 1;
        $choices = [
            0 => get_string('settings_homepage_display_breadcrumbs_disable', 'theme_opentechnology'),
            1 => get_string('settings_homepage_display_breadcrumbs_enable', 'theme_opentechnology'),
        ];
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $settings->add($setting);
        */
    }
    
    /**
     * Добавить раздел настроек фонов для
     *
     * @param admin_category $parentcategory - Родительская категория настроек
     * @@param base $profile - Экземпляр профиля
     *
     * @return void
     */
    protected function admin_settingpage_add_profile_settings_pagebacks(&$settings, $profile)
    {
        // cs*  - collapsiblesection (сворачиваемые секции, шторка)
        // reg* - region (регион, зона для блоков)
        $pagebacks = [
            'cs_htop',
            'h_text',
            'header',
            'dockpanel',
            'reg_heading',
            'breadcrumbs',
            'cs_ctop',
            'content',
            'reg_footing',
            'cs_cbot',
            'f_border',
            'footer'
        ];
        
        foreach($pagebacks as $pageback)
        {
            // Заголовок
            $name = $this->get_theme_setting_name('pb_'.$pageback.'_title', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_pageback_'.$pageback.'_title', 'theme_opentechnology');
            $description = get_string('settings_pageback_'.$pageback.'_title_desc', 'theme_opentechnology');
            $setting = new admin_setting_heading($name, $title, $description);
            $settings->add($setting);
            
            // Цвет фона
            $name = $this->get_theme_setting_name('color_pb_'.$pageback.'_backgroundcolor', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_pageback_'.$pageback.'_backgroundcolor', 'theme_opentechnology');
            $description = get_string('settings_pageback_'.$pageback.'_backgroundcolor_desc', 'theme_opentechnology');
            $default = '';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Изображение фона
            $name = $this->get_theme_setting_name('pb_'.$pageback.'_backgroundimage', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_pageback_'.$pageback.'_backgroundimage', 'theme_opentechnology');
            $description = get_string('settings_pageback_'.$pageback.'_backgroundimage_desc', 'theme_opentechnology');
            $filearea = $this->get_theme_setting_filearea('pb_'.$pageback.'_backgroundimage', $profile);
            $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Изображение фона
            $name = $this->get_theme_setting_name('pb_'.$pageback.'_unlimit_width', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_pageback_'.$pageback.'_unlimit_width', 'theme_opentechnology');
            $description = get_string('settings_pageback_'.$pageback.'_unlimit_width_desc', 'theme_opentechnology');
            $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
            $settings->add($setting);
        }
    }
    
    /**
     * Добавить раздел настроек цветовой схемы профиля
     *
     * @param admin_category $parentcategory - Родительская категория настроек
     * @@param base $profile - Экземпляр профиля
     *
     * @return void
     */
    protected function admin_settingpage_add_profile_settings_color(&$settings, $profile)
    {
        global $CFG;
        
        // Опции яркости иконок
        $brightnessoptions = [
            0 => get_string('settings_icon_brightness_auto', 'theme_opentechnology'),
            1 => get_string('settings_icon_brightness_0', 'theme_opentechnology'),
            2 => get_string('settings_icon_brightness_70', 'theme_opentechnology'),
            3 => get_string('settings_icon_brightness_100', 'theme_opentechnology'),
            4 => get_string('settings_icon_brightness_175', 'theme_opentechnology'),
            5 => get_string('settings_icon_brightness_300', 'theme_opentechnology'),
        ];
        
//         // Заголовок - Основные цвета темы
//         $name = $this->get_theme_setting_name('theme_color_title', $profile);
//         $name = 'theme_opentechnology/'.$name;
//         $title = get_string('settings_theme_color_title', 'theme_opentechnology');
//         $description = get_string('settings_theme_color_title_desc', 'theme_opentechnology');
//         $setting = new admin_setting_heading($name, $title, $description);
//         $settings->add($setting);
        
//         foreach (self::$themecolors as $colorname)
//         {
//             // Настройка цвета темы
//             $name = $this->get_theme_setting_name('theme_color_'.$colorname, $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_theme_color_'.$colorname, 'theme_opentechnology');
//             $description = get_string('settings_theme_color_'.$colorname.'_desc', 'theme_opentechnology');
//             $default = '';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
//         }

        // Заголовок - Цветовая схема шапки
        $name = $this->get_theme_setting_name('color_header_title', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_color_header_title', 'theme_opentechnology');
        $description = get_string('settings_color_header_title_desc', 'theme_opentechnology');
        $setting = new admin_setting_heading($name, $title, $description);
        $settings->add($setting);
    
//             // Цвет фона шапки
//             $name = $this->get_theme_setting_name('color_header_backgroundcolor', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_header_backgroundcolor', 'theme_opentechnology');
//             $description = get_string('settings_color_header_backgroundcolor_desc', 'theme_opentechnology');
//             $default = '#F5EFDA';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет текста фона шапки
//             $name = $this->get_theme_setting_name('color_header_backgroundcolor_text', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_header_backgroundcolor_text', 'theme_opentechnology');
//             $description = get_string('settings_color_header_backgroundcolor_desc_text', 'theme_opentechnology');
//             $default = '#636361';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Яркость иконки для фона шапки
//             $name = $this->get_theme_setting_name('color_header_backgroundcolor_icon_brightness', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_header_backgroundcolor_icon_brightness', 'theme_opentechnology');
//             $description = get_string('settings_color_header_backgroundcolor_desc_icon_brightness', 'theme_opentechnology');
//             $default = '0';
//             $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Базовый цвет шапки
//             $name = $this->get_theme_setting_name('color_header_basecolor', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_header_basecolor', 'theme_opentechnology');
//             $description = get_string('settings_color_header_basecolor_desc', 'theme_opentechnology');
//             $default = '#165373';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Базовый цвет текста шапки
//             $name = $this->get_theme_setting_name('color_header_basecolor_text', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_header_basecolor_text', 'theme_opentechnology');
//             $description = get_string('settings_color_header_basecolor_desc_text', 'theme_opentechnology');
//             $default = '#FFFFFF';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Яркость иконки для базового цвета шапки
//             $name = $this->get_theme_setting_name('color_header_basecolor_icon_brightness', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_header_basecolor_icon_brightness', 'theme_opentechnology');
//             $description = get_string('settings_color_header_basecolor_desc_icon_brightness', 'theme_opentechnology');
//             $default = '0';
//             $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
            // Цвет фона верхней полосы с текстом в шапке
            $name = $this->get_theme_setting_name('color_header_topbasecolor', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_header_topbasecolor', 'theme_opentechnology');
            $description = get_string('settings_color_header_topbasecolor_desc', 'theme_opentechnology');
            $default = '';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Цвет текста верхней полосы с текстом в шапке
            $name = $this->get_theme_setting_name('color_header_topbasecolor_text', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_header_topbasecolor_text', 'theme_opentechnology');
            $description = get_string('settings_color_header_topbasecolor_desc_text', 'theme_opentechnology');
            $default = '';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
//             // Цвет элементов шапки
//             $name = $this->get_theme_setting_name('color_header_elementscolor', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_header_elementscolor', 'theme_opentechnology');
//             $description = get_string('settings_color_header_elementscolor_desc', 'theme_opentechnology');
//             $default = '#69A05A';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет текста элементов шапки
//             $name = $this->get_theme_setting_name('color_header_elementscolor_text', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_header_elementscolor_text', 'theme_opentechnology');
//             $description = get_string('settings_color_header_elementscolor_desc_text', 'theme_opentechnology');
//             $default = '';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Яркость иконки для элементов шапки
//             $name = $this->get_theme_setting_name('color_header_elementscolor_icon_brightness', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_header_elementscolor_icon_brightness', 'theme_opentechnology');
//             $description = get_string('settings_color_header_elementscolor_desc_icon_brightness', 'theme_opentechnology');
//             $default = '0';
//             $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет активных элементов шапки
//             $name = $this->get_theme_setting_name('color_header_elementscolor_active', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_header_elementscolor_active', 'theme_opentechnology');
//             $description = get_string('settings_color_header_elementscolor_active_desc', 'theme_opentechnology');
//             $default = '#4D7F40';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет текста активных элементов шапки
//             $name = $this->get_theme_setting_name('color_header_elementscolor_active_text', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_header_elementscolor_active_text', 'theme_opentechnology');
//             $description = get_string('settings_color_header_elementscolor_active_desc_text', 'theme_opentechnology');
//             $default = '#FFFFFF';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Яркость иконки для активных элементов шапки
//             $name = $this->get_theme_setting_name('color_header_elementscolor_active_icon_brightness', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_header_elementscolor_active_icon_brightness', 'theme_opentechnology');
//             $description = get_string('settings_color_header_elementscolor_active_desc_icon_brightness', 'theme_opentechnology');
//             $default = '0';
//             $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
            // Цвет пользовательского меню шапки
            $name = $this->get_theme_setting_name('color_header_usermenubackgroundcolor', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_header_usermenubackgroundcolor', 'theme_opentechnology');
            $description = get_string('settings_color_header_usermenubackgroundcolor_desc', 'theme_opentechnology');
            $default = '#FFFFFF';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Цвет текста пользовательского меню шапки
            $name = $this->get_theme_setting_name('color_header_usermenubackgroundcolor_text', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_header_usermenubackgroundcolor_text', 'theme_opentechnology');
            $description = get_string('settings_color_header_usermenubackgroundcolor_text_desc', 'theme_opentechnology');
            $default = '';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Яркость иконки для пользовательского меню шапки
            $name = $this->get_theme_setting_name('color_header_usermenubackgroundcolor_icon_brightness', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_header_usermenubackgroundcolor_icon_brightness', 'theme_opentechnology');
            $description = get_string('settings_color_header_usermenubackgroundcolor_desc_icon_brightness', 'theme_opentechnology');
            $default = '0';
            $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Цвет элементов персонального меню шапки
            $name = $this->get_theme_setting_name('color_header_custommenuelementscolor', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_header_custommenuelementscolor', 'theme_opentechnology');
            $description = get_string('settings_color_header_custommenuelementscolor_desc', 'theme_opentechnology');
            $default = '';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Цвет текста элементов персонального меню шапки
            $name = $this->get_theme_setting_name('color_header_custommenuelementscolor_text', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_header_custommenuelementscolor_text', 'theme_opentechnology');
            $description = get_string('settings_color_header_custommenuelementscolor_text_desc', 'theme_opentechnology');
            $default = '';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Яркость иконки для элементов персонального меню шапки
            $name = $this->get_theme_setting_name('color_header_custommenuelementscolor_icon_brightness', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_header_custommenuelementscolor_icon_brightness', 'theme_opentechnology');
            $description = get_string('settings_color_header_custommenuelementscolor_desc_icon_brightness', 'theme_opentechnology');
            $default = '0';
            $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            
            
            
            
            // Цвет элементов персонального меню шапки
            $name = $this->get_theme_setting_name('color_header_custommenuelementscolor_active', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_header_custommenuelementscolor_active', 'theme_opentechnology');
            $description = get_string('settings_color_header_custommenuelementscolor_active_desc', 'theme_opentechnology');
            $default = '';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Цвет текста элементов персонального меню шапки
            $name = $this->get_theme_setting_name('color_header_custommenuelementscolor_active_text', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_header_custommenuelementscolor_active_text', 'theme_opentechnology');
            $description = get_string('settings_color_header_custommenuelementscolor_active_text_desc', 'theme_opentechnology');
            $default = '';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Яркость иконки для элементов персонального меню шапки
            $name = $this->get_theme_setting_name('color_header_custommenuelementscolor_active_icon_brightness', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_header_custommenuelementscolor_active_icon_brightness', 'theme_opentechnology');
            $description = get_string('settings_color_header_custommenuelementscolor_active_desc_icon_brightness', 'theme_opentechnology');
            $default = '0';
            $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            
            
            
            // Цвет персонального меню шапки
            $name = $this->get_theme_setting_name('color_header_custommenubackgroundcolor', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_header_custommenubackgroundcolor', 'theme_opentechnology');
            $description = get_string('settings_color_header_custommenubackgroundcolor_desc', 'theme_opentechnology');
            $default = '';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Цвет текста персонального меню шапки
            $name = $this->get_theme_setting_name('color_header_custommenubackgroundcolor_text', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_header_custommenubackgroundcolor_text', 'theme_opentechnology');
            $description = get_string('settings_color_header_custommenubackgroundcolor_text_desc', 'theme_opentechnology');
            $default = '';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Яркость иконки для персонального меню шапки
            $name = $this->get_theme_setting_name('color_header_custommenubackgroundcolor_icon_brightness', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_header_custommenubackgroundcolor_icon_brightness', 'theme_opentechnology');
            $description = get_string('settings_color_header_custommenubackgroundcolor_desc_icon_brightness', 'theme_opentechnology');
            $default = '0';
            $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            
            
            
            // Цвет персонального меню шапки
            $name = $this->get_theme_setting_name('color_header_custommenubackgroundcolor_active', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_header_custommenubackgroundcolor_active', 'theme_opentechnology');
            $description = get_string('settings_color_header_custommenubackgroundcolor_active_desc', 'theme_opentechnology');
            $default = '';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Цвет текста персонального меню шапки
            $name = $this->get_theme_setting_name('color_header_custommenubackgroundcolor_active_text', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_header_custommenubackgroundcolor_active_text', 'theme_opentechnology');
            $description = get_string('settings_color_header_custommenubackgroundcolor_active_text_desc', 'theme_opentechnology');
            $default = '';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Яркость иконки для персонального меню шапки
            $name = $this->get_theme_setting_name('color_header_custommenubackgroundcolor_active_icon_brightness', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_header_custommenubackgroundcolor_active_icon_brightness', 'theme_opentechnology');
            $description = get_string('settings_color_header_custommenubackgroundcolor_active_desc_icon_brightness', 'theme_opentechnology');
            $default = '0';
            $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
    
        // Заголовок - Цветовая схема контента
        $name = $this->get_theme_setting_name('color_content_title', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_color_content_title', 'theme_opentechnology');
        $description = get_string('settings_color_content_title_desc', 'theme_opentechnology');
        $setting = new admin_setting_heading($name, $title, $description);
        $settings->add($setting);
        
//             // Цвет фона контента
//             $name = $this->get_theme_setting_name('color_content_backgroundcolor', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_content_backgroundcolor', 'theme_opentechnology');
//             $description = get_string('settings_color_content_backgroundcolor_desc', 'theme_opentechnology');
//             $default = '#FFFFFF';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет текста фона контента
//             $name = $this->get_theme_setting_name('color_content_backgroundcolor_text', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_content_backgroundcolor_text', 'theme_opentechnology');
//             $description = get_string('settings_color_content_backgroundcolor_desc_text', 'theme_opentechnology');
//             $default = '';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
            // Цвет заголовка элемента курса
            $name = $this->get_theme_setting_name('color_content_mod_header_text_backgroundcolor', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_content_mod_header_text_backgroundcolor', 'theme_opentechnology');
            $description = get_string('settings_color_content_mod_header_text_backgroundcolor_desc', 'theme_opentechnology');
            $default = '#ffffff';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Цвет фона заголовка элемента курса
            $name = $this->get_theme_setting_name('color_content_mod_header_backgroundcolor', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_content_mod_header_backgroundcolor', 'theme_opentechnology');
            $description = get_string('settings_color_content_mod_header_backgroundcolor_desc', 'theme_opentechnology');
            $default = '#a7a7a7';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
//             // Яркость иконки для фона контента
//             $name = $this->get_theme_setting_name('color_content_backgroundcolor_icon_brightness', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_content_backgroundcolor_icon_brightness', 'theme_opentechnology');
//             $description = get_string('settings_color_content_backgroundcolor_desc_icon_brightness', 'theme_opentechnology');
//             $default = '0';
//             $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Базовый цвет контента
//             $name = $this->get_theme_setting_name('color_content_basecolor', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_content_basecolor', 'theme_opentechnology');
//             $description = get_string('settings_color_content_basecolor_desc', 'theme_opentechnology');
//             $default = '#FFFFFF';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Базовый цвет текста контента
//             $name = $this->get_theme_setting_name('color_content_basecolor_text', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_content_basecolor_text', 'theme_opentechnology');
//             $description = get_string('settings_color_content_basecolor_desc_text', 'theme_opentechnology');
//             $default = '';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Яркость иконки для базового цвета контента
//             $name = $this->get_theme_setting_name('color_content_basecolor_icon_brightness', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_content_basecolor_icon_brightness', 'theme_opentechnology');
//             $description = get_string('settings_color_content_basecolor_desc_icon_brightness', 'theme_opentechnology');
//             $default = '0';
//             $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет элементов контента
//             $name = $this->get_theme_setting_name('color_content_elementscolor', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_content_elementscolor', 'theme_opentechnology');
//             $description = get_string('settings_color_content_elementscolor_desc', 'theme_opentechnology');
//             $default = '#69A05A';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет элементов контента
//             $name = $this->get_theme_setting_name('color_content_elementscolor_text', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_content_elementscolor_text', 'theme_opentechnology');
//             $description = get_string('settings_color_content_elementscolor_desc_text', 'theme_opentechnology');
//             $default = '';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Яркость иконки для элементов контента
//             $name = $this->get_theme_setting_name('color_content_elementscolor_icon_brightness', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_content_elementscolor_icon_brightness', 'theme_opentechnology');
//             $description = get_string('settings_color_content_elementscolor_desc_icon_brightness', 'theme_opentechnology');
//             $default = '0';
//             $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет активных элементов контента
//             $name = $this->get_theme_setting_name('color_content_elementscolor_active', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_content_elementscolor_active', 'theme_opentechnology');
//             $description = get_string('settings_color_content_elementscolor_active_desc', 'theme_opentechnology');
//             $default = '#4D7F40';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет текста активных элементов контента
//             $name = $this->get_theme_setting_name('color_content_elementscolor_active_text', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_content_elementscolor_active_text', 'theme_opentechnology');
//             $description = get_string('settings_color_content_elementscolor_active_desc_text', 'theme_opentechnology');
//             $default = '#FFFFFF';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Яркость иконки для активных элементов контента
//             $name = $this->get_theme_setting_name('color_content_elementscolor_active_icon_brightness', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_content_elementscolor_active_icon_brightness', 'theme_opentechnology');
//             $description = get_string('settings_color_content_elementscolor_active_desc_icon_brightness', 'theme_opentechnology');
//             $default = '0';
//             $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
        
//         // Заголовок - Цветовая схема блоков
//         $name = $this->get_theme_setting_name('color_blocks_title', $profile);
//         $name = 'theme_opentechnology/'.$name;
//         $title = get_string('settings_color_blocks_title', 'theme_opentechnology');
//         $description = get_string('settings_color_blocks_title_desc', 'theme_opentechnology');
//         $setting = new admin_setting_heading($name, $title, $description);
//         $settings->add($setting);
        
//             // Цвет фона блоков
//             $name = $this->get_theme_setting_name('color_blocks_backgroundcolor', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_blocks_backgroundcolor', 'theme_opentechnology');
//             $description = get_string('settings_color_blocks_backgroundcolor_desc', 'theme_opentechnology');
//             $default = '#FFFFFF';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет текста фона блоков
//             $name = $this->get_theme_setting_name('color_blocks_backgroundcolor_text', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_blocks_backgroundcolor_text', 'theme_opentechnology');
//             $description = get_string('settings_color_blocks_backgroundcolor_desc_text', 'theme_opentechnology');
//             $default = '';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Яркость иконки для фона блоков
//             $name = $this->get_theme_setting_name('color_blocks_backgroundcolor_icon_brightness', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_blocks_backgroundcolor_icon_brightness', 'theme_opentechnology');
//             $description = get_string('settings_color_blocks_backgroundcolor_desc_icon_brightness', 'theme_opentechnology');
//             $default = '0';
//             $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Базовый цвет блоков
//             $name = $this->get_theme_setting_name('color_blocks_basecolor', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_blocks_basecolor', 'theme_opentechnology');
//             $description = get_string('settings_color_blocks_basecolor_desc', 'theme_opentechnology');
//             $default = '#FFFFFF';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Базовый цвет текста блоков
//             $name = $this->get_theme_setting_name('color_blocks_basecolor_text', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_blocks_basecolor_text', 'theme_opentechnology');
//             $description = get_string('settings_color_blocks_basecolor_desc_text', 'theme_opentechnology');
//             $default = '';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Яркость иконки для базового цвета блоков
//             $name = $this->get_theme_setting_name('color_blocks_basecolor_icon_brightness', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_blocks_basecolor_icon_brightness', 'theme_opentechnology');
//             $description = get_string('settings_color_blocks_basecolor_desc_icon_brightness', 'theme_opentechnology');
//             $default = '0';
//             $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет элементов блоков
//             $name = $this->get_theme_setting_name('color_blocks_elementscolor', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_blocks_elementscolor', 'theme_opentechnology');
//             $description = get_string('settings_color_blocks_elementscolor_desc', 'theme_opentechnology');
//             $default = '#69A05A';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет текста элементов блоков
//             $name = $this->get_theme_setting_name('color_blocks_elementscolor_text', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_blocks_elementscolor_text', 'theme_opentechnology');
//             $description = get_string('settings_color_blocks_elementscolor_desc_text', 'theme_opentechnology');
//             $default = '';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Яркость иконки для элементов блоков
//             $name = $this->get_theme_setting_name('color_blocks_elementscolor_icon_brightness', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_blocks_elementscolor_icon_brightness', 'theme_opentechnology');
//             $description = get_string('settings_color_blocks_elementscolor_desc_icon_brightness', 'theme_opentechnology');
//             $default = '0';
//             $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет активных элементов блоков
//             $name = $this->get_theme_setting_name('color_blocks_elementscolor_active', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_blocks_elementscolor_active', 'theme_opentechnology');
//             $description = get_string('settings_color_content_elementscolor_active_desc', 'theme_opentechnology');
//             $default = '#4D7F40';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет текста активных элементов блоков
//             $name = $this->get_theme_setting_name('color_blocks_elementscolor_active_text', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_blocks_elementscolor_active_text', 'theme_opentechnology');
//             $description = get_string('settings_color_content_elementscolor_active_desc_text', 'theme_opentechnology');
//             $default = '#FFFFFF';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Яркость иконки для активных элементов блоков
//             $name = $this->get_theme_setting_name('color_blocks_elementscolor_active_icon_brightness', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_blocks_elementscolor_active_icon_brightness', 'theme_opentechnology');
//             $description = get_string('settings_color_blocks_elementscolor_active_desc_icon_brightness', 'theme_opentechnology');
//             $default = '0';
//             $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
        
//         // Заголовок - Цветовая схема подвала
//         $name = $this->get_theme_setting_name('color_footer_title', $profile);
//         $name = 'theme_opentechnology/'.$name;
//         $title = get_string('settings_color_footer_title', 'theme_opentechnology');
//         $description = get_string('settings_color_footer_title_desc', 'theme_opentechnology');
//         $setting = new admin_setting_heading($name, $title, $description);
//         $settings->add($setting);
        
//             // Цвет фона подвала
//             $name = $this->get_theme_setting_name('color_footer_backgroundcolor', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_footer_backgroundcolor', 'theme_opentechnology');
//             $description = get_string('settings_color_footer_backgroundcolor_desc', 'theme_opentechnology');
//             $default = '#444444';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет текста фона подвала
//             $name = $this->get_theme_setting_name('color_footer_backgroundcolor_text', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_footer_backgroundcolor_text', 'theme_opentechnology');
//             $description = get_string('settings_color_footer_backgroundcolor_desc_text', 'theme_opentechnology');
//             $default = '';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Яркость иконки для фона подвала
//             $name = $this->get_theme_setting_name('color_footer_backgroundcolor_icon_brightness', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_footer_backgroundcolor_icon_brightness', 'theme_opentechnology');
//             $description = get_string('settings_color_footer_backgroundcolor_desc_icon_brightness', 'theme_opentechnology');
//             $default = '0';
//             $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Базовый цвет
//             $name = $this->get_theme_setting_name('color_footer_basecolor', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_footer_basecolor', 'theme_opentechnology');
//             $description = get_string('settings_color_footer_basecolor_desc', 'theme_opentechnology');
//             $default = '#0B4765';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Базовый цвет текста
//             $name = $this->get_theme_setting_name('color_footer_basecolor_text', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_footer_basecolor_text', 'theme_opentechnology');
//             $description = get_string('settings_color_footer_basecolor_desc_text', 'theme_opentechnology');
//             $default = '';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Яркость иконки для базового цвета подвала
//             $name = $this->get_theme_setting_name('color_footer_basecolor_icon_brightness', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_footer_basecolor_icon_brightness', 'theme_opentechnology');
//             $description = get_string('settings_color_footer_basecolor_desc_icon_brightness', 'theme_opentechnology');
//             $default = '0';
//             $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет элементов подвала
//             $name = $this->get_theme_setting_name('color_footer_elementscolor', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_footer_elementscolor', 'theme_opentechnology');
//             $description = get_string('settings_color_footer_elementscolor_desc', 'theme_opentechnology');
//             $default = '#69A05A';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет текста элементов подвала
//             $name = $this->get_theme_setting_name('color_footer_elementscolor_text', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_footer_elementscolor_text', 'theme_opentechnology');
//             $description = get_string('settings_color_footer_elementscolor_desc_text', 'theme_opentechnology');
//             $default = '';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Яркость иконки для элементов подвала
//             $name = $this->get_theme_setting_name('color_footer_elementscolor_icon_brightness', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_footer_elementscolor_icon_brightness', 'theme_opentechnology');
//             $description = get_string('settings_color_footer_elementscolor_desc_icon_brightness', 'theme_opentechnology');
//             $default = '0';
//             $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет активных элементов подвала
//             $name = $this->get_theme_setting_name('color_footer_elementscolor_active', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_footer_elementscolor_active', 'theme_opentechnology');
//             $description = get_string('settings_color_footer_elementscolor_active_desc', 'theme_opentechnology');
//             $default = '#4D7F40';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет текста активных элементов подвала
//             $name = $this->get_theme_setting_name('color_footer_elementscolor_active_text', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_footer_elementscolor_active_text', 'theme_opentechnology');
//             $description = get_string('settings_color_footer_elementscolor_active_desc_text', 'theme_opentechnology');
//             $default = '#FFFFFF';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Яркость иконки для активных элементов подвала
//             $name = $this->get_theme_setting_name('color_footer_elementscolor_active_icon_brightness', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_footer_elementscolor_active_icon_brightness', 'theme_opentechnology');
//             $description = get_string('settings_color_footer_elementscolor_active_desc_icon_brightness', 'theme_opentechnology');
//             $default = '0';
//             $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            

//         // Заголовок - Цветовая схема сворачиваемых секций
//         $name = $this->get_theme_setting_name('color_collapsiblesection_title', $profile);
//         $name = 'theme_opentechnology/'.$name;
//         $title = get_string('settings_color_collapsiblesection_title', 'theme_opentechnology');
//         $description = get_string('settings_color_collapsiblesection_title_desc', 'theme_opentechnology');
//         $setting = new admin_setting_heading($name, $title, $description);
//         $settings->add($setting);
            
//             // Цвет фона сворачиваемых секций
//             $name = $this->get_theme_setting_name('color_collapsiblesection_backgroundcolor', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_collapsiblesection_backgroundcolor', 'theme_opentechnology');
//             $description = get_string('settings_color_collapsiblesection_backgroundcolor_desc', 'theme_opentechnology');
//             $default = '#444444';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет текста фона сворачиваемых секций
//             $name = $this->get_theme_setting_name('color_collapsiblesection_backgroundcolor_text', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_collapsiblesection_backgroundcolor_text', 'theme_opentechnology');
//             $description = get_string('settings_color_collapsiblesection_backgroundcolor_desc_text', 'theme_opentechnology');
//             $default = '';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет элементов сворачиваемых секций
//             $name = $this->get_theme_setting_name('color_collapsiblesection_elementscolor', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_collapsiblesection_elementscolor', 'theme_opentechnology');
//             $description = get_string('settings_color_collapsiblesection_elementscolor_desc', 'theme_opentechnology');
//             $default = '#69A05A';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет текста элементов сворачиваемых секций
//             $name = $this->get_theme_setting_name('color_collapsiblesection_elementscolor_text', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_collapsiblesection_elementscolor_text', 'theme_opentechnology');
//             $description = get_string('settings_color_collapsiblesection_elementscolor_desc_text', 'theme_opentechnology');
//             $default = '';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Яркость иконки для элементов сворачиваемых секций
//             $name = $this->get_theme_setting_name('color_collapsiblesection_elementscolor_icon_brightness', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_collapsiblesection_elementscolor_icon_brightness', 'theme_opentechnology');
//             $description = get_string('settings_color_collapsiblesection_elementscolor_desc_icon_brightness', 'theme_opentechnology');
//             $default = '0';
//             $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет активных элементов сворачиваемых секций
//             $name = $this->get_theme_setting_name('color_collapsiblesection_elementscolor_active', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_collapsiblesection_elementscolor_active', 'theme_opentechnology');
//             $description = get_string('settings_color_collapsiblesection_elementscolor_active_desc', 'theme_opentechnology');
//             $default = '#4D7F40';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Цвет текста активных элементов сворачиваемых секций
//             $name = $this->get_theme_setting_name('color_collapsiblesection_elementscolor_active_text', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_collapsiblesection_elementscolor_active_text', 'theme_opentechnology');
//             $description = get_string('settings_color_collapsiblesection_elementscolor_active_desc_text', 'theme_opentechnology');
//             $default = '#FFFFFF';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Яркость иконки для активных элементов сворачиваемых секций
//             $name = $this->get_theme_setting_name('color_collapsiblesection_elementscolor_active_icon_brightness', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_collapsiblesection_elementscolor_active_icon_brightness', 'theme_opentechnology');
//             $description = get_string('settings_color_collapsiblesection_elementscolor_active_desc_icon_brightness', 'theme_opentechnology');
//             $default = '0';
//             $setting = new admin_setting_configselect($name, $title, $description, $default, $brightnessoptions);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//         // Заголовок - Цветовая схема ссылок
//         $name = $this->get_theme_setting_name('color_links_title', $profile);
//         $name = 'theme_opentechnology/'.$name;
//         $title = get_string('settings_color_links_title', 'theme_opentechnology');
//         $description = get_string('settings_color_links_title_desc', 'theme_opentechnology');
//         $setting = new admin_setting_heading($name, $title, $description);
//         $settings->add($setting);
        
//             // Основной цвет ссылок
//             $name = $this->get_theme_setting_name('color_links_color', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_links_color', 'theme_opentechnology');
//             $description = get_string('settings_color_links_color_desc', 'theme_opentechnology');
//             $default = '';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Основной цвет ссылок при наведении
//             $name = $this->get_theme_setting_name('color_links_color_hover', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_links_color_hover', 'theme_opentechnology');
//             $description = get_string('settings_color_links_color_hover_desc', 'theme_opentechnology');
//             $default = '';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Основной цвет ссылок
//             $name = $this->get_theme_setting_name('color_breadcrumb_links_color', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_breadcrumb_links_color', 'theme_opentechnology');
//             $description = get_string('settings_color_breadcrumb_links_color_desc', 'theme_opentechnology');
//             $default = '';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            
//             // Основной цвет ссылок при наведении
//             $name = $this->get_theme_setting_name('color_breadcrumb_links_color_hover', $profile);
//             $name = 'theme_opentechnology/'.$name;
//             $title = get_string('settings_color_breadcrumb_links_color_hover', 'theme_opentechnology');
//             $description = get_string('settings_color_breadcrumb_links_color_hover_desc', 'theme_opentechnology');
//             $default = '';
//             $setting = new colourpicker($name, $title, $description, $default);
//             $setting->set_updatedcallback('theme_opentechnology_purge_caches');
//             $settings->add($setting);
            

        // Заголовок - Цветовая схема элементов док-панели
        $name = $this->get_theme_setting_name('color_dockeditems_title', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_color_dockeditems_title', 'theme_opentechnology');
        $description = get_string('settings_color_dockeditems_title_desc', 'theme_opentechnology');
        $setting = new admin_setting_heading($name, $title, $description);
        $settings->add($setting);
            
            // Цвет фона элементов док-панели
            $name = $this->get_theme_setting_name('color_dockeditems_backgroundcolor', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_dockeditems_backgroundcolor', 'theme_opentechnology');
            $description = get_string('settings_color_dockeditems_backgroundcolor_desc', 'theme_opentechnology');
            $default = '';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Цвет текста фона элементов док-панели
            $name = $this->get_theme_setting_name('color_dockeditems_backgroundcolor_text', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_dockeditems_backgroundcolor_text', 'theme_opentechnology');
            $description = get_string('settings_color_dockeditems_backgroundcolor_text_desc', 'theme_opentechnology');
            $default = '#FFFFFF';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Цвет фона элементов док-панели при наведении
            $name = $this->get_theme_setting_name('color_dockeditems_backgroundcolor_active', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_dockeditems_backgroundcolor_active', 'theme_opentechnology');
            $description = get_string('settings_color_dockeditems_backgroundcolor_active_desc', 'theme_opentechnology');
            $default = '';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Цвет текста фона элементов док-панели при наведении
            $name = $this->get_theme_setting_name('color_dockeditems_backgroundcolor_active_text', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_dockeditems_backgroundcolor_active_text', 'theme_opentechnology');
            $description = get_string('settings_color_dockeditems_backgroundcolor_active_text_desc', 'theme_opentechnology');
            $default = '';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Цвет фона элементов док-панели при отображении иконок
            $name = $this->get_theme_setting_name('color_dockeditems_iconview_backgroundcolor', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_dockeditems_iconview_backgroundcolor', 'theme_opentechnology');
            $description = get_string('settings_color_dockeditems_iconview_backgroundcolor_desc', 'theme_opentechnology');
            $default = '';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Цвет текста фона элементов док-панели при отображении иконок
            $name = $this->get_theme_setting_name('color_dockeditems_iconview_backgroundcolor_text', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_dockeditems_iconview_backgroundcolor_text', 'theme_opentechnology');
            $description = get_string('settings_color_dockeditems_iconview_backgroundcolor_text_desc', 'theme_opentechnology');
            $default = '#FFFFFF';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Цвет фона элементов док-панели при отображении иконок при наведении
            $name = $this->get_theme_setting_name('color_dockeditems_iconview_backgroundcolor_active', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_dockeditems_iconview_backgroundcolor_active', 'theme_opentechnology');
            $description = get_string('settings_color_dockeditems_iconview_backgroundcolor_active_desc', 'theme_opentechnology');
            $default = '';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
            
            // Цвет текста фона элементов док-панели при отображении иконок при наведении
            $name = $this->get_theme_setting_name('color_dockeditems_iconview_backgroundcolor_active_text', $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_color_dockeditems_iconview_backgroundcolor_active_text', 'theme_opentechnology');
            $description = get_string('settings_color_dockeditems_iconview_backgroundcolor_active_text_desc', 'theme_opentechnology');
            $default = '';
            $setting = new colourpicker($name, $title, $description, $default);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
    }
    
    /**
     * Добавить раздел настроек шрифтов профиля
     *
     * @param admin_category $parentcategory - Родительская категория настроек
     * @param base $profile - Экземпляр профиля
     *
     * @return void
     */
    protected function admin_settingpage_add_profile_settings_custom_fonts(&$settings, $profile)
    {
        // Загрузка шрифта
        $name = $this->get_theme_setting_name('custom_fonts_files', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_custom_fonts_files', 'theme_opentechnology');
        $description = get_string('settings_custom_fonts_files_desc', 'theme_opentechnology');
        $filearea = $this->get_theme_setting_filearea('custom_fonts_files', $profile);
        $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea, 0, [
            'maxfiles' => 20,
            'accepted_types' => '.ttf'
        ]);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        $settings->add($setting);
    

        // Толщина шрифта
        $fontweights = [
            '100' => '100',
            '200' => '200',
            '300' => '300',
            '400' => '400',
            '500' => '500',
            '600' => '600',
            '700' => '700',
            '800' => '800',
            '900' => '900'
        ];
    
        // Кегель шрифта
        $fontstyles = [
            'normal' => 'normal',
            'italic' => 'italic'
        ];
    
        $name = $this->get_theme_setting_name('custom_fonts_files', $profile);
        $filearea = $this->get_theme_setting_filearea('custom_fonts_files', $profile);
        $fontfiles = theme_opentechnology_get_filearea_files($filearea, $name, 0);
        foreach ( $fontfiles as $fontfile )
        {
            $settingfilename = strtolower(preg_replace("/[^A-Za-z0-9]/", '_', $fontfile->filename));
        
            // Набор свойств шрифта
            $name = $this->get_theme_setting_name('custom_fonts_font_settings_'.$fontfile->settingname, $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_custom_fonts_font_settings', 'theme_opentechnology', $fontfile->filename);
            $description = get_string('settings_custom_fonts_font_settings_desc', 'theme_opentechnology', $fontfile->filename);
            $setting = new admin_setting_heading($name, $title, $description);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
        
            // font-family
            $name = $this->get_theme_setting_name('custom_fonts_font_family_'.$fontfile->settingname, $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_custom_fonts_font_family', 'theme_opentechnology', $fontfile->filename);
            $description = get_string('settings_custom_fonts_font_family_desc', 'theme_opentechnology', $fontfile->filename);
            $setting = new admin_setting_configtext($name, $title, $description, 'DefaultFont');
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
        
            $name = $this->get_theme_setting_name('custom_fonts_font_weight_'.$fontfile->settingname, $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_custom_fonts_font_weight', 'theme_opentechnology', $fontfile->filename);
            $description = get_string('settings_custom_fonts_font_weight_desc', 'theme_opentechnology', $fontfile->filename);
            $setting = new admin_setting_configselect($name, $title, $description, '400', $fontweights);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
        
            $name = $this->get_theme_setting_name('custom_fonts_font_style_'.$fontfile->settingname, $profile);
            $name = 'theme_opentechnology/'.$name;
            $title = get_string('settings_custom_fonts_font_style', 'theme_opentechnology', $fontfile->filename);
            $description = get_string('settings_custom_fonts_font_style_desc', 'theme_opentechnology', $fontfile->filename);
            $setting = new admin_setting_configselect($name, $title, $description, 'normal', $fontstyles);
            $setting->set_updatedcallback('theme_opentechnology_purge_caches');
            $settings->add($setting);
        }
    }
    
    /**
     * Добавить раздел настроек позиций блоков профиля
     *
     * @param admin_category $parentcategory - Родительская категория настроек
     * @param base $profile - Экземпляр профиля
     *
     * @return void
     */
    protected function admin_settingpage_add_profile_settings_blocks(&$settings, $profile)
    {
        global $CFG, $THEME;
    
        // Заголовок - Настройки Сворачивания блоков
        $name = $this->get_theme_setting_name('docking_title', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_docking_title', 'theme_opentechnology');
        $description = get_string('settings_docking_title_desc', 'theme_opentechnology');
        $setting = new admin_setting_heading($name, $title, $description);
        $settings->add($setting);
        
        include ($CFG->dirroot.'/theme/opentechnology/config.php');
        if ( isset($THEME->layouts) && is_array($THEME->layouts) )
        { // Указаны типы страниц
            $options = [
                'enabled' => get_string('region_enabled', 'theme_opentechnology'),
                'autodocking' => get_string('region_autodocking', 'theme_opentechnology'),
                'fixeddock' => get_string('region_fixeddock', 'theme_opentechnology'),
                'disableddock' => get_string('region_disableddock', 'theme_opentechnology')
            ];
            foreach ( $THEME->layouts as $layoutname => $layoutdata )
            { // Обработка каждого типа страницы
                if ( ! isset($layoutdata['regions']) || empty($layoutdata['regions']) )
                { // Зоны блоков не объявлены
                    continue;
                }
                // Заголовок - Имя зоны
                $name = $this->get_theme_setting_name('layout_' . $layoutname . '_title', $profile);
                $name = 'theme_opentechnology/'.$name;
                $title = get_string('layout_' . $layoutname, 'theme_opentechnology');
                $description = '';
                $setting = new admin_setting_heading($name, $title, $description);
                $settings->add($setting);
                
                
                $collapsiblesections = theme_opentechnology_get_known_collapsiblesections();
                foreach($collapsiblesections as $collapsiblesection)
                {
                    // Объект, передаваемый в языковую строку
                    $a = new stdClass();
                    // наименование сворачиваемой секции
                    $a->collapsiblesection = $collapsiblesection['name'];
                    // наименование зоны (layout)
                    $a->layout = get_string('layout_' . $layoutname, 'theme_opentechnology');
                    
                    // Состояние сворачиваемой секции
                    $name = 'theme_opentechnology/' . $this->get_theme_setting_name(
                        'layout_'.$layoutname.'_collapsiblesection_'.$collapsiblesection['code'].'_state',
                        $profile
                    );
                    $title = get_string('settings_collapsiblesection_state', 'theme_opentechnology', $a);
                    $description = get_string('settings_collapsiblesection_state_desc', 'theme_opentechnology', $a);
                    $default = 0;
                    $choices = [
                        0 => get_string('settings_collapsiblesection_defaultstate_collapse', 'theme_opentechnology'),
                        1 => get_string('settings_collapsiblesection_defaultstate_expand', 'theme_opentechnology'),
                        2 => get_string('settings_collapsiblesection_defaultstate_fixcollapse', 'theme_opentechnology'),
                        3 => get_string('settings_collapsiblesection_defaultstate_fixexpand', 'theme_opentechnology'),
                        4 => get_string('settings_collapsiblesection_forcedstate_fixcollapse', 'theme_opentechnology'),
                        5 => get_string('settings_collapsiblesection_forcedstate_fixexpand', 'theme_opentechnology')
                    ];
                    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
                    $settings->add($setting);
                    
                    // Настройка позиций блоков сворачиваемой секции
                    $name = 'theme_opentechnology/' . $this->get_theme_setting_name(
                        'layout_' . $layoutname . '_collapsiblesection_' . $collapsiblesection['code'],
                        $profile
                    );
                    $title = get_string('settings_collapsiblesection', 'theme_opentechnology', $a);
                    $description = get_string('settings_collapsiblesection_desc', 'theme_opentechnology', $a);
                    $default = '';
                    $setting = new gridsetter($name, $title, $description, $default, 0);
                    $settings->add($setting);
                }
                
                // Заголовки таблицы
                foreach ( $layoutdata['regions'] as $region )
                {
                    // Состояние позиции блоков
                    $name = $this->get_theme_setting_name('region_' . $layoutname . '_' . $region, $profile);
                    $name = 'theme_opentechnology/'.$name;
                    $name = str_replace('-', '_', $name);
                    $title = get_string('region-' . $region, 'theme_opentechnology');
                    $description = '';
                    if ( isset($layoutdata['defaultregiondocking']) &&
                        ! empty($layoutdata['defaultregiondocking']) &&
                        isset($layoutdata['defaultregiondocking'][$region]) &&
                        ! empty($layoutdata['defaultregiondocking'][$region]) )
                    {
                        $default = $layoutdata['defaultregiondocking'][$region];
                    } else
                    {
                        $default = 'enabled';
                    }
                    $setting = new admin_setting_configselect($name, $title, $description, $default,
                        $options);
                    $settings->add($setting);
                }
            }
        }
    }
    
    /**
     * Добавить раздел настроек безопасности профиля
     *
     * @param admin_category $parentcategory - Родительская категория настроек
     * @param base $profile - Экземпляр профиля
     *
     * @return void
     */
    protected function admin_settingpage_add_profile_settings_security(&$settings, $profile)
    {
    
        // Заголовок - Безопасность
        $name = $this->get_theme_setting_name('security_title', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_security_title', 'theme_opentechnology');
        $description = get_string('settings_security_title_desc', 'theme_opentechnology');
        $setting = new admin_setting_heading($name, $title, $description);
        $settings->add($setting);
        
        // Текст уведомления об отключенном Javascript
        $name = $this->get_theme_setting_name('security_nojs_text', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_security_nojs_text', 'theme_opentechnology');
        $description = get_string('settings_security_nojs_text_desc', 'theme_opentechnology');
        $default = get_string('settings_security_nojs_text_default', 'theme_opentechnology');
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $settings->add($setting);
        
        // Запрет перетаскивания
        $name = $this->get_theme_setting_name('security_copy_draganddrop', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_security_copy_draganddrop', 'theme_opentechnology');
        $description = get_string('settings_security_copy_draganddrop_desc', 'theme_opentechnology');
        $default = 0;
        $choices = [
            0 => get_string('settings_security_copy_draganddrop_disable', 'theme_opentechnology'),
            1 => get_string('settings_security_copy_draganddrop_enable', 'theme_opentechnology'),
        ];
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        $settings->add($setting);
        
        // Запрет контекстного меню
        $name = $this->get_theme_setting_name('security_copy_contextmenu', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_security_copy_contextmenu', 'theme_opentechnology');
        $description = get_string('settings_security_copy_contextmenu_desc', 'theme_opentechnology');
        $default = 0;
        $choices = [
            0 => get_string('settings_security_copy_contextmenu_disable', 'theme_opentechnology'),
            1 => get_string('settings_security_copy_contextmenu_enable', 'theme_opentechnology'),
        ];
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        $settings->add($setting);
        
        // Запрет копирования текста
        $name = $this->get_theme_setting_name('security_copy_copy', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_security_copy_copy', 'theme_opentechnology');
        $description = get_string('settings_security_copy_copy_desc', 'theme_opentechnology');
        $default = 0;
        $choices = [
            0 => get_string('settings_security_copy_copy_disable', 'theme_opentechnology'),
            1 => get_string('settings_security_copy_copy_enable', 'theme_opentechnology'),
        ];
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        $settings->add($setting);
        
        // Запрет доступа с отключенным JS
        $name = $this->get_theme_setting_name('security_copy_nojsaccess', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_security_copy_nojsaccess', 'theme_opentechnology');
        $description = get_string('settings_security_copy_nojsaccess_desc', 'theme_opentechnology');
        $default = 0;
        $choices = [
            0 => get_string('settings_security_copy_nojsaccess_disable', 'theme_opentechnology'),
            1 => get_string('settings_security_copy_nojsaccess_enable', 'theme_opentechnology'),
        ];
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        $settings->add($setting);
    }
    
    /**
     * Добавить раздел настроек страницы авторизации профиля
     *
     * @param admin_category $parentcategory - Родительская категория настроек
     * @param base $profile - Экземпляр профиля
     *
     * @return void
     */
    protected function admin_settingpage_add_profile_settings_loginpage_main(&$settings, $profile)
    {
        // Заголовок - Страница авторизации
        $name = $this->get_theme_setting_name('loginpage_main_title', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_loginpage_main_title', 'theme_opentechnology');
        $description = get_string('settings_loginpage_main_title_desc', 'theme_opentechnology');
        $setting = new admin_setting_heading($name, $title, $description);
        $settings->add($setting);
        
        // Тип страницы авторизации
        $name = $this->get_theme_setting_name('loginpage_main_type', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_loginpage_main_type', 'theme_opentechnology');
        $description = get_string('settings_loginpage_main_type_desc', 'theme_opentechnology');
        $default = '';
        $choices = [
            '' => get_string('settings_loginpage_main_type_standard', 'theme_opentechnology'),
            'slider' => get_string('settings_loginpage_main_type_slider', 'theme_opentechnology'),
            'sidebar' => get_string('settings_loginpage_main_type_sidebar', 'theme_opentechnology'),
        ];
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $settings->add($setting);
        
        $loginpagetype = $this->get_theme_setting('loginpage_main_type', $profile);
        if (method_exists($this, 'admin_settingpage_add_profile_settings_loginpage_'.$loginpagetype))
        {
            $this->{'admin_settingpage_add_profile_settings_loginpage_'.$loginpagetype}($settings, $profile);
        }
    }
    /**
     * Добавить раздел настроек страницы авторизации профиля
     *
     * @param admin_category $parentcategory - Родительская категория настроек
     * @param base $profile - Экземпляр профиля
     *
     * @return void
     */
    protected function admin_settingpage_add_profile_settings_loginpage_slider(&$settings, $profile)
    {
        // Заголовок - Страница авторизации
        $name = $this->get_theme_setting_name('loginpage_slider_title', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_loginpage_slider_title', 'theme_opentechnology');
        $description = get_string('settings_loginpage_slider_title_desc', 'theme_opentechnology');
        $setting = new admin_setting_heading($name, $title, $description);
        $settings->add($setting);
        
        // Изображения для слайдера
        $name = $this->get_theme_setting_name('loginpage_slider_images', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_loginpage_slider_images', 'theme_opentechnology');
        $description = get_string('settings_loginpage_slider_images_desc', 'theme_opentechnology');
        $options = ['maxfiles' => 10, 'accepted_types' => 'image'];
        $filearea = $this->get_theme_setting_filearea('loginpage_slider_images', $profile);
        $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea, 0, $options);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        $settings->add($setting);
        
        // Текст в шапке
        $name = $this->get_theme_setting_name('loginpage_header_text', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_loginpage_header_text', 'theme_opentechnology');
        $description = get_string('settings_loginpage_header_text_desc', 'theme_opentechnology');
        $default = '';
        $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
        $settings->add($setting);
        
        // Отступы текста шапки
        $name = $this->get_theme_setting_name('loginpage_header_text_padding', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_loginpage_header_text_padding', 'theme_opentechnology');
        $description = get_string('settings_loginpage_header_text_padding_desc', 'theme_opentechnology');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        $settings->add($setting);
        
    }
    
    /**
     * Добавить раздел настроек профиля для страницы авторизации типа "Боковая панель"
     *
     * @param admin_category $parentcategory - Родительская категория настроек
     * @param base $profile - Экземпляр профиля
     *
     * @return void
     */
    protected function admin_settingpage_add_profile_settings_loginpage_sidebar(&$settings, $profile)
    {
        
        // Заголовок - Страница авторизации
        $name = $this->get_theme_setting_name('loginpage_sidebar_title', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_loginpage_sidebar_title', 'theme_opentechnology');
        $description = get_string('settings_loginpage_sidebar_title_desc', 'theme_opentechnology');
        $setting = new admin_setting_heading($name, $title, $description);
        $settings->add($setting);
        
        // Логотип
        $name = $this->get_theme_setting_name('loginpage_sidebar_logoimage', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_loginpage_sidebar_logoimage', 'theme_opentechnology');
        $description = get_string('settings_loginpage_sidebar_logoimage_desc', 'theme_opentechnology');
        $filearea = $this->get_theme_setting_filearea('loginpage_sidebar_logoimage', $profile);
        $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        $settings->add($setting);
        
        // Изображение для фона
        $name = $this->get_theme_setting_name('loginpage_sidebar_images', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_loginpage_sidebar_images', 'theme_opentechnology');
        $description = get_string('settings_loginpage_sidebar_images_desc', 'theme_opentechnology');
        $options = ['maxfiles' => 1, 'accepted_types' => 'image'];
        $filearea = $this->get_theme_setting_filearea('loginpage_sidebar_images', $profile);
        $setting = new admin_setting_configstoredfile($name, $title, $description, $filearea, 0, $options);
        $setting->set_updatedcallback('theme_opentechnology_purge_caches');
        $settings->add($setting);
        
        // Настройки шапки
        $name = $this->get_theme_setting_name('loginpage_sidebar_header_elements', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_loginpage_sidebar_header_elements', 'theme_opentechnology');
        $description = get_string('settings_loginpage_sidebar_header_elements_desc', 'theme_opentechnology');
        $choices = [
            'usernav' => get_string('loginpage_sidebar_header_element_usernav', 'theme_opentechnology'),
            'custommenu' => get_string('loginpage_sidebar_header_element_custommenu', 'theme_opentechnology'),
        ];
        $setting = new \admin_setting_configmulticheckbox($name, $title, $description, null, $choices);
        $settings->add($setting);
        
        // Текст в шапке
        $name = $this->get_theme_setting_name('loginpage_sidebar_text', $profile);
        $name = 'theme_opentechnology/'.$name;
        $title = get_string('settings_loginpage_sidebar_text', 'theme_opentechnology');
        $description = get_string('settings_loginpage_sidebar_text_desc', 'theme_opentechnology');
        $default = '';
        $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
        $settings->add($setting);
    }
    
    /**
     * Добавить раздел настроек безопасности профиля
     *
     * @param admin_category $parentcategory - Родительская категория настроек
     * @param base $profile - Экземпляр профиля
     *
     * @return void
     */
    protected function admin_settingpage_add_profile_settings_testing(&$settings, $profile)
    {
        // Экспериментальные настройки темы
        /*
         // Заголовок - Экспериментальные настройки
         $name = $this->get_theme_setting_name('testing_title', $profile);
         $name = 'theme_opentechnology/'.$name;
         $title = get_string('settings_testing_title', 'theme_opentechnology');
         $description = get_string('settings_testing_title_desc', 'theme_opentechnology');
         $setting = new admin_setting_heading($name, $title, $description);
         $settings->add($setting);
         */
    }
    
    protected function find_theme_admin_category($path=[], $node=null)
    {
        if (is_null($node))
        {
            $node = admin_get_root();
            $fullpath = ['appearance', 'themes', 'theme_opentechnology'];
            foreach($path as $nodename)
            {
                $fullpath[] = $nodename;
            }
        } else
        {
            $fullpath = $path;
        }
        
        $currentkey = array_search($node->name, $fullpath);
        $currentkey = $currentkey === false ? 0 : $currentkey+1;
        
        foreach ( $node->get_children() as $child )
        {
            if (is_a($child, 'admin_category') && $child->name == $fullpath[$currentkey])
            {// Обнаружена нужная подкатегория
                if ( count($fullpath) == ($currentkey+1) )
                {
                    return $child;
                }
                else
                {
                    return $this->find_theme_admin_category($fullpath, $child);
                }
            }
        }
        return false;
    }
}