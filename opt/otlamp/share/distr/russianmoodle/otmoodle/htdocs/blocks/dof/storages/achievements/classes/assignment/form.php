<?php
use plagiarism_apru\settings_form;

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

// Подключение библиотеки
global $DOF;
require_once($DOF->plugin_path('storage','achievements','/classes/userform.php'));
require_once($DOF->plugin_path('storage','achievements','/classes/settingsform.php'));

/**
 * Форма дополнительных настроек шаблона достижения Simple
 * 
 * @package    storage
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** 
 * Форма создания/редактирования разделов
 */
class dof_storage_assignment_settings_form extends dof_storage_achievement_form
{
    /**
     * {@inheritDoc}
     * @see dof_storage_achievement_form::definition_ext()
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
                'assignment_settings_form_title', 
                $this->dof->get_string('assignment_settings_form_title', 'achievements', null, 'storage')
        );
        
        // Получаем задания
        $assign_instance = $this->dof->modlib('ama')
                ->course(false)
                ->get_instance_object(
                    'assign',
                    false,
                    false
                )
                ->get_manager();
        $data = $assign_instance->get_assignments();
        if( $data )
        {
            $categories[0] = $this->dof->get_string('assignment_settings_form_choice_category', 'achievements', null, 'storage');
            $courses[0][0] = $this->dof->get_string('assignment_settings_form_choice_course', 'achievements', null, 'storage');
            $assignments[0][0][0] = $this->dof->get_string('assignment_settings_form_choice_assignment', 'achievements', null, 'storage');
            foreach($data as $assignmentid => $assignment)
            {
                // Формируем массивы категорий, курсов и заданий для аякс-селекта
                $categories[$assignment->categoryid] = $assignment->categoryname;
                $courses[$assignment->categoryid][$assignment->courseid] = $assignment->coursename;
                $assignments[$assignment->categoryid][$assignment->courseid][$assignment->assignid] = $assignment->assignname;
            }
            // Добавляем аякс-селект для выбора задания
            $sel =& $mform->addElement(
                'dof_hierselect',
                'assignment',
                $this->dof->get_string('assignment_settings_form_choice_assignment', 'achievements', null, 'storage'),
                '',
                '<div class="col-12 px-0"></div>'
            );
            $sel->setOptions([$categories, $courses, $assignments]);
            // Нужно ли учитывать оценку за задание при расчете баллов за достижение
            $mform->addElement(
                'selectyesno',
                'consider',
                $this->dof->get_string('assignment_settings_form_consider', 'achievements', null, 'storage')
            );
            
            // Нужно ли автоматически добавлять в индекс Антиплагиата подтвержденные достижения
            $mform->addElement(
                'selectyesno', 
                'add_to_index', 
                $this->dof->get_string(
                    'assignment_settings_form_add_to_index', 
                    'achievements', 
                    null, 
                    'storage'
                )
            );
            $mform->setType('add_to_index', PARAM_INT);
            
            // Кнопка отправки
            $mform->closeHeaderBefore('submit');
            $mform->addElement(
                'submit',
                'submit',
                $this->dof->get_string('simple_settings_form_save', 'achievements', null, 'storage')
            );
            
            // Применение проверки ко всем элементам
            $mform->applyFilter('__ALL__', 'trim');
        } else
        {// Если не найдено заданий, скажем об этом
            $mform->addElement('static', 'label_no_courses', $this->dof->get_string('assignment_settings_form_no_courses', 'achievements', null, 'storage'));
        }
        
    }

    /**
     * Заполнение формы данными
     */
    protected function definition_after_data_ext(&$mform)
    {
        if ( isset($this->data['simple_data']) && ! empty($this->data['simple_data']) )
        {// Критерии есть
            $mform->setDefault('assignment', [
                                              $this->data['simple_data']['category'],
                                              $this->data['simple_data']['course'], 
                                              $this->data['simple_data']['assignment']
                                             ]
            );
            $lastfields = $this->data['simple_data'];
            unset($lastfields['category'], $lastfields['course'], $lastfields['assignment']);
            if( ! class_exists('\plagiarism_apru\settings_form') || ! settings_form::is_enabled() )
            {
                $mform->setDefault('add_to_index', 0);
                $mform->freeze('add_to_index');
                unset($lastfields['add_to_index']);
            }
            foreach($lastfields as $field => $id)
            {// Подстановка данных
                $mform->setDefault($field, $id);
            }
        }
    }
    
