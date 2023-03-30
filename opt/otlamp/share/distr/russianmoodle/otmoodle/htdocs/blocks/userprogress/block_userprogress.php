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
 * Блок Прогресс по курсу. Класс плагина.
 *
 * @package    block_userprogress
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Подключение библиотек
require_once($CFG->dirroot . '/lib/datalib.php');
require_once($CFG->dirroot . '/lib/weblib.php');
require_once($CFG->dirroot . '/blocks/userprogress/lib.php');

class block_userprogress extends block_base 
{
    /**
     * Инициализация блока
     * 
     * @return void
     */
    function init() 
    {
        $this->title = get_string('pluginname', 'block_userprogress');
    }

    function specialization() 
    {
    }
    
    /**
     * Отображение блока на страницах
     *
     * @return array
     */
    function applicable_formats() {
        return [
                'course-view' => true
        ];
    }
 
    function instance_allow_multiple() 
    {
        return false;
    }

    /**
     * Вернуть контент блока
     *
     * @return stdClass contents of block
     */
    function get_content() 
    {
        global $USER, $COURSE;
        
        if ( $this->content !== null ) 
        {
            return $this->content;
        }
        
        if ( empty($this->instance) ) 
        {
            return $this->content;
        }

        $this->content = new stdClass();
        
        $completiondata = get_user_completion($USER->id, $COURSE);
        
        if ( $completiondata === false ) 
        {
            $this->content->text = get_string('noprogress', 'block_userprogress');
            return $this->content;
        }
        
        $completion = $completiondata->percentcompleted;
        
        // $constant = 1;
        // Градиентный цвет плашки пока оставляю, есть вероятность, что вернемся к нему
        //$red   = ($completion < 50)? 132 : intval(110 - ($completion - 50) * $constant);
        //$green = ($completion < 50)? 168 : intval(140 - ($completion - 50) * $constant);
        //$blue = ($completion < 50)? 216 : intval(210 - ($completion - 50) * $constant);
        
        $barattr = [
            'width:' . $completion . '%' ,
            'height:22px',
            'background:#94e800',
            'text-align:center',
            'color:#000',
            'word-wrap:normal;'
        ];
        $frameattr = [
            'width:100%',
            'background-color: #efefef',
            'font-size: 16px'
        ];
        
        $message = "$completion%";

        $this->content->text = html_writer::tag(
            'div',
            html_writer::tag('div', $message, ['style' => implode(';', $barattr)]),
            [
                'style' =>  implode(';', $frameattr),
            ]
        );
        return $this->content;
    }
}
