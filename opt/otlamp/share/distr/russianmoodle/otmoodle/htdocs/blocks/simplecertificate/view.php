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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Сертификаты. Страница просмотра сертификатов пользователей.
 *
 * @package    block
 * @subpackage simplecertificate
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */ 

require_once ('../../config.php');

use block_simplecertificate\local\utilities;
use html_table;
use html_writer;
use context_course;
use context_system;
use course_enrolment_manager;
use html_table_cell;
use html_table_row;
use moodle_page;
use moodle_url;

global $PAGE, $USER;

// Подключение библиотек
require_once("$CFG->libdir/pdflib.php");
require_once('locallib.php');

// Требуется вход в систему
require_login();

// Получим GET - параметры
$username = optional_param('username', '', PARAM_TEXT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$limit = optional_param('limit', 30, PARAM_INT);
$sort = optional_param('sort', '', PARAM_TEXT);
$dir = optional_param('dir', '', PARAM_TEXT);

// Нормализация ID курса
if ( get_site()->id == $courseid )
{// Курс главной страницы
    $courseid = 0;
}

// Установка контекста страницы
if ( $courseid )
{
    $context = context_course::instance((int)$courseid);
} else 
{// Системный контекст
    $context = context_system::instance();
}

// Проверка доступа к странице
require_capability('block/simplecertificate:viewsertificateslist', $context);

// Массив get параметров
$getparams = [];
if ( ! empty($username) )
{
    $getparams['username'] = $username;
}
if ( ! empty($courseid) )
{
    $getparams['courseid'] = $courseid;
}
if ( ! empty($page) )
{
    $getparams['page'] = $page;
}
if ( $limit != 30 )
{
    $getparams['limit'] = $limit;
}
if ( ! empty($sort) )
{
    $getparams['sort'] = $sort;
}
if ( ! empty($dir) )
{
    $getparams['dir'] = $dir;
}
// Установка опций страницы
$PAGE->set_context($context);
$PAGE->set_url('/blocks/simplecertificate/view.php', $getparams);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('pluginname', 'block_simplecertificate'));
$PAGE->set_title(get_string('pluginname', 'block_simplecertificate'));

// Хлебные крошки
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('pluginname', 'block_simplecertificate'), new moodle_url('/blocks/simplecertificate/view.php'));

// Получим форму фильтрации сертификатов
$customdata = [];
$customdata['username'] = $username;
$customdata['courseid'] = $courseid;
$form = new certificates_filter_form(NULL, $customdata);
// Обработчик формы
$form->process();

// Получим массив ID пользователей по части ФИО
$userids = utilities::get_userids_by_username($username);

$options = [];
if ( ! empty($courseid) )
{
    $options['courses'] = $courseid;
}
$options['users'] = $userids;
$options['active'] = true;

$certificates = utilities::get_certificates($options, $sort, $dir);

// Заголовок
echo $OUTPUT->header();

$form->display();

// Отобразим таблицу
echo utilities::get_certificates_table($certificates, abs($page), abs($limit), $sort, $dir);

// Отобразить пагинацию
echo $OUTPUT->paging_bar(count($certificates), abs($page), abs($limit), $PAGE->url );

// Футер
echo $OUTPUT->footer();
