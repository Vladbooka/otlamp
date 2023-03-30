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
 * Юнит-тест сценария user_registered_recently (Уведомление ни разу не авторизовавшегося пользователя о недавней регистрации)
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_pprocessing_s_user_registered_recently_testcase extends advanced_testcase
{

    /**
     * Уведомление ни разу не авторизовавшегося пользователя о недавней регистрации
     * @group pprocessing_scenario
     */
    public function test_scenario() {
        global $DB;

        $this->resetAfterTest(true);

        // включаем сбор сообщений
        unset_config('noemailever');
        $sink = $this->redirectEmails();

        // включаем сценарий
        set_config('user_registered_recently__status', true, 'local_pprocessing');
        set_config('user_registered_recently__message_subject', 'Тема письма', 'local_pprocessing');
        set_config('user_registered_recently__message_full', 'Полный текст письма', 'local_pprocessing');
        set_config('user_registered_recently__message_short', 'Короткий текст письма', 'local_pprocessing');


        // выбрасываем событие, в результате чего должен запуститься сценарий,
        // который должен отправить пользователям предупреждение, если требуется,
        // чтобы дальнейшие манипуляции происходили уже с нашими данными
        \local_pprocessing\event\daily_executed::create()->trigger();
        // очищаем логи для удобства отладки
        $DB->delete_records('local_pprocessing_logs');

        // функция создает пользователя по переданным параметрам
        // запускает сценарий уведомления пользователей, зарегистрированных два месяца назад и до сих пор не зашедших в систему
        // проверяет результат работы сценария на соответствие ожиданию, переданному в параметре
        $test = function($userrecord, $expected, $message='') use ($sink) {
            global $DB;

            $desiredconfirmed = $userrecord['confirmed'];
            unset($userrecord['confirmed']);
            // создадим пользователя в соответствии с переданными свойствами
            $user = $this->getDataGenerator()->create_user($userrecord);
            $DB->update_record('user', ['id' => $user->id, 'confirmed' => $desiredconfirmed]);

            $sink->clear();

            // выбрасываем событие, в результате чего должен запуститься сценарий,
            // который должен отправить уведомление пользователю, если требуется
            \local_pprocessing\event\daily_executed::create()->trigger();

            // проверяем ожидания
            $this->assertEquals($expected, $sink->count(), $message);

            return $user;
        };

        // проверим пользователя, который подходит под сценарий - должно быть отправлено одно уведомление
        $userrecord = [
            'confirmed' => 1,
            'firstaccess' => 0,
            'timecreated' =>  strtotime('-7 days, -1 hours'),
            'deleted' => 0
        ];
        call_user_func($test, $userrecord, 1, 'Проверка сценария при исполнении всех условий');

        // Проверка повторного запуска сценария, второй раз ничего не должно никому отправиться
        $sink->clear();
        \local_pprocessing\event\daily_executed::create()->trigger();
        $this->assertEquals(0, $sink->count(), 'Проверка повторного запуска сценария, второй раз ничего не должно никому отправиться');

        // проверим, что если не была настроена тема письма, то письмо не будет отправляться
        set_config('user_registered_recently__message_subject', '', 'local_pprocessing');
        call_user_func($test, $userrecord, 0, 'Проверка сценария при исполнении всех условий, но отсутствии темы письма');
        set_config('user_registered_recently__message_subject', 'Тема письма', 'local_pprocessing');

        // проверим, что если не был настроен полный текст письма, то письмо не будет отправляться
        set_config('user_registered_recently__message_full', '', 'local_pprocessing');
        call_user_func($test, $userrecord, 0, 'Проверка сценария при исполнении всех условий, но отсутствии полного текста письма');
        set_config('user_registered_recently__message_full', 'Полный текст письма', 'local_pprocessing');

        // выбрасываем событие, в результате чего должен запуститься сценарий,
        // который должен все-таки отправить пользователям, созданным в последних двух кейсах
        // предупреждение, так как теперь тема и полный текст письма - заданы
        // и предыдущие кейсы не должны повлиять на дальнейшее тестирование
        \local_pprocessing\event\daily_executed::create()->trigger();

        // проверим, что если у пользователя в confirmed не подходящее под сценарий условие, то ему уведомление не отправится
        $negativeconfirmed = $userrecord;
        $negativeconfirmed['confirmed'] = -1;
        call_user_func($test, $negativeconfirmed, 0, 'Проверка сценария, когда условие confirmed >= 0 не исполняется');

        // проверим, что если пользоватль уже входил, то ему уведомление не отправится
        $hasfirstaccess = $userrecord;
        $hasfirstaccess['firstaccess'] = strtotime('-1 hours');
        call_user_func($test, $hasfirstaccess, 0, 'Проверка сценария, когда условие firstaccess = 0 не исполняется');

        // проверим, что если с момента создания еще не прошло 2 месяца, то пользователю уведомление не отправится
        $freshtimecreated = $userrecord;
        $freshtimecreated['timecreated'] = strtotime('-7 days, +1 hours');
        call_user_func($test, $freshtimecreated, 0, 'Проверка сценария, когда условие timecreated <= strtotime(\'-7 days\') не исполняется');

        // проверим, что если пользователь уже был удален, то ему уведомление не отправится
        $deleted = $userrecord;
        $deleted['deleted'] = 1;
        call_user_func($test, $deleted, 0, 'Проверка сценария, когда условие deleted = 0 не исполняется');
    }

}