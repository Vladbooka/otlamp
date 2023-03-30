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
 * Юнит-тест сценария delete_quiz_attempts_by_date (Удаление завершенных попыток тестирования старше заданной даты)
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_pprocessing_s_delete_quiz_attempts_by_date_testcase extends advanced_testcase
{

    /**
     * Удаление завершенных попыток тестирования старше заданной даты
     * @group pprocessing_scenario
     */
    public function test_scenario() {
        global $DB, $CFG;
        $this->resetAfterTest();
        require_once($CFG->dirroot . '/mod/quiz/lib.php');
        set_debugging(DEBUG_NONE);
        // Фиксируем время
        $now = time();
        $relative = strtotime('-3 month', $now);
        $yearago = strtotime('-1 year', $now);
        $first = strtotime('-10 month', $now);
        $second = strtotime('-8 month', $now);
        $third = strtotime('-6 month', $now);
        $fourth = strtotime('-4 month', $now);
        $fifth = strtotime('-2 month', $now);

        $uniqueid = 0;

        // Создаем курс
        $course = $this->getDataGenerator()->create_course();

        // Создаем и подписываем пользователя на курс
        $user1 = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $user2 = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $emptyuser1 = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $emptyuser2 = $this->getDataGenerator()->create_and_enrol($course, 'student');

        // Создаем первый тест с методом оценивания Высшая оценка
        $quiz1 = $this->getDataGenerator()->create_module('quiz',
            ['course' => $course->id, 'grademethod' => QUIZ_GRADEHIGHEST]);
        $emptyquiz1 = $this->getDataGenerator()->create_module('quiz',
            ['course' => $course->id, 'grademethod' => QUIZ_GRADEHIGHEST]);

        // Создаем попытки тестирования: одну свежую не успешную, одну свежую в процессе
        // и несколько годичной давности, в т.ч. две успешных (с одинаковыми высшими баллами)
        // Ожидаемый результат: должны остаться попытки 1, 5, 6

        $expect1 = [];
        for ($i = 1; $i < 3; $i++) {
            // Лучшая попытка, которая не должна быть удалена
            $attempt1 = new stdClass();
            $attempt1->quiz = $quiz1->id;
            $attempt1->userid = ${'user'.$i}->id;
            $attempt1->attempt = 1;
            $attempt1->uniqueid = ++$uniqueid;
            $attempt1->state = 'finished';
            $attempt1->timefinish = rand($yearago + 1, $first - 1);
            $attempt1->timestart = rand($yearago, $attempt1->timefinish - 1);
            $attempt1->sumgrades = 80;
            $attempt1->layout = '';
            $attempt1->id = $DB->insert_record('quiz_attempts', $attempt1);

            // Вторая лучшая попытка, которая должна быть удалена
            $attempt2 = new stdClass();
            $attempt2->quiz = $quiz1->id;
            $attempt2->userid = ${'user'.$i}->id;
            $attempt2->attempt = 2;
            $attempt2->uniqueid = ++$uniqueid;
            $attempt2->state = 'finished';
            $attempt2->timefinish = rand($first + 1, $second - 1);
            $attempt2->timestart = rand($first, $attempt2->timefinish - 1);
            $attempt2->sumgrades = 80;
            $attempt2->layout = '';
            $attempt2->id = $DB->insert_record('quiz_attempts', $attempt2);

            // Заброшенная попытка, которая должна быть удалена
            $attempt3 = new stdClass();
            $attempt3->quiz = $quiz1->id;
            $attempt3->userid = ${'user'.$i}->id;
            $attempt3->attempt = 3;
            $attempt3->uniqueid = ++$uniqueid;
            $attempt3->state = 'abandoned';
            $attempt3->timestart = rand($second, $third - 1);
            $attempt3->layout = '';
            $attempt3->id = $DB->insert_record('quiz_attempts', $attempt3);

            // Завершенная не лучшая попытка, которая должна быть удалена
            $attempt4 = new stdClass();
            $attempt4->quiz = $quiz1->id;
            $attempt4->userid = ${'user'.$i}->id;
            $attempt4->attempt = 4;
            $attempt4->uniqueid = ++$uniqueid;
            $attempt4->state = 'finished';
            $attempt4->timefinish = rand($third + 1, $fourth - 1);
            $attempt4->timestart = rand($third, $attempt4->timefinish - 1);
            $attempt4->sumgrades = 50;
            $attempt4->layout = '';
            $attempt4->id = $DB->insert_record('quiz_attempts', $attempt4);

            // Завершенная не лучшая попытка, которая не должна быть удалена
            $attempt5 = new stdClass();
            $attempt5->quiz = $quiz1->id;
            $attempt5->userid = ${'user'.$i}->id;
            $attempt5->attempt = 5;
            $attempt5->uniqueid = ++$uniqueid;
            $attempt5->state = 'finished';
            $attempt5->timefinish = rand($relative + 1, $fifth - 1);
            $attempt5->timestart = rand($relative, $attempt5->timefinish - 1);
            $attempt5->sumgrades = 40;
            $attempt5->layout = '';
            $attempt5->id = $DB->insert_record('quiz_attempts', $attempt5);

            // Попытка в процессе, которая не должна быть удалена
            $attempt6 = new stdClass();
            $attempt6->quiz = $quiz1->id;
            $attempt6->userid = ${'user'.$i}->id;
            $attempt6->attempt = 6;
            $attempt6->uniqueid = ++$uniqueid;
            $attempt6->state = 'inprogress';
            $attempt6->timestart = rand($fifth, $now);
            $attempt6->sumgrades = 40;
            $attempt6->layout = '';
            $attempt6->id = $DB->insert_record('quiz_attempts', $attempt6);

            // Массив для проверки
            $expect1 = array_merge($expect1, [$attempt1->id, $attempt5->id, $attempt6->id]);
        }
        asort($expect1);

        // Создаем второй тест с методом оценивания Средняя оценка
        $quiz2 = $this->getDataGenerator()->create_module('quiz',
            ['course' => $course->id, 'grademethod' => QUIZ_GRADEAVERAGE]);
        $emptyquiz2 = $this->getDataGenerator()->create_module('quiz',
            ['course' => $course->id, 'grademethod' => QUIZ_GRADEAVERAGE]);

        // Создаем попытки тестирования: две свежих и несколько годичной давности
        // Ожидаемый результат: должны остаться попытки 1, 2, 3, 4, 5, 6

        $expect2 = [];
        for ($i = 1; $i < 3; $i++) {
            // Лучшая попытка, которая не должна быть удалена
            $attempt1 = new stdClass();
            $attempt1->quiz = $quiz2->id;
            $attempt1->userid = ${'user'.$i}->id;
            $attempt1->attempt = 1;
            $attempt1->uniqueid = ++$uniqueid;
            $attempt1->state = 'finished';
            $attempt1->timefinish = rand($yearago + 1, $first - 1);
            $attempt1->timestart = rand($yearago, $attempt1->timefinish - 1);
            $attempt1->sumgrades = 80;
            $attempt1->layout = '';
            $attempt1->id = $DB->insert_record('quiz_attempts', $attempt1);

            // Вторая лучшая попытка, которая не должна быть удалена
            $attempt2 = new stdClass();
            $attempt2->quiz = $quiz2->id;
            $attempt2->userid = ${'user'.$i}->id;
            $attempt2->attempt = 2;
            $attempt2->uniqueid = ++$uniqueid;
            $attempt2->state = 'finished';
            $attempt2->timefinish = rand($first + 1, $second - 1);
            $attempt2->timestart = rand($first, $attempt2->timefinish - 1);
            $attempt2->sumgrades = 80;
            $attempt2->layout = '';
            $attempt2->id = $DB->insert_record('quiz_attempts', $attempt2);

            // Заброшенная попытка, которая не должна быть удалена
            $attempt3 = new stdClass();
            $attempt3->quiz = $quiz2->id;
            $attempt3->userid = ${'user'.$i}->id;
            $attempt3->attempt = 3;
            $attempt3->uniqueid = ++$uniqueid;
            $attempt3->state = 'abandoned';
            $attempt3->timestart = rand($second, $third - 1);
            $attempt3->layout = '';
            $attempt3->id = $DB->insert_record('quiz_attempts', $attempt3);

            // Завершенная не лучшая попытка, которая не должна быть удалена
            $attempt4 = new stdClass();
            $attempt4->quiz = $quiz2->id;
            $attempt4->userid = ${'user'.$i}->id;
            $attempt4->attempt = 4;
            $attempt4->uniqueid = ++$uniqueid;
            $attempt4->state = 'finished';
            $attempt4->timefinish = rand($third + 1, $fourth - 1);
            $attempt4->timestart = rand($third, $attempt4->timefinish - 1);
            $attempt4->sumgrades = 50;
            $attempt4->layout = '';
            $attempt4->id = $DB->insert_record('quiz_attempts', $attempt4);

            // Завершенная не лучшая попытка, которая не должна быть удалена
            $attempt5 = new stdClass();
            $attempt5->quiz = $quiz2->id;
            $attempt5->userid = ${'user'.$i}->id;
            $attempt5->attempt = 5;
            $attempt5->uniqueid = ++$uniqueid;
            $attempt5->state = 'finished';
            $attempt5->timefinish = rand($relative + 1, $fifth - 1);
            $attempt5->timestart = rand($relative, $attempt5->timefinish - 1);
            $attempt5->sumgrades = 40;
            $attempt5->layout = '';
            $attempt5->id = $DB->insert_record('quiz_attempts', $attempt5);

            // Попытка в процессе, которая не должна быть удалена
            $attempt6 = new stdClass();
            $attempt6->quiz = $quiz2->id;
            $attempt6->userid = ${'user'.$i}->id;
            $attempt6->attempt = 6;
            $attempt6->uniqueid = ++$uniqueid;
            $attempt6->state = 'inprogress';
            $attempt6->timestart = rand($fifth, $now);
            $attempt6->sumgrades = 40;
            $attempt6->layout = '';
            $attempt6->id = $DB->insert_record('quiz_attempts', $attempt6);

            // Массив для проверки
            $expect2 = array_merge($expect2, [$attempt1->id, $attempt2->id, $attempt3->id, $attempt4->id, $attempt5->id, $attempt6->id]);
        }
        asort($expect2);

        // Создаем третий тест с методом оценивания Первая попытка
        $quiz3 = $this->getDataGenerator()->create_module('quiz',
            ['course' => $course->id, 'grademethod' => QUIZ_ATTEMPTFIRST]);
        $emptyquiz3 = $this->getDataGenerator()->create_module('quiz',
            ['course' => $course->id, 'grademethod' => QUIZ_ATTEMPTFIRST]);

        // Создаем попытки тестирования: две свежих и несколько годичной давности
        // Ожидаемый результат: должны остаться попытки 1, 5, 6

        $expect3 = [];
        for ($i = 1; $i < 3; $i++) {
            // Первая попытка, которая не должна быть удалена
            $attempt1 = new stdClass();
            $attempt1->quiz = $quiz3->id;
            $attempt1->userid = ${'user'.$i}->id;
            $attempt1->attempt = 1;
            $attempt1->uniqueid = ++$uniqueid;
            $attempt1->state = 'finished';
            $attempt1->timefinish = rand($yearago + 1, $first - 1);
            $attempt1->timestart = rand($yearago, $attempt1->timefinish - 1);
            $attempt1->sumgrades = 80;
            $attempt1->layout = '';
            $attempt1->id = $DB->insert_record('quiz_attempts', $attempt1);

            // Вторая попытка, которая должна быть удалена
            $attempt2 = new stdClass();
            $attempt2->quiz = $quiz3->id;
            $attempt2->userid = ${'user'.$i}->id;
            $attempt2->attempt = 2;
            $attempt2->uniqueid = ++$uniqueid;
            $attempt2->state = 'finished';
            $attempt2->timefinish = rand($first + 1, $second - 1);
            $attempt2->timestart = rand($first, $attempt2->timefinish - 1);
            $attempt2->sumgrades = 80;
            $attempt2->layout = '';
            $attempt2->id = $DB->insert_record('quiz_attempts', $attempt2);

            // Заброшенная попытка, которая должна быть удалена
            $attempt3 = new stdClass();
            $attempt3->quiz = $quiz3->id;
            $attempt3->userid = ${'user'.$i}->id;
            $attempt3->attempt = 3;
            $attempt3->uniqueid = ++$uniqueid;
            $attempt3->state = 'abandoned';
            $attempt3->timestart = rand($second, $third - 1);
            $attempt3->layout = '';
            $attempt3->id = $DB->insert_record('quiz_attempts', $attempt3);

            // Четвертая попытка, которая должна быть удалена
            $attempt4 = new stdClass();
            $attempt4->quiz = $quiz3->id;
            $attempt4->userid = ${'user'.$i}->id;
            $attempt4->attempt = 4;
            $attempt4->uniqueid = ++$uniqueid;
            $attempt4->state = 'finished';
            $attempt4->timefinish = rand($third + 1, $fourth - 1);
            $attempt4->timestart = rand($third, $attempt4->timefinish - 1);
            $attempt4->sumgrades = 50;
            $attempt4->layout = '';
            $attempt4->id = $DB->insert_record('quiz_attempts', $attempt4);

            // Пятая попытка, которая не должна быть удалена
            $attempt5 = new stdClass();
            $attempt5->quiz = $quiz3->id;
            $attempt5->userid = ${'user'.$i}->id;
            $attempt5->attempt = 5;
            $attempt5->uniqueid = ++$uniqueid;
            $attempt5->state = 'finished';
            $attempt5->timefinish = rand($relative + 1, $fifth - 1);
            $attempt5->timestart = rand($relative, $attempt5->timefinish - 1);
            $attempt5->sumgrades = 40;
            $attempt5->layout = '';
            $attempt5->id = $DB->insert_record('quiz_attempts', $attempt5);

            // Попытка в процессе, которая не должна быть удалена
            $attempt6 = new stdClass();
            $attempt6->quiz = $quiz3->id;
            $attempt6->userid = ${'user'.$i}->id;
            $attempt6->attempt = 6;
            $attempt6->uniqueid = ++$uniqueid;
            $attempt6->state = 'inprogress';
            $attempt6->timestart = rand($fifth, $now);
            $attempt6->sumgrades = 40;
            $attempt6->layout = '';
            $attempt6->id = $DB->insert_record('quiz_attempts', $attempt6);

            // Массив для проверки
            $expect3 = array_merge($expect3, [$attempt1->id, $attempt5->id, $attempt6->id]);
        }
        asort($expect3);

        // Создаем четвертый тест с методом оценивания Последняя попытка
        $quiz4 = $this->getDataGenerator()->create_module('quiz',
            ['course' => $course->id, 'grademethod' => QUIZ_ATTEMPTLAST]);
        $emptyquiz4 = $this->getDataGenerator()->create_module('quiz',
            ['course' => $course->id, 'grademethod' => QUIZ_ATTEMPTLAST]);

        // Создаем попытки тестирования: несколько годичной давности и одна свежая в прогрессе
        // Ожидаемый результат: должны остаться попытки 4, 5

        $expect4 = [];
        for ($i = 1; $i < 3; $i++) {
            // Первая попытка, которая должна быть удалена
            $attempt1 = new stdClass();
            $attempt1->quiz = $quiz4->id;
            $attempt1->userid = ${'user'.$i}->id;
            $attempt1->attempt = 1;
            $attempt1->uniqueid = ++$uniqueid;
            $attempt1->state = 'finished';
            $attempt1->timefinish = rand($yearago + 1, $first - 1);
            $attempt1->timestart = rand($yearago, $attempt1->timefinish - 1);
            $attempt1->sumgrades = 80;
            $attempt1->layout = '';
            $attempt1->id = $DB->insert_record('quiz_attempts', $attempt1);

            // Вторая попытка, которая должна быть удалена
            $attempt2 = new stdClass();
            $attempt2->quiz = $quiz4->id;
            $attempt2->userid = ${'user'.$i}->id;
            $attempt2->attempt = 2;
            $attempt2->uniqueid = ++$uniqueid;
            $attempt2->state = 'finished';
            $attempt2->timefinish = rand($first + 1, $second - 1);
            $attempt2->timestart = rand($first, $attempt2->timefinish - 1);
            $attempt2->sumgrades = 80;
            $attempt2->layout = '';
            $attempt2->id = $DB->insert_record('quiz_attempts', $attempt2);

            // Третья попытка, которая должна быть удалена
            $attempt3 = new stdClass();
            $attempt3->quiz = $quiz4->id;
            $attempt3->userid = ${'user'.$i}->id;
            $attempt3->attempt = 3;
            $attempt3->uniqueid = ++$uniqueid;
            $attempt3->state = 'abandoned';
            $attempt3->timestart = rand($second, $third - 1);
            $attempt3->layout = '';
            $attempt3->id = $DB->insert_record('quiz_attempts', $attempt3);

            // Четвертая попытка, которая не должна быть удалена
            $attempt4 = new stdClass();
            $attempt4->quiz = $quiz4->id;
            $attempt4->userid = ${'user'.$i}->id;
            $attempt4->attempt = 4;
            $attempt4->uniqueid = ++$uniqueid;
            $attempt4->state = 'finished';
            $attempt4->timefinish = rand($third + 1, $fourth - 1);
            $attempt4->timestart = rand($third, $attempt4->timefinish - 1);
            $attempt4->sumgrades = 50;
            $attempt4->layout = '';
            $attempt4->id = $DB->insert_record('quiz_attempts', $attempt4);

            // Массив для проверки
            $expect4 = array_merge($expect4, [$attempt4->id]);
        }
        asort($expect4);

        // Включаем сценарий
        set_config('delete_quiz_attempts_by_date__status', 1, 'local_pprocessing');

        // Выставляем время 1 месяц
        set_config('delete_quiz_attempts_by_date__relativedate', $now - $relative, 'local_pprocessing');

        // Кидаем событие для ежедневной обработки прецедентов
        \local_pprocessing\event\daily_executed::create()->trigger();

        list($sqlin, $params) = $DB->get_in_or_equal([$user1->id, $user2->id], SQL_PARAMS_NAMED);
        $select = 'userid ' . $sqlin . ' AND quiz = :quiz';

        // Проверяем, что в первом тесте остались свежая попытка и первая из двух высших
        $params['quiz'] = $quiz1->id;
        $attempts = $DB->get_records_select('quiz_attempts', $select, $params, 'id ASC');
        $this->assertEquals($expect1, array_keys($attempts));

        // Проверяем, что во втором тесте остались все попытки
        $params['quiz'] = $quiz2->id;
        $attempts = $DB->get_records_select('quiz_attempts', $select, $params, 'id ASC');
        $this->assertEquals($expect2, array_keys($attempts));

        // Проверяем, что в третьем тесте остались свежая попытка и первая попытка
        $params['quiz'] = $quiz3->id;
        $attempts = $DB->get_records_select('quiz_attempts', $select, $params, 'id ASC');
        $this->assertEquals($expect3, array_keys($attempts));

        // Проверяем, что в четвертом тесте осталась последняя попытка
        $params['quiz'] = $quiz4->id;
        $attempts = $DB->get_records_select('quiz_attempts', $select, $params, 'id ASC');
        $this->assertEquals($expect4, array_keys($attempts));
    }
}