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
 * Юнит-тест сценария send_new_user_passwords
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_pprocessing_s_send_new_user_passwords_testcase extends advanced_testcase
{

    /**
     * @group pprocessing_scenario
     */
    public function test_scenario() {
        // Удалить все говно после себя
        $this->resetAfterTest(true);

        // включаем сбор сообщений
        unset_config('noemailever');
        $sink = $this->redirectEmails();

        // Отключаем стандартную задачу на генерацию и отправку паролей
        $task = new \core\task\send_new_user_passwords_task();
        $task->set_disabled(true);
        \core\task\manager::configure_scheduled_task($task);

        $user = new stdClass();
        $user->password = 'to be generated';
        // Create a user.
        $user = $this->getDataGenerator()->create_user($user);
        set_user_preference('create_password', 1, $user);

        $sink->clear();

        // Включаем сценарий
        set_config('send_new_user_passwords__status', true, 'local_pprocessing');
        set_config('send_user_password_message_subject', 'Новая учетная запись - Логин: %{user.username}, пароль: %{generated_code}', 'local_pprocessing');
        set_config('send_user_password_message_full', 'Здравствуйте, %{user.fullname}!

                                              На сайте «%{site.fullname}» для Вас была создана новая учетная запись с временным паролем.

                                              Сейчас Вы можете зайти на сайт так:
                                              Логин: %{user.username}
                                              Пароль: %{generated_code}
                                              (Вам придется сменить пароль при первом входе).

                                              Чтобы начать использование сайта «%{site.fullname}», пройдите по адресу %{site.loginurl}

                                              В большинстве почтовых программ этот адрес должен выглядеть как синяя ссылка, на которую достаточно нажать. Если это не так, просто скопируйте этот адрес и вставьте его в строку адреса в верхней части окна Вашего браузера.

                                              С уважением, администратор сайта «%{site.fullname}», %{site.signoff}', 'local_pprocessing');
        set_config('send_user_password_message_short', 'Логин: %{user.username}, пароль: %{generated_code}', 'local_pprocessing');
        set_config('send_user_password_auth_forcepasswordchange', 1, 'local_pprocessing');
        set_config('send_user_password_additional_password_settings', 1, 'local_pprocessing');
        set_config('send_user_password_p_maxlen', '4', 'local_pprocessing');
        set_config('send_user_password_p_numnumbers', '4', 'local_pprocessing');
        set_config('send_user_password_p_numsymbols', '0', 'local_pprocessing');
        set_config('send_user_password_p_lowerletters', '0', 'local_pprocessing');
        set_config('send_user_password_p_upperletters', '0', 'local_pprocessing');

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
        $this->assertEquals($user->username, $username, 'Проверка корректности username');
        preg_match('/пароль:\s([0-9]{4})?/', $message->subject, $matches);
        $password = $matches[1] ?? null;
        // Проверим авторизацию
        $auth = false;
        if (authenticate_user_login($username, $password)) {
            $auth = true;
        }
        // проверяем ожидания
        $this->assertEquals(true, $auth, 'Проверка авторизации по полученной паре логин-пароль');
        // получим пользователя повторно так-как get_user_preferences не делает запросов к базе
        $user = core_user::get_user_by_username($username);
        $forcepasswordchange = get_user_preferences('auth_forcepasswordchange', null, $user);
        // проверяем ожидания
        $this->assertEquals($forcepasswordchange, 1, 'Проверка, что пользователю требуется менять свой пароль при первом входе');

        $sink->clear();
        \local_pprocessing\event\asap_executed::create()->trigger();
        // проверяем ожидания
        $this->assertEquals(0, $sink->count(), 'Проверка, что пользователю повторно не уйдет никаких писем');

        // Влючаем стандартную задачу на генерацию и отправку паролей
        $task->set_disabled(false);
        \core\task\manager::configure_scheduled_task($task);

        $user = new stdClass();
        $user->password = 'to be generated';
        // Create a user.
        $user = $this->getDataGenerator()->create_user($user);
        set_user_preference('create_password', 1, $user);

        $sink->clear();

        \local_pprocessing\event\asap_executed::create()->trigger();

        // проверяем ожидания
        $this->assertEquals(0, $sink->count(), 'Проверка, что при включенной штатной задаче на генерацию и отправку паролей, наш сценарий не будет работать');
        /**
         * @todo добавить проверку отправки по смс, для этого нужно воспользоваться возможность получения статуса по айди сообщения
         */
    }
}