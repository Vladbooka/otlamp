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
 * Языковые строки
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Общие языковые строки
 */
$string['pluginname'] = 'Панель обработки прецедентов';
$string['yes'] = 'Да';
$string['no'] = 'Нет';

/**
 * Предупреждения и ошибки
 */
$string['script_conflict'] = '<b>Внимание!</b><br>В системе активны два сценария реализующие назначение/снятие ролей "Снятие назначенных ролей" и "Назначение или снятие роли пользователям согласно критериям".
                               Возможна ситуация при которой сценарий "Снятие назначенных ролей" будет снимать роль у пользователя, а "Назначение или снятие роли пользователям согласно критериям" заново назначать ее,
                                это не приведет к ошибкам но создаст излишнюю нагрузку на систему.';

/**
 * Права
 */
$string['pprocessing:receive_notifications'] = 'Право получать уведомления';
$string['messageprovider:notifications'] = 'Уведомления о прецедентах';
$string['messageprovider:service_messages'] = 'Панель обработки прецедентов: Служебные уведомления';

/**
 * Таски
 */
$string['task_asap'] = 'Обработка при каждом запуске';
$string['task_hourly'] = 'Ежечасная обработка прецедентов';
$string['task_daily'] = 'Ежедневная обработка прецедентов';
$string['task_weekly'] = 'Еженедельная обработка прецедентов';
$string['task_monthly'] = 'Ежемесячная обработка прецедентов';

$string['event_hourly'] = 'Ежечасная обработка прецедентов';
$string['event_daily'] = 'Ежедневная обработка прецедентов';
$string['event_weekly'] = 'Еженедельная обработка прецедентов';
$string['event_monthly'] = 'Ежемесячная обработка прецедентов';
$string['event_iteration_initialized'] = 'Новая итерация на основе событий была инициализирована';

/**
 * Заголовки сценариев
 */
$string['spelling_mistake_header'] = 'Уведомление об орфографической ошибке';
$string['student_enrolled_header'] = 'Уведомление слушателя о подписке на курс';
$string['teacher_enrolled_header'] = 'Уведомление преподавателя о подписке на курс с правом оценивать других пользователей';
$string['user_registered_recently__header'] = 'Уведомление ни разу не авторизовавшегося пользователя о недавней регистрации';
$string['user_registered_long_ago__header'] = 'Уведомление ни разу не авторизовавшегося пользователя о регистрации, с момента которой прошло почти два месяца';
$string['user_registered_long_ago_deleting__header'] = 'Удаление ни разу не авторизовавшихся пользователей, с момента регистрации которых прошло два месяца';
$string['role_unassign__header'] = 'Снятие назначенных ролей';
$string['send_user_password__header'] = 'Отправка уведомлений с паролем пользователям, загруженным в систему';
$string['sync_user_cohorts__header'] = 'Синхронизация пользователей с глобальными группами';
$string['sync_user_cohorts_task__header'] = 'Синхронизация пользователей с глобальными группами по расписанию';
$string['send_user_db_password__header'] = 'Сохранение и отправка паролей из внешней базы данных пользователям, загруженным в систему';
$string['export_grades_header'] = 'Выгрузка оценок во внешнюю базу данных';
$string['export_grades_header_desc'] = '';
$string['empty_connections_export_grades_header'] = 'Выгрузка оценок во внешнюю базу данных';
$string['empty_connections_export_grades_header_desc'] = 'Для настройки сценария необходимо <a href="/local/opentechnology/dbconnection_management.php">создать подключение к внешней базе данных</a>';
$string['export_grades_schedule_header'] = 'Выгрузка существующих оценок во внешнюю базу данных по расписанию';
$string['export_grades_schedule_header_desc'] = 'Сценарий предназначен для выгрузки существующих в системе оценок (тех оценок, события о выставлении которых произошли до активации сценария "Выгрузка оценок во внешнюю базу данных"). Режим работы сценария регулируется настройками сценария "Выгрузка оценок во внешнюю базу данных".';
$string['empty_connections_export_grades_schedule_header'] = 'Выгрузка существующих оценок во внешнюю базу данных по расписанию';
$string['empty_connections_export_grades_schedule_header_desc'] = 'Для настройки сценария необходимо <a href="/local/opentechnology/dbconnection_management.php">создать подключение к внешней базе данных</a>';

