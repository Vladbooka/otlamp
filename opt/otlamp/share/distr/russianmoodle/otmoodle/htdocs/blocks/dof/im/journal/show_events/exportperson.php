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

$personid             = optional_param("personid", NULL, PARAM_INT);
$date = [];
$date['date_from']    = optional_param("date_from", time(), PARAM_INT);
$date['date_to']      = optional_param("date_to", time(), PARAM_INT);

// в зависимости от статуса пользователя получаем определенный набор данных
if( ! is_null($personid) )
{
    if ( $DOF->storage('eagreements')->is_exists(['personid' => $personid]) )
    {// считаем, что персона - учитель
        $teacherpersonid = $personid;
        $studentpersonid = 0;
        $persontype = 'teacher';
    }
    elseif ( $DOF->storage('contracts')->is_exists(['studentid' => $personid]) )
    {// считаем, что персона - студент
        $teacherpersonid = 0;
        $studentpersonid = $personid;
        $persontype = 'student';
    }
} 

if( ! isset($teacherpersonid) || ! isset($studentpersonid) )
{
    $DOF->messages->add($DOF->get_string('export_person_xls_person_not_found', 'journal'), DOF_MESSAGE_ERROR);
    
    // Печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // Печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
    exit;
}


//подключаем методы получения списка журналов
$events = new dof_im_journal_show_events($DOF, $addvars['departmentid']);
//инициализируем начальную структуру
$events->set_data($date, $teacherpersonid, $studentpersonid);
// получаем данные для экспорта
$export_data = $events->get_data_for_export();


if( empty($export_data) )
{
    $DOF->messages->add($DOF->get_string('export_person_xls_lessons_not_found', 'journal'), DOF_MESSAGE_WARNING);
    
    // Печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // Печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
    exit;
}

$filename = 'person_events_' . $addvars['departmentid'] . '_' . $personid . '_' 
            .dof_userdate($date['date_from'],'%Y%m%d', 99, false) . '_' 
            .dof_userdate($date['date_to'],'%Y%m%d', 99, false);
$sheetname = dof_userdate($date['date_from'],'%d.%m.%Y', 99, false).'-'
            .dof_userdate($date['date_to'],'%d.%m.%Y', 99, false);

/* @var dof_cross_format_table_cell_style $defaultstyle */
$defaultstyle = $DOF->modlib('widgets')->cross_format_table_cell_style();
$defaultstyle->set_font_size(13);
$defaultstyle->set_vertical_align('middle');

$runningtilestyle = clone($defaultstyle);
$runningtilestyle->set_text_align('center');
$runningtilestyle->set_border_width(0);

$oddstyle = clone($defaultstyle);
$oddstyle->set_background_color(233, 233, 233);

$headerstyle = clone($defaultstyle);
$headerstyle->set_font_weight(700);
$headerstyle->set_background_color(200, 200, 200);

/* @var dof_cross_format_table $cft */
$cft = $DOF->modlib('widgets')->cross_format_table($filename, $defaultstyle);


// ФИО персоны, по которой произведена выгрузка
$cft->add_cell($DOF->get_string(
        'export_person_xls_'.$persontype, 
        'journal',
        $DOF->storage('persons')->get_fullname($personid)
    ))
    ->set_colspan(5)
    ->set_text_align('left')
    ->set_border_width(0);

// Даты выборки для отчета
$cft->add_cell($DOF->get_string(
        'export_person_xls_dates',
        'journal',
        dof_userdate($date['date_from'], '%d.%m.%Y', 99, false).' - '.dof_userdate($date['date_to'], '%d.%m.%Y', 99, false)
    ), 0, 6)
    ->set_colspan(4)
    ->set_text_align('right')
    ->set_border_width(0);

$cft->increase_rownum(2);

$cft->set_style($headerstyle, null, null, null, $cft->get_cellnum()+9);
$cft->add_row_of_cells([
    $DOF->get_string('export_person_xls_lessonnum', 'journal'),// № (Номер урока)
    $DOF->get_string('export_person_xls_date', 'journal'),// Дата, время
    $DOF->get_string('export_person_xls_item', 'journal'),// Предмет
    $DOF->get_string('export_person_xls_event_form', 'journal'),// Форма
    $DOF->get_string('export_person_xls_event_lesson_place', 'journal'),// Номер кабинета
    $DOF->get_string('export_person_xls_theme', 'journal'),// Что пройдено
    $DOF->get_string('export_person_xls_teacher_name', 'journal'),// ФИО учителя
    $DOF->get_string('export_person_xls_student_name', 'journal'),// ФИО ученика
    $DOF->get_string('export_person_xls_student_present', 'journal'),// Присутствие
    $DOF->get_string('export_person_xls_student_grade', 'journal')// Оценка
]);
    
$lessonnums = [];
$agenames = [];
foreach ($export_data as $event)
{// обход по урокам
    if ( ! empty($event['students']) )
    {// на уроке были ученики - начинаем запись в файл
        foreach ($event['students'] as $student)
        {// создаем запись для каждого ученика
            
            if( ! in_array($event['agename'], $agenames))
            {
                $agenames[] = $event['agename'];
            }
            
            if( ! array_key_exists($event['event_id'], $lessonnums) )
            {
                $lessonnums[$event['event_id']] = count($lessonnums)+1;
            }
            $style = (count($lessonnums) % 2 == 0 ? $oddstyle : $defaultstyle ); 
            
            
            $cft->set_style($style, null, null, null, $cft->get_cellnum()+9);
            $cft->add_row_of_cells([
                $lessonnums[$event['event_id']],// № (Номер урока)
                $event['date'],// Дата, время
                $event['item'],// Предмет
                $event['form'],// Форма,
                $event['lesson_place'],// Номер кабинета
                str_replace(["\r\n", "\n"], " / ", trim($event['theme'])),// Что пройдено
                $event['teacher_name'],// ФИО учителя
                $student['student_name'],// ФИО ученика
                $student['student_present'],// Присутствие
                $student['student_grade']// Оценка
            ]);
        }
    }
}

// установка собранных в цикле периодов, попавших в выборку
$cft->add_cell(
        $DOF->get_string('export_person_xls_agenames', 'journal', implode(', ', $agenames)),
        0, 5
    )
    ->set_style($runningtilestyle);

// УСТАНОВКА ШИРИН СТОЛБЦОВ
$cft->set_columns_width([4, 15, 30, 7, 12, 40, 25, 18, 12, 8]);

$cft->print_xls(
    $sheetname,
    [
        'sheet__orientation' => 'L',
        'sheet__papersize' => 'A4',
        'sheet__fit_to_height' => 0, // подгонять по высоте не надо, пусть будет много страниц (мы не знаем сколько)
        'sheet__fit_to_width' => 1, // подогнать по ширине
        'sheet__rows_repeat_at_top' => [// строки, повторяющиеся на каждой странице сверху
            0, // номер строки 
            3 // количество строк
        ]
    ]
);

?>