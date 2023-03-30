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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Блок списка курсов в виде плиток. Рендер.
 * 
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot .'/course/renderer.php');

class crw_courses_list_tiles_two_renderer extends core_course_renderer
{
    public function __construct()
    {
    }
    
    /**
     * Получить html-код блока курсов для страницы Витрины
     *
     * @param array $options - опции отображения
     *            
     * @return string - html-код блока
     */
    public function display( $options = array() )
    {
        global $CFG, $PAGE;
        
        // Подготовим HTML
        $html = '';
        
        // Получим id категории
        if ( isset($options['cid']) )
        {
            $catid = $options['cid'];
        } else 
        {
            $catid = 0;
        }
        
        $content = '';
        if ( isset($options['courses']) )
        {// Переданы курсы для отображения - сформируем блок
             
            // Сформируем курсы
            $courses = array();
            foreach ( $options['courses'] as $course )
            {
                $courses[$course->id] = get_course($course->id);
            }
            
            // Добавим блок с плитками курсов
            $content .= $this->cs_catcoursesblock($courses);
        }
        $html .= $content;
        
        // Вернем html код витрины по сформированным данным
        return $html;
    }
    
    /**
     * Сформировать витрину курсов из полученных данных
     *
     * @param coursecat_helper $chelper
     * @param array $courses
     * @param int $totalcount
     * @param bool $totalcount
     * @return string
     */
    protected function cs_catcoursesblock($courses)
    {
    
        $content = '';
    
        // Получим настройку числа блоков в строке
        $course_in_line = get_config('crw_courses_list_tiles_two', 'course_in_line');
        if ( empty($course_in_line) )
        {// Значение по умолчанию
            $course_in_line = 4;
        }
        
        // Формируем код витрины
        $coursecount = 0;
        foreach ( $courses as $course )
        {
            // Класс плитки
            $coursecount ++;
            $classes = 'cltt_item' . $coursecount . ' cltt_courseline_' . $course_in_line;
            if ( (($coursecount - 1) % $course_in_line) == 0 )
            { // Начало строки
                $classes .= ' first';
            }
            if ( ($coursecount % $course_in_line) == 0 )
            { // Конец строки
                $classes .= ' last';
            }
    
            // Плитка курса
            $content .= $this->cs_courseblock($course, $classes);
    
            if ( ($coursecount % $course_in_line) == 0 )
            { // Конец строки
                //$content .= html_writer::div('', 'cltt_clearboth');
            }
        }
        
        if( ! empty($content) )
        {
            $content .= html_writer::div('', 'cltt_clearboth');
            $content = html_writer::div($content, 'cltt_contentblock');
        }
        
        return $content;
    }
    
    /**
     * Сформировать одну плитку курса
     *
     * @param coursecat_helper $chelper
     *            объект хелпера
     * @param core_course_list_element|stdClass $course
     *            - объект курса
     * @param string $additionalclasses
     *            - дополнительные классы блока
     *
     * @return string - HTML код плитки курса
     */
    protected function cs_courseblock($course, $additionalclasses = '')
    {
        global $CFG;
    
        $content = '';
        
        $classes = trim('cltt_coursebox '.$additionalclasses);
        $nametag = 'h4';
    
        $content .= html_writer::start_tag('div', array (
                'class' => $classes,
                'data-courseid' => $course->id
        ));
    
        // Обрамляющая ссылка
        $content .= html_writer::start_tag('a', array (
                    'href' => new moodle_url('/local/crw/course.php', array (
                    'id' => $course->id
                ))
        ));
        
        // Формируем блок
        $content .= $this->cs_courseinfoblock($course);
        $content .= $this->cs_courseimgblock($course);
        $content .= $this->cs_coursenameblock($course);
        
        // Закрывающие теги
        $content .= html_writer::end_tag('a');
        $content .= html_writer::end_tag('div');
    
        return $content;
    }
    
    /**
     * Получить изображение для текущего курса
     *
     * Ищет и выводит изображение курса. Если изображения нет - выводит блок с изображением - заглушкой
     *
     * @param stdClass $course
     *            - объект курса
     *
     * @return string - html код блока с превью изображением
     */
    protected function cs_courseimgblock($course)
    {

        // Возвращаем html
        return html_writer::tag('div', '', array(
            'class' => 'cltt_courseimg hasimage',
            'style' => 'background: url("' . local_crw_course_image_url($course) . '") center center no-repeat;
                                    background-size: cover!important;'
        ));
    }
    
    /**
     * Получить блок информации для плитки курса
     *
     * @param stdClass $course - Объект курса
     *
     * @return string - HTML-код блока
     */
    protected function cs_courseinfoblock($course)
    {
        global $CFG, $USER, $OUTPUT;
    
        require_once ($CFG->dirroot . '/course/lib.php');
        $html = '';
    
        // Дата начала
        $show_course_category = get_config('crw_courses_list_tiles_two', 'settings_courses_showdate');
        if ( $show_course_category )
        { // Отобразить
            if ( $show_course_category == 2 )
            { // Взять из личных настроек курса
                
                $displaystartdate = local_crw_get_course_config($course->id, 'display_startdate');
                if( $displaystartdate !== false && in_array((int)$displaystartdate, [1,3]) )
                {
                    $html .= $this->get_course_date($course);
                }
            } else
            {
                $html .= $this->get_course_date($course);
            }
        }
        
        // Стоимость курса
        $displayprice = local_crw_get_course_config($course->id, 'display_price');
        if ( $displayprice === false || in_array((int)$displayprice, [1,3]) )
        {
            $price = local_crw_get_course_price($course, false);
            if ( ! empty($price) )
            {
                if ( $price == strval(floatval($price)) )
                {
                    $sign = html_writer::span(get_string('sign', 'crw_courses_list_tiles_two'), 'cltt_courseprice_sign');
                    $html .= html_writer::div($price.$sign, 'cltt_courseprice');
                } else 
                {
                    $html .= html_writer::div($price, 'cltt_courseprice_text');
                }
            }
        }
    
        return html_writer::div($html, 'cltt_course_infoblock');
    }
    
    private function get_course_date($course)
    {
        global $USER;
        
        if ( isset($course->startdate) )
        {// Есть дата начала курса
            if ( isset($USER->timezone) )
            { // Если указана временная зона
                $timezone = $USER->timezone;
            } else
            { // Берем по серверу
                $timezone = 99;
            }
            $startlang = html_writer::span(get_string('course_startdate', 'crw_courses_list_tiles_two'));
            return html_writer::div(
                    $startlang . userdate($course->startdate, get_string('strftimedate', 'core_langconfig'), $timezone), 
                    'cltt_coursestartdate'
            );
        }
    }
    
    /**
     * Получить блок имени курса
     *
     * @param stdClass $course - Объект курса
     *
     * @return string - HTML-код блока
     */
    protected function cs_coursenameblock($course)
    {
        $coursename = get_course_display_name_for_list($course);
        return html_writer::div($coursename, 'cltt_coursename');
    }
} 