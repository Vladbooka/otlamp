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
 * Фабрика полей формы
 *
 * @package    auth
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_dof;

use core_user;
use core\notification;
use core_text;
use HTML_QuickForm;
use Complex\Exception;

class form_fields_factory
{
    private $fldname = '';
    private $fldcfg = null;
    private $config = null;
    
    public function __isset($name) {
        return isset($this->fldcfg->{$name});
    }
    
    /**
     * Устанавливает настройки для текущего поля профиля
     *
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value) {
        if (property_exists($this->fldcfg, $name)) {
            $this->fldcfg->{$name} = $value;
        }
    }
        
    function __construct(string $fldname, $config) {
        $this->config = $config;
        $this->fldname = $fldname;
        // Дефолтные значения для конфига поля
        $fldcfg = new \stdClass();
        $fldcfg->required = 0;
        $fldcfg->locked = 0;
        $fldcfg->visible = 1;
        $fldcfg->signup = 1;
        $fldcfg->validate = 1;
        $fldcfg->defaultdata = NULL;
        $this->fldcfg = $fldcfg;
    }
    
    /**
     * Возвращает свойства, переданного стандартного поля пользователя,
     * отсутствующие в core_user 
     * 
     * @param string $fldname
     * @return []|\moodle_exception
     */
    public static function user_field_properties(string $fldname) {
        $fieldsproperties = [
            'url' => ['maxlength' => '255'],
            'icq' => ['forceltr' => true, 'maxlength' => '15'],
            'skype' => ['forceltr' => true, 'maxlength' => '50'],
            'aim' => ['forceltr' => true, 'maxlength' => '50'],
            'yahoo' => ['forceltr' => true, 'maxlength' => '50'],
            'msn' => ['forceltr' => true, 'maxlength' => '50'],
            'idnumber' => ['maxlength' => '255'],
            'institution' => ['maxlength' => '255'],
            'department' => ['maxlength' => '255'],
            'phone1' => ['forceltr' => true, 'maxlength' => '20'],
            'phone2' => ['forceltr' => true, 'maxlength' => '20'],
            'address' => ['maxlength' => '255'],
            'city' => ['maxlength' => '120'],
            'email' => ['maxlength' => '100'],
            'username' => ['maxlength' => '100'],
            'firstname' => ['maxlength' => '100'],
            'lastname' => ['maxlength' => '100'],
            'middlename' => ['maxlength' => '100'],
            'country' => ['maxlength' => '2'],
            'password' => ['maxlength' => '255'],
            'lastnamephonetic' => ['maxlength' => '255'],
            'firstnamephonetic' => ['maxlength' => '255'],
            'alternatename' => ['maxlength' => '255']
        ];
        if (array_key_exists($fldname, $fieldsproperties)) {
            return $fieldsproperties[$fldname];
        }
        print_error("No properties for user standart field '{$fldname}'");
    }
    
