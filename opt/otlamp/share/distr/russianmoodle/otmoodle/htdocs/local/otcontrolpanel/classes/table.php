<?php
namespace local_otcontrolpanel;

use renderer_base;
use templatable;

class table implements templatable {

    private $suffix;
    /**
     * @var table_row[]
     */
    private $rows=[];
    /**
     * @var table_column[]
     */
    private $columns=[];

    /**
     * @param \stdClass[] $records
     */
    public function __construct(array $records, $suffix='')
    {
        $this->suffix = $suffix;
        foreach($records as $record)
        {
            $this->add_row(new table_row($record));
        }
    }

    /**
     * @param table_row $tablerow
     * @return int rownum
     */
    public function add_row(table_row $tablerow)
    {
        $this->rows[] = $tablerow;
        return key(array_slice($this->rows, -1));
    }

    /**
     * @param table_column $tablecolumn
     * @return int colnum
     */
    public function add_column(table_column $tablecolumn)
    {
        $this->columns[] = $tablecolumn;
        return key(array_slice($this->columns, -1));
    }

    public function get_rows_count()
    {
        return count($this->rows);
    }

    public function export_for_template(renderer_base $output)
    {
        $table = [
            'id' => spl_object_hash($this).(empty($this->suffix)?'-orig':'-'.$this->suffix),
            'head' => ['rows' => []],
            'body' => ['rows' => []]
        ];

        // Подготовка данных заголовка таблицы
        $headrow = ['cells' => []];
        foreach($this->columns as $colnum => $tablecolumn)
        {
            $columncode = $tablecolumn->get_code();
            if (empty($table['head']['rows']))
            {
                $headrow['cells'][$colnum] = [
                    'columncode' => $columncode,
                    'column_'.$columncode => true,
                    'value' => $tablecolumn->get_display_name()
                ];
            }
        }
        $table['head']['rows'][] = $headrow;

        // Подготовка данных тела таблицы
        foreach($this->rows as $rownum => $tablerow)
        {
            $bodyrow = [
                'id' => $tablerow->get_record()->id ?? null,
                'rownumgt5' => ($rownum >= 5),
                'cells' => [],
            ];
            $colnum = null;
            foreach($this->columns as $colnum => $tablecolumn)
            {
                $columncode = $tablecolumn->get_code();
                $bodyrow['cells'][$colnum] = [
                    'columncode' => $columncode,
                    'column_'.$columncode => true,
                    'value' => $this->get_cell_value($rownum, $colnum),
                    'classes' => $this->get_cell_classes($rownum, $colnum),
                ];
            }
            if (!is_null($colnum))
            {
                $bodyrow['cells'][$colnum]['lastcell'] = true;
            }
            $table['body']['rows'][$rownum] = $bodyrow;
        }
        $table['body']['rowscount'] = count($table['body']['rows']);
        $table['body']['rowscountgt5'] = ($table['body']['rowscount'] > 5);

        return ['table' => $table];


    }

    public function get_cell_value($rownum, $colnum) {
        $tablerow = $this->rows[$rownum];
        $tablecolumn = $this->columns[$colnum];
        return $tablecolumn->get_value($tablerow->get_record(), $rownum.'-'.$colnum);
    }

    public function get_cell_classes($rownum, $colnum) {
        $tablerow = $this->rows[$rownum];
        $tablecolumn = $this->columns[$colnum];
        return $tablecolumn->get_classes($tablerow->get_record());
    }

}