<?php

// Подключаем библиотеки
require_once('lib.php');

$cstreamid = optional_param('cstreamid', NULL, PARAM_INT);
$lessons_per_sheet = optional_param('lessons_per_sheet', 15, PARAM_INT);
$cpasseds_per_sheet = optional_param('cpasseds_per_sheet', 20, PARAM_INT);

if( is_null($cstreamid) )
{
    $DOF->messages->add(
        $DOF->get_string('not_found_cstream', 'journal', $cstreamid), 
        DOF_MESSAGE_ERROR
    );
} else
{
    $cstream = $DOF->storage('cstreams')->get($cstreamid);
    if( ! $cstream )
    {
        $DOF->messages->add(
            $DOF->get_string('not_found_cstream', 'journal', $cstreamid),
            DOF_MESSAGE_ERROR
        );
    }
}

if( ! $DOF->im('journal')->is_access('export_journal', null, null, $cstream->departmentid) &&
    ! $DOF->im('journal')->is_access('export_journal/owner', $cstreamid, null, $cstream->departmentid) )
{
    $DOF->messages->add(
        $DOF->get_string('export_journal_access_denied', 'journal'), 
        DOF_MESSAGE_ERROR
    );
}


// $errors = $DOF->messages->get_stack_messages('default', DOF_MESSAGE_ERROR);
// if( ! empty($errors) )
if( $DOF->messages->errors_exists() )
{
    // Печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // Печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
    exit;
}

// ПОЛУЧЕНИЕ ИСХОДНЫХ ДАННЫХ

// менеджер для работы с уроками
$lessonprocess = $DOF->modlib('journal')->get_manager('lessonprocess');
// получение списка уроков
$lessonset = $lessonprocess->get_lessons($cstreamid, true);
// Получение сгруппированного списка занятий по датам
$lessondates = $lessonset->group_by_dates();
// Добавление строк подписок слушателей
$cpasseds = $lessonset->get_cpasseds_fullset_lastname();
// наименование программы 
$programmname = '';
// наименование дисциплины
$programmitemname = '';
// наименование периода
$agename = '';

// получение дисциплины
$programmitem = $DOF->storage('programmitems')->get($cstream->programmitemid);
if ( ! empty($programmitem) )
{
    $programmitemname = $DOF->storage('programmitems')->get_name($programmitem);
    
    // получение программы
    $programm = $DOF->storage('programms')->get($programmitem->programmid);
    if( ! empty($programm) )
    {
        $programmname = $programm->name;
        $programmname .= ' ['.$programm->code.']';
    }
}

// получение периода
$age = $DOF->storage('ages')->get($cstream->ageid);
if( ! empty($age->name) )
{
    $agename = $age->name;
}


// ПОДГОТОВКА СТИЛЕЙ

/* @var dof_cross_format_table_cell_style $defaultstyle */
$defaultstyle = $DOF->modlib('widgets')->cross_format_table_cell_style();
$defaultstyle->set_font_size(10);
$defaultstyle->set_vertical_align('middle');

/* @var dof_cross_format_table_cell_style $topheaderstyle */
$topheaderstyle = clone($defaultstyle);
$topheaderstyle->set_text_align('center');

/* @var dof_cross_format_table_cell_style $gradecellstyle */
$gradecellstyle = clone($defaultstyle);
$gradecellstyle->set_text_align('center');

/* @var dof_cross_format_table_cell_style $graygradecellstyle */
$graygradecellstyle = clone($gradecellstyle);
$graygradecellstyle->set_background_color(204, 204, 204);


// ФОРМИРОВАНИЕ ОБЩЕГО ЗАГОЛОВКА

/* @var dof_cross_format_table $every_sheet_cft */
$every_sheet_cft = $DOF->modlib('widgets')->cross_format_table();
$every_sheet_cft->set_default_cell_style($defaultstyle);
$every_sheet_cft->set_column_width(12, 0);
$every_sheet_cft->set_column_width(20, 1);
$every_sheet_cft->set_column_width(9, 2, $lessons_per_sheet);
$every_sheet_cft->set_row_height(15, 2);

// заголовок для программы
$every_sheet_cft->add_cell($DOF->get_string('programm', 'journal'), 0, 0);
// название программы
$every_sheet_cft->add_cell($programmname)
    ->set_colspan($lessons_per_sheet + 1);

// заголовок для периода
$every_sheet_cft->add_cell($DOF->get_string('age', 'journal'), 1, 0);
// название периода
$every_sheet_cft->add_cell($agename)
    ->set_colspan($lessons_per_sheet + 1);

