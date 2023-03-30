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

// Подключение базового класса
require_once('tablebase.php');

/**
 * Журнал предмето-класса. Журнал оценок по учебному процессу.
 *
 * @package    im
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_im_journal_tablecstreamgrades
{
    /**
     * Счетчик форм
     *
     * @param int $form_count
     */
    protected static $form_count = 0;

    /**
     * Счетчик занятий
     *
     * @param int $lesson_count
     */
    protected static $lesson_count = 0;

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
     * GET параметры для формирования ссылок
     *
     * @var array
     */
    protected $addvars = [];

    /**
     * Набор занятий
     *
     * @var dof_lessonset
     */
    protected static $lessonset = null;

    /**
     * @TODO Загружать формы по мере необходимости (Пользователь кликнул на действие, перезагрузили страницу и подгрузили нужную форму с открытой модалкой)
     * Редактируемые состояния
     *
     * @var array
     */
    protected $states = [
        'edit_grades_plan_id' => 0,
        'edit_presence_event_id' => 0,
        'edit_presence_plan_id' => 0,
        'edit_lesson_event_id' => 0,
        'edit_lesson_plan_id' => 0
    ];

    /**
     * Дополнительный html
     *
     * @var string
     */
    protected $additional_html = '';

    /**
     * Текущий пользователь
     *
     * @var stdClass
     */
    protected $currentuser = null;

    /**
     * Список статусов событий, по которым нельзя выставлять посещаемость
     *
     * @var string[]
     */
    protected $scheventpreventstatuses = ['postponed', 'replaced', 'canceled'];

    /**
     * Флаг отображения всех занятий
     *
     * @var bool
     */
    protected $showall = false;

    /**
     * Флаг, обозначающий, что ПУ управления доступом в СДО включена
     *
     * @var bool
     */
    protected $control_active = false;

    /**
     * Открытые занятия слушателей учебного процесса
     *
     * @var array
     */
    protected $opened_lessons_for_students = [];

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
        $this->control_active = $this->dof->modlib('journal')->get_manager('lessonprocess')->is_control_active();
        $this->cstream = $this->dof->storage('cstreams')->get((int)$cstreamid);
        if ( ! empty($addvars['showall']) )
        {
            $this->showall = true;
        } else
        {
            $this->showall = false;
        }
        if ( ! empty($this->cstream) && is_null(static::$lessonset) )
        {// Учебный процесс определен
            // Получение занятий учебного процесса
            static::$lessonset = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_lessons($this->cstream->id, $this->showall);
        }
        $this->addvars = array_merge((array)$addvars, ['csid' => $cstreamid]);

        // Установка редактируемого состояния
        $this->set_state();

        // Иинициализация генератора HTML
        $this->dof->modlib('widgets')->html_writer();

        // Храним в объекте текущего пользователя, чтобы не делать при каждой необходимости запрос в БД
        $this->currentuser = $this->dof->storage('persons')->get_bu();

        // Получение списка пользователей с открытыми им занятиями в журнале
        $this->opened_lessons_for_students = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_opened_lessons_for_students($this->cstream);
    }

    /**
     * Генерация таблицы оценок
     *
     * @return string - HTML-код таблицы
     */
    public function render()
    {
        $html = '';

        // Блок действий
        $html_action = $this->lessons_actionblock();

        $editmode = '';
        if( ! empty($this->states['edit_grades_plan_id']) || ! empty($this->states['edit_presence_event_id']) )
        {
            $editmode = ' edit-mode';
        }

        // Добавление строк подписок слушателей
        $cpasseds = static::$lessonset->get_cpasseds_fullset_lastname();
        $hasitog = false;
        // проверим, что хотя бы у одной подписки есть оценка, выставленная через итоговую ведомость
        foreach ( $cpasseds as $cpassed )
        {
            if ( ! is_null($cpassed->orderid) || ! is_null($cpassed->grade) )
            {
                $hasitog = true;
            }
        }

        $html .= dof_html_writer::start_tag(
            'table',
            [
                'class' => 'dof-groupjournal-grades'.$editmode,
                'border' => 0,
                'data-cstream' => $this->cstream->id,
                'data-department' => $this->addvars['departmentid'],
                'data-showall' => (int)$this->showall
            ]
        );

        // Получение сгруппированного списка занятий по датам
        $dates = static::$lessonset->group_by_dates();

        // Формирование строки с месяцами
        $html .= dof_html_writer::start_tag('tr', ['class' => 'row-months']);
        $html .= dof_html_writer::start_tag('td', ['colspan' => (2 + (int)!$this->showall), 'class' => 'cell-actions-nav']);
        $img = $this->dof->modlib('ig')->icon('upl');
        $html .= dof_html_writer::div($img, 'action-up action');
        $img = $this->dof->modlib('ig')->icon('doubleupl');
        $html .= dof_html_writer::div($img, 'action-uplist action');
        $img = $this->dof->modlib('ig')->icon('totopl');
        $html .= dof_html_writer::div($img, 'action-totop action');
        $img = $this->dof->modlib('ig')->icon('doubleleftl');
        $html .= dof_html_writer::div($img, 'action-tostart action');
        $html .= dof_html_writer::end_tag('td');
        foreach ( $dates as $year => $months )
        {
            foreach ( $months as $month => $days )
            {
                // Подсчет числа ячеек для объединения
                $countlessons = 0;
                foreach ( $days as $day => $lessons )
                {
                    $countlessons += count($lessons);
                }
                $monthname = $this->dof->get_string('monthnum-'.$month);
                $html .= dof_html_writer::start_tag('td', ['colspan' => $countlessons, 'class' => 'cell-month']);
                $html .= dof_html_writer::div($monthname.' '.$year);
                $html .= dof_html_writer::end_tag('td');
            }
        }
        $html .= dof_html_writer::tag('td', '', ['class' => 'cell-empty cell-selfsize']);
        $html .= dof_html_writer::start_tag('td', ['class' => 'cell-actions-nav']);
        $img = $this->dof->modlib('ig')->icon('doublerightl');
        $html .= dof_html_writer::div($img, 'action-toend action');
        $html .= dof_html_writer::end_tag('td');
        $html .= dof_html_writer::end_tag('tr');

        // Формирование строки с датами
        $html .= dof_html_writer::start_tag('tr', ['class' => 'row-days']);
        $html .= dof_html_writer::start_tag('td', ['colspan' => (2 + (int)!$this->showall), 'class' => 'cell-actions-nav']);
        $img = $this->dof->modlib('ig')->icon('downl');
        $html .= dof_html_writer::div($img, 'action-down action');
        $img = $this->dof->modlib('ig')->icon('doubledownl');
        $html .= dof_html_writer::div($img, 'action-downlist action');
        $img = $this->dof->modlib('ig')->icon('todownl');
        $html .= dof_html_writer::div($img, 'action-tobottom action');
        $img = $this->dof->modlib('ig')->icon('leftl');
        $html .= dof_html_writer::div($img, 'action-left action');
        $html .= dof_html_writer::end_tag('td');
        foreach ( $dates as $year => $months )
        {
            foreach ( $months as $month => $days )
            {
                // Подсчет числа ячеек для объединения
                foreach ( $days as $day => $lessons )
                {
                    $html .= dof_html_writer::start_tag('td', ['colspan' => count($lessons), 'class' => 'cell-day']);
                    $html .= dof_html_writer::div($day);
                    $html .= dof_html_writer::end_tag('td');
                }
            }
        }
        $html .= dof_html_writer::tag('td', '', ['class' => 'cell-empty cell-selfsize']);
        $html .= dof_html_writer::start_tag('td', ['class' => 'cell-actions-nav']);
        $img = $this->dof->modlib('ig')->icon('rightl');
        $html .= dof_html_writer::div($img, 'action-right action');
        $html .= dof_html_writer::end_tag('td');
        $html .= dof_html_writer::end_tag('tr');

        // Формирование строки с занятиями
        $html .= dof_html_writer::start_tag('tr', ['class' => 'row-lessons']);
        $html .= dof_html_writer::start_tag('td', ['colspan' => (2 + (int)!$this->showall), 'class' => 'cell-actions']);
        $html .= $html_action;
        $html .= dof_html_writer::end_tag('td');
        $lessonscount = 0;
        foreach ( $dates as $year => $months )
        {
            foreach ( $months as $month => $days )
            {
                foreach ( $days as $day => $lessons )
                {
                    foreach ( $lessons as $lesson )
                    {
                        $lessonscount++;
                        $celllessonhtml = '';
                        if( $planexists = $lesson->plan_exists() )
                        {
                            $planid = $lesson->get_plan()->id;
                        }

                        if ( $eventexists = $lesson->event_exists() )
                        {
                            $eventid = $lesson->get_event()->id;
                        }

                        if ( ! empty($this->states['edit_grades_plan_id']) )
                        {// Редактирование оценок
                            if( $planexists && $this->states['edit_grades_plan_id'] == $planid )
                            {
                                $celllessonhtml = dof_html_writer::start_tag('td', ['class' => 'cell-lesson cell-lesson-edit']);
                                $celllessonhtml .= dof_html_writer::div($this->lesson_infoblock_edit($lesson, ['type'=>'grades_edit']));
                                $celllessonhtml .= dof_html_writer::end_tag('td');
                            }
                        } elseif ( ! empty($this->states['edit_presence_event_id']) )
                        {
                            if( $eventexists && $this->states['edit_presence_event_id'] == $eventid )
                            {
                                $celllessonhtml = dof_html_writer::start_tag('td', ['class' => 'cell-lesson cell-lesson-edit']);
                                $celllessonhtml .= dof_html_writer::div($this->lesson_infoblock_edit($lesson, ['type'=>'presence_edit']));
                                $celllessonhtml .= dof_html_writer::end_tag('td');
                            }
                        }

                        if( ! empty($celllessonhtml) )
                        {
                            $html .= $celllessonhtml;
                        } else
                        {// Обычная ячейка
                            $html .= dof_html_writer::start_tag('td', ['class' => 'cell-lesson']);
                            $html .= dof_html_writer::div($this->lesson_infoblock($lesson), 'dof_lesson_cell');
                            $html .= dof_html_writer::end_tag('td');
                        }
                    }
                }
            }
        }
        // выбрать вариант расчета оценки в последней колонке журнала успеваемости и посещаемости
        $summarycelltype = $this->dof->storage('config')->get_config_value(
            'switch_summary_cell_type',
            'im',
            'journal',
            $this->addvars['departmentid']);
		// Проверим задана ли настройка и находится она в массиве разрешенных
		if (!in_array($summarycelltype, ['sum', 'avg'])) {
			$summarycelltype = 'avg';
		}

        $html .= dof_html_writer::tag('td', '', ['class' => 'cell-empty cell-selfsize']);
        $html .= dof_html_writer::start_tag('td', ['class' => 'cell-empty dof-summary-cell']);
        if ( $hasitog )
        {
            $html .= dof_html_writer::div(
                $this->dof->get_string(
                    'grade_' . $summarycelltype . '_short',
                    'journal'
                ),
                'dof-summary-cell-avg');
            $html .= dof_html_writer::div($this->dof->get_string('grade_final_short', 'journal'), 'dof-summary-cell-final');
        } else
        {
            $html .= dof_html_writer::div(
                $this->dof->get_string(
                    'grade_' . $summarycelltype . '_short',
                    'journal'
                ),
                'dof-summary-cell-avg dof-summary-cell-avg-full');
        }
        $html .= dof_html_writer::end_tag('td');
        $html .= dof_html_writer::end_tag('tr');


        $html .= dof_html_writer::tag(
            'tr',
            dof_html_writer::tag('td', '', ['colspan' => $lessonscount+4, 'class' => 'cell-empty']),
            ['class' => 'row-divider']
        );


        $ratingdata = $this->dof->im('cstreams')->get_cstream_grades($this->cstream->id, false);
        $num = 0;

        foreach ( $cpasseds as $cpassedid => $cpassed )
        {
            // Формирование строки с подпиской на учебный процесс
            $html .= dof_html_writer::start_tag('tr', ['class' => 'row-cpassed']);
            $html .= dof_html_writer::start_tag('td', ['class' => 'cell-cpassednum']);
            $html .= dof_html_writer::div(++$num);
            $html .= dof_html_writer::end_tag('td');
            $html .= dof_html_writer::start_tag('td', ['class' => 'cell-cpassedinfo']);
            $html .= dof_html_writer::div($this->cpassed_infoblock($cpassed));
            $html .= dof_html_writer::end_tag('td');
            if (!$this->showall && $num == 1)
            {
                $addvars = $this->addvars;
                $addvars['showall'] = 1;
                $showallurl = $this->dof->url_im('journal', '/group_journal/index.php', $addvars);
                $showallspan = dof_html_writer::span($this->dof->get_string('display_all_lessons', 'journal'));
                $showalllink = dof_html_writer::link($showallurl, $showallspan);
                $html .= dof_html_writer::tag('td', $showalllink, [
                    'rowspan' => count($cpasseds)+1,
                    'class' => 'cell-showall'
                ]);
            }

            foreach ( $dates as $year => $months )
            {
                foreach ( $months as $month => $days )
                {
                    // Подсчет числа ячеек для объединения
                    foreach ( $days as $day => $lessons )
                    {
                        foreach ( $lessons as $lesson )
                        {

                            $celllessonhtml = '';
                            if( $planexists = $lesson->plan_exists() )
                            {
                                $planid = $lesson->get_plan()->id;
                            }

                            if ( $eventexists = $lesson->event_exists() )
                            {
                                $eventid = $lesson->get_event()->id;
                            }

                            if ( ! empty($this->states['edit_grades_plan_id']) )
                            {// Редактирование оценок
                                if( $planexists && $this->states['edit_grades_plan_id'] == $planid )
                                {
                                    $celllessonhtml = dof_html_writer::start_tag('td', ['class' => 'cell-lesson-cpassed cell-lesson-cpassed-edit']);
                                    $celllessonhtml .= dof_html_writer::div($this->cpassed_gradecell_edit($cpassed, $lesson));
                                    $celllessonhtml .= dof_html_writer::end_tag('td');
                                }
                            } elseif ( ! empty($this->states['edit_presence_event_id']) )
                            {
                                if( $eventexists && $this->states['edit_presence_event_id'] == $eventid )
                                {
                                    $celllessonhtml = dof_html_writer::start_tag('td', ['class' => 'cell-lesson-cpassed cell-lesson-cpassed-edit']);
                                    $celllessonhtml .= dof_html_writer::div(
                                        $this->cpassed_presencecell_edit($cpassed, $lesson)
                                    );
                                    $celllessonhtml .= dof_html_writer::end_tag('td');
                                }
                            }


                            if( ! empty($celllessonhtml) )
                            {
                                $html .= $celllessonhtml;
                            } else
                            {// Обычная ячейка
                                $html .= dof_html_writer::start_tag('td', ['class' => 'cell-lesson-cpassed']);
                                $html .= dof_html_writer::div($this->cpassed_gradecell($cpassed, $lesson));
                                $html .= dof_html_writer::end_tag('td');
                            }
                        }
                    }
                }
            }

            // Итоговая оценка
            $html .= dof_html_writer::tag('td', '', ['class' => 'cell-empty cell-selfsize']);
            $html .= dof_html_writer::start_tag('td', ['class' => 'cell-lesson-cpassed-gradesummary']);

            $summary = '';
            if (($summarycelltype == 'sum')) {
				if (isset($ratingdata->users[$cpassed->studentid]->sumgrade)) {
                	$summary = $ratingdata->users[$cpassed->studentid]->sumgrade;
				}
            } elseif ($summarycelltype == 'avg') {
				if (isset($ratingdata->users[$cpassed->studentid]->grade)) {
                	$summary = $ratingdata->users[$cpassed->studentid]->grade;
				}
            }

            if ( $hasitog )
            {
                $html .= dof_html_writer::div($summary, 'dof-cpassed-summary-cell-avg');
                $html .= dof_html_writer::div(
                        $cpassed->grade,
                        'dof-cpassed-summary-cell-final'.(empty($cpassed->repeatid) && $cpassed->status=='failed' ? ' overgraded' : '')
                        );
            } else
            {
                $html .= dof_html_writer::div(empty($summary) ? '-' : $summary, 'dof-cpassed-summary-cell-avg dof-cpassed-summary-cell-avg-full');
            }

            $html .= dof_html_writer::end_tag('td');
            $html .= dof_html_writer::end_tag('tr');
        }

        if (!$this->showall)
        {
            // строка растягивающаяся под минимальную ширину таблицы
            $html .= dof_html_writer::start_tag('tr', ['class' => 'row-filler', 'data-count' => count($cpasseds)]);
            $html .= dof_html_writer::tag('td', '', ['colspan' => 2]);
            $html .= dof_html_writer::tag('td', '', ['colspan' => $lessonscount + 2]);
            $html .= dof_html_writer::end_tag('tr');
        }

        $html .= dof_html_writer::end_tag('table');
        $html .= dof_html_writer::div($this->additional_html);

        $html = dof_html_writer::div($html, 'dof-groupjournal-grades-wrap');


        $alllessons = $this->dof->get_string('all_lessons', 'journal');
        $lastlessons = $this->dof->get_string('last_lessons', 'journal');
        $contextmenu = $this->dof->modlib('widgets')->context_menu(
            dof_html_writer::div(
                dof_html_writer::tag('span', $this->showall ? $alllessons : $lastlessons)
            ),
            [
                'direction' => 'down'
            ]
        );
        $item = $contextmenu->get_item_link();
        $item->text = $this->showall ? $lastlessons : $alllessons;
        $addvars = $this->addvars;
        $addvars['showall'] = (int)!$this->showall;
        $item->url = $this->dof->url_im('journal', '/group_journal/index.php', $addvars);
        $contextmenu->add_items([$item]);
        $contextmenuhtml = dof_html_writer::div(
            dof_html_writer::div($this->dof->get_string('displayed_lessons', 'journal')) .
            $contextmenu->render(),
            'lessons_list_menu'
        );

        $html =  $contextmenuhtml . $html;

        $this->dof->modlib('nvg')->add_js(
            'im',
            'journal',
            '/group_journal/groupjournal-grades-controller.js',
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

    /**
     * Установка редактируемого состояния
     *
     * @return void
     */
    protected function set_state()
    {
        foreach ( $this->states as $key => $value )
        {
            if ( isset($this->addvars[$key]) &&
                    ! empty($this->addvars[$key]) )
            {
                $this->states[$key] = $this->addvars[$key];
            }
        }
    }

    /**
     * Получить блок информации о занятии
     *
     * @param dof_lesson $lesson - Занятие
     *
     * @return string - HTML-код
     */
    protected function lesson_infoblock(dof_lesson $lesson)
    {
        // Состояние блока (Показать/Скрыть)
        $state_show_dropblock = false;

        // Параметры
        $event_id = 0;
        $plan_id = 0;
        $class_status = '';

        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();

        $addvars = array_merge($this->addvars, ['csid' => $this->cstream->id]);
        if ( $lesson->plan_exists() )
        {
            $addvars = array_merge($addvars, ['planid' => $lesson->get_plan()->id]);
            $plan_id = $lesson->get_plan()->id;
        }
        if ( $lesson->event_exists() )
        {
            $addvars = array_merge($addvars, ['eventid' => $lesson->get_event()->id]);
            $event_id = $lesson->get_event()->id;
        }

        // Генерация ссылки (удаляется JS-ом) и формы РЕДАКТИРОВАНИЯ/ПРОСМОТРА занятия
        $params = [
            'csid' => $this->cstream->id,
            'eventid' => $event_id,
            'planid' => $plan_id,
            'departmentid' => $this->addvars['departmentid'],
            'page_layout' => 'popup'
        ];

        // Формирование html кода формы ПРОСМОТРА/РЕДАКТИРОВАНИЯ занятия
        $lessonedit_form = '';
        if ( ($lesson->plan_exists() && ! in_array($lesson->get_plan()->linktype, ['ages', 'plan'])) || !$lesson->plan_exists() )
        {
            // Проверка права манипуляции с занятием
            $anyeditaccess = $lesson->can_manipulate_plan($this->cstream->id, $this->addvars['departmentid']) ||
                $lesson->can_manipulate_schevent($this->cstream->id, $this->addvars['departmentid']);
            if ( $anyeditaccess )
            {
                // Есть право на редактирование, отобразим иконку редактирования
                $iconlessonform = $this->dof->modlib('ig')->icon('edit');
            } else
            {
                // Нет права на редактирование, отобразим кнопку просмотра
                $iconlessonform = $this->dof->modlib('ig')->icon('viewfull');
            }

            // Формирование модалки
            $content = $this->dof->modlib('widgets')->modal(
                $iconlessonform,
                '',
                $this->dof->get_string('lessons_manage_lesson_header', 'journal')
            );

            // Кнопка Просмотра/Редактирования
            $lessonedit_form .= dof_html_writer::div(
                    $content,
                    'dof-lesson-info-form-edit hidden',
                    [
                        'title' => '',
                        'data-iframe' => $this->dof->url_im('journal', '/group_journal/forms/lessonedit.php', $params),
                        'data-cstreamid' => $this->cstream->id,
                        'data-eventid' => $event_id,
                        'data-planid' => $plan_id
                    ]
                    );
        }

        // ФОРМА ПЕРЕКЛИЧКИ
        $requirepresencereason = $this->dof->storage('config')->get_config_value(
            'require_presence_reason',
            'im', 'journal',
            $this->cstream->departmentid
        );

        $rollcallformclass = 'disabled';
        $rollcallcontent = $this->dof->get_string('lesson_rollcall_link', 'journal');
        $rollcalltitle = $this->dof->get_string('lesson_event_notexist', 'journal');
        if ( $lesson->event_exists() &&
                (! in_array($lesson->get_event()->status, $this->scheventpreventstatuses)) &&
                $this->dof->modlib('journal')->get_manager('lessonprocess')->can_save_presence($lesson->get_event()->id, $this->addvars['departmentid']) )
        {// К занятию привязано календарное событие
            $rollcalltitle = $this->dof->get_string('lesson_rollcall_link', 'journal');
            if ( empty($requirepresencereason) )
            {
                //класс, отслеживаемый для внеднения переклички в основную форму с оценками
                $rollcallformclass = 'internal-form';

                // кнопка переклички
                $rollcallformhtml = dof_html_writer::div(
                        $rollcallcontent,
                        'dof-lesson-info-rollcall-form '.$rollcallformclass,
                        [
                            'title' => $rollcalltitle,
                            'data-event-id' => $lesson->get_event()->id
                        ]
                        );
            } else
            {
                // Перекличка в отдельной форме
                $rollcallformclass = 'fullsize-form';

                $rollcallcontent = $this->dof->modlib('widgets')->modal(
                        $this->dof->get_string('lesson_rollcall_link', 'journal'),
                        '',
                        $this->dof->get_string('page_form_students_presence', 'journal'),
                        ['show' => false]
                        );
                $params = [
                    'csid' => $this->cstream->id,
                    'eventid' => $lesson->get_event()->id,
                    'planid' => $plan_id,
                    'departmentid' => $this->addvars['departmentid']
                ];
                // Ссылка на форму переклички (удаляется при обработке JS)
                $rollcallformhtml = dof_html_writer::link(
                        $this->dof->url_im('journal', '/group_journal/forms/rollcall.php', $params),
                        $rollcalltitle,
                        ['class' => 'dof-lesson-info-rollcall-form '.$rollcallformclass]
                        );

                // Кнопка переклички
                $rollcallformhtml .= dof_html_writer::div(
                        $rollcallcontent,
                        'dof-lesson-info-rollcall-form hidden '.$rollcallformclass,
                        [
                            'title' => $rollcalltitle,
                            'data-iframe' => $this->dof->url_im('journal', '/group_journal/forms/rollcall.php', $params),
                            'data-cstreamid' => $this->cstream->id,
                            'data-eventid' => $lesson->get_event()->id,
                            'data-planid' => $plan_id,
                        ]
                        );
            }
        }

        // Редактирование оценок занятия
        $editurl = $this->dof->url_im('journal', '/group_journal/index.php', $addvars).'#'.$lesson->get_identifier();

        // Получение названия
        $title = dof_html_writer::span($lesson->get_name(), 'dof-lesson-info-titletext');

        // Получение даты проведения занятия
        $date = dof_html_writer::div(
                dof_userdate($lesson->get_startdate(), '%d.%m.%Y %H:%M', $usertimezone),
                'dof-lesson-info-date'
                );
        $dateday = dof_html_writer::div(
            $lesson->get_indexnum(),
            'dof-lesson-info-dateday'
        );


        // Кнопка закрыть
        $closeimg = $this->dof->modlib('ig')->icon('close');
        $closelink = dof_html_writer::label($closeimg, 'dof_dropblock_'.$lesson->get_identifier());

        // Дополнительные классы
        $class = '';
        if ( $lesson->event_exists() && $lesson->plan_exists())
        {
            $class = ' lesson';
        } else
        {
            if( $lesson->event_exists() )
            {
                $event = $lesson->get_event();
                if( $event->status == 'replaced' )
                {
                    $class .= ' replacedevent';
                } else
                {
                    $class .= ' event';
                }
            }
            if( $lesson->plan_exists() )
            {
                $plan = $lesson->get_plan();
                if( $plan->linktype != 'cstreams' )
                {
                    $class .= ' checkpoint';
                } else
                {
                    $class .= ' plan';
                }
            }
        }
        if ( $lesson->is_completed() )
        {// Заниятие завершено
            $class .= ' completed';
        }

        // Статус
        $lessonstatus = $lesson->get_eventstatus_localized();
        if ( empty($lessonstatus) )
        {// Статус не указан
            $lessonstatus = $this->dof->get_string('lesson_infoblock_status_empty', 'journal');
        }
        $lessonstatus = dof_html_writer::div('', 'dof-lesson-info-homework-icon').
                        dof_html_writer::div($lessonstatus, 'dof-lesson-info-status-content');

        // Домашнее задание
        $homework = $lesson->get_homework();
        if ( empty($homework) )
        {// Домашнее задание не указано
            $homework = $this->dof->get_string('lesson_infoblock_homework_empty', 'journal');
        }
        $homework = dof_html_writer::div($homework, 'dof-lesson-info-homework-content');

        // Добавление кнопок контроля доступа к занятию
        $authcontroldropmenuhtml = '';
        $authcontroldropmenuhtmlcontent = '';
        if ( $this->control_active && $lesson->mdl_gradeitem_exists() )
        {
            // получение грейдитема
            $amagradeitem = $lesson->get_mdl_gradeitem();
            $gradeitem = $amagradeitem->get();

            $authcontroldropmenuhtml .= dof_html_writer::div('', 'dof-lesson-info-divider');
            if ( $gradeitem->is_external_item() )
            {
                $authcontroldropmenuhtml .= dof_html_writer::div(
                        dof_html_writer::link(
                                $amagradeitem->get_link_to_element_view(),
                                $this->dof->get_string('gradeitem_info', 'journal', $gradeitem->itemname),
                                ['target' => '_blank']
                                ),
                        'dof-lesson-info-gradeitemname'
                        );

                // включен контролируемый режим доступа в СДО
                // добавление кнопок управления доступом
                $linkparams = [
                    'class' => 'dof-mdlgradeitem-access-tablegrades-lesson-ajax',
                    'data-planid' => $lesson->get_plan()->id,
                    'data-cstreamid' => $this->cstream->id,
                    'data-changeto' => 1
                ];

                // открыть доступ к элементу
                $link = dof_html_writer::link(
                        '/',
                        dof_html_writer::div($this->dof->get_string('local_authcontrol_open_access', 'journal')),
                        $linkparams);
                $authcontroldropmenuhtmlcontent .= dof_html_writer::div( $link, 'dof-mdlgradeitem-info-menu-item');

                $linkparams['data-changeto'] = 0;

                // закрыть доступ к элементу
                $link = dof_html_writer::link(
                        '/',
                        dof_html_writer::div($this->dof->get_string('local_authcontrol_close_access', 'journal')),
                        $linkparams);
                $authcontroldropmenuhtmlcontent .= dof_html_writer::div($link, 'dof-mdlgradeitem-info-menu-item dof-mdlgradeitem-info-menu-item-last');
            } else
            {
                $authcontroldropmenuhtml .= dof_html_writer::div(
                        dof_html_writer::link(
                                $amagradeitem->get_link_to_gradebook(),
                                $this->dof->get_string('gradeitem_info', 'journal', $gradeitem->itemname),
                                ['target' => '_blank']
                                ),
                        'dof-lesson-info-gradeitemname'
                        );
            }
        }
        $authcontroldropmenuhtml .= dof_html_writer::div($authcontroldropmenuhtmlcontent, 'dropmenumdlgradeitem');

        $lessoninfo = '';
        $lessoninfo .= dof_html_writer::start_div('dof-lesson-info-wrapper');
        $lessoninfo .= dof_html_writer::start_div('dof-lesson-info'.$class);
        $lessoninfo .= dof_html_writer::start_div('dof-lesson-info-header');
        $datedropblock = $this->dof->modlib('widgets')->
            dropblock($dateday, $date, '', ['uniqueid' => 'fulldate_'.$lesson->get_identifier(), 'fixed' => true]);
        $lessoninfo .= dof_html_writer::div($datedropblock, 'dof-lesson-info-startdate');
        $lessoninfo .= dof_html_writer::div($title, 'dof-lesson-info-title');
        if( ! empty($rollcallformhtml) )
        {
            $lessoninfo .= dof_html_writer::div($rollcallformhtml, 'dof-lesson-info-rollcall');
        }
        $lessoninfo .= dof_html_writer::div($lessonedit_form, 'dof-lesson-info-edit');
        $lessoninfo .= dof_html_writer::div($closelink, 'dof-lesson-info-close');
        $lessoninfo .= dof_html_writer::end_div();
        $lessoninfo .= dof_html_writer::start_div('dof-lesson-info-content');
        $lessoninfo .= dof_html_writer::div($lessonstatus, 'dof-lesson-info-status');
        $lessoninfo .= dof_html_writer::div(
            $this->dof->modlib('ig')->icon('homeworkg').$homework,
            'dof-lesson-info-homework'
        );
        $lessoninfo .= dof_html_writer::end_div();
        $lessoninfo .= dof_html_writer::div($authcontroldropmenuhtml, 'dof-lesson-info-authcontrol');
        $lessoninfo .= dof_html_writer::end_div();
        $lessoninfo .= dof_html_writer::end_div();


        // Выпадающий блок с информацией о занятии
        $label = dof_html_writer::div('', 'dof-lesson-info-label');
        $html = $this->dof->modlib('widgets')->dropblock($label, $lessoninfo, '', ['uniqueid' => $lesson->get_identifier(), 'fixed' => true, 'show' => $state_show_dropblock]);

        // Иконка наличия домашнего задания
        $homeworkdata = $lesson->get_homework();
        if ( ! empty($homeworkdata) )
        {// Домашнее задание определено
            $homework = dof_html_writer::div(
                $this->dof->modlib('ig')->icon('homeworkg'),
                'dof-lesson-homework-icon'
            );
            $html .= dof_html_writer::div($homework, 'dof-lesson-cell-homework');
        }

        // Иконка редактирования занятия
        $edit = dof_html_writer::div(
            $this->dof->modlib('ig')->icon('editminig'),
            'dof-lesson-edit-icon'
        );
        $edita = dof_html_writer::div(
            $this->dof->modlib('ig')->icon('editmini'),
            'dof-lesson-edit-icon-active'
        );

        $gradeitemicon = '';
        if ( $lesson->plan_exists() && $lesson->has_gradeitem() )
        {
            $gradeitemicon .= $this->dof->modlib('ig')->icon('moodleblue');
        }
        $html .= dof_html_writer::div($gradeitemicon, 'dof-lesson-gradeitem-icon');

        if ( $lesson->plan_exists() &&
                $this->dof->modlib('journal')
                    ->get_manager('lessonprocess')
                    ->can_save_grades($lesson->get_plan()->id, $this->cstream->id, $this->addvars['departmentid']) )
        {
            $html .= dof_html_writer::link($editurl, $edit . $edita,
                    [
                        'class' => 'dof-lesson-edit-cell',
                        'data-plan' => $plan_id
                    ]);
        } else
        {
            $html .= dof_html_writer::div($edit . $edita, 'dof-lesson-edit-cell');
        }

        // Иконка статуса занятия
        $class_status = $this->dof->get_string('lesson_infoblock_status_icon_incomplete', 'journal');
        if ( $lesson->is_completed() )
        {// Заниятие завершено
            $class_status = $this->dof->get_string('lesson_infoblock_status_icon_complete', 'journal');
        }
        $html .= dof_html_writer::div(
            '',
            'dof-lesson-status-icon'.$class
        );

        // Статус активности
        $html .= dof_html_writer::div(
                '',
                'dof-lesson-status-js-controller ' . $class_status
                );

        return $html;
    }

    /**
     * Получить блок информации о занятии
     *
     * @param dof_lesson $lesson - Занятие
     *
     * @return string - HTML-код
     */
    protected function lesson_infoblock_edit(dof_lesson $lesson, $options=[])
    {
        $html = '';

        // Получение стандартного блока вывода информации о занятии
        $html .= $this->lesson_infoblock($lesson);

        $attributes = [];

        if( ! empty($options['type']) )
        {
            $attributes['data-edit-type'] = $options['type'];
        }

        $event = $lesson->get_event();
        if( ! empty($event) )
        {
            $attributes['data-event'] = $event->id;
        }

        $plan = $lesson->get_plan();
        if( ! empty($plan) )
        {
            $attributes['data-plan'] = $plan->id;
        }

        $this->additional_html .= dof_html_writer::div(
            '',
            'lesson-grades-wrapper-save',
            $attributes
        );

        return $html;
    }

    /**
     * Получить блок управлением списком занятий
     *
     * @return string - HTML-код блока
     */
    protected function lessons_actionblock()
    {
        // Сортировка подписок по фамилии и имени
        $label = dof_html_writer::div(
            $this->dof->get_string('lessons_actionblock_cpasseds_label', 'journal'),
            'dof-cpassed-sortblock-label'
        );
        $firstname = dof_html_writer::div(
            dof_html_writer::div(
                $this->dof->get_string('lessons_actionblock_cpasseds_firstname', 'journal')
            ),
            'dof-cpassed-sortblock-firstname'
        );
        $lastname = dof_html_writer::div(
            dof_html_writer::div(
                $this->dof->get_string('lessons_actionblock_cpasseds_lastname', 'journal')
            ),
            'dof-cpassed-sortblock-lastname active'
        );


        // Генерация ссылки (удаляется JS-ом) и формы создания занятия
        $params = [
            'csid' => $this->cstream->id,
            'eventid' => 0,
            'planid' => 0,
            'departmentid' => $this->addvars['departmentid']
        ];
        $content = $this->dof->modlib('widgets')->modal(
                '+',
                '',
                $this->dof->get_string('lessons_manage_lesson_header', 'journal')
                );


        $create = '';
        $lessonobj = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_lesson(null,null,null);
        if( $lessonobj->createform_allowed($this->cstream->id, null, null, $this->addvars['departmentid']) )
        {
            $lessoncreate_form = dof_html_writer::link($this->dof->url_im('journal', '/group_journal/forms/lessonedit.php', $params), '+');

            // Кнопка формы
            $lessoncreate_form .= dof_html_writer::div(
                    $content,
                    'dof-lessons-actions-create-form hidden',
                    [
                        'title' => '',
                        'data-iframe' => $this->dof->url_im('journal', '/group_journal/forms/lessonedit.php', $params),
                        'data-cstreamid' => $this->cstream->id,
                        'data-eventid' => 0,
                        'data-planid' => 0,
                    ]
                    );
            $create = dof_html_writer::div($lessoncreate_form, 'dof-lessons-actions-create');
        }

        return dof_html_writer::div(
                $label.$lastname.$firstname.$create,
                'dof-lessons-actions-wrapper'
        );
    }

    /**
     * Получить блок информации о работе подписки на занятии
     *
     * @param stdClass $cpassed - Подписка на учебный процесс
     * @param dof_lesson $lesson - Занятие
     *
     * @return string - HTML-код
     */
    protected function cpassed_gradecell(stdClass $cpassed, dof_lesson $lesson)
    {
        // Подготовка ячейки и информационного блока
        $cellcontent = '';
        $classes = '';
        $cellclasses = '';
        $plan_id = 0;
        $event_id = 0;

        // Получение данных о работе на занятии
        $gradedata = $lesson->get_listener_gradedata($cpassed->id);

        // GET-параметры для ссылок в
        $addvars = array_merge($this->addvars, ['csid' => $this->cstream->id]);
        if ( $lesson->plan_exists() )
        {
            $addvars = array_merge($addvars, ['planid' => $lesson->get_plan()->id]);
            $plan_id = $lesson->get_plan()->id;
        }
        if ( $lesson->event_exists() )
        {
            $addvars = array_merge($addvars, ['eventid' => $lesson->get_event()->id]);
            $event_id = $lesson->get_event()->id;
        }

        // Данные об оценке
        if ( ! empty($gradedata) && $gradedata->overenroltime === false )
        {
            // Получение даты проведения занятия
            $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
            $date = dof_html_writer::div(
                dof_userdate($lesson->get_startdate(), '%d %B %Y %H:%M', $usertimezone),
                'dof-grade-info-eventdate'
            );

            // ФИО Слушателя
            $fullname = $this->dof->storage('persons')->get_fullname($cpassed->studentid);
            $fullname = dof_html_writer::div($fullname, 'dof-cpassed-info-fullname');

            $editlink = '';
            if ( (! empty($plan_id) &&
                    $this->dof->modlib('journal')->get_manager('lessonprocess')->can_save_grades($plan_id, $this->cstream->id, $this->addvars['departmentid'])) ||
                        (! empty($event_id) &&
                            (! in_array($lesson->get_event()->status, $this->scheventpreventstatuses)) &&
                            $this->dof->modlib('journal')->get_manager('lessonprocess')->can_save_presence($event_id, $this->addvars['departmentid']))
                    )
            {
                // Генерация ссылки (удаляется JS-ом) и формы для редактирования прогресса ученика (оценка + посещаемость)
                $params = [
                    'csid' => $this->cstream->id,
                    'eventid' => $event_id,
                    'planid' => $plan_id,
                    'cpassedid' => $cpassed->id,
                    'departmentid' => $this->addvars['departmentid']
                ];
                $content = $this->dof->modlib('widgets')->modal(
                        $this->dof->modlib('ig')->icon('edit'),
                        '',
                        $this->dof->get_string('page_form_student_progress', 'journal')
                        );
                $userprogress_form = $this->dof->modlib('ig')->icon('edit', $this->dof->url_im('journal', '/group_journal/forms/userprogress.php', $params));

                // Кнопка формы
                $userprogress_form .= dof_html_writer::div(
                        $content,
                        'dof-lesson-info-userprogress-form hidden',
                        [
                            'title' => '',
                            'data-iframe' => $this->dof->url_im('journal', '/group_journal/forms/userprogress.php', $params),
                            'data-cstreamid' => $this->cstream->id,
                            'data-eventid' => $event_id,
                            'data-planid' => $plan_id,
                        ]
                        );
                $editlink .= $userprogress_form;
            }

            // Кнопка закрыть
            $closeimg = $this->dof->modlib('ig')->icon('close');
            $closelink = dof_html_writer::label($closeimg, 'dof_dropblock_'.$lesson->get_identifier().'_cp'.$cpassed->id);

            // Данные о комментариях
            $comments_block = '';
            $comments_gradecell = '';

            // Данные о присутствии на занятии
            $presence_gradecell = '';
            $presence_block = '';

            // Проверка, что студент обучался в эту дату
            $presence = true;
            if ( $lesson->event_exists() )
            {
                $presence = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_present_status($cpassed->studentid, $lesson->get_event()->id);
            }
            if ( $presence === false && ($lesson->get_event()->status != 'replaced') )
            {
                // Добавление серого фона
                $cellclasses .= 'dof-grade-cell-not-studied-background';

                // Студент не обучался на этом занятии
                $presence_block .= dof_html_writer::start_div('dof-grade-cell-presence');
                $presence_block .= dof_html_writer::div(
                        $this->dof->get_string('cpassed_infoblock_presence_not_studied', 'journal'),
                        'dof-grade-cell-presence-no'
                        );
                $presence_block .= dof_html_writer::div(
                        $this->dof->get_string('cpassed_infoblock_presence_not_studied_info', 'journal'),
                        'dof-grade-cell-presence-no'
                        );
                $presence_block .= dof_html_writer::end_div();

                $presence_gradecell .= dof_html_writer::start_div('dof-grade-cell-not-studied');
                $presence_gradecell .= $this->dof->get_string('cpassed_infoblock_presence_not_studied', 'journal');
                $presence_gradecell .= dof_html_writer::end_div();
            } else
            {
                if ( ! empty($gradedata->presence) )
                {// Посещаемость найдена

                    if ( ! empty($gradedata->presence->item) )
                    {// Указана посещаемость
                        // Получение причины отсутствия
                        $reasontext = '';
                        if ( ! empty($gradedata->presence->item->reasonid) )
                        {// Указана причина отсутствия

                            // Получение текста причины отсутствия
                            $reasontext = $this->dof->storage('schabsenteeism')->
                                get_name($gradedata->presence->item->reasonid);
                            if ( $reasontext )
                            {// Текст причины найден
                                $reasontext = dof_html_writer::div($reasontext, 'dof-grade-cell-presence-reason');
                            }
                        }


                        if ( $gradedata->presence->item->present == 0 )
                        {// Отсутствие на занятии
                            if ( empty($gradedata->presence->order) )
                            {// Посещаемость не подкреплена приказом
                                $presence_block .= dof_html_writer::start_div('dof-grade-cell-presence fake');
                                if ( empty($reasontext) )
                                {
                                    $presence_block .= dof_html_writer::div(
                                            $this->dof->get_string('form_presence_field_select_presence_no', 'journal'),
                                            'dof-grade-cell-presence-no'
                                            );
                                } else
                                {
                                    $presence_block .= dof_html_writer::div(
                                            $reasontext,
                                            'dof-grade-cell-presence-reason'
                                            );
                                }
                                $presence_block .= dof_html_writer::div(
                                        $this->dof->get_string('cpassed_infoblock_presence_fake_description', 'journal'),
                                        'dof-grade-cell-presence-fake'
                                        );
                                $presence_block .= dof_html_writer::end_div();

                                $presence_gradecell .= dof_html_writer::start_div('dof-grade-cell-presence fake');
                                $presence_gradecell .= $this->dof->get_string('cpassed_infoblock_presence_no', 'journal');
                                $presence_gradecell .= $this->dof->get_string('cpassed_infoblock_presence_fake_description', 'journal');
                                $presence_gradecell .= dof_html_writer::end_div();
                            } else
                            {// Посещаемость подкреплена приказом
                                $presence_block .= dof_html_writer::start_div('dof-grade-cell-presence');
                                $presence_block .= dof_html_writer::div(
                                    $this->dof->get_string('cpassed_infoblock_presence_no', 'journal'),
                                    'dof-grade-cell-presence-no'
                                );
                                if ( empty($reasontext) )
                                {
                                    $presence_block .= dof_html_writer::div(
                                        $this->dof->get_string('form_presence_field_select_presence_no', 'journal'),
                                        'dof-grade-cell-presence-no'
                                    );
                                } else
                                {
                                    $presence_block .= dof_html_writer::div(
                                        $reasontext,
                                        'dof-grade-cell-presence-reason'
                                    );
                                }
                                $presence_block .= dof_html_writer::end_div();

                                $presence_gradecell .= dof_html_writer::start_div('dof-grade-cell-presence');
                                $presence_gradecell .= $this->dof->get_string('cpassed_infoblock_presence_no', 'journal');
                                $presence_gradecell .= dof_html_writer::end_div();
                            }
                        }
                    }
                }

                if ( ! empty($gradedata->comments) )
                {// Комментарии найдены
                    $comments = '';
                    $commentstext = '';
                    foreach ( $gradedata->comments as $comment )
                    {
                        $commentstext .= $comment->text;
                        $comments .= dof_html_writer::div($comment->text, 'dof-cpassed-info-comment');
                    }

                    if( ! empty($commentstext) )
                    {
                        // Иконка наличия комментария
                        $comments_block .= dof_html_writer::start_div('dof-cpassed-info-cell-hascomment');
                        $comments_block .= dof_html_writer::div($this->dof->modlib('ig')->icon('comment'));
                        $comments_block .= dof_html_writer::div($comments, 'dof-cpassed-info-comments');
                        $comments_block .= dof_html_writer::end_div();

                        // Отображение значка в ячейке о присутствии комментария
                        $comments_gradecell .= dof_html_writer::start_div('dof-cpassed-info-cell-hascomment');
                        $comments_gradecell .= $this->dof->modlib('ig')->icon('comment');
                        $comments_gradecell .= dof_html_writer::end_div();
                    }
                }
            }

            // Данные о персональном домашнем задании
            $homework = '';
//             $homeworkdata = $lesson->get_homework();
//             if ( ! empty($homeworkdata) )
//             {// Домашнее задание определено
//                 $homework .= dof_html_writer::div(
//                     $this->dof->modlib('ig')->icon('homeworkg'),
//                     'dof-lesson-homework-icon'
//                 );
//             }

            // Оценки
            $grades_gradecell = '';
            $grades_block = '';
            foreach ( $gradedata->grades as $grade )
            {
                if ( ! empty($grade->item) )
                {
                    if ( strlen($grade->item->grade) && ! empty($grade->item->workingoff))
                    {
                        $additionalclass = ' dof-grade-cell-grades-grade-workedout';
                    } else
                    {
                        $additionalclass = '';
                    }
                    if ( empty($grade->order) )
                    {// Оценка не подкреплена приказом
                        $grades_gradecell .= dof_html_writer::start_div('dof-grade-cell-grades-grade fake' . $additionalclass);
                        $grades_gradecell .= dof_html_writer::div($grade->item->grade);
                        $grades_gradecell .= dof_html_writer::end_div();

                        $grades_block .= dof_html_writer::start_div('dof-grade-cell-grades-grade fake');
                        if( ! empty($grade->item->grade) )
                        {
                            $grades_block .= dof_html_writer::div($grade->item->grade);
                            $grades_block .= dof_html_writer::div($this->dof->get_string('grade_info_string', 'journal'));
                        }
                        $grades_block .= dof_html_writer::end_div();
                    } else
                    {// Оценка подкреплена приказом
                        $grades_gradecell .= dof_html_writer::start_div('dof-grade-cell-grades-grade' . $additionalclass);
                        $grades_gradecell .= dof_html_writer::div($grade->item->grade);
                        $grades_gradecell .= dof_html_writer::end_div();

                        $grades_block .= dof_html_writer::start_div('dof-grade-cell-grades-grade fake');
                        if( ! empty($grade->item->grade) )
                        {
                            $grades_block .= dof_html_writer::div($grade->item->grade);
                            $grades_block .= dof_html_writer::div($this->dof->get_string('grade_info_string', 'journal'));
                        }
                        $grades_block .= dof_html_writer::end_div();
                    }
                }
            }

            if ( $this->dof->modlib('journal')->get_manager('lessonprocess')->is_debtor($lesson, $cpassed) )
            {
                // флаг, что слушатель является должником
                $cellclasses .= ' dof-grade-is-debtor';
            }

            // Добавление кнопок контроля доступа к занятию
            $authcontroldropmenuhtml = '';
            $authcontroldropmenuhtmlcontent = '';
            if ( $this->control_active && $lesson->mdl_gradeitem_exists() )
            {
                // получение грейдитема
                $amagradeitem = $lesson->get_mdl_gradeitem();
                $gradeitem = $amagradeitem->get();

                $progressbar = dof_html_writer::div('', 'dof-journal-progressbar dof-journal-progressbar-fullheight');

                $authcontroldropmenuhtml .= dof_html_writer::div('', 'dof-lesson-info-divider');
                if ( $gradeitem->is_external_item() )
                {
                    $authcontroldropmenuhtml .= dof_html_writer::div(
                            dof_html_writer::link(
                                    $amagradeitem->get_link_to_element_view(),
                                    $this->dof->get_string('gradeitem_info', 'journal', $gradeitem->itemname),
                                    ['target' => '_blank']
                                    ),
                            'dof-lesson-info-gradeitemname'
                            );

                    // включен контролируемый режим доступа в СДО
                    // добавление кнопок управления доступом
                    $linkparams = [
                        'class' => 'dof-mdlgradeitem-access-tablegrades-ajax',
                        'data-planid' => $lesson->get_plan()->id,
                        'data-cstreamid' => $this->cstream->id,
                        'data-changeto' => 1,
                        'data-cpassedid' => $cpassed->id
                    ];

                    // открыть доступ к элементу
                    $link = dof_html_writer::link(
                            '/',
                            dof_html_writer::div($this->dof->get_string('local_authcontrol_open_access', 'journal')),
                            $linkparams);
                    $authcontroldropmenuhtmlcontent .= dof_html_writer::div($link, 'dof-mdlgradeitem-info-menu-item');

                    $linkparams['data-changeto'] = 0;

                    // закрыть доступ к элементу
                    $link = dof_html_writer::link(
                            '/',
                            dof_html_writer::div($this->dof->get_string('local_authcontrol_close_access', 'journal')),
                            $linkparams);
                    $authcontroldropmenuhtmlcontent .= dof_html_writer::div($link, 'dof-mdlgradeitem-info-menu-item dof-lesson-info-gradeitemname-last');
                } else
                {
                    $authcontroldropmenuhtml .= dof_html_writer::div(
                            dof_html_writer::link(
                                    $amagradeitem->get_link_to_gradebook(),
                                    $this->dof->get_string('gradeitem_info', 'journal', $gradeitem->itemname),
                                    ['target' => '_blank']
                                    ),
                            'dof-lesson-info-gradeitemname'
                            );
                }
            }
            $authcontroldropmenuhtml .= dof_html_writer::div($authcontroldropmenuhtmlcontent, 'dropmenumdlgradeitem');

            // Контент выпадающего блока
            $gradeblock = '';
            $gradeblock .= dof_html_writer::start_div('dof-grade-info-block-wrapper');
            $gradeblock .= dof_html_writer::start_div('dof-grade-info-header');
            $gradeblock .= dof_html_writer::div($date, 'dof-grade-info-eventdate-wrapper');
            if ( ! empty($editlink) )
            {
                $gradeblock .= dof_html_writer::div($editlink, 'dof-grade-info-edit');
            }
            $gradeblock .= dof_html_writer::div($closeimg, 'dof-grade-info-close');
            $gradeblock .= dof_html_writer::end_div();
            $gradeblock .= dof_html_writer::start_div('dof-grade-info-content');
            $gradeblock .= dof_html_writer::div($fullname, 'dof-grade-info-fullname-wrapper');
            $gradeblock .= dof_html_writer::div($presence_block, 'dof-grade-info-presence-wrapper');
            $gradeblock .= dof_html_writer::div($grades_block, 'dof-grade-info-grades-wrapper');
            $gradeblock .= dof_html_writer::div($comments_block, 'dof-grade-info-comments-wrapper');
            $gradeblock .= dof_html_writer::div($authcontroldropmenuhtml, 'dof-grade-info-mdlgradeitem-wrapper');
            $gradeblock .= dof_html_writer::end_div();
            $gradeblock .= dof_html_writer::end_div();

            // Ячейка таблицы
            $gradecell = '';
            $gradecell .= dof_html_writer::start_div("dof-grade-cell $cellclasses");
            $label = dof_html_writer::div('', 'dof-grade-info-label');
            $datedropblock = $this->dof->modlib('widgets')->dropblock($label, $gradeblock, '', ['fixed' => true, 'uniqueid' => 'cp_gradecell_' . ++self::$lesson_count]);
            $gradecell .= dof_html_writer::div($datedropblock, 'dof-grade-cell-info');
            $gradecell .= dof_html_writer::div($grades_gradecell, 'dof-grade-cell-grades');
            $gradecell .= dof_html_writer::div($presence_gradecell, 'dof-grade-cell-presence-group');
            $gradecell .= dof_html_writer::div($homework, 'dof-grade-cell-homework');
            $gradecell .= dof_html_writer::div($comments_gradecell, 'dof-grade-cell-comments');
            if ( ! empty($this->opened_lessons_for_students[$cpassed->studentid]) &&
                    $lesson->plan_exists() &&
                    $lesson->has_gradeitem() &&
                    in_array($lesson->get_plan()->id, $this->opened_lessons_for_students[$cpassed->studentid]) )
            {
                $gradecell .= dof_html_writer::div('', 'dof-grade-cell-access-open');
            }
            $gradecell .= dof_html_writer::end_div();

            $cellcontent .= $gradecell;
        } else
        {// Данные не получены
            $classes .= ' disabled';
        }

        // Обертка для ячейки с информацией по работе на занятии
        return dof_html_writer::div($cellcontent, 'dof-grade-info-wrapper'.$classes);
    }

    /**
     * Получить редактируемый блок информации о работе подписки на занятии
     *
     * @param stdClass $cpassed - Подписка на учебный процесс
     * @param dof_lesson $lesson - Занятие
     *
     * @return string - HTML-код
     */
    protected function cpassed_gradecell_edit(stdClass $cpassed, dof_lesson $lesson)
    {
        $html = '';

        // Получение данных о работе на занятии
        $gradedata = $lesson->get_listener_gradedata($cpassed->id);

        // Получение шкалы оценок
        $scale = $this->dof->modlib('journal')
            ->get_manager('scale')
            ->get_plan_scale($lesson->get_plan());

        // Проверка, что студент обучался в эту дату
        $presence = true;
        if ( $lesson->event_exists() )
        {
            $presence = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_present_status($cpassed->studentid, $lesson->get_event()->id);
        }

        if ( ! empty($scale) &&
                ! empty($gradedata) &&
                ( $gradedata->overenroltime === false ) &&
                $presence !== false )
        {
            $selected = '';
            if ( ! empty($gradedata->grades) )
            {
                $grade = array_shift($gradedata->grades);
                if ( ! empty($grade) )
                {
                    $selected = $grade->item->grade;
                }
            }

            $attrs = !$lesson->can_set_grade($cpassed) ? ['disabled' => 'disabled'] : [];
            $html .= dof_html_writer::select($scale, 'user_grade', $selected, ['' => '-'], $attrs);

            $html = dof_html_writer::div(
                $html,
                'cpassed-gradecell-save',
                [
                    'data-cpassed' => $cpassed->id,
                    'data-student' => $cpassed->studentid
                ]
            );
        } else
        {// Пустая шкала
            $html .= $this->cpassed_gradecell($cpassed, $lesson);
        }

        return $html;
    }

    /**
     * Получить редактируемый блок информации о работе подписки на занятии
     *
     * @param stdClass $cpassed - Подписка на учебный процесс
     * @param dof_lesson $lesson - Занятие
     *
     * @return string - HTML-код
     */
    protected function cpassed_presencecell_edit(stdClass $cpassed, dof_lesson $lesson)
    {
        $html = '';


        // Получение данных о работе на занятии
        $gradedata = $lesson->get_listener_gradedata($cpassed->id);

        // Проверка, что студент обучался в эту дату
        $presence = true;
        if ( $lesson->event_exists() )
        {
            $presence = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_present_status($cpassed->studentid, $lesson->get_event()->id);
        }

        if ( ! empty($gradedata) &&
                ( $gradedata->overenroltime === false ) &&
                $presence !== false )
        {
            $id = "presence-".$cpassed->id."-".$cpassed->studentid;
            $present = true;
            if( isset($gradedata->presence->item->present) )
            {
                $present = ! empty($gradedata->presence->item->present);
            }
            $checbox = dof_html_writer::checkbox(
                $id,
                $cpassed->studentid,
                $present,
                '',
                ['id'=>$id]
            );
            $label = dof_html_writer::label('', $id);
            $html = dof_html_writer::div(
                $checbox.$label,
                'cpassed-presencecell-save'
            );
        } else
        {
            $html .= $this->cpassed_gradecell($cpassed, $lesson);
        }

        return $html;
    }

    /**
     * Получить блок информации о подписке на учебный процесс
     *
     * @param stdClass $cpassed - Подписка на учебный процесс
     *
     * @return string - HTML-код
     */
    protected function cpassed_infoblock(stdClass $cpassed)
    {
        global $CFG;

        $content = '';

        // Общие опции для ссылок
        $general_link_options = ['target' => '_blank'];

        // Получение ФИО слушателя
        $fullnameclass = 'dof-cpassed-info-fullname';
        if ( $cpassed->status == 'failed' || $cpassed->status == 'canceled' )
        {// Неуспешно завершенная подписка
            $fullnameclass .= ' failed';
        } elseif ( $cpassed->status == 'completed' )
        {// Успешно завершенная подписка
            $fullnameclass .= ' completed';
        }
        $nameinfo = $this->dof->storage('persons')->get_name_info($cpassed->studentid);
        $attributes = [
            'data-firstname' => (string)$nameinfo['firstname'],
            'data-lastname' => (string)$nameinfo['lastname'],
            'data-middlename' => (string)$nameinfo['middlename']
        ];
        $fullname = dof_html_writer::span(
            $nameinfo['fullname'],
            $fullnameclass,
            $attributes
            );
        $personurl = $this->dof->url_im(
            'persons',
            '/view.php',
            array_merge($this->addvars, ['id' => $cpassed->studentid])
        );

        // Меню действий с подпиской
        $personmenu = [];

        // просмотр персоны
        $personmenu[] = dof_html_writer::link($personurl, dof_html_writer::div($this->dof->get_string('view_student_profile', 'journal')), $general_link_options);

        // Занятия учащегося
        $url = $this->dof->url_im(
            'journal',
            '/show_events/show_events.php',
            array_merge($this->addvars, ['personid' => $cpassed->studentid, 'date_to' => time(), 'date_from' => time()])
        );
        $item = dof_html_writer::div(
            $this->dof->get_string('view_events_student', 'journal')
        );
        $personmenu[] = dof_html_writer::link($url, $item, $general_link_options);

        // Список шаблонов учащегося
        if ( $this->dof->storage('schtemplates')->is_access('view') )
        {// Просмотр шаблонов доступен

            $url = $this->dof->url_im(
                'schedule',
                '/view_week.php',
                array_merge($this->addvars, ['studentid' => $cpassed->studentid, 'ageid' => $this->cstream->ageid])
            );
            $item = dof_html_writer::div(
                $this->dof->get_string('view_week_template_on_student', 'journal')
            );
            $personmenu[] = dof_html_writer::link($url, $item, $general_link_options);
        }

        // Ссылка на профиль пользователя в курсе Moodle
        $mdlcourse = $this->dof->storage('programmitems')->
            get_field($cpassed->programmitemid, 'mdlcourse');
        if ( $this->dof->modlib('ama')->course(false)->is_course((int)$mdlcourse) )
        {// Курс найден
            $mdluser = $this->dof->storage('persons')->get_field($cpassed->studentid, 'mdluser');
            $url = $CFG->wwwroot . "/course/user.php?id=" . $mdlcourse . "&user=" . $mdluser . "&mode=outline";
            $item = dof_html_writer::div(
                $this->dof->get_string('view_moodle_user_profile', 'journal')
            );
            $personmenu[] = dof_html_writer::link($url, $item, $general_link_options);
        }

        // История обучения по дисциплине
        $url = $this->dof->url_im(
            'journal',
            '/cphistory.php',
            array_merge($this->addvars, [
                'programmsbcid' => $cpassed->programmsbcid,
                'programmitemid' => $cpassed->programmitemid
            ])
        );
        $item = dof_html_writer::div(
            $this->dof->get_string('cphistory', 'journal')
        );
        $personmenu[] = dof_html_writer::link($url, $item, $general_link_options);


        if ( $personmenu )
        {// Элементы меню указаны
            // Добавить выпадающий список действий
            $label = dof_html_writer::div($fullname, 'dof-cpassed-info-menu-label');
            foreach ( $personmenu as &$item )
            {
                $item = dof_html_writer::div($item, 'dof-cpassed-info-menu-item');
            }
            $content .= $this->dof->modlib('widgets')->dropblock(
                $label,
                implode('', $personmenu),
                '',
                ['fixed' => true, 'uniqueid' => 'cp_'.$cpassed->id]
            );
        } else
        {
            // Добавить выпадающий список действий
            $label = dof_html_writer::div($fullname, 'dof-cpassed-info-fullname');
            $content .= $label;
        }
        $html = '';
        $html .= dof_html_writer::start_div('dof-cpassed-info-wrapper');
        $html .= dof_html_writer::div($content);
        $html .= dof_html_writer::end_div();

        return $html;
    }
}



/**
 * Журнал предмето-класса. Таблица разворота журнала.
 *
 * @package    im
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_im_journal_tablegrades extends dof_im_journal_tablebase
{
    /**
     * Массив GET параметров для формирования ссылок
     *
     * @var array
     */
    public $addvars = [];

    /**
     * Конструктор - определяет с каким учебным потоком будет вестись работа
     *
     * @param dof_control - глобальный объект Деканата $DOF
     * @param int $csid - ID учебного процесса(предмето-класса)
     * @param array $addvars - Массив GET параметров
     */

    function __construct(dof_control $dof, $csid, $addvars = [])
    {
        $this->dof  = $dof;
        $this->csid = (int)$csid;
        $this->addvars = (array)$addvars;
    }

    /**
     * Отобразить таблицу оценок журнала учебного процесса для всех учащихся
     *
     * @param int $planid - ID контрольной точки в учебном плане данного предмета-класса
     * @param int $eventid - ID учебного события данного предмета-класса
     * @param bool $returnhtml - Вернуть HTML вместо печати таблицы
     *
     * @return string|void - Печать таблицы оценок или возврат HTML-кода
     */
    public function print_texttable($planid = NULL, $eventid = 0, $returnhtml = FALSE)
    {
        // Получение данных для шаблонизатора
        $docdata = $this->get_all_form($planid, $eventid);

        // Загрузка данных в шаблонизатор
        $templater_package = $this->dof->modlib('templater')->template('im', 'journal', $docdata, 'group_journal');

        // Добавить JS поддержки группового журнала
        $this->dof->modlib('nvg')->add_js('im', 'journal', '/group_journal/groupjournal.js', false);

        // Формирование HTML-шаблона
        if( ! empty($templater_package) )
        {
            $html = $templater_package->get_file('html');
        } else
        {
            $html = '';
        }

        if ( $returnhtml )
        {// Вернуть HTML
            return $html;
        } else
        {// Печать таблицы
            print($html);
        }
    }

    /**
     * Отобразить таблицу оценок журнала учебного процесса для одной подписки на программу
     *
     *
     * @param int $programmsbcid - ID подписки на программу
     * @param bool $returnhtml - Вернуть HTML вместо печати таблицы
     *
     * @return string|void - Печать таблицы оценок или возврат HTML-кода
     */
    public function get_grades_programmsbcid($programmsbcid, $returnhtml = FALSE)
    {
        global $addvars;

        $html = '';

        // Получение всех подписок на текущий учебный процесс по подписке на программу
        $cpasseds = $this->get_cpassed_programmsbc($programmsbcid, true);
        if ( ! empty($cpasseds) )
        {// Найдены подписки на учебные процессы

            // Получить контрольные точки тематического плана текущего учебного процесса
            $plans = $this->get_checkpoints(false);
            if ( $plans )
            {// В результирующем массиве формируем строку месяцев и дат
                $datesstring = $this->create_datesstring($plans);
                if ( !empty($datesstring) )
                {
                    // Сделаем из timestamp дни, чтобы отобразить их под месяцами
                    foreach ( $datesstring->monthdate as $id => $mdate )
                    {
                        $datesstring->monthdate[$id]->date = dof_userdate($mdate->date, '%d');
                    }
                }
            }

            $i = 1;
            $grades = [];
            // Формирование данных по каждой подписке на учебный процесс
            foreach ( $cpasseds as $cpassedid => $cpassed )
            {
                $grades[$cpassedid]       = $this->get_line_for_student($i++, $cpassed, $plans, null, 0, 'grades');
                $begindate                = dof_userdate($cpassed->begindate, '%d.%m.%Y');
                $enddate                  = dof_userdate($cpassed->enddate, '%d.%m.%Y');
                $grades[$cpassedid]->cpdate = $begindate . ' - ' . $enddate;
                $cpstatus = $this->dof->workflow('cpassed')->get_name($cpassed->status);
                $cpassedlink = $this->dof->im('obj')->get_object_url_current('cpassed', $cpassedid, 'view', $addvars, $cpstatus);
                $grades[$cpassedid]->cpassedlink = $cpassedlink;
                if ( ! empty($datesstring) )
                {
                    $grades[$cpassedid]->monthdesc  = $this->dof->get_string('month', 'journal');
                    $grades[$cpassedid]->daydesc    = $this->dof->get_string('date_day', 'journal');
                    $grades[$cpassedid]->monthdate  = $datesstring->monthdate;
                    $grades[$cpassedid]->monthtitle = $datesstring->monthtitle;
                }
            }
            $grobject = new stdClass();
            $grobject->cpasseds = $grades;

            // Загрузка данных в шаблонизатор
            $templater_package = $this->dof->modlib('templater')->template('im', 'journal', $grobject, 'cphistory');

            // Формирование HTML-шаблона
            $html = $templater_package->get_file('html');
        }

        if ( $returnhtml )
        {// Вернуть HTML
            return $html;
        } else
        {// Печать таблицы
            print($html);
        }
    }

    /**
     * Получить список статусов с которыми будут извлекаться события из таблицы schevents
     *
     * @return array|NULL - Массив статусов или NULL, если не требуется фильтрация по статусам
     */
    protected function get_eventstatuses()
    {
        return array('plan', 'completed', 'postponed');
    }

    /**
     * Возвращает объект формы для вставки в templater
     *
     * @param int $editid - ID контрольной точки в учебном плане данного предмета-класса
     * @param int $eventid - ID учебного события данного предмета-класса
     *
     * @return object - Объект нужной структуры для построения шаблона
     */
    private function get_all_form($editid, $eventid)
    {
        $result              = new stdClass();
        $result->monthdesc   = $this->dof->get_string('month', 'journal');
        $result->datedesc    = $this->dof->get_string('date_day', 'journal');
        $result->npp         = $this->dof->get_string('npp', 'journal');
        $result->listtitle   = $this->dof->get_string('students_list2', 'journal');

        // СБОРКА ИСХОДНЫХ ДАННЫХ
        // Получить все запланированные и активные контрольные точки учебного процесса
        $plans = $this->get_checkpoints(false);

        // Массив для названий месяцев
        $result->monthtitle  = [];
        // Массив для дат
        $result->monthdate   = [];
        // Добавление информации по ученикам
        $result->studentinfo = $this->get_lines_for_students($plans, $editid, $eventid, $info = 'info');
        // Добавление ФИО учеников отдельным полем
        $result->student = $this->get_lines_for_students($plans, $editid, $eventid, $info = 'grades');

        if ( $plans )
        {// В результирующем массиве формируются строки месяцев и дат
            $datesstring = $this->create_datesstring($plans);
            $result->upper_anchor = $datesstring->upper_anchor;
            $result->monthdate    = $datesstring->monthdate;
            $result->monthtitle   = $datesstring->monthtitle;
        }

        if ( $editid )
        {// Требуется создание формы для редактирования оценок контрольной точки
            $anchor = $this->get_anchor($plans, $editid, $eventid);
            if ( $this->dof->im('journal')->is_access('give_grade', $editid) ||
                $this->dof->im('journal')->is_access('give_grade/in_own_journal', $editid) )
            {// Есть права на выставление оценок по контрольной точке
                $result->formbegin = $this->get_begin_form($editid, $eventid, $anchor);
                $result->formend   = $this->get_end_form($eventid);
            }
        }
        return $result;
    }


    /**
     * Возвращает редактируемую ячейку контрольной точки для ученика
     *
     * @param int $studentid - ID ученика, которому принадлежит ячейка
     * @param int $cpassedid - ID подписки ученика
     * @param int $gradeid - ID текущей оценки
     * @param string $oldgrade - Текущая оценка
     * @param int $eventid - ID редактируемого события
     * @param string $scale - Шкала оценок
     *
     * @return string - HTML-код ячейки
     */
    private function get_cell_form($studentid, $cpassedid, $oldgrade = NULL, $gradeid = 0, $eventid, $scale = null)
    {
        // Базовые переменные
        $cellhtml = '';

        // Получить подписку на учебный процесс
        $cpassed = $this->dof->storage('cpassed')->get($cpassedid);

        // Время начала подписки
        $begindate = $cpassed->begindate;
        if ( empty($cpassed->begindate) )
        {// Время начала не указано
            // Установка времени начала подписки на время начала учебного процесса
            if ( ! $begindate = $this->dof->storage('cstreams')->get_field($cpassed->cstreamid, 'begindate') )
            {// Время начала учебного процесса не установлено
                // Установка времени начала подписки на время начала учебного периода
                if ( ! $begindate = $this->dof->storage('ages')->get_field($cpassed->ageid, 'begindate') )
                {// Время начала периода не установлено
                    // Текущее время
                    $begindate = time();
                }
            }
        }

        // Нормализация времени до начала дня @todo - Учет часового пояса подразделения
        $time = dof_usergetdate($begindate);
        $begindate = mktime(0, 0, 0, $time['mon'], $time['mday'], $time['year']);

        // Время конца подписки
        $enddate = $cpassed->enddate;
        if ( empty($cpassed->enddate) )
        {// Время начала не указано
            // Установка времени конца подписки на время конца учебного процесса
            if ( ! $enddate = $this->dof->storage('cstreams')->get_field($cpassed->cstreamid, 'enddate') )
            {// Время конца учебного процесса не установлено
                // Установка времени конца подписки на время конца учебного периода
                if ( !$enddatee = $this->dof->storage('ages')->get_field($cpassed->ageid, 'enddate') )
                {// Время конца периода не установлено
                    // Текущее время
                    $enddate = time();
                }
            }
        }

        // Нормализация времени до конца дня @todo - Учет часового пояса подразделения
        $time = dof_usergetdate($enddate);
        $enddate  = mktime(23, 59, 59, $time['mon'], $time['mday'], $time['year']);

        // Возможность редактирования ячейки
        $disabled = FALSE;
        if ( $schevent = $this->dof->storage('schevents')->get($eventid) )
        {// Событие контрольной точки найдено
            if ( ( $schevent->date < $begindate || $schevent->date > $enddate ) AND
                ! $this->dof->im('journal')->is_access('remove_not_studied') )
            {// Запрет редактирования ячейки
                $disabled = TRUE;
                $cellhtml .= '<input type="hidden" name="noaway[' . $cpassedid . ']" value="' . $studentid . '">';
                $cellhtml .= '<input type="hidden" name="editgrades[' . $cpassedid . ']" value="' . $studentid . '">';
                $cellhtml .= '<input type="hidden" name="away[' . $cpassedid . ']" value="' . $studentid . '">';
            }
        }
        // Форирование строки для отключения элементов формы
        $disabledstring = '';
        if ( $disabled )
        {
            $disabledstring = ' disabled ';
        }

        // Формирование таблицы для формы
        $cellhtml .= '<table callpadding="0" celspacing="0" border="0" class="dof_cpassedgradeform">';
        $cellhtml .= '<tr><td rowspan="2">';
        $cellhtml .= '<input type="hidden" name="gradeid[' . $cpassedid . ']" value="' . $gradeid . '">';

        // Получение элементов выпадающего списка оценок
        $variants = $this->get_grade_variants($scale);
        // Создание выпадающего списка оценок
        $cellhtml .= '<select name="editgrades[' . $cpassedid . ']"' . $disabledstring . '>';
        foreach ( $variants as $variant )
        {
            if ( $oldgrade == $variant->value )
            {
                $variant->selected = 'selected';
            } else
            {
                $variant->selected = '';
            }
            $cellhtml .= '<option value="' . $variant->value . '" ' . $variant->selected . '>' . $variant->name . '</option>' . "\n";
        }
        $cellhtml .= '</select>';
        $cellhtml .= '</td>';

        // Посещаемость
        if ( ! empty($schevent) )
        {// Событие найдено
            // Получение посещаемости по событию
            $presence = $this->dof->storage('schpresences')->get_present_status($studentid, $eventid);
        } else
        {// Контрольная точка без посещаемости
            $presence = 'noaway';
        }

        if ( $eventid )
        {// Установка посещаемости только для контрольной точки с событием

            $cellhtml .= '<td align="left">';

            // Проверка на отсутствие на уроке (Н)
            if ( $presence === '0' )
            {// Ученик отсутствовал на уроке
                $check = 'checked';
            } else
            {
                $check = '';
            }
            // Формирование чекбокса с указанием пропуска
            $cellhtml .= '<div class="dof_cpassedgradeform_checkbox">
                <input type="checkbox" name="away[' . $cpassedid . ']" id="checkbox_away[' . $cpassedid . ']" value="' . $studentid . '" ' . $check . ' ' . $disabledstring . '>';
            $cellhtml .= '<label for="checkbox_away[' . $cpassedid . ']">'.$this->dof->get_string('away_n', 'journal')."</label>";
            $cellhtml .= '</div>';

            // Проверка на отсутствие посещаемости (Н/О)
            if ( $presence === false || $presence == 'noaway' )
            {// Посещаемость не найдено, либо явно указано, что ученик не обучался
                $cellhtml .= '<div>';
                if ( $schevent->date < $begindate || $schevent->date > $enddate || $cpassed->status != 'active' )
                {// Событие произошло вне интервала подписки, или подписка не активна
                    $cellhtml .= '<input type="checkbox" name="noaway[' . $cpassedid . ']" value="' . $studentid . '" ' . $disabledstring . ' checked>';
                } elseif ( $schevent->status == 'completed' )
                {// Событие завершено
                    $cellhtml .= '<input type="checkbox" name="noaway[' . $cpassedid . ']" value="' . $studentid . '" checked>';
                } else
                {
                    $cellhtml .= '<input type="checkbox" name="noaway[' . $cpassedid . ']" value="' . $studentid . '">';
                }
                $cellhtml .= $this->dof->get_string('away_no', 'journal');
                $cellhtml .= '</div>';
            } elseif ( $schevent->date < $begindate OR $schevent->date > $enddate )
            {// Есть запись о посещаемости
                if ( $this->dof->im('journal')->is_access('remove_not_studied') )
                {// Дать возможность изменять посещаемость
                    $cellhtml .= '<div>';
                    $cellhtml .= '<input type="checkbox" name="noaway[' . $cpassedid . ']" value="' . $studentid . '">';
                    $cellhtml .= $this->dof->get_string('away_no', 'journal');
                    $cellhtml .= '</div>';
                }
            }
            $cellhtml .= '</td>';

            // Блок с редактированием замечания
            if ( $eventid && ! $disabled )
            {// Для контрольной точки имеется событие
                // Поверка наличия посещаемости
                $params = [];
                $params['personid'] = $studentid;
                $params['eventid'] = $eventid;
                $presence = $this->dof->storage('schpresences')->get_records($params);
                if ( ! empty($presence) && $this->dof->plugin_exists('storage', 'comments') )
                {// Комментарий к посещаемости ученика на событии
                    $presence = end($presence);
                    // Получение списка комментариев
                    $comments = $this->dof->storage('comments')->get_comments_by_object('storage', 'schpresences', $presence->id, 'public');
                    $label_class = 'btn button dof_button grpjournal_comment_modal_label';
                    if ( empty($comments) )
                    {// Комментариев по посещаемости нет
                        // Создание пустого поля
                        $content = '<div>';
                        $content .= '<textarea name="comment[0_'.$presence->id.'_'.$cpassedid.']"></textarea>';
                        $content .= '</div>';
                    } else
                    {// Комментарии есть
                        $label_class .= ' grpjournal_has_comments';
                        $content = '<div>';
                        foreach ( $comments as $comment )
                        {// Добавление полей комментариев
                            $content .= '<textarea name="comment['.$comment->id.'_'.$presence->id.'_'.$cpassedid.']">'.$comment->text.'</textarea>';
                        }
                        $content .= '</div>';
                    }
                    $title = $this->dof->get_string('groupjournal_comment_modal_title', 'journal');
                    $label = '<span class="'.$label_class.'" title="'.$title.'">'.
                        $this->dof->modlib('ig')->icon('feedback', NULL, ['title' => $title]).
                        '</div>';
                        if ( ! empty($content) )
                        {
                            $cellhtml .= '<td>'.$this->dof->modlib('widgets')->modal($label, $content, $title).'</td>';
                        }
                }
            }
            $cellhtml .= '</tr>';
        }
        $cellhtml .= '<input type="hidden" name="cpassedid[' . $cpassedid . ']" value="' . $studentid . '">';
        $cellhtml .= '</table>';

        return $cellhtml;
    }

    /**
     * Получить содержимое ячейки
     *
     * @param int $studentid - ID персоны
     * @param object $plan - Контрольная точка в учебном плане данного учебного процесса
     * @param int $cpassedid - ID подписки на учебный процесс
     * @param object $gradedata - Данные об оценке
     *
     * @return string html-код оценки и отметки об отсутствии
     */
    private function get_cell_string($studentid, $plan, $cpassedid, $gradedata = NULL)
    {
        $html = '';

        $prdate = '';
        $presence = NULL;
        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();

        // Получение события контрольной точки
        if ( isset($plan->event) )
        {// Событие найдено
            // Получение посещаемости персоны
            $params             = [];
            $params['personid'] = $studentid;
            $params['eventid']  = $plan->event->id;
            $presences = $this->dof->storage('schpresences')->get_records($params);
            if ( ! empty($presences) && is_array($presences) )
            {// Посещаемость найдена
                foreach ( $presences as $item )
                {
                    if ( ! empty($item->orderid) )
                    {// Посещаемость подкреплена приказом
                        $presence = $item;
                        $date = $this->dof->storage('orders')->get_field($presence->orderid, 'exdate');
                        $prdate = dof_userdate($date, '%d.%m.%Y %H:%M', $usertimezone, false);
                        break;
                    }
                }

            }
        }

        // Отображение отметки пользователя по контрольной точке
        if ( ! empty($gradedata) )
        {// Имеются данные по оценке
            // Отображение оценки
            $date = $this->dof->storage('orders')->get_field($gradedata->orderid, 'exdate');
            $date = dof_userdate($date, '%d.%m.%Y %H:%M', $usertimezone, false);
            $html = '<span class="has-tooltip" title="' . $date . '">' . $gradedata->grade . '</span>';
        }

        // Добавление данных о посещаемости
        if ( isset($plan->event) AND isset($presence->present) AND $presence->present === '0' )
        {// Ученик отсутствовал на занятии - "Н"
            // Тип (Уважительная/Неуважительная - если указана причина)
            $presence_type = $this->dof->get_string('reason_unknown', 'journal');
            // Название причины (если указана)
            $presence_name = $this->dof->get_string('reason_unknown', 'journal');
            if ( ! empty($presence->reasonid) )
            {// Указан идентификатор
                // Получим название причины отсутствия
                $reason = $this->dof->storage('schabsenteeism')->get_record(['id' => $presence->reasonid]);
                $presence_type = (empty($reason->unexplained) ?  $this->dof->get_string('unexplained_no', 'journal') : $this->dof->get_string('unexplained_yes', 'journal'));
                $presence_name = $reason->name;
            }
            $html .= '<div class="show_modal_caller">
                        <span
                            data-type="' . $this->dof->get_string('type_no', 'journal', $presence_type). '"
                            data-name="' . $this->dof->get_string('persence_no', 'journal', $presence_name) .'"
                            class="has-tooltip show_modal"
                            title="' . $prdate . '">
                            (' . $this->dof->get_string('away_n', 'journal') . ')</span></div>';
        } else
        {// Посещаемость не найдена
            $html .= '<span title="' . $prdate . '">&nbsp;</span>';
        }

        if ( isset($presence->id) )
        {// Отобразить список комментариев по посещаемости
            $options = [];
            $options['display'] = 'icon';
            $html .= $this->presence_comment_block($presence->id, $options);
        }

        // Вернуть данные по ячейке
        return $html;
    }

    /** Возвращает данные в одной клетке
     * @param int $studentid - id студента
     * @param object $plan - контрольная точка с событием  из тем. планирования
     * @param object $gradedata - данные об оценке, либо null
     * @param int $cpassedid - id  подписки
     * @param int $editid - id редактируемого плана
     * @param int $eventid - id редактируемого события
     * @param string $scale - шкала оценок
     * @return string
     */
    private function get_one_cell($studentid, $plan, $gradedata, $cpassedid, $editid, $eventid, $scale = null)
    {
        $grades = '';
        // если id КТ из ем. планирования и редактируемой КТ совпадают
        // ячейка редактируется

        if ( $plan->plan->id == $editid AND ( $this->dof->im('journal')->is_access('give_grade', $editid)
            OR $this->dof->im('journal')->is_access('give_grade/in_own_journal', $editid)) )
        {
            if ( $gradedata )
            {// есть оценка
                $grades = $this->get_cell_form($studentid, $cpassedid, $gradedata->grade, $gradedata->id, $eventid, $scale);
            } else
            {// нет оценки
                $grades = $this->get_cell_form($studentid, $cpassedid, 0, 0, $eventid, $scale);
            }
        } else
        {// это обычная ячейка. Просто покажем оценку
            $grades = $this->get_cell_string($studentid, $plan, $cpassedid, $gradedata);
        }
        // возвращаем код формы
        return $grades;
    }

    /**
     * Возвращает стилизацию ячейки для контрольной точки и пользователя
     *
     * @param int $studentid - ID персоны
     * @param object $plan - Контрольная точка в учебном плане данного учебного процесса
     * @param int $cpassedid - ID подписки на учебный процесс
     * @param int $editid - ID редактируемой контрольной точки
     * @param int $eventid - ID редактируемого события
     *
     * @return string - style-параметр для HTML-тега
     */
    private function get_color_cell($studentid, $plan, $cpassedid, $editid, $eventid)
    {
        $style = '';

        if ( $plan->plan->id != $editid AND isset($plan->event) AND
            (
                ( $this->dof->storage('schpresences')->get_present_status($studentid, $plan->event->id) === false ) OR
                (
                    isset($plan->date) && $plan->date > 0 &&
                    (
                        $this->dof->storage('cpassed')->get_field($cpassedid, 'begindate') > $plan->date ||
                        $this->dof->storage('cpassed')->get_field($cpassedid, 'enddate') < $plan->date )
                    )
                )
            )
        {
            $style = 'style="background: #aaa;"';
        }
        return $style;
    }

    /**
     * Сформировать начало формы редактирования оценок
     *
     * @param int $planid - ID контрольной точки в учебном плане данного предмета-класса
     * @param int $eventid - ID учебного события данного предмета-класса
     *
     * @return string HTML-код формы
     */
    private function get_begin_form($editid, $eventid, $anchor)
    {
        global $USER;

        //запомним идентификатор сессии
        $sesskey = '';
        if ( isset($USER->sesskey) AND $USER->sesskey )
        {//запомним идентификатор сессии
            $sesskey = $USER->sesskey;
        }
        $addvars                 = [];
        $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        $addvars['csid'] = $this->csid;

        // Объявление формы
        $actonurl = $this->dof->url_im('journal', '/group_journal/process_grades.php', $addvars);
        $result = '<form name="gradeeditform" method="post" action="'.$actonurl.'">';

        // Идентификатор контрольной точки
        $result .= '<input type="hidden" name="planid" value="' . $editid . '"/>';
        // Идентификатор учителя
        $result .= '<input type="hidden" name="teacherid" value="' .
            $this->dof->storage('cstreams')->get_field($this->csid, 'teacherid') . '"/>';
            // Идентификатор учителя
            $result .= '<input type="hidden" name="sesskey" value="' . $sesskey . '">';
            // Идентификатор собыия
            $result .= '<input type="hidden" name="eventid" value="' . $eventid . '">';
            // Идентификатор предмето-класса
            $result .= '<input type="hidden" name="csid" value="' . $this->csid . '">';
            // Идентификатор подразделения
            $result .= '<input type="hidden" name="departmentid" value="' . $addvars['departmentid'] . '">';
            // Якорь
            $result .= '<input type="hidden" name="anchor" value="' . $anchor . '">';

            return $result;
    }

    /**
     * Сформировать конец формы редактирования оценок
     *
     * @param int $eventid - ID учебного события данного предмета-класса
     *
     * @return string HTML-код формы
     */
    private function get_end_form($eventid)
    {
        $result = '';
        $result .= '<br/><b>'.$this->dof->get_string('jornal_edit_warning', 'journal').'</b><br/>';

        // ДОБАВЛЕНИЕ ЧЕКБОКСА ДЛЯ ОТМЕТКИ УРОКА
        if ( $this->dof->im('journal')->is_access('can_complete_lesson', $eventid) OR
            $this->dof->im('journal')->is_access('can_complete_lesson/own', $eventid) )
        {// Есть права на завершение занятия
            $result .= '<b>'.$this->dof->get_string('jornal_edit_warningtwo', 'journal') . '</b><br/>';

            if ( $this->dof->storage('config')->get_config_value('time_limit', 'storage', 'schevents', optional_param('departmentid', 0, PARAM_INT)) )
            {// Установлена настройка
                $result .= '<br/><b>'.$this->dof->get_string('jornal_edit_warning_limit_time', 'journal').'</b><br/>';
            }
            // Время начала занятия
            $evdate = $this->dof->storage('schevents')->get_field($eventid, 'date');

            if ( $this->dof->workflow('schevents')->limit_time($evdate) )
            {// Временные лимиты позволяют установить отметку
                // Чекбокс с подтверждением смены статуса урока
                $result .= '<br/><span><b>'.$this->dof->get_string('lesson_complete_title', 'journal').'</b></span>';
                $result .= '<input type="checkbox" name="box"></p>';
            }
        }

        // Действия над оценками
        $result .= '<br/><input type="submit" name="save_and_continue" value="' .
            $this->dof->get_string('save_and_continue', 'journal') . '"/>';
            $result .= '<input type="submit" name="save" value="' .
                $this->dof->get_string('to_save', 'journal') . '"/>';
                $result .= '<input type="submit" name="restore" value="' .
                    $this->dof->get_string('restore', 'journal') . '"/>';
                    $result .= '</form>';

                    return $result;
    }

    /**
     * Возвращает данные для одного студента
     *
     * @param int $i - порядковый номер
     * @param object $student - студент
     * @param array $cpasseds - его подписки
     * @param array $plans - контрольные точки
     * @param int $editid - id редактируемого плана
     * @param int $eventid - id редактируемого события
     * @param string  $info - показывает иформацию, что нужно вывести, если пусто,
     * 							то выводить всю информацию
     * @return object информация о студенте
     */
    private function get_line_for_student($i, $cpassed, $plans, $editid, $eventid, $info = '')
    {
        global $CFG;

        $depid                     = optional_param('departmentid', 0, PARAM_INT);
        $cstreamid                 = optional_param('csid', 0, PARAM_INT);
        $addvars                   = array();
        $addvars['departmentid']   = $depid;
        $curstudent                = new stdClass();
        // устанавливаем порядковый номер
        $curstudent->studentnumber = $i;
        $links                     = '';
        $name                      = $this->dof->storage('persons')->get_fullname($cpassed->studentid);
        // перечеркнем имя
        if ( $cpassed->status == 'failed' OR $cpassed->status == 'canceled' )
        {
            $name = "<span style='text-decoration:line-through;color:gray;'> {$name} </span>";
        }
        // серый цвет
        if ( $cpassed->status == 'completed' )
        {
            $name = "<span style='color:gray;'> {$name} </span>";
        }
        if ( $this->dof->storage('schtemplates')->is_access('view') )
        {// можно просматривать шаблон - добавим ссылку на просмотр шаблона на неделю
            $ageid = $this->dof->storage('cstreams')->get_field($cstreamid, 'ageid');

            $options = array(
                'alt' => $this->dof->get_string('view_week_template_on_student', 'journal'),
                'title' => $this->dof->get_string('view_week_template_on_student', 'journal'),
            );
            $url = $this->dof->url_im('schedule', '/view_week.php?studentid=' . $cpassed->studentid . '&ageid=' . $ageid, $addvars);
            $img = $this->dof->modlib('ig')->icon_plugin('show_schedule_week','im','journal', $url, $options);
            $links = $img;
        }
        $mdlcourse = $this->dof->storage('programmitems')->get_field($cpassed->programmitemid, 'mdlcourse');
        if ( isset($mdlcourse) AND $this->dof->modlib('ama')->course(false)->is_course($mdlcourse) )
        {
            $mdluser = $this->dof->storage('persons')->get_field($cpassed->studentid, 'mdluser');
            $links .= $this->dof->modlib('ig')->icon('moodle', $CFG->wwwroot . "/course/user.php?id=" . $mdlcourse . "&user=" . $mdluser .
                "&mode=outline");
        }
        // Показать занятия учащегося
        $options = array(
            'alt' => $this->dof->get_string('view_events_student', 'journal'),
            'title' => $this->dof->get_string('view_events_student', 'journal'),
        );
        $url = $this->dof->url_im('journal', '/show_events/show_events.php?personid=' . $cpassed->studentid, $addvars) . '&date_to=' . time() . '&date_from=' . time();
        $img = $this->dof->modlib('ig')->icon_plugin('events_student','im','journal', $url, $options);
        // История обучения по дисциплине
        $options = array(
            'programmsbcid' => $cpassed->programmsbcid,
            'programmitemid' => $cpassed->programmitemid,
        );
        $url = $this->dof->url_im('journal', '/cphistory.php', $addvars + $options);
        $options = array(
            'alt' => $this->dof->get_string('cphistory', 'journal'),
            'title' => $this->dof->get_string('cphistory', 'journal'),
        );
        $cphistorylink = $this->dof->modlib('ig')->icon_plugin('history', 'im', 'journal', $url, $options);
        $curstudent->fio = '<a href="' . $this->dof->url_im('journal', '/person.php?personid=' . $cpassed->studentid, $addvars) . '">' .
            $name .
            '</a>' . $img . $links . $cphistorylink;
            ;

            $curstudent->cpassedid = $cpassed->id;
            // вывод информации
            if ( $info == 'info' )
            {
                return $curstudent;
            }
            // объявляем массив для будущих оценок студента
            $curstudent->studentgrades = array();
            // собираем ключи массива - id учебных событий
            if ( is_array($plans) )
            {
                foreach ( $plans as $plan )
                {// для всех дат проставляем оценки
                    // создаем объект оценки для обработки шаблоном
                    $grade = new stdClass();
                    // получаем оценку за указанную дату
                    // нулевая шкала - возьмем из предмета
                    if ( IS_NULL($plan->plan->scale) )
                    {
                        $csid    = $plan->plan->linkid;
                        $pitemid = $this->dof->storage('cstreams')->get_field($csid, 'programmitemid');
                        $scale   = $this->dof->storage('programmitems')->get_field($pitemid, 'scale');
                        // и в программе не указана - возбмем из настроек по умолчанию
                        if ( empty($scale) )
                        {
                            $scale = $this->dof->storage('config')->get_config_value('scale', 'storage', 'plans', $depid);
                        }
                    } else
                    {
                        $scale = $plan->plan->scale;
                    }

                    // @todo в будущем передалать для вывода нескольких оценок за одну дату
                    $gradedata                   = $this->dof->storage('cpgrades')->
                    get_grade_student_cpassed($cpassed->id, $plan->plan->id);
                    // получем оценку студента
                    $grade->grades               = $this->get_one_cell($cpassed->studentid, $plan, $gradedata, $curstudent->cpassedid, $editid, $eventid, $scale);
                    // получем оценку студента
                    $grade->color                = $this->get_color_cell($cpassed->studentid, $plan, $curstudent->cpassedid, $editid, $eventid);
                    // добавляем оценку в массив оценок ученика
                    $curstudent->studentgrades[] = $grade;
                }
            }
            if ( $info == 'grades' )
            {
                unset($curstudent->fio);
                unset($curstudent->studentnumber);
            }
            // вернем информацию о студенте
            return $curstudent;
    }

    /**
     * Возвращает данные для всех студентов
     *
     * @param array $plans - контрольные точки тематического плана учебного процесса
     * @param int $editid - ID редактируемой контрольной точки
     * @param int $eventid - ID редактируемого события
     *
     * @param string $info - показывает иформацию, что нужно вывести, если пусто,
     * 							то выводить всю информацию
     *
     * @return array информация о всех студентах данного потока
     */
    private function get_lines_for_students($plans, $editid, $eventid, $info = '')
    {
        // Настройка "Отображать отписанных учеников в журнале группы"
        $showjunk = $this->dof->storage('config')->get_config_value('showjunkstudents',
            'im', 'journal', optional_param('departmentid', 0, PARAM_INT));

        // Получение всех подписок на предмето-класс
        $cpasseds = $this->get_cpassed($showjunk);

        $result = [];
        if ( $cpasseds )
        {// Подписки найдены
            $i = 0;
            foreach ( $cpasseds as $cpassed )
            {
                // Текущий порядковый номер
                ++$i;
                // Добавление информации о студенте
                $result[$cpassed->id] = $this->get_line_for_student($i, $cpassed, $plans, $editid, $eventid, $info);
            }
        }
        return $result;
    }

    /** Создает строку дат для вывода журнала
     *
     * @return object объект, содержащий массив с данными
     * @param object $plans - массив контрольных  точек учебного потока или false в случае неудачи
     */
    private function create_datesstring($plans)
    {
        $result     = new stdClass();
        // создаем счетчик месяцев
        $monthcount = 0;
        $oldmname   = '';
        if ( !$plans )
        {// не переданно ни одной темы планирования - построить строку дат не удастся
            return false;
        }
        // получаем строку дат
        $dates = $this->generate_all_dates($plans);
        foreach ( $dates as $date )
        {// перебираем все события и собираем массивы дат и названий месяцев
            // создаем якорь
            $anchor                 = new stdClass();
            $anchor->anchornum      = $date->date;
            $result->upper_anchor[] = $anchor;
            // вычисляем название текущего месяца
            $mname                  = dof_im_journal_format_date($date->date, 'm');

            // если про просматриваемая дата не находится в том же месяце, что и предыдущая,
            // то дополняем список месяцев
            if ( $oldmname != $mname )
            {
                $monthcount++;
                // создаем объект месяца
                $result->monthtitle[$monthcount]         = new stdClass();
                // заполняем название месяца
                $result->monthtitle[$monthcount]->mtitle = $mname;
                $oldmname                                = $mname;
            }
            // прибавляем счетчик дат в месяце
            if( empty($result->monthtitle[$monthcount]->mcolspan) )
            {
                $result->monthtitle[$monthcount]->mcolspan = 1;
            } else
            {
                $result->monthtitle[$monthcount]->mcolspan++;
            }
            // записываем новую дату в журнал
            $result->monthdate[] = $date;
        }
        return $result;
    }

    /** Вызывается из generate_all_dates Создает один объект даты для журнала.
     *
     * @return object дата в нужном для templater'a формате
     * @param object $plan
     * @param object $event[optional]
     */
    private function generate_single_date($plan, $date, $event = null)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        // устанавливаем путь к теме в планировании
        $dayurl                  = '#' . $date;
        if ( $event AND is_object($event) )
        {
            $eventid = $event->id;
        } else
        {
            $eventid = 0;
        }

        // Генерация ссылки на редактирование записи вместе с якорем
        $addvars = [
            'csid' => $this->csid,
            'planid' => $plan->id,
            'eventid' => $eventid,
            'departmentid' => $depid
        ];
        $editurl = $this->dof->url_im('journal', '/group_journal/index.php', $addvars).'#jm'.$date;

        $dateobject = new stdClass();
        if ( ! $event )
        {// Элемент тематического плана без привязки к событию

            // Краткое название занятия
            $dateobject->datecode = $plan->name;

            // Добавление данных по редактированию занятия
            if ( $this->dof->im('journal')->is_access('give_grade', $plan->id) OR
                 $this->dof->im('journal')->is_access('give_grade/in_own_journal', $plan->id) )
            {
                $dateobject->datecode .= dof_im_journal_date_edit(null, 'd', $dayurl, $editurl);
            } else
            {
                $dateobject->datecode .= dof_im_journal_date_edit(null, 'd', $dayurl);
            }
        } else
        {// Элемент тематического плана с привязкой к событию

            if ( $this->dof->im('journal')->is_access('give_grade', $plan->id) OR
                 $this->dof->im('journal')->is_access('give_grade/in_own_journal', $plan->id) )
            {// если статус неактивный выведем просто даты
                $dateobject->datecode = dof_im_journal_date_edit($date, 'd', $dayurl, $editurl);
            } else
            {// если активный, то выведем значек редактирования
                $dateobject->datecode = dof_im_journal_date_edit($date, 'd', $dayurl);
            }

            // сделаем дату жирной на текущее время
            $evdate = dof_usergetdate($date);
            $tmdate = dof_usergetdate(time());
            if ( $evdate['mon'] == $tmdate['mon']
                AND $evdate['mday'] == $tmdate['mday']
                AND $evdate['year'] == $tmdate['year'] )
            {
                $dateobject->datecode = '<b>' . $dateobject->datecode . '</b>';
                if ( ($date < time()) AND ( ($date + $event->duration) > time()) )
                {
                    $dateobject->datecode = '<div id="menu">' . $dateobject->datecode . '</div>';
                }
            }
            if ( ! empty($event->url) )
            {// Есть ссылка на занятие, покажем модалку
                $dateobject->datecode = '<div class="show_modal_url" data-url="' . $event->url . '" >' . $dateobject->datecode . '</div>';
            }
        }

        // Добавление информации о метке времени
        $dateobject->date = $date;

        return $dateobject;
    }



    /** Вызывается из generate_datesstring. Получить строку со всеми датами для журнала
     *
     * @return array - даты для вывода в журнал
     * @param array $plans - массив контрольных точек учебного потока
     */
    private function generate_all_dates($plans)
    {
        $result = array();
        // собираем даты
        foreach ( $plans as $plan )
        {// получим событие, которое относится к данной теме тематического планирования
            if ( isset($plan->event) AND is_object($plan->event) )
            {// если событие есть - то покажем дату
                $result[] = $this->generate_single_date($plan->plan, $plan->date, $plan->event);
            } else
            {// если события нет - только название
                $result[] = $this->generate_single_date($plan->plan, $plan->date);
            }
        }
        return $result;
    }

    /** Возвращает масив оценок нужной структуры, для использования в форме.
     * @return array массив объектов вида
     *         value->'значение оценки'
     *         name->'отображаемое в форме имя оценки'
     *         selected->'selected', если вы хотите видеть этот пункт выбранным по умолчанию или null,
     *         в противном случае
     * @param string $scale - тип используемой шкалы
     */
    public function get_grade_variants($scale = null)
    {
        $fromplan = $this->dof->modlib('journal')->get_manager('scale')->get_grades_scale_str($scale);
        $variants = array();
        foreach ( $fromplan as $gradevariant )
        {
            $variant        = new stdClass();
            $variant->name  = $gradevariant;
            $variant->value = $gradevariant;
            $variants[]     = $variant;
        }
        // по умолчанию к любой шкале добавляем "нулевую оценку" - для того, чтобы ее можно было удалить
        $variant        = new stdClass();
        $variant->name  = ' ';
        $variant->value = '';
        $variants[]     = $variant;
        // возвращаем шкалу оценок в указанном виде
        return $variants;
    }

    /** Получить id html-якоря для редактированияячейки
     *
     * @return
     * @param object $plans
     */
    private function get_anchor($plans, $planid, $eventid)
    {
        if ( !is_array($plans) )
        {// неверный формат исходных данных
            return 0;
        }

        foreach ( $plans as $anchor => $plan )
        {// ищем нужный id в массиве
            if ( isset($plan->event) )
            {//если есть событие
                if ( $plan->plan->id == $planid AND $plan->event->id == $eventid )
                {//проверяем и КТ и событие
                    return $anchor;
                }
            } else
            {//события нет
                if ( $plan->plan->id == $planid )
                {//проверяем только КТ
                    return $anchor;
                }
            }
        }
        // если ничего не нашли
        return 0;
    }

    /**
     * Получить блок комментариев по посещаемости
     *
     * @param $presenceid - ID элемента учета посещаемости
     * @param $options - ID элемента учета посещаемости
     *
     * @return string - HTML-блок с комментариями по элементу
     */
    private function presence_comment_block($presenceid, $options = [])
    {
        $html = '';

        // Добавление комментария по ячейке
        if ( $this->dof->plugin_exists('im', 'comments') )
        {// Получить форму комментариев
            if ( $presenceid > 0 )
            {// Есть посещаемость
                // Получение списка комментариев
                $content = $this->dof->im('comments')->show_comments_list(
                    'storage',
                    'schpresences',
                    $presenceid,
                    NULL,
                    ['return_html' => true, 'disable_actions' => true]
                    );
                if ( ! empty($content) )
                {
                    $title = $this->dof->get_string('groupjournal_comment_modal_title', 'journal');
                    $label = dof_html_writer::span(
                        $this->dof->modlib('ig')->icon('feedback', NULL),
                        'btn button dof_button grpjournal_comment_modal_label grpjournal_has_comments'
                        );
                    $html .= $this->dof->modlib('widgets')->modal($label, $content, $title);
                }
            }
        }
        return $html;
    }
}
