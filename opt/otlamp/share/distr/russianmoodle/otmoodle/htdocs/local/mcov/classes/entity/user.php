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
 * Пользователи. Класс сущности.
 *
 * @package    local_mcov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mcov\entity;

use local_mcov\entity;
use core_user\output\myprofile\node;

class user extends entity {

    /**
     * Список прав, необходый для доступа к редактированию свойств полоьзователя
     * @var array
     */
    protected $editcapabilities = [
        // Сейчас нет возможности конфигурировать свои поля и данное право тут не нужно
        // Такая возможность врядли появится, так как кастомные поля профиля и так уже имеются в системе
        // Но если вдруг, то пусть тут будет это право, чтобы случайно не дать доступ на редактирование кому попало
        'moodle/site:config'
    ];


    /**
     * {@inheritDoc}
     * @see \local_mcov\entity::require_edit_capabilities()
     */
    public function require_edit_capabilities($context = null, $objid = null) {

        if (is_null($context)) {
            $context = \context_system::instance();
        }
        if (is_null($objid)) {
            $objid = $this->objid;
        }

        // Необходимо иметь либо право на редактирование настраиваемых полей всех пользователей
        // Либо право на редактирование полей своего профиля
        // И только при отсутствии обоих - ошибка (в ошибке будет указано минимальное право редактировать свои поля)
        if (!$this->has_edit_capabilities()) {
            throw new \required_capability_exception($context, 'local/mcov:edit_users_cov_my', 'nopermissions', '');
        }
    }






    function myprofile_navigation(\core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
        global $PAGE;

        $this->objid = $user->id;

        if ($this->has_editable_fields()) {
            // имеются поля, к редактированию которых у пользователя есть доступ
            // нарисуем ссылку к интерфейсу редактирования
            $nodename = 'edit_mcov_fields';
            $nodetitle = get_string('edit_abstract_entity_title', 'local_mcov');
            $nodeurlparams = [
                'entity' => 'user',
                'objid' => $user->id,
                'backurl' => $PAGE->url->out_as_local_url()
            ];
            $nodeurl = new \moodle_url('/local/mcov/edit.php', $nodeurlparams);
            $nodeclass = $nodename;
            $nodeafter = 'editprofile';
            $node = new node('contact', $nodename, $nodetitle, $nodeafter, $nodeurl, null, null, $nodeclass);
            $tree->add_node($node);
        }
    }

    /**
     * Проверка на наличие прав редактировать настраиваемые поля данного пользователя
     * (текущий редактируемый объект сущности)
     *
     * @return boolean
     */
    public function has_edit_capabilities($context=null) {

        global $USER;

        $syscontext = \context_system::instance();

        // есть право редактировать настраиваемые поля любым пользователям
        if (has_capability('local/mcov:edit_users_cov', $syscontext)) {
            return true;
        }

        // есть право редактировать настраиваемые поля себе, и перед нами сам владелец
        if (has_capability('local/mcov:edit_users_cov_my', $syscontext) && $USER->id == $this->objid) {
            return true;
        }

        return false;
    }
    
    
    /**
     * Имя объекта
     * @return string
     */
    public function get_displayname() {
        global $DB;
        
        $user = $DB->get_record('user', ['id' => $this->objid], '*', MUST_EXIST);
        return fullname($user);
    }
}