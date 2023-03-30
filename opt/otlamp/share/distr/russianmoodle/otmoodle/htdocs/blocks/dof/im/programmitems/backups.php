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
require_once('lib.php');

// навигация на интерфейс всех дисциплин
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('title', 'programmitems'), 
    $DOF->url_im('programmitems'), '/list.php', 
    $addvars
);

// получение идентификатора дисциплины
$pitemid = required_param('id', PARAM_INT);

// получение объекта дисциплины
$pitem = $DOF->storage('programmitems')->get_record(['id' => $pitemid], '*', MUST_EXIST);

// опции удаления файла
$delete = optional_param('delete', false, PARAM_BOOL);
$filename = optional_param('file', 0, PARAM_INT);

// право на удаление бэкапов
$hasright = $DOF->storage('programmitems')->is_access('edit:delete_backups', null, null, $pitem->departmentid);
if ( $hasright && ! empty($filename) && ! empty($delete) )
{
    // удаление версии бэкапа
    $DOF->send_event(
            'storage', 
            'programmitems', 
            'delete_backupfile',
            $pitemid,
            [
                'filearea' => 'im_programmitems_programmitem_coursetemplate',
                'itemid' => $pitemid,
                'filepath' => '/',
                'filename' => $filename . '.mbz'
            ]);
}

// проверка доступа
$DOF->storage('programmitems')->require_access('view');

// получение объекта дисциплины
$pitem = $DOF->storage('programmitems')->get_record(['id' => $pitemid], '*', MUST_EXIST);

// навигация - дисциплина
$DOF->modlib('nvg')->add_level($pitem->name.'['.$pitem->code.']', $DOF->url_im('programmitems','/view.php?pitemid='.$pitemid,$addvars));

$html = '';
$html .= dof_html_writer::link(
        $DOF->url_im('programmitems', '/view.php?pitemid='.$pitem->id, $addvars),
        $DOF->get_string('link_back', 'programmitems'),
        ['class' => 'btn']);

// получение массива бэкапов
$mdlcoursebackups = $DOF->im('programmitems')->get_pitem_mdlcourse_backups($pitem);
if ( ! empty($mdlcoursebackups) )
{
    // список согласованных версий
    $table = new html_table();
    $table->head = [
        $DOF->get_string('mastercourse_version_header', 'programmitems'),
        $DOF->get_string('mastercourse_actions', 'programmitems')
    ];
    
    foreach ( $mdlcoursebackups as $file )
    {
        $table->data[] = [
            dof_html_writer::link($file->url, $file->filename),
            ($hasright ? $DOF->modlib('ig')->icon('delete', $DOF->url_im('programmitems','/backups.php?id='.$pitemid, $addvars + ['delete' => 1, 'file' => $file->version])) : '')
        ];
    }
    
    $html .= dof_html_writer::table($table);
} else
{
    $html .= dof_html_writer::tag('h3', $DOF->get_string('empty_backups', 'programmitems'));
}

// шапка
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

echo $html;

// подвал
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
