<?PHP
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
 * Витрина курсов. Базовые функции
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use crw_system_search\search\crw_course;
use block_otshare\exception\publication;

require_once($CFG->dirroot . '/local/crw/classes/plugin.php');

/**
 * Базовый класс Витрины курсов
 */
class local_crw {

    public $slots = [];

    protected $page = 0;
    protected $perpage = 8;
    protected $topcategory = 0;
    protected $category = 0;
    protected $display_from_subcategories = false;
    protected $searchquery = null;
    protected $coursesslice = null;
    protected $coursescount = null;
    protected $coursessorttype = null;
    protected $coursessortdirection = null;
    protected $srr = null;
    protected $ajax = false;
    protected $pluginsettings = [];
    protected $searchforms = [];
    protected $userid = null;
    protected $user_courses_add_not_active = null;

    /**
     * Конструктор класса
     */
    public function __construct($opt=[])
    {
        global $CFG;

        // установка настроек субплагинов для переопределения системных
        if (!empty($opt['pluginsettings']))
        {
            $this->pluginsettings = $opt['pluginsettings'];
        }

        // Формируем слоты страницы витрины
        $this->slots['showcase'] = array();
        $this->slots['course'] = array();

        if (!empty($opt['forced_showcase_slots']) && is_array($opt['forced_showcase_slots']))
        {
            foreach($opt['forced_showcase_slots'] as $plugincode)
            {
                $this->add_slot($plugincode);
            }
        } else
        {
            $this->add_slot(get_config('local_crw', 'slots_cs_header'));
            $this->add_slot(get_config('local_crw', 'slots_cs_top'));
            $this->add_slot(get_config('local_crw', 'slots_cs_bottom'));
        }

        $this->set_page(isset($opt['page']) ? $opt['page'] : null);
        $this->set_perpage(isset($opt['limitnum']) ? $opt['limitnum'] : null);
        $this->set_topcategory();
        $this->set_category(isset($opt['cid']) ? $opt['cid'] : null);
        $this->set_courses_sort_settings(
            isset($opt['coursessorttype']) ? $opt['coursessorttype'] : null,
            isset($opt['coursessortdirection']) ? $opt['coursessortdirection'] : null
        );
        $this->set_display_from_subcategories(
            isset($opt['display_invested_courses']) ? $opt['display_invested_courses'] : null
            );
        $this->set_searchquery(isset($opt['crws']) ? $opt['crws'] : null);
        $this->set_srr(isset($opt['srr']) ? $opt['srr'] : null);
        $this->ajax = !empty($opt['ajax']);
        $this->ajaxformdata = $opt['ajaxformdata'] ?? null;

        $this->set_usercourses(
            $opt['userid'] ?? null,
            $opt['user_courses_add_not_active'] ?? null
        );

        // Сразу выполняется фильтрация, чтобы без редиректа получить курсы, согласно фильтрации
        $this->apply_filters();
    }

    protected function set_usercourses($userid=null, $notactive=null)
    {
        if (is_null($userid))
        {
            $userid = optional_param('uid', NULL, PARAM_INT);
        }
        if (is_null($notactive))
        {
            $notactive = optional_param('na', NULL, PARAM_INT);
        }

        if (!is_null($userid))
        {
            // пользователь, чьи курсы необходимо отобразить в витрине
            $this->userid = (int)$userid;

            if (!is_null($notactive))
            {// добавить ли в выборку курсов пользователя неактивные подписки
                $this->user_courses_add_not_active = !empty($notactive);
            }
        }
    }

    protected function apply_filters()
    {
        // фильтрация уже настроена
        if (!empty($this->searchquery))
        {
            return;
        }


        foreach ( $this->slots['showcase'] as $key => $slotitem )
        {
            if (!is_object($slotitem) || $slotitem->get_type() != CRW_PLUGIN_TYPE_SEARCH)
            {
                continue;
            }

            $displayoptions = [
                'crws' => $this->searchquery,
                'srr' => $this->srr,
                'categoryid' => $this->category,
                'return_process_result' => true
            ];
            if (!is_null($this->ajaxformdata))
            {
                $displayoptions['ajaxformdata'] = $this->ajaxformdata;
            }

            list($crws, $searchform) = $slotitem->display($displayoptions);

            $this->searchforms[$key] = $searchform;
            $this->set_searchquery($crws . (empty($this->searchquery) ? '' : ';'.$this->searchquery ));
        }
    }

    /**
     * Добавление слота на витрину с указанным плагином
     *
     * @param string $plugincode - код плагина
     */
    protected function add_slot($plugincode)
    {
        if (!empty($plugincode))
        {
            $this->slots['showcase'][] = local_crw_plugin::get(
                $plugincode,
                isset($this->pluginsettings[$plugincode]) ? $this->pluginsettings[$plugincode] : null
            );
        }
    }

    public function set_courses_sort_settings($coursessorttype = null, $coursessortdirection = null)
    {
        // доступные в категории
        $sorttypes = local_crw_get_all_allowed_sort_types($this->category);

        if (!is_null($coursessorttype) && array_key_exists($coursessorttype, $sorttypes))
        {
            $this->coursessorttype = $coursessorttype;
        } else
        {
            $this->coursessorttype = local_crw_get_default_sort_type($this->category);
        }

        if (!is_null($coursessortdirection))
        {
            $this->coursessortdirection = $coursessortdirection;
        } else
        {
            // пока ни конфига, ни плагина для настройки сортировки нет, делаем значение по умолчанию
            $sortdirection = get_config('local_crw', 'course_sort_direction');
            if ($sortdirection !== false) {
                $this->coursessortdirection = $sortdirection;
            } else {
                $this->coursessortdirection = 'ASC';
            }
        }
    }

    public function set_display_from_subcategories($displayfromsubcategories = null)
    {

        if( ! is_null($displayfromsubcategories) )
        {
            // Переданный параметр
            $this->display_from_subcategories = (bool)$displayfromsubcategories;
        } else
        {
            // из настроек
            $display_invested_courses = get_config('local_crw', 'display_invested_courses');
            $this->display_from_subcategories = (bool)$display_invested_courses;
        }
    }

    public function set_perpage($limitnum=null)
    {
        global $CFG;

        if( ! is_null($limitnum) )
        {
            // Переданный параметр
            $this->perpage = (int)$limitnum;
        } else
        {
            // Лимит из GET
            $limitnum = optional_param('limit', NULL, PARAM_INT);
            if( ! empty($limitnum) )
            {
                $this->perpage = (int)$limitnum;
            } else
            {
                // Лимит из настроек
                $limitcfg = get_config('local_crw', 'courses_pagelimit');
                if( ! empty($limitcfg) )
                {
                    $this->perpage = (int)$limitcfg;
                } else
                {
                    $this->perpage = (int)$CFG->frontpagecourselimit;
                }
            }
        }
    }

    public function set_page($page=null)
    {
        if( ! is_null($page) )
        {
            // Переданный параметр
            $this->page = (int)$page;
        } else
        {
            // Страница из GET
            $this->page = optional_param('page', 0, PARAM_INT);
        }
    }

    public function set_topcategory()
    {
        $this->topcategory = (int)get_config('local_crw', 'main_catid');
    }

    public function set_category($cid=null)
    {
        if( ! is_null($cid) )
        {
            // Переданный параметр
            $this->category = (int)$cid;
        } else
        {
            // Страница из GET
            $this->category = optional_param('cid', (int)$this->topcategory, PARAM_INT);
        }

        $canviewoptions = [];
        $notnested = get_config('local_crw', 'display_not_nested');
        if(empty($notnested))
        {
            $canviewoptions['must_have_parent'] = $this->topcategory;
        }

        // Текущая категория Витрины
        $canview = local_crw_category_can_view($this->category, 0, $canviewoptions);
        if ( empty($canview) || ( $this->category == 0 && $this->topcategory != 0 ) )
        {// Категорию нельзя просматривать
            // Переход к базовой категории
            $this->category = (int)$this->topcategory;
        }
    }

