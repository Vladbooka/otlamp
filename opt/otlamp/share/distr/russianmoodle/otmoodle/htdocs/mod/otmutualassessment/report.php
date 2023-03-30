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

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/otmutualassessment/locallib.php');
require_once($CFG->libdir . '/classes/notification.php');

global $OUTPUT, $PAGE, $USER;

// Id модуля курса
$cmid = optional_param('cmid', 0, PARAM_INT);
$groupid = optional_param('group', null, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', '', PARAM_ALPHANUM);   //md5 confirmation hash
$edit = optional_param('edit', -1, PARAM_BOOL);

list($course, $cm) = get_course_and_cm_from_cmid($cmid);
$instance = mod_otmutualassessment_get_instance($cm->instance);
$strategylist = mod_otmutualassessment_get_strategy_list();

// Требуется авторизация
require_login($course, false, $cm);

// Получение контекста модуля
$context = context_module::instance($cm->id);

$html = '';

// Событие просмотра модуля
$event = \mod_otmutualassessment\event\report_viewed::create_from_otmutualassessment($instance, $context);
$event->trigger();

if (!empty($strategylist[$instance->strategy])) {
    // Объект для работы с модулем
    $otmutualassessment = new $strategylist[$instance->strategy]($context, $cm, $course);

    $activegroup = $otmutualassessment->get_active_group();
    
    // Установки страницы
    $params = ['cmid' => $cm->id];
    if (!is_null($groupid)) {
        $params['group'] = $groupid;
    } elseif (!empty($activegroup)) {
        $params['group'] = $activegroup;
        $groupid = $activegroup;
    } elseif (!empty($otmutualassessment->get_course_module()->effectivegroupmode)
        && $firstgroup = $otmutualassessment->get_first_course_group()) {
            $params['group'] = $firstgroup;
            $groupid = $firstgroup;
    }
    if (!empty($delete)) {
        $params['delete'] = $delete;
    }
    if (!empty($confirm)) {
        $params['confirm'] = $confirm;
    }
    $url = new moodle_url('/mod/otmutualassessment/report.php', $params);
    $PAGE->set_url($url);
    $PAGE->set_pagelayout('incourse');
    $PAGE->set_title("{$course->shortname}: {$instance->name}");
    $PAGE->set_heading("{$course->fullname}");
    
    if (!isset($USER->editing)) {
        $USER->editing = 0;
    }
    if ($PAGE->user_allowed_editing()) {
        if (($edit == 1) and confirm_sesskey()) {
            $USER->editing = 1;
            $url = new moodle_url($PAGE->url, ['notifyeditingon' => 1]);
            redirect($url);
        } else if (($edit == 0) and confirm_sesskey()) {
            $USER->editing = 0;
            redirect($PAGE->url);
        }
        $buttons = $OUTPUT->edit_button($PAGE->url);
        $PAGE->set_button($buttons);
    } else {
        $USER->editing = 0;
    }
    
    if ($delete) {
        $otmutualassessment->process_delete_vote($delete, $confirm, $groupid);
    }

    if ($otmutualassessment->can_view_grades($groupid)) {
        $html .= $otmutualassessment->get_warning_info();
        // Если есть право просматривать все оценки
        $html .= $otmutualassessment->get_group_menu($otmutualassessment->get_report_url(), true);
        $html .= $otmutualassessment->get_grades_table($groupid);
    } else {
        $html .= get_string('nopermissions', 'mod_otmutualassessment', get_capability_string('mod/otmutualassessment:viewgrades'));
    }
} else {
    $url = new moodle_url('/mod/otmutualassessment/report.php', ['cmid' => $cm->id]);
    $PAGE->set_url($url);
    $PAGE->set_pagelayout('incourse');
    $PAGE->set_title("{$course->shortname}: {$instance->name}");
    $PAGE->set_heading("{$course->fullname}");
    core\notification::add(get_string('error_invalid_strategy', 'mod_otmutualassessment'), core\notification::ERROR);
}

// Header
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($instance->name, true, ['context' => $context]) . '. ' . get_string('report', 'mod_otmutualassessment'), 2);
// Info
echo $html;

// Footer
echo $OUTPUT->footer();