// заголовок для дисциплины
$every_sheet_cft->add_cell($DOF->get_string('course', 'journal'), 2, 0);
// название дисциплины
$every_sheet_cft->add_cell($programmitemname)
    ->set_colspan($lessons_per_sheet + 1);

// заголовок для ФИО учителя
$every_sheet_cft->add_cell($DOF->get_string('teacher', 'journal'), 3, 0);
// ФИО учителя
$every_sheet_cft->add_cell($DOF->storage('persons')->get_fullname($cstream->teacherid))
    ->set_colspan($lessons_per_sheet + 1);

// пустая строчка перед таблицей оценок
$every_sheet_cft->add_cell('', 4, 0)
    ->set_colspan($lessons_per_sheet + 2);

// Заголовок: ФИО студентов
$every_sheet_cft->add_cell($DOF->get_string('export_journal_persons_header', 'journal'), 5, 0)
    ->set_rowspan(2)
    ->set_colspan(2)
    ->set_style($topheaderstyle);

// Заголовок: Даты
$every_sheet_cft->add_cell($DOF->get_string('export_journal_lesson_date_header', 'journal'), 5, 2)
    ->set_colspan($lessons_per_sheet)
    ->set_style($topheaderstyle);
    
// Пустая строка перед таблицей "что пройдено"
$every_sheet_cft->add_cell('', 5 + $cpasseds_per_sheet + 2, 0)
    ->set_colspan($lessons_per_sheet + 2);


// ФОРМИРОВАНИЕ ТАБЛИЦ С ДАННЫМИ

// Подключение файла классов работы с XLS
require_once($CFG->libdir.'/excellib.class.php');

$lesson_index = null;
$last_sheet_lesson_index = 0;
$last_sheet_cpassed_index = 0;
$cfts = [];

