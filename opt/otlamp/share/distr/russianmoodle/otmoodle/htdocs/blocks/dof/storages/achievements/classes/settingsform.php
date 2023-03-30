<?php

// //////////////////////////////////////////////////////////////////////////
// //
// NOTICE OF COPYRIGHT //
// //
// Dean`s Office for Moodle //
// Электронный деканат //
// <http://deansoffice.ru/> //
// //
// //
// This program is free software: you can redistribute it and/or modify //
// it under the terms of the GNU General Public License as published by //
// the Free Software Foundation, either version 3 of the Licensen. //
// //
// This program is distributed in the hope that it will be useful, //
// but WITHOUT ANY WARRANTY; without even the implied warranty of //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the //
// GNU General Public License for more details. //
// //
// You should have received a copy of the GNU General Public License //
// along with this program. If not, see <http://www.gnu.org/licenses/>. //
// //
// //////////////////////////////////////////////////////////////////////////

/**
 * Форма редактирования цели / достижения
 *
 * @package storage
 * @subpackage achievements
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class dof_storage_achievement_form extends dof_modlib_widgets_form
{

    /**
     *
     * @var dof_control
     */
    protected $dof;

    /**
     *
     * @var $id - ID раздела
     */
    protected $id = 0;

    /**
     *
     * @var $addvars - GET параметры для ссылки
     */
    protected $addvars = [];

    /**
     * @var stdClass $achievement
     */
    protected $achievement = null;
    
    /**
     * @var $data - Дополнительные настройки
     */
    protected $data = [];
    
    /**
     * @var $notificationdata - Дополнительные настройки уведомлений
     */
    protected $notificationdata = [];

    /**
     * @var bool
     */
    protected $moderationenabled = false;

    /**
     * @var bool
     */
    protected $ratingenabled = false;
    
    
    /**
     * @param $mform MoodleQuickForm
     */
    protected function definition_ext(&$mform)
    {
    }
    
    /**
     * @param $mform MoodleQuickForm
     */
    protected function definition_after_data_ext(&$mform)
    {
    }
    
    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    protected function validation_ext($data, $files)
    {
        return [];
    }
    
    /**
     * @param stdClass $formdata
     * @return array
     */
    protected function process_ext($formdata)
    {
        return [];
    }
    
    
    /**
     * добавление вкладки с уведомлениями
     *
     * @return void
     */
    protected function add_notification_tab()
    {
        $mform = $this->_form;
        
        // заголовок вкладки с уведомлениями для цели
        $mform->addElement(
                'header',
                'form_achievements_edit_goal_notification_header',
                $this->dof->get_string('form_achievements_edit_goal_notification_header', 'achievements', null, 'storage')
                );
        $mform->setExpanded('form_achievements_edit_goal_notification_header', true);
        
        
        // периодично уведомлять о наличи несогласованных или неподтвержденных целей
        $group[] = $mform->createElement(
                'checkbox',
                'form_achievements_edit_goal_notification_stat_periodic_enable',
                $this->dof->get_string('form_achievements_edit_goal_notification_stat_periodic_enable', 'achievements', null, 'storage')
                );
        
        $group[] = $mform->createElement(
                'dof_duration',
                'form_achievements_edit_goal_notification_stat_periodic_days',
                '',
                ['availableunits' => [86400 => $this->dof->modlib('ig')->igs('days')]]
                );
        $mform->addGroup($group, 'group_first', $this->dof->get_string('form_achievements_edit_goal_notification_stat_periodic_enable', 'achievements', null, 'storage'));
        $mform->disabledIf(
                'group_first[form_achievements_edit_goal_notification_stat_periodic_days][number]',
                'group_first[form_achievements_edit_goal_notification_stat_periodic_enable]',
                'notchecked'
                );
        $mform->disabledIf(
                'group_first[form_achievements_edit_goal_notification_stat_periodic_days][timeunit]',
                'group_first[form_achievements_edit_goal_notification_stat_periodic_enable]',
                'notchecked'
                );
        
        // единовременно уведомлять о наличи несогласованных или неподтвержденных целей
        $mform->addElement(
                'advcheckbox',
                'form_achievements_edit_goal_notification_stat_promptly_enable',
                $this->dof->get_string('form_achievements_edit_goal_notification_stat_promptly_enable', 'achievements', null, 'storage')
                );
        
        if ( $this->dof->storage('achievements')->is_goal_add_allowed($this->achievement->scenario) )
        {
            $group = [];
            // предупреждать за N дней до дедлайна, если цель не достигнута
            $group[] = $mform->createElement(
                    'checkbox',
                    'form_achievements_edit_goal_notification_wait_completion_before_deadline_enable',
                    $this->dof->get_string('form_achievements_edit_goal_notification_wait_completion_before_deadline_enable', 'achievements', null, 'storage')
                    );
            
            $group[] = $mform->createElement(
                    'dof_duration',
                    'form_achievements_edit_goal_notification_wait_completion_before_deadline_days',
                    '',
                    ['availableunits' => [86400 => $this->dof->modlib('ig')->igs('days')]]
                    );
            $mform->addGroup($group, 'group_second', $this->dof->get_string('form_achievements_edit_goal_notification_wait_completion_before_deadline_enable', 'achievements', null, 'storage'));
            $mform->disabledIf(
                    'group_second[form_achievements_edit_goal_notification_wait_completion_before_deadline_days][number]',
                    'group_second[form_achievements_edit_goal_notification_wait_completion_before_deadline_enable]',
                    'notchecked'
                    );
            $mform->disabledIf(
                    'group_second[form_achievements_edit_goal_notification_wait_completion_before_deadline_days][timeunit]',
                    'group_second[form_achievements_edit_goal_notification_wait_completion_before_deadline_enable]',
                    'notchecked'
                    );
            
            $group = [];
            // предупреждать куратора за N дней до дедлайна, если цель не достигнута
            $group[] = $mform->createElement(
                    'advcheckbox',
                    'form_achievements_edit_goal_notification_wait_completion_curator_before_deadline_enable',
                    $this->dof->get_string('form_achievements_edit_goal_notification_wait_completion_curator_before_deadline_enable', 'achievements', null, 'storage')
                    );
            
            $group[] = $mform->createElement(
                    'dof_duration',
                    'form_achievements_edit_goal_notification_wait_completion_curator_before_deadline_days',
                    null,
                    ['availableunits' => [86400 => $this->dof->modlib('ig')->igs('days')]]
                    );
            $mform->addGroup($group, 'group_third', $this->dof->get_string('form_achievements_edit_goal_notification_wait_completion_curator_before_deadline_enable', 'achievements', null, 'storage'));
            $mform->disabledIf(
                    'group_third[form_achievements_edit_goal_notification_wait_completion_curator_before_deadline_days][number]',
                    'group_third[form_achievements_edit_goal_notification_wait_completion_curator_before_deadline_enable]',
                    'notchecked'
                    );
            $mform->disabledIf(
                    'group_third[form_achievements_edit_goal_notification_wait_completion_curator_before_deadline_days][timeunit]',
                    'group_third[form_achievements_edit_goal_notification_wait_completion_curator_before_deadline_enable]',
                    'notchecked'
                    );
            
            // предупреждать в день дедлайна, если цель не достигнута
            $mform->addElement(
                    'advcheckbox',
                    'form_achievements_edit_goal_notification_wait_completion_inday_deadline_enable',
                    $this->dof->get_string('form_achievements_edit_goal_notification_wait_completion_inday_deadline_enable', 'achievements', null, 'storage')
                    );
            
            // предупреждать куратора в день дедлайна, если цель не достигнута
            $mform->addElement(
                    'advcheckbox',
                    'form_achievements_edit_goal_notification_wait_completion_curator_inday_deadline_enable',
                    $this->dof->get_string('form_achievements_edit_goal_notification_wait_completion_curator_inday_deadline_enable', 'achievements', null, 'storage')
                    );
            
            $group = [];
            // предупреждать через N дней после дедлайна, если цель не достигнута
            $group[] = $mform->createElement(
                    'advcheckbox',
                    'form_achievements_edit_goal_notification_wait_completion_after_deadline_enable',
                    $this->dof->get_string('form_achievements_edit_goal_notification_wait_completion_after_deadline_enable', 'achievements', null, 'storage')
                    );
            
            $group[] = $mform->createElement(
                    'dof_duration',
                    'form_achievements_edit_goal_notification_wait_completion_after_deadline_days',
                    null,
                    ['availableunits' => [86400 => $this->dof->modlib('ig')->igs('days')]]
                    );
            $mform->addGroup($group, 'group_fourth', $this->dof->get_string('form_achievements_edit_goal_notification_wait_completion_after_deadline_enable', 'achievements', null, 'storage'));
            $mform->disabledIf(
                    'group_fourth[form_achievements_edit_goal_notification_wait_completion_after_deadline_days][number]',
                    'group_fourth[form_achievements_edit_goal_notification_wait_completion_after_deadline_enable]',
                    'notchecked'
                    );
            $mform->disabledIf(
                    'group_fourth[form_achievements_edit_goal_notification_wait_completion_after_deadline_days][timeunit]',
                    'group_fourth[form_achievements_edit_goal_notification_wait_completion_after_deadline_enable]',
                    'notchecked'
                    );
            
            $group = [];
            // предупреждать куратора через N дней после дедлайна, если цель не достигнута
            $group[] = $mform->createElement(
                    'advcheckbox',
                    'form_achievements_edit_goal_notification_wait_completion_curator_after_deadline_enable',
                    $this->dof->get_string('form_achievements_edit_goal_notification_wait_completion_curator_after_deadline_enable', 'achievements', null, 'storage')
                    );
            
            $group[] = $mform->createElement(
                    'dof_duration',
                    'form_achievements_edit_goal_notification_wait_completion_curator_after_deadline_days',
                    null,
                    ['availableunits' => [86400 => $this->dof->modlib('ig')->igs('days')]]
                    );
            $mform->addGroup($group, 'group_five', $this->dof->get_string('form_achievements_edit_goal_notification_wait_completion_curator_after_deadline_enable', 'achievements', null, 'storage'));
            $mform->disabledIf(
                    'group_five[form_achievements_edit_goal_notification_wait_completion_curator_after_deadline_days][number]',
                    'group_five[form_achievements_edit_goal_notification_wait_completion_curator_after_deadline_enable]',
                    'notchecked'
                    );
            $mform->disabledIf(
                    'group_five[form_achievements_edit_goal_notification_wait_completion_curator_after_deadline_days][timeunit]',
                    'group_five[form_achievements_edit_goal_notification_wait_completion_curator_after_deadline_enable]',
                    'notchecked'
                    );
            
            // уведомлять пользователя при одобрении достижения
            $mform->addElement(
                    'advcheckbox',
                    'form_achievements_edit_goal_notification_user_approve',
                    $this->dof->get_string('form_achievements_edit_goal_notification_user_approve', 'achievements', null, 'storage')
                    );
            
            // уведомлять пользователя при одобрении достижения
            $mform->addElement(
                    'advcheckbox',
                    'form_achievements_edit_goal_notification_user_reject',
                    $this->dof->get_string('form_achievements_edit_goal_notification_user_reject', 'achievements', null, 'storage')
                    );
            
        }
        
        $mform->addElement(
                'submit',
                'general_submit',
                $this->dof->get_string('simple_settings_form_save', 'achievements', null, 'storage')
                );
    }

    /**
     * установка настрек уведомления
     *
     * @param stdClass $formdata
     *
     * @return void
     */
    protected function get_notification_data($formdata)
    {
        $result = [];
        
        // периодичные уведомления о неподтвержденных и несогласованных целях
        if ( ! empty($formdata->group_first['form_achievements_edit_goal_notification_stat_periodic_enable']) )
        {
            // периодично
            $result['stat_periodic'] = $formdata->{'group_first[form_achievements_edit_goal_notification_stat_periodic_days]'};
        } else 
        {
            $result['periodic'] = 0;
        }
        
        // единовременные уведомления о неподтвержденных и несогласованных целях
        if ( ! empty($formdata->form_achievements_edit_goal_notification_stat_promptly_enable) )
        {
            $result['stat_promptly'] = 1;
        } else
        {
            $result['stat_promptly'] = 0;
        }
        
        // пользователю до дедлайна
        if ( ! empty($formdata->group_second['form_achievements_edit_goal_notification_wait_completion_before_deadline_enable']) )
        {
            $result['before_user'] = $formdata->{'group_second[form_achievements_edit_goal_notification_wait_completion_before_deadline_days]'};
        } else 
        {
            $result['before_user'] = 0;
        }
        
        // куратору до дедлайна
        if ( ! empty($formdata->group_third['form_achievements_edit_goal_notification_wait_completion_curator_before_deadline_enable']) )
        {
            $result['before_curator'] = $formdata->{'group_third[form_achievements_edit_goal_notification_wait_completion_curator_before_deadline_days]'};
        } else 
        {
            $result['before_curator'] = 0;
        }
        
        // пользователю после дедлайна
        if ( ! empty($formdata->group_fourth['form_achievements_edit_goal_notification_wait_completion_after_deadline_enable']) )
        {
            $result['after_user'] = $formdata->{'group_fourth[form_achievements_edit_goal_notification_wait_completion_after_deadline_days]'};
        } else
        {
            $result['after_user'] = 0;
        }
        
        // куратору после дедлайна
        if ( ! empty($formdata->group_five['form_achievements_edit_goal_notification_wait_completion_curator_after_deadline_enable']) )
        {
            $result['after_curator'] = $formdata->{'group_five[form_achievements_edit_goal_notification_wait_completion_curator_after_deadline_days]'};
        } else
        {
            $result['after_curator'] = 0;
        }
        
        // пользователю в день дедлайна
        if ( ! empty($formdata->form_achievements_edit_goal_notification_wait_completion_inday_deadline_enable) )
        {
            $result['inday_user'] = 1;
        } else
        {
            $result['inday_user'] = 0;
        }
        
        // куратору в день дедлайна
        if ( ! empty($formdata->form_achievements_edit_goal_notification_wait_completion_curator_inday_deadline_enable) )
        {
            $result['inday_curator'] = 1;
        } else
        {
            $result['inday_curator'] = 0;
        }
        
        // пользователю при одобрении
        if ( ! empty($formdata->form_achievements_edit_goal_notification_user_approve) )
        {
            $result['user_approve'] = 1;
        } else
        {
            $result['user_approve'] = 0;
        }
        // пользователю при отклонении
        if ( ! empty($formdata->form_achievements_edit_goal_notification_user_reject) )
        {
            $result['user_reject'] = 1;
        } else
        {
            $result['user_reject'] = 0;
        }
        
        return $result;
    }
    
    /**
     * {@inheritDoc}
     * @see dof_modlib_widgets_form::definition()
     */
    protected function definition()
    {
        $courseids = [];
        // создаем ссылку на HTML_QuickForm
        $mform = & $this->_form;
        
        // добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->id = $this->_customdata->id;
        $this->addvars = $this->_customdata->addvars;
        $achievement = $this->_customdata->achievementclass->get_achievement();
        $this->achievement = $achievement;
        $this->data = unserialize($achievement->data);
        $this->notificationdata = unserialize($achievement->notificationdata);
        
        // доступность подсистем
        $this->moderationenabled = $this->_customdata->achievementclass->moderate_enabled();
        $this->ratingenabled = $this->_customdata->achievementclass->rating_enabled();
        
        // скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'id', $this->id);
        $mform->setType('id', PARAM_INT);
        
        $this->definition_ext($mform);
    }

    /**
     * Проверка данных формы
     *
     * @param array $data
     *            - данные, пришедшие из формы
     *            
     * @return array - массив ошибок, или пустой массив, если ошибок нет
     */
    public function validation($data, $files)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform = & $this->_form;
        
        // Массив ошибок
        $errors = [];
        
        // проверка настроек уведомлений (Если включено, то дней должно быть > 0)
        if ( ! empty($data['group_first']['form_achievements_edit_goal_notification_stat_periodic_enable']) &&
                (empty($data['group_first[form_achievements_edit_goal_notification_stat_periodic_days]']) || $data['group_first[form_achievements_edit_goal_notification_stat_periodic_days]'] < 0) )
        {
            $errors['group_first'] = $this->dof->get_string('form_achievements_edit_goal_validate_put_days', 'achievements', null, 'storage');
        }
        if ( ! empty($data['group_second']['form_achievements_edit_goal_notification_wait_completion_before_deadline_enable']) &&
                (empty($data['group_second[form_achievements_edit_goal_notification_wait_completion_before_deadline_days]']) || $data['group_second[form_achievements_edit_goal_notification_wait_completion_before_deadline_days]'] < 0) )
        {
            $errors['group_second'] = $this->dof->get_string('form_achievements_edit_goal_validate_put_days', 'achievements', null, 'storage');
        }
        if ( ! empty($data['group_third']['form_achievements_edit_goal_notification_wait_completion_curator_before_deadline_enable']) &&
                (empty($data['group_third[form_achievements_edit_goal_notification_wait_completion_curator_before_deadline_days]']) || $data['group_third[form_achievements_edit_goal_notification_wait_completion_curator_before_deadline_days]'] < 0) )
        {
            $errors['group_third'] = $this->dof->get_string('form_achievements_edit_goal_validate_put_days', 'achievements', null, 'storage');
        }
        if ( ! empty($data['group_fourth']['form_achievements_edit_goal_notification_wait_completion_after_deadline_enable']) &&
                (empty($data['group_fourth[form_achievements_edit_goal_notification_wait_completion_after_deadline_days]']) || $data['group_fourth[form_achievements_edit_goal_notification_wait_completion_after_deadline_days]'] < 0) )
        {
            $errors['group_fourth'] = $this->dof->get_string('form_achievements_edit_goal_validate_put_days', 'achievements', null, 'storage');
        }
        if ( ! empty($data['group_five']['form_achievements_edit_goal_notification_wait_completion_curator_after_deadline_enable']) &&
                (empty($data['group_five[form_achievements_edit_goal_notification_wait_completion_curator_after_deadline_days]']) || $data['group_five[form_achievements_edit_goal_notification_wait_completion_curator_after_deadline_days]'] < 0) )
        {
            $errors['group_five'] = $this->dof->get_string('form_achievements_edit_goal_validate_put_days', 'achievements', null, 'storage');
        }
        
        $errors = array_merge($errors, $this->validation_ext($data, $files));
        
        // Возвращаем ошибки, если они есть
        return $errors;
    }

    /**
     * Заполнение формы данными
     */
    public function definition_after_data()
    {
        $this->definition_after_data_ext($this->_form);
        
        // уведомления только для сценариев с включенной возможность добавления цели
        $this->add_notification_tab();
        
        // заполнение данными полей
        if ( ! empty($this->notificationdata) )
        {
            if ( ! empty($this->notificationdata['stat_periodic']) )
            {
                $this->_form->setDefault('group_first[form_achievements_edit_goal_notification_stat_periodic_enable]', 1);
                $this->_form->setDefault('group_first[form_achievements_edit_goal_notification_stat_periodic_days]',
                        $this->notificationdata['stat_periodic']);
            }
            if ( $this->dof->storage('achievements')->is_goal_add_allowed($this->achievement->scenario) )
            {
                if ( ! empty($this->notificationdata['stat_promptly']) )
                {
                    $this->_form->setDefault(
                            'form_achievements_edit_goal_notification_stat_promptly_enable', 1);
                }
                
                if ( ! empty($this->notificationdata['before_user']) )
                {
                    $this->_form->setDefault(
                            'group_second[form_achievements_edit_goal_notification_wait_completion_before_deadline_enable]', 1);
                    $this->_form->setDefault(
                            'group_second[form_achievements_edit_goal_notification_wait_completion_before_deadline_days]',
                            $this->notificationdata['before_user']);
                }
                if ( ! empty($this->notificationdata['before_curator']) )
                {
                    $this->_form->setDefault(
                            'group_third[form_achievements_edit_goal_notification_wait_completion_curator_before_deadline_enable]', 1);
                    $this->_form->setDefault(
                            'group_third[form_achievements_edit_goal_notification_wait_completion_curator_before_deadline_days]',
                            $this->notificationdata['before_curator']);
                }
                
                if ( ! empty($this->notificationdata['inday_user']) )
                {
                    $this->_form->setDefault(
                            'form_achievements_edit_goal_notification_wait_completion_inday_deadline_enable', 1);
                }
                if ( ! empty($this->notificationdata['inday_curator']) )
                {
                    $this->_form->setDefault(
                            'form_achievements_edit_goal_notification_wait_completion_curator_inday_deadline_enable', 1);
                }
                
                if ( ! empty($this->notificationdata['after_user']) )
                {
                    $this->_form->setDefault(
                            'group_fourth[form_achievements_edit_goal_notification_wait_completion_after_deadline_enable]', 1);
                    $this->_form->setDefault(
                            'group_fourth[form_achievements_edit_goal_notification_wait_completion_after_deadline_days]',
                            $this->notificationdata['after_user']);
                }
                if ( ! empty($this->notificationdata['after_curator']) )
                {
                    $this->_form->setDefault(
                            'group_five[form_achievements_edit_goal_notification_wait_completion_curator_after_deadline_enable]', 1);
                    $this->_form->setDefault(
                            'group_five[form_achievements_edit_goal_notification_wait_completion_curator_after_deadline_days]',
                            $this->notificationdata['after_curator']);
                }
                
                if ( ! empty($this->notificationdata['user_approve']) )
                {
                    $this->_form->setDefault(
                            'form_achievements_edit_goal_notification_user_approve', 1);
                }
                if ( ! empty($this->notificationdata['user_reject']) )
                {
                    $this->_form->setDefault(
                            'form_achievements_edit_goal_notification_user_reject', 1);
                }
            }
        }
    }

    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        if ( $formdata = $this->get_data() )
        {
            $data = [];
            // установка данных по уведомлениям
            $data = array_merge($data, $this->process_ext($formdata));
            
            // Готовим объект достижения для сохранения
            $achievement = new stdClass();
            $achievement->id = $formdata->id;
            $achievement->data = serialize($data);
            $achievement->notificationdata = serialize($this->get_notification_data($formdata));
            
            // Получение старого шаблона достижения
            $oldachievement = $this->dof->storage('achievements')->get($formdata->id);
            // Сохранение данных
            $id = $this->dof->storage('achievements')->save($achievement);
            
            if ( $id )
            { // Успешное сохранение
                if ( ! empty($oldachievement) )
                { // Старый шаблон имелся в системе
                  // Проверка изменения данных достижения
                    $olddata = $oldachievement->data;
                    $newdata = $achievement->data;
                    if ( $newdata != $olddata )
                    { // Данные изменились
                        $this->dof->send_event('storage', 'achievements', 'data_update', $achievement->id);
                    }
                }
                $this->addvars['success'] = 1;
                $this->addvars['id'] = $id;
                
                // получение реальных статусов достижений
                $statuses = array_keys($this->dof->workflow('achievementins')->get_meta_list('real'));
                
                // проверим, существуют ли достижения по текущему шаблону
                $achievementins = $this->dof->storage('achievementins')->get_records(['achievementid' => $id, 'status' => $statuses]);
                if ( ! empty($achievementins) )
                {
                    $olddata = unserialize($oldachievement->notificationdata);
                    $newdata = unserialize($achievement->notificationdata);
                    unset(
                            $olddata['stat_periodic'],
                            $newdata['stat_periodic'],
                            $olddata['stat_promptly'],
                            $newdata['stat_promptly'],
                            $olddata['user_approve'],
                            $newdata['user_approve'],
                            $olddata['user_reject'],
                            $newdata['user_reject']
                            );
                    if ( $olddata != $newdata )
                    {
                        // данные по уведомлениям изменились
                        $this->addvars['notoficationdatachanged'] = true;
                    }
                }
                redirect($this->dof->url_im('achievements', '/edit_achievement.php', $this->addvars));
            } else
            { // Ошибки
                $this->addvars['success'] = 0;
                redirect($this->dof->url_im('achievements', '/edit_achievement.php', $this->addvars));
            }
        }
    }
}