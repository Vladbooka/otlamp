<?php
$string['title'] = 'Учебные дисциплины';
$string['page_main_name'] = 'Управление учебными дисциплинами';
$string['name'] = 'Название';
$string['eduweeks'] = 'Количество недель';
$string['eduweeks_thead'] = 'Количество<br/>недель';
$string['department'] = 'Подразделение';
$string['status'] = 'Статус';
$string['notfoundpitem'] = 'Дисциплина не найдена';
$string['eduyear'] = 'Учебный год';
$string['editpitem'] = 'Редактировать дисциплину';
$string['newpitem'] = 'Создать дисциплину';
$string['pridepends'] = 'Зависимости для дисциплины \'$a\'';
$string['addpitem'] = 'Добавить дисциплину';
$string['pitem'] = 'Дисциплина';
$string['nameorcode'] = 'Название или код';
$string['list'] = 'Список';
$string['new'] = 'Создать';
$string['no_pitems_found'] = 'Дисциплин с такими параметрами не найдено';
$string['pages'] = 'Страницы';
$string['actions'] = 'Действия';
$string['errorsavepitem'] = 'Не удалось сохранить дисциплину';
$string['edit'] = 'Редактировать';
$string['view'] = 'Просмотреть';
$string['sname'] = 'Название в стандарте';
$string['sname_thead'] = 'Название<br/>в<br/>стандарте';
$string['code'] = 'Код';
$string['scode'] = 'Код в стандарте';
$string['scode_thead'] = 'Код<br/>в<br/>стандарте';
$string['program'] = 'Программа';
$string['type'] = 'Тип';
$string['required'] = 'Обязательный';
$string['required_thead'] = 'Обяз.';
$string['maxcredit'] = 'Зачетные единицы трудоемкости (максимальное количество кредитов за курс, ЗЕТ)';
$string['maxduration'] = 'Длительность';
$string['hours_all'] = 'Часов всего';
$string['hours_all_thead'] = 'Часов<br/>всего';
$string['hours_laboratorywork'] = 'Часов лабораторных работ';
$string['hours_laboratoryworks_thead'] = 'Часов<br/>лабораторных<br/>работ';
$string['hours_selfstudywithteacher'] = 'Часов самостоятельной работы с преподавателем';
$string['hours_selfstudywithteacher_thead'] = 'Часов<br>самостоятельной работы<br>с преподавателем';
$string['hours_theory'] = 'Часов лекций (Лек)';
$string['hours_theory_thead'] = 'Часов<br/>лекций (Лек)';
$string['hours_practice'] = 'Часов практики (Пр)';
$string['hours_week'] = 'Часов в неделю';
$string['hours_practice_thead'] = 'Часов<br/>практики (Пр)';
$string['pitem_type'] = 'Тип дисциплины';
$string['level'] = 'Уровень компоненты';
$string['level_thead'] = 'Уровень<br/>компоненты';
$string['about'] = 'Описание';
$string['notice'] = 'Заметки';
$string['controltype'] = 'Тип итогового контроля';
$string['controltype_thead'] = 'Тип<br/>итогового<br/>контроля';
$string['err_numeric'] = 'Только числовые значения';
$string['err_name_required'] = 'Необходимо ввести название';
$string['err_required'] = 'Это поле необходимо заполнить';
$string['department_and_programm'] = 'Подразделение:<br/>Программа<br/>Количество периодов';
$string['in_development'] = '<i>(В разработке)</i>';
$string['scale'] = 'Шкала оценок';
$string['scale_description'] = 'Введите список возможных оценок через запятую';
$string['mingrade'] = 'Минимальный балл,$a необходимый для успешного$a окончания курса';
$string['lessonscale'] = 'Шкала оценок занятий по дисциплине';
$string['lessonpassgrade'] = 'Проходной балл занятий по дисциплине';
$string['lessonpassgrade_help'] = 'Если слушатель получит оценки ниже проходного, то занятие будет помечено в отчетах как требующее отработки';
$string['usediscscaleandpassgrade'] = 'Использовать шкалу, правила конвертации и проходной балл занятий как у дисциплины';
$string['coursegradesconversation'] = 'Параметры конвертации оценки из СДО для дисциплины';
$string['coursegradesconversation_help'] = '
Настроив параметр, при конвертации оценки из курса Moodle в итоговую оценку в ЭД, будут использоваться введенные правила конвертации<br><br>

