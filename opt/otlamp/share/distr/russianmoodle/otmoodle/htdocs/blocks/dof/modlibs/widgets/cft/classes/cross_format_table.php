<?php

require_once(dirname(realpath(__FILE__)) . '/cross_format_table_cell.php');
require_once(dirname(realpath(__FILE__)) . '/cross_format_table_cell_style.php');

class dof_cross_format_table
{
    protected $name;
    protected $tablecells = [[]];
    protected $cellstyles = [[]];
    protected $defaultcellstyle;
    protected $columns_width = [];
    protected $rows_height = [];
    protected $rownum = 0;
    protected $cellnum = 0;

    public function __construct($name='table', $defaultcellstyle = null)
    {
        $this->name = (string)$name;
        if( ! is_null($defaultcellstyle) && $defaultcellstyle instanceof dof_cross_format_table_cell_style )
        {
            $defaultstyle = clone($defaultcellstyle);
            $this->set_default_cell_style($defaultstyle);
        } else
        {
            $this->defaultcellstyle = null;
        }
    }

    public function set_default_cell_style(dof_cross_format_table_cell_style $defaultcellstyle)
    {
        $this->defaultcellstyle = $defaultcellstyle;
    }

    public function increase_rownum($length = 1)
    {
        $this->rownum += (int)$length;
        $this->cellnum = 0;
    }

    public function set_rownum($rownum)
    {
        $this->rownum = (int)$rownum;
    }

    public function set_cellnum($cellnum)
    {
        $this->cellnum = (int)$cellnum;
    }

    public function get_max_rownum()
    {
        return max(array_keys($this->tablecells));
    }

    public function get_rownum()
    {
        return $this->rownum;
    }

    public function get_cellnum()
    {
        return $this->cellnum;
    }

    /**
     * @param int $rownum
     * @param int $cellnum
     * @return dof_cross_format_table_cell
     */
    public function add_cell($text='', $rownum=null, $cellnum=null)
    {
        if( is_null($rownum) )
        {
            $rownum = $this->rownum;
        } else
        {
            $this->rownum = $rownum;
        }
        if( is_null($cellnum) )
        {
            $cellnum = $this->cellnum++;
        } else
        {
            $this->cellnum = $cellnum+1;
        }
        if( ! isset($this->tablecells[(int)$rownum][(int)$cellnum]) )
        {
            $cell = new dof_cross_format_table_cell((string)$text);
            if( ! empty($this->defaultcellstyle) )
            {
                $cell->set_style(clone($this->defaultcellstyle));
            }
            $this->tablecells[(int)$rownum][(int)$cellnum] = $cell;
        } else
        {
            $this->tablecells[(int)$rownum][(int)$cellnum]->set_text($text);
        }
        return $this->tablecells[(int)$rownum][(int)$cellnum];
    }

    public function add_row_of_cells($cellstext)
    {
        foreach($cellstext as $celltext)
        {
            $this->add_cell($celltext);
        }
        $this->increase_rownum();
    }

    public function set_style(dof_cross_format_table_cell_style $style, $rownum1 = null, $cellnum1 = null, $rownum2=null, $cellnum2=null)
    {
        if( is_null($rownum1) )
        {
            $rownum1 = $this->rownum;
        }
        if( is_null($cellnum1) )
        {
            $cellnum1 = $this->cellnum;
        }
        if( is_null($rownum2) )
        {
            $rownum2 = $rownum1;
        }
        if( is_null($cellnum2) )
        {
            $cellnum2 = $cellnum1;
        }
        for($r = $rownum1; $r<=$rownum2; $r++)
        {
            for($c = $cellnum1; $c<=$cellnum2; $c++)
            {
                $this->cellstyles[$r][$c] = clone($style);
            }
        }
    }

    protected function apply_stored_style()
    {
        foreach($this->cellstyles as $rownum => $rowcells)
        {
            foreach($rowcells as $cellnum => $cellstyle)
            {
                if (isset($this->tablecells[$rownum][$cellnum]))
                {
                    $this->tablecells[$rownum][$cellnum]->set_style($cellstyle);
                }
            }
        }
    }

