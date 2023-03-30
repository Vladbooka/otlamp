<?php
/*
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// подключаем библиотеки верхнего уровня
require_once('../../config.php');
//подключаем библиотеки для работы
require_once($CFG->dirroot.'/blocks/notgraded/lib.php');
//подключаем библиотеки для работы
require_once($CFG->dirroot.'/blocks/moodleblock.class.php');
require_once($CFG->dirroot.'/blocks/notgraded/block_notgraded.php');

$userid = optional_param('userid', $USER->id, PARAM_INT);
$viewall = optional_param('viewall', 0, PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);
$pageurl = new moodle_url('/blocks/notgraded/notgraded_courses.php');
$PAGE->set_url($pageurl);

$PAGE->set_pagelayout('mydashboard');
$PAGE->set_pagetype('my-index');

$PAGE->set_title(get_string('notgraded_courses_title', 'block_notgraded'));
// выводим заголовок
$PAGE->set_heading(get_string('notgraded', 'block_notgraded'), 
                    get_string('notgraded_courses_title', 'block_notgraded'));

// страница требует авторизации в курсе
require_login($COURSE->id, false);

$html = '';

if( $USER->id != $userid )
{
    require_capability('block/notgraded:view_others', $context);
} 

//получаем объект для работы
$coursesobj = new block_notgraded_items(null, $userid, $viewall);

$html .= $OUTPUT->heading(get_string('notgraded_courses_title', 'block_notgraded'));

if( has_capability('block/notgraded:viewall', context_system::instance()) )
{// Если есть право на просмотр всех непроверенных заданий по системе
    if( empty($viewall) )
    {// Добавим ссылку на просмотр всех заданий
        $url = new moodle_url($pageurl, ['userid' => $userid, 'viewall' => 1]);
        $html .= html_writer::div(html_writer::link($url, get_string('viewallnotgradedassigns', 'block_notgraded')), 'notgraded_viewall');
    
    } else
    {// или ссылку на просмотр только своих заданий
        $url = new moodle_url($pageurl, ['userid' => $userid]);
        $html .= html_writer::div(html_writer::link($url, get_string('viewmynotgradedassigns', 'block_notgraded')), 'notgraded_viewall');
    }
}

$html .= html_writer::table($coursesobj->get_data_for_table());

echo $OUTPUT->header();

echo $html;

echo $OUTPUT->footer();
?>