Поле можно оставить пустым, тогда при конвертации шкала разделится на равные интервалы<br><br>

Проверка вхождения в диапазон осуществляется<br>
from <= процент прохождения < to<br>
Для последней оценки в шкале<br>
from <= процент прохождения <= to<br><br>

Правила формирования разметки:<br>
1) Каждый интервал деления шкалы определяется строкой вида - <оценка шкалы> : {from: <крайнее левое значение диапазона, to: <крайнее правое значение диапазона>}<br>
2) Крайнее левое значение интервала первого деления должно быть равно 0<br>
3) Крайнее правое значение интервала второго деления должно быть равно 100<br><br>

Пример правильной разметки для шкалы 1,2,3,4,5 :<br><br>

1 : {from: 0, to: 20}<br>
2 : {from: 20, to: 40}<br>
3 : {from: 40, to: 60}<br>
4 : {from: 60, to: 80}<br>
5 : {from: 80, to: 100}<br><br>

Пример правильной разметки для шкалы 2,3,4,5 :<br><br>

2 : {from: 0, to: 50}<br>
3 : {from: 50, to: 70}<br>
4 : {from: 70, to: 85}<br>
5 : {from: 85, to: 100}<br><br>

Пример правильной разметки для шкалы 2,3,4,5 :<br><br>

2 : {from: 0, to: 50}<br>
3 : {from: 50, to: 80}<br>
4 : {from: 80, to: 80}<br>
5 : {from: 80, to: 100}<br>

В последнем примере для оценки 4 указан диапазон от 80 до 80. При конвертации 80 процентов в оценку, в результате получим оценку - 4<br>';

$string['modulegradesconversation'] = 'Параметры конвертации оценки для занятий';
$string['modulegradesconversation_help'] = '
Настроив параметр, при синхронизации оценки между электронным деканатом и Moodle будут использоваться введенные правила конвертации<br><br>

Поле можно оставить пустым, тогда при конвертации шкала разделится на равные интервалы<br><br>

Проверка вхождения в диапазон осуществляется<br>
from <= процент прохождения < to<br>
Для последней оценки в шкале<br>
from <= процент прохождения <= to<br><br>

Правила формирования разметки:<br>
1) Каждый интервал деления шкалы определяется строкой вида - <оценка шкалы> : {from: <крайнее левое значение диапазона, to: <крайнее правое значение диапазона>}<br>
2) Крайнее левое значение интервала первого деления должно быть равно 0<br>
3) Крайнее правое значение интервала второго деления должно быть равно 100<br><br>

Пример правильной разметки для шкалы 1,2,3,4,5 :<br><br>

1 : {from: 0, to: 20}<br>
2 : {from: 20, to: 40}<br>
3 : {from: 40, to: 60}<br>
4 : {from: 60, to: 80}<br>
5 : {from: 80, to: 100}<br><br>

Пример правильной разметки для шкалы 2,3,4,5 :<br><br>

2 : {from: 0, to: 50}<br>
3 : {from: 50, to: 70}<br>
4 : {from: 70, to: 85}<br>
5 : {from: 85, to: 100}<br><br>

Пример правильной разметки для шкалы 2,3,4,5 :<br><br>

2 : {from: 0, to: 50}<br>
3 : {from: 50, to: 80}<br>
4 : {from: 80, to: 80}<br>
5 : {from: 80, to: 100}<br>

В последнем примере для оценки 4 указан диапазон от 80 до 80. При конвертации 80 процентов в оценку, в результате получим оценку - 4<br>';

