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
 * Модуль Логика курса. Языковые переменные.
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Базовые языковые переменные
$string['pluginname'] = 'Логика курса';
$string['modulename'] = 'Логика курса';
$string['pluginadministration'] = 'Управление элементом "Логика курса"';
$string['modulename_help'] = 'Модуль поддержки для курса, позволяющий автоматизировать процессы отправки уведомлений, зачисления на курс, отчисления из курса и внесения изменений в профиль слушателей в зависимости от широкого ряда условий и настроек.';
$string['modulenameplural'] = 'Логики курса';
$string['messageprovider:otcourselogic_reminders'] = 'Уведомления от логики курса';
$string['event_state_switched_title'] = 'Изменение состояния элемента "Логика курса"';
$string['event_state_switched_desc'] = '';
$string['event_enrol_to_course_cycle_title'] = 'Циклическая перезапись на курс';
$string['event_enrol_to_course_cycle_desc'] = '';
$string['task_periodic_execution_title'] = 'Периодическое исполнение задач';
$string['task_state_checking_title'] = 'Проверка состояния элемента "Логика курса"';
$string['otcourselogic:addinstance'] = 'Право добавлять модуль "Логика курса" в курс';
$string['otcourselogic:view'] = 'Право видеть элемент курса "Логика курса"';
$string['otcourselogic:view_student_states'] = 'Право просмотра состояний студентов курса';
$string['otcourselogic:is_student'] = 'Право получать уведомления для студентов';
$string['otcourselogic:is_teacher'] = 'Право получать уведомления для преподавателей';
$string['otcourselogic:is_curator'] = 'Право получать уведомления для кураторов';
$string['otcourselogic:admin_panel'] = 'Право на использование административной панели';
$string['event_action_execution_ended_title'] = 'Действие выполнено';
$string['event_action_execution_ended_desc'] = 'Событие удачного завершения действия Логики курса';

// Глобальные настройки плагина
$string['settings_tarif'] = 'Тарифный план';
$string['already_has_serial'] = 'Серийный номер уже был получен';
$string['reset_otserial'] = 'Сбросить серийный номер';
$string['otserial_check_fail'] = 'Серийный номер не прошел проверку на сервере.
Причина: {$a}. Если Вы считаете, что этого не должно было
произойти, пожалуйста, обратитесь в службу технической поддержки.';
$string['otkey'] = 'Секретный ключ';
$string['otserial'] = 'Серийный номер СЭО 3KL';
$string['otserial_check_ok'] = 'Серийный номер действителен.';
$string['get_otserial'] = 'Получить серийный номер';
$string['get_otserial_fail'] = 'Не удалось получить серийный номер СЭО 3KL на сервере api.opentechnology.ru. Сервер сообщил ошибку: {$a}';
$string['otservice'] = 'Тарифный план: <u>{$a}</u>';
$string['otserial_tariff_wrong'] = "Тарифный план недоступен для данного продукта. Обратитесь в службу технической поддержки.";
$string['otservice_expired'] = 'Срок действия Вашего тарифного плана истёк. Если Вы желаете продлить срок, пожалуйста, свяжитесь с менеджерами ООО "Открытые технологии".';
$string['otservice_active'] = 'Тарифный план действителен до {$a}';
$string['otservice_unlimited'] = 'Тарифный план действует бессрочно';
$string['demo_settings'] = 'Для активации плагина обратитесь в компанию <a href="http://opentechnology.ru/">Открытые Технологии</a>.';
$string['settings_general'] = 'Общие настройки';

// Форма очистки курса
$string['form_reset_course_reset_state_label'] = 'Очистить состояния элементов для всех пользователей';
$string['form_reset_course_userstates_were_reset'] = 'Пользовательские состояния элемента {$a} сброшены';
$string['form_reset_course_userstates_were_not_reset'] = 'Пользовательские состояния элемента {$a} не были сброшены';

