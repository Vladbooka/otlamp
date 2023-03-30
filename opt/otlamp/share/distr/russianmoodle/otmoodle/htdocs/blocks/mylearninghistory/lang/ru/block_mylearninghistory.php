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

$string['mylearninghistory:myaddinstance'] = 'Добавить блок "История обучения" на Мою домашнюю страницу';
$string['mylearninghistory:addinstance'] = 'Добавить блок "История обучения"';
$string['mylearninghistory:viewmylearninghistory'] = 'Просматривать свою собственную историю обучения';
$string['mylearninghistory:viewuserslearninghistory'] = 'Просматривать историю обучения других пользователей';
$string['pluginname'] = 'История обучения';
$string['pluginconfig'] = 'Конфигурация плагина "История обучения"';
$string['error_loginrequired'] = 'Чтобы использовать этот блок, вы должны авторизоваться';
$string['defaults'] = 'По умолчанию';
$string['config'] = 'Конфигурация';
$string['useplugin'] = 'Включить автоматическое управление плагинами';
$string['nocourses'] = 'В данный момент курсы не доступны';
$string['nograde'] = '-';
$string['course'] = 'Название курса';
$string['rating'] = 'Оценка';
$string['competencies'] = 'Освоено компетенций';
$string['progress'] = 'Статус завершения';
$string['progressdoesnttracking'] = 'не&nbsp;отслеживается';
$string['ueenddate'] = 'Тип подписки';
$string['ueenddatenolimit'] = 'не&nbsp;ограничено';
$string['students'] = 'Студенты';
$string['enrolscount'] = 'Записано на курс';
$string['learninghistory'] = 'История обучения';
$string['linktointerface'] = 'Перейти к истории обучения';
$string['to_full_history'] = 'Полный список';
$string['my_studcourses'] = 'Я изучаю';
$string['my_studcourses_completed'] = 'Изученные';
$string['my_teachcourses'] = 'Я преподаю';
$string['accessdenied'] = 'Доступ запрещен';
$string['course_completed'] = '100%';
$string['equal_label'] = 'Совпадает с указанным значением';
$string['notequal_label'] = 'Не совпадает с указанным значением';
$string['like_label'] = 'Содержит указанное значение';
$string['graterorequal_label'] = 'Больше либо равно указанному значению';
$string['lessorequal_label'] = 'Меньше либо равно указанному значению';
$string['grater_label'] = 'Больше указанного значения';
$string['less_label'] = 'Меньше указанного значения';
$string['in_label'] = 'Совпадает с одним из указанных значений';
$string['notin_label'] = 'Не совпадает ни с одним из указанных значений';
$string['switch_label'] = 'Учитывать поле при фильтрации';
$string['rule_label'] = 'Правило фильтрации';
$string['noccf_value'] = 'Кастомные поля курса не заданы. Вы можете задать набор кастомных полей в <a href="/admin/settings.php?section=crw_settings">общих настройках витрины курсов</a>';
$string['save'] = 'Сохранить';

