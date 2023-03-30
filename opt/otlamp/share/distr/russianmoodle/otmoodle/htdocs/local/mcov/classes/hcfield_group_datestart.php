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

namespace local_mcov;

class hcfield_group_datestart extends hcfield {
    
    public function __construct() {
        $this->set_entity_code('group');
        $this->set_prop('local_mcov_group_datestart');
        $this->set_config([
            'type' => 'date_selector',
            'label' => get_string('e_group_fld_local_mcov_group_datestart', 'local_mcov'),
            'options' => ['optional' => true]
        ]);
    }
    
    /**
     * Проверка наличия прав на редактирование конфига панели управления СЭО 3KL
     * @return boolean
     */
    public function has_edit_capability($objid=null) {
        
        if (is_null($objid)) {
            // есть ли право редактировать поле для любых групп в системе
            $syscontext = \context_system::instance();
            return has_capability('local/mcov:edit_groups_cov', $syscontext);
        }
        
        // есть право настраивать поле в конкретной группе
        $group = groups_get_group($objid, 'id, courseid', MUST_EXIST);
        $coursecontext = \context_course::instance($group->courseid);
        return has_capability('local/mcov:edit_groups_cov', $coursecontext);
    }
}

