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
 * Интерфейс история статусов
 *
 * @package    im
 *
 * @package    statushistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// Подключение библиотек
require_once('lib.php');
require_once('form.php');
// HTML-код старинцы
$html = '';
// Проверка доступа к интерфейсу
if($DOF->im('statushistory')->is_access('view') === false)
{// Доступ к просмотру статусов закрыт
    $DOF->messages->add(
        $DOF->get_string('accessdenied', 'statushistory'),
        'error'
        );
}else
{// Доступ открыт
    // Инициализация фильтра
    $url = $DOF->url_im('statushistory','/index.php', $addvars);
    $customdata = new stdClass();
    $customdata->dof = $DOF;
    $customdata->plugintype = $DOF->storage('statushistory')->get_exists_statushistory_plugintypes();
    $customdata->plugincode = $DOF->storage('statushistory')->get_exists_statushistory_plugincodes();
    // получение фильтра из get/post запроса
    $customdata->defaults['timedefault'] = time();
    $customdata->defaults['plugintype'] = optional_param('plugintype', 'notselected', PARAM_TEXT);
    $customdata->defaults['plugincode'] = optional_param('plugincode', 'notselected', PARAM_TEXT);
    $customdata->defaults['objectid']   = optional_param('objectid', null, PARAM_INT);
    $customdata->defaults['datestart']  = optional_param(
        'startdate', $customdata->defaults['timedefault'] - (86400 * 92), PARAM_INT
        );
    $customdata->defaults['datefinish'] = optional_param('finishdate', null, PARAM_INT);
    $customdata->defaults['sort'] = optional_param('sort', 'statusdate', PARAM_TEXT);
    $customdata->defaults['sdir'] = optional_param('sdir', 'desc', PARAM_TEXT);
    // Получение числа записей по умолчанию
    $limitnumdefault = (int)$DOF->modlib('widgets')->get_limitnum_bydefault($addvars['departmentid']);
    $customdata->limitnum = optional_param('limitnum', $limitnumdefault, PARAM_INT);
    // Получение смещения
    $customdata->limitfrom  = optional_param('limitfrom', '1', PARAM_INT);
    
    $filter = new dof_im_statushistory_form($url, $customdata);
    // Обработчик фильтра возвращает статусы и conditions
    $dat = $filter->process();
    // Отображение фильтра
    $html .= $filter->render();
    
    // Формирование GET-параметров
    $addvars['limitnum'] = $customdata->limitnum;
    $addvars['limitfrom'] = $customdata->limitfrom;
    $addvars['plugintype'] = $dat['conditions'][0];
    $addvars['plugincode'] = $dat['conditions'][1];
    $addvars['objectid'] = $dat['conditions'][2];
    $addvars['startdate'] = $dat['conditions'][3];
    $addvars['finishdate'] = $dat['conditions'][4];
    $addvars['sort'] = $customdata->defaults['sort'];
    $addvars['sdir'] = $customdata->defaults['sdir'];
    
    // Пагинация
    $pages = $DOF->modlib('widgets')->pages_navigation(
        'statushistory', null, $customdata->limitnum, $customdata->limitfrom
        );
    if(!empty($dat)){
        // Инициализация таблицы истории статусов
        $html .= $DOF->im('statushistory')->make_table($dat['data'], $addvars);
        
        // Сформируем html код пагинации
        $pages->count = $DOF->storage('statushistory')->count_statuses(
            $dat['conditions'][0],
            $dat['conditions'][1],
            $dat['conditions'][2],
            $dat['conditions'][3],
            $dat['conditions'][4]
            );
        
        $html .= $pages->get_navpages_list('/index.php', $addvars);
    }
}
// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
// Выводим контент
echo $html;
// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