/**
 * Заголовки активации сценариев
 */
$string['action_status'] = 'Включить выполнение действия';
$string['role_unassign_status'] = 'Включить снятие ролей';
$string['send_user_password_status'] = 'Включить отправку уведомлений с паролем';
$string['sync_user_cohorts_status'] = 'Включить синхронизацию';
$string['sync_user_cohorts_task_status'] = 'Включить синхронизацию по расписанию';
$string['send_user_password_status_desc'] = 'Для работы сценария необходимо <a href="/admin/tool/task/scheduledtasks.php?action=edit&task=core%5Ctask%5Csend_new_user_passwords_task">отключить</a> выполнение задачи Рассылка паролей новым пользователям (\core\task\send_new_user_passwords_task)';
$string['send_user_db_password_status'] = 'Включить сохранение паролей из внешней базы данных';
$string['send_user_db_password_status_desc'] = 'Для работы сценария необходимо <a href="/admin/tool/task/scheduledtasks.php?action=edit&task=core%5Ctask%5Csend_new_user_passwords_task">отключить</a> выполнение задачи Рассылка паролей новым пользователям (\core\task\send_new_user_passwords_task).
                                                Пароль во внешней базе данных должен быть сохранен в открытом виде.
                                                Для получения пароля будут использованы настройки метода аутентификации <a href="/admin/settings.php?section=authsettingdb">Внешняя база данных</a>';
$string['settings_export_grades_schedule_status'] = 'Включить выгрузку существующих оценок по расписанию';
$string['settings_export_grades_schedule_status_desc'] = '';

/**
 * Дополнительные настройки сценариев
 */
$string['settings_recievers'] = 'Выберите получателей уведомлений';
$string['settings_recievers_desc'] = 'Если поле будет пустым, уведомления будут получать все администраторы сайта. Получатели отбираются по праву local/pprocessing:receive_notifications в контексте главной страницы. Настройка включения отправки орфографических ошибок находится в Администрирование -> Внешний вид -> Темы -> СЭО 3KL -> Профиль (выбираете свой профиль) -> Общие настройки.
                                     <br>Чтобы выбрать канал, через который будут отправляться сообщения об ошибках (email, sms, внутренние уведомления), требуется изменить настройку "Панель обработки прецедентов" -> "Уведомления о прецедентах" в настройках уведомлений пользователя, или изменить глобальные настройки уведомлений в "Администрирование" -> "Плагины" -> "Способы доставки сообщений" -> "Настройки значений по умолчанию для способов доставки сообщений"';
$string['message_subject'] = 'Заголовок уведомления';
$string['message_full'] = 'Полный текст уведомления';
$string['message_short'] = 'Короткий текст уведомления';
$string['message_status'] = 'Включить отправку уведомления';
$string['settings_role_unassign_context'] = 'Уровень контекста поиска назначений ролей';
$string['settings_role_unassign_context_desc'] = 'Снятие назначений будет происходить только в выбранном уровне контексте';
$string['settings_role_unassign_role'] = 'Назначенная роль';
$string['settings_role_unassign_role_desc'] = 'Снятие назначений будет происходить только с указанной роли';
$string['settings_role_unassign_context_none'] = 'Выберите уровень контекста...';
$string['settings_role_unassign_context_system'] = 'Система';
$string['settings_role_unassign_context_coursecat'] = 'Категория';
$string['settings_role_unassign_context_course'] = 'Курс';
$string['settings_role_unassign_context_module'] = 'Модуль';
$string['settings_role_unassign_context_user'] = 'Пользователь';
$string['settings_role_unassign_context_block'] = 'Блок';
$string['settings_role_unassign_role_none'] = 'Выберите роль...';

$string['setting_send_user_password_message_subject'] = 'Заголовок уведомления';
$string['setting_send_user_password_message_full'] = 'Полный текст уведомления';
$string['setting_send_user_password_message_short'] = 'Короткий текст уведомления';
$string['newusernewpassword_message_full'] = 'Здравствуйте, %{user.fullname}!

                                              На сайте «%{site.fullname}» для Вас была создана новая учетная запись с временным паролем.

                                              Сейчас Вы можете зайти на сайт так:
                                              Логин: %{user.username}
                                              Пароль: %{generated_code}
                                              (Вам придется сменить пароль при первом входе).

                                              Чтобы начать использование сайта «%{site.fullname}», пройдите по адресу %{site.loginurl}

                                              В большинстве почтовых программ этот адрес должен выглядеть как синяя ссылка, на которую достаточно нажать. Если это не так, просто скопируйте этот адрес и вставьте его в строку адреса в верхней части окна Вашего браузера.

                                              С уважением, администратор сайта «%{site.fullname}», %{site.signoff}';
