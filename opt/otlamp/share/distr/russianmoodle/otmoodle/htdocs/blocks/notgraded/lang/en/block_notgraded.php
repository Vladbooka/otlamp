<?php
$string['notgraded'] = 'To be graded: ';
$string['not_notgraded'] = 'All activities have been graded.';
$string['pluginname'] = 'Notgraded';
$string['notgraded_courses'] = 'Всего по курсам';
$string['notgraded_courses_title'] = 'List';
$string['course'] = 'Курс';
$string['notgraded_total'] = '$a Непроверенных заданий';
$string['all_courses_graded'] = 'Задания во всех курсах проверены';
$string['deny_access'] = 'Доступ запрещен!';
$string['notgraded_list'] = 'Список непроверенных заданий';
$string['view_notgraded_list'] = 'Посмотреть список заданий';
$string['notgraded:addinstance'] = 'Add a new Notgraded block';
$string['notgraded:myaddinstance'] = 'Add a new Notgraded block to My home';
$string['notgraded:view_others'] = 'View statistics of notgraded items of other users';
$string['error_require_view_others_capability'] = 'Требуется право просмотра непроверенных работ других пользователей';
$string['error_require_view_capability'] = 'Требуется право просмотра непроверенных работ';
$string['all_notgraded'] = 'Отображать все непроверенные задания';
$string['none_notgraded'] = 'Не отображать непроверенные задания';
$string['none_notgraded_group'] = 'Не отображать непроверенные задания только в курсах с групповым обучением';
$string['confignotgradedlist'] = 'Режим отображения непроверенных заданий на странице';
$string['notgraded:view'] = 'View notgraded block';
$string['mygroupssection'] = 'My groups';
$string['othergroupssection'] = 'Other groups';
    
$string['setting_cache_update_mode'] = 'Режим обновления данных';
$string['setting_cache_update_mode_desc'] =
'<div>Мощный сервер - всегда актуальные данные, обновляются при срабатывании событий, влияющих на количество непроверенных работ. Создается постоянная нагрузка на сервер.</div>'.
'<div>Слабый сервер - актульность данных ограничена только временем жизни кэша. Обновление кэша производится при условии, что кэш устарел, во время получения данных. Для этой опции лучше настроить время жизни кэша не очень большим.</div>';
$string['setting_cache_update_mode_events_on'] = 'Мощный сервер';
$string['setting_cache_update_mode_events_off'] = 'Слабый сервер';

$string['setting_cache_lifetime'] = 'Время жизни кэша';
$string['setting_cache_lifetime_desc'] = 'Настройка определяет время, в течение которого допустимо отображать пользователю количество непроверенных работ, не производя обновление этого значения. Чем меньше значение, тем чаще будет выполняться обновление данных, тем выше нагрузка на сервер.';
$string['need_dof_library'] = 'For operation it is necessary to install the Free Dean\'s Office of version 2017110300 or higher';
$string['viewallnotgradedassigns'] = 'Посмотреть список всех непроверенных заданий в системе';
$string['viewmynotgradedassigns'] = 'Посмотреть список моих непроверенных заданий';
$string['notgraded:viewall'] = 'Право видеть все непроверенные задания по системе';

?>