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
 * Панель управления доступом в СДО
 * 
 * Файл с классами форм
 * 
 * @package    local_authcontrol
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_authcontrol;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/lib/formslib.php');

use moodleform;
use context_course;
use moodle_url;

/**
 * Главная панель управления доступом в СДО
 */
class local_authcontrol_access_panel_courses extends moodleform
{
    /** 
     * @var int - ID курса
     */
    private $courseid;
    
    public function definition()
    {
        global $SITE;     
        
        // Форма
        $mform = &$this->_form;

        // Кастом данные
        if ( isset($this->_customdata->courseid) && ! empty($this->_customdata->courseid) )
        {
            $this->courseid = $this->_customdata->courseid;
        }
        
        // Получим все курсы
        $courses = get_courses('', '', 'c.id,c.visible,c.fullname');
        // Уберем лишний курс
        unset($courses[$SITE->id]);
        $select_courses = [];
        if ( ! empty($courses) )
        {
            foreach ($courses as $course)
            {
                if ( local_authcontrol_can_view_course(context_course::instance($course->id) ) 
                        && ! empty($course->visible) )
                {
                    $select_courses[$course->id] = $course->fullname;
                }
            }
            if ( ! empty($select_courses) ) 
            {
                $mform->addElement('select','select_course', get_string('form_courses_select', 'local_authcontrol'), $select_courses);
                if ( ! empty($this->courseid) )
                {
                    $mform->setDefault('select_course', $this->courseid);
                }
                $mform->addElement('submit', 'submit_course', get_string('form_courses_load_info', 'local_authcontrol'));
            }
        }
        else
        {
            $mform->addElement('html', get_string('form_courses_empty', 'local_authcontrol'));
        }
    }
    
    public function validation($data, $files)
    {
        // Массив ошибок
        $errors = [];
        
        // Провалидируем пришедшие данные
        if ( ! isset($data['select_course']) && empty($data['select_course']) )
        {
            $errors['select_course'] = get_string('form_courses_error_empty', 'local_authcontrol');
        }
        else 
        {
            if ( ! local_authcontrol_can_view_course(context_course::instance($data['select_course'])) )
            {
                $errors['select_course'] = get_string('form_courses_error_capability', 'local_authcontrol');
            }
        }

        return $errors;
    }
    
    public function process()
    {
        if ( $this->is_submitted() 
                && confirm_sesskey() 
                && $this->is_validated() 
                && $formdata = $this->get_data() )
        {
            if ( isset($formdata->select_course) && ! empty($formdata->select_course) )
            {
                redirect(new moodle_url('/local/authcontrol/controlpage.php', ['id' => $formdata->select_course]));
            }
        }
    }
}

/**
 * Панель выбора курса
 */
class local_authcontrol_access_panel_main extends moodleform
{
    /**
     * @var int - ID курса
     */
    private $courseid;
    
    /**
     * @var string - url для редиректа
     */
    private $returnurl;
    
    /**
     * @var array - роли с локальными именами в контекст курса
     */
    private $all_roles;
    
    public function definition()
    {
        // Форма
        $mform = &$this->_form;
        
        // Кастом данные
        if ( isset($this->_customdata->courseid) && ! empty($this->_customdata->courseid) )
        {
            $this->courseid = $this->_customdata->courseid;
        }
        if ( isset($this->_customdata->returnurl) && ! empty($this->_customdata->returnurl) )
        {
            $this->returnurl = $this->_customdata->returnurl;
        }
        // Получим курс и проверим, что он не скрыт
        if ( ! empty($this->courseid) )
        {
            $course = get_course($this->courseid);
            $context = context_course::instance($this->courseid);
            if ( ! empty($course->visible) )
            {
                $students = get_users_by_capability($context, 'local/authcontrol:access_control');
                foreach ($students as $id => $student)
                {
                    if ( ! local_authcontrol_under_controlled($student->id, $context) )
                    {
                        unset($students[$id]);
                    }
                }
                if ( ! empty($students) )
                {
                    if ( $this->create_select_modules() ) 
                    {// Модули курса есть
                        // Получим все роли с локальными изменениями
                        $this->set_all_roles($context);
                        // Печать таблицы
                        $this->create_table_students($students, $context);
                    }
                    else
                    {// Нет модулей курса
                        $mform->addElement('html', get_string('form_main_access_notific_empty_modules', 'local_authcontrol'));
                    }
                }
                else
                {// Нет студентов
                    $mform->addElement('html', get_string('form_main_access_notific_empty_students', 'local_authcontrol'));
                }
            }
            else 
            {
                $mform->addElement('html', get_string('form_main_course_hidden', 'local_authcontrol'));
            }
        }
    }