    public function definition(HTML_QuickForm $mform) {
        global $CFG, $DB;
        if (! $this->fldcfg->visible) {
            return;
        }
        if (stripos($this->fldname, 'user_field_') === 0) {
            $fldname = substr($this->fldname, 11);
            // Moodle optional fields.
            $fieldproperties = self::user_field_properties($fldname);
            $req =  '';
            // Обязательное поле
            if ($this->fldcfg->required) $req =  '*';
            $placeholder = ['placeholder' => get_user_field_name($fldname) . $req];
            if (! in_array($fldname, ['country', 'password'])) {
                $mform->addElement(
                    'text',
                    $fldname,
                    get_user_field_name($fldname),
                    array_merge(
                        ['maxlength' => $fieldproperties['maxlength'], 'size' => 30],
                        $placeholder
                        )
                    );
                $mform->setType($fldname, core_user::get_property_type($fldname));
                
                try {
                    $propertydefault = core_user::get_property_default($fldname);
                } catch(\Exception $e) {
                    $propertydefault = null;
                }
                if (!is_null($propertydefault)) {
                    $mform->setDefault($fldname, $propertydefault);
                }
                if (! empty($fieldproperties['forceltr'])) {
                    $mform->setForceLtr($fldname);
                }
            } else {
                switch ($fldname) {
                    //Страна
                    case 'country':
                        $choices = get_string_manager()->get_list_of_countries();
                        $choices = array('' => get_string('selectacountry') . '...') + $choices;
                        $mform->addElement('select', 'country', get_string('selectacountry'), $choices);
                        if (!empty($CFG->country)) {
                            $mform->setDefault('country', core_user::get_property_default('country'));
                        }
                        break;
                    //Пароль
                    case 'password':
                        if (! empty($CFG->passwordpolicy)) {
                            // Политика безопасности
                            $mform->addElement('static', 'passwordpolicyinfo', '', print_password_policy());
                        }
                        if (! empty($this->config->passwordrepeat)) {
                            $passwordfieldtype = 'password';
                        } else {
                            $passwordfieldtype = 'passwordunmask';
                        }
                        $mform->addElement(
                            $passwordfieldtype,
                            'password',
                            get_string('password'),
                            [
                                'placeholder' => get_string('password'),
                                'class' => $passwordfieldtype,
                                'maxlength' => $fieldproperties['maxlength']
                            ]
                            );
                        $mform->setType('password', PARAM_RAW_TRIMMED);
                        if (! empty($this->config->passwordrepeat)) {
                            $mform->addElement(
                                $passwordfieldtype,
                                'passwordrepeat',
                                get_string('passwordrepeat', 'auth_dof'),
                                [
                                    'placeholder' => get_string('passwordrepeat', 'auth_dof'),
                                    'class' => $passwordfieldtype,
                                    'maxlength' => $fieldproperties['maxlength']
                                ]
                                );
                            $mform->setType('passwordrepeat', PARAM_RAW_TRIMMED);
                            // Обязательное поле
                            if ($this->fldcfg->required) {
                                $mform->addRule('passwordrepeat', get_string('required'), 'required', null, 'server');
                            }
                        }
                        break;
                }
            }
            // Обязательное поле
            if ($this->fldcfg->required) {
                $mform->addRule($fldname, get_string('required'), 'required', null, 'server');
            }
            // Замороженное поле
            if ($this->fldcfg->locked) {
                // Использование freeze($fldname) не оправдало себя так-как с модификатором обязательное поле
                // валидация возвращяет ошибку
                $mform->updateElementAttr($fldname, ['readonly' => 'readonly']);
            }
            // Установим значение по умолчанию
            if ($this->fldcfg->defaultdata !== null) {
                $mform->setDefault($fldname, $this->fldcfg->defaultdata);
            }
        } elseif (stripos($this->fldname, 'user_profilefield_') === 0) {
            if ($field = $DB->get_record('user_info_field', ['shortname' => substr($this->fldname, 18)])) {
                foreach ($this->fldcfg as $cfgname => $cfgval) {
                    if ($cfgname == 'defaultdata' && is_null($cfgval)) continue;
                    $field->{$cfgname} = $cfgval;
                }
                require_once($CFG->dirroot.'/user/profile/field/'.$field->datatype.'/field.class.php');
                $newfield = 'profile_field_'.$field->datatype;
                $fieldobject = new $newfield(0, 0, $field);
                $fieldobject->edit_field($mform);
                $fieldobject->edit_after_data($mform);
            }
        }
    }
    
    
    public function validation($data, $files) {
        global $CFG, $DB;
        $errors = [];
        if (! $this->fldcfg->validate) {
            return $errors;
        }
        if (stripos($this->fldname, 'user_field_') === 0) {
            $fldname = substr($this->fldname, 11);
            if (! array_key_exists($fldname, $data)) {
                return $errors;
            }
            switch ($fldname) {
                // Валидация имени пользователя
                case 'username':
                    // Нормализация имени пользователя
                    if ( $DB->record_exists(
                        'user', array('username' => $data['username'], 'mnethostid'=>$CFG->mnet_localhost_id)
                    )) {// Имя пользователя зарезервировано в системе
                        $errors['username'] = get_string('usernameexists');
                    } else {
                        // Проверка символов в имени пользователя
                        if ($data['username'] !== core_text::strtolower($data['username'])) {
                            // Имя пользователя заполняется только в нижнем регистре
                            $errors['username'] = get_string('usernamelowercase');
                        } else {
                            // Имя прользователя содержит недопустимые символы
                            if ($data['username'] !== clean_param($data['username'], PARAM_USERNAME)) {
                                $errors['username'] = get_string('invalidusername');
                            }
                        }
                    }
                    break;
                // Валидация email
                case 'email':
                    if (!(!$this->fldcfg->required && empty($data[$fldname]))) {
                        if (! validate_email($data['email']) ) {
                            // Email не валиден
                            $errors['email'] = get_string('invalidemail');
                        } else if ($DB->record_exists('user', ['email' => $data['email']])) {
                            // Email пользователя зарезервирован
                            $errors['email'] = get_string('emailexists')
                                . ' <a href="forgot_password.php">'.get_string('newpassword').'?</a>';
                        }
                        if (! isset($errors['email'])) {// Проверка на поддержку данного типа email
                            if ($err = email_is_not_allowed($data['email'])) {
                                $errors['email'] = $err;
                            }
                        }
                    }
                    break;
                // Валидация номера телефона
                case 'phone1':
                case 'phone2':
                    if (!(!$this->fldcfg->required && empty($data[$fldname]))) {
                        // Получение текущего метода авторизации
                        $authplugin = get_auth_plugin($CFG->registerauth);
                        $phone = $authplugin->clean_phonenumber($data[$fldname]);
                        if (empty($phone)) {// Номер не валиден
                            $errors[$fldname] = get_string('phone_not_valid', 'auth_dof');
                        } else {
                            $exist = $DB->record_exists('user', [$fldname => $data[$fldname], 'deleted' => 0]);
                            if ( ! empty($exist) )
                            {// Номер телефона уже указан в системе
                                $errors[$fldname] = get_string('phone_exists', 'auth_dof');
                            }
                        }
                    }
                    break;
                // Валидация пароля
                case 'password':
                    if (!(!$this->fldcfg->required && empty($data[$fldname]))) {
                        $errmsg = '';
                        if (! check_password_policy($data['password'], $errmsg)) {
                            $errors['password'] = $errmsg;
                        }
                        if(! empty($this->config->passwordrepeat) && $data['password'] !== $data['passwordrepeat']) {
                            $errors['passwordrepeat'] = get_string('error_password_mismatch', 'auth_dof');
                        }
                    }
                    break;
            }
        } elseif (stripos($this->fldname, 'user_profilefield_') === 0) {
            $fldname = 'profile_field_' . substr($this->fldname, 18);
            if (! array_key_exists($fldname, $data)) {
                return $errors;
            }
            // Валидация дополнительных полей пользователя
            if ($field = $DB->get_record('user_info_field', ['shortname' => substr($this->fldname, 18)])) {
                foreach ($this->fldcfg as $cfgname => $cfgval) {
                    if ($cfgname == 'defaultdata' && is_null($cfgval)) continue;
                    $field->{$cfgname} = $cfgval;
                }
                // Выполним валидацию за счет библиотеки пользовательских полей 
                require_once($CFG->dirroot.'/user/profile/field/'.$field->datatype.'/field.class.php');
                $newfield = 'profile_field_'.$field->datatype;
                $fieldobject = new $newfield(0, 0, $field);
                $usernew = (object)$data;
                $usernew->id = 0;
                $errors += $fieldobject->edit_validate_field($usernew, $files);
                // так как на момент написание библиотека облодала весьма скудными возможностями
                // дописана валидация для типов дата, селект и чекбокс
                switch ($field->datatype) {
                    case 'datetime':
                        if (!(($field->param1 && $data[$fldname] > make_timestamp($field->param1))
                            || ($field->param2 && $data[$fldname] < make_timestamp($field->param2 + 1))))
                        {
                            $errors[$fldname] = get_string('fff_datetime_not_valid', 'auth_dof');
                        }
                        break;
                    case 'menu':
                        $options = explode("\n", $field->param1);
                        if (!in_array($data[$fldname], $options)) {
                            $errors[$fldname] = get_string('fff_menu_not_valid', 'auth_dof');
                        }
                        break;
                    case 'checkbox':
                        if (!(!is_bool($data[$fldname]) || !(is_int($data[$fldname]) && $data[$fldname] <= 1))) {
                            $errors[$fldname] = get_string('fff_checkbox_not_valid', 'auth_dof');
                        }
                        break;
                }
            }
        }
        return $errors;
    }
    
    public function process($user) {
        $prepareduf = [];
        $fldname = '';
        if (stripos($this->fldname, 'user_field_') === 0) {
            $fldname = substr($this->fldname, 11);
        } elseif (stripos($this->fldname, 'user_profilefield_') === 0) {
            $fldname = 'profile_field_' . substr($this->fldname, 18);
        } 
        if ($fldname && isset($user->{$fldname})) {
            $prepareduf[$fldname] = $user->{$fldname};
        }
        return $prepareduf;
    }
}