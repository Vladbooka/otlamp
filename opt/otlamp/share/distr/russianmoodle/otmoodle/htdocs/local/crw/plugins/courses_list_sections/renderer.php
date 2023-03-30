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
 * Блок списка курсов в виде отдельных блоков. Рендер.
 * 
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once ($CFG->dirroot . '/course/renderer.php');
require_once ($CFG->dirroot . '/local/crw/lib.php');

class crw_courses_list_sections_renderer extends core_course_renderer
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

        if ( ! empty($content))
        {
            $html .= $content;
        }
        // Вернем html код витрины по сформированным данным
        return $html;
    }
    
    /**
     * Сформировать витрину курсов из полученных данных
     *
     * @param array $courses
     *            - Массив курсов
     * @param bool $addbutton
     *            - Отобразить кнопку добавления курсов
     * @return string - HTML-код страницы
     */
    protected function cs_catcoursesblock($courses)
    {
        global $CFG, $OUTPUT;

        $content = '';
        
        // Формируем код блока
        $coursecount = 0;
        foreach ( $courses as $course )
        {
            $coursecount ++;
            $classes = 'cls_item' . $coursecount;
            
            // Блок курса
            $content .= $this->cs_courseblock($course, $classes);
        }
        
        if( ! empty($content) )
        {
            $content .= html_writer::div('', 'crw_clearboth');
            $content = html_writer::div($content, 'cls_coursesblock');
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
        
        $chelper = new coursecat_helper;
        if ( $course instanceof stdClass )
        {
            $course = new \core_course_list_element($course);
        }
        
        // Готовим данные
        $content = '';
        $classes = trim('cls_coursebox ' . $additionalclasses);
        $nametag = 'h4';
        
        // Открываем блок курса
        $content .= html_writer::start_tag('div', array (
                'class' => $classes,
                'data-courseid' => $course->id,
                'data-type' => self::COURSECAT_TYPE_COURSE 
        ));
        
        // Блок изображения курса с ссылкой
        $img = html_writer::link(new moodle_url('/local/crw/course.php', array (
                'id' => $course->id 
        )), $this->cs_courseimgblock($course));
        $content .= html_writer::div($img, 'cls_courseimagebox');
        
        // Название курса
        $name = html_writer::tag('h3', $course->fullname, array('class' => 'cls_coursename'));
        $namelink = html_writer::link(new moodle_url('/local/crw/course.php', array (
                'id' => $course->id
        )), $name);
        $infobox = html_writer::div($namelink, 'cls_course_titleblock');
        
        // Блок иконок c информацией по курсу
        $infobox .= html_writer::div($this->cs_courseinfoblock($course), 'cls_course_infoblock');
        
        // Блок описания курса
        $infobox .= html_writer::div($chelper->get_course_formatted_summary($course), 'cls_course_descriptionblock');
        
        $content .= html_writer::div($infobox, 'cls_courseinfobox');
        
        $content .= html_writer::div('', 'crw_clearboth');
        
        // Отобразить кнопку Подробнее
        $displaymore = get_config('crw_courses_list_sections', 'settings_display_more');
        if ( $displaymore )
        {
            $content .= html_writer::link(new moodle_url('/local/crw/course.php', array (
                    'id' => $course->id
            )), get_string('more','crw_courses_list_sections'), array('class' => 'cls_coursemorebitton btn'));
        }
        
        $content .= html_writer::div('', 'crw_clearboth');
        
        // Закрыли блок курса
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
        return html_writer::div('', 'cls_course_image', array(
            'style' => 'background: url("' . local_crw_course_image_url($course) . '") center center no-repeat;'
        ));
    }
    
    /**
     * Получить блок информации для плитки курса
     *
     * Отображается поверх изображения курса
     *
     * @param stdClass $course
     *            - объект курса
     *            
     * @return string - html код блока
     */
    private function cs_courseinfoblock($course)
    {
        global $CFG, $USER, $OUTPUT;
        
        require_once ($CFG->dirroot . '/course/lib.php');
        $html = '';

        $displaystartdate = local_crw_get_course_config($course->id, 'display_startdate');
        if( $displaystartdate !== false && in_array((int)$displaystartdate, [1,3]) )
        {
            if ( isset($course->startdate) )
            { // Есть дата начала курса
                if ( isset($USER->timezone) )
                { // Если указана временная зона
                    $timezone = $USER->timezone;
                } else
                { // Берем по серверу
                    $timezone = 99;
                }
                $icon = $OUTPUT->pix_icon('i/calendar', '');
                $html .= html_writer::div($icon . userdate($course->startdate, get_string('strftimedate', 'core_langconfig'), $timezone), 'cls_course_startdate');
            }
        }
        
        // Стоимость курса
        $displayprice = local_crw_get_course_config($course->id, 'display_price');
        if ( $displayprice === false || in_array((int)$displayprice, [1,3]) )
        {
            $price = local_crw_get_course_price($course);
            if ( ! empty($price) )
            {
                $pricespan = html_writer::span($price);
                $html .= html_writer::div(get_string('course_price','local_crw')." ".$pricespan, 'cls_course_price');
            }
        }
        
        // Иконки подписок курса
        $displayicons = local_crw_get_course_config($course->id, 'display_enrolicons');
        if ( $displayicons === false || in_array((int)$displayicons, [1,3]) )
        {
            $icons = enrol_get_course_info_icons($course);
            if ( ! empty($icons) )
            {
                foreach ( $icons as $pix_icon )
                {
                    $html .= html_writer::start_tag('div', array (
                            'class' => 'cls_course_enrolmenticons' 
                    ));
                    $html .= $OUTPUT->render($pix_icon);
                    $html .= html_writer::end_tag('div');
                }
            }
        }
        
        return html_writer::div($html, 'cp_course_img_infoblock');
    }
}