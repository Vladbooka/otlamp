<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
//                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

/**
 * Менеджер обмена данными Деканата. Языковые переменные.
 *
 * @package    im
 * @subpackage participants
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Общие
$string['title'] = 'Менеджер обмена данными Деканата';
$string['page_main_name'] = 'Менеджер обмена данными Деканата';

// КОНФИГУРАТОРЫ
$string['configurator_import_name'] = 'Импорт';
$string['configurator_import_description'] = 'Менеджер импорта данных в Электронный Деканат из внешних хранилищ данных';
$string['configurator_export_name'] = 'Экспорт';
$string['configurator_export_description'] = 'Менеджер экспорта данных из Электронного Деканата во внешние хранилища данных';
// Форма настроек обмена
$string['header_configform_source_import'] = 'Настройка источника';
$string['header_configform_mask_import'] = 'Опции импорта данных';
$string['configform_action_create_pack_label'] = 'Сохранить пакет настроек';
$string['pack_name_shouldn\'t_be_empty'] = 'Пожалуйста, укажите название пакета';
$string['configform_action_reset_label'] = 'Сброс';
$string['header_configform_report'] = 'Предварительный отчет';
$string['configform_import_simulate_label'] = 'Проверка данных';
$string['configform_import_execute_label'] = 'Импортировать данные';
$string['configform_mask_import_fields_header'] = 'Перечень полей для импорта';
$string['header_configform_source_export'] = 'Способы экспорта данных';
$string['header_configform_mask_export'] = 'Опции экспорта данных';
$string['configform_export_execute_label'] = 'Экспортировать данные';
$string['configform_source_info'] = 'Справка';
$string['configform_source_info_desc'] = 'Слева отображаются внутренние интерпретируемые поля справочников Электронного Деканата, справа отображаются поля из внешнего источника.';
// Форма выбота способа обмена
$string['setupform_header'] = 'Выбор способа обмена данных';
$string['setupform_select_mask_label'] = 'Тип данных для обмена';
$string['setupform_select_source_label'] = 'Источник данных';
$string['setupform_submit_label'] = 'Применить';
$string['setupform_error_empty_mask'] = 'Не указан тип данных';
$string['setupform_error_empty_sourcecode'] = 'Не указан источник данных';
$string['setupform_import_header'] = 'Выбор способа импорта';
$string['setupform_import_select_mask_label'] = 'Тип импорта';
$string['setupform_import_select_source_label'] = 'Формат импортируемых данных';
$string['setupform_export_header'] = 'Выбор способа экспорта';
$string['setupform_export_select_mask_label'] = 'Тип экспорта';
$string['setupform_export_select_source_label'] = 'Формат экспортируемых данных';

// ИСТОЧНИКИ ДАННЫХ
$string['source_file_name'] = 'Файл';
$string['source_file_description'] = 'Данные в виде файла';
$string['source_file_csv_name'] = 'CSV';
$string['source_file_csv_description'] = 'Файл формата .csv';
$string['source_file_zipcsv_name'] = 'ZIP-архив';
$string['source_file_zipcsv_description'] = 'Заархивированный CSV файл, содержащий также приложенные файлы';
$string['source_db_name'] = 'База Данных';
$string['source_db_description'] = 'Промежуточная База Данных';
$string['source_db_mssql_name'] = 'MSSQL';
$string['source_db_mssql_description'] = 'Таблица MSSQL';
$string['source_db_mysql_name'] = 'MYSQL';
$string['source_db_mysql_description'] = 'Таблица MYSQL';
$string['source_db_postgresql_name'] = 'PostgreSQL';
$string['source_db_postgresql_description'] = 'Таблица PostgreSQL';
$string['source_configform_host_title'] = 'Хост';
$string['source_configform_host_error_empty'] = 'Хост не указан';
$string['source_configform_dbname_title'] = 'Имя Базы Данных';
$string['source_configform_dbname_error_empty'] = 'Имя Базы Данных не указано';
$string['source_configform_user_title'] = 'Пользователь';
$string['source_configform_user_error_empty'] = 'Пользователь не указан';
$string['source_configform_password_title'] = 'Пароль';
$string['source_configform_password_error_empty'] = 'Пароль не указан';
$string['source_configform_tablename_title'] = 'Имя промежуточной таблицы';
$string['source_configform_charset_title'] = 'Кодировка промежуточной таблицы';
$string['source_configform_tablename_error_empty'] = 'Имя таблицы не указано';
$string['source_configform_port_title'] = 'Порт';
$string['header_configform_source_db_fields'] = 'Сопоставление полей БД';
$string['source_db_error_empty_matchingfields'] = 'Не указано ни одного сопоставления';
$string['source_db_error_access_host_denied'] = 'Ошибка авторизации';
$string['source_db_error_access_db_denied'] = 'Доступ к Базе Данных запрещен';
$string['source_db_error_undefined'] = 'Неизвестная ошибка ({$a->errorcode}: {$a->errortext})';
$string['source_configform_db_error_table_not_found'] = 'Указанная таблица не найдена';
$string['source_configform_db_error_fields_not_found'] = 'Указанная таблица пуста';
$string['source_configform_delimiter_title'] = 'Разделитель';
$string['source_configform_file_title'] = 'Файл';
$string['source_configform_file_error_empty'] = 'Файл не добавлен';
$string['source_configform_encoding_title'] = 'Кодировка файла';
$string['source_filterform_header'] = 'Фильтрация';
$string['source_filterform_dont_filter'] = 'Фильтрация отключена';
$string['source_filterform_missed_filter_value'] = "Отсутствует значение для фильтрации";
$string['source_moodle_name'] = 'Moodle';
$string['source_moodle_description'] = 'В качестве источника будут использованы данные из текущей СДО';
$string['source_moodle_userdata_name'] = 'Данные пользователей';
$string['source_moodle_userdata_description'] = 'Данные из стандартных полей пользователя и настраиваемых полей профиля';
$string['source_moodle_configform_header'] = 'Сопоставление c полями из Moodle';
$string['source_moodle_configform_matching_not_use'] = 'Не использовать';
$string['source_moodle_error_empty_matchingfields'] = 'Не указано ни одного сопоставления';
$string['source_moodle_field_lastname'] = 'Фамилия';
$string['source_moodle_field_firstname'] = 'Имя';
$string['source_moodle_field_middlename'] = 'Отчество';


// СТРАТЕГИИ ИМПОРТА
$string['optional_field'] = 'Опция';
$string['transmit_delimiter_newline'] = 'Новая строка';
$string['transmit_delimiter_comma'] = 'Запятая';
$string['transmit_delimiter_space'] = 'Пробел';
// Стратегия обмена контингента
$string['strategy_participants_name'] = 'Контингент';
$string['strategy_participants_description'] = 'Контингент учебного заведения';
$string['strategy_participants_fieldname_student_email'] = 'Email студента по договору обучения';
$string['strategy_participants_fieldname_user_email'] = 'Email пользователя';
$string['strategy_participants_fieldname_user_country'] = 'Страна пользователя';
$string['strategy_participants_fieldname_user_region'] = 'Регион пользователя';
$string['strategy_participants_fieldname_user_city'] = 'Город пользователя';
$string['strategy_participants_fieldname_user_manager_email'] = 'Электронная почта непосредственного руководителя';
$string['strategy_participants_fieldname_user_manager_idnumber'] = 'Индивидуальный номер Moodle непосредственного руководителя';
$string['strategy_participants_fieldname_department_id'] = 'Идентификатор подразделения';
$string['strategy_participants_fieldname_department_name'] = 'Название подразделения';
$string['strategy_participants_fieldname_department_code'] = 'Код подразделения';
$string['strategy_participants_fieldname_position_code'] = 'Код должности';
$string['strategy_participants_fieldname_position_name'] = 'Название должности';
$string['strategy_participants_fieldname_/user_profilefield_([0-9a-zA-Z]*)/m'] = 'Кастомное поля пользователя';
$string['strategy_participants_fieldname_schposition_generate'] = 'Флаг генерация новой вакансии (Всегда выбирайте да)';
$string['strategy_participants_fieldname_student_fullname'] = 'ФИО студента';
$string['strategy_participants_fieldname_student_firstname'] = 'Имя студента';
$string['strategy_participants_fieldname_student_lastname'] = 'Фамилия студента';
$string['strategy_participants_fieldname_student_middlename'] = 'Отчество студента';
$string['strategy_participants_fieldname_student_dateofbirth'] = 'Дата рождения студента';
$string['strategy_participants_fieldname_student_gender'] = 'Пол студента';
$string['strategy_participants_fieldname_student_phonecell'] = 'Мобильный телефон студента';
$string['strategy_participants_fieldname_student_id'] = 'Идентификатор студента';
$string['strategy_participants_fieldname_student_mdluser'] = 'Идентификатор пользователя Moodle студента';
$string['strategy_participants_fieldname_student_departmentid'] = 'Домашнее подразделение студента';
$string['strategy_participants_fieldname_/customfield_([0-9a-zA-Z]*)/m'] = 'Данные дополнительных полей студента';
$string['strategy_participants_fieldname_student_doublepersonfullname'] = 'Создавать новую персону при нахождении персоны с аналогичным ФИО.';
$string['strategy_participants_fieldname_student_email_generate'] = 'Шаблон для генерации EMAIL студента';
$string['strategy_participants_fieldname_student_formatfullname'] = 'Формат ФИО студента для разбиения на части';
$string['strategy_participants_fieldname_student_password'] = 'Пароль студента';
$string['strategy_participants_fieldname_student_passwordformat'] = 'Формат пароля студента (в открытом виде или md5)';
$string['strategy_participants_fieldname_student_department_code'] = 'Код подразделения студента';
$string['strategy_participants_fieldname_student_sync2moodle'] = 'Флаг необходимости синхронизации персоны с пользователем Moodle';
$string['strategy_participants_fieldname_student_extid'] = 'Внешний идентификатор студента';
$string['strategy_participants_fieldname_student_username'] = 'Логин студента';
$string['strategy_participants_fieldname_student_department_code_default'] = 'Код подразделения студента по умолчанию';
$string['strategy_participants_fieldname_student_passwordformat_default'] = 'Формат пароля студента по умолчанию (в открытом виде или md5)';
$string['strategy_participants_fieldname_student_sync2moodle_default'] = 'Флаг необходимости синхронизации персоны с пользователем Moodle по умолчанию';
$string['strategy_participants_fieldname_parent_email'] = 'Email законного представителя по договору обучения';
$string['strategy_participants_fieldname_parent_fullname'] = 'ФИО законного представителя';
$string['strategy_participants_fieldname_parent_firstname'] = 'Имя законного представителя';
$string['strategy_participants_fieldname_parent_lastname'] = 'Фамилия законного представителя';
$string['strategy_participants_fieldname_parent_middlename'] = 'Отчество законного представителя';
$string['strategy_participants_fieldname_parent_dateofbirth'] = 'Дата рождения законного представителя';
$string['strategy_participants_fieldname_parent_gender'] = 'Пол законного представителя';
$string['strategy_participants_fieldname_parent_doublepersonfullname'] = 'Создавать новую персону при нахождении персоны с аналогичным ФИО.';
$string['strategy_participants_fieldname_parent_email_generate'] = 'Шаблон для генерации EMAIL законного представителя';
$string['strategy_participants_fieldname_parent_formatfullname'] = 'Формат ФИО законного представителя для разбиения на части';
$string['strategy_participants_fieldname_curator_email'] = 'Email куратора по договору обучения';
$string['strategy_participants_fieldname_curator_fullname'] = 'ФИО куратора';
$string['strategy_participants_fieldname_curator_firstname'] = 'Имя куратора';
$string['strategy_participants_fieldname_curator_lastname'] = 'Фамилия куратора';
$string['strategy_participants_fieldname_curator_middlename'] = 'Отчество куратора';
$string['strategy_participants_fieldname_curator_dateofbirth'] = 'Дата рождения куратора';
$string['strategy_participants_fieldname_curator_gender'] = 'Пол куратора';
$string['strategy_participants_fieldname_curator_doublepersonfullname'] = 'Создавать новую персону при нахождении персоны с аналогичным ФИО.';
$string['strategy_participants_fieldname_curator_email_generate'] = 'Шаблон для генерации EMAIL куратора';
$string['strategy_participants_fieldname_curator_formatfullname'] = 'Формат ФИО куратора для разбиения на части';
$string['strategy_participants_fieldname_seller_email'] = 'Email менеджера по договору обучения';
$string['strategy_participants_fieldname_seller_fullname'] = 'ФИО менеджера';
$string['strategy_participants_fieldname_seller_firstname'] = 'Имя менеджера';
$string['strategy_participants_fieldname_seller_lastname'] = 'Фамилия менеджера';
$string['strategy_participants_fieldname_seller_middlename'] = 'Отчество менеджера';
$string['strategy_participants_fieldname_seller_dateofbirth'] = 'Дата рождения менеджера';
$string['strategy_participants_fieldname_seller_gender'] = 'Пол менеджера';
$string['strategy_participants_fieldname_seller_doublepersonfullname'] = 'Создавать новую персону при нахождении персоны с аналогичным ФИО.';
$string['strategy_participants_fieldname_seller_email_generate'] = 'Шаблон для генерации EMAIL менеджера';
$string['strategy_participants_fieldname_seller_formatfullname'] = 'Формат ФИО менеджера для разбиения на части';
$string['strategy_participants_fieldname_student_contract_date'] = 'Дата заключения договора';
$string['strategy_participants_fieldname_student_contract_activate'] = 'Активация договора';
$string['strategy_participants_fieldname_student_contract_num'] = 'Номер договора';
$string['strategy_participants_fieldname_student_contract_num_description'] = '. Пример - #000{person_id} / #000{person_departmentid}. Подстанавливаемым полем может быть любое поле хранилища персон';
$string['strategy_participants_fieldname_student_contract_notice'] = 'Заметка о договоре';
$string['strategy_participants_fieldname_student_contract_num_generate'] = 'Генерировать номер для нового договора';

// Стратегия обмена достижений
$string['strategy_achievements_name'] = 'Достижения';
$string['strategy_achievements_description'] = '';
$string['strategy_achievements_fieldname_person_id'] = 'Идентификатор владельца достижения';
$string['strategy_achievements_fieldname_person_email'] = 'Email владельца достижения';
$string['strategy_achievements_fieldname_achievement_id'] = 'Идентификатор шаблона достижения';
$string['strategy_achievements_fieldname_achievement_update_exists'] = 'Обновлять пользовательские достижения';
$string['strategy_achievements_fieldname_/(criteria[0-9]*)/m'] = 'Пронумерованные критерии пользовательского достижения';

// Стратегия обмена программ
$string['strategy_programms_name'] = 'Программы';
$string['strategy_programms_description'] = '';
$string['strategy_programms_fieldname_programm_id'] = 'Идентификатор программы';
$string['strategy_programms_fieldname_programm_name'] = 'Название программы';
$string['strategy_programms_fieldname_programm_code'] = 'Код программы';
$string['strategy_programms_fieldname_programm_agenums'] = 'Число параллелей';
$string['mask_programms_programms_name'] = 'Программы';
$string['mask_programms_programms_description'] = '';

// Стратегия обмена подразделениями
$string['strategy_departments_name'] = 'Подразделения';
$string['strategy_departments_description'] = '';
$string['strategy_departments_fieldname_department_id'] = 'Идентификатор подразделения';
$string['strategy_departments_fieldname_department_name'] = 'Название подразделения';
$string['strategy_departments_fieldname_department_code'] = 'Код подразделения';
$string['strategy_departments_fieldname_department_description'] = 'Описание подразделения';
$string['strategy_departments_fieldname_department_leaddepid'] = 'Идентификатор вышестоящего подразделения';
$string['strategy_departments_fieldname_department_leaddepcode'] = 'Код вышестоящего подразделения';
$string['strategy_departments_fieldname_department_activate'] = 'Активация подразделения';
$string['mask_departments_departments_name'] = 'Подразделения';
$string['mask_departments_departments_description'] = '';

// Стратегия обмена учебного плана
$string['strategy_cstreams_name'] = 'Учебный план';
$string['strategy_cstreams_description'] = '';
$string['mask_cstreams_cstreams_name'] = 'Учебные процессы';
$string['mask_cstreams_cstreams_description'] = '';
$string['mask_cstreams_option_age'] = 'Учебный период';

$string['mask_cstreams_option_cpassed_list_delimiter'] = 'Разделитель';
$string['mask_cstreams_option_cpassed_fullnameformat'] = 'Формат ФИО';

$string['strategy_cstreams_fieldname_department_id'] = 'Идентификатор подразделения учебного процесса';
$string['strategy_cstreams_fieldname_department_code'] = 'Код подразделения учебного процесса';
$string['strategy_cstreams_fieldname_age_id'] = 'Идентификатор учебного периода';
$string['strategy_cstreams_fieldname_programmitem_id'] = 'Идентификатор дисциплины учебного процесса';
$string['strategy_cstreams_fieldname_programmitem_code'] = 'Код дисциплины учебного процесса';
$string['strategy_cstreams_fieldname_teacher_email'] = 'Email преподавателя';
$string['strategy_cstreams_fieldname_teacher_lastname'] = 'Фамилия преподавателя';
$string['strategy_cstreams_fieldname_teacher_firstname'] = 'Имя преподавателя';
$string['strategy_cstreams_fieldname_teacher_middlename'] = 'Отчество преподавателя';
$string['strategy_cstreams_fieldname_teacher_appointemnt_id'] = 'Идентификатор должостного назначения преподавателя';
$string['strategy_cstreams_fieldname_cstream_id'] = 'Идентификатор учебного процесса';
$string['strategy_cstreams_fieldname_cstream_name'] = 'Название учебного процесса';
$string['strategy_cstreams_fieldname_cstream_description'] = 'Описание учебного процесса';
$string['strategy_cstreams_fieldname_cstream_begindate'] = 'Дата начала обучения';
$string['strategy_cstreams_fieldname_cstream_enddate'] = 'Дата окончания обучения';
$string['strategy_cstreams_fieldname_cstream_hoursweek'] = 'Недельная нагрузка преподавателя';
$string['strategy_cstreams_fieldname_cpassed_fullname_list'] = 'Список ФИО учащихся, которых необходимо зачислить на импортируемый учебный процесс. Например: Иванов Иван Иванович\nПетров Петр Петрович';
$string['strategy_cstreams_fieldname_cpassed_fullname_list_delimiter'] = 'Разделитель списка ФИО слушателей. В примере выше разделителем является \n (перенос на следующую строку)';
$string['strategy_cstreams_fieldname_cpassed_fullname_list_fullnameformat'] = 'Формат ФИО слушателей. В примере выше формат выглядит следующим образом: LASTNAME FIRSTNAME MIDDLENAME';

// ОБРАБОТЧИКИ
$string['converter_programmsbc_fullname_to_id_error_multiple_persons'] = 'Найдено несколько персон с указанным ФИО';
$string['converter_position_name_to_id_duplicate'] = 'Найдено {$a} одноименных должностей';


// Дефолтные строки
$string['yes'] = 'Да';
$string['no'] = 'Нет';

$string['none'] = 'Пропустить';
$string['default'] = 'По умолчанию';
$string['override'] = 'Переопределить';

// Стратегии


// Маски
$string['mask_participants_contracts_name'] = 'Договоры на обучение';
$string['mask_participants_persons_name'] = 'Персоны';
$string['mask_participants_appointments_name'] = 'Сотрудники';
$string['mask_learningitems_ages_name'] = 'Учебные периоды';
$string['mask_learningitems_cstreams_name'] = 'Учебные процессы';
$string['mask_learningitems_departments_name'] = 'Подразделения';
$string['mask_learningitems_programmitems_name'] = 'Дисциплины';
$string['mask_learningitems_programms_name'] = 'Программы';
$string['mask_achievements_achievementins_name'] = 'Достижения пользователей';

$string['configform_import_simulate'] = 'Сформировать предварительный отчет';
$string['configform_import_execute'] = 'Импорт';
$string['configform_export_export'] = 'Экспорт';

// Маска обмена данных достижений
$string['strategy_achievements_mask_achievementins_achievementid_label'] = 'Шаблон достижения';
$string['strategy_achievements_mask_achievementins_update_exists'] = 'Обновить имеющиеся';

// Маска персон
$string['mask_persons_header_student'] = 'Студент';
$string['mask_persons_header_parent'] = 'Законный представитель';
$string['mask_persons_header_seller'] = 'Продавец';
$string['mask_persons_header_curator'] = 'Куратор';

$string['mask_persons_choose_option_email'] = 'Генерация email';
$string['mask_persons_choose_option_fullnameformat'] = 'Формат ФИО';
$string['mask_persons_choose_option_doublepersonfullname'] = 'Дублировать по ФИО';
$string['mask_persons_choose_option_passwordformat'] = 'Формат пароля в источнике';
$string['mask_persons_passwordformat_clear'] = 'Пароль в открытом виде';
$string['mask_persons_passwordformat_md5'] = 'Хеш пароля (алгоритм md5)';
$string['mask_persons_passwordformat'] = 'Формат пароля';
$string['mask_persons_choose_option_sync2moodle'] = 'Синхронизировать персону с пользователем Moodle';
$string['mask_persons_choose_option_departmentcode'] = 'Код подразделения';

// Маска договоров
$string['mask_contracts_header_student_contract'] = 'Договор студента';
$string['mask_contracts_group_contract_num_input'] = 'Номер договора';
$string['mask_contracts_group_contract_activate'] = 'Активация договора';
$string['mask_contracts_group_contract_num'] = 'Генерация номера договора';
$string['mask_contracts_group_contract_date'] = 'Дата заключения';
$string['mask_contracts_group_contract_notice'] = 'Заметка к договору';

// Маска подразделений
$string['mask_departments_choose_activate'] = 'Активировать подразделения';
$string['dof_modlib_transmit_mask_departments_header'] = 'Подразделение';

// Маска программ
$string['dof_sync_transmit_mask_programms_header'] = 'Программа';
$string['mask_programms_choose_activate'] = 'Активировать программу';
$string['mask_programms_group_about'] = 'Описание программы';
$string['mask_programms_group_notice'] = 'Заметки к программе';
$string['mask_programms_group_agenums'] = 'Количество параллелей';
$string['mask_programms_group_flowagenums'] = 'Плавающие учебные планы';
$string['mask_programms_group_edulevel'] = 'Уровень образования';
$string['mask_programms_group_duration'] = 'Длительность обучения';
$string['mask_programms_group_price'] = 'Цена';
$string['academic_hours'] = 'Ак. часов';
$string['academic_days'] = 'Дней';

// Маска учебных периодов
$string['dof_sync_transmit_mask_ages_header'] = 'Учебный период';
$string['mask_ages_begindate'] = 'Начало учебного периода в подразделении';
$string['mask_ages_enddate'] = 'Конец учебного периода в подразделении';
$string['mask_ages_eduweeks'] = 'Количество недель';
$string['mask_ages_group_schdays'] = 'Количество дней в учебной неделе';
$string['mask_ages_group_schedudays'] = 'Список учебных дней в учебной неделе';
$string['mask_ages_group_schstartdaynum'] = 'Номер первого дня в периоде';
$string['mask_ages_group_useweekdaynames'] = 'Использовать календарные названия дней';

// Маска дисциплин
$string['dof_sync_transmit_mask_programmitems_header'] = 'Дисциплина';
$string['mask_pitems_group_scale'] = 'Шкала оценок';
$string['mask_pitems_group_mingrade'] = 'Минимальный проходной балл';
$string['mask_programmitem_group_about'] = 'Описание';
$string['mask_programmitem_group_notice'] = 'Заметки';
$string['mask_pitems_group_required'] = 'Обязательный';
$string['mask_pitems_group_price'] = 'Цена';
$string['mask_pitems_group_controltype'] = 'Тип итогового контроля';
$string['mask_pitems_group_gradelevel'] = 'Уровень оценки';

// Маска учебных процессов
$string['dof_sync_transmit_mask_сstreams_header'] = 'Учебный процесс';
$string['mask_cstreams_group_age'] = 'Учебный период';
$string['mask_cstreams_group_about'] = 'Описание';
$string['mask_cstreams_begindate'] = 'Начало обучения';
$string['mask_cstreams_enddate'] = 'Конец обучения';
$string['mask_cstreams_group_eduweeks'] = 'Количество недель';
$string['mask_cstreams_group_hours'] = 'Часов всего';
$string['mask_cstreams_group_hoursweek'] = 'Часов в неделю';
$string['mask_cstreams_group_hoursweekinternally'] = 'Часов в неделю очно';
$string['mask_cstreams_group_hoursweekdistance'] = 'Часов дистанционно';
$string['mask_cstreams_begindate_equal_age'] = 'Дата начала обучения совпадает с датой периода';
$string['mask_cstreams_begindate_write'] = 'Дата конца обучения задается вручную';
$string['mask_cstreams_enddate_equal_age'] = 'Дата конца обучения совпадает с датой периода';
$string['mask_cstreams_enddate_age_duration'] = 'Дата конца обучения расчитывается из продолжительности дисциплины';
$string['mask_cstreams_enddate_write'] = 'Дата конца обучения задается вручную';

// маска сотрудников
$string['mask_appointments_group_schposition_generate'] = 'Генерация новой вакансии';
$string['mask_appointments_group_position_name'] = 'Название должности';

// Все ошибки
$string['not_enough_data'] = 'Недостаточно данных';
$string['doubleperson_error'] = 'Пользователь с ФИО «{$a->lastname} {$a->firstname} {$a->middlename}» уже существует в системе. Для импорта необходимо включить настройку дублирования по ФИО.';

// Коды для логирования
$string['import'] = 'Импорт';

// Импорт подразделений
$string['leaddepid_equal'] = 'Импортуремое подразделение не может быть унаследовано от самого себя. ';
$string['code_already_exists'] = 'Введный код подразделения уже используется. ';
$string['department_cannot_activate_dep'] = 'Во время перевода подразделения в активный статус произошла ошибка. ';

// Импорт программ
$string['programm_update_invalid_agenums'] = 'Неверное количество параллелей. ';
$string['programm_create_limit_error'] = 'Превышен лимит програм в выбранном подразделении. ';
$string['programm_cannot_activate_programm'] = 'Во время активации программы возникла ошибка. ';
$string['programm_code_already_used'] = 'Код программы уже используется другой программой. ';
$string['programm_code_already_used_generated'] = 'Для импортируемой программы будет сгенерирован новый код, так как введенный уже используется. ';
$string['programm_empty_name'] = 'Отсутствует название программы. ';
$string['programm_create_emptydep'] = 'Отсутствует подразделение. ';

// Импорт учебных периодов
$string['age_invalid_schdays'] = 'При использовании календарных названий дней, количество дней в учебной неделе должно быть равно 7. ';
$string['age_error_import'] = 'Во время процесса импорта учебного периода возникла ошибка. ';
$string['age_empty_dep'] = 'Отсутствует подразделение. ';

// Импорт дисциплин
$string['programmitem_code_already_used'] = 'Введенный код дисциплины уже используется. ';
$string['programmitem_cannot_change_mdlcourse'] = 'Изменение привязанного к дисциплине курса в мудл запрещено. ';
$string['programmitem_empty_dep'] = 'Отсутствует подразделение дисциплины. ';
$string['programmitem_limit_dep'] = 'Превышен лимит создаваемых дисциплин. ';
$string['programmitem_code_already_used'] = 'Введенный код дисциплины уже используется. ';
$string['programmitem_empty_name'] = 'Не указано название дисциплины. ';

// Импорт учебных процессов
$string['cstream_age_invalid_status'] = 'Учебный период находится в невалидном статусе. ';
$string['cstream_limit_objs'] = 'Превышен лимит учебных процессов. ';
$string['cstream_begindate_invalid'] = 'Неверная дата начала учебного процесса (Будет взята из учебного периода). ';
$string['cstream_enddate_invalid'] = 'Неверная дата конца учебного процесса (Будет взята из учебного периода). ';
$string['cstream_teacher_cannot_teach'] = 'Указанный преподаватель не может вести указанную дисциплину. ';

// Импорт допполей
$string['customfield_import_error'] = 'При обновлении дополнительного поля «{$a->code}» возникла ошибка. ';
$string['customfield_import_success'] = 'Дополнительное поле успешно обновлено';

// Импорт договоров
$string['contract_cannot_change_status'] = 'Не удалось сменить статус договора на «оказание услуг»';
$string['contract_success_change_status'] = 'Договор успешно активирован';

// Импорт сотрудников (должностных назначений)
$string['importer_appointments_required_fields'] = 'Отсутствуют обязательные параметры, достаточные для проведения операции. Убедитесь, что вы указали должность, подразделение, вакансию, email для идентификации персоны';
$string['importer_appointments_appointment_not_found'] = 'Должностное назначение, синхронизированное ранее, не найдено';
$string['importer_appointments_appointment_canceled'] = 'Предыдущее должностное назначение отменено в связи с появлением новых данных';
$string['importer_appointments_not_enough_data_to_create'] = 'Недостаточно данных для создания должностного назначения. Убедитесь, что вы указали должность, подразделение, вакансию, email для идентификации персоны';

$string['sql_comparison_operator_equal_to'] = '=';
$string['sql_comparison_operator_greater_than'] = '>';
$string['sql_comparison_operator_less_than'] = '<';
$string['sql_comparison_operator_greater_than_or_equal_to'] = '>=';
$string['sql_comparison_operator_less_than_or_equal_to'] = '<=';
$string['sql_comparison_operator_not_equal_to'] = '<>';
?>