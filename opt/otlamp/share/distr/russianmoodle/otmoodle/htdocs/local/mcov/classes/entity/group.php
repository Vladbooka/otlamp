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
 * Настраиваемые поля. Класс полей локальной группы (в курсе).
 *
 * @package    local_mcov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mcov\entity;

use local_mcov\entity;
use local_mcov\helper;
use context;
use context_system;
use navigation_node;
use settings_navigation;

class group extends entity {

    /**
     * Список обрабатываемых событий и их обработчиков локальной группы
     * @var array
     */
    protected static $events = [
        '\core\event\group_deleted' => 'mcov_group_group_deleted'
    ];

    /**
     * Список прав, необходый для доступа к редактированию свойств локальной группы
     * @var array
     */
    protected $editcapabilities = [
        'local/mcov:edit_groups_cov'
    ];

    /**
     * Обработчик события удаления локальной группы. Удаляет свойства удаленной группы из универсального справочника.
     * @param \core\event\group_deleted $event
     */
    public function mcov_group_group_deleted(\core\event\group_deleted $event) {
        $mcov = [
            'entity' => $this->code,
            'objid' => $event->objectid
        ];
        $this->delete_mcov($mcov);
    }

    /**
     * Переопределение навигации сущностью
     *
     * @param settings_navigation $settingsnav
     * @param context $context
     */
    public function extend_settings_navigation(settings_navigation $settingsnav, context $context)
    {
        global $PAGE;

        if (in_array($PAGE->pagetype, ['group-group']))
        {
            // Текущая нода
            $currentnode = $settingsnav->find_active_node();

            // Идентификатор редактируемой глобальной группы
            $this->objid = optional_param('id', null, PARAM_INT);

            if ($currentnode !== false && !empty($this->objid) && $this->has_editable_fields())
            {
                // Ссылка на редактирование настраиваемых полей
                $nodeurl = new \moodle_url('/local/mcov/edit.php', [
                    'entity' => 'group',
                    'objid' => $this->objid,
                    'backurl' => $PAGE->url->out_as_local_url()
                ]);
                $nodetitle = $this->get_edit_entity_title();
                $nodetype = navigation_node::NODETYPE_LEAF;
                $nodekey = 'group_edit_mcov';
                $nodeicon = new \pix_icon('i/edit', '');

                // Создание нового пункта меню
                $node = navigation_node::create($nodetitle, $nodeurl, $nodetype, null, $nodekey, $nodeicon);

                // Добавление ноды после текущей
                helper::add_navigation_node_next_to($node, $currentnode);
            }
        }
    }

    /**
     * Имя объекта
     * @return string
     */
    public function get_displayname() {
        $group = groups_get_group($this->objid, 'id, name', MUST_EXIST);
        return $group->name;
    }
}