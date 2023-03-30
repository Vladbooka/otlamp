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
 * Плагин формата курсов OpenTechnology. Генерация формата курса.
 *
 * @package    format
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Подключение дополнительных библиотек
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/completionlib.php');

// Замена GET-параметров для обратной совместимости
if ( $topic = optional_param('topic', 0, PARAM_INT) ) 
{
    $url = $PAGE->url;
    $url->param('section', $topic);
    debugging('Outdated topic param passed to course/view.php', DEBUG_DEVELOPER);
    redirect($url);
}

// Получение контекста курса
$context = context_course::instance($course->id);

// Изменение текущего активного раздела
if ( ( $marker >= 0 ) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) 
{// Требуется сменить текущий активный раздел
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

// Определение базовых переменных
$courseformat = course_get_format($course);
$courseformat_settings = $courseformat->get_settings();
$course = $courseformat->get_course();
// Досоздание разделов в соответствии с настройкой числа разделов в курсе
course_create_sections_if_missing($course, range(0, $course->numsections));

// Получение рендера формата курса
$renderer = $PAGE->get_renderer('format_opentechnology');

// Установка типа устройства пользователя
$devicetype = core_useragent::get_device_type();
if ( $devicetype == 'mobile' ) 
{// Мобильный телефон
    $renderer->set_device_level(2);
} else if ( $devicetype == 'tablet' ) 
{// Планшет
    $renderer->set_device_level(1);
} else 
{// Компьютер
    $renderer->set_device_level(0);
}

// Отображение в зависимости от настройки
if ( ! empty($displaysection) ) 
{// Отобразить страницу с одним разделом и пагинацией
    $renderer->print_single_section_page($course, null, null, null, null, $displaysection);
} else 
{// Отобразить все разделы на одной странице

    // Получение настроек состояния разделов для текущего пользователя
    user_preference_allow_ajax_update('format_opentechnology_sectionscollapsestate_' . $course->id, PARAM_ALPHANUM);
    $userpreference = get_user_preferences('format_opentechnology_sectionscollapsestate_' . $course->id);
    
    if ( is_string($userpreference) && ! empty($userpreference) )
    {// Пользовательская настройка найдена
        // Нормализация настройки
        $userpreference = substr($userpreference, 0, $course->numsections+1);
        if ( strlen($userpreference) < $course->numsections+1 )
        {
            $userpreference = str_pad($userpreference, $course->numsections+1 , '0');
        }
    } else
    {// Автозаполнение настройки
        $userpreference = '';
        for ( $s = 0; $s <= $course->numsections; $s++ )
        {
            $userpreference .= '0';
        }
        set_user_preference('format_opentechnology_sectionscollapsestate_' . $course->id, $userpreference);
    }
    // Установка данных для рендера
    $renderer->set_user_preference($userpreference);
    
    // Инициализация JS - поддержки сворачивания разделов
    $PAGE->requires->js_init_call('M.format_opentechnology.init', [
        $course->id,
        $userpreference,
        $course->numsections
    ], true, [
        'name' => 'format_opentechnology',
        'fullpath' => '/course/format/opentechnology/module.js',
        'requires' => ['node', 'event','event-mouseenter']
    ]);
    $PAGE->requires->js(new moodle_url('/course/format/opentechnology/script.js'));

    $renderer->print_multiple_section_page($course, null, null, null, null);
}

// Добавление JS поддержки
$PAGE->requires->js('/course/format/opentechnology/format.js');
