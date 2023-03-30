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
 * Подготовка данных для рендеринга темплейта с технической информацией
 *
 * @package    local_crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_opentechnology\output;
defined('MOODLE_INTERNAL') || die();

use renderer_base;
use templatable;
use renderable;
use html_table;
use html_table_row;
use html_table_cell;
use html_writer;

class techinfo implements renderable,templatable {

    /**
     * @var array массив отображаемых таблиц вида:
     * [0] => array(
     *     'header'=> 'заголовок', //<h4> перед таблицей
     *     'table'=> array(
     *         'type'=>'тип',// например, main
     *         'info'=>array(), //структурированные данные таблицы
     *     )
     *     'alert'=>'alert' //дополнительно выводящийся комментарий или предупреждение
     * )
     */


    public $tables;

    /**
     * @ var array массив расположения таблиц на странице технической поддержки, ключами которого являются тип таблицы, а значениями - колонка (right/left)
     */

    protected $locations;

    /**
     * @ var array массив весов таблиц на странице технической поддержки, ключами которого являются тип таблицы, а значениями - колонка (right/left)
     */
    protected $weights;

    /**
     * Конструктор
     */
    public function __construct($tables){
        $this->tables = $tables;
        $this->locations = array(
            'otapi' => 'left',
            'main' => 'left',
            'diskspace' => 'right',
            'additionalinfo' => 'right',
            'networkinterfaces' => 'right'
        );
    }

    /**
     * Экспорт в шаблон
     */

    public function export_for_template(renderer_base $output){
        $lctables = array();
        $rctables = array();

        foreach ($this->tables as $tableitem){
            if (empty($tableitem['table'])) {
                continue;
            }
            if (isset ($tableitem['table']['type'])){

                $location = $this->locations[$tableitem['table']['type']];

            } else {
                $location = 'left';
            }
            $tableitem['table'] = $this->get_table_html($tableitem['table']);

            switch ($location){
                case 'left':
                    $lctables[] = $tableitem;
                    break;
                case 'right':
                    $rctables[] = $tableitem;
                    break;
            }
        }

        return array ('lctables' => $lctables, 'rctables' => $rctables);
    }

    /**
     * Метод получения html-разметки для таблицы с технической информацией
     *
     * @param array $table - массив данных таблицы в виде, содержщий 'type'=> тип таблицы (main, diskspace, ит.д.) и 'info'=> структурированное содержимое таблицы
     */

    protected function get_table_html($table){
        if (! array_key_exists('type', $table)){
            //Не знаем, что надо отрисовать - ничего не отрисовываем.
            return '';
        }
        //Для otapi просто выводим готовый уже html
        if ($table['type'] === 'otapi'){
            return $table['info'];
        }
        // Отрисовываем таблицу заданного типа
        $method = 'get_'.$table['type'].'_table';
        if (! method_exists($this, $method)){
            // Нет метода для отрисовки таблицы, не отрисовываем таблицу
            return;
        }
        $htmltable = $this->$method($table['info']);
        return html_writer::table($htmltable);
    }

    /**
     * Метод получения таблицы общей информации об инсталляции.
     *
     * @param array $info - массив с данными строк таблицы 'type' => main
     * @return html_table - таблица с общей информацией
     */

    protected function get_main_table($info){
        $table = new html_table();

        $table->attributes['class'] = 'table table-sm table-striped';
        $table->colclasses[1] = 'text-right';
        $table->data = array();
        foreach ($info as $infoitem){
            $cells = array();
            $cells[] = new html_table_cell($infoitem['name']);
            $cells[] = new html_table_cell($infoitem['value']);
            $table->data[] = new html_table_row($cells);
        }

        return $table;
    }

    /**
     * Метод получения таблицы общей информации о свободном дисковом пространстве.
     *
     * @param array $info - массив с данными строк таблицы 'type' => diskspace
     * @return html_table - таблица с данными о свободном дисковом пространстве
     */

    protected function get_diskspace_table($info){
        $table = new html_table();

        $table->attributes['class'] = 'table table-sm table-striped';
//         $table->colclasses[0] = 'text-center';
//         $table->colclasses[1] = 'text-center';
//         $table->colclasses[2] = 'text-center';
        $labelcol = get_string('partition_purpose', 'local_opentechnology');
        $bytescol = get_string('free_diskspace_bytes', 'local_opentechnology');
        $percentagecol = get_string('free_diskspace_percentage', 'local_opentechnology');
        $table->head = array($labelcol, $bytescol, $percentagecol);
        $table->data = array();

        foreach ($info as $infoitem){
            $cells = array();
            $danger = false;
            $cells[] = new html_table_cell($infoitem['name']);
            if (isset ($infoitem['value']['failed'])){
                $cells[] = new html_table_cell($infoitem['value']['failed']);
            }
            else {
                $cells[] = new html_table_cell($infoitem['value']['dsbytes']);
                $cells[] = new html_table_cell($infoitem['value']['dspercentage']);
                if ($infoitem['value']['dspercentage'] < 20){
                    $danger = true;
                }
            }
            $row = new html_table_row($cells);
            if ($danger){
                $row->attributes['class'] .= 'blinkingbg';
            }
            $table->data[] = $row;
        }
        return $table;
    }

    /**
     * Метод получения таблицы параметров сетевых интерфейсов.
     *
     * @param array $info - массив с данными строк таблицы 'type' => networkinterfaces
     * @return html_table - таблица с данными о свободном дисковом пространстве
     */

    protected function get_networkinterfaces_table($info){
        $table = new html_table();

        $table->attributes['class'] = 'table table-sm table-striped';
        $namecol = get_string('if_name','local_opentechnology');
        $ipcol = get_string('inet_addr','local_opentechnology');
        $maskcol = get_string('net_mask','local_opentechnology');
        $table->head = array($namecol, $ipcol, $maskcol);
        $table->data = array();
        // Добавляем список интерфейсов или информацию о том, что не удалось его получить
        if (is_string($info['nwinterfaces'])){
            $cell = new html_table_cell($info['nwinterfaces']);
            $row = new html_table_row(array($cell));
            $table->data[] = $row;
        } elseif (is_array($info['nwinterfaces'])){
            foreach($info['nwinterfaces'] as $ifitem){
                $cells = array();
                $cells[] = new html_table_cell($ifitem['name']);
                $cells[] = new html_table_cell($ifitem['value']['ip']);
                $cells[] = new html_table_cell($ifitem['value']['mask']);
                $row = new html_table_row($cells);
                $table->data[] = $row;
            }
        }
        // добавляем default gateway
        $cells = array();
        $cell = new html_table_cell($info['defgateway']['name']);
        $cell->attributes['class'] .= ' font-weight-bold';
        $cells[] = $cell;
        $cells[] = new html_table_cell($info['defgateway']['value']['defgateway']);
        $cells[] = new html_table_cell('');
        $row = new html_table_row($cells);
        $table->data[] = $row;
        //добавляем список dns
        $cells = array();
        $cell = new html_table_cell($info['dnsserverlist']['name']);
        $cell->attributes['class'] .= ' font-weight-bold';
        $cells[] = $cell;
        $cells[] = new html_table_cell(implode("<br />",$info['dnsserverlist']['value']));
        $cells[] = new html_table_cell('');
        $row = new html_table_row($cells);
        $table->data[] = $row;
        return $table;
    }

}