$string['newusernewpassword_message_subject'] = 'Новая учетная запись';
$string['newusernewpassword_message_short'] = 'Логин: %{user.username}, пароль: %{generated_code}';
$string['settings_send_user_password_auth_forcepasswordchange'] = 'Принудительная смена пароля';
$string['settings_send_user_password_auth_forcepasswordchange_desc'] = 'Если поставить отметку, то пользователю будет предложено изменить пароль при следующем входе в систему';

$string['setting_send_user_db_password_message_subject'] = 'Заголовок уведомления';
$string['setting_send_user_db_password_message_full'] = 'Полный текст уведомления';
$string['setting_send_user_db_password_message_short'] = 'Короткий текст уведомления';
$string['newusernewdbpassword_message_full'] = 'Здравствуйте, %{user.fullname}!

                                              На сайте «%{site.fullname}» для Вас была создана новая учетная запись с временным паролем.

                                              Сейчас Вы можете зайти на сайт так:
                                              Логин: %{user.username}
                                              Пароль: %{extdbpassworld}
                                              (Вам придется сменить пароль при первом входе).

                                              Чтобы начать использование сайта «%{site.fullname}», пройдите по адресу %{site.loginurl}

                                              В большинстве почтовых программ этот адрес должен выглядеть как синяя ссылка, на которую достаточно нажать. Если это не так, просто скопируйте этот адрес и вставьте его в строку адреса в верхней части окна Вашего браузера.

                                              С уважением, администратор сайта «%{site.fullname}», %{site.signoff}';
$string['newusernewdbpassword_message_subject'] = 'Новая учетная запись';
$string['newusernewdbpassword_message_short'] = 'Логин: %{user.username}, пароль: %{extdbpassworld}';
$string['settings_send_user_db_password_auth_forcepasswordchange'] = 'Принудительная смена пароля';
$string['settings_send_user_db_password_auth_forcepasswordchange_desc'] = 'Если поставить отметку, то пользователю будет предложено изменить пароль при следующем входе в систему';
$string['settings_send_user_db_password_macro_write'] = 'Укажите текст, который будет отправлен пользователю. Вы можете также использовать следующие доступные макроподстановки:<br>
                                            %{extdbpassworld} - Пароль из внешней базы данных;<br>
                                            %{site.fullname} - Полное имя сайта;<br>
                                            %{site.shortname} - Короткое имя сайта;<br>
                                            %{site.summary} - Описание сайта;<br>
                                            %{site.loginurl} - Ссылка на страницу авторизации;<br>
                                            %{site.url} - Ссылка на сайт;<br>
                                            %{site.signoff} - Администратор сайта;<br>
                                            <i>Дополнительные макроподстановки данных пользователя:</i><br>
                                            %{user.fullname} - ФИО пользователя;<br>
                                            %{user.username} - Логин;<br>
                                            %{user.firstname} - Имя;<br>
                                            %{user.lastname} - Фамилия;<br>
                                            %{user.email} - Адрес электронной почты;<br>
                                            %{user.city} - Город;<br>
                                            %{user.country} - Страна;<br>
                                            %{user.lang} - Предпочитаемый язык;<br>
                                            %{user.description} - Описание;<br>
                                            %{user.url} - Веб-страница;<br>
                                            %{user.idnumber} - Индивидуальный номер;<br>
                                            %{user.institution} - Учреждение (организация);<br>
                                            %{user.department} - Отдел;<br>
                                            %{user.phone1} - Телефон;<br>
                                            %{user.phone2} - Мобильный телефон;<br>
                                            %{user.address} - Адрес;';
$string['send_user_db_password_send_message'] = 'Отправка сообщения пользователю';
$string['send_user_db_password_send_message_desc'] = 'Если опция активна, пользователю будет отправлено нижеуказанное уведомление';