foreach ( $lessondates as $year => $months )
{
    foreach ( $months as $month => $days )
    {
        foreach ( $days as $day => $lessons )
        {
            foreach ( $lessons as $lesson )
            {
                $cpassed_index = 0;
                
                foreach ( $cpasseds as $cpassedid => $cpassed )
                {
                    if( isset($cfts[$last_sheet_lesson_index][$last_sheet_cpassed_index]) )
                    {// таблица для наполнения данными
                        $cft = $cfts[$last_sheet_lesson_index][$last_sheet_cpassed_index];
                    }
                    if( ($cpassed_index % $cpasseds_per_sheet == 0 && $last_sheet_cpassed_index != $cpassed_index )|| 
                        ($lesson_index % $lessons_per_sheet == 0 && $last_sheet_lesson_index != $lesson_index) ||
                        is_null($lesson_index) )
                    {// изменилась таблица (вышли за рамка ограничения количества учащихся или занятий на страницу)
                        if( is_null($lesson_index))
                        {// первая проходка
                            $lesson_index = 0;
                        }
                        // установка новых значений индексов, которые были созданы последний раз
                        $last_sheet_lesson_index = $lesson_index % $lessons_per_sheet == 0 ? $lesson_index : $last_sheet_lesson_index;
                        $last_sheet_cpassed_index = $cpassed_index % $cpasseds_per_sheet == 0 ? $cpassed_index : $last_sheet_cpassed_index;
                        if( ! isset($cfts[$last_sheet_lesson_index][$last_sheet_cpassed_index]) )
                        {// новая таблица - запишем общую для всех страниц информацию
                            $cft = $cfts[$last_sheet_lesson_index][$last_sheet_cpassed_index] = clone($every_sheet_cft);
                        } else
                        {// таблицу уже использовали, будем дописывать в нее
                            $cft = $cfts[$last_sheet_lesson_index][$last_sheet_cpassed_index];
                        }
                    }
                    
                    if( $lesson_index % $lessons_per_sheet == 0 )
                    {// первая колонка с уроком, надо напечатать левые заголовки
                        // ФИО учащегося
                        $nameinfo = $DOF->storage('persons')->get_name_info($cpassed->studentid);
                        $fiocell = $cft->add_cell($nameinfo['fullname'], $cpassed_index % $cpasseds_per_sheet + 7, 0)
                            ->set_colspan(2);
                        
                        if ( $cpassed->status == 'failed' || $cpassed->status == 'canceled' )
                        {// Неуспешно завершенная подписка
                            $fiocell->set_text_decoration('line-through');
                        } elseif ( $cpassed->status == 'completed' )
                        {// Успешно завершенная подписка
                            
                        }
                    }
                    
                    if( $cpassed_index % $cpasseds_per_sheet == 0 )
                    {// первая строка с учащимся, надо напечатать верхние заголовки
                        // дата урока
                        $cft->add_cell(date('d.m.Y', strtotime($day.".".$month.".".$year)), 6, $lesson_index % $lessons_per_sheet + 2)
                            ->set_style($topheaderstyle);
                        
                        // индекс строки в таблице прохождения программы
                        $rownumtemplan = $lesson_index % $lessons_per_sheet + $cpasseds_per_sheet + 8;
                        
                        // вывод даты в таблице прохождения программы
                        $cft->add_cell(date('d.m.Y', $lesson->get_startdate()), $rownumtemplan, 0)
                            ->set_style($topheaderstyle);
                        
                        // что пройдено на занятии
                        $passed = '';
                        if ( $lesson->plan_exists() )
                        {
                            $passed = $lesson->get_plan()->name;
                            $passed = str_replace("\r\n", "\r", $passed);
                            $passed = str_replace("\n", "\r", $passed);
                            $passed = str_replace("\r", " / ", $passed);
                        }
                        $cft->add_cell($passed)
                            ->set_colspan($lessons_per_sheet + 1);
                    }
                    
                    // ФОМИРОВАНИЕ ЯЧЕЙКИ С ОЦЕНКОЙ
                    $graycolor = false;
                    $gpcelltext = [];
                    
                    // Получение данных о работе на занятии
                    $gradedata = $lesson->get_listener_gradedata($cpassed->id);
                    
                    // получение информаиции о присутствии на занятии
                    $presence = true;
                    if ( $eventexists = $lesson->event_exists() )
                    {
                        $event = $lesson->get_event();
                        $presence = $lessonprocess->get_present_status($cpassed->studentid, $event->id);
                    }
                    
                    // Данные об оценке
                    if ( ! empty($gradedata) && $gradedata->overenroltime === false )
                    {
                        foreach ( $gradedata->grades as $grade )
                        {
                            if( ! empty($grade->item->grade) )
                            {// оценка
                                $gpcelltext[] = $grade->item->grade;
                            }
                        }
                        
                        if ( $presence === false && ($event->status != 'replaced') )
                        {
                            // + серый фон
                            $graycolor = true;
                            // Н/О
                            $gpcelltext[] = $DOF->get_string('cpassed_infoblock_presence_not_studied', 'journal');
                        }
                        elseif( isset($gradedata->presence->item->present) && $gradedata->presence->item->present == 0 )
                        {
                            // Н
                            $gpcelltext[] = $DOF->get_string('cpassed_infoblock_presence_no', 'journal');
                        }
                    } else
                    {
                        // серый фон
                        $graycolor = true;
                    }
                    
                    $cft->add_cell(
                            implode(' ', $gpcelltext),
                            $cpassed_index % $cpasseds_per_sheet + 7,
                            $lesson_index % $lessons_per_sheet + 2 
                        )
                        ->set_style($graycolor ? $graygradecellstyle : $gradecellstyle);
                    
                    
                    $cpassed_index++;
                }
                
                $lesson_index++;
            }
        }
    }
}

if( empty($cfts) )
{
    $DOF->messages->add(
        $DOF->get_string('export_journal_cstream_has_no_events', 'journal'),
        DOF_MESSAGE_WARNING
    );
    // Печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // Печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
    exit;
}

// СБОР ВСЕХ ТАБЛИЦ В ОДНУ КНИГУ

// Создание объекта xls файла
$workbook = new MoodleExcelWorkbook('journal_export');
ksort($cfts);
foreach( $cfts as $l => $ldata )
{
    ksort($ldata);
    foreach($ldata as $c=>$cft)
    {
        $a = new stdClass();
        $a->from_lesson = $l+1;
        $a->to_lesson = $l+$lessons_per_sheet;
        $a->from_cpassed = $c+1;
        $a->to_cpassed = $c+$cpasseds_per_sheet;
        
        $cft->add_xls_sheet(
            $workbook, 
            $DOF->get_string('export_journal_sheetname', 'journal', $a),
            [
                'orientation' => 'L', // альбомная ориентация
                'papersize' => 'A4', // формат листа А4
                'fit_to_width' => 1, // подогнать по ширине
                'fit_to_height' => 1, // + подогнать по высоте = уместить на 1 странице
                'print_areas' => [ // ограничение печатной области, которую будем умещать на 1 странице
                    [
                        0, // первая строка
                        0, // первый столбец
                        $cpasseds_per_sheet + $lessons_per_sheet + 7, // последняя строка 
                        $lessons_per_sheet + 1 // последний столбец
                    ]
                ]
            ]
        );
    }
}


// ОТПРАВКА ФАЙЛА ПОЛЬЗОВАТЕЛЮ
$workbook->close();
exit;

