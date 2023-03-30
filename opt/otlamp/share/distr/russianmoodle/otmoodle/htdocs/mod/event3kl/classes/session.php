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
use mod_event3kl\format\base\abstract_format;

defined('MOODLE_INTERNAL') || die();

/**
 * Сессия занятия (подгруппа, комната, сеанс)
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class session extends persistent {

    const TABLE = 'event3kl_sessions';
    private $modified = false;
    private $need_update_calendar_events = false;

    /**
     * {@inheritDoc}
     * @see \core\persistent::define_properties()
     */
    protected static function define_properties() {
        return [
            'name' => [
                'type' => PARAM_TEXT,
                'null' => NULL_ALLOWED,
            ],
            'startdate' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'overridenstartdate' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'offereddate' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'maxmembers' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'event3klid' => [
                'type' => PARAM_INT
            ],
            'extid' => [
                'type' => PARAM_TEXT,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'pendingrecs' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'groupid' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'status' => [
                'type' => PARAM_TEXT,
                'default' => 'plan',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     * @see \core\persistent::after_delete()
     */
    protected function after_delete($result) {
        if ($result) {
            // после удаления сессии требуется удалить и всех её участников
            $members = session_member::get_records(['sessionid' => $this->get('id')]);
            foreach($members as $member) {
                $member->delete();
            }
        }
    }

    /**
     * {@inheritDoc}
     * @see \core\persistent::before_update()
     */
    protected function before_update() {
        $oldsession = new self($this->get('id'));
        $this->need_update_calendar_events = ($oldsession->obtain_startdate() != $this->obtain_startdate());
    }

    /**
     * {@inheritDoc}
     * @see \core\persistent::after_update()
     */
    protected function after_update($result) {
        if ($result && $this->need_update_calendar_events) {
            $members = session_member::get_records(['sessionid' => $this->get('id')]);
            foreach($members as $member) {
                $member->update_calendar_event();
            }
        }
        $this->need_update_calendar_events = false;
    }

    /**
     * Задае свойства объекта переданные в массиве $data
     * @param array $data
     * @param bool $setsame - устанавливать ли значение, если оно не изменилось
     *                        может быть полезным, если требуется сохранить объект не зависимо от того, изменился он или нет
     * @return \mod_event3kl\session
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
     * Работат в паре с методом from_array
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

//     public static function get_individual_participant_session(int $event3klid, int $userid) {
//         $participantsessions = self::get_participant_sessions($event3klid, $userid);
//         if (count($participantsessions) != 1) {
//             throw new \Exception('the number of records found is not equal to one');
//         }
//         $individualsession = array_shift($participantsessions);
//         return $individualsession;
//     }

    public static function get_participant_sessions(int $event3klid, int $userid) {
        global $DB;

        $sessions = self::get_records(['event3klid' => $event3klid]);

        if (empty($sessions)) {
            return [];
        }

        $keyedsessions = [];
        foreach($sessions as $session) {
            $keyedsessions[$session->get('id')] = $session;
        }

        list($insql, $params) = $DB->get_in_or_equal(array_keys($keyedsessions), SQL_PARAMS_NAMED, 'sess');
        $select = "sessionid {$insql} AND userid=:userid";
        $params['userid'] = $userid;
        $members = session_member::get_records_select($select, $params);

        $participantsessions = [];
        foreach($members as $member) {
            $membersessionid = $member->get('sessionid');
            if (!array_key_exists($member->get('sessionid'), $participantsessions)) {
                $participantsessions[$membersessionid] = $keyedsessions[$membersessionid];
            }
        }
        return $participantsessions;

    }

    // TODO: переименовать get_ на obtain_ во всех аналогичных методах
    public static function get_speaker_sessions(int $event3klid, int $userid) {

        $event3kl = new event3kl($event3klid);
        if (!has_capability('mod/event3kl:speakatevent', $event3kl->obtain_module_context())) {
            return [];
        }

        $speakersessions = [];
        $eventsessions = self::get_records(['event3klid' => $event3klid]);
        $eventusers = $event3kl->get_event_users();
        foreach($eventsessions as $eventsession) {
            $groupid = $eventsession->get('groupid');
            if (!array_key_exists($groupid, $eventusers)) {
                continue;
            }
            if (!array_key_exists($userid, $eventusers[$groupid]['speakers'])) {
                continue;
            }
            $speakersessions[] = $eventsession;
        }

        return $speakersessions;

    }

    public static function get_manager_sessions(int $event3klid, int $userid) {

        $event3kl = new event3kl($event3klid);
        if (!has_capability('mod/event3kl:managesessions', $event3kl->obtain_module_context())) {
            return [];
        }

        $managersessions = [];
        $eventsessions = self::get_records(['event3klid' => $event3klid]);
        $eventusers = $event3kl->get_event_users();
        foreach($eventsessions as $eventsession) {
            $groupid = $eventsession->get('groupid');
            if (!array_key_exists($groupid, $eventusers)) {
                continue;
            }
            if (!array_key_exists($userid, $eventusers[$groupid]['managers'])) {
                continue;
            }
            $managersessions[] = $eventsession;
        }

        return $managersessions;

    }

    public function try_start() {
        global $USER;

        if ($this->get('status') == 'plan' && $this->obtain_startdate() < time()) {
            if ($this->is_manager($USER->id) || $this->is_speaker($USER->id) || $this->is_member($USER->id)) {
                $event3kl = $this->obtain_event3kl();
                $provider = providers::instance($event3kl->get('provider'));
                $success = true;

                // TODO: добавить обработку результата старта сессии на случай провала
                $extid = $provider->start_session($this, $event3kl);
                $this->set('extid', $extid);

                if ($success) {
                    $this->set('status', 'active');
                    $this->save();
                }
            }
        }
    }

    public function obtain_event3kl($resetcache=false) {
        static $event3kls = [];
        $event3klid = $this->get('event3klid');
        if (!array_key_exists($event3klid, $event3kls) || $resetcache) {
            $event3kls[$event3klid] = event3kl::get_record(['id' => $event3klid]);
        }
        return $event3kls[$event3klid];
    }

    public function is_manager($userid) {
        $groupid = $this->get('groupid');
        $event3kl = $this->obtain_event3kl();
        return $event3kl->is_manager($userid, $groupid);
    }

    public function is_speaker($userid) {
        $groupid = $this->get('groupid');
        $event3kl = $this->obtain_event3kl();
        return $event3kl->is_speaker($userid, $groupid);
    }

    public function is_potential_member($userid) {
        $groupid = $this->get('groupid');
        $event3kl = $this->obtain_event3kl();
        return $event3kl->is_potential_member($userid, $groupid);
    }

    public function is_member($userid) {
        $member = session_member::get_session_member($this->get('id'), $userid);
        return $this->is_potential_member($userid) && $member !== false;
    }

    public function try_finish() {
        global $USER;

        if ($this->get('status') == 'active' && $this->is_manager($USER->id)) {
            $event3kl = $this->obtain_event3kl();
            $provider = providers::instance($event3kl->get('provider'));
            if ($provider->finish_session($this, $event3kl)) {
                $this->set('status', 'finished');
                $pendingrecs = ($provider->supports_records_download() ? 1 : 0);
                $this->set('pendingrecs', $pendingrecs);
                $this->save();
            }
        }
    }

    public function try_download_records() {
        if ($this->get('status') == 'finished' && $this->get('pendingrecs') == 1) {
            $event3kl = $this->obtain_event3kl();
            $provider = providers::instance($event3kl->get('provider'));
            $records = $provider->get_records($this, $event3kl);
            if (!empty($records)) {
                $modulecontext = $event3kl->obtain_module_context();
                $fs = get_file_storage();
                foreach($records as $record) {
                    $filecontent = $provider->get_record_content($record);
                    // Prepare file record object
                    $fileinfo = [
                        'contextid' => $modulecontext->id,
                        'component' => 'mod_event3kl',
                        'filearea' => 'sessionrecord',
                        'itemid' => $this->get('id'),
                        'filepath' => '/',
                        'filename' => $record['name']
                    ];
                    // if (call_user_func_array([$fs, 'file_exists'], array_values($fileinfo))) {
                    //     $file = call_user_func_array([$fs, 'get_file'], array_values($fileinfo));
                    //     $file->delete();
                    // }
                    $pathnamehash = call_user_func_array([$fs, 'get_pathname_hash'], array_values($fileinfo));
                    if ($fs->file_exists_by_hash($pathnamehash)) {
                        $file = $fs->get_file_by_hash($pathnamehash);
                        $file->delete();
                    }
                    $fs->create_file_from_string($fileinfo, $filecontent);
                }
            }
            $this->set('pendingrecs', 0);
            $this->save();

        }
        return true;
    }

    public function try_get_participate_link($userid) {
        global $USER;
        if ($this->get('status') == 'active' && !is_null($this->get('extid'))) {
            if ($this->is_speaker($USER->id) || $this->is_member($USER->id)) {
                $event3kl = $this->obtain_event3kl();
                $provider = providers::instance($event3kl->get('provider'));
                return $provider->get_participate_link($this, $event3kl, $userid);
            }
        }
        return null;
    }

    public function can_edit_attendance($userid) {
        $groupid = $this->get('groupid');
        $event3kl = $this->obtain_event3kl();
        return $event3kl->can_edit_attendance($userid, $groupid);
    }

    public function obtain_startdate() {
        $startdate = $this->get('overridenstartdate');
        if (is_null($startdate)) {
            $startdate = $this->get('startdate');
        }
        return $startdate;
    }
}