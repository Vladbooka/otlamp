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
 * Юнит-тест сценария role_unassign (Снятие назначенных ролей)
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_pprocessing_s_role_unassign_testcase extends advanced_testcase
{

    /**
     * Снятие назначенных ролей
     * @group pprocessing_scenario
     */
    public function test_scenario() {
        // Удалить все говно после себя
        $this->resetAfterTest(true);

        // Create a user.
        $user1 = $this->getDataGenerator()->create_user();
        // Create a user.
        $user2 = $this->getDataGenerator()->create_user();

        $role = new stdClass();
        $role->archetype = 'manager';
        $role->id = $this->getDataGenerator()->create_role($role);

        $category1 = $this->getDataGenerator()->create_category();
        $cc1 = context_coursecat::instance($category1->id);
        $category2 = $this->getDataGenerator()->create_category();
        $cc2 = context_coursecat::instance($category2->id);

        // Делаем назначение в первой категории
        $this->getDataGenerator()->role_assign($role->id, $user1->id, $cc1);
        $this->getDataGenerator()->role_assign($role->id, $user2->id, $cc1);

        // Делаем назначение во второй категории
        $this->getDataGenerator()->role_assign($role->id, $user1->id, $cc2);
        $this->getDataGenerator()->role_assign($role->id, $user2->id, $cc2);

        // Делаем глобальное назначение роли
        $sra1 = $this->getDataGenerator()->role_assign($role->id, $user1->id);
        $sra2 = $this->getDataGenerator()->role_assign($role->id, $user2->id);

        // включаем сценарий
        set_config('role_unassign__status', true, 'local_pprocessing');
        // Выбираем контекст категории
        set_config('role_unassign_context', 'coursecat', 'local_pprocessing');
        // Ищем созданную роль
        set_config('role_unassign_role', $role->id, 'local_pprocessing');

        // Выбрасываем событие на запуск сценария
        \local_pprocessing\event\daily_executed::create()->trigger();

        // Полученные данные должны совпадать с ожидаемыми
        $this->assertEquals(get_users_from_role_on_context($role, $cc1), [], 'Проверка снятия назначения ролей в контексте первой категории');// Назначения сняты
        $this->assertEquals(get_users_from_role_on_context($role, $cc2), [], 'Проверка снятия назначения ролей в контексте второй категории');// Назначения сняты
        $ra = array_keys(get_users_from_role_on_context($role, context_system::instance()));
        asort($ra);
        $this->assertEquals($ra, [$sra1, $sra2], 'Проверка, что назначения ролей в системном контексте остались');// Назначения остались
    }
}