$string['settings_password_type'] = 'Тип пароля во внешней базе данных';
$string['settings_password_type_desc'] = 'Данный сценарий поддерживает два варианта пароля:<br>
        1. Текстовый - пароль хранится в открытом виде и может быть отправлен пользователю.<br>
        2. MD5 - хеш от пароля хранится в формате MD5, пользователю можно отправить уведомление о создании учетной записи без пароля.';
$string['pass_plaintext'] = 'Текстовый';
$string['pass_md5'] = 'Хеш MD5';

$string['choose_cohorts_field'] = 'Выберите поле профиля...';
$string['settings_user_cohorts'] = 'Выберите поле профиля, в котором указан список глобальных групп пользователя';
$string['settings_user_cohorts_desc'] = '';
$string['cohortid'] = 'ID';
$string['cohortname'] = 'Имя группы';
$string['cohortidnumber'] = 'Идентификатор группы';
$string['settings_cohort_identifier'] = 'Идентификатором группы следует считать';
$string['settings_cohort_identifier_desc'] = 'Выберите поле для идентификации группы, которое будет испльзоваться при поиске групп во время синхронизации';
$string['settings_cohorts_manage_mode'] = 'Ручное управление глобальными группами';
$string['settings_cohorts_manage_mode_desc'] = 'Настройка позволяет задать реакцию системы на редактирование состава глобальных групп вручную. Доступны 2 сценария:
                                                <br> - система позволяет добавлять/исключать пользователей вручную в группы, не указанные в поле профиля
                                                <br> - система запрещает добавлять/исключать пользователя вручную в любые группы';
$string['cohortsmanagemodes_enable'] = 'Разрешено';
$string['cohortsmanagemodes_disable'] = 'Запрещено';
$string['setting_sync_user_cohorts_task_desc'] = 'Сценарий записывает пользователей в глобальные группы по расписанию, согласно настройкам, указанным в основном сценарии "Синхронизация пользователей с глобальными группами".
                                                 <br/>Для работы синхронизации пользователей по расписанию требуется, чтобы основной сценарий был настроен и активен.
                                                 <br/>Если данный сценарий выключен, пользователь будет добавляться в группы при изменении профиля пользователя согласно основному сценарию.';
$string['settings_send_user_password_additional_password_settings'] = 'Дополнительные настройки пароля';
$string['settings_send_user_password_additional_password_settings_desc'] = 'Позволяет задать настройки пароля отличные от политики паролей в Moodle';
$string['settings_send_user_password_p_maxlen'] = 'Длина пароля';
$string['settings_send_user_password_p_maxlen_desc'] = 'Не рекомендуется устанавливать длину пароля менее 4 символов';
$string['settings_send_user_password_p_numnumbers'] = 'Числовые символы';
$string['settings_send_user_password_p_numnumbers_desc'] = 'Количество числовых символов в пароле';
$string['settings_send_user_password_p_numsymbols'] = 'Символы';
$string['settings_send_user_password_p_numsymbols_desc'] = 'Количество символов (&, #, ÷, *...) в пароле';
$string['settings_send_user_password_p_lowerletters'] = 'Буквы в нижнем регистре';
$string['settings_send_user_password_p_lowerletters_desc'] = 'Количество букв нижнего регистра в пароле';
$string['settings_send_user_password_p_upperletters'] = 'Буквы в верхнем регистре';
$string['settings_send_user_password_p_upperletters_desc'] = 'Количество букв верхнего регистра в пароле';

$string['settings_unenrol_cohorts_by_date_header'] = 'Удаление подписок типа "Синхронизация с глобальной группой" по дате из настраиваемых полей глобальной группы';
$string['settings_unenrol_cohorts_by_date_header_desc'] = '';
$string['settings_empty_cohort_config_unenrol_cohorts_by_date_header_desc'] = 'Конфигурация кастомных полей, необходимых для работы сценария не настроена. Для включения сценария настройте конфигурацию кастомных полей cohort_yaml в плагине <a href="/admin/settings.php?section=mcov_settings">Настраиваемые поля</a>';
$string['settings_unenrol_cohorts_by_date_status'] = 'Включить сценарий удаления подписок';
$string['settings_unenrol_cohorts_by_date_status_desc'] = '';
$string['settings_unenrol_cohorts_by_date_unenroldate'] = 'Настраиваемое поле, в котором хранится дата отписки глобальной группы';
$string['settings_unenrol_cohorts_by_date_unenroldate_desc'] = '';

