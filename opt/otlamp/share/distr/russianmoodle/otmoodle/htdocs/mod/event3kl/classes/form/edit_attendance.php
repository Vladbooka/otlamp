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
 * Форма редактирования посещаемости сессии занятия
 *
 * @package    mod_event3kl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_event3kl\form;

use mod_event3kl\event3kl;
use mod_event3kl\session;
use mod_event3kl\session_member;
use moodle_url;
use core\notification;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class edit_attendance extends \moodleform {

    protected $sessionid;

    public static function form_prechecks($sessionid) {

        global $USER;

        $session = $session = new session($sessionid);

        if ((!$session->is_manager($USER->id)) && (!$session->is_speaker($USER->id))) {
            throw new \coding_exception('Edit session attendance is available for managers and speakers only');
        }

        return ['session' => $session];
    }

    protected function definition() {

        global $USER, $DB;

        $sessionid = $this->_customdata['sessionid'] ?? null;
        $this->sessionid = $sessionid;

        // проверка доступности формы и корректности сессии
        try {
            list('session' => $session) = self::form_prechecks($sessionid);
        } catch (\Exception $ex) {
            return;
        }
        $event3kl = $session->obtain_event3kl();
        $cm = $event3kl->obtain_cm();

        // Актуализация сессий для получения участников
        $event3kl->actualize_sessions();
        if (! session::get_record(['id' => $sessionid])){
            notification::error(get_string('error_session_out_of_date','mod_event3kl'));
            $redirecturl = new moodle_url('/mod/event3kl/view.php',['id' => $cm->id]);
            redirect($redirecturl);
        }

        // Если сессия актуальна, все данные верны и права есть, выводим список
        $mform =& $this->_form;
        $members = session_member::get_records(['sessionid' => $sessionid]);
        $viewonly = true;

        // Если сессия в активном статусе, имеющие право пользователи могут отредаткировать посещаемость
        $activeorfinished = (($session->get('status') == 'active') || ($session->get('status') == 'finished'));
        $cansetattendance = $session->can_edit_attendance($USER->id);
        if ($activeorfinished && $cansetattendance){
            $viewonly = false;
        }

        // Элемент - флаг возможности редактирования посещаемости
        $mform->addElement('hidden', 'viewonly', $viewonly);
        $mform->setType('viewonly', PARAM_BOOL);

        // Элемент - id сессии
        $mform->addElement('hidden', 'id', $sessionid);
        $mform->setType('id', PARAM_INT);

        // Пояснение к использованию формы
        $mform->addElement('static', 'description', '', get_string('attendance_description','mod_event3kl'));

        $attendancestates = [
            -1 => '',
            0 => get_string('attendance_not_attended','mod_event3kl'),
            1 => get_string('attendance_is_attended','mod_event3kl'),

        ];

        foreach ($members as $member){
            // Получаем фулнейм и ссылку на профиль пользователя
            $user = $DB->get_record('user', ['id' => $member->get('userid')]);
            $profileurl = new moodle_url('/user/profile.php', ['id' => $user->id]);
            $profilelink = \html_writer::link($profileurl, fullname($user));

            // Селект для проставления посещаемости
            $elementname = 'member_attendance';
            $groupelements = [];
            $select = $mform->createElement('select', $elementname, '', $attendancestates);

            // Подставляем уже заданную посещаемость
            $attendance = $member->get('attendance');
            if (is_null($attendance)){
                $attendance = -1;
            }
            $select->setValue($attendance);
            $groupelements[] = $select;

            $manualformat = ($event3kl->get('format') == 'manual');
            $notvacantseat = ($event3kl->get('datemode') != 'vacantseat');
            // Добавляем удалитель участника
            if ($manualformat && $notvacantseat && (! $activeorfinished)){

                $name = 'delete_member';
                $label = get_string('delete_member', 'mod_event3kl');
                $groupelements[] = $mform->createElement('submit', $name, $label);
            }

            // Формируем группу элементов
            $name = 'user_attendance_' . $member->get('id');
            $mform->addGroup($groupelements, $name, $profilelink);

            // Дизэйблим посещаемость
            $elementname = $name . '[member_attendance]';
            $mform->disabledIf($elementname, 'viewonly', 'eq' ,1);
        }

        if ((! $viewonly) && (! empty($members))){
            $this->add_action_buttons(false);
//             $mform->addElement('submit','submitbutton', get_string('savechanges'));
        }

    }

    public function process(){

        global $USER, $PAGE;

        $sessionid = $this->_customdata['sessionid'] ?? null;
        $formdata = $this->get_data();

        if (! empty($formdata)){

            // проверка доступности формы и корректности сессии
            try {
                list('session' => $session) = self::form_prechecks($sessionid);
            } catch (\Exception $ex) {
                return;
            }
            $event3kl = $session->obtain_event3kl();
            $cm = $event3kl->obtain_cm();

            // Проверяем, есть ли у пользователя право редактировать посещаемость и только в этом случае что-то делаем
            if ($session->can_edit_attendance($USER->id)){
                // Актуализация сессий для получения участников
                $event3kl->actualize_sessions();
                if (! session::get_record(['id' => $sessionid])){
                    notification::error(get_string('error_session_out_of_date','mod_event3kl'));
                    $redirecturl = new moodle_url('/mod/event3kl/view.php',['id' => $cm->id]);
                    redirect($redirecturl);
                }

                // Список пользователей
                $members = session_member::get_records(['sessionid' => $sessionid]);
                foreach ($members as $member){

                    // Удаляем пользователя, если он помечен удаленным
                    $elementname = 'user_attendance_' . $member->get('id');
                    if (! empty($formdata->$elementname['delete_member']) && ($event3kl->get('format') == 'manual')){
                        $member->delete();
                        continue;
                    }

                    // Заполняем посещаемость
                    if (property_exists($formdata, $elementname) &&
                        array_key_exists('member_attendance', $formdata->$elementname)) {

                        $attendance = $formdata->$elementname['member_attendance'];
                        if ($attendance == -1) {
                            $attendance = null;
                        }
                        $member->set('attendance', $attendance);
                        $member->update();
                    }

                }
                notification::success(get_string('changessaved'));
                redirect($PAGE->url);
            }
        }
    }
}