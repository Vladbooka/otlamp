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

use core\persistent;

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/calendar/lib.php';

/**
 * Сессия занятия (подгруппа, комната, сеанс)
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class session_member extends persistent {

    const TABLE = 'event3kl_session_members';
    private $modified = false;

    /**
     * {@inheritDoc}
     * @see \core\persistent::define_properties()
     */
    protected static function define_properties() {
        return [
            'sessionid' => [
                'type' => PARAM_INT,
            ],
            'userid' => [
                'type' => PARAM_INT,
            ],
            'calendareventid' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'attendance' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'default' => null,
            ]
        ];
    }

    /**
     * {@inheritDoc}
     * @see \core\persistent::after_create()
     */
    protected function after_create() {
        // обновления события календаря
        $this->update_calendar_event();
    }

    /**
     * {@inheritDoc}
     * @see \core\persistent::after_update()
     */
    protected function after_update($result) {

        // пометка для исключения зацикливания обновления (ниже по коду имеются доп.пояснения)
        static $updatewasmine = false;

        if ($updatewasmine) {
            // мы уже заходили в after_update, который вызвал update и попал в цикл - выходим из него
            $updatewasmine = false;
            return;
        }

        // обновление события календаря ниже может вызывать обновление
        // это сделано для сохранения идентификатора события календаря в запись участника сессии
        // сделаем пометку, что мы сейчас вызовем обновление участника сессии, чтобы отловить зацикливание,
        $updatewasmine = true;

        // обновления события календаря
        $this->update_calendar_event();

        // в случае, если обновление участника сессии не было вызвано во время обновления события календаря
        // то тут сами же отменим нашу пометку
        $updatewasmine = false;
    }

    /**
     * {@inheritDoc}
     * @see \core\persistent::after_delete()
     */
    protected function after_delete($result) {

        // идентификатор сессии, из которой был удален участник
        $sessionid = $this->get('sessionid');
        // получение оставшихся участникой той сессии
        $members = self::get_records(['sessionid' => $sessionid]);
        if (empty($members)) {
            // участников в той сессии не осталось - удалим и сессию (если ещё не..)
            try {
                $session = new session($sessionid);
                // удалять сессию не стоит даже пустую, если у нас формат подгрупп
                // там пустые сессии вполне нужны, чтобы в них потом подписывать пользователей
                $event3kl = $session->obtain_event3kl();
                if ($event3kl->get('format') != 'manual') {
                    $session->delete();
                }
            } catch (\Exception $ex) {}
        }

        // Удаление события календаря
        $calendareventid = $this->get('calendareventid');
        if (isset($calendareventid)) {
            // удалим событие в календаре (если еще не...)
            try {
                $calendarevent = \calendar_event::load($calendareventid);
                $calendarevent->delete();
            } catch(\Exception $ex) {}
        }
    }

    public function update_calendar_event() {

        $calendareventid = $this->get('calendareventid');
        $userid = $this->get('userid');
        $session = new session($this->get('sessionid'));
        $sessionname = $session->get('name');
        $sessionstartdate = $session->obtain_startdate();


        if (isset($calendareventid)) {
            try {
                $oldcalendarevent = \calendar_event::load($calendareventid);
                $properties = $oldcalendarevent->properties();
            } catch(\Exception $ex) {
                $properties = new \stdClass();
            }
        } else {
            $properties = new \stdClass();
        }

        // событий в календаре без даты быть не может
        if (empty($sessionstartdate)) {
            // если были - удаляем
            if (isset($oldcalendarevent)) {
                $oldcalendarevent->delete(true);
                $this->set('calendareventid', null);
                $this->update();
            }
            // и новых не создаем
            return;
        }


        $event3kl = new event3kl($session->get('event3klid'));
        // $courseid = $event3kl->get('course');
        $instanceid = $event3kl->get('id');
        $event3klrecord = $event3kl->to_record();
        $cm = $event3kl->obtain_cm();

        $a = new \stdClass();
        $a->name = $event3kl->get('name');
        $a->sessionname = $sessionname;
        $properties->name        = get_string('calendar_event_name', 'mod_event3kl', $a);
        $properties->description = format_module_intro('event3kl', $event3klrecord, $cm->id, false);
        $properties->format      = FORMAT_HTML;
        // если не указать модуль и инстанс, будет событием пользователя без возможности перехода к элементу курса
        $properties->modulename  = 'event3kl';
        $properties->instance    = $instanceid;
        $properties->userid      = $userid;
        $properties->eventtype   = 'user';
        $properties->timestart   = $sessionstartdate;
        $properties->timeduration= 0;
        // CALENDAR_EVENT_TYPE_STANDARD - если требуется, чтобы не отображалось в блоке my_overview
        $properties->type        = CALENDAR_EVENT_TYPE_ACTION;
        // в блоке my_overview события сортируются вроде по timesort
        $properties->timesort    = $properties->timestart;
        // если элемент курса скрыт, то и событие скрываем
        $properties->visible     = instance_is_visible('event3kl', $event3klrecord);
        // если указать курс/группу, событие станет событием курса и будет доступно всем участникам группы
        $properties->courseid    = 0;
        $properties->groupid     = 0;

        // создание/обновление данных события календаря
        $newcalendarevent = \calendar_event::create($properties, false);
        if ($newcalendarevent !== false && $calendareventid != $newcalendarevent->id) {
            // это было создание, а не обновление и оно завершилось успешно - добавим идентификатор события календаря в участника
            $this->set('calendareventid', $newcalendarevent->id);
            $this->update();
        }

    }

    /**
     * Задает свойства объекта переданные в массиве $data
     * @param array $data
     * @param bool $setsame - устанавливать ли значение, если оно не изменилось
     *                        может быть полезным, если требуется сохранить объект не зависимо от того, изменился он или нет
     * @return \mod_event3kl\session_member
     */
    public function from_array(array $data, bool $setsame=true) {
        foreach ($data as $property => $value) {
            if (!$setsame && $this->get($property) == $value) {
                // не разрешено устанавливать значение, если оно не изменилось
                // а оно не изменилось - проходим, не задерживаем
                continue;
            }
            $this->raw_set($property, $value);
            $this->modified = true;
        }
        return $this;
    }

    /**
     * Был ли модифицирован объект (требуется ли его сохранять)
     * Работат только в паре с методом from_array
     * @return boolean
     */
    public function is_modified() {
        return $this->modified;
    }

    /**
     * Устанавливает принудительно значение свойству modified
     * @param bool $value
     */
    public function force_modified(bool $value) {
        $this->modified = $value;
    }

    /**
     * Сохраняет объект только в случае, если он был модифицирован (работат в паре с методом from_array)
     * Возвращает идентификатор сохраненного объекта
     * @return mixed идентификтаор
     */
    public function save_modified() {
        if ($this->is_modified()) {
            $this->save();
        }
        return $this->get('id');
    }

    public static function get_session_member($sessionid, $userid) {
        return session_member::get_record([
            'sessionid' => $sessionid,
            'userid' => $userid
        ]);
    }
}