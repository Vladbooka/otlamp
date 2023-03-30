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

namespace local_opentechnology\admin_setting;

use admin_setting_configtext;
use core_component;

/**
 * Локальный плагин Техподдержка СЭО 3KL. Класс административной настройки в виде кнопки с фронтенд-обработчиком.
 * @author moxhatblu
 *
 */
class number extends admin_setting_configtext {
    public $min = null;
    public $max = null;
    /**
     * Config text constructor
     *
     * @param string $name unique ascii name, either 'mysetting' for settings that in config, or 'myplugin/mysetting' for ones in config_plugins.
     * @param string $visiblename localised
     * @param string $description long localised info
     * @param string $defaultsetting
     * @param mixed $paramtype int means PARAM_XXX type, string is a allowed format in regex
     * @param int $size default field size
     */
    public function __construct($name, $visiblename, $description, $defaultsetting, $size = null, $min = null, $max = null) {
        $this->min = $min ?? null;
        $this->max = $max ?? null;
        parent::__construct($name, $visiblename, $description, $defaultsetting, PARAM_INT, $size);
    }
    /**
     * Return an XHTML string for the setting
     * @return string Returns an XHTML string
     */
    public function output_html($data, $query='') {
        global $OUTPUT;
        
        $default = $this->get_defaultsetting();
        $context = (object) [
            'size' => $this->size,
            'id' => $this->get_id(),
            'name' => $this->get_full_name(),
            'value' => $data,
            'forceltr' => $this->get_force_ltr(),
            'min' => $this->get_min(),
            'max' => $this->get_max(),
            'hasmin' => !is_null($this->get_min()),
            'hasmax' => !is_null($this->get_max()),
        ];
        $element = $OUTPUT->render_from_template('local_opentechnology/setting_confignumber', $context);
        
        return format_admin_setting($this, $this->visiblename, $element, $this->description, true, '', $default, $query);
    }
    
    public function get_min() {
        return $this->min;
    }
    
    public function get_max() {
        return $this->max;
    }
}