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
 * Плагин записи на курс OTPAY. Панель администрирования
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require ("../../config.php");
require_once($CFG->dirroot . '/enrol/otpay/lib.php');
require_once($CFG->dirroot . '/course/lib.php');

global $DB, $PAGE;

// Идентификатор способа записи
$instanceid = optional_param('instance', null, PARAM_INT);
// Идентификатор курса
$courseid = optional_param('course', null, PARAM_INT);
// Признак экспорта
$download = optional_param('download', '', PARAM_ALPHA);
// заготовка под смену количества итемов на страницу
$perpage = optional_param('perpage', 30, PARAM_INT);

// Требуется авторизация на странице
require_login();


// Формирование ссылки
$baseurl = new moodle_url('/enrol/otpay/apanel.php', ['perpage' => $perpage]);
/** @var moodle_url $currenturl */
$currenturl = fullclone($baseurl);
if (!is_null($instanceid))
{
    $currenturl->param('instance', $instanceid);

    // Запись на курс
    $enrol = $DB->get_record('enrol', [
        'id' => $instanceid,
        'enrol' => 'otpay'
    ]);

    // Валидация
    if (empty($enrol))
    {
        throw new moodle_exception('error_enrol_record', 'enrol_otpay');
    }

    $courseid = $enrol->courseid;

    // Получение контекста курса
    $context = context_course::instance($courseid);

} else if(!is_null($courseid))
{
    $currenturl->param('course', $courseid);

    // Получение контекста курса
    $context = context_course::instance($courseid);

} else {

    // Получение контекста системы
    $context = context_system::instance();
}

if ($coursecontext = $context->get_course_context(false))
{
    // Отсекаем лишние действия, если заранее известно, что нет прав в курсе
    require_capability('enrol/otpay:appsmanage', $coursecontext);
}

// Устанавливаем свойства страницы
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$PAGE->set_url($currenturl);
$PAGE->set_title(get_string('admin_panel', 'enrol_otpay'));
$PAGE->set_heading(get_string('admin_panel', 'enrol_otpay'));
$PAGE->requires->js('/enrol/otpay/adminpanel.js');

// метод для создания экземпляра класса таблицы
$constructor = ['\\enrol_otpay\\apanel_table', 'instance'];
// параметры для вызова метода
$constructargs = [];
// ссылка на просмотр всех заявок
$PAGE->navbar->add(get_string('admin_panel', 'enrol_otpay'), $baseurl);

if (!is_null($courseid))
{
    $course = get_course($courseid);
    $PAGE->navbar->add($course->shortname, course_get_url($course));

    $url = fullclone($baseurl);
    $url->param('course', $courseid);
    $PAGE->navbar->add(get_string('course_applications', 'enrol_otpay'), $url);
    $constructor = ['\\enrol_otpay\\apanel_table', 'instance_by_courseid'];
    $constructargs = [$courseid];
}

if (!is_null($instanceid))
{
    $enrolname = $enrol->name;
    if (empty($enrolname))
    {
        $paymethod = $enrol->customchar1;
        $providers = get_providers();
        if (array_key_exists($paymethod, $providers))
        {
            $provider = $providers[$paymethod];
            $paymethod = $provider->get_localized_name();
        }
        $enrolname = get_string('enrol_noname', 'enrol_otpay', (object)[
            'enrolid' => $enrol->id,
            'paymethod' => $paymethod
        ]);
    }
    $PAGE->navbar->add($enrolname);

    $url = fullclone($baseurl);
    $url->param('instance', $instanceid);
    $PAGE->navbar->add(get_string('enrol_applications', 'enrol_otpay'), $url);
    $constructor = ['\\enrol_otpay\\apanel_table', 'instance_by_enrolid'];
    $constructargs = [$instanceid];
}

$html = '';

// Получение таблицы
$table = call_user_func_array($constructor, $constructargs);
// установка текущего адреса страницы (например, для корректной работы пейджинга)
$table->define_baseurl($PAGE->url);
// устанавливаем признак экспорта
$table->is_downloading($download, 'otpay_items');

if (!$download)
{// планируется вывод таблицы - соберем вывод в переменную
    ob_start();
}

// Вывод таблицы (если был передан параметр экспорта, то вывод прервётся после исполнения следующей команды)
$table->out($perpage, false);

if (!$download)
{// планируется вывод таблицы - соберем вывод в переменную
    $html .= ob_get_contents();
    ob_end_clean();
}


echo $OUTPUT->header();

echo $html;

echo $OUTPUT->footer();
