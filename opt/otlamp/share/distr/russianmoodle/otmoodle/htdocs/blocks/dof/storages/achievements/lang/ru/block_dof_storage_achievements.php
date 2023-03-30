<?php
$string['title'] = 'Шаблоны достижений';
    
/** Классы достижений **/
$string['dof_storage_achievements_complex'] = 'Комплексные критерии';
$string['complex_settings_form_title'] = 'Критерии';
$string['complex_settings_form_addcriteriatype'] = 'Новый критерий';
$string['complex_settings_form_addcriteriasubmit'] = 'Добавить';
$string['complex_settings_form_type_text'] = 'Текст';
$string['complex_settings_form_type_info'] = 'Общая информация';
$string['complex_settings_form_type_select'] = 'Список';
$string['complex_settings_form_save'] = 'Сохранить';
$string['complex_settings_form_criteria_name'] = 'Название поля';
$string['complex_settings_form_criteria_significant'] = 'Требуется подтверждение модератора';
$string['complex_settings_form_criteria_confirmfield'] = 'Включить поле загрузки файла';
$string['complex_settings_form_criteria_rate'] = 'Коэффициент';
$string['complex_settings_form_addselectoptsubmit'] = 'Добавить элемент списка';
$string['complex_settings_form_delete'] = 'Удалить критерий';
$string['complex_settings_form_criteria_confirmfielddesc'] = 'Название поля загрузки файла';
$string['complex_user_form_save'] = 'Сохранить';
$string['table_achievementins_criteria_name'] = 'Название критерия';
$string['table_achievementins_criteria_value'] = 'Значение критерия';
$string['table_achievementins_criteria_points'] = 'Коэф-т';
$string['table_achievementins_criteria_confirm'] = 'Подтверждающий документ';

$string['dof_storage_achievements_simple'] = 'Настраиваемый';
$string['simple_settings_form_title'] = 'Критерии';
$string['simple_settings_form_addcriteriatype'] = 'Новый критерий';
$string['simple_settings_form_addcriteriasubmit'] = 'Добавить';
$string['simple_settings_form_type_text'] = 'Текст';
$string['simple_settings_form_type_data'] = 'Дата';
$string['simple_settings_form_type_file'] = 'Файл';
$string['simple_settings_form_type_file_plagiarism_header'] = 'Настройки антиплагиата';
$string['simple_settings_form_type_select'] = 'Список';
$string['simple_settings_form_criteria_rate'] = 'Коэффициент';
$string['simple_settings_form_criteria_significant'] = 'Требуется подтверждение модератора';
$string['simple_settings_form_save'] = 'Сохранить';
$string['simple_settings_form_criteria_name'] = 'Название';
$string['simple_settings_form_addselectoptsubmit'] = 'Добавить элемент списка';
$string['simple_settings_form_delete'] = 'Удалить критерий';
$string['simple_user_form_save'] = 'Сохранить';
$string['simple_user_form_save_close'] = 'Сохранить и закрыть';
$string['simple_settings_form_criteria_plagiarism_add_to_index'] = 'Добавлять в индекс подтвержденный критерий';

// прохождение курса
$string['dof_storage_achievements_coursecompletion'] = 'Прохождение курса';

$string['coursecompletion_achievementin_course_name'] = 'Курс';
$string['coursecompletion_achievementin_course_deleted'] = 'Курс удален';
$string['coursecompletion_achievementin_auto_adding_on'] = 'Достижения автоматически будут добавлены в портфолио при прохождении курсов';

$string['achievement_coursecompletion_settings_form_title'] = 'Настройки';
$string['achievement_coursecompletion_settings_form_deadline'] = 'Крайний срок исполнения';
$string['achievement_coursecompletion_settings_form_autocompletion'] = 'Автоматически создавать достижения при завершении курса (не потребуется действий учащегося)';
$string['achievement_coursecompletion_settings_form_goaltype'] = 'Тип цели';
$string['achievement_coursecompletion_settings_form_choice_course'] = 'Целевые курсы';
$string['achievement_coursecompletion_settings_form_choice_course_all'] = 'Все курсы';
$string['achievement_coursecompletion_settings_form_choice_course_help'] = 'Если не выбран ни один курс, то как целевые курсы будут использованы все курсы Moodle';

$string['achievement_coursecompletion_settings_form_target'] = 'Формулировка цели';
$string['achievement_coursecompletion_settings_form_target_placeholder'] = 'Заполнив это поле вы жестко закрепите формулировку цели, которая будет установлена с помощью этого шаблона достижения. Если поле оставить пустым, пользователю будет необходимо заполнить его самостоятельно.';

$string['achievement_coursecompletion_settings_form_allowed_courses'] = 'Список допустимых курсов';
$string['achievement_coursecompletion_settings_form_save'] = 'Сохранить';

$string['coursecompletion_user_form_save'] = 'Сохранить';
$string['coursecompletion_user_form_save_close'] = 'Сохранить и закрыть';
$string['coursecompletion_user_form_choose_course'] = 'Целевые курсы';

