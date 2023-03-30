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
use core\output\notification;
use mod_event3kl\event3kl;
use mod_event3kl\datemode\vacantseat;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class vacantseat_select extends \moodleform {

    protected function definition() {

        $mform =& $this->_form;

        // кнопка выбора сессии для присоединения к ней
        $mform->addElement('submit', 'vacantseat_select', get_string('vacantseat_select', 'mod_event3kl'));
    }

    protected function get_form_identifier() {
        $sessionid = $this->_customdata['sessionid'];
        $userid = $this->_customdata['userid'];
        $class = get_class($this);
        return preg_replace('/[^a-z0-9_]/i', '_', $class).'_'.$sessionid.'_'.$userid;
    }

    public function is_suitable() {
        $sessionid = $this->_customdata['sessionid'];
        $userid = $this->_customdata['userid'];

        $session = new session($sessionid);
        // нельзя записываться в сессию со статусом "действующая" и дальше, только "план"
        if ($session->get('status') != 'plan') {
            return false;
        }

        $event3kl = new event3kl($session->get('event3klid'));
        // нельзя подписываться в сессии, относящиеся к занятию, настроенному не на "Время по заявке"
        if ($event3kl->get('datemode') != 'vacantseat') {
            return false;
        }
        // "Время по заявке" не может быть использовано совместно с форматами отличными от "Подгруппы" (manual)
        if (!in_array($event3kl->get('format'), vacantseat::get_suitable_formats())) {
            return false;
        }

        $sessionmembers = session_member::get_records(['sessionid' => $session->get('id')]);
        // нельзя запиываться в сессию, места в которой уже заполнены
        if ($session->get('maxmembers') != 0 && $session->get('maxmembers') <= count($sessionmembers)) {
            return false;
        }

        $participantsessions = session::get_participant_sessions($event3kl->get('id'), $userid);
        // нельзя быть подписаным больше, чем на одну сессию
        if (count($participantsessions) > 0) {
            return false;
        }

        // нельзя стать участником сессии, если группа тебе не доступна
        if (!$session->is_potential_member($userid)) {
            return false;
        }

        return true;
    }

    public function process() {

        $sessionid = $this->_customdata['sessionid'];
        $userid = $this->_customdata['userid'];

        // получение данных формы (если была отправлена)
        $formdata = $this->get_data();

        if (!empty($formdata)) {
            if ($this->is_suitable() && !empty($formdata->vacantseat_select)) {
                // сессия выбрана для записи в неё и она подходит для этой операции
                // записываем пользователя в сессию
                $sessionmember = new session_member(0, (object)[
                    'sessionid' => $sessionid,
                    'userid' => $userid
                ]);
                $sessionmember->save();
                // вывести сообщение, что успешно выполнили операцию
                return new notification(get_string('vacantseat_process_success', 'mod_event3kl'), notification::NOTIFY_SUCCESS);
            } else {
                // пользователь пытался выбрать сессию, но что-то пошло не так
                // вывести сообщение, что операция не доступна
                return new notification(get_string('vacantseat_process_error', 'mod_event3kl'), notification::NOTIFY_ERROR);
            }
        }

        // форма не отправлялась - мы не обрабатывали
        return null;
    }

}