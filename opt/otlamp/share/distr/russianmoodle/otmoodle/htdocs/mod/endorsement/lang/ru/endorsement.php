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
 * Plugin strings are defined here.
 *
 * @package     mod_endorsement
 * @category    string
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Отзыв о курсе';
$string['modulename'] = 'Отзыв о курсе';
$string['modulename_help'] = 'С помощью элемента курса «Отзыв о курсе» студенты могут оставлять отзывы о текущем курсе. Система направляет уведомления о новых отзывах модераторам, которые через «Панель модератора отзывов» могут одобрять или отклонять отзывы студентов.';
$string['modulenameplural'] = 'Отзывы о курсе';
$string['endorsementname'] = 'Название элемента';
$string['endorsementname_help'] = '';
$string['pluginadministration'] = 'Управление плагином отзывов о курсе';
$string['missingidandcmid'] = 'Просмотр страницы не возможен без указания обязательных параметров';

$string['endorsement:addinstance'] = 'Добавлять модуль "Отзыв на курс" в курс';
$string['endorsement:moderate_endorsements'] = 'Модерировать отзывы';
$string['endorsement:view_endorsements'] = 'Просматривать отзывы всех пользователей';
$string['endorsement:to_endorse'] = 'Оставлять отзыв';
$string['endorsement:view_new_own_endorsements'] = 'Просматривать собственные отзывы, которые еще не были промодерированы';
$string['endorsement:view_accepted_own_endorsements'] = 'Просматривать собственные отзывы, которые были приняты модератором';
$string['endorsement:view_rejected_own_endorsements'] = 'Просматривать собственные отзывы, которые были отклонены модератором';
$string['endorsement:receive_notifications'] = 'Получать уведомления об отзывах. Позволяет настраивать провайдер сообщений';
$string['endorsement:receive_new_endorsement_notification'] = 'Получать уведомление о новом отзыве';
$string['messageprovider:new_endorsement'] = 'Уведомление о новом отзыве';

$string['endorsement_list'] = 'Отзывы всех пользователей';
$string['user_list_header'] = 'Ваши отзывы';
$string['user_items_header'] = 'Ваши отзывы';
$string['onemore'] = 'Добавить новый отзыв';
$string['endorsement_form_field_endorsement'] = 'Ваш отзыв';
$string['endorsement_form_field_save'] = 'Сохранить';
$string['endorsement_form_field_cancel'] = 'Вернуться назад';
$string['endorsement_save_failed'] = 'К сожалению, не удалось сохранить ваш отзыв. Попробуйте отправить его еще раз, пожалуйста.';
$string['endorsement_was_empty'] = 'Текст отзыва не был получен. Пожалуйста, убедитесь, что вы ввели текст отзыва перед отправой формы.';
$string['endorsement_status_not_isset'] = 'Статус отзыва не был получен. Пожалуйста, убедитесь, что вы корретно отправили форму.';
$string['endorsement_publication_success'] = 'Ваш отзыв был успешно сохранен и отправлен на модерацию. Благодарим вас за проявленный интерес.';
$string['endorsement_endorse_access_denied'] = 'К сожалению, у вас нет доступа к публикации отзыва';

$string['endorsement_list_page_title'] = 'Панель модератора отзывов';
$string['endorsement_list_page_heading'] = 'Панель модератора отзывов';
$string['moderator_list_header'] = 'Панель модератора отзывов';
$string['moderator_items_header'] = 'Отзывы всех пользователей';
$string['filter_statuses'] = 'Фильтрация:';
$string['filter_courses'] = 'Текущий курс:';

$string['endorsement_status_new'] = 'Не проверено';
$string['endorsement_status_rejected'] = 'Отклонено';
$string['endorsement_status_accepted'] = 'Одобрено';
$string['endorsement_status_all'] = 'Любой статус';

$string['moderate_link_text'] = 'Модерация отзывов';
$string['message__new_endorsement__subject'] = 'Новый отзыв в курсе "{$a->coursefullname}"';
$string['message__new_endorsement__smallmessage'] = 'Пользователь {$a->userfullname} оставил новый отзыв в "{$a->coursefullname}"';
$string['message__new_endorsement__fullmessage'] = '<p>Пользователь {$a->userfullname} оставил новый отзыв в курсе "{$a->coursefullname}".</p><p>Текст отзыва: <br/>"{$a->endorsementcontent}".</p>{$a->moderatelink}';

$string['removeallendorsementfeedbacks'] = 'Удалить все отзывы, оставленные через этот элемент курса';
$string['feedbacks_deleted'] = 'Отзывы, оставленные через элемент "{$a}" удалены';

$string['feedback_source'] = 'Источник отзыва';
$string['mod_view_no_data'] = 'К сожалению, вам не доступна возможность добавлять или просматривать (модерировать) отзывы.';
