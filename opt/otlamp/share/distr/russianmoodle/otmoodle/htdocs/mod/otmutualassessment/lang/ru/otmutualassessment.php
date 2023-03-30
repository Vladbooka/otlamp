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
 * Общие языковые строки
 */
$string['pluginname'] = 'Взаимная оценка';
$string['modulename'] = 'Взаимная оценка';
$string['modulename_help'] = 'Элемент курса «Взаимная оценка» позволяет организовать оценивание слушателями друг друга с помощью распределения или начисления баллов. Модуль поддерживает взаимное оценивание слушателей курса или членов локальной группы (или потока). Результаты оценивания отображаются в «Отчете по выставленным баллам» и попадают в журнал текущих оценок курса.';
$string['modulenameplural'] = 'Взаимная оценка';
$string['pluginadministration'] = 'Управление модулем Взаимная оценка';
$string['no_graded_users'] = 'Пользователей, доступных для оценки, не найдено';
$string['instruction_for_grader_mutual_0'] = '<p>Для выставления баллов участникам курса/группы воспользуйтесь формой ниже.<br/> Вам нужно распределить {$a->points} балл между всеми участниками.</p>';
$string['instruction_for_grader_mutual_1'] = '<p>Для выставления баллов участникам курса/группы воспользуйтесь формой ниже.<br/> Вам нужно распределить {$a->points} балла между всеми участниками.</p>';
$string['instruction_for_grader_mutual_2'] = '<p>Для выставления баллов участникам курса/группы воспользуйтесь формой ниже.<br/> Вам нужно распределить {$a->points} баллов между всеми участниками.</p>';
$string['instruction_for_grader_mutual'] = '<p>Для выставления баллов участникам курса/группы воспользуйтесь формой ниже.<br/> Вам нужно распределить {$a->points} баллов между всеми участниками.</p>';
$string['instruction_for_grader_range_0'] = '<p>Для выставления баллов участникам курса/группы воспользуйтесь формой ниже.<br/> Вам нужно поставить от {$a->min} до {$a->max} балла каждому участнику.</p>';
$string['instruction_for_grader_range_1'] = '<p>Для выставления баллов участникам курса/группы воспользуйтесь формой ниже.<br/> Вам нужно поставить от {$a->min} до {$a->max} баллов каждому участнику.</p>';
$string['instruction_for_grader_range_2'] = '<p>Для выставления баллов участникам курса/группы воспользуйтесь формой ниже.<br/> Вам нужно поставить от {$a->min} до {$a->max} баллов каждому участнику.</p>';
$string['instruction_for_grader_range'] = '<p>Для выставления баллов участникам курса/группы воспользуйтесь формой ниже.<br/> Вам нужно поставить от {$a->min} до {$a->max} баллов каждому участнику.</p>';
$string['grades_already_set'] = 'Вы выставили оценки всем участникам';
$string['your_total_points_0'] = 'На текущий момент другие участники поставили вам {$a} балл';
$string['your_total_points_1'] = 'На текущий момент другие участники поставили вам {$a} балла';
$string['your_total_points_2'] = 'На текущий момент другие участники поставили вам {$a} баллов';
$string['your_total_points'] = 'На текущий момент другие участники поставили вам {$a} баллов';
$string['report_link_text'] = 'Перейти к отчету';
$string['report'] = 'Отчет по выставленным баллам';
$string['warning_info'] = 'На данный момент не все задачи на обновление данных завершены. Данные в отчете могут быть не актуальны.
                           <br/>Когда все задачи завершатся и данные будут актуальны, это сообщение не будет отображаться.';
