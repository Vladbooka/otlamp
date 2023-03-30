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

namespace mod_event3kl;

use mod_event3kl\datemode\base\abstract_datemode;

defined('MOODLE_INTERNAL') || die();

class datemodes extends \ArrayObject {


    public function offsetSet($name, $value)
    {
        if (!is_object($value) || !($value instanceof abstract_datemode))
        {
            throw new \InvalidArgumentException(sprintf('Only subclasses of abstract_datemode allowed.'));
        }
        parent::offsetSet($name, $value);
    }

    /**
     * Получение списка имеющихся классов форматов в виде массива незаполненных инстансов
     * @return datemodes
     */
    public static function get_all_datemodes()
    {
        $datemodes = new self();

        foreach(glob(__DIR__ . '/datemode/*', GLOB_NOSORT) as $datemodefilename)
        {
            if (is_file($datemodefilename)) {
                require_once($datemodefilename);
                $datemodecode = basename($datemodefilename, '.php');
                try {
                    $datemodes->append(self::instance($datemodecode));
                } catch(\Exception $ex) {
                    continue;
                }
            }
        }

        return $datemodes;
    }

    /**
     * @param string $datemodecode
     * @throws \Exception
     * @return abstract_datemode
     */
    public static function instance($datemodecode, event3kl $event3kl=null, int $groupid=null, int $userid=null) {
        $datemodeclass = '\\mod_event3kl\\datemode\\'.$datemodecode;
        if (class_exists($datemodeclass)) {
            return new $datemodeclass($event3kl, $groupid, $userid);
        }
        throw new \Exception('Date mode class not found');
    }

    public function get_select_options() {
        $options = [];
        foreach($this as $datemode) {
            $options[$datemode->get_code()] = $datemode->get_display_name();
        }
        return $options;
    }

    public function mod_form_definition(&$mform, &$form) {

        // Запись модуля курса, будет не null, если происходит редактирование, а не создание
        $cm = $form->get_coursemodule();

        if (!is_null($cm)) {
            // модуль курса уже существует, мы его редактируем
            // $event3kl = new event3kl($cm->instance);


            // при редактировании мы отталкиваемся от того, что менять дефолтные модификаторы надо с осторожностью
            // особенно из-за того, что модификаторы мог настраивать человек, находящийся в одной временной зоне
            // а редактировать будет другой, и это чревато последствиями
            //
            // пример: дата старта группы 1 мая (00:00),
            // модификаторы настраивал человек, который видел перед собой эту дату
            // он сказал, что занятие будет ровно через месяц и один день
            // +1 месяц = 1 июня (00:00), +1 день = 2 июня (00:00)
            //
            // человек в другой временной зоне будет видеть дату старта курса 30 апреля (21:00)
            // для него те же модификаторы будут иметь другой эффект (+1 месяц = 30 мая (21:00), +1 день 31 мая (21:00))
            //
            // даже если учесть разницу в часовых поясах, выходит потерялся один день,
            // а после установки требуемого времени итого, может получиться два дня потеряно от изначальной задумки
            // поэтому пользователь, редактирующий модификаторы, должен понимать ответственность
            // и проверять даты группы, курса, основываясь на которых будет формироваться конечная дата
            //
            // поясняющий пример в виде кода:
            // echo 'На сервере дата старта группы храниться такая:' . PHP_EOL;
            // $date = new DateTime('@1625054400');
            // echo $date->format('Y.m.d H:i:s') . PHP_EOL . PHP_EOL;
            // echo 'Потому что её настраивал преподаватель из Камчатки (+12), и устанавливал её такой:' . PHP_EOL;
            // $date->setTimezone(new DateTimeZone('Asia/Kamchatka'));
            // echo $date->format('Y.m.d H:i:s') . PHP_EOL . PHP_EOL;
            // echo 'Он настроил модификаторы и добавил к дате старта курса один месяц, получилось:' . PHP_EOL;
            // $date->modify('+1 months');
            // echo $date->format('Y.m.d H:i:s') . PHP_EOL . PHP_EOL;
            // echo '..и еще две недели, получилось:' . PHP_EOL;
            // $date->modify('+2 weeks');
            // echo $date->format('Y.m.d H:i:s') . PHP_EOL . PHP_EOL;
            // echo '..и еще два дня, получилось (напоминаю, так себе конечную дату представляет препод из Камчатки):' . PHP_EOL;
            // $date->modify('+2 days');
            // echo $date->format('Y.m.d H:i:s') . PHP_EOL . PHP_EOL;
            // echo 'На сервере эта дата будет в итоге храниться так:' . PHP_EOL;
            // $date->setTimezone(new DateTimeZone('Etc/GMT'));
            // echo $date->format('Y.m.d H:i:s') . PHP_EOL . PHP_EOL;
            // echo 'А студент из Москвы увидит, что у него занятие в эту дату:' . PHP_EOL;
            // $date->setTimezone(new DateTimeZone('Europe/Moscow'));
            // echo $date->format('Y.m.d H:i:s') . PHP_EOL . PHP_EOL;

            $mform->addElement('advcheckbox', 'datemode_edit_confirmed', get_string('datemode_edit_confirmed', 'mod_event3kl'));
            $mform->setDefault('edit_confirmed', false);
            $mform->disabledif('datemode', 'datemode_edit_confirmed', 'notchecked');
        }

        $mform->addElement('select', 'datemode', get_string('datemodetype', 'mod_event3kl'), $this->get_select_options());
        $mform->setDefault('datemode', 'relative');

        foreach($this as $datemode) {
            $datemode->mod_form_definition($mform, $form);
        }

        // Добавление модификатора времени
        $modifierclass = '\\mod_event3kl\\datemodifier\\set_time';
        $elements = call_user_func_array([$modifierclass, 'get_mform_elements'],[&$mform]);
        $name = 'set_time';
        $label = get_string('set_time', 'mod_event3kl');
        $mform->addGroup($elements, $name, $label);
        // Не задаем время для дейтмодов, для которых не указывается дата
        $mform->hideif($name, 'datemode', 'eq', 'opendate');
        $mform->hideif($name, 'datemode', 'eq', 'vacantseat');
        $mform->disabledif($name, 'datemode_edit_confirmed', 'notchecked');

    }

    public function mod_form_validation($data, $files) {
        $errors = [];
        foreach($this as $datemode) {
            $errors = array_merge($errors, $datemode->mod_form_validation($data, $files));
        }
        return $errors;
    }
}