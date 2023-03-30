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
 * Отчет. Сводная ведомость текущих оценок и пропущенных уроков по параллели
 * 
 * @package    im
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_im_journal_rtreport_personsplans_summary extends dof_modlib_rtreport_base
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
        return 'personsplans_summary';
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
        
        // Строка заголовков
        $first = [];
        
        // Строка заголовков
        $interval_string = date('d.m.Y', $this->data['filter']->datefrom) . ' - ' .  date('d.m.Y', $this->data['filter']->todate);
        
        // Временной интервал
        $result[] = [$this->dof->get_string('rtreport_personsplans_summary_interval', 'journal', $interval_string)];
        
        // Учебный период
        $result[] = [$this->dof->get_string('rtreport_personsplans_summary_agename', 'journal', $this->data['filter']->agename)];
        
        // Программа
        $result[] = [$this->dof->get_string('rtreport_personsplans_summary_programmname', 'journal', $this->data['filter']->programmname)];
        
        // Параллель
        $result[] = [$this->dof->get_string('rtreport_personsplans_summary_parallel', 'journal', $this->data['filter']->parallel)];
        
        // ФИО учащихся
        $first[] = $this->dof->get_string('rtreport_personsplans_summary_fio', 'journal');
        
        // Кол-во уроков
        $first[] = $this->dof->get_string('rtreport_personsplans_summary_lessons_all', 'journal');
        
        // Пропущенных уроков - количество
        $first[] = $this->dof->get_string('rtreport_personsplans_summary_lessonsmissed', 'journal');
        
        // Пропущенных уроков в процентах
        $first[] = $this->dof->get_string('rtreport_personsplans_summary_lessonsmissed_percent', 'journal') . ' (%)';
        
        $this->style_cells['0_0'] = $this->style_cells['1_0'] = $this->style_cells['2_0'] = $this->style_cells['3_0'] = ['border' => 1, 'v_align' => 'center', 'h_align' => 'left'];
        
        if ( ! empty($this->data['result']->disciplines) )
        {
            $second = $third = ['','','',''];
            
            // Строка информации над названиями предметов
            $first[] = $this->dof->get_string('rtreport_personsplans_summary_infoheader', 'journal');;
            
            foreach ( $this->data['result']->disciplines as $disciplineid => $info )
            {
                // Название дисциплины
                $second[] = $info->name;
                array_push($second, '', '', '');
            }
            
            foreach ( $this->data['result']->disciplines as $disciplineid => $info )
            {
                // Текущие оценки дисциплины
                $third[] = $this->dof->get_string('rtreport_personsplans_summary_disc_currentgrades', 'journal');
                $third[] = $this->dof->get_string('rtreport_personsplans_summary_disc_average_student_grade', 'journal');;
                $third[] = $this->dof->get_string('rtreport_personsplans_summary_disc_average_grade', 'journal');;
                $third[] = $this->dof->get_string('rtreport_personsplans_summary_disc_quality', 'journal');;
            }
            
            
            array_push($result, $first, $second, $third);
        }
        
        return $result;
    }
    
    /**
     * Получение заголовков
     *
     * @see dof_modlib_rtreport_base::get_headers()
     */
    protected function get_headers()
    {
        $result = [];
        
        if ( empty($this->data['result']) )
        {
            return $result;
        }
        
        $countdisciplines = count($this->data['result']->disciplines);
        $colspan = 4 + $countdisciplines*4;
        
        // Строка заголовков
        $first = new html_table_row();
        
        $interval_string = date('d.m.Y', $this->data['filter']->datefrom) . ' - ' .  date('d.m.Y', $this->data['filter']->todate);
        
        // Временной интервал
        $interval = new html_table_cell();
        $interval->colspan = $colspan;
        $interval->text = $this->dof->get_string('rtreport_personsplans_summary_interval', 'journal', $interval_string);
        $interval->style = 'text-align:left;border:1px solid black;';
        $int = new html_table_row();
        $int->cells[] = $interval;
        $result[] = $int;
        
        // Учебный период
        $uchperiod = new html_table_cell();
        $uchperiod->colspan = $colspan;
        $uchperiod->text = $this->dof->get_string('rtreport_personsplans_summary_agename', 'journal', $this->data['filter']->agename);
        $uchperiod->style = 'text-align:left;border:1px solid black;';
        $per = new html_table_row();
        $per->cells[] = $uchperiod;
        $result[] = $per;
        
        // Программа
        $programm = new html_table_cell();
        $programm->colspan = $colspan;
        $programm->text = $this->dof->get_string('rtreport_personsplans_summary_programmname', 'journal', $this->data['filter']->programmname);
        $programm->style = 'text-align:left;border:1px solid black;';
        $per = new html_table_row();
        $per->cells[] = $programm;
        $result[] = $per;
        
        // Параллель
        $par = new html_table_cell();
        $par->colspan = $colspan;
        $par->text = $this->dof->get_string('rtreport_personsplans_summary_parallel', 'journal', $this->data['filter']->parallel);
        $par->style = 'text-align:left;border:1px solid black;';
        $per = new html_table_row();
        $per->cells[] = $par;
        $result[] = $per;
        
        // ФИО учащихся
        $fio = new html_table_cell();
        $fio->rowspan = 4;
        $fio->text = $this->dof->get_string('rtreport_personsplans_summary_fio', 'journal');
        $fio->style = 'text-align:center;border:1px solid black;';
        $first->cells[] = $fio;
        
        // Кол-во уроков
        $alllessons = new html_table_cell();
        $alllessons->rowspan = 4;
        $alllessons->text = $this->dof->get_string('rtreport_personsplans_summary_lessons_all', 'journal');
        $alllessons->style = 'text-align:center;border:1px solid black;';
        $first->cells[] = $alllessons;
        
        // Пропущенных уроков - количество
        $missedlessons = new html_table_cell();
        $missedlessons->rowspan = 4;
        $missedlessons->text = $this->dof->get_string('rtreport_personsplans_summary_lessonsmissed', 'journal');
        $missedlessons->style = 'text-align:center;border:1px solid black;';
        $first->cells[] = $missedlessons;
        
        // Пропущенных уроков в процентах
        $missedlessonspercent = new html_table_cell();
        $missedlessonspercent->rowspan = 4;
        $missedlessonspercent->text = $this->dof->get_string('rtreport_personsplans_summary_lessonsmissed_percent', 'journal') . ' (%)';
        $missedlessonspercent->style = 'text-align:center;border:1px solid black;';
        $first->cells[] = $missedlessonspercent;
        
        $result[] = $first;
        
        if ( ! empty($this->data['result']->disciplines) )
        {
            $second = new html_table_row();
            $third = new html_table_row();
            $fourth = new html_table_row();
            
            // Строка информации над названиями предметов
            $infoheader = new html_table_cell();
            $infoheader->rowspan = 1;
            $infoheader->colspan = count($this->data['result']->disciplines) * 4;
            $infoheader->text = $this->dof->get_string('rtreport_personsplans_summary_infoheader', 'journal');
            $infoheader->style = 'text-align:center;border:1px solid black;';
            $second->cells[] = $infoheader;
            foreach ( $this->data['result']->disciplines as $disciplineid => $info )
            {
                // Название дисциплины
                $discipline = new html_table_cell();
                $discipline->colspan = 4;
                $discipline->rowspan = 1;
                $discipline->text = $info->name;
                $discipline->style = 'text-align:center;border:1px solid black;';
                $third->cells[] = $discipline;
            }
            
            foreach ( $this->data['result']->disciplines as $disciplineid => $info )
            {
                // Текущие оценки дисциплины
                $disciplinecell = new html_table_cell();
                $disciplinecell->colspan = 1;
                $disciplinecell->rowspan = 1;
                $disciplinecell->text = $this->dof->get_string('rtreport_personsplans_summary_disc_currentgrades', 'journal');
                $disciplinecell->style = 'text-align:center;border:1px solid black;';
                $fourth->cells[] = $disciplinecell;
                
                $disciplinecell = new html_table_cell();
                $disciplinecell->colspan = 1;
                $disciplinecell->rowspan = 1;
                $disciplinecell->text = $this->dof->get_string('rtreport_personsplans_summary_disc_average_student_grade', 'journal');
                $disciplinecell->style = 'text-align:center;border:1px solid black;';
                $fourth->cells[] = $disciplinecell;
                
                $disciplinecell = new html_table_cell();
                $disciplinecell->colspan = 1;
                $disciplinecell->rowspan = 1;
                $disciplinecell->text = $this->dof->get_string('rtreport_personsplans_summary_disc_average_grade', 'journal');
                $disciplinecell->style = 'text-align:center;border:1px solid black;';
                $fourth->cells[] = $disciplinecell;
                
                $disciplinecell = new html_table_cell();
                $disciplinecell->colspan = 1;
                $disciplinecell->rowspan = 1;
                $disciplinecell->text = $this->dof->get_string('rtreport_personsplans_summary_disc_quality', 'journal');
                $disciplinecell->style = 'text-align:center;border:1px solid black;';
                $fourth->cells[] = $disciplinecell;
            }
            
            
            array_push($result, $second, $third, $fourth);
        }
        
        return $result;
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
        if ( ! empty($this->data['result']->students) )
        {
            $processed_disciplines = [];
            foreach ( $this->data['result']->students as $userid => $info )
            {
                $row = new html_table_row();
                $row->style = 'text-align:center;';
                
                $userinfo = new html_table_cell();
                $userinfo->colspan = 1;
                $userinfo->rowspan = 1;
                $userinfo->text = $info->name;
                $userinfo->style = 'text-align:center;border:1px solid black;';
                $row->cells[] = $userinfo;
                
                $userinfo = new html_table_cell();
                $userinfo->colspan = 1;
                $userinfo->rowspan = 1;
                $userinfo->text = $info->lessonsnumber;
                $userinfo->style = 'text-align:center;border:1px solid black;';
                $row->cells[] = $userinfo;
                
                $userinfo = new html_table_cell();
                $userinfo->colspan = 1;
                $userinfo->rowspan = 1;
                $userinfo->text = $info->missedlessons;
                $userinfo->style = 'text-align:center;border:1px solid black;';
                $row->cells[] = $userinfo;
                
                $userinfo = new html_table_cell();
                $userinfo->colspan = 1;
                $userinfo->rowspan = 1;
                $userinfo->text = $info->missedpercent . '%';
                $userinfo->style = 'text-align:center;border:1px solid black;';
                $row->cells[] = $userinfo;
                
                // Заполнение данными дисциплины
                foreach ( $this->data['result']->disciplines as $disciplineid => $info )
                {
                    $first = true;
                    if ( ! empty($this->data['result']->disciplines[$disciplineid]->usersinfo[$userid]) )
                    {
                        $disciplinecell = new html_table_cell();
                        $disciplinecell->colspan = 1;
                        $disciplinecell->rowspan = 1;
                        $disciplinecell->text = implode('', $this->data['result']->disciplines[$disciplineid]->usersinfo[$userid]->grades);
                        $disciplinecell->style = 'text-align:center;border:1px solid black;';
                        $row->cells[] = $disciplinecell;
                        
                        $disciplinecell = new html_table_cell();
                        $disciplinecell->colspan = 1;
                        $disciplinecell->rowspan = 1;
                        $disciplinecell->text = ( ! empty($this->data['result']->disciplines[$disciplineid]->usersinfo[$userid]->average) ? $this->data['result']->disciplines[$disciplineid]->usersinfo[$userid]->average : '');
                        $disciplinecell->style = 'text-align:center;border:1px solid black;';
                        $row->cells[] = $disciplinecell;
                    } else 
                    {
                        $disciplinecell = new html_table_cell();
                        $disciplinecell->colspan = 1;
                        $disciplinecell->rowspan = 1;
                        $disciplinecell->text = '';
                        $disciplinecell->attributes['class'] = 'personsplans_summary_missed';
                        $disciplinecell->style = 'text-align:center;border:1px solid black;background-color:gray !important;';
                        $row->cells[] = $disciplinecell;
                        
                        $disciplinecell = new html_table_cell();
                        $disciplinecell->colspan = 1;
                        $disciplinecell->rowspan = 1;
                        $disciplinecell->text = '';
                        $disciplinecell->attributes['class'] = 'personsplans_summary_missed';
                        $disciplinecell->style = 'text-align:center;border:1px solid black;background-color:gray !important;';
                        $row->cells[] = $disciplinecell;
                    }
                    
                    if ( ! array_key_exists($disciplineid, $processed_disciplines) )
                    {
                        $processed_disciplines[$disciplineid] = $disciplineid;
                        
                        $disciplinecell = new html_table_cell();
                        $disciplinecell->colspan = 1;
                        $disciplinecell->rowspan = count($this->data['result']->students);
                        $disciplinecell->text = $info->average;
                        $disciplinecell->style = 'text-align:center;border:1px solid black;';
                        $row->cells[] = $disciplinecell;
                        
                        $disciplinecell = new html_table_cell();
                        $disciplinecell->colspan = 1;
                        $disciplinecell->rowspan = count($this->data['result']->students);
                        $disciplinecell->text = $info->quality . '%';
                        $disciplinecell->style = 'text-align:center;border:1px solid black;';
                        $row->cells[] = $disciplinecell;
                    }
                }
                
                array_push($result, $row);
            }
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
        
        if ( ! empty($this->data['result']->students) )
        {
            $processed_disciplines = [];
            $rownum = 7;
            foreach ( $this->data['result']->students as $userid => $info )
            {
                $row = [];
                
                $row[] = $info->name;;
                $row[] = $info->lessonsnumber;
                $row[] = $info->missedlessons;
                $row[] = $info->missedpercent . '%';
                
                $colnum = 4;
                // Заполнение данными дисциплины
                foreach ( $this->data['result']->disciplines as $disciplineid => $info )
                {
                    if ( ! empty($this->data['result']->disciplines[$disciplineid]->usersinfo[$userid]) )
                    {
                        $row[] = implode('', $this->data['result']->disciplines[$disciplineid]->usersinfo[$userid]->grades);;
                        $row[] = ( ! empty($this->data['result']->disciplines[$disciplineid]->usersinfo[$userid]->average) ? $this->data['result']->disciplines[$disciplineid]->usersinfo[$userid]->average : '');
                    } else
                    {
                        $key = $rownum . '_' . $colnum;
                        $this->style_cells[$key] = ['bg_color' => 'gray', 'border' => 1];
                        $key = $rownum . '_' . ( $colnum + 1 );
                        $this->style_cells[$key] = ['bg_color' => 'gray', 'border' => 1];
                        $row[] = '';
                        $row[] = '';
                    }
                    
                    if ( ! array_key_exists($disciplineid, $processed_disciplines) )
                    {
                        $processed_disciplines[$disciplineid] = $disciplineid;
                        
                        $key = $rownum . '_' . ($colnum + 2);
                        $this->style_cells[$key] = ['border' => 1, 'v_align' => 'top', 'h_align' => 'center'];
                        $key = $rownum . '_' . ($colnum + 3);
                        $this->style_cells[$key] = ['border' => 1, 'v_align' => 'top', 'h_align' => 'center'];
                        $row[] = $info->average;
                        $row[] = $info->quality . '%';
                    } else 
                    {
                        $row[] = '';
                        $row[] = '';
                    }
                    $colnum += 4;
                }
                array_push($result, $row);
                $rownum++;
            }
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
        $merge_cells = [
            [4, 0, 6, 0],
            [4, 1, 6, 1],
            [4, 2, 6, 2],
            [4, 3, 6, 3]
        ];
        
        if ( empty($this->data['result']) )
        {
            return $merge_cells;
        }

        $countdisciplines = count($this->data['result']->disciplines);
        $countstudents = count($this->data['result']->students);
        
        if ( ! empty($countdisciplines) )
        {
            $merge_cells[] = [0, 0, 0, $countdisciplines*4 + 3];
            $merge_cells[] = [1, 0, 1, $countdisciplines*4 + 3];
            $merge_cells[] = [2, 0, 2, $countdisciplines*4 + 3];
            $merge_cells[] = [3, 0, 3, $countdisciplines*4 + 3];
        }
        
        if ( $countdisciplines > 0 )
        {
            $merge_cells[] = [4, 4, 4, 3 + 4 * $countdisciplines];
        }
        
        $curpos = 4;
        $curposdisc = 6;
        // Заполнение данными для слияния ячеек
        foreach ( $this->data['result']->disciplines as $disciplineid => $info )
        {
            $merge_cells[] = [5, $curpos, 5, $curpos + 3];
            $curpos += 4;
            
            if ( ! empty($countstudents) )
            {
                $merge_cells[] = [7, $curposdisc, 6 + $countstudents, $curposdisc];
                $merge_cells[] = [7, $curposdisc + 1, 6 + $countstudents, $curposdisc + 1];
                $curposdisc += 4;
            }
        }
        
        return $merge_cells;
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
    
    /**
     * Установка данных
     * 
     * @see dof_modlib_rtreport_base::set_variables()
     */
    protected function set_variables()
    {
        global $addvars;
        
        $result = [];
        
        // Формирование URL формы
        $url = $this->dof->url_im('journal','/personsplans_summary/index.php', $addvars);
        
        // Формирование дополнительных данных
        $customdata = new stdClass;
        $customdata->dof = $this->dof;
        $customdata->addvars = $addvars;
        
        // Форма сохранения подразделения
        $result['form'] = new dof_im_journal_personsplans_summary($url, $customdata);
        $result['form']->process();
        $result['filter'] = $result['form']->get_filter();
        
        // Установка данных
        $this->set_data($result);
        
        // Установка данных
        $this->set_table_data();
    }
    
    /**
     * Получение данных для отчета
     * 
     * @return void
     */
    protected function set_table_data()
    {
        if ( ! empty($this->data['filter']) )
        {
            // Получение объекта результата для отчета
            $this->data['result'] = $this->dof->im('journal')->get_personsplans_summary(
                    $this->data['filter']->programmid, 
                    $this->data['filter']->parallel,
                    $this->data['filter']->ageid, 
                    $this->data['filter']->datefrom, 
                    $this->data['filter']->todate);
            if ( ! empty($this->data['filter']->exporter) )
            {
                $this->set_exporter($this->data['filter']->exporter);
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
        $html = '';
        $html .= dof_html_writer::start_div();
        $html .= dof_html_writer::end_div();
        
        return $this->data['form']->render();
    }
    
    /**
     * Установка навигации
     *
     * @return void
     */
    public function set_nvg()
    {
        $this->dof->modlib('nvg')->add_level(
                $this->dof->get_string('personplans_summary', 'journal'), 
                $this->dof->url_im('journal', '/personsplans_summary/index.php', $this->get_variables())
                );
    }
}

