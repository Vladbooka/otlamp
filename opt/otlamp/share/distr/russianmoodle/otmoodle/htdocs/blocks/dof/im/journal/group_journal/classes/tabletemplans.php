<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
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
 * Журнал предмето-класса. Класс рендера занятий
 *
 * @package    im
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_im_journal_tabletemplans extends dof_im_journal_tablecstreamgrades
{
    /**
     * Счетчик занятий
     *
     * @param int $lessons_count
     */
    protected static $lessons_count = 0;
    
    /**
     * Формирование строки хидеров
     * 
     * @return string
     */
    protected function get_row_headers()
    {
        $html = '';
        $html .= dof_html_writer::start_tag('tr', ['class' => 'row-headers ot-sort-fix-top']);
        
        $html .= dof_html_writer::start_tag('th', ['class' => 'cell-number']);
        $html .= dof_html_writer::div($this->dof->get_string('N', 'journal'));
        $html .= dof_html_writer::end_tag('th');
        
        $html .= dof_html_writer::start_tag('th', ['class' => 'cell-date ot-sort-date-title']);
        $html .= dof_html_writer::div($this->dof->get_string('date', 'journal'));
        $html .= dof_html_writer::end_tag('th');
        
        $html .= dof_html_writer::start_tag('th', ['class' => 'cell-additional-icons']);
        $html .= '';
        $html .= dof_html_writer::end_tag('th');
        
        $html .= dof_html_writer::start_tag('th', ['class' => 'cell-passed']);
        $html .= dof_html_writer::div($this->dof->get_string('what_passed_on_lesson', 'journal'));
        $html .= dof_html_writer::end_tag('th');
        
        $html .= dof_html_writer::start_tag('th', ['class' => 'cell-homework']);
        $html .= dof_html_writer::div($this->dof->get_string('homework', 'journal'));
        $html .= dof_html_writer::end_tag('th');
        
        $html .= dof_html_writer::start_tag('th', ['class' => 'cell-homework-time']);
        $html .= dof_html_writer::div($this->dof->get_string('hwhours', 'journal'));
        $html .= dof_html_writer::end_tag('th');
        
        $html .= dof_html_writer::start_tag('th', ['class' => 'cell-status']);
        $html .= dof_html_writer::div($this->dof->get_string('status', 'journal'));
        $html .= dof_html_writer::end_tag('th');

        $html .= dof_html_writer::end_tag('tr');

        $html .= dof_html_writer::tag(
            'tr',
            dof_html_writer::tag('th', '', ['colspan' => '7', 'class' => 'cell-empty']),
            ['class' => 'row-divider ot-sort-fix-top']
        );
        
        if (!$this->showall)
        {
            $addvars = $this->addvars;
            $addvars['showall'] = 1;
            $showallurl = $this->dof->url_im('journal', '/group_journal/index.php', $addvars);
            $showallspan = dof_html_writer::span($this->dof->get_string('display_all_themes', 'journal'));
            $showalllink = dof_html_writer::link($showallurl, $showallspan);
            $showalltd = dof_html_writer::tag('td', $showalllink, [
                'colspan' => '7',
                'class' => 'cell-showall'
            ]);
            $html .= dof_html_writer::tag('tr', $showalltd, ['class' => 'ot-sort-fix-top']);
        }
        
        
        return $html;
    }
    
    /**
     * Формирование строки информации о занятии
     *
     * @return string
     */
    protected function get_row_lesson_info(dof_lesson $lesson)
    {
        // Дефолтные значения полей
        $passed = '';
        $homework = '';
        $homework_time = '';
        $statusicon = '';
        $statustext = '';
        $row_attr = ['data-plan' => 0];
        $status_class = 'dof-lesson-status-icon';

        //$lesson->createform_allowed($this->cstream->id, null, null, $this->addvars['departmentid'])
        $row_attr['data-plan-editable'] = $lesson->editform_allowed('editexistplan', null, $this->addvars['departmentid']);
            
        // Дополнительные классы
        if ( $lesson->event_exists() && $lesson->plan_exists())
        {
            $status_class .= ' lesson';
        } else
        {
            if( $lesson->event_exists() )
            {
                $event = $lesson->get_event();
                if( $event->status == 'replaced' )
                {
                    $status_class .= ' replacedevent';
                } else
                {
                    $status_class .= ' event';
                }
            }
            if( $lesson->plan_exists() )
            {
                $plan = $lesson->get_plan();
                if( $plan->linktype != 'cstreams' )
                {
                    $status_class .= ' checkpoint';
                } else
                {
                    $status_class .= ' plan';
                }
            }
        }
        if ( $lesson->is_completed() )
        {// Заниятие завершено
            $status_class .= ' completed';
        }

        // Формирование даты
        if ( $lesson->event_exists() )
        {
            // Дата смены статуса события
            $datechangestatus   = '';
            
            // Поиск записи о смене статуса
            $status = new stdClass();
            $status->plugintype = 'storage';
            $status->plugincode = 'schevents';
            $status->objectid   = $lesson->get_event()->id;
            $status->prevstatus = 'plan';
            
            $sqlstatus = $this->dof->storage('statushistory')->get_select_listing($status);
            if ( $statuses = $this->dof->storage('statushistory')->get_records_select($sqlstatus, null, 'statusdate DESC') )
            {
                $statusdate = current($statuses)->statusdate;
                $datechangestatus = dof_userdate($statusdate, '%d.%m.%Y');
            }
            
            $statustext = dof_html_writer::span($this->dof->workflow('schevents')->get_name($lesson->get_event()->status));
            if ( $lesson->get_event()->status == 'replaced' )
            {// Урок заменен
                
                $statusicon = dof_html_writer::span('', '', ['title' => $datechangestatus, 'class' => $status_class]);
                $newschevent = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_next_replaced_schevent($lesson->get_event()->id);
                if ( ! empty($newschevent) )
                {
                    $newlesson = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_lesson(
                        null,
                        $newschevent->id,
                        $newschevent->planid
                    );
                    $editbutton = $this->get_lesson_button(
                        $newlesson, 
                        $statustext, 
                        [
                            'title' => dof_userdate($newschevent->date, '%F %T')
                        ]
                    );
                    if( ! empty($editbutton) )
                    {
                        $statustext = $editbutton;
                    }
                }
            } else
            {
                $statusicon = dof_html_writer::span('', '', ['title' => $datechangestatus, 'class' => $status_class]);
                $statustext = dof_html_writer::span("{$this->dof->storage('persons')->get_fullname($lesson->get_event()->teacherid)}");
            }
        }
        
        if ( $lesson->plan_exists() )
        {
            // Дата-аттрибут для AJAX
            $row_attr['data-plan'] = $lesson->get_plan()->id;
                        
            // Что пройдено на занятии
            $passed = $lesson->get_plan()->name;
            
            // Домашнее задание
            $homework = $lesson->get_plan()->homework;
            
            // Время на домашнее задание
            if ( ! empty($lesson->get_plan()->homeworkhours) )
            {
                $a = new stdClass();
                $a->time = intval($lesson->get_plan()->homeworkhours / 60);
                
                $homework_time = $this->dof->get_string('lesson_homework_time', 'journal', $a);
            }
            $statusicon = dof_html_writer::span('', '', ['class' => $status_class]);
        }
        
        $html = '';
        $html .= dof_html_writer::start_tag('tr', array_merge(['class' => 'row-lesson-info'], $row_attr));
        
        // Номер
        $lessonnumhtml = dof_html_writer::div($lesson->get_indexnum(), $status_class.' hidecompletion');

        $lessoneditform = $this->get_lesson_button($lesson);
        
        $html .= dof_html_writer::tag('td', $lessonnumhtml . $lessoneditform, ['class' => 'cell-number']);
        
        // Дата
        $date = $lesson->get_startdate();
        $html .= dof_html_writer::start_tag('td', [
            'class' => 'cell-date  ot-sort-date-data ot-filter-date-data',
            'data-sort-value' => $date
        ]);
        if ( $lesson->mdl_gradeitem_exists() )
        {
            $progressbar = dof_html_writer::div('', 'dof-journal-progressbar');
            
            // получение грейдитема
            $amagradeitem = $lesson->get_mdl_gradeitem();
            $gradeitem = $amagradeitem->get();
            
            // контент дроп-блока
            $dropmenuhtml = '';
            $afterclosehtml = '';
            
            if ( $this->control_active )
            {
                if ( $gradeitem->is_external_item() )
                {
                    // включен контролируемый режим доступа в СДО
                    // добавление кнопок управления доступом
                    $linkparams = [
                        'class' => 'dof-mdlgradeitem-access-ajax',
                        'data-planid' => $lesson->get_plan()->id,
                        'data-cstreamid' => $this->cstream->id,
                        'data-changeto' => 1
                    ];
                    
                    // открыть доступ к элементу
                    $link = dof_html_writer::link(
                            '/',
                            dof_html_writer::div($this->dof->get_string('local_authcontrol_open_access', 'journal')),
                            $linkparams);
                    $dropmenuhtml .= dof_html_writer::div($progressbar . $link, 'dof-mdlgradeitem-info-menu-item');
                    
                    $linkparams['data-changeto'] = 0;
                    
                    // закрыть доступ к элементу
                    $link = dof_html_writer::link(
                            '/',
                            dof_html_writer::div($this->dof->get_string('local_authcontrol_close_access', 'journal')),
                            $linkparams);
                    $dropmenuhtml .= dof_html_writer::div($progressbar . $link, 'dof-mdlgradeitem-info-menu-item');
                }
                
                // ссылка на панель управления доступом в курса
                $mdlcourseid = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_plan_mdlcourse($lesson->get_plan()->id);
                if ( $mdlcourseid )
                {
                    $url = $this->dof->modlib('ama')->course($gradeitem->courseid)->get_authcontrol_page_url();
                    $link = dof_html_writer::link(
                            $url,
                            dof_html_writer::div($this->dof->get_string('controlpage', 'journal')),
                            [
                                'target' => '_blank'
                            ]);
                    $afterclosehtml .= dof_html_writer::div($link, 'dof-mdlgradeitem-info-menu-item');
                }
            }
            
            if ( $this->dof->modlib('journal')->get_manager('lessonprocess')->can_save_grades($lesson->get_plan()->id, $this->cstream->id, $this->addvars['departmentid']) &&
                    $lesson->is_synced_with_moodle() )
            {
                // кнопка синхронизации оценок
                $link = dof_html_writer::link('/', dof_html_writer::div($this->dof->get_string('gradessync_go', 'journal')),
                        [
                            'data-planid' => $lesson->get_plan()->id,
                            'target' => '_blank',
                            'class' => 'dof-mdlgradeitem-sync-grades'
                        ]);
                $dropmenuhtml .= dof_html_writer::div($progressbar. $link, 'dof-mdlgradeitem-info-menu-item');
            }
            
            // кастомная ссылка на элемент, если она есть у элемента
            $customlink = $amagradeitem->get_link_to_element_gradebook();
            if ( ! empty($customlink) )
            {
                $dropmenuhtml .= dof_html_writer::div($customlink, 'dof-mdlgradeitem-info-menu-item');
            }
            
            // ссылка на журнал оценок
            $link = dof_html_writer::link($amagradeitem->get_link_to_gradebook(),
                    dof_html_writer::div($this->dof->get_string('mdlgrades', 'journal')), [
                        'target' => '_blank'
                    ]);
            $dropmenuhtml .= dof_html_writer::div($link, 'dof-mdlgradeitem-info-menu-item');
            
            // ссылка на панель управления доступом в СДО
            $dropmenuhtml .= $afterclosehtml;
            
            $html .= $this->dof->modlib('widgets')->dropblock(dof_html_writer::span(date('d.m.y', $date), 'dof-journal-text-lighter dof-journal-text-dashed'),
                    dof_html_writer::div($dropmenuhtml, 'dropmenumdlgradeitem'), '',
                    [
                        'uniqueid' => 'mdldropmenu_' . $lesson->get_identifier(),
                        'fixed' => true
                    ]);
        } else
        {
            $html .= dof_html_writer::div(date('d.m.y', $date));
        }
        $html .= dof_html_writer::end_tag('td');
        
        // Дополнительные иконки
        $html .= dof_html_writer::start_tag('td', ['class' => 'cell-additional-icons']);
        if ( $lesson->has_gradeitem() )
        {
            $html .= dof_html_writer::label($this->dof->modlib('ig')->icon('moodleblue'), 'dof_dropblock_mdldropmenu_' . $lesson->get_identifier());
        }
        $html .= dof_html_writer::end_tag('td');
        
        // Что пройдено на занятии
        $html .= dof_html_writer::start_tag('td', ['class' => 'cell-passed ot-filter-passed-data']);
        $html .= dof_html_writer::div($passed);
        $html .= dof_html_writer::end_tag('td');
        
        // Домашнее задание
        $html .= dof_html_writer::start_tag('td', ['class' => 'cell-homework ot-filter-homework-data']);
        $html .= dof_html_writer::div($homework);
        $html .= dof_html_writer::end_tag('td');
        
        // Время на дз
        $html .= dof_html_writer::start_tag('td', ['class' => 'cell-homework-time']);
        $html .= dof_html_writer::div($homework_time);
        $html .= dof_html_writer::end_tag('td');
        
        // Статус
        $html .= dof_html_writer::start_tag('td', ['class' => 'cell-status']);
        $html .= dof_html_writer::div($statusicon . $statustext);
        $html .= dof_html_writer::end_tag('td');
        
        $html .= dof_html_writer::end_tag('tr');
        
        return $html;
    }
    
    /**
     * Получение кнопки редактирования/просмотра занятия
     * 
     * @param dof_lesson $lesson
     * @param string $text
     * @param array $options
     * 
     * @return string
     */
    protected function get_lesson_button($lesson, $text=null, $options=[])
    {
        // Генерация кнопки для редактирования занятия
        $editbutton = '';
        
        $istemplan = false;
        $canedit = true;
        $lessoneditableclass = '';
        
        // Проверка права манипуляции с занятием
        $anyeditaccess = $lesson->can_manipulate_plan($this->cstream->id, $this->addvars['departmentid']) ||
            $lesson->can_manipulate_schevent($this->cstream->id, $this->addvars['departmentid']);
        if( ! $anyeditaccess )
        {// редактирование запрещено
            $canedit = false;
        }
        
        if ( $lesson->plan_exists() && in_array($lesson->get_plan()->linktype, ['ages', 'plan']) )
        {
            $lessoneditableclass = 'disabled';
        }
        
        $event_id = 0;
        $plan_id = 0;
        if ( $lesson->plan_exists() )
        {
            $plan_id = $lesson->get_plan()->id;
        }
        if ( $lesson->event_exists() )
        {
            $event_id = $lesson->get_event()->id;
        }
        $lessoneditlink = $this->dof->url_im('journal', '/group_journal/forms/lessonedit.php', [
            'csid' => $this->cstream->id,
            'eventid' => $event_id,
            'planid' => $plan_id,
            'departmentid' => $this->addvars['departmentid']
        ]);
        
        if( is_null($text) )
        {
            if ( $canedit )
            {
                $button = $this->dof->modlib('ig')->icon('edit', $lessoneditlink);
            } else
            {
                $button = $this->dof->modlib('ig')->icon('viewfull', $lessoneditlink);
            }
        } else
        {
            $title = '';
            if( ! empty($options['title']) )
            {
                $title = $options['title'];
            }
            $button = html_writer::link($lessoneditlink, $text, ['title'=>$title]);
        }
        
        // Кнопка на редактирование, отобразится при помощи js, а ссылка сотрется
        $editbutton = dof_html_writer::div(
            $button,
            'dof-lesson-info-form-edit '.$lessoneditableclass,
            [
                'title' => '',
                'data-iframe' => $lessoneditlink,
                'data-cstreamid' => $this->cstream->id,
                'data-eventid' => $event_id,
                'data-planid' => $plan_id,
            ]
        );
        
        return $editbutton;
    }
    
    /**
     * Возращает html код кнопок создания занятия/события/контроьной точки
     * 
     * @return string
     */
    protected function get_create_actions() : string
    {
        $actionshtml = '';
        $lessonobj = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_lesson(null,null,null);
        $createplan = $lessonobj->can_manipulate_plan($this->cstream->id, $this->cstream->departmentid);
        $createschevent = $lessonobj->can_manipulate_schevent($this->cstream->id, $this->cstream->departmentid);
        $params = [
            'csid' => $this->cstream->id,
            'eventid' => 0,
            'planid' => 0,
            'departmentid' => $this->addvars['departmentid']
        ];
        if( $createplan && $createschevent )
        {
            $params['formtypecode'] = 0;
            $content = $this->dof->modlib('widgets')->modal(
                    $this->dof->get_string('switch_type_lesson__lesson', 'journal'),
                    '',
                    $this->dof->get_string('lessons_manage_lesson_header', 'journal')
                    );
            $lessoncreate_form = dof_html_writer::div(
                    $content,
                    'dof-lessons-actions-create-form',
                    [
                        'title' => '',
                        'data-iframe' => $this->dof->url_im('journal', '/group_journal/forms/lessonedit.php', $params),
                        'data-cstreamid' => $this->cstream->id,
                        'data-eventid' => 0,
                        'data-planid' => 0,
                    ]
                    );
            $actionshtml .= dof_html_writer::div($lessoncreate_form, 'action action-create-lesson dof-lessons-actions-create');
        }
        if( $createschevent )
        {
            $params['formtypecode'] = 1;
            $content = $this->dof->modlib('widgets')->modal(
                    $this->dof->get_string('lessons_actionblock_create_event', 'journal'),
                    '',
                    $this->dof->get_string('lessons_manage_lesson_header', 'journal')
                    );
            $lessoncreate_form = dof_html_writer::div(
                    $content,
                    'dof-lessons-actions-create-form',
                    [
                        'title' => '',
                        'data-iframe' => $this->dof->url_im('journal', '/group_journal/forms/lessonedit.php', $params),
                        'data-cstreamid' => $this->cstream->id,
                        'data-eventid' => 0,
                        'data-planid' => 0,
                    ]
                    );
            $actionshtml .= dof_html_writer::div($lessoncreate_form, 'action action-create-lesson dof-lessons-actions-create');
        }
        if( $createplan )
        {
            $params['formtypecode'] = 2;
            $content = $this->dof->modlib('widgets')->modal(
                    $this->dof->get_string('lessons_actionblock_create_plan', 'journal'),
                    '',
                    $this->dof->get_string('lessons_manage_lesson_header', 'journal')
                    );
            $lessoncreate_form = dof_html_writer::div(
                    $content,
                    'dof-lessons-actions-create-form',
                    [
                        'title' => '',
                        'data-iframe' => $this->dof->url_im('journal', '/group_journal/forms/lessonedit.php', $params),
                        'data-cstreamid' => $this->cstream->id,
                        'data-eventid' => 0,
                        'data-planid' => 0,
                    ]
                    );
            $actionshtml .= dof_html_writer::div($lessoncreate_form, 'action action-create-lesson dof-lessons-actions-create');
        }
        
        return $actionshtml;
    }
    
    /**
     * Генерация таблицы тематического плана
     * 
     * @return string - HTML-код таблицы
     */
    public function render()
    {
        $tablehtml = '';
        $tablehtml .= dof_html_writer::start_tag(
                'table',
                [
                    'class' => 'dof-groupjournal-templans ot-sortable ot-searchable',
                    'border' => 1,
                    'data-cstream' => $this->cstream->id,
                    'data-department' => $this->addvars['departmentid'],
                    'data-showall' => $this->showall,
                    'data-sortable-cells' => json_encode([
                        'ot-sort-date-title' => 'ot-sort-date-data'
                    ]),
                    'data-searchable-filter-parent' => '.dof-groupjournal-templans-actions',
                    'data-searchable-filter-hide' => true,
                    'data-searchable-cells' => json_encode([
                        'ot-filter-date-data' => $this->dof->get_string('date', 'journal'),
                        'ot-filter-passed-data' => $this->dof->get_string('what_passed_on_lesson', 'journal'),
                        'ot-filter-homework-data' => $this->dof->get_string('homework', 'journal')
                    ])
                ]
                );
        
        // Формирование строки хидеров
        $tablehtml .= $this->get_row_headers();
        
        // Получение сгруппированного списка занятий по датам
        $dates = static::$lessonset->group_by_dates(true);
        static::$lessons_count = static::$lessonset->get_count(); 
        
        if ( ! empty($dates) )
        {
            foreach ( $dates as $year => $months )
            {
                foreach ( $months as $month => $days )
                {
                    foreach ( $days as $day => $lessons )
                    {
                        foreach ( $lessons as $lesson )
                        {
                            $tablehtml .= $this->get_row_lesson_info($lesson);
                        }
                    }
                }
            }
        }
 
        
        $tablehtml .= dof_html_writer::end_tag('table');
        
        $actionshtml = '';

        $actionshtml .= $this->get_create_actions();
        
        $img = $this->dof->modlib('ig')->icon('totopl');
        $actionshtml .= dof_html_writer::div($img, 'action-totop action action-navigation');
        
        $img = $this->dof->modlib('ig')->icon('doubleupl');
        $actionshtml .= dof_html_writer::div($img, 'action-uplist action action-navigation');
        
        $img = $this->dof->modlib('ig')->icon('upl');
        $actionshtml .= dof_html_writer::div($img, 'action-up action action-navigation');
        
        $img = $this->dof->modlib('ig')->icon('todownl');
        $actionshtml .= dof_html_writer::div($img, 'action-tobottom action action-navigation');
        
        $img = $this->dof->modlib('ig')->icon('doubledownl');
        $actionshtml .= dof_html_writer::div($img, 'action-downlist action action-navigation');
        
        $img = $this->dof->modlib('ig')->icon('downl');
        $actionshtml .= dof_html_writer::div($img, 'action-down action action-navigation');
        
//         $actionshtml .= dof_html_writer::div('Справка', 'action action-journal-info');
        
        $actionshtml = dof_html_writer::div($actionshtml, 'dof-groupjournal-templans-actions');
        
        $wrappedtable = dof_html_writer::div($tablehtml, 'dof-groupjournal-templans-wrap');
        
        $html = $actionshtml.$wrappedtable;
        
        $this->dof->modlib('nvg')->add_js(
            'im',
            'journal',
            '/group_journal/groupjournal-templans-controller.js',
            false
        );
        $this->dof->modlib('nvg')->add_js(
            'im',
            'journal',
            '/group_journal/groupjournal-controller.js',
            false
        );
        
        return $html;
    }
}
