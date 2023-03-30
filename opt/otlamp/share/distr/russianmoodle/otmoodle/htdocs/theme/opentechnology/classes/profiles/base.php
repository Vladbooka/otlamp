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
 * Тема СЭО 3KL. Класс стандартного профиля темы.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_opentechnology\profiles;

use coding_exception;
use html_writer;
use moodle_url;
use stdClass;
use theme_opentechnology\profilemanager;
use context_system;

class base
{
    /**
     * Запись профиля из БД
     *
     * @var null
     */
    protected $record = null;
    
    /**
     * Конструктор
     *
     */
    public function __construct($id)
    {
        global $DB;
        
        $record = $DB->get_record('theme_opentechnology_profile', ['id' => (int)$id]);
        if ( ! $record )
        {// Запись не найдена
            throw new coding_exception(get_string('profile_error_notexist', 'theme_opentechnology'));
        }
        $this->record = $record;
    }
    
    /**
     * Поддержка удаления профиля
     *
     * @return boolean
     */
    public function can_delete()
    {
        return true;
    }
    
    /**
     * Процесс удаления данных профиля
     *
     * @return void
     */
    public function delete_profile_data()
    {
    }
    
    /**
     * Поддержка изменения профиля
     *
     * @return boolean
     */
    public function can_edit()
    {
        return true;
    }
    
    /**
     * Поддержка создания профиля
     *
     * @return boolean
     */
    public function can_create()
    {
        return false;
    }
    
    /**
     * Поддержка импорта профиля
     *
     * @return boolean
     */
    public function can_import()
    {
        return true;
    }
    
    /**
     * Поддержка экспорта профиля
     *
     * @return boolean
     */
    public function can_export()
    {
        return true;
    }
    
    /**
     * Получить название профиля
     *
     * @return int
     */
    public function get_classname()
    {
        return 'base';
    }
    
    /**
     * Получить идентификатор профиля
     *
     * @return int
     */
    public function get_id()
    {
        return $this->record->id;
    }
    
    /**
     * Проверка на профиль по умолчанию
     *
     * Оформление профиля по умолчанию распространяется на всю Тему
     *
     * @return null
     */
    public function is_default()
    {
        return (bool)$this->record->defaultprofile;
    }
    
    /**
     * Получить код профиля
     *
     * @return string
     */
    public function get_code()
    {
        return $this->record->code;
    }
    
    /**
     * Получить локализованное название профиля
     *
     * @return string
     */
    public function get_name()
    {
        if ( $this->is_default() )
        {
            return "&#10004; ".format_string($this->record->name);
        }
        return format_string($this->record->name);
    }
    
    /**
     * Получение имени настройки
     *
     * @param string $basesettingname - Базовое имя настройки
     *
     * @return string
     */
    public function get_setting_name($basesettingname)
    {
        return $this->get_code().'_'.$basesettingname;
    }
    
    /**
     * Получить локализованное описание профиля
     *
     * @return string
     */
    public function get_description()
    {
        return format_text($this->record->description, $this->record->descriptionformat);
    }
    
    /**
     * Получить запись профиля из БД
     *
     * @return stdClass
     */
    public function get_record()
    {
        return $this->record;
    }
    
