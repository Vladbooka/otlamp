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

class dof_modlib_transmit_strategy_participants_mask_persons extends dof_modlib_transmit_strategy_mask_base
{
    /**
     * Префиксы для полей
     *
     * @var array
     */
    protected static $prefixes = [
        'student'
    ];
    
    /**
     * Филды маски
     *
     * @var array
     */
    protected static $fields_import_persons = [
        'email',
        'fullname',
        'firstname',
        'lastname',
        'middlename',
        'doublepersonfullname',
        'dateofbirth',
        'gender',
        'formatfullname',
        'email_generate',
        'password',
        'passwordformat',
        'extid',
        'department_code',
        'sync2moodle',
        'username',
        'department_code_default',
        'passwordformat_default',
        'sync2moodle_default'
    ];
    
    /**
     * Филды маски на экспорт
     *
     * @var array
     */
    protected static $fields_export_persons = [
        'person_id',
        'email',
        'firstname',
        'lastname',
        'middlename',
        'dateofbirth',
        'gender'
    ];

    /**
     * Конфиг маски
     *
     * @var array
     */
    protected $config = [
        'formatfullname_override' => null,
        'formatfullname' => null,
        'email_override' => null,
        'email' => null,
        'doublepersonfullname_override' => null,
        'doublepersonfullname' => null,
        'passwordformat_override' => null,
        'passwordformat' => null,
        'sync2moodle_override' => null,
        'sync2moodle' => null,
        'department_code_override' => null,
        'department_code' => null,
        
    ];
    
    /**
     * Данные для запуска процесса экспорта
     * 
     * @var array
     */
    private $exportiteractiondata = null;
    
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
        
        // Текущая маска работает только с полями персоны
        foreach ( $maskfields as $fieldcode => &$fieldinfo )
        {
            if ( ( strpos($fieldcode, 'student_') !== 0 || strpos($fieldcode, 'student_contract') === 0 ) && strpos($fieldcode, '/customfield_') !== 0 )
            {// Поле не относится к студенту
                unset($maskfields[$fieldcode]);
            }
        }
        
