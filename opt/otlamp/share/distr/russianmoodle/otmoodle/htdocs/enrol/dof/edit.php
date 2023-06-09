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
 * Adds new instance of enrol_manual to specified course
 * or edits current instance.
 *
 * @package    enrol
 * @subpackage manual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('edit_form.php');

$courseid = required_param('courseid', PARAM_INT);

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
if ( class_exists('context_course') )
{// начиная с moodle 2.6
    $context = context_course::instance($course->id, MUST_EXIST);
}else
{// оставим совместимость с moodle 2.5 и менее
    $context = get_context_instance(CONTEXT_COURSE, $course->id, MUST_EXIST);
}

require_login($course);
require_capability('enrol/dof:config', $context);

$PAGE->set_url('/enrol/dof/edit.php', array('courseid'=>$course->id));
$PAGE->set_pagelayout('admin');

$return = new moodle_url('/enrol/instances.php', array('id'=>$course->id));
if (!enrol_is_enabled('dof')) {
    redirect($return);
}

$plugin = enrol_get_plugin('dof');

if ($instances = $DB->get_records('enrol', array('courseid'=>$course->id, 'enrol'=>'dof'), 'id ASC')) {
    $instance = array_shift($instances);
    if ($instances) {
        // oh - we allow only one instance per course!!
        foreach ($instances as $del) {
            $plugin->delete_instance($del);
        }
    }
} else {
    require_capability('moodle/course:enrolconfig', $context);
    // no instance yet, we have to add new instance
    navigation_node::override_active_url(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));
    $instance = new stdClass();
    $instance->id       = null;
    $instance->courseid = $course->id;
}

$mform = new enrol_dof_edit_form(NULL, array($instance, $plugin, $context));

if ($mform->is_cancelled()) {
    redirect($return);

} else if ($data = $mform->get_data()) {
    if ($instance->id) {
        $reset = ($instance->status != $data->status);

        $instance->status       = $data->status;
        $instance->enrolperiod  = $data->enrolperiod;
        $instance->roleid       = $data->roleid;
        $instance->timemodified = time();
        $DB->update_record('enrol', $instance);

        if ($reset) {
            $context->mark_dirty();
        }

    } else {
        $fields = array('status'=>$data->status, 'enrolperiod'=>$data->enrolperiod, 'roleid'=>$data->roleid);
        $plugin->add_instance($course, $fields);
    }

    redirect($return);
}

$PAGE->set_title(get_string('pluginname', 'enrol_dof'));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'enrol_dof'));
$mform->display();
echo $OUTPUT->footer();
