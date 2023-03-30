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
 * Strings for component 'block_mastercourse', language 'ru'
 *
 * @package    block_mastercourse
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Согласование мастер-курса';

$string['mastercourse:addinstance'] = 'Добавлять блок "Согласование мастер-курса"';
$string['mastercourse:myaddinstance'] = 'Добавлять блок "Согласование мастер-курса" в личный кабинет';
$string['mastercourse:request_verification'] = 'Отправлять запрос на согласование новой версии мастер-курса для дисциплины';
$string['mastercourse:respond_requests'] = 'Принимать решение по согласованию версии мастер-курса для дисциплины';
$string['mastercourse:receive_notification_of_requests'] = 'Получать уведомления о новых запросах на согласование мастер-курса для дисциплины';
$string['mastercourse:receive_notification_of_responses'] = 'Получать уведомления о решениях по согласованию мастер-курса для дисцпилины';
$string['mastercourse:view_mastercourse'] = 'Видеть ссылку на мастер-курс';

$string['mastercourse_title'] = 'Мастер-курс';
$string['mastercourse_verification_requested_message'] = '{$a->initiator} отправил текущую версию мастер-курса "{$a->course}" на проверку для дисциплины "{$a->discipline}"';
$string['mastercourse_accepted_mail_text'] = 'Представленная на проверку версия мастер-курса "{$a->course}" одобрена для дисциплины "{$a->discipline}"';
$string['mastercourse_declined_mail_text'] = 'Представленная на проверку версия мастер-курса "{$a->course}" отклонена для дисциплины "{$a->discipline}"';

$string['config_display_navbar_caption'] = 'Отображать метку в хлебных крошках';
$string['config_display_navbar_caption_desc'] = 'Если курс является мастер-курсом, в хлебных крошках будет отображена соответствующая пометка';
$string['config_display_verification_panel_caption'] = 'Отображать метку в панели согласования';
$string['config_display_verification_panel_caption_desc'] = 'Если курс является мастер-курсом, в панели согласования будет отображена соответствующая пометка';

$string['mastercourse:manage_publication'] = 'Управлять публикацией курса';
$string['config_display_publication_panel_caption'] = 'Отображать панель публикации курса';
$string['config_display_publication_panel_caption_desc'] = 'В блоке может быть отображена панель публикации курсо во внешние системы';


$string['page__publication'] = 'Публикация курса';
$string['page__publication__back_to_the_course'] = 'Вернуться в курс';

$string['service__nmfo'] = 'Портал непрерывного медицинского и фармацевтического образования Минздрава России';
$string['service__nmfo__status__not_published'] = 'Не опубликовано';
$string['service__nmfo__status__sent_for_publication'] = 'Отправлено на публикацию';
$string['service__nmfo__status__on_review'] = 'На просмотре';
$string['service__nmfo__status__published'] = 'Опубликовано';
$string['service__nmfo__status__rejected'] = 'Отказано в публикации';
$string['service__nmfo__status__sent_to_unpublish'] = 'Отправлено на снятие с публикации';
$string['service__nmfo__status__error'] = 'Ошибка';

$string['form_publication__field__submit'] = 'Отправить';
$string['form_publication__field__current_status'] = 'Текущий статус';
$string['form_publication__field__new_status'] = 'Установить статус';
$string['current_status_wrapper'] = 'Текущий: {$a}';

$string['form_publication__error__status_not_available'] = 'Запрашиваемый статус недоступен';

$string['course_publication_only'] = 'Публикация курсов предусмотрена только для блоков, добавленных непосредственно в курс';
