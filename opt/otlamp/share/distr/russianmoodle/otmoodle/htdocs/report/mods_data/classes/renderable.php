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
 * Log report renderer.
 *
 * @package    report_log
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/report/mods_data/form.php');

/**
 * Report log renderable class.
 *
 * @package    report_log
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_mods_data_renderable implements renderable {
    
    protected $form;
    
    protected $table;
    
    protected $cache;
    
    protected $format;
    
    protected $availableformats;
    
    protected $global;
    
    protected $course;
    
    public function __construct($course = 0) {
        
        global $SITE;
        
        $this->global = false;
        if( ! empty($course) && is_int($course) )
        {
            $this->course = get_course($course);
        } elseif( empty($course) )
        {
            $this->course = get_site();
            $this->global = true;
        } else
        {
            $this->course = $course;
        }
        if( $this->course->id == $SITE->id )
        {
            $this->global = true;
        }
        $customdata = new stdClass();
        $customdata->report = & $this;
        $customdata->course = $this->course;
        $url = new moodle_url("/report/mods_data/index.php", ['id' => $this->course->id]);
        $this->form = new report_mods_data_generalreport_form($url, $customdata, 'post', '', ['data-double-submit-protection' => 'off']);
        $this->availableformats = ['csv', 'xls', 'pdf', 'html'];
        $this->format = 'html';
    }
    
    /**
     * Получить доступные форматы
     * @return string
     */
    public function get_available_formats()
    {
        return $this->availableformats;
    }
    
    /**
     * Отобразить отчет
     */
    public function display()
    {
        echo $this->render();
    }
    
    /**
     * Сформировать отчет
     * @return string
     */
    public function render()
    {
        $html = '';
        $this->form->process();
        $html .= $this->form->render();
        
        if( ! empty($this->table) )
        {
            $html .= $this->table;
        }
        
        return $html;
    }
    
    /**
     * Установить данные для формирования отчета
     * @param unknown $formdata
     */
    public function set_data($formdata)
    {
        $this->table = $this->get_data($formdata);
    }
    
    /**
     * Получить данные для формирования отчета
     * @return unknown
     */
    public function get_report()
    {
        return $this->table;
    }
    
    /**
     * Получить таблицу отчета по переданным параметрам
     * @param stdClass $formdata объект с параметрами, переданный из формы
     * @return string таблица отчета
     */
    protected function get_data($formdata)
    {
        global $SESSION;
        $uniondata = $this->get_uniondata($formdata);
        // Список пользователей берем из сесси, остальное прилетает из формы
        $users = ! empty($SESSION->report_mods_data_bulk_users) ? $SESSION->report_mods_data_bulk_users : [];
        $groups = ! empty($formdata->groups) ? $formdata->groups : [];
        $completion = ! empty($formdata->completion) ? $formdata->completion : 'all';
        $attempts = ! empty($formdata->attempts) ? $formdata->attempts : 'all';
        $startdate = ! empty($formdata->period['startdate']) ? $formdata->period['startdate'] : null;
        $enddate = ! empty($formdata->period['enddate']) ? $formdata->period['enddate'] : null;
        $attemptsinperiod = ! empty($formdata->attemptsinperiod) ? $formdata->attemptsinperiod : null;
        $table = $this->get_report_table($uniondata, $users, $groups, $completion, $attempts, $startdate, $enddate, $attemptsinperiod);
        return $table;
    }
    
    /**
     * Формирует динамические заголовки для таблицы отчета
     * @param stdClass $formdata объект параметров для формирования заголовков
     * @return array
     */
    protected function get_uniondata($formdata)
    {
        $uniondata = [];
        
        if ( empty($formdata) )
        {// Данные из формы не получены
            return $uniondata;
        }
        
        // Получить поддерживаемые модули
        $supported_modules = report_mods_data_get_supported_modules();
        
        foreach ( $formdata as $field => $value )
        {
            $type = explode('_', $field);
            if ( isset( $type[0]) )
            {// Тип поля определен
                switch ( $type[0] )
                {
                    // Пользовательское поле
                    case 'userfields' :
                        if ( ! isset($uniondata[$type[0]]) )
                        {// Объявление массива пользовательских полей
                            $uniondata[$type[0]] = [];
                        }
                        $name = str_replace($type[0].'_', '', $field);
                        $uniondata[$type[0]][$name] = $value;
                        break;
                    case 'customuserfields' :
                        if ( ! isset($uniondata[$type[0]]) )
                        {// Объявление массива пользовательских полей
                            $uniondata[$type[0]] = [];
                        }
                        $name = str_replace($type[0].'_', '', $field);
                        $uniondata[$type[0]][$name] = $value;
                        break;
                        // Поле персоны деканата
                    case 'dofpersonfields' :
                        if ( ! isset($uniondata[$type[0]]) )
                        {// Объявление массива пользовательских полей
                            $uniondata[$type[0]] = [];
                        }
                        $name = str_replace($type[0].'_', '', $field);
                        $uniondata[$type[0]][$name] = $value;
                        break;
                        // Поле плагина
                    default :
                        if ( isset($supported_modules[$type[0]]) )
                        {// Модуль поддерживается
                            if ( ! isset($uniondata[$type[0]]) )
                            {// Объявление массива пользовательских полей
                                $uniondata[$type[0]] = [];
                            }
                            $name = str_replace($type[0].'_', '', $field);
                            if( ! empty($value[$name]) )
                            {
                                $uniondata[$type[0]][$name] = $value[$name];
                            }
                        }
                        break;
                }
            }
        }
        return $uniondata;
    }
    
    /**
     * Возвращает сформированную таблицу отчета
     * @param array $uniondata массив динамических заголовков
     * @param array $users массив пользователей, которые должны попасть в отчет
     * @param array $groups массив идентификаторов локальных групп, пользователи из которых должны попасть в отчет
     * @param string $completion критерий отбора по выполнению элементов
     * @param string $attempts критерий отбора по попыткам прохождения элементов
     * @param int $startdate начало периода, за который необходимо собрать данные (timestamp)
     * @param int $enddate конец периода, за которые необходимо собрать данные (timestamp)
     * @return string таблица отчета
     */
    protected function get_report_table($uniondata, $users, $groups, $completion, $attempts, $startdate, $enddate, $attemptsinperiod)
    {
        $report = new report_mods_data_report(
            $uniondata, 
            $this->format, 
            null, 
            $users, 
            $groups,
            $completion, 
            $attempts, 
            $startdate, 
            $enddate,
            $attemptsinperiod
        );
        return $report->get_report();
    }
    
    /**
     * Выставить формат отчета
     * @param string $format формат, который необходимо задать
     */
    public function set_format($format = 'html')
    {
        if( in_array($format, $this->availableformats) )
        {
            $this->format = $format;
        } else 
        {
            $this->format = 'html';
        }
    }
}
