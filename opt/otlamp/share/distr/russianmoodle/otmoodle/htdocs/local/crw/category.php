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
 * Lists the course categories
 *
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package course
 */

require_once("../../config.php");
require_once($CFG->dirroot. '/course/lib.php');

$categoryid = optional_param('cid', 0, PARAM_INT); // Category id

if (!$categoryid) {
    throw new moodle_exception('unknowncategory');
} else {
    $PAGE->set_category_by_id($categoryid);
    $PAGE->set_url(new moodle_url('/local/crw/category.php', ['cid' => $categoryid]));
    $PAGE->set_pagetype('course-index-category');

    if (!$PAGE->category->visible && !has_capability('moodle/category:viewhiddencategories', $PAGE->context))
    {
        throw new moodle_exception('unknowncategory');
    }
}



// Формируем навигационные ссылки
$customfrontpageinclude = $CFG->customfrontpageinclude ?? '';
$frontpagecrw = stristr($customfrontpageinclude, 'local/crw/homepageinclude.php') !== FALSE;
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


// Получаем объект
$coursecat = core_course_category::get($categoryid);
// ПОлучаем всех родителей категории
$parents = $coursecat->get_parents();
if ( ! empty($parents) )
{// Если есть родители
    foreach ( $parents as $pcat )
    {// Добавим к хлебным крошкам
        $pcategory = core_course_category::get($pcat);
        $purl = new moodle_url('/local/crw/category.php', array('cid' => $pcat));
        $PAGE->navbar->add($pcategory->name, $purl);
    }
}
// Добавим саму категорию
$PAGE->navbar->add($coursecat->name);
$PAGE->set_heading($coursecat->name);
$PAGE->set_pagelayout('coursecategory');

if ($CFG->forcelogin) {
    require_login();
}

// Подключаем витрину
require_once($CFG->dirroot .'/local/crw/lib.php');
// Получаем плагин витрины
$showcase = new local_crw();
$displayopts = ['return_html' => true];
// Отобразить витрину курсов
$content = $showcase->display_showcase($displayopts);

echo $OUTPUT->header();
echo $OUTPUT->skip_link_target();
echo $content;

// Trigger event, course category viewed.
$eventparams = array('context' => $PAGE->context, 'objectid' => $categoryid);
$event = \core\event\course_category_viewed::create($eventparams);
$event->trigger();

echo $OUTPUT->footer();
