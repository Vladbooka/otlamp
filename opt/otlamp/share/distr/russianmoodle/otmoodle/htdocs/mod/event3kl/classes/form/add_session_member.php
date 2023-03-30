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
 * Согласование даты, подтверждение даты (предложенной участником) преподавателем.
 *
 * @package    mod_event3kl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_event3kl\form;

use mod_event3kl\session;
use mod_event3kl\session_member;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class add_session_member extends \moodleform {

    public static function form_prechecks($sessionid) {

        global $USER;

        $session = new session($sessionid);
        // убедимся, что есть права добавлять участников сессии, иначе - нельзя отображать форму
        if (!$session->is_manager($USER->id)) {
            throw new \coding_exception('Adding session members is available for managers only');
        }

        $event3kl = $session->obtain_event3kl();
        // добавлять участников вручную можно только в формате подгруппы
        if ($event3kl->get('format') != 'manual') {
            throw new \coding_exception('Adding session members is available for manual format only');
        }
        // добавлять участников вручную пока считаем, что нельзя, если
        // включен режим Время по заявке (выбор сессии со свободными местами) - ведь там
        // пользователи сами выбирают сессию, а не менеджер их насильно запихивает
        if ($event3kl->get('datemode') == 'vacantseat') {
            throw new \coding_exception('Adding members for session with "vacantseat" datemode is unavailable');
        }

        // получение текущих участников сессии
        $members = session_member::get_records(['sessionid' => $sessionid]);
        // нельзя добавлять участников, если уже достигнуто максимально допустимое число участников
        if ($session->get('maxmembers') != 0 && $session->get('maxmembers') <= count($members)) {
            throw new \Exception('Maxmembers exceeded. Adding new member denied.');
        }

        // Добавление участников возсожно только на этапе планирования сессии
        if ($session->get('status') != 'plan'){
            throw new \coding_exception('Adding session members is available for sessions in plan status only');
        }
        
        // получение потенциальных участников сессии
        $eventusers = $event3kl->get_event_users();
        $potentialmembers = $eventusers[$session->get('groupid')]['members'] ?? [];
        // удаление из потенциальных - текущих, чтобы узнать, сколько потенциальных еще осталось
        foreach ($members as $member) {
            $memberuserid = $member->get('userid');
            if (array_key_exists($memberuserid, $potentialmembers)) {
                unset($potentialmembers[$memberuserid]);
            }
        }
        // если потенциальных не осталось - форма не нужна
        if (count($potentialmembers) == 0) {
            throw new \Exception('No users to add as member to the session');
        }


        return [
            'session' => $session,
            'event3kl' => $event3kl,
            'potential_members' => $potentialmembers
        ];
    }

    protected function definition() {

        $sessionid = $this->_customdata['sessionid'] ?? null;

        /**
         * @var session $session
         * @var event3kl $event3kl
         * @var array $potentialmembers
         */
        try {
            list(
                'session' => $session,
                'event3kl' => $event3kl,
                'potential_members' => $potentialmembers
            ) = self::form_prechecks($sessionid);
        } catch (\Exception $ex) {
            return;
        }

        $mform =& $this->_form;

        // заголовок формы добавления участника сессии
        $mform->addElement('html', \html_writer::tag('h2', get_string('add_member_header', 'mod_event3kl')));

        // Добавление нового участника
        $options = [];
        foreach($potentialmembers as $potentialmember) {
            $options[$potentialmember->id] = fullname($potentialmember);
        }
        $addmemberlabel = get_string('add_member', 'mod_event3kl');
        $mform->addElement('select', 'add_member', $addmemberlabel, $options);

        // кнопка отправки формы добавления
        $mform->addElement('submit', 'add_member_submit', get_string('add_member_submit', 'mod_event3kl'));
    }

    public function process() {

        $sessionid = $this->_customdata['sessionid'] ?? null;

        /**
         * @var session $session
         * @var event3kl $event3kl
         * @var array $potentialmembers
         */
        try {
            list(
                'session' => $session,
                'event3kl' => $event3kl,
                'potential_members' => $potentialmembers
            ) = self::form_prechecks($sessionid);
        } catch (\Exception $ex) {
            return;
        }

        $formdata = $this->get_data();
        if ($formdata) {
            $userid = $formdata->add_member;
            if (array_key_exists($userid, $potentialmembers)) {
                $sessionmember = new session_member();
                $sessionmember->set('userid', $userid);
                $sessionmember->set('sessionid', $sessionid);
                $sessionmember->save();
            }
        }

        if ($formdata || $this->is_cancelled()) {
            $sessionmembersurl = new \moodle_url('/mod/event3kl/session_members.php', ['id' => $sessionid]);
            redirect($sessionmembersurl);
        }

    }

}