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
 * Панель управления достижениями
 *
 * @package    im
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('lib.php');
require_once(__DIR__.'/plugins/usersfilter/form.php');

GLOBAL $PAGE;

// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
        $DOF->get_string('moderator_panel_title', 'achievements'),
        $DOF->url_im('achievements', '/moderator_panel.php'),
        $addvars
);

// Установим ссылку странице
$PAGE->set_url($DOF->url_im('achievements', '/moderator_panel.php'));

// Дефолтные параметры
$html = '';

// Проверяем, что есть параметр фильтра
$achievementcat = optional_param('achievement_category', null, PARAM_INT);

// Сортировка
$sort = optional_param('sort', '', PARAM_TEXT);
$direct = optional_param('direct', '', PARAM_TEXT);

// Пагинация
$limitnum = optional_param('limitnum', 50, PARAM_INT);
$limitfrom = optional_param('limitfrom', 1, PARAM_INT);

// Нормализация пагинации
if ( $limitnum < 1 )
{// Недопустимое значение числа отображаемых записей
    $limitnum = 50;
}
if ( $limitfrom < 1 )
{// Недопустимое значение страинцы
    $limitfrom = 1;
}

// Дополним массив параметрами для пагинации и сортировки
$addvars['limitfrom'] = $limitfrom;
$addvars['limitnum'] = $limitnum;
$addvars['sort'] = $sort;
$addvars['direct'] = $direct;

// Права доступа
$DOF->im('achievements')->require_access('control_panel');

//Стили для таблицы
$DOF->modlib('nvg')->add_css('im', 'achievements', '/styles/moderator_panel.css');
// Опции формирования таблицы разделов достижений
$options = ['addvars' => $addvars];

$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->addvars = $addvars;
$customdata->departmentid = $addvars['departmentid'];
$filterform = new dof_im_achievements_usersfilter_userform(NULL, $customdata);
$filterform->process();
// Получить данные фильтрации
$filterdata = $filterform->get_filter();
// Получим параметры фильтра и добавим в массив параметров
$filterform->add_get_params($additional_params);
if ( ! empty($additional_params) )
{
    $options['additional'] = $additional_params;
} else 
{
    $options['additional'] = [];
}
if ( isset($filterdata['persons']) )
{
    $options['persons'] = $filterdata['persons'];
}
if ( isset($filterdata['achievementins']) )
{
    $options['achievementins'] = $filterdata['achievementins'];
}
if ( ! empty($sort) )
{
    $options['sort'] = $sort;
}
if ( ! empty($direct) )
{
    $options['direct'] = $direct;
}

// Получить массив модерируемых персон
$moderationdata = $DOF->im('achievements')->get_moderation_data($options);
$count = count($moderationdata);
$moderationdata = array_slice($moderationdata, $limitfrom - 1, $limitnum, true);
// Таблица разделов
$moderationtable = $DOF->im('achievements')->get_moderation_table($moderationdata, $options);
$time_end = time();
$pagination = $DOF->modlib('widgets')->pages_navigation('achievements', $count, $limitnum, $limitfrom);
$paginationhtml = $pagination->get_navpages_list('/moderator_panel.php', array_merge($addvars, $options['additional']));

$html .= $paginationhtml;
$html .= $moderationtable;
$html .= $paginationhtml;

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

$filterform->display();

print($html);

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

