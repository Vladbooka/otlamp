<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
//                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

global $DOF;
require_once($DOF->plugin_path('storage','achievements','/classes/userform.php'));
require_once($DOF->plugin_path('storage','achievements','/classes/settingsform.php'));

/**
 * форма настроек шаблона
 * 
 * @package    storage
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_storage_achievement_coursecompletion_settings_form extends dof_storage_achievement_form
{
    /**
     * Доступные курсы
     *
     * @var array
     */
    protected $allowed_courses = [];
    
    /**
     * Доступные категории курсов для выбора пользователем
     *
     * @var array
     */
    protected $userform_allowed_categories = [];
    
    /**
     * Доступные курсы для выбора пользователем
     *
     * @var array
     */
    protected $userform_allowed_courses = [];

    /**
     * @param MoodleQuickForm $mform
     */
    protected function definition_ext(&$mform)
    {
        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'id', $this->id);
        $mform->setType('id', PARAM_INT);
        
        // Заголовок формы
        $mform->addElement(
            'header', 
            'achievement_goal_settings_form_title', 
            $this->dof->get_string('achievement_coursecompletion_settings_form_title', 
                'achievements', null, 'storage'
            )
        );
        
        // Автоматическое вычисление завершения курса
        $mform->addElement(
                'selectyesno',
                'auto_add_achievement',
                $this->dof->get_string('achievement_coursecompletion_settings_form_autocompletion', 'achievements', null, 'storage')
                );
        // если имеется сохраненное значение - устновим
        if( isset($this->data['coursecompletion_data']['auto_add_achievement']) )
        {
            $mform->setDefault('auto_add_achievement', $this->data['coursecompletion_data']['auto_add_achievement']);
        } else
        {
            $mform->setDefault('auto_add_achievement', '1');
        }
        
        // получение спика курсов в системе
        $choices = $this->dof->modlib('ama')->course(false)->get_courses_all();
        foreach ( $choices as $courseinfo )
        {
            $this->allowed_courses[$courseinfo->id] = $courseinfo->fullname;
        }
        $mform->addElement(
                'autocomplete',
                'allowed_courses',
                $this->dof->get_string('achievement_coursecompletion_settings_form_choice_course','achievements', null, 'storage'),
                $this->allowed_courses,
                [
                    'multiple' => 'multiple',
                    'noselectionstring' => $this->dof->get_string('achievement_coursecompletion_settings_form_choice_course_all','achievements', null, 'storage')
                ]
                );
        $this->add_help('allowed_courses', 'achievement_coursecompletion_settings_form_choice_course', 'achievements', 'storage');
        
        // установка выбранных курсов
        if ( isset($this->data['coursecompletion_data']['allowed_courses']) )
        {
            $mform->setDefault('allowed_courses', $this->data['coursecompletion_data']['allowed_courses']);
        }
        
        // Кнопка сохранения
        $mform->closeHeaderBefore('submit');
        $mform->addElement(
            'submit',
            'submit',
            $this->dof->get_string('achievement_coursecompletion_settings_form_save', 'achievements', null, 'storage')
        );
        
        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /**
     * сформировать массив из данных формы
     * 
     * @param array $formdata - данные формы
     * 
     * @return array
     */
    private function coursecompletion_data($formdata)
    {
        if ( empty($formdata) )
        {
            return '';
        }
        
        $result = [];
        if ( isset($formdata->auto_add_achievement) )
        {
            $result['auto_add_achievement'] = $formdata->auto_add_achievement;
        }
        $result['allowed_courses'] = [];
        if ( isset($formdata->allowed_courses) )
        {
            $result['allowed_courses'] = $formdata->allowed_courses;
        }

        return $result;
    }
    
    /** 
     * обработать пришедшие из формы данные
     *
     * @return bool
     */
    protected function process_ext($formdata)
    {
        // Сбор данных о критериях
        $data['coursecompletion_data'] = $this->coursecompletion_data($formdata);
        return $data;
    }
    
}

