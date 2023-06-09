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
 * Ведомость оценок по подписке персоны. Точка входа в сабинтерфейс.
 * 
 * @package    im
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('lib.php');
require_once('form.php');

$html = '';

// Получение GET-параметров
// Временной интервал
$timestart = optional_param('timestart', NULL, PARAM_INT);
$timeend = optional_param('timeend', NULL, PARAM_INT);
$view_type = optional_param('viewtype', '00', PARAM_ALPHANUM);
// Получение числа записей по умолчанию
$limitnumdefault = (int)$DOF->modlib('widgets')->get_limitnum_bydefault($addvars['departmentid']);
$limitnum = optional_param('limitnum', $limitnumdefault, PARAM_INT);
// Получение смещения
$limitfrom  = optional_param('limitfrom', '1', PARAM_INT);
// Нормализация
if ( $limitnum < 1 )
{
    $limitnum = $limitnumdefault;
}
if ( $limitfrom < 1 )
{
    $limitfrom = 1;
}
if ( ! isset($view_type{0}) )
{
    $view_type{0} = '0';
}
if ( ! isset($view_type{1}) )
{
    $view_type{1} = '0';
}


// Формирование GET-параметров
if ( is_int($timestart) )
{// Указан начальный интервал
    $addvars['timestart'] = $timestart;
}
if ( is_int($timeend) )
{// Указан конечный интервал
    $addvars['timeend'] = $timeend;
}
// Тип отображения
$addvars['viewtype'] = $view_type;

// Формирование URL формы
$url = $DOF->url_im('journal','/personsbc_gradeslist/index.php', $addvars);

// Формирование дополнительных данных
$customdata = new stdClass;
$customdata->dof = $DOF;
$customdata->addvars = $addvars;

// Форма сохранения подразделения
$form = new dof_im_journal_pbcgl_sourceselect($url, $customdata);

// Обработчик формы
$form->process();

if ( ! is_null($personbc) || ! is_null($agroupid) )
{// Указаны данные для формирования таблицы

    // Получение доступных подписок на программы
    $programmbcs = $DOF->im('journal')->get_available_programmbcs($addvars);
    
    // Получение массива доступных подписок на предмето-классы
    $cpasseds = $DOF->im('journal')->get_cpasseds_by_programmbcs($programmbcs, $addvars);

    // Число элементов для пагинации
    $fullcount = 0;
    // Отображение в зависимости от viewtype
    switch ($view_type{0})
    {
        // Группировка оценок по подпискам на предмето-класс(cpasseds)
        case '0' :
            // Подсчет для пагинации
            if ( ! empty($cpasseds) )
            {// Суммарное число строк в таблицах
                $fullcount = count($cpasseds);
            }

            // Формирование среза с учетом пагинации
            $cpasseds = array_slice($cpasseds, $limitfrom - 1, $limitnum);
            
            break;
        // Группировка оценок по предмето-класам(cstreams)
        case '1' :
            // Получение предмето-классов, сгруппированных по подписке на программу
            $cstreams = $DOF->storage('cpassed')->get_elements_set_by_cpasseds($cpasseds, 'cstreamid', ['grouping' => 'programmsbcid']);
            if ( ! empty($cstreams) )
            {// Суммарное число строк в таблицах - это сумма всех сгруппированных предмето-классов
                foreach ( $cstreams as $group => $groupcs )
                {
                    $fullcount += count($cstreams[$group]);
                }
            }
            // Получение среза предмето-классов
            $lf = $limitfrom - 1;
            $ln = $limitnum;
            $cstreams = dof_array_slice($cstreams, $lf, $ln);
            
            // Формирование среза подписок
            $filteredcpasseds = [];
            foreach ( $cstreams as $programmbcid => $cstream )
            {
                foreach ( $cpasseds as $cid => $cpassed )
                {
                    if ( $cpassed->programmsbcid == $programmbcid && isset($cstream[$cpassed->cstreamid]) )
                    {
                        $filteredcpasseds[$cid] = $cpassed;
                    }
                }
            }
            $cpasseds = $filteredcpasseds;
            break;
        // Группировка оценок по дисциплине(programmitem)
        case '2' :
            // Получение дисциплин, сгруппированных по подписке на программу
            $programmitems = $DOF->storage('cpassed')->get_elements_set_by_cpasseds($cpasseds, 'programmitemid', ['grouping' => 'programmsbcid']);
            if ( ! empty($programmitems) )
            {// Суммарное число строк в таблицах - это сумма всех сгруппированных дисциплин
                foreach ( $programmitems as $group => $grouppgi )
                {
                    $fullcount += count($programmitems[$group]);
                }
            }
            
            // Получение среза дисциплин
            $lf = $limitfrom - 1;
            $ln = $limitnum;
            $programmitems = dof_array_slice($programmitems, $lf, $ln);
            
            // Формирование среза подписок
            $filteredcpasseds = [];
            foreach ( $programmitems as $programmbcid => $programmitems )
            {
                foreach ( $cpasseds as $cid => $cpassed )
                {
                    if ( $cpassed->programmsbcid == $programmbcid && isset($programmitems[$cpassed->programmitemid]) )
                    {
                        $filteredcpasseds[$cid] = $cpassed;
                    }
                }
            }
            $cpasseds = $filteredcpasseds;
            break;
    }
    
    // Формирование ведомости
    $options = [];
    $options['addvars'] = $addvars;
    $options['timestart'] = $timestart;
    $options['timeend'] = $timeend;
    $options['view_type'] = $view_type;
    
    $html .= $DOF->im('journal')->personbc_gradelist_table($cpasseds, $options);
    
    $addvars['limitnum'] = $limitnum;
    $addvars['limitfrom'] = $limitfrom;
    // Пагинация
    $pages = $DOF->modlib('widgets')->pages_navigation('journal', $fullcount, $limitnum, $limitfrom);
    $html .= $pages->get_navpages_list('/personsbc_gradeslist/index.php', $addvars);
}

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Отображение сообщений
$DOF->messages->display();
// Отображение формы
$form->display();
// Отображение контента
print($html);

// Печать подвала страницы
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>