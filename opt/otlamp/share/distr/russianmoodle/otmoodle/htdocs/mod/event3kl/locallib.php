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
 * Локальная библиотека модуля Занятие
 *
 * @package   mod_event3kl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_event3kl\event3kl;
use mod_event3kl\formats;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/event3kl/classes/renderable.php');

/**
 * View a summary listing of all assignments in the current course.
 * @param stdClass $course инстанс курса
 */
function mod_event3kl_view_course_index($course) {
    global $USER, $PAGE;

    $renderer = $PAGE->get_renderer('mod_event3kl', null, RENDERER_TARGET_GENERAL);

    $o = '';

    $strplural = get_string('modulenameplural', 'event3kl');

    if (!$cms = get_coursemodules_in_course('event3kl', $course->id)) {
        $o .= $renderer->notification(get_string('thereareno', 'moodle', $strplural));
        $o .= $renderer->continue_button(new moodle_url('/course/view.php', ['id' => $course->id]));
        return $o;
    }

    $strsectionname = '';
    $usesections = course_format_uses_sections($course->format);
    $modinfo = get_fast_modinfo($course);

    if ($usesections) {
        $strsectionname = get_string('sectionname', 'format_'.$course->format);
        $sections = $modinfo->get_section_info_all();
    }
    $courseindexsummary = new event3kl_course_index_summary($usesections, $strsectionname);

    foreach ($modinfo->instances['event3kl'] as $cm) {
        if (!$cm->uservisible) {
            continue;
        }
        $sectionname = '';
        if ($usesections && $cm->sectionnum) {
            $sectionname = get_section_name($course, $sections[$cm->sectionnum]);
        }
        $gradinginfo = grade_get_grades($course->id, 'mod', 'event3kl', $cm->instance, $USER->id);
        if (isset($gradinginfo->items[0]->grades[$USER->id]) && ! $gradinginfo->items[0]->grades[$USER->id]->hidden) {
            $grade = $gradinginfo->items[0]->grades[$USER->id]->str_grade;
        } else {
            $grade = '-';
        }
        $courseindexsummary->add_event3kl_info($cm->id, $cm->get_formatted_name(), $sectionname, $grade);
    }

    $o .= $renderer->render($courseindexsummary);
    if (!PHPUNIT_TEST) {
        $o .= $renderer->render_footer();
    }

    return $o;
}

/**
 * Актуализация сессий для всех занятий в курсе
 * @param int $courseid id курса
 */
function mod_event3kl_actualize_sessions_in_course($courseid){
    try {
        $extrafields = 'm.format as format';
        $modules = get_coursemodules_in_course('event3kl', $courseid, $extrafields);
    } catch (Exception $e) {
        return;
    }
    if (empty($modules)) {
        return;
    }
    foreach ($modules as $module) {

        try {

            $event3kl = event3kl::get_record(['id' => $module->instance]);
            $event3kl->actualize_sessions();

        } catch (Exception $e){}

    }
}