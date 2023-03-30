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
 * Отчет по неопубликованным заданиям. Класс формирования данных в формате xls.
 * 
 * @package    report
 * @subpackage notreleased_assignments
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/lib/excellib.class.php');

class report_notreleased_assignments_format_xls
{
    /**
     * Двумерный массив с данными для отображения
     * @var array
     */
    private $reportdata = [];
    
    /**
     * Название файла с отчетом
     * @var string
     */
    private $filename = '';
    
    /**
     * Объект таблицы эксель
     * @var MoodleExcelWorkbook
     */
    private $workbook;
    
    /**
     * Объект рабочего листа эксель
     * @var MoodleExcelWorksheet
     */
    private $worksheet;

    /**
     * Конструктор
     * 
     * array $reportdata -  Двумерный массив с данными для отображения.
     */
    public function __construct($reportdata)
    {
        $this->reportdata = $reportdata;
        $this->filename = 'notreleased_assignments_'.date('m.d.y').'.xls';
        
        // Объявление файла отчета
        $this->workbook = new MoodleExcelWorkbook($this->filename);
        $this->worksheet = $this->workbook->add_worksheet('');
    }
    
    public function get_current_worksheet()
    {
        return $this->worksheet;
    }

    /**
     * Генерация данных для отчета
     */
    protected function generate_report()
    {
        //посылаем заголовок
        $this->workbook->send($this->filename);
        foreach($this->reportdata as $r => $reportrow) {
            foreach($reportrow as $c => $reportcell) {
                //на текущий лист пишем в нужную чейку текст в нужном формате
                $this->get_current_worksheet()->write_string(
                    $r,
                    $c,
                    $reportcell
                );
            }
        }
        $this->workbook->close();
    }

    /**
     * Формирует отчет и запускает его скачивание в формате xls
     *
     */
    public function print_report()
    {
        $this->generate_report();
    }
}