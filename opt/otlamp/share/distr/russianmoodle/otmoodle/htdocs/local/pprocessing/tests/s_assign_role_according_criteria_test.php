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
 * Юнит-тест сценария assign_role_according_criteria (Назначение или снятие роли пользователям согласно критериям)
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_pprocessing_s_assign_role_according_criteria_testcase extends advanced_testcase
{

    /**
     * Назначение или снятие роли пользователям согласно критериям
     * @group pprocessing_scenario
     */
    public function test_scenario() {
        global $CFG;
        // Подключим библиотеку пользователя
        require_once($CFG->dirroot . '/user/lib.php');
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
        $category2 = $this->getDataGenerator()->create_category();
        $cc1 = context_coursecat::instance($category1->id);
        $cc2 = context_coursecat::instance($category2->id);
        $cs = context_system::instance();
        // Делаем назначение в первой категории
        $this->getDataGenerator()->role_assign($role->id, $user2->id, $cc1);
        // Делаем назначение во второй категории
        $this->getDataGenerator()->role_assign($role->id, $user2->id, $cc2);
        // Делаем глобальное назначение роли
        $this->getDataGenerator()->role_assign($role->id, $user2->id);
        // Инициализация генератора плагина local_pprocessing
        $popgen = $this->getDataGenerator()->get_plugin_generator('local_pprocessing');
        // Create profile field.
        $profilefield = $popgen->create_profile_field();
        // Пишем в поле профиля пользователю 1
        profile_save_data((object)[
            'id' => $user1->id,
            'profile_field_'.$profilefield->shortname => 'manager'
        ]);
        // Пишем в поле профиля пользователю 2
        profile_save_data((object)[
            'id' => $user2->id,
            'profile_field_'.$profilefield->shortname => 'manager_not_equal'
        ]);
        // добавим обычное поле пользователям
        $user1->department = 'manager';
        $user2->department = 'manager_not_equal';
        // Обновляем пользователей
        user_update_user($user1, false, false);
        user_update_user($user2, false, false);

        // Выбор поля профиля
        set_config('assign_role_according_criteria__user_field', 'profile.' . $profilefield->shortname, 'local_pprocessing');
        // Выбор отношения к значению в поле профиля
        set_config('assign_role_according_criteria__field_ratio_variant', '=', 'local_pprocessing');
        // Значение поля профиля
        set_config('assign_role_according_criteria__user_field_value', 'manager', 'local_pprocessing');
        // Выбор назначаемой роли
        set_config('assign_role_according_criteria__assigned_role', $role->id, 'local_pprocessing');
        // Выбор уровня контекста для назначения роли - курс
        set_config('assign_role_according_criteria__context_level', CONTEXT_COURSECAT, 'local_pprocessing');
        // Выбор категорий для назначения роли
        set_config('assign_role_according_criteria__category', $category1->id, 'local_pprocessing');

        $test = function($unsetfield) use($user1, $user2, $cc1, $cc2, $cs) {
            // Выбрасываем событие обновления пользователя
            \core\event\user_updated::create_from_userid($user1->id)->trigger();
            \core\event\user_updated::create_from_userid($user2->id)->trigger();

            // Полученные данные должны совпадать с ожидаемыми
            $this->assertEquals(empty(get_user_roles($cc1, $user1->id, false)), true, "Тест отсутствия изменений по первой категории пользователя 1 ($unsetfield)");
            $this->assertEquals(empty(get_user_roles($cc1, $user2->id, false)), false, "Тест отсутствия изменений по первой категории пользователя 2 ($unsetfield)");
            $this->assertEquals(empty(get_user_roles($cc2, $user2->id, false)), false, "Тест отсутствия изменений по второй категории пользователя 2 ($unsetfield)");
            $this->assertEquals(empty(get_user_roles($cc2, $user1->id, false)), true, "Тест отсутствия изменений по второй категории пользователя 1 ($unsetfield)");
            $this->assertEquals(empty(get_user_roles($cs, $user1->id, false)), true, "Тест отсутствия изменений в системном контексте пользователя 1 ($unsetfield)");
            $this->assertEquals(empty(get_user_roles($cs, $user2->id, false)), false, "Тест отсутствия изменений в системном контексте пользователя 2 ($unsetfield)");
        };
        // Протестируем на выключеном сценарии
        call_user_func($test, 'status');
        // включаем сценарий
        set_config('assign_role_according_criteria__status', true, 'local_pprocessing');
        // Выбор поля профиля
        unset_config('assign_role_according_criteria__user_field', 'local_pprocessing');
        call_user_func($test, 'user_field');
        // Выбор поля профиля
        set_config('assign_role_according_criteria__user_field', 'profile.' . $profilefield->shortname, 'local_pprocessing');
        // Выбор отношения к значению в поле профиля
        unset_config('assign_role_according_criteria__field_ratio_variant', 'local_pprocessing');
        call_user_func($test, 'field_ratio_variant');
        // Выбор отношения к значению в поле профиля
        set_config('assign_role_according_criteria__field_ratio_variant', '=', 'local_pprocessing');
        // Выбор назначаемой роли
        unset_config('assign_role_according_criteria__assigned_role', 'local_pprocessing');
        call_user_func($test, 'assigned_role');
        // Выбор назначаемой роли
        set_config('assign_role_according_criteria__assigned_role', $role->id, 'local_pprocessing');
        // Выбор уровня контекста для назначения роли - курс
        unset_config('assign_role_according_criteria__context_level', 'local_pprocessing');
        call_user_func($test, 'context_level');
        // Выбор уровня контекста для назначения роли - курс
        set_config('assign_role_according_criteria__context_level', CONTEXT_COURSECAT, 'local_pprocessing');
        // Выбор категорий для назначения роли
        unset_config('assign_role_according_criteria__category', 'local_pprocessing');
        call_user_func($test, 'category');
        // Выбор категорий для назначения роли
        set_config('assign_role_according_criteria__category', $category1->id, 'local_pprocessing');


        // Выбрасываем событие обновления пользователя
        \core\event\user_updated::create_from_userid($user1->id)->trigger();
        \core\event\user_updated::create_from_userid($user2->id)->trigger();

        // Полученные данные должны совпадать с ожидаемыми
        $this->assertEquals(empty(get_user_roles($cc1, $user1->id, false)), false, 'Проверка назначение роли в контексте категории пользователю 1');
        $this->assertEquals(empty(get_user_roles($cc1, $user2->id, false)), true, 'Проверка снятие роли в контексте категории пользователю 2');
        $this->assertEquals(empty(get_user_roles($cc2, $user2->id, false)), false, 'Проверка не снятие роли в контексте второй категории пользователю 2');
        $this->assertEquals(empty(get_user_roles($cc2, $user1->id, false)), true, 'Проверка не назначение роли в контексте второй категории пользователю 1');
        // Выбор уровня контекста для назначения роли - система
        set_config('assign_role_according_criteria__context_level', CONTEXT_SYSTEM, 'local_pprocessing');
        // Выбрасываем событие обновления пользователя
        \core\event\user_updated::create_from_userid($user1->id)->trigger();
        \core\event\user_updated::create_from_userid($user2->id)->trigger();
        // Полученные данные должны совпадать с ожидаемыми
        $this->assertEquals(empty(get_user_roles($cs, $user1->id, false)), false, 'Проверка назначение роли в системном контексте пользователю 1');
        $this->assertEquals(empty(get_user_roles($cs, $user2->id, false)), true, 'Проверка снятие роли в системном контексте пользователю 2');
        $this->assertEquals(empty(get_user_roles($cc2, $user2->id, false)), false, 'Проверка не снятие роли в контексте второй категории пользователю 2 (тест системного контекста)');
        $this->assertEquals(empty(get_user_roles($cc2, $user1->id, false)), true, 'Проверка не назначение роли в контексте второй категории пользователю 1 (тест системного контекста)');
        $this->assertEquals(empty(get_user_roles($cc1, $user1->id, false)), false, 'Проверка не снятия роли в контексте категории пользователю 1 (тест системного контекста)');
        $this->assertEquals(empty(get_user_roles($cc1, $user2->id, false)), true, 'Проверка не назначение роли в контексте категории пользователю 2 (тест системного контекста)');
        // Выбор отношения к значению в поле профиля - содержит
        set_config('assign_role_according_criteria__field_ratio_variant', 'LIKE', 'local_pprocessing');
        \core\event\user_updated::create_from_userid($user2->id)->trigger();
        $this->assertEquals(empty(get_user_roles($cs, $user2->id, false)), false, 'Проверка назначение роли в системном контексте пользователю 2 (не строгое соответствие)');
        // Выбор отношения к значению в поле профиля - не содержит
        set_config('assign_role_according_criteria__field_ratio_variant', 'NOT LIKE', 'local_pprocessing');
        \core\event\user_updated::create_from_userid($user2->id)->trigger();
        \core\event\user_updated::create_from_userid($user1->id)->trigger();
        $this->assertEquals(empty(get_user_roles($cs, $user2->id, false)), true, 'Проверка снятия роли в системном контексте пользователю 2 (не строгое соответствие)');
        $this->assertEquals(empty(get_user_roles($cs, $user1->id, false)), true, 'Проверка снятия роли в системном контексте пользователю 1 (не строгое соответствие)');
        // Выбор отношения к значению в поле профиля - не равно
        set_config('assign_role_according_criteria__field_ratio_variant', '<>', 'local_pprocessing');
        \core\event\user_updated::create_from_userid($user2->id)->trigger();
        $this->assertEquals(empty(get_user_roles($cs, $user2->id, false)), false, 'Проверка назначение роли в системном контексте пользователю 2 (не равно)');
        // Выбор отношения к значению в поле профиля - равно
        set_config('assign_role_according_criteria__field_ratio_variant', '=', 'local_pprocessing');
        // Оставим у роли только контекст категории
        set_role_contextlevels($role->id, [CONTEXT_COURSECAT]);
        \core\event\user_updated::create_from_userid($user2->id)->trigger();
        $this->assertEquals(empty(get_user_roles($cs, $user2->id, false)), false, 'Проверка не снятия роли в системном контексте пользователю 2 (у роли нет системного контекста)');

        // Выбор поля профиля - department
        set_config('assign_role_according_criteria__user_field', 'department', 'local_pprocessing');
        // Выбор категорий для назначения роли
        set_config('assign_role_according_criteria__category', $category2->id, 'local_pprocessing');
        // Выбор уровня контекста для назначения роли - курс
        set_config('assign_role_according_criteria__context_level', CONTEXT_COURSECAT, 'local_pprocessing');
        // Выбрасываем событие обновления пользователя
        \core\event\user_created::create_from_userid($user1->id)->trigger();
        \core\event\user_created::create_from_userid($user2->id)->trigger();
        // Полученные данные должны совпадать с ожидаемыми
        $this->assertEquals(empty(get_user_roles($cc2, $user1->id, false)), false, 'Тестируем назначение роли по стандартному полю профиля с событием user_created пользователю 1');
        $this->assertEquals(empty(get_user_roles($cc2, $user2->id, false)), true, 'Тестируем снятие роли по стандартному полю профиля с событием user_created пользователю 2');
    }
}