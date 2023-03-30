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

/**
 * Блок топ-10
 * 
 * @package    block
 * @subpackage topten
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_topten;

use MoodleQuickForm;
use html_writer;

abstract class base
{
    /**
     * Конструктор
     *
     * @param array $config
     */
    public function __construct($config, $instanceid)
    {
        global $DB;
        
        $this->db = $DB;
        $this->config = $config;
        $this->instanceid = $instanceid;
    }
    /**
     * Флаг включения кеширования отчета
     * 
     * @return boolean
     */
    public abstract function is_cached();
    
    /**
     * Флаг подтверждения отчетом готовности данных для отображения
     * 
     */
    public abstract function is_ready();
    
    /**
     *  Доступные контексты добавления отчета
     *  
     * @return boolean[]
     */
    public function applicable_formats()
    {
        return [
            'all' => true,
        ];
    }
    
    /**
     * Метод обновления кэша
     * 
     * @param bool|??? $oldcache может быть false или старым кешем
     */
    public abstract function get_cache_data($oldcache = false);
    
    /**
     * Получение контента отчета
     * 
     */
    public abstract function get_html($data);

    
    /**
     * Получение заголовка отчета
     *
     * @return string
     */
    public static function get_default_header($small = false) 
    {
        return '';
    }
    /**
     * Получение индикатора значения
     *
     * @return string
     */
    public function get_indicator($value = 0, $maxvalue = 0)
    {
        $percent = 0;
        if ( ! empty($maxvalue) )
        {
            $percent = $value * 100 / $maxvalue;
        }
        
        // Формирование индикатора
        $firstcircle = html_writer::empty_tag(
                'circle', 
                ['class' => 'block-topten-pie-fill', 'r' => 16, 'cx' => 16, 'cy' => 16, 'stroke-dasharray' => "$percent " . (100 - $percent)]
                );
        $secondcircle = html_writer::empty_tag(
                'circle',
                ['class' => 'block-topten-pie-center', 'r' => 14, 'cx' => 16, 'cy' => 16]
                );
        $thirdcircle = html_writer::empty_tag(
                'circle',
                ['class' => 'block-topten-pie-fon', 'r' => 12, 'cx' => 16, 'cy' => 16]
                );
        $valtext = html_writer::nonempty_tag(
                'text',
                $value,
                ['class' => 'block-topten-pie-num', 'x' => '50%', 'y' => '50%', 'dy' => '.3em', 'transform' => 'rotate(90 16,16)']
                );
        
        
        return html_writer::nonempty_tag('svg', $firstcircle . $secondcircle . $thirdcircle . $valtext, ['class' => 'block-topten-pie', 'viewBox' => '0 0 32 32']);
    }
    
    /**
     * Добавление собственных настроек в форму
     * 
     * @param MoodleQuickForm $mform
     */
    public function definition(&$mform, $formsave = null)
    {
    }
}