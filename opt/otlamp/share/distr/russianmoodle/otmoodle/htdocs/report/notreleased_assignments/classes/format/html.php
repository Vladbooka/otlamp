<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Отчет по неопубликованным заданиям. Класс формирования данных в формате html.
 * 
 * @package    report
 * @subpackage notreleased_assignments
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class report_notreleased_assignments_format_html
{
    /**
     * Двумерный массив с данными для отображения 
     * @var array
     */
    private $reportdata = [];
    
    /**
     * Строка с данными для отображения
     * @var string
     */
    private $html = '';

    /**
     * Конструктор
     * 
     * array $reportdata -  Двумерный массив с данными для отображения.
     */
    public function __construct($reportdata)
    {
        $this->reportdata = $reportdata;
    }
    
    /**
     * Генерация данных для отчета
     */
    protected function generate_report()
    {
        // создание таблицы
        $table = new html_table();
        $tablerows = [];
        foreach ($this->reportdata as $reportrow) { // строка таблицы
            $tablerow = new html_table_row();
            foreach ($reportrow as $c => $reportcell) { // ячейка таблицы
                // запишем данные
                $tablecell = new html_table_cell($reportcell);
                // добавим ячейку в строку
                $tablerow->cells[$c] = $tablecell;
            }
            // добавим строку в данные для таблицы
            $tablerows[] = $tablerow;
        }
        // запишем в таблицу данные
        $table->data = $tablerows;
        // сохраним их в виде html
        $this->html = html_writer::table($table);
    }
    
    /**
     * Получение отчета в виде html-строки
     * 
     * @return string отчет в формате html
     */
    public function get_report() {
        $this->generate_report();
        return $this->html;
    }
    
    /**
     * Распечатывает отчет в формате html 
     */
    public function print_report() {
        echo $this->get_report();
    }
}