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
 * Пользовательские достижения
 *
 * @package    im
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');

// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
        $DOF->get_string('mypage_title', 'achievements'),
        $DOF->url_im('achievements', '/my.php'),
        $addvars
);

// ПОДГОТОВКА ДАННЫХ
// ID пользователя
$personid = optional_param('personid', NULL, PARAM_INT);
// ID текущего раздела
$catid = optional_param('catid', 0, PARAM_INT);
// Получение текущего пользователя
$currentperson = $DOF->storage('persons')->get_bu(NULL, true);
// Пагинация
$limitnum = $addvars['limitnum'];
$limitfrom = optional_param('limitfrom', 1, PARAM_INT);
// Сортировка
$sort = optional_param('sort', NULL, PARAM_TEXT);
$sortdir = optional_param('dir', 'ASC', PARAM_TEXT);
// Фильтр достижений
$filter = optional_param('filter', NULL, PARAM_RAW);
// Последнее отмодерированное достижение (для прокрутки)
$lastmoderated = optional_param('lastmoderated', NULL, PARAM_INT);

$headeroptions = NULL;
if( ! empty($lastmoderated) )
{
    $DOF->modlib('nvg')->add_js('modlib', 'widgets', '/js/scrollto.js', false);
    $DOF->modlib('nvg')->add_css('modlib', 'widgets', '/css/scrollto.css');
    $headeroptions = [
        'scrollto' => 'moderate_achievementin_'.$lastmoderated
    ];
}
$DOF->modlib('nvg')->add_js('im', 'achievements', '/js/achievements_hide_toggle.js', false);
// НОРМАЛИЗАЦИЯ ЗНАЧЕНИЙ
if ( isset($currentperson->id) && $currentperson->id == $personid )
{// Передана текущая персона в параметре
    $personid = NULL;
}
if ( $limitfrom < 1 )
{// Недопустимое значение страинцы
    $limitfrom = 1;
}
// Нормализация сортировки
if ( $sortdir != 'ASC' )
{
    $sortdir = 'DESC';
}

// ФОРМИРОВАНИЕ GET-ПАРАМЕТРОВ
$addvars['limitfrom'] = $limitfrom;
if ( ! empty($personid) )
{// Персона не-текущая
    $addvars['personid'] = $personid;
}
if ( ! empty($sort) )
{
    $addvars['dir'] = $sortdir;
    $addvars['sort'] = $sort;
}
// Добавление системынх сообщений из GET-ПАРАМЕТРОВ
$DOF->im('achievements')->messages();

// ПЕРЕХОД В ПОДРАЗДЕЛЕНИЕ ЦЕЛЕВОЙ ПЕРСОНЫ
if ( ! empty($personid) )
{// Персона передана в параметре
    // Получение данных персоны
    $settedperson = $DOF->storage('persons')->get($personid);
    if ( isset($settedperson->departmentid) && $addvars['departmentid'] != $settedperson->departmentid )
    {// Текущий пользователь находится не в подразделении персоны
        // Переход в подразделение целевой персоны
        $addvars['departmentid'] = $settedperson->departmentid;
        $url = $DOF->url_im('achievements', '/my.php', $addvars);
        redirect($url);
    }
} else 
{// Персона текущая
    if ( isset($currentperson->departmentid) && $addvars['departmentid'] != $currentperson->departmentid )
    {// Персона находится не в своем подразделении
        // Переход в подразделение целевой персоны
        $addvars['departmentid'] = $currentperson->departmentid;
        $url = $DOF->url_im('achievements', '/my.php', $addvars);
        redirect($url);
    }
}

// Права доступа
$DOF->im('achievements')->require_access('my');
   
// Формирование url формы
$url = $DOF->url_im('achievements', '/my.php', $addvars);

// ФОРМА СОЗДАНИЯ ДОСТИЖЕНИЙ
// Отображение формы создания достижения
// Сформируем дополнительные данные
$customdata = new stdClass;
$customdata->dof = $DOF;
$customdata->id = $catid;
$customdata->addvars = $addvars;
if ( ! empty($personid) )
{
    $customdata->creatingachievementownerid = $personid;
} else
{
    $customdata->creatingachievementownerid = $currentperson->id;
}
// Сформируем форму
$form = new dof_im_achievementins_select_form($url, $customdata, 'post', '', ['id'=>'dof_im_achievementins_select_form']);
// Обработчик формы
$form->process();

