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
 * Блок объединения отчетов. Классы форм.
*
* @package    block
* @subpackage reports_union
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/report/mods_data/locallib.php');

if (!defined('MAX_BULK_USERS')) {
    define('MAX_BULK_USERS', 2000);
}

class report_mods_data_generalreport_form extends moodleform
{
    /**
     * Объект курса
     *
     * @var stdClass
     */
    public $course = null;
    
    /**
     * Объект отчета
     * @var report_mods_data_renderable
     */
    protected $report;

    /**
     * URL для возврата
     *
     * @var moodle_url|string
     */
    public $returnurl = null;
    
    protected $onlycanviewself;
    
    /**
     * Объект dof
     * @var dof_control
     */
    protected $dof;
    
    protected $userid;

    protected function definition()
    {
        global $COURSE, $SITE, $CFG, $USER;

        $mform = & $this->_form;

        // ДАННЫЕ ФОРМЫ
        $this->report = $this->_customdata->report;
        $this->course = $this->_customdata->course;
        $this->userid = $USER->id;
        
        $this->dof = report_mods_data_get_dof();
        
        $context = context_course::instance($this->course->id);
        if( has_capability('report/mods_data:view_self_report_data', $context) &&
            ! has_capability('report/mods_data:view', $context) )
        {
            $this->onlycanviewself = true;
        } else 
        {
            $this->onlycanviewself = false;
        }

        if( ! $this->onlycanviewself )
        {
            // Секция фильтрации по курсам
            $mform->addElement(
                'header',
                'form_filter_courses_title',
                get_string('form_filter_courses_title', 'report_mods_data')
                );
            $mform->setExpanded('form_filter_courses_title', false);
            
            $courses = $this->get_courses_for_filter();
            $mform->addElement(
                'autocomplete',
                'courses',
                get_string('form_filter_courses','report_mods_data'),
                $courses,
                [
                    'multiple' => 'multiple',
                    'noselectionstring' => get_string('for_all_courses', 'report_mods_data')
                ]
                );
            
            $mform->addElement('submit', 'set_courses', get_string('form_set_filter_courses', 'report_mods_data'));
            
            // Секция фильтрации по локальным группам
            $mform->addElement(
                'header',
                'form_filter_groups_title',
                get_string('form_filter_groups_title', 'report_mods_data')
                );
            $mform->setExpanded('form_filter_groups_title', false);
            if ($this->course->id == $SITE->id) {
                $groups = $this->get_groups_for_filter(array_keys($courses));
            } else {
                $groups = $this->get_groups_for_filter([$this->course->id]);
            }
            $mform->addElement(
                'autocomplete',
                'groups',
                get_string('form_filter_groups','report_mods_data'),
                $groups,
                [
                    'multiple' => 'multiple',
                    'noselectionstring' => get_string('for_all_groups', 'report_mods_data')
                ]
                );
            
            $mform->addElement('submit', 'set_groups', get_string('form_set_filter_groups', 'report_mods_data'));
            
            // Фильтрация пользователей (делаем по аналогии с интерфейсом "Действия над несколькими пользователями")
            $mform->addElement(
                'header',
                'form_filter_users_title',
                get_string('form_filter_users_title', 'report_mods_data')
                );
            $mform->setExpanded('form_filter_users_title', false);
            
            $customfields = $this->get_customfields_list(true);
            if( is_null($this->dof) )
            {
                $userfields = [];
            } else
            {
                $userfields = $this->dof->modlib('ama')->user(false)->get_userfields_list();
            }
            $fields = array_merge([0 => get_string('choose_field', 'report_mods_data')], $userfields, $customfields);
            
            $userfieldsfilter[] = $mform->createElement(
                'select',
                'fieldname',
                '',
                $fields
                );
            
            $userfieldsfilter[] = $mform->createElement(
                'text',
                'fieldvalue',
                '',
                ''
                );
            
            $repeatarray[] = $mform->createElement(
                'group',
                null,
                get_string('fields', 'report_mods_data'),
                $userfieldsfilter,
                null,
                false
                );
            
            // настройки полей
            $repeateloptions = [];
            $repeateloptions['fieldname']['type'] = PARAM_TEXT;
            $repeateloptions['fieldvalue']['type'] = PARAM_TEXT;
            
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
            
            $mform->addElement('submit', 'set_users', get_string('form_set_filter_users', 'report_mods_data'));
            
            $userlist = $this->get_selection_data([]);
            
            $acount = $userlist['acount'];
            $scount = $userlist['scount'];
            $ausers = $userlist['ausers'];
            $susers = $userlist['susers'];
            $total  = $userlist['total'];
            
            $objs = $achoices = $schoices = [];
            
            if (is_array($ausers)) {
                if ($total == $acount) {
                    $achoices[0] = get_string('allusers', 'report_mods_data', $total);
                } else {
                    $a = new stdClass();
                    $a->total  = $total;
                    $a->count = $acount;
                    $achoices[0] = get_string('allfilteredusers', 'report_mods_data', $a);
                }
                $achoices = $achoices + $ausers;
            
                if ($acount > MAX_BULK_USERS) {
                    $achoices[-1] = '...';
                }
            
            } else {
                $achoices[-1] = get_string('nofilteredusers', 'report_mods_data', $total);
            }
            
            if (is_array($susers)) {
                $a = new stdClass();
                $a->total  = $total;
                $a->count = $scount;
                $schoices[0] = get_string('allselectedusers', 'report_mods_data', $a);
                $schoices = $schoices + $susers;
            
                if ($scount > MAX_BULK_USERS) {
                    $schoices[-1] = '...';
                }
            
            } else {
                $schoices[-1] = get_string('noselectedusers', 'report_mods_data');
            }
            
            $objs[0] = $mform->createElement('select', 'ausers', get_string('available', 'report_mods_data'), $achoices, 'size="15"');
            $objs[0]->setMultiple(true);
            $objs[1] = $mform->createElement('select', 'susers', get_string('selected', 'report_mods_data'), $schoices, 'size="15"');
            $objs[1]->setMultiple(true);
            
            
            $grp = $mform->addElement('group', 'usersgrp', get_string('users', 'report_mods_data'), $objs, ' ', true);
            $mform->addHelpButton('usersgrp', 'users', 'report_mods_data');
            
            $mform->addElement('static', 'comment');
            
            $objs = array();
            $objs[] = $mform->createElement('submit', 'addsel', get_string('addsel', 'report_mods_data'));
            $objs[] = $mform->createElement('submit', 'removesel', get_string('removesel', 'report_mods_data'));
            $objs[] = $mform->createElement('submit', 'addall', get_string('addall', 'report_mods_data'));
            $objs[] = $mform->createElement('submit', 'removeall', get_string('removeall', 'report_mods_data'));
            $grp = $mform->addElement('group', 'buttonsgrp', get_string('selectedlist', 'report_mods_data'), $objs, array(' ', '<br />'), false);
            $mform->addHelpButton('buttonsgrp', 'selectedlist', 'report_mods_data');
            
            $renderer = $mform->defaultRenderer();
            $template = '<label class="qflabel" style="vertical-align:top">{label}</label> {element}';
            $renderer->setGroupElementTemplate($template, 'usersgrp');
        }
    }
    
