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
use mod_event3kl\datemodes;
use mod_event3kl\session_member;

/**
 * Класс общего формата занятия
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class common extends abstract_format implements format_interface {

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
        // с учетом группового режима выбираются группы
        // для каждой группы создается сессия
        // в каждую сессию подключаются участники группы
        // все кто остался вне групп объединяются в отдельную сессию
        // в режиме без групп все всегда попадают в эту отдельную сессию


        // существующие данные по текущему занятию (сессии и их участники)
        $exist = $event3kl->get_exist_sessions_data();

        foreach($event3kl->get_event_users() as $groupid => $groupdata) {
            $group = $groupdata['group'];
            $groupmembers = $groupdata['members'];

            // определяем дефолтную дату сессии в соответствии с настройками занятия,
            $datemode = datemodes::instance($event3kl->get('datemode'), $event3kl, $groupid);
            $defaultstartdate = $datemode->get_start_date();

            // получение существующей сессии для текущей группы в этом занятии
            $existgroup = $exist['groups'][$groupid] ?? null;
            if (is_null($existgroup)) {
                // сессии для этой группы еще не существует

                // создаем сессию, передаем её дефолтные свойства
                $session = new session();
                $session->from_array([
                    'event3klid' => $event3kl->get('id'), // идентификатор инстанса модуля
                    'groupid' => $groupid, // идентификатор группы
                    'status' => 'plan', // начальный статус - план
                    'startdate' => $defaultstartdate, // дефолтная дата начала сессии занятия
                ]);
            } else {
                // сессия для этой группы уже существует - получаем объект сессии для редактирования (если потребуется)
                $session = $existgroup['session'];
            }

            // редактируем свойства запланированной сессии (состоявшиеся не трогаем)
            // сохранение позднее произойдёт только в случае, если данные действительно поменялись
            if ($session->get('status') == 'plan') {
                $session->from_array([
                    // в бщем формате нет возможности задавать собственные названия сессиям
                    'name' => $group->name,
                    // общий формат не поддерживает режим с согласованием дат, поэтому всегда null
                    'offereddate' => null,
                    // в общем формате максимальное число участников соответствует количеству участников группы
                    'maxmembers' => count($groupmembers),
                    // дефолтная дата старта сессии, сформированная на основании настроек инстанса
                    'startdate' => $defaultstartdate
                ], false);
            }
            // сохранение изменений в базу и получение идентификатора сессии
            $sid = $session->save_modified();
//             if (array_key_exists($sid, $exist['sessions'])) {
//                 // сессия обработана - удалим её из списка,
//                 // чтобы позднее вычислить те, которые не попали в выборку, потому что стали не актуальными
//                 unset($exist['sessions'][$sid]);
//             }


            // обработка участников группы для превращения в участников сессии
            foreach($groupmembers as $groupmember) {

                // получение существующей записи участника сессии для текущего пользователя и группы в этом занятии
                $existuser = $existgroup['users'][$groupmember->id] ?? null;
                if (is_null($existuser)) {
                    // участника сессии еще не существует

                    // создаем участника сессии
                    $sessionmember = new session_member();
                    $sessionmember->from_array([
                        'userid' => $groupmember->id,
                        'sessionid' => $sid
                    ]);

                } else {

                    // сессия для этого пользователя в этой группе уже существует
                    $sessionmember = $existuser['sessionmember'];
                }
                // сохранение изменений в базу и получение идентификатора участника сессии
                // сохранение произойдёт только в случае, если данные действительно поменялись
                $smid = $sessionmember->save_modified();
                if (array_key_exists($smid, $exist['sessionmembers'])) {
                    // участник сессии обработан - удалим его из списка,
                    // чтобы позднее вычислить тех, которые не попали в выборку, потому что стали не актуальными
                    unset($exist['sessionmembers'][$smid]);
                }
            }
        }

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