    protected function get_max_row_cells()
    {
        $max = 0;
        foreach($this->tablecells as $rownum => $rowcells)
        {
            $rowcellscount = 0;
            foreach($rowcells as $cellnum => $cell)
            {
                $rowcellscount += $cell->get_colspan();
            }
            if($rowcellscount > $max)
            {
                $max = $rowcellscount;
            }
        }
        return $max;
    }

    protected function add_empty_rowspan_cells()
    {
        // сортировка строк по порядку
        ksort($this->tablecells);

        foreach($this->tablecells as $rownum => $rowcells)
        {
            foreach($rowcells as $cellnum => $cell)
            {
                $rowspan = $cell->get_rowspan();
                if($rowspan > 1)
                {
                    $rowspaninitrow = $rownum;
                    $cell->set_data('rowspan', ($rownum - $rowspaninitrow + 1).'of'.$rowspan);
                    for( $r = ($rownum + 1); $r < ($rownum + $cell->get_rowspan()); $r++ )
                    {
                        $emptycell = $this->add_cell('', $r, $cellnum);
                        $emptycell->set_style($cell->get_style_clone());
                        if ($cell->get_colspan() > 1)
                        {
                            $emptycell->set_colspan($cell->get_colspan());
                        }
                        $emptycell->set_data('rowspan', ($r - $rowspaninitrow + 1).'of'.$rowspan);
                    }
                }
            }
            // сортировка ячеек в строке по порядку
            ksort($this->tablecells[$rownum]);
        }
    }

    protected function add_empty_colspan_cells()
    {
        // сортировка строк по порядку
        ksort($this->tablecells);

        foreach($this->tablecells as $rownum => $rowcells)
        {
            foreach($rowcells as $cellnum => $cell)
            {
                $colspan = $cell->get_colspan();
                if ($colspan > 1)
                {
                    $colspaninitrow = $cellnum;
                    $cell->set_data('colspan', ($cellnum - $colspaninitrow + 1).'of'.$colspan);
                    // добавление неописанных ячеек с пустым текстом
                    for( $c = ($cellnum + 1); $c < ($cellnum + $colspan); $c++ )
                    {
                        $emptycell = $this->add_cell('', $rownum, $c);
                        $emptycell->set_style($cell->get_style_clone());
                        $emptycell->set_data('colspan', ($c - $colspaninitrow + 1).'of'.$colspan);
                    }
                }
            }
            // сортировка ячеек в строке по порядку
            ksort($this->tablecells[$rownum]);
        }
    }

    protected function add_empty_missed_cells()
    {
        // сортировка строк по порядку
        ksort($this->tablecells);
        // поиск максимального количества ячеек в строке
        $maxrowcells = $this->get_max_row_cells();
        $skipcells = [];

        foreach($this->tablecells as $rownum => $rowcells)
        {
            // добавление неописанных ячеек с пустым текстом
            for($cellnum = 0; $cellnum < $maxrowcells; $cellnum++)
            {
                if (isset($this->tablecells[$rownum][$cellnum]))
                {
                    $cell = $this->tablecells[$rownum][$cellnum];
                    if ($cell->get_colspan() > 1 || $cell->get_rowspan() > 1)
                    {
                        // для добавления фантомных ячеек для объединенных ячеек следует использовать специальные методы
                        // здесь мы объединенные ячейки будем пропускать, для них несуществующие ячейки добавляться не будут
                        for($r = $rownum; $r < ($rownum + $cell->get_rowspan()); $r++ )
                        {
                            for($c = $cellnum; $c < ($cellnum + $cell->get_colspan()); $c++ )
                            {
                                $skipcells[$r][$c] = true;
                            }
                        }
                    }
                }
                if( ! isset($this->tablecells[$rownum][$cellnum]) && empty($skipcells[$rownum][$cellnum]) )
                {
                    $this->add_cell('', $rownum, $cellnum);
                }
            }

            // сортировка ячеек в строке по порядку
            ksort($this->tablecells[$rownum]);
        }
    }