$string['coursecls'] = 'Степень однородности';
$string['mdlcourse'] = 'Курс в moodle';
$string['gradelevel'] = 'Уровень оценки';
$string['to_save'] = 'Сохранить';
$string['agenums'] = 'Параллель';
$string['no_periods'] = 'Нет';
$string['yes'] = 'Да';
$string['no'] = 'Нет';
$string['days'] = 'Дней';
$string['in_days'] = 'в днях';
$string['err_required'] = 'Код дисциплины должен быть уникальным';
$string['err_dept_notexists'] = 'Такого подразделения нет в базе данных';
$string['err_prog_notexists'] = 'Такой учебной программы нет в базе данных';
$string['err_incorrect_agenum'] = 'Неправильно указана параллель';
$string['err_only_positive'] = 'Это значение не может быть отрицательным';
$string['err_unique'] = 'Код дисциплины должен быть уникальным';
$string['err_numeric'] = 'Это поле только для чисел';
$string['to_find'] = 'Найти';
$string['search'] = 'Поиск';
$string['school'] = 'Школа';
$string['program_structure'] = 'Состав программы';
$string['program_not_found'] = 'Программа не найдена';
$string['no_items_in_program'] = 'Учебная программа пуста';
$string['parallel'] = 'Параллель';
$string['optional_pitems'] = 'Доступны для всех параллелей';
$string['pitems_list_for_program'] = 'Список дисциплин, входящих в программу';
$string['view_plans'] = 'Посмотреть тематическое планирование';
$string['view_cpassed'] = 'Посмотреть подписки на эту дисциплину';
$string['to_select'] = 'Выбрать';
$string['create_cstreams_for_this'] = 'Создать учебные процессы для этой параллели';
$string['get_exam_roll'] = 'Получить экзаменационную ведомость';
$string['err_course_Moodle'] = 'Неверно введен id курса Moodle';
$string['err_scale'] = 'Неверно указана шкала оценок';
$string['err_scale_not_number_diapason'] = 'Диапазон может быть только числовым';
$string['err_scale_max_min_must_be_different'] = 'Максимальное и минимальное значение должны различаться';
$string['err_mingrade_is_not_valid'] = 'Указанный минимальный балл не соответствует введенной шкале оценок';
$string['err_scale_null_element'] = 'Пустые элементы в шкале недопустимы';
$string['return_on_programm'] = 'Вернуться обратно на программу';
$string['return_on_list_programm'] = 'Вернуться обратно на состав программы';
$string['view_programm'] = 'Посмотреть программу на эту дисциплину';
$string['change_status'] = 'Сменить статус';
$string['invalid_status'] = 'Недопустимый статус';
$string['change_status_manual'] = 'Изменить статус вручную';
$string['change_to'] = 'Изменить на';
$string['status_change_success'] = 'Статус успешно изменен';
$string['status_change_failure'] = 'Не удалось изменить статус';
$string['this_is_final_status'] = 'Это конечный статус, его нельзя изменить';
$string['no_status'] = 'Статус отсутствует, его нельзя изменить';
$string['create_cstream_for_programm'] = 'Создать учебные процессы для этой программы';
$string['create_cstream_for_programmiteam'] = 'Создать учебный процесс для этой дисциплины';
$string['teachers_list_for_pitem'] = 'Список преподавателей для этой дисциплины';
$string['assign_teachers_for_programmiteam'] = 'Подписать преподавателей на эту дисциплину';
$string['participants_cstreams'] = 'Учебный план';
$string['dependtype'] = 'Тип зависимости';
$string['adddepend'] = 'Добавить зависимость';
$string['alreadyexist'] = 'Данная зависимость уже существует';
$string['candidatedepempty'] = 'Дисциплины-кандидаты для зависимостей отсутствуют';
$string['view_plancstream'] = 'Просмотр примерного тематического планирования на дисциплину';
$string['gradesyncenabled'] = 'Синхронизация<br/>оценок<br/>разрешена';
$string['incjournwithoutgrade'] = 'Включать в ведомость<br/>пользователей без оценки<br/>или не подписанных на курс';
$string['incjournwithunsatisfgrade'] = 'Включать в ведомость<br/>пользователей с<br/>неудовлетворительной оценкой';
$string['altgradeitem'] = 'id категории оценивания из журнала Moodle';
$string['err_get_discs_list'] = 'Ошибка получения всех доступных для добавления в зависимости дисциплин';
$string['mcourse'] = 'Курс в Moodle';
$string['err_active_cstreams_exist'] = 'Для того чтобы изменить привязку к курсу moodle нужно сначала завершить эти учебные потоки:';
$string['dependtype:requirepritem'] = 'Требуется прохождение дисциплины';
$string['altgradeitem'] = 'id категории оценивания';
$string['gradesyncenabled'] = 'Синхронизация<br/>оценок<br/>разрешена';
$string['incjournwithoutgrade'] = 'Включать в ведомость<br/>пользователей без оценки<br/>или не подписанных на курс';
$string['incjournwithunsatisfgrade'] = 'Включать в ведомость<br/>пользователей с<br/>неудовлетворительной оценкой';
$string['limit_message'] = 'Достигнут лимит объектов в этом подразделении $a';
$string['err_pitem_not_exists'] = 'Ошибка: указанная дисциплина не существует';
$string['resync_task_added'] = 'Задание на пересинхронизацию было добавлено, и будет выполнено в течении нескольких часов';
$string['resync_cstreams'] = 'Пересинхронизировать учебные потоки';
$string['resync'] = 'Пересинхронизация';
$string['active_stop'] = 'Приостановить активные cведения об изучаемых курсах';
$string['suspend_go'] = 'Активировать приостановленные cведения об изучаемых курсах';
$string['active_suspend'] = 'Задание на $a было добавлено, и будет выполнено в течении нескольких часов';
$string['resync_notice'] = 'Приостановить, а затем снова запустить все потоки дисциплины, причем берется 1 поток, приостанавливается, запускается, берется 2...
         В свою очередь приостановленный поток приостанавливает сведения об изучаемых дисциплинах(cpassed).';