// ФОРМА СОЗДАНИЯ ЦЕЛЕЙ
// Отображение формы создания цели
// Сформируем дополнительные данные
$customdata = new stdClass;
$customdata->dof = $DOF;
$customdata->id = $catid;
$customdata->addvars = $addvars;
if ( ! empty($personid) )
{
    $customdata->creatingachievementownerid = $personid;
} else
{
    $customdata->creatingachievementownerid = $currentperson->id;
}
// Сформируем форму
$goalform = new dof_im_achievements_goal_select_form($url, $customdata, 'post', '', ['id'=>'dof_im_goals_select_form']);
// Обработчик формы
$goalform->process();

// ФИЛЬТРАЦИЯ
$display_filter = $DOF->storage('config')->
    get_config_value('display_filter', 'im', 'achievements', $addvars['departmentid']);
if ( ! empty($display_filter) )
{
    // Сформируем дополнительные данные
    $customdata = new stdClass;
    $customdata->dof = $DOF;
    $customdata->filter = $filter;
    $customdata->addvars = $addvars;
    $customdata->personid = $personid;
    // Сформируем форму фильтрации
    $filterform = new dof_im_achievementins_filter_form($url, $customdata, 'post', '', ['id'=>'dof_im_achievementins_filter_form']);
    // Обработчик формы
    $filterform->process();
}

// ТАБЛИЦА ДОСТИЖЕНИЙ 
// Опции для получения\формирования таблицы достижений пользователя
$options = [
    'personid' => $personid, 
    'sort'=> $sort, 
    'dir' => $sortdir,
    'limitfrom' => $limitfrom,
    'limitnum' => $limitnum,
    'filter' => $filter,
    'addvars' => $addvars
];
// Получение данных о пользовательских достижениях
$achievementinsdata = $DOF->im('achievements')->get_achievementins($options);
// Число пользовательских достижений
$count = count($achievementinsdata);
// Получение html таблицы достижений
$achievementinstable = $DOF->im('achievements')->get_achievementinstable($achievementinsdata, $options);

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL, $headeroptions);

// СЛАЙДЕР ИЗОБРАЖЕНИЙ ДОСТИЖЕНИЙ ПОЛЬЗОВАТЕЛЯ
if ($DOF->im('achievements')->is_access('otslider_view', $addvars['departmentid'])) {
    print $DOF->im('achievements')->get_otslider_photo_by_criteria($personid, $addvars['departmentid']);
}

// Отображение блока информации о пользователе
$userinfo = $DOF->im('achievements')->get_user_info($personid, $addvars);
if ( ! empty($userinfo) )
{
    $description = $DOF->get_string('personal_info', 'achievements');
    print(dof_html_writer::tag('h4', $description));
    print($userinfo);
}

// Рейтинг
$system_rating_enabled = $DOF->storage('config')->
    get_config_value('system_rating_enabled', 'im', 'achievements', $addvars['departmentid']);
if ( $system_rating_enabled )
{// Рейтинг включен в подразделении
    $selectsaddvars = $addvars;
    $selectsaddvars['filter'] = $filter;
    $DOF->im('achievements')->get_rating_selects($personid, $currentperson, $selectsaddvars, $url);
}

// Отображение форм
$form->display();
$goalform->display();

if ( ! empty($display_filter) )
{
    // Отобразить форму фильтрации
    $filterform->display();
}

$pagination = $DOF->modlib('widgets')->pages_navigation('achievements', $count, $limitnum, $limitfrom);
$paginationaddvars = $addvars;
$paginationaddvars['limitfrom'] = $limitfrom;
$paginationaddvars['limitnum'] = $limitnum;
$paginationaddvars['filter'] = $filter;
$paginationhtml = $pagination->get_navpages_list('/my.php', $paginationaddvars);

//print($paginationhtml);

// Панель редактирования разделов достижений
print($achievementinstable);

print($paginationhtml);
// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>