    public function validation($data, $files)
    {
        // Массив ошибок
        $errors = [];
        if ( ! empty($data['userids']) )
        {
            $check = array_search(1, $data['userids']);
            if ( empty($check) )
            {
                $errors['authcontrolmodule'] = get_string('form_main_access_empty_chose_students', 'local_authcontrol');
            }
        }
        else 
        {
            $errors['authcontrolmodule'] = get_string('form_main_access_empty_chose_students', 'local_authcontrol');
        }
        return $errors;
    }

    public function process()
    {
        if ( $this->is_submitted() 
                && confirm_sesskey() 
                && $this->is_validated() 
                && $formdata = $this->get_data() )
        {
            if ( ! empty($formdata->userids) )
            {
                // Пользователи, над которыми совершаем действие
                $moduleid = null;
                // Отфильтруем пользователей
                $users = [];
                foreach ( $formdata->userids as $id => $status )
                {
                    if ( ! empty($status) )
                    {
                        $users[] = $id;
                    }
                }
                // Действие, которое совершаем (1 - открываем доступ, 0 - закрываем)
                $action = $formdata->select_access;
                // ID модуля курса
                if ( ! empty($formdata->authcontrolcourse) )
                {
                    $moduleid = $formdata->authcontrolmodule;
                }
                // Сохраним
                if ( local_authcontrol_save_access_info($this->courseid, $users, $moduleid, $action) )
                {// Успешно
                    redirect($this->returnurl, get_string('form_main_process_save_success', 'local_authcontrol'));
                }
                else 
                {// Ошибка
                    redirect($this->returnurl, get_string('form_main_process_save_fail', 'local_authcontrol'));
                }
            }
        }
    }
    
