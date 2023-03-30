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
 * Блок комментарий преподавателя. Языковые переменные.
 *
 * @package    block
 * @subpackage block_quiz_teacher_feedback
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// СИСТЕМНЫЕ СТРОКИ
$string['pluginname'] = 'Комментарий преподавателя';
$string['quiz_teacher_feedback:addinstance'] = 'Добавлять новый блок «Комментарий преподавателя»';
$string['quiz_teacher_feedback:myaddinstance'] = 'Добавлять новый блок «Комментарий преподавателя» на страницу /my (Мои курсы, Личный кабинет, Dashboard)';
$string['quiz_teacher_feedback:use'] = 'Использовать блок «Комментарий преподавателя»';

$string['no'] = 'Нет';
$string['yes'] = 'Да';

// НАСТРОЙКИ
$string['config_header'] = 'Настройки блока';
$string['config_user_attempt_control_title'] = 'Контроль прохождения теста';
$string['config_user_attempt_control_enable'] = 'Включено';
$string['config_user_attempt_control_disable'] = 'Выключено';
$string['config_user_attempt_control_title_help'] = 'Описание';
$string['config_user_attempt_control_title_help_help'] = 'Данная опция позволяет контролировать прохождение теста. Студент не может перейти к следующей странице, пока преподаватель вручную не подтвердит текущий ответ.';
$string['config_question_slot'] = 'Слот: ';

$string['config_request_mode'] = 'Режим отправки ответа на проверку';
$string['config_request_mode_immidiately'] = 'Сразу отправлять';
$string['config_request_mode_choose_default_yes'] = 'Спрашивать (по умолчанию отправлять)';
$string['config_request_mode_choose_default_no'] = 'Спрашивать (по умолчанию не отправлять)';

$string['config_replace_checkbox'] = 'Перенести галку отправки ответа на проверку в форму отправки ответа на вопрос';

// ПОЛЬЗОВАТЕЛЬСКИЕ СТРОКИ
$string['title'] = 'Комментарий преподавателя';

$string['feedback_form_feedback'] = 'Комментарий';
$string['feedback_form_feedback_help'] = '';
$string['feedback_form_feedback_help_help'] = '';
$string['feedback_form_grade'] = 'Оценка (из {$a->maxmark})';
$string['feedback_form_completed_title'] = 'Статус вопроса';
$string['feedback_form_notcompleted'] = 'Вопрос не завершен';
$string['feedback_form_completed'] = 'Вопрос завершен';
$string['feedback_form_control_on'] = 'Контролировать';
$string['feedback_form_control_off'] = 'Не контролировать';
$string['feedback_form_control_questions'] = 'Контроль вопросов';
$string['feedback_form_submit'] = 'Сохранить';

// Информация
$string['feedback_info_current_notcompleted'] = 'Вопрос не подтвержден преподавателем';
$string['feedback_info_current_completed'] = 'Вопрос подтвержден преподавателем';
$string['feedback_info_current_grade'] = 'Текущая оценка:';
$string['feedback_info_current_grade_not_set'] = 'Не указана';
$string['feedback_info_unfinished_attempts'] = 'Незавершенные попытки';
$string['feedback_info_current_grade_not_set'] = 'Не указана';
$string['feedback_info_users_attempts'] = 'Попытки';
$string['feedback_info_all_students'] = 'Все учащиеся';
$string['feedback_info_filter_group'] = 'Фильтр по группе';
$string['feedback_info_questions_to_grade'] = 'Статус вопросов';
$string['feedback_info_button_to_view_attempt'] = 'попытка';

// Модальное окно
$string['feedback_info_modal_header'] = 'Информация по прохождению теста';
$string['feedback_info_modal_notyet_question'] = 'Преподаватель еще не подтвердил ответ на предыдущий вопрос!';
$string['feedback_info_modal_check_questions'] = 'Преподаватель подтвердил все ваши ответы!';

// Статус вопросов
$string['button_graded'] = 'Подтвержден преподавателем';
$string['button_not_answered'] = 'Нет ответа';
$string['button_in_process'] = 'Требуется подтверждение';
$string['button_in_process_with_grade'] = 'Требуется повторное подтверждение (Балл: {$a})';

$string['question_header_grade'] = '(Балл: {$a})';

$string['send_request'] = 'Отправить ответ на проверку';
$string['send_rerequest'] = 'Отправить ответ на повторную проверку';

$string['feedbacksaveok'] = 'Данные успешно сохранены';