// варнинги и ошибки
$string['achievementin_userform_error_invalid_goaldeadline_date'] = 'Дедлайн не может быть в прошлом';
$string['achievement_coursecompletion_settings_form_error_courses_empty'] = 'Не выбран ни один курс';
$string['achievement_coursecompletion_settings_form_error_chose_prohibited_course'] = 'Выбран недоступный курс';
$string['achievement_coursecompletion_userform_error_empty_course'] = 'Выберите курс';
$string['achievement_coursecompletion_userform_error_invalid_course'] = 'Выберите курс из предложенного списка';

$string['dof_storage_achievements_assignment'] = 'Результаты заданий';
$string['assignment_settings_form_title'] = 'Доступные задания';
$string['assignment_settings_form_choice_category'] = 'Выберите категорию';
$string['assignment_settings_form_choice_course'] = 'Выберите курсы';
$string['assignment_settings_form_choice_assignment'] = 'Выберите задание';
$string['assignment_settings_form_consider'] = 'Назначать баллы пропорционально оценке';
$string['assignment_settings_form_significant'] = 'Требуется подтверждение модератора';
$string['assignment_settings_form_add_to_index'] = 'Автоматически добавлять в индекс Антиплагиата подтвержденные достижения';
$string['assignment_settings_form_no_courses'] = 'В системе нет ни одного курса, содержащего задание';
$string['assignment_settings_user_form_title'] = 'Данные о достижении';
$string['assignment_settings_user_form_course'] = 'Курс';
$string['assignment_settings_user_form_assign'] = 'Задание';
$string['assignment_settings_user_form_grade'] = 'Оценка';
$string['assignment_settings_user_form_no_grade'] = 'Ваше задание еще не оценено';
$string['assignment_settings_user_form_achievement_already_added'] = 'Достижение уже добавлено';
$string['assignment_settings_user_form_assign_deleted'] = 'Задание удалено из системы';

$string['dof_storage_achievements_assignment_text_label'] = 'Текст задания';
$string['dof_storage_achievements_assignment_deleted'] = 'Причина: задание удалено из системы';
$string['dof_storage_achievements_notavailable'] = 'Достижение недоступно';
$string['dof_storage_achievements_assignment_feedback_label'] = 'Рецензия';

$string['dof_storage_achievements_base_no_access'] = 'Нет прав на добавление достижения';
$string['dof_storage_achievements_base_no_data'] = 'Достижение не сформировано';
$string['dof_storage_achievements_base_manual_create_error'] = 'Достижение не доступно для добавления';

$string['achievement_shortname'] = 'Достижение «{$a->name}»';
$string['achievementin_form_goal_deadline'] = 'Определите крайний срок достижения цели';

// вкладка настройки уведомлений
$string['form_achievements_edit_goal_notification_header'] = 'Настройка уведомлений';
$string['form_achievements_edit_goal_notification_stat_periodic_enable'] = 'Периодически, раз в N дней, уведомлять уполномоченных лиц о наличии несогласованных целей или неподтвержденных достижениях';
$string['form_achievements_edit_goal_notification_stat_promptly_enable'] = 'Единовременно уведомлять уполномоченных лиц о наличии несогласованных целей или неподтвержденных достижениях';
$string['form_achievements_edit_goal_notification_wait_completion_before_deadline_enable'] = 'Предупреждать пользователя за N дней до дедлайна, если цель не достигнута';
$string['form_achievements_edit_goal_notification_wait_completion_curator_before_deadline_enable'] = 'Предупреждать куратора за N дней до дедлайна, если цель не достигнута';
$string['form_achievements_edit_goal_notification_wait_completion_inday_deadline_enable'] = 'Предупреждать пользователя в день дедлайна, если цель не достигнута';
$string['form_achievements_edit_goal_notification_wait_completion_curator_inday_deadline_enable'] = 'Предупреждать куратора в день дедлайна, если цель не достигнута';
$string['form_achievements_edit_goal_notification_wait_completion_after_deadline_enable'] = 'Предупреждать пользователя через N дней после дедлайна, если цель не достигнута';
$string['form_achievements_edit_goal_notification_wait_completion_curator_after_deadline_enable'] = 'Предупреждать куратора через N дней после дедлайна, если цель не достигнута';
$string['form_achievements_edit_goal_notification_user_approve'] = 'Уведомлять пользователя при одобрении цели';
$string['form_achievements_edit_goal_notification_user_reject'] = 'Уведомлять пользователя при отклонении цели';

// валидация
$string['form_achievements_edit_goal_validate_put_days'] = 'Количество дней должно быть больше нуля';

$string['message_subject_stat_periodic'] = 'Отчет по шаблону в портфолио';

// для склонения целе/достижений
$string['wait_approval_0'] = '{$a} несогласованная цель';
$string['wait_approval_1'] = '{$a} несогласованные цели';
$string['wait_approval_2'] = '{$a} несогласованных целей';
$string['notavailable_0'] = '{$a} неподтвержденное достижение';
$string['notavailable_1'] = '{$a} неподтвержденных достижения';
$string['notavailable_2'] = '{$a} неподтвержденных достижений';

/**
 * Права/Capabilities
 */
$string['acl_create'] = 'Не используется [[storage_achievements_create]]';
$string['acl_edit'] = 'Не используется [[storage_achievements_edit]]';
$string['acl_view'] = 'Не используется [[storage_achievements_view]]';

    