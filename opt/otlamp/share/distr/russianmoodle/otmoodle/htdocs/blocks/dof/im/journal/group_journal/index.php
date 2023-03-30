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
 * Журнал предмето-класса. Точка входа в сабинтерфейс.
 *
 * @package    im
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('lib.php');
require_once($DOF->plugin_path('im','journal','/group_journal/libform.php'));

// Получение GET-параметров
// ID предмето-класса
$csid = required_param('csid', PARAM_INT);

// ID учебного события или плана, оценки которго редактируются
$planid  = optional_param('planid', 0, PARAM_INT);
$eventid = optional_param('eventid', 0, PARAM_INT);

$edit_grades_plan_id = optional_param('edit_grades_plan_id', 0, PARAM_INT);
$edit_presence_event_id = optional_param('edit_presence_event_id', 0, PARAM_INT);
$edit_presence_plan_id = optional_param('edit_presence_plan_id', 0, PARAM_INT);
$edit_lesson_event_id = optional_param('edit_lesson_event_id', 0, PARAM_INT);
$edit_lesson_plan_id = optional_param('edit_lesson_plan_id', 0, PARAM_INT);
$addvars['showall'] = optional_param('showall', 0, PARAM_BOOL);

$addvars['planid'] = $planid;
$addvars['eventid'] = $eventid;

$addvars['edit_grades_plan_id'] = $edit_grades_plan_id;
$addvars['edit_presence_event_id'] = $edit_presence_event_id;
$addvars['edit_presence_plan_id'] = $edit_presence_plan_id;
$addvars['edit_lesson_event_id'] = $edit_lesson_event_id;
$addvars['edit_lesson_plan_id'] = $edit_lesson_plan_id;

// Подключение библиотек
$DOF->modlib('widgets')->js_init('show_hide');
// Добавление таблицы стилей
$DOF->modlib('nvg')->add_css('im', 'journal', '/styles.css');
// Добавить CSS
$DOF->modlib('nvg')->add_css('modlib', 'widgets', '/dropblock/styles.css', false);
// Добавить JS
$DOF->modlib('nvg')->add_js('modlib', 'widgets', '/dropblock/script.js', false);

// Проверка параметров
if ( ! $cstream = $DOF->storage('cstreams')->get($csid) )
{// Предмето-класс не найден
	$DOF->messages->add(
	    $DOF->get_string('error_grpjournal_cstream_not_found', 'journal', $csid),
	    'error'
	);
}

// Проверка прав доступа
if ( ! $DOF->im('journal')->is_access('view_journal/own', $csid) &&
       $DOF->im('journal')->require_access('view_journal', $csid) )
{// Нет прав для просмотра журнала
    $DOF->messages->add(
        $DOF->get_string('error_grpjournal_view_access_denied', 'journal'),
        'error'
    );
}

if ( $DOF->messages->errors_exists() )
{// Имеются ошибки
    // Печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // Печать подвала страницы
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
    die;
}

// ДЕЙСТВИЯ В ЖУРНАЛЕ
$links = '';

// Ссылка на получение итоговой ведомости
if (  //  право завершать cstream до истечения срока cstream
      (($DOF->im('journal')->is_access('complete_cstream_before_enddate',$csid) AND $cstream->enddate > time()) OR
      // право завершать cstream после истечения срока cstream (пересдача)
      ($DOF->im('journal')->is_access('complete_cstream_after_enddate', $csid) AND $cstream->enddate < time()) OR
      // право  Закрывать итоговую ведомость до завершения cstream
      // (под завершением имеется в виду cstream в конечном статусе)
      ($DOF->im('journal')->is_access('close_journal_before_closing_cstream', $csid) AND $cstream->status != 'completed' ) OR
      // право Закрывать итоговую ведомость до истечения даты cstream
      ($DOF->im('journal')->is_access('close_journal_before_cstream_enddate', $csid) AND $cstream->enddate > time() ) OR
      //  право Закрывать итоговую ведомость после истечения даты cstream, но до завершения cstream
      ($DOF->im('journal')->is_access('close_journal_after_active_cstream_enddate', $csid)
          AND $cstream->status != 'completed' AND time() > $cstream->enddate ))
      AND
      ( $DOF->storage('cpassed')->is_access('edit:grade/own',$csid) OR
              $DOF->storage('cpassed')->is_access('edit:grade/auto',$csid) OR
        $DOF->storage('cpassed')->is_access('edit:grade',$csid) )
   )
{// Прав достаточно
    $links .= dof_html_writer::start_div('dof_group_journal_action dof_group_journal_action_finalgrades');
    $somevars = $addvars + ['id' => $csid];
    $links .= dof_html_writer::link(
        $DOF->url_im('journal','/itog_grades/edit.php', $somevars),
        $DOF->get_string('itog_grades', 'journal'),
        ['class' => 'btn button dof_button btn-primary']
    );
    $links .= dof_html_writer::end_div();
}

