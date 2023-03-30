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
 * Обмен данных с внешними источниками. Класс работы с паком
 *
 * @package    modlib
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_pack
{
    /**
     * Контроллер Деканата
     *
     * @var dof_control
     */
    protected $dof = null;
    
    /**
     * Идентификатор пака
     * 
     * @var integer
     */
    protected $id = 0;
    
    /**
     * Тип синхронизации
     *
     * @var string
     */
    protected $type = null;
    
    /**
     * Код маски
     *
     * @var string
     */
    protected $mask = null;
    
    /**
     * Конфиг маски
     *
     * @var array
     */
    protected $config_mask = null;
    
    /**
     * Код источника
     *
     * @var string
     */
    protected $source = null;
    
    /**
     * Конфиг источника
     *
     * @var array
     */
    protected $config_source = null;
    
    /**
     * Название пакета настроек
     *
     * @var string
     */
    protected $name = '';
    
    /**
     * Описание пакета настроек
     *
     * @var string
     */
    protected $description = '';
    
    /**
     * Статус пакета настроек
     *
     * @var string
     */
    protected $status = '';
    
    /**
     * Статусы, доступные на смену текущему
     *
     * @var array
     */
    protected $available_statuses = [];

    /**
     * Конструктор
     *
     * @param dof_control $dof
     *
     * @return void
     */
    public function __construct(dof_control $dof, $record=null)
    {
        $this->dof = $dof;
        if (!is_null($record))
        {
            $this->parse_record($record);
        }
    }
    
    /**
     * Установка идентификатора
     *
     * @param int $id
     *
     * @return void
     */
    public function set_id($id)
    {
        $this->id = $id;
    }
    
    /**
     * Установка маски
     *
     * @param string $code
     *
     * @return void
     */
    public function set_mask($code)
    {
        $this->mask = $code;
    }
    
    /**
     * Установка источника
     *
     * @param string $code
     *
     * @return void
     */
    public function set_source($code)
    {
        $this->source = $code;
    }
    
    /**
     * Установка конфига маски
     *
     * @param array $config
     *
     * @return void
     */
    public function set_mask_config(array $config)
    {
        $this->config_mask = $config;
    }
    
    /**
     * Установка конфига источника
     *
     * @param array $config
     *
     * @return void
     */
    public function set_source_config($config)
    {
        $this->config_source = $config;
    }
    
    /**
     * Установка типа синхронизации
     *
     * @param string $type
     *
     * @return void
     */
    public function set_transmit_type($type)
    {
        $this->type = $type;
    }
    
    /**
     * Установка наименования пакета настроек
     *
     * @param string $name
     *
     * @return void
     */
    public function set_name($name)
    {
        $this->name = $name;
    }
    
    /**
     * Установка описания пакета настроек
     *
     * @param string $description
     *
     * @return void
     */
    public function set_description($description)
    {
        $this->description = $description;
    }
    
    /**
     * Установка статуса пакета настроек
     *
     * @param string $status
     *
     * @return void
     */
    public function set_status($status)
    {
        $this->status = $status;
        $this->available_statuses = $this->dof->workflow('transmitpacks')->get_available($this->get_id());
    }
    
    /**
     * Получение идентификатора
     * 
     * @return number
     */
    public function get_id()
    {
        return $this->id;
    }
    
    /**
     * Код маски
     *
     * @return string
     */
    public function get_mask_code()
    {
        return $this->mask;
    }
    
    /**
     * Конфиг маски
     *
     * @return string
     */
    public function get_mask_config()
    {
        return $this->config_mask;
    }
    
    /**
     * Код источника
     *
     * @return string
     */
    public function get_source_code()
    {
        return $this->source;
    }
    
    /**
     * Конфиг источника
     *
     * @return string
     */
    public function get_source_config()
    {
        return $this->config_source;
    }
    
    /**
     * Тип синхронизации
     *
     * @return string
     */
    public function get_transmit_type()
    {
        return $this->type;
    }
    
    /**
     * Наименование пакета настроек
     *
     * @return string
     */
    public function get_name()
    {
        return $this->name;
    }
    
    /**
     * Описание пакета настроек
     *
     * @return string
     */
    public function get_description()
    {
        return $this->description;
    }
    
    /**
     * Статуса пакета настроек
     *
     * @return string 
     */
    public function get_status()
    {
        return $this->status;
    }
    
    /**
     * Получени статуса активности пакета
     * 
     * @return boolean
     */
    public function is_active()
    {
        $activestatuses = $this->dof->workflow('transmitpacks')->get_meta_list('active');
        return array_key_exists($this->get_status(), $activestatuses);
    }
    
    /**
     * Проверка доступности статуса
     * 
     * @param []string $status
     * @return boolean
     */
    public function is_available_status($status)
    {
        return array_key_exists($status, $this->available_statuses);
    }
    
    /**
     * Заполнить форму данными
     *
     * @param dof_modlib_transmit_configurator_configform_base $form - Форма настройки
     * @param MoodleQuickForm $mform - Контроллер формы
     *
     * @return void
     */
    public function configform_definition_after_data_import(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform)
    {
        $mask = $form->get_mask();
        $source = $form->get_source();
        
        if (!is_null($mask) && !is_null($source))
        {
            // Заголовок
            $mform->addElement('header', 'header_pack_settings', 'Опции сохранения');
            $mform->setExpanded('header_pack_settings', true);
            
            // Наименование пакета
            $mform->addElement('text', 'pack_name', 'Наименование пакета');
            
            // Описание пакета
            $mform->addElement('textarea', 'pack_description', 'Описание пакета');
        }
    }
    
    /**
     * Установка данных полей пакета
     * 
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     * @param stdClass $formdata
     */
    public function configform_setupconfig_import(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform, $formdata)
    {
        $mask = $form->get_mask();
        $source = $form->get_source();
        
        if (!empty($formdata->pack_name) && !is_null($mask) && !is_null($source))
        {
            $packfakerecord = new stdClass();
            
            $packfakerecord->name = $formdata->pack_name;
            
            $packfakerecord->description = '';
            if (!empty($formdata->packdescription))
            {
                $packfakerecord->description = $formdata->pack_description;
            }
            
            $packfakerecord->config = json_encode([
                'transmittype' => $form->get_transmit_type(),
                'maskcode' => $mask->get_fullcode(),
                'maskconfig' => $mask->get_configitems(),
                'sourcecode' => $source->get_code(),
                'sourceconfig' => $source->get_configitems()
            ]);
            
            $this->parse_record($packfakerecord);
        }
    }
    
    /**
     * Парсинг простого объекта в класс пакета
     * 
     * @param stdClass $record
     * 
     * @throws dof_exception_coding
     */
    protected function parse_record($record)
    {
        if (!isset($record->name) || !isset($record->config))
        {
            throw new dof_exception_coding('missed_required_fields');
        }
        $config = json_decode($record->config, true);
        if (is_null($config))
        {
            throw new dof_exception_coding('wrong_format');
        }
        
        if (trim($record->name)=='' || empty($config['transmittype']) ||
            empty($config['maskcode']) || !isset($config['maskconfig']) || !is_array($config['maskconfig']) ||
            empty($config['sourcecode']) || !isset($config['sourceconfig']) || !is_array($config['sourceconfig']))
        {
            throw new dof_exception_coding('empty_required_field');
        }
        
        if (isset($record->id))
        {
            // Установка идентификатора
            $this->set_id($record->id);
        }
        
        // Установка наименования
        $this->set_name($record->name);
        
        // Установка описания
        $this->set_description($record->description);
        
        if (isset($record->status))
        {
            // Установка текущего статуса
            $this->set_status($record->status);
        }
        
        // Установка типа синхронизации
        $this->set_transmit_type($config['transmittype']);
        
        // Установка кода маски
        $this->set_mask($config['maskcode']);
        
        // Установка конфига маски
        $this->set_mask_config($config['maskconfig']);
        
        // Установка кода источника
        $this->set_source($config['sourcecode']);
        
        // Установка конфига источника
        $this->set_source_config($config['sourceconfig']);
    }
    
    /**
     * Валидация полей пакета
     * 
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     * @param array $data
     * @param array $files
     * 
     * @return string[]|mixed[]
     */
    public function configform_validation_import(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform, $data, $files)
    {
        $errors = [];
        if (isset($data['pack_name']) && trim($data['pack_name']) == '' &&
            !empty($data['actions']['action_create_pack']))
        {
            $errors['pack_name'] = $this->dof->get_string("pack_name_shouldn't_be_empty", 'transmit', null, 'modlib');
        }
        return $errors;
    }
    
    /**
     * Сохранение пакета настроек
     */
    public function save()
    {
        $packrecord = new stdClass();
        
        if ($this->get_id() > 0)
        {
            $packrecord->id = $this->get_id();
        }
        
        $packrecord->name = $this->get_name();
        
        $packrecord->description = $this->get_description();
        
        $packrecord->config = json_encode([
            'transmittype' => $this->get_transmit_type(),
            'maskcode' => $this->get_mask_code(),
            'maskconfig' => $this->get_mask_config(),
            'sourcecode' => $this->get_source_code(),
            'sourceconfig' => $this->get_source_config()
        ]);
        
        $packrecord->status = $this->get_status();
        $this->dof->storage('transmitpacks')->save($packrecord);
    }
}
