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
use core\output\notification;
use mod_event3kl\event3kl;
use mod_event3kl\datemode\opendate;
use core_user;
use core\message\message;
use core_date;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class opendate_coordination extends \moodleform {

    protected function definition() {

        $mform =& $this->_form;

        // учащийся предлложил дату - надо согласовать
        $mform->addElement('submit', 'opendate_approve', get_string('opendate_approve', 'mod_event3kl'));
        $mform->addElement('submit', 'opendate_reject', get_string('opendate_reject', 'mod_event3kl'));
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
        // согласовнаие даты не производится в сессиях со статусом "действующая" и дальше, только "план"
        if ($session->get('status') != 'plan') {
            return false;
        }

        // нельзя согласовать дату, если дата уже согласована
        if (!empty($session->obtain_startdate())) {
            return false;
        }

        // нельзя согласовать дату, если дата не была предложена к согласованию
        if (empty($session->get('offereddate'))) {
            return false;
        }

        $event3kl = new event3kl($session->get('event3klid'));
        // нельзя согласовать дату в сессии, относящейся к занятию, настроенному не на "Свободное время" (opendate)
        if ($event3kl->get('datemode') != 'opendate') {
            return false;
        }
        // "Свободное время" (opendate) не может быть использовано совместно с форматами отличными от "Индивидуальный" (individual)
        if (!in_array($event3kl->get('format'), opendate::get_suitable_formats())) {
            return false;
        }

        // нельзя согласовывать дату, если группа тебе не доступна для упралвения ей
        if (!$session->is_manager($userid)) {
            return false;
        }

        return true;
    }

    public function process() {

        // получение данных формы (если была отправлена)
        $formdata = $this->get_data();

        if (!empty($formdata)) {

            $sessionid = $this->_customdata['sessionid'];
            $session = new session($sessionid);
            $approved = $formdata->opendate_approve ?? false;
            $rejected = $formdata->opendate_reject ?? false;
            $suitable = $this->is_suitable();

            if ($suitable && $approved) {
                $session->set('startdate', $session->get('offereddate'));
                $session->set('overridenstartdate', $session->get('offereddate'));
                $session->save();
                // Отправка уведомления о подтверждении даты
                $this->send_notification('confirmed', $session);
                // вывести сообщение, что успешно выполнили операцию
                return new notification(get_string('opendate_coordination_approve_success', 'mod_event3kl'), notification::NOTIFY_SUCCESS);
            } else if ($suitable && $rejected) {
                // Отправка уведомления об отклонении даты
                $this->send_notification('rejected', $session);
                $session->set('startdate', null);
                $session->set('overridenstartdate', null);
                $session->set('offereddate', null);
                $session->save();
                // вывести сообщение, что успешно выполнили операцию
                return new notification(get_string('opendate_coordination_reject_success', 'mod_event3kl'), notification::NOTIFY_SUCCESS);
            } else {
                // менеджер пытался согласовать дату, но что-то пошло не так
                // вывести сообщение, что операция не доступна
                return new notification(get_string('opendate_coordination_process_error', 'mod_event3kl'), notification::NOTIFY_ERROR);
            }
        }

        // форма не отправлялась - мы не обрабатывали
        return null;
    }

    /**
     * Отправка уведомлений
     *
     * @param string $key код уведомления (confirmed/rejected/confirmed_for_speakers)
     * @param session $session сессия, для которой определяется дата
     */
    protected function send_notification($key,$session){

        global $DB;

        // Получение сессии, если передан идентификатор
        if (is_int($session)){
            $session = new session($session);
        }
        if (! $session instanceof session){
            throw new \coding_exception('Incorrect session was handed to send_notification method');
        }

        // Получаем event, пользователей и группу сессии
        $event3kl = $session->obtain_event3kl();
        $cm = $event3kl->obtain_cm();
        $users = $event3kl->get_event_users(false);
        $groupid = $session->get('groupid');

        // Получение получателей уведомления:)
        $receivers = [];
        if (($key == 'confirmed') || ($key == 'rejected')){
            $receivers = array_merge($receivers, $users[$groupid]['members']);
        } else if ($key = 'confirmed_for_speakers'){
            $receivers = array_merge($receivers, $users[$groupid]['speakers']);
        }
        if (! empty($receivers)){
            // Подстановки в строки сообщения
            $messagevars = new \stdClass();
            $course = get_course($event3kl->get('course'));
            $messagevars->coursefullname = $course->fullname;
            $event3kllink = new \moodle_url('/mod/event3kl/view.php', ['id' => $cm->id]);
            $messagevars->event3kllink = $event3kllink->out(true);
            $user = $DB->get_record('user', ['id' => $this->_customdata['userid']]);
            $messagevars->userfullname = fullname($user);
            $messagevars->event3klfullname = $event3kl->get('name');
            $messagevars->sessionname = $session->get('name');
            // Формирование и отправка уведомления
            foreach ($receivers as $receiver){
                if ((! $session->is_member($receiver->id)) && (! $session->is_speaker($receiver->id))){
                    continue;
                }
                if ($key == 'confirmed_for_speakers' && $session->is_speaker($receiver->id) && $receiver->id == $user->id) {
                    // не отправляем уведомление самому себе о том, что сам же подтвердил дату
                    continue;
                }
                if ($receiver->timezone == 99){
                    $timezone = core_date::get_server_timezone();
                } else {
                    $timezone = $receiver->timezone;
                }
                $messagevars->eventdate = userdate($session->get('offereddate'),'',$timezone);
                $message = new message();
                $message->userfrom = core_user::get_noreply_user();
                $message->component = 'mod_event3kl';
                $message->fullmessageformat = FORMAT_HTML;
                $message->subject = strip_tags(get_string(
                    'message__opendate_request_' . $key .'__subject',
                    'mod_event3kl',
                    $messagevars
                    ));
                $message->smallmessage = strip_tags(get_string(
                    'message__opendate_request_' . $key .'__smallmessage',
                    'mod_event3kl',
                    $messagevars
                    ));
                $message->fullmessagehtml = get_string(
                    'message__opendate_request_' . $key .'__fullmessage',
                    'mod_event3kl',
                    $messagevars
                    );
                $message->fullmessage = strip_tags($message->fullmessagehtml);
                $message->name = 'opendate_request_' . $key;
                $message->userto = $receiver->id;
                $message->notification = 1;

                message_send($message);
            }
        }
        // Отправляем сообщение для спикеров
        if ($key == 'confirmed'){
            $this->send_notification('confirmed_for_speakers', $session);
        }

    }
}