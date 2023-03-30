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

global $OUTPUT, $PAGE;

// Id модуля курса
$cmid = optional_param('cmid', 0, PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($cmid);
$instance = mod_otmutualassessment_get_instance($cm->instance);
$strategylist = mod_otmutualassessment_get_strategy_list();

// Требуется авторизация
require_login($course, false, $cm);

// Получение контекста модуля
$context = context_module::instance($cm->id);

$html = '';

if (!empty($strategylist[$instance->strategy])) {
    // Объект для работы с модулем
    $otmutualassessment = new $strategylist[$instance->strategy]($context, $cm, $course);
    
    // Установки страницы
    $params = ['cmid' => $cm->id];
    $url = new moodle_url('/mod/otmutualassessment/refresh.php', $params);
    $PAGE->set_url($url);
    $PAGE->set_pagelayout('incourse');
    $PAGE->set_title("{$course->shortname}: {$instance->name}");
    $PAGE->set_heading("{$course->fullname}");
    
    if (has_capability('mod/otmutualassessment:refreshgrades', $context)) {
        // Если есть право пересчитывать оценки
        if ($otmutualassessment->get_efficiencyofrefresh() == 'live') {
            $refresh = $otmutualassessment->get_refresh_form();
            $refresh->process();
            $html .= $refresh->render();
        } else {
            $refreshtask = $otmutualassessment->get_refresh_task_form();
            $refreshtask->process();
            $html .= $refreshtask->render();
        }
    } else {
        $html .= get_string('nopermissions', 'mod_otmutualassessment', get_capability_string('mod/otmutualassessment:refreshgrades'));
    }
} else {
    $url = new moodle_url('/mod/otmutualassessment/refresh.php', ['cmid' => $cm->id]);
    $PAGE->set_url($url);
    $PAGE->set_pagelayout('incourse');
    $PAGE->set_title("{$course->shortname}: {$instance->name}");
    $PAGE->set_heading("{$course->fullname}");
    core\notification::add(get_string('error_invalid_strategy', 'mod_otmutualassessment'), core\notification::ERROR);
}

// Header
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($instance->name, true, ['context' => $context]) . '. ' . get_string('refresh', 'mod_otmutualassessment'), 2);
// Info
echo $html;

// Footer
echo $OUTPUT->footer();
