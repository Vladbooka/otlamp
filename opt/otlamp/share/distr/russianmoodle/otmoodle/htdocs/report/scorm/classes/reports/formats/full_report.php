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
 * Отчет по результатам SCORM
 * 
 * @package    report
 * @subpackage scorm
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_scorm\reports\formats;


use report_scorm\reports\AbstractReportFactory;
use report_scorm\cmmanager;
use context_module;

defined('MOODLE_INTERNAL') || die();


class full_report extends AbstractReportFactory 
{
    function __construct(array $data, $type, $options)
    {
        $this->format = 'full_report';
        $this->filename = 'report_scorm_full_report_' . date('m.d.y');
        $this->data = $data;
        $this->type = $type;
    }
    
    function get_headers()
    {
        return [
            get_string('full_report_header_username', 'report_scorm'),
            get_string('full_report_header_email', 'report_scorm'),
            get_string('full_report_header_lastname', 'report_scorm'),
            get_string('full_report_header_firstname', 'report_scorm'),
            get_string('full_report_header_coursename', 'report_scorm'),
            get_string('full_report_header_passstatus', 'report_scorm'),
            get_string('full_report_header_passpersent', 'report_scorm'),
            get_string('full_report_header_city', 'report_scorm')
        ];
    }
    
    public function generate_data($boolean = false)
    {
        global $DB;
        
        // Операция долгая, увеличим лимиты
        // Могут выводиться уведомления, которые сломают PDF либу, по этой причине экранирование
        @set_time_limit(0);
        @raise_memory_limit(MEMORY_HUGE);
        
        // Инициализация менеджера работы со SCORM
        $cmmanager = new cmmanager();
        $report = [];
        
        foreach ( $this->data as $cmid )
        {
            $passpersent = $cmmanager->get_passpercent($cmid);
        
            // Получение модуля курса
            $cm = get_coursemodule_from_id('scorm', $cmid);
        
            // Получение данных о модуле
            $scorm = $DB->get_record('scorm',
                    [
                                    'id' => $cm->instance
                    ], '*', MUST_EXIST
                    );
            $course = get_course($cm->course);
            // Получение данных по пользователям
            $sco = scorm_get_sco($scorm->launch);
            if ( $sco )
            {
                // Получение списка студентов, которым доступно прохождение текущего SCORM
                $contextmodule = context_module::instance($cm->id);
                $students = get_users_by_capability($contextmodule, 'mod/scorm:savetrack',
                        'u.id, u.lastname, u.firstname, u.username', '', '', '', '', '', false);
                foreach ( $students as $student )
                {
                    $user = $DB->get_record('user',
                            [
                                            'id' => $student->id
                            ], '*', MUST_EXIST
                            );
                    // Получение попытки прохождения
                    $userpersent = $cmmanager->get_user_info($cm, $sco, $student->id)['userpercent'];
        
                    $tabledata = [];
                    // Логин
                    $tabledata[] = $user->username;
                    // Email
                    $tabledata[] = $user->email;
                    // Фамилия
                    $tabledata[] = $user->lastname;
                    // Имя
                    $tabledata[] = $user->firstname;
                    // Курс
                    $tabledata[] = $course->shortname;
                    // Сдал/Не сдал
                    $pass = get_string('full_report_header_passstatus_fail', 'report_scorm');
                    if ( $userpersent >= $passpersent )
                    {
                        $pass = get_string('full_report_header_passstatus_pass', 'report_scorm');
                    }
                    $tabledata[] = $pass;
                    // Процент прохождения
                    $tabledata[] = round($userpersent, 2).'%';
                    // Город
                    $tabledata[] = $user->city;
                    $report[] = $tabledata;
                }
            }
        }
        
        if ( ! empty($report) )
        {
            $this->report = $report;
            if ( ! $boolean )
            {
                $this->export();
            }
        }
    }
}


