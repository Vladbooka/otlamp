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
 * Тип вопроса Объекты на изображении. Источник изображения - веб-камера студента.
 * 
 * Файл изображения формируется на основе захваченной веб-камероой фотографии
 *
 * @package    qtype
 * @subpackage otimagepointer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../../../../config.php');
require_once($CFG->dirroot . '/question/type/otimagepointer/lib.php');
require_once($CFG->libdir . '/questionlib.php');

// ID набора вопросов
$qubaid = required_param('quba', PARAM_INT);
// Номер слота в наборе
$slot = required_param('slot', PARAM_INT);
// Токен доступа
$token = required_param('token', PARAM_RAW_TRIMMED);

// Требуется авторизация в системе
require_login();

// Получить набор вопросов
try {
    // Получение набора вопросов
    $quba = question_engine::load_questions_usage_by_activity($qubaid);
    // Получение попытки прохождения вопроса пользователем
    $qa = $quba->get_question_attempt($slot);
    // Получение вопроса
    $question = $qa->get_question();

    // Проверка на тип вопроса
    if ( $question->get_type_name() != 'otimagepointer' )
    {// Тип вопроса не валиден
        throw new moodle_exception('invalid_question_type');
    }
    
    // Проверка типа источника изображения
    $imagesource = $question->get_imagesource();
    if ( $imagesource->get_plugin_name() != 'imagesource_webcamera' )
    {// Тип источника изображения вопроса не валиден
        throw new moodle_exception('invalid_question_baseimagesource');
    }
    
    // Проверка токена доступа к старнице
    if ( ! $imagesource->verify_access_token($qubaid, $slot, $token) )
    {
        throw new moodle_exception('invalid_question_access_token');
    }
    
    // Опция подтверждения сохранения захвата
    $saving_confirmation = false;
    if ( ! empty($question->id) )
    {
        $config = qtype_otimagepointer_get_options($question->id);
        if ( isset($config->webcamera->saving_confirmation) )
        {
            $saving_confirmation = (bool)$config->webcamera->saving_confirmation;
        }
    }
} catch ( moodle_exception $e )
{// Ошибка получения данных о вопросе
    print_object($e->errorcode);
    die;
}

// Установка параметров страницы
$PAGE->set_pagelayout('popup');
$context = $quba->get_owning_context();

if ( $context->contextlevel == CONTEXT_MODULE )
{
    $modulecontext = $context;
    $coursecontext = $context->get_course_context();
    $cm = get_fast_modinfo($coursecontext->instanceid)->cms[$modulecontext->instanceid];
    $PAGE->set_cm($cm);
} else
{
    $context = $context->get_course_context();
    $PAGE->set_context($context);
}

$sourcepluginurl = '/question/type/otimagepointer/classes/baseimagesources/webcamera';
$pageurl = new moodle_url($sourcepluginurl.'/capturewebcam.php');
$PAGE->set_url($pageurl);
$PAGE->set_title(get_string('imagesource_webcamera_capture_add', 'qtype_otimagepointer'));

// Добавление поддержки CSS
$cssurl = new moodle_url($sourcepluginurl.'/styles.css');
$PAGE->requires->css($cssurl);

// Регистрация YUI модуля в Moodle
$PAGE->requires->js_module(
		[
				'name' => 'moodle-qtype_otimagepointer-imagesource_webcamera-capture',
				'fullpath' => new moodle_url($sourcepluginurl.'/yui/capture/capture.js'),
				'requires' => array('node', 'event', 'panel', 'json', 'querystring')
		]
    );

// ОТОБРАЖЕНИЕ УВЕДОМЛЕНИЙ
$html = html_writer::div('', '', ['id' => 'imgs_wbc_messages']);

// ОТОБРАЖЕНИЕ ВЕБ-КАМЕРЫ
$videoblock = html_writer::start_tag(
    'video', 
    ['id' => 'imgs_wbc_video', 'class' => 'active', 'autoplay' => null]
);
$videoblock .= html_writer::end_tag('video');
// Блок отображения полученного изображения
$videoblock .= html_writer::start_tag(
    'canvas',
    ['id' => 'imgs_wbc_canvas']
);
$videoblock .= html_writer::end_tag('canvas');
// Добавление на страницу блока отображения веб-камеры
$html .= html_writer::div($videoblock, 'imgs_wbc_capturewrapper', ['id' => 'imgs_wbc_capturewrapper']);

// ОТОБРАЖЕНИЕ ИНТЕРФЕЙСА УПРАВЛЕНИЯ

$html .= html_writer::start_div('imgs_wbc_controlwrapper', ['id' => 'imgs_wbc_controlwrapper']);

// Управляющий блок захвата изображения
$classes = 'imgs_wbc_control_capture';
if ( ! $saving_confirmation )
{
    $classes .= ' force_save';
}
$html .= html_writer::start_div($classes, ['id' => 'imgs_wbc_control_capture']);
$html .= html_writer::link(
    null,
    get_string('imagesource_webcamera_capture_capture', 'qtype_otimagepointer'),
    [
        'id' => 'imgs_wbc_capturebtn',
        'class' => 'button btn'
    ]
);
$html .= html_writer::end_div();
// Управляющий блок подтверждения изображения
$html .= html_writer::start_div('imgs_wbc_control_submit', ['id' => 'imgs_wbc_control_submit']);
$html .= html_writer::link(
    null, 
    get_string('imagesource_webcamera_capture_save', 'qtype_otimagepointer'),
    [
        'id' => 'imgs_wbc_submitbtn',
        'class' => 'button btn'
    ]
);
$html .= html_writer::link(
    null, 
    get_string('imagesource_webcamera_capture_cancel', 'qtype_otimagepointer'),
    [
        'id' => 'imgs_wbc_cancelbtn',
        'class' => 'button btn'
    ]
);
$html .= html_writer::end_div();
// Управляющий блок закрытия окна
$html .= html_writer::start_div('imgs_wbc_control_close', ['id' => 'imgs_wbc_controlwrapper']);
$html .= html_writer::link(
    null, 
    get_string('imagesource_webcamera_capture_close', 'qtype_otimagepointer'),
    [
        'id' => 'imgs_wbc_closebtn',
        'class' => 'button btn'
    ]
    );
$html .= html_writer::end_div();

$html .= html_writer::end_div();

// ФОРМА СОХРАНЕНИЯ ПОЛЬЗОВАТЕЛЬСКОГО ИЗОБРАЖЕНИЯ
$formurl = new moodle_url(
    $sourcepluginurl.'/capturewebcam.php',
    [
        'quba' => $qubaid,
        'slot' => $slot,
        'token' => $token
    ]
);
$customdata = new stdClass();
$customdata->qa = $qa;
$form = new qtype_otimagepointer\baseimagesources\webcamera\captureform(
    $formurl, $customdata, 'post', '', ['id' => 'imgs_wbc_form']);
$form->process();
$html .= $form->render();

// Обертка
$html = html_writer::div($html, 'imgs_wbc_popupwrapper');

// Инициализация модуля захвата видео
$PAGE->requires->yui_module(
    'moodle-qtype_otimagepointer-imagesource_webcamera-capture',
    'Y.Moodle.qtype_otimagepointer.imagesource_webcamera.capture.init'
);

// Установка шапки страницы
echo $OUTPUT->header();

echo $html;

// Установка подвала страницы
echo $OUTPUT->footer();