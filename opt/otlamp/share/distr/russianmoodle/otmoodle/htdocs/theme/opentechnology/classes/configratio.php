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
 * Тема СЭО 3KL. Поле настройки соотношения сторон
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_opentechnology;

defined('MOODLE_INTERNAL') || die;

use admin_setting_configtext;
use moodle_url;

class configratio extends admin_setting_configtext
{   
    /**
     * Конструктор поля настроек
     *
     * @param string $name - Уникальное имя настройки
     * @param string $visiblename - Локализованное имя настройки
     * @param string $description - Локализованное описание настройки
     * @param string $defaultsetting - Значение по умолчанию
     */
    public function __construct($name, $visiblename, $description, $defaultsetting) 
    {
        parent::__construct($name, $visiblename, $description, $defaultsetting);
        
        $this->paramtype = PARAM_INT;
    }
    
    /**
     * Рендер поля настроек
     * 
     * @return string - HTML-код настройки
     */
    public function output_html($data, $query='') 
    {
        global $PAGE;
        $PAGE->requires->js(new moodle_url('/theme/opentechnology/javascript/configratio.js'));
        
        $default = $this->get_defaultsetting();
        return format_admin_setting(
            $this, 
            $this->visiblename,
            '<div class="form-text defaultsnext"><input class="configratio" type="text" size="3" id="'.$this->get_id().'" name="'.$this->get_full_name().'" value="'.s($data).'" /></div>',
            $this->description, true, '', $default, $query);
    }
}