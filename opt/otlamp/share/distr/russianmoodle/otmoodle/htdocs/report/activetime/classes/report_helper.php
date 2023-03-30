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
 * Report activetime helper class.
 *
 * @package    report
 * @subpackage activetime
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_activetime;

use MoodleExcelWorkbook;
use MoodleODSWorkbook;
use csv_export_writer;
use html_table;
use html_writer;
use moodle_exception;
use stdClass;
use core_php_time_limit;

class report_helper 
{
    /**
     * Доступные типы экспорта
     * 
     * @var array
     */
    protected static $export_types = ['xlsx', 'ods', 'csv'];
    
    /**
     * Экcпорт в XLSX
     *
     * @param string $data
     *
     * @return void
     */
    protected static function export_xlsx(html_table $table)
    {
        global $CFG;
        
        // Подключение библиотеки xlsx
        require_once($CFG->libdir.'/excellib.class.php');
        
        // If this file was requested from a form, then mark download as complete (before sending headers).
        \core_form\util::form_download_complete();
        
        // Создание объекта xlsx файла
        $workbook = new MoodleExcelWorkbook('report_activetime_' . date('d.m.Y', time()));
        
        // Задаем название файла
        $workbook->send('report_activetime');
        $sheettitle = get_string('pluginname', 'report_activetime');
        $myxlsx = $workbook->add_worksheet($sheettitle);
        
        // Стили
        $style_header = $workbook->add_format();
        $style_header->set_bold(1);
        $style_header->set_align('center');
        $style_header->set_border(1);
        
        $style_col = $workbook->add_format();
        $style_col->set_align('center');
        $style_col->set_border(1);
        
        $style_col_left = $workbook->add_format();
        $style_col_left->set_align('left');
        $style_col_left->set_border(1);
        
        $colnum = 0;
        foreach ( $table->head as $tablecell )
        {
            $myxlsx->write(0, $colnum, trim(strip_tags($tablecell->text)), $style_header);
            $colnum++;
        }
        $rownum = 1;
        
        foreach ( $table->data as $tablerow )
        {
            $colnum = 0;
            foreach ( $tablerow->cells as $tablecell )
            {
                if ( $colnum == 0 )
                {
                    $myxlsx->write($rownum, $colnum, trim(strip_tags($tablecell->text)), $style_col_left);
                } else 
                {
                    $myxlsx->write($rownum, $colnum, trim(strip_tags($tablecell->text)), $style_col);
                }
                $myxlsx->set_column($colnum, $colnum+1, 30);
                $colnum++;
            }
            $rownum++;
        }
        $workbook->add_format();
        
        $merge_cells = self::merge_cells($table);
        if ( ! empty($merge_cells) && is_array($merge_cells) )
        {// Ячейки, которые необходимо смержить
            foreach ( $merge_cells as $merge_info )
            {
                if ( count($merge_info) == 4 )
                {
                    $myxlsx->merge_cells(intval($merge_info[0]), intval($merge_info[1]), intval($merge_info[2]), intval($merge_info[3]));
                }
            }
        }
        
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
    protected static function export_ods(html_table $table)
    {
        global $CFG;
        
        // Подключение файла классов работы с ODS
        require_once($CFG->libdir.'/odslib.class.php');
        
        // Создание объекта xlsx файла
        $workbook = new MoodleODSWorkbook('report_activetime' . date('d.m.Y', time()));
        
        // Задаем название файла
        $workbook->send('report_activetime.ods');
        $sheettitle = get_string('pluginname', 'report_activetime');
        $myxlsx = $workbook->add_worksheet($sheettitle);
        
        // Стили
        $style_header = $workbook->add_format();
        $style_header->set_bold(1);
        $style_header->set_align('center');
        $style_header->set_border(1);
        
        $style_col = $workbook->add_format();
        $style_col->set_align('center');
        $style_col->set_border(1);
        
        $style_col_left = $workbook->add_format();
        $style_col_left->set_align('left');
        $style_col_left->set_border(1);
        
        $colnum = 0;
        foreach ( $table->head as $tablecell )
        {
            $myxlsx->write(0, $colnum, trim(strip_tags($tablecell->text)), $style_header);
            $colnum++;
        }
        $rownum = 1;
        
        foreach ( $table->data as $tablerow )
        {
            $colnum = 0;
            foreach ( $tablerow->cells as $tablecell )
            {
                if ( $colnum == 0 )
                {
                    $myxlsx->write($rownum, $colnum, trim(strip_tags($tablecell->text)), $style_col_left);
                } else
                {
                    $myxlsx->write($rownum, $colnum, trim(strip_tags($tablecell->text)), $style_col);
                }
                $myxlsx->set_column($colnum, $colnum+1, 30);
                $colnum++;
            }
            $rownum++;
        }
        $workbook->add_format();
        
        $merge_cells = self::merge_cells($table);
        if ( ! empty($merge_cells) && is_array($merge_cells) )
        {// Ячейки, которые необходимо смержить
            foreach ( $merge_cells as $merge_info )
            {
                if ( count($merge_info) == 4 )
                {
                    $myxlsx->merge_cells(intval($merge_info[0]), intval($merge_info[1]), intval($merge_info[2]), intval($merge_info[3]));
                }
            }
        }
        
        $workbook->close();
        exit;
    }
    
    /**
     * Экcпорт в CSV
     *
     * @param string $data
     *
     * @return void
     */
    protected static function export_csv(html_table $table)
    {
        global $CFG;
        
        // Подключение библиотеки работы с CSV
        require_once($CFG->libdir . '/csvlib.class.php');
        
        // Создание объекта xlsx файла
        $csv = new csv_export_writer('semicolon');
        
        foreach($table->head as $tablecell)
        {
            $head[] = strip_tags($tablecell->text);
        }
        $csv->add_data($head);
        
        foreach($table->data as $tablerow)
        {
            foreach($tablerow->cells as $tablecell)
            {
                $row[] = strip_tags($tablecell->text);
            }
            $csv->add_data($row);
            $row = [];
        }
        
        // Название файла
        $csv->set_filename('report_activetime' . date('d.m.Y', time()));
        
        // Скачивание csv файла
        $csv->download_file();
    }
    
    /**
     * Получение типов экспорта для селекта
     * 
     * @return string[]
     */
    public static function get_export_types_select()
    {
        $string_types = [];
        foreach ( self::$export_types as $type )
        {
            $string_types[$type] = get_string('export_' . $type, 'report_activetime');
        }
        
        return $string_types;
    }
    
    /**
     * Экcпорт
     *
     * @param string $type
     *
     * @return void
     */
    public static function export($type = null, html_table $table)
    {
        if ( ! empty($type) )
        {
            if ( in_array($type, self::$export_types) )
            {
                $method_name = 'export_' . $type;
                self::$method_name($table);
            } else 
            {// Пока только XLSX
                $method_name = 'export_xlsx';
                self::$method_name($table);
            }
        }
    }
    
    /**
     * Определить матрицу для объединения ячеек по вертикали
     * @param html_table $table
     * @return array
     */
    private static function merge_cells(html_table $table)
    {
        $mergetable = [];
        foreach($table->data as $rk => $tablerow)
        {
            foreach($tablerow->cells as $ck => $tablecell)
            {
                if( isset($tablecell->rowspan) )
                {
                    $mergetable[] = [$rk + 1, $ck, $rk + $tablecell->rowspan, $ck];
                }
            }
        }
        return $mergetable;
    }
}