$string['points_summ'] = 'Сумма баллов';
$string['grade'] = 'Оценка';
$string['empty_report'] = 'Ни один из участников еще не выставлял оценки';
$string['noanycapability'] = 'У вас недостаточно прав для работы с модулем курса';
$string['no_enrol_or_capability'] = 'Для выставления оценок необходимо быть подписанным в курс и иметь право на выставление оценок';
$string['modulenameplural'] = 'Взаимные оценки';
$string['deletepoints'] = 'Удалить все выставленные баллы';
$string['deletestatuses'] = 'Сбросить статусы оценщиков';
$string['graders_caption'] = 'Оценщики';
$string['gradeds_caption'] = 'Оценённые';
$string['refresh'] = 'Пересчет оценок';
$string['refresh_form_header'] = 'Пересчет оценок';
$string['refresh_form_submit'] = 'Пересчитать сейчас';
$string['refresh_task_form_header'] = 'Отложенная задача на пересчет оценок';
$string['refresh_task_form_submit'] = 'Добавить задачу';
$string['task_already_added'] = 'Задача на пересчет оценок уже запланирована, дождитесь ее выполнения';
$string['task_added'] = 'Задача на пересчет оценок запланирована';
$string['task_not_added'] = 'Во время добавления задачи на пересчет оценок возникли ошибки. Возможно задача уже была добавлена.';
$string['process_refresh_live_ended'] = 'Обновление оценок завершено';
$string['process_refresh_cron_started'] = 'Задачи на обновление оценок добавлены';
$string['process_refresh_not_required'] = 'Обновление данных не требуется, так как оценки еще не выставлялись';
$string['status'] = 'Статус';
$string['status_notrequired'] = 'Оценивание не требуется';
$string['status_completed'] = 'Оценивание завершено';
$string['status_notcompleted'] = 'Оценивание не завершено';
$string['deletevote'] = 'Удалить результат голосования участника {$a->fullname}';
$string['deletegroupvote'] = 'Удалить результат голосования участника {$a->fullname} в группе {$a->groupname}';
$string['deletevote_desc'] = 'Удаление приведет к отмене выполнения элемента оценщиком и пересчету оценок других участников. 
                              <br/>История голосования не будет сохранена, восстановить данные будет невозможно.';
$string['deletevote_success'] = 'Результат голосования пользователя {$a->fullname} удален';
$string['deletegroupvote_success'] = 'Результат голосования пользователя {$a->fullname} в группе {$a->groupname} удален';
$string['deletevote_failed'] = 'Во время удаления результата голосования пользователя {$a} произошла ошибка. Обратитесь в службу технической поддержки.';
$string['deletegroupvote_failed'] = 'Во время удаления результата голосования пользователя {$a} в группе {$a->groupname} произошла ошибка. Обратитесь в службу технической поддержки.';

/**
 * Форма редактирования элемента курса
 */
$string['strategy_mutualassessment'] = 'Взаимная оценка';
$string['title'] = 'Название';
$string['description'] = 'Описание';
$string['strategy'] = 'Стратегия';
$string['strategy_help'] = 'Выбранная стратегия оценки влияет на формирование групп и рассчет оценок. После выставления оценок кому-либо из участников смена стратегии модуля невозможна.';
$string['completionsetgrades'] = 'Студент должен выставить оценки другим участникам';
$string['save_grades'] = 'Сохранить оценки';
$string['leftpoints'] = 'Осталось распределить';
$string['strategy_mutual'] = 'Взаимная оценка: распределение баллов';
$string['strategy_range'] = 'Взаимная оценка: выбор из диапазона баллов';
$string['gradingmode'] = 'Метод расчета оценки';
$string['absolute_gradingmode'] = 'Абсолютная оценка';
$string['relative_gradingmode'] = 'Относительная оценка';
$string['gradingmode_help'] = 'Абсолютная оценка - сумма выставленных пользователю баллов другими участниками, но не более максимального балла за элемент курса. Относительная оценка - отношение суммы выставленных пользователю баллов другими участниками к максимально возможноному числу баллов, которые он может набрать теоретически.';
$string['minpoints_label'] = 'Минимально возможное количество баллов';
$string['maxpoints_label'] = 'Максимально возможное количество баллов';

/**
 * Форма сохранения баллов
 */
$string['grades_saved_successfully'] = 'Оценки успешно сохранены';

/**
 * Настройки плагина
 */