// Настройки элемента курса
$string['form_header_activity_state'] = 'Оперативность проверки';
$string['form_active_state_available'] = 'Доступен пользователю';
$string['form_active_state_notavailable'] = 'Не доступен пользователю';
$string['form_active_state_label'] = 'Элемент активен, когда';
$string['form_active_state_help'] = 'Описание';
$string['form_active_state_help_help'] = 'Установка активности элемента курса в зависимости от его доступности для студента. Активному модулю можно назначить задачи на исполнение(Отправка уведомлений и др.)';
$string['form_activating_delay_label'] = 'Отсрочка активации';
$string['form_activating_delay_help'] = 'Описание';
$string['form_activating_delay_help_help'] = 'Данная настройка позволяет отложить момент активации логики курса на указанный период после выполнения условий активации';
$string['form_check_period_every_start'] = 'При каждой возможности';
$string['form_check_period_every_15'] = 'Каждые 15 минут';
$string['form_check_period_every_30'] = 'Каждые 30 минут';
$string['form_check_period_every_60'] = 'Каждый час';
$string['form_check_period_every_180'] = 'Каждые 3 часа';
$string['form_check_period_every_1440'] = 'Каждый день';
$string['form_check_period_never'] = 'Не делать периодических проверок';
$string['form_check_period_label'] = 'Периодическая проверка состояния';
$string['form_check_period_help'] = 'Описание';
$string['form_check_period_help_help'] = 'Период проверки состояния модуля для поддержания актуальности данных. Уменьшение интервала увеличивает нагрузку на сервер, но повышает актуальность данных о состоянии модуля для подписанных на курс пользователей.';
$string['form_check_event_state_switched_no'] = 'Нет';
$string['form_check_event_state_switched_yes'] = 'Да';
$string['form_check_event_state_switched_label'] = 'Проверять, если другой элемент курса сменил свое состояние';
$string['form_check_event_state_switched_help'] = 'Описание';
$string['form_check_event_state_switched_help_help'] = 'Повышение актуальности данных за счет проверки состояния при каждой смене статуса другого аналогичного модуля в курсе. Возможна сильная нагрузка на сервер.';
$string['form_check_event_course_viewed_no'] = 'Нет';
$string['form_check_event_course_viewed_yes'] = 'Да';
$string['form_check_event_course_viewed_label'] = 'Проверка состояния при входе пользователя в курс';
$string['form_check_event_course_viewed_help'] = 'Описание';
$string['form_check_event_course_viewed_help_help'] = 'Повышение актуальности данных за счет проверки состояния при каждом просмотре курса пользователем. Возможна сильная нагрузка на сервер.';
$string['form_header_activity_display'] = 'Отображение модуля';
$string['form_name_label'] = 'Название';
$string['form_display_to_students_no'] = 'Нет';
$string['form_display_to_students_yes'] = 'Да';
$string['form_display_to_students_label'] = 'Скрывать элемент курса от учеников';
$string['form_display_to_students_help'] = 'Описание';
$string['form_display_to_students_help_help'] = 'Если элемент отображается в курсе, студент может увидеть состояние по отношению к себе. Преподаватели всегда видят текущий элемент с данными по всем студентам в курсе.';
$string['form_delivery_redirect_message_label'] = 'Сообщение при переходе из уведомления';
$string['form_delivery_redirect_message_help'] = 'Описание';
$string['form_delivery_redirect_message_help_help'] = 'Сообщение, которое будет отображаться пользователям при переходе из уведомлений. Данный элемент курса имеет возможность рассылать уведомления пользователям, в которые можно добавить ссылку для перехода в систему.';
$string['form_delivery_redirect_url_label'] = 'Ссылка для перехода из уведомлений';
$string['form_delivery_redirect_url_help'] = 'Описание';
$string['form_delivery_redirect_url_help_help'] = 'Возможность переопределелить стандартную ссылку, которую можно добавить в уведомления, рассылаемые данным элементом курса. Стандартная ссылка ведет на страницу просмотра курса.';
$string['form_completionstate_enabled_label'] = 'Включить условие';
$string['form_completionstate_active'] = 'Активен';
$string['form_completionstate_notactive'] = 'Не активен';
$string['form_completionstate_label'] = '';
$string['form_completionstategroup_label'] = 'Элемент курса переходит в состояние';
$string['form_header_grading'] = 'Оценивание';
$string['form_grading_enabled'] = 'Оценивание включено';

// Отображение в курсе
$string['shortuserstate'] = 'Состояние элемента: {$a}';
$string['shortuserstate_active'] = 'Активен';
$string['shortuserstate_preactive'] = 'Активация будет выполнена после {$a}';
$string['shortuserstate_notactive'] = 'Не активен';
$string['shortuserstate_notset'] = '';

// Тема письма для уведомлений
$string['otcourselogic_email_subject'] = 'Уведомление из курса';

// Просмотра элемента курса
$string['return_to_course'] = 'Вернуться в курс';
$string['caption_username'] = 'Пользователь';
$string['caption_roles_all'] = 'Роли';
$string['caption_groups'] = 'Группы';
$string['caption_state_element'] = 'Состояние элемента курса';
$string['caption_last_change_state'] = 'Дата последнего изменения состояния элемента курса';

// Ошибки
$string['invalid_instance_id'] = 'Передан неверный instanceid в метод otcourselogic_check_user_state';
$string['invalid_course_id'] = 'Передан неверный courseid в метод otcourselogic_check_user_state';
$string['invalid_user_id'] = 'Передан неверный userid в метод otcourselogic_check_user_state';