        return $maskfields;
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
        // Обработка полей для указанных префиксов
        foreach ( $this->prefix as $prefix )
        {
            // Хидер элементов для студента
            $mform->addElement('html', '<b><div class="dof_modlib_transmit_mask_persons_header_'.$prefix.'">' . $this->dof->get_string('mask_persons_header_'.$prefix, 'transmit', null, 'modlib') . '</div></b>');
            
            // Выбор шаблона email
            $selects_email = [
                1 => $this->dof->get_string('default', 'transmit', null, 'modlib'),
                2 => $this->dof->get_string('override', 'transmit', null, 'modlib')
            ];
            $group_email = [];
            $group_email[] = $mform->createElement(
                'select',
                'mask_persons_option_email_'.$prefix,
                '',
                $selects_email
            );
            $group_email[] = $mform->createElement('text', 'mask_persons_email_template_'.$prefix, '', ['maxlength' => 200, 'size' => 32]);
            $mform->addGroup(
                $group_email,
                'mask_persons_group_email_'.$prefix,
                $this->dof->get_string('mask_persons_choose_option_email', 'transmit', null, 'modlib'),
                [' ']
            );
            $mform->setType('mask_persons_group_email_'.$prefix.'[mask_persons_email_template_'.$prefix.']', PARAM_EMAIL);
            $mform->setDefault('mask_persons_group_email_'.$prefix.'[mask_persons_email_template_'.$prefix.']', 'example@example.ru');
            
            // Выбор формата ФИО
            $selects_fullnameformat = [
                1 => $this->dof->get_string('default', 'transmit', null, 'modlib'),
                2 => $this->dof->get_string('override', 'transmit', null, 'modlib')
            ];
            $group_fullname = [];
            $group_fullname[] = $mform->createElement(
                'select',
                'mask_persons_option_fullname_'.$prefix,
                '',
                $selects_fullnameformat
            );
            $group_fullname[] = $mform->createElement('text', 'mask_persons_fullname_format_'.$prefix, '', ['maxlength' => 200, 'size' => 32]);
            $mform->addGroup(
                $group_fullname,
                'mask_persons_group_fullname_format_'.$prefix,
                $this->dof->get_string('mask_persons_choose_option_fullnameformat', 'transmit', null, 'modlib'),
                [' ']
            );
            $mform->setType('mask_persons_group_fullname_format_'.$prefix.'[mask_persons_fullname_format_'.$prefix.']', PARAM_TEXT);
            $mform->setDefault('mask_persons_group_fullname_format_'.$prefix.'[mask_persons_fullname_format_'.$prefix.']', 'LASTNAME FIRSTNAME MIDDLENAME');
        
            // Дублировать по ФИО
            $selects_doublestatus = [
                1 => $this->dof->get_string('default', 'transmit', null, 'modlib'),
                2 => $this->dof->get_string('override', 'transmit', null, 'modlib')
            ];
            $selects_doublepersonfullname = [
                0 => $this->dof->get_string('no', 'transmit', null, 'modlib'),
                1 => $this->dof->get_string('yes', 'transmit', null, 'modlib')
            ];
            $group_doublepersonfullname = [];
            $group_doublepersonfullname[] = $mform->createElement(
                'select',
                'mask_persons_option_doublepersonfullname_override_'.$prefix,
                '',
                $selects_doublestatus
            );
            $group_doublepersonfullname[] = $mform->createElement(
                'select',
                'mask_persons_option_doublepersonfullname_'.$prefix,
                $this->dof->get_string('mask_persons_doublepersonfullname', 'transmit', null, 'modlib'),
                $selects_doublepersonfullname
            );
            $mform->addGroup(
                $group_doublepersonfullname,
                'mask_persons_group_doublepersonfullname_'.$prefix,
                $this->dof->get_string('mask_persons_choose_option_doublepersonfullname', 'transmit', null, 'modlib'),
                [' ']
            );
            $mform->setDefault('mask_persons_group_doublepersonfullname_'.$prefix.'[mask_persons_option_doublepersonfullname_'.$prefix.']', 1);
            
            // Формат пароля
            $selects_passwordformatstatus = [
                1 => $this->dof->get_string('default', 'transmit', null, 'modlib'),
                2 => $this->dof->get_string('override', 'transmit', null, 'modlib')
            ];
            $selects_passwordformat = [
                'clear' => $this->dof->get_string('mask_persons_passwordformat_clear', 'transmit', null, 'modlib'),
                'md5' => $this->dof->get_string('mask_persons_passwordformat_md5', 'transmit', null, 'modlib')
            ];
            $group_passwordformat = [];
            $group_passwordformat[] = $mform->createElement(
                'select',
                'mask_persons_option_passwordformat_override_'.$prefix,
                '',
                $selects_passwordformatstatus
            );
            $group_passwordformat[] = $mform->createElement(
                'select',
                'mask_persons_option_passwordformat_'.$prefix,
                $this->dof->get_string('mask_persons_passwordformat', 'transmit', null, 'modlib'),
                $selects_passwordformat
            );
            $mform->addGroup(
                $group_passwordformat,
                'mask_persons_group_passwordformat_'.$prefix,
                $this->dof->get_string('mask_persons_choose_option_passwordformat', 'transmit', null, 'modlib'),
                [' ']
                );
            $mform->setDefault('mask_persons_group_passwordformat_'.$prefix.'[mask_persons_option_passwordformat_'.$prefix.']', 'md5');
            
            // Синхронизация персоны с пользователем moodle
            $selects_sync2moodlestatus = [
                1 => $this->dof->get_string('default', 'transmit', null, 'modlib'),
                2 => $this->dof->get_string('override', 'transmit', null, 'modlib')
            ];
            $selects_sync2moodle = [
                0 => $this->dof->get_string('no', 'transmit', null, 'modlib'),
                1 => $this->dof->get_string('yes', 'transmit', null, 'modlib')
            ];
            $group_sync2moodle = [];
            $group_sync2moodle[] = $mform->createElement(
                'select',
                'mask_persons_option_sync2moodle_override_'.$prefix,
                '',
                $selects_sync2moodlestatus
            );
            $group_sync2moodle[] = $mform->createElement(
                'select',
                'mask_persons_option_sync2moodle_'.$prefix,
                $this->dof->get_string('mask_persons_sync2moodle', 'transmit', null, 'modlib'),
                $selects_sync2moodle
            );
            $mform->addGroup(
                $group_sync2moodle,
                'mask_persons_group_sync2moodle_'.$prefix,
                $this->dof->get_string('mask_persons_choose_option_sync2moodle', 'transmit', null, 'modlib'),
                [' ']
            );
            $mform->setDefault('mask_persons_group_sync2moodle_'.$prefix.'[mask_persons_option_sync2moodle_'.$prefix.']', 1);
            
            // Подразделение для импорта персон
            $selects_departmentcodestatus = [
                1 => $this->dof->get_string('default', 'transmit', null, 'modlib'),
                2 => $this->dof->get_string('override', 'transmit', null, 'modlib')
            ];
            $departments = $this->dof->storage('departments')->get_departments(0, ['statuses' => ['active', 'plan']]);
            foreach ($departments as $department) {
                $selects_departmentcode[$department->code] = $department->name . ' [' . $department->code . ']';
            }
            asort($selects_departmentcode);
            $group_departmentcode = [];
            $group_departmentcode[] = $mform->createElement(
                'select',
                'mask_persons_option_departmentcode_override_'.$prefix,
                '',
                $selects_departmentcodestatus
            );
            $group_departmentcode[] = $mform->createElement(
                'select',
                'mask_persons_option_departmentcode_'.$prefix,
                $this->dof->get_string('mask_persons_departmentcode', 'transmit', null, 'modlib'),
                $selects_departmentcode
            );
            $mform->addGroup(
                $group_departmentcode,
                'mask_persons_group_departmentcode_'.$prefix,
                $this->dof->get_string('mask_persons_choose_option_departmentcode', 'transmit', null, 'modlib'),
                [' ']
            );
            $defaultid = optional_param('departmentid', 0, PARAM_INT);
            if ($defaultid === 0) {
                $defaultid = $this->dof->storage('departments')->get_default()->id;
            }
            $defaultdepartment = $this->dof->storage('departments')->get($defaultid);
            $mform->setDefault('mask_persons_group_departmentcode_'.$prefix.'[mask_persons_option_departmentcode_'.$prefix.']', $defaultdepartment->code, PARAM_INT);
        }
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
        // Массив ошибок
        $errors = [];
        
