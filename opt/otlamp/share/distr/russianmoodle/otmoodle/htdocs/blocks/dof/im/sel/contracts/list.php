<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
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
 * Панель управления приемной комиссии. Список договоров на обучение.
 *
 * @package    im
 * @subpackage sel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('lib.php');
require_once('form.php');

require_once($DOF->plugin_path('im','departments','/lib.php'));

// HTML-код старинцы
$html = '';

// Отображение сообщений на основе GET-параметров
$DOF->im('sel')->messages();

// Получение текущей персоны
$currentperson = $DOF->storage('persons')->get_bu(null, true);

// Проверка доступа к интерфейсу
if ( ! $DOF->storage('contracts')->is_access('view') )
{// Доступ к просмотру договоров закрыт
    $DOF->messages->add(
        $DOF->get_string('error_page_contracts_list_access_denied', 'sel'),
        'error'
    );
} else
{// Доступ открыт
    // Проверка лимитов договоров в подразделении
    if ( $DOF->storage('config')->get_limitobject('contracts', $addvars['departmentid']) )
    {// Создание договоров доступно
        $cancreate = $DOF->storage('contracts')->is_access('create');
        if ( $cancreate )
        {
            $url = $DOF->url_im('sel', '/contracts/edit_first.php', $addvars);
            $html .= dof_html_writer::link(
                $url,
                $DOF->get_string('newcontract', 'sel'),
                ['class' => 'btn button dof_button btn-secondary btn-sm']
            );
        }
    } else
    {// Достигнут лимит по договорам в подразделении
        $DOF->messages->add(
            $DOF->get_string('limit_message', 'sel'),
            'notice'
        );
    }
    
    // Ссылки на отчеты по действиям с договорами
    $links = '';
    $somevars = [];
    $somevars['departmentid'] = $addvars['departmentid'];
    $somevars['plugintype'] = 'im';
    $somevars['plugincode'] = 'sel';
    $somevars['code'] = 'contracts_status';
    $url = $DOF->url_im('reports', '/list.php', $somevars);
    $links .= dof_html_writer::link(
        $url,
        $DOF->get_string('link_status_report', 'sel'),
        ['class' => 'btn button dof_button btn-secondary btn-sm']
    );
    $somevars['code'] = 'contracts_department';
    $url = $DOF->url_im('reports', '/list.php', $somevars);
    $links .= dof_html_writer::link(
        $url,
        $DOF->get_string('link_depart_report', 'sel'),
        ['class' => 'btn button dof_button btn-secondary btn-sm']
    );
    $html .= dof_html_writer::div($links, 'mt-1');
    
    // Инициализация фильтра
    $url = $DOF->url_im('sel','/contracts/list.php', $addvars);
    $customdata = new stdClass();
    $customdata->dof = $DOF;
    $customdata->addvars = $addvars;
    $filter = new dof_im_sel_contracts_filter($url, $customdata);
    // Обработчик фильтра
    $filter->process();
    // Отображение фильтра
    $html .= $filter->render();
    
    // Получение идентификаторов договоров на основе фильтра
    $contractids = (array)$filter->get_contractsids();

    // Пагинация
    $pages = $DOF->modlib('widgets')->pages_navigation(
        'sel',
        count($contractids),
        $addvars['limitnum'],
        $addvars['limitfrom']
    );
    
    // Получение договоров
    $contracts = $filter->get_contracts(['id' => $contractids], $addvars['limitnum'], $addvars['limitfrom']);

    // Инициализация таблицы договоров
    $url = $DOF->url_im('sel', '/contracts/list.php', $addvars);
    $contractstable = $DOF->im('sel')->form_listeditor($contracts, $url, $addvars);
    // Обработчик массовых действий над договорами
    $contractstable->process();
    
    // Рендеринг формы
    $html .= $contractstable->render();
    // Добавление пагинации
    $html .= $pages->get_navpages_list('/contracts/list.php', $addvars);
}
    
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
				
// Выводим форму
echo $html;

$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>