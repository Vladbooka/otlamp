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
 * @package   block_mylearninghistory
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['mylearninghistory:myaddinstance'] = 'Add a new "My learning history" block to My home';
$string['mylearninghistory:addinstance'] = 'Add a new "My learning history" block';
$string['mylearninghistory:viewmylearninghistory'] = 'View own learning history';
$string['mylearninghistory:viewuserslearninghistory'] = 'View learning history of other users';
$string['pluginname'] = 'My learning history';
$string['pluginconfig'] = 'My learning history configuration';
$string['error_loginrequired'] = 'To use this block you must be logged in';
$string['defaults'] = 'Default';
$string['config'] = 'Configuration';
$string['useplugin'] = 'Enable automatic plugin management';
$string['nocourses'] = 'No courses available at the moment';
$string['nograde'] = '-';
$string['course'] = 'Course';
$string['rating'] = 'Rating';
$string['competencies'] = 'Competencies';
$string['progress'] = 'Course completion';
$string['progressdoesnttracking'] = 'doesnt tracking';
$string['ueenddate'] = 'User enrolment end date';
$string['ueenddatenolimit'] = 'no limit';
$string['students'] = 'Students';
$string['enrolscount'] = 'Count of enrols';
$string['learninghistory'] = 'Learning history';
$string['linktointerface'] = 'Show learning history';
$string['to_full_history'] = 'Full list';
$string['my_studcourses'] = 'I am learning';
$string['my_studcourses_completed'] = 'I have learned';
$string['my_teachcourses'] = 'I am teaching';
$string['accessdenied'] = 'Access denied';
$string['course_completed'] = '100%';
$string['equal_label'] = 'Equal to the specified value';
$string['notequal_label'] = 'Not equal to the specified value';
$string['like_label'] = 'Contains the specified value';
$string['graterorequal_label'] = 'Greater than or equal to the specified value';
$string['lessorequal_label'] = 'Less than or equal to the specified value';
$string['grater_label'] = 'Greater than the specified value';
$string['less_label'] = 'Less than the specified value';
$string['in_label'] = 'Matches one of the specified values';
$string['notin_label'] = 'Does not match any of the specified values';
$string['switch_label'] = 'Consider field when filtering';
$string['rule_label'] = 'Filter rule';
$string['noccf_value'] = 'Custom course fields are not defined. You can specify a set of custom fields in the <a href="/admin/settings.php?section=crw_settings">showcase general settings</a>';
$string['save'] = 'Save';

$string['config_header_learning'] = 'Настройки раздела "Я изучаю"';
$string['config_header_learning_desc'] = 'В зависимости от настроек в этом разделе меняется внешний вид блока в разделе "Я изучаю"';
$string['config_learning_grade'] = 'Отображать колонку с оценкой пользователя';
$string['config_learning_grade_desc'] = 'Если включено, в таблице "Я изучаю" отображается колонка с оценкой';
$string['config_learning_competencies'] = 'Отображать колонку с освоенными компетенциями в курсе';
$string['config_learning_competencies_desc'] = 'Если включено, в таблице "Я изучаю" отображается колонка с общим количеством компетенций в курсе и с количеством освоенных компетенций';
$string['config_learning_progress'] = 'Отображать колонку со статусом прохождения';
$string['config_learning_progress_desc'] = 'If enabled, the "I am learning" table displays a column with progress status based on course completion conditions';
$string['config_learning_enroldata'] = 'Отображать сведения о подписке на курс';
$string['config_learning_enroldata_desc'] = 'Если включено, в таблице "Я изучаю" отображается колонка с данными о подписке пользователя на курс';
$string['config_max_grade'] = 'Отображать максимальную оценку за курс';
$string['config_max_grade_desc'] = 'Если включено, то в итоговой оценке будет отображаться максимальная оценка за курс';
$string['config_view_type'] = 'Отфильтровать курсы на Активные/Завершенные';
$string['config_view_type_desc'] = 'Если включено, то курсы будут отфильтрованы и отображены в двух таблицах "Я изучаю" и "Изученные"';
$string['config_learning_group_by'] = 'Группировать по';
$string['config_learning_group_by_desc'] = 'Если включено, в таблице "Я изучаю" курсы группируются по выбранному параметру, и по каждой группе выводится своя таблица курсов с заголовком';
$string['config_learning_group_by_nothing'] = 'Отключено';
$string['config_learning_group_by_category_name'] = 'Наименованию категории курса';
$string['config_learning_course_link_url'] = 'Follow links should be on';
$string['config_learning_course_link_url_course'] = 'course page';
$string['config_learning_course_link_url_crw'] = 'advanced course description page';
$string['config_learning_course_link_url_desc'] = '';
$string['config_learning_grade_view'] = 'Grade display option';
$string['config_learning_grade_view_desc'] = 'The option with a restriction on length will suit you if you use the grade type "Value". If you use the grade type "Scale" and you have long rating names, choose an option with no length limit.';
$string['config_learning_grade_view_overflowhidden'] = 'One line with a length limit';
$string['config_learning_grade_view_overflowauto'] = 'Multiple lines with no length limit';
$string['config_learning_courses_filter'] = 'Course Filtering';
$string['config_learning_courses_filter_desc'] = 'This setting will allow you to filter the courses that need to be displayed in the section &laquo;I\'m learning&raquo;';
$string['config_learning_courses_filter_button'] = 'Set up filtering';
$string['config_learning_courses_filter_rules'] = 'Filtering rules';
$string['config_learning_courses_filter_rules_desc'] = 'This setting allows you to specify the rules for filtering courses for the section &laquo;I\'m learning&raquo;';
$string['config_learning_courses_filter_rules_button'] = 'Set up filtering rules';

