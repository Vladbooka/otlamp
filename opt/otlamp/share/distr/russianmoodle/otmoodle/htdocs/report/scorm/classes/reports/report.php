<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Отчет по результатам SCORM
 *
 * @package    report
 * @subpackage scorm
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_scorm\reports;

require_once($CFG->libdir.'/excellib.class.php');

defined('MOODLE_INTERNAL') || die();

use report_scorm\reports\formats\basic_report;
use report_scorm\reports\formats\full_report;
use report_scorm\reports\formats\short_report;
use pdf;
use html_table;
use html_writer;
use MoodleExcelWorkbook;

class report
{
    public function get_writer(array $data, $format, $type, $options)
    {
        // Возвращаемый объект
        $writer = null;

        switch ( $format )
        {
            case 'basic_report':
                $writer = new basic_report($data, $type, $options);
                break;

            case 'short_report':
                $writer = new short_report($data, $type, $options);
                break;

            case 'full_report':
                $writer = new full_report($data, $type, $options);
                break;

            default:
                break;
        }

        return $writer;
    }
}

abstract class AbstractReportFactory
{
    protected $format;
    protected $type;
    protected $filename;
    protected $data;
    protected $report = NULL;

    abstract function generate_data($boolean);
    abstract function get_headers();

    private function export_pdf( $dest = 'D' )
    {
        // Сформируем таблицу для вывода в pdf
        $table = new html_table();
        $table->data = $this->report;
        $table->head = $this->get_headers();
        $table->attributes = [
                        'border' => '1'
        ];

        // Переведем таблицу в HTML
        $html_to_pdf = html_writer::table($table);

        // Переведем в PDF и выведем окно сохранения файла
        $pdf = new pdf('L', 'mm', [
            297,
            210
        ], true, 'UTF-8');
        $pdf->SetTitle('report_scorm');
        $pdf->SetSubject('report_scorm');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->setFontSubsetting(true);
        $pdf->SetMargins(20, 10, 10, true);
        $pdf->AddPage();
        $pdf->writeHTML($html_to_pdf);
        $pdf->Output($this->filename . '.pdf', $dest);
    }

    private function export_xls()
    {
        // Создание объекта xls файла
        $workbook = new MoodleExcelWorkbook($this->filename);
        // Задаем название файла
        $workbook->send($this->filename);
        $sheettitle = get_string('report', 'scorm');
        $myxls = $workbook->add_worksheet($sheettitle);

        // Получим хидеры для отчета
        $headers = $this->get_headers();

        // Стили
        $style_header = $workbook->add_format();
        $style_header->set_bold(1);

        if ( ! empty($this->report) )
        {

            $colnum = 0;
            foreach ( $headers as $item )
            {
                $myxls->write(0, $colnum, $item, $style_header);
                $colnum++;
            }
            $rownum = 1;

            foreach ( $this->report as $item)
            {
                $colnum = 0;
                foreach ( $item as $row )
                {
                    $myxls->write($rownum, $colnum, trim(strip_tags($row)));
                    $colnum++;
                }
                $rownum++;
            }

            $workbook->close();
            exit;
        }
    }

    function get_html()
    {
        // Сгенерируем данные
        $this->generate_data(true);

        // Строим таблицу для отображения на странице
        $table = new html_table();
        $table->data = $this->report;
        $table->head = $this->get_headers();
        $table->attributes = [
                        'border' => '1'
        ];
        return html_writer::table($table);
    }

    function export()
    {
        $function = 'export_' . $this->type;
        $this->$function();
    }
}



