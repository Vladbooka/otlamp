<?php
$string['title'] = 'Менеджер журнала';
$string['page_main_name'] = 'Менеджер журнала';

$string['grades_priority_dof'] = 'Электронный деканат';
$string['grades_priority_moodle'] = 'Moodle';

$string['grades_synctype_off'] = 'Выключена';
$string['grades_synctype_manually'] = 'Вручную';
$string['grades_synctype_auto'] = 'Автоматически';

// валидация шкалы
$string['err_scale'] = 'Неверно указана шкала оценок';
$string['err_scale_not_number_diapason'] = 'Интервал может быть только числовым';
$string['err_scale_max_min_must_be_different'] = 'Максимальное и минимальное значение должны различаться';
$string['err_scale_null_element'] = 'Пустые элементы в шкале недопустимы';
$string['err_mingrade_is_not_valid'] = 'Указанный минимальный балл не соответствует введенной шкале оценок';

// валидация разметки конвертации оценок
$string['err_grades_conversation_options_invalid_markup'] = 'Неверная разметка. Пожалуйста, посмотрите пример и попробуйте еще раз';
$string['err_grades_conversation_options_invalid_count'] = 'Неверно определены интервалы для оценок. Должно быть определено «{$a->ok}» интервалов, определено «{$a->invalid}» интервалов';
$string['err_grades_conversation_options_invalid_grade'] = 'Оценка «{$a}» отсутствует в шкале';
$string['err_grades_conversation_options_invalid_from'] = 'Отсутствует параметр from в интервале для оценки «{$a}»';
$string['err_grades_conversation_options_invalid_to'] = 'Отсутствует параметр to в интервале для оценки «{$a}»';
$string['err_grades_conversation_options_invalid_params_type'] = 'Параметр from/to должны быть числовыми';
$string['err_grades_conversation_options_invalid_from_more_to'] = 'Параметр from не может быть больше параметру to';
$string['err_grades_conversation_options_invalid_from_more_prev_to'] = 'Параметр from следующего интервала не может быть меньше параметра to предыдущего интервала';
$string['err_grades_conversation_options_invalid_first_from'] = 'Параметр from первого интервала должен равняться 0';
$string['err_grades_conversation_options_invalid_last_to'] = 'Параметр to последнего интервала должен равняться 100';
$string['err_grades_conversation_options_invalid_sum'] = 'Сумма разности всех интервалов должна равняться 100';

$string['gradescompulsion_normal'] = 'Обычная оценка';
$string['gradescompulsion_need_grade'] = 'Обязательно должна быть оценка';
$string['gradescompulsion_need_positive_grade'] = 'Обязательно должна быть положительная оценка';