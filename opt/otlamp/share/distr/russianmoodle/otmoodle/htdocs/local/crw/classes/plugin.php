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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

define('CRW_PLUGIN_TYPE_COURSES_LIST', 10);
define('CRW_PLUGIN_TYPE_CATEGORIES_LIST', 20);
define('CRW_PLUGIN_TYPE_SEARCH', 30);

define('CRW_COURSES_SORT_TYPE_COURSE_SORT', 10);
define('CRW_COURSES_SORT_TYPE_COURSE_CREATED', 20);
define('CRW_COURSES_SORT_TYPE_COURSE_START', 30);
define('CRW_COURSES_SORT_TYPE_LEARNINGHISTORY_ENROLMENTS', 40);
define('CRW_COURSES_SORT_TYPE_ACTIVE_ENROLMENTS', 50);
define('CRW_COURSES_SORT_TYPE_COURSE_POPULARITY', 60);
define('CRW_COURSES_SORT_TYPE_COURSE_NAME', 70);

require_once($CFG->dirroot . '/local/crw/lib.php');

/**
 * Абстрактный класс для всех субплагинов отображения Витрины
 *
 * @package local
 * @subpackage crw
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

abstract class local_crw_plugin
{
    
    /** @var string Имя субплагина(папки) */
    protected $plugin;
    
    /** @var string Тип субплагина */
    protected $type;
    
    /** @var array - Массив свойств субплагина */
    protected $config = null;
    
    /** @var array - Переопределенные настройки субплагина */
    protected $forcedsettings = null;

    /**
     * Конструктор
     *
     * @param string $plugin - Имя субплагина
     */
    public function __construct($plugin, $forcedsettings = null)
    {
        $this->plugin = $plugin;
        $this->forcedsettings = $forcedsettings;
    }

    /**
     * Получить все свойства субплагина
     *
     * @return void
     */
    protected function load_config()
    {
        if ( ! isset($this->config) )
        {
            // Получим имя субплагина
            $name = $this->get_name();
            // Сформируем свойства
            $this->config = get_config("crw_$name");
        }
    }

    /**
     * Получить свойство субплагина
     *
     * @param  string $name - Имя свойства
     * @param  string $default - Значение по умолчанию
     *
     * @return string - Значение свойства, или значение по умолчанию, или NULL
     */
    public function get_config($name, $default = null)
    {
        // Загрузим свойства
        $this->load_config();
        
        if (is_array($this->forcedsettings) && (array_key_exists($name, $this->forcedsettings)))
        {
            $config = $this->forcedsettings[$name];
            
        } else if(isset($this->config->$name))
        {
            $config = $this->config->$name;
            
        } else
        {
            $config = $default;
        }
        
        // Вернем свойство
        return $config;
    }

    /**
     * Установить свойство субплагина
     *
     * @param  string $name - Имя свойства
     * @param  string $value string - Значение свойства, или NULL для удаления
     *
     * @return string value - Значение
     */
    public function set_config($name, $value)
    {
        // ПОлучим имя субплагина
        $pluginname = $this->get_name();
        // Загрузим свойства
        $this->load_config();
        
        if ($value === null)
        {// Удалим из массива свойств
            unset($this->config->$name);
        } else
        {// Обновим в массиве свойств
            $this->config->$name = $value;
        }
        // Обновим свойство в БД
        set_config($name, $value, "crw_$pluginname");
    }

    /**
     * Получить имя субпагина
     *
     * @return string
     */
    public function get_name()
    {
        // Все классы начинаются с "local_crw".
        $words = explode('_', get_class($this), 2);
        return $words[1];
    }

    /**
     * Получить версию плагина
     *
     * @return string - Версия плагина
     */
    protected function get_version()
    {
        global $CFG;
        // Объявим класс версии
        $plugin = new stdClass;
        include($CFG->dirroot . '/local/crw/plugins/' . $this->plugin . '/version.php');
        
        return $plugin->version;
    }
    
    /**
     * Получить объект субплагина по имени
     *
     * @param string $plugin - Имя субплагина
     * @param array $forcedsettings - массив настроек сабплагина для переопределения системных
     *
     * @return local_crw_plugin - Объект субплагина
     */
    public static function get($plugin, $forcedsettings = null)
    {
        global $CFG;
        // Подучим адрес субплагина
        $dir = core_component::get_component_directory('crw_' . $plugin);
        // Подключим файл класса субплагина
        require_once($dir . '/lib.php');
        // Сформируем имя класса
        $classname = 'crw_'.$plugin;
        return new $classname($plugin, $forcedsettings);
    }
    
    /**
     * Сравнивает два плагина
     *
     * @param editor_tinymce_plugin $a
     * @param editor_tinymce_plugin $b
     * @return Negative number if $a is before $b
     */
    public static function compare_plugins(local_crw_plugin $a, local_crw_plugin $b)
    {
        // Use sort order first.
        $order = $a->get_sort_order() - $b->get_sort_order();
        if ($order != 0)
        {
            return $order;
        }
        // Then sort alphabetically.
        return strcmp($a->plugin, $b->plugin);
    }
    
    /**
     * Сформировать html блока
     *
     * @param int $id - ID категории курсов, или курса
     * @param array $options - дополнительные опции
     *
     * @return string - HTML-код блока
     */
    public function outputhtml($id = 0, $options = array() )
    {
        return '';
    }
    
    /**
     * Сформировать html блока
     *
     * @param array $options - Опции отображения блока
     *
     * @return string - HTML-код блока
     */
    public function display($options = array() )
    {
        return '';
    }
    
    /**
     * Сформировать строку поиска курсов
     *
     * @param array $options - Дополнительные опции
     *
     * @return string - sql запрос
     */
    public function get_sql_courses($options = array() )
    {
        return null;
    }
    

    /**
     * Получить URL файла субплагина Витрины
     *
     * @param string $file - Имя файла вместе с разрешением
     * @param bool $absolute - Абсолютный путь
     */
    public function get_file_url($file = '', $absolute = true)
    {
        global $CFG;
    
        // Версия плагина
        if ($CFG->debugdeveloper)
        {
            $version = '-1';
        } else
        {
            $version = $this->get_version();
        }
        
        // Формируем URL файла, получаемый через загрузчик
        if ($CFG->slasharguments)
        {
            $url = '/local/crw/plugins/loader.php/' . $this->plugin . '/' . $version . '/' . $file;
        } else
        {
            $url = $this->plugin . '/assets/' . $file;
        }
    
        // Если необходим абсолютный путь
        if ($absolute)
        {
            $url = $CFG->wwwroot . $url;
        }
    
        return $url;
    }
    
    public function get_type()
    {
        return $this->type;
    }
}
