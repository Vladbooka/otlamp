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
 * Отчет по индивидуальным планам развития "Сводная статистика по подразделениям".
 *
 * @package    im
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_im_achievements_rtreport_idp_summary extends dof_modlib_rtreport_base
{
    /**
     * Ячейки, которые необходимо стилизовать
     *
     * @var array
     */
    protected $style_cells = [];
    
    /**
     * Получение кода рейтинга
     *
     * @return string
     */
    public function get_type_code()
    {
        return 'idp_summary';
    }
    
    /**
     * Особое получение заголовков для XLS и ODS
     *
     * @return []
     */
    protected function get_headers_values()
    {
        $result = [];
        
        if ( empty($this->data['result']) )
        {
            return $result;
        }
        
        $row = [];
        
        $firstheadercellstyle = ['bold' => '600', 'border' => '1', 'v_align' => 'center', 'h_align' => 'left', 'text_wrap' => '1'];
        $headercellstyle = ['bold' => '600', 'border' => '1', 'v_align' => 'center', 'h_align' => 'center', 'text_wrap' => '1'];
        
        // Наименование подразделения
        $row[] = $this->dof->get_string('report__idp_summary__department_name', 'achievements');
        $this->style_cells['0_0'] = $firstheadercellstyle;
        // Код подразделения
        $row[] = $this->dof->get_string('report__idp_summary__department_code', 'achievements');
        $this->style_cells['0_1'] = $headercellstyle;
        // Количество пользователей
        $row[] = $this->dof->get_string('report__idp_summary__persons_count', 'achievements');
        $this->style_cells['0_2'] = $headercellstyle;
        // Количество пользователей с учетом дочерних подразделений
        $row[] = $this->dof->get_string('report__idp_summary__sum_persons_count', 'achievements');
        $this->style_cells['0_3'] = $headercellstyle;
        // Количество целей, ожидающих одобрения
        $row[] = $this->dof->get_string('report__idp_summary__wait_approve_goals_count', 'achievements');
        $this->style_cells['0_4'] = $headercellstyle;
        // Количество целей, ожидающих одобрения с учетом дочерних подразделений
        $row[] = $this->dof->get_string('report__idp_summary__sum_wait_approve_goals_count', 'achievements');
        $this->style_cells['0_5'] = $headercellstyle;
        // Количество одобренных целей
        $row[] = $this->dof->get_string('report__idp_summary__approved_goals_count', 'achievements');
        $this->style_cells['0_6'] = $headercellstyle;
        // Количество одобренных целей с учетом дочерних подразделений
        $row[] = $this->dof->get_string('report__idp_summary__sum_approved_goals_count', 'achievements');
        $this->style_cells['0_7'] = $headercellstyle;
        // Количество просроченных дедлайнов
        $row[] = $this->dof->get_string('report__idp_summary__expired_deadlines_count', 'achievements');
        $this->style_cells['0_8'] = $headercellstyle;
        // Количество просроченных дедлайнов с учетом дочерних подразделений
        $row[] = $this->dof->get_string('report__idp_summary__sum_expired_deadlines_count', 'achievements');
        $this->style_cells['0_9'] = $headercellstyle;
        // Количество достижений, ожидающих подтверждения
        $row[] = $this->dof->get_string('report__idp_summary__wait_approve_achievements_count', 'achievements');
        $this->style_cells['0_10'] = $headercellstyle;
        // Количество достижений, ожидающих подтверждения с учетом дочерних подразделений
        $row[] = $this->dof->get_string('report__idp_summary__sum_wait_approve_achievements_count', 'achievements');
        $this->style_cells['0_11'] = $headercellstyle;
        // Количество подтвержденных достижений
        $row[] = $this->dof->get_string('report__idp_summary__achievements_count', 'achievements');
        $this->style_cells['0_12'] = $headercellstyle;
        // Количество подтвержденных достижений с учетом дочерних подразделений
        $row[] = $this->dof->get_string('report__idp_summary__sum_achievements_count', 'achievements');
        $this->style_cells['0_13'] = $headercellstyle;
        // Из них целями были
        $row[] = $this->dof->get_string('report__idp_summary__achieved_goals_count', 'achievements');
        $this->style_cells['0_14'] = $headercellstyle;
        // Из них целями были с учетом дочерних подразделений
        $row[] = $this->dof->get_string('report__idp_summary__sum_achieved_goals_count', 'achievements');
        $this->style_cells['0_15'] = $headercellstyle;
        // Доля пользователей без просроченных дедлайнов
        $row[] = $this->dof->get_string('report__idp_summary__executive_persons_percent', 'achievements');
        $this->style_cells['0_16'] = $headercellstyle;
        // Доля пользователей без просроченных дедлайнов с учетом дочерних подразделений
        $row[] = $this->dof->get_string('report__idp_summary__executive_persons_percent', 'achievements');
        $this->style_cells['0_17'] = $headercellstyle;
                
        $result[] = $row;
        
        // Возвращение данных
        return $result;
    }
    
    /**
     * Получение заголовков
     *
     * @see dof_modlib_rtreport_base::get_headers()
     */
    protected function get_headers()
    {
        $headers = [
            [
                // Наименование подразделения
                $this->dof->get_string('report__idp_summary__department_name', 'achievements'),
                // Код подразделения
                $this->dof->get_string('report__idp_summary__department_code', 'achievements'),
                // Количество пользователей
                $this->dof->get_string('report__idp_summary__persons_count', 'achievements'),
                // Количество целей, ожидающих одобрения
                $this->dof->get_string('report__idp_summary__wait_approve_goals_count', 'achievements'),
                // Количество одобренных целей
                $this->dof->get_string('report__idp_summary__approved_goals_count', 'achievements'),
                // Количество просроченных дедлайнов
                $this->dof->get_string('report__idp_summary__expired_deadlines_count', 'achievements'),
                // Количество достижений, ожидающих подтверждения
                $this->dof->get_string('report__idp_summary__wait_approve_achievements_count', 'achievements'),
                // Количество подтвержденных достижений
                $this->dof->get_string('report__idp_summary__achievements_count', 'achievements'),
                // Из них целями были
                $this->dof->get_string('report__idp_summary__achieved_goals_count', 'achievements'),
                // Доля пользователей без просроченных дедлайнов
                $this->dof->get_string('report__idp_summary__executive_persons_percent', 'achievements')
            ]
        ];
        foreach($headers as $headerrownum => $headerrow)
        {
            foreach($headerrow as $headercellnum=>$headercell)
            {
                $th = new html_table_cell($headercell);
                $th->header = true;
                $headers[$headerrownum][$headercellnum] = $th;
            }
        }
        return $headers;
    }
    
    /**
     * Получение строк
     *
     * @see dof_modlib_rtreport_base::get_rows()
     */
    protected function get_rows()
    {
        // Результирующий массив строк
        $result = [];
        
        if ( empty($this->data['result']) )
        {
            return $result;
        }
        
        $laststudent = null;
        foreach($this->data['result']->rows as $rowdata)
        {
            // новая строка
            $row = new html_table_row();
            $row->attributes['class'] = 'department';
            $row->attributes['data-department-level'] = $rowdata->department->depth;
            
            // Наименование подразделения
            $depname = new html_table_cell();
            if ( $this->data['input']->departmentid != $rowdata->department->id
                 && $this->dof->im('achievements')->is_access('view:rtreport/idp_personalized', null, null, $rowdata->department->id) )
            {// есть право просмотра отчета - выводим ссылку
                $depname->text = dof_html_writer::link(
                    $this->dof->url_im(
                        'achievements',
                        '/reports.php',
                        [
                            'departmentid' => $rowdata->department->id,
                            'rtroptions' => json_encode([
                                'pt' => 'im',
                                'pc' => 'achievements',
                                'report_name' => $this->dof->get_string('report__idp_departments_summary', 'achievements'),
                                'type' => 'idp_summary',
                                'displaysubdepartments' => true
                            ])
                        ]
                    ),
                    $rowdata->department->name
                );
            } else
            {
                $depname->text = $rowdata->department->name;
            }
            $depname->attributes['class'] = 'header-cell department-name';
            $row->cells[] = $depname;
            
            // Код подразделения
            $depcode = new html_table_cell();
            $depcode->attributes['class'] = 'body-cell';
            $depcode->text = $rowdata->department->code;
            $row->cells[] = $depcode;
            
            // Количество пользователей
            $percount = new html_table_cell();
            
            if ( $this->dof->im('achievements')->is_access('view:rtreport/idp_personalized', null, null, $rowdata->department->id) )
            {// есть право просмотра отчета - выводим ссылку
                $percount->text = dof_html_writer::link(
                    $this->dof->url_im(
                        'achievements',
                        '/reports.php',
                        [
                            'departmentid' => $rowdata->department->id,
                            'rtroptions' => json_encode([
                                'pt' => 'im',
                                'pc' => 'achievements',
                                'report_name' => $this->dof->get_string('report__idp_department_personalized', 'achievements'),
                                'type' => 'idp_personalized',
                                'displaysubdepartments' => false
                            ])
                        ]
                    ),
                    $rowdata->persons->count
                );
                $percount->text .= ' / ' . dof_html_writer::link(
                    $this->dof->url_im(
                        'achievements',
                        '/reports.php',
                        [
                            'departmentid' => $rowdata->department->id,
                            'rtroptions' => json_encode([
                                'pt' => 'im',
                                'pc' => 'achievements',
                                'report_name' => $this->dof->get_string('report__idp_departments_personalized', 'achievements'),
                                'type' => 'idp_personalized',
                                'displaysubdepartments' => true
                            ])
                        ]
                    ),
                    $rowdata->sumpersons->count
                ); 
            } else
            {// нет права просмотра отчета - выводим только показатель
                $percount->text = $rowdata->persons->count;
                $percount->text .= ' / ' . $rowdata->sumpersons->count;
            }
            $percount->attributes['class'] = 'body-cell persons-count';
            $row->cells[] = $percount;
            
            // Количество целей, ожидающих одобрения
            $waitapprovegoals = new html_table_cell();
            if( $rowdata->persons->count == 0 )
            {
                $waitapprovegoals->text = '&mdash;';
            } else
            {
                $waitapprovegoals->text = $rowdata->stat->wait_approve_goals;
            }
            if( $rowdata->sumpersons->count == 0 )
            {
                $waitapprovegoals->text .= ' / &mdash;';
            } else
            {
                $waitapprovegoals->text .= ' / ' . $rowdata->sumstat->wait_approve_goals;
            }
            $waitapprovegoals->attributes['class'] = 'body-cell wait-approve-goals';
            $row->cells[] = $waitapprovegoals;
            
            // Количество одобренных целей
            $approvedgoals = new html_table_cell();
            if( $rowdata->persons->count == 0 )
            {
                $approvedgoals->text = '&mdash;';
            } else
            {
                $approvedgoals->text = $rowdata->stat->approved_goals;
            }
            if( $rowdata->sumpersons->count == 0 )
            {
                $approvedgoals->text .= ' / &mdash;';
            } else
            {
                $approvedgoals->text .= ' / ' . $rowdata->sumstat->approved_goals;
            }
            $approvedgoals->attributes['class'] = 'body-cell approved-goals';
            $row->cells[] = $approvedgoals;
            
            // Количество просроченных дедлайнов
            $expireddeadlines = new html_table_cell();
            if( $rowdata->persons->count == 0 )
            {
                $expireddeadlines->text = '&mdash;';
            } else
            {
                $expireddeadlines->text = $rowdata->stat->expired_deadlines;
            }
            if( $rowdata->sumpersons->count == 0 )
            {
                $expireddeadlines->text .= ' / &mdash;';
            } else
            {
                $expireddeadlines->text .= ' / ' . $rowdata->sumstat->expired_deadlines;
            }
            $expireddeadlines->attributes['class'] = 'body-cell expired-deadlines';
            $row->cells[] = $expireddeadlines;
            
            // Количество достижений, ожидающих подтверждения
            $waitapproveachievements = new html_table_cell();
            if( $rowdata->persons->count == 0 )
            {
                $waitapproveachievements->text = '&mdash;';
            } else
            {
                $waitapproveachievements->text = $rowdata->stat->wait_approve_achievements;
            }
            if( $rowdata->sumpersons->count == 0 )
            {
                $waitapproveachievements->text .= ' / &mdash;';
            } else
            {
                $waitapproveachievements->text .= ' / ' . $rowdata->sumstat->wait_approve_achievements;
            }
            $waitapproveachievements->attributes['class'] = 'body-cell wait-approve-achievements';
            $row->cells[] = $waitapproveachievements;
            
            // Количество подтвержденных достижений
            $achievements = new html_table_cell();
            if( $rowdata->persons->count == 0 )
            {
                $achievements->text = '&mdash;';
            } else
            {
                $achievements->text = $rowdata->stat->achievements;
            }
            if( $rowdata->sumpersons->count == 0 )
            {
                $achievements->text .= ' / &mdash;';
            } else
            {
                $achievements->text .= ' / ' . $rowdata->sumstat->achievements;
            }
            $achievements->attributes['class'] = 'body-cell achievements';
            $row->cells[] = $achievements;
            
            // Из них целями были
            $achievedgoals = new html_table_cell();
            if( $rowdata->persons->count == 0 )
            {
                $achievedgoals->text = '&mdash;';
            } else
            {
                $achievedgoals->text = $rowdata->stat->achieved_goals;
            }
            if( $rowdata->sumpersons->count == 0 )
            {
                $achievedgoals->text .= ' / &mdash;';
            } else
            {
                $achievedgoals->text .= ' / ' . $rowdata->sumstat->achieved_goals;
            }
            $achievedgoals->attributes['class'] = 'body-cell achieved-goals';
            $row->cells[] = $achievedgoals;
            
            // Доля пользователей без просроченных дедлайнов
            $executivepersons = new html_table_cell();
            if( $rowdata->persons->count == 0 )
            {
                $executivepersons->text = '&mdash;';
            } else
            {
                $executivepersons->text = round(
                    ($rowdata->persons->count - $rowdata->stat->expired_deadlines_users) * 100 / $rowdata->persons->count, 
                    2, 
                    PHP_ROUND_HALF_UP
                );
            }
            if( $rowdata->sumpersons->count == 0 )
            {
                $executivepersons->text .= ' / &mdash;';
            } else
            {
                $executivepersons->text .= ' / ' . round(
                    ($rowdata->sumpersons->count - $rowdata->sumstat->expired_deadlines_users) * 100 / $rowdata->sumpersons->count,
                    2,
                    PHP_ROUND_HALF_UP
                );
            }
            $executivepersons->attributes['class'] = 'body-cell executive-persons';
            $row->cells[] = $executivepersons;
            
            array_push($result, $row);
        }
        
        // Возвращение данных
        return $result;
    }
    
    /**
     * Получение строк
     *
     * @see dof_modlib_rtreport_base::get_rows()
     */
    protected function get_rows_values()
    {
        // Результирующий массив строк
        $result = [];
        
        if ( empty($this->data['result']) )
        {
            return $result;
        }
        $rownum = 0;
        $firstcellstyle = ['bold' => 600, 'border' => 1, 'v_align' => 'top', 'h_align' => 'left'];
        $cellstyle = ['border' => 1, 'v_align' => 'center', 'h_align' => 'center'];
        foreach($this->data['result']->rows as $rowdata)
        {
            // новая строка
            $row = [];
            $rownum++;
            
            // Наименование подразделения
            $deplevel = str_repeat('—', $rowdata->department->depth);
            $depname = $rowdata->department->name;
            $row[] = $deplevel . ' ' . $depname;
            $this->style_cells[$rownum.'_0'] = $firstcellstyle;
            
            // Код подразделения
            $row[] = $rowdata->department->code;
            $this->style_cells[$rownum.'_1'] = $cellstyle;
            
            // Количество пользователей
            $percount = $rowdata->persons->count;
            $row[] = $percount;
            $this->style_cells[$rownum.'_2'] = $cellstyle;
            
            // Количество пользователей с учетом дочерних подразделений
            $sumpercount = $rowdata->sumpersons->count;
            $row[] = $sumpercount;
            $this->style_cells[$rownum.'_3'] = $cellstyle;
            
            // Количество целей, ожидающих одобрения
            if( $rowdata->persons->count == 0 )
            {
                $waitapprovegoals = '—';
            } else
            {
                $waitapprovegoals = $rowdata->stat->wait_approve_goals;
            }
            $row[] = $waitapprovegoals;
            $this->style_cells[$rownum.'_4'] = $cellstyle;
            
            // Количество целей, ожидающих одобрения, с учетом дочерних подразделений
            if( $rowdata->sumpersons->count == 0 )
            {
                $sumwaitapprovegoals = '—';
            } else
            {
                $sumwaitapprovegoals = $rowdata->sumstat->wait_approve_goals;
            }
            $row[] = $sumwaitapprovegoals;
            $this->style_cells[$rownum.'_5'] = $cellstyle;
            
            // Количество одобренных целей
            if( $rowdata->persons->count == 0 )
            {
                $approvedgoals = '—';
            } else
            {
                $approvedgoals = $rowdata->stat->approved_goals;
            }
            $row[] = $approvedgoals;
            $this->style_cells[$rownum.'_6'] = $cellstyle;
            
            // Количество одобренных целей, с учетом дочерних подразделений
            if( $rowdata->sumpersons->count == 0 )
            {
                $sumapprovedgoals = '—';
            } else
            {
                $sumapprovedgoals = $rowdata->sumstat->approved_goals;
            }
            $row[] = $sumapprovedgoals;
            $this->style_cells[$rownum.'_7'] = $cellstyle;
            
            // Количество просроченных дедлайнов
            if( $rowdata->persons->count == 0 )
            {
                $expireddeadlines = '—';
            } else
            {
                $expireddeadlines = $rowdata->stat->expired_deadlines;
            }
            $row[] = $expireddeadlines;
            $this->style_cells[$rownum.'_8'] = $cellstyle;
            
            // Количество просроченных дедлайнов с учетом дочерних подразделений
            if( $rowdata->sumpersons->count == 0 )
            {
                $sumexpireddeadlines = '—';
            } else
            {
                $sumexpireddeadlines = $rowdata->sumstat->expired_deadlines;
            }
            $row[] = $sumexpireddeadlines;
            $this->style_cells[$rownum.'_9'] = $cellstyle;
            
            // Количество достижений, ожидающих подтверждения
            if( $rowdata->persons->count == 0 )
            {
                $waitapproveachievements = '—';
            } else
            {
                $waitapproveachievements = $rowdata->stat->wait_approve_achievements;
            }
            $row[] = $waitapproveachievements;
            $this->style_cells[$rownum.'_10'] = $cellstyle;
            
            // Количество достижений, ожидающих подтверждения с учетом дочерних подразделений
            if( $rowdata->sumpersons->count == 0 )
            {
                $sumwaitapproveachievements = '—';
            } else
            {
                $sumwaitapproveachievements = $rowdata->sumstat->wait_approve_achievements;
            }
            $row[] = $sumwaitapproveachievements;
            $this->style_cells[$rownum.'_11'] = $cellstyle;
            
            // Количество подтвержденных достижений
            if( $rowdata->persons->count == 0 )
            {
                $achievements = '—';
            } else
            {
                $achievements = $rowdata->stat->achievements;
            }
            $row[] = $achievements;
            $this->style_cells[$rownum.'_12'] = $cellstyle;
            
            // Количество подтвержденных достижений с учетом дочерних подразделений
            if( $rowdata->sumpersons->count == 0 )
            {
                $sumachievements = '—';
            } else
            {
                $sumachievements = $rowdata->sumstat->achievements;
            }
            $row[] = $sumachievements;
            $this->style_cells[$rownum.'_13'] = $cellstyle;
            
            // Из них целями были
            if( $rowdata->persons->count == 0 )
            {
                $achievedgoals = '—';
            } else
            {
                $achievedgoals = $rowdata->stat->achieved_goals;
            }
            $row[] = $achievedgoals;
            $this->style_cells[$rownum.'_14'] = $cellstyle;
            
            // Из них целями были с учетом дочерних подразделений
            if( $rowdata->sumpersons->count == 0 )
            {
                $sumachievedgoals = '—';
            } else
            {
                $sumachievedgoals = $rowdata->sumstat->achieved_goals;
            }
            $row[] = $sumachievedgoals;
            $this->style_cells[$rownum.'_15'] = $cellstyle;
            
            // Доля пользователей без просроченных дедлайнов
            if( $rowdata->persons->count == 0 )
            {
                $executivepersons = '—';
            } else
            {
                $executivepersons = round(
                    ($rowdata->persons->count - $rowdata->stat->expired_deadlines_users) * 100 / $rowdata->persons->count,
                    2,
                    PHP_ROUND_HALF_UP
                    );
            }
            $row[] = $executivepersons;
            $this->style_cells[$rownum.'_16'] = $cellstyle;
            
            // Доля пользователей без просроченных дедлайнов
            if( $rowdata->sumpersons->count == 0 )
            {
                $sumexecutivepersons = '—';
            } else
            {
                $sumexecutivepersons = round(
                    ($rowdata->sumpersons->count - $rowdata->sumstat->expired_deadlines_users) * 100 / $rowdata->sumpersons->count,
                    2,
                    PHP_ROUND_HALF_UP
                );
            }
            $row[] = $sumexecutivepersons;
            $this->style_cells[$rownum.'_17'] = $cellstyle;
            
            array_push($result, $row);
        }
        
        // Возвращение данных
        return $result;
    }
    
    
    /**
     * Массив ячеек для объединения
     *
     * @see dof_modlib_rtreport_base::merge_cells()
     */
    protected function merge_cells()
    {
        $merge_cells = [];
        
        if ( empty($this->data['result']) )
        {
            return $merge_cells;
        }
        
        return $merge_cells;
    }
    
    /**
     * Установка данных
     *
     * @see dof_modlib_rtreport_base::set_variables()
     */
    protected function set_variables()
    {
        $result = [];
        
        if ( is_null($this->get_exporter()) )
        {
            $accessallowed = $this->dof->im('achievements')->is_access('view:rtreport/idp_summary');
        } else
        {
            $accessallowed = $this->dof->im('achievements')->is_access('export:rtreport/idp_summary');
        }
        
        if( $accessallowed )
        {
            $achievementsreportsman = $this->dof->modlib('achievements')->get_manager('reports');
            if( ! empty($achievementsreportsman) )
            {
                $this->data['result'] = $achievementsreportsman->get_idp_summary_data(
                    $this->data['input']->departmentid,
                    $this->data['input']->displaysubdepartments
                );
            }
        }
    }
    
    /**
     * Массив ячеек, которые необходимо дополнительно стилизовать
     *
     * @example Пример [ '3_9' => ['bg_color' => 'red] ]
     *
     * @return array
     */
    protected function style_cells()
    {
        return $this->style_cells;
    }
    
    public function get_header()
    {
        $result = '';
        
        if ( is_null($this->get_exporter()) )
        {
            $accessallowed = $this->dof->im('achievements')->is_access('view:rtreport/idp_summary');
        } else
        {
            $accessallowed = $this->dof->im('achievements')->is_access('export:rtreport/idp_summary');
        }
        
        if( ! $accessallowed )
        {
            $result = $this->dof->get_string(
                (is_null($this->get_exporter()) ? 'view' : 'export') . ':rtreport/idp_summary_denied',
                'achievements'
            );
        }
        
        return $result;
    }
}

