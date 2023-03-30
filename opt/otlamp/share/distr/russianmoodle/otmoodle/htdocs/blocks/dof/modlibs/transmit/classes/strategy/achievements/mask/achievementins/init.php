<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//
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
 * Обмен данных с внешними источниками. Класс маски персон
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_strategy_achievements_mask_achievementins extends dof_modlib_transmit_strategy_mask_base
{
    /**
     * Поля данных, с которыми работает маска
     *
     * @var array
     */
    protected static $fields_import_achievementins = [
        'person_id',
        'person_email',
        'achievement_id',
        'achievement_update_exists',
    ];
    
    /**
     * Конфигурация маски
     *
     * @var array
     */
    protected $config_achievementins = [
        'achievement_id_override' => false,
        'achievement_id' => null,
        'achievement_update_exists_override' => false,
        'achievement_update_exists' => null
    ];
    
    /**
     * Доступные достижения
     *
     * @var array
     */
    protected $achievements = [];
    
    /**
     * Поддержка импорта
     *
     * @return bool
     */
    public static function support_import()
    {
        return true;
    }
    
    /**
     * Получить список полей импорта, с которыми работает текущая маска
     *
     * @return array
     */
    public function get_importfields()
    {
        // Получение полного набора полей стратегии
        $strategy = $this->strategy;
        $maskfields = (array)$strategy::$importfields;
        
        return $maskfields;
    }
    
    /**
     * Изменение формы настройки импорта
     *
     * @param dof_modlib_transmit_configurator_configform_base $form - Форма настройки обмена данными
     * @param MoodleQuickForm $mform
     *
     * @return void
     */
    protected function configform_definition_import_prepared(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform)
    {
        // Действие над полем ID шаблона
        $action = [
            1 => $this->dof->get_string('default', 'transmit', null, 'modlib'),
            2 => $this->dof->get_string('override', 'transmit', null, 'modlib')
        ];
        $this->achievements = $this->dof->modlib('achievements')->
            get_manager('achievements')->get_available_by_person();
        foreach ( $this->achievements as &$achievement )
        {
            $achievement = $this->dof->storage('achievements')->get_shortname($achievement);
        }
        $achievement_id_group[] = $mform->createElement(
            'select',
            'achievement_id',
            '',
            $action
        );
        $achievement_id_group[] = $mform->createElement(
            'select', 
            'achievement_id_option', 
            '',
            $this->achievements
        );
        $mform->addGroup(
                $achievement_id_group,
            'achievement_id_group',
            $this->dof->get_string('strategy_achievements_mask_achievementins_achievementid_label', 'transmit', null, 'modlib'),
            [' ']
        );
        
        // Обновлять достижение пользователя, если оно есть у пользователя
        $select_update_exists = [
                0 => $this->dof->get_string('no', 'transmit', null, 'modlib'),
                1 => $this->dof->get_string('yes', 'transmit', null, 'modlib')
        ];
        $achievement_update_exists_group[] = $mform->createElement(
                'select',
                'achievement_update_exists',
                '',
                $action
                );
        $achievement_update_exists_group[] = $mform->createElement(
                'select',
                'achievement_update_exists_option',
                '',
                $select_update_exists
                );
        $mform->addGroup(
                $achievement_update_exists_group,
                'achievement_update_exists_group',
                $this->dof->get_string('strategy_achievements_mask_achievementins_update_exists', 'transmit', null, 'modlib'),
                [' ']
                );
    }
    
    /**
     * Заполнить форму данными (ИМПОРТ)
     * 
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     *
     * @return void
     */
    protected function configform_definition_after_data_import_prepared(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform)
    {
        // Если GET параметром указан ID достижения, установка в селекте
        $achievement_id = optional_param('id', 0, PARAM_NUMBER);
        if ( ! empty($achievement_id) &&
                isset($this->achievements[$achievement_id]) )
        {
            $mform->setDefault('achievement_id_group[achievement_id_option]', $achievement_id);
        }
    }
    
    /**
     * Валидация формы (ИМПОРТ)
     *
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     * @param array $data
     * @param array $files
     *
     * @return void
     */
    protected function configform_validation_import_prepared(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform, $data, $files)
    {
        // Массив ошибок
        $errors = [];
        
        return $errors;
    }
    
    /**
     * Установка конфига
     *
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     * @param stdClass $formdata
     *
     * @return void
     */
    protected function configform_setupconfig_import_prepared(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform, $formdata)
    {
        $importoptions = $this->get_configitem('importoptions');
        
        if ( $formdata->{'achievement_id_group'}['achievement_id'] == 2 )
        {
            $importoptions['achievement_id_override'] = true;
            $importoptions['achievement_id'] = $formdata->{'achievement_id_group'}['achievement_id_option'];
        } elseif ( $formdata->{'achievement_id_group'}['achievement_id'] == 1 )
        {
            $importoptions['achievement_id_override'] = false;
            $importoptions['achievement_id'] = $formdata->{'achievement_id_group'}['achievement_id_option'];
        }
        if ( $formdata->{'achievement_update_exists_group'}['achievement_update_exists'] == 2 )
        {
            $importoptions['achievement_update_exists_override'] = true;
            $importoptions['achievement_update_exists'] = (bool)$formdata->{'achievement_update_exists_group'}['achievement_update_exists_option'];
        } elseif ( $formdata->{'achievement_update_exists_group'}['achievement_update_exists'] == 1 )
        {
            $importoptions['achievement_update_exists_override'] = false;
            $importoptions['achievement_update_exists'] = (bool)$formdata->{'achievement_update_exists_group'}['achievement_update_exists_option'];
        }
        
        $this->set_configitem('importoptions', $importoptions);
    }
    
    /**
     * Фильтрация процесса импорта
     *
     * Метод для фильтрации пулла данных
     *
     * @return void
     */
    protected function transmit_import_filter(&$data)
    {
        // Базовый сбор полей
        parent::transmit_import_filter($data);
        
        $importoptions = $this->get_configitem('importoptions');
        
        if ( $importoptions['achievement_id_override'] )
        {// Жесткое переопределение
            $data['achievement_id'] = $importoptions['achievement_id'];
        } elseif ( ! isset($data['achievement_id']) ||
            empty($data['achievement_id']) )
        {// Мягкое переопределение
            $data['achievement_id'] = $importoptions['achievement_id'];
        }
        if ( $importoptions['achievement_update_exists_override'] )
        {// Жесткое переопределение
            $data['achievement_update_exists'] = $importoptions['achievement_update_exists'];
        } elseif ( ! isset($data['achievement_update_exists']) ||
            empty($data['achievement_update_exists']) )
        {// Мягкое переопределение
            $data['achievement_update_exists'] = $importoptions['achievement_update_exists'];
        }
    }
}
