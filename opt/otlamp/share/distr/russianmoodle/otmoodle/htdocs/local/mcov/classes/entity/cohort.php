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
 * Настраиваемые поля. Класс полей глобальной группы.
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

class cohort extends entity {

    /**
     * Список обрабатываемых событий и их обработчиков глобальной группы
     * @var array
     */
    protected static $events = [
        '\core\event\cohort_deleted' => 'mcov_cohort_cohort_deleted'
    ];

    /**
     * Список прав, необходый для доступа к редактированию свойств глобальной группы
     * @var array
     */
    protected $editcapabilities = [
        'local/mcov:edit_cohorts_cov'
    ];

    /**
     * Обработчик события удаления глобальной группы. Удаляет свойства удаленной группы из универсального справочника.
     * @param \core\event\cohort_deleted $event
     */
    public function mcov_cohort_cohort_deleted(\core\event\cohort_deleted $event) {
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

        if (in_array($PAGE->pagetype, ['cohort-edit', 'cohort-assign']))
        {
            // Текущая нода
            $currentnode = $settingsnav->find_active_node();

            // Идентификатор редактируемой глобальной группы
            $this->objid = optional_param('id', null, PARAM_INT);

            if ($currentnode !== false && !is_null($this->objid) && $this->has_editable_fields())
            {
                // Ссылка на редактирование настраиваемых полей
                $nodeurl = new \moodle_url('/local/mcov/edit.php', [
                    'entity' => 'cohort',
                    'objid' => $this->objid,
                    'backurl' => $PAGE->url->out_as_local_url()
                ]);
                $nodetitle = $this->get_edit_entity_title();
                $nodetype = navigation_node::NODETYPE_LEAF;
                $nodekey = 'cohort_edit_mcov';
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
        global $DB;
        $cohort = $DB->get_record('cohort', ['id' => $this->objid], '*', MUST_EXIST);
        return $cohort->name;
    }
}