<?php
$string['dof:view'] = 'Использование модуля';
$string['dof:datamanage'] = 'Управление обучением в обход установленных правил';
$string['dof:manage'] = 'Управление обучением';
$string['dof:admin'] = 'Техническая настройка';
$string['dof:addinstance'] = 'Добавление нового блока "Электронный деканат"';
$string['dof:myaddinstance'] = 'Добавление нового блока "Электронный деканат" на "Мою страницу"';
$string['title'] = 'Электронный деканат';
$string['page_main_name'] = 'Электронный деканат';
$string['access_denied'] = 'Доступ запрещен';
$string['nopermissions'] = 'Доступ запрещен {$a}';
$string['aboutdof'] = 'Подробнее о продукте';
$string['version'] = 'Версия';
$string['projectname'] = 'Free Dean\'s Office';
$string['project_site'] = 'Сайт проекта:';
$string['license'] = 'Лицензия';
$string['project_info'] = 'Информация о проекте';
$string['pluginname'] = 'Электронный деканат';
$string['version'] = 'Версия';
$string['navigation'] = 'Навигация';

$string['notice_datamanage_access'] = 'Вы вошли под учетной записью, которая позволяет работать с системой в обход стандартной логики!';
$string['warning_delete_instance'] = 'Не удаляйте этот блок! Это приведет к уничтожению файлов в модуле "Электронный деканат"';

$string['execute_plugins_cron_loan_1'] = 'Выполнение срочных плановых задач по обслуживанию Деканата';
$string['execute_plugins_cron_loan_2'] = 'Выполнение стандартных плановых задач по обслуживанию Деканата';
$string['execute_plugins_cron_loan_3'] = 'Выполнение высоконагруженных плановых задач по обслуживанию Деканата';
$string['execute_transmit'] = 'Выполнение очереди задач обмена данными';
$string['execute_plugins_todos'] = 'Выполнение заданий (todo) Деканата';

$string['plugin_installation'] = 'Установка плагинов';
$string['plugin_installation_success'] = 'Установка плагинов завершена успешно';
$string['plugin_installation_error'] = 'Обновление плагинов завершилось с ошибкой. Обратитесь к сотруднику, обслуживающему вашу инсталляцию.';

////////////////////////////////////////
// config
$string['config_header'] = 'Настройки блока';
$string['config_translation_mode'] = 'Выберите режим трансляции';
$string['config_translation_mode_block'] = 'Блок';
$string['config_translation_mode_section'] = 'Секция';
$string['config_translation_im'] = 'Выберите интерфейс';
$string['config_translation_name'] = 'Введите имя';
$string['config_translation_id_mode'] = 'Выберите, что передавать в качестве идентификатора';
$string['config_translation_id_mode_manual'] = 'Указанный вручную';
$string['config_translation_id_mode_userid'] = 'Идентификатор пользователя Moodle';
$string['config_translation_id_mode_courseid'] = 'Идентификатор курса';
$string['config_translation_id_mode_personid'] = 'Идентификатор пользователя электронного деканата';
$string['config_translation_id'] = 'Введите идентификатор, если выбрано указание вручную и если требуется его передавать';


$string['config_choose_category_content_role'] = 'Выберите роль';
$string['config_choose_category_content_role_desc'] = '';
$string['config_view_category_content_role'] = 'Роль, назначаемая в категории для просмотра контента';
$string['config_view_category_content_role_desc'] = '';
$string['config_edit_category_content_role'] = 'Роль, назначаемая в категории для редактирования контента';
$string['config_edit_category_content_role_desc'] = '';
$string['config_manage_category_content_role'] = 'Роль, назначаемая в категории для управления контентом';
$string['config_manage_category_content_role_desc'] = '';
$string['config_mdlcategoryid_number'] = 'Номер структуры синхронизации подразделений с категориями для назначения ролей';
$string['config_mdlcategoryid_number_desc'] = '';


////////////////////////////////////////
// OpenTechnology services
$string['get'] = 'получить';
$string['save'] = 'сохранить';

$string['pageheader'] = 'Техническая поддержка модуля "Электронный деканат"';
$string['otkey'] = 'Секретный ключ';
$string['otserial'] = 'Серийный номер';

$string['get_otserial'] = 'Получить серийный номер';
$string['get_otserial_fail'] = 'Не удалось получить серийный номер СЭО 3KL на сервере api.opentechnology.ru. Сервер сообщил ошибку: {$a}';
$string['reset_otserial'] = "Сбросить серийный номер";
$string['already_has_otserial'] = 'Инсталляция уже зарегистрирована и получила серийный номер, нет необходимости получать ещё один.';
$string['otserial_check_ok'] = 'Серийный номер действителен.';
$string['otserial_check_fail'] = 'Серийный номер не прошел проверку на сервере.
Причина: {$a}. Если Вы считаете, что этого не должно было
произойти, пожалуйста, обратитесь в службу технической поддержки.';
$string['otserial_tariff_wrong'] = "Тарифный план недоступен для данного продукта. Обратитесь в службу технической поддержки.";


// Service
$string['otservice'] = 'Тарифный план: <u>{$a}</u>';
$string['otservice_send_order'] = "Заполнить заявку на заключение договора об обслуживании";
$string['otservice_renew'] = 'Заполнить заявку на продление';
$string['otservice_change_tariff'] = 'Сменить тарифный план';

$string['otservice_expired'] = 'Срок действия Вашего тарифного плана истёк. Если Вы желаете продлить срок, пожалуйста, свяжитесь с менеджерами ООО "Открытые технологии".';
$string['otservice_active'] = 'Тарифный план действителен до {$a}';
$string['otservice_unlimited'] = 'Тарифный план действует бессрочно';

$string['monthnum-01'] = 'Январь';
$string['monthnum-02'] = 'Февраль';
$string['monthnum-03'] = 'Март';
$string['monthnum-04'] = 'Апрель';
$string['monthnum-05'] = 'Май';
$string['monthnum-06'] = 'Июнь';
$string['monthnum-07'] = 'Июль';
$string['monthnum-08'] = 'Август';
$string['monthnum-09'] = 'Сентябрь';
$string['monthnum-10'] = 'Октябрь';
$string['monthnum-11'] = 'Ноябрь';
$string['monthnum-12'] = 'Декабрь';

$string['messageprovider:urgent_notifications'] = 'Срочные уведомления от электронного деканата';
$string['messageprovider:noturgent_notifications'] = 'Несрочные уведомления от электронного деканата';
$string['messageprovider:ordinary_notifications'] = 'Обычные уведомления от электронного деканата';

?>