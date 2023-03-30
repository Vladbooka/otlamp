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
 * Модуль Логика курса. Страница просмотра элемента курса.
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');

// CMID
$cmid = optional_param('id', 0, PARAM_INT);
// INSTANCEID
$instanceid = optional_param('instanceid', 0, PARAM_INT);

if ( $cmid ) 
{
    $PAGE->set_url('/mod/otcourselogic/message.php', ['id' => $cmid]);
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
} else {
    $PAGE->set_url('/mod/otcourselogic/view.php', ['instanceid' => $instanceid]);
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

require_course_login($course, true);
$context = context_course::instance($course->id);

$PAGE->set_title($course->shortname.': '.$instance->name);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

if( !empty($instance->redirectmessage) )
{
    echo html_writer::div($instance->redirectmessage);
    if( !empty($instance->redirecturl) )
    {
        echo html_writer::div(html_writer::link($instance->redirecturl, $instance->redirecturl));
    } else 
    {
        $course = get_course($instance->course);
        echo html_writer::div(html_writer::link('/course/view.php?id=' . $instance->course, $course->fullname));
    }
} 

echo $OUTPUT->footer();