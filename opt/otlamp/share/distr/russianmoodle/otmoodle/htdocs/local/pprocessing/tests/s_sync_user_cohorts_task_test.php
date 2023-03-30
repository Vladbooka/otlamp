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
 * Юнит-тест сценария sync_user_cohorts_task (Синхронизация пользователей с глобальными группами по расписанию)
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_pprocessing_s_sync_user_cohorts_task_testcase extends advanced_testcase
{
    /**
     * Синхронизация пользователей с глобальными группами по расписанию
     * @dataProvider scenario_provider
     * @group pprocessing_scenario
     * @param string $fieldname - название поля профиля пользователя
     * @param string $cid - поле, по которому требуется идентифицировать группу
     * @param string $mode - режим ручного изменения
     */
    public function test_scenario($fieldname, $cid, $mode) {

        // Удалить все говно после себя
        $this->resetAfterTest(true);

        unset_config('sync_user_cohorts__status', 'local_pprocessing');
        unset_config('user_cohorts', 'local_pprocessing');
        unset_config('cohort_identifier', 'local_pprocessing');
        unset_config('cohorts_manage_mode', 'local_pprocessing');
        unset_config('sync_user_cohorts_task__status', 'local_pprocessing');

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
        $cohort3 = $popgen->create_cohort_with_idnumber();
        $cohort4 = $popgen->create_cohort_with_idnumber();



        // Создаем пользователя и пишем в поле профиля две группы
        $cids = $cohort1->{$cid}.','.$cohort2->{$cid};
        $user = $this->getDataGenerator()->create_user([$fieldname => $cids]);
        // Trigger event if required.
        \core\event\user_updated::create_from_userid($user->id)->trigger();

        // Добавляем в группу вручную
        cohort_add_member($cohort2->id, $user->id);
        // Добавляем в группу вручную
        cohort_add_member($cohort3->id, $user->id);

        // включаем сценарий
        set_config('sync_user_cohorts__status', true, 'local_pprocessing');
        // Выбираем поле в настройках
        set_config('user_cohorts', $fieldname, 'local_pprocessing');
        // Указываем где искать группы
        set_config('cohort_identifier', $cid, 'local_pprocessing');
        // Запрет на ручное изменение состава групп
        set_config('cohorts_manage_mode', $mode, 'local_pprocessing');
        // включаем сценарий
        set_config('sync_user_cohorts_task__status', true, 'local_pprocessing');

        \local_pprocessing\event\daily_executed::create()->trigger();

        // Получим и отсортируем идентификаторы групп, в которых состоит пользователь
        $usercohorts = array_keys(cohort_get_user_cohorts($user->id));
        sort($usercohorts, SORT_NUMERIC);
        // Полученные данные должны совпадать с ожидаемыми
        switch ($mode) {
            case 'enable':
                $this->assertEquals([$cohort1->id, $cohort2->id, $cohort3->id], $usercohorts);
                break;
            case 'disable':
                $this->assertEquals([$cohort1->id, $cohort2->id], $usercohorts);
                break;
        }
    }

    public function scenario_provider() {

        $fieldnames = ['department', 'profile_field_example'];
        $cids = ['name', 'id', 'idnumber'];
        $modes = ['disable', 'enable'];

        $finaltests = [];
        foreach($fieldnames as $fieldname) {
            foreach($cids as $cid) {
                foreach($modes as $mode) {
                    $key = 'Поле :'.$fieldname.'; Идентификация группы: '.$cid.'; Ручные изменения: '.$mode;
                    $finaltests[$key] = [
                        'fieldname' => $fieldname,
                        'cid' => $cid,
                        'mode' => $mode
                    ];
                }
            }
        }
        return $finaltests;
    }
}