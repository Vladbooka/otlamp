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

defined('MOODLE_INTERNAL') || die();

use context_module;
use core_course_category;
use report_scorm\cmmanager;
use report_scorm\reports\AbstractReportFactory;

class basic_report extends AbstractReportFactory
{
    function __construct(array $data, $type, $options)
    {
        $this->format = 'basic_statistic';
        $this->filename = 'report_scorm_basic_report' . date('m.d.y');
        $this->data = $data;
        $this->type = $type;
    }
    
    function get_headers()
    {
        return [
            get_string('report_scorm_header_finishtime', 'report_scorm'),
            get_string('report_scorm_header_material', 'report_scorm'),
            get_string('report_scorm_header_username', 'report_scorm'),
            get_string('report_scorm_header_organization', 'report_scorm'),
            get_string('report_scorm_header_group', 'report_scorm'),
            get_string('report_scorm_header_quizresult', 'report_scorm'),
            get_string('report_scorm_header_quizstatus', 'report_scorm'),
            get_string('report_scorm_header_progress', 'report_scorm'),
            get_string('report_scorm_header_totaltime', 'report_scorm')
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
        
        // Дефолтные параметры
        $data = [];
        
        // Получение списков курсов, где добавлен модуль SCORM
        $searchcriteria = [
                        'modulelist' => 'scorm'
        ];
        $courses = core_course_category::search_courses($searchcriteria);
        // Получение модулей SCORM в курсах
        foreach ( $courses as $courseinlist )
        {
            // Получим все модули из курса
            $coursemodinfo = get_fast_modinfo($courseinlist->id);
            $cms = $coursemodinfo->get_cms();
        
            // Сбор данных о модулях SCORM
            foreach ( $cms as $cm )
            {
                if ( $cm->modname == 'scorm' )
                {// Текущий модуль курса - SCORM
        
                    // Получение данных о модуле
                    $scorm = $DB->get_record('scorm',
                            [
                                            'id' => $cm->instance
                            ], '*', MUST_EXIST
                            );
        
                    // Получение данных по пользователям
                    $sco = scorm_get_sco($scorm->launch);
                    if ( $sco )
                    {
                        $passpersent = $cmmanager->get_passpercent($cm->id);
                        //получаем контекст модуля
                        $contextmodule = context_module::instance($cm->id);
                        //получаем список студентов, которые могут делать попытки в скорме
                        $students = get_users_by_capability($contextmodule, 'mod/scorm:savetrack',
                                'u.id, u.lastname, u.firstname, u.username, u.institution', '', '', '', '', '', false);
                        foreach ( $students as $student )
                        {
                            //Получим группы курса, к которым принадлежит пользователь
                            $group = [];
                            $studentgroups = groups_get_all_groups($courseinlist->id, $student->id);
                            foreach ( $studentgroups as $studentgroup )
                            {
                                $group[] = $studentgroup->name;
                            }
                            
                            $userinfo = $cmmanager->get_user_info($cm, $sco, $student->id);
                            if ( ! empty($userinfo) )
                            {
                                $attemptdata = [];
        
                                //Дата (Последний раз работал)
                                $attemptdata[] =  ( ! empty($userinfo['runtime']) ? date("Y.m.d H:i:s", $userinfo['runtime']) : '' );
                                
                                //Материал (Название курса)
                                $attemptdata[] = $courseinlist->fullname . " " . $cm->name;
        
                                //Пользователь (ФИО и логин пользователя)
                                $attemptdata[] = $student->lastname . " " . $student->firstname . ", " .
                                        $student->username;
        
                                //Организация
                                $attemptdata[] = $student->institution;

                                //Группа
                                $attemptdata[] = implode(', ', $group);

                                // Сдал/Не сдал
                                $pass = get_string('full_report_header_passstatus_fail', 'report_scorm');
                                if ( $userinfo['userpercent'] >= $passpersent )
                                {
                                    $pass = get_string('full_report_header_passstatus_pass', 'report_scorm');
                                }
                                $attemptdata[] = $pass;
                                // Процент прохождения
                                $attemptdata[] = round($userinfo['userpercent'], 2) . '%';

                                //Просмотрено (процент просмотренного курса)
                                $attemptdata[] = ( ! empty($userinfo['scoreraw'] ) ? round($userinfo['scoreraw'], 2) . '%' : '');

                                //Потрачено (сколько времени потрачено на курс)
                                $attemptdata[] =  ( ! empty($userinfo['totaltime']) ? $userinfo['totaltime'] : '');

                                $data[] = $attemptdata;
                            }
                        }
                    }
                }
            }
        }
        
        if ( ! empty($data) )
        {
            $this->report = $data;
            if ( ! $boolean )
            {
                $this->export();
            }
        }
    }
}


