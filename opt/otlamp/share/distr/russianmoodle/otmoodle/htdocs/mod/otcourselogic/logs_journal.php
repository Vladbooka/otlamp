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
 * Модуль Логика курса. Страница просмотра логов групп действий для пользователя для выбранной логики курса
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/user/lib.php');

// ID экземпляра модуля курса
$cmid = optional_param('id', 0, PARAM_INT);
// ID экземпляра логики курса для получения настроек
$instanceid = optional_param('instanceid', 0, PARAM_INT);
// Идентификатор пользователя
$userid = optional_param('userid', 0, PARAM_INT);

if ( empty($userid) || ( empty($instanceid) && empty($cmid) ) )
{
    throw new moodle_exception('invalid_data', 'mod_otcourselogic');
}

// Идентификатора модуля курса определен
if ( $cmid )
{
    $PAGE->set_url('/mod/otcourselogic/logs_journal.php', ['id' => $cmid, 'userid' => $userid]);
    if ( ! $cm = get_coursemodule_from_id('otcourselogic', $cmid) )
    {
        print_error('invalidcoursemodule');
    }
    
    if ( ! $course = $DB->get_record('course', ['id' => $cm->course]))
    {
        print_error('coursemisconf');
    }
    
    if ( ! $instance = $DB->get_record('otcourselogic', ['id' => $cm->instance]))
    {
        print_error('invalidcoursemodule');
    }
} else
{
    $PAGE->set_url('/mod/otcourselogic/logs_journal.php', ['instanceid' => $instanceid]);
    if ( ! $instance = $DB->get_record('otcourselogic', ['id' => $instanceid]) )
    {
        print_error('invalidcoursemodule');
    }
    if ( ! $course = $DB->get_record('course', ['id' => $instanceid->course]) )
    {
        print_error('coursemisconf');
    }
    if ( ! $cm = get_coursemodule_from_instance('otcourselogic', $instanceid->id, $course->id))
    {
        print_error('invalidcoursemodule');
    }
}

// Требуется вход в систему
require_course_login($course, true, $cm);

// Получение контекста страницы
$context = context_module::instance($cm->id);

// Проверка прав доступа
require_capability('mod/otcourselogic:view', $context);
require_capability('mod/otcourselogic:view_student_states', $context);

// Установка параметров страницы
$PAGE->set_title($course->shortname.': '.$instance->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($instance);

$html = '';

// Шапка страницы
$html .= $OUTPUT->header();

// Кнопка возврата на страницу просмотра состояний
$returnurl = new moodle_url('/mod/otcourselogic/view.php', ['id' => $cmid, 'userid' => $userid]);
$html .=  html_writer::link(
        $returnurl,
        get_string('return', 'mod_otcourselogic'),
        ['class' => 'btn button btn-secondary']
        );

// Сбор логов
$table = new html_table();
$table->head = [
    get_string('log_type', 'mod_otcourselogic'),
    get_string('log_objectid', 'mod_otcourselogic'),
    get_string('log_timecreated', 'mod_otcourselogic'),
    get_string('log_comment', 'mod_otcourselogic'),
    get_string('log_executionstatus', 'mod_otcourselogic'),
];

// Получение логов
$table->data = \mod_otcourselogic\apanel\helper::get_user_processors_logs($cm->instance, $userid);

// Отображение данных
$html .=  html_writer::table($table);

// Футер
$html .=  $OUTPUT->footer();

echo $html;


