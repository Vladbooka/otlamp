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

global $USER, $OUTPUT, $PAGE;

$cmid = optional_param('id', 0, PARAM_INT);
$groupid = optional_param('group', null, PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($cmid);
$instance = mod_otmutualassessment_get_instance($cm->instance);
$strategylist = mod_otmutualassessment_get_strategy_list();

// Требуется авторизация
require_login($course, true, $cm);

// Получение контекста модуля
$context = context_module::instance($cm->id);

$html = '';

// Событие просмотра модуля
$event = \mod_otmutualassessment\event\course_module_viewed::create_from_otmutualassessment($instance, $context);
$event->trigger();

if (!empty($strategylist[$instance->strategy])) {
    // Объект для работы с модулем
    $otmutualassessment = new $strategylist[$instance->strategy]($context, $cm, $course);
    
    // Update module completion status.
    $otmutualassessment->set_module_viewed();
    
    $activegroup = $otmutualassessment->get_active_group();
    
    // Установки страницы
    $params = ['id' => $cm->id];
    if (!is_null($groupid)) {
        $params['group'] = $groupid;
    } elseif (!empty($activegroup)) {
        $params['group'] = $activegroup;
        $groupid = $activegroup;
    } elseif (!empty($otmutualassessment->get_course_module()->effectivegroupmode) 
        && $firstgroup = $otmutualassessment->get_first_user_group($USER->id)) {
            $params['group'] = $firstgroup;
            $groupid = $firstgroup;
    }
    $url = new moodle_url('/mod/otmutualassessment/view.php', $params);
    $PAGE->set_url($url);
    $PAGE->set_pagelayout('incourse');
    $PAGE->set_title("{$course->shortname}: {$instance->name}");
    $PAGE->set_heading("{$course->fullname}");
    
    if ($otmutualassessment->can_view_grades($groupid)) {
        // Если есть право просматривать все оценки
        if (has_capability('mod/otmutualassessment:gradeothers', $context)) {
            // Если также есть право на оценку других участников - покажем ссылку на отчет по выставленным баллам
            $html .= $otmutualassessment->get_report_link();
        } else {
            // Если права на оценку других участников нет - сразу редиректим на отчет
            redirect($otmutualassessment->get_report_url());
        }
    }
    
    if (is_enrolled($otmutualassessment->get_context(), $USER->id, 'mod/otmutualassessment:gradeothers')) {
        $otmutualassessment->set_grader($USER->id);
        $otmutualassessment->set_graded_users($groupid);
        $html .= $otmutualassessment->get_group_menu($otmutualassessment->get_view_url(), true);
        if (empty($otmutualassessment->get_gradedusers())) {
            // Некого оценивать
            $html .= get_string('no_graded_users', 'mod_otmutualassessment');
        } else {
            if ($form = $otmutualassessment->get_grades_form($groupid)) {
                $form->process();
            }
            $status = $otmutualassessment->get_status($USER->id, $groupid);
            if (!$status || $status === $otmutualassessment::NOTCOMPLETED) {
                $html .= $otmutualassessment->get_instruction_for_grader($groupid);
            } elseif ($status === $otmutualassessment::COMPLETED) {
                //$html .= $otmutualassessment->get_info_after_grading();
            }
            // Выведем форму для оценивания участников
            if ($form) {
                $html .= $form->render();
            } else {
                core\notification::add(get_string('error_invalid_grader', 'mod_otmutualassessment'), core\notification::ERROR);
            }
            
        }
    } else {
        $html .= get_string('no_enrol_or_capability', 'mod_otmutualassessment');
    }
    if (empty($html)) {
        $html .= get_string('noanycapability', 'mod_otmutualassessment');
    }
} else {
    $url = new moodle_url('/mod/otmutualassessment/view.php', ['id' => $cm->id]);
    $PAGE->set_url($url);
    $PAGE->set_pagelayout('incourse');
    $PAGE->set_title("{$course->shortname}: {$instance->name}");
    $PAGE->set_heading("{$course->fullname}");
    core\notification::add(get_string('error_invalid_strategy', 'mod_otmutualassessment'), core\notification::ERROR);
}

// Header
echo $OUTPUT->header($course);
echo $OUTPUT->heading(format_string($instance->name), 2);
// Info
echo $html;

// Footer
echo $OUTPUT->footer($course);
