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
 * Панель управления СЭО 3KL.
 * Класс настраиваемого поля для хранения пользовательской конфигурации плагина.
 *
 * @package    local_otcontrolpanel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_otcontrolpanel;

use local_mcov\hcfield;

class mcov_config extends hcfield {

    public function __construct() {
        $this->set_entity_code('user');
        $this->set_prop('local_otcontrolpanel_viewsconfig');
        $this->set_config([
            'type' => 'textarea',
            'label' => get_string('e_user_fld_local_otcontrolpanel_viewsconfig', 'local_mcov'),
            'attributes' => ['rows'=>10, 'cols'=>45, 'width'=>0,'height'=>0]
        ]);
    }

    /**
     * Проверка наличия прав на редактирование конфига панели управления СЭО 3KL
     * @return boolean
     */
    public function has_edit_capability($objid=null) {

        global $USER;

        $syscontext = \context_system::instance();

        // есть право настраивать конфиг любым пользователям
        if (has_capability('local/otcontrolpanel:config', $syscontext)) {
            return true;
        }
        // есть право настраивать конфиг себе, и перед нами сам владелец
        if (has_capability('local/otcontrolpanel:config_my', $syscontext) && $USER->id == $objid) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     * @see \local_mcov\hcfield::process_form_value()
     */
    public function process_form_value($formvalue) {
        $yaml = $formvalue;
        $config = \otcomponent_yaml\Yaml::parse($yaml);
        $jsonconfig = json_encode($config, JSON_UNESCAPED_UNICODE);
        return $jsonconfig;
    }

    /**
     * {@inheritDoc}
     * @see \local_mcov\hcfield::process_stored_value()
     */
    public function process_stored_value($storedvalue) {

        $config = json_decode($storedvalue, true);
        if (!is_null($config))
        {
            return \otcomponent_yaml\Yaml::dump($config, 6);
        } else {
            return '';
        }
    }
}