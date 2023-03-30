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
 * Витрина курсов. Страница редактирования кастомных дополнительных полей курса
 *
 * @package    local_crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use otcomponent_yaml\Yaml;

require_once('../../config.php');
require_once('locallib.php');
require_once('lib.php');

// ID курса
$id = required_param('id', PARAM_INT);


// Права доступа
if ( $id == SITEID )
{// Неправильный ID курса
    print_error('cannoteditsiteform');
}
// Получим курс
$course = get_course($id);
// Требуется вход
require_login($course);
// Контекст
$coursecontext = context_course::instance($course->id);
// Проверка прав
require_capability('moodle/course:update', $coursecontext);



$PAGE->set_course($course);
$PAGE->set_pagelayout('course');
$PAGE->set_context($coursecontext);
$PAGE->set_title(get_string('coursesettings', 'local_crw'));
$PAGE->set_heading(get_string('coursesettings', 'local_crw'));
$PAGE->set_pagetype('crw-custom-settings');
// $PAGE->set_pagelayout('admin');
$pageurl = new moodle_url('/local/crw/coursecustomsettings.php', ['id' => $id]);
$PAGE->set_url($pageurl);


$customformhtml = '';
$customcoursefields = get_config('local_crw', 'custom_course_fields');

if (!empty($customcoursefields))
{
    
    // Нам необходимо проверить все поля на предмет их отключения в категории
    // поэтому вручную парсим форму, чтобы получить все поля и пробежаться по ним
    $arr = [];
    try {
        // парсинг разметки
        $arr = Yaml::parse($customcoursefields, yaml::PARSE_OBJECT);
    } catch (Exception $e) { }
    if ( array_key_exists('class', $arr) && is_array($arr['class']) )
    {
        foreach($arr['class'] as $fieldname => $value)
        {
            // Получение настройки роли поля на уровне категории
            $fieldrole = local_crw_get_category_config(
                $course->category,
                'custom_field_'.$fieldname.'_role'
            );
            // Для редактирования оно будет запрещено только в одном случае - если полностью отключено
            if ($fieldrole == 'field_disabled')
            {
                // Удаляем из конфига
                unset($arr['class'][$fieldname]);
            }
        }
        // конвертим объект обратно в yaml разметку, чтобы скормить её нашему плагину и двигаться дальше по стандартному алгоритму
        $customcoursefields = \otcomponent_yaml\Yaml::dump($arr);
    }
    
    $result = \otcomponent_customclass\utils::parse($customcoursefields);
    
    if ( $result->is_form_exists() )
    {
        
        // Форма
        $customform = $result->get_form();
        // Кастомные поля формы
        $cffields = $customform->get_fields();
        // Сохраненные данные
        $cfdata = custom_form_course_fields_get_data($course->id, $cffields);
        // получение сведений о повторяющихся группах полей (количество).
        $repeatscount = custom_form_course_fields_count_repeats($cffields, $cfdata);
        // инициализация формы
        $customform->setForm($pageurl, ['repeatscount' => $repeatscount]);
        // Обработка отправленной формы
        custom_form_course_fields_process($customform, $course->id);
        // Установка хранящихся в БД данных к форме
        $customform->set_data($cfdata);
        
        // Рендер формы
        $customformhtml .= $customform->render();
    } else {
        // Настройка кастомных полей формы не валидна
    }
} else {
    // Кастомные поля формы никто не настраивал
}


echo $OUTPUT->header();

// Отобразим форму
echo $customformhtml;

echo $OUTPUT->footer();