// Ссылки на работу с тематическим планированием занятий
if ( $DOF->im('plans')->is_access('viewthemeplan', $csid) ||
     $DOF->im('plans')->is_access('viewthemeplan/my', $csid) )
{// Прав достаточно
    // Ссылка на просмотр фактического планирования
    $links .= dof_html_writer::start_div('dof_group_journal_action dof_group_journal_action_plancstream_fact');
    $somevars = $addvars + ['linktype' => 'cstreams', 'linkid' => $csid];
    $links .= dof_html_writer::link(
        $DOF->url_im('plans','/themeplan/viewthemeplan.php', $somevars),
        $DOF->get_string('view_plancstream', 'journal'),
        ['class' => 'btn button dof_button btn-primary']
    );
    $links .= dof_html_writer::end_div();
    // Ссылка на просмотр учебного тематического планирования
    $links .= dof_html_writer::start_div('dof_group_journal_action dof_group_journal_action_iutp');
    $somevars = $addvars + ['linktype' => 'plan', 'linkid' => $csid];
    $links .= dof_html_writer::link(
        $DOF->url_im('plans','/themeplan/viewthemeplan.php', $somevars),
        $DOF->get_string('view_iutp', 'journal'),
        ['class' => 'btn button dof_button btn-primary']
    );
    $links .= dof_html_writer::end_div();
}

// Ссылка на рейтинг по учебному процессу
if ( $DOF->im('cstreams')->is_access('view:rtreport/rating_cstream', $csid) )
{// Прав достаточно
    $links .= dof_html_writer::start_div('dof_group_journal_action dof_group_journal_action_plancstream');
    $somevars = $addvars + ['cstreamid' => $csid, 'type' => 'rating_cstream', 'pt' => 'im', 'pc' => 'journal'];
    $links .= dof_html_writer::link(
            $DOF->url_im('rtreport','/index.php', $somevars),
            $DOF->get_string('view_rating', 'journal'),
            ['class' => 'btn button dof_button btn-primary']
            );

    $links .= dof_html_writer::end_div();
}

// Отчет выполнения учебной нагрузки преподавателя
if ( $DOF->im('cstreams')->is_access('view:rtreport/workloadcstream', $csid) )
{// Прав достаточно
    $links .= dof_html_writer::start_div('dof_group_journal_action dof_group_journal_action_plancstream');
    $somevars = $addvars + ['cstreamid' => $csid, 'type' => 'workloadcstream', 'pt' => 'im', 'pc' => 'journal'];
    $links .= dof_html_writer::link(
            $DOF->url_im('rtreport','/index.php', $somevars),
            $DOF->get_string('view_workload', 'journal'),
            ['class' => 'btn button dof_button btn-primary']
            );

    $links .= dof_html_writer::end_div();
}


if( $DOF->im('journal')->is_access('export_journal', null, null, $cstream->departmentid) ||
    $DOF->im('journal')->is_access('export_journal/owner', $csid, null, $cstream->departmentid) )
{
    $links .= dof_html_writer::div(
        $exportlink = dof_html_writer::link(
            $DOF->url_im('journal','/group_journal/export.php', ['cstreamid' => $csid]),
            $DOF->get_string('export_journal', 'journal'),
            ['class' => 'btn button dof_button btn-primary']
            ),
        'dof_group_journal_action dof_group_journal_action_export'
        );
}

// Формирование базового блока информации о журнале потока
$table = new stdClass();
$table->cellpadding = 0;
$table->cellspacing = 0;
$table->size = ['50%', '50%'];
$table->align = ['center', 'left'];
$table->class = 'dof_group_journal_baseinfo';
// Шапка
$table->head = [];
// Данные таблицы
$table->data = [[
    $DOF->im('journal')->get_cstream_info($csid),
    $links
]];

// Формирование вывода
$html_new = '';

$html_new .= dof_html_writer::start_div('dof-journal-groupjournal-wrapper');

// Информация о учебном процессе
$cstream_info_table = new dof_im_journal_tablecstreaminfo($DOF, $csid, $addvars);
$html_new .= $cstream_info_table->render();
$html_new .= dof_html_writer::div($links, 'dof-cstream-links-wrapper');

// Враппер журнала и занятий
$html_new .= dof_html_writer::start_div('dof-journal-groupjournal-grades-templans-wrapper');

// Журнал оценок
$grades_table = new dof_im_journal_tablecstreamgrades($DOF, $csid, $addvars);
$html_new .= dof_html_writer::div($grades_table->render(), 'tablegrades');

// Занятия
$html_new .= dof_html_writer::start_div('tabletemplans');

// Сворачивание занятий
$actionshtml = '';

$img = $DOF->modlib('ig')->icon('leftd');
$actionshtml .= dof_html_writer::div($img, 'action-left action action-collapse');
$img = $DOF->modlib('ig')->icon('rightd');
$actionshtml .= dof_html_writer::div($img, 'action-right action action-collapse show');
$html_new .= dof_html_writer::div($actionshtml, 'actions');

$themeplans_table = new dof_im_journal_tabletemplans($DOF, $csid, $addvars);
$html_new .= dof_html_writer::div($themeplans_table->render(), 'dof-groupjournal-templans-and-tools');

$html_new .= dof_html_writer::end_div();

// Закрытие враппера журналом и занятиями
$html_new .= dof_html_writer::end_div();

// Закрытие основного враппера
$html_new .= dof_html_writer::end_div();


$nvgoptions = [
    'sidecode' => 'none'
];
// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL, $nvgoptions);

// Вывод html кода (NEW)
echo $html_new;

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
