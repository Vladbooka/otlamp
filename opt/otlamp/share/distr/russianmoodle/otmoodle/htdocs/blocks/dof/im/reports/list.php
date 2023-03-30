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
 * Отображение списка объектов. 
 * Если соответствующие поля есть в справочнике. 
 * страница принимает параметры departmentid, status, 
 * по необходимости, могут быть другие, 
 * специфичные для справочника. Если они переданы, 
 * то выводится не весь список, а только его часть, 
 * удовлетворяющая условию. 
 * Параметры limitfrom и limitnum предназначены для ограничения количества 
 * выводимых записей. Если limitnum задан, то внизу выводится указатель страниц. 
 * Сам указатель страниц должен быть реализован в виде метода в плагине 
 * modlib widgets, которому передаются значения $code (код плагина im для ссылки), 
 * $adds, $vars, $limitfrom, $limitnum и $count, 
 * на основании которых возвращается html-код указателя. 
 * Ссылки генерируются с помощью $DOF->url_im(). 
 * Если для этого типа записей предусмотрен поиск, 
 * то форма поиска отображается над списком. 
 * Для результатов поиска действуют те же фильтры, что и для вывода списка. 
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');
// создаем объект, который будет содержать будущие условия выборки
$conds = new stdClass();
// id учебного подразделения в таблице departmrnts
//выводятся классы с любым departmentid, если ничего не передано
$conds->departmentid = optional_param('departmentid', null, PARAM_INT);
// 
$conds->plugintype   = required_param('plugintype', PARAM_TEXT);
$conds->plugincode   = required_param('plugincode', PARAM_TEXT);
$conds->code         = required_param('code', PARAM_TEXT);
// статус учебного периода. Выводятся периоды 
// с любым статусом, если ничего не передано
$conds->status       = optional_param('status', '', PARAM_ALPHA);
// ловим номер страницы, если его передали
// какое количество строк таблицы выводить на экран
$limitnum = $DOF->modlib('widgets')->get_limitnum_bydefault();
$limitnum     = optional_param('limitnum', $limitnum, PARAM_INT);
// начиная с какого номера записи показывать ее
$limitfrom    = optional_param('limitfrom', '1', PARAM_INT);
$addvars['sort'] = optional_param('sort','requestdate', PARAM_TEXT);
$addvars['dir'] = optional_param('dir','desc', PARAM_TEXT);

//добавление уровня навигации
// TODO раньше тут чтояло $conds
//$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'reports'), $DOF->url_im('reports','/list.php'),$addvars);
$customdata = new stdClass();
$customdata->dof  = $DOF;
$customdata->addvars = $addvars;
$customdata->plugintype   = $conds->plugintype;
$customdata->plugincode   = $conds->plugincode;
$customdata->code         = $conds->code;

$fullname = $customdata->plugintype.'_'.$customdata->plugincode.'_'.$customdata->code;
if ( ! $DOF->storage('reports')->is_access('view_report_'.$fullname) )
{
    $DOF->storage('reports')->require_access('view_report');
}
//выводим форму выбора даты
$depchoose = new dof_im_journal_report_form($DOF->url_im('reports','/list.php',
                    $addvars), $customdata);

$dispay = new dof_im_reports_display($DOF,$conds->departmentid,$addvars);    
$reportcl = $dispay->report($conds->plugintype,$conds->plugincode,$conds->code);

$depchoose->process();

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

//получаем список групп
// массивы в PHP нумеруются с нуля, а наши страницы - с 1, 
//поэтому от стартового значения отнимем единицу.
// подключаем класс для вывода страниц
$sort = array();
$sort[$addvars['sort']] = $addvars['sort'];
$sort['dir'] = $addvars['dir'];
$pages = $DOF->modlib('widgets')->pages_navigation('reports',null,$limitnum, $limitfrom);
$list = $DOF->storage('reports')->get_listing($conds,$pages->get_current_limitfrom()-1, 
                                      $pages->get_current_limitnum(),$sort);

if ( $DOF->storage('reports')->is_access('request_report') OR
     $DOF->storage('reports')->is_access('request_report_'.$fullname) )
{//проверяем полномочия на заказ отчета
    $depchoose->display();
}

// получаем html-код таблицы с группами
$reports = $dispay->get_table_list($list);
    
if ( ! $reports )
{// Не найдено ни одного отчета
    if ( ($conds->plugintype == 'im') && ($conds->plugincode == 'sel') )
    {// Отчеты по смене статусов/подразделений договоров
        if ( ($conds->code == 'contracts_status') )
        {// Нету отчетов по смене статусов
            print('<p align="center">(<i>'.$DOF->
                    get_string('no_data_change_status', 'sel').'</i>)</p>');
        }
        elseif ( ($conds->code == 'contracts_department') )
        {// Нету отчетов по смене статусов
            print('<p align="center">(<i>'.$DOF->
                get_string('no_data_change_depart', 'sel').'</i>)</p>');
        }
    }
    else
    {// Остальные отчеты
        print('<p align="center">(<i>'.$DOF->
                get_string('no_agroups_found', 'agroups').'</i>)</p>');
    }
}
else
{//есть группы
    // выводим таблицу с учебными группами
    echo '<br>'.$reports;
    
    // помещаем в массив все параметры страницы, 
    //чтобы навигация по списку проходила корректно
    $vars = array('limitnum'  => $pages->get_current_limitnum(),
                  'limitfrom' => $pages->get_current_limitfrom(),
                  'sort'      => $addvars['sort'],
                  'dir'       => $addvars['dir']);
                  
    // добавляем все необходимые условия фильтрации
    $vars = array_merge($vars, (array)$conds);
    // составим запрос для извлечения количества записей
    $selectlisting = $DOF->storage('reports')->get_select_listing($conds);
    // посчитаем общее количество записей, которые нужно извлечь
    $pages->count = $DOF->storage('reports')->count_records_select($selectlisting);
    // выводим строку со списком страниц
    $pagesstring = $pages->get_navpages_list('/list.php', $vars);
    echo $pagesstring;
}
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>