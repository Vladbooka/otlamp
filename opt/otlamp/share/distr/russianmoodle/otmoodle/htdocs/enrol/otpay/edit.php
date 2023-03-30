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
 * Плагин записи на курс OTPAY. Страница сохранения экземпляра способа записи на курс.
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require ('../../config.php');
require_once ($CFG->dirroot . '/enrol/otpay/form.php');

// Получение GET-параметров
$courseid = required_param('courseid', PARAM_INT);
$instanceid = optional_param('id', 0, PARAM_INT);

$html = '';

// Получение курса
$course = get_course($courseid);
if ( $course )
{// Курс получен
    // Инициализация контекста
    $context = context_course::instance($course->id, MUST_EXIST);
    
    // Проверка доступа
    require_login($course);
    require_capability('enrol/otpay:config', $context);
    
    // URL возврата
    $backurl = new moodle_url('/enrol/instances.php',
        [
            'id' => $course->id
        ]
    );
    
    // Проверка активности плагина
    if ( ! enrol_is_enabled('otpay') )
    {
        redirect($backurl);
    }
    
    // Установка параметров страницы
    $pageurl = new moodle_url('/enrol/otpay/edit.php', 
        [
            'courseid' => $course->id,
            'id' => $instanceid
        ]
    );
    $PAGE->set_url($pageurl);
    $PAGE->set_pagelayout('admin');
    $PAGE->set_heading($course->fullname);
    $PAGE->set_title(get_string('pluginname', 'enrol_otpay'));
    
    // Инициализация плагина
    $plugin = enrol_get_plugin('otpay');
    // Инициализация провайдеров
    $providers = $plugin->get_providers();
    
    // Подготовка данных для формы сохранения
    if ( $instanceid )
    {
        // Получение экземпляра подписки
        $instance = $DB->get_record('enrol',
            [
                'courseid' => $course->id,
                'enrol' => 'otpay',
                'id' => $instanceid
            ], '*', MUST_EXIST);
        // Форматирование стоимости
        $instance->cost = format_float((float)$instance->cost, 2, true);
        $plugin->otpay_config($instance);
    } else
    {
        require_capability('moodle/course:enrolconfig', $context);
        
        navigation_node::override_active_url(
            new moodle_url('/enrol/instances.php', [
                'id' => $course->id
            ])
        );
        $instance = new stdClass();
        $instance->id = null;
        $instance->courseid = $course->id;
    }
    
    // Объявление формы
    $data = new stdClass();
    $data->course = $course;
    $data->context = $context;
    $data->instance = $instance;
    $form = new enrol_otpay_edit_enrol_form($pageurl, $data);
    
    // Обработчик формы
    $form->process();
    
    // Панель управления купонами
    $btncoupons = html_writer::link(
            new moodle_url('/enrol/otpay/coupons.php'), 
            get_string('coupon_system', 'enrol_otpay'), 
            ['class' => 'btn btn-primary']
            );
    
    // Панель администрирования
    $btnapanel = html_writer::link(
            new moodle_url('/enrol/otpay/apanel.php', ['instance' => $instance->id]), 
            get_string('admin_panel', 'enrol_otpay'), 
            ['class' => 'btn btn-primary']
            );
    $html .= html_writer::div($btncoupons . $btnapanel, 'otpay-btn-holder');
    
    $html .= $form->render();
}

// Отображение страницы
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'enrol_otpay'));
echo $html;
echo $OUTPUT->footer();