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

// Подключение родительского класса
global $DOF;
require_once ($DOF->plugin_path('im', 'agroups', '/rtreports/rating_agroup/init.php'));

/**
 * Отчет. Личный рейтинг по программе
 * 
 * @package    im
 * @subpackage recordbook
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_im_recordbook_rtreport_my extends dof_im_agroups_rtreport_rating_agroup
{
    /**
     * Получение кода рейтинга
     *
     * @return string
     */
    public function get_type_code()
    {
        return 'my';
    }
    
    /**
     * Особое получение заголовков для XLS и ODS
     *
     * @return []
     */
    protected function get_headers_values()
    {
        $result = []; 
        
        // Первый массив заголовков
        $head_first = ['', ''];
        
        if ( ! empty($this->data['grades']->cstreams) )
        {
            foreach ( $this->data['grades']->cstreams as $cstream )
            {
                // Название дисциплины
                $head_first[] = $cstream->programmitem->name;
                $head_first[] = '';
            }
        }
        
        // Суммарный рейтинг
        array_push($head_first, $this->dof->get_string('rtreport_rating_agroup_sum_rtreport', 'agroups'), '');
        
        // Добавление первой строки в массив заголовков
        $result[] = $head_first;
        
        // Второй массив заголовков
        $head_second = ['', $this->dof->get_string('rtreport_rating_agroup_teacher_header', 'agroups')];
        
        if ( ! empty($this->data['grades']->cstreams) )
        {
            foreach ( $this->data['grades']->cstreams as $cstream )
            {
                // Название дисциплины
                $teacher_name = $cstream->cstream->teacher_name;
                
                // Добавление в строку
                array_push($head_second, $teacher_name, '');
            }
        }
        
        // Добавление в строку
        array_push($head_second, '', '');
        
        // Добавление третьей строки в массив заголовков
        $result[] = $head_second;
        
        // Третий массив заголовков
        $head_third = ['', ''];
        
        // Балл
        $grade = $this->dof->get_string('rtreport_rating_agroup_grade', 'agroups');
        
        // Процент
        $percent = '%';
        
        if ( ! empty($this->data['grades']->cstreams) )
        {
            foreach ( $this->data['grades']->cstreams as $cstream )
            {
                // Добавление в строку
                array_push($head_third, $grade, $percent);
            }
        }
        
        // Добавление в строку
        array_push($head_third, $grade, $percent);
        
        // Добавление второй строки в массив заголовков
        $result[] = $head_third;
        
        // Четвертый массив заголовков
        $head_fourth = ['', $this->dof->get_string('rtreport_rating_agroup_max_grade', 'agroups')];
        
        if ( ! empty($this->data['grades']->cstreams) )
        {
            foreach ( $this->data['grades']->cstreams as $cstream )
            {
                // Балл
                $grade_disc = number_format($cstream->grades->max_grade_average, 1);
                
                // Процент
                $percent_disc = 100;
                
                // Добавление в строку
                array_push($head_fourth, $grade_disc, $percent_disc);
            }
        }
        
        // Балл
        $max_grade_disc = ! empty($this->data['grades']->sum_grades) ? $this->data['grades']->sum_grades : 0;
        
        // Процент
        $max_percent_disc = 100;
        
        // Добавление в строку
        array_push($head_fourth, $max_grade_disc, $max_percent_disc);
        
        // Добавление второй строки в массив заголовков
        $result[] = $head_fourth;
        
        // Возвращение заголовков
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
        
        // Первый массив заголовков
        $head_first = new html_table_row();
        $head_first->cells = ['', ''];
        $head_first->style = 'text-align:center;';
        
        if ( ! empty($this->data['grades']->cstreams) )
        {
            foreach ( $this->data['grades']->cstreams as $cstream )
            {
                // Название дисциплины
                $discipline_name = new html_table_cell();
                $discipline_name->colspan = 2;
                $discipline_name->text = $cstream->programmitem->name;
                
                // Добавление в строку
                $head_first->cells[] = $discipline_name;
            }
        }
        
        // Суммарный рейтинг
        $sum_rtreport = new html_table_cell();
        $sum_rtreport->colspan = 2;
        $sum_rtreport->rowspan = 2;
        $sum_rtreport->text = $this->dof->get_string('rtreport_rating_agroup_sum_rtreport', 'agroups');
        $head_first->cells[] = $sum_rtreport;
        
        // Добавление первой строки в массив заголовков
        $result[] = $head_first;
        
        // Суммарный рейтинг
        $teacher_header = new html_table_cell();
        $teacher_header->style = 'font-style: italic;';
        $teacher_header->text = $this->dof->get_string('rtreport_rating_agroup_teacher_header', 'agroups');
        
        // Второй массив заголовков
        $head_second = new html_table_row();
        $head_second->cells = ['', $teacher_header];
        $head_second->style = 'text-align:center;';
        
        if ( ! empty($this->data['grades']->cstreams) )
        {
            foreach ( $this->data['grades']->cstreams as $cstream )
            {
                // Название дисциплины
                $teacher_name = new html_table_cell();
                $teacher_name->colspan = 2;
                $teacher_name->text = $cstream->cstream->teacher_name;
                
                // Добавление в строку
                $head_second->cells[] = $teacher_name;
            }
        }
        
        // Добавление третьей строки в массив заголовков
        $result[] = $head_second;
        
        // Третий массив заголовков
        $head_third = new html_table_row();
        $head_third->cells = ['', ''];
        $head_third->style = 'text-align:center;';
        
        // Балл
        $grade = new html_table_cell();
        $grade->text = $this->dof->get_string('rtreport_rating_agroup_grade', 'agroups');
        
        // Процент
        $percent = new html_table_cell();
        $percent->text = '%';
        
        if ( ! empty($this->data['grades']->cstreams) )
        {
            foreach ( $this->data['grades']->cstreams as $cstream )
            {
                // Добавление в строку
                array_push($head_third->cells, $grade, $percent);
            }
        }
        
        // Добавление в строку
        array_push($head_third->cells, $grade, $percent);
        
        // Добавление второй строки в массив заголовков
        $result[] = $head_third;
        
        // Максимально возможный балл
        $max_grade = new html_table_cell();
        $max_grade->style = 'font-style: italic';
        $max_grade->text = $this->dof->get_string('rtreport_rating_agroup_max_grade', 'agroups');
        
        // Четвертый массив заголовков
        $head_fourth = new html_table_row();
        $head_fourth->cells = ['', $max_grade];
        $head_fourth->style = 'text-align:center;';
        
        if ( ! empty($this->data['grades']->cstreams) )
        {
            foreach ( $this->data['grades']->cstreams as $cstream )
            {
                // Балл
                $grade_disc = new html_table_cell();
                $grade_disc->text = number_format($cstream->grades->max_grade_average, 1);
                
                // Процент
                $percent_disc = new html_table_cell();
                $percent_disc->text = 100;
                
                // Добавление в строку
                array_push($head_fourth->cells, $grade_disc, $percent_disc);
            }
        }
        
        // Балл
        $max_grade_disc = new html_table_cell();
        $max_grade_disc->text = (!empty($this->data['grades']->sum_grades) ? $this->data['grades']->sum_grades : '0');
        
        // Процент
        $max_percent_disc = new html_table_cell();
        $max_percent_disc->text = 100;
        
        // Добавление в строку
        array_push($head_fourth->cells, $max_grade_disc, $max_percent_disc);
        
        // Добавление второй строки в массив заголовков
        $result[] = $head_fourth;
        
        // Возвращение заголовков
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
        
        if ( ! empty($this->data['grades']->users) )
        {
            $counter = 0;
            if ( ! empty($this->data['user']) )
            {
                if ( isset($this->data['grades']->users[$this->data['user']]) )
                {
                    $this->data['grades']->users = [$this->data['grades']->users[$this->data['user']]];
                }
            }
            foreach ( $this->data['grades']->users as $info )
            {
                $row = [++$counter];
                $row[] = $info->user->sortname;
                foreach ( $this->data['grades']->cstreams as $cstream )
                {
                    // Балл студента за учебный процесс
                    if ( ! empty($info->grades[$cstream->cstream->id]) )
                    {
                        $row[] = $info->grades[$cstream->cstream->id];
                    } else
                    {
                        $row[] = 0;
                    }
                    
                    // Процент студента за учебный процесс
                    if ( ! empty($info->percents[$cstream->cstream->id]) )
                    {
                        $row[] = $info->percents[$cstream->cstream->id];
                    } else
                    {
                        $row[] = 0;
                    }
                }
                
                $row[] = $info->final_grade;
                $row[] = $info->final_grade_percent;
                
                $row_c = new html_table_row();
                $row_c->cells = $row;
                $row_c->style = 'text-align:center;';
                
                $result[] = $row_c;
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
        
        if ( ! empty($this->data['grades']->users) )
        {
            $counter = 0;
            if ( ! empty($this->data['user']) )
            {
                if ( isset($this->data['grades']->users[$this->data['user']]) )
                {
                    $this->data['grades']->users = [$this->data['grades']->users[$this->data['user']]];
                }
            }
            foreach ( $this->data['grades']->users as $info )
            {
                $row = [++$counter];
                $row[] = $info->user->sortname;
                foreach ( $this->data['grades']->cstreams as $cstream )
                {
                    // Балл студента за учебный процесс
                    if ( ! empty($info->grades[$cstream->cstream->id]) )
                    {
                        $row[] = $info->grades[$cstream->cstream->id];
                    } else
                    {
                        $row[] = 0;
                    }
                    
                    // Процент студента за учебный процесс
                    if ( ! empty($info->percents[$cstream->cstream->id]) )
                    {
                        $row[] = $info->percents[$cstream->cstream->id];
                    } else
                    {
                        $row[] = 0;
                    }
                }
                
                $row[] = $info->final_grade;
                $row[] = $info->final_grade_percent;
                
                $result[] = $row;
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
        $merge_cells = [];
        $pos = 2;
        if ( ! empty($this->data['grades']->cstreams) )
        {
            foreach ( $this->data['grades']->cstreams as $c )
            {
                $prev = $pos;
                $merge_cells[] = [0, $pos++, 0, $pos++];
                $merge_cells[] = [1, $prev++, 1, $prev];
            }
        }
        $merge_cells[] = [0, $pos++, 1, $pos];
        
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
        
        $sbc_id = optional_param('programmsbcid', 0, PARAM_INT);
        // Проверка идентификатора учебного процесса
        if ( ! empty($sbc_id) )
        {
            $sbc = $this->dof->storage('programmsbcs')->get_record(['id' => $sbc_id]);
            if ( ! empty($sbc) )
            {
                $result['programmsbc'] = $sbc;
                $result['programmsbcid'] = $sbc_id;
                $result['programm'] = $this->dof->storage('programms')->get_record(['id' => $sbc->programmid]);
                
                $result['user'] = $this->dof->storage('programmsbcs')->get_studentid_by_programmsbc($sbc_id);
                
                // Получение оценок пользователей
                $result['grades'] = $this->dof->im('recordbook')->get_my_grades($sbc_id, $result['user']);
            }
        }
        
        // Установка данных
        $this->set_data($result);
    }
    
    /**
     * Получение HTML Заголовка
     *
     * @return string
     */
    public function get_header()
    {
        $name = new stdClass();
        $name->name = $this->data['programm']->name;
        $name->studentname = $this->dof->storage('persons')->get_fullname($this->data['user']);
        
        $html = '';
        $html .= dof_html_writer::start_div();
        $html .= dof_html_writer::tag('h2', $this->dof->get_string('rtreport_my_name', 'recordbook', $name));
        $html .= dof_html_writer::end_div();
        
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
                $this->dof->get_string('title', 'recordbook'), 
                $this->dof->url_im('recordbook', '/index.php', $this->get_variables())
                );
        
        $this->dof->modlib('nvg')->add_level(
                $this->dof->get_string('rtreport_my', 'recordbook'),
                $this->dof->url_im('rtreport', '/index.php', array_merge($this->data, $this->get_variables()))
                );
    }
}

