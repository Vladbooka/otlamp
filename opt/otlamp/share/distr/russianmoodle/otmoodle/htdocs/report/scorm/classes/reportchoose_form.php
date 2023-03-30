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
 * Отчет по результатам SCORM. Форма локальных настроек плагина.
 *
 * @package    report
 * @subpackage scorm
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_scorm;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/formslib.php');

use moodleform;
use moodle_url;
use html_writer;
use core_course_category;
use context_system;
use core_course_list_element;
use context_module;

class reportchoose_form extends moodleform
{

    public function definition()
    {
        // Базовая инициализация
        $mform = &$this->_form;
        $this->cmid = $this->_customdata->cmid;
        $cms = $this->get_available_cms();

        // Заголовок
        $mform->addElement(
            'header',
            'reportchoose_form_header',
            get_string('reportchoose_form_header', 'report_scorm')
        );

        // Выбор отчета
        $availablereports = $this->get_available_reports($cms);
        $mform->addElement(
            'select',
            'reportchoose_form_select_report',
            get_string('reportchoose_form_select_report_title', 'report_scorm'),
            $availablereports
        );

        if ( isset($availablereports['shortstatistic']) || isset($availablereports['fullstatistic']) )
        {// Добавление фильтров для отчета по статистике прохождения

            // Генерация данных фильтра
            $filterdata = [];
            foreach ( $cms as $cmid => $cm )
            {
                $courseid = (int)$cm->course;
                if ( ! isset($filterdata[$courseid]) )
                {// Добавление курса в данные фильтрации
                    $course = get_course($courseid);
                    $filterdata[$courseid]['course'] = $course;
                }
                $filterdata[$courseid]['cms'][$cm->id] = $cm->get_formatted_name();
            }
            $filter = [];
            // Отображение фильтра
            foreach ( $filterdata as $courseid => $coursedata )
            {
                $cms = [];
                if ( ! empty( $coursedata['cms'] ) )
                {
                    foreach ( $coursedata['cms'] as $cmid => $cmname )
                    {
                        $cms[] = $mform->createElement(
                            'checkbox', '_'.$cmid,
                            '',
                            $cmname
                        );
                        if ( $this->cmid == $cmid )
                        {
                            $mform->setDefault('_'.$cmid, 1);
                        }
                    }
                }

                if ( ! empty($cms) )
                {// Модули курса найдены
                    // Добавление курса
                    $course = $mform->createElement(
                        'static',
                        'courselabel'.$courseid,
                        '',
                        html_writer::tag('div', html_writer::tag('strong', $coursedata['course']->shortname))
                    );
                    $filter = array_merge($filter, [$course], $cms);
                }
            }
            $mform->addGroup($filter, 'cms', '', \html_writer::div('','col-12 py-0'), false);
            $mform->disabledIf('cms', 'reportchoose_form_select_report', 'eq', 'main');

            // Выбрать поля для группировки
            $mform->addElement(
                    'select',
                    'group_field',
                    get_string('reportchoose_form_select_group_field', 'report_scorm'),
                    [
                        'city' => get_string('report_scorm_header_city', 'report_scorm'),
                        'department' => get_string('report_scorm_header_department', 'report_scorm'),
                    ]);
            $mform->disabledIf('group_field', 'reportchoose_form_select_report', 'noteq', 'shortstatistic');
        }

        // Действия
        $buttonarray = [];
        $buttonarray[] = $mform->createElement(
                'select',
                'export_format',
                '',
                [
                                'xls' => get_string('reportchoose_form_export_format_xls', 'report_scorm'),
                                'pdf' => get_string('reportchoose_form_export_format_pdf', 'report_scorm'),
                                'html' => get_string('reportchoose_form_export_format_html', 'report_scorm')
                ]
                );
        $buttonarray[] = $mform->createElement(
            'submit',
            'reportchoose_form_submit',
            get_string('reportchoose_form_submit', 'report_scorm')
        );
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
    }

