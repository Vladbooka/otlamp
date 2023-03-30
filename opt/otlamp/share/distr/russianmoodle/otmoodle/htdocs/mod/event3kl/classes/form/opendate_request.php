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
 * Согласование даты, предложение даты участником.
 *
 * @package    mod_event3kl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_event3kl\form;

use mod_event3kl\session;
use mod_event3kl\event3kl;
use mod_event3kl\session_member;
use core\output\notification;
use mod_event3kl\datemode\opendate;
use function src\transformer\utils\get_user;
use core_user;
use core\message\message;
use core_date;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class opendate_request extends \moodleform {

    protected function definition() {

        $mform =& $this->_form;

        $offerlabel = get_string('opendate_offer', 'mod_event3kl');
        $mform->addElement('date_time_selector', 'opendate_offer', $offerlabel);
        $this->add_action_buttons(false);
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
        // нельзя выбирать дату в сессиях со статусом "действующая" и дальше, только "план"
        if ($session->get('status') != 'plan') {
            return false;
        }

        // нельзя запрашивать дату, если дата уже согласована
        if (!empty($session->obtain_startdate())) {
            return false;
        }

        // нельзя запрашивать дату, если она уже запрошена
        if (!empty($session->get('offereddate'))) {
            return false;
        }

        $event3kl = new event3kl($session->get('event3klid'));
        // нельзя подписываться в сессии, относящиеся к занятию, настроенному не на "Свободное время" (opendate)
        if ($event3kl->get('datemode') != 'opendate') {
            return false;
        }
        // "Свободное время" (opendate) не может быть использовано совместно с форматами отличными от "Индивидуальный" (individual)
        if (!in_array($event3kl->get('format'), opendate::get_suitable_formats())) {
            return false;
        }

        $member = session_member::get_session_member($sessionid, $userid);
        // Нельзя запрашивать дату, если ты не являешься участником сессии
        if (!$member) {
            return false;
        }

        // нельзя стать участником сессии, если группа тебе не доступна (на случай если права/подписки поменялись, а члены сессии не обновлены)
        if (!$session->is_potential_member($userid)) {
            return false;
        }

        return true;
    }

    public function process() {

        // получение данных формы (если была отправлена)
        $formdata = $this->get_data();

        if (!empty($formdata)) {

            if ($this->is_suitable() && !empty($formdata->opendate_offer)) {
                $sessionid = $this->_customdata['sessionid'];
                $session = new session($sessionid);
                $session->set('offereddate', $formdata->opendate_offer);
                $session->save();
                // Отправить уведомление преподавателю
                $this->send_notification($session);
                // вывести сообщение, что успешно выполнили операцию
                return new notification(get_string('opendate_request_process_success', 'mod_event3kl'), notification::NOTIFY_SUCCESS);
            } else {
                // пользователь пытался запросить дату, но что-то пошло не так
                // вывести сообщение, что операция не доступна
                return new notification(get_string('opendate_request_process_error', 'mod_event3kl'), notification::NOTIFY_ERROR);
            }
        }

        // форма не отправлялась - мы не обрабатывали
        return null;
    }

    /*
     * Метод отправки уведомления преподавателю
     *
     * @param session|int $session сессия занятия или её идентификатор
     */

    protected function send_notification($session){

        global $DB;

        // Получение сессии, если передан идентификатор
        if (is_int($session)){
            $session = new session($session);
        }
        if (! $session instanceof session){
            throw new \coding_exception('Incorrect session was handed to send_notification method');
        }
        // Получение получателей уведомления:)
        $event3kl = $session->obtain_event3kl();
        $cm = $event3kl->obtain_cm();
        $users = $event3kl->get_event_users(false);
        $groupid = $session->get('groupid');
        $receivers = [];
        $receivers = array_merge($receivers, $users[$groupid]['managers']);
        if (! empty($receivers)){
            // Подстановки в строки сообщения
            $messagevars = new \stdClass();
            $course = get_course($event3kl->get('course'));
            $messagevars->coursefullname = $course->fullname;
            $confirmationlink = new \moodle_url('/mod/event3kl/view.php', ['id' => $cm->id]);
            $messagevars->confirmationlink = $confirmationlink->out(true);
            $user = $DB->get_record('user', ['id' => $this->_customdata['userid']]);
            $messagevars->userfullname = fullname($user);
            $messagevars->event3klfullname = $event3kl->get('name');
            $messagevars->sessionname = $session->get('name');
            // Формирование и отправка уведомления
            foreach ($receivers as $receiver){
                if ($receiver->timezone == 99){
                    $timezone = core_date::get_server_timezone();
                } else {
                    $timezone = $receiver->timezone;
                }
                $messagevars->offereddate = userdate($session->get('offereddate'),'',$timezone);
                $message = new message();
                $message->userfrom = core_user::get_noreply_user();
                $message->component = 'mod_event3kl';
                $message->fullmessageformat = FORMAT_HTML;
                $message->subject = strip_tags(get_string(
                    'message__new_opendate_request__subject',
                    'mod_event3kl',
                    $messagevars
                    ));
                $message->smallmessage = strip_tags(get_string(
                    'message__new_opendate_request__smallmessage',
                    'mod_event3kl',
                    $messagevars
                    ));
                $message->fullmessagehtml = get_string(
                    'message__new_opendate_request__fullmessage',
                    'mod_event3kl',
                    $messagevars
                    );
                $message->fullmessage = strip_tags($message->fullmessagehtml);
                $message->name = 'new_opendate_request';
                $message->userto = $receiver->id;
                $message->notification = 1;

                message_send($message);
            }
        }
    }

}