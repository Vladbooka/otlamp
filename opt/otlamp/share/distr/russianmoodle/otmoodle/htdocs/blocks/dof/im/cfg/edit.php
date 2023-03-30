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
 * Отображает форму добавления и редактирования. 
 * Если передан параметр id, 
 * то отображается редактирование, 
 * если не передан - добавление. 
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');
$conds = new stdClass;
$conds->departmentid = $addvars['departmentid'];
$conds->plugintype = $addvars['plugintype'];
$conds->plugincode = $addvars['plugincode'];
// готовим объект для вставки в форму
$customdata = new stdClass();
$customdata->id = $addvars['departmentid'];
$customdata->dof = $DOF;
// подключаем форму
$form = new dof_im_cfg_form($DOF->url_im('cfg','/edit.php',$addvars), $customdata);

/*
// принимаем id настройки (таблица config)
$id = optional_param('id', 0, PARAM_INT);
// проверка на существование записи в БД
if ( $id AND ! $DOF->storage('config')->is_exists($id) )
{
    print_error($DOF->get_string('notfound','cfg',$id));
}

// навигация
if ( $id )
{
     $DOF->modlib('nvg')->add_level($DOF->get_string('edit_cfg','cfg'), $DOF->url_im('cfg','/edit.php?id='.$id,$addvars));
}else
{
    $DOF->modlib('nvg')->add_level($DOF->get_string('new','cfg'), $DOF->url_im('cfg','/edit.php?id='.$id,$addvars));
}

// TODO права открыть
//проверяем доступ
/*
if ( $id )
{//проверка права редактировать подписку на курс
    $DOF->im('cfg')->require_access('edit', $id);
}else
{//проверка права создавать подписку на курс
    $DOF->im('cfg')->require_access('new');
}*/

$DOF->modlib('nvg')->add_level($DOF->get_string('edit_cfg','cfg'), $DOF->url_im('cfg','/edit.php',$addvars));
// есть ошибка-запомним, нет-то в обработчике сработает redirect 
$message = $form->process();
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
// будут ошибки - тут отобразятся они

echo  $message;

//Выведем название выбранного подразделения
if ( $addvars['departmentid'] )
{// получили id подразделения - выведем название и код
    $depname = $DOF->storage('departments')->get_field($addvars['departmentid'],'name').' ['.
               $DOF->storage('departments')->get_field($addvars['departmentid'],'code').']';
}else
{// нету - значит выводим для всех
    $depname = $DOF->get_string('all_departments', 'cfg');
}

// список настроек
$configs = $DOF->storage('config')->get_listing($conds);
// вспомогательный обект для содержания
$con = new stdClass();
$con->plugintype = '';
$con->plugincode = '';


$html = '';

// Заголовок - оглавление
$html .= dof_html_writer::div($DOF->get_string('content','cfg'), 'plugins-list-header');

// вывод метки для перемещения сразу вверх
$html .= dof_html_writer::div('','',['id'=>'top']);

// Сбор данных по плагинам
$pluginsdata = [];
foreach ($configs as $config)
{
    if ( $con->plugintype != $config->plugintype )
    {// начался другой тип плагина
        if( ! empty($con->plugintype) )
        {
            // формирование html по предыдущему типу плагина (уже собранному)
            $prevplugintypelink = dof_html_writer::div($pluginsdata[$con->plugintype]['link']);
            $prevplugintypelist = dof_html_writer::alist(
                $pluginsdata[$con->plugintype]['plugins'],
                [
                    'class' => 'cfg_list_plugins'
                ]
            );
            $pluginsdata[$con->plugintype] = $prevplugintypelink . $prevplugintypelist;
        }
        
        // формирование структуры для сбора данных по новому типу плагина 
        $pluginsdata[$config->plugintype] = [
            'link' => dof_html_writer::link(
                '#cfg_ptype_'.$config->plugintype, 
                $config->plugintype
            ),
            'plugins' => []
        ];
    }
    if ( $con->plugincode != $config->plugincode )
    {// новый плагин - добавляем в список
        $pluginsdata[$config->plugintype]['plugins'][] = dof_html_writer::link(
            '#cfg_pcode_' . $config->plugintype . '_' . $config->plugincode, 
            $config->plugincode
        );
    }
    
    $con = $config;
}
// формирование html по предыдущему типу плагина (уже собранному)
$prevplugintypelink = dof_html_writer::div($pluginsdata[$con->plugintype]['link']);
$prevplugintypelist = dof_html_writer::alist(
    $pluginsdata[$con->plugintype]['plugins'],
    ['class' => 'cfg_list_plugins']
);
$pluginsdata[$con->plugintype] = $prevplugintypelink . $prevplugintypelist;

// формирование html по всем типам плагинов
$html .= html_writer::start_tag('ol', ['class'=>'cfg_list_plugintypes']);
foreach ($pluginsdata as $plugin=>$link) {
    $plugintypecols = 1;
    switch($plugin)
    {
        case 'im':
            $plugintypecols = 2;
            break;
        case 'storage':
            $plugintypecols = 4;
            break;
        default:
            break;
    }
    $html .= html_writer::tag('li', $link, [
        'data-cols' => $plugintypecols
    ]);
}
$html .= html_writer::end_tag('ol');
// $html .= dof_html_writer::alist($pluginsdata, ['class'=>'cfg_list_plugintypes']);

// вывод наименования подразделения
$html .= dof_html_writer::div($depname, 'cfg-depname');

// вывод формы
$html .= $form->render();

// вывод метки для перемещения сразу вниз
$html .= dof_html_writer::div('','',['id'=>'down']);

echo $html;



//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>