    /**
     * Генерация плитки
     */
    public function render_tile()
    {
        $tile = '';
        
        $systemcontext = context_system::instance();
        
        // Получение базовой информации о профиле
        $baseinfo = '';
        $baseinfo .= html_writer::div(
            $this->get_name(),
            'theme_opentechnology_profile_tile_name'
        );
        $baseinfo .= html_writer::div(
            $this->get_code(),
            'theme_opentechnology_profile_tile_code'
        );
        // Добавление информации о профиле по умолчанию
        $defaultprofile = profilemanager::instance()->get_default_profile();
        if ( $defaultprofile->get_code() == $this->get_code() )
        {// Текущий профиль является профилем по умолчанию
            $label = get_string('profile_default_label', 'theme_opentechnology');
            $baseinfo .= html_writer::div(
                $label,
                'theme_opentechnology_profile_tile_default'
            );
        }
        $baseinfo .= html_writer::div(
            $this->get_description(),
            'theme_opentechnology_profile_tile_description'
        );
        // Сборка основной информации о плитке
        $tile .= html_writer::div(
            $baseinfo,
            'theme_opentechnology_profile_tile_info'
        );
        
        // Сборка блока кнопок на плитке
        $actions = '';
        
        $id = $this->get_id();
        
        // Экспорт профиля
        if ( $this->can_export() && has_capability('theme/opentechnology:settings_export', $systemcontext) )
        {// Ссылка на экспорт профиля
            $url = new moodle_url(
                '/theme/opentechnology/profiles/export.php',
                ['id' => $this->get_id()]
            );
            $actions .= html_writer::link(
                $url,
                get_string('profile_export_title', 'theme_opentechnology'),
                ['class' => 'btn btn-primary theme-ot-profile-action-export']
            );
        }
        
        if ( is_number($id) && $id > 0 )
        {
            
            // Редактирование профиля
            $allowedit = profilemanager::instance()->profile_allow_edit($this->get_id());
            if ( $allowedit && $this->can_import() && has_capability('theme/opentechnology:settings_import', $systemcontext) )
            {
                // Ссылка на импорт профиля
                $url = new moodle_url(
                    '/theme/opentechnology/profiles/import.php',
                    ['id' => $this->get_id()]
                    );
                $actions .= html_writer::link(
                    $url,
                    get_string('profile_import_title', 'theme_opentechnology'),
                    ['class' => 'btn btn-primary']
                    );
            }
            if ( $allowedit && has_capability('theme/opentechnology:profile_edit', $systemcontext) )
            {
                // Ссылка на редактирование профиля
                $url = new moodle_url(
                    '/theme/opentechnology/profiles/save.php',
                    ['id' => $this->get_id()]
                );
                $actions .= html_writer::link(
                    $url,
                    get_string('profile_edit_title', 'theme_opentechnology'),
                    ['class' => 'btn btn-primary']
                );
            }
            // Удаление профиля
            $allowdelete = profilemanager::instance()->profile_allow_delete($this->get_id());
            if ( $allowdelete && has_capability('theme/opentechnology:profile_delete', $systemcontext) )
            {// Ссылка на удаление профиля
                $url = new moodle_url(
                    '/theme/opentechnology/profiles/delete.php',
                    ['id' => $this->get_id()]
                );
                $actions .= html_writer::link(
                    $url,
                    get_string('profile_delete_title', 'theme_opentechnology'),
                    ['class' => 'btn btn-primary']
                );
            }
            // Ссылка на привязки
            $url = new moodle_url(
                '/theme/opentechnology/profiles/links.php',
                ['id' => $this->get_id()]
            );
            $actions .= html_writer::link(
                $url,
                get_string('profile_links_title', 'theme_opentechnology'),
                ['class' => 'btn btn-primary']
            );
        }
        
        if ($this->can_create())
        {
            $url = new moodle_url(
                '/theme/opentechnology/profiles/save.php',
                ['profilecode' => $this->get_code()]
            );
            $actions .= html_writer::link(
                $url,
                get_string('profile_create_title', 'theme_opentechnology'),
                ['class' => 'btn btn-primary']
            );
        }
        
        $tile .= html_writer::div(
            $actions,
            'theme_opentechnology_profile_tile_actions'
        );
        
        return html_writer::div(
            $tile,
            'theme_opentechnology_profile_tile_wrapper '.$this->get_classname()
        );
    }
    
    /**
     * Отобразить информацию по профилю
     *
     * @return string
     */
    public function render_viewinfo()
    {
        $html = '';
        
        $label = get_string('profile_name_label', 'theme_opentechnology');
        $html .= html_writer::div(
            $label.': '.$this->get_name(),
            'theme_opentechnology_profile_info_name'
        );
        $defaultprofile = profilemanager::instance()->get_default_profile();
        if ( $defaultprofile->get_code() == $this->get_code() )
        {// Текущий профиль является профилем по умолчанию
            $label = get_string('profile_default_label', 'theme_opentechnology');
            $html .= html_writer::div(
                $label,
                'theme_opentechnology_profile_info_default'
            );
        }
        $label = get_string('profile_code_label', 'theme_opentechnology');
        $html .= html_writer::div(
            $label.': '.$this->get_code(),
            'theme_opentechnology_profile_info_code'
        );
        $html .= html_writer::div(
            $this->get_description(),
            'theme_opentechnology_profile_info_description'
        );
        
        return html_writer::div(
            $html,
            'theme_opentechnology_profile_info_wrapper '.$this->get_classname()
        );
    }
    