// Макроподстановки
$string['macro_write_profile_field'] = '';
$string['macro_write_profile_field_help'] = '
Укажите текст, который будет записан в профиля пользователя. Вы можете также использовать следующие доступные макроподстановки:<br>
{CURRENTDATE} - Текущая дата в формате d-m-Y H:i:s<br>
{STUDENTFULLNAME} - ФИО студента;<br>
{STUDENTPROFILELINK} - Ссылка на профиль студента;<br>
{COURSEFULLNAME} - полное название курса;<br>
{COURSELINK} - Ссылка на курс;<br>
{MODULEPAGE} - Ссылка на страницу текущего элемента с сообщением;<br>
{MODULENAME} - Текущее название элемента курса.<br>
<i>Дополнительные макроподстановки данных студента:</i><br>
{USERNAME} - Логин;<br>
{FIRSTNAME} - Имя;<br>
{LASTNAME} - Фамилия;<br>
{EMAIL} - Адрес электронной почты;<br>
{CITY} - Город;<br>
{COUNTRY} - Страна;<br>
{LANG} - Предпочитаемый язык;<br>
{DESCRIPTION} - Описание;<br>
{URL} - Веб-страница;<br>
{IDNUMBER} - Индивидуальный номер;<br>
{INSTITUTION} - Учреждение (организация);<br>
{DEPARTMENT} - Отдел;<br>
{PHONE1} - Телефон;<br>
{PHONE2} - Мобильный телефон;<br>
{ADDRESS} - Адрес;<br>
{FIRSTNAMEPHONETIC} - Имя - фонетическая запись;<br>
{LASTNAMEPHONETIC} - Фамилия - фонетическая запись;<br>
{MIDDLENAME} - Отчество или второе имя;<br>
{ALTERNATENAME} - Альтернативное имя;<br>
{PROFILE_FIELD_XXXX} - Кастомное поле профиля, где XXXX - краткое имя поля.';

$string['macro_send_message'] = '';
$string['macro_send_message_help'] = '
Введите текст сообщения, который будет высылаться пользователям. Используется для рассылок в системах, ограничивающих длину сообщений(SMS и др.)<br>
Доступные макроподстановки:<br>
{CURRENTDATE} - Текущая дата в формате d-m-Y H:i:s<br>
{STUDENTFULLNAME} - ФИО студента;<br>
{STUDENTPROFILELINK} - Ссылка на профиль студента;<br>
{COURSEFULLNAME} - полное название курса;<br>
{COURSELINK} - Ссылка на курс;<br>
{MODULEPAGE} - Ссылка на страницу текущего элемента с сообщением;<br>
{MODULENAME} - Текущее название элемента курса.<br>
<i>Дополнительные макроподстановки данных студента:</i><br>
{USERNAME} - Логин;<br>
{FIRSTNAME} - Имя;<br>
{LASTNAME} - Фамилия;<br>
{EMAIL} - Адрес электронной почты;<br>
{CITY} - Город;<br>
{COUNTRY} - Страна;<br>
{LANG} - Предпочитаемый язык;<br>
{DESCRIPTION} - Описание;<br>
{URL} - Веб-страница;<br>
{IDNUMBER} - Индивидуальный номер;<br>
{INSTITUTION} - Учреждение (организация);<br>
{DEPARTMENT} - Отдел;<br>
{PHONE1} - Телефон;<br>
{PHONE2} - Мобильный телефон;<br>
{ADDRESS} - Адрес;<br>
{FIRSTNAMEPHONETIC} - Имя - фонетическая запись;<br>
{LASTNAMEPHONETIC} - Фамилия - фонетическая запись;<br>
{MIDDLENAME} - Отчество или второе имя;<br>
{ALTERNATENAME} - Альтернативное имя;<br>
{PROFILE_FIELD_XXXX} - Кастомное поле профиля, где XXXX - краткое имя поля.';

$string['admin_panel'] = 'Управление действиями';

// Общие строки
$string['admin'] = 'Администратор';
$string['student'] = $string['sender'] = 'Студент';
$string['teacher'] = 'Преподаватель';
$string['curator'] = 'Куратор';
$string['save'] = 'Сохранить';
$string['seconds'] = 'Интервал: {$a}';
$string['lang'] = 'Язык';

$string['choose_action'] = 'Выберите действие...';
$string['form_header_additional'] = 'Дополнительные настройки';
$string['availability_empty'] = 'Обратите внимание, что у текущего модуля не установлено ограничение доступа!';
$string['reset_states'] = 'Пересчитать состояния';

// Опции
$string['mode_onetime'] = 'Срабатывает: 1 раз';
$string['mode_periodic'] = 'Срабатывает: периодично';

$string['mode_onetime_short'] = '1 раз';
$string['mode_periodic_short'] = 'Периодично';

