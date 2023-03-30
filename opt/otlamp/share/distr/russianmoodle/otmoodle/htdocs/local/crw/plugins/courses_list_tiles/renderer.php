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

class crw_courses_list_tiles_renderer extends core_course_renderer
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
        global $CFG, $PAGE, $OUTPUT;
    
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
     * @param array $courses - массив курсов для отображения
     * @param bool $addbutton - отобразить кнопки редактирования
     * 
     * @return string
     */
    protected function cs_catcoursesblock($courses)
    {
        global $CFG, $OUTPUT;
    
        $passepartout = get_config('crw_courses_list_tiles', 'passe_partout') == 1 ? ' clt_passe_partout' : '';
        
        $content = '';
    
        // Получим настройку числа блоков в строке
        $course_in_line = get_config('crw_courses_list_tiles', 'course_in_line');

        if ( empty($course_in_line) )
        { // Значение по умолчанию
            $course_in_line = 4;
        }
        // Формируем код витрины
        $coursecount = 0;
        
        foreach ( $courses as $course )
        {
            $classes = [
                'clt_item'.$coursecount,
                'clt_courseline'.$course_in_line
            ];
            
            // классы-счетчики
            $coursecount ++;
            if ( (($coursecount - 1) % $course_in_line) == 0 )
            { // Начало строки
                $classes[] = 'first';
            }
            if ( ($coursecount % $course_in_line) == 0 )
            { // Конец строки
                $classes[] = 'last';
            }
            
            // классы тегов
            $coursetags = core_tag_tag::get_item_tags('core', 'course', $course->id);
            foreach($coursetags as $coursetag)
            {
                $classes[] = 'tag_'.$coursetag->id;
            }
            
            // класс скрытого курса
            $hidecourseenabled = local_crw_get_course_config($course->id, 'hide_course');
            if( $course->visible == '0' || ! empty($hidecourseenabled) )
            {
                $classes[] = 'clt_hidden';
            }
            
            // Плитка курса
            $content .= $this->cs_courseblock($course, implode(' ', $classes));
            
        }
        
        if( ! empty($content) )
        {
            $content .= html_writer::div('', 'clt_clearboth');
            $content = html_writer::div($content, 'clt_coursesblock'.$passepartout);
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
        
        if ( $course instanceof stdClass )
        {
            $course = new \core_course_list_element($course);
        }
    
        $content = '';
        $classes = trim('clt_coursebox ' . $additionalclasses);
        $nametag = 'h4';
    
        $content .= html_writer::start_tag('div', array (
                'class' => $classes,
                'data-courseid' => $course->id,
                'data-type' => self::COURSECAT_TYPE_COURSE
        ));
    

        $url = new moodle_url('/local/crw/course.php', [
            'id' => $course->id
        ]);
        $configlinkholder = (string)get_config('crw_courses_list_tiles', 'link_holder');
        $button = '';
        if( $configlinkholder == '0' )
        {
            // ссылка - весь блок с изображением
            $content .= html_writer::start_tag('a', [
                'href' => $url,
                'class' => 'clt_courseinfo_holder'
            ]);
            $linkholder = 'a';
        } elseif( in_array($configlinkholder, ['1','2','3']) )
        {
            // ссылка - кнопка под изображением
            $content .= html_writer::start_tag('div', [
                'class' => 'clt_courseinfo_holder'
            ]);
            $linkholder = 'div';
            if( in_array($configlinkholder, ['1','2']) )
            {
                $button = html_writer::link($url, get_string('button_readmore_text', 'crw_courses_list_tiles'),[
                    'class'=>'button btn btn-primary'
                ]);
            }
        }
        $content .= $this->cs_courseimgblock($course);
    
        // Название курса
        if( $configlinkholder == '3' )
        {
            $coursename = html_writer::link(
                $url,
                $course->fullname, 
                ['class' => 'clt_course_title_content']
            );
        } else
        {
            $coursename = html_writer::div($course->fullname, 'clt_course_title_content');
        }
        $content .= html_writer::start_div('clt_course_title_wrapper');
        $content .= html_writer::div($coursename, 'clt_course_title');
        $content .= html_writer::end_div();
        
        // Категория курса
        $show_course_category = get_config('crw_courses_list_tiles', 'settings_courses_showcategory');
        if ( $show_course_category )
        { // Отобразить
            $coursecat = \core_course_category::get($course->category, IGNORE_MISSING);
            $coursecaturl = new moodle_url('/local/crw/', [
                'cid' => $coursecat->id
            ]);
            $coursecatlink = html_writer::link(
                $coursecaturl, 
                $coursecat->name, 
                ['class' => 'clt_course_catname']
            );
            if ( $show_course_category == 2 )
            { // Взять из личных настроек курса
                $coursecat_view = local_crw_get_course_config($course->id, 'coursecat_view');
                if ( $coursecat_view && $coursecat_view > 0 )
                { // Настройки курса разрешают показ категории
                    $content .= $coursecatlink;//html_writer::div($coursecat->name, 'clt_course_catname');
                }
            } else
            { // Отобразим
                $content .= $coursecatlink;//html_writer::div($coursecat->name, 'clt_course_catname');
            }
        }

        if( (string)get_config('crw_courses_list_tiles', 'courseinfo_place') == '1' )
        {
            $desc = $this->cs_courseinfoblock($course);
            // если ссылкой является вся плитка, очистим от запрещенных тегов
            if ( $configlinkholder == '0' )
            {
                $desc = $this->remove_prohibited_tags_from_link($desc);
            }
            $content .= html_writer::div($desc, 'clt_courseinfo_below_image');
        }
    
        if ( ! empty($button) )
        {
            $content .= html_writer::div($button,'clt_courselink_wrapper');
        }
        $content .= html_writer::end_tag($linkholder);
        $content .= html_writer::end_tag('div'); // .info
    
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
        global $CFG;
    
        require_once ($CFG->libdir . '/filestorage/file_storage.php');
        require_once ($CFG->dirroot . '/course/lib.php');

        $courseurl = new moodle_url('/local/crw/course.php', [
            'id' => $course->id
        ]);
        
        // Формируем внутренний блок с информацией по курсу поверх изображения
        $imginfoblock = '';
    
        if( (string)get_config('crw_courses_list_tiles', 'courseinfo_place') == '0' )
        {
            $imginfoblock = $this->cs_courseinfoblock($course);
            // если ссылкой является вся плитка, очистим от запрещенных тегов
            if ( (string)get_config('crw_courses_list_tiles', 'link_holder') == '0' )
            {
                $imginfoblock = $this->remove_prohibited_tags_from_link($imginfoblock);
            }
        }
        
        $stickerkey = local_crw_get_course_config($course->id, 'sticker');
        if ( ! empty($stickerkey) )
        {
            $stickercode = local_crw_get_stickers($stickerkey);
            $stickername = local_crw_get_stickers($stickerkey, ['langstrings' => true]);
            $imginfoblock .= html_writer::div(
                '',
                'clt_icon_'.$stickercode,
                [
                    'data-sticker' => $stickername
                ]
            );
        }
        $courseimgblock = html_writer::tag('div', $imginfoblock, array(
            'class' => 'clt_course_img',
            'style' => 'background-image: url("' . local_crw_course_image_url($course) . '");'
        ));
        // Возвращаем html
        if (in_array((string) get_config('crw_courses_list_tiles', 'link_holder'), [
            '2',
            '3'
        ])) {
            return html_writer::link($courseurl, $courseimgblock);
        } else {
            return $courseimgblock;
        }
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
    protected function cs_courseinfoblock($course)
    {
        global $CFG, $USER, $OUTPUT;
    
        require_once ($CFG->dirroot . '/course/lib.php');
        $html = '';
    
    
        $additionaldescriptionview = local_crw_get_course_config($course->id, 'additional_description_view');
        if( isset($additionaldescriptionview) )
        {
            if( $additionaldescriptionview == 1 || $additionaldescriptionview == 3 )
            {//настроено отображение описания везде или только в витрине
                // Добавим краткое описание курса, если есть
                $additional_description = local_crw_get_course_config($course->id, 'additional_description');
                if ( ! empty($additional_description) )
                {
                    $html .= html_writer::div($additional_description, 
                        'clt_additional_description');
                }
            }
        }
        
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
                $startlang = html_writer::span(get_string('showcase_course_startdate', 'local_crw'));
                $html .= html_writer::div($startlang . userdate($course->startdate, get_string('strftimedate', 'core_langconfig'), $timezone), 'clt_coursestartdate');
            }
        }
        
        // Стоимость курса
        $displayprice = local_crw_get_course_config($course->id, 'display_price');
        if ( $displayprice === false || in_array((int)$displayprice, [1,3]) )
        {
            $price = local_crw_get_course_price($course);
            if ( ! empty($price) )
            {
                $pricelabel = html_writer::span(get_string('showcase_course_price', 'crw_courses_list_tiles'));
                $html .= html_writer::div($pricelabel . $price, 'clt_courseprice');
            }
        }
        
        // Иконки подписок курса
        $displayicons = local_crw_get_course_config($course->id, 'display_enrolicons');
        if ( $displayicons === false || in_array((int)$displayicons, [1,3]) )
        {
            $icons = enrol_get_course_info_icons($course);
            if ( $icons )
            {
                foreach ( $icons as $pix_icon )
                {
                    $html .= html_writer::start_tag('div', array (
                            'class' => 'clt_enrolmenticons'
                    ));
                    $html .= $OUTPUT->render($pix_icon);
                    $html .= html_writer::end_tag('div');
                }
            }
        }
    
        return html_writer::div($html, 'clt_course_img_infoblock');
    }
    
    /**
     * Метод для очистки строки от запрещенных тегов в ссылке
     * 
     * @param string $string
     * 
     * @return string
     */
    protected function remove_prohibited_tags_from_link($string = '')
    {
        /* 
         * удаление тегов с контентом внутри них
         * <(?:(?P<tag>a|iframe))(?:.|\n)*?>(?:(?:.|\n)*?<\/\k<tag>>)?
         *  
         * */
        return preg_replace("/<(?:\/?a|iframe)(?:.|\n)*?>/", '', $string);
    }
} 