    public function set_searchquery($crws=null)
    {
        if( ! is_null($crws) )
        {
            // Переданный параметр
            $this->searchquery = $crws;
        } else
        {
            // Страница из GET
            $this->searchquery = optional_param('crws', null, PARAM_RAW);
        }
        if (!is_null($this->searchquery))
        {
            $this->searchquery = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $this->searchquery);
        }
    }

    public function set_srr($srr = null)
    {
        if( ! is_null($srr) )
        {
            // Переданный параметр
            $this->srr = $srr;
        } else
        {
            // Страница из GET
            $this->srr = optional_param('srr', null, PARAM_RAW);
        }
    }


    /**
     * Отобразить витрину курсов
     *
     * Отображает главную страницу витрины курсов с учетом блоков
     *
     * @param array $opt - Опции отображения
     */
    public function display_showcase($opt = array())
    {
        global $CFG, $PAGE;
//         require_once ($CFG->dirroot . '/local/crw/renderer.php');
        require_once ($CFG->dirroot . '/local/crw/plugins/system_search/lib.php');

        // Получить рендер
        $renderer = $PAGE->get_renderer('local_crw');
        // Текущий url витрины
        $baseurl = local_crw_current_url(
            $this->category,
            $this->searchquery,
            $this->userid,
            $this->user_courses_add_not_active
        );
        $ajaxcoursesflow = get_config('local_crw', 'ajax_courses_flow');
        $display_paging = get_config('local_crw', 'display_paging');
        $display_statistics = get_config('local_crw', 'display_statistics');
        $display_pagelimit_change_tool = get_config('local_crw', 'display_pagelimit_change_tool');


        // Cтрока с параметрами запроса
        $searchconditions = crw_system_search::get_conditions($this->searchquery);


        $canview = local_crw_category_can_view($this->category);
        if ( empty($canview) )
        {// Категорию нельзя просматривать
            return '';
        }
        // Массив курсов с учетом пейджинга
        $coursesslice = $this->get_courses_slice();
        // Количество отфильтрованных курсов
        $countcourses = $this->get_courses_count();
        // Категории
        $categories = $this->get_category_children($this->category);
        // Является ли текущая категория базовой
        $maincategory = ( (int)$this->category == (int)$this->topcategory );

        $splobjecthash = ($opt['splobjecthash'] ?? spl_object_hash($this));
        // Отобразим блоки
        $html = '';

        foreach ( $this->slots['showcase'] as $key => $block )
        {
            if ( is_object($block) )
            {
                if (array_key_exists('sq', $searchconditions) &&
                    $block->get_type() != CRW_PLUGIN_TYPE_SEARCH)
                {
                    continue;
                }
                $pluginheader = $pluginbody = $pluginfooter = '';
                $pluginclass = get_class($block);
                switch($block->get_type())
                {
                    case CRW_PLUGIN_TYPE_CATEGORIES_LIST:

                        $pluginbody = $block->display([
                            'cid' => $this->category,
                            'categories' => $categories,
                            'searchquery' => $this->searchquery,
                            'userid' => $this->userid,
                            'usercourses_add_not_active' => $this->user_courses_add_not_active
                        ]);

                        // Получение настройки отображения заголовка
                        switch($pluginclass)
                        {
                            case 'crw_categories_list_icons':
                                $displaytitlesetting = get_config($pluginclass, 'display_title');
                                $displaytitle = ! empty($displaytitlesetting);
                                break;
                            case 'crw_categories_list_block':
                            case 'crw_categories_list_tiles':
                                $displaytitlesetting = get_config($pluginclass, 'hide_cat_block_title');
                                $displaytitle = empty($displaytitlesetting);
                                break;
                            default:
                                $displaytitle = false;
                                break;
                        }


                        // Заголовок блока
                        $categoryheadertitle = "";
                        if( $displaytitle && ! empty($pluginbody) )
                        {
                            $categoryheadertitle = local_crw_get_categories_header(
                                core_course_category::get($this->category)
                            );
                        }
                        // Инструменты администратора
                        $createcategorytool = local_crw_get_category_create_link($this->category);


                        if( ! empty($categoryheadertitle) || ! empty($createcategorytool) )
                        {
                            $pluginheader = html_writer::div(
                                html_writer::div($categoryheadertitle) . html_writer::div($createcategorytool),
                                'crw_categories_header header'
                            );
                        }
                        break;
                    case CRW_PLUGIN_TYPE_COURSES_LIST:

                        $pluginbody = $block->display([
                            'cid' => $this->category,
                            'courses' => $coursesslice
                        ]);

                        // Получение настройки отображения заголовка
                        switch($pluginclass)
                        {
                            case 'crw_courses_list_ajax':
                            case 'crw_courses_list_tiles':
                            case 'crw_courses_list_sections':
                            case 'crw_courses_list_squares':
                                $displaytitlesetting = get_config($pluginclass, 'hide_course_block_title');
                                $displaytitle = empty($displaytitlesetting);
                                break;
                            case 'crw_courses_list_tiles_two':
                                $displaytitle = true;
                            default:
                                $displaytitle = false;
                                break;
                        }

                        // Заголовок блока
                        $coursesheadertitle = "";
                        if( $displaytitle && ( ! empty($pluginbody) || $maincategory ) )
                        {
                            $coursesheadertitle = local_crw_get_courses_header(
                                core_course_category::get($this->category),
                                !empty($searchconditions)
                            );
                        }
                        // Инструменты администратора
                        $createcoursetool = local_crw_get_course_create_link($this->category);

                        if( ! empty($coursesheadertitle) || ! empty($createcoursetool) )
                        {
                            $pluginheader = html_writer::div(
                                html_writer::div($coursesheadertitle) . html_writer::div($createcoursetool),
                                'crw_courses_header'
                            );
                        }



                        // имеет смысл отображать пейджинг
                        if( $countcourses > $this->perpage && $display_paging > 0 && empty($ajaxcoursesflow) )
                        {
                            $paginghtml = $renderer->paging_bar(
                                $countcourses,
                                $this->page,
                                $this->perpage,
                                $baseurl
                            );

                            // отображение пейджинга над курсами
                            if($display_paging & 0b001)
                            {
                                $pluginheader = $paginghtml . $pluginheader;
                            }
                            // отображение пейджинга под курсами
                            if($display_paging & 0b010)
                            {
                                $pluginfooter = $paginghtml;
                            }
                        }

                        // отображение статистики для пейджинга, доступной не зависимо от количества страниц
                        if($display_statistics > 0)
                        {
                            $a = new stdClass();
                            $a->perpage = html_writer::span(count($this->get_courses_slice()));
                            $a->totalcount = $countcourses;
                            $pagingdescription = html_writer::div(
                                get_string('top_paging_description', 'local_crw', $a),
                                'local_crw_top_paging_description'
                            );
                            if($display_statistics & 0b001)
                            {
                                $pluginheader = $pagingdescription . $pluginheader;
                            }
                            if($display_statistics & 0b010)
                            {
                                $pluginfooter = $pagingdescription . $pluginfooter;
                            }
                        }






                        $limitcfg = get_config('local_crw', 'courses_pagelimit');
                        if( $limitcfg > 0 && $display_pagelimit_change_tool > 0 )
                        {
                            $perpager = html_writer::span(
                                get_string('perpager_title', 'local_crw')
                            );

                            foreach([1,2,3,7] as $ppm)
                            {
                                $newperpage = $limitcfg * $ppm;
                                // несколько вариантов с количеством курсов на странице
                                $perpagerurl = $baseurl;
                                $perpagerurl->param('limit', $newperpage);
                                $perpagerurl->param('page', 0);

                                $perpager .= html_writer::link(
                                    $perpagerurl,
                                    $limitcfg * $ppm,
                                    [
                                        'class' => ($newperpage == $this->perpage ? 'current' : '')
                                    ]
                                );
                            }

                            // все курсы на странице
                            $perpagerurl = $baseurl;
                            $perpagerurl->param('limit', $countcourses);
                            $perpagerurl->param('page', 0);
                            $perpager .= html_writer::link(
                                $perpagerurl,
                                get_string('perpager_all', 'local_crw'),
                                [
                                    'class' => ($countcourses == $this->perpage ? 'current' : '')
                                ]
                            );

                            if($display_pagelimit_change_tool & 0b001)
                            {
                                $pluginheader = html_writer::div($perpager, 'perpager') . $pluginheader;
                            }
                            if($display_pagelimit_change_tool & 0b010)
                            {
                                $pluginfooter = $pluginfooter . html_writer::div($perpager, 'perpager');
                            }
                        }

                        break;
                    case CRW_PLUGIN_TYPE_SEARCH:
                        if (array_key_exists($key, $this->searchforms))
                        {
                            $pluginbody = $this->searchforms[$key];
                        } else
                        {
                            $pluginbody = $block->display([
                                'crws' => $this->searchquery,
                                'srr' => $this->srr,
                                'categoryid' => $this->category
                            ]);
                        }
                        break;
                    default:
                        $pluginbody = '';
                        break;
                }
                $pluginhtml = '';
                $pluginhtml .= html_writer::div($pluginheader, 'crw_plugin_header');
                $pluginhtml .= html_writer::div($pluginbody, 'crw_plugin_body');
                $pluginhtml .= html_writer::div($pluginfooter, 'crw_plugin_footer');
                $html .= html_writer::div(
                    $pluginhtml,
                    'crw_ptype_'.$block->get_type(),
                    [
                        'data-plugin-code' => $block->get_name()
                    ]
                );
            }
        }

        if (empty($coursesslice))
        {
            if (!array_key_exists('sq', $searchconditions))
            {
                $html .= html_writer::div(
                    get_string('no_courses_was_find', 'local_crw'),
                    'crw_no_courses'
                );
            } elseif( ! $maincategory )
            {
                $html .= html_writer::div(
                    get_string('no_courses_in_selected_category', 'local_crw'),
                    'crw_no_courses'
                );
            }
        }



        // Подключение ajax-загрузки курсов
        if( (int)$countcourses > (int)$this->perpage )
        {

            if( ! empty($ajaxcoursesflow) )
            {
                $ajaxcoursesflowautoload = get_config('local_crw', 'ajax_courses_flow_autoload');
                $PAGE->requires->js_call_amd(
                    'local_crw/courses_flow',
                    'init',
                    [
                        $splobjecthash,
                        $this->category,
                        $this->page,
                        $this->perpage,
                        (int)$countcourses,
                        ! empty($ajaxcoursesflowautoload),
                        $this->display_from_subcategories,
                        $this->searchquery,
                        $this->userid,
                        $this->user_courses_add_not_active
                    ]
                );
            }
        }

        if (empty($opt['no-wrapper']))
        {
            // Обернем витрину в блок
            $html = html_writer::div($html, 'local_crw', ['id'=>'local_crw', 'data-object-id' => $splobjecthash]);
        }

        if( ! empty($opt['return_html']) )
        {
            return $html;
        }
        print($html);
    }

    /**
     * Отобразить страницу курса
     *
     * Отображает страницу описания курса
     *
     * @param array $opt - Опции отображения
     */
    public function display_course($opt = array())
    {
        // Текущая категория
        if ( ! isset($opt['id']))
        {
            $opt['id'] = optional_param('id', 0, PARAM_INT);
        }

        // Отобразим блоки
        $html = '';
        foreach ( $this->slots['course'] as $block )
        {
            if ( is_object($block) )
            {
                $html .= $block->display($opt);
            }
        }
        print($html);
    }

    /**
     * Получение массива доступных категорий
     *
     * @param int $catid - идентификатор категории
     * @return array - массив, доступных категорий
     */
    public function get_category_children( $catid = 0 )
    {
        $result = [];
        // Получим категорию
        $parentcategory = core_course_category::get($catid);

        if ( empty($parentcategory) )
        { // Категория не найдена
            return [];
        }

        // Получить дочерние категории
        $childrencategories = $parentcategory->get_children();

        foreach($childrencategories as $childcategory)
        {
            $category = core_course_category::get($childcategory->id);
            //проверка доступности категории
            $visible = local_crw_category_can_view($category->id, 0, [
                'must_have_parent' => $parentcategory->id
            ]);
            //количество курсов, доступных в категории пользователю
            $havecourses = $this->get_courses($category->id, null, ['count' => true]);
            //количество категорий, доступных в категории пользователю
            $havehcildren = count($this->get_category_children($category->id));

            if( $visible && ( $havecourses || $havehcildren ) )
            {//все испытания пройдены - добавляем категорию в список доступных
                $result[$category->id] = $category;
            }
        }
        return $result;
    }

    /**
     * Получение массива курсов для текущей категории с учетом пейджинга
     *
     * @return array
     */
    public function get_courses_slice()
    {
        if( ! is_null($this->coursesslice))
        {
            return $this->coursesslice;
        }

        // Получим массив отфильтрованных курсов без учета пейджинга
        $this->coursesslice = $this->get_courses($this->category, null, ['paging' => true]);

        return $this->coursesslice;
    }

    /**
     * Получение количества курсов для текущей категории (с которой работаем)
     *
     * @return number
     */
    public function get_courses_count()
    {
        if( ! is_null($this->coursescount))
        {
            return $this->coursescount;
        }

        // Количество отфильтрованных курсов
        $this->coursescount = $this->get_courses($this->category, null, ['count' => true]);

        return $this->coursescount;
    }

    /**
     * Получение html-представления с курсами, отерендеренного указанным плагином
     *
     * @param string $plugincode - код плагина для рендера
     *
     * @return string
     */
    public function get_courses_html($plugincode)
    {
        $html = '';

        // Массив курсов с учетом пейджинга
        $coursesslice = $this->get_courses_slice();
        // Является ли текущая категория базовой
        $maincategory = ( (int)$this->category == (int)$this->topcategory );

        $block = local_crw_plugin::get(
            $plugincode,
            isset($this->pluginsettings[$plugincode]) ? $this->pluginsettings[$plugincode] : null
        );

        if( is_object($block) && $block->get_type() == CRW_PLUGIN_TYPE_COURSES_LIST )
        {
            $html .= $block->display([
                'cid' => $this->category,
                'courses' => $coursesslice,
                'main_category' => $maincategory
            ]);
        }

        return $html;
    }

    /**
     * Получить id курсов с учетом GET параметров
     *
     * @param int $category - идентификатор категории курсов
     * @param bool $fromsubcategories - требуется ли включать в выборку курсы из вложенных категорий
     *
     * @return array - Массив id курсов
     */
    private function get_courses($category=null, $fromsubcategories=null, $options=[])
    {
        global $DB, $SITE;

        if( is_null($category) )
        {
            $category = $this->category;
        }
        if( is_null($fromsubcategories) )
        {
            $fromsubcategories = $this->display_from_subcategories;
        }

        $where = '';
        $vars = [];

        // Фильтрация по категории
        $add = $this->get_sql_courses_by_category($category, $fromsubcategories);
        if ( ! empty($add) )
        {
            list($sqlselect, $varselect) = $add;
            $where .= $sqlselect;
            $vars = array_merge($vars, $varselect);
        }

        // Фильтрация по скрытым курсам
        $context = context_system::instance();
        if ( ! has_capability('moodle/course:viewhiddencourses', $context) )
        {// Прав нет, фильтруем скрытые
            if ( ! empty($where) )
            {// Добавим AND
                $where .= ' AND ';
            }
            $where .= ' c.visible = 1 ';
        }

        // Опрос плагинов и получение строки фильтрации от них
        foreach ( $this->slots['showcase'] as $block )
        {
            if ( is_object($block) )
            {
                $add = $block->get_sql_courses(['crws' => $this->searchquery, 'categoryid' => $category]);
                if ( ! empty($add) )
                {
                    list($sqlselect, $varselect, $sorttype) = $add;
                    if ( ! empty($where) && ! empty($sqlselect) )
                    {// Добавим AND
                        $where .= ' AND ';
                    }
                    $where .= $sqlselect;
                    $vars = array_merge($vars, $varselect);
                    // при отправке формы поиска был переопределён тип сортировка, настроенный в плагине
                    $this->set_courses_sort_settings($sorttype, 'ASC');
                }
            }
        }

        // Отображение только курсов, на которые пользователь подписан,
        if (!empty($this->userid))
        {
            $usercourses = enrol_get_all_users_courses(
                $this->userid,
                empty($this->user_courses_add_not_active),
                'id'
            );
            if( ! empty($usercourses) )
            {
                if ( ! empty($where) )
                {// Добавим AND
                    $where .= ' AND ';
                }
                $where .= " c.id IN (".implode(',',array_keys($usercourses)).") ";
            }
        }


        // Проверка прав на просмотр скрытых в витрине курсов
        $systemcontext = context_system::instance();
        if ( ! has_capability('local/crw:view_hidden_courses', $systemcontext) )
        {// Права на просмотр скрытых нет - фильтрация включена
            // Фильтрация по настройке скрыть курс
            if ( ! empty($where) )
            {// Добавим AND
                $where .= ' AND ';
            }
            $where .= " c.id NOT IN
                    ( SELECT DISTINCT crwcourse.courseid
                      FROM {crw_course_properties} crwcourse
                      WHERE crwcourse.name LIKE :crwcoursename AND crwcourse.value = :crwcoursevalue ) ";
            $varselect = array( 'crwcoursename' => 'hide_course', 'crwcoursevalue' => 1 );
            $vars = array_merge($vars, $varselect);
        }

        // Проверка прав на просмотр скрытых в витрине категорий
        if ( ! has_capability('local/crw:view_hidden_categories', $systemcontext) )
        {// Права на просмотр скрытых нет - фильтрация включена
            // Фильтрация по настройке скрыть категорию
            if ( ! empty($where) )
            {// Добавим AND
                $where .= ' AND ';
            }
            $where .= " c.category NOT IN
                    ( SELECT DISTINCT crwcategory.categoryid
                      FROM {crw_category_properties} crwcategory
                      WHERE crwcategory.name LIKE :crwcategoryname AND crwcategory.value = :crwcategoryvalue ) ";
            $varselect = array( 'crwcategoryname' => 'hide_category', 'crwcategoryvalue' => 1 );
            $vars = array_merge($vars, $varselect);
        }

        // Сформировать запрос
        if ( ! empty($where) )
        {// Есть условия
            $where = ' AND '.$where;
        }
        $vars['siteid'] = $SITE->id;



        $fields = 'c.id';
        if (!empty($options['count']))
        {
            $fields = 'COUNT(c.id) as coursescount';
            // требуется общее количество, отменяем настройки пагинации
            $limitfrom = 0;
            $limitnum = 0;
        }

        $orderby = '';
        // Дополнительная сортировка на случай необходимости
        $additionalorderby = '';
        // настраиваем сортировку только если она нужна (при запросе количества сортировка не важна, не добавляем лишнюю нагрузку)
        if(empty($options['count']))
        {
            switch($this->coursessorttype)
            {
                case CRW_COURSES_SORT_TYPE_COURSE_CREATED:
                    $orderby = 'c.timecreated';
                    break;
                case CRW_COURSES_SORT_TYPE_COURSE_START:
                    $orderby = 'c.startdate';
                    break;
                case CRW_COURSES_SORT_TYPE_LEARNINGHISTORY_ENROLMENTS:
                    $fields = 'c.id, (SELECT count(distinct(lh.userid)) FROM {local_learninghistory} lh WHERE lh.courseid=c.id) as enrolmentsever';
                    $orderby = 'enrolmentsever';
                    break;
                case CRW_COURSES_SORT_TYPE_ACTIVE_ENROLMENTS:
                    $fields = 'c.id, (
                        SELECT count(distinct(ue.userid))
                          FROM mdl_enrol e
                     LEFT JOIN mdl_user_enrolments ue ON ue.enrolid=e.id
                         WHERE e.courseid=c.id AND e.status=0 AND ue.status=0
                               AND (ue.timestart=0 OR ue.timestart<NOW())
                               AND (ue.timeend = 0 OR ue.timeend>NOW())
                    ) as activeenrolments';
                    $orderby = 'activeenrolments';
                    break;
                case CRW_COURSES_SORT_TYPE_COURSE_POPULARITY:
                    $fields = 'c.id, (SELECT distinct(crwcourse.sortvalue) FROM {crw_course_properties} crwcourse WHERE crwcourse.courseid=c.id AND crwcourse.name=\'course_popularity\') as cpopularity';
                    $orderby = 'cpopularity';
                    $additionalorderby = 'c.sortorder ASC';
                    break;
                case CRW_COURSES_SORT_TYPE_COURSE_NAME:
                    $orderby = 'c.fullname';
                    break;
                case CRW_COURSES_SORT_TYPE_COURSE_SORT:
                    $orderby = 'c.sortorder';
                    break;
                default:
                    if (mb_substr($this->coursessorttype, 0, 4) == 'cff_')
                    {
                        $joins = ' LEFT JOIN {crw_course_properties} crwcp
                                          ON crwcp.id = (SELECT id
                                                           FROM {crw_course_properties}
                                                          WHERE courseid=c.id
                                                            AND name=\''.$this->coursessorttype.'\'
                                                          LIMIT 1
                                                         )';
                        $orderby = 'CASE WHEN crwcp.sortvalue IS NULL THEN 1 ELSE 0 END ASC, crwcp.sortvalue';
                        $additionalorderby = 'CASE WHEN crwcp.svalue IS NULL THEN 1 ELSE 0 END ASC, crwcp.svalue ASC';

                    } else
                    {
                        $orderby = 'c.sortorder';
                    }
                    break;
            }
            if (trim($this->coursessortdirection) == 'DESC')
            {
                $orderby .= ' DESC';
            } else {
                $orderby .= ' ASC';
            }
        }
        if (!empty($orderby))
        {
            $orderby = ' ORDER BY '.$orderby;
            if (!empty($additionalorderby)) {
                $orderby .= ', ' . $additionalorderby;
            }
        }


        $limitfrom = 0;
        $limitnum = 0;
        if (!empty($options['paging']))
        {
            $limitfrom = $this->perpage * $this->page;
            $limitnum = $this->perpage;
        }

        $sql = 'SELECT ' . $fields . '
                  FROM {course} c ' . ($joins ?? '') . '
                 WHERE c.id != :siteid ' . $where . $orderby;

        // Получить курсы
        $courses = $DB->get_records_sql($sql, $vars, $limitfrom, $limitnum);

        if (!empty($options['count']))
        {
            $countrec = array_shift($courses);
            return $countrec->coursescount;
        } elseif (count($courses) == 1)
        {
            $redirect = get_config('crw_system_search', 'settings_single_result_redirect');
            if ($redirect == 'always' && !$this->ajax)
            {
                $course = array_shift($courses);
                $crwcourseurl = new moodle_url('/local/crw/course.php', ['id' => $course->id]);
                redirect($crwcourseurl);
            }
        }

        return $courses;
    }


    /**
     * Получить элемент sql кода для фильтрации курсов по категории
     *
     * @param int $category - идентификатор категории курсов
     * @param bool $fromsubcategories - требуется ли включать в выборку курсы из вложенных категорий
     * @return array(string $sql, array $vars)
     *                  $sql - участок sql запроса
     *                  $vars - массив плейсхолдеров
     */
    private function get_sql_courses_by_category($category, $fromsubcategories)
    {
        global $DB;

        // массив категорий, в которых требуется отобразить курс
        $categories = [$category];

        if ( $fromsubcategories && $category == 0)
        {// включено отображение вложенных категорий для верхнего уровня - никакие условия не нужны
            return ['', []];
        } else if ( $fromsubcategories )
        {// Необходимо добавить в выборку курсы из подкатегорий
            $categorycondition = $DB->sql_like('path', ':catpath', false);
            $categoryparams = [
                'catpath' => "%/$category/%"
            ];
            $categoryrecords = $DB->get_records_select(
                'course_categories',
                $categorycondition,
                $categoryparams,
                '',
                'id'
            );
            if( ! empty($categoryrecords) )
            {
                foreach($categoryrecords as $categoryrecord)
                {
                    $categories[] = $categoryrecord->id;
                }
            }
        }

        // формирование запроса с учетом отображения курса в дополнительных категориях
        list($sqlin1, $sqlparams1) = $DB->get_in_or_equal($categories, SQL_PARAMS_NAMED);
        list($sqlin2, $sqlparams2) = $DB->get_in_or_equal($categories, SQL_PARAMS_NAMED);
        $sql = '( c.category '.$sqlin1.' OR c.id IN (
                SELECT DISTINCT crwcoursecat.courseid
                FROM {crw_course_categories} crwcoursecat
                WHERE crwcoursecat.categoryid '.$sqlin2.'
            )
        )';
        $sqlparams = array_merge($sqlparams1,$sqlparams2);

        return [$sql, $sqlparams];
    }


}