$string['config_header_teaching'] = 'Настройки раздела "Я преподаю"';
$string['config_header_teaching_desc'] = 'В зависимости от настроек в этом разделе меняется внешний вид блока в разделе "Я преподаю"';
$string['config_teaching_enrolscount'] = 'Отображать количество подписанных на курс пользователей';
$string['config_teaching_enrolscount_desc'] = 'Если включено, в таблице "Я преподаю" отображается колонка с количеством пользователей, подписанным на курс';
$string['config_teaching_enroldata'] = 'Отображать сведения о подписке на курс';
$string['config_teaching_enroldata_desc'] = 'Если включено, в таблице "Я преподаю" отображается колонка с данными о подписке пользователя на курс';
$string['config_teaching_group_by'] = 'Группировать по';
$string['config_teaching_group_by_desc'] = 'Если включено, в таблице "Я преподаю" курсы группируются по выбранному параметру, и по каждой группе выводится своя таблица курсов с заголовком';
$string['config_teaching_group_by_nothing'] = 'Отключено';
$string['config_teaching_group_by_category_name'] = 'Наименованию категории курса';
$string['config_teaching_course_link_url'] = 'Follow links should be on';
$string['config_teaching_course_link_url_course'] = 'course page';
$string['config_teaching_course_link_url_crw'] = 'advanced course description page';
$string['config_teaching_course_link_url_desc'] = '';
$string['config_teaching_courses_filter'] = 'Course Filtering';
$string['config_teaching_courses_filter_desc'] = 'This setting will allow you to filter the courses that need to be displayed in the section &laquo;I\'m teaching&raquo;';
$string['config_teaching_courses_filter_button'] = 'Set up filtering';
$string['config_teaching_courses_filter_rules'] = 'Filtering rules';
$string['config_teaching_courses_filter_rules_desc'] = 'This setting allows you to specify the rules for filtering courses for the section &laquo;I\'m teaching&raquo;';
$string['config_teaching_courses_filter_rules_button'] = 'Set up filtering rules';

$string['ajaxpopup_courses_filter_header'] = 'Setting course filtering';
$string['ajaxpopup_courses_filter_failed'] = 'Failed to load filtering form';
$string['ajaxpopup_courses_filter_rules_header'] = 'Setting Course Filtering Rules';
$string['ajaxpopup_courses_filter_rules_failed'] = 'Failed to load filtering rule settings form';
$string['frontend_handler_not_found'] = 'The specified field handler was not found. Maybe you forgot to put the file ({$a}).';
