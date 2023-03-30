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
 * Тема СЭО 3KL. Панель управления профилями.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');

// Текущий URL страницы
$pageurl = new moodle_url('/theme/opentechnology/profiles/index.php');
// Получение текущего контекста
$systemcontext = context_system::instance();
// Требуется авторизация в системе
require_login();
// Требуется право доступа
require_capability('theme/opentechnology:profile_view', $systemcontext);

// Установка общих параметров страницы
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_url($pageurl);
$PAGE->set_title(
    get_string('profile_control_panel_title', 'theme_opentechnology')
);

// Подключение CSS
$cssurl = new moodle_url('styles.css');
$PAGE->requires->css($cssurl);

$html = '';

// Добавление кнопки создания нового профиля
if ( has_capability('theme/opentechnology:profile_create', $systemcontext) )
{
    $url = new moodle_url(
        '/theme/opentechnology/profiles/save.php'
    );
    $html .= html_writer::link(
        $url,
        get_string('profile_control_panel_create_title', 'theme_opentechnology'),
        ['class' => 'btn btn-primary']
    );
}

// Генерация плиток профилей
$tiles = \theme_opentechnology\profilemanager::instance()->get_profile_tiles();
$html .= html_writer::div($tiles, 'theme_opentechnology_profile_tiles');

// Установка шапки страницы
echo $OUTPUT->header();

echo $html;

// Установка подвала страницы
echo $OUTPUT->footer();