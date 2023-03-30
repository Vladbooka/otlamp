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
 * Обмен данных с внешними источниками. Класс маски сотрудников
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_modlib_transmit_strategy_participants_mask_appointments extends dof_modlib_transmit_strategy_mask_base
{
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
     * Поддержка экспорта
     *
     * @return bool
     */
    public static function support_export()
    {
        return false;
    }
    
    /**
     * Получить список полей импорта, с которыми работает текущая маска
     *
     * @return array
     */
    public function get_importfields()
    {
        $strategy = $this->strategy;
        $maskfields = (array)$strategy::$importfields;
        
        $processedfields['user_email'] = $maskfields['user_email'];
        $processedfields['user_country'] = $maskfields['user_country'];
        $processedfields['user_region'] = $maskfields['user_region'];
        $processedfields['user_city'] = $maskfields['user_city'];
        $processedfields['user_manager_email'] = $maskfields['user_manager_email'];
        $processedfields['user_manager_idnumber'] = $maskfields['user_manager_idnumber'];
        $processedfields['department_id'] = $maskfields['department_id'];
        $processedfields['department_code'] = $maskfields['department_code'];
        $processedfields['department_name'] = $maskfields['department_name'];
        $processedfields['position_code'] = $maskfields['position_code'];
        $processedfields['position_name'] = $maskfields['position_name'];
        $processedfields['schposition_generate'] = $maskfields['schposition_generate'];
        
        return $processedfields;
    }
    
    /**
     * Заполнить форму дополнительными настройками маски (ИМПОРТ)
     *
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     *
     * @return void
     */
    protected function configform_definition_import_prepared(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform)
    {
        // флаг создания вакансии
        $options = [
            0 => $this->dof->get_string('none', 'transmit', null, 'modlib'),
            1 => $this->dof->get_string('default', 'transmit', null, 'modlib'),
            2 => $this->dof->get_string('override', 'transmit', null, 'modlib')
        ];
        $gen = [
            1 => $this->dof->get_string('yes', 'transmit', null, 'modlib'),
            0 => $this->dof->get_string('no', 'transmit', null, 'modlib')
        ];
        $generateschposition = [];
        $generateschposition[] = $mform->createElement(
                'select',
                'schposition_generate',
                '',
                $options
                );
        $generateschposition[] = $mform->createElement(
                'select',
                'schposition_generate_option',
                '',
                $gen
                );
        $mform->addGroup(
                $generateschposition,
                'schposition_generate_group',
                $this->dof->get_string('mask_appointments_group_schposition_generate', 'transmit', null, 'modlib'),
                [' ']
                );
        $mform->disabledIf(
                'schposition_generate_group[schposition_generate_option]', 
                'schposition_generate_group[schposition_generate]',
                'eq',
                0);
        
        // название должности
        $options = [
            1 => $this->dof->get_string('default', 'transmit', null, 'modlib'),
            2 => $this->dof->get_string('override', 'transmit', null, 'modlib')
        ];
        $positionname = [];
        $positionname[] = $mform->createElement(
                'select',
                'position_name',
                '',
                $options
                );
        $positionname[] = $mform->createElement(
                'text',
                'position_name_value',
                '',
                $gen
                );
        $mform->addGroup(
                $positionname,
                'position_name_group',
                $this->dof->get_string('mask_appointments_group_position_name', 'transmit', null, 'modlib'),
                [' ']
                );
        $mform->setType('position_name_group[position_name_value]', PARAM_TEXT);
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
        return [];
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
        $options = [];
        
        // генерация вакансии
        if ( $formdata->schposition_generate_group['schposition_generate'] == 2 )
        {
            $options['schposition_generate_override'] = true;
            $options['schposition_generate'] = (bool)$formdata->schposition_generate_group['schposition_generate_option'];
        } elseif ( $formdata->schposition_generate_group['schposition_generate'] == 1 )
        {
            $options['schposition_generate_override'] = false;
            $options['schposition_generate'] = (bool)$formdata->schposition_generate_group['schposition_generate_option'];
        }
        
        // название должности
        if ( $formdata->position_name_group['position_name'] == 2 )
        {
            $options['position_name_override'] = true;
            $options['position_name'] = strip_tags($formdata->position_name_group['position_name_value']);
        } elseif ( $formdata->position_name_group['position_name'] == 1 )
        {
            $options['position_name_override'] = false;
            $options['position_name'] = strip_tags($formdata->position_name_group['position_name_value']);
        }
        
        $this->set_configitem('importoptions', $options);
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
        
        $options = $this->get_configitem('importoptions');
        
        if ( array_key_exists('schposition_generate_override', $options) && array_key_exists('schposition_generate_override', $options) )
        {
            if ( $options['schposition_generate_override'] )
            {// Жестко переопределяем поле
                if ( isset($data['schposition_generate']) )
                {// Жесткое переопределение, удаляем указанный email из пула
                    unset($data['schposition_generate']);
                }
                $data['schposition_generate'] = $options['schposition_generate'];
            } elseif ( ! isset($data['schposition_generate']) ||
                    empty($data['schposition_generate']) )
            {// Мягко установим поле
                $data['schposition_generate'] = $options['schposition_generate'];
            }
        }
        
        if ( array_key_exists('position_name_override', $options) && array_key_exists('position_name', $options) )
        {
            if ( $options['position_name_override'] )
            {// Жестко переопределяем поле
                if ( isset($data['position_name']) )
                {// Жесткое переопределение, удаляем указанный email из пула
                    unset($data['position_name']);
                }
                $data['position_name'] = $options['position_name'];
            } elseif ( ! isset($data['position_name']) ||
                    empty($data['position_name']) )
            {// Мягко установим поле
                $data['position_name'] = $options['position_name'];
            }
        }
    }
}