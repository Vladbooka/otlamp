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
 * Внешние данные
 *
 * @package    block_otexternaldata
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_otexternaldata;

use core\notification;

require_once($CFG->libdir . '/formslib.php');

class content_management_form extends \moodleform {
    
    public function definition()
    {
        $mform = &$this->_form;
        
        // заголовок
        $mform->addElement('header', 'content_type_header', get_string('content_type_header', 'block_otexternaldata'));
        
        // выбор типа контента: записи из внешней бд, файлы по webdav и т.д.
        $contenttypes = \block_otexternaldata\content_type::get_content_types_list();
        $mform->addElement('select', 'content_type', get_string('content_type', 'block_otexternaldata'), $contenttypes);
        
        // применение выбранного типа (форма перезагрузится и подгрузит настройки выбранного типа)
        $mform->addElement('submit', 'content_management_apply_content_type', get_string('content_management_apply_content_type', 'block_otexternaldata'));
        
    }
    
    
    public function definition_after_data()
    {
        global $DB;
        $mform =& $this->_form;
        
        try {
            // сохраненный (!) тип контента (не тот, который сейчас был выбран в форме)
            $contenttypename = $this->_customdata['content_type'];
            
            $blockinstancerecord = $DB->get_record('block_instances', ['id' => $this->_customdata['blockinstanceid']]);
            $blockinstance = block_instance('otexternaldata', $blockinstancerecord);
            
            // экземпляр класса сохраненного типа контента
            $contenttypeinstance = \block_otexternaldata\content_type::get_content_type_instance($contenttypename, $blockinstance);
            
            // заголовок типа контента
            $mform->addElement('header', $contenttypename . '_header', get_string($contenttypename . '_header', 'block_otexternaldata'));
            
            // скрытое поле для ошибок
            $mform->addElement('static', $contenttypename . '_errors', '', '');
            $mform->setType($contenttypename . '_errors', PARAM_RAW);
            
            // добавление элементов формы, свойственных типу контента
            $extended = $contenttypeinstance->extend_form_definition($mform);
            if (!empty($extended))
            {
                // отображение контента всегда пропускается через шаблон
                $mform->addElement('textarea', 'mustache', get_string('mustache', 'block_otexternaldata'),
                    ['class' => 'otexternaldata_textarea']);
                $mform->addHelpButton('mustache', $contenttypename.'_mustache', 'block_otexternaldata');
                $mform->setType('mustache', PARAM_RAW);
                
                
                // кнопки для сохранения всей формы
                $this->add_action_buttons(false);
            }
        } catch(\Exception $ex)
        {
        }
        
    }
    
    public function validation($data, $files)
    {
        global $DB;
        
        $errors = [];
        
        // получение текущих настроек блока
        try {
            $blockinstancerecord = $DB->get_record('block_instances', ['id' => $this->_customdata['blockinstanceid']]);
            $blockinstance = block_instance('otexternaldata', $blockinstancerecord);
        } catch(\Exception $ex)
        {
            $errors['content_type'] = get_string('error_getting_block_instance', 'block_otexternaldata');
            return $errors;
        }
        
        if (!empty($data['content_management_apply_content_type']))
        {
            // экземпляр класса выбранного в форме типа контента
            try {
                $contenttypeinstance = \block_otexternaldata\content_type::get_content_type_instance($data['content_type'], $blockinstance);
            } catch(\Exception $ex)
            {
                $errors['content_type'] = get_string('error_unknown_content_type', 'block_otexternaldata');
            }
            
        } else {
            
            // экземпляр класса сохраненного типа контента
            try {
                $contenttypeinstance = \block_otexternaldata\content_type::get_content_type_instance(
                    $blockinstance->config->content_type,
                    $blockinstance
                );
            } catch (\Exception $ex) {
                $errors['content_type'] = get_string('error_unknown_content_type', 'block_otexternaldata');
                return $errors;
            }
            
            $contenttypename = $blockinstance->config->content_type;
            
            // формирование конфига по данным из формы
            try {
                $config = $contenttypeinstance->compose_config($data, true);
            } catch (\Exception $ex) {
                $errors[$contenttypename.'_errors'] = get_string('error_while_composing_config', 'block_otexternaldata', $ex->getMessage());
                return $errors;
            }
            
            try {
                $contenttypeinstance->validate_config($config);
            } catch(\Exception $ex)
            {
                $errors[$contenttypename.'_errors'] = get_string('error_config_not_valid', 'block_otexternaldata', $ex->getMessage());
            }
            
        }
        
        return $errors;
    }
    
    public function process()
    {
        global $DB;
        
        if ($formdata = $this->get_data())
        {
            
            // получение текущих настроек блока
            $blockinstancerecord = $DB->get_record('block_instances', ['id' => $this->_customdata['blockinstanceid']]);
            $blockinstance = block_instance('otexternaldata', $blockinstancerecord);
            
            // установка значений по умолчанию
            if (is_null($blockinstance->config))
            {
                $blockinstance->config = new \stdClass();
            }
            if (!property_exists($blockinstance->config, 'content_type'))
            {
                $blockinstance->config->content_type = null;
            }
            if (!property_exists($blockinstance->config, 'content_type_configs'))
            {
                $blockinstance->config->content_type_configs = [];
            }
            
            // Установка отправленных формой значений
            if (!empty($formdata->content_management_apply_content_type))
            {
                // Требуется смена типа контента
                $blockinstance->config->content_type = $formdata->content_type;
                
            } else {
                
                // Форма отправлена с целью сохранения данных.
                // Считаем, что тип контента тот же, что и был ранее сохранен, даже если кто-то его изменил, но не применил
                // (ну не применил же!)
                
                // экземпляр класса сохраненного типа контента
                $contenttypeinstance = \block_otexternaldata\content_type::get_content_type_instance(
                    $blockinstance->config->content_type,
                    $blockinstance
                );
                // формирование конфига по данным из формы
                $config = $contenttypeinstance->compose_config(json_decode(json_encode($formdata), true));
                // шаблон отображения добавляем в конфиг выбранного типа, чтобы можно было сохранить каждому свой
                $config['mustache'] = $formdata->mustache;
                // установка данных в свойства блока
                $blockinstance->config->content_type_configs[$blockinstance->config->content_type] = $config;
                
            }
            
            // В любом случае сохраняем изменения
            $blockinstance->instance_config_save($blockinstance->config);
            
            redirect($this->_customdata['baseurl']);
        }
    }
    
    
}