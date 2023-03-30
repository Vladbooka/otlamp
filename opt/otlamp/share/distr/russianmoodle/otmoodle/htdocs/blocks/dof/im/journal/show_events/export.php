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
 * Список событий. Экспорт списка событий.
 *
 * @package    im
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('lib.php');

// Проверка прав доступа
$DOF->im('journal')->require_access('export_events');

$personid             = optional_param("personid", 0, PARAM_INT);
$date['date_from']    = optional_param("date_from", time(), PARAM_INT);
$date['date_to']      = optional_param("date_to", time(), PARAM_INT);

// в зависимости от статуса пользователя получаем определенный набор данных
if ( $personid == 0 )
{// считаем, что персона учитель
    //подключаем методы получения списка журналов
    $events = new dof_im_journal_show_events($DOF, $addvars['departmentid']);
    //инициализируем начальную структуру
    $events->set_data($date);
    //получаем список журналов
    $rez = $events->get_table_events('time', true);
}
if ( ($DOF->storage('eagreements')->is_exists(array('personid'=>$personid))) AND ($personid != 0) )
{// считаем, что персона учитель
    //подключаем методы получения списка журналов
    $events = new dof_im_journal_show_events($DOF,$addvars['departmentid']);
    //инициализируем начальную структуру
    $events->set_data($date, $personid);
    //получаем список журналов
    $rez = $events->get_table_events('time', true);
}
if ( ($DOF->storage('contracts')->is_exists(array('studentid'=>$personid))) AND ($personid != 0) )
{// считаем, что персона студент
    //подключаем методы получения списка журналов
    $events = new dof_im_journal_show_events($DOF,$addvars['departmentid']);
    //инициализируем начальную структуру
    $events->set_data($date, null, $personid);
    //получаем список журналов
    $rez = $events->get_table_events('time', true);
}

// получаем данные для экспорта
$export_data = $events->get_data_for_export();
$filename = 'event('.dof_userdate($date['date_from'],'%Y-%m-%d').'-'
        .dof_userdate($date['date_from'],'%Y-%m-%d').')('
        .dof_userdate(time(),'%Y-%m-%d').')('.$addvars['departmentid'].').csv';

$export_path = $DOF->plugin_path('im', 'journal', '/dat/'.$filename);

// открываем файл
$fp = fopen($export_path, 'w');

// массив с заголовками
$title = [
    $DOF->get_string('export_csv_event_id',  'journal'),
    $DOF->get_string('export_csv_date',             'journal'),
    $DOF->get_string('export_csv_item',             'journal'),
    $DOF->get_string('export_csv_theme',            'journal'),
    $DOF->get_string('export_csv_teacher_name',     'journal'),
    $DOF->get_string('export_csv_teacher_enumber',  'journal'),
    $DOF->get_string('export_csv_event_form',       'journal'),
    $DOF->get_string('export_csv_lesson_place',     'journal'),
    $DOF->get_string('export_csv_student_name',     'journal'),
    $DOF->get_string('export_csv_student_contract', 'journal'),
    $DOF->get_string('export_csv_student_present',  'journal'),
    $DOF->get_string('export_csv_student_grade',    'journal'),
    $DOF->get_string('export_csv_event_statusname', 'journal'),
    $DOF->get_string('export_csv_event_notes', 'journal'),
];

// пропишем в файл заголовки
fputcsv($fp, $title, ';');
foreach ($export_data as $event)
{// обход по урокам
    if ( !empty($event['students']) )
    {// на уроке были ученики - начинаем запись в файл
        foreach ($event['students'] as $student)
        {// создаем запись для каждого ученика
            $line = array($event['event_id'], 
                $event['date'], 
                $event['item'], 
                $event['theme'],
                $event['teacher_name'],
                (string)$event['teacher_enumber'],
                $event['form'],
                $event['lesson_place'],
                $student['student_name'],
                $student['student_contract'],
                $student['student_present'],
                $student['student_grade'],
                $event['event_statusname'],
                $event['notes']
            );
            fputcsv($fp, $line, ';');
        }
    }
}
fclose($fp);
// Прописываем заголовки для скачивания файла
header('Content-Description: File Transfer');
header("Content-Type: application/octet-stream");
header('Content-disposition: extension-token; filename=' . $filename);
readfile($export_path);

?>