/**
 * Функция обновляет значения в БД при изменении конфигурации
 *
 * @param int $newversion - номер версии, до которой выполняется обновление
 *
 */
function local_crw_fix_config_changes( $newversion )
{
    global $CFG, $DB;

    switch ( $newversion )
    {
        case 2016033100:
            //заменяем старую настройку видимости категорий курсов новой настройкой
            $DB->set_field_select('crw_course_properties', 'name', 'coursecat_view',
            "name='display_coursecat'");

            $courseswithlinks = '';
            //@todo - если не работает group_concat - выбирать все и пробегаясь циклом собирать id курсов
            //получаем идентификаторы курсов, в которых категория курсов должна отображаться в виде ссылки
            $courseswithlinks = $DB->get_record_select('crw_course_properties',
                'name = :propname AND svalue = :propsvalue GROUP BY name',
                [
                    'propname' => 'display_coursecat_link',
                    'propsvalue' => '1'
                ], "GROUP_CONCAT(`courseid` SEPARATOR ',') as `value`");

            if ( $courseswithlinks != '' )
            {
                //если новая настройка говорит, что категорию курсов надо отображать и при этом она в старых настройках отображалась в виде ссылки - ставим нужное значение в новые настройки
                $DB->set_field_select('crw_course_properties', 'value', '2',
                    "name='coursecat_view' AND value='1' AND courseid IN (" .
                    $courseswithlinks->value . ")");
                //второе поле тоже делаем с такой настройкой
                $DB->set_field_select('crw_course_properties', 'svalue', '2',
                    "name='coursecat_view' AND value='2'");
            }

            //удаляем старые настройки отображения курса в виде ссылки
            $DB->delete_records('crw_course_properties', [
                'name' => 'display_coursecat_link'
            ]);

            break;
        case 2016121902:
            $coursesticker = [];
            //получим все устаревшие настройки наклеек на курсы
            $oldstickers = $DB->get_records_select('crw_course_properties', "
                name IN ('special_offer','action_offer','free_offer')
                AND (value<>'0' || svalue<>'0')");
            if( !empty($oldstickers) )
            {//есть устаревшие настройки
                foreach($oldstickers as $oldsticker)
                {
                    switch($oldsticker->name)
                    {
                        case 'special_offer':
                            $coursesticker[$oldsticker->courseid] = 1;
                            break;
                        case 'action_offer':
                            $coursesticker[$oldsticker->courseid] = 2;
                            break;
                        case 'free_offer':
                            $coursesticker[$oldsticker->courseid] = 3;
                            break;
                    }
                }
            }

            $keysofcoursesticker = array_keys($coursesticker);
            if( !empty($keysofcoursesticker) )
            {
                //получим курсы, у которых уже есть новая настройка и ее не надо создавать (надо только обновить при необходимости)
                $properties = $DB->get_records_select('crw_course_properties', "name='sticker' AND
                    courseid IN (".implode(',',$keysofcoursesticker).")");
                if( !empty($properties) )
                {
                    $toupdate = [];
                    foreach( $properties as $property )
                    {
                        $toupdate[$coursesticker[$property->courseid]][] = $property->courseid;
                        unset($coursesticker[$property->courseid]);
                    }
                    //зададим новую настройку, чтобы появилась возможность избавиться от старой
                    foreach ($toupdate as $stickervalue=>$courses)
                    {
                        if( !empty($courses) )
                        {
                            //обновляем новую настройку если она равна нулю (отображение велось по старой настройке)
                            $DB->set_field_select('crw_course_properties', 'svalue', $stickervalue, "
                                name='sticker' AND value='0' AND courseid IN (".implode(',',$courses).")");
                            $DB->set_field_select('crw_course_properties', 'value', $stickervalue, "
                                name='sticker' AND value='0' AND courseid IN (".implode(',',$courses).")");
                        }
                    }
                }
            }

            $configobjs = [];
            //остались только курсы, у которых новой настройки пока вообще нет
            foreach ($coursesticker as $courseid=>$stickervalue)
            {
                //задаем новую настройку
                $configobj = new stdClass();
                $configobj->name = 'sticker';
                $configobj->courseid = $courseid;
                $configobj->svalue = $stickervalue;
                $configobj->value = $stickervalue;
                $configobjs[] = $configobj;
            }
            if ( !empty($configobjs) )
            {
                $DB->insert_records('crw_course_properties', $configobjs);
            }

            //удаляем старую настройку
            $DB->delete_records_select('crw_course_properties', "name IN ('special_offer','action_offer','free_offer')");

            break;
    }
}

/**
 * Имеет ли пользователь право на просмотр курса, на который он не подписан
 *
 * @param object $context
 * @param object $user
 * @return boolean
 */
function local_crw_can_view_course($context,$user=null)
{
    global $USER;
    if( is_null($user) )
    {
        $user = clone($USER);
    }
    return has_capability('moodle/course:view', $context, $user->id);
}

/**
 * Является-ли пользователь администратором
 *
 * @return bool - true, если пользователь является администратором и
 *                false, если пользователь не входт в эту группу
 */
function local_crw_is_admin()
{
    global $USER;
    // Получаем администраторов
    $admins = get_admins();
    // Проверяем, есть ли среди администраторов данный пользователь
    foreach ( $admins as $user )
    {
        if ( $user->id == $USER->id )
        {// Пользователь - администратор
            return true;
        }
    }
    // Пользователь - не администратор
    return false;
}

/**
 * Возвращает текущий url
 */
function local_crw_current_url($categoryid=null, $crws=null, $userid=null, $notactive=null)
{
    global $PAGE;
    /**
     * @var moodle_url $url
     */
    $url = $PAGE->__get('url');
    $url->params($_GET);
    if (!is_null($categoryid))
    {
        $url->param('cid', $categoryid);
    }
    if (!is_null($crws))
    {
        $url->param('crws', $crws);
    }
    if (!is_null($userid))
    {
        $url->param('uid', $userid);
    }
    if (!is_null($notactive))
    {
        $url->param('na', (int)!empty($notactive));
    }
    return $url;
}

/**
 * Получить дополнительные свойства курса
 *
 * @param int $courseid
 *            - ID курса
 * @param string $name
 *            - имя свойства
 * @param bool $multiple
 *            - комплексное свойство
 *
 * @return mixed - значение(я) свойства
 */
function local_crw_get_course_config($courseid, $name, $multiple = false)
{
    global $DB;

    if ( $multiple )
    { // Настройка комплексная
        // Получим свойства
        $config = $DB->get_records('crw_course_properties', array (
                'courseid' => $courseid,
                'name' => $name
        ));
        return $config;
    } else
    { // Настройка состоит из 1 записи
        // Получим свойство
        $config = $DB->get_record('crw_course_properties', array (
                'courseid' => $courseid,
                'name' => $name
        ));
        if ( ! empty($config) )
        {
            return $config->value;
        } else
        {
            return false;
        }
    }
}

/**
 * Получить дополнительные свойства категории
 *
 * @param int $categoryid
 *            - ID категории
 * @param string $name
 *            - имя свойства
 * @param bool $multiple
 *            - комплексное свойство
 *
 * @return mixed - значение(я) свойства
 */
function local_crw_get_category_config($categoryid, $name, $multiple = false)
{
    global $DB;

    if ( $multiple )
    { // Настройка комплексная
        // Получим свойства
        $config = $DB->get_records('crw_category_properties', array (
                        'categoryid' => $categoryid,
                        'name' => $name
        ));
        return $config;
    } else
    { // Настройка состоит из 1 записи
        // Получим свойство
        $config = $DB->get_record('crw_category_properties', array (
                        'categoryid' => $categoryid,
                        'name' => $name
        ));
        if ( ! empty($config) )
        {
            return $config->value;
        } else
        {
            return false;
        }
    }
}

/**
 * Получить стоимость курса
 *
 * @param stdClass $course
 *            - объект курса
 *
 * @return string - стоимость курса
 */
function local_crw_get_course_price($course, $sign = true)
{
    // Получаем свойство
    $price = local_crw_get_course_config($course->id, 'course_price');
    if ( ! empty($price) )
    { // Свойство найдено
        if ( $price == strval(floatval($price)) && $sign )
        {// Передано число
            $rub = html_writer::empty_tag('del');
            $rub .= get_string('rub', 'local_crw');
            $rub .= html_writer::end_tag('del');
            return $price.$rub;
        }
        return $price;
    } else
    { // Свойство не найдено
        return '';
    }
}

/**
 * @deprecated необходимо использовать extend вместо extends в названии метода, начиная с версии 2.9
 * Описание: метод, добавляющий ссылку на страницу дополнительных настроек курса
 *
 * @param unknown $settingsnav
 * @param unknown $context
 */
function local_crw_extends_settings_navigation($settingsnav, $context) {
    local_crw_extend_settings_navigation($settingsnav, $context);
}

function local_crw_extend_navigation(global_navigation $nav)
{
    global $PAGE;
    if( strpos($PAGE->pagetype, 'local-crw') !== false )
    {
        $activenode = $nav->find_active_node();
        if( ! empty($activenode->parent) )
        {
            if($activenode->parent->has_children())
            {
                $activenode->remove();
            }
        }
    }

    $configoverride = get_config('local_crw', 'override_navigation');
    if (!empty($configoverride)) {
        // Переопределяем стандартную навигацию
        local_crw_override_navigation($nav);
    }

    // настройка скрывать узел Курсы (Мои курсы)
    $removecoursesnode = get_config('local_crw', 'remove_courses_nav_node');
    if (!empty($removecoursesnode))
    {
        // Найдем тот узел, который активен (отображается в хлебных крошках)
        $coursesnode = $nav->find('mycourses', navigation_node::TYPE_ROOTNODE);
        if (!$coursesnode || !$coursesnode->contains_active_node()) {
            $coursesnode = $nav->find('courses', navigation_node::TYPE_ROOTNODE);
        }
        if ($coursesnode) {
            // привязка ближайшего потомка к родителю (т.о. исключаем найденную ноду из последовательности)
            $child = $coursesnode->children->getIterator()->current();
            if (method_exists($child, 'set_parent'))
            {
                $child->set_parent($coursesnode->parent);
            }
        }
    }
}

/**
 * Метод, добавляющий ссылку на страницу дополнительных настроек курса
 *
 * @param unknown $settingsnav
 * @param unknown $context
 */
function local_crw_extend_settings_navigation(settings_navigation $settingsnav, context $context) {
    global $PAGE;

    // Ссылку на дополнительные настройки категории курсов увидят люди только с соответствующими правами
    if ($context->contextlevel == CONTEXT_COURSECAT && has_capability('moodle/category:manage', $context) &&
        $settingnode = $settingsnav->find('categorysettings', navigation_node::TYPE_UNKNOWN))
    {// Нужный контекст, доступ есть, вкладка "Управление категрией" найдена

        // Формирование ссылки на страницу доп.настроек
        $url = new moodle_url(
                '/local/crw/categorysettings.php',
                [
                    'id' => $context->instanceid,
                    'returnto' => $PAGE->url->out(true)
                ]
        );
        // Создание нового пункта меню
        $node = navigation_node::create(
                get_string('categorysettings', 'local_crw'),
                $url,
                navigation_node::NODETYPE_LEAF,
                'additionalcategorysettings',
                'additionalcategorysettings',
                new pix_icon('i/settings', '')
        );
        // Если пользователь находится на странице доп.настроек, надо выделить пункт
        if ($PAGE->url->compare($url, URL_MATCH_BASE))
        {
            $node->make_active();
        }
        // Добавление нового пункта
        $settingnode->add_node($node);
    }

    // Добавим новоую страницу настроек для страниц курса
    if ( $PAGE->course && $PAGE->course->id != SITEID ) {

//         if ($context->contextlevel == CONTEXT_COURSE || $context->contextlevel == CONTEXT_MODULE) {
//             $configoverride = get_config('local_crw', 'override_breadcrumb_navigation');
//             if (!empty($configoverride)) {
//                 // Переопределяем стандартную навигацию в хлебных крошках
//                 local_crw_override_breadcrumb_navigation();
//             }
//         }

        // Ссылку страницу увидят люди только с соответствующими правами
        if ( ! has_capability('moodle/course:update', context_course::instance($PAGE->course->id))) {
            return NULL;
        }

        if ( $settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE) )
        {// Есть вкладка "Управление курсом"
            // Ссылка на страницу настроек
            $url = new moodle_url(
                    '/local/crw/coursesettings.php',
                    array('id' => $PAGE->course->id)
            );
            // Добавим новый пункт меню
            $node = navigation_node::create(
                    get_string('additional_coursesettings', 'local_crw'),
                    $url,
                    navigation_node::NODETYPE_LEAF,
                    'course_preview',
                    'course_preview',
                    new pix_icon('i/settings', '')
            );
            if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
                $node->make_active();
            }
            $settingnode->add_node($node);


            // Ссылка на страницу настроек по кастомной форме
            $customcoursefields = get_config('local_crw', 'custom_course_fields');
            if (!empty($customcoursefields))
            {
                $url = new moodle_url('/local/crw/coursecustomsettings.php', ['id' => $PAGE->course->id]);
                // Добавим новый пункт меню
                $node = navigation_node::create(
                    get_string('additional_coursecustomsettings', 'local_crw'),
                    $url,
                    navigation_node::NODETYPE_LEAF,
                    'course_custom_settings',
                    'course_custom_settings',
                    new pix_icon('i/settings', '')
                );
                if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
                    $node->make_active();
                }
                $settingnode->add_node($node);
            }
        }
    }
}

/**
 * Метод, вызывающийся в самом начале, сразу после обработки конфига
 * Используется для того, чтобы сделать редирект в случае, когда настроено переопределение навигации.
 */
function local_crw_after_config() {
    global $FULLME;

    $configoverride = get_config('local_crw', 'override_navigation');
    if (!empty($configoverride)) {

        $currenturl = new moodle_url($FULLME);
        if (in_array($currenturl->get_path(), ['/course/','/course/index.php'])) {
            $cid = $currenturl->get_param('categoryid');
            if (!is_null($cid)) {
                redirect(new moodle_url('/local/crw/category.php', ['cid'=>$cid]));
            }
        }
    }
}

/**
 * Переопределяет стандартную навигацию в хлебных крошках,
 * заменяя:
 *  - ссылки на категорию курсов ссылками на категорию курсов в витрине,
 *  - ссылки на станицу курсов ссылками на основную страницу витрины курсов
 */
function local_crw_override_navigation(global_navigation $nav) {
    global $PAGE, $USER;

    if ($coursesnode = $nav->find('courses', navigation_node::TYPE_ROOTNODE)) {
        // Заменяем ссылки на станицу курсов ссылками на основную страницу витрины курсов
        $coursesnode->action = new moodle_url('/local/crw/');
    }
    $categoryid = $PAGE->course->category ? $PAGE->course->category : optional_param('categoryid', 0, PARAM_INT);
    if ($categorynode = $nav->find($categoryid, navigation_node::TYPE_CATEGORY)) {
        // Заменяем ссылки на категорию курсов ссылками на категорию курсов в витрине
        $categorynode->action = new moodle_url('/local/crw/category.php', ['cid' => $categoryid]);
    }
    if ($PAGE->course && $PAGE->course->id != SITEID) {
        if ($coursenode = $nav->find($PAGE->course->id, navigation_node::TYPE_COURSE)) {

            // проверим, не был ли настроен пропуск страницы описания курса
            $courseinfoview = local_crw_get_course_config($PAGE->course->id, 'course_info_view');
            if (empty($courseinfoview))
            {// настройки нет или настроено наследовать - берем из настройки плагина
                $courseinfoview = get_config('local_crw', 'course_info_view');
            }
            $coursecontext = \context_course::instance($PAGE->course->id, MUST_EXIST);
            if ($courseinfoview != 3 && ($courseinfoview != 2 || !is_enrolled($coursecontext, $USER, '', true)))
            {// Страницу описания можно показывать - вот и добавим её в навигацию

                // Добавляем на страницы курсов дополнительный узел - страница О курсе
                $textandtitle = get_string('about_course', 'local_crw');
                $coursedescproperties = [
                    'type' => navigation_node::TYPE_COURSE,
                    'key' => 'local-crw-course-' . $PAGE->course->id,
                    'action' => new moodle_url('/local/crw/course.php', ['id' => $PAGE->course->id]),
                    'text' => $textandtitle
                ];
                $coursedescnode = new navigation_node($coursedescproperties);
                $coursedescnode->add_class('about_course');
                $coursedescnode->title($textandtitle);
                $coursedescnode->set_parent($coursenode->parent);
                $coursenode->set_parent($coursedescnode);
            }
        }
    }
}

/**
 * Возможен ли просмотр категории текущим пользователем
 *
 * @param int $catid - ID категории, по которой ведется проверка
 * @param int $userid - ID пользователя, для которого требуется сделать проверку
 * @param array $options - Массив дополнительных опций проверки
 *              ['must_have_parent'] - ID категории, которая обязана быть родительской для проверяемой категории
 * @return bool - Результат проверки
 */
function local_crw_category_can_view($catid, $userid = 0, $options = [])
{
    global $DB, $USER;

    if ( empty($userid) )
    {// ID не указан
        $userid = $USER->id;
    } else
    {// ID указан
        $user = $DB->get_record('user', ['id' => $userid]);
        if ( empty($user) )
        {// Пользователь не найден
            return false;
        }
    }

    if ( $catid != 0 )
    {
        // Получение категории
        $category = $DB->get_record('course_categories', ['id' => $catid]);
        if ( empty($category) )
        {// Категория не найдена
            return false;
        }

        if ( isset($options['must_have_parent']) && $options['must_have_parent'] != 0 )
        {// Определен родитель
            $isparent = strpos($category->path, '/'.$options['must_have_parent'].'/');
            if ( $isparent === false )
            {
                return false;
            }
        }

        // Контекст категории
        $catcontext = context_coursecat::instance($category->id);
        if ( empty($category->visible) && ! has_capability('moodle/category:viewhiddencategories', $catcontext, $userid) )
        {// Категория скрыта и пользователь не может ее просматривать
            return false;
        }

        // Получение состояния скрытия текущей категории в дополнительных настройках
        $ishidden = local_crw_get_category_config($category->id, 'hide_category');
        $systemcontext = context_system::instance();
        if ( ! empty($ishidden) && ! has_capability('local/crw:view_hidden_categories', $systemcontext, $userid) )
        {// Категория скрыта в витрине и пользователь не может ее просматривать
            return false;
        }
    }

    // Пользователь может просматривать категорию
    return true;
}

/**
 * Создать превью изображения
 *
 * @param stored_file $src - Объект исходного изображения
 * @param stdClass $recorddiff - Изменения в записи исходного файла и превью
 * @param integer $width - Ширина превью. Если не указано превью массштабируется
 * @param integer $height - Высота превью. Если не указано превью массштабируется
 * @param real $rgb - Цвет заливки изображения, если формат избражения не соответствует исходному
 * @param integer $quality - Качество изображения
 * @param bool $scailing - Включить пропорциональное масштабирование превью изображения
 *
 * @return boolean - Результат создания изображения
 */
function local_crw_create_preview(stored_file $file, stdClass $recorddiff, $width = NULL, $height = NULL, $rgb=0xFFFFFF, $quality=100)
{
    global $CFG;
    require_once ($CFG->libdir . '/filestorage/file_storage.php');

    if ( ! $file->is_valid_image() )
    {// Файл не является изображением
        return false;
    }

    // Копирование исходного файла изображения
    $thumbnailpath = $file->copy_content_to_temp('crw_thumbnails');
    if ( empty($thumbnailpath) )
    {// Файл превью не создался
        return false;
    }

    // Получение информации об изображении
    $imageinfo = $file->get_imageinfo();
    if ( empty($imageinfo) )
    {// Данные о изображении не получены
        // Получение информации из превью
        $imageinfo = getimagesize($thumbnailpath);
        if ( empty($imageinfo) )
        {// Данные невозможно получить
            return false;
        }
        // Нормализация
        $imageinfo['mimetype'] = $imageinfo['mime'];
    }

    // Формирование размеров превью
    if ( ! empty($width) && $width > 0 )
    {// Указана ширина изображения
        $resizewidth = $width;
        if ( ! empty($height) && $height > 0  )
        {// Указана высота
            $resizeheight = $height;
        } else
        {// Высота не указана
            $resizeheight = ($width / $imageinfo['width']) * $imageinfo['height'];
        }
    } else
    {// Ширина в зависимости от высоты
        if ( ! empty($height) && $height > 0  )
        {// Указана высота
            $resizewidth = ($height / $imageinfo['height']) * $imageinfo['width'];
            $resizeheight = $height;
        } else
        {// Высота не указана
            // Исходные размеры
            $resizewidth = $imageinfo['width'];
            $resizeheight = $imageinfo['height'];
        }
    }

    // Формат изображения
    $format = strtolower(substr($imageinfo['mimetype'], strpos($imageinfo['mimetype'], '/')+1));

    // Функции работы с типом изображения
    $icfunc = "imagecreatefrom" . $format;
    $imagefunc = "image" . $format;

    if ( ! function_exists($icfunc) || ! function_exists($imagefunc) )
    {// Функция работы с текущим типом изображения не найдена
        return false;
    }

    // Получение коэфициента сжатия по горизонтали и вертикали
    $x_ratio = $resizewidth / $imageinfo['width'];
    $y_ratio = $resizeheight / $imageinfo['height'];

    // Получение общего сжатия
    $ratio       = min($x_ratio, $y_ratio);
    $use_x_ratio = ($x_ratio == $ratio);

    $new_width   = $use_x_ratio  ? $resizewidth  : floor($imageinfo['width'] * $ratio);
    $new_height  = !$use_x_ratio ? $resizeheight : floor($imageinfo['height'] * $ratio);
    $new_left    = $use_x_ratio  ? 0 : floor(($resizewidth - $new_width) / 2);
    $new_top     = !$use_x_ratio ? 0 : floor(($resizeheight - $new_height) / 2);

    // Формирование превью
    $isrc = $icfunc($thumbnailpath);
    $idest = imagecreatetruecolor($resizewidth, $resizeheight);
    imagefill($idest, 0, 0, $rgb);
    imagecopyresampled(
            $idest,
            $isrc,
            $new_left,
            $new_top,
            0,
            0,
            $new_width,
            $new_height,
            $imageinfo['width'],
            $imageinfo['height']
    );

    // Нормализация значений
    switch ( $format )
    {
        case 'png' :
            $quality = (integer)(( 100 - $quality ) / 9);
            $imagefunc($idest, $thumbnailpath, $quality);
            break;
        case 'gif' :
            $imagefunc($idest, $thumbnailpath);
            break;
        default:
            $imagefunc($idest, $thumbnailpath, $quality);
            break;
    }

    // Удаление temp изображений
    imagedestroy($isrc);
    imagedestroy($idest);

    // Создание превью записи
    $thumbnailrecord = new stdClass();
    $thumbnailrecord->contextid = $file->get_contextid();
    $thumbnailrecord->component = $file->get_component();
    $thumbnailrecord->filearea = $file->get_filearea();
    $thumbnailrecord->itemid = $file->get_itemid();
    $thumbnailrecord->filepath = $file->get_filepath();
    $thumbnailrecord->filename = $file->get_filename();
    $thumbnailrecord->userid = $file->get_userid();
    $thumbnailrecord->filesize = $file->get_filesize();
    $thumbnailrecord->mimetype = $file->get_mimetype();
    $thumbnailrecord->status = $file->get_status();
    $thumbnailrecord->source = $file->get_source();
    $thumbnailrecord->author = $file->get_author();
    $thumbnailrecord->license = $file->get_license();
    $thumbnailrecord->timecreated = $file->get_timecreated();
    $thumbnailrecord->timemodified = $file->get_timemodified();
    $thumbnailrecord->sortorder = $file->get_sortorder();
    $thumbnailrecord->referencefileid = $file->get_referencefileid();
    $thumbnailrecord->reference = $file->get_reference();
    $thumbnailrecord->referencelastsync = $file->get_referencelastsync();
    if ( ! empty($recorddiff) )
    {// Получение значений, которые следует изменить в исходной записи
        foreach ( $recorddiff as $key => $value )
        {
            if ( isset($thumbnailrecord->$key) )
            {// Значение найдено
                $thumbnailrecord->$key = $value;
            }
        }
    }

    // Получение хранилища
    $fs = get_file_storage();

    // Сохранение превью изображения
    $thumbnail = $fs->create_file_from_pathname($thumbnailrecord, $thumbnailpath);

    return true;
}

/**
 * Получить URL превью изображения
 *
 * Если превью не найдено - производит создание. Если создание невозможно - возвращает URL исходного
 *
 * @param stored_file $src - Объект исходного изображения
 * @param stdClass $recorddiff - Изменения в записи исходного файла для превью
 * @param integer $width - Ширина превью. Если не указано превью массштабируется
 * @param integer $height - Высота превью. Если не указано превью массштабируется
 * @param real $rgb - Цвет заливки изображения, если формат избражения не соответствует исходному
 * @param integer $quality - Качество изображения
 * @param bool $scailing - Включить пропорциональное масштабирование превью изображения
 *
 * @return string - URL превью
 */
function local_crw_get_preview(stored_file $file, stdClass $recorddiff, $width = 500, $height = NULL, $rgb=0xFFFFFF, $quality=100)
{
    global $CFG;

    require_once ($CFG->libdir . '/filestorage/file_storage.php');

    // Формирование данных превью изображения
    $preview = new stdClass();
    $preview->contextid = $file->get_contextid();
    $preview->component = $file->get_component();
    $preview->filearea = $file->get_filearea();
    $preview->itemid = $file->get_itemid();
    $preview->filepath = $file->get_filepath();
    $preview->filename = $file->get_filename();
    if ( ! empty($recorddiff) )
    {// Получение значений, которые следует изменить в исходной записи
        foreach ( $recorddiff as $key => $value )
        {
            if ( isset($preview->$key) )
            {// Значение найдено
                $preview->$key = $value;
            }
        }
    }

    // Получение хранилища
    $fs = get_file_storage();

    // Проверка наличия превью
    $exist = $fs->file_exists(
                        $preview->contextid,
                        $preview->component,
                        $preview->filearea,
                        $preview->itemid,
                        $preview->filepath,
                        $preview->filename
    );
    if ( ! $exist )
    {// Файл не нейден
        // Создание превью изображения
        $result = local_crw_create_preview($file, $recorddiff, $width, $height, $rgb, $quality);
        if ( empty($result) )
        {// Создание не удалось
            // Вернуть url исходного
            $url = moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
            );

            return $url;
        }
    }
    // Вернуть url превью
    $url = moodle_url::make_pluginfile_url(
                        $preview->contextid,
                        $preview->component,
                        $preview->filearea,
                        $preview->itemid,
                        $preview->filepath,
                        $preview->filename
    );

    return $url;
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function local_crw_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array())
{

    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    /*if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }*/

    // Make sure the filearea is one of those used by the plugin.
    /*if ($filearea !== 'expectedfilearea' && $filearea !== 'anotherexpectedfilearea') {
        return false;
    }*/

    // Make sure the user is logged in and has access to the module (plugins that are not course modules should leave out the 'cm' part).
    //require_login($course, true, $cm);

    // Check the relevant capabilities - these may vary depending on the filearea being accessed.
    /*if (!has_capability('mod/MYPLUGIN:view', $context)) {
        return false;
    }*/

    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = array_shift($args); // The first item in the $args array.

    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // $args is empty => the path is '/'
    } else {
        $filepath = '/'.implode('/', $args).'/'; // $args contains elements of the filepath
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_crw', $filearea, $itemid, $filepath, $filename);

    if (!$file) {
        return false; // The file does not exist.
    }

    // NOTE: it woudl be nice to have file revisions here, for now rely on standard file lifetime,
    //       do not lower it because the files are dispalyed very often.
    \core\session\manager::write_close();
    send_stored_file($file, null, 0, $forcedownload, $options);
}

