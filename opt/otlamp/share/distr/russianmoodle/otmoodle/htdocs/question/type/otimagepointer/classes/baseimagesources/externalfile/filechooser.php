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
 * Тип вопроса Объекты на изображении. Источник изображения - внешний файл.
 * 
 * Файл изображения формируется на основе загруженного пользователем изображения
 *
 * @package    qtype
 * @subpackage otimagepointer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../../../../../config.php');
require_once($CFG->dirroot . '/question/type/otimagepointer/lib.php');
require_once($CFG->libdir . '/questionlib.php');

use qtype_otimagepointer\baseimagesources\externalfile\filepicker;

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
    if ( $imagesource->get_plugin_name() != 'imagesource_externalfile' )
    {// Тип источника изображения вопроса не валиден
        throw new moodle_exception('invalid_question_baseimagesource');
    }
    
    // Проверка токена доступа к старнице
    if ( ! $imagesource->verify_access_token($qubaid, $slot, $token) )
    {
        throw new moodle_exception('invalid_question_access_token');
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

$sourcepluginurl = '/question/type/otimagepointer/classes/baseimagesources/externalfile';
$pageurl = new moodle_url($sourcepluginurl.'/filechooser.php', ['quba' => $qubaid, 'slot' => $slot, 'token' => $token]);
$PAGE->set_url($pageurl); 

// Добавление поддержки CSS
$cssurl = new moodle_url($sourcepluginurl.'/styles.css');
$PAGE->requires->css($cssurl);

// Регистрация YUI модуля в Moodle
$PAGE->requires->js_module(
	[
			'name' => 'moodle-qtype_otimagepointer-imagesource_externalfile-saveprocess',
			'fullpath' => new moodle_url($sourcepluginurl.'/yui/saveprocess/saveprocess.js'),
			'requires' => ['node', 'event', 'panel', 'json', 'querystring']
	]
);

// Инициализация модуля загрузки изображения
$PAGE->requires->yui_module(
    'moodle-qtype_otimagepointer-imagesource_externalfile-saveprocess',
    'Y.Moodle.qtype_otimagepointer.imagesource_externalfile.saveprocess.init'
);

$html = '';

$customdata = new stdClass();
$customdata->qa = $qa;

$form = new filepicker($pageurl, $customdata);
$form->process();
$html .= $form->render();

// Установка шапки страницы
echo $OUTPUT->header();

echo $html;

// Установка подвала страницы
echo $OUTPUT->footer();