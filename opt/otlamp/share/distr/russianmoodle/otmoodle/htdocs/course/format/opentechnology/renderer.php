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
 * Плагин формата курсов OpenTechnology. Рендер плагина.
 *
 * @package    format
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Подключение бибилиотек
require_once($CFG->dirroot.'/course/format/renderer.php');

class format_opentechnology_renderer extends format_section_renderer_base
{
    /**
     * Уровень текущего устройства, для которого будет отображаться формат курса
     *              0 - Компьютер
     *              1 - Планшет
     *              2 - Мобильный телефон
     * @var int
     */
    private $devicelevel = 0;

    /**
     * Настройки состояния разделов для пользователя
     * @var array|NULL
     */
    private $userpreference = [];

    /**
     * Пользователь может переходить в режим редактирования
     * @var bool
     */
    private $userisediting = FALSE;

    /**
     * Формат курса
     *
     * @var format_opentechnology|NULL
     */
    private $courseformat = NULL;

    /**
     * Контекст текущего курса
     *
     * @var stdClass
     */
    private $context = NULL;

    /**
     * Изображения бэйджей
     *
     * @var array|NULL
     */
    private $badgeicons = [];

    /**
     * Конструктор
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target)
    {
        global $PAGE;

        parent::__construct($page, $target);

        // Установка формата курса
        $this->courseformat = course_get_format($page->course);
        $this->userisediting = $PAGE->user_is_editing();

        $this->context = context_course::instance($page->course->id);

        $page->set_other_editing_capability('moodle/course:setcurrentsection');
    }

    /**
     * Установка уровня устройства, для которого требуется сформировать формат курса
     *
     * @param int $level - Уровень устройства
     *              0 - Компьютер
     *              1 - Планшет
     *              2 - Мобильный телефон
     *
     * @return void
     */
    public function set_device_level($level)
    {
        switch ($level)
        {
            case 1 :
            case 2:
                $this->devicelevel = $level;
                break;
            default:
                $this->devicelevel = 0;
                break;
        }
    }

    /**
     * Установка состояния свернутости разделов
     *
     * @param array|NULL - Массив настроек разделов в виде
     *                     'Номер раздела' => 0/1 (Свернут, Раскрыт)
     *
     * @return void
     */
    public function set_user_preference($preference = NULL)
    {
        $this->userpreference = $preference;
    }

    /**
     * Сформировать начальный HTML-код обертки для списка разделов
     *
     * @return string
     */
    protected function start_section_list()
    {
        $courseformat_settings = $this->courseformat->get_settings();

        $classes = ['format_opentechnology_sections'];
        if ( ! $this->page->user_is_editing() )
        {// Страница не редактируется
            $classes[] = 'not-editing';
        } else
        {// Страница редактируется
            $classes[] = 'editing';
        }


        if ( isset($courseformat_settings['display_mode']) )
        {
            $classes[] = (string)$courseformat_settings['display_mode'];
            if ( $courseformat_settings['display_mode'] == "format_opentechnology_carousel" )
            {//по умолчанию добавляем анимацию в виде слайдера с затенением
                $classes[] = "carousel-slide";
            }
        }

        if ( isset($courseformat_settings['elements_display_mode']) )
        {
            if ( $courseformat_settings['elements_display_mode'] == "format_opentechnology_icon_elements_view"
                 || $courseformat_settings['elements_display_mode'] == "format_opentechnology_icon_with_badges_elements_view"
            )
            {//включено отображение в виде иконок
                $classes[] = 'icon_elements_view';
            }
        }

        $attributes = [];
        switch ( $this->devicelevel )
        {
            case '1' :
                $classes[] = 'display_tablet';
                break;
            case '2' :
                $classes[] = 'display_mobile';
                break;
            default :
                $classes[] = 'display_pc';
                break;
        }
        $attributes['class'] = implode(' ', $classes);


        return html_writer::start_tag('ul', $attributes);
    }

    /**
     * Сформировать закрывающий HTML-код обертки для списка разделов
     *
     * @return string
     */
    protected function end_section_list()
    {
        return html_writer::end_tag('ul');
    }

    /**
     * Сформировать заголовок страницы
     *
     * @return string
     */
    protected function page_title()
    {
        return get_string('topicoutline');
    }