$string['settings_delete_cohorts_by_date_header'] = 'Удаление глобальных групп по дате из настраиваемых полей глобальной группы';
$string['settings_delete_cohorts_by_date_header_desc'] = '';
$string['settings_empty_cohort_config_delete_cohorts_by_date_header_desc'] = 'Конфигурация кастомных полей, необходимых для работы сценария не настроена. Для включения сценария настройте конфигурацию кастомных полей cohort_yaml в плагине <a href="/admin/settings.php?section=mcov_settings">Настраиваемые поля</a>';
$string['settings_delete_cohorts_by_date_status'] = 'Включить сценарий удаления глобальных групп';
$string['settings_delete_cohorts_by_date_status_desc'] = '';
$string['settings_delete_cohorts_by_date_deldate'] = 'Настраиваемое поле, в котором хранится дата для удаления глобальной группы';
$string['settings_delete_cohorts_by_date_deldate_desc'] = '';

$string['settings_delete_quiz_attempts_by_date_header'] = 'Удаление завершенных попыток тестирования старше заданной даты';
$string['settings_delete_quiz_attempts_by_date_header_desc'] = '';
$string['settings_delete_quiz_attempts_by_date_status'] = 'Включить удаление завершенных попыток тестирования';
$string['settings_delete_quiz_attempts_by_date_status_desc'] = 'После включения работы сценария все неуспешные завершенные попытки тестирования, с момента завершения которых прошло больше времени, чем указано в настройке сценария, будут удалены.
                                                                <br/>Успешность будет определяться в соответствии с настройками теста:
                                                                <ul><li>при выставленном методе оценивания "Первая попытка" успешной попыткой будет считаться первая попытка тестирования, все остальные завершенные попытки, с момента завершения которых прошло больше времени, чем указано в настройках сценария, будут удалены
                                                                    <li>при выставленном методе оценивания "Последняя попытка" успешной попыткой будет считаться последняя попытка тестирования, все остальные завершенные попытки, с момента завершения которых прошло больше времени, чем указано в настройках сценария, будут удалены
                                                                    <li>при выставленном методе оценивания "Средняя оценка" ни одна из попыток не будет удалена
                                                                    <li>при выставленном методе оценивания "Высшая оценка" успешной попыткой будет считаться первая попытка тестирования с максимальным баллом среди всех попыток, все остальные завершенные попытки, с момента завершения которых прошло больше времени, чем указано в настройках сценария, будут удалены
                                                                </ul>Работа сценария по умолчанию выполняется раз в день. Удаление попыток тестирования может влияет на итоговую оценку за тест и курс (в засимости от настроек в системе).';
$string['settings_delete_quiz_attempts_by_date_relativedate'] = 'С момента завершения попытки должно пройти больше';
$string['settings_delete_quiz_attempts_by_date_relativedate_desc'] = '';

