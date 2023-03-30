<?php
$string['notgraded'] = 'Надо проверить ';
$string['not_notgraded'] = 'Проверять нечего';
$string['pluginname'] = 'Непроверенные задания';
$string['notgraded_courses'] = 'Всего по курсам';
$string['notgraded_courses_title'] = 'Список курсов с непроверенными заданиями';
$string['course'] = 'Курс';
$string['notgraded_total'] = 'Непроверенных заданий';
$string['all_courses_graded'] = 'Задания во всех курсах проверены';
$string['deny_access'] = 'Доступ запрещен!';
$string['notgraded_list'] = 'Список непроверенных заданий';
$string['view_notgraded_list'] = 'Посмотреть список заданий';
$string['notgraded:addinstance'] = 'Добавление нового блока "Надо проверить"';
$string['notgraded:myaddinstance'] = 'Добавление нового блока "Надо проверить" на "Мою страницу"';
$string['notgraded:view_others'] = 'Видеть статистику непроверенных работ других пользователей';
$string['error_require_view_others_capability'] = 'Требуется право просмотра непроверенных работ других пользователей';
$string['error_require_view_capability'] = 'Требуется право просмотра непроверенных работ';
$string['all_notgraded'] = 'Отображать все непроверенные задания';
$string['none_notgraded'] = 'Не отображать непроверенные задания';
$string['none_notgraded_group'] = 'Не отображать непроверенные задания только в курсах с групповым обучением';
$string['confignotgradedlist'] = 'Режим отображения непроверенных заданий на странице';
$string['notgraded:view'] = 'Видеть блок "Надо проверить"';
$string['mygroupssection'] = 'Мои группы';
$string['othergroupssection'] = 'Остальные группы';

$string['setting_cache_update_mode'] = 'Режим обновления данных';
$string['setting_cache_update_mode_desc'] = 
    '<div>Мощный сервер - всегда актуальные данные, обновляются при срабатывании событий, влияющих на количество непроверенных работ. Создается постоянная нагрузка на сервер.</div>'.
    '<div>Слабый сервер - актульность данных ограничена только временем жизни кэша. Обновление кэша производится при условии, что кэш устарел, во время получения данных. Для этой опции лучше настроить время жизни кэша не очень большим.</div>';
$string['setting_cache_update_mode_events_on'] = 'Мощный сервер';
$string['setting_cache_update_mode_events_off'] = 'Слабый сервер';

$string['setting_cache_lifetime'] = 'Время жизни кэша';
$string['setting_cache_lifetime_desc'] = 'Настройка определяет время, в течение которого допустимо отображать пользователю количество непроверенных работ, не производя обновление этого значения. Чем меньше значение, тем чаще будет выполняться обновление данных, тем выше нагрузка на сервер.';
$string['need_dof_library'] = 'Для работы блока необходимо установить блок Электронный деканат версии 2017110300 или выше';
$string['viewallnotgradedassigns'] = 'Посмотреть список всех непроверенных заданий в системе';
$string['viewmynotgradedassigns'] = 'Посмотреть список моих непроверенных заданий';
$string['notgraded:viewall'] = 'Право видеть все непроверенные задания по системе';

?>