    /**
     * Создаем таблицу со студентами
     * @param array $students
     * @return void
     */
    private function create_table_students($students, $context)
    {
        $mform = &$this->_form;
        $can_use = has_capability('local/authcontrol:use', $context);
        $mform->addElement('html', '<div class="local_authcontrol_content"><table class="generaltable boxaligncenter"><thead><tr>');
        if ( $can_use )
        {
            $mform->addElement('html', '<th>');
            $mform->addElement('html', '<div class="local_authcontrol_select_all"><a>+/-</a></div>');
            $mform->addElement('html', '</th>');
        }
        $mform->addElement('html', '
            <th class="local_authcontrol_search_filter local_authcontrol_fio" data-search="fio">' . get_string('fio', 'local_authcontrol') . '</th>
            <th class="local_authcontrol_search_filter local_authcontrol_username" data-search="login">' . get_string('login', 'local_authcontrol') . '</th>
            <th class="local_authcontrol_search_filter local_authcontrol_group" data-search="group">' . get_string('group', 'local_authcontrol') . '</th>
            <th class="local_authcontrol_role">' . get_string('role', 'local_authcontrol') . '</th>
            <th>' . get_string('status', 'local_authcontrol') . '</th>
            <th class="local_authcontrol_context">' . get_string('context', 'local_authcontrol') . '</th>
            <th class="local_authcontrol_course">' . get_string('course', 'local_authcontrol') . '</th>
            <th class="local_authcontrol_module">' . get_string('module', 'local_authcontrol') . '</th>');
        if ( $can_use )
        {
            $mform->addElement('html', '<th class="local_authcontrol_actions">' . get_string('actions', 'local_authcontrol') . '</th>');
        }
        $mform->addElement('html', '</tr></thead><tbody>');
        // Вставляем данные студентов
        foreach ($students as $id => $student)
        {
            if ( ! has_capability('local/authcontrol:use', $context, $id) )
            {
                // Сформируем область доступа для студента
                $groups = $this->get_user_groups($id);
                $info = local_authcontrol_get_context_info($id);
                $mform->addElement('html', '</td>');
                if ( $can_use )
                {
                    $mform->addElement('html', '<td>');
                    $mform->addElement('advcheckbox', 'userids['.$id.']', null, null, ['group' => 1]);
                    $mform->addElement('html', '</td>');
                }
                $mform->addElement('html','
                    <td data-field="fio"><a href="' . new moodle_url('/user/profile.php', ['id' => $id, 'course' => $this->courseid]) . '"> ' 
                                                                                                                . fullname($student) . ' </a></td>
                    <td class="local_authcontrol_username" data-field="login"> ' . $student->username . ' </td>
                    <td class="local_authcontrol_group" data-field="group"> ' . ((!empty($groups)) ? $groups : '') . ' </td>
                    <td class="local_authcontrol_role"> ' . $this->get_user_roles($id, $context) . ' </td>
                    <td class="local_authcontrol_status" data-id="'.$id.'"> 
                                    ' . ((local_authcontrol_user_has_context($id)) 
                                        ? get_string('form_main_access_student_on', 'local_authcontrol')
                                        : get_string('form_main_access_student_off', 'local_authcontrol')) . ' </td>
                    <td class="local_authcontrol_context" data-id="'.$id.'"> 
                                    ' . ( (! empty($info['context']) ) 
                                        ? $info['context']
                                        : '') . ' </td>
                    <td class="local_authcontrol_course" data-id="'.$id.'"> 
                                ' . ( (! empty($info['course']) ) 
                                    ? $info['course']
                                    : '') . ' </td>
                    <td class="local_authcontrol_module" data-id="'.$id.'"> 
                                ' . ( (! empty($info['module']) ) 
                                    ? $info['module']
                                    : '') . ' </td>
                        ');
                if ( $can_use )
                {
                    // Сформируем token для ajax запросов
                    $token = md5(sesskey().$id.$this->courseid);
                    $mform->addElement('html', '<td class="local_authcontrol_actions">');
                    $mform->addElement('html', 
                       '<div class="local_authcontrol_popup" data-id="' . $id . '">
                            <div class="local_authcontrol_popup_click">
                                                ' . get_string('form_main_access_settings', 'local_authcontrol') . '</div>
                            <span class="local_authcontrol_popuptext" id="student_id_' . $id . '"
                                            data-token=' . $token . '
                                            data-student= ' . $id . '
                                            data-course= ' . $this->courseid . '>
                                <div id="reset_password_' . $id . '" class="local_authcontrol_reset_form_hidden" data-student=' . $id . '>
                                    ' . get_string('form_main_enter_password', 'local_authcontrol') . '<br>
                                    <input id="new_password_' . $id . '"class="local_authcontrol_input_password" type="text" value="" />
                                    <div class="local_authcontrol_button_password"> ' . get_string('form_main_button_change', 'local_authcontrol') . ' 
                                </div>
                            </div>
                            <div class="local_authcontrol_change_password_click" data-student= ' . $id . '> 
                                                    ' . get_string('form_main_access_reset_password', 'local_authcontrol') . ' </div>
                            <div class="local_authcontrol_delimiter"></div>
                            <div class="local_authcontrol_reset_sessions_click"> 
                                                ' . get_string('form_main_access_reset_sessions', 'local_authcontrol') . ' 
                            </div>
                            <div class="local_authcontrol_delimiter"></div>
                            <div class="local_authcontrol_close_access"> 
                                                ' . get_string('form_main_access_close_access', 'local_authcontrol') . ' 
                            </div>
                            </span>
                        </div>');
                    $mform->addElement('html', '</td>');
                }
                $mform->addElement('html', '</tr>');
            }
        }
        $mform->addElement('html','</tbody></table></div>');
        if ( $can_use )
        {
            // Подтверждение
            $choices = [1 => get_string('form_main_access_on', 'local_authcontrol'), 0 => get_string('form_main_access_off', 'local_authcontrol')];
            $mform->addElement('select', 'select_access', get_string('form_main_actions', 'local_authcontrol'), $choices);
            $mform->addElement('submit', 'submit_access', get_string('form_main_submit', 'local_authcontrol'));
        }
    }
    
    /**
     * Создаем селект модулей курса
     * @return boolean
     */
    private function create_select_modules()
    {
        $mform = &$this->_form;
        
        // Получим все модули курса и отобразим в селекте
        $course_modules = [];
        
        $course_info = get_fast_modinfo($this->courseid);
        $modules = $course_info->get_instances();
        foreach($modules as $items)
        {// Массив модулей по типам
            foreach($items as $inst)
            {// Массив элементов конкретного типа
                $element_info = $inst->get_course_module_record();
                if ( ! empty($element_info) && ! empty($element_info->visible) )
                {
                    $course_modules[$element_info->id] = $inst->get_formatted_name();
                }
            }
        }
        
        if ( ! empty($course_modules) )
        {
            $context = context_course::instance($this->courseid);
            if ( has_capability('local/authcontrol:use', $context) )
            {
                $mform->addElement('select',
                        'authcontrolcourse',
                        get_string('form_main_access_course_context', 'local_authcontrol'),
                        [0 => get_string('form_main_choice_course', 'local_authcontrol'), 1 => get_string('form_main_choice_module', 'local_authcontrol')]
                        );
                $mform->addElement(
                        'select', 
                        'authcontrolmodule', 
                        get_string('form_main_access_choose_course', 'local_authcontrol'), 
                        $course_modules
                        );
                $mform->disabledIf('authcontrolmodule', 'authcontrolcourse', 1);
                $mform->setDefault('authcontrolcourse', 0);
            }
            // Модули курса есть
            return true;
        }
        
        // Пустые данные
        return false;
    }
    
    /**
     * Вернем группы пользователя в вид строки 
     * для отображения в таблице
     * @param int $userid
     * @return string
     */
    private function get_user_groups($userid = null)
    {
        if ( empty($userid) | ! is_numeric($userid) )
        {
            return '';
        }
        
        $groups_names = [];
        
        if ( ! empty($userid) )
        {
            $groups = groups_get_all_groups($this->courseid, $userid);
            if ( ! empty($groups) )
            {
                foreach ($groups as $id => $group)
                {
                    $groups_names[] = $group->name;
                }
            }
        }
        
        // Вернем строку
        return implode(', ', $groups_names);
    }
    
    /**
     * Вернем роли студента в виде строки для отображения
     * @param int $userid
     * @param context_course $context
     * @return string
     */
    private function get_user_roles($userid = null, $context = null)
    {
        if ( empty($userid) || ! is_numeric($userid) )
        {
            return '';
        }
        if ( empty($context) || ! ($context instanceof context_course) )
        {
            return '';
        }
        
        $string = [];
        $user_roles = get_user_roles($context, $userid);
        if ( ! empty($user_roles) && ! empty($this->all_roles))
        {
            foreach ($user_roles as $role)
            {
                $string[] = $this->all_roles[$role->roleid]->localname;
            }
        }
        
        return implode(', ', $string);
    }
    
    /**
     * Получим все роли с локальными изменениями
     * @param context_course $context
     * @return boolean
     */
    private function set_all_roles($context = null)
    {
        if ( empty($context) || ! ($context instanceof context_course) )
        {
            return false;
        }
        $this->all_roles = role_get_names($context);
        // Успешно
        return true;
    }
}


