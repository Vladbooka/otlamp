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

use mod_event3kl\event3kl;
use mod_event3kl\session;
use mod_event3kl\datemodes;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class edit_session extends \moodleform {

    public static function form_prechecks($sessionid=null, $event3klid=null, $groupid=null) {
        global $USER;

        if (is_null($sessionid) && is_null($event3klid)) {
            throw new \coding_exception('missing required: no sessionid to edit or event3klid to create');
        }

        if (is_null($sessionid) && is_null($groupid)) {
            throw new \coding_exception('Groupid not defined but required to create session');
        }

        $session = null;
        $event3kl = null;
        if (!is_null($sessionid)) {
            // получение объекта сессии
            $session = new session($sessionid);
            // получение объекта модуля занятия
            $event3kl = new event3kl($session->get('event3klid'));
            // получение идентификатора группы
            $groupid = $session->get('groupid');
        } else if (!is_null($event3klid)) {
            // получение объекта модуля занятия
            $event3kl = new event3kl($event3klid);
        }

//         var_dump($sessionid, $session, $event3klid, $event3kl);exit;
        if (!$event3kl->is_manager($USER->id, $groupid)) {
            throw new \coding_exception('Create / edit session is available for managers only');
        }

        if (is_null($session) && $event3kl->get('format') != 'manual') {
            throw new \coding_exception('Manual creating session is available with manual format only');
        }
        if (!is_null($session) && $session->get('status') != 'plan') {
            throw new \coding_exception('Editing available for sessions with status plan only');
        }
        if (!is_null($session) && !is_null($groupid) && $session->get('groupid') != $groupid) {
            throw new \coding_exception('Session group is different to groupid from customdata');
        }
        if (!is_null($session) && !is_null($event3klid) && $session->get('event3klid') != $event3klid) {
            throw new \coding_exception('Session event3klid is different to event3klid from customdata');
        }

        return [
            'session' => $session,
            'event3kl' => $event3kl,
            'groupid' => $groupid
        ];
    }

    protected function definition() {

        $sessionid = $this->_customdata['sessionid'] ?? null;
        $event3klid = $this->_customdata['event3klid'] ?? null;
        $groupid = $this->_customdata['groupid'] ?? null;

        /**
         * @var session $session
         * @var event3kl $event3kl
         */
        try {
            list(
                'session' => $session,
                'event3kl' => $event3kl,
                'groupid' => $groupid
            ) = self::form_prechecks($sessionid, $event3klid, $groupid);
        } catch (\Exception $ex) {
            return;
        }

        $mform =& $this->_form;

        // название сессии
        $namelabel = get_string('form_session_name', 'mod_event3kl');
        $mform->addElement('text', 'name', $namelabel);
        $mform->setType('name', PARAM_RAW);

        // дата старта сессии
        $startdatelabel = get_string('form_session_startdate', 'mod_event3kl');
        $mform->addElement('date_time_selector', 'startdate', $startdatelabel);

        // максимальное число участников сессии (0 - не ограничено)
        $maxmemberslabel = get_string('form_session_maxmembers', 'mod_event3kl');
        $mform->addElement('text', 'maxmembers', $maxmemberslabel);
        $mform->setType('maxmembers', PARAM_INT);

        // в случае, если формате - не подгруппы (где препод вручную создаёт сессии),
        // редактировать можно только дату - всё остальное фризим
        if ($event3kl->get('format') != 'manual') {
            $mform->freeze(['name', 'maxmembers']);
        }
        // в случае, если настроен способ указания даты путём согалсования с учащимся,
        // редактировать дату нельзя - только согласовывать через специализированный инструмент
        if ($event3kl->get('datemode') == 'opendate') {
            $mform->freeze(['startdate']);
        }

        $this->add_action_buttons(true);

        if (!is_null($session)) {
            $data = $session->to_record();
            $data->startdate = $session->obtain_startdate();
        } else {
            // Сессии еще нет, берем время из инстанса
            $datemode = datemodes::instance($event3kl->get('datemode'), $event3kl, $groupid);
            $data = ['startdate' => $datemode->get_start_date()];
        }
        $this->set_data($data);
    }

    public function process() {

        $mform =& $this->_form;

        $sessionid = $this->_customdata['sessionid'] ?? null;
        $event3klid = $this->_customdata['event3klid'] ?? null;
        $groupid = $this->_customdata['groupid'] ?? null;

        /**
         * @var session $session
         * @var event3kl $event3kl
         */
        try {
            list(
                'session' => $session,
                'event3kl' => $event3kl,
                'groupid' => $groupid
            ) = self::form_prechecks($sessionid, $event3klid, $groupid);
        } catch (\Exception $ex) {
            return;
        }

        $formdata = $this->get_data();
        if ($formdata) {

            if (is_null($session)) {
                $session = new session();
            }
            $session->set('event3klid', $event3kl->get('id'));
            $session->set('groupid', $groupid);
            $session->set('status', 'plan');
            if (property_exists($formdata, 'name') && !$mform->isElementFrozen('name')) {
                $session->set('name', $formdata->name);
            }
            if (property_exists($formdata, 'startdate') && !$mform->isElementFrozen('startdate')) {
                if ($formdata->startdate != $session->obtain_startdate()) {
                    $session->set('overridenstartdate', $formdata->startdate);
                }
            }
            if (property_exists($formdata, 'maxmembers') && !$mform->isElementFrozen('maxmembers')) {
                $session->set('maxmembers', $formdata->maxmembers);
            }
            $session->save();
        }

        if ($formdata || $this->is_cancelled()) {
            $cm = $event3kl->obtain_cm();
            $viewurl = new \moodle_url('/mod/event3kl/view.php', ['id' => $cm->id]);
            redirect($viewurl);
        }

    }

}