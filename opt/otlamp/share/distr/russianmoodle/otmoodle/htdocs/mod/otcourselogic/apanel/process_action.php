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
 * Панель управления логикой курса. Обработка карты
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require ("../../../config.php");

// Требуется авторизация на странице
require_login();

// Идентификатор способа записи
$instanceid = required_param('instance', PARAM_INT);

// Обработчик
$processorid = required_param('processor', PARAM_INT);

// Действие
$action = required_param('action', PARAM_RAW_TRIMMED);

// Тип
$type = optional_param('select_type', '', PARAM_RAW_TRIMMED);

// Идентификатор экшна
$actionid = optional_param('actionid', 0, PARAM_INT);

// Получение модуля курса
$cm_data = get_course_and_cm_from_instance($instanceid, 'otcourselogic');

// Формирование ссылки
$url = new moodle_url(
        '/mod/otcourselogic/apanel/process_action.php', 
        ['instance' => $instanceid, 'action' => $action, 'processor' => $processorid, 'type' => $type, 'actionid' => $actionid]
        );

// Устанавливаем свойства страницы
$PAGE->set_cm($cm_data[1]);
$PAGE->set_pagelayout('course');
$PAGE->set_url($url);
$PAGE->set_title(get_string('admin_panel', 'mod_otcourselogic'));
$PAGE->set_heading(get_string('admin_panel', 'mod_otcourselogic'));
$PAGE->navbar->add(get_string('admin_panel', 'mod_otcourselogic'));

// Проверка прав
require_capability('mod/otcourselogic:admin_panel', $PAGE->context);

$renderer = new \mod_otcourselogic\apanel\renderer();
$renderer->set_data($cm_data);

$options = new stdClass();
$options->action = $action;
$options->current_url = $url;
$options->success_url = new moodle_url('/mod/otcourselogic/apanel/index.php', ['instance' => $instanceid]);
$options->processorid = $processorid;
$options->type = $type;
$options->action = $action;
$options->actionid = $actionid;

$form = $renderer->get_form_process_action($options);
$form->process();

$html = '';

// Панель управления текущим инстансом логики курса
// $apanel_url = new moodle_url('/mod/otcourselogic/apanel/index.php', ['instance' => $instanceid]);
// $html .= html_writer::link($apanel_url, get_string('admin_panel', 'mod_otcourselogic'), ['class' => 'mod_otcourselogic btn']);

$html .= $form->render();

echo $OUTPUT->header();

echo $html;

echo $OUTPUT->footer();
