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
 * Отчет. Отчет выполнения учебной  нагрузки преподавателя
 * 
 * @package    im
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_im_journal_rtreport_workloadcstream extends dof_modlib_rtreport_base
{
    /**
     * Получение кода рейтинга
     *
     * @return string
     */
    public function get_type_code()
    {
        return 'workloadcstream';
    }
    
    /**
     * Типы занятий для отображения
     * 
     * @var array
     */
    protected $lesson_types = [];
    
    /**
     * Особое получение заголовков для XLS и ODS
     *
     * @return []
     */
    protected function get_headers_values()
    {
        // Четыре строки заголовков
        $row_first = [];
        $row_second = [];
        $row_third = [];
        $row_fourth = [];
        
        // Получение типов занятий
        $edited_lesson_types = [];
        
        // Получение типов для отображения
        $lesson_types_to_show = explode(',',
                $this->dof->storage('config')->get_config_value(
                        'cstream_workload_lesson_types',
                        'im', 'cstreams',
                        optional_param('departmentid', 0, PARAM_INT)));
        
        if ( in_array('facetime', $lesson_types_to_show) || in_array('distance', $lesson_types_to_show) )
        {
            if( ($key = array_search('distance', $lesson_types_to_show)) !== false)
            {
                unset($lesson_types_to_show[$key]);
            }
            if( ($key = array_search('facetime', $lesson_types_to_show)) !== false)
            {
                unset($lesson_types_to_show[$key]);
            }
            $lesson_types_to_show[] = 'lesson';
        }
        foreach ( $lesson_types_to_show as $value )
        {
            $edited_lesson_types[$value] = $this->dof->get_string('rtreport_workloadcstream_' . $value, 'journal');
        }
        
        $this->lesson_types = $edited_lesson_types;
        
        // Ширина таблица
        $number = 6 + count($this->lesson_types);
        
        // Учет выполнения учебной нагрузки
        $info = $this->dof->get_string('rtreport_workloadcstream_workload', 'journal');
        $row_first[] = $info;
        for ( $i = 1; $i < $number; $i++ )
        {
            $row_first[] = '';
        }
        
        // ФИО
        $fio = $this->dof->get_string('rtreport_workloadcstream_fio', 'journal', $this->data['info']->teacher_name);
        $row_second[] = $fio;
        for ( $i = 1; $i < $number; $i++ )
        {
            $row_second[] = '';
        }
        
        // Дисциплина
        $discipline = $this->dof->get_string('rtreport_workloadcstream_discipline', 'journal', $this->data['info']->discipline_name);
        $row_third[] = $discipline;
        for ( $i = 1; $i < $number; $i++ )
        {
            $row_third[] = '';
        }
        
        // Дата
        $date = $this->dof->get_string('rtreport_workloadcstream_date', 'journal');
        
        // Группа или поток
        $group = $this->dof->get_string('rtreport_workloadcstream_group', 'journal');
        
        // Тема занятий
        $theme = $this->dof->get_string('rtreport_workloadcstream_theme', 'journal');
        
        // Всего
        $aboutall = $this->dof->get_string('rtreport_workloadcstream_aboutall', 'journal');
        
        // Подпись
        $sign = $this->dof->get_string('rtreport_workloadcstream_sign', 'journal');
        
        $row_fourth = array_merge([$date, $group, $theme, ''], $edited_lesson_types, [$aboutall, $sign]);
        
        // Возвращение заголовков
        return [
            $row_first,
            $row_second,
            $row_third,
            $row_fourth
        ];
    }
    
    /**
     * Получение заголовков
     * 
     * @see dof_modlib_rtreport_base::get_headers()
     */
    protected function get_headers()
    {
        // Получение типов занятий
        $edited_lesson_types = [];
        
        // Получение типов для отображения
        $lesson_types_to_show = explode(',',
                $this->dof->storage('config')->get_config_value(
                        'cstream_workload_lesson_types',
                        'im', 'cstreams',
                        optional_param('departmentid', 0, PARAM_INT)));
        
        if ( in_array('facetime', $lesson_types_to_show) || in_array('distance', $lesson_types_to_show) )
        {
            if( ($key = array_search('distance', $lesson_types_to_show)) !== false)
            {
                unset($lesson_types_to_show[$key]);
            }
            if( ($key = array_search('facetime', $lesson_types_to_show)) !== false)
            {
                unset($lesson_types_to_show[$key]);
            }
            $lesson_types_to_show[] = 'lesson';
        }
        foreach ( $lesson_types_to_show as $value )
        {
            $cell = new html_table_cell();
            $cell->text = html_writer::div($this->dof->get_string('rtreport_workloadcstream_' . $value, 'journal'), '',  ['style' => 'transform: rotate(-90deg);padding: 80px 0;']);
            $edited_lesson_types[$value] = $cell;
        }
        
        $this->lesson_types = $edited_lesson_types;
        
        $quantity = 6 + count($this->lesson_types); 
        
        // Учет выполнения учебной нагрузки
        $number = new html_table_cell();
        $number->colspan = $quantity;
        $number->text = $this->dof->get_string('rtreport_workloadcstream_workload', 'journal');
        $number->style = 'text-align:center;';
        
        // ФИО
        $fio = new html_table_cell();
        $fio->colspan = $quantity;
        $fio->text = $this->dof->get_string('rtreport_workloadcstream_fio', 'journal', $this->data['info']->teacher_name);
        $fio->style = 'text-align:center;';
        
        // Дисциплина
        $discipline = new html_table_cell();
        $discipline->colspan = $quantity;
        $discipline->text = $this->dof->get_string('rtreport_workloadcstream_discipline', 'journal', $this->data['info']->discipline_name);
        $discipline->style = 'text-align:center;';
        
        // Дата
        $date = new html_table_cell();
        $date->text = html_writer::div($this->dof->get_string('rtreport_workloadcstream_date', 'journal'), '', ['style' => 'transform: rotate(-90deg);padding: 80px 0;']);
        
        // Группа или поток
        $group = new html_table_cell();
        $group->text = html_writer::div($this->dof->get_string('rtreport_workloadcstream_group', 'journal'), '',  ['style' => 'transform: rotate(-90deg);padding: 80px 0;']);
        
        // Тема занятий
        $theme = new html_table_cell();
        $theme->colspan = 2;
        $theme->text = html_writer::div($this->dof->get_string('rtreport_workloadcstream_theme', 'journal'), '', ['style' => 'transform: rotate(-90deg);padding: 80px;']);
        
        // Всего
        $aboutall = new html_table_cell();
        $aboutall->text = html_writer::div($this->dof->get_string('rtreport_workloadcstream_aboutall', 'journal'), '', ['style' => 'transform: rotate(-90deg);padding: 80px 0;']);
        
        // Подпись
        $sign = new html_table_cell();
        $sign->text = html_writer::div($this->dof->get_string('rtreport_workloadcstream_sign', 'journal'), '', ['style' => 'transform: rotate(-90deg);padding: 80px 0;']);
        
        $header_first = new html_table_row();
        $header_first->cells = array_merge([$date, $group, $theme], $edited_lesson_types, [$aboutall, $sign]);
        $header_first->style = 'text-align:center;';
        
        // Возвращение заголовков
        return [
            [$number],
            [$fio],
            [$discipline],
            $header_first
        ];
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
        
        if ( ! empty($this->data['info']->lessons) )
        {
            // Формирование строки с группами
            $groups_string = '';
            if ( ! empty($this->data['info']->groups) )
            {
                foreach ( $this->data['info']->groups as $group_name )
                {
                    if ( empty($groups_string) )
                    {
                        $groups_string = $group_name->name;
                    } else 
                    {
                        $groups_string .= ', ' . $group_name->name;
                    }
                }
            }
            
            foreach ( $this->data['info']->lessons as $lesson )
            {
                // Строка данных
                $row = [];
                $row_second = [];
                
                // Дата
                $date = new html_table_cell();
                $date->rowspan = 2;
                $date->text = html_writer::div(date('d.m.Y', $lesson->date), '', ['style' => 'transform: rotate(-90deg);padding: 40px 0;']);
                $row[] = $date;
                
                // Группа или поток
                $group = new html_table_cell();
                $group->rowspan = 2;
                $group->text = $groups_string;
                $row[] = $group;

                // Тема занятий
                $theme = new html_table_cell();
                $theme->text = $lesson->theme;
                $theme->rowspan = 2;
                $theme->colspan = 1;
                $row[] = $theme;
                
                // Б (бюджетная)
                $row[] = $this->dof->get_string('rtreport_workloadcstream_budget', 'journal');
                
                // Заполнение часами
                foreach ( $this->lesson_types as $code => $lang )
                {
                    if ( ($code === 'lesson') && (($lesson->lesson_type === 'distance') || ($lesson->lesson_type === 'facetime')) )
                    {
                        $row[] = $lesson->hours;
                    } elseif ( $code === $lesson->lesson_type )
                    {
                        $row[] = $lesson->hours;
                    } else 
                    {
                        $row[] = '';
                    }
                }
                
                $row[] = $lesson->hours;
                $row[] = '';
                
                $row_c = new html_table_row();
                $row_c->cells = $row;
                $row_c->style = 'text-align:center;';
                
                
                // В/Б
                $row_second[] = $this->dof->get_string('rtreport_workloadcstream_vbudget', 'journal');
                foreach ( $this->lesson_types as $v )
                {
                    $row_second[] = '';
                }
                
                $row_second[] = 0;
                $row_second[] = '';
                
                $row_second_c = new html_table_row();
                $row_second_c->cells = $row_second;
                $row_second_c->style = 'text-align:center;';
                
                array_push($result, $row_c, $row_second_c);
            }
            
            // Установка общего количества часов по всем занятиям
            $last_row = ['', '', '', ''];
            foreach ( $this->lesson_types as $type )
            {
                $last_row[] = '';
            }
            array_pop($last_row);
            $last_row[] = $this->dof->get_string('rtreport_workloadcstream_all_hours', 'journal');
            $last_row[] = $this->data['info']->total_hours;
            $last_row[] = '';
            
            $last_row_c = new html_table_row();
            $last_row_c->cells = $last_row;
            $last_row_c->style = 'text-align:center;';
            
            array_push($result, $last_row_c);
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
        
        if ( ! empty($this->data['info']->lessons) )
        {
            // Формирование строки с группами
            $groups_string = '';
            if ( ! empty($this->data['info']->groups) )
            {
                foreach ( $this->data['info']->groups as $group_name )
                {
                    if ( empty($groups_string) )
                    {
                        $groups_string = $group_name->name;
                    } else
                    {
                        $groups_string .= ', ' . $group_name->name;
                    }
                }
            }
            
            foreach ( $this->data['info']->lessons as $lesson )
            {
                        // Строка данных
                        $row_first = [];
                        $row_second = [];
                        
                        // Дата
                        $row_first[] = date('d.m.Y', $lesson->date);
                        
                        // Группа или поток
                        $row_first[] = $groups_string;
                        
                        // Тема занятий
                        $row_first[] = $lesson->theme;
                        
                        // Б (бюджетная)
                        $row_first[] = $this->dof->get_string('rtreport_workloadcstream_budget', 'journal');;
                        
                        // Заполнение часами
                        foreach ( $this->lesson_types as $code => $lang )
                        {
                            if ( ($code === 'lesson') && (($lesson->lesson_type === 'distance') || ($lesson->lesson_type === 'facetime')) )
                            {
                                $row_first[] = $lesson->hours;
                            } elseif ( $code === $lesson->lesson_type )
                            {
                                $row_first[] = $lesson->hours;
                            } else
                            {
                                $row_first[] = '';
                            }
                        }
                        
                        $row_first[] = $lesson->hours;
                        $row_first[] = '';
                        
                        $row_second[] = '';
                        $row_second[] = '';
                        $row_second[] = '';
                        
                        // В/Б
                        $row_second[] = $this->dof->get_string('rtreport_workloadcstream_vbudget', 'journal');
                        foreach ( $this->lesson_types as $v )
                        {
                            $row_second[] = '';
                        }
                        
                        $row_second[] = 0;
                        $row_second[] = '';
                        
                        array_push($result, $row_first, $row_second);
            }
            
            // Установка общего количества часов по всем занятиям
            $last_row = ['', '', '', ''];
            foreach ( $this->lesson_types as $type )
            {
                $last_row[] = '';
            }
            array_pop($last_row);
            $last_row[] = $this->dof->get_string('rtreport_workloadcstream_all_hours', 'journal');
            $last_row[] = $this->data['info']->total_hours;
            $last_row[] = '';
            
            array_push($result, $last_row);
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
        $number = 5 + count($this->lesson_types);
        
        // Массив информации о слиянии
        $merge_cells = [
            [0, 0, 0, $number],
            [1, 0, 1, $number],
            [2, 0, 2, $number],
            [3, 2, 3, 3]
        ];
        
        // Строки данных начинаются с 5 строки
        $counter = 4;
        
        if ( ! empty($this->data['info']->lessons) )
        {
            foreach ( $this->data['info']->lessons as $elem )
            {
               array_push(
                       $merge_cells, 
                       [$counter, 0, $counter + 1, 0],
                       [$counter, 1, $counter + 1, 1],
                       [$counter, 2, ++$counter, 2]
                       );
               $counter++;
            }
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
        
        $cstream_id = optional_param('cstreamid', 0, PARAM_INT);
        // Проверка идентификатора учебного процесса
        if ( ! empty($cstream_id) )
        {
            $cstream = $this->dof->storage('cstreams')->get_record(['id' => $cstream_id]);
            if ( ! empty($cstream) )
            {
                $result['cstream'] = $cstream;
                $result['cstreamid'] = $cstream_id;
            }
        }
        
        // Проверка прав
        $this->dof->im('rtreport')->require_access('view:rtreport/workloadcstream');
        
        // Получение оценок пользователей
        $result['info'] = $this->dof->im('cstreams')->get_workloadcstream($cstream_id);
        
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
        $name->name = $this->data['cstream']->name;
        
        $html = '';
        $html .= dof_html_writer::start_div();
        $html .= dof_html_writer::tag('h2', $this->dof->get_string('rtreport_workloadcstream_name', 'journal', $name));
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
        GLOBAL $addvars;
        $this->dof->modlib('nvg')->add_level(
                $this->dof->get_string('group_journal', 'journal'),
                $this->dof->url_im('journal', '/group_journal/index.php?csid=' . $this->data['cstreamid'], $this->get_variables())
                );
        
        $this->dof->modlib('nvg')->add_level(
                $this->dof->get_string('rtreport_workloadcstream', 'journal'),
                $this->dof->url_im('rtreport', '/index.php', array_merge($this->data, $this->get_variables(), ['type' => $this->get_type_code()]))
                );
    }
}

