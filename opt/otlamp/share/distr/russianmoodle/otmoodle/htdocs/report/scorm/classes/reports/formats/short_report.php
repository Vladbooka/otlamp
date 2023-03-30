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

class short_report extends AbstractReportFactory 
{
    private $group_field = null;
    
    function __construct(array $data, $type, $options = [])
    {
        $this->format = 'short_report';
        $this->filename = 'report_scorm_short_report_' . date('m.d.y');
        $this->data = $data;
        $this->type = $type;
        
        // Поле, по которому группируем пользователей
        $availabe = $this->get_available_group_fields();
        if ( isset($options['group_field']) && 
                ! empty($options) && 
                in_array($options['group_field'], $availabe) )
        {
            $this->group_field = $options['group_field'];
        }
    }
    
    function get_headers()
    {
        switch ( $this->group_field )
        {
            case 'city':
                return [ 
                    get_string('short_report_header_city', 'report_scorm'),
                    get_string('short_report_header_course', 'report_scorm'),
                    get_string('short_report_header_passpersent', 'report_scorm') 
                ];
            case 'department':
                return [
                    get_string('short_report_header_department', 'report_scorm'),
                    get_string('short_report_header_course', 'report_scorm'),
                    get_string('short_report_header_passpersent', 'report_scorm')
                ];
        }
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
        
        // Сбор данных по городам
        $data = [];
        $report = [];
        
        foreach ( $this->data as $cmid )
        {
            $passpersent = $cmmanager->get_passpercent($cmid);
            $gradeelementsdata = $cmmanager->get_gradeelements_data($cmid);
        
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
                        'u.id, u.lastname, u.firstname, u.username, u.department, u.city', '', '', '', '', '', false);
                foreach ( $students as $student )
                {
                    // Добавление данных о курсе
                    $data[(int)$cm->course]['coursename'] = $course->shortname;
                    // Добавление в курс города пользователя
                    $field = $student->{$this->group_field};
                    $data[(int)$cm->course]['fields'][$field]['field_name'] = $field;
                    // Добавление данных о сдаче
                    if ( ! isset($data[(int)$cm->course]['fields'][$field]['countpass']) )
                    {
                        $data[(int)$cm->course]['fields'][$field]['countpass'] = 0;
                    }
                    if ( ! isset($data[(int)$cm->course][$field]['countnotpass']) )
                    {
                        $data[(int)$cm->course]['fields'][$field]['countnotpass'] = 0;
                    }
                    // Получение попытки прохождения
                    $userpersent = $cmmanager->get_user_info($cm, $sco, $student->id)['userpercent'];
                    if ( $userpersent >= $passpersent )
                    {
                        $data[(int)$cm->course]['fields'][$field]['countpass']++;
                    } else
                    {
                        $data[(int)$cm->course]['fields'][$field]['countnotpass']++;
                    }
                }
            }
        }
        
        foreach ( $data as $courseid => $coursedata )
        {
            $coursename = $coursedata['coursename'];
            foreach ( $coursedata['fields'] as $field_data )
            {
                $field_name = $field_data['field_name'];
                $countpass = $field_data['countpass'];
                $countnotpass = $field_data['countnotpass'];
        
                $persent = ( $countpass * 100 ) / ( $countpass + $countnotpass );
                $report[] = [$field_name, $coursename, round($persent, 2).'%'];
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
    
    public function get_available_group_fields()
    {
        return [
            'city', 
            'department'           
        ];
    }
}