$string['config_header_learning'] = 'Настройки раздела "Я изучаю"';
$string['config_header_learning_desc'] = 'В зависимости от настроек в этом разделе меняется внешний вид блока в разделе "Я изучаю"';
$string['config_learning_grade'] = 'Отображать колонку с оценкой пользователя';
$string['config_learning_grade_desc'] = 'Если включено, в таблице "Я изучаю" отображается колонка с оценкой';
$string['config_learning_competencies'] = 'Отображать колонку с освоенными компетенциями в курсе';
$string['config_learning_competencies_desc'] = 'Если включено, в таблице "Я изучаю" отображается колонка с общим количеством компетенций в курсе и с количеством освоенных компетенций';
$string['config_learning_progress'] = 'Отображать колонку со статусом прохождения';
$string['config_learning_progress_desc'] = 'Если включено, в таблице "Я изучаю" отображается колонка со статусом прохождения, основывающимся на условиях завершения курса';
$string['config_learning_enroldata'] = 'Отображать сведения о подписке на курс';
$string['config_learning_enroldata_desc'] = 'Если включено, в таблице "Я изучаю" отображается колонка с данными о подписке пользователя на курс';
$string['config_max_grade'] = 'Отображать максимальную оценку за курс';
$string['config_max_grade_desc'] = 'Если включено, то в итоговой оценке будет отображаться максимальная оценка за курс';
$string['config_view_type'] = 'Разделить курсы на Активные/Завершенные';
$string['config_view_type_desc'] = 'Если включено, то курсы будут отфильтрованы и отображены в двух таблицах "Я изучаю" и "Изученные"';
$string['config_learning_group_by'] = 'Группировать по';
$string['config_learning_group_by_desc'] = 'Если включено, в таблице "Я изучаю" курсы группируются по выбранному параметру, и по каждой группе выводится своя таблица курсов с заголовком';
$string['config_learning_group_by_nothing'] = 'Отключено';
$string['config_learning_group_by_category_name'] = 'Наименованию категории курса';
$string['config_learning_course_link_url'] = 'Переход по ссылкам должен осуществляться на';
$string['config_learning_course_link_url_course'] = 'страницу курса';
$string['config_learning_course_link_url_crw'] = 'страницу расширенного описания курса';
$string['config_learning_course_link_url_desc'] = '';
$string['config_learning_grade_view'] = 'Вариант отображения оценки';
$string['config_learning_grade_view_desc'] = 'Вариант с ограничением по длине подойдет вам, если вы используете тип оценки "Значение". Если вы используете тип оценки "Шкала" и у вас длинные названия оценок, выбирайте вариант без ограничения по длине.';
$string['config_learning_grade_view_overflowhidden'] = 'В одну строку с ограничением по длине';
$string['config_learning_grade_view_overflowauto'] = 'Несколько строк без ограничения по длине';
$string['config_learning_courses_filter'] = 'Фильтрация курсов';
$string['config_learning_courses_filter_desc'] = 'Данная настройка позволит отфильтровать курсы, которые необходимо отображать в секции &laquo;Я изучаю&raquo;';
$string['config_learning_courses_filter_button'] = 'Настроить фильтрацию';
$string['config_learning_courses_filter_rules'] = 'Правила фильтрации';
$string['config_learning_courses_filter_rules_desc'] = 'Данная настройка позволит указать правила фильтрации курсов для секции &laquo;Я изучаю&raquo;';
$string['config_learning_courses_filter_rules_button'] = 'Настроить правила фильтрации';

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
$string['config_teaching_course_link_url'] = 'Переход по ссылкам должен осуществляться на';
$string['config_teaching_course_link_url_course'] = 'страницу курса';
$string['config_teaching_course_link_url_crw'] = 'страницу расширенного описания курса';
$string['config_teaching_course_link_url_desc'] = '';
$string['config_teaching_courses_filter'] = 'Фильтрация курсов';
$string['config_teaching_courses_filter_desc'] = 'Данная настройка позволит отфильтровать курсы, которые необходимо отображать в секции &laquo;Я преподаю&raquo;';
$string['config_teaching_courses_filter_button'] = 'Настроить фильтрацию';
$string['config_teaching_courses_filter_rules'] = 'Правила фильтрации';
$string['config_teaching_courses_filter_rules_desc'] = 'Данная настройка позволит указать правила фильтрации курсов для секции &laquo;Я преподаю&raquo;';
$string['config_teaching_courses_filter_rules_button'] = 'Настроить правила фильтрации';

$string['ajaxpopup_courses_filter_header'] = 'Настройка фильтрации курсов';
$string['ajaxpopup_courses_filter_failed'] = 'Не удалось загрузить форму фильтрации курсов';
$string['ajaxpopup_courses_filter_rules_header'] = 'Настройка правил фильтрации курсов';
$string['ajaxpopup_courses_filter_rules_failed'] = 'Не удалось загрузить форму настройки правил фильтрации курсов';

