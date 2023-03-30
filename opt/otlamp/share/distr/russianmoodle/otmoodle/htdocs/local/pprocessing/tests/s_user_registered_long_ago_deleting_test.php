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
 * Юнит-тест сценария user_registered_long_ago_deleting (Удаление ни разу не авторизовавшихся пользователей, с момента регистрации которых прошло два месяца)
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_pprocessing_s_user_registered_long_ago_deleting_testcase extends advanced_testcase
{
    /**
     * Удаление ни разу не авторизовавшихся пользователей, с момента регистрации которых прошло два месяца
     * @group pprocessing_scenario
     */
    public function test_scenario()
    {
        global $DB;

        $this->resetAfterTest(true);

        // включаем сценарий
        set_config('user_registered_long_ago_deleting__status', true, 'local_pprocessing');

        // выбрасываем событие, в результате чего должен запуститься сценарий,
        // который должен удалить пользователей, если требуется,
        // чтобы дальнейшие манипуляции происходили уже с нашими данными
        \local_pprocessing\event\daily_executed::create()->trigger();
        // очищаем логи для удобства отладки
        $DB->delete_records('local_pprocessing_logs');

        // функция создает пользователя по переданным параметрам
        // запускает сценарий удаления пользователей, зарегистрированных два месяца назад и до сих пор не зашедших в систему
        // проверяет результат работы сценарий на соответствие ожиданию, переданному в параметре
        $test = function($userrecord, $expected, $message='') {
            global $DB;

            $desiredconfirmed = $userrecord['confirmed'];
            unset($userrecord['confirmed']);
            // создадим пользователя в соответствии с переданными свойствами
            $user = $this->getDataGenerator()->create_user($userrecord);
            $DB->update_record('user', ['id' => $user->id, 'confirmed' => $desiredconfirmed]);

            // выбрасываем событие, в результате чего должен запуститься сценарий,
            // который должен удалить пользователя, если требуется
            \local_pprocessing\event\daily_executed::create()->trigger();

            // получим заново пользователя, чтобы проверить результаты работы сценария
            $user = $DB->get_record('user', array('id' => $user->id), '*', MUST_EXIST);

            // проверяем ожидания
            $this->assertEquals($expected, $user->deleted, $message);

            return $user;
        };

        // проверим пользователя, который подходит под сценарий и должен быть удален
        $userrecord = [
            'confirmed' => 1,
            'firstaccess' => 0,
            'timecreated' =>  strtotime('-2 months, -1 hours'),
            'deleted' => 0
        ];
        call_user_func($test, $userrecord, 1, 'Проверка сценария при исполнении всех условий');

        // проверим, что если у пользователя в confirmed не подходящее под сценарий условие, то он не удалится
        $negativeconfirmed = $userrecord;
        $negativeconfirmed['confirmed'] = -1;
        call_user_func($test, $negativeconfirmed, 0, 'Проверка сценария, когда условие confirmed >= 0 не исполняется');

        // проверим, что если пользоватль уже входил, то он не удалится
        $hasfirstaccess = $userrecord;
        $hasfirstaccess['firstaccess'] = strtotime('-1 months');
        call_user_func($test, $hasfirstaccess, 0, 'Проверка сценария, когда условие firstaccess = 0 не исполняется');

        // проверим, что если с момента создания еще не прошло 2 месяца, то пользователь не будет удален
        $freshtimecreated = $userrecord;
        $freshtimecreated['timecreated'] = strtotime('-2 months, +1 hours');
        call_user_func($test, $freshtimecreated, 0, 'Проверка сценария, когда условие timecreated <= strtotime(\'-2 months\') не исполняется');

        // проверим, что если пользователь уже был удален, то он так и останется удаленным
        $deleted = $userrecord;
        $deleted['deleted'] = 1;
        call_user_func($test, $deleted, 1, 'Проверка сценария, когда условие deleted = 0 не исполняется');

    }
}