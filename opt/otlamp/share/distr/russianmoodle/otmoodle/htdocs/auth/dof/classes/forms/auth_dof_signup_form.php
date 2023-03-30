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
 * Плагин аутентификации Деканата. Форма шагов регистрации.
 *
 * @package    auth_dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_dof\forms;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot .'/auth/dof/locallib.php');

use moodleform;
use auth_dof\form_fields_factory;
use core\notification;
use moodle_url;
use stdClass;

class auth_dof_signup_form extends moodleform
{
    /**
     * Конфиг плагина
     * @var array
     */
    private $config = [];
    
    /**
     * Шаг регистрации
     * @var integer
     */
    private $step = 1;
    
    /**
     * Настройки полей формы регистрации
     * @var array
     */
    private $user_cfg_fields = [];
    
    /**
     * Зкзекпляры классов "Фабрика полей формы"
     * @var array
     */
    private $fofifainstances = [];
    
    /**
     * Флаг наличия поискового модификатора
     * 
     * @var boolean
     */
    private $has_search_modifier = false;
    
    /**
     * {@inheritDoc}
     * @see moodleform::definition()
     */
    protected function definition() {
        global $CFG, $OUTPUT;
        // Получение настроек плагина
        $this->config = get_config('auth_dof');
        $mform = $this->_form;
        $this->step = $this->_customdata['step'];
        $this->user_cfg_fields = auth_dof_prepare_fields($this->step);
        
        $hasattempts = true;
        if (! empty($this->config->limiting_registration_attempts)) {
            try {
                // Проверяет оставшиеся попытки регистрации по предварительным спискам
                auth_dof_check_attempts('retry', $this->config->plist_reg_retry_time,
                    'auth_dof_plist_reg_attempts', $this->config->plist_reg_attempts);
            } catch (\Exception $e) {
                notification::error(get_string('plist_registration_attempts', 'auth_dof', $e->getMessage()));
                $hasattempts = false;
            }
        }
        if ($hasattempts) {
            // Заголовок формы
            $mform->addElement('static', 'header', get_string('registration', 'auth_dof'), '');
            
            // Получение записи из источника соответствующего условиям поисковых полей первого шага
            $externalrecord = null;
            if ($this->step == 2) {
                if (isset($_SESSION['auth_dof_step_registration_1']) 
                    && is_object($step1dat = json_decode($_SESSION['auth_dof_step_registration_1'])))
                {
                    $externalrecord = auth_dof_get_source_data(auth_dof_prepare_fields(1), (array)$step1dat);
                } else {
                    print_error('Wrong data in server session variable');
                }
            }
            // Созданим элемент формы и применим к нему модификаторы
            foreach ($this->user_cfg_fields as $fldname => $fldcfg) {
                $fofifa = new form_fields_factory($fldname, $this->config);
                if (isset($fldcfg['mod'])) {
                    if (is_array($modcfg = json_decode($fldcfg['mod'], true))) {
                        // Запустим включенные модификаторы для поля
                        foreach (auth_dof_get_handlers('modifiers') as $modname => $str) {
                            // Это проверка на включенность модификатора
                            if (! empty($modcfg[$modname])) {
                                if ($modname == 'search') $this->has_search_modifier = true;
                                $classname = '\\auth_dof\\modifiers\\' . $modname;
                                if (class_exists($classname)) {
                                    $modifier = new $classname(
                                        $fldname, $this->user_cfg_fields, $this->config, $externalrecord);
                                    $modifier->definition($fofifa);
                                }
                            }
                        }
                    }
                }
                $fofifa->definition($mform);
                $this->fofifainstances[$fldname] = $fofifa;
            }
            // Валидация транслируемых полей после формирования форы с данными из внешнего источника 
            // на втором этапе (с дальнейшем редиректом на первый шаг и отображения сообщения 
            // "Данные из внешнего источнике не прошли валидацию, обратитесь к администратору")
            if ($this->step == 2) {
                $errors = [];
                $data = $mform->exportValues();
                foreach ($this->user_cfg_fields as $fldname => $fldcfg) {
                    if (isset($fldcfg['mod']) 
                        && is_array($modcfg = json_decode($fldcfg['mod'], true))
                        && ! empty($modcfg['broadcast'])) 
                    {
                        if ($modcfg['hidden']) {
                            $classname = '\\auth_dof\\modifiers\\broadcast';
                            if (class_exists($classname)) {
                                $modifier = new $classname($fldname, $this->user_cfg_fields,
                                    $this->config, $externalrecord);
                                $modflddata = new stdClass();
                                $modifier->process(null, $modflddata);
                                $data += (array)$modflddata;
                            }
                        }
                        $errors += $this->fofifainstances[$fldname]->validation($data, []);                
                    } 
                }
                if ((is_array($errors) && count($errors)!==0)) {
                    // non-empty array means errors
                    foreach ($errors as $field => $err) {
                        debugging($field . ': ' . $err, DEBUG_DEVELOPER);
                    }
                    notification::error(get_string('no_valid_broadcast_fields', 'auth_dof'));
                    redirect(new moodle_url('/login/signup.php', ['step' => 1]));
                } 
            }
            
            // Капча
            if ($this->step == 1 && signup_captcha_enabled() && get_config('auth_dof', 'recaptcha'))
            {// Капча включена
                $mform->addElement('recaptcha', 'recaptcha_element', get_string('recaptcha', 'auth'));
                $mform->addHelpButton('recaptcha_element', 'recaptcha', 'auth');
            }
            //Добавление Placeholder для кастомных полей с аттрибутом placeholder: text и password
            foreach ($mform->_elements as $formelement) {
                if (isset($formelement->_attributes['type']) &&
                    ($formelement->_attributes['type'] == 'text' ||
                        $formelement->_attributes['type'] == 'password')) {
                            //Является ли поле обязательным для заполнения?
                            if ($mform->isElementRequired($formelement->_attributes['name'])) {
                                $formelement->_attributes['placeholder'] = $formelement->_label . '*';
                            } else {
                                $formelement->_attributes['placeholder'] = $formelement->_label;
                            }
                        }
            }
            // Политика использования
            if ( ! empty($CFG->sitepolicy) )
            {// Политика пользователя доступна
                $mform->addElement('header', 'policyagreement', get_string('policyagreement'), '');
                $mform->setExpanded('policyagreement');
                $mform->addElement('static', 'policylink', '',
                    '<a href="'.$CFG->sitepolicy.'" onclick="this.target=\'_blank\'">'.get_String('policyagreementclick').'</a>');
                $mform->addElement('checkbox', 'policyagreed', get_string('policyaccept'));
                $mform->addRule('policyagreed', get_string('policyagree'), 'required', null, 'server');
            }
            // Подтверждение формы
            if ($this->step == 1 && auth_dof_is_displayed_fields_in_step(2)) {
                $this->add_action_buttons(false, get_string('next'));
            } else {
                $this->add_action_buttons(false, get_string('createaccount', 'auth_dof'));
            }
            // Добавим провайдеры
            $authsequence = get_enabled_auth_plugins(true);
            $identityproviders = new \auth_dof\output\identityproviders($authsequence);
            $mform->addElement('static', 'identityproviders', '', $OUTPUT->render($identityproviders));
        }
    }
    
