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
 * Плагин формата курсов OpenTechnology. Класс плагина.
 *
 * @package    format
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot. '/course/format/lib.php');

class format_opentechnology extends format_base
{
    /**
     * Настройки формата для текущего курса
     *
     * @var array
     */
    private $settings = [];
    
    /**
     * Создание экземпляра формата курса
     *
     * @return format_topcoll
     */
    protected function __construct($format, $courseid)
    {
        if ( $courseid === 0 )
        {// Багфикс получения идентификатора курса
            global $COURSE;
            $courseid = $COURSE->id;
        }
        parent::__construct($format, $courseid);
    }
    
    /**
     * Использование форматом курса разделов элементов
     *
     * @return bool
     */
    public function uses_sections()
    {
        return true;
    }
    
    /**
     * Поддержка AJAX форматом курса
     *
     * @return stdClass - Объект с опциями
     */
    public function supports_ajax()
    {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
    
        return $ajaxsupport;
    }
    
    /**
     * {@inheritDoc}
     * @see format_base::supports_news()
     */
    public function supports_news() {
        return true;
    }
    
    /**
     * Формирование полей настройки секции
     */
    public function section_format_options($foreditform = false)
    {
        // Массив параметров секции
        $result = [];
        
        // Получение опций формата курса
        $courseformatoptions = $this->course_format_options();

        // Получение текущих настроек формата курса
        $formatoptions = $this->get_format_options();
        
        // ШИРИНА СЕКЦИИ
        $defaultwidth = '100';
        if ( ! empty($courseformatoptions['section_width']['default']) )
        {// Установлена глобальная настройка секции
            // Настройка по умолчанию
            $defaultwidth = $courseformatoptions['section_width']['default'];
        }
        // Генерация поля формы настройки секции
        $width = [
            'element_type' => 'select',
            'label' => get_string('course_section_settings_section_width','format_opentechnology'),
            'element_attributes' => [
                [
                    '100' => '100%',
                    '75' => '75%',
                    '66' => '66.66%',
                    '50' => '50%',
                    '33' => '33.33%',
                    '25' => '25%'
                ]
            ],
            'help' => 'course_settings_section_width',
            'help_component' => 'format_opentechnology',
            'default' => $defaultwidth,
            'type' => PARAM_INT,
        ];
        if ( empty($formatoptions['course_display_mode']) )
        {// Выбран не преднастроенный режим. Можно предоставить возможность выбора ширины
            $result['width'] = $width;
        }
        
        
        
        // ЗАВЕРШЕНИЕ РАЗДЕЛА
        $defaultlastinrow = '0';
        if ( ! empty($courseformatoptions['section_lastinrow']['default']) )
        {// Установлена глобальная настройка секции
            $defaultlastinrow = $courseformatoptions['section_lastinrow']['default'];
        }
        // Генерация поля формы настройки секции
        $lastinrow = [
            'element_type' => 'select',
            'label' => get_string('course_section_settings_section_lastinrow','format_opentechnology'),
            'element_attributes' => [
                [
                    '0' => get_string('no'),
                    '1' => get_string('yes'),
                ]
            ],
            'help' => 'course_section_settings_section_lastinrow',
            'help_component' => 'format_opentechnology',
            'default' => $defaultlastinrow,
            'type' => PARAM_INT,
        ];
        $result['lastinrow'] = $lastinrow;

        
        // ШИРИНА ОПИСАНИЯ СЕКЦИИ
        $defaultsummarywidth = '100';
        if ( ! empty($courseformatoptions['section_summary_width']['default']) )
        {// Установлена глобальная настройка секции
            $defaultsummarywidth = $courseformatoptions['section_summary_width']['default'];
        }
        // Генерация поля формы настройки секции
        $summarywidth = [
            'element_type' => 'select',
            'label' => get_string('course_section_settings_section_summary_width','format_opentechnology'),
            'element_attributes' => [
                [
                    '100' => '100%',
                    '75' => '75%',
                    '66' => '66.66%',
                    '50' => '50%',
                    '33' => '33.33%',
                    '25' => '25%'
                ]
            ],
            'help' => 'course_settings_section_summary_width',
            'help_component' => 'format_opentechnology',
            'default' => $defaultsummarywidth,
            'type' => PARAM_INT,
        ];
        $result['summary_width'] = $summarywidth;
        
        
        // Возврат параметров секции
        return $result;
    }
    
