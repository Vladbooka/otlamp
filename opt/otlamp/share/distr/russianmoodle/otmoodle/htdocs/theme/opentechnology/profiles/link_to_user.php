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
 * Тема СЭО 3KL. Привязка профиля к пользователю
 *
 * @package    theme_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');

// Идентификатор пользователя для привязки
$userid = optional_param('userid', $USER->id, PARAM_INT);
// Страница для возврата после привязки
$returnto = optional_param('returnto', new moodle_url(''), PARAM_URL);

// Текущий URL страницы
$pageurl = new moodle_url(
    '/theme/opentechnology/profiles/link_to_user.php',
    [
        'userid' => $userid,
        'returnto' => $returnto
    ]
);

// Получение текущего контекста
$systemcontext = context_system::instance();
// Требуется авторизация в системе
require_login();

if (!has_capability('theme/opentechnology:profile_links_manage', $systemcontext) && $userid == $USER->id)
{
    // Требуется право доступа для назнчения профиля себе
    require_capability('theme/opentechnology:profile_link_self', $systemcontext);
} else
{
    // Требуется право доступа для управления привязками профилей
    require_capability('theme/opentechnology:profile_links_manage', $systemcontext);
}

// Установка общих параметров страницы
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_url($pageurl);
$PAGE->set_title(get_string('profile_link_to_user_title', 'theme_opentechnology'));

$html = '';

$user = $DB->get_record('user', ['id' => $userid, 'deleted' => 0], 'id', MUST_EXIST);
// Генерация формы создания привязки
$customdata = new stdClass();
$customdata->user = $user;
$customdata->returnto = $returnto;
$form = new \theme_opentechnology\links\form_link_to_user($pageurl, $customdata);
$form->process();

$html .= $form->render();

// Установка шапки страницы
echo $OUTPUT->header();

echo $html;

// Установка подвала страницы
echo $OUTPUT->footer();