/**
 * Редирект на основе полученных настроек витрины
 * @param int $courseid id курса
 * @param bool $isenrolled подписан пользователь или нет
 */
function crw_redirect($courseid, $isenrolled)
{
    $localview = local_crw_get_course_config($courseid, 'course_info_view');
    if( (int)$localview === 0)
    {
        $localview = get_config('local_crw', 'course_info_view');
    }
    $globalview = get_config('local_crw', 'course_info_view');
    if( isset($localview) && $localview !== false )
    {
        switch_redirect($courseid, $isenrolled, $localview);
    } elseif( isset($globalview) && $globalview !== false )
    {
        switch_redirect($courseid, $isenrolled, $globalview);
    }
}
/**
 * Перенаправляет на страницу курса в зависимости от настроек
 * @param int $courseid id курса
 * @param bool $isenrolled подписан пользователь или нет
 * @param int $viewoption значение настройки additional_course_page_options|gloval_additional_course_page_options
 */
function switch_redirect($courseid, $isenrolled, $viewoption)
{
    switch((int)$viewoption)
    {
        case 1:
            break;
        case 2:
            if( $isenrolled )
            {
                redirect('/course/view.php?id=' . $courseid);
            }
            break;
        case 3:
            redirect('/course/view.php?id=' . $courseid);
            break;
        default:
            break;
    }
}