    /**
     * Обработчик формы
     */
    public function process()
    {
        if ( $this->is_submitted() && confirm_sesskey() &&
             $this->is_validated() && $formdata = $this->get_data()
           )
        {// Форма отправлена и проверена

            // Тип запрашиваемого отчета
            $reporttype = '';
            if ( isset($formdata->reportchoose_form_select_report) )
            {// Найден отчет
                $reporttype = (string)$formdata->reportchoose_form_select_report;
            }

            // Дефолтные параметры
            $type = $formdata->export_format;
            $format = '';
            $cms = [];
            $vars = [];

            switch ( $reporttype )
            {
                // Базовый отчет
                case 'main' :
                    $format = 'basic_report';
                    break;

                case 'shortstatistic' :
                    // Получить выбранные модули курса
                    foreach ( $formdata as $cmid => $data )
                    {
                        $cmid = str_replace('_', '', $cmid);
                        if ( (string)$cmid === (string)intval($cmid) )
                        {// Передан идентификатор cmid
                            // Проверка доступа
                            $context = context_module::instance((int)$cmid);
                            if ( has_capability('report/scorm:viewstatistic', $context) )
                            {// Доступ получен
                                $cms[] = (int)$cmid;
                            }
                        }
                    }
                    $vars['group_field'] = $formdata->group_field;
                    $format = 'short_report';
                    break;

                case 'fullstatistic' :
                    // Получить выбранные модули курса
                    foreach ( $formdata as $cmid => $data )
                    {
                        $cmid = str_replace('_', '', $cmid);
                        if ( (string)$cmid === (string)intval($cmid) )
                        {// Передан идентификатор cmid
                            // Проверка доступа
                            $context = context_module::instance((int)$cmid);
                            if ( has_capability('report/scorm:viewstatistic', $context) )
                            {// Доступ получен
                                $cms[] = (int)$cmid;
                            }
                        }
                    }
                    $format = 'full_report';
                    break;

                default:
                    break;
            }

            if ( in_array($format, ['short_report', 'full_report', 'basic_report']) &&
                    in_array($type, ['xls', 'pdf', 'html']))
            {
                $link = new moodle_url('/report/scorm/export.php', array_merge($vars, [
                                'format' => $format,
                                'type' => $formdata->export_format,
                                'cmids' => implode(',', $cms)
                ]));
                // If this file was requested from a form, then mark download as complete (before sending headers).
                \core_form\util::form_download_complete();
                redirect($link);
            } else
            {
                redirect(new moodle_url('/report/scorm/index.php'));
            }
        }
    }

    /**
     * Получить доступные типы отчетов
     *
     * @param array $cms - Массив доступных модулей курса
     *
     * @return array
     */
    protected function get_available_reports($cms)
    {
        $available = [];

        $systemcontext = context_system::instance();
        if ( has_capability('report/scorm:view', $systemcontext) )
        {// Доступ к общему отчету открыт
            $available['main'] = get_string('reportchoose_form_select_report_main', 'report_scorm');
        }

        // Добавление отчетов по статистике
        if ( ! empty($cms) )
        {
            $available['shortstatistic'] = get_string('reportchoose_form_select_report_shortstatistic', 'report_scorm');
            $available['fullstatistic'] = get_string('reportchoose_form_select_report_fullstatistic', 'report_scorm');
        }

        return $available;
    }

    /**
     * Получить доступные модули курса SCORM
     *
     * @return array
     */
    protected function get_available_cms()
    {
        // Получение списков курсов, где добавлен модуль SCORM
        $searchcriteria = [
            'modulelist' => 'scorm'
        ];
        $courses = core_course_category::search_courses($searchcriteria);

        $cms = [];
        // Получение модулей SCORM в курсах
        foreach ( $courses as $courseinlist )
        {
            // Получение всех модулей из курса
            $coursemodinfo = get_fast_modinfo($courseinlist->id);
            $coursecms = (array)$coursemodinfo->get_cms();
            foreach ( $coursecms as $cm )
            {
                if ( $cm->modname == 'scorm' && has_capability('report/scorm:viewstatistic', $cm->context) )
                {// Пользователю доступен модуль курса
                    $cms[$cm->id] = $cm;
                }
            }
        }
        return $cms;
    }
}