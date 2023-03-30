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
 * Модификатор - генерируемое поле
 *
 * @package    auth
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_dof\modifiers;

use auth_dof\modifiers_base;
use auth_dof\form_fields_factory;
use core_text;
use stdClass;

class generated extends modifiers_base
{
    /**
     * Получение языковой строки модификатора
     * 
     * @return string
     */
    public static function get_name_string() {
        return get_string('mod_generated', 'auth_dof');
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \auth_dof\modifiers_base::is_field_data_returned()
     */
    public function is_field_data_returned() {
        return true;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \auth_dof\modifiers_base::definition()
     */
    public function definition(form_fields_factory $fofifa) {
        $fofifa->visible = 0;
        $fofifa->validate = 0;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \auth_dof\modifiers_base::process()
     */
    public function process($user, &$prepareuf) {
        global $DB, $CFG;
        if (stripos($this->fldname, 'user_field_') === 0) {
            $fldname = substr($this->fldname, 11);
            switch ($fldname) {
                case 'email':
                    // Генерация почтового адреса
                    $host = parse_url($CFG->wwwroot, PHP_URL_HOST);
                    if (empty($prepareuf->username)) {
                        // Имя пользователя со счетчиком
                        $username = $this->find_free_username();
                    } else {
                        $username = $prepareuf->username;
                    }
                    $prepareuf->email = $username . '@' . $host;
                    break;
                case 'password':
                    // Генерация пароля
                    $prepareuf->password = generate_password();
                    break;
                case 'username':
                    // Генерация логина на основе данных пользователя
                    // Попытка генерации на основе email
                    if (! empty($prepareuf->email)) {// Передан email пользователя
                        // Попытка сгенерировать имя пользователя на основе email
                        $emaillovercase = core_text::strtolower($prepareuf->email);
                        $emailusername = clean_param($emaillovercase, PARAM_USERNAME);
                        if ($emailusername === $emaillovercase) {
                            // Email можно использовать в качестве имени пользователя
                            // Проверка имени пользователя на наличие в системе
                            if (! $DB->record_exists('user', ['username' => $emailusername, 'deleted' => 0, 'mnethostid'=>$CFG->mnet_localhost_id])) {
                                // Имя пользователя свободно
                                $prepareuf->username = $emailusername;
                            }
                        } elseif (! empty($emailusername)) {// Генерация логина с учетом email
                            $emailusername = substr($emailusername, 0, strpos($emailusername, '@'));
                            if (! $DB->record_exists('user', ['username' => $emailusername, 'deleted' => 0, 'mnethostid'=>$CFG->mnet_localhost_id])) {
                                // Имя пользователя свободно
                                $prepareuf->username = $emailusername;
                            } else {// Имя пользователя зарезервировано, формирование логина со счетчиком
                                $counter = 1;
                                // Получение последнего аналогичного логина
                                $where = " username LIKE '$emailusername%' AND deleted = 0";
                                $lastusername = (array)$DB->get_records_select('user', $where, [], 'username DESC', 'id, username', 0, 1);
                                $lastusername = array_pop($lastusername);
                                if (isset($lastusername->username)) {// Имя пользователя найдено
                                    // Получение счетчика
                                    $counter = (int)str_replace($emailusername, '', $lastusername->username);
                                    $counter++;
                                }
                                // Имя пользователя со счетчиком
                                $prepareuf->username = $emailusername.$counter;
                            }
                        }
                    }
                    // Попытка генерации на основе номера телефона
                    if (empty($prepareuf->username) && ! empty($prepareuf->phone2)) {
                        // Передан телефон пользователя
                        $phoneusername = clean_param($prepareuf->phone2, PARAM_USERNAME);
                        if (! empty($phoneusername)) {
                            // Проверка имени пользователя на наличие в системе
                            if (! $DB->record_exists('user', ['username' => $phoneusername, 'deleted' => 0, 'mnethostid'=>$CFG->mnet_localhost_id])) {
                                // Имя пользователя свободно
                                $prepareuf->username = $phoneusername;
                            } else {
                                $prepareuf->username = $this->find_free_username($phoneusername . '_');
                            }
                        }
                    }
                    // Генерация случайного логина
                    if (empty($prepareuf->username)) {
                        // Имя пользователя со счетчиком
                        $prepareuf->username = $this->find_free_username();
                    }
                    if (empty($prepareuf->username)) {// Имя пользователя не получено
                        print_error('error_signup_username_not_generated', 'auth_email');
                    }
                    break;
                default:
                    print_error('Generation of field "' . $fldname . '" is not supported');
                    break;
            }
        }
    }
    
    /**
     * Поиск свободного логина с индексом по формату
     *
     * @param string $username - основа для логина (используется в качетсве префикса)
     * @param int $startindex - индекс, с которого предполагается начать поиск свободного логина
     *                              (можно оставить пустым, тогда начнет поиск с номера равного количеству записей,
     *                               начинающихся с префикса)
     * @return string - свободный логин
     */
    protected function find_free_username($username='username', $startindex=null)
    {
        global $DB;
        if (is_null($startindex)) {
            // Получение последнего аналогичного логина
            $usedindexes = (int)$DB->get_field_select(
                'user',
                'COUNT(username) as counter',
                " username LIKE '$username%' "
                );
            $index = $usedindexes + 1;
        } else {
            $index = $startindex;
        }
        if ($DB->record_exists('user', ['username' => $username.$index, 'deleted' => 0])) {
            return $this->find_free_username($username, ($index+1));
        } else {
            return $username.$index;
        }
    }
    
    /**
     * Валидация настроек на странице "Настройки полей формы регистрации"
     * 
     * @param array $data
     * @param string $fldname
     */
    public static function settings_validation(array $data, string $fldname) {
        $errors = [];
        // Поисковое поле не может быть генерируемым
        if (! empty($data['fld_' . $fldname . '_mod']['search'])) {
            $errors['group_' . $fldname]  = get_string(
                'generated_field_cannnot_be_search', 'auth_dof');
        }
        // Транслируемое поле не может быть генерируемым
        if (! empty($data['fld_' . $fldname . '_mod']['broadcast'])) {
            $errors['group_' . $fldname]  = get_string(
                'generated_field_cannnot_be_broadcast', 'auth_dof');
        }
        // Поле проверка уникальности не может быть генерируемым
        if (! empty($data['fld_' . $fldname . '_mod']['unique'])) {
            $errors['group_' . $fldname]  = get_string(
                'generated_field_cannnot_be_unique', 'auth_dof');
        }
        return $errors;
    }
    
    /**
     * Определяет будет ли можификатор отображаться на форме настроек
     *
     * @param string $fldname
     * @param array $srcconfigfields
     * @return boolean
     */
    static function display_on_settings_form(string $fldname, array $srcconfigfields) {
        $supportedflds = ['user_field_email', 'user_field_password', 'user_field_username'];
        if (! in_array($fldname, $supportedflds)) {
            return false;
        }
        return true;
    }
}