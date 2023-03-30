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
 * Activetime report. Forms classes.
 *
 * @package    report_activetime
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
// Подключим библиотеки
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/report/activetime/locallib.php');

class report_activetime_filter_form extends moodleform {
    
    // Свойства класса
    
    /**
     * Объект курса
     * @var stdClass
     */
    protected $course;
    
    /**
     * Объект отчета
     * @var report_activetime_renderable
     */
    protected $report;
    
    /**
     * Объявление формы
     */
    function definition() 
    {
        // Получим данные
        $mform        = $this->_form;
        $this->report = $this->_customdata['report'];
        $this->course = $this->_customdata['report']->course;
        
        // Секции режима отображения отчета
        $mform->addElement('header', 'mode_display', get_string('mode_display', 'report_activetime'));
        $mform->setExpanded('mode_display', false);
        
        $enrolmodes = [
            'all' => get_string('enrolmode_all', 'report_activetime'),
            'active' => get_string('enrolmode_active', 'report_activetime'),
            'archive' => get_string('enrolmode_archive', 'report_activetime')
        ];
        $mform->addElement('select', 'enrolmode', get_string('enrolmode', 'report_activetime'), $enrolmodes);
        $mform->setDefault('enrolmode', $this->report->enrolmode);
        
        $modulemodes = [
            'all' => get_string('modulemode_all', 'report_activetime'),
            'active' => get_string('modulemode_active', 'report_activetime'),
            'archive' => get_string('modulemode_archive', 'report_activetime')
        ];
        $mform->addElement('select', 'modulemode', get_string('modulemode', 'report_activetime'), $modulemodes);
        $mform->setDefault('modulemode', $this->report->modulemode);
        
        // Секция фильтрации отчета
        $mform->addElement('header', 'filter', get_string('filter', 'report_activetime'));
        $mform->setExpanded('filter', true);
        
        $customfields = report_activetime_get_customfields_list();
        $userfields = report_activetime_get_userfields_list(['firstname', 'lastname']);
        $fields = array_merge([0 => get_string('choose_field', 'report_activetime')], $userfields, $customfields);
        
        $userfieldsfilter[] = $mform->createElement(
            'select',
            'userfieldname',
            '',
            $fields
        );
        
        $userfieldsfilter[] = $mform->createElement(
            'text',
            'userfieldvalue',
            '',
            ''
        );
        
        $repeatarray[] = $mform->createElement(
            'group', 
            null,
            get_string('userfieldsfilter', 'report_activetime'), 
            $userfieldsfilter, 
            null,
            false
        );
        
        // настройки полей
        $repeateloptions = [];
        $repeateloptions['userfieldname']['type'] = PARAM_TEXT;
        $repeateloptions['userfieldvalue']['type'] = PARAM_TEXT;

        // повторение элементов
        $this->repeat_elements(
            $repeatarray,
            1,
            $repeateloptions,
            'option_repeats',
            'option_add_fields',
            1,
            null,
            true
        );
        
        $modules = $this->get_modules_for_filter();
        $mform->addElement(
            'autocomplete',
            'modules',
            get_string('module','report_activetime'),
            $modules,
            [
                'multiple' => 'multiple',
                'noselectionstring' => get_string('for_all_modules', 'report_activetime')
            ]
        );
        
        $users = $this->get_users_for_filter();
        $mform->addElement(
            'autocomplete',
            'users',
            get_string('user','report_activetime'),
            $users,
            [
                'multiple' => 'multiple',
                'noselectionstring' => get_string('for_all_users', 'report_activetime')
            ]
        );
        
        // Кнопка сохранения
        $this->add_action_buttons(true, get_string('get_report', 'report_activetime'));
        
        // Секция экспорта отчета
        $mform->addElement('header', 'export_header', get_string('export_header', 'report_activetime'));
        $mform->setExpanded('export_header', false);
        $exporttypeselect = [
            'csv' => 'csv',
            'xlsx' => 'xlsx',
            'ods' => 'ods'
        ];
        $mform->addElement('select', 'export_type', get_string('export_type', 'report_activetime'), $exporttypeselect);
        $mform->setDefault('export_type', 'csv');
        $mform->setType('export_type', PARAM_RAW_TRIMMED);
        
        $mform->addElement('submit', 'export_submit', get_string('export_submit', 'report_activetime'));
        
        // Применим фильтр
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /**
     * Валидация формы
     * {@inheritDoc}
     * @see moodleform::validation()
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
    
    /**
     * Обработчик формы
     */
    function process()
    {
        if( $this->is_cancelled() )
        {// Отменили форму
            $url = new moodle_url(
                '/report/activetime/index.php',
                ['id' => $this->course->id]
            );
            redirect($url);
        }
    
        if( ! $formdata = $this->get_submitted_data() )
        {// Форма не отправлена - инициализируем данные по умолчанию
            $formdata = new stdClass();
            $formdata->enrolmode = 'active';
            $formdata->modulemode = 'active';
            $formdata->userfieldname = $formdata->modules = $formdata->users = [];
        } else 
        {
            // Инициализируем входные данные для отчета
            $this->init_report($formdata);
        }
        if( ! empty($formdata->export_submit) )
        {// Если запрошен экспорт отчета - выполним его
            $this->report->set_export_type($formdata->export_type);
            $this->report->download();
        }
    }
    
    /**
     * Инициализация входных данных отчета
     * @param stdClass $formdata данные, переданные формой или данные по умолчанию
     */
    protected function init_report($formdata)
    {
        // По умолчанию формат отчета html
        $format = 'html';
        $this->report->enrolmode = $formdata->enrolmode;
        $this->report->modulemode = $formdata->modulemode;
        $this->report->userfields = $this->report->customfields = [];
        if( ! empty($formdata->userfieldname) )
        {// Отделяем кастомные поля от стандартных
            foreach($formdata->userfieldname as $k => $userfieldname)
            {
                if( $userfieldname == '0' )
                {
                    unset($formdata->userfieldname[$k]);
                    continue;
                }
                if( strpos($userfieldname, 'profile_field_') !== false )
                {
                    $this->report->customfields[$userfieldname][] = $formdata->userfieldvalue[$k];
                } else
                {
                    $this->report->userfields[$userfieldname][] = $formdata->userfieldvalue[$k];
                }
            }
        }
        $this->report->modules = ! empty($formdata->modules) ? $formdata->modules : [];
        $this->report->users = ! empty($formdata->users) ? $formdata->users : [];
        if( ! empty($formdata->export_submit) && ! empty($formdata->export_type) )
        {// Если запрошен экспорт - выставим формат экспорта
            $format = $formdata->export_type;
        }
        // Запускаем формирование отчета
        $this->report->set_data($format);
    }
    
    /**
     * Получить список идентификаторов модулей для поля выбора модулей
     * @return array
     */
    protected function get_modules_for_filter()
    {
        global $DB;
        $result = [];
        list($insql, $params) = $DB->get_in_or_equal(['active', 'archive']);
        if( $this->report->is_global() )
        {
            $courseselect = '';
        } else 
        {
            $courseselect = ' AND courseid=?';
            $params[] = $this->course->id;
        }
        $sql = 'SELECT * FROM {local_learninghistory_module} WHERE status '. $insql . $courseselect;
        $modules = $DB->get_records_sql($sql, $params);
        if( ! empty($modules) )
        {
            foreach($modules as $module)
            {
                $result[$module->cmid] = $module->name . ' (' . $module->cmid .')';
            }
        }
        return $result;
    }
    
    /**
     * Получить список идентификаторов пользователей для поля выбора пользователей
     * @return array
     */
    protected function get_users_for_filter()
    {
        global $DB;
        $result = [];
        list($insql, $params) = $DB->get_in_or_equal(['active', 'archive']);
        if( $this->report->is_global() )
        {
            $courseselect = '';
        } else 
        {
            $courseselect = ' AND ll.courseid=?';
            $params[] = $this->course->id;
        }
        
        $sql = 'SELECT ll.* 
                FROM {local_learninghistory} ll 
                JOIN {local_learninghistory_cm} llcm 
                ON llcm.llid=ll.id 
                WHERE ll.status '. $insql . $courseselect . ' GROUP BY ll.courseid, ll.userid';
        $lls = $DB->get_records_sql($sql, $params);
        if( ! empty($lls) )
        {
            foreach($lls as $ll)
            {
                $user = $DB->get_record('user', ['id' => $ll->userid]);
                $result[$ll->userid] = fullname($user);
            }
        }
        return $result;
    }
}