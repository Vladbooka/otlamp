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
 * Блок таблиц курсов по категориям. Рендер.
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once ($CFG->dirroot . '/course/renderer.php');
require_once ($CFG->dirroot . '/local/crw/lib.php');

class crw_courses_list_ajax_renderer extends core_course_renderer
{
    var $icon = '';
    var $scrolltotopicon = '';

    public function __construct()
    {
        global $CFG, $PAGE;

        $fs = get_file_storage();
        $files = $fs->get_area_files(context_system::instance()->id, 'crw_courses_list_ajax', 'icon');
        foreach ( $files as $file )
        {
            $isimage = $file->is_valid_image();
            if ( $isimage )
            {
                $url = moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
                );
                $this->icon = '<div class="clajax_icon_wrap" ><img class="clajax_icon" src="' . $url . '" /></div>';
            }
        }

        $files = $fs->get_area_files(context_system::instance()->id, 'crw_courses_list_ajax', 'scrolltotopicon');
        foreach ( $files as $file )
        {
            $isimage = $file->is_valid_image();
            if ( $isimage )
            {
                $url = moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
                );
                $scrolltotopiconurl = $url;
            }
        }
        if( empty($scrolltotopiconurl) )
        {
            $scrolltotopiconurl = $PAGE->theme->image_url('scrolltotop', 'local_crw');
        }
        $this->scrolltotopicon = '<img class="clajax_scrolltotopicon icon" src="' . $scrolltotopiconurl . '" />';

        $files = $fs->get_area_files(context_system::instance()->id, 'crw_courses_list_ajax', 'morecoursesicon');
        foreach ( $files as $file )
        {
            $isimage = $file->is_valid_image();
            if ( $isimage )
            {
                $url = moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
                );
                $this->morecoursesiconurl = $url;
            }
        }
        if( empty($this->morecoursesiconurl) )
        {
            $this->morecoursesiconurl = $PAGE->theme->image_url('switch_plus', 'local_crw');
        }

        $files = $fs->get_area_files(context_system::instance()->id, 'crw_courses_list_ajax', 'lesscoursesicon');
        foreach ( $files as $file )
        {
            $isimage = $file->is_valid_image();
            if ( $isimage )
            {
                $url = moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
                );
                $this->lesscoursesiconurl = $url;
            }
        }
        if( empty($this->lesscoursesiconurl) )
        {
            $this->lesscoursesiconurl = $PAGE->theme->image_url('switch_minus', 'local_crw');
        }
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

            // Если включен режим редактирования - добавим кнопку на создание курса
            if ( $PAGE->user_is_editing() && has_capability('moodle/course:create', context_system::instance()) )
            {
                $addbutton = true;
            } else
            {
                $addbutton = false;
            }
            // Добавим блок с плитками курсов
            $content .= $this->cs_catcoursesblock($courses, $addbutton);
        }

        if ( ! empty($content))
        {
            $html .= $content;
            $html .= html_writer::start_div('', array('id' => 'clajax_ajaxcontent_wrapper'));
            $html .= html_writer::div('', '', array('id' => 'clajax_ajaxcontent'));
            $html .= html_writer::end_div();
        }

        // Вернем html код витрины по сформированным данным
        return $html;
    }


    /**
     * Сформировать витрину курсов из полученных данных
     *
     * @param array $courses - Массив курсов
     * @param bool $addbutton - Отобразить кнопку добавления курсов
     *
     * @return string - HTML-код страницы
     */
    protected function cs_catcoursesblock($courses, $addbutton = false)
    {
        global $CFG;

        // Начало блока ajax-списка курсов
        $html = '';

        // Формат группировки курсов
        $grouppedcourses = [];
        $config = get_config('crw_courses_list_ajax', 'group_by_category');
        if ( ! empty($config) )
        {// Группировать курсы по категориям
            foreach ( $courses as $course )
            {
                $grouppedcourses[$course->category][] = $course;
            }
        } else
        {// Не группировать курсы по категориям
            $grouppedcourses['']=$courses;
        }

        $toolsdisplaymode = get_config('crw_courses_list_ajax', 'tools_display_mode');
        if ( empty($toolsdisplaymode) )
        {
            $toolsdisplaymode = '';
        } else
        {
            $toolsdisplaymode = 'btn btn-primary';
        }


        $content = '';
        // Отображение списка курсов
        foreach ( $grouppedcourses as $categoryid => $coursesgroup )
        {
            // Заголовок категории
            if ( $categoryid )
            {// Категория указана
                // Получение данных категории
                $category = \core_course_category::get($categoryid);
                // Ссылка на категорию
                $content .= html_writer::link('#', $category->name,
                    [
                        'name' => 'catid' . $categoryid,
                        'class' => 'clajax_coursescategory_title'
                    ]
                );
            }

            $coursesobj = $this->cs_coursesblock($coursesgroup);

            if ( ! empty($coursesobj->htmltable) )
            {
                // Отображение таблицы курсов
                $content .= html_writer::div(
                    $coursesobj->htmltable,
                    'clajax_coursescategory_content'
                );

                // Кнопка "Показать больше"
                $morelink = '';
                $hidemorethan = get_config('crw_courses_list_ajax', 'hide_more_than');
                if( ! empty($hidemorethan) && $coursesobj->count > (int)$hidemorethan )
                {
                    $morecoursesicon = html_writer::img(
                        $this->morecoursesiconurl,
                        '',
                        [
                            'class' => 'clajax_morecoursesicon icon'
                        ]
                    );
                    // Кнопка "Показать больше"
                    $morelink = html_writer::link(
                        'javascript:void(0);',
                        $morecoursesicon . get_string('morecourses', 'crw_courses_list_ajax'),
                        [
                            'data-morecoursessrc' => $this->morecoursesiconurl,
                            'data-lesscoursessrc' => $this->lesscoursesiconurl,
                            'data-count_to_show' => $hidemorethan,
                            'data-lesscourses' => get_string('lesscourses', 'crw_courses_list_ajax'),
                            'data-morecourses' => get_string('morecourses', 'crw_courses_list_ajax'),
                            'class' => 'clajax_morecourses '.$toolsdisplaymode
                        ]
                    );
                }

                // Кнопка "Наверх"
                $toplink = html_writer::link(
                    '#',
                    $this->scrolltotopicon . get_string('scrolltotop', 'crw_courses_list_ajax'),
                    [
                        'class' => 'clajax_scrolltotop '.$toolsdisplaymode
                    ]
                );

                $content .= html_writer::div( $morelink . $toplink, 'clajax_tools_wrapper' );
                // Окончание блока с группой (категорией) курсов
                $html .= html_writer::div($content, 'clajax_coursescategory');
                // Очищаем матрешку
                $content = '';
            }
        }

        if( ! empty($html) )
        {
            $html = html_writer::div($html, 'clajax_coursesblock');
        }

        // Возвращаем таблицу в обертке
        return $html;
    }

    /**
     * Сформировать блок курса
     *
     * @param array $course - массив объектов курса
     * @param bool $additionalclasses - Дополнительные классы
     *
     * @return string - HTML-код страницы
     */
    protected function cs_coursesblock($courses)
    {
        // Настройка скрытия курсов
        $hidemorethan = get_config('crw_courses_list_ajax', 'hide_more_than');
        if ( empty($hidemorethan) )
        {
            $hidemorethan = 0;
        }
        // Создаем таблицу
        $table = new html_table();
        //массив строк таблицы
        $tablerows = [];
        //счетчик курсов в категории
        $coursecount = 0;
        // Формируем код блока
        foreach ( $courses as $course )
        {
            $coursecount++;
            $classsuffix = $coursecount;
            if( $hidemorethan > 0 && $coursecount > $hidemorethan )
            {
                $classsuffix .= ' crw_clajax_morethan';
            }
            $tablerows[] = $this->cs_get_course_row($course, $table, $classsuffix);
        }
        //добавление данных в таблицу
        $table->data = $tablerows;

        $object = new stdClass();
        $object->count = $coursecount;
        if( ! empty($tablerows) )
        {
            $object->htmltable = html_writer::table($table);
        } else
        {
            $object->htmltable = '';
        }

        return $object;
    }


    private function cs_get_course_row($course, &$table, $coursecount)
    {
        //требуется ли отображать заголовок у таблицы
        $config = get_config('crw_courses_list_ajax', 'display_table_header');
        $displayheader = ! empty($config);
        //строка таблицы с курсом
        $tablerow = new html_table_row();
        //указываем класс с порядковым номером курса
        $tablerow->attributes = [
            'class' => 'clajax_item'.$coursecount
        ];
        // Иконка
        if ( ! empty($this->icon) )
        {
            $icon = html_writer::span($this->icon, 'clajax_iconcell');
            $tablerow->cells[] = new html_table_cell($icon);
            if ( $coursecount == 1 )
            {//первая строка таблицы - добавим в таблицу настройки столбца
                $table->align[] = 'left';
                $table->size[] = '1%';
                if( $displayheader )
                {//нужно отобразить заголовок
                    $table->head[] = get_string('course_icon', 'crw_courses_list_ajax');
                }
            }
        }

        //ширина ячейки с кратким именем курса
        $courseshortnamewidth = 0;

        $config = get_config('crw_courses_list_ajax', 'display_course_shortname');
        if( ! empty($config) )
        {//требуется отображение краткого имени курса
            $courseshortname = html_writer::span($course->shortname, 'clajax_shortname');
            //отображаем краткое название
            $tablerow->cells[] = new html_table_cell($courseshortname);
            $courseshortnamewidth = 30;
            if ( $coursecount == 1 )
            {//первая строка таблицы - добавим в таблицу настройки столбца
                $table->align[] = 'left';
                $table->size[] = $courseshortnamewidth.'%';
                if( $displayheader )
                {//нужно отобразить заголовок
                    $table->head[] = get_string('course_shortname', 'crw_courses_list_ajax');
                }
            }
        }

        //Название курса
        $coursename =html_writer::span($course->fullname, 'clajax_name');
        $tablerow->cells[] =  new html_table_cell($coursename);
        if ( $coursecount == 1 )
        {//первая строка таблицы - добавим в таблицу настройки столбца
            $table->align[] = 'left';
            $table->size[] = 70-$courseshortnamewidth.'%';
            if( $displayheader )
            {//нужно отобразить заголовок
                $table->head[] = get_string('course_fullname', 'crw_courses_list_ajax');
            }
        }


        //сложность курса
        $config = get_config('crw_courses_list_ajax', 'display_course_difficult');
        if( ! empty($config) )
        {//требуется отображение сложности курса
            if ( ! $difficult = local_crw_get_course_config($course->id, 'course_difficult') )
            {//настройки уровня сложности не производились - отобразим в ячейке неразрывный пробел
                $difficult = '&nbsp;';
            } else
            {//получение языковой строки уровня слолжности курса
                $difficult = get_string('course_difficult_'.$difficult, 'local_crw');
            }
            $coursedifficult = html_writer::span(
                $difficult,
                'clajax_difficult'
            );
            $tablerow->cells[] = new html_table_cell($coursedifficult);
            if ( $coursecount == 1 )
            {//первая строка таблицы - добавим в таблицу настройки столбца
                $table->align[] = 'left';

                $config = get_config('crw_courses_list_ajax', 'enable_enrol_button');
                if ( empty($config) )
                {
                    $table->size[] = '19%';
                } else
                {
                    $table->size[] = '9%';
                }
                if( $displayheader )
                {//нужно отобразить заголовок
                    $table->head[] = get_string('course_difficult', 'crw_courses_list_ajax');
                }
            }
        }


        //ссылка на курс
        $config = get_config('crw_courses_list_ajax', 'use_course_link');
        if ( ! empty($config) )
        {//найтройками задано отображение прямой ссылки на курс вместо ссылки на страницу описания курса
            $courselink = new moodle_url('/course/view.php',
                [
                    'id' => $course->id
                ]);
        } else
        {//по умолчанию ссылка ведет на страницу описания курса
            $courselink = new moodle_url('/local/crw/course.php',
                [
                    'id' => $course->id
                ]);
        }
        //атрибуты для подцепления ссылки аяксом
        $courselinkattrs = [
            'class' => ' clajax_courselink',
            'data-courseid' => $course->id
        ];
        $config = get_config('crw_courses_list_ajax', 'enable_enrol_button');
        if ( ! empty($config) )
        { //требуется отобразить кнопку для перехода
            //ссылка-кнопка
            $enrolbuttonlink = html_writer::link($courselink,
                get_string('enrol', 'crw_courses_list_ajax'), [
                    'class' => 'clajax_enrol btn btn-primary'
                ]);
            $enrolbuttoncell = new html_table_cell($enrolbuttonlink);
            $enrolbuttoncell->attributes = $courselinkattrs;
            $tablerow->cells[] = $enrolbuttoncell;
            if ( $coursecount == 1 )
            {//первая строка таблицы - добавим в таблицу настройки столбца
                $table->align[] = 'right';
                if( $displayheader )
                {//нужно отобразить заголовок
                    $table->head[] = get_string('course_action', 'crw_courses_list_ajax');
                }
            }
        } else
        { //должен обрабатываться клик по всем ячейкам с данными по курсу
            foreach ( $tablerow->cells as $i => $cell )
            {
                $tablerow->cells[$i]->attributes = $courselinkattrs;
                $tablerow->cells[$i]->text = html_writer::link($courselink,
                    $tablerow->cells[$i]->text);
            }
        }


        return $tablerow;
    }


    /**
     * Блок курса на странице описания
     *
     * @param stdClass $course
     *            - объект курса из БД
     *
     * @return string - HTML код блока
     */
    public function cajax_courseblock(stdClass $course)
    {
        // Получим хелпер
        $chelper = new coursecat_helper();

        $html = '';
        $html .= html_writer::start_div('crw_cajax_courseblock');

        // Блок заголовка
        $html .= $this->cajax_courseblock_top($chelper, $course);

        $html .= html_writer::start_div('crw_cajax_courseblock_wrap');
        // Блок изображения
        $html .= $this->cajax_courseblock_left($chelper, $course);

        // Описание курса
        $html .= $this->cajax_courseblock_right($chelper, $course);
        $html .= html_writer::div('', 'crw_clearboth');
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();
        // Возвращаем блок
        return $html;
    }

    /**
     * Блок заголовка
     *
     * @param coursecat_helper $chelper
     *            - хелпер
     * @param unknown $course
     *            - объект курса из БД
     *
     * @return string - HTML код блока
     */
    protected function cajax_courseblock_top(coursecat_helper $chelper, $course)
    {
        $button = html_writer::div('', 'crw_cajax_coursetitle_close', array('id' => 'crw_cajax_coursetitle_close' ) );
        return html_writer::div($course->fullname . $button, 'crw_cajax_coursetitle');
    }

    /**
     * Блок файлов курса
     *
     * @param coursecat_helper $chelper
     *            - хелпер
     * @param unknown $course
     *            - объект курса из БД
     *
     * @return string - HTML код блока
     */
    protected function cajax_courseblock_left(coursecat_helper $chelper, $course)
    {
        global $CFG;
        require_once ($CFG->libdir . '/outputrenderers.php');

        $course = new \core_course_list_element($course);

        // Сформировать файлы курса
        $htmlimg = '';
        foreach ( $course->get_course_overviewfiles() as $file )
        { // Обработаем каждый файл
            // Является ли файл изображением
            $isimage = $file->is_valid_image();
            // URL файла
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file->get_contextid() . '/' . $file->get_component() . '/' . $file->get_filearea() . $file->get_filepath() . $file->get_filename(), ! $isimage);
            if ( $isimage )
            { // Изображение
                $htmlimg = html_writer::tag('div', html_writer::empty_tag('img', array (
                        'src' => $url,
                        'class' => 'crw_cajax_courseblock_image'
                )), array (
                        'class' => 'crw_cajax_courseblock_image_wrap'
                ));
            }
        }

        $return = '';
        if ( $htmlimg )
        { // Есть изображения
            $return .= html_writer::div($htmlimg, 'crw_cajax_left');
        }

        return $return;
    }

    /**
     * Блок дополнительных полей курса
     *
     * @param coursecat_helper $chelper
     *            - хелпер
     * @param unknown $course
     *            - объект курса из БД
     *
     * @return string - HTML код блока
     */
    protected function cajax_courseblock_right(coursecat_helper $chelper, $course)
    {
        global $CFG, $DB, $OUTPUT;
        require_once ('lib.php');
        require_once ($CFG->libdir . '/outputrenderers.php');

        $course = new core_course_list_element($course);

        $html = '';

        $html .= html_writer::div($course->fullname, 'crw_cajax_ajaxbkock_coursename');

        if ( $course->has_summary() )
        { // Если есть описание
            $summary = $chelper->get_course_formatted_summary($course, array (
                    'overflowdiv' => true,
                    'noclean' => true,
                    'para' => false
            ));
            $html .= html_writer::div($summary, 'crw_cajax_summary');
        }

        // Добавим контакты курса
        if ( $course->has_course_contacts() )
        { // Есть контакты
            $html .= html_writer::start_div('crw_cajax_contacts');

            $html .= html_writer::start_tag('ul', array (
                    'class' => 'crw_ci_courseblock_contacts_ul'
            ));
            foreach ( $course->get_course_contacts() as $userid => $coursecontact )
            {
                // Получаем пользователя
                $user = get_complete_user_data('id', $userid);

                // Блок с изображением пользователя
                $userpic = $OUTPUT->user_picture($user, array (
                        'size' => '50'
                ));
                // Блок с именем
                $name = html_writer::link(new moodle_url('/user/view.php', array (
                        'id' => $userid
                )), $coursecontact['username'], array (
                        'class' => 'crw_cajax_contacts_username'
                ));
                // Блок с ролью
                $role = html_writer::div($coursecontact['rolename'], 'crw_cajax_contacts_role');
                // Сформируем блок информации о пользователе
                $userinfo = html_writer::div($name . $role, 'crw_cajax_contacts_userinfo');
                $userinfo .= html_writer::div('', 'crw_clearboth');
                $html .= html_writer::tag('li', $userpic . $userinfo, array (
                        'class' => 'crw_cajax_contacts_li'
                ));
            }
            $html .= html_writer::end_tag('ul');
            $html .= html_writer::end_div();
        }

        // Добавим дату начала
        if ( isset($course->startdate) )
        { // Есть дата начала курса
            if ( isset($USER->timezone) )
            { // Если указана временная зона
                $timezone = $USER->timezone;
            } else
            { // Берем по серверу
                $timezone = 99;
            }
            $startlang = html_writer::span(get_string('courseblock_course_startdate', 'local_crw'));
            $html .= html_writer::div($startlang . userdate($course->startdate, get_string('strftimedate', 'core_langconfig'), $timezone), 'crw_cajax_cstartdate');
        }

        // Добавим краткое название
        if ( isset($course->shortname) )
        { // Есть краткое название
            $title = html_writer::span(get_string('ajax_courseshortname', 'local_crw'));
            $html .= html_writer::div($title . $course->shortname, 'crw_cajax_cshortname');
        }

        // Добавим требуемые навыки
        $course_difficult = local_crw_get_course_config($course->id, 'course_difficult');
        if ( ! empty($course_difficult) )
        {
        	$course_difficult_localize = get_string('course_difficult_'.$course_difficult, 'local_crw');
            $title = html_writer::span(get_string('ajax_coursedifficult', 'local_crw'));
            $html .= html_writer::div($title . $course_difficult_localize, 'crw_cajax_courseblock_difficult');
        }
        // Добавим дополнительное описание курса
        $additional_description = local_crw_get_course_config($course->id, 'additional_description');
        if ( ! empty($additional_description) )
        {
            $html .= html_writer::div($additional_description, 'crw_cajax_courseblock_additional_description');
        }

        //ссылка на курс
        $config = get_config('crw_courses_list_ajax', 'use_course_link');
        if ( ! empty($config) )
        {
            $courselink = new moodle_url('/course/view.php',
                [
                    'id' => $course->id
                ]);
        } else
        {
            $courselink = new moodle_url('/local/crw/course.php',
                [
                    'id' => $course->id
                ]);
        }
        // Формируем ссылку
        $context = context_course::instance($course->id, MUST_EXIST);
    	$isenrolled = is_enrolled($context, $USER, '', true);
        if ( $isenrolled )
        {// Пользователь подписан на курс, или является администратором
            $linktext = get_string('link_viewcourse', 'local_crw');
        } else
        {
            $linktext = get_string('enrol', 'crw_courses_list_ajax');
        }
        $courseviewlink = html_writer::link(
            $courselink,
            $linktext,
            [
                'class' => 'button btn btn-primary'
            ]
        );

        // Формируем блок
        $html .= html_writer::div($courseviewlink, 'crw_cajax_link');

        return html_writer::div($html, 'crw_cajax_right');
    }
}