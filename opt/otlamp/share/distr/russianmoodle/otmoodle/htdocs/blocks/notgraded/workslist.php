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
// страница которая выводит список всех непроверенных заданий

// подключаем библиотеки верхнего уровня
require_once('../../config.php');
//подключаем библиотеки для работы
require_once($CFG->dirroot.'/blocks/moodleblock.class.php');
require_once($CFG->dirroot.'/blocks/notgraded/lib.php');
require_once($CFG->dirroot.'/blocks/notgraded/block_notgraded.php');

$PAGE->set_url('/blocks/notgraded/workslist.php');
$courseid = required_param('courseid', PARAM_INT);

// страница требует авторизации в курсе
require_login($courseid, false);

$block  = new block_notgraded();
$html = $block->get_notgraded_html();

// выводим заголовок
$PAGE->set_heading(get_string('notgraded', 'block_notgraded'), 
                    get_string('notgraded_list', 'block_notgraded'));
echo $OUTPUT->header();
echo $html;
echo $OUTPUT->footer();
