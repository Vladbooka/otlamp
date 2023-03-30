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
 * История обучения. Класс формы дополнительных настроек курса
 *
 * Для добавления новых свойств курса необходимо:
 * - Объявить поле в методе definition
 * - Если поле сложное(значение поля нельзя сразу записать в БД),
 * то необходимо добавить логику сохранения поля и заполнения значения по умолчанию
 * - Если поле простое, то необходмо добавить его низвание в массив $configs обработчика
 * формы.
 * Сохраниение и заполнение поля в форме установленным значением произойдет автоматически.
 *
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace otcomponent_otlogger\form;

use moodleform;
use otcomponent_otlogger\log_manager;
use core\notification;

defined('MOODLE_INTERNAL') || die;

class logger_settings_form extends moodleform {
       
    
    public function definition(){
        
        $mform    = $this->_form;
        
//         Заголовок формы
//         $mform->addElement('header','loggersettings', get_string('pluginname', 'otcomponent_otlogger'));
        
        // Добавляем глобальную настройку логирования
        $mform->addElement('checkbox', 'log_enabled',get_string('log_enabled_description', 'otcomponent_otlogger'));
        $mform->setdefault('log_enabled', get_config('otcomponent_otlogger','log_enabled'));
        
        // Получаем список доступных получателей логов
        $receivers = $this->get_available_log_receivers_list();
        
        // Получаем список плагинов, поддерживающих логи и список кодов логов для плагина.
        $filteritems = $this->get_available_log_keys_list();
        $filternames = array_keys($filteritems);
        
       
        // Получаем список конфигураций логгера из config_plugins и сохраняем в свойство
        $configurations = [];
        if (! empty($config = get_config('otcomponent_otlogger', 'log_configurations'))){
            $configurations = explode(',',$config);
        }
                        
        // Для удаления конфигурации
        $yesno = [
            0 => get_string('no'),
            1 => get_string('yes')
        ];
        
        // Изменение существующих конфигураций
        if (! empty($configurations)){
                                               
            foreach($configurations as $configuration){
                
                // log configuration header
                $headername = $configuration . '_header';
                $headerlabel = get_string('editing_configurations', 'otcomponent_otlogger', $configuration);
                $mform->addElement('header', $headername , $headerlabel);
                
//                 // Имя конфигурации. В данном случае можно не отображать: 
//                 $label = get_string('log_method_name', 'otcomponent_otlogger');
//                 $attrs = ['readonly' => true ];
//                 $mform->addElement('text', $configuration, $label, $attrs);
//                 $mform->setType($configuration, PARAM_ALPHA);
//                 $mform->setDefault($configuration, $configuration);
                
                // Включить логирование по этой конфигурации
                $name = $configuration . '_enabled';
                $label = get_string('log_method_enabled', 'otcomponent_otlogger');
                $mform->addElement('checkbox', $name, $label);
                $mform->setDefault($name, get_config('otcomponent_otlogger', $configuration . '_enabled'));
                
                // Выбор получателя
                $name = $configuration . '_receiver';
                $label = get_string('receiver', 'otcomponent_otlogger');
                $mform->addElement('select', $name, $label, $receivers);
                
                // Если в настройках работающий получатель, устанавливаем его по умолчанию
                $receiver = get_config('otcomponent_otlogger', $configuration . '_receiver');
                
                if ($receiver && (array_key_exists($receiver,$receivers))){
                    $mform->setDefault($name, $receiver);
                }
                
                // Выбор логов
                $name = $configuration . '_filter';
                $label = get_string('filter', 'otcomponent_otlogger');
                $options = array(
                    'multiple' => true,
                    'noselectionstring' => get_string('filter_noselection', 'otcomponent_otlogger'),
                );
                $mform->addElement('autocomplete', $name, $label, $filteritems, $options);
                // По умолчанию выставляем пересечение доступных логов и настройки
                $mform->setDefault(
                    $configuration . '_filter',
                    array_intersect($filternames, explode(',',get_config('otcomponent_otlogger', $configuration . '_filter')))
                );
                
                // Удаление конфигураций
                $name = $configuration . '_deleter';
                $label = get_string('delete_configuration','otcomponent_otlogger');
                $mform->addElement('select', $name, $label, $yesno);
                // По умолчанию ничего не удаляем!
                $mform->setDefault(
                    $configuration . '_deleter',
                    0
                );
                
            }
        }
        
        // Заголовок добавления новой конфигурации
        $mform->addElement('header','new_configurations', get_string('adding_configurations', 'otcomponent_otlogger'));
         
        //Имя новой конфигурации логирования
        $name = 'new_log_configuration_name';
        $label = get_string('new_log_configuration_name', 'otcomponent_otlogger');
        $mform->addElement('text', $name, $label);
        $mform->setType($name, PARAM_ALPHA);
        
        // Включить метод логирования
        $name = 'new_log_configuration_enabled';
        $label = get_string('log_method_enabled', 'otcomponent_otlogger');
        $mform->addElement('checkbox', $name, $label);
        
        //Добавляем выбор получателя логов
        $name = 'new_log_configuration_receiver';
        $label = get_string('receiver', 'otcomponent_otlogger');
        $mform->addElement('select', $name, $label, $receivers);
        
        // Фильтрация логов
        $name =  'new_log_configuration_filter';
        $label = get_string('filter', 'otcomponent_otlogger');
        $options = array(
            'multiple' => true,
            'noselectionstring' => get_string('filter_noselection', 'otcomponent_otlogger'),
        );
        $mform->addElement('autocomplete', $name, $label, $filteritems, $options);       
        
        // Добавляем кнопки управления
        $this->add_action_buttons();
        
    }
    
