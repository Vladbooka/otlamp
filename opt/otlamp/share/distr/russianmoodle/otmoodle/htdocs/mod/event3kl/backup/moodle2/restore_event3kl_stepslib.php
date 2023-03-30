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
 * @package    mod_event3kl
 * @subpackage backup-moodle2
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class restore_event3kl_activity_structure_step extends restore_activity_structure_step
{
    /**
     * Определение структуры для восстановления
     * {@inheritDoc}
     * @see restore_structure_step::define_structure()
     */
    protected function define_structure()
    {
        // Пути к объектам восстановления
        $paths = [];

        // Флаг восстановления пользовательских данных
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('event3kl', '/activity/event3kl');

        if ( $userinfo )
        {
            $paths[] = new restore_path_element('session', '/activity/event3kl/sessions/session');
            $paths[] = new restore_path_element('member', '/activity/event3kl/sessions/session/members/member');
        }

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Восстановление объекта занятия
     *
     * @param array $data
     */
    protected function process_event3kl($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('event3kl', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Восстановление сессии
     *
     * @param array $data
     */
    protected function process_session($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->event3klid = $this->get_new_parentid('event3kl');
        $data->groupid = $this->get_mappingid('group', $data->groupid, -1);

        $newitemid = $DB->insert_record('event3kl_sessions', $data);
        $this->set_mapping('session', $oldid, $newitemid, true);
    }

    /**
     * Восстановление участника сессии
     *
     * @param array $data
     */
    protected function process_member($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->sessionid = $this->get_new_parentid('session');
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('event3kl_session_members', $data);
        $this->set_mapping('member', $oldid, $newitemid);
    }

    protected function after_execute() {
        // Add related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_event3kl', 'intro', null);
        $this->add_related_files('mod_event3kl', 'sessionrecord', 'session');
    }
}
