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

namespace mod_event3kl\format;

defined('MOODLE_INTERNAL') || die();

use mod_event3kl\format\base\format_interface;
use mod_event3kl\format\base\abstract_format;
use Exception;
use moodle_url;
use core\notification;
use mod_event3kl\event3kl;
use mod_event3kl\session;
use mod_event3kl\session_member;
use mod_event3kl\datemode\base\abstract_datemode;
use mod_event3kl\datemodes;

/**
 * Класс общего формата занятия
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class individual extends abstract_format implements format_interface {

    /**
     * {@inheritDoc}
     * @see \mod_event3kl\format\base\format_interface::mod_form_definition()
     */
    public function mod_form_definition(\MoodleQuickForm &$mform, \mod_event3kl_mod_form &$form) {

    }
    /**
     * общий метод валидации формы настроек инстанса в части элементов формы формата
     */
    public function mod_form_validation() {
        return [];
    }
    /**
     * {@inheritDoc}
     * @see \mod_event3kl\format\base\format_interface::mod_form_processing()
     */
    public function mod_form_processing(array $formdata) {
        return [];
    }

    /**
     * {@inheritDoc}
     * @see \mod_event3kl\format\base\abstract_format::actualize_sessions()
     */
    public function actualize_sessions(event3kl $event3kl) {

        // описание логики для формата:
        // у каждого участника занятия своя индивидуальная сессия
        // сессия может состоять максимум из одного участника



        // при смене формата надо внимательно проверять не было ли в одной сессии несколько участников
        // и если было, то перепривязывать участников к новой сессии
        $sessionsinuse = [];
        // существующие данные по текущему занятию (сессии и их участники)
        $exist = $event3kl->get_exist_sessions_data();

        foreach($event3kl->get_event_users() as $groupid => $groupdata) {
            $groupmembers = $groupdata['members'];

            // обработка участников группы для превращения в участников сессии
            foreach($groupmembers as $groupmember) {
                // получение существующей записи участника сессии для текущего пользователя и группы в этом занятии
                $existuser = $exist['groups'][$groupid]['users'][$groupmember->id] ?? null;

                // определяем дефолтную дату сессии в соответствии с настройками занятия,
                $datemode = datemodes::instance($event3kl->get('datemode'), $event3kl, $groupid, $groupmember->id);
                $defaultstartdate = $datemode->get_start_date();

                // здесь проверяем существование пользователя, а не группы, потому что
                // сессия создается под каждого пользователя, а не под каждую группу
                if (is_null($existuser) || in_array($existuser['session']->get('id'), $sessionsinuse)) {
                    // сессии для этой группы еще не существует

                    // создаем сессию, передаем её дефолтные свойства
                    $session = new session();
                    $session->from_array([
                        'offereddate' => null, // при создании предложенная участником дата всегда не определена
                        'event3klid' => $event3kl->get('id'), // идентификатор инстанса модуля
                        'groupid' => $groupid, // идентификатор группы (-1 если без групп)
                        'status' => 'plan', // начальный статус - план
                        'startdate' => $defaultstartdate, // дефолтная дата начала сессии занятия
                    ]);
                } else {
                    // сессия для этого пользователя уже существует - получаем объект сессии для редактирования (если потребуется)
                    $session = $existuser['session'];
                    $sessionsinuse[] = $session->get('id');
                }

                // редактируем свойства запланированной сессии (состоявшиеся не трогаем)
                // сохранение позднее произойдёт только в случае, если данные действительно поменялись
                if ($session->get('status') == 'plan') {
                    $session->from_array([
                        // в индивидуальном формате нет возможности задавать собственные названия сессиям
                        'name' => fullname($groupmember),
                        // в индивидуальном формате всегда максимум один участник сессии
                        'maxmembers' => 1,
                        // дефолтная дата старта сессии, сформированная на основании настроек инстанса
                        'startdate' => $defaultstartdate
                    ], false);
                }
                // сохранение изменений в базу и получение идентификатора сессии
                $sid = $session->save_modified();
                if (array_key_exists($sid, $exist['sessions'])) {
                    // сессия обработана - удалим её из списка,
                    // чтобы позднее вычислить те, которые не попали в выборку, потому что стали не актуальными
                    unset($exist['sessions'][$sid]);
                }


                if (is_null($existuser)) {
                    // участника сессии еще не существует
                    // создаем участника сессии - в данном формате он всегда один
                    $sessionmember = new session_member();
                } else {
                    // сессия для этого пользователя в этой группе уже существует
                    $sessionmember = $existuser['sessionmember'];
                }
                // даже для уже существующего участника могла смениться сессия в случае, если была смена формата
                $sessionmember->from_array([
                    'userid' => $groupmember->id,
                    'sessionid' => $sid
                ]);

                // сохранение изменений в базу и получение идентификатора участника сессии
                $smid = $sessionmember->save_modified();
                if (array_key_exists($smid, $exist['sessionmembers'])) {
                    // участник сессии обработан - удалим его из списка,
                    // чтобы позднее вычислить тех, которые не попали в выборку, потому что стали не актуальными
                    unset($exist['sessionmembers'][$smid]);
                }
            }
        }

        // велик соблазн написать здесь удаление сессий (а участники сесси сами удаляться), но нельзя
        // потому что если до этого использовался другой формат, у которого участников в сессии может быть много, то
        // мы не будем трогать сессию, которую модифицировали и в ней останется много участников, а такого быть не должно

        // удаляем всех участников сесси1, которые не были затронуты в результате создания/обновления до актуальных данных
        // потому что оставшиеся участники сессии перестали отвечать условиям и по правилам формата не дожны больше существовать
//         foreach($exist['sessionmembers'] as $sessionmember) {
//             $sid = $sessionmember->get('sessionid');
//             $session = $exist['sessions'][$sid] ?? new session($sid);
//             // редактируем и удаляем только планируемые сессии, состоявшиеся не трогаем
//             if ($session->get('status') == 'plan') {
//                 // если после удаления участника сессии, в сессии больше не останется участников, то
//                 // это повлечет за собой и удаление самой сессии,
//                 // поэтому за них мы не волнуемся и отдельно не удаляем
//                 $sessionmember->delete();
//             }
//         }


    }
}