    /**
     * Получить название раздела
     *
     * @param int|stdClass $section - Объект раздела из БД или номер раздела
     *
     * @return string - Отображаемое название раздела
     */
    public function get_section_name($section)
    {
        // Получение раздела
        $section = $this->get_section($section);
        
        if ( (string)$section->name !== '' )
        {// Имя раздела указано
            return format_string(
                $section->name,
                true,
                ['context' => context_course::instance($this->courseid)]
            );
        } else if ( $section->section == 0 )
        {// Вводный раздел
            return get_string('section_0_name', 'format_opentechnology');
        } else
        {// Сформировать название раздела
            return get_string('section_default_name', 'format_opentechnology', $section->section);
        }
    }
    
    /**
     * Получить настройки формата для текущего курса
     *
     * @return array - Массив настроку формата для текущего курса
     */
    public function get_settings()
    {
        if ( empty($this->settings) )
        {
            $this->settings = $this->get_format_options();
        }
        return $this->settings;
    }
    
    /**
     * Получить URL курса с целевым разделом
     *
     * @param int|stdClass $section Объект текущего раздела из БД или номер раздела
     * @param array $options - Масив дополнительных параметров для формирования ссылки
     *
     * @return null|moodle_url
     */
    public function get_view_url($section, $options = [])
    {
        $course = $this->get_course();
        
        // Базовый URL курса
        $url = new moodle_url('/course/view.php', ['id' => $course->id]);

        // Целевой раздел
        $sr = null;
        if ( array_key_exists('sr', $options) )
        {// Среди GET-параметров указан целевой раздел
            $sr = $options['sr'];
        }
        
        // Получение номера текущего раздела
        if ( is_object($section) )
        {
            $sectionno = $section->section;
        } else
        {
            $sectionno = $section;
        }
        
        if ( $sectionno !== null )
        {// Получен номер текущего раздела
            if ( $sr !== null )
            {// Указан номер целевого раздела
                if ( $sr )
                {// Включить разбиение на страницы
                    $usercoursedisplay = COURSE_DISPLAY_MULTIPAGE;
                    $sectionno = $sr;
                } else
                {// Отключить разбиение на страницы
                    $usercoursedisplay = COURSE_DISPLAY_SINGLEPAGE;
                }
            } else
            {// Целевой раздел не указан
                // Разбиение на страницы в зависимости от настройки курса
                $usercoursedisplay = $course->coursedisplay;
            }
            
            // Добавление параметров в URL
            if ( $sectionno != 0 && $usercoursedisplay == COURSE_DISPLAY_MULTIPAGE )
            {
                // Добавление целевого раздела в URL
                $url->param('section', $sectionno);
            } else
            {
                // Одностраничное отображение курса
                if ( ! empty($options['navigation'])
                    && ! in_array($course->display_mode, [
                        'format_opentechnology_accordion',
                        'format_opentechnology_carousel'
                    ])
                )
                {
                    return NULL;
                }
                $url->set_anchor('section-'.$sectionno);
            }
        }
        return $url;
    }

