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

use admin_setting_configempty;
use core_component;

/**
 * Локальный плагин Техподдержка СЭО 3KL. Класс административной настройки в виде кнопки с фронтенд-обработчиком.
 * @author moxhatblu
 *
 */
class html extends admin_setting_configempty {
    private $html = '';
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
    public function __construct($name, $visiblename, $description, $html = '') {
        $this->html = $html ?? '';
        parent::__construct($name, $visiblename, $description);
    }
    /**
     * Return an XHTML string for the setting
     * @return string Returns an XHTML string
     */
    public function output_html($data, $query='') {
        global $OUTPUT;
        
        $default = $this->get_defaultsetting();
        $context = (object) [
            'html' => $this->get_html(),
            'id' => $this->get_id(),
            'name' => $this->get_full_name(),
            'value' => $data,
        ];
        $element = $OUTPUT->render_from_template('local_opentechnology/setting_confightml', $context);
        
        return $this->format_admin_setting($this, $this->visiblename, $element, $this->description, true, '', $default, $query);
    }
    
    public function get_html() {
        return $this->html;
    }
    
    /**
     * Format admin settings
     *
     * @param object $setting
     * @param string $title label element
     * @param string $form form fragment, html code - not highlighted automatically
     * @param string $description
     * @param mixed $label link label to id, true by default or string being the label to connect it to
     * @param string $warning warning text
     * @param sting $defaultinfo defaults info, null means nothing, '' is converted to "Empty" string, defaults to null
     * @param string $query search query to be highlighted
     * @return string XHTML
     */
    function format_admin_setting($setting, $title='', $form='', $description='', $label=true, $warning='', $defaultinfo=NULL, $query='') {
        global $CFG, $OUTPUT;
        
        $context = (object) [
            'name' => empty($setting->plugin) ? $setting->name : "$setting->plugin | $setting->name",
            'fullname' => $setting->get_full_name(),
        ];
        
        // Sometimes the id is not id_s_name, but id_s_name_m or something, and this does not validate.
        if ($label === true) {
            $context->labelfor = $setting->get_id();
        } else if ($label === false) {
            $context->labelfor = '';
        } else {
            $context->labelfor = $label;
        }
        
        $form .= $setting->output_setting_flags();
        
        $context->warning = $warning;
        $context->override = '';
        if (empty($setting->plugin)) {
            if (array_key_exists($setting->name, $CFG->config_php_settings)) {
                $context->override = get_string('configoverride', 'admin');
            }
        } else {
            if (array_key_exists($setting->plugin, $CFG->forced_plugin_settings) and array_key_exists($setting->name, $CFG->forced_plugin_settings[$setting->plugin])) {
                $context->override = get_string('configoverride', 'admin');
            }
        }
        
        $defaults = array();
        if (!is_null($defaultinfo)) {
            if ($defaultinfo === '') {
                $defaultinfo = get_string('emptysettingvalue', 'admin');
            }
            $defaults[] = $defaultinfo;
        }
        
        $context->default = null;
        $setting->get_setting_flag_defaults($defaults);
        if (!empty($defaults)) {
            $defaultinfo = implode(', ', $defaults);
            $defaultinfo = highlight($query, nl2br(s($defaultinfo)));
            $context->default = get_string('defaultsettinginfo', 'admin', $defaultinfo);
        }
        
        
        $context->error = '';
        $adminroot = admin_get_root();
        if (array_key_exists($context->fullname, $adminroot->errors)) {
            $context->error = $adminroot->errors[$context->fullname]->error;
        }
        
        $context->id = 'admin-' . $setting->name;
        $context->title = highlightfast($query, $title);
        $context->name = highlightfast($query, $context->name);
        $context->description = highlight($query, markdown_to_html($description));
        $context->element = $form;
        $context->forceltr = $setting->get_force_ltr();
        
        return $OUTPUT->render_from_template('local_opentechnology/setting_without_label', $context);
    }
}