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
 * Витрина курсов. Страница для отображения Витрины на главной странице
 *
 * Для работы включите в файл config.php строку
 * $CFG->customfrontpageinclude = dirname(__FILE__) . '/local/crw/homepageinclude.php';
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/local/crw/lib.php');

// Получим параметры
$catid = optional_param('cid', 0, PARAM_INT);
$current_page = optional_param('page', 0, PARAM_INT);

// Создадим главный рендер контента - это рендер локального плагина
$courserenderer = $PAGE->get_renderer('local_crw');

// Подключаем AJAX
include_course_ajax($SITE, $modnamesused);
if (isloggedin() and !isguestuser() and isset($CFG->frontpageloggedin))
{
    $frontpagelayout = $CFG->frontpageloggedin;
} else
{
    $frontpagelayout = $CFG->frontpage;
}
$PAGE->requires->js_call_amd('local_crw/crw_gallery', 'init');

// Подключаем витрину
require_once($CFG->dirroot .'/local/crw/lib.php');
// Получаем плагин витрины
$showcase = new local_crw();
// Отобразить витрину курсов
$showcase->display_showcase();

echo $OUTPUT->footer();
exit;