$string['mode_on'] = 'Статус: включен';
$string['mode_off'] = 'Статус: выключен';
$string['is_disabled'] = 'Выключен';
$string['delay'] = 'Отсрочка исполнения: {$a}';

$string['actions_generated_fields'] = 'Формируемые поля';

// Логи
$string['log_type'] = 'Тип';
$string['log_objectid'] = 'Идентификатор объекта';
$string['log_timecreated'] = 'Дата выполнения';
$string['log_comment'] = 'Информация';
$string['log_executionstatus'] = 'Статус';

$string['log_info_execute_processor'] = 'Исполнение обработчика';
$string['return'] = 'Вернуться назад';
$string['processor'] = 'Группа действий';

$string['success'] = 'Успешно';
$string['fail'] = 'Неуспешно';

$string['timemodifiedheader'] = 'Изменено';

// Задачи
$string['action_send_message'] = $string['mod_otcourselogic_apanel_actions_send_message_send_message'] = 'Отправить сообщение';
$string['action_write_profile_field'] = $string['mod_otcourselogic_apanel_actions_write_profile_field_write_profile_field'] = 'Записать в поле профиля';
$string['action_enrol_to_course'] = $string['mod_otcourselogic_apanel_actions_enrol_to_course_enrol_to_course'] = 'Записать на курс';
$string['action_unenrol_from_course'] = $string['mod_otcourselogic_apanel_actions_unenrol_from_course_unenrol_from_course'] = 'Отписать от текущего курса';

// События
$string['otcourselogic_activate'] = 'Условие: логика курса активна';
$string['otcourselogic_deactivate'] = 'Условие: логика курса неактивна';

$string['form_protect_label'] = 'Защита от случайных срабатываний';
$string['form_protect_label_help'] = 'Логика курса не сработает, пока не будет добавлено хотя бы одно ограничение доступа';

// Форма обработчика
$string['form_processor_main_header'] = 'Настройки обработчика';
$string['form_processor_status'] = 'Включить';
$string['form_processor_is_periodic'] = 'Режим периодичности';
$string['form_processor_periodic'] = 'Интервал';
$string['form_processor_otcourselogic_activate'] = 'Логика курса активна';
$string['form_processor_otcourselogic_deactivate'] = 'Логика курса неактивна';
$string['form_processor_depend_activate'] = 'Условие';
$string['form_processor_type'] = 'Срабатывает';
$string['form_processor_delay'] = 'Отсрочка активации';
$string['form_processor_delay_help'] = 'Данная настройка позволяет отложить момент срабатывания действий обработчика';

$string['form_action_main_header'] = 'Опции задач';
$string['form_action_main_header_action'] = 'Настройки задачи';
$string['form_action_type'] = 'Выберите задачу';
$string['form_action_show_action'] = 'Загрузить настройки задачи';

// Экшн отправки сообщения
$string['action_send_message_active'] = 'Включить';
$string['action_send_message_recipient'] = 'Получатель';
$string['action_send_message_fullmessage'] = 'Текст уведомления';
$string['action_send_message_shortmessage'] = 'Краткое уведомление';
$string['action_send_message_sender'] = 'Отправлять от имени';
$string['action_send_message_sender_user'] = 'Выберите отправителя';

// Экшн записи в поле профиля
$string['action_write_profile_field_active'] = 'Включить';
$string['action_write_profile_field_active_field'] = 'Поле профиля';
$string['action_write_profile_field_active_field_name'] = 'Запись в поле профиля';
$string['action_write_profile_field_active_field_text_short'] = 'Шаблон';
$string['action_write_profile_field_active_field_text'] = 'Шаблон макроподстановки';

// Экшн записи на курс
$string['action_enrol_to_course_active'] = 'Включить';
$string['action_enrol_to_course_role'] = 'Роль';
$string['action_enrol_to_course_course'] = 'Курс';
$string['action_enrol_to_course_reenrol'] = 'Перезапись на курс';
$string['action_enrol_to_course_reenrol_help'] = 'Если у пользователя имеется подписка на курс, то он будет отписан и записан по новой.';
$string['action_enrol_to_course_recover'] = 'Восстановить оценки';
$string['action_enrol_to_course_recover_help'] = 'Оценки по выбранному курсу будут восстановлены для пользователя.';
$string['action_enrol_to_course_clear'] = 'Очистить модули курса';
$string['action_enrol_to_course_clear_help'] = 'Все попытки в элементах тест и задание будут очищены для пользователя.';
$string['action_enrol_to_course_empty_course'] = 'Выберите курс!';
$string['action_enrol_to_course_invalid_role'] = 'Выбрана неверная роль! Попробуйте еще раз';

// Экшн отписки от текущего курса
$string['action_unenrol_from_course_active'] = 'Включить';

