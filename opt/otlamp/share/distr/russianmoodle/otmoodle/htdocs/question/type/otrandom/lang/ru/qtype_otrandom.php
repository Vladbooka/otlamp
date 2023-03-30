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
 * Тип вопроса Случайный вопрос с учетом правил. Языковые переменные.
 *
 * @package    qtype
 * @subpackage otrandom
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Базовые переменные
$string['pluginname'] = 'Случайный вопрос с учетом правил выдачи';
$string['pluginname_help'] = '';
$string['pluginname_link'] = 'question/type/otrandom';
$string['pluginnameadding'] = 'Добавить вопрос "Случайный вопрос с учетом правил"';
$string['pluginnameediting'] = 'Редактировать вопрос "Случайный вопрос с учетом правил"';
$string['pluginnamesummary'] = 'Случайный вопрос с настраиваемой вероятностью добавления';

// Настройка плагина

// Группы
$string['group_base_name'] = '';
$string['group_lastfailed_name'] = 'Последний ответ ошибочный';
$string['group_morefailed_name'] = 'Число ошибочных ответов больше чем число верных';
$string['group_lessused_name'] = 'Вопрос редко добавлялся пользователю';
$string['group_repetition_name'] = 'Вопрос добавлялся пользователю';

// Настройки экземпляра
$string['editform_base_weight'] = 'Справка';
$string['editform_lastfailed_weight'] = 'Справка';
$string['editform_morefailed_weight'] = 'Справка';
$string['editform_lessused_weight'] = 'Справка';
$string['editform_repetition_weight'] = 'Справка';
$string['editform_base_weight_help'] = '';
$string['editform_lastfailed_weight_help'] = 'При увеличении веса в тест будет попадать больше вопросов, по которым последний ответ пользователя был неправильным';
$string['editform_morefailed_weight_help'] = 'При увеличении веса в тест будет попадать больше вопросов, по которым количество неправильных ответов пользователя превышает количество правильных';
$string['editform_lessused_weight_help'] = 'При увеличении веса в тест будет попадать больше вопросов, которые реже других показывались пользователю';
$string['editform_repetition_weight_help'] = 'При увеличении веса в тест будет попадать больше вопросов, на которые пользователь уже давал ответ';
$string['editform_header_randomquestion'] = 'Настройки выбора возможных вопросов';
$string['editform_targetcategory_label'] = 'Категория для выбора случайного вопроса';
$string['editform_includesubcategories_label'] = 'Учитывать подкатегории';
$string['editform_header_groups'] = 'Весовые коэффициенты групп';
$string['editform_groupweight_description'] = 'Вес группы определяет, насколько велика вероятность попадания вопроса из указанной группы в тест';
$string['editform_grouplevel_label'] = 'Видимость групп';
$string['editform_grouplevel_system'] = 'Система';
$string['editform_grouplevel_course'] = 'Курс';
$string['editform_grouplevel'] = 'Справка';
$string['editform_grouplevel_help'] = 'Видимость определяет область, в которой группы пытаются найти ответы пользователя на вопросы';

// Отображение вопроса
$string['question_name_default'] = 'Случайный вопрос с учетом правил';

// Ошибки
$string['error_editform_groupweight_overflow_min'] = 'Вес группы не не может быть меньше {$a}';
$string['error_editform_groupweight_overflow_max'] = 'Вес группы не не может быть больше {$a}';
$string['error_editform_emptyavailable'] = 'В указанной категории нет вопросов для выбора';

