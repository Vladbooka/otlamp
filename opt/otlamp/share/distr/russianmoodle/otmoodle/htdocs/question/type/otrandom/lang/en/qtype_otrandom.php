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
$string['pluginname'] = 'Random with rules';
$string['pluginname_help'] = '';
$string['pluginname_link'] = 'question/type/otrandom';
$string['pluginnameadding'] = 'Add question "Random with rules"';
$string['pluginnameediting'] = 'Edit question "Random with rules"';
$string['pluginnamesummary'] = 'Random with configurable probability of a question adding';

// Настройка плагина

// Группы
$string['group_base_name'] = '';
$string['group_lastfailed_name'] = 'The last answer is wrong';
$string['group_morefailed_name'] = 'The number of erroneous answers is greater than the number of correct ones';
$string['group_lessused_name'] = 'The question was rarely added to the user';
$string['group_repetition_name'] = 'The question has been added to the user';

// Настройки экземпляра
$string['editform_base_weight'] = 'Help';
$string['editform_lastfailed_weight'] = 'Help';
$string['editform_morefailed_weight'] = 'Help';
$string['editform_lessused_weight'] = 'Help';
$string['editform_repetition_weight'] = 'Help';
$string['editform_base_weight_help'] = '';
$string['editform_lastfailed_weight_help'] = 'If you increase the weight, more questions will appear in the test, according to which the user`s last response was incorrect';
$string['editform_morefailed_weight_help'] = 'If you increase the weight, more questions will appear in the test, in which the number of incorrect user responses exceeds the number of correct ones';
$string['editform_lessused_weight_help'] = 'As the weight increases, more questions will appear in the test, which are less often shown to the user';
$string['editform_repetition_weight_help'] = 'If you increase the weight, more questions will be added to the test, to which the user has already responded';
$string['editform_header_randomquestion'] = 'Question choosing settings';
$string['editform_targetcategory_label'] = 'Target category';
$string['editform_includesubcategories_label'] = 'Add subcategories';
$string['editform_header_groups'] = 'Group weights';
$string['editform_groupweight_description'] = 'The weight of the group determines how likely the question gets from the specified group to the quiz';
$string['editform_grouplevel_label'] = 'Group visibility';
$string['editform_grouplevel_system'] = 'System';
$string['editform_grouplevel_course'] = 'Course';
$string['editform_grouplevel'] = 'Help';
$string['editform_grouplevel_help'] = 'Visibility defines the area in which groups try to find user answers to questions';

// Отображение вопроса
$string['question_name_default'] = 'Random with rules';

// Ошибки
$string['error_editform_groupweight_overflow_min'] = 'The weight of the group can not be less than {$a}';
$string['error_editform_groupweight_overflow_max'] = 'The weight of the group can not be greater than {$a}';
$string['error_editform_emptyavailable'] = 'There are no questions to select in the specified category';