$string['settings_savegraderhistory'] = 'Сохранять историю голосования при изменении состава участников?';
$string['settings_savegraderhistory_desc'] = 'По умолчанию при удалении пользователя из группы или курса его история голосования сохраняется. При повторной подписке или возврате в группу ранее проставленые баллы и оценки восстановятся. Если необходимо удалять историю, выставите соответствующую настройку.';
$string['setting_strategy_range_min'] = 'Значение по умолчанию для минимально возможного количество баллов';
$string['setting_strategy_range_min_desc'] = 'Данное значение будет автоматически подставлено в форму редактирования элемента курса как значение по умолчанию в соответствующем поле';
$string['setting_strategy_range_max'] = 'Значение по умолчанию для максимально возможного количество баллов';
$string['setting_strategy_range_max_desc'] = 'Данное значение будет автоматически подставлено в форму редактирования элемента курса как значение по умолчанию в соответствующем поле';
$string['settings_page_general'] = 'Общие настройки';
$string['settings_category_strategies'] = 'Стратегии';
$string['live_efficiency'] = 'В реальном времени';
$string['cron_efficiency'] = 'В фоновом режиме';
$string['settings_efficiencyofrefresh'] = 'Оперативность обновления данных при изменении условий';
$string['settings_efficiencyofrefresh_desc'] = 'При изменении состава участников (снятие/назначение роли, удаление подписки, добавление/удаление члена локальной группы) в модуле реализован пересчет оценок, статусов голосования и статусов выполнения элемента Взаимная оценка.
                                                <br/>При большом числе участников курса этот процесс может занимать существенное время и замедлять работу.
                                                <br/>В случае замедления работы рекомендуется использовать фоновый режим пересчета.
                                                <br/>В фоновом режиме при возникновении ситуации, требующей актуализации информации, в системе будет запланирована задача на пересчет данных, которая будет выполнена в ближайшем цикле обработки фоновых задач.
                                                <br/>Пока задача не выполнится, в отчете по выставленным баллам будет показано уведомление о том, что текущие данные могут быть не актуальны и задача на актуализацию данных запланирована к выполнению.';

/**
 * Ошибки
 */
$string['error_invalid_grader'] = 'Во время получения формы оценивания участников произошла ошибка: не установлен оценщик';
$string['error_invalid_grade_must_be_greater_than_zero'] = 'Введено недопустимое значение: оценка должна быть больше нуля';
$string['error_all_points_are_not_distributed'] = 'Вы не распределили все баллы между участниками';
$string['error_invalid_userpoints_summ'] = 'Неверная сумма баллов пользователя';
$string['error_failed_to_save_points'] = 'Не удалось сохранить выставленные баллы';
$string['error_cannot_load_the_grade_item'] = 'N/A';
$string['error_undefined_otmutualassessment_id'] = 'Не удалось найти модуль по переданному идентификатору или идентификатор не передан';
$string['error_failed_to_set_status'] = 'Не удалось выставить статус {$a->status} пользователю с идентификатором {$a->userid}';
$string['error_invalid_strategy'] = 'Не удалось загрузить стратегию оценки';
$string['error_mod_form_strategy_can_not_be_changed'] = 'Часть оценок за модуль уже выставлена, изменение стратегии при выставленных оценках невозможно';
$string['error_mod_form_gradingmode_can_not_be_changed'] = 'Часть оценок за модуль уже выставлена, изменение режима оценивания при выставленных оценках невозможно';
$string['error_mod_form_groupmode_can_not_be_changed'] = 'Часть оценок за модуль уже выставлена, изменение группового режима при выставленных оценках невозможно';
$string['error_mod_form_invalid_min_value'] = 'Нельзя указать минимальное значение меньше заданного в глобальных настройках модуля курса';
$string['error_mod_form_invalid_max_value'] = 'Нельзя указать максимальное значение больше заданного в глобальных настройках модуля курса';
$string['error_invalid_grade_must_be_grater_than_min_value'] = 'Минимальное значение не может быть меньше {$a}';
$string['error_invalid_grade_must_be_less_than_max_value'] = 'Максимальное значение не может быть больше {$a}';
$string['error_invalid_grade_must_be_not_empty'] = 'Нельзя указать пустое значение, нужно выставить участнику оценку';
$string['error_mod_form_min_must_be_less_max'] = 'Минимальное значение должно быть меньше максимального';

/**
 * Сообщения
 */

/**
 * Права
 */
$string['otmutualassessment:gradeothers'] = 'Право оценивать других участников курса или группы';
$string['otmutualassessment:begradedbyothers'] = 'Право быть оценным другими участниками курса или группы';
$string['otmutualassessment:addinstance'] = 'Право добавлять в курс элемент Взаимная оценка';
$string['otmutualassessment:viewgrades'] = 'Право просматривать оценки';
$string['nopermissions'] = 'У вас нет права делать это: {$a}';
$string['otmutualassessment:refreshgrades'] = 'Право пересчитывать оценки';
$string['otmutualassessment:managesettings'] = 'Право изменять глобальные настройки модуля';
$string['otmutualassessment:deletevotes'] = 'Право удалять результаты голосования участников';

/**
 * События
 */
$string['grade_updated'] = 'Оценка обновлена';
$string['grader_status_updated'] = 'Статус оценщика обновлен';
$string['refresh_grades'] = 'Обновление оценок';

/**
 * Задачи
 */
$string['task_refresh_grades_title'] = 'Обновление оценок в модуле Взаимная оценка';

