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

namespace mod_simplecertificate;

use core\notification;

require_once ($CFG->dirroot . '/mod/simplecertificate/locallib.php');

defined('MOODLE_INTERNAL') || die();

/**
 * Класс распределитель входящих событий
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class event_handler
{
    public static function handle_core_user_graded(\core\event\user_graded $event)
    {
        global $DB;
        
        $gradegrade = $event->get_grade();
        $gradeitem = $gradegrade->load_grade_item();
        
        // проверка, что оценка была - за курс
        if ($gradeitem->is_course_item())
        {
            // не получаем пользователя сразу, чтобы не делать лишних запросов
            // (если оценка не отслеживается сертификатами в курсе, то пользователь и не понадобится)
            $user = null;
            $simplecertificates = \simplecertificate::get_course_objects($gradeitem->courseid);
            foreach($simplecertificates as $simplecertificate)
            {
                if (!empty($simplecertificate->get_instance()->issueongrade))
                {
                    if (is_null($user))
                    {
                        $user = $DB->get_record('user', ['id' => $gradegrade->userid], '*', MUST_EXIST);
                    }
                    $simplecertificate->user_certificate_issue($user);
                }
            }
        }
    }
    
    public static function handle_mod_otcourselogic_state_switched(\mod_otcourselogic\event\state_switched $event)
    {
        global $DB;
        
        // проверка, что логика курса стала активна
        if (!empty($event->other['state']))
        {
            // не получаем пользователя сразу, чтобы не делать лишних запросов
            // (если эта логика курса не отслеживается сертификатами, то пользователь и не понадобится)
            $user = null;
            $simplecertificates = \simplecertificate::get_course_objects($event->courseid);
            foreach($simplecertificates as $simplecertificate)
            {
                
                $instance = $simplecertificate->get_instance();
                // проверка, что сертификат настроен на автовыпуск в сочетании с этой логикой курса
                if (!empty($instance->issueonotcl) && !empty($instance->otcltoissue) &&
                    $instance->otcltoissue == $event->get_context()->instanceid)
                {
                    if (is_null($user))
                    {
                        $user = $DB->get_record('user', ['id' => $event->relateduserid], '*', MUST_EXIST);
                    }
                    $simplecertificate->user_certificate_issue($user);
                }
            }
        }
    }
    
    private static function display_course_modal($courseid)
    {
        global $USER, $DB, $PAGE;
        
        if (!isset($PAGE->context))
        {
            $PAGE->set_context(null);
        }
        
        $simplecertificates = \simplecertificate::get_course_objects($courseid);
        foreach($simplecertificates as $simplecertificate)
        {
            $scinstance = $simplecertificate->get_instance();
            // проверка, что настроено отображать в модалке о недоставленных сертификатах
            if (!empty($scinstance->modalundelivered))
            {
                // проверяем, был ли сертификат выпущен, но не доставлен
                $issuecert = $DB->get_record(
                    'simplecertificate_issues',
                    [
                        'userid' => $USER->id,
                        'certificateid' => $scinstance->id,
                        'timedeleted' => null,
                        'firstdeliverytime' => null
                    ]
                    );
                if (!empty($issuecert))
                {
                    
                    // показать модалку
                    
                    $messagedata = new \stdClass();
                    $messagedata->coursefullname = $simplecertificate->get_course()->fullname;
                    $messagedata->certificatename = $scinstance->name;
                    
                    $modalheading = get_string('certificate_issued_by_system_modal_heading', 'mod_simplecertificate', $messagedata);
                    $modaltext = get_string('certificate_issued_by_system_modal_text', 'mod_simplecertificate', $messagedata);
                    
                    $certificateurl = new \moodle_url('/mod/simplecertificate/view.php', [
                        'id' => $simplecertificate->get_course_module()->id,
                        'action' => 'get'
                    ]);
                    $certificatetext = get_string('display_certificate', 'simplecertificate');
                    switch($scinstance->delivery)
                    {
                        case $simplecertificate::OUTPUT_DISABLED:
                            $certificateurl = null;
                            $certificatetext = null;
                            break;
                        case $simplecertificate::OUTPUT_FORCE_DOWNLOAD:
                            $certificatetext = get_string('download_certificate', 'simplecertificate');
                            break;
                        case $simplecertificate::OUTPUT_SEND_EMAIL:
                            $certificateurl->param('fd', $simplecertificate::OUTPUT_OPEN_IN_BROWSER);
                            break;
                    }
                    
                    $PAGE->requires->js_call_amd('mod_simplecertificate/notification', 'display', [
                        $modalheading,
                        $modaltext,
                        $certificatetext,
                        (is_null($certificateurl) ? null : $certificateurl->out(false)),
                        get_string('closebuttontitle', 'moodle')
                    ]);
                    
                    // устанавливаем дату первой доставки сертификата, чтобы модалка больше не показывалась
                    $issuecert->firstdeliverytime = time();
                    $DB->update_record('simplecertificate_issues', $issuecert);
                }
            }
        }
    }
    
    public static function handle_core_course_viewed(\core\event\course_viewed $event)
    {
        self::display_course_modal($event->courseid);
    }
    
    public static function handle_mod_quiz_course_module_viewed(\mod_quiz\event\course_module_viewed $event)
    {
        self::display_course_modal($event->courseid);
    }
    
    
}
