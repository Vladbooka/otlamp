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

// Подключение файла классов работы с XLS
require_once($CFG->libdir.'/excellib.class.php');

// Подключение файла классов работы с PDF
require_once($CFG->libdir.'/pdflib.php');

// Подключение файла классов работы с ODS
require_once($CFG->libdir.'/odslib.class.php');


/**
 * RT отчет. Хелпер работы с экспортом
 *
 * @package    modlib
 * @subpackage rtreport
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_rtreport_helper_exporter
{
    /**
     * Доступные форматы экспорта
     *
     * @var array
     */
    protected static $available_formats = [
        'pdf',
        'xls',
        'ods',
//         // экспорт в csv и docx не реализованы - не ясно почему они здесь есть
//         // из-за них отображаются кнопки экспорта, которые не работают, например рейтинг студентов (кнопка в журнале)
//         'csv',
//         'docx'
    ];

    /**
     * Экспорт в PDF
     *
     * @param array $info
     *
     * @return void
     */
    protected static function export_pdf($info = '')
    {
        // Прямое скачивание
        $dest = 'D';

        // Переведем таблицу в HTML
        $html_to_pdf = html_writer::table($info);

            // Переведем в PDF и выведем окно сохранения файла
        $pdf = new pdf('L', 'mm', [297,210], true, 'UTF-8');

        $pdf->SetTitle('rtreport_' . date('d_m_Y', time()));
        $pdf->SetSubject('rtreport_' . date('d_m_Y', time()));
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->setFontSubsetting(true);
        $pdf->SetMargins(20, 10, 10, true);
        $pdf->AddPage();
        $pdf->writeHTML($html_to_pdf);
        $pdf->Output('rtreport_' . date('d_m_Y', time()) . '.pdf', $dest);
    }

    /**
     * Экспорт в XLS
     *
     * @param array $info
     * @param array $merge_cells
     *
     * @return void
     */
    protected static function export_xls($info = [], $merge_cells = [], $style_cells = [])
    {
        // Создание объекта xls файла
        $workbook = new MoodleExcelWorkbook('rtreport_' . date('d_m_Y', time()));

        // Название файла
        $workbook->send('rtreport_' . date('d_m_Y', time()));
        $sheettitle = 'rtreport_' . date('d_m_Y', time());
        $myxls = $workbook->add_worksheet($sheettitle);

        // Стили
        $style = $workbook->add_format();
        $style->set_v_align('center');
        $style->set_h_align('center');
        $style->set_border(1);
        $style->set_text_wrap();

        if ( ! empty($info) )
        {
            $rownum = 0;
            foreach ( $info as $item)
            {
                $colnum = 0;
                foreach ( $item as $row )
                {
                    $str = $rownum . '_' . $colnum;
                    if ( array_key_exists($str, $style_cells) )
                    {
                        $st = $workbook->add_format();
                        foreach ( $style_cells[$str] as $prop => $value )
                        {
                            $method_name = 'set_' . $prop;
                            $st->$method_name($value);
                        }
                        $myxls->write_string($rownum, $colnum, trim(strip_tags($row)), $st);
                    } else
                    {
                        $myxls->write_string($rownum, $colnum, trim(strip_tags($row)), $style);
                    }
                    $colnum++;

                }
                $rownum++;
            }
        }

        if ( ! empty($merge_cells) && is_array($merge_cells) )
        {// Ячейки, которые необходимо смержить
            foreach ( $merge_cells as $merge_info )
            {
                if ( count($merge_info) == 4 )
                {
                    $myxls->merge_cells(intval($merge_info[0]), intval($merge_info[1]), intval($merge_info[2]), intval($merge_info[3]));
                }
            }
        }

        // Отправка файла пользователю
        $workbook->close();
        exit;
    }

    /**
     * Экспорт в ODS
     *
     * @param array $info
     *
     * @return void
     */
    protected static function export_ods($info = [], $merge_cells = [], $style_cells = [])
    {// Создание объекта xls файла
        $workbook = new MoodleODSWorkbook('rtreport_' . date('d_m_Y', time()));

        // Название файла
        $workbook->send('rtreport_' . date('d_m_Y', time()). '.ods');
        $sheettitle = 'rtreport_' . date('d_m_Y', time());
        $myxls = $workbook->add_worksheet($sheettitle);

        // Стили
        $style = $workbook->add_format();
        $style->set_v_align('center');
        $style->set_h_align('center');
        $style->set_border(1);
        $style->set_text_wrap();

        if ( ! empty($info) )
        {
            $rownum = 0;
            foreach ( $info as $item)
            {
                $colnum = 0;
                foreach ( $item as $row )
                {
                    $str = $rownum . '_' . $colnum;
                    if ( array_key_exists($str, $style_cells) )
                    {
                        $st = $workbook->add_format();
                        foreach ( $style_cells[$str] as $prop => $value )
                        {
                            $method_name = 'set_' . $prop;
                            $st->$method_name($value);
                        }
                        $myxls->write_string($rownum, $colnum, trim(strip_tags($row)), $st);
                    } else
                    {
                        $myxls->write_string($rownum, $colnum, trim(strip_tags($row)), $style);
                    }
                    $colnum++;

                }
                $rownum++;
            }
        }

        if ( ! empty($merge_cells) && is_array($merge_cells) )
        {// Ячейки, которые необходимо смержить
            foreach ( $merge_cells as $merge_info )
            {
                if ( count($merge_info) == 4 )
                {
                    $myxls->merge_cells(intval($merge_info[0]), intval($merge_info[1]), intval($merge_info[2]), intval($merge_info[3]));
                }
            }
        }

        // Отправка файла пользователю
        $workbook->close();
        exit;
    }

    /**
     * Доступные форматы экспорта
     *
     * @param string $type
     * @return bool
     */
    public static function is_valid($type = '')
    {
        if ( ! is_string($type) )
        {
            return false;
        }
        if ( in_array(strtolower($type), static::get_available_formats()) )
        {
            return true;
        }

        return false;
    }

    /**
     * Доступные форматы экспорта
     *
     * @return string[]
     */
    public static function get_available_formats()
    {
        return static::$available_formats;
    }

    /**
     * Экспорт
     *
     * @param string $format
     * @param string $info
     *
     * @return void
     */
    public static function export($format = 'xls', $info = '', $merge_cells = [], $style_cells = [])
    {
        if ( in_array($format, static::get_available_formats()) )
        {
            $function = 'export_' . $format;
            static::{$function}($info, $merge_cells, $style_cells);
        }
    }
}

