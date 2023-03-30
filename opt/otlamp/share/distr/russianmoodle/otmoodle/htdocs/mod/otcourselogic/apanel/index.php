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
 * Панель управления логикой курса
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require ("../../../config.php");

// Требуется авторизация на странице
require_login();

// Идентификатор способа записи
$instanceid = required_param('instance', PARAM_INT);

// Получение модуля курса
$cm_data = get_course_and_cm_from_instance($instanceid, 'otcourselogic');

// Формирование ссылки
$url = new moodle_url('/mod/otcourselogic/apanel/index.php', ['instance' => $instanceid]);
// Устанавливаем свойства страницы
$PAGE->set_cm($cm_data[1]);
$PAGE->set_pagelayout('course');
$PAGE->set_url($url);
$PAGE->set_title(get_string('admin_panel', 'mod_otcourselogic'));
$PAGE->set_heading(get_string('admin_panel', 'mod_otcourselogic'));
$PAGE->navbar->add(get_string('admin_panel', 'mod_otcourselogic'));
$PAGE->requires->js('/mod/otcourselogic/script.js');

// Проверка прав
require_capability('mod/otcourselogic:admin_panel', $PAGE->context);

$renderer = new \mod_otcourselogic\apanel\renderer();
$renderer->set_data($cm_data);

$html = '';
$html .= $renderer->get_cards();

echo $OUTPUT->header();

// Кнопка возврата в курс
$courseurl = new moodle_url('/course/view.php', ['id' => $cm_data[0]->id]);
echo html_writer::link(
        $courseurl,
        get_string('return_to_course', 'mod_otcourselogic'),
        ['class' => 'btn btn-secondary button otcourselogic_button_return_to_course']
        );

echo $html;

echo $OUTPUT->footer();
