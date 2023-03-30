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
 * Отчет по неопубликованным заданиям.
 *
 * @package    report
 * @subpackage notreleased_assignments
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/report/notreleased_assignments/form.php');

/**
 * Report notreleased_assignments renderable class.
 *
 * @package    report
 * @subpackage notreleased_assignments
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_notreleased_assignments_renderable implements renderable {
    
    /**
     * Форма настроек отображения отчета
     * @var report_notreleased_assignments_generalreport_form
     */
    protected $form;
    
    /**
     * Таблица отчета
     * @var html_table
     */
    protected $table;

    /**
     * Формат отчета
     * @var string
     */
    protected $format;
    
    /**
     * Доступные форматы отчета
     * @var array
     */
    protected $availableformats;
    
    public function __construct($course = null, $format = 'html') {
        $this->availableformats = ['html', 'xls'];
        $customdata = new stdClass();
        $customdata->report = & $this;
        $url = new moodle_url("/report/notreleased_assignments/index.php");
        $this->form = new report_notreleased_assignments_generalreport_form($url, $customdata);
        $this->format = 'html';
    }
    
    /**
     * Получить доступные форматы
     * @return string
     */
    public function get_available_formats()
    {
        return $this->availableformats;
    }
    
    /**
     * Отобразить отчет
     */
    public function display()
    {
        echo $this->render();
    }
    
    /**
     * Сформировать отчет
     * @return string
     */
    public function render()
    {
        $html = '';
        $this->form->process();
        $html .= $this->form->render();
        
        if( ! empty($this->table) )
        {
            $html .= $this->table;
        } 
        if($this->form->is_submitted() && empty($this->table)) {
            $html .= get_string('empty_report', 'report_notreleased_assignments');
        }
        
        return $html;
    }
    
    /**
     * Установить данные для формирования отчета
     * @param stdClass $formdata
     */
    public function set_data($course = null)
    {
        $this->table = $this->get_data($course);
    }
    
    /**
     * Получить данные для формирования отчета
     * @return html_table объект таблицы
     */
    public function get_report()
    {
        return $this->table;
    }
    
    /**
     * Получить таблицу отчета по переданным параметрам
     * @param stdClass $course объект курса
     * @return string таблица отчета
     */
    protected function get_data($course = null)
    {
        $table = $this->get_report_table($course);
        return $table;
    }
    
    /**
     * Возвращает сформированную таблицу отчета
     * @param stdClass $course объект курса
     * @return string таблица отчета
     */
    protected function get_report_table($course = null)
    {
        $report = new report_notreleased_assignments_report($course, $this->format);
        return $report->get_report();
    }
    
    /**
     * Выставить формат отчета
     * @param string $format формат, который необходимо задать
     */
    public function set_format($format = 'html')
    {
        if( in_array($format, $this->availableformats) )
        {
            $this->format = $format;
        } else 
        {
            $this->format = 'html';
        }
    }
}
