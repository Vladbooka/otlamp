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

// Подключаем библиотеки
require_once('../lib.php');

$agroupid = required_param('agroupid', PARAM_INT);
$html = '';


if ( $DOF->storage('agroups')->is_access('view_itoggrades', $agroupid) )
{
    $agroupgradesdata = $DOF->im('agroups')->get_itog_grades_data($agroupid);
    
    $options = [
        'format' => 'html'
    ];
    $agroupgradesview = $DOF->im('agroups')->render_itog_grades($agroupgradesdata, $options);
    $html .= $agroupgradesview;
}

$DOF->modlib('nvg')->add_level(
    $DOF->get_string('title', 'agroups'),
    $DOF->url_im('agroups', '/list.php'),
    $addvars
);
$addvars['agroupid'] = $agroupid;
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('agroup_itog_grades', 'agroups'),
    $DOF->url_im('agroups', '/itoggrades/'),
    $addvars
);

$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

echo $html;

$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);