/**
 * пользовательская форма достижения
 *
 * @package    storage
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_storage_coursecompletion_user_form extends dof_storage_achievementin_form
{
    /**
     * Доступные курсы
     *
     * @var array
     */
    protected $allowed_courses = [];
    
    /**
     * Доступные категории курсов для выбора пользователем
     *
     * @var array
     */
    protected $userform_allowed_categories = [];
    
    /**
     * Доступные курсы для выбора пользователем
     *
     * @var array
     */
    protected $userform_allowed_courses = [];
    
    
    /**
     * {@inheritDoc}
     * @see dof_storage_achievementin_form::definition_ext()
     */
    protected function definition_ext(&$mform)
    {
        if ( ! empty($this->data['coursecompletion_data']['auto_add_achievement']) )
        {
            // нельзя добавлять достижение, если оно автоматически фиксируется и добавляется
            throw new dof_exception('cannot_add_autocompletion_achievement');
        }
        
        // выбор курса
        $coursescatalogue = $this->dof->modlib('ama')
            ->course(false)
            ->get_courses_catalogue_on_transfered_coursesids($this->data['coursecompletion_data']['allowed_courses'], true);
        $categories = $coursescatalogue['categories'];
        $this->userform_allowed_courses = $coursescatalogue['courses'];
        
        $courseelement = $mform->addElement(
                'dof_hierselect',
                'mdlcourse',
                $this->dof->get_string('coursecompletion_user_form_choose_course', 'achievements', null, 'storage'),
                '',
                '<div class="col-12 px-0"></div>'
                );
        $courseelement->setOptions([$categories, $this->userform_allowed_courses]);
        
        if ( ! empty($this->userdata['courseid']) )
        {
            if ( $this->dof->modlib('ama')->course(false)->is_exists($this->userdata['courseid']) )
            {
                // курс существует
                $mdlcourse = $this->dof->modlib('ama')->course($this->userdata['courseid'])->get();
                if ( ! empty($mdlcourse) &&
                        array_key_exists($mdlcourse->category, $this->userform_allowed_courses) &&
                        array_key_exists($mdlcourse->id, $this->userform_allowed_courses[$mdlcourse->category]) )
                {
                    // категория есть в доступном списке
                    // курс есть в доступном списке
                    $mform->setDefault('mdlcourse', [$mdlcourse->category, $mdlcourse->id]);
                }
            }
        }
        
        $group = [];
        $group[] = $mform->createElement(
                'submit',
                'submitclose',
                $this->dof->get_string('coursecompletion_user_form_save_close', 'achievements', null, 'storage')
                );
        $mform->addGroup($group, 'submit', '', '');
    }
    
    /**
     * {@inheritDoc}
     * @see dof_storage_achievementin_form::validation_ext()
     */
    protected function validation_ext($data, $files)
    {
        $errors = [];
        
        if ( empty($data['mdlcourse']) ||
                (! is_array($data['mdlcourse'])) ||
                empty($data['mdlcourse'][0]) ||
                empty($data['mdlcourse'][1]) )
        {
            // не выбран курс
            $errors['mdlcourse'] = $this->dof->get_string('achievement_coursecompletion_userform_error_empty_course', 'achievements', null, 'storage');
        } else
        {
            // проверка, что курс входит в список доступных
            if ( ! array_key_exists($data['mdlcourse'][0], $this->userform_allowed_courses) )
            {
                // выбранный курс не соответствует выбранной категории
                $errors['mdlcourse'] = $this->dof->get_string('achievement_coursecompletion_userform_error_invalid_course', 'achievements', null, 'storage');
            } else
            {
                if ( ! array_key_exists($data['mdlcourse'][1], $this->userform_allowed_courses[$data['mdlcourse'][0]]) )
                {
                    // выбранный курс не соответствует выбранной категории
                    $errors['mdlcourse'] = $this->dof->get_string('achievement_coursecompletion_userform_error_invalid_course', 'achievements', null, 'storage');
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * {@inheritDoc}
     * @see dof_storage_achievementin_form::process_ext()
     */
    protected function process_ext($formdata)
    {
        return [
            'courseid' => $formdata->mdlcourse['1'],
            'coursename' => $this->dof->modlib('ama')->course($formdata->mdlcourse['1'])->get()->fullname
        ];
    }
}
