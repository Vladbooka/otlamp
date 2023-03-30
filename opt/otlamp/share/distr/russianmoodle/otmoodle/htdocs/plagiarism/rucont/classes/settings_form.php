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
 * Плагин определения заимствований Руконтекст. Форма для настроек плагина
 * в административной панели.
 *
 * @package    plagiarism
 * @subpackage rucont
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_rucont;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/formslib.php');
use moodle_exception;
use moodleform;
use tabobject;
use core\notification;
use moodle_url;
use stdClass;

class settings_form extends moodleform {

    public $page = null;
    /**
     * Определение формы
     */
    public function definition () 
    {
        $mform = &$this->_form;
        $page = $this->_customdata->page; 
        $pluginconfig = $this->_customdata->pluginconfig;
        $plugindefaults = $this->_customdata->plugindefaults;
        
        $this->page = $page;
        
        switch ( $page )
        {
            // Тарифный план
            case 'tarif' :
                break;
            // Настройки плагина
            case 'configuration':
                // Заголовок
                $mform->addElement('header', 'config', get_string('rucontconfig', 'plagiarism_rucont'));
                // Глобальное включение плигина
                $mform->addElement('advcheckbox', 'enable', get_string('userucont', 'plagiarism_rucont'), '', [0, 1]);
                // Включение плагина для модулей
                $supported_mods = ['assign'];
                foreach ( $supported_mods as $mod )
                {
                    $modname = get_string('pluginname', "mod_$mod");
                    $mform->addElement('checkbox', 'rucont_use_mod_'.$mod, get_string('userucont_mod', 'plagiarism_rucont', $modname), '', [0, 1]);
                    $mform->disabledIf('rucont_use_mod_'.$mod, 'rucont_use');
                }
                // Действия
                $buttonarray = [];
                $buttonarray[] = $mform->createElement('submit', '', get_string('save', 'plagiarism_rucont'));
                $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
                $this->set_data($pluginconfig);
                
                break;
                
            // Настройки по умолчанию для модулей
            case 'defaults':
                // Заголовок
                $mform->addElement('header', 'plugin_header', get_string('rucontdefaults', 'plagiarism_rucont'));
                // Описание
                $mform->addElement('html', get_string('defaultsdesc', 'plagiarism_rucont'));
                
                $options = [0 => get_string('no'), 1 => get_string('yes')];
                $mform->addElement('select', 'use_rucont', get_string('userucont', 'plagiarism_rucont'), $options);
                
                $mform->addElement('select', 'plagiarism_rucont_show_student_report', get_string("studentreports", "plagiarism_rucont"), $options);
                $mform->addHelpButton('plagiarism_rucont_show_student_report', 'studentreports', 'plagiarism_rucont');
                
                // Действия
                $buttonarray = [];
                $buttonarray[] = $mform->createElement('submit', '', get_string('save', 'plagiarism_rucont'));
                $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
                
                $this->set_data($plugindefaults);
                break;
        }
    }
    
    /**
     * Значения по умолчанию для формы
     */
    function set_data($default_values) 
    {
        if ( is_object($default_values) )
        {// Конвертируем в массив данные из формы
            $default_values = (array)$default_values;
        }
        
        foreach ( $default_values as $name => $val )
        {
            $default_values[$name] = $val;
        }
        parent::set_data($default_values);
    }
    
    /**
     * Обработчик формы
     */
    function process()
    {
        global $DB;
    
        if ($formdata = $this->get_data()) {
            // Форма отправлена и проверена
            switch ( $this->page )
            {
                // Настройки плагина
                case 'configuration':
                    set_config('enabled', (int) $formdata->enabled, 'plagiarism_rucont');
                    // Поддерживаемые модули
                    $supported_mods = ['assign'];
                    
                    // Включение плагина для отдельных модулей
                    foreach ( $supported_mods as $mod )
                    {
                        if ( isset($formdata->{'rucont_use_mod_'.$mod}) )
                        {
                            $rucontmoduse = $formdata->{'rucont_use_mod_'.$mod};
                        } else 
                        {
                            $rucontmoduse = 0;
                        }
                        
                        // Если плагин глобально выключен - выключить для всех плагинов
                        $rucontmoduse = ((int) $formdata->enabled == 0) ? 0 : $rucontmoduse;
                        set_config('rucont_use_mod_' . $mod, $rucontmoduse, 'plagiarism_rucont');
                    }
                    
                    // Запись об успешном обновлении конфигурации
                    notification::add(get_string('configupdated', 'plagiarism_rucont'), notification::SUCCESS);
                    redirect(new moodle_url('/plagiarism/rucont/settings.php', [
                        'page' => 'configuration'
                    ]));
                    break;
                    
                // Настройки по умолчанию для модулей
                case 'defaults':
                    
                    $fields = self::get_config_settings_fields();
                    foreach ($fields as $field) {
                        if (! isset($formdata->{$field})) {
                            continue;
                        } else {
                            $name = $field;
                            $val = $formdata->{$field};
                        }
                        $defaultfield = new stdClass();
                        $defaultfield->cm = 0;
                        $defaultfield->name = $name;
                        $defaultfield->value = $val;
                        
                        $configfield = $DB->get_record('plagiarism_rucont_config', [
                            'name' => $name,
                            'cm' => 0
                        ]);
                        if (! empty($configfield)) {
                            // Обновить
                            $configfield->value = $val;
                            if (! $DB->update_record('plagiarism_rucont_config', $configfield)) {
                                print_error('defaultupdateerror', 'plagiarism_rucont');
                            }
                        } else {
                            // Добавить
                            if (! $DB->insert_record('plagiarism_rucont_config', $defaultfield)) {
                                print_error('defaultinserterror', 'plagiarism_rucont');
                            }
                        }
                    }
                    // Уведомление о сохранении настроек
                    notification::add(get_string('defaultupdated', 'plagiarism_rucont'), notification::SUCCESS);
                    redirect(new moodle_url('/plagiarism/rucont/settings.php', [
                        'page' => 'defaults'
                    ]));
                    break;
            }
        }
    }
    
