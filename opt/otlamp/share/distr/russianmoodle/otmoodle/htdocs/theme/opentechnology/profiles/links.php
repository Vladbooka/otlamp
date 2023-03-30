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
 * Тема СЭО 3KL. Привязки темы.
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
    '/theme/opentechnology/profiles/links.php', 
    ['id' => $id]
);
// Получение текущего контекста
$systemcontext = context_system::instance();
// Требуется авторизация в системе
require_login();
// Требуется право доступа
require_capability('theme/opentechnology:profile_links_view', $systemcontext);

// Установка общих параметров страницы
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_url($pageurl);
$PAGE->set_title(
    get_string('profile_links_title', 'theme_opentechnology')
);

// Подключение CSS
$cssurl = new moodle_url('styles.css');
$PAGE->requires->css($cssurl);

$html = '';

// Добавление кнопки возврата на страницу просмотра списка профилей
$returnurl = new moodle_url(
    '/theme/opentechnology/profiles/index.php'
);
$html .= html_writer::link(
    $returnurl,
    get_string('profile_control_panel_title', 'theme_opentechnology'),
    ['class' => 'btn btn-primary']
);

// Получение текущего профиля
$profile = \theme_opentechnology\profilemanager::instance()->get_profile($id);
if ( empty($profile) || ! $profile->get_id() )
{// Профиль не найден
    redirect($returnurl);
}

// Добавление кнопки создания нового профиля
if ( has_capability('theme/opentechnology:profile_links_manage', $systemcontext) )
{
    $url = new moodle_url(
        '/theme/opentechnology/profiles/link_create.php',
        ['profileid' => $profile->get_id()]
    );
    $html .= html_writer::link(
        $url,
        get_string('profile_create_link_title', 'theme_opentechnology'),
        ['class' => 'btn btn-primary']
    );
}

$table = new html_table();
$table->head = [
    'num' => get_string('profile_link_table_num_title', 'theme_opentechnology'),
    'name' => get_string('profile_link_table_name_title', 'theme_opentechnology'),
    'description' => get_string('profile_link_table_description_title', 'theme_opentechnology'),
    'info' => get_string('profile_link_table_info_title', 'theme_opentechnology'),
    'actions' => get_string('profile_link_table_actions_title', 'theme_opentechnology')
];
$table->align = [
    'num' => 'center',
    'name' => 'center',
    'description' => 'center',
    'info' => 'center',
    'actions' => 'center'
];
$num = 1;
// Добавление списка привязок текущего профиля
$links = \theme_opentechnology\links\manager::instance()->get_profile_links($profile);
foreach ( $links as $link )
{
    $data = [];
    $data['num']= $num++;
    $data['name'] = $link->get_name();
    $data['description'] = $link->get_description();
    $data['info'] = $link->get_info();
    
    $deleteactionurl = new moodle_url(
        '/theme/opentechnology/profiles/link_delete.php',
        ['id' => $link->get_id()]
    );
    $deleteaction = html_writer::link(
        $deleteactionurl,
        get_string('profile_link_table_actions_delete', 'theme_opentechnology'),
        ['class' => 'btn btn-danger']
    );
    $data['actions'] = $deleteaction;
    $table->data[] = $data;
}
$html .= html_writer::table($table);

// Установка шапки страницы
echo $OUTPUT->header();

echo $html;

// Установка подвала страницы
echo $OUTPUT->footer();