    /**
     * Формирование навигации курса
     *
     * @param global_navigation $navigation - Объект Навигации Moodle
     *
     * @param navigation_node $node
     */
    public function extend_course_navigation($navigation, navigation_node $node)
    {
        global $PAGE;
        
        if ( $navigation->includesectionnum === false )
        {
            // Текущая секция
            $selectedsection = optional_param('section', NULL, PARAM_INT);
            
            if ( $selectedsection !== NULL && ( ! defined('AJAX_SCRIPT') || AJAX_SCRIPT == '0' ) &&
                 $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE) )
            {
                $navigation->includesectionnum = $selectedsection;
            }
        }
        parent::extend_course_navigation($navigation, $node);
    }

    /**
     * Действия при перемещении секции через AJAX скрипт
     *
     * @return array - Данные, получаемые после успешного перемещения раздела по AJAX
     */
    public function ajax_section_move()
    {
        global $PAGE, $DB;
        
        $titles = [];
        $course = $this->get_course();
        $modinfo = get_fast_modinfo($course);
        $renderer = $this->get_renderer($PAGE);
        
        if ( $renderer && ( $sections = $modinfo->get_section_info_all() ) )
        {
            // Пересоздание имен всех заголовков в разделов для соблюдения верной очередности
            foreach ( $sections as $number => $section )
            {
                $titles[$number] = $renderer->section_title($section, $course);
            }
        }
        return ['sectiontitles' => $titles, 'action' => 'move'];
    }

    /**
     * Получить список блоков, которые будут автоматически добавлены
     * на страницу курса сразу после его создания
     *
     * Блоки формируются исходя из настроек формата курса
     *
     * @return array - Массив кодов блоков, разделенных по позициям
     */
    public function get_default_blocks()
    {
        // Получение кодов позиций блоков
        $left_region_code = BLOCK_POS_LEFT;
        $rename = trim(get_config('format_opentechnology', 'region_side_pre_rename'));
        if ( ! empty($rename) )
        {// Код позиции переопределен
            $left_region_code = $rename;
        }
        $right_region_code = BLOCK_POS_RIGHT;
        $rename = trim(get_config('format_opentechnology', 'region_side_post_rename'));
        if ( ! empty($rename) )
        {// Код позиции переопределен
            $right_region_code = $rename;
        }
        
        // Доступные блоки
        $plugin_manager = core_plugin_manager::instance();
        $available_blocks = $plugin_manager->get_plugins_of_type('block');
        
        // Формирование списка блоков
        $blocks = [];
        $left_blocks = trim(get_config('format_opentechnology', 'default_blocks_region_side_pre'));
        if ( ! empty($left_blocks) )
        {// Указан список кодов блоков
            // Добавление каждого кода блока
            $left_blocks = explode(',', $left_blocks);
            foreach ( $left_blocks as $blockcode )
            {
                $blockcode = trim($blockcode);
                if ( ! empty($blockcode) && isset($available_blocks[$blockcode]) )
                {
                    $blocks[$left_region_code][] = $blockcode;
                }
            }
        }
        $right_blocks = trim(get_config('format_opentechnology', 'default_blocks_region_side_post'));
        if ( ! empty($right_blocks) )
            {// Указан список кодов блоков
            // Добавление каждого кода блока
            $right_blocks = explode(',', $right_blocks);
            foreach ( $right_blocks as $blockcode )
            {
                $blockcode = trim($blockcode);
                if ( ! empty($blockcode) && isset($available_blocks[$blockcode]) )
                {
                    $blocks[$right_region_code][] = $blockcode;
                }
            }
        }
        
        return $blocks;
    }

    /**
     * Получить настройки формата курса
     *
     * Объявление дополнительных настроек формата курса
     *
     * @param bool $foreditform - Требуются данные для формы настроек курса
     *
     * @return array - Массив настроек курса
     */
    public function course_format_options($foreditform = false)
    {
        global $DB;
        static $courseformatoptions = false;
        
        if ( $courseformatoptions === false )
        {// Инициализация настроек формата курса при первом вызове функции
            
            // Получение стандартных настроек курса
            $courseconfig = get_config('moodlecourse');
            // Получение значений по умолчанию из глобальных настроек плагина
            $defaultformatconfig = get_config('format_opentechnology');
            // Получение настроек формата для текущего курса
            $formatconfig = $DB->get_records('course_format_options',
                [
                    'courseid' => $this->courseid,
                    'format' => $this->format,
                    'sectionid' => 0
                ], '', 'id,name,value');
            
            // Добавление значений глобальных настроек для неопределенных локальных настроек
            foreach ( $formatconfig as $option )
            {
                switch ( $option->name )
                {
                    case 'section_width':
                        $value = $option->value;
                        if ( $value !== null )
                        {
                            $defaultformatconfig->section_width = clean_param($value, PARAM_INT);
                        }
                        break;
                    case 'section_summary_width':
                        $value = $option->value;
                        if ( $value !== null )
                        {
                            $defaultformatconfig->section_summary_width = clean_param($value,
                                PARAM_INT);
                        }
                        break;
                    case 'section_lastinrow':
                        $value = $option->value;
                        if ( $value !== null )
                        {
                            $defaultformatconfig->section_lastinrow = clean_param($value, PARAM_INT);
                        }
                        break;
                }
            }
            
            // Инициализация настроек формата курса
            $courseformatoptions = [
                // Основные настройки
                'coursedisplay' => [
                    'default' => $courseconfig->coursedisplay,
                    'type' => PARAM_INT
                ],
                'numsections' => [
                    'default' => $courseconfig->numsections,
                    'type' => PARAM_INT
                ],
                // Настройки вида курса
                'course_display_mode' => [
                    'default' => 1,
                    'type' => PARAM_INT
                ],
                // Настройки вида секций
                'display_mode' => [
                    'default' => $defaultformatconfig->display_mode,
                    'type' => PARAM_TEXT
                ],
                'hiddensections' => [
                    'default' => $courseconfig->hiddensections,
                    'type' => PARAM_INT
                ],
                'caption_align' => [
                    'default' => $defaultformatconfig->caption_align,
                    'type' => PARAM_TEXT
                ],
                'section_width' => [
                    'default' => $defaultformatconfig->section_width,
                    'type' => PARAM_INT
                ],
                'set_section_width' => [
                    'default' => 0,
                    'type' => PARAM_INT
                ],
                'section_lastinrow' => [
                    'default' => $defaultformatconfig->section_lastinrow,
                    'type' => PARAM_INT
                ],
                'set_section_lastinrow' => [
                    'default' => 0,
                    'type' => PARAM_INT
                ],
                'section_summary_width' => [
                    'default' => $defaultformatconfig->section_summary_width,
                    'type' => PARAM_INT
                ],
                'set_section_summary_width' => [
                    'default' => 0,
                    'type' => PARAM_INT
                ],
                'caption_icons_enabled' => [
                    'default' => $defaultformatconfig->caption_icons_enabled,
                    'type' => PARAM_INT
                ],
                'caption_icon_toggle_open_filemanager' => [
                    'default' => 0
                ],
                'caption_icon_toggle_closed_filemanager' => [
                    'default' => 0
                ],
                // Настройки вида элементов курса
                'elements_display_mode' => [
                    'default' => $defaultformatconfig->elements_display_mode,
                    'type' => PARAM_TEXT
                ]
            ];
            
            if ( $foreditform && ! isset($courseformatoptions['coursedisplay']['label']) )
            {// Требуется инициалиация формы настроек
                // Подготовка файлменеджеров
                $context = $this->get_context();
                $data = new stdClass();
                $data = file_prepare_standard_filemanager(
                    $data,
                    'caption_icon_toggle_open',
                    ['maxfiles' => 1, 'subdirs' => 0, 'maxbytes' => 0, 'context' => $context],
                    $context,
                    'format_opentechnology',
                    'caption_icon_toggle_open',
                    0
                );
                file_prepare_standard_filemanager(
                    $data,
                    'caption_icon_toggle_closed',
                    ['maxfiles' => 1, 'subdirs' => 0, 'maxbytes' => 0, 'context' => $context],
                    $context,
                    'format_opentechnology',
                    'caption_icon_toggle_closed',
                    0
                );
                // Установка идентификаторов драфтзон с файлами
                $courseformatoptions['caption_icon_toggle_open_filemanager'] = [
                    'default' => $data->caption_icon_toggle_open_filemanager
                ];
                $courseformatoptions['caption_icon_toggle_closed_filemanager'] = [
                    'default' => $data->caption_icon_toggle_closed_filemanager
                ];
            } else
            {// Получение настроек для курса
                $fs = get_file_storage();
                $files = $fs->get_area_files($this->get_context()->id, 'format_opentechnology', 'caption_icon_toggle_open');
                if ( count($files) )
                {
                    foreach ( $files as $file )
                    {
                        // Является ли файл изображением
                        $isimage = $file->is_valid_image();
                        if ( $isimage )
                        {
                            // Запишем адрес изображения
                            $courseformatoptions['caption_icon_toggle_open_filemanager'] = ['default' => $file->get_itemid()];
                            break;
                        }
                    }
                }
                $files = $fs->get_area_files($this->get_context()->id, 'format_opentechnology', 'caption_icon_toggle_closed');
                if ( count($files) )
                {
                    foreach ( $files as $file )
                    {
                        // Является ли файл изображением
                        $isimage = $file->is_valid_image();
                        if ( $isimage )
                        {
                            // Запишем адрес изображения
                            $courseformatoptions['caption_icon_toggle_open_filemanager'] = ['default' => $file->get_itemid()];
                            break;
                        }
                    }
                }
            }
        }
        
        if ( $foreditform && ! isset($courseformatoptions['coursedisplay']['label']) )
        {// Данные для формы настроек не определены
            $courseconfig = get_config('moodlecourse');
            
            // Максимальное число разделов в курсе
            if ( ! isset($courseconfig->maxsections) || ! is_numeric($courseconfig->maxsections) )
            {// Установка значения по умолчанию
                $max = 52;
            } else
            {// Значение из настроек курса
                $max = $courseconfig->maxsections;
            }
            
            // Формирование выпадающего списка для выбора числа разделов
            $numsections_options = [];
            for ( $i = 0; $i <= $max; $i++ )
            {
                $numsections_options[$i] = "$i";
            }
            
            // Формирвание данных для формы настроек курса
            $courseformatoptionsedit = [
                'numsections' => [
                    'label' => new lang_string('course_settings_sectionsnumber', 'format_opentechnology'),
                    'element_type' => 'select',
                    'element_attributes' => [$numsections_options],
                ],
                'hiddensections' => [
                    'label' => new lang_string('course_settings_hiddensections', 'format_opentechnology'),
                    'help' => 'hiddensections',
                    'help_component' => 'moodle',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            0 => new lang_string('course_settings_hiddensections_collapsed', 'format_opentechnology'),
                            1 => new lang_string('course_settings_hiddensections_invisible', 'format_opentechnology')
                        ]
                    ],
                ],
                'coursedisplay' => [
                    'label' => new lang_string('course_settings_coursedisplay', 'format_opentechnology'),
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            COURSE_DISPLAY_SINGLEPAGE => new lang_string('course_settings_coursedisplay_multi', 'format_opentechnology'),
                            COURSE_DISPLAY_MULTIPAGE => new lang_string('course_settings_coursedisplay_single', 'format_opentechnology')
                        ]
                    ],
                    'help' => 'coursedisplay',
                    'help_component' => 'moodle',
                ],
                'caption_align' => [
                    'label' => get_string('course_settings_caption_align_title', 'format_opentechnology'),
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            'left'   => get_string('course_settings_caption_align_option_left', 'format_opentechnology'),
                            'center' => get_string('course_settings_caption_align_option_center', 'format_opentechnology'),
                            'right'  => get_string('course_settings_caption_align_option_right', 'format_opentechnology')
                        ]
                    ],
                    'help' => 'course_settings_caption_align_desc',
                    'help_component' => 'format_opentechnology',
                ],
                'display_mode' => [
                    'label' => get_string('course_settings_display_mode_title',
                        'format_opentechnology'),
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            'format_opentechnology_base' => get_string(
                                'settings_format_opentechnology_base', 'format_opentechnology'),
                            'format_opentechnology_spoiler' => get_string(
                                'settings_format_opentechnology_spoiler', 'format_opentechnology'),
                            'format_opentechnology_accordion' => get_string(
                                'settings_format_opentechnology_accordion', 'format_opentechnology'),
                            'format_opentechnology_carousel' => get_string(
                                'settings_format_opentechnology_carousel', 'format_opentechnology')
                        ]
                    ],
                    'help' => 'course_settings_display_mode_desc',
                    'help_component' => 'format_opentechnology'
                ],
                'course_display_mode' => [
                    'label' => get_string('course_settings_course_display_mode_title',
                        'format_opentechnology'),
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            '1' => get_string(
                                'settings_format_opentechnology_course_display_mode_1', 'format_opentechnology'),
                            '2' => get_string(
                                'settings_format_opentechnology_course_display_mode_2', 'format_opentechnology'),
                            '0' => get_string(
                                'settings_format_opentechnology_course_display_mode_0', 'format_opentechnology')
                        ]
                    ]
                ],
                'section_width' => [
                    'element_type' => 'select',
                    'label' => get_string('course_settings_section_width', 'format_opentechnology'),
                    'element_attributes' => [
                        [
                            '100' => '100%',
                            '75' => '75%',
                            '66' => '66.66%',
                            '50' => '50%',
                            '33' => '33.33%',
                            '25' => '25%'
                        ]
                    ],
                    'help' => 'course_settings_section_width',
                    'help_component' => 'format_opentechnology'
                ],
                'set_section_width' => [
                    'element_type' => 'select',
                    'label' => get_string('course_settings_set_section_width',
                        'format_opentechnology'),
                    'element_attributes' => [
                        [
                            '0' => get_string('no'),
                            '1' => get_string('yes')
                        ]
                    ],
                    'help' => 'course_settings_set_section_width',
                    'help_component' => 'format_opentechnology'
                ],
                'section_lastinrow' => [
                    'element_type' => 'select',
                    'label' => get_string('course_settings_section_lastinrow',
                        'format_opentechnology'),
                    'element_attributes' => [
                        [
                            '0' => get_string('no'),
                            '1' => get_string('yes')
                        ]
                    ],
                    'help' => 'course_settings_section_lastinrow',
                    'help_component' => 'format_opentechnology'
                ],
                'set_section_lastinrow' => [
                    'element_type' => 'select',
                    'label' => get_string('course_settings_set_section_lastinrow',
                        'format_opentechnology'),
                    'element_attributes' => [
                        [
                            '0' => get_string('no'),
                            '1' => get_string('yes')
                        ]
                    ],
                    'help' => 'course_settings_set_section_lastinrow',
                    'help_component' => 'format_opentechnology'
                ],
                'section_summary_width' => [
                    'element_type' => 'select',
                    'label' => get_string('course_settings_section_summary_width',
                        'format_opentechnology'),
                    'element_attributes' => [
                        [
                            '100' => '100%',
                            '75' => '75%',
                            '66' => '66.66%',
                            '50' => '50%',
                            '33' => '33.33%',
                            '25' => '25%'
                        ]
                    ],
                    'help' => 'course_settings_section_summary_width',
                    'help_component' => 'format_opentechnology'
                ],
                'set_section_summary_width' => [
                    'element_type' => 'select',
                    'label' => get_string('course_settings_set_section_summary_width',
                        'format_opentechnology'),
                    'element_attributes' => [
                        [
                            '0' => get_string('no'),
                            '1' => get_string('yes')
                        ]
                    ],
                    'help' => 'course_settings_set_section_summary_width',
                    'help_component' => 'format_opentechnology'
                ],
                'elements_display_mode' => [
                    'label' => get_string('course_settings_elements_display_mode_title', 'format_opentechnology'),
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            'format_opentechnology_base_elements_view'   => get_string('settings_format_opentechnology_base_elements_view', 'format_opentechnology'),
                            'format_opentechnology_icon_elements_view' => get_string('settings_format_opentechnology_icon_elements_view', 'format_opentechnology'),
                            'format_opentechnology_base_with_badges_elements_view'   => get_string('settings_format_opentechnology_base_with_badges_elements_view', 'format_opentechnology'),
                            'format_opentechnology_icon_with_badges_elements_view' => get_string('settings_format_opentechnology_icon_with_badges_elements_view', 'format_opentechnology')
                        ]
                    ],
                    'help' => 'course_settings_elements_display_mode_desc',
                    'help_component' => 'format_opentechnology',
                ],
                'caption_icons_enabled' => [
                    'label' => get_string('course_settings_caption_icons_enabled_title', 'format_opentechnology'),
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            0 => new lang_string('no'),
                            1 => new lang_string('yes')
                        ]
                    ],
                    'help' => 'course_settings_caption_icons_enabled_desc',
                    'help_component' => 'format_opentechnology',
                ],
                'caption_icon_toggle_open_filemanager' => [
                    'label' => get_string('course_settings_caption_icon_toggle_open_title', 'format_opentechnology'),
                    'element_type' => 'filemanager',
                    'element_attributes' => [
                        null,
                        [
                            'subdirs' => 0,
                            'maxfiles' => 1
                        ]
                    ]
                ],
                'caption_icon_toggle_closed_filemanager' => [
                    'label' => get_string('course_settings_caption_icon_toggle_closed_title', 'format_opentechnology'),
                    'element_type' => 'filemanager',
                    'element_attributes' => [
                        null,
                        [
                            'subdirs' => 0,
                            'maxfiles' => 1
                        ]
                    ]
                ],
            ];
            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }
        return $courseformatoptions;
    }

    /**
     * Добавить настройки формата курса в форму редактирования курса/раздела
     *
     * Метод вызывается в {@link course_edit_form::definition_after_data()}
     *
     * @param MoodleQuickForm $mform - Объект формы, в которую требуется добавить поля
     * @param bool $forsection - Флаг типа формы настроек.
     *              true - форма настройки секции
     *              false - форма настройки курса
     *
     * @return array - Массив полей настроек формата курса
     */
    public function create_edit_form_elements(&$mform, $forsection = false)
    {
        // Получение массива элементов для настройки формата курса
        $elements = parent::create_edit_form_elements($mform, $forsection);

        if ( ! $forsection )
        {// Дополнительный обработчик формы редактирования формата курса
            
            // Заголовки формы
            $header = [$mform->addElement('static', 'header_general', '', html_writer::tag('h4',
                get_string('course_settings_header_general', 'format_opentechnology')))];
            array_splice($elements, 0, 0, $header);
            $header = [$mform->addElement('static', 'header_courseview', '', html_writer::tag('h4',
                get_string('course_settings_header_courseview', 'format_opentechnology')))];
            array_splice($elements, 3, 0, $header);
            $header = [$mform->addElement('static', 'header_sectionview', '', html_writer::tag('h4',
                get_string('course_settings_header_sectionview', 'format_opentechnology')))];
            array_splice($elements, 5, 0, $header);
            $header = [$mform->addElement('static', 'header_modview', '', html_writer::tag('h4',
                get_string('course_settings_header_modview', 'format_opentechnology')))];
            array_splice($elements, 18, 0, $header);
            
            // Отключение элементов формы
            $mform->disabledIf('section_width', 'course_display_mode', 'neq', 0);
            $mform->disabledIf('set_section_width', 'course_display_mode', 'neq', 0);
            $mform->disabledIf('caption_icon_toggle_open_filemanager', 'caption_icons_enabled', 'neq', 1);
            $mform->disabledIf('caption_icon_toggle_closed_filemanager', 'caption_icons_enabled', 'neq', 1);
            
            // Корректировка настроек для конкретного курса
            $maxsections = get_config('moodlecourse', 'maxsections');
            $numsections = $mform->getElementValue('numsections');
            $numsections = $numsections[0];
            if ( $numsections > $maxsections )
            {
                $element = $mform->getElement('numsections');
                for ( $i = $maxsections+1; $i <= $numsections; $i++ )
                {
                    $element->addOption("$i", $i);
                }
            }
        } else
        {// Дополнительный обработчик формы редактирования секции
            
        }
        return $elements;
    }

    /**
     * Процесс сохранения настроек формата курса
     *
     * @param stdClass|array - Данные формы настроек курса, включая настройки формата курса
     * @param null|int - Номер секции, для которой производится настройка или же NULL, если настройка для всех секций
     *
     * @return bool
     */
    protected function update_format_options($data, $sectionid = null)
    {
        global $DB;
        
        // Сохранение загруженных файлов в зоне формата курса
        $context = $this->get_context();
        file_postupdate_standard_filemanager(
            (object)$data,
            'caption_icon_toggle_open',
            ['maxfiles' => 1, 'subdirs' => 0],
            $context,
            'format_opentechnology',
            'caption_icon_toggle_open',
            0
        );
        file_postupdate_standard_filemanager(
            (object)$data,
            'caption_icon_toggle_closed',
            ['maxfiles' => 1, 'subdirs' => 0],
            $context,
            'format_opentechnology',
            'caption_icon_toggle_closed',
            0
        );
        // Фикс имени настроек
        if ( isset($data->caption_icon_toggle_open_filemanager) )
        {
            $data->caption_icon_toggle_open = $data->caption_icon_toggle_open_filemanager;
            unset($data->caption_icon_toggle_open_filemanager);
        }
        if ( isset($data->caption_icon_toggle_open_filemanager) )
        {
            $data->caption_icon_toggle_closed = $data->caption_icon_toggle_closed_filemanager;
            unset($data->caption_icon_toggle_closed_filemanager);
        }
        
        $data = (array)$data;
        
        if ( ! empty($data['set_section_width']) && isset($data['section_width']) )
        { //требуется установка ширины для всех секций
            $sections = $this->get_sections();
            foreach ( $sections as $section )
            {
                $sectiondata = [
                    'width' => $data['section_width']
                ];
                parent::update_format_options($sectiondata, $section->id);
            }
        }
        
        if ( ! empty($data['set_section_summary_width']) && isset($data['section_summary_width']) )
        { //требуется установка ширины блока описания секции для всех секций
            $sections = $this->get_sections();
            foreach ( $sections as $section )
            {
                $sectiondata = [
                    'summary_width' => $data['section_summary_width']
                ];
                parent::update_format_options($sectiondata, $section->id);
            }
        }
        
        if ( ! empty($data['set_section_lastinrow']) && isset($data['section_lastinrow']) )
        { //требуется установка опции "посленяя секция в сроке / слайде" для всех секций
            $sections = $this->get_sections();
            foreach ( $sections as $section )
            {
                $sectiondata = [
                    'lastinrow' => $data['section_lastinrow']
                ];
                parent::update_format_options($sectiondata, $section->id);
            }
        }
        
        //галочку переопределения ширины секции не сохраняем, чтобы случайно не сбить настройки в будущем
        $data['set_section_width'] = 0;
        //галочку переопределения ширины блока описания секции не сохраняем, чтобы случайно не сбить настройки в будущем
        $data['set_section_summary_width'] = 0;
        //галочку переопределения опции обрыва строки не сохраняем, чтобы случайно не сбить настройки в будущем
        $data['set_section_lastinrow'] = 0;
        
        // Сохранение опций
        return parent::update_format_options($data, $sectionid);
    }
    
    /**
     * Миграция настроек предыдущего формата курса при его смене на текущий формат
     *
     * Производит попытку сохранить данные настроек предыдущего формата курса и
     * применить их к текущему
     *
     * @param stdClass|array $data - Данные из формы настройки курса
     * @param stdClass $oldcourse - Данные о курса перед сохранением
     *
     * @return bool - Были ли изменены настройки
     */
    public function update_course_format_options($data, $oldcourse = NULL)
    {
        global $DB;
        
        if ( $oldcourse !== null )
        {// Курс определен
            $data = (array)$data;
            $oldcourse = (array)$oldcourse;
            $format_options = $this->course_format_options();
            
            foreach ( $format_options as $key => $unused )
            {
                if ( ! array_key_exists($key, $data) )
                {// Не найдено настройки для текущего формата курса
                    // Поиск настройки в прошлом формате курса
                    if ( array_key_exists($key, $oldcourse) )
                    {// Настройка найдена
                        // Миграция настройки
                        $data[$key] = $oldcourse[$key];
                    } else if ( $key === 'numsections' )
                    {// Настройка не найдена в предыдущем формате курса, но это число разделов
                        // Установка текущего значения на основе имеющихся разделов в курса
                        $maxsection = $DB->get_field_sql(
                            'SELECT max(section)
                             FROM {course_sections}
                             WHERE course = ?', [$this->courseid]
                        );
                        if ( $maxsection )
                        {// Установлено число разделов в курса
                            // Сохранеие настройки
                            $data['numsections'] = $maxsection;
                        }
                    }
                }
            }
        }
        return $this->update_format_options($data);
    }
    
    /**
     * Получение контекста курса
     *
     * @return context|context_course
     */
    private function get_context()
    {
        global $SITE;
    
        if ($SITE->id == $this->courseid) {
            // Use the context of the page which should be the course category.
            global $PAGE;
            return $PAGE->context;
        } else {
            return context_course::instance($this->courseid);
        }
    }
    
    /**
     * Получить ширину секции
     *
     * @param int $sectionnum - Номер секции в курсе
     *
     * @return int - Процент ширины секции
     */
    public function get_section_width($sectionnum = 0)
    {
        if ( $sectionnum == 0 )
        {// Информационная секция
            
            // Получение настроек для текущей секции
            $sectionsettings = $this->get_format_options((int)$sectionnum);
            if ( ! empty($sectionsettings['width']) )
            {// Настройка секции
                return (int)$sectionsettings['width'];
            }
        } else
        {// Стандартная секция курса
            // Получение текущих настроек формата курса
            $formatoptions = $this->get_format_options();
            // Установка ширины на основе настроек формата курса
            if ( ! empty($formatoptions['course_display_mode']) )
            {// Принудительная установка ширины секции
                return 100/(int)$formatoptions['course_display_mode'];
            }
            
            if ( empty($formatoptions['set_section_width']) )
            {// Принудительная установка ширины секции для курса отключена
                // Получение настроек для текущей секции
                $sectionsettings = $this->get_format_options((int)$sectionnum);
                if ( ! empty($sectionsettings['width']) )
                {// Настройка секции
                    return (int)$sectionsettings['width'];
                }
            }
            
            // Ширина секции для курса по умолчанию
            if ( ! empty($formatoptions['section_width']) )
            {// Настройка найдена
                return (int)$formatoptions['section_width'];
            }
            
            // Глобальная настройка ширины секции для плагина
            $defaultformatoptions = get_config('format_opentechnology');
            if ( ! empty($defaultformatoptions->section_width) )
            {// Настройка найдена
                return (int)$defaultformatoptions->section_width;
            }
        }
        // Настройка не найдена
        return 100;
    }
    
    /**
     * Returns whether this course format allows the activity to
     * have "triple visibility state" - visible always, hidden on course page but available, hidden.
     *
     * @param stdClass|cm_info $cm course module (may be null if we are displaying a form for adding a module)
     * @param stdClass|section_info $section section where this module is located or will be added to
     * @return bool
     */
    public function allow_stealth_module_visibility($cm, $section) {
        return true;
    }
}

/**
 * Функция подготовки файлов плагина
 *
 * Организует подготовку, проверку доступа и отображение файлов для пользователей
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 *
 * @return bool|null - false, если файл не доступен для текущего пользователя
 */
function format_opentechnology_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array())
{
    // Получить itemid файла
    $itemid = array_shift($args);

    // Получение пути до файла и его имени
    $filename = array_pop($args);
    if (!$args) {
        $filepath = '/';
    } else {
        $filepath = '/'.implode('/', $args).'/';
    }
    
    // Поиск файла
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'format_opentechnology', $filearea, $itemid, $filepath, $filename);

    if ( ! $file)
    {// Файл не найден в системе
        return false;
    }
    // Отправка файла
    \core\session\manager::write_close();
    send_stored_file($file, null, 0, $forcedownload, $options);
}