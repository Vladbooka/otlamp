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
 * Оотчет. Рейтинг по учебному процессу
 *
 * @package    im
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_im_journal_rtreport_rating_cstream extends dof_modlib_rtreport_base
{
    /**
     * Получение кода рейтинга
     *
     * @return string
     */
    public function get_type_code()
    {
        return 'rating_cstream';
    }

    /**
     * Особое получение заголовков для XLS и ODS
     *
     * @return []
     */
    protected function get_headers_values()
    {
        // Текущее время для языковых строк
        $time = new stdClass();
        $time->time = date('d-m-Y', time());

        // Название групп
        $group_string = '';
        foreach ( $this->data['grades']->groups as $group )
        {
            if ( empty($group_string) )
            {
                $group_string = $group->name;
            } else
            {
                $group_string .= ', ' . $group->name;
            }
        }
        // Заголовок
        $head_info = $this->dof->get_string('rtreport_rating_cstream_groups', 'journal', $group_string);

        // Предмет
        $discipline = $this->dof->get_string('rtreport_rating_cstream_discipline', 'journal', $this->data['grades']->discipline_name);

        // Препод
        $teacher = $this->dof->get_string('rtreport_rating_cstream_teacher', 'journal', $this->data['grades']->teacher_name);

        // Номер
        $number = $this->dof->get_string('rtreport_rating_cstream_number', 'journal');

        // Список учащихся
        $list_users = $this->dof->get_string('rtreport_rating_cstream_list_users', 'journal');

        // Средний балл
        $average_grade = $this->dof->get_string('rtreport_rating_cstream_average_grade', 'journal', $time);

        // Средний балл
        $max_grade = $this->dof->get_string('rtreport_rating_cstream_max_grade', 'journal', $time);

        // Первая строка заголовков
        $head_first = [$number, $list_users, $average_grade, '', $max_grade];

        // Балл
        $grade = $this->dof->get_string('rtreport_rating_cstream_grade', 'journal');

        // Процент
        $percent = '%';

        // Вторая строка заголовков
        $head_second = ['', '', $grade, $percent, ''];

        // Возвращение заголовков
        return [[$head_info], [$discipline], [$teacher], $head_first, $head_second];
    }

    /**
     * Получение заголовков
     *
     * @see dof_modlib_rtreport_base::get_headers()
     */
    protected function get_headers()
    {
        // Текущее время для языковых строк
        $time = new stdClass();
        $time->time = date('d-m-Y', time());

        // Название групп
        $group_string = '';
        foreach ( $this->data['grades']->groups as $group )
        {
            if ( empty($group_string) )
            {
                $group_string = $group->name;
            } else
            {
                $group_string .= ', ' . $group->name;
            }
        }
        // Заголовок
        $head_info = new html_table_cell();
        $head_info->colspan = 5;
        $head_info->text = $this->dof->get_string('rtreport_rating_cstream_groups', 'journal', $group_string);
        $head_info->style = 'text-align:center;';

        $head_info_row = new html_table_row();
        $head_info_row->cells[] = $head_info;
        $head_info_row->style = 'text-align:center;';

        // Предмет
        $discipline = new html_table_cell();
        $discipline->colspan = 5;
        $discipline->text = $this->dof->get_string('rtreport_rating_cstream_discipline', 'journal', $this->data['grades']->discipline_name);
        $discipline_row = new html_table_row();
        $discipline_row->cells[] = $discipline;
        $discipline_row->style = 'text-align:center;';

        // Препод
        $teacher = new html_table_cell();
        $teacher->colspan = 5;
        $teacher->text = $this->dof->get_string('rtreport_rating_cstream_teacher', 'journal', $this->data['grades']->teacher_name);
        $teacher_row = new html_table_row();
        $teacher_row->cells[] = $teacher;
        $teacher_row->style = 'text-align:center;';

        // Номер
        $number = new html_table_cell();
        $number->rowspan = 2;
        $number->text = $this->dof->get_string('rtreport_rating_cstream_number', 'journal');

        // Список учащихся
        $list_users = new html_table_cell();
        $list_users->rowspan = 2;
        $list_users->text = $this->dof->get_string('rtreport_rating_cstream_list_users', 'journal');

        // Средний балл
        $average_grade = new html_table_cell();
        $average_grade->colspan = 2;
        $average_grade->text = $this->dof->get_string('rtreport_rating_cstream_average_grade', 'journal', $time);

        // Максимальный балл
        $max_grade = new html_table_cell();
        $max_grade->rowspan = 2;
        $max_grade->text = $this->dof->get_string('rtreport_rating_cstream_max_grade', 'journal', $time);

        // Первый массив заголовков
        $head_first = new html_table_row();
        $head_first->cells = [$number, $list_users, $average_grade, $max_grade];
        $head_first->style = 'text-align:center;';

        // Балл пользователя
        $grade = new html_table_cell();
        $grade->rowspan = 1;
        $grade->colspan = 1;
        $grade->text = $this->dof->get_string('rtreport_rating_cstream_grade', 'journal');

        // Процент
        $percent = new html_table_cell();
        $percent->rowspan = 1;
        $percent->colspan = 1;
        $percent->text = '%';

        // Второй массив заголовков
        $head_second = new html_table_row();
        $head_second->cells = [$grade, $percent];
        $head_second->style = 'text-align:center;';

        // Возвращение заголовков
        return [$head_info_row, $discipline_row, $teacher_row, $head_first, $head_second];
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
            foreach ( $this->data['grades']->users as $user_id => $user_result )
            {
                // Возвращение заголовков
                $row = new html_table_row();
                $row->cells = [$user_result->number, $user_result->fio, $user_result->grade, $user_result->percent, $user_result->max_grade];
                $row->style = 'text-align:center;';
                $result[] = $row;
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
            foreach ( $this->data['grades']->users as $user_id => $user_result )
            {
                // Возвращение заголовков
                $result[] = [$user_result->number, $user_result->fio, $user_result->grade, $user_result->percent, $user_result->max_grade];
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
        // Массив информации о слиянии
        $merge_cells = [
            [0, 0, 0, 4],
            [1, 0, 1, 4],
            [2, 0, 2, 4],
            [3, 0, 4, 0],
            [3, 1, 4, 1],
            [3, 2, 3, 3],
            [3, 4, 4, 4]
        ];

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
        $this->dof->im('cstreams')->require_access('view:rtreport/rating_cstream');

        $should_update = (bool)optional_param('update', 0, PARAM_INTEGER);
        $get_from_cache = true;
        if ( ! empty($should_update) )
        {
            $get_from_cache = false;
            $this->dof->messages->add($this->dof->get_string('rtreport_cache_rating_cstream_updated', 'journal'), 'message');
        }

        // Получение оценок пользователей
        $result['grades'] = $this->dof->im('cstreams')->get_cstream_grades($cstream_id, $get_from_cache);

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
        $html .= dof_html_writer::tag('h2', $this->dof->get_string('rtreport_rating_cstream_name', 'journal', $name));
        $html .= dof_html_writer::end_div();

        return $html;
    }

    /**
     * Дополнительные обработчики
     *
     * @return string
     */
    public function get_processors()
    {
        $html = '';

        // Создание задачи на обновление кэша
        $updatelink = dof_html_writer::link(
                $this->dof->url_im('rtreport', '/index.php', array_merge($this->get_variables(), ['type' => $this->get_type_code(), 'update' => 1])),
                $this->dof->get_string('update_cache', 'journal'),
                ['class' => 'btn btn-primary button dof_button']);
        $html .= dof_html_writer::div($updatelink, 'mt-2');

//         $should_update = optional_param('update', 0, PARAM_INTEGER);
//         if ( ! empty($should_update) )
//         {
//             $this->dof->add_todo('im', 'cstreams', 'update_cstream_cache', $this->data['cstreamid'], null , 1, time());
//             $this->dof->messages->add($this->dof->get_string('rtreport_cache_todo_created', 'journal'), 'message');
//         }

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
                $this->dof->url_im('journal', '/group_journal/index.php?csid=' . $this->data['cstreamid'], $addvars)
                );

        $this->dof->modlib('nvg')->add_level(
                $this->dof->get_string('rtreport_rating_cstream', 'journal'),
                $this->dof->url_im('rtreport', '/index.php', array_merge($this->data, $addvars, ['type' => $this->get_type_code()]))
                );
    }
}

