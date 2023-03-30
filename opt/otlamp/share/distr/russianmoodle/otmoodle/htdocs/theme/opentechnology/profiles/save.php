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
 * Тема СЭО 3KL. Сохранение профиля.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');

// Получение идентификатора профиля
$id = optional_param('id', null, PARAM_INT);
$profilecode = optional_param('profilecode', null, PARAM_ALPHANUM);
$params = null;

// Текущий URL страницы
if ( $id )
{
    $params['id'] = $id;
    
} 
if (!is_null($profilecode))
{
    $params['profilecode'] = $profilecode;
}
$pageurl = new moodle_url('/theme/opentechnology/profiles/save.php', $params);

// Получение текущего контекста
$systemcontext = context_system::instance();
// Требуется авторизация в системе
require_login();
// Требуется право доступа
if ( $id )
{
    require_capability('theme/opentechnology:profile_edit', $systemcontext);
} else 
{
    require_capability('theme/opentechnology:profile_create', $systemcontext);
}

// Установка общих параметров страницы
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_url($pageurl);
if ( $id )
{
    $PAGE->set_title(get_string('profile_edit_title', 'theme_opentechnology'));
} else
{
    $PAGE->set_title(get_string('profile_create_title', 'theme_opentechnology'));
}

// Подключение CSS
$cssurl = new moodle_url('styles.css');
$PAGE->requires->css($cssurl);

$html = '';

// Добавление кнопки возврата на страницу просмотра профиля
$returnurl = new moodle_url(
    '/theme/opentechnology/profiles/index.php', 
    $params
);

// Проверка возможности редактирования профиля
if ( $id && ! \theme_opentechnology\profilemanager::instance()->profile_allow_edit((int)$id) )
{// Профиль не поддерживает удаление
    redirect($returnurl);
}

// Генерация формы подтверждения удаления
$customdata = new stdClass();
$customdata->profile = \theme_opentechnology\profilemanager::instance()->get_profile((int)$id);
$customdata->profilecode = $profilecode;
$form = new \theme_opentechnology\profilesaveform($pageurl->out(false), $customdata);
$form->process();

$html .= $form->render();

// Установка шапки страницы
echo $OUTPUT->header();

echo $html;

// Установка подвала страницы
echo $OUTPUT->footer();