    public function definition_after_data()
    {
        global $SITE, $SESSION;
        $mform = & $this->_form;
        
        // Получить поддерживаемые блоком модули
        $supported_modules = report_mods_data_get_supported_modules();
        $moduleelements = $courses = [];
        
        $formdata = $this->get_submitted_data();
        if( ! $this->onlycanviewself )
        {
            if( ! empty($formdata->courses) )
            {
                $courses = $formdata->courses;
            } else
            {
                if( ! empty($this->course) && $this->course->id != $SITE->id )
                {
                    $courses = [$this->course->id];
                    $coursesfield = $mform->getElement('courses');
                    $coursesfield->setValue($courses);
                } else
                {
                    $courses = array_keys(get_courses());
                }
            }
        } else
        {
            $courses = [$this->course->id];
        }
        
        if (!empty($formdata->courses)) {
            // Если была фильтрация по курсам
            $groupsincourses = $this->get_groups_for_filter($formdata->courses);
            $selectedgroups = [];
            if (!empty($formdata->groups)) {
                // Была фильтрация по группам
                foreach ($formdata->groups as $selectedgroup) {
                    // Из тех групп, которые были выбраны, соберем только те группы, которые есть в выбранных курсах
                    if (key_exists($selectedgroup, $groupsincourses)) {
                        $selectedgroups[$selectedgroup] = $groupsincourses[$selectedgroup];
                    }
                }
            }
            $groupsfield = $mform->getElement('groups');
            $groupsfield->_options = [];
            $groupsfield->loadArray($groupsincourses, $selectedgroups);
        }
        
        foreach($courses as $courseid)
        {
            // Получить список всех доступных экземпляров модулей в курсе
            if ( ! empty($courseid) )
            {// Передан ID курса
                $course_info = get_fast_modinfo($courseid);
                foreach($supported_modules as $module)
                {
                    if( ! isset($module['code']) )
                    {// Код модуля не передан
                        continue;
                    }
                
                    // Получение экземпляров модуля
                    $instances = $course_info->get_instances_of($module['code']);
                
                    if( ! empty($instances) )
                    {// Экземпляры найдены
                        foreach($instances as $cm)
                        {
                            $cmid = $cm->get_course_module_record()->id;
                
                            // Добавление чекбокса
                            $cmname = $cm->get_formatted_name();
                            $moduleelements[] = $mform->createElement(
                                'advcheckbox',
                                $module['code'].'_'.$cmid.'['.$cmid.']',
                                $cmname,
                                '',
                                ['group' => 1]
                            );
                        }
                    }
                }
            }
        }
        
        // Получаем данные фильтрации по полям
        $ufiltering = [];
        if( ! $this->onlycanviewself )
        {
            if( ! empty($formdata->fieldname) )
            {
                foreach($formdata->fieldname as $key => $fieldcode)
                {
                    if( $fieldcode !== '0' )
                    {
                        $ufiltering[$fieldcode][] = $formdata->fieldvalue[$key];
                    }
                }
            }
        } else 
        {
            $ufiltering['id'][] = $this->userid;
        }
        
        $groups = [];
        if (!empty($formdata->groups)) {
            $groups = $formdata->groups;
        }
        
        if (empty($formdata) || !empty($formdata->set_users) || !empty($formdata->set_groups)) {
            $this->add_selection_all($ufiltering, $groups);
        }
        
        if( ! $this->onlycanviewself )
        {
            // Обрабатываем кнопки отбора пользователей
            if (!empty($formdata->addall)) {
                $this->add_selection_all($ufiltering, $groups);
            
            } else if (!empty($formdata->addsel)) {
                if (!empty($formdata->usersgrp['ausers'])) {
                    if (in_array(0, $formdata->usersgrp['ausers'])) {
                        $this->add_selection_all($ufiltering, $groups);
                    } else {
                        foreach($formdata->usersgrp['ausers'] as $userid) {
                            if ($userid == -1) {
                                continue;
                            }
                            if (!isset($SESSION->report_mods_data_bulk_users[$userid])) {
                                $SESSION->report_mods_data_bulk_users[$userid] = $userid;
                            }
                        }
                    }
                }
            
            } else if (!empty($formdata->removeall)) {
                $SESSION->report_mods_data_bulk_users = [];
            
            } else if (!empty($formdata->removesel)) {
                if (!empty($formdata->usersgrp['susers'])) {
                    if (in_array(0, $formdata->usersgrp['susers'])) {
                        $SESSION->report_mods_data_bulk_users = [];
                    } else {
                        foreach($formdata->usersgrp['susers'] as $userid) {
                            if ($userid == -1) {
                                continue;
                            }
                            unset($SESSION->report_mods_data_bulk_users[$userid]);
                        }
                    }
                }
            }
            
            // Составляем списки отобранных и имеющихся по данным фильтрации
            $userlist = $this->get_selection_data($ufiltering, $groups);
            
            $acount = $userlist['acount'];
            $scount = $userlist['scount'];
            $ausers = $userlist['ausers'];
            $susers = $userlist['susers'];
            $total  = $userlist['total'];
            
            $achoices = $schoices = [];
            
            if (is_array($ausers)) {
                if ($total == $acount) {
                    $achoices[0] = get_string('allusers', 'report_mods_data', $total);
                } else {
                    $a = new stdClass();
                    $a->total  = $total;
                    $a->count = $acount;
                    $achoices[0] = get_string('allfilteredusers', 'report_mods_data', $a);
                }
                $achoices = $achoices + $ausers;
            
                if ($acount > MAX_BULK_USERS) {
                    $achoices[-1] = '...';
                }
            
            } else {
                $achoices[-1] = get_string('nofilteredusers', 'report_mods_data', $total);
            }
            
            if (is_array($susers)) {
                $a = new stdClass();
                $a->total  = $total;
                $a->count = $scount;
                $schoices[0] = get_string('allselectedusers', 'report_mods_data', $a);
                $schoices = $schoices + $susers;
            
                if ($scount > MAX_BULK_USERS) {
                    $schoices[-1] = '...';
                }
            
            } else {
                $schoices[-1] = get_string('noselectedusers', 'report_mods_data');
            }
            
            // Заполняем форму полученным данными
            $usersgrp = $mform->getElement('usersgrp');
            $group[0] = $mform->createElement('select', 'ausers', get_string('available', 'report_mods_data'), $achoices, 'size="15"');
            $group[0]->setMultiple(true);
            $group[1] = $mform->createElement('select', 'susers', get_string('selected', 'report_mods_data'), $schoices, 'size="15"');
            $group[1]->setMultiple(true);
            $usersgrp->setElements($group);
        }
        
        // Фильтрация по выполнению и попыткам
        $mform->addElement(
            'header',
            'mods_criterias_title',
            get_string('mods_criterias_title', 'report_mods_data')
            );
        // По выполнению элементов
        $completionselect = [
            'all' => get_string('completion_all', 'report_mods_data'),
            'completed' => get_string('completion_completed', 'report_mods_data'),
            'notcompleted' => get_string('completion_notcompleted', 'report_mods_data')
        ];
        $mform->addElement(
            'select',
            'completion',
            get_string('completion', 'report_mods_data'),
            $completionselect,
            'all'
            );
        // По попыткам
        $attemptsselect = [
            'all' => get_string('attempts_all', 'report_mods_data'),
            'best' => get_string('attempts_best', 'report_mods_data')
        ];
        $mform->addElement(
            'select',
            'attempts',
            get_string('attempts', 'report_mods_data'),
            $attemptsselect,
            'all'
            );
        // Период
        $periodgroup[] = $mform->createElement(
            'date_selector',
            'startdate',
            get_string('startdate', 'report_mods_data')
            );
        
        $periodgroup[] = $mform->createElement(
            'date_selector',
            'enddate',
            get_string('enddate', 'report_mods_data')
            );
        $mform->addGroup($periodgroup, 'period', get_string('period', 'report_mods_data'), '<br>');
        
        $defaultperiod = get_config('report_mods_data', 'defaultperiod');
        if( empty($defaultperiod) )
        {
            $defaultperiod = 3600 * 24 * 365;
        }
        $mform->setDefault('period[startdate]', time() - (int)$defaultperiod);
        $mform->setDefault('period[enddate]', time() + 3600 * 24);
        
        // Какие попытки в периоде нужны (все или законченные)
        $attemptsinperiod = [
            'all' => get_string('attemptsinperiod_all', 'report_mods_data'),
            'finished' => get_string('attemptsinperiod_finished', 'report_mods_data')
        ];
        $mform->addElement(
            'select',
            'attemptsinperiod',
            get_string('attemptsinperiod', 'report_mods_data'),
            $attemptsinperiod
        );
        
        // ЧЕКБОКСЫ ПОЛЕЙ ПОЛЬЗОВАТЕЛЯ
        if( is_null($this->dof) )
        {
            $userfields = [];
        } else
        {
            $userfields = $this->dof->modlib('ama')->user(false)->get_userfields_list();
        }
        // Генерация чекбоксов
        $userelements = [];
        if( ! empty($userfields) )
        {// Есть поля пользователя
            foreach($userfields as $fieldcode => $fieldname)
            {
                // Добавление чекбокса
                $userelements[] = $mform->createElement(
                    'checkbox',
                    'userfields_' . $fieldcode,
                    $fieldname
                    );
            }
        }
        
        $customfields = $this->get_customfields_list();
        if( ! empty($customfields) )
        {
            foreach($customfields as $fieldcode => $fieldname)
            {
                // Добавление чекбокса
                $userelements[] = $mform->createElement(
                    'checkbox',
                    'customuserfields_' . $fieldcode,
                    $fieldname
                    );
            }
        }
        
        // Отображение чекбоксов
        if( ! empty($userelements) )
        {
            // Набор пользовательских полей
            $mform->addElement(
                'header',
                'userfields_title',
                get_string('userfields_title', 'report_mods_data')
                );
            $mform->setExpanded('userfields_title', false);
        
            foreach($userelements as $userelement)
            {
                $mform->addElement($userelement);
            }
        }
        // Выставляем значения по умолчанию в форме
        $checkedfields = get_config('report_mods_data', 'checkedfields');
        if( ! empty($checkedfields) )
        {
            $checkedfields = explode(',', $checkedfields);
        }
        foreach($checkedfields as $checkedfield)
        {
            if( strpos($checkedfield, 'profile_field_') !== false )
            {
                $mform->setDefault('customuserfields_' . substr($checkedfield, 14), '1');
            } else
            {
                $mform->setDefault('userfields_' . $checkedfield, '1');
            }
        }
        
        // ЧЕКБОКСЫ ПОЛЕЙ ПЕРСОНЫ ДЕКАНАТА
        $dofpersonelements = [];
        // Получение API работы с Деканатом
        $personsapi = report_mods_data_get_dof_persons_api();
        if( ! empty($personsapi) )
        {// API доступно
            
            // Получение полей персоны
            $personfields = $personsapi->get_person_fieldnames();
            
            // Удаление системных полей
            unset($personfields['id']);
            unset($personfields['sortname']);
            unset($personfields['mdluser']);
            unset($personfields['sync2moodle']);
            unset($personfields['addressid']);
            unset($personfields['status']);
            unset($personfields['adddate']);
            unset($personfields['passportaddrid']);
            unset($personfields['birthaddressid']);
            unset($personfields['departmentid']);
            
            if( ! empty($personfields) )
            {// Есть поля персоны
                foreach($personfields as $personfield => $personfieldname)
                {
                    $dofpersonelements[] = $mform->createElement(
                        'checkbox',
                        'dofpersonfields_'.$personfield,
                        $personfieldname
                        );
                }
            }
        }
        
        // Отображение чекбоксов
        if( ! empty($dofpersonelements) )
        {
            // Набор пользовательских полей Деканата
            $mform->addElement(
                'header',
                'dofpersonfields_title',
                get_string('dofpersonfields_title', 'report_mods_data')
                );
            $mform->setExpanded('dofpersonfields_title', false);
        
            foreach( $dofpersonelements as $dofpersonelement )
            {
                $mform->addElement($dofpersonelement);
            }
        }
        
        // Отображение чекбоксов
        if( ! empty($moduleelements) )
        {
            // Набор отчетов модулей
            $mform->addElement(
                'header',
                'modulefields_title',
                get_string('modulefields_title', 'report_mods_data')
            );
            $mform->setExpanded('modulefields_title', false);
            
            $this->add_checkbox_controller(1, '', []);
        
            foreach($moduleelements as $moduleelement)
            {
                $mform->addElement($moduleelement);
            }
            
            // ФОРМАТЫ ЭКСПОРТА ОТЧЕТА
            $mform->addElement(
                'header',
                'submit_title',
                get_string('general_title_submit', 'report_mods_data')
                );
            
            $group = [];
            
            // Отчет по всем пользователям курса
            if( $this->onlycanviewself )
            {
                $report_types['self'] = get_string('type_self', 'report_mods_data');
            } else 
            {
                $report_types['all'] = get_string('type_all', 'report_mods_data');
                $report_types['self'] = get_string('type_self', 'report_mods_data');
            }

            $group[] = $mform->createElement(
                'select',
                'report_type',
                get_string('export_format', 'report_mods_data'),
                $report_types
            );
            
            // Форматы отчета
            $group[] = $mform->createElement(
                'select',
                'export_format',
                get_string('export_format', 'report_mods_data'),
                [
                    'html' => get_string('export_format_html', 'report_mods_data'),
                    'xls' => get_string('export_format_xls', 'report_mods_data'),
                    'pdf' => get_string('export_format_pdf', 'report_mods_data')
                ]
            );
            
            // Кнопка отправки формы
            $group[] = $mform->createElement(
                'submit',
                'export',
                get_string('export_submit', 'report_mods_data')
            );
            $mform->addGroup($group, 'submit', '', [' '], false);
            
            // Свернуть блоки формы по умолчанию
            $mform->setExpanded('submit_title', true);
        } else 
        {
            $mform->closeHeaderBefore('no_modules_for_display');
            $mform->addElement('static', 'no_modules_for_display', '', get_string('no_modules_for_display', 'report_mods_data'));
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
        $errors = parent::validation($data, $files);
        if( isset($data['period']['startdate']) && 
            isset($data['period']['enddate']) && 
            $data['period']['startdate'] > $data['period']['enddate'] )
        {
            $errors['period'] = get_string('invalid_period', 'report_mods_data');
        }

        // Возвращаем ошибки, если они возникли
        return $errors;
    }

    /**
     * Обработчик формы
     */
    public function process()
    {
        if ( $formdata = $this->get_data() )
        {// Форма отправлена и проверена
            if ( isset($formdata->export) && isset($formdata->export_format) )
            {// Объединение в единый документ
                if ( ! $this->onlycanviewself && ! empty($formdata->report_type) && $formdata->report_type == 'self' )
                {
                    $ufiltering['id'][] = $this->userid;
                    $this->add_selection_all($ufiltering);
                }
                if( ! empty($formdata->export_format) )
                {// Формат отчета
                    $this->report->set_format($formdata->export_format);
                }
                $this->report->set_data($formdata);
                return $this->report->get_report();
            }
        }
        return '';
    }
    
    /**
     * Получить список курсов с учетом доступности пользователю для отображения в поле фильтрации по курсам
     * @return array
     */
    private function get_courses_for_filter()
    {
        global $SITE;
        $coursesselect = [];
        $courses = get_courses();
        $context = context_system::instance();
        foreach($courses as $course)
        {
            $context_course = context_course::instance($course->id, IGNORE_MISSING);
            if( ($course->visible == 0 && ! has_capability('moodle/course:viewhiddencourses', $context)) || 
                (! is_enrolled($context_course) && ! has_capability('moodle/course:view', $context)) ||
                $course->id == $SITE->id )
            {
                continue;
            }
            $coursesselect[$course->id] = $course->fullname;
        }
        return $coursesselect;
    }
    
    /**
     * Получить список локальных групп для фильтра
     * @param array $courses массив идентификаторов курсов, из которых нужно получить локальные группы.
     * Если массив идентификаторов курсов пуст, будет возвращен пустой массив.
     * @return array
     */
    private function get_groups_for_filter($courses = []) {
        global $DB;
        $result = $allgroups = [];
        $courses = $DB->get_records_list('course', 'id', $courses, '', 'id, shortname');
        foreach ($courses as $course) {
            if ($allgroups = groups_get_all_groups($course->id)) {
                foreach ($allgroups as $group) {
                    $result[$group->id] = $group->name . ' (' . $course->shortname . ')';
                }
            }
        }
        return $result;
    }
    
    /**
     *
     * Получить список настраиваемых полей пользователей
     * @$withprefix если true, вернет массив с ключами profile_field_[shortname]
     *              если false, вернет массив с ключами [shortname]
     * @return array
     */
    private function get_customfields_list($withprefix = false)
    {
        global $DB;
    
        $customfields = [];
        
        if( is_null($this->dof) )
        {
            return $customfields;
        } else 
        {
            $profilefields = $this->dof->modlib('ama')->user(false)->get_user_custom_fields();
            foreach ($profilefields as $profilefield)
            {
                if( $withprefix )
                {
                    $customfields['profile_field_' . $profilefield->shortname] = $profilefield->name;
                } else 
                {
                    $customfields[$profilefield->shortname] = $profilefield->name;
                }
            }
            return $customfields;
        }
    }
    
    /**
     * Возвращает sql-запрос и набор параметров для получения пользователей по фильтру
     * @example list($sql, $params) = $this->get_sql_filter($ufiltering);
     * @param array $ufiltering массив полей, по которым необходима фильтрация
     * @param array $groups массив идентификаторов локальных групп для отбора пользователей
     * @return [$sql, $params]
     */
    private function get_sql_filter($ufiltering, $groups = [])
    {
        global $DB, $CFG;
        
        $paramsuserfields = $paramscustomfields = $paramsgroups = [];
        $userfieldsselectpart = $customfieldsselectpart = [];
        foreach($ufiltering as $fieldcode => $fieldvalues)
        {// Разберем на кастомные и не кастомные
            if( strpos($fieldcode, 'profile_field_') === 0 )
            {
                $paramscustomfields[] = substr($fieldcode, 14);
                // Кастомное поле
                foreach($fieldvalues as $fieldvalue)
                {
                    $customfieldsor[] = 'uid.data=?';
                    $paramscustomfields[] = $fieldvalue;
                }
                // Подготовим массив select'ов для кастомных полей
                $customfieldsselectpart[] = '(uif.shortname=? AND ' . implode(' OR ', $customfieldsor) . ')';
                
            } else
            {
                $userfieldsor = array();
                // Стандартное поле профиля
                foreach($fieldvalues as $fieldvalue)
                {
                    $userfieldsor[] = 'u.' . $fieldcode . '=?';
                    $paramsuserfields[] = $fieldvalue;
                }
                // Подготовим массив select'ов для стандартных полей
                $userfieldsselectpart[] = implode(' OR ', $userfieldsor);
            }
        }

        // Собираем все в один запрос
        $userfieldsselect = implode(' AND ', $userfieldsselectpart);
        $customfieldsselect = implode(' AND ', $customfieldsselectpart);
        $joincustomfields = $joingroups = '';
        $groupby = '';
        $where = 'WHERE ';
        
        if( ! empty($userfieldsselect) )
        {
            $fieldsselect[] = $userfieldsselect;
        }
        if( ! empty($customfieldsselect) )
        {// Собираем join для кастомных полей
            $fieldsselect[] = $customfieldsselect;
            $joincustomfields = 'LEFT JOIN {user_info_data} uid
                                        ON u.id=uid.userid
                                      JOIN {user_info_field} uif
                                        ON uid.fieldid=uif.id ';
        }
        $fieldsselect[] = 'u.id<>? AND deleted <> 1';
        
        $where .= implode(' AND ', $fieldsselect);
        
        if (!empty($groups)) {
            $joingroups = 'LEFT JOIN {groups_members} gm
                                  ON u.id = gm.userid ';
            list($sqlingroups, $paramsgroups) = $DB->get_in_or_equal($groups);
            $where .= ' AND gm.id IS NOT NULL AND gm.groupid ' . $sqlingroups;
            $groupby = ' GROUP BY u.id';
        }
        
        $sql = 'SELECT u.id,' . $DB->sql_fullname() . ' AS fullname FROM {user} u ' . $joincustomfields . $joingroups . $where . $groupby . ' ORDER BY fullname';
        $params = array_merge($paramsuserfields, $paramscustomfields, [$CFG->siteguest], $paramsgroups);
        
        return [$sql, $params];
    }
    
    /**
     * Добавляет отобранных пользователей в сессию
     * @param array $ufiltering набор полей для фильтрации
     * @param array $groups массив идентификаторов локальных групп для отбора пользователей
     */
    private function add_selection_all($ufiltering, $groups = []) 
    {
        global $SESSION, $DB;
        
        list($sql, $params) = $this->get_sql_filter($ufiltering, $groups);
        
        $rs = $DB->get_recordset_sql($sql, $params);

        unset($SESSION->report_mods_data_bulk_users);
        foreach($rs as $user) 
        {
            $SESSION->report_mods_data_bulk_users[$user->id] = $user->id;
        }
        $rs->close();
    }
    
    /**
     * Возвращает массив с отобранными и доступными для отбора пользователями
     * @param array $ufiltering набор полей для фильтрации
     * @param array $groups массив идентификаторов локальных групп для отбора пользователей
     * @return array
     */
    private function get_selection_data($ufiltering, $groups = []) 
    {
        global $SESSION, $DB, $CFG;
        
        // get the SQL filter
        list($sql, $params) = $this->get_sql_filter($ufiltering, $groups);
        
        $total  = $DB->count_records_select('user', "id<>:exguest AND deleted <> 1", ['exguest'=>$CFG->siteguest]);
        $acount = count($DB->get_records_sql($sql, $params));
        $scount = isset($SESSION->report_mods_data_bulk_users) ? count($SESSION->report_mods_data_bulk_users) : 0;
    
        $userlist = ['acount'=>$acount, 'scount'=>$scount, 'ausers'=>false, 'susers'=>false, 'total'=>$total];
        $userlist['ausers'] = $DB->get_records_sql_menu($sql, $params, 0, MAX_BULK_USERS);
    
        if ($scount) {
            if ($scount < MAX_BULK_USERS) {
                $bulkusers = $SESSION->report_mods_data_bulk_users;
            } else {
                $bulkusers = array_slice($SESSION->report_mods_data_bulk_users, 0, MAX_BULK_USERS, true);
            }
            list($in, $inparams) = $DB->get_in_or_equal($bulkusers);
            $userlist['susers'] = $DB->get_records_select_menu('user', "id $in", $inparams, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname');
        }
    
        return $userlist;
    }
}
