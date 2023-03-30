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
 * Условие показа по числу выполненных условий. Класс фронтенда.
 *
 * @package    availability_counter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_counter;

defined('MOODLE_INTERNAL') || die();

class frontend extends \core_availability\frontend 
{
    /**
     * Формирование массива языковых переменных, доступных в JS объявлени формы
     */
    protected function get_javascript_strings() 
    {
        return [
                        'cond_form_th_enable',
                        'cond_form_th_element',
                        'cond_form_th_condition',
                        'cond_form_option_min',
                        'cond_form_label_min',
                        'cond_form_option_max',
                        'cond_form_label_max',
                        'cond_form_label_counter',
                        'cond_form_notice_nooneelements'  
        ];
    }

    /**
     * Формирование массива параметров, которые будут добавлены в JS функцию
     */
    protected function get_javascript_init_params( $course, \cm_info $cm = null, \section_info $section = null ) 
    {
        global $DB, $CFG;
        
        require_once($CFG->libdir . '/gradelib.php');

        // Сформируем массив элементов курса, по которым происходит проверка условий
        $gradeoptions = [];
        $items = \grade_item::fetch_all(['courseid' => $course->id]);
        
        // Если нет оценок - вернем пустой массив
        $items = $items ? $items : [];
        foreach ( $items as $id => $item ) 
        {
            // Исключим оценки по текущему элементу
            if ( $cm && $cm->instance == $item->iteminstance
                     && $cm->modname == $item->itemmodule
                     && $item->itemtype == 'mod' ) 
            {
                continue;
            }
            
            $gradeoptions[$id] = $item->get_name(true);
        }
        \core_collator::asort($gradeoptions);

        // Change to JS array format and return.
        $jsarray = array();
        foreach ($gradeoptions as $id => $name) {
            $jsarray[] = (object)array('id' => $id, 'name' => $name);
        }
        return array($jsarray);
    }
}
