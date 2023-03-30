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
 * Блок объединения отчетов. Класс формирования данных в формате xls.
 * 
 * @package    block
 * @subpackage reports_union
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/lib/excellib.class.php');

class report_mods_data_format_xls
{
    private $reportdata=[];
    private $filename = '';
    private $workbook;
    private $worksheet;
    private $xlsformats;

    /**
     * Конструктор
     * 
     * array $reportdata -  Двумерный массив с данными для отображения. 
     *                      Каждая ячейка - массив вида ['data'=>'данные','class'=>'класс для оформления']
     */
    public function __construct($reportdata)
    {        
        $this->reportdata = $reportdata;
        $this->filename = 'report_mods_data_' . date('d.m.Y');
        
        // Объявление файла отчета
        $this->workbook = new MoodleExcelWorkbook($this->filename);
        $this->worksheet = $this->workbook->add_worksheet('');
        // Форматирование
        $this->xlsformats = new stdClass();

        //стили для заголовка первого уровня
        $this->xlsformats->header1 = $this->workbook->add_format(array(
            'bold'=>1,
            'size'=>12));
        //стили для заголовка второго уровня
        $this->xlsformats->header2 = $this->workbook->add_format(array(
            'bold'=>1,
            'size'=>11));

        //стили для остальных значений (четные/нечетные)
        $this->xlsformats->default0 = $this->workbook->add_format(array(
            'align'=>'left',
            'v_align'=>'top'));
        $this->xlsformats->default0->set_bg_color('#efefef');
        
        $this->xlsformats->default1 = $this->workbook->add_format(array(
            'align'=>'left',
            'v_align'=>'top'));
    }
    
    public function get_current_worksheet()
    {
        return $this->worksheet;
    }

    /**
     * Получить формат отображения
     * 
     * @param string $classname - наименование класса
     * @return string строка с css-стили
     */
    public function get_format($classname)
    {
        if(!empty($this->xlsformats->$classname))
        {//имеется такой формат отображения - вернем его
            return $this->xlsformats->$classname;
        } else
        {
            return null;
        }
        
    }

    /**
     * Генерация данных для отчета
     */
    protected function generate_report()
    {
        //посылаем заголовок
        $this->workbook->send($this->filename);
        //оформление первой строки
        $format = 'default0';
        
        foreach($this->reportdata as $r=>$reportrow)
        {
            //меняем формат каждую строку
            if ( $format == 'default0' )
            {
                $format = 'default1';
            } else
            {
                $format = 'default0';
            }
            foreach($reportrow as $c=>$reportcell)
            {
                if($reportcell['class']=='value')
                {//если класс - обычное значение, применяем чересстрочный формат отображения
                    $reportcell['class']=$format;
                }
                //на текущий лист пишем в нужную чейку текст в нужном формате
                $this->get_current_worksheet()->write_string($r,$c,$reportcell['data'],$this->get_format($reportcell['class']));
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