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
 * Тема СЭО 3KL. Импорт профиля.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');

// Получение идентификатора профиля
$id = optional_param('id', null, PARAM_INT);

// Текущий URL страницы
$pageurl = new moodle_url(
    '/theme/opentechnology/profiles/import.php', 
    ['id' => $id]
);
// Получение текущего контекста
$systemcontext = context_system::instance();
// Требуется авторизация в системе
require_login();
// Требуется право доступа
require_capability('theme/opentechnology:settings_import', $systemcontext);

// Установка общих параметров страницы
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_url($pageurl);
$PAGE->set_title(
    get_string('profile_import_title', 'theme_opentechnology')
);

// Подключение CSS
$cssurl = new moodle_url('styles.css');
$PAGE->requires->css($cssurl);

$html = '';

// Форма загрузки файла импорта
if( theme_opentechnology\profilemanager::instance()->profile_allow_edit((int)$id) )
{
    // Генерация формы подтверждения удаления
    $customdata = new stdClass();
    $customdata->profile = \theme_opentechnology\profilemanager::instance()->get_profile((int)$id);
    $form = new \theme_opentechnology\profileimportform($pageurl, $customdata);
    $form->process();
    
    $html .= $form->render();
}

// Установка шапки страницы
echo $OUTPUT->header();

echo $html;

// Установка подвала страницы
echo $OUTPUT->footer();