$string['active_notice'] = 'Использеутся для активации всех cpassed-ов для данной дисциплины.';
$string['stop_notice'] = 'Служит для остановки всех cpassed-ов данной дисциплины.Это позволяет сменить привязки к курсу Moodle';
$string['metapitem_add_from']='Добавить из метадисциплины';
$string['metapitem_add']='Добавить метадисциплину';
$string['metapitem_table_title']='Доступный список метадисциплин для параллели ';
$string['newmetapitem']='Создать метадисциплину';
$string['editmetapitem']='Редактировать метадисциплину';
$string['metaprogrammitems_list']='Метадисциплины';
$string['create']='Создать';
$string['create_and_edit']='Создать и редактировать';
$string['limit_message_metapitems']='Достигнут лимит метадисциплин!';
$string['sync_on']='Включена';
$string['sync_off']='Отключена';
$string['sync_with_metapitems']='Синхронизация с метадисциплиной';
$string['pitem_create_success'] = 'Дисциплина успешно создана';
$string['pitem_create_failure'] = 'Не удалось создать дисциплину';
$string['change_course_header'] = 'Смена курса';
$string['change_course_confirm'] = ' Подтвердить смену курса';
$string['change_course_select'] = 'Курс в Moodle ';
$string['change_course_submit'] = 'Изменить';
$string['no_selection_course'] = 'Оставить без курса';
$string['change_course_message'] = 'Задание на смену курса Moodle добавлено, оно будет выполнено в ближайшее время';
$string['change_course_not_confirmed'] = ' ';
$string['billingtext'] = 'Цена дисциплины';
$string['salfactor'] = 'Поправочный$a зарплатный$a коэффициент';
$string['hours_lab'] = 'Часов лабораторных (Лаб)';
$string['hours_ind'] = 'Часов самостоятельной работы слушателя (СРС)';
$string['hours_control'] = 'Часов контроля (Контроль)';
$string['hours_classroom'] = 'Аудиторных часов';
$string['autohours'] = 'Автоматически расчитывать количество &quot;Часов всего&quot;';
$string['create_course'] = '——Создать——';
$string['no_course'] = '——Нет——';

