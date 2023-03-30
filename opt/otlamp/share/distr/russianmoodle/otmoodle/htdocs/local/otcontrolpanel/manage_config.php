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
 * Универсальная панель управления. Конфигурация
 *
 * @package    local_otcontrolpanel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!file_exists('../../config.php'))
{// Не нашли конфиги - переходим к установке
    header('Location: install.php');
    die;
}
// Подключаем библиотеки
require_once('../../config.php');
require_once("$CFG->libdir/adminlib.php");

$baseurl = new moodle_url('/local/otcontrolpanel/manage_config.php');

/** @var moodle_url $currenturl */
$currenturl = fullclone($baseurl);

// Базовые свойства страницы
$PAGE->set_url($currenturl);
admin_externalpage_setup('otcontrolpanel');

$PAGE->navbar->add(get_string('config', 'local_otcontrolpanel'), $currenturl);

// проверка прав доступа к странице редактирования конфига
$syscontext = \context_system::instance();
if (!\local_otcontrolpanel\config::has_access_config_otcontrolpanel($USER->id)) {
    throw new \required_capability_exception($syscontext, 'local/otcontrolpanel:configmy', 'nopermissions', '');
}

$views = html_writer::div(get_string('config_is_loading', 'local_otcontrolpanel'), 'views-section');
$addview = html_writer::div('', 'add-view-section mt-3');
$editview = html_writer::div('', 'edit-view-section');
$config = html_writer::div('', 'config-section');

$html = html_writer::div(
    $views . $addview . $editview . $config,
    'otcontrolpanel otcontrolpanel_config'
);
$PAGE->requires->js_call_amd('local_otcontrolpanel/config', 'init', []);

// Шапка
echo $OUTPUT->header();

echo $html;
// Подвал
echo $OUTPUT->footer();