/**
 * Получение списка наклеек на курсы
 *
 * @param int $stickerkey - ключ. Если указать null, вернет полный список
 * @param array $options - дополнительные опции
 *                      ['langstrings'] - если true, вернет в качестве значений языковые строки
 * @return string[]|string|boolean - массив с данными, объект по ключу или false в случае ошибки
 */
function local_crw_get_stickers($stickerkey=null, $options=[] )
{
    $stickers = [
        1 => 'special_offer',
        2 => 'action_offer',
        3 => 'free_offer',
        4 => 'demo',
        5 => 'card_payment',
        6 => 'new',
        7 => 'bestseller',
        8 => 'beginner'
    ];

    if( ! empty($options['langstrings']) )
    {
        foreach($stickers as $k=>$v)
        {
            $stickers[$k] = get_string('sticker_'.$v, 'local_crw');
        }
    }

    if( is_null($stickerkey) )
    {
        return $stickers;
    }

    if( array_key_exists($stickerkey, $stickers) )
    {
        return $stickers[$stickerkey];
    }

    return false;
}

function local_crw_get_categories_header(core_course_category $category)
{
    // Базовая категория Витрины
    $topcategoryid = get_config('local_crw', 'main_catid');

    $stringid = 'showcase_course_categories_title';
    if ( $category->id == $topcategoryid )
    {
        $stringid = 'top_'.$stringid;
    }

    $a = new stdClass();
    $a->name = $category->name;

    // Формирование заголовка
    $categoriesheadertitle = html_writer::div(
        get_string($stringid, 'local_crw', $a),
        'crw_cs_catblocktitle'
    );

    return $categoriesheadertitle;
}

