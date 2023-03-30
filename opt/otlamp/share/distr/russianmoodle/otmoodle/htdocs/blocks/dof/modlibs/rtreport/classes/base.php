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

/**
 * RT отчет. Базовый класса отчета
 *
 * @package    modlib
 * @subpackage rtreport
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

abstract class dof_modlib_rtreport_base
{
    /**
     * Счетчик
     *
     * @var integer
     */
    protected $counter = 0;
    
    /**
     * Контроллер деканата
     *
     * @var dof_control
     */
    protected $dof = null;
    
    /**
     * Данные
     *
     * @var array
     */
    protected $data = [];
    
    /**
     * Данные
     *
     * @var string
     */
    protected $exporter = null;
    
    /**
     * Получение заголовков (Возможно использование HTML_ROW,HTML_CELL)
     *
     * @return array
     */
    protected function get_headers()
    {
        return [];
    }
    
    /**
     * Получение заголовков (Только значения)
     *
     * @return array
     */
    protected function get_headers_values()
    {
        return [];
    }
    
    /**
     * Получение данных (Возможно использование HTML_ROW,HTML_CELL)
     *
     * @return array
     */
    protected function get_rows()
    {
        return [];
    }
    
    /**
     * Получение данных (Только значения)
     *
     * @return array
     */
    protected function get_rows_values()
    {
        return [];
    }
    
    /**
     * Установка данных из POST/GET
     */
    protected function set_variables()
    {
    }
    
    /**
     * Массив ячеек, которые необходимо смержить (XLS/ODS)
     *
     * @example Пример [ [0,0,1,0], [0,0,0,1] ]
     *
     * @return array
     */
    protected function merge_cells()
    {
        return [];
    }
    
    /**
     * Массив ячеек, которые необходимо дополнительно стилизовать
     *
     * @return array
     */
    protected function style_cells()
    {
        return [];
    }
    
    /**
     * Конвертация в таблицу с заданием нужных стилей
     *
     * @param array $data
     *
     * @return array
     */
    protected function convert_to_table($data = [])
    {
        // Объект html таблицы
        $table = new html_table();
        $table->attributes = ['border' => 1, 'class' => 'generaltable rtreport'];
        $table->data = $data;
        
        return $table;
    }
    
    /**
     * Возвращение данных
     *
     * @param boolean $return_table
     *
     * @return array | bool
     */
    protected function get_rtreport_data($not_only_values = true)
    {
        // Валидация
        if ( empty($this->data) )
        {
            return false;
        }
        
        $cft = $this->get_cft();
        if ( $cft instanceof dof_cross_format_table )
        {
            return $cft;
        }
        
        if ( $not_only_values )
        {
            // Получение заголовков
            $headers = $this->get_headers();
            
            // Получение данных
            $rows = $this->get_rows();
            
            return $this->convert_to_table(array_merge($headers, $rows));
        } else
        {
            // Получение заголовков
            $headers = $this->get_headers_values();
            
            // Получение данных
            $rows = $this->get_rows_values();
            
            return array_merge($headers, $rows);
        }
    }
    
    /**
     * Интеграция с modlibs/widgets/cft
     *
     * @return NULL | dof_cross_format_table
     */
    protected function get_cft()
    {
        return null;
    }
    
    /**
     * Экспорт
     *
     * @return void
     */
    protected function export()
    {
        if ( ! empty($this->exporter) )
        {
            $cft = $this->get_cft();
            if ( $cft instanceof dof_cross_format_table )
            {
                $method = 'print_' . $this->exporter;
                if ( method_exists($cft, $method) )
                {
                    $cft->{$method}();
                }
            } else
            {
                switch ( $this->exporter )
                {
                    // Для XLS дополнительно можно отправить массив ячеек, которые необходимо смержить
                    case 'ods':
                    case 'xls':
                        dof_modlib_rtreport_helper_exporter::export($this->exporter, $this->get_rtreport_data(false), $this->merge_cells(), $this->style_cells());
                        break;
                    default:
                        dof_modlib_rtreport_helper_exporter::export($this->exporter, $this->get_rtreport_data());
                        break;
                }
            }
        }
    }
    
    /**
     * Получение кода рейтинга
     *
     * @return string
     */
    abstract public function get_type_code();
    
    /**
     * Конструктор
     *
     * @return void
     */
    public function __construct()
    {
        dof_hugeprocess();
        
        // Контроллер деканата
        GLOBAL $DOF;
        $this->dof = $DOF;
    }
    
    /**
     * Установка экспортера
     *
     * @param string $exporter
     *
     * @return void
     */
    public function set_exporter($exporter)
    {
        if ( dof_modlib_rtreport_helper_exporter::is_valid($exporter) )
        {
            $this->exporter = strtolower($exporter);
        }
    }
    
    /**
     * Получение данных
     *
     * @return array
     */
    public function get_variables()
    {
        global $addvars;
        return array_merge($addvars, $this->data, ['showall' => optional_param('showall', true, PARAM_BOOL)]);
    }
    
    /**
     * Установка данных
     *
     * @param array $data
     *
     * @return void
     */
    public function set_data($data = [])
    {
        $this->data = array_merge($this->data, $data);
    }
    
    /**
     * Получение HTML Заголовка
     *
     * @return string
     */
    public function get_header()
    {
        return '';
    }
    
    /**
     * Дополнительные обработчики
     *
     * @return string
     */
    public function get_processors()
    {
        return '';
    }
    
    /**
     * Установка навигации
     *
     * @return void
     */
    public function set_nvg()
    {
    }
        
    /**
     * Запуск сбора рейтинга
     *
     * @return void | string
     */
    public function run()
    {
        // Установка классом нужных ему данных
        $this->set_variables();
        
        // Экспорт
        $this->export();
        
        return $this->get_rtreport_data();
    }
    
    /**
     * Получение значения экспортера
     *
     * @return string|null - формат экспорта или null, если выбран не экспорт
     */
    public function get_exporter()
    {
        return $this->exporter;
    }
}