    /**
     * {@inheritDoc}
     * @see moodleform::validation()
     */
    public function validation($data, $files) {
        // Базовая валидация
        $errors = parent::validation($data, $files);
        // Провеерим наличие записи во внешних источниках
        // критерием работы "по предварительным спискам" будет наличие поискового модификатора
        if ($this->step == 1 && $this->has_search_modifier) {
            if(is_null(auth_dof_get_source_data($this->user_cfg_fields, $data))) {
                $errors['error'] = 'error';
                notification::error(get_string('no_records_found', 'auth_dof'));
                if (! empty($this->config->limiting_registration_attempts)) {
                    // Устанавливает не удачную попытку поиска во внешних источниках
                    auth_dof_set_unsuccessful_attempt('retry', 'auth_dof_plist_reg_attempts');
                    try {
                        // Проверяет оставшиеся попытки регистрации по предварительным спискам
                        auth_dof_check_attempts('retry', $this->config->plist_reg_retry_time,
                            'auth_dof_plist_reg_attempts', $this->config->plist_reg_attempts);
                    } catch (\Exception $e) {
                        redirect(new moodle_url('/login/signup.php', ['step' => 1]));
                    }
                }
            }
        }
        // Выполним валидацию полей текущего шага из экземпляров form_fields_factory
        // сформированных в definition
        foreach ($this->fofifainstances as $fofifa) {
            $errors += $fofifa->validation($data, $files);
        }
        // Выполним валидацию придусмотренную групповыми модификаторами
        foreach (auth_dof_get_handlers('group_modifiers') as $modname => $str) {
            $classname = '\\auth_dof\\group_modifiers\\' . $modname;
            if (class_exists($classname)) {
                $modifier = new $classname(auth_dof_prepare_fields(), $this->config, $this->step);
                $errors += $modifier->validation($data, $files);
            }
        }
        // Валидация капчи
        if ($this->step == 1 && signup_captcha_enabled() && get_config('auth_dof', 'recaptcha')) {
            $recaptchaelement = $this->_form->getElement('recaptcha_element');
            if (!empty($this->_form->_submitValues['g-recaptcha-response'])) {
                $response = $this->_form->_submitValues['g-recaptcha-response'];
                if (!$recaptchaelement->verify($response)) {
                    $errors['recaptcha_element'] = get_string('incorrectpleasetryagain', 'auth');
                }
            } else {
                $errors['recaptcha_element'] = get_string('missingrecaptchachallengefield');
            }
        }
        return $errors;
    }
    
    /**
     * Зависимости в форме от установленных значений 
     */
    function definition_after_data()
    {
        $mform = $this->_form;
        $mform->applyFilter('username', 'trim');
    }
}