function local_crw_get_courses_header(core_course_category $category, $issearchresult = false)
{
    // Базовая категория Витрины
    $topcategoryid = get_config('local_crw', 'main_catid');

    $stringid = 'showcase_course_courses_title';
    if ( $category->id == $topcategoryid )
    {
        $stringid = 'top_'.$stringid;
    }

    $a = new stdClass();
    $a->name = $category->name;

    // Формирование заголовка
    $coursesheadertitle = html_writer::div(
        get_string($stringid, 'local_crw', $a) . ($issearchresult ?  get_string('search_results_subheader', 'local_crw') : ''),
        'crwh_categoryname'
    );

    return $coursesheadertitle;
}

function local_crw_get_category_create_link($categoryid=0)
{
    if ( $categoryid )
    {
        $categorycontext = context_coursecat::instance($categoryid);
    } else
    {
        $categorycontext = context_system::instance();
    }

    if( has_capability('moodle/category:manage', $categorycontext) )
    {
        $newcaturl = new moodle_url('/course/editcategory.php', [
            'parent' => $categoryid
        ]);
        $categorycreatelink = html_writer::link(
            $newcaturl,
            get_string('add_category','local_crw'),
            ['class' => 'btn btn-primary addcategory']
        );
        return $categorycreatelink;
    } else
    {
        return '';
    }
}


