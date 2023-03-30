<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://www.deansoffice.ru/>                                           //
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

/**
 * Класс шаблона достижений, позволяющий добавлять результаты заданий Moodle
 * 
 * @package    storage
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
class dof_storage_achievements_assignment extends dof_storage_achievements_base 
{
    /**
     * Возвращает код класса
     * 
     * @return string
     */
    public static function get_classname()
    {
        return 'assignment';
    }
    
    /**
     * Содержит ли класс дополнительные настройки
     *
     * @return bool
     */
    public static function has_additional_settings()
    {
        return true;
    }
    
    /**
     * Поддержка ручного удаления
     *
     * @return bool
     */
    public function manual_delete()
    {
        return false;
    }
    
    /**
     * Поддержка ручного добавления
     *
     * @param int $personid - ID пользователя, для которого проверяем
     *
     * @return array
     */
    public function manual_create($personid)
    {
        // Массив ошибок
        $errors = [];
    
        // Получение пользователя Moodle по ID персоны
        $person = $this->dof->storage('persons')->get($personid);
        if ( isset($person->mdluser) )
        {// Пользователь синхранизирован с персоной
            // Проверка прав доступа
            if ( ! $this->dof->storage('achievementins')->is_access('create', $personid) )
            {// Прав нет
                $errors[] = $this->dof->get_string(
                    'dof_storage_achievements_base_no_access',
                    'achievements',
                    null,
                    'storage'
                    );
            }
        } else
        {// Пользователь не синхранизирован
            $errors[] = $this->dof->get_string(
                'dof_storage_achievements_base_no_access',
                'achievements',
                null,
                'storage'
            );
        }
    
        // Проверка наличия задания
        if ( empty($this->get_achievement()->data) )
        {// Поле data пустое, добавлять достижение нельзя
            $errors[] = $this->dof->get_string(
                'dof_storage_achievements_base_no_data',
                'achievements',
                null,
                'storage'
                );
        }
    
        return $errors;
    }
    
    
    
    /**
     * Создать форму настроек
     *
     * @param string $url - Url перехода
     * @param object $customdata - Опции формы
     * @param array $options - Массив дополнительных опций
     *
     * @return object|null - Массив типов достижений
     */
    public function settingsform($url, $customdata, $options = [])
    {
        $this->dof->modlib('widgets')->webform();
        require_once $this->dof->plugin_path('storage', 'achievements','/classes/assignment/form.php');
        
        if ( empty($customdata) )
        {
            $customdata = new stdClass();
        }
        $customdata->achievementclass = $this;
        $form = new dof_storage_assignment_settings_form($url, $customdata);
        $this->settingsform = $form;
        return $this->settingsform;
    }
    
    /**
     * Создать форму настроек
     *
     * @param string $url - Url перехода
     * @param object $customdata - Опции формы
     * @param array $options - Массив дополнительных опций
     *
     * @return object|null - Массив типов достижений
     */
    public function userform($url, $customdata, $options = [])
    {
        $this->dof->modlib('widgets')->webform();
        
        require_once $this->dof->plugin_path('storage', 'achievements','/classes/assignment/form.php');
        
        if ( empty($customdata) )
        {
            $customdata = new stdClass();
        }
        // Шаблон достижения
        $customdata->achievementclass = $this;
        $form = new dof_storage_assignment_user_form($url, $customdata);
        $this->userform = $form;
        
        return $this->userform;
    }
    
    /**
     * Подтвердить элемент пользовательского достижения
     *
     * @param array $userdata - Данные пользовательского достижения
     *
     * @param array $options - Дополнительные опции, определяющие подтверждающий элемент
     *              ['additionalid'] - Дополнительный параметр INTEGER
     *              ['additionalname'] - Дополнительный параметр STRING
     *              ['additionalid2'] - Дополнительный параметр INTEGER
     *
     * @return $userdata - Обработанные пользовательские достижения
     */
    public function moderate_confirm($userdata, $options = [])
    {
        // Получение ID критерия
        if ( isset($options['additionalid']) && ! is_null($options['additionalid']) )
        {
            $key = 'confirm';
            if ( isset($options['additionalid2']) && ! is_null($options['additionalid2']) )
            {// Сброс подтверждения
                $userdata[$key] = 0;
            } else
            {
                $userdata[$key] = 1;
            }
            return $userdata;
        } else
        {
            return false;
        }
    }
    
    /** 
     * Вычислить баллы по достижению
     *
     * @param array $userdata - Данные пользовательского достижения
     * @param array $options - Дополнительные опции
     * 
     * @return float|bool - Баллы пользователя по достижению или false в случае ошибки
     */
    public function instance_calculate_userpoints($userdata, $options = [])
    {
        // Получение баллов шаблона
        $basicpoints = $this->achievement->points;
        $result = 0;
        
        // Получение критериев шаблона
        $adata = unserialize($this->achievement->data);
        if ( isset($adata['simple_data']) && ! empty($adata['simple_data']) )
        {// Критерии определены
            // Получаем оценку за задание в процентах
            $grade_percentage = $this->dof->modlib('ama')->course($adata['simple_data']['course'])->get_instance_object(
                'assign',
                $adata['simple_data']['assignment'],
                $adata['simple_data']['course']
                )->get_manager()->get_grade_percentage($options['userid']);
            if( (boolean)$adata['simple_data']['consider'] )
            {// Если нужно учитывать оценку при расчете баллов за достижение
                $result = floatval($basicpoints) * floatval(str_replace(' %', '', $grade_percentage)) / 100;
            } else
            {// Если не нужно учитывать оценку при расчете баллов за достижение
                $result = floatval(str_replace(' %', '', $grade_percentage)) / 100;
            }
        }
        return $result;
    }
    
    /**
     * Произвести действия над пользовательским достижением перед сохранением
     *
     * @param object $newinstance - Объект пользовательского достижения, готового к обновлению
     * @param object $oldinstance - Объект пользовательского достижения до обновления
     *
     * @return object|bool $newinstance - Отредактированный объект пользовательского достижения
     *                                    или false в случае ошибки
     */
    public function beforesave_process($newinstance, $oldinstance = NULL)
    {
        return $newinstance;
    }
    
    /**
     * Проверить на необходимость модерации данных пользователя
     *
     * @param array $userdata - Данные пользовательского достижения
     *
     * @return bool - TRUE - Данные не требуют модерации
     *                FALSE - Данные требуют модерации
     *                NULL - Ошибка
     */
    public function is_completely_confirmed($data, $instance)
    {     
        /**
         * @todo на текущий момент модерация не нужна, раскомментировать строки ниже, когда будет нужна
         */
//         if ( empty($data) )
//         {// Данных нет
//             return true;
//         } 
//         if ( ! isset($this->achievement->data) )
//         {// Данные не найдены
//             return null;
//         }
        
//         $achievementdata = unserialize($this->achievement->data);

//         if ( ! isset($achievementdata['simple_data']) )
//         {// Данные не найдены
//             return null;
//         }
//         if( ! empty($achievementdata['simple_data']['significant']) ) 
//         { // Критерий требует модерации
            
//             if( isset($data['confirm']) ) 
//             {
//                 if( empty($data['confirm']) ) 
//                 {
//                     return false;
//                 }
//             } else 
//             { // Критерий не подтвержден
//                 return false;
//             }
//         }
        return true;
    }
    
    /**
     * Получить форматированные данные пользователя
     * 
     * @param array $userdata - Пользовательские данные
     * 
     * @return string
     */
    public function get_formatted_user_data($userdata)
    {
        $table = '';
        $adata = unserialize($this->achievement->data);
        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        
        $instance_assign = $this->dof->modlib('ama')->course($userdata['course'])
        ->get_instance_object(
            'assign', 
            $userdata['assignment'], 
            $userdata['course']
        )->get_manager();
        if( is_null($instance_assign) )
        {
            if( $this->dof->plugin_exists('workflow', 'achievementins') )
            {
                $this->dof->workflow('achievementins')->change($userdata['achievementinsid'], 'notavailable');
            }
            $table = new stdClass;
            $table->tablealign = "center";
            $table->cellpadding = 0;
            $table->cellspacing = 0;
            $table->head = [
                $this->dof->get_string(
                    'dof_storage_achievements_notavailable', 
                    'achievements', 
                    null, 
                    'storage'
                )
            ];
            $table->data[] = [
                $this->dof->get_string(
                    'dof_storage_achievements_assignment_deleted', 
                    'achievements', 
                    null, 
                    'storage'
                )
            ];
            return $table;
        }

        $html = '';
        
        // Получим текст, отправленный в задании
        $text = $instance_assign->get_text_by_submissionid($userdata['submission']);

        if( ! empty($text) )
        {
            $label = dof_html_writer::span(
                $this->dof->get_string('dof_storage_achievements_assignment_text_label', 'achievements', null, 'storage'),
                'btn btn-primary button dof_button'
            );
            $html .= $this->dof->modlib('widgets')->modal($label, $text, $instance_assign->assignrecord->name);
            $html .= '<br/>';
        }
        
        // Получим файлы задания
        $assignfiles = $instance_assign->render_assign_files($userdata['userid']);
        
        $html .= $assignfiles;

        // Получим текст рецензии
        $commentsfeedback = $instance_assign->render_assign_feedback_plugin_feedback($userdata['userid'], 'comments');
        $commentsfeedbackclear = trim(strip_tags($commentsfeedback));
        if( ! empty($commentsfeedbackclear) )
        {
            $html .= '<hr class="asw-feed-sep"/>';
            $label = dof_html_writer::span(
                $this->dof->get_string('dof_storage_achievements_assignment_feedback_label', 'achievements', null, 'storage'),
                'btn btn-primary button dof_button'
                );
            $html .= $this->dof->modlib('widgets')->modal($label, $commentsfeedback, $instance_assign->assignrecord->name);
            $html .= '<br/>';
        }
        
        // Получим файлы рецензии
        $filesfeedback = $instance_assign->render_assign_feedback_plugin_feedback($userdata['userid'], 'file');
        if( strpos($filesfeedback, '<ul>') && empty($commentsfeedbackclear) )
        {
            $html .= '<hr class="asw-feed-sep"/>';
        }
        $html .= $filesfeedback;
        
        if ( isset($adata['simple_data']) )
        {// Определены критерии достижения
            $table = new stdClass;
            $table->tablealign = "center";
            $table->cellpadding = 0;
            $table->cellspacing = 0;
            $table->head = ['Название задания', 'Ответ'];
            $table->data[] = [html_writer::link($instance_assign->get_assign_link(), $instance_assign->assignrecord->name), $html];
            $table->align = [];
            $table->size = [];
            $table->do = [];
            $table->style = [];
            $data = [];
            $actions = [];
            $style = [];
        }
            
        // ДЕЙСТВИЯ
        $criteriaaction = [];
        $key = 0;
        if ( ! empty($adata['simple_data']['significant']) ) 
        { // Требуется подтверждение критерия
          // Ключ для проверки подтверждения критерия
            $userkey = 'confirm';
            if ( isset($userdata[$userkey]) && ! empty($userdata[$userkey]) ) 
            { // Критерий подтвержден
                $do = 'deconfirm';
                $hash = $key;
                $icon = 'moderation_confirm';
                // Добавление действия по снятию подтверждения
                $criteriaaction[] = [
                    'do' => $do,
                    'hash' => $hash,
                    'icon' => $icon
                ];
            } else { // Критерий не подтвержден
                $do = 'confirm';
                $hash = $key;
                $icon = 'moderation_need';
                // Добавление действия по снятию подтверждения
                $criteriaaction[] = [
                    'do' => $do,
                    'hash' => $hash,
                    'icon' => $icon
                ];
            }
            $actions[$key] = $criteriaaction;
        }
        $table->do[0] = $actions;
        return $table;
    }     
    
    /**
     * Выполнить действия перед подтверждением достижения
     *
     * @param array $userdata - Пользовательские данные
     *
     * @return void
     */
    public function before_completely_confirmed_process($userdata)
    {
        if ( empty($userdata) )
        {// Данных нет
            return;
        } 
        
        if ( ! isset($this->achievement->data) )
        {// Данные не найдены
            return;
        }
        $achievementdata = unserialize($this->achievement->data);
        if ( ! isset($achievementdata['simple_data']) )
        {// Данные не найдены
            return;
        }
        
        if ( empty($userdata['files']) && !(boolean)$userdata['submission'] )
        {// Файлы и текст не найдены
            return;
        }

        $pathnamehashes = [];
        $assign_instance = $this->dof->modlib('ama')->course($userdata['course'])
        ->get_instance_object(
            'assign',
            $userdata['assignment'],
            $userdata['course']
        )
        ->get_manager();
        $person = $this->dof->storage('persons')->get($userdata['userid']);
        if( empty($person->mdluser) )
        {
            return;
        } else 
        {
            $user = $this->dof->modlib('ama')->user($person->mdluser)->get();
        }
        $submission = $assign_instance->get_submission($user->id);
        if( empty($submission->id) )
        {
            return;
        }
        
        if( (! empty($achievementdata['simple_data']['significant']) && ! empty($userdata['confirm'])) || empty($achievementdata['simple_data']['significant']) )
        {
            if( ((boolean)$userdata['submission'] ||
                ! empty($userdata['files'])) &&
                ! empty($achievementdata['simple_data']['add_to_index']) )
            {
                // Получим идентификатор отправки задания (для получения текста при отправке в Антиплагиат)
                $this->dof->sync('achievements')->process_add_to_apru_index($assign_instance, $user->id, $submission);
            }
        }
        return;
    }
}