    /**
     * Сформировать HTML страницы курса для одного раздела
     *
     * @param stdClass $course - Объект курса
     * @param array $sections - Не используется
     * @param array $mods Не используется
     * @param array $modnames Не используется
     * @param array $modnamesused Не используется
     * @param int $displaysection - Номер раздела для отображения на странице
     *
     * @return void
     */
    public function print_single_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection)
    {
        parent::print_single_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection);
    }

    /**
     * Сформировать HTML страницы курса для всех разделов
     *
     * @param stdClass $course - Объект курса
     * @param array $sections - Не используется
     * @param array $mods Не используется
     * @param array $modnames Не используется
     * @param array $modnamesused Не используется
     *
     * @return void
     */
    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused)
    {
        global $CFG;
        // Получить базовые данные для генерации страницы
        $modinfo = get_fast_modinfo($course);
        $course = $this->courseformat->get_course();
        $courseformat_settings = $this->courseformat->get_settings();
        $context = context_course::instance($course->id);


        // Заголовок страницы с данными о завершении курса
        $completioninfo = new completion_info($course);
        echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');

        // Отображение сообщений от модулей курса
        echo $this->course_activity_clipboard($course, 0);

        // ГЕНЕРАЦИЯ РАЗДЕЛОВ
        echo $this->start_section_list();
        // Получение разделов курса
        $sections = $modinfo->get_section_info_all();

        // Переорпеделение иконок в курсе
        $elements_display_mode = false;
        if ( isset($courseformat_settings['elements_display_mode']) )
        {
            $elements_display_mode = $courseformat_settings['elements_display_mode'];
        }

        // Обработка модулей в разделах
        foreach ( $sections as $section )
        {
            if ( ! empty($modinfo->sections[$section->section]) )
            {// Модули в разделе найдены
                $lastsmodnumber = count($modinfo->sections[$section->section]);
                // Обработка каждого модуля в разделе
                foreach ( $modinfo->sections[$section->section] as $counter => $modnumber )
                {
                    $mod = $modinfo->cms[$modnumber];
                    // Дополнительные классы
                    $extraclasses = $mod->extraclasses.' ';
                    if ( $lastsmodnumber == 0 )
                    {// Первый элемент в разделе
                        $extraclasses .= 'first ';
                    }
                    if ( $lastsmodnumber == 0 )
                    {// Последний элемент в разделе
                        $extraclasses .= 'last ';
                    }

                    $extraclasses .= 'otformat-modindent-'.$mod->indent.' ';
                    if ( $mod->indent )
                    {
                        $extraclasses .= 'otformat-modindent ';
                    }

                    // Отслеживание выполнения
                    if ( $completioninfo === null )
                    {
                        $completioninfo = new completion_info($course);
                    }
                    // Тип выполнения элемента
                    $completion = $completioninfo->is_enabled($mod);
                    $icon = 'nonecomplete';
                    if ( $completion == COMPLETION_TRACKING_NONE )
                    {// Система отслеживания выполнения элементов не включена в курсе
                        $extraclasses .= 'completion_disabled';
                    } else
                    {// Отслеживание выполнения включено в курсе

                        // Получение данных о завершении элемента
                        $completiondata = $completioninfo->get_data($mod, true);
                        // Установка класса в зависимости от состояния
                        if ( $completion == COMPLETION_TRACKING_MANUAL )
                        {// Установка вручную
                            switch( $completiondata->completionstate )
                            {
                                // Элемент не завершен
                                case COMPLETION_INCOMPLETE:
                                    $extraclasses .= 'completion_incompleted';
                                    break;
                                // Элемент завершен
                                case COMPLETION_COMPLETE:
                                    $extraclasses .= 'completion_completed';
                                    $icon = 'complete';
                                    break;
                            }
                        } else
                        {// Автоматическое определение
                            switch($completiondata->completionstate)
                            {
                                // Элемент не завершен
                                case COMPLETION_INCOMPLETE:
                                    $extraclasses .= 'completion_incompleted';
                                    break;
                                // Элемент завершен
                                case COMPLETION_COMPLETE:
                                case COMPLETION_COMPLETE_PASS:
                                    $extraclasses .= 'completion_completed';
                                    $icon = 'complete';
                                    break;
                                case COMPLETION_COMPLETE_FAIL:
                                    $extraclasses .= 'completion_failed';
                                    $icon = 'fail';
                                    break;
                            }
                        }
                    }
                    // Добавление классов модуля
                    $mod->set_extra_classes($extraclasses);

                    //проверяем включено ли отображение в виде иконок
                    if ( ($elements_display_mode == "format_opentechnology_icon_elements_view" ||
                        $elements_display_mode == "format_opentechnology_icon_with_badges_elements_view")
                        && !$this->page->user_is_editing() )
                    {// Требуется смена иконки
                        //ожидаемый адрес иконки в теме
                        $iconlocation = $this->page->theme->dir."/pix_plugins/mod/".$mod->modname."/icon_".$icon;
                        if ( file_exists("$iconlocation.svg") || file_exists("$iconlocation.png") )
                        {//иконка в теме есть - будем использовать ее
                            // Изменение иконки элемента курса
                            $currenticon = $mod->get_icon_url($this->courserenderer);
                            $path = $currenticon->get_path().'_'.$icon;
                            $currenticon->set_slashargument($path);
                            $mod->set_icon_url($currenticon);
                        }
                    }
                }
            }
        }

        // Генерация раздела "Введение"
        $thissection = $sections[0];
        unset($sections[0]);
        if ( $thissection->summary || ! empty($modinfo->sections[0]) || $this->userisediting )
        {// Требуется отображение раздела
            echo $this->section_header($thissection, $course, FALSE, 0);
            echo $this->course_section_cm_list($course, $thissection, 0);
            echo $this->courserenderer->course_section_add_cm_control($course, $thissection->section, 0, 0);
            echo $this->section_footer();
            if( ! empty($thissection->lastinrow) )
            {// форсирование переноса секции
                echo html_writer::tag('li', '', ['class'=>'cf_ot_fakesection_forcebreak']);
            }
        }

        // Генерация остальных разделов
        if ( $course->numsections > 0 )
        {// В курсе есть разделы

            if ( $course->numsections > 1 )
            {
                if ( $this->userisediting || $course->coursedisplay != COURSE_DISPLAY_MULTIPAGE )
                {// Отобразить кнопки "Показать все" и "Скрыть все"
                    echo $this->toggle_all_buttons();
                }
            }

            foreach ( $sections as $section => $thissection )
            {
                if ( $section > $course->numsections )
                {
                    // Модули раздела будут отображаться как скрытые. Обработчик ниже.
                    continue;
                }

                // Проверка необходимости отображения раздела для пользователя
                $showsection = $thissection->uservisible ||
                    ( $thissection->visible && ! $thissection->available && ! empty($thissection->availableinfo) );

                if ( ! $showsection)
                {// Раздел скрыт для пользователя
                    // Отображение в зависимости от настройки
                    if ( ! $course->hiddensections && $thissection->available )
                    {// Отображать невидимые разделы как скрытые
                        echo $this->section_hidden($thissection, $course->id);
                    }
                    continue;
                }

                if ( ! $this->userisediting && $course->coursedisplay == COURSE_DISPLAY_MULTIPAGE)
                {
                    echo $this->section_summary($thissection, $course, null);
                } else
                {
                    // Получение состояния раздела
                    $togglestate = substr($this->userpreference, (int)$section, 1);
                    // Получение состояния раздела
                    if ( $course->marker == $section )
                    {
                        $togglestate = 1;
                    }
                    $thissection->toggle = (bool)$togglestate;
                    echo $this->section_header($thissection, $course, false, 0);
                    if ($thissection->uservisible)
                    {
                        echo $this->course_section_cm_list($course, $thissection, 0);
                        echo $this->courserenderer->course_section_add_cm_control($course, $thissection->section, 0);
                    }
                    echo $this->section_footer();
                    if( ! empty($thissection->lastinrow) )
                    {// форсирование переноса секции
                        echo html_writer::tag('li', '', ['class'=>'cf_ot_fakesection_forcebreak']);
                    }
                }
            }
        }

        if ( $this->userisediting and has_capability('moodle/course:update', $context) )
        {// Отобразить невидимые разделы
            foreach ( $modinfo->get_section_info_all() as $section => $thissection )
            {
                if ( $section <= $course->numsections || empty($modinfo->sections[$section]) )
                {
                    continue;
                }
                echo $this->stealth_section_header($section);
                echo $this->course_section_cm_list($course, $thissection, 0);
                echo $this->stealth_section_footer();
            }

            echo $this->end_section_list();

            echo html_writer::start_tag('div', array('id' => 'changenumsections', 'class' => 'mdl-right'));

            // Кнопка добавления раздела
            $straddsection = get_string('increasesections', 'moodle');
            $url = new moodle_url('/course/changenumsections.php',
                [
                    'courseid' => $course->id,
                    'increase' => TRUE,
                    'sesskey' => sesskey()
                ]
            );
            $icon = $this->output->pix_icon('t/switch_plus', $straddsection);
            echo html_writer::link($url, $icon . get_accesshide($straddsection), ['class' => 'increase-sections']);

            // Кнопка удаления раздела
            if ( $course->numsections > 0 )
            {
                $strremovesection = get_string('reducesections', 'moodle');
                $url = new moodle_url('/course/changenumsections.php',
                    [
                        'courseid' => $course->id,
                        'increase' => FALSE,
                        'sesskey' => sesskey()
                    ]
                );
                $icon = $this->output->pix_icon('t/switch_minus', $strremovesection);
                echo html_writer::link($url, $icon . get_accesshide($strremovesection), ['class' => 'reduce-sections']);
            }

            echo html_writer::end_tag('div');

        } else
        {
            echo $this->end_section_list();
        }
    }

    /**
     * Отобразить кнопки массового сворачивания/разворачивания разделов
     *
     * @return string - HTML-код
     */
    protected function toggle_all_buttons()
    {
        $course_settings = $this->courseformat->get_settings();
        $html = '';
        // Если включен режим аккордеон - отобразим загрузчик
        if( $course_settings['display_mode'] == 'format_opentechnology_carousel' )
        {
            $html .= html_writer::tag('li', '',['class'=>'format_opentechnology_carousel_loader']);
        }
        $html .= html_writer::start_tag('li', ['id' => 'course-format-tools', 'class' => 'main clearfix']);
        // Если включен режим спойлера, отобразить кнопки Свернуть все/Развернуть все
        if( $course_settings['display_mode'] == 'format_opentechnology_spoiler' )
        {
            $html .= html_writer::start_tag('div', ['class' => 'format_opentechnology_toggleall_wrapper']);
            $html .= html_writer::tag(
                'a',
                html_writer::span(get_string('toggleall_collapse', 'format_opentechnology')),
                ['class' => 'toggleall_collapse_button button btn btn-primary', 'href' => '#', 'id' => 'toggles-all-opened']). PHP_EOL;
            $html .= html_writer::tag(
                'a',
                html_writer::span(get_string('toggleall_expand', 'format_opentechnology')),
                ['class' => 'toggleall_expand_button button btn btn-primary', 'href' => '#', 'id' => 'toggles-all-closed']). PHP_EOL;

            $html .= html_writer::end_tag('div');
        }
        $html .= html_writer::end_tag('li');
        return $html;
    }

   /**
    * Сформировать HTML скрытого раздела
    *
    * @param stdClass $section - Раздел курсе, который требуется отобразить
    * @param int|stdClass $courseorid - ID курса или объект курса
    *
    * @return string - HTML-код
    */
    protected function section_hidden($section, $courseorid = NULL)
    {
        $html = '';

        if ( $courseorid )
        {
            $sectionname = get_section_name($courseorid, $section);
            $strnotavailable = get_string('notavailablecourse', '', $sectionname);
        } else {
            $strnotavailable = get_string('notavailable');
        }

        $html .= html_writer::start_tag(
            'li',
            [
                'id' => 'section-' . $section->section,
                'class' => 'section main clearfix hidden',
                'role' => 'region',
                'aria-label' => get_section_name($courseorid, $section)
            ]
        );
        $html .= html_writer::tag('div', '', ['class' => 'left side']);
        $html .= html_writer::tag('div', '', ['class' => 'right side']);
        $html .= html_writer::start_tag('div', ['class' => 'content sectionhidden']);
        $html .= html_writer::tag('div', $strnotavailable);
        $html .= html_writer::end_tag('div');
        $html .= html_writer::end_tag('li');

        return $html;
    }

    /**
     * Получить правую часть раздела
     *
     * @param stdClass $course - Объект курса
     * @param stdClass $section - Раздел курса
     * @param bool $onsectionpage - Флаг типа страницы курса
     *
     * @return string - HTML-код
     */
    protected function section_right_content($section, $course, $onsectionpage)
    {
        $html = '';

        // Действия над разделом
        $controls = $this->section_edit_control_items($course, $section, $onsectionpage);
        $html .= $this->section_edit_control_menu($controls, $course, $section);

        return $html;
    }

    /**
     * Получить левую часть раздела
     *
     * @param stdClass $course - Объект курса
     * @param stdClass $section - Раздел курса
     * @param bool $onsectionpage - Флаг типа страницы курса
     *
     * @return string - HTML-код
     */
    protected function section_left_content($section, $course, $onsectionpage)
    {
        $html = '';

        if ( $section->section != 0 )
        {// Раздел определен
            if ( $this->courseformat->is_section_current($section) )
            {
                $html .= get_accesshide(get_string('currentsection', 'format_opentechnology'));
            }
        }
        return $html;
    }

    /**
     * Отобразить заголовок секции
     *
     * @param stdClass $section The course_section entry from DB.
     * @param stdClass $course The course entry from DB.
     * @param bool $onsectionpage true if being printed on a section page.
     * @param int $sectionreturn The section to return to after an action.
     * @return string HTML to output.
     */
    protected function section_header($section, $course, $onsectionpage, $sectionreturn = null)
    {
        $html = '';

        // Базовые переменные
        $context = context_course::instance($course->id);
        $course_settings = $this->courseformat->get_settings();
        // Дополнительные классы секции
        $additionalclass = '';

        if ( $section->section != 0)
        {// Раздел определен
            if ( ! $section->visible )
            {// Раздел скрыт
                $additionalclass .= ' hidden';
            } else if ( $this->courseformat->is_section_current($section) )
            {// Активный раздел
                $section->toggle = true;
                $additionalclass .= ' current';
            }
        }

        // Установка ширины секции
        $sectionwidth = $this->courseformat->get_section_width($section->section);
        if( ! empty($sectionwidth) )
        {
            $additionalclass .= ' cf_ot_section_width_'.(int)$sectionwidth;
        }

        if( ! empty($section->lastinrow) )
        {
            $additionalclass .= ' cf_ot_section_lastinrow';
        }
        if( ! empty($section->summary_width) )
        {
            $additionalclass .= ' cf_ot_section_summary_width_'.(int)$section->summary_width;
        }

        // Формироваине начала секции
        $liattributes = [
            'id' => 'section-' . $section->section,
            'class' => 'section main ' . $additionalclass,
            'role' => 'region',
            'aria-label' => get_section_name($course, $section)
        ];
        $html .= html_writer::start_tag('li', $liattributes);

        // Формирование колонок секции
        $leftcontent = $this->section_left_content($section, $course, $onsectionpage);
        $rightcontent = $this->section_right_content($section, $course, $onsectionpage);

        if ( $this->userisediting && has_capability('moodle/course:update', $context) )
        {// Все разделы раскрыты в режиме редактирования
            $section->toggle = true;
        }

        // Сформировать управляющие блоки
        $html .= html_writer::tag('div', $leftcontent, ['class' => 'left side']);
        $html .= html_writer::tag('div', $rightcontent, ['class' => 'right side']);

        // Сформировать контент раздела
        $html .= html_writer::start_tag('div', ['class' => 'content']);
        if ( ( $onsectionpage == false ) && ( $section->section != 0 ) )
        {
            // Определение режима работы формата курса
            $mode = str_replace('format_opentechnology_', '', (string)$course_settings['display_mode']);

            if ( $mode == 'accordion' || $mode == 'spoiler' )
            {//классы для сворачиваний отображаем только если настроен соответствующий режим отобржения секций
                // Формирование данных в зависимости от состояния секции
                if ( $section->toggle === true )
                {// Секция развернута
                    $state = ' toggle_open';
                    $sectionclass = ' toggled_section sectionopen';
                } else
                {// Секция свернута
                    $state = ' toggle_closed';
                    $sectionclass = ' toggled_section sectionclosed';
                }

                // Заголовок раздела
                $html .= html_writer::start_tag(
                    'div',
                    [
                        'class' => 'sectionhead clear toggle '.$mode.$state,
                        'id' => 'toggle-' . $section->section
                    ]
                );
            } else
            {
                $sectionclass = '';
                // Заголовок раздела
                $html .= html_writer::start_tag(
                    'div',
                    [
                        'class' => 'sectionhead clear',
                    ]
                );
            }

            // Добавление выравнивания заголовка в зависимости от настройки
            $align = $course_settings['caption_align'];
            if( empty($align) )
            {
                $align = 'center';
            }
            $captionclass = 'align-'.$align;

            // Заголовок
            if ( $mode == 'base' || $mode=='accordion' )
            {
                $html .= html_writer::start_tag(
                    'span',
                    [
                        'class' => 'the_header'
                    ]
                );
            } else
            {
                $headerurl = new moodle_url('/course/view.php', array('id' => $course->id));
                $html .= html_writer::start_tag(
                    'a',
                    [
                        'class' => 'the_header',
                        'data-href' => $headerurl,
                        'href' => ''
                    ]
                );
            }

            $html .= $this->output->heading($this->section_title($section, $course), 3, 'sectionname ' . $captionclass);

            // Отображение иконок заголовка
            if ( $course_settings['caption_icons_enabled'] == 1 )
            {// Требуется отображение иконок
                $caption_icons = '';
                // Получение иконок
                $caption_icon_open = $this->get_caption_icon_url_open($course);
                $attributes = [];
                if ( $caption_icon_open )
                {
                    $attributes = ['style' => 'background-image: url("' . $caption_icon_open->out() . '");'];
                }
                $caption_icons .= html_writer::div('', 'toggle_icon open', $attributes);

                $caption_icon_closed = $this->get_caption_icon_url_closed($course);
                $attributes = [];
                if ( $caption_icon_closed )
                {
                    $attributes = ['style' => 'background-image: url("' . $caption_icon_closed->out() . '");'];
                }
                $caption_icons .= html_writer::div('', 'toggle_icon closed', $attributes);
                // Обертка для иконок
                $html .= html_writer::div($caption_icons, 'wrapper_toggle_icon');
                $html .= html_writer::div('', 'clear');
            }
            // Заголовок
            if ( $mode == 'base' )
            {
                $html .= html_writer::end_tag('span');
            } else
            {
                $html .= html_writer::end_tag('a');
            }

            $html .= html_writer::end_div();

            // Контент раздела
            $html .= html_writer::start_tag(
                'div',
                [
                    'class' => 'sectionbody' . $sectionclass,
                     'id' => 'toggledsection-' . $section->section
                ]
            );
            $html .= html_writer::start_div();

            $html .= $this->section_summary_container($section);
            $html .= $this->section_availability_message($section,
                        has_capability('moodle/course:viewhiddensections', $context)
            );

        } else
        {
            $hasnamesecpg = ( $section->section == 0 && (string) $section->name !== '' );

            if ( $hasnamesecpg )
            {
                $html .= $this->output->heading($this->section_title($section, $course), 3, 'sectionname');
            }
            $html .= html_writer::start_tag('div', ['class' => 'summary']);
            $html .= $this->format_summary_text($section);

            $html .= html_writer::end_tag('div');

            $html .= $this->section_availability_message($section,
                has_capability('moodle/course:viewhiddensections', $context));
        }
        return $html;
    }

    /**
     * Футер раздела
     *
     * @return string HTML to output.
     */
    protected function section_footer() {
        $o = html_writer::end_tag('div');
        $o = html_writer::end_tag('div');
        $o.= html_writer::end_tag('li');

        return $o;
    }

    protected function section_summary_container($section)
    {
        $summarytext = $this->format_summary_text($section);
        if ( $summarytext )
        {
            $html = html_writer::start_tag('div', ['class' => 'summary']);
            $html .= $summarytext;
            $html .= html_writer::end_tag('div');
        } else
        {
            $html = '';
        }
        return $html;
    }

    /**
     * Получить кнопки для управления разделом
     *
     * @param stdClass $course - Объект курса
     * @param stdClass $section - Раздел курса
     * @param bool $onsectionpage - Флаг типа страницы курса
     *
     * @return array - Массив ссылок
     */
    protected function section_edit_control_items($course, $section, $onsectionpage = FALSE)
    {
        if ( ! $this->userisediting)
        {// Пользователь не в режиме редактирования
            return [];
        }

        $coursecontext = context_course::instance($course->id);

        // Сформировать базовый URL действий
        if ($onsectionpage)
        {
            $url = course_get_url($course, $section->section);
        } else {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());

        $controls = [];
        // Кнопка установки текущего раздела активным разделом
        if ( has_capability('moodle/course:setcurrentsection', $coursecontext) && $section->section != 0 )
        {
            if ( $course->marker == $section->section )
            {// Раздел уже установлен как активный
                // Кнопка сброса активного раздела
                $url->param('marker', 0);
                $controls['marker'] = [
                    'url' => $url,
                    "icon" => 'i/marked',
                    'name' => get_string('markedthistopic'),
                    'pixattr' => [
                        'class' => 'icon',
                        'alt' => get_string('markedthistopic')
                    ],
                    'attr' => [
                        'class' => 'editing_highlight',
                        'title' => get_string('markedthistopic')
                    ]
                ];
            } else
            {// Раздел не-активный
                // Кнопка установки активного раздела
                $url->param('marker', $section->section);
                $controls['marker'] = [
                    'url' => $url,
                    "icon" => 'i/marker',
                    'name' => get_string('markthistopic'),
                    'pixattr' => [
                        'class' => 'icon',
                        'alt' => get_string('markthistopic')
                    ],
                    'attr' => [
                        'class' => 'editing_highlight',
                        'title' => get_string('markthistopic')
                    ]
                ];
            }
        }

        return array_merge($controls, parent::section_edit_control_items($course, $section, $onsectionpage));
    }

    /**
     * Сгенерировать модули раздела курса
     *
     * @param stdClass $course - Объект курса
     * @param int|stdClass|section_info $section - Номер или объект раздела
     * @param int $sectionreturn section number to return to
     * @param int $displayoptions
     *
     * @return void
     */
    private function course_section_cm_list($course, $section, $sectionreturn = null, $displayoptions = array()) {
        global $USER;

        $output = '';
        $modinfo = get_fast_modinfo($course);
        if (is_object($section)) {
            $section = $modinfo->get_section_info($section->section);
        } else {
            $section = $modinfo->get_section_info($section);
        }
        $completioninfo = new completion_info($course);

        // check if we are currently in the process of moving a module with JavaScript disabled
        $ismoving = $this->page->user_is_editing() && ismoving($course->id);
        if ($ismoving)
        {
            $movingpix = new pix_icon('movehere', get_string('movehere'), 'moodle', array('class' => 'movetarget'));
            $strmovefull = strip_tags(get_string("movefull", "", "'$USER->activitycopyname'"));
        }

        // Get the list of modules visible to user (excluding the module being moved if there is one)
        $moduleshtml = array();
        // первый модуль в секции
        $firstmodinsection = true;
        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];
                $modhtml = '';

                $displayoptions['firstmodinsection']=$firstmodinsection;
                $firstmodinsection = false;

                if ($ismoving and $mod->id == $USER->activitycopy) {
                    // do not display moving mod
                    continue;
                }

                // Сгенерировать HTML-код блока модуля курса
                $cmhtml = $this->course_section_cm($course, $completioninfo, $mod, $sectionreturn, $displayoptions);
                if ( ! empty($cmhtml) )
                {// Блок получен
                    // Классы блока
                    $modclasses = 'activity '.$mod->modname.' modtype_'.$mod->modname.' '.$mod->extraclasses;
                    if ( ! empty($mod->indent) )
                    {// Модуль с отступами
                        $modclasses .= ' mod_indent mod_indent_'.$mod->indent;
                    } else
                    {
                        $modclasses .= ' mod_indent_none';
                    }

                    $modhtml .= html_writer::tag(
                        'li',
                        $cmhtml,
                        [
                            'class' => $modclasses,
                            'id' => 'module-'.$mod->id
                        ]
                        );

                    // Обертка для модуля курса
                    $moduleshtml[$modnumber] = $modhtml;
                }
            }
        }

        $sectionoutput = '';
        if (!empty($moduleshtml) || $ismoving) {
            foreach ($moduleshtml as $modnumber => $modulehtml) {
                if ($ismoving) {
                    $movingurl = new moodle_url('/course/mod.php', array('moveto' => $modnumber, 'sesskey' => sesskey()));
                    $sectionoutput .= html_writer::tag('li',
                        html_writer::link($movingurl, $this->courserenderer->output->render($movingpix), array('title' => $strmovefull)),
                        array('class' => 'movehere'));
                }

                $sectionoutput .= $modulehtml;
            }

            if ($ismoving) {
                $movingurl = new moodle_url('/course/mod.php', array('movetosection' => $section->id, 'sesskey' => sesskey()));
                $sectionoutput .= html_writer::tag('li',
                    html_writer::link($movingurl, $this->courserenderer->output->render($movingpix), array('title' => $strmovefull)),
                    array('class' => 'movehere'));
            }
        }

        // Always output the section module list.
        $output .= html_writer::tag('ul', $sectionoutput, array('class' => 'section img-text'));

        return $output;
    }

    /**
     * Сгенерировать элемент курса
     *
     * @param stdClass $course - Объект курса
     * @param completion_info $completioninfo - Данные по завершению элемента курса
     * @param cm_info $mod - Модуль курса
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return string
     */
    private function course_section_cm($course, &$completioninfo, cm_info $mod, $sectionreturn, $displayoptions = array())
    {
        global $OUTPUT;

        $html = '';
        $isindent = FALSE;

        if ( ! $mod->uservisible && empty($mod->availableinfo) )
        {// Модуль невидим для пользователя
            return $html;
        }

        // Формирование отступов
        $indentclasses = 'mod-indent';
        if ( ! empty($mod->indent) )
        {
            $indentclasses .= ' mod-indent-'.$mod->indent;
            if ($mod->indent > 15) {
                $indentclasses .= ' mod-indent-huge';
            }
        }

        // Генерация блока модуля
        $html .= html_writer::start_tag('div');

        if ( $this->page->user_is_editing() )
        {// Режим редактирования
            // Добавление функций перемещения
            $html .= course_get_cm_move($mod, $sectionreturn);
        }

        // Обертка контента модуля
        $html .= html_writer::start_tag('div', array('class' => 'mod-indent-outer'));


        // Контент
        $html .= html_writer::start_tag('div');

        // Названия модуля
        $cmname = $this->courserenderer->course_section_cm_name($mod, $displayoptions);
        if ( ! empty($cmname) )
        {// Назваине определено
            $html .= html_writer::start_tag('div', array('class' => 'activityinstance'));
            // Блок значков


            $courseformat_settings = $this->courseformat->get_settings();
            $elements_display_mode = false;
            if ( isset($courseformat_settings['elements_display_mode']) )
            {
                $elements_display_mode = $courseformat_settings['elements_display_mode'];
            }
            //проверим, включено ли отображение со значками
            if ( ! $this->page->user_is_editing() && ($elements_display_mode ==
                 "format_opentechnology_base_with_badges_elements_view" || ($elements_display_mode ==
                 "format_opentechnology_icon_with_badges_elements_view" && $mod->indent == 0))
            )
            {//рядом с этим элементом требуется отобразить значки
                $html .= $this->mod_bages_block($course, $completioninfo, $mod);
            }
            // Блок для формирования отступа
            $html .= html_writer::div('', $indentclasses);

            $html .= $cmname;
            $html .= $mod->afterlink;
            $html .= html_writer::end_tag('div'); // .activityinstance
        }

        // Описание модуля курса
        $contentpart = $this->courserenderer->course_section_cm_text($mod, $displayoptions);
        $url = $mod->url;
        if ( empty($url) )
        {// Ссылок нет - описание добавлено перед всеми иконками
            $html .= $contentpart;
        }

        // Действия над модулем курса
        $modicons = '';
        if ( $this->page->user_is_editing() )
        {// Режим редактирования
            $editactions = course_get_cm_edit_actions($mod, $mod->indent, $sectionreturn);
            $modicons .= ' '. $this->courserenderer->course_section_cm_edit_actions($editactions, $mod, $displayoptions);
            $modicons .= $mod->afterediticons;
        }
        $modicons .= $this->courserenderer->course_section_cm_completion($course, $completioninfo, $mod, $displayoptions);
        if ( ! empty($modicons) )
        {// Действия определены
            $html .= html_writer::span($modicons, 'actions');
        }

        if ( ! empty($url) )
        {// Ссылки есть - описание после иконок
            $html .= $contentpart;
        }

        // Блок с данными о доступности модуля.
        $html .= $this->courserenderer->course_section_cm_availability($mod, $displayoptions);

        $html .= html_writer::end_tag('div');
        $html .= html_writer::end_tag('div');
        $html .= html_writer::end_tag('div');

        return $html;
    }
    /**
     * Блок значков курса
     *
     * @param unknown $course
     * @param unknown $completioninfo
     * @param cm_info $mod
     */
    private function mod_bages_block($course, &$completioninfo, cm_info $mod)
    {
        $html = '';
        if( ! (bool)get_config('core', 'enablebadges') )
        {// Если подсистема значков отключена
            return $html;
        }

        $html .= html_writer::start_div('mod-bages-completion');
        if ( ! isset($this->cmbages) )
        {// Значки модулей курса не определены
            $this->cmbages = [];
            $this->cmcompletebages = [];
            // Получение значков курса
            $badges = (array)badges_get_badges(BADGE_TYPE_COURSE, $course->id, '', '' , 0, 0);
            foreach ( $badges as $badge )
            {
                $this->cmcompletebages[$badge->id] = [];
                $criteria = (array)$badge->get_criteria();

                foreach ( $criteria as $c )
                {
                    if ( get_class($c) == 'award_criteria_activity' )
                    {// В значке имеется критерий выдачи по завершению модулей
                        $this->cmbages[$badge->id] = $badge;
                        $cms = (array)$c->params;

                        foreach ( $cms as $cmid => $cm )
                        {
                            $this->cmcompletebages[$badge->id][$cmid] = $cm;
                        }
                    }
                }
            }
        }
        if ( ! empty($this->cmbages) )
        {// Есть значки с условием выдачи по модулям курса
            foreach ( $this->cmbages as $badge )
            {
                if( !array_key_exists($badge->id, $this->badgeicons) )
                {//такое изображение мы еще не получали
                    $this->badgeicons[$badge->id] = print_badge_image($badge, $this->context, 'f1');
                }
                if ( isset($this->cmcompletebages[$badge->id][$mod->id]) )
                {// Модуль курса участвует в учете выполнения значков
                    $html .= html_writer::span($this->badgeicons[$badge->id], 'cm-using-badge');
                } else
                {// Модуль курса не участвует в условиях выполнения
                    $html .= html_writer::span($this->badgeicons[$badge->id], 'cm-not-using-badge');
                }
            }
        }
        $html .= html_writer::end_div();
        return $html;
    }

    /**
     * Получить URL иконки открытого заголовка
     *
     * @return moodle_url|null
     */
    protected function get_caption_icon_url_open($course)
    {
        static $icon;
        if ( is_null($icon) )
        {// Получение иконки
            // Поиск иконки в локальных настройках формата курса
            $fs = get_file_storage();
            $context = context_course::instance($course->id);
            $files = $fs->get_area_files($context->id, 'format_opentechnology', 'caption_icon_toggle_open');
            foreach ( $files as $file )
            {
                if ( $file->is_valid_image() )
                {// Иконка найдена
                    $icon = moodle_url::make_pluginfile_url(
                        $file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea(),
                        $file->get_itemid(),
                        $file->get_filepath(),
                        $file->get_filename()
                    );
                }
            }
            if ( empty($icon) )
            {// Иконка не найдена в локальных настройках, получение глобальной иконки
                $context = context_system::instance();
                $files = $fs->get_area_files($context->id, 'format_opentechnology', 'caption_icon_toggle_open');
                foreach ( $files as $file )
                {
                    if ( $file->is_valid_image() )
                    {// Иконка найдена
                        $icon = moodle_url::make_pluginfile_url(
                            $file->get_contextid(),
                            $file->get_component(),
                            $file->get_filearea(),
                            $file->get_itemid(),
                            $file->get_filepath(),
                            $file->get_filename()
                        );
                    }
                }
            }
        }
        return $icon;
    }

    /**
     * Получить URL иконки закрытого заголовка
     *
     * @return moodle_url|null
     */
    protected function get_caption_icon_url_closed($course)
    {
        static $icon;
        if ( is_null($icon) )
        {// Получение иконки
            // Поиск иконки в локальных настройках формата курса
            $fs = get_file_storage();
            $context = context_course::instance($course->id);
            $files = $fs->get_area_files($context->id, 'format_opentechnology', 'caption_icon_toggle_closed');
            foreach ( $files as $file )
            {
                if ( $file->is_valid_image() )
                {// Иконка найдена
                    $icon = moodle_url::make_pluginfile_url(
                        $file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea(),
                        $file->get_itemid(),
                        $file->get_filepath(),
                        $file->get_filename()
                        );
                }
            }
            if ( empty($icon) )
            {// Иконка не найдена в локальных настройках, получение глобальной иконки
                $context = context_system::instance();
                $files = $fs->get_area_files($context->id, 'format_opentechnology', 'caption_icon_toggle_closed');
                foreach ( $files as $file )
                {
                    if ( $file->is_valid_image() )
                    {// Иконка найдена
                        $icon = moodle_url::make_pluginfile_url(
                            $file->get_contextid(),
                            $file->get_component(),
                            $file->get_filearea(),
                            $file->get_itemid(),
                            $file->get_filepath(),
                            $file->get_filename()
                            );
                    }
                }
            }
        }
        return $icon;
    }
}