    /**
     * Распечатать вкладки для страницы настроек
     *
     * @param string $currenttab - Идентификатор активной вкладки
     */
    public function draw_settings_tab_menu($currenttab, $notice = null)
    {
        // Формируем табы
        $tabs = [];
        
        // Тарифный план
        $tabs[] = new tabobject(
                'tarif', 
                'settings.php?page=tarif', 
                get_string('otapi', 'plagiarism_rucont'), 
                get_string('otapi', 'plagiarism_rucont'), 
                false
        );
        // Конфигурация
        $tabs[] = new tabobject(
                'configuration', 
                'settings.php?page=configuration', 
                get_string('config', 'plagiarism_rucont'), 
                get_string('config', 'plagiarism_rucont'), 
                false
        );
        // Настройки по умолчанию
        $tabs[] = new tabobject(
                'defaults', 
                'settings.php?page=defaults', 
                get_string('defaults', 'plagiarism_rucont'), 
                get_string('defaults', 'plagiarism_rucont'), 
                false
        );
        
        // Распечатать вкладки
        print_tabs([$tabs], $currenttab);
    }

    /**
     * Получить настройки экземпляра модуля Руконтекст для элемента курса
     *
     * @param int $cmid id из таблицы course_modules, 0 - настройки по умолчанию
     * @param bool $adddefaults - Добавлять значения по умолчанию для неопределенных полей
     * 
     * @return array настройки Руконтекста для модуля
     */
    public static function get_settings($cmid = 0, $adddefaults = true) 
    {
        global $DB;
        $settings = $DB->get_records_menu('plagiarism_rucont_config', ['cm' => $cmid], '', 'name,value');
        if ( ! is_array($settings) )
        {// Нормализация
            $settings = [];
        }
        
        // Заполнение значениями по умолчанию
        if ( ! empty($adddefaults) )
        {
            $defaults = $DB->get_records_menu('plagiarism_rucont_config', ['cm' => 0], '', 'name,value');
            // Заполнение значениями по умолчанию
            if ( is_array($defaults) )
            {
                foreach ( $defaults as $key => $val )
                {
                    if (!key_exists($key, $settings))
                    {// Установка значения по умолчанию
                        $settings[$key] = $val;
                    }
                }
            }
        }

        return $settings;
    }

    /**
     * Get the fields to be used in the form to configure each activities Antiplagiat settings.
     *
     * @return array of settings fields
     */
    public static function get_config_settings_fields() 
    {
        return ['use_rucont', 'plagiarism_rucont_show_student_report'];
    }


    /**
     * Проверить, включён ли плагин для текущего модуля или глобально
     *
     * @param int $cmid
     * @return bool
     */
    public static function is_enabled($cmid = 0) 
    {
        $cmid = (int) $cmid;
        
        if ($cmid === 0) {
            // Получение настройки по умолчанию
            $configsettings = self::get_config_settings();
            if (empty($configsettings['enabled'])) {
                return false;
            }
        } else {
            // Получение настройки для элемента курса
            // Проверка наличия элемента курса
            $cm = get_coursemodule_from_id('', $cmid);
            if (empty($cm)) {
                // Элемент курса не найден
                debugging("cmid [$cmid] not found", DEBUG_DEVELOPER);
                return false;
            }
            // Получение глобальной настройки
            $settings = self::get_settings($cmid);
            if (empty($settings['use_rucont'])) {
                return false;
            }
            // Получение настройки для элемента курса
            $configsettings = self::get_config_settings('mod_' . $cm->modname);
            // Если Антиплагиат не используется в этом элементе курса.
            if (empty($configsettings['rucont_use_mod_' . $cm->modname])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Получение конфигурации плагина
     */
    public static function get_config_settings($modulename = null) 
    {
        $configsettings = [];
        if (empty($modulename)) {
            $configsettings['enabled'] = (int) get_config('plagiarism_rucont', 'enabled');
        } else {
            $configsettings['rucont_use_' . $modulename] = (int) get_config('plagiarism_rucont', 'rucont_use_' . $modulename);
        }
        return $configsettings;
    }
}