    public function get_name()
    {
        return $this->name;
    }

    public function get_html($calcsize = true, $options=[])
    {
        $this->add_empty_missed_cells();
        $this->apply_stored_style();


        $htmltable = new html_table();
        if (isset($options['tableattributes']))
        {
            $htmltable->attributes = $options['tableattributes'];
        }

        if (!isset($htmltable->attributes['style']))
        {
            $htmltable->attributes['style'] = '';
        }
        $htmltable->attributes['style'] .= 'border-spacing: 0; border-collapse: collapse; width: 100%;';

        $deniedstyles = [];
        if (isset($options['deniedstyles']))
        {
            $deniedstyles = $options['deniedstyles'];
        }

        foreach($this->tablecells as $rownum => $rowcells)
        {
            $htmltablerow = new html_table_row();
            /* @var dof_cross_format_table_cell $cell */
            foreach($rowcells as $cellnum => $cell)
            {
                $celltext = $cell->get_text();
                if (!empty($options['striptags']))
                {
                    $celltext = strip_tags($celltext);
                }
                $htmltablecell = new html_table_cell($celltext);

                // объединение ячеек по вертикали
                if( $cell->get_rowspan() > 1 )
                {
                    $htmltablecell->rowspan = $cell->get_rowspan();
                }

                // объединение ячеек по горизонтали
                if( $cell->get_colspan() > 1 )
                {
                    $htmltablecell->colspan = $cell->get_colspan();
                }

                // применение стилей
                $htmltablecell->style = $cell->get_style_attribute($deniedstyles);

                $fontweight = $cell->get_font_weight();
                if( $fontweight == 'bold' || (int)$fontweight > 600 )
                {
                    $htmltablecell->text = "<strong>".$htmltablecell->text."</strong>";
                }
                $htmltablerow->cells[$cellnum] = $htmltablecell;
            }
            $htmltable->data[$rownum] = $htmltablerow;
        }

        if( $calcsize )
        {
            // применение ширин колонок пропорционально формату в excel
            $size = [];
            $maxrowcells = $this->get_max_row_cells();
            $sumsize = 0;
            for($i=0; $i<=$maxrowcells; $i++)
            {
                if( ! isset($this->columns_width[$i]) )
                {
                    $this->columns_width[$i] = 10;
                }
                $sumsize += $this->columns_width[$i];
            }
            for($i=0; $i<=$maxrowcells; $i++)
            {
                $size[] = $this->columns_width[$i] * 100 / $sumsize . '%';
            }
            $htmltable->size = $size;
        }

        if (!isset($options['prehtml']))
        {
            $options['prehtml'] = '';
        }
        if (!isset($options['posthtml']))
        {
            $options['posthtml'] = '';
        }

        return $options['prehtml'] . dof_html_writer::table($htmltable) . $options['posthtml'];
    }

