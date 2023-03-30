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
 * Витрина курсов.
 * Страница курса
 *
 * @package local
 * @subpackage crw
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once ('../../config.php');
require_once ($CFG->dirroot . '/local/crw/lib.php');
require_once ($CFG->libdir . '/enrollib.php');
require_once ($CFG->libdir . '/navigationlib.php');



// Входные параметры
$id = optional_param('id', null, PARAM_INT);
$shortname = optional_param('name', null, PARAM_RAW);
$idnumber = optional_param('idnumber', null, PARAM_RAW);



// Получаем курс из БД
$course = null;
if (is_null($course) && !is_null($id))
{
    $course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);
}
if (is_null($course) && !is_null($idnumber))
{
    $course = $DB->get_record('course', ['idnumber' => $idnumber], '*', MUST_EXIST);
}
if (is_null($course) && !is_null($shortname))
{
    $course = $DB->get_record('course', ['shortname' => $shortname], '*', MUST_EXIST);
}
if (is_null($course))
{
    print_error('unspecifycourseid', 'error');
}



// Контекст
$context = context_course::instance($course->id, MUST_EXIST);



// Проверки
$isenrolled = is_enrolled($context, $USER, '', true);
crw_redirect($course->id, $isenrolled);

if ( $course->id == SITEID )
{// Это не настоящий курс, перевод на главную
    redirect($CFG->wwwroot . '/');
}



// Устанавливаем свойства страницы
$PAGE->set_course($course);
$PAGE->set_context($context);
$PAGE->set_title(get_string('course') . ': ' . $course->fullname);
$PAGE->set_heading($course->fullname);
$PAGE->set_url('/local/crw/course.php', ['id' => $course->id]);

$PAGE->set_pagelayout('course');
if (array_key_exists('coursedesc', $PAGE->theme->layouts))
{
    $PAGE->set_pagelayout('coursedesc');
}



// Получаем рендер локального плагина
$courserenderer = $PAGE->get_renderer('local_crw');
$coursepage = new \local_crw\output\course($course, ['enrol_forms' => true]);



// Формируем навигационные ссылки
$frontpagecrw = isset($CFG->customfrontpageinclude) &&
    stristr($CFG->customfrontpageinclude, 'local/crw/homepageinclude.php') !== FALSE;
// Настройка переопределения навигации
$configoverride = get_config('local_crw', 'override_navigation');

if ((!$frontpagecrw || $CFG->defaulthomepage != HOMEPAGE_SITE) && !empty($configoverride))
{ // Витрина на главной не включена, добавим в навигацию ссылку на плагин
    $PAGE->navbar->add(
        get_string('courses_showcase', 'local_crw'),
        new moodle_url('/local/crw/index.php'),
        navigation_node::TYPE_ROOTNODE
    );
}

foreach($coursepage->get_course_category_parents() as $pcat)
{
    $url = new moodle_url('/local/crw/category.php', ['cid' => $pcat['id']]);
    $PAGE->navbar->add($pcat['name'], $url);
}
$cat = $coursepage->get_course_category();
$url = new moodle_url('/local/crw/category.php', ['cid' => $cat['id']]);
$PAGE->navbar->add($cat['name'], $url, navigation_node::TYPE_CATEGORY);

$textandtitle = get_string('course_info', 'local_crw');
$PAGE->navbar->add($textandtitle, new moodle_url('/local/crw/course.php', ['id' => $PAGE->course->id]),
    navigation_node::TYPE_COURSE,
    null,
    'local-crw-course-' . $PAGE->course->id);
$about_course_node = $PAGE->navbar->get('local-crw-course-' . $PAGE->course->id, navigation_node::TYPE_COURSE);
$about_course_node->add_class('about_course');
$about_course_node->title($textandtitle);

// Формирование вывода страницы
$html = $courserenderer->render_coursepage($coursepage);
echo $OUTPUT->header();
echo $html;

$eventdata = array('context' => $context);
$event = \local_crw\event\coursepage_viewed::create($eventdata);
$event->trigger();

echo $OUTPUT->footer();