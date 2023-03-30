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
 * Страница информации по истории обучения пользователя
 *
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once ('../../config.php');
require_once ('lib.php');
// Получим ID пользователя
$uid = optional_param('uid', 0, PARAM_INT);

// Получим пользователя, для которого будем формировать таблицу
if ( empty($uid) || $uid === $USER->id )
{
    $user = $USER;
} else
{
    $user = $DB->get_record('user', array( 'id' => $uid ));
}

// Базовые свойства страницы
$PAGE->set_url('/local/learninghistory/index.php', array('id' => $uid ));
$PAGE->set_course($SITE);
$PAGE->set_pagetype('site-index-local-learninghistory');
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('index_learninghistory', 'local_learninghistory'));
$PAGE->set_heading(get_string('index_learninghistory', 'local_learninghistory'));
$PAGE->navbar->add(get_string('index_learninghistory', 'local_learninghistory'));

// Требуется вход в систему
require_login();

echo $OUTPUT->header();

$context = context_system::instance();
if( $user->id == $USER->id )
{
    //пользователь просматривает свою информацию
    $hascapability = has_capability('local/learninghistory:viewmylearninghistory', $context);
}
else
{
    //пользователь просматривает информацию других пользователей
    $hascapability = has_capability('local/learninghistory:viewuserslearninghistory', $context);
}

if($hascapability)
{
    echo html_writer::tag('h3', $user->firstname .' '. $user->lastname );
    
    // Получим историю обучения
    $options = ['status' => ''];
    $historycourses = \local_learninghistory\local\utilities::get_learninghistory_snapshots(
            NULL, $user->id, $options
    );
    
    // Произведем обновление оценок для активных процессов пользователя 
    foreach ( $historycourses as $key => &$item )
    {
        if ( $item->status === 'active' )
        {
            // Обновим итоговую оценку
            $parameters = [];
            $parameters['finalgrade'] = \local_learninghistory\local\grades_manager::get_user_finalgrade($item->courseid, $item->userid);
            // Запишем данные
            \local_learninghistory\local\utilities::set_learninghistory_snapshot($item->courseid, $item->userid, $parameters);
            $item->finalgrade = $parameters['finalgrade'];
        }
    }
    
    // Формируем таблицу
    $table = new html_table();
    $table->width = '100%';
    $table->align = [
            "left",
            "center",
            "center",
            "center"
    ];
    $table->head = array (
            get_string('table_course', 'local_learninghistory'),
            get_string('table_startdate', 'local_learninghistory'),
            get_string('table_enddate', 'local_learninghistory'),
            get_string('table_finalgrade', 'local_learninghistory') 
    );
    
    
    foreach ( $historycourses as $key => &$item )
    {
        // Начинаем формировать строку
        $row = [ ];
        // Проверка курса на существование
        $exists = $DB->record_exists('course', ['id' => $item->courseid] );
        if ( $exists )
        {// Курс существует
            $coursecontext = context_course::instance($item->courseid);
        
            
            // Доступность курса
            $course = get_course($item->courseid);
        
            if ( ! empty($course) )
            { // Курс есть в системе
                if ( $course->visible )
                {
                    $row[] = html_writer::link('/course/view.php?id=' . $item->courseid, $course->fullname);
                } else 
                {
                    if ( has_capability('moodle/course:viewhiddencourses', $coursecontext) )
                    { // Есть права на просмотр курса
                        $row[] = html_writer::link('/course/view.php?id=' . $item->courseid, $course->fullname);
                    } else
                    { // Курс скрыт для пользователя
                        $row[] = html_writer::span($course->fullname);
                    }
                }
            } else
            { // Курс не найден
                $row[] = get_string('table_error', 'local_learninghistory');
            }
        } else 
        {
            $row[] = get_string('course_not_found', 'local_learninghistory').$item->courseid;
        }
    
        // Дата начала
        if ( ! empty($item->begindate) )
        {
            $date = usergetdate($item->begindate);
            $row[] =
                str_pad($date['mday'], 2, '0', STR_PAD_LEFT) . '.' .
                str_pad($date['mon'], 2, '0', STR_PAD_LEFT) . '.' .
                $date['year'] . ' ' .
                str_pad($date['hours'], 2, '0', STR_PAD_LEFT) . ':' .
                str_pad($date['minutes'], 2, '0', STR_PAD_LEFT) . ':' .
                str_pad($date['seconds'], 2, '0', STR_PAD_LEFT);
        } else
        {
            $row[] = '';
        }
        
        // Дата окончания
        if ( ! empty($item->enddate) )
        {
            $date = usergetdate($item->enddate);
            $row[] =
                str_pad($date['mday'], 2, '0', STR_PAD_LEFT) . '.' .
                str_pad($date['mon'], 2, '0', STR_PAD_LEFT) . '.' .
                $date['year'] . ' ' .
                str_pad($date['hours'], 2, '0', STR_PAD_LEFT) . ':' .
                str_pad($date['minutes'], 2, '0', STR_PAD_LEFT) . ':' .
                str_pad($date['seconds'], 2, '0', STR_PAD_LEFT);
        } else
        {
            $row[] = '';
        }
        
        // Итоговая оценка
        if ( ! empty($item->finalgrade) )
        { // Есть итоговая оценка
            $row[] = floatval($item->finalgrade);
        } else
        {
            $row[] = '-';
        }
        
        if ( $exists || ( !$exists && has_capability('local/learninghistory:viewlearninghistoryofdeleted', $context)) )
        {//курс существует или у пользователя есть право просматривать данные по удаленным курсам
            $table->data[] = $row;
        }
    }
    
    echo html_writer::table($table);
}
else
{
    echo html_writer::tag( 'p', get_string('table_accessdenied', 'local_learninghistory') );
}
echo $OUTPUT->footer();
