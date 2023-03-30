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
 * Тема СЭО 3KL. Удаление привязки.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');

// Получение идентификатора привязки
$id = optional_param('id', null, PARAM_INT);

// Текущий URL страницы
$pageurl = new moodle_url(
    '/theme/opentechnology/profiles/link_delete.php', 
    ['id' => $id]
);
// Получение текущего контекста
$systemcontext = context_system::instance();
// Требуется авторизация в системе
require_login();
// Требуется право доступа
require_capability('theme/opentechnology:profile_links_manage', $systemcontext);

// Установка общих параметров страницы
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_url($pageurl);
$PAGE->set_title(
    get_string('profile_link_delete_title', 'theme_opentechnology')
);

// Подключение CSS
$cssurl = new moodle_url('styles.css');
$PAGE->requires->css($cssurl);

$html = '';

$link = \theme_opentechnology\links\manager::instance()->get_link((int)$id);
if ( ! $link )
{// Редирект на главную
    // Добавление кнопки возврата на страницу просмотра профиля
    $returnurl = new moodle_url(
        '/theme/opentechnology/profiles/index.php'
    );
    redirect($returnurl);
}

// Добавление кнопки возврата на страницу просмотра привязок
$returnurl = new moodle_url(
    '/theme/opentechnology/profiles/links.php', 
    ['id' => $link->get_record()->profileid]
);

// Генерация формы удаления привязки
$customdata = new stdClass();
$customdata->link = $link;
$form = new \theme_opentechnology\links\formdelete($pageurl, $customdata);
$form->process();

$html .= $form->render();

// Установка шапки страницы
echo $OUTPUT->header();

echo $html;

// Установка подвала страницы
echo $OUTPUT->footer();