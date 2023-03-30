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
 * Юнит-тест сценария sync_user_cohorts (Синхронизация пользователей с глобальными группами)
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_pprocessing_s_sync_user_cohorts_testcase extends advanced_testcase
{

    /**
     * Синхронизация пользователей с глобальными группами
     * @dataProvider scenario_provider
     * @group pprocessing_scenario
     */
    public function test_scenario($fieldname, $cid) {
        // Удалить все говно после себя
        $this->resetAfterTest(true);

        unset_config('sync_user_cohorts__status', 'local_pprocessing');
        unset_config('user_cohorts', 'local_pprocessing');
        unset_config('cohort_identifier', 'local_pprocessing');
        unset_config('cohorts_manage_mode', 'local_pprocessing');

        // Инициализация генератора плагина local_pprocessing
        $popgen = $this->getDataGenerator()->get_plugin_generator('local_pprocessing');

        // Создание поля профиля при необходимости
        if (strpos($fieldname, 'profile_field_') === 0) {
            $popgen->create_profile_field([
                'shortname' => mb_substr($fieldname, 14)
            ]);
        }

        // Создание глобальных групп
        $cohort1 = $popgen->create_cohort_with_idnumber();
        $cohort2 = $popgen->create_cohort_with_idnumber();

        // включаем сценарий
        set_config('sync_user_cohorts__status', true, 'local_pprocessing');
        // Выбираем поле в настройках
        set_config('user_cohorts', $fieldname, 'local_pprocessing');
        // Указываем где искать группы
        set_config('cohort_identifier', $cid, 'local_pprocessing');
        // Запрет на ручное изменение состава групп
        set_config('cohorts_manage_mode', 'disable', 'local_pprocessing');




        // Создаем пользователя и пишем в поле профиля две группы
        $cids = $cohort1->{$cid}.','.$cohort2->{$cid};
        $user = $this->getDataGenerator()->create_user([$fieldname => $cids]);

        // Trigger event if required.
        \core\event\user_updated::create_from_userid($user->id)->trigger();

        // Получим и отсортируем идентификаторы групп, в которых состоит пользователь
        $usercohorts = array_keys(cohort_get_user_cohorts($user->id));
        sort($usercohorts, SORT_NUMERIC);
        // Полученные данные должны совпадать с ожидаемыми
        $this->assertEquals([$cohort1->id, $cohort2->id], $usercohorts);




        // Убираем из поля профиля одну группу
        $popgen->save_user_data([
            'id' => $user->id,
            $fieldname => $cohort2->{$cid}
        ]);
        // Trigger event if required.
        \core\event\user_updated::create_from_userid($user->id)->trigger();

        // Получим и отсортируем идентификаторы групп, в которых состоит пользователь
        $usercohorts = array_keys(cohort_get_user_cohorts($user->id));
        sort($usercohorts, SORT_NUMERIC);
        // Полученные данные должны совпадать с ожидаемыми
        $this->assertEquals([$cohort2->id], $usercohorts);




        // Create cohort
        $cohort3 = $popgen->create_cohort_with_idnumber();
        // Добавляем в группу вручную
        cohort_add_member($cohort3->id, $user->id);

        // Получим и отсортируем идентификаторы групп, в которых состоит пользователь
        $usercohorts = array_keys(cohort_get_user_cohorts($user->id));
        sort($usercohorts, SORT_NUMERIC);
        // Полученные данные должны совпадать с ожидаемыми - пользователь должен остаться в тех группах, в которых был
        $this->assertEquals([$cohort2->id], $usercohorts);




        // Разрешаем ручное изменение состава групп
        set_config('cohorts_manage_mode', 'enable', 'local_pprocessing');
        // Добавляем в группу вручную
        cohort_add_member($cohort3->id, $user->id);

        // Получим и отсортируем идентификаторы групп, в которых состоит пользователь
        $usercohorts = array_keys(cohort_get_user_cohorts($user->id));
        sort($usercohorts, SORT_NUMERIC);
        // Полученные данные должны совпадать с ожидаемыми
        $this->assertEquals([$cohort2->id, $cohort3->id], $usercohorts);

    }

    public function scenario_provider() {

        $fieldnames = ['department', 'profile_field_example'];
        $cids = ['name', 'id', 'idnumber'];

        $finaltests = [];
        foreach($fieldnames as $fieldname) {
            foreach($cids as $cid) {
                $key = 'Поле :'.$fieldname.'; Идентификация группы: '.$cid.';';
                $finaltests[$key] = [
                    'fieldname' => $fieldname,
                    'cid' => $cid
                ];
            }
        }
        return $finaltests;
    }
}