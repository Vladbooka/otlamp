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
 * Форма для настроек плагина plagiarism_apru в административной панели
 * 
 * @package    plagiarism
 * @subpackage apru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_apru;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/formslib.php');
use moodleform; 
use tabobject;
use core\notification;
use moodle_url;
use stdClass;

class settings_form extends moodleform {

    var $page = null;
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
                $mform->addElement('header', 'config', get_string('apruconfig', 'plagiarism_apru'));
                // Глобальное включение плигина
                $mform->addElement('advcheckbox', 'enabled', get_string('useapru', 'plagiarism_apru'), '', [0, 1]);
                // Включение плагина для модулей
                $supported_mods = ['assign'];
                foreach ( $supported_mods as $mod )
                {
                    $modname = get_string('pluginname', "mod_$mod");
                    $mform->addElement('checkbox', 'apru_use_mod_'.$mod, get_string('useapru_mod', 'plagiarism_apru', $modname), '', [0, 1]);
                    $mform->disabledIf('apru_use_mod_'.$mod, 'apru_use');
                }
                
                // Действия
                $buttonarray = [];
                $buttonarray[] = $mform->createElement('submit', '', get_string('save', 'plagiarism_apru'));
                $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
                
                $this->set_data($pluginconfig);
                break;
            
            // Настройки по умолчанию для модулей
            case 'defaults':
                // Заголовок
                $mform->addElement('header', 'plugin_header', get_string('aprudefaults', 'plagiarism_apru'));
                // Описание
                $mform->addElement('html', get_string('defaultsdesc', 'plagiarism_apru'));
                
                $options = [0 => get_string('no'), 1 => get_string('yes')];
                $mform->addElement('select', 'use_apru', get_string('useapru', 'plagiarism_apru'), $options);
                
                $mform->addElement('select', 'plagiarism_show_student_report', get_string("studentreports", "plagiarism_apru"), $options);
                $mform->addHelpButton('plagiarism_show_student_report', 'studentreports', 'plagiarism_apru');
                
                $mform->addElement('select', 'mod_assign_confirmation_required', get_string("setting_mod_assign_confirmation_required", "plagiarism_apru"), $options);
                $docsforcheck = [
                    25 => '25', 
                    50 => '50', 
                    100 => '100', 
                    200 => '200', 
                    500 => '500'
                ];
                $mform->addElement('select', 'docs_for_check', get_string('setting_docs_for_check', 'plagiarism_apru'), $docsforcheck);
                
                $mform->addElement('select', 'docs_for_update', get_string('setting_docs_for_update', 'plagiarism_apru'), $docsforcheck);
                
                // Действия
                $buttonarray = [];
                $buttonarray[] = $mform->createElement('submit', '', get_string('save', 'plagiarism_apru'));
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
            switch ($this->page) {
                // Настройки плагина
                case 'configuration':
                    // Глобальное состояние плагина
                    set_config('enabled', (int) $formdata->enabled, 'plagiarism_apru');

                    // Поддерживаемые модули
                    $supported_mods = [
                        'assign'
                    ];
                    // Включение плагина для отдельных модулей
                    foreach ($supported_mods as $mod) {
                        if (isset($formdata->{'apru_use_mod_' . $mod})) {
                            $aprumoduse = $formdata->{'apru_use_mod_' . $mod};
                        } else {
                            $aprumoduse = 0;
                        }
                        // Если плагин глобально выключен - выключить для всех плагинов
                        $aprumoduse = ((int) $formdata->enabled == 0) ? 0 : $aprumoduse;
                        set_config('apru_use_mod_' . $mod, $aprumoduse, 'plagiarism_apru');
                    }

                    // Запись об успешном обновлении конфигурации
                    notification::add(get_string('configupdated', 'plagiarism_apru'), notification::SUCCESS);
                    redirect(new moodle_url('/plagiarism/apru/settings.php', [
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

                        $configfield = $DB->get_record('plagiarism_apru_config', [
                            'name' => $name,
                            'cm' => 0
                        ]);
                        if (! empty($configfield)) { 
                            // Обновить
                            $configfield->value = $val;
                            if (! $DB->update_record('plagiarism_apru_config', $configfield)) {
                                print_error('defaultupdateerror', 'plagiarism_apru');
                            }
                        } else { 
                            // Добавить
                            if (! $DB->insert_record('plagiarism_apru_config', $defaultfield)) {
                                print_error('defaultinserterror', 'plagiarism_apru');
                            }
                        }
                    }
                    // Уведомление о сохранении настроек
                    notification::add(get_string('defaultupdated', 'plagiarism_apru'), notification::SUCCESS);
                    redirect(new moodle_url('/plagiarism/apru/settings.php', [
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
                get_string('otapi', 'plagiarism_apru'), 
                get_string('otapi', 'plagiarism_apru'), 
                false
        );
        // Конфигурация
        $tabs[] = new tabobject(
                'configuration', 
                'settings.php?page=configuration', 
                get_string('config', 'plagiarism_apru'), 
                get_string('config', 'plagiarism_apru'), 
                false
        );
        // Настройки по умолчанию
        $tabs[] = new tabobject(
                'defaults', 
                'settings.php?page=defaults', 
                get_string('defaults', 'plagiarism_apru'), 
                get_string('defaults', 'plagiarism_apru'), 
                false
        );
        
        // Распечатать вкладки
        print_tabs([$tabs], $currenttab);
    }

    /**
     * Получить настройки экземпляра модуля Антиплагиата для элемента курса
     *
     * @param int $cmid id из таблицы course_modules, 0 - настройки по умолчанию
     * @return array настройки Антиплагиата для модуля
     */
    public static function get_settings($cmid = 0) {
        global $DB;
        $settings = $DB->get_records_menu('plagiarism_apru_config', array('cm' => $cmid), '', 'name,value');
        return $settings;
    }

    /**
     * Get the fields to be used in the form to configure each activities Antiplagiat settings.
     *
     * @return array of settings fields
     */
    public static function get_config_settings_fields() 
    {
        return [
            'use_apru', 
            'plagiarism_show_student_report', 
            'mod_assign_confirmation_required', 
            'docs_for_check', 
            'docs_for_update'
        ];
    }


    /**
     * Проверить, включён ли плагин антиплагиата для текущего модуля или глобально
     *
     * @param int $cmid - ID элемента курса
     * 
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
            if (empty($settings['use_apru'])) {
                return false;
            }
            // Получение настройки для элемента курса
            $configsettings = self::get_config_settings('mod_' . $cm->modname);
            // Если Антиплагиат не используется в этом элементе курса.
            if (empty($configsettings['apru_use_mod_' . $cm->modname])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Получение конфигурации плагина
     * @param string $modulename имя плагина в формате component_plaginname (например, mod_assign)
     * @return number[]
     */
    public static function get_config_settings($modulename = null) 
    {
        $configsettings = [];
        if (empty($modulename)) {
            $configsettings['enabled'] = (int) get_config('plagiarism_apru', 'enabled');
        } else {
            $configsettings['apru_use_' . $modulename] = (int) get_config('plagiarism_apru', 'apru_use_' . $modulename);
        }
        return $configsettings;
    }
}
