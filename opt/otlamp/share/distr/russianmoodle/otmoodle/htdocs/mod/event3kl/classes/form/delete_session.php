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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class delete_session extends \moodleform {

    public static function form_prechecks($sessionid, $userid) {

        if (is_null($sessionid)) {
            throw new \coding_exception('missing required: sessionid');
        }

        // получение объекта сессии
        $session = new session($sessionid);
        // получение объекта модуля занятия
        $event3kl = new event3kl($session->get('event3klid'));
        // получение идентификатора группы
        $groupid = $session->get('groupid');


        if (!$event3kl->is_manager($userid, $groupid)) {
            throw new \coding_exception('Delete session is available for managers only');
        }

        if ($event3kl->get('format') != 'manual') {
            throw new \coding_exception('Manual delete session is available with manual format only');
        }
        if ($session->get('status') != 'plan') {
            throw new \coding_exception('Deletion available for sessions with status plan only');
        }

        return [
            'session' => $session,
            'event3kl' => $event3kl,
            'groupid' => $groupid
        ];
    }

    protected function definition() {

        $sessionid = $this->_customdata['sessionid'] ?? null;
        $userid = $this->_customdata['userid'] ?? null;

        /**
         * @var session $session
         * @var event3kl $event3kl
         */
        try {
            list('session' => $session, 'event3kl' => $event3kl) = self::form_prechecks($sessionid, $userid);
        } catch (\Exception $ex) {
            return;
        }

        $mform =& $this->_form;
        $mform->addElement('submit', 'delete', get_string('delete_session', 'mod_event3kl'));

    }

    protected function get_form_identifier() {
        $sessionid = $this->_customdata['sessionid'];
        $userid = $this->_customdata['userid'];
        $class = get_class($this);
        return preg_replace('/[^a-z0-9_]/i', '_', $class).'_'.$sessionid.'_'.$userid;
    }

    public function process() {

        $sessionid = $this->_customdata['sessionid'] ?? null;
        $userid = $this->_customdata['userid'] ?? null;
        /**
         * @var session $session
         * @var event3kl $event3kl
         */
        try {
            list('session' => $session, 'event3kl' => $event3kl) = self::form_prechecks($sessionid, $userid);
        } catch (\Exception $ex) {
            return;
        }

        $formdata = $this->get_data();
        if (!empty($formdata->delete)) {
            $session->delete();
        }
        return null;
    }


    public function render() {
        global $OUTPUT;

        $sessionid = $this->_customdata['sessionid'] ?? null;
        $userid = $this->_customdata['userid'] ?? null;

        /**
         * @var session $session
         * @var event3kl $event3kl
         */
        try {
            list('session' => $session, 'event3kl' => $event3kl) = self::form_prechecks($sessionid, $userid);
        } catch (\Exception $ex) {
            return '';
        }

        $mform =& $this->_form;
        $mform->updateAttributes(['class' => $mform->getAttribute('class').' no-air']);
        $this->set_display_vertical();

        $templatedata = [
            'id' => $sessionid,
            'form_delete_session' => parent::render()
        ];

        return $OUTPUT->render_from_template('mod_event3kl/delete_session_btn', $templatedata);
    }

}