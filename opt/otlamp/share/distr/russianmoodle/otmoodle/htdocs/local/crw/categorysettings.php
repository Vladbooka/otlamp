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
 * Редактирование дополнительных полей категории
 */

require_once('../../config.php');
require_once('categorysettings_form.php');

// ID категории
$id = required_param('id', PARAM_INT);

// Перенаправление со страницы настроек
$returnto = optional_param('returnto', null, PARAM_URL);

$PAGE->set_pagelayout('admin');
$pageparams = array('id' => $id);
$PAGE->set_url('/local/crw/categorysettings.php', $pageparams);

// Требуется вход
require_login($PAGE->course);

// Получим категорию
$category = $DB->get_record('course_categories', array('id' => $id), '*', MUST_EXIST);
if ( empty($category) )
{
    print_error('wrong category id');
}

// Контекст категории
$context = context_coursecat::instance($id);

// Проверка прав
require_capability('moodle/category:manage', $context);

// Создаем форму
$form = new categorysettings_form($PAGE->url, array('category' => $category, 'returnto' => $returnto ));

// Обработчик формы
$form->process();

// Отобразить форму
$PAGE->set_title(get_string('coursesettings', 'local_crw'));
$PAGE->set_heading(get_string('coursesettings', 'local_crw'));

echo $OUTPUT->header();

$form->display();

echo $OUTPUT->footer();
