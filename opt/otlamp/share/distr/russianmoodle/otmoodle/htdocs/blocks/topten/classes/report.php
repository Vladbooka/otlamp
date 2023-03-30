<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Блок топ 10. Класс отчетов.
 *
 * @package    block
 * @subpackage topten
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_topten;

use block_base;
use cache;
use stdClass;

class report
{
    /**
     * Экземпляр блока
     * 
     * @var \stdClass
     */
    private $config = null;
    /**
     * Инстанс id блока
     * 
     * @var int
     */
    private $instanceid = null;
    
    /**
     * Инициализация top 10
     * 
     * @param block_base $block - Экземпляр блока
     */
    public function __construct($config, $instanceid)
    {
        // Сохранение ссылки на экземпляр блока
        $this->config = $config;
        $this->instanceid = $instanceid;
    }
    
    /**
     * Получение всех типов отчетов
     *
     * @return array
     */
    public static function get_rating_types()
    {
        global $CFG;
 
        $reports = [];
        // Директория с классами слайдов
        $classesdir = $CFG->dirroot.'/blocks/topten/classes/reports/';
        // Интерфейс для просмотра содержимого каталогов
        $dir = new \DirectoryIterator($classesdir);
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isFile()) {
                $file = $fileinfo->getBasename('.php');
                $classname = '\\block_topten\\reports\\'.$file;
                if ( class_exists($classname))
                {// Класс найден
                    $reports[$file] = $classname::get_default_header();
                }
            }
        }
        return $reports;
    }
    
    /**
     * Получение объекта отчета
     *
     *
     * @return NULL|\block_topten\base
     */
    public static function get_rating_object($config, $instanceid)
    {
        $types = self::get_rating_types();
        if (! isset($config->rating_type) || ! array_key_exists($config->rating_type, $types) )
        {
            return null;
        }
        $classname = "\block_topten\\reports\\$config->rating_type";
        return new $classname($config, $instanceid);
    }
    
    /**
     * Обнавляет кэш текущего отчета
     * 
     * @return array|boolean
     */    
    public function update_cache()
    {
        // получаем обьект отчета
        $repobj = $this->get_rating_object($this->config, $this->instanceid);
        // проверяем поддерживает отчет кеш и готов ли он для формирования данных
        if ( $repobj->is_cached() && $repobj->is_ready())
        {
            $cache = cache::make('block_topten', 'rating_data');
            $key =  $this->config->rating_type . '_' . $this->instanceid;
            $olddata = $cache->get($key);
            $data = [
                'data'        => $repobj->get_cache_data($olddata['data']),
                'timecreated' => time()
            ];
            // записываем данные в кеш
            $cache->set($key, $data);
            return $data;
        }
        return false;
    }
    /**
     * Обнавляет кеш для всех актирных отчетов
     * 
     * @param boolean $forceupdate
     * @return boolean
     */
    public static function update_all_cache($forceupdate = false) {
        global $DB;
        $instances = $DB->get_records('block_instances', ['blockname' => 'topten']);
        $types = self::get_rating_types();
        $cache = cache::make('block_topten', 'rating_data');
        foreach ($instances as $instance)
        {
            // получаем конфиг блока
            $config = unserialize(base64_decode($instance->configdata));
            // установим частоту равную суткам ели ранее частота не была задана
            if (empty($config->timelimit)) {
                $lifetime = 86400;
            } else {
                $lifetime = (int)$config->timelimit;
            }
            // получаем обьект отчета
            $repobj = self::get_rating_object($config, $instance->id);
            // проверяем выбран ли отчет, поддерживает ли он кеш, готов ли он для формирования данных
            if (isset($config->rating_type) && 
                array_key_exists($config->rating_type, $types) && 
                $repobj->is_cached() && 
                $repobj->is_ready()
                )
            {
                $key =  $config->rating_type . '_' . $instance->id;
                // получаем данные кеша
                if (!empty($dat = $cache->get($key)) && 
                    isset($dat['timecreated']) && 
                    $dat['timecreated'] + $lifetime > time()&&
                    !$forceupdate
                    )
                {
                    // время жизни кеша еще не истекло
                    mtrace($types[$config->rating_type] . ' ' . $instance->id . ' [Cache still alive]' );
                    continue;
                }
                $data = [
                    'data'        => $repobj->get_cache_data($dat['data']),
                    'timecreated' => time()
                ];
                // записываем данные в кеш
                $cache->set($key, $data);
                // кеш обновлен
                mtrace($types[$config->rating_type] . ' ' . $instance->id . ' [Cache updated]' );
            } else {
                mtrace('Instance id ' . $instance->id . ' [Cache not updated]' );
            }
        }
        return true;
    }
    /**
     * Получение контента блока
     * 
     * @return array|string
     */
    public function get_content()
    {
        $repobj = self::get_rating_object($this->config, $this->instanceid);
        // проверяем поддерживает ли отчет кеширование
        if ($repobj->is_cached()) {
            // получаем данные кеша
            $cache = cache::make('block_topten', 'rating_data');
            $key =  $this->config->rating_type . '_' . $this->instanceid;
            $data = $cache->get($key);
            // получаем время жизни кеша
            if (isset($this->config->timelimit)) {
                $lifetime = (int)$this->config->timelimit;
            } else {
                $lifetime = 86400;
            }
            // Пересоздаем кеш если его время вышло
            if (! isset($data['timecreated']) || $data['timecreated'] + $lifetime < time()) {
                $data = $this->update_cache();  
            }
            // формируем html из кеша
            return $repobj->get_html($data['data']);
        // если отчет не поддерживает кеширование проверяем его готовность   
        } elseif ($repobj->is_ready()) {
            $data = $repobj->get_cache_data(false);
            return $repobj->get_html($data);
        } else {
            // отчет не готов
            return get_string('report_not_ready', 'block_topten');
        }
    }
    /**
     * Получение заголовка блока
     * 
     * @return string
     */
    public function header() {
        $ratingname = '';
        if (isset($this->config->rating_name))
        {
            $ratingname = format_text(trim($this->config->rating_name), FORMAT_PLAIN);
        }
        
        if (empty($ratingname))
        {
            $ratingobj = self::get_rating_object($this->config, $this->instanceid);
            // Объект для языковой строки
            $stringobj = new stdClass();
            $stringobj->name = $ratingobj->get_default_header(true);
            $stringobj->number = $this->config->rating_number;
            // Переопределение заголовка отчета
            $ratingname = get_string(
                'report_header',
                'block_topten',
                $stringobj
                );
        }
        return $ratingname;
    }
}