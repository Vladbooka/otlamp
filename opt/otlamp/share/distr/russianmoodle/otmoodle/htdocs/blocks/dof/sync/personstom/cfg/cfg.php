<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
//                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

$sync_personstom = array();
// принудительная смена логина
$sync_personstom['autochangelogin'] = false; //выключена

// пытаться ли автоматически совершить привязку пользователя к персоне, сопоставляя обе записи по полю email
$sync_personstom['autolink_by_email'] = true;

// если настроена автоматическая привязка по email,
// и найдено несколько подходящих вариантов
// (в Moodle есть опция, разрешающая регистрировать пользователей с одинаковым email)
// 0 - не привязывать
// 1 - создать еще одного пользователя ( только если в moodle разрешено заводить несколько пользователей с одинаковым email )
// 2 - привязать к любому
$sync_personstom['autolink_double'] = 0;

// требуется ли отправлять уведомление о новом пароле после создания пользователя с паролем
$sync_personstom['set_password_notification_required'] = false;
// разрешено ли обновлять пароль для уже существующего пользователя
$sync_personstom['update_password_allowed'] = true;
// требуется ли отправлять уведомление о новом пароле после обновления
$sync_personstom['update_password_notification_required'] = false;

// причины, позволяющие синхронизировать персону ЭД в Moodle
// person_is_active:       true        - активация персоны является поводом синхронизировать персону ЭД в Moodle
//                         false       - активация персоны не является поводом синхронизировать персону ЭД в Moodle
// contract_is_active:     true        - активация договора является поводом синхронизировать студента по договору в Moodle
//                         false       - активация договора не является поводом синхронизировать студента по договору в Moodle
// cpassed_is_active:      true        - активация подписки на дисциплину является поводом синхронизировать студента по подписке в Moodle
//                         false       - активация подписки на дисциплину не является поводом синхронизировать студента по подписке в Moodle
// eagreeement_is_active:  true        - активация договора с сотрудником является поводом синхронизировать студента по подписке в Moodle
//                         false       - активация договора с сотрудником не является поводом синхронизировать студента по подписке в Moodle
// appointment_is_active:  true или [] - активация должностного назначения является поводом синхронизировать сотрудника в Moodle
//                                       если массив не пуст, то значения воспринимаются как идентификаторы должностей, по которым дозволена синхронизация
//                         false       - активация должностного назначения не является поводом синхронизировать сотрудника в Moodle
$sync_personstom['sync_reason'] = [
    'person_is_active' => false,
    'contract_is_active' => true,
    'cpassed_is_active' => false,
    'eagreeement_is_active' => true,
    'appointment_is_active' => false
];
// причины, позволяющие не удалять пользователя Moodle
// пока только запланировано к реализации
$sync_personstom['keep_synced_reason'] = [
    'person_is_active' => true,
    'contract_is_active' => false,
    'cpassed_is_active' => false,
    'eagreeement_is_active' => true,
    'appointment_is_active' => false
];

?>