    public function add_docx_table(&$section)
    {
        $this->add_empty_rowspan_cells();
        $this->add_empty_missed_cells();
        $this->apply_stored_style();

        $sectionstyle = $section->getStyle();
        $sectionwidth = $sectionstyle->getPageSizeW() - $sectionstyle->getMarginRight() - $sectionstyle->getMarginLeft();

        $table = $section->addTable(['width' => $sectionwidth]);

        // вычисление ширин колонок в процентах, пропорционально указанному формату для excel
        $sumsize = 0;
        for($i=0; $i < $this->get_max_row_cells(); $i++)
        {
            if (!isset($this->columns_width[$i]))
            {
                $this->columns_width[$i] = 10;
            }
            $sumsize += $this->columns_width[$i];
        }

        foreach($this->tablecells as $rownum => $rowcells)
        {
            $table->addRow();
            /* @var dof_cross_format_table_cell $cell */
            foreach($rowcells as $cellnum => $cell)
            {

                $style = [];

                $cellwidth = $this->columns_width[$cellnum] / $sumsize * 100;

                if( $cell->get_colspan() > 1 )
                {
                    $style['gridSpan'] = $cell->get_colspan();
                    for($i = ($cellnum+1); $i < ($cellnum+$cell->get_colspan()); $i++)
                    {
                        $cellwidth += $this->columns_width[$i] / $sumsize * 100;
                    }
                }

                $rowspandata = $cell->get_data('rowspan');
                if (!is_null($rowspandata))
                {
                    if (explode('of', $rowspandata)[0] == '1')
                    {
                        $style['vMerge'] = \PhpOffice\PhpWord\Style\Cell::VMERGE_RESTART;
                    } else
                    {
                        $style['vMerge'] = \PhpOffice\PhpWord\Style\Cell::VMERGE_CONTINUE;
                        // в примере было именно так, но в LibreOffice в таком случае открывается коряво
                        // $cellwidth = null;
                    }
                }


                // установка цвета фона ячейки
                $bgcolor = $cell->get_background_color();
                $style['bgColor'] =
                str_pad(dechex($bgcolor[0]), 2, '0', STR_PAD_LEFT) .
                str_pad(dechex($bgcolor[1]), 2, '0', STR_PAD_LEFT) .
                str_pad(dechex($bgcolor[2]), 2, '0', STR_PAD_LEFT);


                // установка цвета текста ячейки
                $color = $cell->get_color();
                $style['color'] =
                str_pad(dechex($color[0]), 2, '0', STR_PAD_LEFT) .
                str_pad(dechex($color[1]), 2, '0', STR_PAD_LEFT) .
                str_pad(dechex($color[2]), 2, '0', STR_PAD_LEFT);

                // установка толщины рамки ячейки
                $style['borderSize'] = $cell->get_border_width();

                // установка выравнивания текста по вертикали
                switch($cell->get_vertical_align())
                {
                    case 'middle':
                        $style['valign'] = \PhpOffice\PhpWord\Style\Cell::VALIGN_CENTER;
                        break;
                    case 'bottom':
                        $style['valign'] = \PhpOffice\PhpWord\Style\Cell::VALIGN_BOTTOM;
                        break;
                    case 'top':
                    default:
                        $style['valign'] = \PhpOffice\PhpWord\Style\Cell::VALIGN_TOP;
                        break;
                }

                $doctablecell = $table->addCell($sectionwidth / 100 * $cellwidth, $style);

                if (!isset($style['vMerge']) || $style['vMerge'] != \PhpOffice\PhpWord\Style\Cell::VMERGE_CONTINUE)
                {
                    $fontstyle = [];
                    $paragraphstyle = [];

                    // установка оформления текста
                    switch($cell->get_text_decoration())
                    {
                        case 'line-through':
                            // зачеркивание
                            $fontstyle['strikethrough'] = true;
                            break;
                        case 'none':
                        default:
                            break;
                    }

                    // установка размера шрифта текста в ячейке
                    $fontstyle['size'] = $cell->get_font_size() * 0.75;

                    // установка жирности
                    $fontweight = $cell->get_font_weight();
                    if( $fontweight == 'bold' || (int)$fontweight > 600 )
                    {
                        $fontstyle['bold'] = true;
                    }

                    // установка выравнивания текста по горизонтали
                    switch($cell->get_text_align())
                    {
                        case 'center':
                            $paragraphstyle['alignment'] = \PhpOffice\PhpWord\SimpleType\JcTable::CENTER;
                            break;
                        case 'right':
                            $paragraphstyle['alignment'] = \PhpOffice\PhpWord\SimpleType\JcTable::END;
                            break;
                        case 'justify':
                        case 'left':
                        default:
                            $paragraphstyle['alignment'] = \PhpOffice\PhpWord\SimpleType\JcTable::START;
                            break;
                    }


                    $doctablecell->addText(strip_tags($cell->get_text()), $fontstyle, $paragraphstyle); //.' '$cellwidth.' ('.$cell->get_colspan().')'.' ('.$rowspandata.')'
                }
            }
        }
    }

