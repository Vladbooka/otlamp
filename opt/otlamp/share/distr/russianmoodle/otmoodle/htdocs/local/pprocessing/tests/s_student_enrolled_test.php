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
 * Юнит-тест сценария student_enrolled (Уведомление слушателя о подписке на курс)
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_pprocessing_s_student_enrolled_testcase extends advanced_testcase
{
    /**
     * Уведомление слушателя о подписке на курс
     * @group pprocessing_scenario
     */
    public function test_scenario()
    {
        $this->resetAfterTest(true);

        // включаем сценарий отправки уведомлений записанным на курс студентам
        set_config('student_enrolled_message_status', true, 'local_pprocessing');
        // выключаем сценарий отправки уведомлений записанным на курс преподавателям
        set_config('teacher_enrolled_message_status', false, 'local_pprocessing');

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();

        unset_config('noemailever');
        $sink = $this->redirectEmails();

        // запишем на курс учителя - не должно отправляться никаких писем
        $sink->clear();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'editingteacher');
        $this->assertEquals(0, $sink->count());

        // запишем на курс студента - должно отправиться одно письмо
        $sink->clear();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->assertEquals(1, $sink->count());

    }
}