function local_crw_get_course_create_link($categoryid=0)
{
    if ( $categoryid )
    {
        $category = core_course_category::get($categoryid);
    } else
    {
        $category = core_course_category::get_default();
    }

    $categorycontext = context_coursecat::instance($category->id);
    $crwcategoryurl = new moodle_url(
        '/local/crw/category.php',
        [
            'cid'=>$category->id
        ]
    );
    if( has_capability('moodle/course:create', $categorycontext) )
    {
        $newcourseurl = new moodle_url('/course/edit.php', [
            'category' => $category->id,
            'returnto' => 'url',
            'returnurl' => $crwcategoryurl->out(true),
            'sesskey' => sesskey()
        ]);
        $coursecreatelink = html_writer::link(
            $newcourseurl,
            get_string('add_course','local_crw'),
            ['class' => 'btn btn-primary addcourse']
        );
        return $coursecreatelink;
    } else
    {
        return '';
    }
}
/**
 * Получить url-адрес изображения для текущего курса
 *
 * @param stdClass $course - объект курса
 * @return string - url-адрес изображения для текущего курса
 */
function local_crw_course_image_url($course)
{
    global $CFG;

    require_once ($CFG->libdir . '/filestorage/file_storage.php');
    require_once ($CFG->dirroot . '/course/lib.php');

    $courseimgurl = file_encode_url($CFG->wwwroot, '/local/crw/assets/no-photo.gif');

    // Если лимит не позволяет - выводим заглушку
    if ( ! empty($CFG->courseoverviewfileslimit) )
    {
        //Получаем ид изображения для фона
        $showcaseimg = local_crw_get_course_config($course->id, 'showcase_imgs');
        // Получаем хранилище
        $fs = get_file_storage();
        // Получаем контекст
        $context = context_course::instance($course->id);
        // Получаем файлы
        $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', false, 'filename', false);
        // Вывод первого файла
        if ( count($files) )
        {
            // Формирование изменений между превью и исходным файлом
            $preview = new stdClass();
            $preview->component = 'local_crw';
            foreach ( $files as $file )
            {
                if ( $file->is_valid_image() )
                {// файл является изображением
                    $fileid = $file->get_id();
                    if(!$showcaseimg || $fileid == $showcaseimg) {
                        $courseimgurl = local_crw_get_preview($file, $preview);
                        break;
                    }
                }
            }
        }
    }
    return $courseimgurl;
}