    public function print_docx()
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        $this->add_docx_table($section);

        $file = $this->name . '.docx';
        header("Content-Description: File Transfer");
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $xmlWriter->save("php://output");
        die;
    }

    public function print_html($options=[])
    {
        $htmloptions = [];
        if (isset($options['prehtml']))
        {
            $htmloptions['prehtml'] = $options['prehtml'];
        }
        if (isset($options['posthtml']))
        {
            $htmloptions['posthtml'] = $options['posthtml'];
        }
        echo $this->get_html(true, $htmloptions);
    }

    public function print_pdf($orientation='P', $options=[])
    {
        global $CFG;
        $htmloptions = [
            'tableattributes' => [
                'cellspacing' => '0',
                'cellpadding' => '1',
                'border' => '1'
            ],
            'deniedstyles' => [
                'border-width',
                'border-style',
                'border-color'
            ],
            'striptags' => true
        ];
        if (isset($options['prehtml']))
        {
            $htmloptions['prehtml'] = $options['prehtml'];
        }
        if (isset($options['posthtml']))
        {
            $htmloptions['posthtml'] = $options['posthtml'];
        }
        $html = $this->get_html(false, $htmloptions);

        // Подключение файла классов работы с PDF
        require_once($CFG->libdir.'/pdflib.php');

        ob_clean();
        $pdf = new pdf($orientation);
        $pdf->SetTitle($this->name);
        $pdf->SetSubject($this->name);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setFontSubsetting(true);
        $pdf->SetMargins(20, 10, 10, true);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();
        $pdf->writeHTML($html);
        $pdf->Output($this->name . '.pdf', 'D');
        exit;
    }

    public function print_xls($sheetname = null, $options=[])
    {
        global $CFG;
        // Подключение файла классов работы с XLS
        require_once($CFG->libdir.'/excellib.class.php');
        $workbook = new MoodleExcelWorkbook($this->name);

        /* @var MoodleExcelWorksheet $sheet */
        $sheetoptions = [];

        if( isset($options['sheet__orientation']) )
        {
            $sheetoptions['orientation'] = $options['sheet__orientation'];
        }
        if( isset($options['sheet__papersize']) )
        {
            $sheetoptions['papersize'] = $options['sheet__papersize'];
        }
        if( isset($options['sheet__print_areas']) && is_array($options['sheet__print_areas']) )
        {
            $sheetoptions['print_areas'] = $options['sheet__print_areas'];
        }
        if( isset($options['sheet__fit_to_width']) )
        {
            $sheetoptions['fit_to_width'] = $options['sheet__fit_to_width'];
        }
        if( isset($options['sheet__fit_to_height']) )
        {
            $sheetoptions['fit_to_height'] = $options['sheet__fit_to_height'];
        }
        if( isset($options['sheet__rows_repeat_at_top']) )
        {
            $sheetoptions['rows_repeat_at_top'] = $options['sheet__rows_repeat_at_top'];
        }
        $sheet = $this->add_xls_sheet($workbook, $sheetname, $sheetoptions);

        // Отправка файла пользователю
        $workbook->close();
        exit;
    }

    public function print_csv()
    {
        $this->add_empty_rowspan_cells();
        $this->add_empty_colspan_cells();
        $this->add_empty_missed_cells();

        $content = '';

        $fp = fopen('php://temp', 'r+');
        foreach($this->tablecells as $rownum => $rowcells)
        {
            $csvrow = [];
            /* @var dof_cross_format_table_cell $cell */
            foreach($rowcells as $cellnum => $cell)
            {
                $csvrow[] = strip_tags($cell->get_text());
            }
            // Сохранение данных во временное хранилище в формате csv
            fputcsv($fp, $csvrow, ';');
        }
        rewind($fp);
        while( ! feof($fp) )
        {
            $content .= fread($fp, 8192);
        }
        // Закрытие временного хранилища
        fclose($fp);


        // Прописываем заголовки для скачивания файла
        header('Content-Description: File Transfer');
        header("Content-Type: application/octet-stream");
        header('Content-disposition: extension-token; filename=' . $this->name . '.csv');
        echo $content;
        exit;
    }

    protected function set_sheet_options(&$sheet, $options=[])
    {
        global $DOF;
        require_once $DOF->plugin_path('modlib', 'phpexcel', '/lib/PHPExcel/Worksheet/PageSetup.php');

        $refsheet = new ReflectionObject($sheet);
        $prop = $refsheet->getProperty('worksheet');
        $prop->setAccessible(true);
        /** @var PHPExcel_Worksheet $hackedsheet */
        $hackedsheet = $prop->getValue($sheet);

        $sheetpagesetup = $hackedsheet->getPageSetup();

        $sheetpagesetup->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_DEFAULT);
        if( isset($options['orientation']) )
        {// установка ориентации страницы
            if( $options['orientation'] == 'L' )
            {
                $sheetpagesetup->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
            }
            elseif ( $options['orientation'] == 'P' )
            {
                $sheetpagesetup->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
            }
        }

        if( isset($options['papersize']) )
        {
            if( $options['papersize'] == 'A4' )
            {// установка размера страницы A4
                $sheetpagesetup->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
            }
            else
            {
                $sheetpagesetup->setPaperSize($options['papersize']);
            }
        }

        if( isset($options['print_areas']) && is_array($options['print_areas']) )
        {
            foreach($options['print_areas'] as $index => $printarea)
            {
                $sheetpagesetup->addPrintAreaByColumnAndRow(
                    // column1
                    $printarea[1],
                    // row1
                    $printarea[0]+1,
                    // column2
                    $printarea[3],
                    // row2
                    $printarea[2]+1,
                    $index
                );
            }
        }

        if( isset($options['rows_repeat_at_top']) && is_array($options['rows_repeat_at_top']))
        {
            $sheetpagesetup->setRowsToRepeatAtTopByStartAndEnd(
                // номер первой строки
                $options['rows_repeat_at_top'][0] + 1,
                // количество строк
                $options['rows_repeat_at_top'][0] + $options['rows_repeat_at_top'][1]
            );
        }

        if( isset($options['fit_to_width']) )
        {
            $sheetpagesetup->setFitToWidth((int)$options['fit_to_width']);
        }

        if( isset($options['fit_to_height']) )
        {
            $sheetpagesetup->setFitToHeight((int)$options['fit_to_height']);
        }

        $prop->setValue($sheet, $hackedsheet);
    }

    public function add_xls_sheet(&$workbook, $name=null, $options=[])
    {
        $this->add_empty_rowspan_cells();
        $this->add_empty_missed_cells();
        $this->apply_stored_style();

        if( is_null($name) )
        {
            $name = $this->name;
        }
        /* @var MoodleExcelWorksheet $sheet */
        $sheet = $workbook->add_worksheet($name);

        if( ! empty($options) )
        {
            $this->set_sheet_options($sheet, $options);
        }

        foreach($this->columns_width as $column => $width)
        {
            $sheet->set_column($column, $column, $width);
        }

        foreach($this->rows_height as $row => $height)
        {
            $sheet->set_row($row, $height);
        }

        foreach($this->tablecells as $rownum => $rowcells)
        {
            /* @var dof_cross_format_table_cell $cell */
            foreach($rowcells as $cellnum => $cell)
            {

                if( $cell->get_colspan() > 1 )
                {
                    $mergetocol = $cellnum + $cell->get_colspan() - 1;
                } else
                {
                    $mergetocol = $cellnum;
                }

                if( $cell->get_rowspan() > 1 )
                {
                    $mergetorow = $rownum + $cell->get_rowspan() - 1;
                } else
                {
                    $mergetorow = $rownum;
                }

                if( $mergetocol > $cellnum || $mergetorow > $rownum )
                {
                    $sheet->merge_cells(
                        $rownum, $cellnum,
                        $mergetorow, $mergetocol
                    );
                }

                $style = new MoodleExcelFormat();

                // установка цвета фона ячейки
                $bgcolor = $cell->get_background_color();
                $style->set_bg_color('#' .
                    str_pad(dechex($bgcolor[0]), 2, '0', STR_PAD_LEFT) .
                    str_pad(dechex($bgcolor[1]), 2, '0', STR_PAD_LEFT) .
                    str_pad(dechex($bgcolor[2]), 2, '0', STR_PAD_LEFT));


                // установка цвета текста ячейки
                $color = $cell->get_color();
                $style->set_color('#' .
                    str_pad(dechex($color[0]), 2, '0', STR_PAD_LEFT) .
                    str_pad(dechex($color[1]), 2, '0', STR_PAD_LEFT) .
                    str_pad(dechex($color[2]), 2, '0', STR_PAD_LEFT));

                // установка толщины рамки ячейки
                if($cell->get_border_width() == 1)
                {
                    $style->set_border(1);
                }
                elseif($cell->get_border_width() > 1)
                {
                    $style->set_border(2);
                }

                // установка оформления текста
                switch($cell->get_text_decoration())
                {
                    case 'line-through':
                        // зачеркивание
                        $style->set_strikeout();
                        break;
                    case 'none':
                    default:
                        break;
                }

                // установка выравнивания текста по горизонтали
                switch($cell->get_text_align())
                {
                    case 'center':
                        $style->set_align('center');
                        break;
                    case 'right':
                        $style->set_align('right');
                        break;
                    case 'justify':
                        $style->set_align('justify');
                        break;
                    case 'left':
                    default:
                        $style->set_align('left');
                        break;
                }

                // установка выравнивания текста по вертикали
                switch($cell->get_vertical_align())
                {
                    case 'middle':
                        $style->set_v_align('center');
                        break;
                    case 'bottom':
                        $style->set_v_align('bottom');
                        break;
                    case 'top':
                    default:
                        $style->set_v_align('top');
                        break;
                }

                // установка размера шрифта текста в ячейке
                $style->set_size($cell->get_font_size() * 0.75);

                // установка переносов текста
                switch($cell->get_word_break())
                {
                    case 'normal':
                        break;
                    case 'break-all':
                    default:
                        $style->set_text_wrap();
                        break;
                }

                // установка жирности
                $fontweight = $cell->get_font_weight();
                if( $fontweight == 'bold' || (int)$fontweight > 600 )
                {
                    $style->set_bold();
                }

                $sheet->write_string(
                    $rownum, $cellnum,
                    strip_tags($cell->get_text()),
                    $style
                );
            }
        }

        return $sheet;
    }

    public function set_column_width($width, $columnnum, $length=1)
    {
        for($i = 0; $i < $length; $i++)
        {
            $this->columns_width[$columnnum + $i] = $width;
        }
    }

    public function set_columns_width($columnswidth)
    {
        foreach($columnswidth as $columnnum => $width)
        {
            $this->columns_width[$columnnum] = $width;
        }
    }

    public function set_row_height($height, $rownum, $length=1)
    {
        for($i = 0; $i < $length; $i++)
        {
            $this->rows_height[$rownum + $i] = $height;
        }
    }


}

/**
 * Автозагрузчик PHPWord библиотеки
 */
spl_autoload_register(function ($classname)
{
    if ( strpos($classname, 'PhpOffice') !== false )
    {
        global $CFG;
        $classname = str_replace('PhpOffice\\', '', $classname);
        $filepath =  $CFG->dirroot . '/local/opentechnology/component/phpword/classes/src/' . str_replace('\\', '/', $classname) . '.php';
        if ( file_exists($filepath) )
        {
            require_once ($filepath);
        }
    }
});