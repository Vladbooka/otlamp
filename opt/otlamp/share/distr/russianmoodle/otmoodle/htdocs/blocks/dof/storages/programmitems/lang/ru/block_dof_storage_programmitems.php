<?php

$string['title'] = 'Дисциплины';
$string['page_main_name'] = 'Дисциплины';
// статусы предметов
$string['notused'] = 'Оценка не идет в кондуит';
$string['discipline'] = 'Дисциплина';
$string['coursework'] = 'Курсовая работа';
$string['practice'] = 'Практика';
$string['finalexam'] = 'Междисциплинарный экзамен';
$string['diplom'] = 'Дипломная работа';
$string['unknown_level'] = '&lt;Неизвестно&gt;';
// типы предметов
$string['type_required'] = 'Обязательный';
$string['type_recommended'] = 'Рекомендованный';
$string['type_free'] = 'По выбору';
$string['unknown_type'] = '&lt;Неизвестно&gt;';

// Логирование в крон
$string['change_mdlcourse_successful'] = 'Смена привязки курса к дисциплине (id={$a->programmitemid}) прошла успешно';
$string['change_mdlcourse_not_successful'] = 'Смена привязки курса к дисциплине (id={$a->programmitemid}) не удалась';
$string['cstream_group_was_delete'] = 'Группа Moodle, связанная с учебным процессом (id={$a->cstreamid}) удалена';
$string['cstream_group_was_not_delete'] = 'Не удалось удалить группа Moodle, связанную с учебным процессом (id={$a->cstreamid})';
$string['start_check_deleted_courses'] = 'Старт задачи на отвязку удаленных курсов...';
$string['programmitems_recieved'] = 'Дисциплины получены...';
$string['courses_recieved'] = 'Курсы получены...';
$string['course_realy_deleted'] = 'Курс id={$a->id} удален. Начинаем процедуру отвязки курса от дисциплины...';
$string['delcourses_not_recieved'] = 'Удаленных курсов, привязанных к дисциплинам, не найдено.';


$string['coursedata_verification_requested_mail_subject'] = 'Мастер-курс ожидает проверки';
$string['coursedata_verification_requested_mail_text'] = '{$a->initiator} отправил текущую версию мастер-курса \'{$a->course}\' на проверку для дисциплины \'{$a->discipline}\'';
$string['coursedata_accepted_mail_subject'] = 'Мастер-курс одобрен';
$string['coursedata_accepted_mail_text'] = 'Представленная на проверку версия мастер-курса \'{$a->course}\' одобрена для дисциплины \'{$a->discipline}\'';
$string['coursedata_declined_mail_subject'] = 'Мастер-курс отклонен';
$string['coursedata_declined_mail_text'] = 'Представленная на проверку версия мастер-курса \'{$a->course}\' отклонена для дисциплины \'{$a->discipline}\'';

$string['programmitem_not_found'] = 'Дисциплина не найдена';
$string['mdlcourse_not_found'] = 'Курс не найден';
$string['programmitem_save_failed'] = 'Не удалось сохранить данные дисциплины';
$string['edit_verificationrequested_access_denied'] = 'Недостаточно прав для отправки курса на проверку';
$string['coursedata_backup_failed'] = 'Не удалось создать резервную копию курса';
$string['edit_coursetemplate_access_denied'] = 'Недостаточно прав для верификации версии курса';

$string['acl_edit:verificationrequested'] = 'Отправлять мастер-курсы на проверку';
$string['acl_benotified:edit:coursetemplateversion'] = 'Получать уведомления о результатах проверки мастер-курса';
$string['acl_edit:coursetemplateversion'] = 'Согласовывать версию мастер-курса';
$string['acl_benotified:edit:verificationrequested'] = 'Получать уведомления о новых запросах на проверку мастер-курса';
$string['acl_view:mastercourse'] = 'Видеть ссылку на мастер-курс';