        // Обработка полей для указанных префиксов
        foreach ( $this->prefix as $prefix )
        {
            if ( ! isset($data['mask_persons_group_email_'.$prefix]['mask_persons_email_template_'.$prefix]) ||
                    empty($data['mask_persons_group_email_'.$prefix]['mask_persons_email_template_'.$prefix]))
            {
                $errors['mask_persons_group_email_'.$prefix.'[mask_persons_email_template_'.$prefix.']'] = $this->dof->get_string('mask_persons_invalid_email', 'transmit', null, 'modlib');
            }
            if ( ! isset($data['mask_persons_group_fullname_format_'.$prefix]['mask_persons_fullname_format_'.$prefix]) ||
                    empty($data['mask_persons_group_fullname_format_'.$prefix]['mask_persons_fullname_format_'.$prefix]))
            {
                $errors['mask_persons_group_fullname_format_'.$prefix.'[mask_persons_fullname_format_'.$prefix.']'] = $this->dof->get_string('mask_persons_invalid_fullnameformat', 'transmit', null, 'modlib');
            }
        }
        
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
        
        // Обработка полей для указанных префиксов
        foreach ( $this->prefix as $prefix )
        {
            // Email
            if ( $formdata->{'mask_persons_group_email_'.$prefix}['mask_persons_option_email_'.$prefix] == 2 )
            {
                $importoptions[$prefix.'_email_override'] = true;
                $importoptions[$prefix.'_email'] = $formdata->{'mask_persons_group_email_'.$prefix}['mask_persons_email_template_'.$prefix];
            } elseif ( $formdata->{'mask_persons_group_email_'.$prefix}['mask_persons_option_email_'.$prefix] == 1 )
            {
                $importoptions[$prefix.'_email_override'] = false;
                $importoptions[$prefix.'_email'] = $formdata->{'mask_persons_group_email_'.$prefix}['mask_persons_email_template_'.$prefix];
            }
            
            // Формат ФИО
            if ( $formdata->{'mask_persons_group_fullname_format_'.$prefix}['mask_persons_option_fullname_'.$prefix] == 2 )
            {
                $importoptions[$prefix.'_formatfullname_override'] = true;
                $importoptions[$prefix.'_formatfullname'] = $formdata->{'mask_persons_group_fullname_format_'.$prefix}['mask_persons_fullname_format_'.$prefix];
            } elseif ( $formdata->{'mask_persons_group_fullname_format_'.$prefix}['mask_persons_option_fullname_'.$prefix] == 1 )
            {
                $importoptions[$prefix.'_formatfullname_override'] = false;
                $importoptions[$prefix.'_formatfullname'] = $formdata->{'mask_persons_group_fullname_format_'.$prefix}['mask_persons_fullname_format_'.$prefix];
            }
            
            // Дублировать по ФИО
            if ( $formdata->{'mask_persons_group_doublepersonfullname_'.$prefix}['mask_persons_option_doublepersonfullname_override_'.$prefix] == 2 )
            {
                $importoptions[$prefix.'_doublepersonfullname_override'] = true;
                $importoptions[$prefix.'_doublepersonfullname'] = (bool)$formdata->{'mask_persons_group_doublepersonfullname_'.$prefix}['mask_persons_option_doublepersonfullname_'.$prefix];
            } elseif ( $formdata->{'mask_persons_group_doublepersonfullname_'.$prefix}['mask_persons_option_doublepersonfullname_override_'.$prefix] == 1 )
            {
                $importoptions[$prefix.'_doublepersonfullname_override'] = false;
                $importoptions[$prefix.'_doublepersonfullname'] = (bool)$formdata->{'mask_persons_group_doublepersonfullname_'.$prefix}['mask_persons_option_doublepersonfullname_'.$prefix];
            }
            
            // Формат пароля
            if ( $formdata->{'mask_persons_group_passwordformat_'.$prefix}['mask_persons_option_passwordformat_override_'.$prefix] == 2 )
            {
                $importoptions[$prefix.'_passwordformat_override'] = true;
                $importoptions[$prefix.'_passwordformat_default'] = $formdata->{'mask_persons_group_passwordformat_'.$prefix}['mask_persons_option_passwordformat_'.$prefix];
            } elseif ( $formdata->{'mask_persons_group_passwordformat_'.$prefix}['mask_persons_option_passwordformat_override_'.$prefix] == 1 )
            {
                $importoptions[$prefix.'_passwordformat_override'] = false;
                $importoptions[$prefix.'_passwordformat_default'] = $formdata->{'mask_persons_group_passwordformat_'.$prefix}['mask_persons_option_passwordformat_'.$prefix];
            }
            
            // Синхронизация персоны с пользователем moodle
            if ( $formdata->{'mask_persons_group_sync2moodle_'.$prefix}['mask_persons_option_sync2moodle_override_'.$prefix] == 2 )
            {
                $importoptions[$prefix.'_sync2moodle_override'] = true;
                $importoptions[$prefix.'_sync2moodle_default'] = (bool)$formdata->{'mask_persons_group_sync2moodle_'.$prefix}['mask_persons_option_sync2moodle_'.$prefix];
            } elseif ( $formdata->{'mask_persons_group_sync2moodle_'.$prefix}['mask_persons_option_sync2moodle_override_'.$prefix] == 1 )
            {
                $importoptions[$prefix.'_sync2moodle_override'] = false;
                $importoptions[$prefix.'_sync2moodle_default'] = (bool)$formdata->{'mask_persons_group_sync2moodle_'.$prefix}['mask_persons_option_sync2moodle_'.$prefix];
            }
            
            // Подразделение для импорта персон
            if ( $formdata->{'mask_persons_group_departmentcode_'.$prefix}['mask_persons_option_departmentcode_override_'.$prefix] == 2 )
            {
                $importoptions[$prefix.'_departmentcode_override'] = true;
                $importoptions[$prefix.'_department_code_default'] = $formdata->{'mask_persons_group_departmentcode_'.$prefix}['mask_persons_option_departmentcode_'.$prefix];
            } elseif ( $formdata->{'mask_persons_group_departmentcode_'.$prefix}['mask_persons_option_departmentcode_override_'.$prefix] == 1 )
            {
                $importoptions[$prefix.'_departmentcode_override'] = false;
                $importoptions[$prefix.'_department_code_default'] = $formdata->{'mask_persons_group_departmentcode_'.$prefix}['mask_persons_option_departmentcode_'.$prefix];
            }
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
        
        // Обработка полей для указанных префиксов
        foreach ( $this->prefix as $prefix )
        {
            if ( $importoptions[$prefix.'_email_override'] )
            {// Жестко переопределяем поле
                if ( isset($data[$prefix.'_email']) )
                {// Жесткое переопределение, удаляем указанный email из пула
                    unset($data[$prefix.'_email']);
                }
                $data[$prefix.'_email_generate'] = $importoptions[$prefix.'_email'];
            } elseif ( ! isset($data[$prefix.'_email']) ||
                empty($data[$prefix.'_email']) )
            {// Мягко установим поле
                $data[$prefix.'_email_generate'] = $importoptions[$prefix.'_email'];
            }
            
            if ( $importoptions[$prefix.'_formatfullname_override'] )
            {// Жестко переопределяем поле
                $data[$prefix.'_formatfullname'] = $importoptions[$prefix.'_formatfullname'];
            } elseif ( ! isset($data[$prefix.'_formatfullname']) ||
                empty($data[$prefix.'_formatfullname']) )
            {// Мягко установим поле
                $data[$prefix.'_formatfullname'] = $importoptions[$prefix.'_formatfullname'];
            }
            
            if ( $importoptions[$prefix.'_doublepersonfullname_override'] )
            {// Жестко переопределяем поле
                $data[$prefix.'_doublepersonfullname'] = $importoptions[$prefix.'_doublepersonfullname'];
            } elseif ( ! isset($data[$prefix.'_doublepersonfullname']) ||
                empty($data[$prefix.'_doublepersonfullname']) )
            {// Мягко установим поле
                $data[$prefix.'_doublepersonfullname'] = $importoptions[$prefix.'_doublepersonfullname'];
            }
            
            if ($importoptions[$prefix . '_passwordformat_override']) { // Жестко переопределяем поле
                if ( isset($data[$prefix.'_passwordformat']) )
                {// Жесткое переопределение, удаляем указанный код подразделения из пула
                    unset($data[$prefix.'_passwordformat']);
                }
                $data[$prefix . '_passwordformat_default'] = $importoptions[$prefix . '_passwordformat_default'];
            } elseif (! isset($data[$prefix . '_passwordformat']) || empty($data[$prefix . '_passwordformat'])) { // Мягко установим поле
                $data[$prefix . '_passwordformat_default'] = $importoptions[$prefix . '_passwordformat_default'];
            }
            
            if ($importoptions[$prefix . '_sync2moodle_override']) { // Жестко переопределяем поле
                if ( isset($data[$prefix.'sync2moodle']) )
                {// Жесткое переопределение, удаляем указанный код подразделения из пула
                    unset($data[$prefix.'sync2moodle']);
                }
                $data[$prefix . '_sync2moodle_default'] = $importoptions[$prefix . '_sync2moodle_default'];
            } elseif (! isset($data[$prefix . '_sync2moodle']) || empty($data[$prefix . '_sync2moodle'])) { // Мягко установим поле
                $data[$prefix . '_sync2moodle_default'] = $importoptions[$prefix . '_sync2moodle_default'];
            }
            
            if ($importoptions[$prefix . '_departmentcode_override']) { // Жестко переопределяем поле
                if ( isset($data[$prefix.'_department_code']) )
                {// Жесткое переопределение, удаляем указанный код подразделения из пула
                    unset($data[$prefix.'_department_code']);
                }
                $data[$prefix . '_department_code_default'] = $importoptions[$prefix . '_department_code_default'];
            } elseif (! isset($data[$prefix . '_department_code_default']) || empty($data[$prefix . '_department_code_default'])) { // Мягко установим поле
                $data[$prefix . '_department_code_default'] = $importoptions[$prefix . '_department_code_default'];
            }
        }
    }
    
    /**
     * Подготовка процесса экспорта единичного объекта
     *
     * @return array
     */
    protected function transmit_export_init(&$data)
    {
        parent::transmit_export_init($data);
        
        if ( $this->exportiteractiondata === null )
        {// Первичная инициализация
            // Смещение и лимиты экспорта
            $limitfrom = 0;
            $limitnum = 0;
            // Для выгрузки только персоны требуется передать идентификатор
            $this->exportiteractiondata = array_keys($this->dof->storage('persons')->
                get_list_search('', $this->get_configitem('departmentid'), true, $limitfrom, $limitnum));
            reset($this->exportiteractiondata);
        }
        
        $currentpersonid = current($this->exportiteractiondata);
        if ( $currentpersonid === false )
        {
            // Достигнут конец экспортируемого набора
            return;
        }
        
        // Добавление данных в пулл для запуска экспортеров
        $data['student_person_id_valid'] = $currentpersonid;
        
        // Перемещение индекса
        next($this->exportiteractiondata);
    }
}
