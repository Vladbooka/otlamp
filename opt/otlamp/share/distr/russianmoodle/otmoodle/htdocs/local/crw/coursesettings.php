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
 * Витрина курсов. Страница редактирования дополнительных полей курса
 * 
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('coursesettings_form.php');

// ID курса
$id = required_param('id', PARAM_INT);
// Перенаправление со страницы настроек
$returnto = optional_param('returnto', 0, PARAM_ALPHANUM); 

$PAGE->set_pagelayout('admin');
$pageparams = array('id' => $id);
$PAGE->set_url('/local/crw/coursesettings.php', $pageparams);

// Права доступа
if ( $id == SITEID )
{// Неправильный ID курса
    print_error('cannoteditsiteform');
}
// Получим курс
$course = get_course($id);
// Требуется вход
require_login($course);
// Получим курс вместе с полями формата курса
$course = course_get_format($course)->get_course();
// Получим категорию
$category = $DB->get_record('course_categories', array('id'=>$course->category), '*', MUST_EXIST);

// Контекст
$coursecontext = context_course::instance($course->id);
// Проверка прав
require_capability('moodle/course:update', $coursecontext);

// Создаем форму
$form = new coursesettings_form(NULL, array('course'=>$course, 'returnto'=>$returnto));
// Обработчик формы
$form->process();

// Отобразить форму
$site = get_site();
$PAGE->set_title(get_string('coursesettings', 'local_crw'));
$PAGE->set_heading(get_string('coursesettings', 'local_crw'));

echo $OUTPUT->header();

// Отобразим форму
$form->display();

echo $OUTPUT->footer();
