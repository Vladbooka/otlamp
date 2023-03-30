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
 * Юнит-тест сценария spelling_mistake (Уведомление об орфографической ошибке)
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_pprocessing_s_spelling_mistake_testcase extends advanced_testcase
{

    /**
     * Уведомление об орфографической ошибке
     * @group pprocessing_scenario
     */
    public function test_scenario()
    {
        $this->resetAfterTest(true);

        // включаем сбор сообщений
        unset_config('noemailever');
        $sink = $this->redirectEmails();

        // включаем сценарий
        set_config('spelling_mistake_message_status', true, 'local_pprocessing');

        // подготовка списка получателей
        $admins = get_admins();
        $recievers = array_merge(
            get_users_by_capability(context_course::instance(SITEID), 'local/pprocessing:receive_notifications'),
            $admins
            );
        set_config('recievers', implode(',', array_keys($recievers)), 'local_pprocessing');
        set_config('spelling_mistake_message_subject', 'Тема письма', 'local_pprocessing');
        set_config('spelling_mistake_message_full', 'Полный текст письма', 'local_pprocessing');
        set_config('spelling_mistake_message_short', 'Короткий текст письма', 'local_pprocessing');


        // формирование данных для события
        $eventdata = [
            'context' => context_system::instance(),
            'other' => [
                'url' => (new moodle_url('/'))->out(),
                'mistake' => 'очепятка',
                'phrase' => 'если в нашем тексте была найдена очепятка, вы можете сообщить нам о ней...',
                'start' => 'если в нашем тексте была найдена',
                'end' => 'вы можете сообщить нам о ней',
                'comment' => 'я нашёл опечатку!'
            ]
        ];

        // Проверка, что получатели есть. В принципе, это не ошибка сценария, но тест сработает неправильно если вдруг такое произойдёт
        $this->assertGreaterThan(0, count($recievers), 'Проверка, что получатели есть. В принципе, это не ошибка сценария, но тест сработает неправильно если вдруг такое произойдёт');

        // Проверка отправки уведомлений об орфографической ошибке
        $sink->clear();
        // создание события о новой орфографической ошибки
        \theme_opentechnology\event\spelling_mistake::create($eventdata)->trigger();
        $this->assertEquals(count($recievers), $sink->count(), 'Проверка отправки уведомлений об орфографической ошибке. '.var_export($sink->get_messages(), true));


        // Проверка отправки уведомлений об орфографической ошибке при отсутствии получателей
        // (оказывается даже если забыли настроить получателей, хэндлер отправляет админам, что ж - проверим все равно кейс)
        $sink->clear();
        set_config('recievers', implode(',', array_keys($admins)), 'local_pprocessing');
        \theme_opentechnology\event\spelling_mistake::create($eventdata)->trigger();
        $this->assertEquals(count($admins), $sink->count(), 'Проверка отправки уведомлений об орфографической ошибке при отсутствии получателей. '.var_export($sink->get_messages(), true));
        set_config('recievers', implode(',', array_keys($recievers)), 'local_pprocessing');

        // Проверка отправки уведомлений об орфографической ошибке с пустой темой (не настроили)
        $sink->clear();
        set_config('spelling_mistake_message_subject', '', 'local_pprocessing');
        \theme_opentechnology\event\spelling_mistake::create($eventdata)->trigger();
        $this->assertEquals(0, $sink->count(), 'Проверка отправки уведомлений об орфографической ошибке с пустой темой (не настроили). '.var_export($sink->get_messages(), true));
        set_config('spelling_mistake_message_subject', 'Тема письма', 'local_pprocessing');

        // Проверка отправки уведомлений об орфографической ошибке с пустым полным текстом письма (не настроили)
        $sink->clear();
        set_config('spelling_mistake_message_full', '', 'local_pprocessing');
        \theme_opentechnology\event\spelling_mistake::create($eventdata)->trigger();
        $this->assertEquals(0, $sink->count(), 'Проверка отправки уведомлений об орфографической ошибке с пустым полным текстом письма (не настроили). '.var_export($sink->get_messages(), true));
        set_config('spelling_mistake_message_full', 'Полный текст письма', 'local_pprocessing');


    }
}