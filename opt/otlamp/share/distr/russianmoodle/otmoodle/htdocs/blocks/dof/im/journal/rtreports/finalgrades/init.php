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
 * Отчет. Сводный отчет по оценкам и ведомость оценок учащихся за учебные периоды по параллели
 * 
 * @package    im
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_im_journal_rtreport_finalgrades extends dof_modlib_rtreport_base
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
        return 'finalgrades';
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
        
        // ФИО
        $row[] = $this->dof->get_string('finalgrades_students_fullnames', 'journal');
        
        // Дисциплины
        $row[] = $this->dof->get_string('finalgrades_programmitems', 'journal');
        
        // Названия контрольных точек
        foreach($this->data['result']->plans as $plan)
        {
            $row[] = $plan->name;
        }
        
        // итог
        $row[] = $this->dof->get_string('finalgrades_summary_grade', 'journal');
        
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
        return $this->get_headers_values();
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
            $student = $this->data['result']->students[$rowdata->studentid];
            $pitem = $this->data['result']->pitems[$rowdata->pitemid];
            $cpassed = $this->data['result']->cpasseds[$rowdata->cpassedid];
            
            // новая строка
            $row = new html_table_row();
            $row->attributes['class'] = 'cpassed_'.$cpassed->status;
            
            if( $laststudent != $student)
            {
                // ФИО
                $cellfio = new html_table_cell();
                $cellfio->text = $student->fullname;
                $cellfio->rowspan = count($this->data['result']->students[$student->id]->cpassedids);
                $cellfio->attributes['class'] = 'student_fio';
                $row->cells[] = $cellfio;
            }
            
            // Дисциплина
            $cellpitem = new html_table_cell();
            $cpassedlink = dof_html_writer::link(
                $this->dof->url_im('cpassed', '/view.php', ['cpassedid' => $cpassed->id]),
                $cpassed->statusname,
                [
                    'class' => 'cpassed_link'
                ]
            );
            $cellpitem->text = $pitem->name.$cpassedlink;
            $cellpitem->attributes['class'] = 'pitem';
            $row->cells[] = $cellpitem;
            
            // Для каждого плана (в том же порядке, что и в заголовках
            foreach($this->data['result']->plans as $planid => $plan)
            {
                $grades = [];
                if( ! empty($rowdata->plangrades[$planid]) )
                {
                    // оценки
                    foreach($rowdata->plangrades[$planid] as $cpgrade)
                    {
                        $grades[] = $cpgrade->grade;
                    }
                }
                
                $row->cells[] = new html_table_cell(implode(', ', $grades));
            }
            // итог - оценка из cpassed
            $row->cells[] = new html_table_cell($cpassed->grade);
            
            array_push($result, $row);
            $laststudent = $student;
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

        $laststudent = null;
        foreach($this->data['result']->rows as $rowdata)
        {
            $student = $this->data['result']->students[$rowdata->studentid];
            $pitem = $this->data['result']->pitems[$rowdata->pitemid];
            $cpassed = $this->data['result']->cpasseds[$rowdata->cpassedid];
            
            // новая строка
            $row = [];
            
            if( $laststudent != $student)
            {
                // ФИО
                $row[] = $student->fullname;
            } else
            {
                $row[] = '';
            }
            
            // Дисциплина
            $row[] = $pitem->name.' - '.$cpassed->statusname;
            
            // Для каждого плана (в том же порядке, что и в заголовках
            foreach($this->data['result']->plans as $planid => $plan)
            {
                
                $grades = [];
                if( ! empty($rowdata->plangrades[$planid]) )
                {
                    // оценки
                    foreach($rowdata->plangrades[$planid] as $cpgrade)
                    {
                        $grades[] = $cpgrade->grade;
                    }
                }
                $row[] = implode(', ', $grades);
            }
            // итог - оценка из cpassed
            $row[] = $cpassed->grade;
            
            if( $cpassed->status == 'failed' )
            {
                for($colnum = 1; $colnum < count($row); $colnum++)
                {
                    $this->style_cells[(count($result)+1)."_".$colnum] = ['color' => 'gray', 'border' => 1];
                }
            }
            
            array_push($result, $row);
            $laststudent = $student;
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
        
        $laststudent = null;
        $rownum = 1;
        foreach($this->data['result']->rows as $rowdata)
        {
            $student = $this->data['result']->students[$rowdata->studentid];
                
            if( $laststudent != $student && (count($this->data['result']->students[$student->id]->cpassedids) > 1))
            {
                $merge_cells[] = [$rownum, 0, $rownum+count($this->data['result']->students[$student->id]->cpassedids)-1, 0]; 
            }
            
            $laststudent = $student;
            $rownum++;
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

        $this->data['result'] = $this->dof->im('journal')->get_finalgrades(
            $this->data['input']->programmid,
            $this->data['input']->parallel,
            $this->data['input']->ageid
        );
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
}

