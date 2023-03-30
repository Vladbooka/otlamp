<?php
use Mpdf\Mpdf;

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
 * Блок объединения отчетов. Класс формирования данных в формате pdf.
 * 
 * @package    block
 * @subpackage reports_union
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/report/mods_data/classes/format/html.php');
//используем не мудловскую pdf-библиотеку, потому как мудловская не умеет отображать мудловские картинки, требующие авторизации
require_once($CFG->dirroot.'/report/mods_data/classes/lib/mpdf-7.1.6/src/Mpdf.php');

class report_mods_data_format_pdf
{
    private $reportdata=[];
    private $filename = '';
    private $pdf='';

    /**
     * Конструктор
     * 
     * array $reportdata -  Двумерный массив с данными для отображения. 
     *                      Каждая ячейка - массив вида ['data'=>'данные','class'=>'класс для оформления']
     */
    public function __construct($reportdata)
    {
        $this->reportdata = $reportdata;
        $this->filename = 'unionreport'.date('m.d.y').'.pdf';
        $this->pdf = new Mpdf();
    }

    /**
     * Генерация данных для отчета
     */
    protected function generate_report()
    {
        $html = new report_mods_data_format_html($this->reportdata);
        //получим отчет в формате html
        $htmlreport = $html->get_report();
        //запишем полученную html-таблицу в нашу pdf
        $this->pdf->WriteHTML($htmlreport);
    }

    /**
     * Формирует отчет и запускает его скачивание в формате pdf
     *
     */
    public function print_report()
    {
        $this->generate_report();
        //запускаем скачивание отчета в формате pdf
        $this->pdf->Output($this->filename, 'D');
    }
}








// require_once($CFG->dirroot.'/blocks/reports_union/classes/dompdf/autoload.inc.php');
// use Dompdf\Dompdf;
// class block_reports_union_format_pdf
// {
//     private $reportdata=[];
//     private $filename = '';
//     private $pdf='';

//     /**
//      * Конструктор
//      */
//     public function __construct($reportdata)
//     {
//         $this->reportdata = $reportdata;
//         $this->filename = 'unionreport'.date('m.d.y').'.pdf';
//         $this->pdf = new Dompdf();
//         // (Optional) Setup the paper size and orientation
//         $this->pdf->setPaper('A4', 'landscape');
        
//     }

//     public function generate_report()
//     {
//         $html = new block_reports_union_format_html($this->reportdata);
//         $htmlreport = $html->get_report();
//         $this->pdf->loadHtml($htmlreport);
        
//         // Render the HTML as PDF
//         $this->pdf->render();
        
//     }

//     public function print_report()
//     {
//         $this->generate_report();
//         // Output the generated PDF to Browser
//         $this->pdf->stream($this->filename);
//     }
// }







// require_once ($CFG->libdir . '/pdflib.php');

// class block_reports_union_format_pdf
// {
//     private $reportdata=[];
//     private $filename = '';
//     private $pdf='';

//     /**
//      * Конструктор
//      */
//     public function __construct($reportdata)
//     {
//         $this->reportdata = $reportdata;
//         $this->filename = 'unionreport'.date('m.d.y').'.pdf';
//         $this->pdf = new pdf('L', 'mm', [
//             297,
//             210
//         ], true, 'UTF-8');
//         $this->pdf->SetTitle($name);
//         $this->pdf->SetSubject($name);
//         $this->pdf->setPrintHeader(false);
//         $this->pdf->setPrintFooter(false);
//         $this->pdf->SetAutoPageBreak(true, 10);
//         $this->pdf->setFontSubsetting(true);
//         $this->pdf->SetMargins(20, 10, 10, true);
//     }
    
//     public function generate_report()
//     {
//         $html = new block_reports_union_format_html($this->reportdata);
//         $htmlreport = $html->get_report();
        
//         //посчитаем сколько ячеек в строке нашего отчета
//         $cellsinrow = count($this->reportdata[0]);
//         //примерно 10 ячеек нормально помещаются в ландшафтную ориентацию А4, посчитаем коэффициент
//         $kwide = ceil($cellsinrow/10);
        
//         $this->pdf->AddPage('L',[$kwide*297, $kwide*210]);
        
//         $this->pdf->writeHTML($htmlreport);
//     }
    
//     public function print_report()
//     {
//         $this->generate_report();
//         $this->pdf->lastPage();
//         $this->pdf->Output($this->filename, 'D');
//     }
// }