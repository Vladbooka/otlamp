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
 * Отчет по индивидуальным планам развития "Персонализированная статистика по подразделению".
 *
 * @package    im
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_im_achievements_rtreport_idp_personalized extends dof_modlib_rtreport_base
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
        return 'idp_personalized';
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
        
        $headercellstyles = ['bold' => '600', 'border' => '1', 'v_align' => 'center', 'h_align' => 'center', 'text_wrap' => '1'];
        
        // ФИО
        $row[] = $this->dof->get_string('report__idp_personalized__person_fullname', 'achievements');
        $this->style_cells['0_0'] = $headercellstyles;
        // Подразделения пользователя
        $row[] = $this->dof->get_string('report__idp_personalized__departments', 'achievements');
        $this->style_cells['0_1'] = $headercellstyles;
        // Код подразделения
        $row[] = $this->dof->get_string('report__idp_personalized__departments_code', 'achievements');
        $this->style_cells['0_2'] = $headercellstyles;
        // Количество целей, ожидающих одобрения
        $row[] = $this->dof->get_string('report__idp_personalized__wait_approve_goals_count', 'achievements');
        $this->style_cells['0_3'] = $headercellstyles;
        // Количество одобренных целей
        $row[] = $this->dof->get_string('report__idp_personalized__approved_goals_count', 'achievements');
        $this->style_cells['0_4'] = $headercellstyles;
        // Количество просроченных дедлайнов
        $row[] = $this->dof->get_string('report__idp_personalized__expired_deadlines_count', 'achievements');
        $this->style_cells['0_5'] = $headercellstyles;
        // Количество достижений, ожидающих подтверждения
        $row[] = $this->dof->get_string('report__idp_personalized__wait_approve_achievements_count', 'achievements');
        $this->style_cells['0_6'] = $headercellstyles;
        // Количество подтвержденных достижений
        $row[] = $this->dof->get_string('report__idp_personalized__achievements_count', 'achievements');
        $this->style_cells['0_7'] = $headercellstyles;
        // Из них целями были
        $row[] = $this->dof->get_string('report__idp_personalized__achieved_goals_count', 'achievements');
        $this->style_cells['0_8'] = $headercellstyles;
                
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
        $headers = $this->get_headers_values();
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
            $row->attributes['class'] = 'person';
            $row->attributes['data-person-id'] = $rowdata->person->id;
            
            // ФИО пользователя
            $fullname = new html_table_cell();
            $fullname->text = $rowdata->person->fullname;
            $fullname->attributes['class'] = 'header-cell person-fullname';
            $row->cells[] = $fullname;
            
            // Подразделения пользователя
            $deps = new html_table_cell();
            $deps->text = $rowdata->person->departments;
            $deps->attributes['class'] = 'body-cell person-departments';
            $row->cells[] = $deps;
            
            // Код подразделения
            $deps = new html_table_cell();
            $deps->text = $rowdata->person->departmentscode;
            $deps->attributes['class'] = 'body-cell';
            $row->cells[] = $deps;
            
            // Количество целей, ожидающих одобрения
            $waitapprovegoals = new html_table_cell();
            $waitapprovegoals->text = $rowdata->stat->wait_approve_goals;
            $waitapprovegoals->attributes['class'] = 'body-cell wait-approve-goals';
            $row->cells[] = $waitapprovegoals;
            
            // Количество одобренных целей
            $approvedgoals = new html_table_cell();
            $approvedgoals->text = $rowdata->stat->approved_goals;
            $approvedgoals->attributes['class'] = 'body-cell approved-goals';
            $row->cells[] = $approvedgoals;
            
            // Количество просроченных дедлайнов
            $expireddeadlines = new html_table_cell();
            $expireddeadlines->text = $rowdata->stat->expired_deadlines;
            $expireddeadlines->attributes['class'] = 'body-cell expired-deadlines';
            $row->cells[] = $expireddeadlines;
            
            // Количество достижений, ожидающих подтверждения
            $waitapproveachievements = new html_table_cell();
            $waitapproveachievements->text = $rowdata->stat->wait_approve_achievements;
            $waitapproveachievements->attributes['class'] = 'body-cell wait-approve-achievements';
            $row->cells[] = $waitapproveachievements;
            
            // Количество подтвержденных достижений
            $achievements = new html_table_cell();
            $achievements->text = $rowdata->stat->achievements;
            $achievements->attributes['class'] = 'body-cell achievements';
            $row->cells[] = $achievements;
            
            // Из них целями были
            $achievedgoals = new html_table_cell();
            $achievedgoals->text = $rowdata->stat->achieved_goals;
            $achievedgoals->attributes['class'] = 'body-cell achieved-goals';
            $row->cells[] = $achievedgoals;
            
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
        $firstcellstyle = ['bold' => 600, 'border' => 1, 'v_align' => 'top', 'h_align' => 'left'];
        $cellstyle = ['bold' => 600, 'border' => 1, 'v_align' => 'top', 'h_align' => 'left'];
        $rownum = 0;
        foreach($this->data['result']->rows as $rowdata)
        {
            // новая строка
            $row = [];
            $rownum++;
            
            // ФИО пользователя
            $fullname = $rowdata->person->fullname;
            $row[] = $fullname;
            $this->style_cells[$rownum.'_0'] = $firstcellstyle;
            
            // Подразделения пользователя
            $deps = $rowdata->person->departments;
            $row[] = $deps;
            $this->style_cells[$rownum.'_1'] = $cellstyle;
            
            // Код пдразделения
            $deps = $rowdata->person->departmentscode;
            $row[] = $deps;
            $this->style_cells[$rownum.'_2'] = $cellstyle;
            
            // Количество целей, ожидающих одобрения
            $waitapprovegoals = $rowdata->stat->wait_approve_goals;
            $row[] = $waitapprovegoals;
            $this->style_cells[$rownum.'_3'] = $cellstyle;
            
            // Количество одобренных целей
            $approvedgoals = $rowdata->stat->approved_goals;
            $row[] = $approvedgoals;
            $this->style_cells[$rownum.'_4'] = $cellstyle;
            
            // Количество просроченных дедлайнов
            $expireddeadlines = $rowdata->stat->expired_deadlines;
            $row[] = $expireddeadlines;
            $this->style_cells[$rownum.'_5'] = $cellstyle;
            
            // Количество достижений, ожидающих подтверждения
            $waitapproveachievements = $rowdata->stat->wait_approve_achievements;
            $row[] = $waitapproveachievements;
            $this->style_cells[$rownum.'_6'] = $cellstyle;
            
            // Количество подтвержденных достижений
            $achievements = $rowdata->stat->achievements;
            $row[] = $achievements;
            $this->style_cells[$rownum.'_7'] = $cellstyle;
            
            // Из них целями были
            $achievedgoals = $rowdata->stat->achieved_goals;
            $row[] = $achievedgoals;
            $this->style_cells[$rownum.'_8'] = $cellstyle;
            
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
            $accessallowed = $this->dof->im('achievements')->is_access('view:rtreport/idp_personalized');
        } else
        {
            $accessallowed = $this->dof->im('achievements')->is_access('export:rtreport/idp_personalized');
        }
        
        if( $accessallowed )
        {
            $achievementsreportsman = $this->dof->modlib('achievements')->get_manager('reports');
            if( ! empty($achievementsreportsman) )
            {
                $this->data['result'] = $achievementsreportsman->get_idp_personalized_data(
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
            $accessallowed = $this->dof->im('achievements')->is_access('view:rtreport/idp_personalized');
        } else
        {
            $accessallowed = $this->dof->im('achievements')->is_access('export:rtreport/idp_personalized');
        }
        
        if( ! $accessallowed )
        {
            $result = $this->dof->get_string(
                (is_null($this->get_exporter()) ? 'view' : 'export') . ':rtreport/idp_personalized_denied',
                'achievements'
            );
        }
        
        return $result;
    }
}

