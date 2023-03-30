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
 * Панель управления доступом в СДО
 * 
 * Главная страница плагина для открытия/закрытия доступа в СДО
 * 
 * @package    local_authcontrol
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_authcontrol;

global $CFG, $PAGE, $SITE;

require_once ('../../config.php');
require_once ($CFG->dirroot . '/local/authcontrol/lib.php');
require_once ($CFG->dirroot . '/local/authcontrol/formlib.php');

use dml_exception;
use context_course;
use local_authcontrol\local_authcontrol_access_panel_courses as panel_courses;
use local_authcontrol\local_authcontrol_access_panel_main as panel_main;
use moodle_url;
use stdClass;

// Получим параметры
$courseid = optional_param('id', 0, PARAM_INT);

// Дефолтные параметры
$urlparams = [];
$url = new moodle_url('/local/authcontrol/controlpage.php');
$context = null;
// Для корректной работы require_login()
$PAGE->set_url($url);
// Подключим JS
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/authcontrol/authcontrol.js'));

require_login();

// Инициализация страницы
if ( ! empty($courseid) )
{// Передан ID курса
   
    try 
    {
        $course = get_course($courseid);
    } catch (dml_exception $e) 
    {// Ошибка получения курса
        redirect($url, get_string('course_not_exists', 'local_authcontrol'));
    }
    
    // Контекст
    $context = context_course::instance($course->id, MUST_EXIST);
    
    // Параметры
    $urlparams = ['id' => $course->id];
    $url->params($urlparams);
    
    // Устанавливаем свойства страницы
    $PAGE->set_course($course);
    $PAGE->set_pagelayout('course');
    $PAGE->set_context($context);
    $PAGE->set_title(get_string('course') . ': ' . $course->fullname);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_url($url);
}
else 
{// Контекст системы (Не передан ID курса)
    // Контекст системы
    $PAGE->set_course($SITE);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title($SITE->fullname);
    $PAGE->set_heading($SITE->fullname);
    $PAGE->set_url($url);
    $PAGE->navbar->add(get_string('pluginname', 'local_authcontrol'), $url);
}

// Вывод html
$html = '';

// Форма выбора курса
$customdata_courses = new stdClass();
$customdata_courses->courseid = $courseid;

$form_courses = new panel_courses($url, $customdata_courses);
$form_courses->process();
$html .= $form_courses->render();

if ( ! empty($context) )
{// Указан контекст курса
    if ( has_capability('local/authcontrol:use', $context) || has_capability('local/authcontrol:view', $context) )
    {
        // Форма выбора пользователей и модулей курса
        $customdata_main = new stdClass();
        $customdata_main->courseid = $courseid;
        $customdata_main->returnurl = $url;
        
        $form_main = new panel_main($url, $customdata_main);
        $form_main->process();
        $html .= $form_main->render();
    } else 
    {// Нет прав к панели управления доступа в СДО выбранного курса
        $html .= get_string('course_not_access_panel', 'local_authcontrol');
    }
}

// Шапка страницы
echo $OUTPUT->header();

echo $html;

// Футер
echo $OUTPUT->footer();