    /**
     * Проверка на стороне сервера
     *
     * @param array data - данные из формы
     * @param array files - файлы из формы
     *
     * @return array - массив ошибок
     */
    public function validation($data,$files)
    {
        $errors = parent::validation($data, $files);
        $configurations = explode(',',get_config('otcomponent_otlogger', 'log_configurations'));
        
        if (in_array($data['new_log_configuration_name'], $configurations)){
            // Имя конфигурации должно быть уникальным
            $errors['new_log_configuration_name'] = get_string('error_duplicate_configuration_name', 'otcomponent_otlogger');
        }
        // Возвращаем ошибки, если они возникли
        return $errors;
    }

    /**
     * Обработчик формы
     */
    public function process()
    {
        global $PAGE;
        
        if ( $formdata = $this->get_data() )
        {// Форма отправлена и проверена
            // Включить/выключить логирование
            if (isset ($formdata->log_enabled)){ 
                $log_enabled = $formdata->log_enabled;                
            } else {
                $log_enabled = 0;
            }
            set_config('log_enabled', $log_enabled, 'otcomponent_otlogger');
            
            // Настройки существующих конфигураций
            $configurations = [];
            if (! empty($config = get_config('otcomponent_otlogger', 'log_configurations'))){
                $configurations = explode(',',$config);
            }
            // Если заполнено имя новой конфигурации  и такой еще нет - создаем новую конфигурацию
            if (! empty($formdata->new_log_configuration_name)){
                $configuration = $formdata->new_log_configuration_name;
                $configurations[] = $configuration;
                
            }
            
            $deleted = [];
            // Заполняем настройки конфигураций
            foreach ($configurations as $configuration){

                // Строки - метки настроек
                $enabledstr = $configuration . '_enabled';
                $receiverstr = $configuration . '_receiver';
                $filterstr = $configuration . '_filter';
                
                // Удаляем конфигурацию, если необходимо
                $deletestr = $configuration . '_deleter';
                if (isset($formdata->$deletestr) && $formdata->$deletestr){
                    unset_config($enabledstr,'otcomponent_otlogger');
                    unset_config($receiverstr,'otcomponent_otlogger');
                    unset_config($filterstr,'otcomponent_otlogger');
                    $deleted[] = $configuration;
                    continue;
                }
                
                $newconfiguration = ($configuration == $formdata->new_log_configuration_name);
                
                // Включить/выключить логирование по конфигурации
                if (isset($formdata->$enabledstr)){
                    $config = $formdata->$enabledstr;
                } elseif ($newconfiguration && isset($formdata->new_log_configuration_enabled)){
                    $config = $formdata->new_log_configuration_enabled;
                } else {
                    $config = 0;
                }
                set_config($enabledstr, $config, 'otcomponent_otlogger');
                
                // Получатель логов
                if (isset($formdata->$receiverstr)){
                    $config = $formdata->$receiverstr;
                    
                } elseif ($newconfiguration && isset($formdata->new_log_configuration_receiver)){
                    $config = $formdata->new_log_configuration_receiver;
                } else {
                    $config = '';
                }
                set_config($receiverstr, $config, 'otcomponent_otlogger');
                // Фильтрация логов
                if (isset($formdata->$filterstr)){
                    $config = implode(',', (array)$formdata->$filterstr);
                } elseif ($newconfiguration && isset($formdata->new_log_configuration_filter)){
                    $config = implode(',', $formdata->new_log_configuration_filter);
                } else {
                    $config = '';
                }
                set_config($filterstr, $config, 'otcomponent_otlogger');
            }
            // Сохраняем обновленный список конфигураций
            $configurations = array_diff($configurations, $deleted);
            $config = '';
            if (! empty($configurations)){
                $config = implode(',', $configurations);
            }
            set_config('log_configurations', $config, 'otcomponent_otlogger');
            
            notification::success(get_string('changessaved'));
            redirect($PAGE->url);
        }
    }
        
    /**
     * Получить список доступных получателей лога
     *
     * @return array список получателей логов для отображения: 
     *                  ключи массива - коды получателей, сохраняемые в config_plugins,
     *                  значения - названия для отображения
     */
    
    private function get_available_log_receivers_list(){
        $receivers = log_manager::get_available_log_receivers();
        foreach ($receivers as $receiver){
            $receiverlist[$receiver] = get_string($receiver,'otcomponent_otlogger');
        }
        return $receiverlist;
    }
    
    
    /**
     * Получить список доступных кодов лога для всех плагинов, поддерживающих логирование 
     * 
     * @return array of strings ключами которого являются коды лога вида pluginname_logtype_logkey
     *                          значениями - лейбл кода для отображения в фильтре
     */
    
    private function get_available_log_keys_list(){
        $logkeys = log_manager::get_available_log_keys();
        foreach($logkeys as $plugin => $logtypes){
            foreach ($logtypes as $logtype => $logkeys){
                foreach ($logkeys as $logkey){
                    $logkeyname = $plugin . '_' . $logtype . '_' . $logkey;
                    $logkeynamel = get_string('log_' . $logtype , $plugin) . ': ' . get_string($logkey, $plugin);
                    $logkeyslist[$logkeyname] = $logkeynamel;
                }
            }
        }
        return $logkeyslist;
    }
    
}