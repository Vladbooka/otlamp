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
require_once($DOF->plugin_path('im','cstreams','/form.php'));
$html = '';
// создаем объект, который будет содержать будущие условия выборки
$conds = new stdClass();
// id учебного подразделения в таблице departments
$conds->departmentid   = optional_param('departmentid', 0, PARAM_INT);
//id программы 
$conds->programmid     = optional_param('programmid', null, PARAM_INT);
//id предмета 
$conds->programmitemid = optional_param('programmitemid', null, PARAM_INT);
//id предмета 
$conds->appointmentid  = optional_param('appointmentid', null, PARAM_INT);
//id академической группы 
$conds->agroupid       = optional_param('agroupid', null, PARAM_INT);
//id ученика 
$conds->personid       = optional_param('personid', null, PARAM_INT);
// статус предмето-потока
$conds->status         = optional_param('status', '', PARAM_ALPHA);
// учебный период
$conds->ageid          = optional_param('ageid', null, PARAM_INT);
// учебный период
$conds->subsalfactor   = optional_param('subsalfactor', 'none', PARAM_TEXT);
// ловим номер страницы, если его передали
// какое количество строк таблицы выводить на экран
$limitnum  = optional_param('limitnum', $DOF->modlib('widgets')->get_limitnum_bydefault(), PARAM_INT);
// начиная с какого номера записи показывать ее
$limitfrom = optional_param('limitfrom', '1', PARAM_INT);

$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'cstreams'), 
                     $DOF->url_im('cstreams','/list.php'),$addvars);
//вывод на экран

//проверяем доступ
$DOF->storage('cstreams')->require_access('view');


// подключаем класс для вывода страниц
$pages = $DOF->modlib('widgets')->pages_navigation('cstreams',null,$limitnum, $limitfrom);
//получаем список потоков
// массивы в PHP нумеруются с нуля, а наши страницы - с 1, 
//поэтому от стартового значения отнимем единицу.

$list = $DOF->storage('cstreams')->get_listing($conds,$pages->get_current_limitfrom()-1, 
                                      $pages->get_current_limitnum());
                                                                           
// получаем html-код таблицы с потоками
$cstreams = $DOF->im('cstreams')->showlist($list,$conds);

//покажем ссылку на создание нового потока
if ( $DOF->storage('cstreams')->is_access('create') )
{// если есть право на создание потока 
    if ( $DOF->storage('config')->get_limitobject('cstreams',$conds->departmentid) )
    {
        $link = '<a href='.$DOF->url_im('cstreams','/edit.php',$conds).'>'.
                $DOF->get_string('newcstream', 'cstreams').'</a>';
        $html .= '<br>'.$link.'<br>';
    }else 
    {
        $link =  '<span style="color:silver;">'.$DOF->get_string('newcstream', 'cstreams').
        	' ('.$DOF->get_string('limit_message','cstreams').')</span>';
        $html .= '<br>'.$link.'<br>'; 
    } 
}

if ( ! $cstreams )
{// не найдено ни одной потока
    $html .= '<p align="center">(<i>'.$DOF->
            get_string('no_cstreams_found', 'cstreams').'</i>)</p>';
}else
{//есть потоки
    
    // выводим таблицу с учебными потоками
    $html .= '<br>'.$cstreams;
    
    // помещаем в массив все параметры страницы, 
    //чтобы навигация по списку проходила корректно
    $vars = array('limitnum'       => $pages->get_current_limitnum(),
                  'limitfrom'      => $pages->get_current_limitfrom());
    // Добавление параметров фильтрации
    if ( ! empty($conds) )
    {
        foreach ( $conds as $filtername => $filterval )
        {
            if ( ! empty($filterval) )
            {
                $vars[$filtername] = $filterval;
            }
        }
    }
    // посчитаем общее количество записей, которые нужно извлечь
    $pages->count = $DOF->storage('cstreams')->get_listing($conds,$pages->get_current_limitfrom(), 
                        $pages->get_current_limitnum(),'','c.*',true);
    // выводим строку со списком страниц
    $pagesstring = $pages->get_navpages_list('/list.php', $vars);
    $html .= $pagesstring;
}


//создаем форму поиска
//объект данных для передачи в форму
$cdata = new stdClass();
$cdata->dof = $DOF;
//инициализация формы
$search = new dof_im_cstreams_search_form(null, $cdata);
//установка значений формы
$search->set_data($conds);
//вывод формы на экран
$html .= $search->render();

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
echo $html;
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>