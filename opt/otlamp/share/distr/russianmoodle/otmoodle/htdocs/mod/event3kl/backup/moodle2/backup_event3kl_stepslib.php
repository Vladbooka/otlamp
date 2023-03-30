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

class backup_event3kl_activity_structure_step extends backup_activity_structure_step
{
    /**
     * {@inheritDoc}
     * @see backup_structure_step::define_structure()
     */
    protected function define_structure()
    {
        // Флаг настройки бэкапа с пользовательскими данными
        $userinfo = $this->get_setting_value('userinfo');

        // Корневой элемент, содержащий информацию о модуле курса из '{event3kl}'
        $event3kl = new backup_nested_element('event3kl', ['id'],
            [
                'name',
                'intro',
                'introformat',
                'provider',
                'providerdata',
                'format',
                'formatdata',
                'datemode',
                'datemodedata',
                'timecreated',
                'timemodified'
            ]
        );

        // Элемент для списка сессий. Объявлен в любом случае, будет пустым
        // в отсутсвие пользовательских данных
        $sessions = new backup_nested_element('sessions');
        // Элемент отдельно для каждой сессии (соответствует записи в {event3kl_sessions})
        $session = new backup_nested_element('session', ['id'],
            [
                'name',
                'startdate',
                'overridenstartdate',
                'offereddate',
                'maxmembers',
                'groupid',
                'status',
                'extid',
                'pendingrecs',
                'timecreated',
                'timemodified'
            ]
        );
        // Элемент для списка участников сессии
        $members = new backup_nested_element('members');
        // Элемент для каждого участника сессии (соответствует записи в {event3kl_session_members})
        $member = new backup_nested_element('member', ['id'],
            [
                'userid',
                'calendareventid',
                'attendance',
                'timecreated',
                'timemodified'
            ]
        );

        // Задаем связи между элементами
        $event3kl->add_child($sessions);
        $sessions->add_child($session);
        $session->add_child($members);
        $members->add_child($member);

        // Определяем источники данных для элементов, в данном случае - таблицы в БД
        $event3kl->set_source_table('event3kl', ['id' => backup::VAR_ACTIVITYID]);
        // Данные сессий и участников записываем только если нужны пользовательские данные
        if ($userinfo){
            $session->set_source_table('event3kl_sessions', ['event3klid' => backup::VAR_PARENTID]);
            $member->set_source_table('event3kl_session_members', ['sessionid' => backup::VAR_PARENTID]);
        }
        // аннотирование id
        $member->annotate_ids('user', 'userid');
        $session->annotate_ids('group', 'groupid');
        // аннотирование файлов
        $event3kl->annotate_files('mod_event3kl', 'intro', null);
        $session->annotate_files('mod_event3kl', 'sessionrecord', 'id');

        return $this->prepare_activity_structure($event3kl);
    }
}

