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
 * Тема СЭО 3KL. Поле настройки разметки по сетке
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_opentechnology;

defined('MOODLE_INTERNAL') || die;

use admin_setting;
use moodle_url;

class gridsetter extends admin_setting
{
    /**
     * Размерность сетки
     * 
     * @var int
     */
    protected $gridlength = null;
    
    /**
     * Лимит строк
     * 
     * @var int
     */
    protected $rowslimit = 0;
    
    /**
     * Конструктор поля настроек
     *
     * @param string $name - Уникальное имя настройки
     * @param string $visiblename - Локализованное имя настройки
     * @param string $description - Локализованное описание настройки
     * @param string $defaultsetting - Значение по умолчанию
     * @param bool $rowslimit - Лимит строк сетки
     * @param int $gridlength - Размерность сетки
     */
    public function __construct($name, $visiblename, $description, $defaultsetting, $rowslimit = 0, $gridlength = 12) 
    {
        $this->rowslimit = (int)$rowslimit;
        $this->gridlength = (int)$gridlength;
        
        if ( $this->gridlength < 1 ) 
        {
            $this->gridlength = 1;
        }
        if ( $this->rowslimit < 0 )
        {
            $this->rowslimit = 0;
        }
        parent::__construct($name, $visiblename, $description, $defaultsetting);
    }
    
    /**
     * Получить настройку
     *
     * @return array|null - Настройка сетки
     */
    public function get_setting() 
    {
        $config = (string)$this->config_read($this->name);
        return json_decode($config);
    }
    
    /**
     * Записать настройку сетки
     */
    public function write_setting($data) 
    {
        $data = (string)$data;
        $validated = $this->validate($data);
        if ( $validated !== true ) 
        {
            return $validated;
        }
        return ($this->config_write($this->name, $data) ? '' : get_string('errorsetting', 'admin'));
    }
    
    /**
     * Validate data before storage
     * @param string data
     * @return mixed true if ok string if error found
     */
    public function validate($data) {
        return true;
    }
    
    /**
     * Рендер поля настроек
     * 
     * @return string - HTML-код настройки
     */
    public function output_html($data, $query='') 
    {
        global $PAGE;
        $PAGE->requires->js(new moodle_url('/theme/opentechnology/javascript/gridsetter.js'));
        
        $default = $this->get_defaultsetting();
        $data = json_encode($data);
        return format_admin_setting($this, $this->visiblename,
            '<div class="form-text defaultsnext"><input class="gridsetter" data-rowslimit="'.$this->rowslimit.'" data-gridlength="'.$this->gridlength.'" type="text" id="'.$this->get_id().'" name="'.$this->get_full_name().'" value="'.s($data).'" /></div>',
            $this->description, true, '', $default, $query);
    }
}