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
 * Витрина курсов. Страница для отображения результатов поиска курсов
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('../../config.php');
require_once($CFG->dirroot . '/local/crw/lib.php');

$crws = optional_param('crws', null, PARAM_RAW);
$srr = optional_param('srr', null, PARAM_TEXT);

$PAGE->requires->js_call_amd('local_crw/crw_gallery', 'init');
$PAGE->set_url('/local/crw/search.php', ['crws' => $crws, 'srr' => $srr]);
$PAGE->set_pagelayout('standard');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->navbar->add(get_string('search_result', 'local_crw'));

$adminediting = optional_param('adminedit', -1, PARAM_BOOL);
if ($PAGE->user_allowed_editing() && $adminediting != -1) {
    $USER->editing = $adminediting;
}

if ($PAGE->user_allowed_editing()) {
    if ($PAGE->user_is_editing()) {
        $caption = get_string('blockseditoff');
        $url = new moodle_url($PAGE->url, array('adminedit'=>'0', 'sesskey'=>sesskey()));
    } else {
        $caption = get_string('blocksediton');
        $url = new moodle_url($PAGE->url, array('adminedit'=>'1', 'sesskey'=>sesskey()));
    }
    $PAGE->set_button($OUTPUT->single_button($url, $caption, 'get'));
}

$PAGE->set_title(get_string('search_result', 'local_crw'));
$PAGE->set_heading('');

// Получим рендер для отображения результатов поиска
$searchrenderer = get_config('crw_system_search', 'search_result_renderer');
if( empty($searchrenderer) )
{
    $searchrenderer = 'courses_list_tiles';
}
// Сформируем опции для поиска
$searchoptions = [
    'crws' => $crws,
    'srr' => $srr,
    'forced_showcase_slots' => [
        ! empty($srr) ? $srr : 'system_search',
        $searchrenderer
    ],
    'display_invested_courses' => true
];

// Получаем плагин витрины
$showcase = new local_crw($searchoptions);
// Получаем курсы для отображения
$html = $showcase->display_showcase(['return_html' => true]);
// Шапка
echo $OUTPUT->header();
// Отобразить результаты поиска
echo $html;
// Футер
echo $OUTPUT->footer();
