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

defined('MOODLE_INTERNAL') || die();

/**
 * Плагин поиска курсов. Класс субплагина.
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class crw_system_search extends local_crw_plugin
{
    protected $hints = null;
    protected $totalcount = null;
    protected $type = CRW_PLUGIN_TYPE_SEARCH;
    
    /**
     * Получение HTML-кода блока поиска курсов
     *
     * @param array $options - Дополнительные опции отображения
     *
     * @return string - HTML-код блока
     */
    public function display($options = array() )
    {
        global $CFG, $PAGE;
        
        // Подключение рендера плагина
        require_once($CFG->dirroot .'/local/crw/plugins/system_search/renderer.php');
        $renderer = new crw_system_search_renderer();
        
//         $PAGE->requires->css(new moodle_url('/local/crw/plugins/system_search/styles.css'));
        // Подключаем js-обработчик для переключения полного/краткого вида формы
        $PAGE->requires->js_call_amd('crw_system_search/fullsearch_toggler', 'init', []);
        // Подключаем js-обработчик для сброса формы поиска
        $PAGE->requires->js_call_amd('crw_system_search/reset_search_form', 'init', []);
        
        if ($this->get_config('settings_query_string_role', 'name') == 'hints')
        {
            // Подключаем js для обработки элемента формы, выдающего подсказки
            $PAGE->requires->js_call_amd('crw_system_search/hints_sugguestions', 'init', []);
        }
        
        $getblockoptions = $options;
        $getblockoptions['plugin'] = $this;
        $getblockoptions['return_process_result'] = true;
        
        list($processresult, $html) = $renderer->get_block(null, $getblockoptions);
        
        if (!empty($options['return_process_result']))
        {
            return [$processresult, $html];
        }
        return $html;
    }
        
    /**
     * Получить массив условий из поискового запроса с превалидацией
     *
     * @param string $query - поисковый запрос пользователя
     * @return array[]
     */
    public static function get_conditions($query)
    {
        $conditions = [];
        if (!empty($query))
        {// Передана строка поиска
            // Разбиваем строку поиска на части
            $queryparts = explode(';', $query);
            if (!empty($queryparts))
            {// Значения есть
                foreach ($queryparts as $conditionstring)
                {
                    $conditionarr = explode('=', $conditionstring, 2);
                    if (!empty($conditionarr) && count($conditionarr) == 2)
                    {
                        list ($field, $value) = $conditionarr;
                        $conditions[$field] = $value;
                    }
                    
                    if (!empty($conditionarr) && count($conditionarr) == 1)
                    {
                        list ($value) = $conditionarr;
                        if (trim($value) != '')
                        {
                            return ['sq' => $value];
                        }
                    }
                }
            }
        }
        return $conditions;
    }
    
    /**
     * Формирует строку запроса из массива условий фильтрации
     *
     * @param array $conditions
     *
     * @return string
     */
    public static function get_string_from_conditions($conditions)
    {
        $crws = [];
        foreach($conditions as $filter => $value)
        {
            $crws[] = $filter . '=' . $value;
        }
        return implode(';', $crws);
    }
    
    /**
     * Получить элемент sql кода для фильтрации курсов по параметрам поиска
     *
     * @param array $opt - Дополнительные опции
     *
     * @return array(string $sql, array $vars)
     *                  $sql - участок sql запроса
     *                  $vars - массив плейсхолдеров
     */
    public function get_sql_courses($opt = array())
    {
        global $CFG, $DB;
        
        require_once($CFG->dirroot .'/local/crw/lib.php');
        
        // Получаем GET параметр
        $query = '';
        if( ! empty($opt['crws']) )
        {
            $query = $opt['crws'];
        }
        
        $parameters = [];
        $sql = [];
        $sorttype = null;
    
        $conditions = self::get_conditions($query);
                
        if (!empty($conditions) && !array_key_exists('sq', $conditions))
        {
            foreach($conditions as $field => $value)
            {
                if (!empty($value))
                {
                    switch($field)
                    {
                        case 'mindate':
                            $sql[] = " c.startdate >= :mindate ";
                            $parameters['mindate'] = intval($value);
                            break;
                        case 'maxdate':
                            $sql[] = " c.startdate <= :maxdate ";
                            $parameters['maxdate'] = intval($value);
                            break;
                        case 'minprice':
                            if (is_numeric($value))
                            {
                                $sql[] = " c.id IN
                                                ( SELECT DISTINCT c.id
                                                  FROM {crw_course_properties} cprop RIGHT JOIN {course} c
                                                    ON cprop.courseid = c.id
                                                  WHERE ( cprop.name LIKE 'course_price' AND ABS(cprop.svalue) >= :minprice )
                                                 ) ";
                                $parameters['minprice'] = $value;
                            }
                            break;
                        case 'maxprice':
                            if (is_numeric($value))
                            {
                                $sql[] = " c.id IN
                                                ( SELECT DISTINCT c.id
                                                  FROM {crw_course_properties} cprop RIGHT JOIN {course} c
                                                    ON cprop.courseid = c.id
                                                  WHERE ( cprop.name LIKE 'course_price' AND ABS(cprop.svalue) <= :maxprice )
                                                ) ";
                                $parameters['maxprice'] = $value;
                            }
                            break;
                        case 'name':
                            $sql[] = " ( c.fullname LIKE :namef OR c.shortname LIKE :names ) ";
                            $parameters['namef'] = '%'.$value.'%';
                            $parameters['names'] = '%'.$value.'%';
                            break;
                        case 'tags':
                            // Массив курсов, соответствующих условиям
                            $suitecourses = [];
                            // Выбранные пользователем теги
                            $selectedtags = explode(',', $value);
                            // Теги, исключенные из поиска
                            $excludetags = explode(',', get_config('crw_system_search', 'settings_exclude_standard_tags'));
                            
                            foreach( $selectedtags as $selectedtag )
                            {
                                $taggedinstances = [];
                                $suitecourses[$selectedtag] = [];
                                
                                // Проверка на соответствие настроенным разрешениям
                                if( ! in_array($selectedtag, $excludetags))
                                {
                                    // Получение курсов, помеченных тегом курса
                                    $taggedinstances = array_merge(
                                        $taggedinstances,
                                        $this->get_tag_instances('core', 'course', $selectedtag)
                                    );
                                }
                                
                                // Получение курсов, помеченных тегом из коллекции 1
                                $taggedinstances = array_merge(
                                    $taggedinstances,
                                    $this->get_tag_instances('local_crw', 'crw_course_custom1', $selectedtag)
                                );
                                
                                // Получение курсов, помеченных тегом из коллекции 2
                                $taggedinstances = array_merge(
                                    $taggedinstances,
                                    $this->get_tag_instances('local_crw', 'crw_course_custom2', $selectedtag)
                                );
                                
                                
                                foreach( $taggedinstances as $taggedinstance )
                                {
                                    $suitecourses[$selectedtag][$taggedinstance->itemid] = $taggedinstance->itemid;
                                }
                            }
                            
                            
                            if (!empty($suitecourses))
                            {
                                $logiсconfig = get_config('crw_system_search', 'settings_tagfilter_logic');
                                $taggedcourses = null;
                                
                                foreach( $suitecourses as $courses )
                                {
                                    if (is_null($taggedcourses))
                                    {
                                        $taggedcourses = array_keys($courses);
                                    } else
                                    {
                                        if (!empty($logiсconfig))
                                        {
                                                $taggedcourses = array_intersect($taggedcourses, array_keys($courses));
                                        } else
                                        {
                                            $taggedcourses = array_merge($taggedcourses, array_keys($courses));
                                        }
                                    }
                                }
                                
                                if (!empty($taggedcourses))
                                {
                                    // Добавляем условия, чтобы у курса были все выбранные пользователем теги одновременно
                                    $sql[] = "  c.id IN ( ".implode(', ', $taggedcourses)." ) ";
                                } else
                                {
                                    $sql = [" 1=2 "];
                                    break 2;
                                }
                            } else
                            { // нет курсов, удовлетворяющих условию, необходимо обломить выборку
                                $sql = [" 1=2 "];
                                break 2;
                            }
                            break;
                        case 'coursecontact':
                            
                            $suitecourses = [];
                            
                            $coursecontacts =  explode(',', $value);
                            if( !empty($coursecontacts) )
                            {
                                // Все курсы, с предзагрузкой контактов курса
                                $courses = \core_course_category::get(0)->get_courses([
                                    'recursive' => true,
                                    'coursecontacts' => true
                                ]);
                                foreach($courses as $course)
                                {
                                    // Проверка курса на соответствие условиям
                                    
                                    // Получение контактов курса
                                    $ccs = $course->get_course_contacts();
                                    // Количество выполненных условий (должны быть выполнены все)
                                    $ccconditions = 0;
                                    $acceptedconditions = 0;
                                    foreach( $coursecontacts as $coursecontact )
                                    {
                                        list($roleid, $userid) = explode(':', $coursecontact);
                                        if ((!empty($ccs[$userid]) && $ccs[$userid]['role']->id == $roleid) ||
                                            $userid == 0)
                                        {
                                            $acceptedconditions++;
                                        }
                                        $ccconditions++;
                                    }
                                    if( $ccconditions == $acceptedconditions )
                                    {
                                        $suitecourses[] = $course->id;
                                    }
                                }
                            }
                            
                            if( ! empty($suitecourses) )
                            {
                                $sql[] = " c.id IN ( ".implode(', ',$suitecourses)." ) ";
                            } else
                            { // нет курсов, удовлетворяющих условию, необходимо обломить выборку
                                $sql = [" 1=2 "];
                                break 2;
                            }
                            break;
                            
                        case 'cid':
                            $redirect = get_config('crw_system_search', 'settings_single_result_redirect');
                            $ajax = get_config('crw_system_search', 'settings_ajax_search');
                            if (empty($ajax) && ($redirect=='id_specified' || $redirect == 'always'))
                            {
                                $crwcourseurl = new moodle_url('/local/crw/course.php', ['id' => $value]);
                                redirect($crwcourseurl);
                            }
                            $sql[] = " c.id = :cid ";
                            $parameters['cid'] = $value;
                            break;
                        case 'sorttype':
                            $categoryid = null;
                            if (!empty($opt['categoryid']))
                            {
                                $categoryid = $opt['categoryid'];
                            }
                            
                            if (array_key_exists($value, local_crw_get_all_allowed_sort_types($categoryid)))
                            {
                                $sorttype = $value;
                            }
                            break;
                        default:
                            break;
                    }
                    
                    
                }
            }
            
            // обработка кастомных полей для поиска
            $customcoursefields = get_config('local_crw', 'custom_course_fields');
            if (!empty($customcoursefields))
            {
                $result = \otcomponent_customclass\utils::parse($customcoursefields);
                if ( $result->is_form_exists() )
                {
                    // Форма
                    $customform = $result->get_form();
                    // Кастомные поля формы
                    $cffields = $customform->get_fields();
                    
                    $categoryid = null;
                    if (!empty($opt['categoryid']))
                    {
                        $categoryid = $opt['categoryid'];
                    }
                    
                    foreach($cffields as $fieldname => $cffield)
                    {
                        if (isset($conditions['cff_'.$fieldname]))
                        {
                            // Получение настройки роли поля на уровне категории
                            $fieldrole = local_crw_get_category_config(
                                $categoryid,
                                'custom_field_'.$fieldname.'_role'
                            );
                            
                            if ($fieldrole == 'inherit' || $fieldrole === false)
                            {
                                // Получение настройки плагина
                                if (get_config('crw_system_search', 'settings_filter_customfield__'.$fieldname) == false)
                                {
                                    // В настройке плагина указано не отображать фильтр по данному полю
                                    continue;
                                }
                            }

                            if (in_array($fieldrole, ['field_disabled', 'search_disabled', 'search_disabled_sort_enabled']))
                            {
                                // Поле не доступно для поиска
                                continue;
                            }
                            
                            $type = $cffield['type'];
                            
                            if ($type == 'select' && array_key_exists('multiple', $cffield) &&
                                $cffield['multiple'] == 'multiple')
                            {
                                $type = 'multiple_select';
                            }
                            
                            switch ($type)
                            {
                                case 'text':
                                case 'textarea':
                                case 'multiple_select':
                                    if ($conditions['cff_'.$fieldname] != '')
                                    {
                                        $condition = $DB->sql_like('cprop.svalue', ':cff_'.$fieldname);
                                        $sql[] = " c.id IN
                                            ( SELECT DISTINCT c.id
                                              FROM {crw_course_properties} cprop RIGHT JOIN {course} c
                                                ON cprop.courseid = c.id
                                              WHERE ( cprop.name = 'cff_".$fieldname."' AND ".$condition." )
                                             ) ";
                                        $parameters['cff_'.$fieldname] = '%'.$conditions['cff_'.$fieldname].'%';
                                    }
                                    break;
                                case 'checkbox':
                                case 'select':
                                    $condition = 'cprop.svalue=:cff_'.$fieldname;
                                    $sql[] = " c.id IN
                                            ( SELECT DISTINCT c.id
                                              FROM {crw_course_properties} cprop RIGHT JOIN {course} c
                                                ON cprop.courseid = c.id
                                              WHERE ( cprop.name = 'cff_".$fieldname."' AND ".$condition." )
                                             ) ";
                                    $parameters['cff_'.$fieldname] = $conditions['cff_'.$fieldname];
                                    break;
                                default:
                                    break;
                                    
                            }
                        }
                    }
                }
            }
            
        } elseif (array_key_exists('sq', $conditions))
        {
            $this->load_hints($conditions['sq']);
            $sql = [" 1=2 "];
        }
        
        return [implode(' AND ', $sql), $parameters, $sorttype];
    }
    
    public function load_hints($query)
    {
        if (is_null($this->hints) && is_null($this->totalcount) && !empty($query))
        {
            $page = optional_param('page', 0, PARAM_INT);
            $perpage = \core_search\manager::DISPLAY_RESULTS_PER_PAGE;
            list($this->hints, $this->totalcount) = $this->get_hints($query, $perpage, $page * $perpage);
        }
        return [$this->hints, $this->totalcount];
    }
    
    /**
     * Получить подсказки для результатов поиска
     *
     * @param string $query - запрос, введенный в форму поиска
     * @param int $limit - ограничение на количество подсказок (0 - без ограничений)
     * @return array|array - массив подсказок и общее количество
     */
    public function get_hints($query, $limit=5, $offset=0)
    {
        // результирующий массив
        $hints = [];
        $totalcount = 0;
        
        if ($query == '')
        {
            return [$hints, $totalcount];
        }
        
        
        
        $incoursecontacts = get_config('crw_system_search', 'hints_settings_area_coursecontacts');
        if (!isset($incoursecontacts) || !empty($incoursecontacts))
        {// если указано искать среди контактов курса или настройка вообще не существует (по умолчанию - да)
            list($coursecontacts, $ccroles) = $this->get_crw_courses_contacts();
            // отфильтруем контакты курса по запросу
            $coursecontacts = array_filter($coursecontacts, function($value) use ($query) {
                return strpos(mb_strtolower($value), mb_strtolower($query)) !== false;
            });
            foreach($coursecontacts as $userid => $username)
            {
                foreach($ccroles[$userid] as $roleid => $rolename)
                {
                    $totalcount ++;
                    if ($limit == 0 || ($limit + $offset - count($hints)) > 0)
                    {
                        $hints[] = [
                            'hintarea' => get_string('hintarea:course_contacts', 'crw_system_search', $rolename),
                            'hintsubarea' => get_string('hintsubarea:course_contacts', 'crw_system_search', $rolename),
                            'hintvalue' => 'coursecontact=' . $roleid . ':' . $userid,
                            'hintleft' => '',
                            'hintcenter' => '',
                            'hintright' => '',
                            'doctitle' => $username
                        ];
                    }
                }
            }
        }
        
        
        
        $incoursetags = get_config('crw_system_search', 'hints_settings_area_tags');
        if (!isset($incoursetags) || !empty($incoursetags))
        {// если указано искать среди тегов курса или настройка вообще не существует (по умолчанию - да)
            $coursetags = $this->get_collection_tags('core', 'course');
            // отфильтруем теги курса по запросу
            $coursetags = array_filter($coursetags, function($value) use ($query) {
                return strpos(mb_strtolower($value), mb_strtolower($query)) !== false;
            });
            foreach($coursetags as $tagid=>$tag)
            {
                $totalcount ++;
                if ($limit == 0 || ($limit + $offset - count($hints)) > 0)
                {
                    $hints[] = [
                        'hintarea' => get_string('hintarea:course_tags','crw_system_search'),
                        'hintsubarea' => get_string('hintsubarea:course_tags','crw_system_search'),
                        'hintvalue' => 'tags='.$tagid,
                        'hintleft' => '',
                        'hintcenter' => '',
                        'hintright' => '',
                        'doctitle' => $tag
                    ];
                }
            }
        }
        
        
        $incustom1tags = get_config('crw_system_search', 'hints_settings_area_tagcollection_custom1');
        if (!isset($incustom1tags) || !empty($incustom1tags))
        {// если указано искать среди тегов из коллекции 1 или настройка вообще не существует (по умолчанию - да)
            $custom1tags = $this->get_collection_tags('local_crw', 'crw_course_custom1');
            // отфильтруем теги курса по запросу
            $custom1tags = array_filter($custom1tags, function($value) use ($query) {
                return strpos(mb_strtolower($value), mb_strtolower($query)) !== false;
            });
            foreach($custom1tags as $tagid=>$tag)
            {
                $totalcount ++;
                if ($limit == 0 || ($limit + $offset - count($hints)) > 0)
                {
                    $hints[] = [
                        'hintarea' => get_string('hintarea:course_tagcollection_custom1','crw_system_search'),
                        'hintsubarea' => get_string('hintsubarea:course_tagcollection_custom1','crw_system_search'),
                        'hintvalue' => 'tags='.$tagid,
                        'hintleft' => '',
                        'hintcenter' => '',
                        'hintright' => '',
                        'doctitle' => $tag
                    ];
                }
            }
        }
        
        $incustom2tags = get_config('crw_system_search', 'hints_settings_area_tagcollection_custom2');
        if (!isset($incustom2tags) || !empty($incustom2tags))
        {// если указано искать среди тегов из коллекции 2 или настройка вообще не существует (по умолчанию - да)
            $custom2tags = $this->get_collection_tags('local_crw', 'crw_course_custom2');
            // отфильтруем теги курса по запросу
            $custom2tags = array_filter($custom2tags, function($value) use ($query) {
                return strpos(mb_strtolower($value), mb_strtolower($query)) !== false;
            });
            foreach($custom2tags as $tagid=>$tag)
            {
                $totalcount ++;
                if ($limit == 0 || ($limit + $offset - count($hints)) > 0)
                {
                    $hints[] = [
                        'hintarea' => get_string('hintarea:course_tagcollection_custom2','crw_system_search'),
                        'hintsubarea' => get_string('hintsubarea:course_tagcollection_custom2','crw_system_search'),
                        'hintvalue' => 'tags='.$tagid,
                        'hintleft' => '',
                        'hintcenter' => '',
                        'hintright' => '',
                        'doctitle' => $tag
                    ];
                }
            }
        }
        
        $searchmanager = \core_search\manager::instance();
        $gshints = [];
        $gslimit = $limit + $offset - count($hints);
        $gslimit = $gslimit > $searchmanager::MAX_RESULTS ? $searchmanager::MAX_RESULTS : $gshints;
        
        // поиск документов по запросу
        $docs = $this->crw_global_search_by_query(
            $searchmanager,
            $this->prepare_global_query($query, $searchmanager->get_engine()->get_plugin_name()),
            $this->get_search_areas(),
            ($limit == 0 ? 0 : ($gslimit > 0 ? $gslimit : 1))
        );
        
        $gstotalcount = $searchmanager->get_engine()->get_query_total_count();
        $totalcount += count($docs) < $gslimit ? count($docs) : min($gstotalcount, static::MAX_RESULTS);
        
        if ($limit == 0 || ($limit + $offset - count($hints)) > 0)
        {
            // формирование подсказок из найденных документов
            $gshints = $this->make_hints_from_found_docs($docs);
        }
        
        $slice = array_slice(array_merge($hints, $gshints), $offset, $limit);
        
        return [$slice, $totalcount];
    }
    
    /**
     * Формирование запроса на основе фразы, введенной пользователем
     *
     * @param string $query - запрос, введенный пользователем
     * @return string
     */
    protected function prepare_global_query($query, $engine='search_simpledb')
    {
        switch ($engine)
        {
            case 'search_solr':
                $qws = explode(' ', $query);
                // перед каждым словом ставим +, что гарантирует его наличие в результате,
                // а после каждого слова длиннее двух символов ставим ~, что позволяет результату иметь небольшие расхождения с оригинальным запросом
                foreach($qws as $i=>$qw)
                {
                    $qwlen = mb_strlen($qw);
                    $qws[$i] = ($qwlen > 0 ? '+' : '') . $qws[$i] . ($qwlen > 2 && $i<(count($qws)-1)? '~' : '');
                }
                // если после последнего слова не стоял пробел, считаем что его ввод не завершен и ставим после него *
                $gsphrase = implode(' ', $qws) . ($qw == '' ? '' : '*');
                
                // ограничиваем поля для поиска (так не будет искать в title, а название курса у нас есть в description2)
                // + есть возможность повысить приоритет для description2, добавив после скобок ^2. Но результаты не понравились
                $gsquery = 'description2:('.$gsphrase.') content:('.$gsphrase.') description1:('.$gsphrase.')';
                break;
            default:
                $gsquery = $query;
                break;
        }
        
        return $gsquery;
    }
    
    /**
     * Получить теги из коллекции по компоненту и типу тегируемого элемента
     *
     * @param string $component - компонент
     * @param string $itemtype - тип тегируемого элемента
     * @return array|mixed[] - массив тегов
     */
    public function get_collection_tags($component, $itemtype)
    {
        global $DB;
        $collectionid = \core_tag_area::get_collection($component, $itemtype);
        $conditions = [
            'isstandard' => 1,
            'tagcollid' => $collectionid
        ];
        $tags = $DB->get_records_menu('tag', $conditions, 'name', 'id, name');
        return $tags;
        
    }
    
    /**
     * Получить экземпляры назначенных тегов
     *
     * @param string $component - компонент
     * @param string $itemtype - тип тегируемого элемента
     * @param int $tagid - идентификатор тега
     * @return array - массив экземпляров тегов
     */
    public function get_tag_instances($component, $itemtype, $tagid)
    {
        global $DB;
        return $DB->get_records('tag_instance', [
            'itemtype' => $itemtype,
            'component' => $component,
            'tagid' => $tagid
        ]);
    }
    
    /**
     * Сформировать данные (подсказки) из найденных результатов (документов)
     *
     * @param object $docs
     * @return array - массив подсказок (массивов с данными найденных результатов)
     */
    public function make_hints_from_found_docs($docs)
    {
        $hints = [];
        
        
        foreach($docs as $doc)
        {
            $foundtext = $doc->docdata['description2'] . ' ' . PHP_EOL . $doc->docdata['content']  . ' ' . PHP_EOL . $doc->docdata['description1'];
            
            // найдем лучшую подсказку
            $foundhint = $this->get_highlight_from_found_text($foundtext, 1, 2);
            if (!empty($foundhint))
            {
                list($beforewords, $highlighted, $afterwords, $highlightedwords) = $foundhint;
            } else
            {
                $highlighted = '';
                $beforewords = '';
                $afterwords = '';
                $highlightedwords = 0;
            }
            
            $hints[] = [
                'hintarea' => get_string('hintarea:gsa_'.$doc->docdata['areaname'],'crw_system_search'),
                'hintsubarea' => get_string('hintsubarea:gsa_'.$doc->docdata['areaname'],'crw_system_search'),
                'hintvalue' => 'cid='.$doc->get('courseid'),
                'hintleft' => $beforewords,
                'hintcenter' => $highlighted,
                'hintright' => $afterwords,
                'doctitle' => strip_tags($doc->docdata['title']),
                'hightlightedwords' => $highlightedwords,
                'courseid' => $doc->get('courseid')
            ];
        }
        
        // отсортируем подсказки так, чтобы сверху оказались с наибольшим количеством подсвеченных слов
        usort($hints, function ($item1, $item2) {
            if ($item1['hightlightedwords'] == $item2['hightlightedwords']) return 0;
            return $item1['hightlightedwords'] > $item2['hightlightedwords'] ? -1 : 1;
        });
        
        return $hints;
    }
    
    /**
     * Получить области поиска, настроенные в витрине и доступные для поиска
     *
     * @return string[]
     */
    protected function get_search_areas()
    {
        $searchareas = [];
        $incourseinfo = get_config('crw_system_search', 'hints_settings_area_gs_crw_course');
        if (!isset($incourseinfo) || !empty($incourseinfo))
        {// если указано искать в информации о курсе или настройка вообще не существует (по умолчанию - да)
            $searchareas[] = 'crw_system_search-crw_course';
        }
        
        $incoursecontacts = get_config('crw_system_search', 'hints_settings_area_gs_crw_course_contacts');
        if (!isset($incoursecontacts) || !empty($incoursecontacts))
        {// если указано искать в контактах курса или настройка вообще не существует (по умолчанию - да)
            $searchareas[] = 'crw_system_search-crw_course_contacts';
        }
        
        $incoursetags = get_config('crw_system_search', 'hints_settings_area_gs_crw_course_tags');
        if (!isset($incoursetags) || !empty($incoursetags))
        {// если указано искать в тегах курса или настройка вообще не существует (по умолчанию - да)
            $searchareas[] = 'crw_system_search-crw_course_tags';
        }
        
        $incoursetagcollectioncustom1 = get_config('crw_system_search', 'hints_settings_area_gs_crw_course_tagcollection_custom1');
        if (!isset($incoursetagcollectioncustom1) || !empty($incoursetagcollectioncustom1))
        {// если указано искать в коллекции1 тегов курса или настройка вообще не существует (по умолчанию - да)
            $searchareas[] = 'crw_system_search-crw_course_tagcollection_custom1';
        }
        
        $incoursetagcollectioncustom2 = get_config('crw_system_search', 'hints_settings_area_gs_crw_course_tagcollection_custom2');
        if (!isset($incoursetagcollectioncustom2) || !empty($incoursetagcollectioncustom2))
        {// если указано искать в коллекции2 тегов курса или настройка вообще не существует (по умолчанию - да)
            $searchareas[] = 'crw_system_search-crw_course_tagcollection_custom2';
        }
        
        $searchmanager = \core_search\manager::instance();
        
        foreach($searchareas as $k=>$searcharea)
        {
            $sa = $searchmanager->get_search_area($searcharea);
            if (!$sa->is_enabled())
            {// область поиска отключена в настройках глобального поиска
                unset($searchareas[$k]);
            }
        }
        
        return $searchareas;
    }
    
    /**
     * Выполнить глобальный поиск с указанными параметрами
     *
     * @param string $query - пользовательский запрос поиска
     * @param array $searchareas - области поиска
     * @return array|\core_search\document[]
     */
    public function crw_global_search_by_query($searchmanager, $query, $searchareas, $limit=0)
    {
        global $PAGE, $CFG;
        require_once("$CFG->dirroot/course/lib.php");
        
        $docs = [];
        
        if (!isset($PAGE->context))
        {
            $PAGE->set_context(\context_system::instance());
        }
        $renderer = $PAGE->get_renderer('core_search');
        
        if (!empty($searchareas))
        {
            // формирование подсказок из результатов глобального поиска
            $searchdata = new \stdClass();
            $searchdata->q =$query;
            $searchdata->areaids = $searchareas;
            $docs = $searchmanager->search($searchdata, $limit);
            foreach ($docs as $k=>$doc)
            {
                $docdata = $doc->export_for_template($renderer);
                $docs[$k]->docdata = $docdata;
            }
        }
        
        return $docs;
    }
    
        
    /**
     * Выцепляет из найденного текста релевантный кусок с пользовательским запросом и окружающими словами
     *
     * @param string $text - строка с подсвеченными результатами поиска
     * @param number $addleft - количество слов, которые было бы хорошо добавить слева от подсвеченной фразы, если они связаны
     * @param number $addright - количество слов, которые было бы хорошо добавить справа от подсвеченной фразы, если они связаны
     * @return array - массив содержащий слова слева, подсвеченный текст, слова справа и количество подсвеченных слов
     */
    protected function get_highlight_from_found_text($text, $addleft=0, $addright=0)
    {
        $hints = [];
        
        $maxwords = 0;
        $besthintkey = 0;
        
        $hs = '<span class="highlight">';
        $he = '<\/span>';
        
        // базовая регулярка, ищет подсвеченный кусок текста
        $regex = $hs.'([\S\s]*?)'.$he;
        // требуется добавить все, что в тексте слева
        if (is_null($addleft))
        {
            // добавляем группу, в которую должно войти все, что слева
            $regex = '([\S\s]*)?'.$regex;
            // она одна, ее одну и будем искать в результатах
            $addleft = 1;
        } else
        {
            // добавляем нужное количество групп для поиска отдельных слов
            // в регулярке - хитрость, которая не берет слово слева, если оно было в предыдущем предложении (.?!)
            // или части предложения (,-:), не связанной с искомой фразой
            for ($i=0; $i<(int)$addleft; $i++)
            {
                $regex = '((?:(?![.?!])\S(?![\r\n]))*)(?:[\s])?'.$regex;
            }
        }
        
        // требуется добавить все, что в тексте справа
        if (is_null($addright))
        {
            // добавляем группу, в которую должно войти все, что справа
            $regex = $regex.'([\S\s]*)?';
            // она одна, поэтому и пометим,что искать надо в результатах одну
            $addright = 1;
        } else
        {
            // добавляем нужное количество групп для поиска отдельных слов
            // в регулярке - хитрость, которая не берет следующее слово, если оно уже в следующем предложении (.?!)
            // или части предложения (,-:), не связанной с искомой фразой
            for ($i=0; $i<(int)$addright; $i++)
            {
                $regex .= '(?:[\s])?((?:(?![.?!])\S(?![\r\n]))*)';
            }
        }
        
        $matches = [];
        $found = preg_match_all('/'.$regex.'/', $text, $matches);
        
        
        for($i=0; $i<$found; $i++)
        {
            // формирование требуемого количества совпадений перед искомым материалом
            $beforewords = [];
            for($w=1; $w<($addleft+1); $w++)
            {
                if (isset($matches[$w][$i]))
                {
                    $beforewords[] = strip_tags($matches[$w][$i]);
                }
            }
            $beforewords = trim(implode(' ',$beforewords));
            
            // порядковый номер совпадения, в котором содержится искомый материал
            $highlightednum = ($addleft+1);
            $highlighted = '';
            // само подсвеченное слово
            if (!empty($matches[$highlightednum][$i]))
            {
                $highlighted = strip_tags($matches[$highlightednum][$i]);
            }
            
            // формирование требуемого количества совпадений после искомого материала
            $afterwords = [];
            for($w=($highlightednum+1); $w<($addright+$highlightednum+1); $w++)
            {
                if (isset($matches[$w][$i]))
                {
                    $afterwords[] = strip_tags($matches[$w][$i]);
                }
            }
            $afterwords = trim(implode(' ',$afterwords));
            
            
            if (trim($highlighted) != '')
            {
                // количество найденных слов в результате
                $highlightedwords = substr_count(trim($highlighted), ' ')+1;
                
                $hints[] = [
                    empty($beforewords)?'':$beforewords.' ',
                    $highlighted,
                    empty($afterwords)?'':' '.$afterwords,
                    $highlightedwords
                ];
                
                // фиксация лучшего результата
                if ($highlightedwords > $maxwords)
                {
                    $maxwords = $highlightedwords;
                    $besthintkey = count($hints)-1;
                }
            }
        }
        
        if (isset($hints[$besthintkey]))
        {
            return $hints[$besthintkey];
        } else
        {
            return [];
        }
    }
    
    /**
     * Получение всех возможных контактов курсов
     *
     * @return array[] контакты курсов
     */
    public function get_crw_courses_contacts()
    {
        global $CFG, $DB;
        
        $coursecontacts = [];
        $ccroles = [];
            
        if (!empty($CFG->coursecontact))
        {
            if ($courses = $DB->get_records('course', null, '', 'id')) {
                \core_course_category::preload_course_contacts($courses);
                foreach($courses as $course)
                {
                    $context = context_course::instance($course->id);
                    foreach($course->managers as $manager)
                    {
                        if (!array_key_exists($manager->id, $coursecontacts))
                        {
                            $coursecontacts[$manager->id] = fullname($manager);
                        }
                        if (!array_key_exists($manager->id, $ccroles))
                        {
                            $ccroles[$manager->id] = [];
                        }
                        if (!array_key_exists($manager->roleid, $ccroles[$manager->id]))
                        {
                            $role = new stdClass();
                            $role->id = $manager->roleid;
                            $role->name = $manager->rolename;
                            $role->shortname = $manager->roleshortname;
                            $role->coursealias = $manager->rolecoursealias;
                            $ccroles[$manager->id][$manager->roleid] = role_get_name($role, $context, ROLENAME_ORIGINAL);
                        }
                    }
                }
            }
        }

        return [$coursecontacts, $ccroles];
    }
}


