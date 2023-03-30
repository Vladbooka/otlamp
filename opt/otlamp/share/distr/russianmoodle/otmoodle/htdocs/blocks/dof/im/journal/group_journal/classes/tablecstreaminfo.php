<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
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
 * Журнал предмето-класса. Журнал оценок по учебному процессу.
 *
 * @package    im
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_im_journal_tablecstreaminfo
{
    /**
     * Ссылка на контроллер Деканата
     *
     * @var dof_control
     */
    protected $dof = null;
    
    /**
     * Учебный процесс
     *
     * @var stdClass
     */
    protected $cstream = null;
    
    /**
     * GET параметры для ссылки
     * 
     * @var $addvars
     */
    protected $addvars = [];
    
    /**
     * Конструктор
     *
     * @param dof_control $dof - Ссылка на контроллер Деканата
     * @param int $cstreamid - ID учебного процесса
     * @param array $addvars - Массив GET параметров для формирования ссылок
     */
    public function __construct(dof_control $dof, $cstreamid, $addvars = [])
    {
        // Базовая инициализация
        $this->dof = $dof;
        $this->cstream = $this->dof->storage('cstreams')->get((int)$cstreamid);
        $this->addvars = (array)$addvars;
        
        // Иинициализация генератора HTML
        $this->dof->modlib('widgets')->html_writer();
    }
    
    /**
     * Генерация таблицы информации об учебном процессе
     * 
     * @return string - HTML-код таблицы
     */
    public function render()
    {
        // Для генерации ссылок на курс
        global $CFG;

        $html = '';
        
        if ( empty($this->cstream) )
        {// Учебный процесс не получен
            return $html;
        }
        
        // Получение интервалов учебного процесса
        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        $begindate = $this->dof->storage('cstreams')->get_begindate($this->cstream);
        $begindate = dof_userdate($begindate, '%d.%m.%Y', $usertimezone);
        $enddate = $this->dof->storage('cstreams')->get_enddate($this->cstream);
        $enddate = dof_userdate($enddate, '%d.%m.%Y', $usertimezone);
        $cstreamdate = $begindate . ' - ' . $enddate;
        
        // Получение статуса учебного процесса
        $statusname = $this->dof->workflow('cstreams')->get_name($this->cstream->status);
        
        // Получение имени учебного процесса
        $somavars = $this->addvars;
        $somavars['cstreamid'] = $this->cstream->id;
        $cstreamname = $this->dof->storage('cstreams')->get_name($this->cstream);
        $cstreamurl = $this->dof->url_im('cstreams', '/view.php', $somavars);
        
        // Получение описания учебного процесса
        $cstreamdescription = $this->dof->storage('cstreams')->get_description($this->cstream);
        
        // Получение имени преподавателя учебного процесса
        $teacherfullname = (string)$this->dof->storage('persons')->
            get_fullname($this->cstream->teacherid);
        // Генерация ссылок для просмотра данных по преподавателю
        if ( $teacherfullname )
        {
            // Информация о персоне
            $somavars = $this->addvars;
            $somavars['id'] = $this->cstream->teacherid;
            $teacherfullname .= $this->dof->modlib('ig')->icon(
                'viewfull',
                $this->dof->url_im('persons', '/view.php', $somavars),
                [
                    'title' => $this->dof->get_string('teacher', 'journal'), 
                    'alt' => $this->dof->get_string('teacher', 'journal')
                ]
            );
            
            // Информация о занятиях
            $somavars = $this->addvars;
            $somavars['personid'] = $this->cstream->teacherid;
            $teacherfullname .= $this->dof->modlib('ig')->icon(
                'viewshort',
                $this->dof->url_im('journal', '/show_events/show_events.php', $somavars),
                [
                    'title' => $this->dof->get_string('view_events_teacher', 'journal'),
                    'alt' => $this->dof->get_string('view_events_teacher', 'journal')
                ]
            );
            
            // Информация о расписании
            if ( $this->dof->storage('schtemplates')->is_access('view') )
            {
                // Информация о занятиях
                $somavars = $this->addvars;
                $somavars['teacherid'] = $this->cstream->teacherid;
                $somavars['ageid'] = $this->cstream->ageid;
                $teacherfullname .= $this->dof->modlib('ig')->icon(
                    'plan',
                    $this->dof->url_im('schedule', '/view_week.php', $somavars),
                    [
                        'title' => $this->dof->get_string('view_week_template_on_teacher', 'journal'),
                        'alt' => $this->dof->get_string('view_week_template_on_teacher', 'journal')
                    ]
                );
            }
        }
        
        // Получение имени дисциплины, программы, параллели и курса Moodle
        $programmname = '';
        $coursename = '';
        $programmitemname = '';
        $agenum = $this->dof->get_string('agenum0', 'journal');
        $programmitem = $this->dof->storage('programmitems')->get($this->cstream->programmitemid);
        // Генерация ссылок для просмотра данных по дисциплине
        if ( $programmitem )
        {
            // Номер параллели
            if ( $programmitem->agenum > 0 )
            {
                $agenum = $programmitem->agenum;
            }
            
            // Имя дисциплины
            $programmitemname = $this->dof->storage('programmitems')->
                get_name($programmitem);
            $programmitemcode = '['.$programmitem->code.']';
            // Информация о дисциплине
            $programmitemurl = '';
            if ( $this->dof->storage('programmitems')->is_access('view', $programmitem->id) )
            {
                $somavars = $this->addvars;
                $somavars['pitemid'] = $programmitem->id;
                $programmitemurl = $this->dof->url_im('programmitems', '/view.php', $somavars);
            }
            
            // Имя программы
            $programmname = $this->dof->storage('programms')->
                get_field($programmitem->programmid, 'name');
            $programmname .= '['.$this->dof->storage('programms')->
                get_field($programmitem->programmid, 'code').']';
            // Информация о программе
            if ( $this->dof->storage('programms')->is_access('view', $programmitem->programmid) )
            {
                $somavars = $this->addvars;
                $somavars['programmid'] = $programmitem->programmid;
                $programmname = dof_html_writer::link(
                    $this->dof->url_im('programms', '/view.php', $somavars),
                    $programmname
                );
            }
            
            $mdlcourseid = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_cstream_mdlcourse($this->cstream->id);
            
            // Имя курса
            if ( ! empty($mdlcourseid) && $this->dof->modlib('ama')->course(false)->is_course((int)$mdlcourseid) )
            {
                $course = $this->dof->modlib('ama')->course($mdlcourseid)->get();
                $coursename = $course->fullname;
                $courseurl = $CFG->wwwroot.'/course/view.php?id='.$mdlcourseid;
            }
        }

        $headerline = dof_html_writer::div($this->dof->get_string('title', 'journal'), 'dof-cstream-header-item journal-title');
        $headerline .= dof_html_writer::link($cstreamurl,$cstreamname, ['class'=>'dof-cstream-header-item cstream-name']);
        if( ! empty($programmitemurl) )
        {
            $headerline .= dof_html_writer::link($programmitemurl, $programmitemname, ['class'=>'dof-cstream-header-item programmitem-name']);
            $headerline .= dof_html_writer::link($programmitemurl, $programmitemcode, ['class'=>'dof-cstream-header-item programmitem-code']);
        } else
        {
            $headerline .= dof_html_writer::div($programmitemname, 'dof-cstream-header-item programmitem-name');
            $headerline .= dof_html_writer::div($programmitemcode, 'dof-cstream-header-item programmitem-code');
        }
        $html .= dof_html_writer::div($headerline, 'dof-cstream-header-line');
        
        if( ! empty($coursename) )
        {
            $headerline = dof_html_writer::div($this->dof->get_string('course_moodle', 'journal'), 'dof-cstream-header-item mdlcourse-title');
            $headerline .= dof_html_writer::link($courseurl, $coursename, ['class'=>'dof-cstream-header-item mdlcourse-name']);
            $html .= dof_html_writer::div($headerline, 'dof-cstream-header-line');
        }
        
        
        $table = new stdClass();
        $table->class = 'dof-cstream-info';
        $table->data = [];
        
//         $table->data[] = [
//             dof_html_writer::div($this->dof->get_string('name', 'journal'), 'dof-cstream-info-cstreamname first'),
//             dof_html_writer::div($cstreamname, 'dof-cstream-info-cstreamname first')
//         ];
        
//         $table->data[] = [
//             dof_html_writer::div($this->dof->get_string('course_moodle', 'journal'), 'dof-cstream-info-mdlcoursename'),
//             dof_html_writer::div($coursename, 'dof-cstream-info-mdlcoursename')
//         ];
        
//         $table->data[] = [
//             dof_html_writer::div($this->dof->get_string('course', 'journal'), 'dof-cstream-info-coursename'),
//             dof_html_writer::div($programmitemname, 'dof-cstream-info-coursename')
//         ];
        
        // Программа
        $table->data[] = [
            dof_html_writer::div($this->dof->get_string('programm', 'journal'), 'dof-cstream-info-programmname'),
            dof_html_writer::div($programmname, 'dof-cstream-info-programmname')
        ];
        
        // Дата
        $table->data[] = [
            dof_html_writer::div($this->dof->get_string('date', 'journal'), 'dof-cstream-info-cstreamdate'),
            dof_html_writer::div($cstreamdate, 'dof-cstream-info-cstreamdate')
        ];
        
        // Параллель
        $table->data[] = [
            dof_html_writer::div($this->dof->get_string('agenum', 'journal'), 'dof-cstream-info-agenum'),
            dof_html_writer::div($agenum, 'dof-cstream-info-agenum')
        ];
        
        //Преподаватель
        $table->data[] = [
            dof_html_writer::div($this->dof->get_string('teacher', 'journal'), 'dof-cstream-info-teacher'),
            dof_html_writer::div($teacherfullname, 'dof-cstream-info-teacher')
        ];
        
        // Статус
        $table->data[] = [
            dof_html_writer::div($this->dof->get_string('status', 'journal'), 'dof-cstream-info-statusname'),
            dof_html_writer::div($statusname, 'dof-cstream-info-statusname')
        ];
        
        // Описание
        if( ! empty($cstreamdescription) )
        {
            $table->data[] = [
                dof_html_writer::div($this->dof->get_string('description', 'journal'), 'dof-cstream-info-description last'),
                dof_html_writer::div($cstreamdescription, 'dof-cstream-info-description last')
            ];
        }
        
        // Генерация таблицы
        $tablehtml = $this->dof->modlib('widgets')->print_table($table, true);
        $html .= dof_html_writer::div($tablehtml, 'dof-cstream-info-wrapper');
        
        return $html;
    }
}