    /** 
     * Обработать пришедшие из формы данные
     *
     * @return array
     */
    protected function process_ext($formdata)
    {
        // Сбор данных о критериях
        $data['simple_data'] = $this->assignment_data($formdata);
        
        return $data;
    }
    
    /**
     * Сформировать массив из данных формы
     * @param array $formdata данные формы
     * @return array
     */
    private function assignment_data($formdata)
    {
        if ( empty($formdata) )
        {
            return '';
        }
        $result = [];
        if ( isset($formdata->assignment[0]) )
        {
            $result['category'] = $formdata->assignment[0];
        }
        if ( isset($formdata->assignment[1]) )
        {
            $result['course'] = $formdata->assignment[1];
        }
        if ( isset($formdata->assignment[2]) )
        {
            $result['assignment'] = $formdata->assignment[2];
        }
        if( isset($formdata->consider) )
        {
            $result['consider'] = $formdata->consider;
        }
        if( isset($formdata->significant) )
        {
            $result['significant'] = $formdata->significant;
        }
        if( isset($formdata->add_to_index) )
        {
            $result['add_to_index'] = $formdata->add_to_index;
        }
        return $result;
    }
}

/**
 * Форма создания/редактирования данных пользователя
 */
class dof_storage_assignment_user_form extends dof_storage_achievementin_form
{
    
    protected function definition_ext(&$mform)
    {

        // Определяем кто работает с формой - пользователь или кто-то другой (например, админ)
        // В зависимости от этого, определим идентификатор пользователя
        if( !empty($this->addvars['personid']) && 
            $this->addvars['personid'] != $this->dof->storage('persons')->get_bu()->id )
        {
            $userid = $this->dof->storage('persons')->get($this->addvars['personid'])->mdluser;
        } else 
        {
            $userid = $this->dof->storage('persons')->get_bu()->mdluser;
        }
        
        // Определим идентификатор достижения по get-параметру
        if( !empty($this->addvars['id']) )
        {
            $achievementinsid = $this->addvars['id'];
        } else 
        {
            $achievementinsid = null;
        }
        // Получим объект достижения пользователя
        $achievementins = $this->dof->storage('achievementins')
            ->get_achievementins( 
                $this->achievement->id,
                $this->dof->storage('persons')->get_bu($userid)->id, 
                ['metastatus' => 'real']
            );
        $assign_instance = $this->dof->modlib('ama')
            ->course($this->data['simple_data']['course'])
            ->get_instance_object(
                'assign',
                $this->data['simple_data']['assignment'],
                $this->data['simple_data']['course']
            )
            ->get_manager();
        $usergrade = $assign_instance->get_user_grades($userid);
        if( is_null($assign_instance) )
        {
            $mform->addElement(
                'header',
                'title',
                $this->dof->get_string('assignment_settings_user_form_title', 'achievements', null, 'storage')
                );
            $mform->addElement(
                'static',
                'label_no_grade',
                $this->dof->get_string('assignment_settings_user_form_assign_deleted', 'achievements', null, 'storage')
                );
        } else {
            if( count($achievementins) < 1 || (count($achievementins) == 1 && $achievementinsid) )
            {// Показываем форму, если мы создаем первое достижение по шаблону или редактируем уже созданное
                if( $achievementins )
                {
                    $achievementins = array_shift($achievementins);
                }
                // Проверяем права на создание и редактирование достижения
                if( ($achievementins &&
                    $this->dof->storage('achievementins')->is_access('edit', $achievementins->id)) ||
                    (!$achievementins &&
                        $this->dof->im('achievements')->is_access('achievement/use', $this->achievement->id)) )
                {// Если с правами все хорошо, проверяем оценено ли задание пользователя
                    if( ! empty($usergrade[$userid]->rawgrade) && ! is_null($usergrade[$userid]->rawgrade) && (int)$usergrade[$userid]->rawgrade != -1 )
                    {// Если оценено, показываем форму сохранения достижения
                        // Скрытые поля
                        
                        $mform->addElement('hidden', 'category', $this->data['simple_data']['category']);
                        $mform->setType('category', PARAM_INT);
                        
                        $mform->addElement('hidden', 'course', $this->data['simple_data']['course']);
                        $mform->setType('course', PARAM_INT);
                        
                        $mform->addElement('hidden', 'assignment', $this->data['simple_data']['assignment']);
                        $mform->setType('assignment', PARAM_INT);
                        
                        if( isset($this->data['simple_data']['significant']) )
                        {
                            $mform->addElement('hidden', 'significant', $this->data['simple_data']['significant']);
                            $mform->setType('significant', PARAM_INT);
                        }
                        
                        if( isset($this->data['simple_data']['add_to_index']) )
                        {
                            $mform->addElement('hidden', 'add_to_index', $this->data['simple_data']['add_to_index']);
                            $mform->setType('add_to_index', PARAM_INT);
                        }
            
                        // Заголовок формы
                        $mform->addElement('header', 'title', $this->dof->get_string('assignment_settings_user_form_title', 'achievements', null, 'storage'));
                        // Название курса
                        $mform->addElement('static',
                            'label_course',
                            $this->dof->get_string('assignment_settings_user_form_course', 'achievements', null, 'storage'),
                            $this->dof->modlib('ama')->course($this->data['simple_data']['course'])->get()->fullname
                            );
                        // Название задания
                        $mform->addElement('static',
                            'label_assign',
                            $this->dof->get_string('assignment_settings_user_form_assign', 'achievements', null, 'storage'),
                            $assign_instance->get_cm()->name
                            );
                        // Оценка за задание в процентах
                        $mform->addElement('static',
                            'label_grade',
                            $this->dof->get_string('assignment_settings_user_form_grade', 'achievements', null, 'storage'),
                            $assign_instance->get_grade_percentage($userid)
                            );
                        $group = [];
                        $group[] = $mform->createElement(
                            'submit',
                            'submitclose',
                            $this->dof->get_string('simple_user_form_save_close', 'achievements', null, 'storage')
                            );
                        $mform->addGroup($group, 'submit', '', '');
                        
                        // Применение проверки ко всем элементам
                        $mform->applyFilter('__ALL__', 'trim');
                    } else
                    {// Если задание не оценено, напишем об этом
                        $mform->addElement(
                            'header',
                            'title',
                            $this->dof->get_string('assignment_settings_user_form_title', 'achievements', null, 'storage')
                            );
                        $mform->addElement(
                            'static',
                            'label_no_grade',
                            $this->dof->get_string('assignment_settings_user_form_no_grade', 'achievements', null, 'storage')
                            );
                    }
                }
            
            } else
            {// Если достижение уже добавлено, напишем об этом
                $mform->addElement(
                    'header',
                    'title',
                    $this->dof->get_string('assignment_settings_user_form_title', 'achievements', null, 'storage')
                );
                $mform->addElement(
                    'static',
                    'label_no_grade',
                    $this->dof->get_string('assignment_settings_user_form_achievement_already_added', 'achievements', null, 'storage')
                );
            }
        }
    }

