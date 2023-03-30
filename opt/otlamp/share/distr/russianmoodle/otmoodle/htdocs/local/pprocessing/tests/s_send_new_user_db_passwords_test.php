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
 * Юнит-тест сценария send_new_user_db_passwords
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_pprocessing_s_send_new_user_db_passwords_testcase extends advanced_testcase
{

    /**
     * @group pprocessing_scenario
     */
    public function test_scenario() {
        global $CFG, $DB;
        // подключим библиотеку крона
        require_once($CFG->libdir . '/cronlib.php');
        // Удалить все говно после себя
        $this->resetAfterTest(true);
        // включаем сбор сообщений
        unset_config('noemailever');
        $sink = $this->redirectEmails();
        //создадим таблицу
        $dbman = $DB->get_manager();
        // таблица для auth_db
        $table = new xmldb_table('auth_db_ext_table');
        // поля
        $fields = [];
        $fields[] = new xmldb_field('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $fields[] = new xmldb_field('username', XMLDB_TYPE_CHAR, 100, null, XMLDB_NOTNULL, null, null, 'id');
        $fields[] = new xmldb_field('password', XMLDB_TYPE_CHAR, 100, null, XMLDB_NOTNULL, null, null, 'username');
        $fields[] = new xmldb_field('firstname', XMLDB_TYPE_CHAR, 100, null, XMLDB_NOTNULL, null, null, 'password');
        $fields[] = new xmldb_field('lastname', XMLDB_TYPE_CHAR, 100, null, XMLDB_NOTNULL, null, null, 'firstname');
        $fields[] = new xmldb_field('email', XMLDB_TYPE_CHAR, 100, null, XMLDB_NOTNULL, null, null, 'lastname');
        // ключи
        $keys = [];
        $keys[] = new xmldb_key('id', XMLDB_KEY_PRIMARY, ['id']);
        $table->setFields($fields);
        $table->setKeys($keys);
        // создаем таблицу
        if ( ! $dbman->table_exists($table) ) {
            $dbman->create_table($table);
        }
        //заполним таблицу
        $dataset = $this->createXMLDataSet(__DIR__ . '/dataset/auth_db_dataset.xml');
        $this->loadDataSet($dataset);
        // включаем синхронизацию с внешней базой данных
        set_config('host', $CFG->dbhost, 'auth_db');
        set_config('type', $CFG->dbtype, 'auth_db');
        set_config('sybasequoting', 0, 'auth_db');
        set_config('name', $CFG->dbname, 'auth_db');
        set_config('user', $CFG->dbuser, 'auth_db');
        set_config('pass', $CFG->dbpass, 'auth_db');
        set_config('table', $CFG->phpunit_prefix . 'auth_db_ext_table', 'auth_db');
        set_config('fielduser', 'username', 'auth_db');
        set_config('fieldpass', 'password', 'auth_db');
        set_config('passtype', 'internal', 'auth_db');
        set_config('extencoding', 'utf-8', 'auth_db');
        set_config('setupsql', 'SET NAMES \'utf8\'', 'auth_db');
        // имя
        set_config('field_map_firstname', 'firstname', 'auth_db');
        set_config('field_updatelocal_firstname', 'onlogin', 'auth_db');
        set_config('field_updateremote_firstname', 0, 'auth_db');
        set_config('field_lock_firstname', 'unlocked', 'auth_db');
        // фамилия
        set_config('field_map_lastname', 'lastname', 'auth_db');
        set_config('field_updatelocal_lastname', 'onlogin', 'auth_db');
        set_config('field_updateremote_lastname', 0, 'auth_db');
        set_config('field_lock_lastname', 'unlocked', 'auth_db');
        // емаил
        set_config('field_map_email', 'email', 'auth_db');
        set_config('field_updatelocal_email', 'onlogin', 'auth_db');
        set_config('field_updateremote_email', 0, 'auth_db');
        set_config('field_lock_email', 'unlocked', 'auth_db');
        // включаем плагин
        set_config('auth', 'db');

        // Выполним синхронизацию с внешней базой данных
        $auth = get_auth_plugin('db');
        $trace = new null_progress_trace();
        $auth->sync_users($trace, false);

        // Включаем сценарий
        set_config('send_user_db_password_password_type', 'plaintext', 'local_pprocessing');
        set_config('send_new_user_db_passwords__status', true, 'local_pprocessing');
        set_config('send_new_user_db_passwords_send_message', true, 'local_pprocessing');
        set_config('send_user_db_password_message_subject', 'Новая учетная запись - Логин: %{user.username}, пароль: %{extdbpassworld}', 'local_pprocessing');
        set_config('send_user_db_password_message_full', 'Здравствуйте, %{user.fullname}!
            На сайте «%{site.fullname}» для Вас была создана новая учетная запись с временным паролем.
            Сейчас Вы можете зайти на сайт так: Логин: %{user.username} Пароль: %{extdbpassworld} (Вам придется сменить пароль при первом входе).
            Чтобы начать использование сайта «%{site.fullname}», пройдите по адресу %{site.loginurl}
            В большинстве почтовых программ этот адрес должен выглядеть как синяя ссылка, на которую достаточно нажать.
            Если это не так, просто скопируйте этот адрес и вставьте его в строку адреса в верхней части окна Вашего браузера.
            С уважением, администратор сайта «%{site.fullname}», %{site.signoff}', 'local_pprocessing');
        set_config('send_user_db_password_message_short', 'Логин: %{user.username}, пароль: %{extdbpassworld}', 'local_pprocessing');
        set_config('send_user_db_password_auth_forcepasswordchange', 1, 'local_pprocessing');

        // Влючаем стандартную задачу на генерацию и отправку паролей
        $task = new \core\task\send_new_user_passwords_task();
        $task->set_disabled(false);
        \core\task\manager::configure_scheduled_task($task);
        $sink->clear();
        // Выполним сценарий
        \local_pprocessing\event\asap_executed::create()->trigger();
        // проверяем ожидания
        $this->assertEquals(0, $sink->count(), 'Проверка, что при включенной штатной задаче на генерацию и отправку паролей, наш сценарий не будет работать');

        // Отключаем стандартную задачу на генерацию и отправку паролей
        $task = new \core\task\send_new_user_passwords_task();
        $task->set_disabled(true);
        \core\task\manager::configure_scheduled_task($task);
        $sink->clear();
        // Выполним сценарий
        \local_pprocessing\event\asap_executed::create()->trigger();
        // проверяем ожидания
        $this->assertEquals(1, $sink->count(), 'Проверка получения письма логином и паролем');
        // Вытащим текст письма
        $messages = $sink->get_messages();
        $message = array_shift($messages);
        // Извлечем отпралвенные логин и пароль
        preg_match('/Логин:\s([a-z0-9_]+)?/', $message->subject, $matches);
        $username = $matches[1] ?? null;
        // проверяем ожидания
        $this->assertEquals('unit_test_user', $username, 'Проверка корректности username');
        preg_match('/пароль:\s([a-z0-9_]+)?/', $message->subject, $matches);
        $password = $matches[1] ?? null;
        // Проверим авторизацию
        $auth = false;
        if (authenticate_user_login($username, $password)) {
            $auth = true;
        }
        // получим пользователя
        $user = core_user::get_user_by_username($username);
        // проверяем ожидания
        $this->assertEquals(true, $auth, 'Проверка авторизации по полученной паре логин-пароль');
        $forcepasswordchange = get_user_preferences('auth_forcepasswordchange', null, $user);
        // проверяем ожидания
        $this->assertEquals($forcepasswordchange, 1, 'Проверка, что пользователю нужно сменить свой пароль при первом входе');

        $sink->clear();
        \local_pprocessing\event\asap_executed::create()->trigger();
        // проверяем ожидания
        $this->assertEquals(0, $sink->count(), 'Проверка, что пользователю повторно не уйдет никаких писем');
    }

}