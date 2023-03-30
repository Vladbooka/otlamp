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
 * Отчет по задолжникам
 *
 * @package    block_dof
 * @subpackage im_journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_im_journal_rtreport_debtor extends dof_modlib_rtreport_base
{
    /**
     * Группировка
     * user - по пользователю
     * group - по академ группе
     * department - по подразделению
     *
     * @var integer
     */
    protected $grouping = 'user';

    /**
     * Учебные процессы
     *
     * @var array
     */
    protected $cstreams = [];

    /**
     * Занятия учебны процессов
     *
     * @var []dof_lesson
     */
    protected $lessons = [];

    /**
     * Основной двигатель прогресса
     *
     * @var array
     */
    protected $cpasseds = [];

    /**
     * Текущий URL
     *
     * @var moodle_url
     */
    protected $currenturl = null;

    /**
     * Получение кода рейтинга
     *
     * @return string
     */
    public function get_type_code()
    {
        return 'debtor';
    }

    /**
     * Получение таблицы
     *
     * {@inheritDoc}
     * @see dof_modlib_rtreport_base::get_cft()
     */
    protected function get_cft()
    {
        if ( empty($this->cpasseds) )
        {
            return $this->dof->modlib('widgets')->cross_format_table();
        }

        if ( ! method_exists($this, 'get_cft_' . $this->data['filter']->grouping) )
        {
            throw new dof_exception('invalid_grouping', 'journal');
        }

        return $this->{'get_cft_' . $this->data['filter']->grouping}();

    }

    /**
     * Формирование данных для таблицы с группировкой по подразделению и параллели
     *
     * @return dof_cross_format_table
     */
    protected function get_cft_department_with_parallel()
    {
        $filename = 'report_' . date('d-m-Y-H-i-s');

        $defaultstyle = $this->dof->modlib('widgets')->cross_format_table_cell_style();
        $defaultstyle->set_font_size(14);
        $defaultstyle->set_vertical_align('middle');
        $defaultstyle->set_text_align('center');
        $defaultstyle->set_border_width(1);
        $defaultstyle->set_border_color(100, 100, 100);
        $defaultstyle->set_word_break('normal');

        $firstcolstyle = clone($defaultstyle);
        $firstcolstyle->set_text_align('left');

        $cft = $this->dof->modlib('widgets')->cross_format_table($filename, $defaultstyle);

        if ( empty($this->cpasseds) )
        {
            return $cft;
        }

        $cft->add_cell($this->dof->get_string('rtreport_debtor__header_department_with_parallel', 'journal'))
            ->set_rowspan(2)
            ->set_colspan(1);

        $cft->add_cell($this->dof->get_string('rtreport_debtor__header_count_debtors', 'journal'))
            ->set_rowspan(2)
            ->set_colspan(1);

        $cft->add_cell($this->dof->get_string('rtreport_debtor__header_count_debtors_by_reason', 'journal'))
            ->set_rowspan(1)
            ->set_colspan(2);

        $cft->add_cell($this->dof->get_string('rtreport_debtor__header_count_debtors_workedout', 'journal', (object)['fromdate' => date('d.m.Y', $this->data['filter']->fromdate), 'todate' => date('d.m.Y', $this->data['filter']->todate)]), null, 4)
            ->set_rowspan(2)
            ->set_colspan(1);

        $cft->add_cell($this->dof->get_string('rtreport_debtor__header_fullname_under_extracontrol', 'journal'))
            ->set_rowspan(2)
            ->set_colspan(1);
        $cft->increase_rownum();

        $cft->add_cell($this->dof->get_string('rtreport_debtor__header_count_debtors_by_reason_bad_grade', 'journal'), 1, 2);
        $cft->add_cell($this->dof->get_string('rtreport_debtor__header_count_debtors_by_reason_skip_lesson', 'journal'), 1, 3);


        $departments = [];
        foreach ($this->cpasseds as $cpassed)
        {
            if ( empty($cpassed['info']->amountdebts) )
            {
                continue;
            }
            if ( ! array_key_exists($this->cstreams[$cpassed['cpassed']->cstreamid]->departmentid . '_' . $cpassed['lhepisode']->agenum, $departments) )
            {
                $departments[$this->cstreams[$cpassed['cpassed']->cstreamid]->departmentid . '_' . $cpassed['lhepisode']->agenum] = new stdClass();
                $departments[$this->cstreams[$cpassed['cpassed']->cstreamid]->departmentid . '_' . $cpassed['lhepisode']->agenum]->agenum = $cpassed['lhepisode']->agenum;
                $departments[$this->cstreams[$cpassed['cpassed']->cstreamid]->departmentid . '_' . $cpassed['lhepisode']->agenum]->department = $this->dof->storage('departments')->get($this->cstreams[$cpassed['cpassed']->cstreamid]->departmentid);
                $departments[$this->cstreams[$cpassed['cpassed']->cstreamid]->departmentid . '_' . $cpassed['lhepisode']->agenum]->amountdebtsusers = [];
                $departments[$this->cstreams[$cpassed['cpassed']->cstreamid]->departmentid . '_' . $cpassed['lhepisode']->agenum]->amountdebtsuserscount = 0;
                $departments[$this->cstreams[$cpassed['cpassed']->cstreamid]->departmentid . '_' . $cpassed['lhepisode']->agenum]->amountdebtsbyskippedlesson = 0;
                $departments[$this->cstreams[$cpassed['cpassed']->cstreamid]->departmentid . '_' . $cpassed['lhepisode']->agenum]->amountdebtsbybadgrade = 0;
                $departments[$this->cstreams[$cpassed['cpassed']->cstreamid]->departmentid . '_' . $cpassed['lhepisode']->agenum]->amountusersworked = 0;
            }
            if ( ! array_key_exists($cpassed['cpassed']->studentid, $departments[$this->cstreams[$cpassed['cpassed']->cstreamid]->departmentid . '_' . $cpassed['lhepisode']->agenum]->amountdebtsusers) )
            {
                $departments[$this->cstreams[$cpassed['cpassed']->cstreamid]->departmentid . '_' . $cpassed['lhepisode']->agenum]->amountdebtsuserscount++;
                $departments[$this->cstreams[$cpassed['cpassed']->cstreamid]->departmentid . '_' . $cpassed['lhepisode']->agenum]->amountdebtsusers[$cpassed['cpassed']->studentid] = true;
                if ( ! empty($cpassed['info']->amountworked) )
                {
                    $departments[$this->cstreams[$cpassed['cpassed']->cstreamid]->departmentid . '_' . $cpassed['lhepisode']->agenum]->amountusersworked++;
                }
            }
            $departments[$this->cstreams[$cpassed['cpassed']->cstreamid]->departmentid . '_' . $cpassed['lhepisode']->agenum]->amountdebtsbyskippedlesson += $cpassed['info']->amountdebtsbyskippedlesson;
            $departments[$this->cstreams[$cpassed['cpassed']->cstreamid]->departmentid . '_' . $cpassed['lhepisode']->agenum]->amountdebtsbybadgrade += $cpassed['info']->amountdebtsbybadgrade;
        }

        foreach ($departments as $departmentinfo)
        {
            $cft->increase_rownum();
            $cft->add_cell(
                    $this->dof->get_string(
                            'rtreport_debtor__row_department_with_parallel', 'journal',
                            (object)['parallel' => $departmentinfo->agenum, 'departmentname' => $departmentinfo->department->name]));


            $params = $this->currenturl->params();
            $params['departmentid'] = $departmentinfo->department->id;
            $params['includechildren'] = 0;
            $params['parallels'] = [$departmentinfo->agenum => $departmentinfo->agenum];
            $params['grouping'] = 'user';
            $url = new moodle_url($this->currenturl->get_path(false) . '?' . http_build_query($params));
            $cft->add_cell(dof_html_writer::link($url->out(false), $departmentinfo->amountdebtsuserscount, ['style' => 'display: inline; border-bottom: 1px dashed #999;']));

            $cft->add_cell($departmentinfo->amountdebtsbybadgrade);
            $cft->add_cell($departmentinfo->amountdebtsbyskippedlesson);
            $cft->add_cell($departmentinfo->amountusersworked);
        }

        $cft->set_style($firstcolstyle, 0, 0, $cft->get_max_rownum(), 0);

        return $cft;
    }

    /**
     * Формирование данных для таблицы с группировкой по группе
     *
     * @return dof_cross_format_table
     */
    protected function get_cft_group()
    {
        $filename = 'report_' . date('d-m-Y-H-i-s');

        $defaultstyle = $this->dof->modlib('widgets')->cross_format_table_cell_style();
        $defaultstyle->set_font_size(14);
        $defaultstyle->set_vertical_align('middle');
        $defaultstyle->set_text_align('center');
        $defaultstyle->set_border_width(1);
        $defaultstyle->set_border_color(100, 100, 100);
        $defaultstyle->set_word_break('normal');

        $firstcolstyle = clone($defaultstyle);
        $firstcolstyle->set_text_align('left');

        $cft = $this->dof->modlib('widgets')->cross_format_table($filename, $defaultstyle);

        if ( empty($this->cpasseds) )
        {
            return $cft;
        }


        $cft->add_cell($this->dof->get_string('rtreport_debtor__header_group', 'journal'))
        ->set_rowspan(2)
        ->set_colspan(1);

        $cft->add_cell($this->dof->get_string('rtreport_debtor__header_count_debtors', 'journal'))
        ->set_rowspan(2)
        ->set_colspan(1);

        $cft->add_cell($this->dof->get_string('rtreport_debtor__header_count_debtors_by_reason', 'journal'))
        ->set_rowspan(1)
        ->set_colspan(2);

        $cft->add_cell($this->dof->get_string('rtreport_debtor__header_count_debtors_workedout', 'journal', (object)['fromdate' => date('d.m.Y', $this->data['filter']->fromdate), 'todate' => date('d.m.Y', $this->data['filter']->todate)]), null, 4)
        ->set_rowspan(2)
        ->set_colspan(1);

        $cft->add_cell($this->dof->get_string('rtreport_debtor__header_fullname_under_extracontrol', 'journal'))
        ->set_rowspan(2)
        ->set_colspan(1);

        $cft->increase_rownum();

        $cft->add_cell($this->dof->get_string('rtreport_debtor__header_count_debtors_by_reason_bad_grade', 'journal'), 1, 2);
        $cft->add_cell($this->dof->get_string('rtreport_debtor__header_count_debtors_by_reason_skip_lesson', 'journal'), 1, 3);

        $groups = [];
        foreach ($this->cpasseds as $cpassed)
        {
            if ( empty($cpassed['info']->amountdebts) )
            {
                continue;
            }
            $cpassed['cpassed']->agroupid = (int)$cpassed['cpassed']->agroupid;
            if ( ! array_key_exists($cpassed['cpassed']->agroupid, $groups) )
            {
                $groups[$cpassed['cpassed']->agroupid] = new stdClass();
                $groups[$cpassed['cpassed']->agroupid]->groupname = ! empty($cpassed['cpassed']->agroupid) ? $this->dof->storage('agroups')->get($cpassed['cpassed']->agroupid)->name : $this->dof->get_string('rtreport_debtor__row_missing_group', 'journal');
                $groups[$cpassed['cpassed']->agroupid]->amountdebtsusers = [];
                $groups[$cpassed['cpassed']->agroupid]->amountdebtsuserscount = 0;
                $groups[$cpassed['cpassed']->agroupid]->amountdebtsbyskippedlesson = 0;
                $groups[$cpassed['cpassed']->agroupid]->amountdebtsbybadgrade = 0;
                $groups[$cpassed['cpassed']->agroupid]->amountusersworked = 0;
            }
            if ( ! array_key_exists($cpassed['cpassed']->studentid, $groups[$cpassed['cpassed']->agroupid]->amountdebtsusers) )
            {
                $groups[$cpassed['cpassed']->agroupid]->amountdebtsuserscount++;
                $groups[$cpassed['cpassed']->agroupid]->amountdebtsusers[$cpassed['cpassed']->studentid] = true;
                if ( ! empty($cpassed['info']->amountworked) )
                {
                    $groups[$cpassed['cpassed']->agroupid]->amountusersworked++;
                }
            }
            $groups[$cpassed['cpassed']->agroupid]->amountdebtsbyskippedlesson += $cpassed['info']->amountdebtsbyskippedlesson;
            $groups[$cpassed['cpassed']->agroupid]->amountdebtsbybadgrade += $cpassed['info']->amountdebtsbybadgrade;
        }

        foreach ($groups as $groupinfo)
        {
            $cft->increase_rownum();

            $cft->add_cell($groupinfo->groupname);
            $cft->add_cell($groupinfo->amountdebtsuserscount);
            $cft->add_cell($groupinfo->amountdebtsbybadgrade);
            $cft->add_cell($groupinfo->amountdebtsbyskippedlesson);
            $cft->add_cell($groupinfo->amountusersworked);
        }

        $cft->set_style($firstcolstyle, 0, 0, $cft->get_max_rownum(), 0);

        return $cft;
    }

    /**
     * Формирование данных для таблицы с группировкой по пользователям
     *
     * @return dof_cross_format_table
     */
    protected function get_cft_user()
    {
        $filename = 'report_' . date('d-m-Y-H-i-s');

        $defaultstyle = $this->dof->modlib('widgets')->cross_format_table_cell_style();
        $defaultstyle->set_font_size(14);
        $defaultstyle->set_vertical_align('middle');
        $defaultstyle->set_text_align('center');
        $defaultstyle->set_border_width(1);
        $defaultstyle->set_border_color(100, 100, 100);
        $defaultstyle->set_word_break('normal');

        $firstcolstyle = clone($defaultstyle);
        $firstcolstyle->set_text_align('left');

        $cft = $this->dof->modlib('widgets')->cross_format_table($filename, $defaultstyle);

        if ( empty($this->cpasseds) )
        {
            return $cft;
        }

        $cft->add_cell($this->dof->get_string('rtreport_debtor__header_fullname', 'journal'))
            ->set_rowspan(2)
            ->set_colspan(1);

        $cft->add_cell($this->dof->get_string('rtreport_debtor__header_count_debtors_by_reason', 'journal'))
            ->set_rowspan(1)
            ->set_colspan(2);

        $cft->increase_rownum();
        $cft->add_cell($this->dof->get_string('rtreport_debtor__header_count_debtors_by_reason_bad_grade', 'journal'), null, 1)
            ->set_rowspan(1)
            ->set_colspan(1);

        $cft->add_cell($this->dof->get_string('rtreport_debtor__header_count_debtors_by_reason_skip_lesson', 'journal'), null, 2)
            ->set_rowspan(1)
            ->set_colspan(1);

        $users = [];
        foreach ($this->cpasseds as $cpassed)
        {
            if ( ! array_key_exists($cpassed['cpassed']->studentid, $users) )
            {
                $users[$cpassed['cpassed']->studentid] = new stdClass();
                $users[$cpassed['cpassed']->studentid]->user = $this->dof->storage('persons')->get($cpassed['cpassed']->studentid);
                $users[$cpassed['cpassed']->studentid]->amountdebtsbyskippedlesson = 0;
                $users[$cpassed['cpassed']->studentid]->amountdebtsbybadgrade = 0;
            }
            $users[$cpassed['cpassed']->studentid]->amountdebtsbybadgrade += $cpassed['info']->amountdebtsbybadgrade;
            $users[$cpassed['cpassed']->studentid]->amountdebtsbyskippedlesson += $cpassed['info']->amountdebtsbyskippedlesson;
        }

        foreach ($users as $userinfo)
        {
            if ( empty($userinfo->amountdebtsbybadgrade) && empty($userinfo->amountdebtsbyskippedlesson) )
            {
                continue;
            }
            $cft->increase_rownum();
            $cft->add_cell($this->dof->storage('persons')->get_fullname($userinfo->user->id));
            $cft->add_cell($userinfo->amountdebtsbybadgrade);
            $cft->add_cell($userinfo->amountdebtsbyskippedlesson);
        }

        $cft->set_style($firstcolstyle, 0, 0, $cft->get_max_rownum(), 0);

        return $cft;
    }

    /**
     * Установка данных
     *
     * @see dof_modlib_rtreport_base::set_variables()
     */
    protected function set_variables()
    {
        global $addvars;

        // Формирование URL формы
        $url = $this->dof->url_im('journal','/debtor/index.php', $addvars + ['departmentid' => optional_param('departmentid', 0, PARAM_INT)]);

        // Формирование дополнительных данных
        $customdata = new stdClass;
        $customdata->dof = $this->dof;
        $customdata->addvars = $addvars + ['departmentid' => optional_param('departmentid', 0, PARAM_INT)];

        // Форма сохранения подразделения
        $form = new dof_im_journal_form_debtor($url, $customdata, 'get');
        $form->process();
        $filter = $form->get_filter();

        $result = [
            'form' => $form
        ];

        if ( ! empty($filter) )
        {
            $result['title'] = $this->dof->get_string('rtreport_debtor__title', 'journal');
            $filter->signer = '';
            if ( ! empty($filter->signerid) )
            {
                $filter->signer = $this->dof->storage('persons')->get_fullname_initials($filter->signerid);
            }

            $result['filter'] = $filter;
            $this->currenturl = $filter->url;
            if ( ! empty($filter->exporter) )
            {
                $this->set_exporter($filter->exporter);
            }
        }

        $this->set_data($result);

        // сбор данных
        $this->data();
    }

    /**
     * Установка подписок на учебные процессы с данными для дальнейщего сбора отчета
     *
     * @return void
     */
    protected function data()
    {
        if ( ! empty($this->data['filter']) )
        {
            dof_hugeprocess();
            session_write_close();

            // Статусы для подписок
            $statusesbcs = [
                'active',
                'condactive',
                'suspend',
                'active',
                'failed',
                'completed'
            ];

            // Статусы для подписок на учебные процессы
            $statusescpasseds = $this->dof->workflow('cpassed')->get_meta_list('real');

            // Получение статусов учебных процессов
            $cstreamstatuses = $this->dof->workflow('cstreams')->get_meta_list('real');

            $allbcs = [];
            if ( empty($this->data['filter']->grouping) || $this->data['filter']->grouping > 2 )
            {
                $this->grouping = 0;
            } else
            {
                $this->grouping = (int)$this->data['filter']->grouping;
            }
            if ( empty($this->data['filter']->departments) ||
                    empty($this->data['filter']->ages) ||
                    empty($this->data['filter']->parallels) ||
                    empty($this->data['filter']->programms) )
            {
                return;
            }

            // Получение истории
            $listlearninghistory = $this->dof->storage('learninghistory')->get_records(
                    [
                        'ageid' => array_keys($this->data['filter']->ages),
                        'agenum' => $this->data['filter']->parallels
                    ]);
            foreach ($listlearninghistory as $episode)
            {
                if ( ! array_key_exists($episode->programmsbcid, $allbcs) )
                {
                    if ( $bcs = $this->dof->storage('programmsbcs')->get_record(['id' => $episode->programmsbcid, 'status' => $statusesbcs]) )
                    {
                        $allbcs[$bcs->id] = $bcs;
                    } else
                    {
                        continue;
                    }
                } else {
                    $bcs = $allbcs[$episode->programmsbcid];
                }
                if ( ! array_key_exists($allbcs[$bcs->id]->programmid, $this->data['filter']->programms) )
                {
                    continue;
                }
                if ( $cpasseds = $this->dof->im('journal')->get_cpasseds_by_programmbcs([$bcs]) )
                {
                    foreach ($cpasseds as $cpassed)
                    {
                        if ( ! array_key_exists($cpassed->status, $statusescpasseds) )
                        {
                            continue;
                        }
                        if ( ! array_key_exists($cpassed->cstreamid, $this->cstreams) )
                        {
                            $this->cstreams[$cpassed->cstreamid] = $this->dof->storage('cstreams')->get_record(['id' => $cpassed->cstreamid]);
                        }
                        if ( empty($this->cstreams[$cpassed->cstreamid]) )
                        {
                            continue;
                        }
                        if ( ! array_key_exists($this->cstreams[$cpassed->cstreamid]->ageid, $this->data['filter']->ages) ||
                                ! array_key_exists($this->cstreams[$cpassed->cstreamid]->status, $cstreamstatuses) ||
                                ! array_key_exists($this->cstreams[$cpassed->cstreamid]->departmentid, $this->data['filter']->departments) )
                        {
                            continue;
                        }
                        if ( ! array_key_exists($cpassed->cstreamid, $this->lessons) )
                        {
                            $this->lessons[$cpassed->cstreamid] = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_lessons($cpassed->cstreamid, true)->get_lessons();
                        }
                        $this->cpasseds[] = [
                            'lhepisode' => $episode,
                            'cpassed' => $cpassed,
                            'info' => $this->dof->modlib('learningplan')->get_manager('cpassed')->get_cpassed_info($cpassed, $this->lessons[$cpassed->cstreamid], $this->data['filter']->fromdate, $this->data['filter']->todate)];
                    }
                } else
                {
                    continue;
                }
            }
        }
    }

    /**
     * Получение HTML Заголовка
     *
     * @return string
     */
    public function get_header()
    {
        $html = $this->data['form']->render();
        if (!empty($this->data['title']))
        {
            $html .= dof_html_writer::tag('h1', $this->data['title'], ['class' => 'debtor_report_title']);
        }
        return $html;
    }

    /**
     * Установка навигации
     *
     * @return void
     */
    public function set_nvg()
    {
        $this->dof->modlib('nvg')->add_level(
                $this->dof->get_string('debtor', 'journal'),
                $this->dof->url_im('journal', '/debtor/index.php', $this->get_variables())
                );
    }


    /**
     * Запуск сбора рейтинга
     *
     * @return void | string
     */
    public function run()
    {
        // Установка классом нужных ему данных
        $this->set_variables();

        $cft = $this->get_rtreport_data();

        // шапка документа, его верхняя правая часть
        $header = $this->dof->get_string('rtreport_debtor__docheader', 'journal');
        // название отчета
        $title = $this->dof->get_string('rtreport_debtor__title', 'journal');
        // дата формирования отчета
        $date = dof_userdate(time(), '%d.%m.%Y');
        // подписант
        $signer = '';
        $signerinfo = '';
        if (!empty($this->data['filter']->signer))
        {
            $signer = $this->data['filter']->signer;
        }
        if (!empty($this->data['filter']->signerinfo))
        {
            $signerinfo = $this->data['filter']->signerinfo;
        }

        $exporter = $this->get_exporter();
        if (empty($exporter) || $exporter == 'html')
        {
            // это не экспорт, просто возвращаем html с таблицей
            return dof_html_writer::div($cft->get_html());
        } else
        {
            switch($exporter)
            {
                case 'docx':
                    $phpWord = new \PhpOffice\PhpWord\PhpWord();
                    $section = $phpWord->addSection();

                    $sectionstyle = $section->getStyle();
                    $sectionwidth = $sectionstyle->getPageSizeW() - $sectionstyle->getMarginRight() - $sectionstyle->getMarginLeft();

                    if ($header!='')
                    {
                        $section->addText($header, [], ['alignment' => 'right']);
                        $section->addTextBreak(3);
                    }
                    if ($title!='')
                    {
                        $section->addText($title, [], ['alignment' => 'center']);
                        $section->addTextBreak();
                    }

                    $cft->add_docx_table($section);

                    $section->addTextBreak();

                    if ( ! empty($signerinfo) )
                    {
                        foreach ( explode(PHP_EOL, $signerinfo) as $string )
                        {
                            $section->addText($string, [],[
                                'alignment' => 'right'
                            ]);
                        }
                    }

                    $section->addText(
                        $date . "\t" . $signer,
                        [],
                        [
                            'alignment' => 'left',
                            'tabs' => [
                                new \PhpOffice\PhpWord\Style\Tab(
                                    \PhpOffice\PhpWord\Style\Tab::TAB_STOP_RIGHT,
                                    $sectionwidth)
                            ]
                        ]
                    );

                    $file = $cft->get_name() . '.docx';
                    header("Content-Description: File Transfer");
                    header('Content-Disposition: attachment; filename="' . $file . '"');
                    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
                    header('Content-Transfer-Encoding: binary');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Expires: 0');
                    $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
                    $xmlWriter->save("php://output");
                    die;
                    break;
                case 'pdf':
                    $prehtml = '';
                    if ($header!='')
                    {
                        $prehtml .= dof_html_writer::div($header, '', ['style' => 'text-align: right']);
                        $prehtml .= dof_html_writer::div('', '', ['style' => 'height:50px;']);
                    }
                    if ($title!='')
                    {
                        $prehtml .= dof_html_writer::div($title, '', ['style' => 'text-align: center']);
                        $prehtml .= dof_html_writer::div('', '', ['style' => 'height:25px;']);
                    }

                    $posthtml = dof_html_writer::div('', '', ['style' => 'height:25px;']);
                    $posthtmltable = new html_table();
                    $posthtmltable->attributes = [
                        'width' => '100%',
                        'cellpadding' => '0',
                        'border' => '0'
                    ];
                    if ( ! empty($signerinfo) )
                    {
                        $row = 0;
                        foreach ( explode(PHP_EOL, $signerinfo) as $string )
                        {
                            $row++;
                            $posthtmltable->data[$row][0] = new html_table_cell('');
                            $posthtmltable->data[$row][0]->attributes['align'] = 'left';
                            $posthtmltable->data[$row][0]->attributes['width'] = '50%';
                            $posthtmltable->data[$row][1] = new html_table_cell($string);
                            $posthtmltable->data[$row][1]->attributes['align'] = 'right';
                            $posthtmltable->data[$row][1]->attributes['width'] = '50%';
                        }
                        $row++;
                        $posthtmltable->data[0][0] = new html_table_cell($date);
                        $posthtmltable->data[0][0]->attributes['align'] = 'left';
                        $posthtmltable->data[0][0]->attributes['width'] = '50%';
                        $posthtmltable->data[0][1] = new html_table_cell($signer);
                        $posthtmltable->data[0][1]->attributes['align'] = 'right';
                        $posthtmltable->data[0][1]->attributes['width'] = '50%';
                    }
                    $posthtml .= dof_html_writer::table($posthtmltable);

                    $cft->print_pdf('P', [
                        'prehtml' => $prehtml,
                        'posthtml' => $posthtml
                    ]);
                    break;
                default:
                    $cft->{'print_'.$exporter}();
                    exit;
                    break;
            }
        }
    }


    /**
     * Установка экспортера - у нас тут есть поддержка docx (потому что cft!),
     * поэтому переопределяем стандартный метод
     *
     * @param string $exporter
     *
     * @return void
     */
    public function set_exporter($exporter)
    {
        if ( dof_modlib_rtreport_helper_exporter::is_valid($exporter) || strtolower($exporter) == 'docx' )
        {
            $this->exporter = strtolower($exporter);
        }
    }
}