$string['settings_export_grades_connection'] = 'Соединение с базой данных';
$string['settings_export_grades_connection_desc'] = 'Для работы сценария необходимо выбрать подключение к базе данных, куда будут выгружаться оценки. Создать подключение к базе данных можно на странице <a href="/local/opentechnology/dbconnection_management.php">Управление подключениями к внешним БД</a>';
$string['settings_export_grades_table'] = 'Имя таблицы';
$string['settings_export_grades_table_desc'] = 'Укажите имя таблицы во внешней базе данных, куда будут выгружаться оценки';
$string['settings_export_grades_llh_courseid'] = 'Идентификатор курса';
$string['settings_export_grades_llh_courseid_desc'] = '';
$string['settings_export_grades_llh_coursefullname'] = 'Полное имя курса';
$string['settings_export_grades_llh_coursefullname_desc'] = '';
$string['settings_export_grades_llh_courseshortname'] = 'Краткое имя курса';
$string['settings_export_grades_llh_courseshortname_desc'] = '';
$string['settings_export_grades_llh_finalgrade'] = 'Оценка за курс';
$string['settings_export_grades_llh_finalgrade_desc'] = '';
$string['settings_export_grades_llh_lastupdate'] = 'Дата оценки за курс';
$string['settings_export_grades_llh_lastupdate_desc'] = '';
$string['settings_export_grades_llhcm_cmid'] = 'Идентификатор модуля курса';
$string['settings_export_grades_llhcm_cmid_desc'] = '';
$string['settings_export_grades_llhm_modname'] = 'Краткое имя модуля курса';
$string['settings_export_grades_llhm_modname_desc'] = '';
$string['settings_export_grades_llhm_name'] = 'Полное имя модуля курса';
$string['settings_export_grades_llhm_name_desc'] = '';
$string['settings_export_grades_llh_userid'] = 'Идентификатор пользователя';
$string['settings_export_grades_llh_userid_desc'] = '';
$string['settings_export_grades_llhcm_finalgrade'] = 'Оценка за модуль';
$string['settings_export_grades_llhcm_finalgrade_desc'] = '';
$string['settings_export_grades_llhcm_timemodified'] = 'Дата оценки за модуль';
$string['settings_export_grades_llhcm_timemodified_desc'] = '';
$string['settings_export_grades_llh_activetime'] = 'Время изучения курса';
$string['settings_export_grades_llh_activetime_desc'] = '';
$string['settings_export_grades_llhcm_activetime'] = 'Время изучения модуля';
$string['settings_export_grades_llhcm_activetime_desc'] = '';
$string['settings_export_grades_data_mapping'] = 'Сопоставление данных ({$a})';
$string['settings_export_grades_data_mapping_desc'] = '';
$string['settings_export_grades_user_fullname'] = 'ФИО пользователя';
$string['settings_export_grades_user_fullname_desc'] = '';
$string['do_not_send'] = 'Не выгружать';
$string['settings_export_grades_primarykey1'] = 'Поле идентификатора пользователя';
$string['settings_export_grades_primarykey1_desc'] = 'Поле во внешней базе данных, в котором ожидается хранение идентификатора пользователя';
$string['settings_export_grades_foreignkey1'] = 'Идентификатор пользователя';
$string['settings_export_grades_foreignkey1_desc'] = 'Поле профиля пользователя, которое нужно использовать в качестве идентификатора пользователя для поиска записей во внешней базе данных';
$string['settings_export_grades_primarykey2'] = 'Поле идентификатора модуля курса';
$string['settings_export_grades_primarykey2_desc'] = 'Поле во внешней базе данных, в котором ожидается хранение идентификатора модуля курса';
$string['settings_export_grades_foreignkey2'] = 'Идентификатор модуля курса';
$string['settings_export_grades_foreignkey2_desc'] = 'Поле хранилища истории обучения, которое нужно использовать в качестве идентификатора модуля курса для поиска записей во внешней базе данных';
$string['settings_export_grades_primarykey3'] = 'Поле идентификатора курса';
$string['settings_export_grades_primarykey3_desc'] = 'Поле во внешней базе данных, в котором ожидается хранение идентификатора курса';
$string['settings_export_grades_foreignkey3'] = 'Идентификатор курса';
$string['settings_export_grades_foreignkey3_desc'] = 'Поле хранилища истории обучения, которое нужно использовать в качестве идентификатора курса для поиска записей во внешней базе данных';
$string['llhcm_cmid'] = 'Идентификатор модуля курса';
$string['llh_courseid'] = 'Идентификатор курса';
$string['settings_export_grades_grade_format'] = 'Формат оценки для экспорта';
$string['settings_export_grades_grade_format_desc'] = 'Укажите в каком формате необходимо выгружать оценки из СДО';
$string['settings_export_grades_status'] = 'Включить экспорт оценок во внешнюю базу данных';
$string['settings_export_grades_status_desc'] = '';
$string['settings_export_grades_date_format'] = 'Формат времени выставления оценки';
$string['settings_export_grades_date_format_desc'] = '';
$string['dateformat_timestamp'] = 'timestamp';
$string['dateformat_date'] = 'date (YYYY-MM-DD)';
$string['dateformat_datetime'] = 'datetime (YYYY-MM-DD HH:MM:SS)';
$string['settings_export_grades_grade_itemtype'] = 'Какие оценки выгружать?';
$string['settings_export_grades_grade_itemtype_desc'] = '';
$string['gradeitemtype_mod'] = 'За элементы курсов';
$string['gradeitemtype_course'] = 'За курсы';
$string['gradeitemtype_all'] = 'Все оценки';
$string['settings_export_grades_grade_itemmodule'] = 'За какие модули выгружать оценки?';
$string['settings_export_grades_grade_itemmodule_desc'] = '';
$string['gradeitemmodule_all'] = 'За все модули';
$string['gradeitemmodule_quiz'] = 'Только за тесты';
$string['do_not_relate'] = 'Не связывать поле';
$string['settings_export_grades_description_composite_keys'] = 'Идентификация записей во внешней базе данных при обновлении оценок осуществляется по составному ключу вида "Идентификатор пользователя + Идентификатор модуля курса" (оценки за модули курса) или "Идентификатор пользователя + Идентификатор курса" (оценки за курсы). Для выгрузки и обновления оценок должны быть настроены соответствующие составные ключи (для оценок за модули курса, для оценок за курсы или оба для выгрузки всех оценок). С помощью настроек ниже вы можете настроить формирование нужных составных ключей.';
$string['settings_export_grades_description_mapping_fields'] = 'С помощью ниже указанных настроек можно указать, какие данные пользователя и его истории обучения в какие поля внешней базы данных нужно сохранять. Для работы сценария сохранение составных ключей является обязательным условием.';

