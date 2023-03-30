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
use mod_event3kl\form\opendate_request;
use mod_event3kl\form\opendate_coordination;
use mod_event3kl\form\vacantseat_select;
use core\output\notification;
use mod_event3kl\datemode\opendate;
use mod_event3kl\datemode\vacantseat;
use completion_info;
use mod_event3kl\form\delete_session;
use mod_event3kl\datemode\relative_to_enrol;
use mod_event3kl\datemode\relative_to_enrolment;

defined('MOODLE_INTERNAL') || die();

/**
 * Занятие
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class event3kl extends persistent {

    const TABLE = 'event3kl';

    private $cmid;
    private $format_was_changed_while_update;

    /**
     * {@inheritDoc}
     * @see \core\persistent::define_properties()
     */
    protected static function define_properties() {
        return [
            'course' => [
                'type' => PARAM_INT,
            ],
            'name' => [
                'type' => PARAM_TEXT,
            ],
            'intro' => [
                'type' => PARAM_RAW,
            ],
            'introformat' => [
                'type' => PARAM_INT,
            ],
            'provider' => [
                'type' => PARAM_TEXT,
            ],
            'providerdata' => [
                'type' => PARAM_TEXT,
            ],
            'format' => [
                'type' => PARAM_TEXT,
            ],
            'formatdata' => [
                'type' => PARAM_TEXT,
            ],
            'datemode' => [
                'type' => PARAM_TEXT,
            ],
            'datemodedata' => [
                'type' => PARAM_TEXT,
            ],
        ];
    }

    /**
     * {@inheritDoc}
     * @see \core\persistent::before_update()
     */
    protected function before_update() {
        $oldevent3kl = new self($this->get('id'));
        $this->format_was_changed_while_update = ($oldevent3kl->get('format') != $this->get('format'));
    }

    /**
     * {@inheritDoc}
     * @see \core\persistent::after_update()
     */
    protected function after_update($result) {
        if ($result && $this->format_was_changed_while_update) {
            $sessions = session::get_records(['event3klid' => $this->get('id')]);
            foreach ($sessions as $session) {
                // при изменении формата происходит перегруппировка
                // новые сессии - новый состав участников - старые даты не используем
                $session->set('overridenstartdate', null);
                $session->set('offereddate', null);
                $session->save();
            }
        }
        $this->format_was_changed_while_update = false;
    }

    public function from_form_data($formdata) {

        $formdata = (array) $formdata;

        // установка приватного свойства cmid
        $this->cmid = $formdata['coursemodule'];
        unset($formdata['coursemodule']);

        // установка выбранного провайдера и его конфига
        $provider = \mod_event3kl\providers::instance($formdata['provider']);
        $providerdata = $provider->mod_form_processing($formdata);
        $this->raw_set('provider', $provider->get_code());
        $this->raw_set('providerdata', json_encode($providerdata));
        unset($formdata['provider']);

        // установка выбранного формата и его конфига
        $format = \mod_event3kl\formats::instance($formdata['format']);
        $formatdata = $format->mod_form_processing($formdata);
        $this->raw_set('format', $format->get_code());
        $this->raw_set('formatdata', json_encode($formatdata));
        unset($formdata['format']);

        // установка выбранного способа определяни даты и времени заняти, и конфига этого способа
        $datemode = \mod_event3kl\datemodes::instance($formdata['datemode']);
        $datemodedata = $datemode->mod_form_processing($formdata);
        $this->raw_set('datemode', $datemode->get_code());
        if (!is_null($datemodedata)) {
            $this->raw_set('datemodedata', json_encode($datemodedata));
        }
        unset($formdata['datemode']);

        foreach ($formdata as $property => $value) {
            try {
                $this->raw_set($property, $value);
            } catch(\coding_exception $ex) {
                continue;
            }
        }

        return $this;
    }

    /**
     * Получение cm для текущего инстанса
     * @return \stdClass
     */
    public function obtain_cm() {
        return get_coursemodule_from_instance('event3kl', $this->get('id'));
    }

    /**
     * Получение контекста модуля для текущего инстанса
     * @return \context_module
     */
    public function obtain_module_context() {
        $cm = $this->obtain_cm();
        return \context_module::instance($cm->id);
    }

    public static function handle_course_module_created(\core\event\course_module_created $event) {

        if ($event->other['modulename'] !== 'event3kl') {
            return;
        }

        $cm = get_coursemodule_from_id('event3kl', $event->objectid, 0, false, MUST_EXIST);
        $event3kl = new self($cm->instance);
        $event3kl->actualize_sessions();

    }

    public static function handle_course_module_updated(\core\event\course_module_updated $event) {

        if ($event->other['modulename'] !== 'event3kl') {
            return;
        }

        $cm = get_coursemodule_from_id('event3kl', $event->objectid, 0, false, MUST_EXIST);
        $event3kl = new self($cm->instance);
        $event3kl->actualize_sessions();
    }

    /**
     * Update the module completion status (set it viewed) and trigger module viewed event.
     */
    public function set_module_viewed() {
        $completion = new completion_info($this->obtain_course_instance());
        $completion->set_module_viewed($this->obtain_cm());

        $params = [
            'objectid' => $this->get('id'),
            'context' => $this->obtain_module_context()
        ];

        $event = \mod_event3kl\event\course_module_viewed::create($params);

        $event->add_record_snapshot('event3kl', $this->to_record());
        $event->trigger();
    }

    /**
     * Getter for property 'course'
     * @return int
     */
    protected function get_course() {
        if (is_null($this->raw_get('course'))) {
            $this->set_course();
        }
        return $this->raw_get('course');
    }

    /**
     * Setter for property 'course'
     */
    protected function set_course() {
        $this->raw_set('course', $this->obtain_cm()->course);
    }

    /**
     * Getter for course instance record
     * @return mixed|stdClass|false
     */
    public function obtain_course_instance() {
        global $DB;
        return $DB->get_record('course', ['id' => $this->get('course')], '*', MUST_EXIST);
    }

    private function get_view_template_data(int $userid, $output) {

        global $PAGE;

        // способ задания даты/времени занятия
        $datemode = $this->get('datemode');
        // формат занятия
        $format = $this->get('format');

        ///////////////////////////////
        // предварительная валидация //
        ///////////////////////////////

        if ($datemode == 'relative_to_enrolment' && !in_array($format, relative_to_enrolment::get_suitable_formats())) {
            // дейтмод "относительно даты подписки" не у индивидуального формата - ошибка
            throw new \coding_exception(get_string('error_relative_to_enrolment_format', 'mod_event3kl'));
        }
        if ($datemode == 'opendate' && !in_array($format, opendate::get_suitable_formats())) {
            // дейтмод "свободное время" не у индивидуального формата - ошибка
            throw new \coding_exception(get_string('error_opendate_format', 'mod_event3kl'));
        }
        if ($datemode == 'vacantseat' && !in_array($format, vacantseat::get_suitable_formats())) {
            // дейтмод "время по заявке" не у формата подгрупп - ошибка
            throw new \coding_exception(get_string('error_vacantseat_format', 'mod_event3kl'));
        }

        /////////////////////////////////////////////
        // обработка форм, если вдруг отправлялись //
        /////////////////////////////////////////////

        $allsessions = session::get_records(['event3klid' => $this->get('id')]);
        $notifiactions = [];
        foreach ($allsessions as $session) {
            $customdata = [
                'sessionid' => $session->get('id'),
                'userid' => $userid
            ];

            // Форма выбора сессии
            $vacantseatselect = new vacantseat_select($PAGE->url, $customdata);
            $processresult = $vacantseatselect->process();
            if (!empty($processresult)) {
                // форма была обработана
                $notifiactions[$session->get('id')] = $processresult;
                // отправиться может только одна форма, остальное проверять бессмысленно - выходим
                break;
            }

            // Форма запроса даты сессии
            $opendaterequest = new opendate_request($PAGE->url, $customdata);
            $processresult = $opendaterequest->process();
            if (!empty($processresult)) {
                // форма была обработана
                $notifiactions[$session->get('id')] = $processresult;
                // отправиться может только одна форма, остальное проверять бессмысленно - выходим
                break;
            }

            // Форма согласования даты сессии
            $opendatecoordination = new opendate_coordination($PAGE->url, $customdata);
            $processresult = $opendatecoordination->process();
            if (!empty($processresult)) {
                // форма была обработана
                $notifiactions[$session->get('id')] = $processresult;
                // отправиться может только одна форма, остальное проверять бессмысленно - выходим
                break;
            }

            // Форма удаления сессии
            $deletesession = new delete_session($PAGE->url, $customdata);
            $processresult = $deletesession->process();
            if (!empty($processresult)) {
                // форма была обработана
                $notifiactions[$session->get('id')] = $processresult;
                // отправиться может только одна форма, остальное проверять бессмысленно - выходим
                break;
            }
        }


        ////////////////////////////////////////////////////////////////
        // формирование отображения сессий с уже обновленными данными //
        ////////////////////////////////////////////////////////////////

        $groupsessions = [];
        $eventusers = $this->get_event_users();
        foreach($eventusers as $eventgroupid => $eventgroup) {
            if (!array_key_exists($eventgroupid, $groupsessions)) {
                $ismanager = !empty($eventgroup['managers'][$userid]);
                $isspeaker = !empty($eventgroup['speakers'][$userid]);
                $groupsessions[$eventgroupid] = [
                    'event3klid' => $this->get('id'),
                    'group' => $eventgroup['group'],
                    'ismanager' => $ismanager,
                    'isspeaker' => $isspeaker,
                    'ismanagerorspeaker' => ($ismanager || $isspeaker),
                    'canparticipate' => !empty($eventgroup['members'][$userid]),
                    'sessions' => [],
                    'sessionscount' => 0
                ];
            }
        }

        $allsessions = session::get_records(['event3klid' => $this->get('id')]);
        $participantsessions = session::get_participant_sessions($this->get('id'), $userid);

        foreach ($allsessions as $session) {

            $hascontent = false;

            $sid = $session->get('id');
            $groupid = $session->get('groupid');

            $ismanager = $groupsessions[$groupid]['ismanager'];
            $isspeaker = $groupsessions[$groupid]['isspeaker'];
            $canparticipate = $groupsessions[$groupid]['canparticipate'];
            $ismember = $canparticipate && array_key_exists($sid, $participantsessions);

            $participatelink = null;
            // дата определена и просмотр страницы осуществляет один из участников занятия
            if (!empty($session->obtain_startdate()) && ($ismember || $isspeaker)) {
                // попытка запустить сессию, если проходит по условиям
                $session->try_start();
                // получение ссылки для присоединения к сессии, если выполняются все необходимые условия
                $participatelink = $session->try_get_participate_link($userid);
                $hascontent = true;
            }

            $sessiondata = $session->to_record();
            $sessiondata->realstartdate = $session->obtain_startdate();
            $sessiondata->notifications = [];
            $sessiondata->participatelink = $participatelink ?? null;
            $sessiondata->statusstr = get_string('session_status_'.$session->get('status'), 'mod_event3kl');
            $sessiondata->{'status_'.$session->get('status')} = true;

            if (array_key_exists($sid, $notifiactions)) {
                $sessiondata->notifications[] = $notifiactions[$sid]->export_for_template($output);
                $hascontent = true;
            }


            $customdata = [
                'sessionid' => $session->get('id'),
                'userid' => $userid
            ];

            // Форма выбора сессии
            $vacantseatselect = new vacantseat_select($PAGE->url, $customdata);
            if ($vacantseatselect->is_suitable()) {
                $sessiondata->vacantseatselect = $vacantseatselect->render();
                $hascontent = true;
            }
            // Форма запроса даты сессии
            $opendaterequest = new opendate_request($PAGE->url, $customdata);
            if ($opendaterequest->is_suitable()) {
                $sessiondata->opendaterequest = $opendaterequest->render();
                $hascontent = true;
            }
            // Форма согласования даты сессии
            $opendatecoordination = new opendate_coordination($PAGE->url, $customdata);
            if ($opendatecoordination->is_suitable()) {
                $sessiondata->opendatecoordination = $opendatecoordination->render();
                $hascontent = true;
            }

            // Форма удаления сессии
            $deletesession = new delete_session($PAGE->url, $customdata);
            $deletesessionhtml = $deletesession->render();
            if (!empty($deletesessionhtml)) {
                $sessiondata->deletesessionform = $deletesessionhtml;
                $hascontent = true;
            }

            $records = [];
            if ($isspeaker || $ismember || $ismanager) {
                $fs = get_file_storage();
                $files = $fs->get_area_files($this->obtain_module_context()->id, 'mod_event3kl', 'sessionrecord', $session->get('id'));
                foreach ($files as $file) {
                    $filename = $file->get_filename();
                    $fileurl = \moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
                    $records[] = [
                        'url' => $fileurl->out(),
                        'name' => $filename
                    ];
                }
                $hascontent = true;
            }
            $sessiondata->records = $records;
            $sessiondata->recordscount = count($records);

            // количество участников сессии
            $members = session_member::get_records(['sessionid' => $sid]);
            $sessiondata->memberscount = count($members);

            // Модалка для подтверждения остановки сессии
            $a = new \stdClass();
            $a->sessionname = $sessiondata->name;
            $sessiondata->stopmtitle = get_string('stop_session_modal_title','mod_event3kl', $a);
            $sessiondata->stopmbody = get_string('stop_session_modal_body', 'mod_event3kl');
            $sessiondata->stopmcancel = get_string('cancel');
            $sessiondata->stopmstop = get_string('stop_event_session','mod_event3kl');

            // если в текущей сессии нет прав на управление и выступления,
            // а пользователь не является участником сессии и не может выбрать эту сессию для участия в ней
            if (!$ismanager && !$isspeaker && !$ismember && !$hascontent) {
                // сессия нам не интересна, мы её пропускаем
                continue;
            }

            $groupsessions[$groupid]['sessions'][] = $sessiondata;
            $groupsessions[$groupid]['sessionscount'] = count($groupsessions[$groupid]['sessions']);
        }

        // удаляем пустые группы, если не менеджер
        foreach ($groupsessions as $gs => $groupsession) {
            if ($groupsession['sessionscount'] == 0 && !$groupsession['ismanager']) {
                unset($groupsessions[$gs]);
            }
        }

        //         менеджерить можно только сессии в статусе план
        //         в негрупповом формате преподаватель не может регулировать состав участников сессии (определяются автоматически), не может указывать максимальное количество пользователей (проставляется автоматически при изменении состава), но может менять даты, если статус сессии - план

        return [
            'datemode_'.$datemode => true,
            'format_'.$format => true,
            'groups' => array_values($groupsessions)
        ];
    }

    public function render_view(\renderer_base $output) {
        global $USER;

        $templatedata = [];

        $notifications = [];
        try {
            $templatedata = $this->get_view_template_data($USER->id, $output);
            if (empty($templatedata['groups'])) {
                $notificationtext = get_string('error_no_view_capabilities', 'mod_event3kl');
                $notification = new notification($notificationtext, notification::NOTIFY_INFO);
                $notifications[] = $notification->export_for_template($output);
            }
        } catch(\Exception $ex) {
            $notificationtext = $ex->getMessage();
            $notification = new notification($notificationtext, notification::NOTIFY_ERROR);
            $notifications[] = $notification->export_for_template($output);
        }
        $templatedata['notifications'] = $notifications;
//         var_dump($templatedata);exit;
        return $output->render_from_template('mod_event3kl/view', $templatedata);
    }

    /**
     * Получение массива данных о пользователях и их правах в разрезе групп в занятии
     *
     * @param boolean $accessallgroupsoutofcourse - если true, то в списке менеджеров будут также те,
     *                  кто имеет права moodle/site:accessallgroups + mod/event3kl:managesessions, но даже не подписан на курс
     *                  если false, то в списки попадут только те, кто имеет право в группе или право + accessallgroups, но при этом подписан на курс
     * @return array
     */
    public function get_event_users($accessallgroupsoutofcourse=true) {

        $eventusers = [];

        $course = get_course($this->get('course'));
        $contextmodule = $this->obtain_module_context();
        // без ребилда $cminfo содержала неактуальные данные относительно группового режима
        rebuild_course_cache($course->id, true);
        $modinfo = get_fast_modinfo($course);
        $cminfo = $modinfo->get_cm($contextmodule->instanceid);

        // получаем всех, кто мог бы участвовать в мероприятии
        $participants = get_users_by_capability($contextmodule, 'mod/event3kl:participateevent');
        // получаем всех, кто мог бы быть спикером в мероприятии
        $speakers = get_users_by_capability($contextmodule, 'mod/event3kl:speakatevent');
        // получаем всех, кто мог бы управлять сессиями мероприятия
        $managers = get_users_by_capability($contextmodule, 'mod/event3kl:managesessions');
        // получаем всех, кто имеет право доступа ко всем группам
        $allgroupsusers = get_users_by_capability($contextmodule, 'moodle/site:accessallgroups');

        // сюда будем собирать пользователей, которых уже обработали (попали хотя бы в одну сессию)
        $processedusers = [];
        // группы, под которые будут созданы отдельные сессии
        $groups = [];
        // группа Нет групп
        $groupsnone = (object)['id' => -1, 'name' => get_string('groupsnone')];
        // массив групп будет заполнен только если включен групповой режим
        // если не включен - массив останется пустым
        // все кто не попал ни в одну группу, впоследствии будут объединены в отдельную сессию (без группы)
        // соответственно, частный случай - режим без групп, все участники попадут в отдельную сессию (без группы)
        if ($cminfo->effectivegroupmode != NOGROUPS) {
            // TODO: заменить на groups_get_all_groups, оказывается то что ниже - deprecated
            $groups = groups_get_all_groups_for_courses([$course]);
        }
        $coursegroups = $groups[$course->id] ?? [];


        foreach($coursegroups as $group) {
            $gid = $group->id;
            if (!array_key_exists($gid, $eventusers)) {
                $eventusers[$gid] = [
                    'group' => $group,
                    'members' => [],
                    'speakers' => [],
                    'managers' => []
                ];
            }
            $groupmembers = groups_get_groups_members([$gid]);
            // фильтруем, оставим только тех, кто может принимать участие в занятии
            foreach($groupmembers as $groupmember) {
                $uid = $groupmember->id;
                if (array_key_exists($uid, $participants)) {
                    $eventusers[$gid]['members'][$uid] = $participants[$uid];
                }
                if (array_key_exists($uid, $speakers)) {
                    $eventusers[$gid]['speakers'][$uid] = $speakers[$uid];
                }
                if (array_key_exists($uid, $managers)) {
                    $eventusers[$gid]['managers'][$uid] = $managers[$uid];
                }
                // собираем пользователей, которые были добавлены хотя бы в одну из сессий - в отдельный массив
                if (!in_array($uid, $processedusers)) {
                    $processedusers[] = $uid;
                }
            }

            // те пользователи, у которых есть право доступа ко всем группам, и они подписаны на курс
            // (в $participants, $speakers, $managers попадают только из контекста курса)
            // условно - преподаватели, которым мы предоставили возможность лазать во все группы
            foreach($allgroupsusers as $allgroupsuser) {
                $uid = $allgroupsuser->id;
                if (array_key_exists($uid, $participants)) {
                    $eventusers[$gid]['members'][$uid] = $participants[$uid];
                }
                if (array_key_exists($uid, $speakers)) {
                    $eventusers[$gid]['speakers'][$uid] = $speakers[$uid];
                }
                if (array_key_exists($uid, $managers)) {
                    $eventusers[$gid]['managers'][$uid] = $managers[$uid];
                }
            }
        }
        // убираем пользователей, которые попали хотя бы в одну из групповых сессий
        // чтобы остались только те, кто вне групп
        foreach($processedusers as $userid) {
            if (array_key_exists($userid, $participants)) {
                unset($participants[$userid]);
            }
            if (array_key_exists($userid, $speakers)) {
                unset($speakers[$userid]);
            }
            if (array_key_exists($userid, $managers)) {
                unset($managers[$userid]);
            }
        }

        // если остались пользователи, то это пользователи без группы,
        // они должны обрабатываться как бы отдельной группой, поэтому
        // создаем для них отдельную сессию
        if (!empty($participants) || !empty($speakers) || !empty($managers)) {
            $eventusers[-1] = [
                'group' => $groupsnone,
                'members' => $participants,
                'speakers' => $speakers,
                'managers' => $managers
            ];
        }

        // те пользователи, у которых есть право доступа ко всем группам, и они подписаны на курс
        // (в $participants, $speakers, $managers попадают только из контекста курса)
        // условно - преподаватели, которым мы предоставили возможность лазать во все группы
        foreach($allgroupsusers as $allgroupsuser) {
            $uid = $allgroupsuser->id;
            if (array_key_exists($uid, $participants)) {
                $eventusers[-1]['members'][$uid] = $participants[$uid];
            }
            if (array_key_exists($uid, $speakers)) {
                $eventusers[-1]['speakers'][$uid] = $speakers[$uid];
            }
            if (array_key_exists($uid, $managers)) {
                $eventusers[-1]['managers'][$uid] = $managers[$uid];
            }
        }

        if ($accessallgroupsoutofcourse) {
            // а здесь обрабатываются даже те пользователи, которые не подписаны на курс, и имеют право accessallgroups
            // условно - админы
            //
            // для решения задачи, здесь не проверяем $participants, $speakers, $managers, а проверяем через has_capability,
            // есть ли у пользователя комплект прав moodle/site:accessallgroups + mod/event3kl:managesessions
            // тогда даем ему возможность менеджерить
            //
            // но благодаря флагу $accessallgroupsoutofcourse можем иногда их не брать в расчет, чтобы например не отправлять уведомления
            // право выступать на занятии или принимать участие тоже не даем, чтобы не ломать состав участников
            foreach($allgroupsusers as $allgroupsuser) {
                $uid = $allgroupsuser->id;

                if (has_capability('mod/event3kl:managesessions', $contextmodule, $uid)) {
                    foreach($coursegroups as $group) {
                        $eventusers[$group->id]['managers'][$uid] = $allgroupsuser;
                    }
                    if (!array_key_exists(-1, $eventusers)) {
                        $eventusers[-1] = [
                            'group' => $groupsnone,
                            'members' => [],
                            'speakers' => [],
                            'managers' => []
                        ];
                    }
                    $eventusers[-1]['managers'][$uid] = $allgroupsuser;
                }
            }
        }

        return $eventusers;
    }


    public function is_manager($userid, $groupid) {
        $eventusers = $this->get_event_users();
        return array_key_exists($userid, $eventusers[$groupid]['managers']);
    }

    public function is_speaker($userid, $groupid) {
        $eventusers = $this->get_event_users();
        return array_key_exists($userid, $eventusers[$groupid]['speakers']);
    }

    public function is_potential_member($userid, $groupid) {
        $eventusers = $this->get_event_users();
        return array_key_exists($userid, $eventusers[$groupid]['members']);
    }

    protected function after_delete($result){

        if ($result){

            // Получаем сессии для удаленного модуля
            $sessions = session::get_records( ['event3klid' => $this->raw_get('id')]);
            // Удаляем записи о сессиях
            foreach ($sessions as $session){
                $session->delete();
            }
        }
    }

    public function can_edit_attendance($userid, $groupid){

        $contextmodule = $this->obtain_module_context();

        // проверяем право редактировать посещаемость, если его нет то дальше нет смысла проверять
        if (! has_capability('mod/event3kl:managesessionattendance', $contextmodule, $userid)){
            return false;
        }
        // Редактировать посещаемость может только пользователь, имеющий отношение к сессии
        if ($this->is_speaker($userid, $groupid)){
            return true;
        }
        if ($this->is_manager($userid, $groupid)){
            return true;
        }
        if ($this->is_potential_member($userid, $groupid)){
            return true;
        }
        return false;
    }

    public function get_exist_sessions_data() {

        // соберем массив уже существующих данных по этому занятию для актуализации, если потребуется
        $exist = [
            // нам потребуется быстрый доступ к сессиям по идентификатору
            'sessions' => [],
            // нам потребуется легко находить существует ли уже сессия для группы (+пользователя)
            'groups' => [],
            // нам может потребоваться быстрый доступ к участникам сессии по идентификатору
            'sessionmembers' => [],
        ];

        $sessions = session::get_records(['event3klid' => $this->get('id')]);
        foreach($sessions as $session) {
            // идентификатор сессии
            $sid = $session->get('id');
            // идентификатор группы
            $gid = $session->get('groupid');

            $exist['sessions'][$sid] = $session;
            // собираем все существующие группы и пользователей
            if (!array_key_exists($gid, $exist['groups'])) {
                $exist['groups'][$gid] = [
                    'users' => [],
                    'session' =>  &$exist['sessions'][$sid],
                ];
            }

            // участники сессии
            $sessionmembers = session_member::get_records(['sessionid' => $sid]);
            foreach($sessionmembers as $sessionmember) {
                // идентификатор участника сессии
                $smid = $sessionmember->get('id');
                // идентификатор пользователя
                $uid = $sessionmember->get('userid');

                $exist['sessionmembers'][$smid] = $sessionmember;

                if (!array_key_exists($uid, $exist['groups'][$gid]['users'])) {
                    $exist['groups'][$gid]['users'][$uid] = [
                        'sessionmember' => &$exist['sessionmembers'][$smid],
                        'session' =>  &$exist['sessions'][$sid],
                    ];
                }
            }
        }

        return $exist;
    }

    public function actualize_sessions() {

        // создание нужных сессий, соответствующих формату занятия
        $format = formats::instance($this->raw_get('format'));
        $format->actualize_sessions($this);

        //////////////////////////////////////////////////////////////////////////
        // удаление сессий и участников, не соответствующих незыблемым правилам //
        //////////////////////////////////////////////////////////////////////////

        // существующие данные по текущему занятию (сессии и их участники)
        $exist = $this->get_exist_sessions_data();
        // потенциальные участники
        $eventusers = $this->get_event_users();

        // удаление участников сессии, более не имеющих прав в группе сессии
        foreach($exist['sessionmembers'] as $sessionmember) {
            $sid = $sessionmember->get('sessionid');
            $session = $exist['sessions'][$sid] ?? new session($sid);
            $groupid = $session->get('groupid');

            // актуализировать будем только планируемые сессии. все остальные - пропускаем
            if ($session->get('status') != 'plan') {
                continue;
            }

            if (!array_key_exists($groupid, $eventusers) ||
                !array_key_exists('members', $eventusers[$groupid]) ||
                !array_key_exists($sessionmember->get('userid'), $eventusers[$groupid]['members'])) {

                    // в потенциальных участниках пользователя (уже являющегося ранее участником) - нет
                    // значит у него нет больше прав участвовать в занятии
                    // отписываем от сессии
                    $sessionmember->delete();
                }
        }

        // удаление сессий, чьи группы больше не существуют
        // в случае, если был изменён групповой режим, то группы для сессии, созданной вручную может больше не существовать
        // необходимо удалить сессии, принадлежащие несуществующим более группам
        foreach(array_keys($exist['sessions']) as $sid) {

            // убеждаемся, что сессия еще существует после удаления участников
            try {
                $session = new session($sid);
            } catch(\Exception $ex) {
                // видимо, сессия уже удалена - идем дальше
                continue;
            }

            // актуализировать будем только планируемые сессии. все остальные - пропускаем
            if ($session->get('status') != 'plan') {
                continue;
            }

            // той группы, которая указана в сессии, больше нет - значит и сессии больше не будет
            if (!array_key_exists($session->get('groupid'), $eventusers)) {
                $session->delete();
            }


            // Если формат не подгруппы - проверяем есть ли в сессии участники
            // если участников нет - это повод удалить сессию (в формате подгрупп могут быть пустые)
            $event3kl = $session->obtain_event3kl();
            if ($event3kl->get('format') != 'manual') {
                $members = session_member::get_records(['sessionid' => $sid]);
                if (count($members) == 0) {
                    $session->delete();
                }
            }
        }
    }
}