// ФОРМЫ
$string['formsave_header_main_label'] = 'Основная информация';
$string['formsave_header_additional_label'] = 'Дополнительная информация';
$string['formsave_header_planning_label'] = 'Планирование дисциплины';
$string['formsave_header_stydying_label'] = 'Обучение в дисциплине';
$string['formsave_header_notused_label'] = 'Неиспользуемые параметры дисциплины';
$string['formsave_name_label'] = 'Название';
$string['formsave_name_label_help'] = 'Название, которое будет представлять дисциплину в системе';
$string['formsave_about_label'] = 'Описание';
$string['formsave_about_label_help'] = 'Описание дисциплины видимо пользователям и может быть переопределено в каждом учебном процессе этой дисциплины';
$string['formsave_code_label'] = 'Код';
$string['formsave_code_label_help'] = 'Уникальный код, по которому можно точно определить дисциплину';
$string['formsave_mingrade_label'] = 'Минимальный проходной балл';
$string['formsave_mingrade_label_help'] = 'Минимальный проходной балл в итоговой ведомости, необходимый для успешного завершения студентом дисциплины';
$string['formsave_selfenrol_label'] = 'Самостоятельная запись в дисциплину';
$string['formsave_selfenrol_label_help'] = "Значение по умолчанию, которое устанавливается для всех учебных процессов дисциплины. Возможность студентам самостоятельно записываться на учебные процессы по дисциплине. В случае, когда выбран пункт 'Доступна с заявкой' - запись на дисциплину должна быть подтверждена преподавателем вручную";
$string['formsave_selfenrol_disabled'] = 'Недоступна';
$string['formsave_selfenrol_enabled'] = 'Доступна без заявки';
$string['formsave_selfenrol_enabled_request'] = 'Доступна с заявкой';
$string['formsave_studentslimit_label'] = 'Максимальное число слушателей в потоке';
$string['formsave_studentslimit_label_help'] = 'Значение по умолчанию, которое устанавливается для всех учебных процессов дисциплины. Лимит, определяющий число одновременно обучающихся в учебном процессе студентов';


$string['selfenrol'] = 'Самозапись';
$string['studentslimit'] = 'Максимальное количество студентов';
$string['form_off_available'] = 'Недоступна';
$string['form_on_available'] = 'Доступна';
$string['form_request_available'] = 'Доступна с заявкой';

$string['mastercourse_verification'] = 'Согласование мастер-курса';
$string['request_coursedata_verification'] = 'Отправить на проверку';
$string['coursedata_verification_requested'] = 'Отправлено на проверку';
$string['verification_state_loading'] = 'Выполняется запрос';
$string['verification_state_loading_failed'] = 'Во время выполнения запроса возникла ошибка';
$string['accept_coursedata'] = 'Одобрить';
$string['decline_coursedata'] = 'Отклонить';
$string['mastercourse_link'] = 'Перейти в мастер-курс';
$string['verificationrequested'] = 'Требуется согласование мастер-курса';
$string['mastercourse_version'] = 'Версия мастер-курса';
$string['mastercourse_version_not_set'] = 'Нет согласованной версии';

$string['courselinktype'] = 'Режим формирования учебных процессов по умолчанию';
$string['err_courselinktype'] = 'Неверный тип связи';
$string['backups_link'] = '(Список согласованных версий контента мастер-курса)';
$string['empty_backups'] = 'У дисциплины отсутствуют согласованные версии контента мастер-курса';
$string['link_back'] = 'Вернуться назад';
$string['mastercourse_version_header'] = 'Список одобренных версий контента мастер-курса';
$string['mastercourse_actions'] = 'Действия';

$string['cannot_change_scale'] = 'Нельзя сменить шкалу, так как существуют занятия, по которым уже выставлена по текущей шкале! Сбросьте все оценки по занятиям и попробуйте еще раз';
$string['invalid_form_data'] = 'Отправленная форма не прошла валидацию! Пожалуйста проверьте правильность заполненных полей!';
$string['field'] = 'Поле «{$a}»: ';