    /**
     * Получить привязки профиля к элементам системы
     */
    public function get_links()
    {
        return [];
    }
    
    
    public function import_settings($storedfile)
    {
        // Получение менеджера файлов
        $fs = get_file_storage();
        $itemid = 0;
        
        if (is_string($storedfile))
        {
            $filepath = $storedfile;
            
            if (is_file($filepath))
            {
                $filerecord = [
                    'contextid' => context_system::instance()->id,
                    'component' => 'theme_opentechnology',
                    'filearea' => 'import',
                    'itemid' => $itemid,
                    'filepath' => '/'.$this->get_code().'/',
                    'filename' => basename($filepath)
                ];
                $storedfile = $fs->create_file_from_pathname($filerecord, $filepath);
            }
        }
        
        if (get_class($storedfile) == 'stored_file')
        {
            // Попытка распаковать архив
            $packer = get_file_packer('application/zip');
            $extractresult = $packer->extract_to_storage(
                $storedfile,
                context_system::instance()->id,
                'theme_opentechnology',
                'import',
                $itemid,
                '/'.$this->get_code().'/'
            );
            
            if ($extractresult)
            {
                // Получаем распакованные файлы
                $settingsfiles = $fs->get_area_files(
                    context_system::instance()->id,
                    'theme_opentechnology',
                    'import',
                    $itemid
                );
                
                
                // Собираем файлы в массив так, чтобы по ним можно было выполнять поиск
                $settingfiles = [];
                foreach($settingsfiles as $settingsfile)
                {
                    $settingfiles[$settingsfile->get_filepath()][$settingsfile->get_filename()] = $settingsfile;
                }
                
                // Импортируемые настройки
                $importsettings = [];
                if( isset($settingfiles['/'.$this->get_code().'/']['settings']) )
                {
                    // Найден основной файл с настройками
                    $fp = tmpfile();
                    fwrite($fp, $settingfiles['/'.$this->get_code().'/']['settings']->get_content());
                    rewind($fp);
                    
                    // Преобразование csv-строк в структурированные данные
                    while (($row = fgetcsv($fp, 0, ";")) !== FALSE)
                    {
                        $fullsettingname = $this->get_setting_name($row[0]);
                        $importsetting = new \stdClass();
                        $importsetting->name = $fullsettingname;
                        $importsetting->value = unserialize($row[1]);
                        $importsettings[$fullsettingname] = $importsetting;
                    }
                    fclose($fp);
                    
                    // Исключаем все остальные файлы ИЗ КОРНЯ во избежание ошибок при дальнейшей обработке (все остальные в поддиректориях)
                    unset($settingfiles['/'.$this->get_code().'/']);
                }
                
                foreach( $settingfiles as $filepath => $settingdata )
                {
                    // Название папки - это название настройки, для которой требуется файл
                    $settingname = substr($filepath, strlen($this->get_code())+2, -1);
                    $fullsettingname = $this->get_setting_name($settingname);
                    foreach($settingdata as $filename => $file)
                    {
                        //@TODO: Предусмотреть возможность загрузки нескольких файлов для настройки
                        if( isset($importsettings[$fullsettingname])
                            && $filename == substr($importsettings[$fullsettingname]->value, 1) )
                        {// Найден файл для настройки
                            $importsettings[$fullsettingname]->file = $file;
                            break;
                        }
                    }
                }
                                
                profilemanager::instance()->import_profile($this->get_code(), $importsettings);
            }
        }
        $fs->delete_area_files(
            context_system::instance()->id,
            'theme_opentechnology',
            'import',
            $itemid
        );
        
    }
    
    
    
    public function call_overriden_renderer($renderername, $function, $params, $page, $target) {
        
        global $CFG;

        $reflection = new \ReflectionClass($renderername);
        $renderershortname = $reflection->getShortName();
        
        // Код текуцщего профиля
        $profilecode = $this->get_code();
        
        $classpath = $CFG->dirroot . '/theme/opentechnology/profiles/overrides/'.$profilecode.'/'.$renderershortname.'.php';
        
        if( file_exists($classpath))
        {
            require_once($classpath);
            $classname = 'theme_opentechnology_profile_'.$profilecode.'_'.$renderershortname;
            
            if( class_exists($classname) && method_exists($classname, $function))
            {
                $renderer = new $classname($page, $target);
                return call_user_func_array([$renderer, $function], $params);
            }
        }
        
        throw new profile_overrides_exception('No renderer overrides found');
    }
}

class profile_overrides_exception extends \Exception { }