    /**
     * Проверка данных формы
     *
     * @param array $data - данные, пришедшие из формы
     *
     * @return array - массив ошибок, или пустой массив, если ошибок нет
     */
    protected function validation_ext($data, $files)
    {
        // Массив ошибок
        $errors = [];

        // Возвращаем ошибки, если они есть
        return $errors;
    }

    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    protected function process_ext($formdata)
    {
        $userdata = [];
        $assign_instance = $this->dof->modlib('ama')->course($this->data['simple_data']['course'])
                ->get_instance_object(
                    'assign', 
                    $this->data['simple_data']['assignment'], 
                    $this->data['simple_data']['course']
                )
                ->get_manager();
        $userid = $this->dof->storage('persons')->get_bu()->mdluser;
        if ( isset($this->data['simple_data']) )
        {// Определены критерии
            foreach ( $this->data['simple_data'] as $field => $value )
            {// Передадим данные из шаблона в достижение
                $userdata[$field] = $value;
            }
            if( !empty($this->data['simple_data']['assignment']) && !empty($this->data['simple_data']['course']) )
            {// Получим идентификаторы загруженных в задание файлов (itemid)
                $files = $assign_instance->get_files_by_userid($userid);
                if( !empty($files) )
                {
                    foreach($files as $file)
                    {
                        $userdata['files'][] = $file->get_itemid();
                    }
                }
                // Получим идентификатор отправки задания (для получения текста при отправке в Антиплагиат)
                $submission = $assign_instance->get_submission($userid);
                if( $submission )
                {
                    $userdata['submission'] = $submission->id;
                } else 
                {
                    $userdata['submission'] = 0;
                }
                $userdata['grade'] = (string)round(floatval(str_replace(' %', '', $assign_instance->get_grade_percentage($userid))) / 100, 4);
            }
        }
        return $userdata;
    }
}
?>