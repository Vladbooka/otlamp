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
 * Юнит-тест сценария delete_cohorts_by_date (Удаление глобальных групп по дате из настраиваемых полей глобальной группы)
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_pprocessing_s_delete_cohorts_by_date_testcase extends advanced_testcase
{

    /**
     * Удаление глобальных групп по дате из настраиваемых полей глобальной группы
     * @group pprocessing_scenario
     */
    public function test_scenario() {
        global $DB;
        // Удалить все говно после себя
        $this->resetAfterTest(true);
        $code = 'cohort';
        $yamlcfg =
        'class:
           unenroldate:
              type: date_selector
              label: Срок завершения обучения группы
           deldate:
              type: date_selector
              label: Срок жизни группы
           submit:
              type: submit
              label: Сохранить';
        set_config($code . '_yaml', $yamlcfg, 'local_mcov');
        set_config('delete_cohorts_by_date__deldate', 'pub_deldate', 'local_pprocessing');
        set_config('delete_cohorts_by_date__status', 1, 'local_pprocessing');

        // Инициализация генератора плагина local_pprocessing
        $popgen = $this->getDataGenerator()->get_plugin_generator('local_pprocessing');
        $cohort1 = $popgen->create_cohort_with_idnumber();
        $cohort2 = $popgen->create_cohort_with_idnumber();

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $user4 = $this->getDataGenerator()->create_user();
        $user5 = $this->getDataGenerator()->create_user();
        $user6 = $this->getDataGenerator()->create_user();
        $user7 = $this->getDataGenerator()->create_user();
        $user8 = $this->getDataGenerator()->create_user();

        $cohortplugin = enrol_get_plugin('cohort');
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $this->assertNotEmpty($studentrole);
        $id1 = $cohortplugin->add_instance($course1, ['customint1' => $cohort1->id, 'roleid' => $studentrole->id]);
        $cohortinstance1 = $DB->get_record('enrol', ['id' => $id1]);
        $id2 = $cohortplugin->add_instance($course2, ['customint1' => $cohort2->id, 'roleid' => $studentrole->id]);
        $cohortinstance2 = $DB->get_record('enrol', ['id' => $id2]);

        cohort_add_member($cohort1->id, $user1->id);
        cohort_add_member($cohort1->id, $user2->id);
        cohort_add_member($cohort1->id, $user3->id);
        cohort_add_member($cohort1->id, $user4->id);
        cohort_add_member($cohort2->id, $user5->id);
        cohort_add_member($cohort2->id, $user6->id);
        cohort_add_member($cohort2->id, $user7->id);
        cohort_add_member($cohort2->id, $user8->id);

        \local_pprocessing\event\daily_executed::create()->trigger();
        // Способы записи, группы и членство в группах дожны остаться прежними
        $this->assertEquals(8, $DB->count_records('user_enrolments', []));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance1->id, 'userid' => $user1->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance1->id, 'userid' => $user2->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance1->id, 'userid' => $user3->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance1->id, 'userid' => $user4->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance2->id, 'userid' => $user5->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance2->id, 'userid' => $user6->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance2->id, 'userid' => $user7->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance2->id, 'userid' => $user8->id]));
        $this->assertTrue(cohort_is_member($cohort1->id, $user1->id));
        $this->assertTrue(cohort_is_member($cohort1->id, $user2->id));
        $this->assertTrue(cohort_is_member($cohort1->id, $user3->id));
        $this->assertTrue(cohort_is_member($cohort1->id, $user4->id));
        $this->assertTrue(cohort_is_member($cohort2->id, $user5->id));
        $this->assertTrue(cohort_is_member($cohort2->id, $user6->id));
        $this->assertTrue(cohort_is_member($cohort2->id, $user7->id));
        $this->assertTrue(cohort_is_member($cohort2->id, $user8->id));
        $this->assertTrue($DB->record_exists('cohort', ['id' => $cohort1->id]));
        $this->assertTrue($DB->record_exists('cohort', ['id' => $cohort2->id]));

        // Выставляем дату в будущем
        $mcov1 = new stdClass();
        $mcov1->entity = $code;
        $mcov1->objid = $cohort1->id;
        $mcov1->prop = 'pub_deldate';
        $mcov1->value = $mcov1->searchval = $mcov1->sortval = strtotime('+1 month');
        $mcov1->id = $DB->insert_record('local_mcov', $mcov1);

        \local_pprocessing\event\daily_executed::create()->trigger();
        // Способы записи, группы и членство в группах дожны остаться прежними
        $this->assertEquals(8, $DB->count_records('user_enrolments', []));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance1->id, 'userid' => $user1->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance1->id, 'userid' => $user2->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance1->id, 'userid' => $user3->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance1->id, 'userid' => $user4->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance2->id, 'userid' => $user5->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance2->id, 'userid' => $user6->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance2->id, 'userid' => $user7->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance2->id, 'userid' => $user8->id]));
        $this->assertTrue(cohort_is_member($cohort1->id, $user1->id));
        $this->assertTrue(cohort_is_member($cohort1->id, $user2->id));
        $this->assertTrue(cohort_is_member($cohort1->id, $user3->id));
        $this->assertTrue(cohort_is_member($cohort1->id, $user4->id));
        $this->assertTrue(cohort_is_member($cohort2->id, $user5->id));
        $this->assertTrue(cohort_is_member($cohort2->id, $user6->id));
        $this->assertTrue(cohort_is_member($cohort2->id, $user7->id));
        $this->assertTrue(cohort_is_member($cohort2->id, $user8->id));
        $this->assertTrue($DB->record_exists('cohort', ['id' => $cohort1->id]));
        $this->assertTrue($DB->record_exists('cohort', ['id' => $cohort2->id]));

        // Выставляем дату в прошлом
        $mcov1->value = $mcov1->searchval = $mcov1->sortval = strtotime('-1 month');
        $DB->update_record('local_mcov', $mcov1);
        // Удаляем конфигурацию кастомных полей ГГ
        unset_config('cohort_yaml', 'local_mcov');

        \local_pprocessing\event\daily_executed::create()->trigger();
        // Способы записи, группы и членство в группах дожны остаться прежними
        $this->assertEquals(8, $DB->count_records('user_enrolments', []));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance1->id, 'userid' => $user1->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance1->id, 'userid' => $user2->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance1->id, 'userid' => $user3->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance1->id, 'userid' => $user4->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance2->id, 'userid' => $user5->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance2->id, 'userid' => $user6->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance2->id, 'userid' => $user7->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance2->id, 'userid' => $user8->id]));
        $this->assertTrue(cohort_is_member($cohort1->id, $user1->id));
        $this->assertTrue(cohort_is_member($cohort1->id, $user2->id));
        $this->assertTrue(cohort_is_member($cohort1->id, $user3->id));
        $this->assertTrue(cohort_is_member($cohort1->id, $user4->id));
        $this->assertTrue(cohort_is_member($cohort2->id, $user5->id));
        $this->assertTrue(cohort_is_member($cohort2->id, $user6->id));
        $this->assertTrue(cohort_is_member($cohort2->id, $user7->id));
        $this->assertTrue(cohort_is_member($cohort2->id, $user8->id));
        $this->assertTrue($DB->record_exists('cohort', ['id' => $cohort1->id]));
        $this->assertTrue($DB->record_exists('cohort', ['id' => $cohort2->id]));

        // Снова выставляем конфигурацию кастомных полей ГГ
        set_config($code . '_yaml', $yamlcfg, 'local_mcov');

        \local_pprocessing\event\daily_executed::create()->trigger();
        // Способы записи и группы должны быть удалены
        $this->assertEquals(4, $DB->count_records('user_enrolments', []));
        $this->assertFalse($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance1->id, 'userid' => $user1->id]));
        $this->assertFalse($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance1->id, 'userid' => $user2->id]));
        $this->assertFalse($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance1->id, 'userid' => $user3->id]));
        $this->assertFalse($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance1->id, 'userid' => $user4->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance2->id, 'userid' => $user5->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance2->id, 'userid' => $user6->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance2->id, 'userid' => $user7->id]));
        $this->assertTrue($DB->record_exists('user_enrolments', ['enrolid' => $cohortinstance2->id, 'userid' => $user8->id]));
        $this->assertFalse(cohort_is_member($cohort1->id, $user1->id));
        $this->assertFalse(cohort_is_member($cohort1->id, $user2->id));
        $this->assertFalse(cohort_is_member($cohort1->id, $user3->id));
        $this->assertFalse(cohort_is_member($cohort1->id, $user4->id));
        $this->assertTrue(cohort_is_member($cohort2->id, $user5->id));
        $this->assertTrue(cohort_is_member($cohort2->id, $user6->id));
        $this->assertTrue(cohort_is_member($cohort2->id, $user7->id));
        $this->assertTrue(cohort_is_member($cohort2->id, $user8->id));
        $this->assertFalse($DB->record_exists('cohort', ['id' => $cohort1->id]));
        $this->assertTrue($DB->record_exists('cohort', ['id' => $cohort2->id]));
    }
}