/**
 * Общие настройки
 */
$string['common_settings_header'] = 'Общие настройки';
$string['disable_logging'] = 'Отключить логирование процесса выполнения сценариев';
$string['disable_logging_desc'] = '';

/**
 * mtrace
 */
$string['quiz_mtrace'] = 'quiz processed (id = {$a->id}, cmid = {$a->cmid}) at {$a->mtracetime}';
$string['user_mtrace'] = 'user processed (id = {$a->id}) at {$a->mtracetime}';
$string['attempt_mtrace'] = 'attempt processed (id = {$a->id}, quiz = {$a->quiz}) at {$a->mtracetime}';

// Массовое назначение ролей пользователям
$string['settings_assign_role_according_criteria_header'] = 'Назначение или снятие роли пользователям согласно критериям';
$string['settings_assign_role_according_criteria_header_desc'] = '';
$string['settings_assign_role_according_criteria_status'] = 'Включить массовое назначение ролей';
$string['settings_assign_role_according_criteria_status_desc'] = 'Запуск сценария осуществляется по событиями создания и редактирования пользователя.
                                                                  При соответствии пользователя заданным критериям по полю профиля пользователю будет назначена указанная роль в указанном контексте, при не соответствии критериям и наличии указанной роли в указанном контексте назначение роли будет снято.';
$string['settings_assign_role_according_criteria_user_field'] = 'Выбор поля профиля';
$string['settings_assign_role_according_criteria_user_field_desc'] = '';
$string['settings_assign_role_according_criteria_field_ratio_variant'] = 'Выбор отношения к значению в поле профиля';
$string['settings_assign_role_according_criteria_field_ratio_variant_desc'] = '';
$string['settings_assign_role_according_criteria_user_field_value'] = 'Значение поля профиля';
$string['settings_assign_role_according_criteria_user_field_value_desc'] = '';
$string['settings_assign_role_according_criteria_context_level'] = 'Выбор уровня контекста для назначения роли';
$string['settings_assign_role_according_criteria_context_level_desc'] = 'Если выбран уровень контекста "Категория" требуется сохранить настройки, что позволит выбрать категорию из появившегося выпадающего списка';
$string['settings_assign_role_according_criteria_assigned_role'] = 'Выбор назначаемой роли';
$string['settings_assign_role_according_criteria_assigned_role_desc'] = 'Требуется выбирать роль которая соответствует выбранному контексту';
$string['settings_assign_role_according_criteria_category'] = 'Выбор категорий для назначения роли';
$string['settings_assign_role_according_criteria_category_desc'] = 'Данная опция работает только совместно с уровнем контекста установленным в значение "Категория"';

$string['assign_role_according_criteria_fieldratiovariant_equal'] = 'Совпадает';
$string['assign_role_according_criteria_fieldratiovariant_notequal'] = 'Не совпадает';
$string['assign_role_according_criteria_fieldratiovariant_contain'] = 'Содержит';
$string['assign_role_according_criteria_fieldratiovariant_notcontain'] = 'Не содержит';