/**
 * Получение списка кодов реализованных шаблонов описательной страницы курса
 * @return string[]
 */
function local_crw_get_coursepage_templates_codes()
{
    global $CFG;

    $templatecodes = [];

    foreach(glob($CFG->dirroot . '/local/crw/templates/*_coursepage.mustache') as $templatepath)
    {
        $templatecodes[] = basename($templatepath, '_coursepage.mustache');
    }

    return $templatecodes;
}

function local_crw_get_coursepage_templates()
{
    // получение имеющихся шаблонов
    $templatescodes = local_crw_get_coursepage_templates_codes();

    // формирование имен шаблонов
    $templatesnames = array_map(function($templatecode){
        if (get_string_manager()->string_exists('coursepage_template_code_'.$templatecode, 'local_crw')) {
            return get_string('coursepage_template_code_'.$templatecode, 'local_crw');
        }
        return $templatecode;
    }, $templatescodes);

    return array_combine($templatescodes, $templatesnames);
}

/**
 * Получение всех обычных вариантов сортировки
 *
 * @return string[]
 */
function local_crw_get_all_default_sort_types()
{
    return [
        CRW_COURSES_SORT_TYPE_COURSE_SORT => get_string('courses_sort_type_course_sort', 'local_crw'),
        CRW_COURSES_SORT_TYPE_COURSE_POPULARITY => get_string('courses_sort_type_course_popularity', 'local_crw'),
        CRW_COURSES_SORT_TYPE_COURSE_NAME => get_string('courses_sort_type_course_name', 'local_crw'),
        CRW_COURSES_SORT_TYPE_COURSE_CREATED => get_string('courses_sort_type_course_created', 'local_crw'),
        CRW_COURSES_SORT_TYPE_COURSE_START => get_string('courses_sort_type_course_start', 'local_crw'),
        CRW_COURSES_SORT_TYPE_LEARNINGHISTORY_ENROLMENTS => get_string('courses_sort_type_learninghistory_enrolments', 'local_crw'),
        CRW_COURSES_SORT_TYPE_ACTIVE_ENROLMENTS => get_string('courses_sort_type_active_enrolments', 'local_crw'),
    ];
}

/**
 * Получение обычных вариантов сортировки, настроенных для использовании в витрине
 *
 * @return string[]
 */
function local_crw_get_allowed_basic_sort_types()
{

    // Сортировка курсов
    $result = local_crw_get_all_default_sort_types();

    $allowedsorttypesstr = get_config('local_crw', 'course_sort_types');
    if ($allowedsorttypesstr !== false)
    {
        $allowedsorttypes = explode(',', $allowedsorttypesstr);
        $result = array_filter(
            $result,
            function ($key) use ($allowedsorttypes) {
                return in_array($key, $allowedsorttypes);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    return $result;
}

/**
 * Получение всех вариантов сортировки (включая кастомные поля), настроенных для использования в категории
 *
 * @param int $categoryid
 * @return string[]|string[]|unknown
 */
function local_crw_get_all_allowed_sort_types($categoryid=null)
{
    // Сортировка курсов
    $result = local_crw_get_allowed_basic_sort_types();

    if (!is_null($categoryid))
    {
        $customcoursefields = get_config('local_crw', 'custom_course_fields');
        if (empty($customcoursefields))
        {
            return $result;
        }
        $parseresult = \otcomponent_customclass\utils::parse($customcoursefields);
        if (!$parseresult->is_form_exists())
        {
            return $result;
        }
        // Форма
        $customform = $parseresult->get_form();
        // Кастомные поля формы
        $cffields = $customform->get_fields();
        foreach($cffields as $fieldname => $cffield)
        {
            // Получение настройки из текущей категории
            $fieldrole = local_crw_get_category_config($categoryid, 'custom_field_'.$fieldname.'_role');

            // В текущей категории в настройке указано не отображать фильтр по данному полю
            // (либо поле целиком отключено даже для редактирования)
            if (in_array($fieldrole, ['search_disabled_sort_enabled', 'search_enabled_sort_enabled']))
            {
                $result['cff_'.$fieldname] = $cffield['custom']['sortlabel'] ?? $cffield['label'];
            }
        }
    }

    return $result;
}


function local_crw_get_default_sort_type($categoryid=null)
{
    // настроенный по умолчанию
    $sorttype = get_config('local_crw', 'course_sort_type');

    // все доступные в категории
    $allowed = local_crw_get_all_allowed_sort_types($categoryid);

    if ($sorttype === false || !in_array($sorttype, $allowed))
    {
        // сортировки по умолчанию нет или она недоступна в категории,
        // берем первое из списка доступных
        reset($allowed);
        $sorttype = key($allowed);
    }

    return $sorttype;
}