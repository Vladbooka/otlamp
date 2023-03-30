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
 * Report class.
 *
 * @package    report
 * @subpackage notreleased_assignments
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');

class report_notreleased_assignments_report
{
    /**
     * Поддерживаемые форматы отчета
     * @var array
     */
    private $supportedformats = ['html', 'xls'];
    /**
     * Формат по умолчанию
     * @var string
     */
    private $exportformat = 'html';
    
    /**
     * Сформированные массив отчета
     * @var array
     */
    private $report;
    
    /**
     * Заголовки отчета
     * @var array
     */
    private $caption;
    
    /**
     * Объект курса
     * @var stdClass
     */
    private $course;
    
    /**
     * 
     * @param int|stdClass $course
     * @param string $exportformat
     */
    public function __construct($course = null, $exportformat = 'html') {
        $this->course = $course;
        $this->exportformat = $exportformat;
        $this->report = [];
        $this->caption = [
            get_string('caption_course', 'report_notreleased_assignments'),
            get_string('caption_course_description', 'report_notreleased_assignments'),
            get_string('caption_assign_name', 'report_notreleased_assignments'),
            get_string('caption_user_fullname', 'report_notreleased_assignments'),
            get_string('caption_allocatedmarker_fullanme', 'report_notreleased_assignments'),
        ];
    }
    
    /**
     *  Формирование отчета в заданном формате
     * @param boolean $download флаг скачивания отчета
     * @return array
     */
    public function get_report($download = false)
    {
        global $CFG;
        
        //получим данные для отображения таблицы согласно настройкам
        $reportdata = $this->get_reportdata();
        if (!empty($reportdata)) {
            $formatpath = $CFG->dirroot . '/report/notreleased_assignments/classes/format/' .$this->exportformat . '.php';
            if ( file_exists( $formatpath ) )
            {
                //подключение файла с классом требуемого формата
                require_once ($formatpath);
                $formatclass = 'report_notreleased_assignments_format_' . $this->exportformat;
                if ( class_exists($formatclass) )
                { // Подключение класса формата
                    $formatmanager = new $formatclass($reportdata);
                    
                    if ( ( $download || !method_exists($formatmanager, 'get_report') )
                        && method_exists($formatmanager, 'print_report'))
                    {
                        ob_clean();
                        //распечатаем/выведем отчет
                        $formatmanager->print_report();
                        exit;
                    } else if( method_exists($formatmanager, 'get_report') )
                    {
                        //вернем данные отчета
                        $outputreport = $formatmanager->get_report();
                        return $outputreport;
                    }
                }
            }
        }
        
        return [];
    }
    
    /**
     * Получить поддерживаемые форматы отчета
     * @return array
     */
    public function get_supported_formats()
    {
        return $this->supportedformats;
    }
    
    /**
     * Получить данные для отчета
     * @return array
     */
    public function get_reportdata() {
        global $CFG;
        require_once($CFG->dirroot . '/local/opentechnology/locallib.php');
        $dof = local_opentechnology_get_dof();
        if (!is_null($dof)) {
            if (is_null($this->course)) {
                $this->course = false;
            }
            $assigninstance = $dof->modlib('ama')->course($this->course)->get_instance_object('assign', false, false)->get_manager();
            if ($submissions = $assigninstance->get_notreleased_assignments()) {
                $this->set_caption();
                $this->set_data($submissions);
            }
        }
        return $this->report;
    }
    
    /**
     * Задать заголовки отчета
     */
    private function set_caption() {
        if (empty($this->report)) {
            $this->report[] = $this->caption;
        }
    }
    
    /**
     * Сформировать данные отчета для конечного пользователя
     * @param array $submissions массив объектов неопубликованных заданий с оценками
     * @return array
     */
    private function set_data($submissions) {
        if (!empty($submissions)) {
            if (empty($this->report)) {
                $this->set_caption();
            };
            foreach ($submissions as $submission) {
                if (is_null($submission->allocatedmarkerfullname)) {
                    $allocatedmarkerfullname = get_string('allocatedmarker_not_set', 'report_notreleased_assignments');
                } else {
                    $allocatedmarkerfullname = $this->exportformat == 'html' ? html_writer::link(
                            new moodle_url('/user/profile.php', ['id' => $submission->allocatedmarker]),
                            $submission->allocatedmarkerfullname
                        ) : $submission->allocatedmarkerfullname;
                }
                $this->report[] = [
                    $this->exportformat == 'html' ? 
                        html_writer::link(
                            new moodle_url('/course/view.php', ['id' => $submission->courseid]), 
                            $submission->coursefullname
                        ) : $submission->coursefullname,
                    format_text($submission->summary, $submission->summaryformat),
                    $this->exportformat == 'html' ? html_writer::link(
                            new moodle_url('/mod/assign/view.php', [
                                'id' => $submission->cmid, 
                                'action' => 'grader',
                                'userid' => $submission->userid,
                                'rownum' => 0
                            ]),
                            $submission->assignname
                        ) : $submission->assignname,
                    $this->exportformat == 'html' ? html_writer::link(
                            new moodle_url('/user/profile.php', ['id' => $submission->userid]),
                            $submission->userfullname
                        ) : $submission->userfullname,
                    $allocatedmarkerfullname,
                ];
            }
        }
        return $this->report;
    }
}