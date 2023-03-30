<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Отчет по неопубликованным заданиям. Классы форм.
 *
 * @package    report
 * @subpackage notreleased_assignments
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir.'/formslib.php');

class report_notreleased_assignments_generalreport_form extends moodleform
{
    protected function definition()
    {
        $mform = & $this->_form;
        $this->report = $this->_customdata->report;

        // ДАННЫЕ ФОРМЫ
        
        $context = context_system::instance();
        $mform->addElement('header', 'header', get_string('form_header', 'report_notreleased_assignments'));
        if (!has_capability('report/notreleased_assignments:view', $context)) {
            $mform->addElement('static', 'nopermission', '', get_string('nopermission', 'report_notreleased_assignments'));
        } else {
            $mform->setExpanded('header');
            $mform->addElement(
                'select', 
                'format', 
                get_string('choose_format', 'report_notreleased_assignments'), 
                array_combine($this->report->get_available_formats(), $this->report->get_available_formats())
            );
            $mform->setDefault('format', 'html');
            $this->add_action_buttons(false, get_string('download_report', 'report_notreleased_assignments'));
        }
    }

    /**
     * Проверка на стороне сервера
     *
     * @param array data - данные из формы
     * @param array files - файлы из формы
     *
     * @return array - массив ошибок
     */
    public function validation($data,$files)
    {
        return parent::validation($data, $files);
    }

    /**
     * Обработчик формы
     */
    public function process()
    {
        if ($formdata = $this->get_data()) {// Форма отправлена и проверена
            $this->report->set_format($formdata->format);
            
            if (! empty($formdata->format)) {// Формат отчета
                $this->report->set_format($formdata->format);
            }
            $this->report->set_data();
            return $this->report->get_report();
        }
        return '';
    }
}