/**
 * Получение формы с фильтрацией в качестве фрагмента
 *
 * @param array $args Список именованных аргументов для загрузчика фрагмента
 * @return string
 */
function crw_system_search_output_fragment_search($args)
{
    
    global $PAGE, $CFG;
    require_once($CFG->dirroot . '/local/crw/lib.php');
    
    $PAGE->set_context(context_system::instance());
    
    // сериализация формы
    $formdata = [];
    if (!empty($args['jsonformdata'])) {
        $serialiseddata = json_decode($args['jsonformdata']);
        parse_str($serialiseddata, $formdata);
    }
    
    $showcaseopts = [
        'ajax' => true,
        'ajaxformdata' => $formdata
    ];
    if (!empty($args['categoryid'])) {
        $showcaseopts['cid'] = $args['categoryid'];
    }
    
    // Получаем плагин витрины
    $showcase = new local_crw($showcaseopts);
    
    // Отобразить витрину курсов
    return $showcase->display_showcase([
        'return_html' => true,
        'no-wrapper' => true,
        // для случаев, когда нужна витрина без враппера, нам надо передавать идентификатор витрины, чтобы js жил
        'splobjecthash' => ($args['splobjecthash'] ?? null)
    ]);
}

function crw_system_search_is_custom_field_searchable($cffield)
{
    // проверенные в поиске типы полей
    $searchabletypes = ['text', 'textarea', 'select', 'checkbox'];
    
    return in_array($cffield['type'], $searchabletypes);
}