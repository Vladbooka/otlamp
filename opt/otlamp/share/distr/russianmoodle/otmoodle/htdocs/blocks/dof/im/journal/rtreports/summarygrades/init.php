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

class dof_im_journal_rtreport_summarygrades extends dof_modlib_rtreport_base
{
    /**
     * Получение кода рейтинга
     *
     * @return string
     */
    public function get_type_code()
    {
        return 'summarygrades';
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
        
        // Заголовки
        $first_row = $second_row = [];
        
        // Предметы
        $first_row[] = $this->dof->get_string('rtreport_summarygrades_pitems', 'journal');
        $second_row[] = '';
        
        foreach($this->data['result']->plans as $plan)
        {
            // Название контрольной точки
            $first_row[] = $plan->name;
            array_push($first_row, '', '', '');
            
            // Добавление заголовкой для конкретной контрольной точки
            array_push($second_row,
                    $this->dof->get_string('rtreport_summarygrades_countusers', 'journal'),
                    $this->dof->get_string('rtreport_summarygrades_countusers_success_percent', 'journal'),
                    $this->dof->get_string('rtreport_summarygrades_pitem_average_grade', 'journal'),
                    $this->dof->get_string('rtreport_summarygrades_pitem_quality', 'journal'));
        }
        
        $result[] = $first_row;
        $result[] = $second_row;
        
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
        $result = [];
        
        if ( empty($this->data['result']) )
        {
            return $result;
        }
        
        // Заголовки
        $row = new html_table_row();
        $second_row = new html_table_row();
        
        // Предметы
        $pitems = new html_table_cell($this->dof->get_string('rtreport_summarygrades_pitems', 'journal'));
        $pitems->rowspan = 2;
        $row->cells[] = $pitems;
        
        foreach($this->data['result']->plans as $plan)
        {
            // Название контрольной точки
            $planheader = new html_table_cell($plan->name);
            $planheader->colspan = 4;
            $planheader->style = 'text-align: center;';
            
            array_push(
                    $row->cells, 
                    $planheader
                    );
            
            // Добавление заголовкой для конкретной контрольной точки
            array_push($second_row->cells,
                    $this->dof->get_string('rtreport_summarygrades_countusers', 'journal'),
                    $this->dof->get_string('rtreport_summarygrades_countusers_success_percent', 'journal'),
                    $this->dof->get_string('rtreport_summarygrades_pitem_average_grade', 'journal'),
                    $this->dof->get_string('rtreport_summarygrades_pitem_quality', 'journal'));
        }
        
        $result[] = $row;
        $result[] = $second_row;
        
        // Возвращение данных
        return $result;
    }
    
    /**
     * Получение строк
     *
     * @see dof_modlib_rtreport_base::get_rows()
     */
    protected function get_rows()
    {
        $result = [];
        
        if ( empty($this->data['result']) )
        {
            return $result;
        }
        
        foreach ( $this->data['result']->pitemsinfo as $pitemid => $pitem )
        {
            $row = [$this->data['result']->pitems[$pitemid]->name];
            
            foreach ( $this->data['result']->plans as $plan )
            {
                array_push(
                        $row, 
                        $pitem[$plan->id]->countusers,
                        $pitem[$plan->id]->countsuccessuserspercent . '%',
                        $pitem[$plan->id]->average,
                        $pitem[$plan->id]->qualitypercent . '%'
                        );
            }
            
            $result[] = $row;
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
        return $this->get_rows();
    }
    
    
    /**
     * Массив ячеек для объединения
     *
     * @see dof_modlib_rtreport_base::merge_cells()
     */
    protected function merge_cells()
    {
        $merge_cells = [
            [0, 0, 1, 0]
        ];
        
        $colnum = 1;
        foreach ( $this->data['result']->plans as $plan )
        {
            $merge_cells[] = [0, $colnum, 0, $colnum + 3];
            $colnum += 4;
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
        
        $this->data['result'] = $this->dof->im('journal')->get_summarygrades(
            $this->data['input']->programmid, 
            $this->data['input']->parallel,
            $this->data['input']->ageid
        );
    }
}

