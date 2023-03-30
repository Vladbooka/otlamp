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
 * Плагин поиска курсов.
 * Класс формы поиска
 *
 * @package local
 * @subpackage crw
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
// Подклчим библиотеки
require_once ($CFG->dirroot . '/local/crw/lib.php');
require_once ($CFG->libdir . '/formslib.php');
require_once ($CFG->libdir . '/completionlib.php');

class crw_search_form extends moodleform
{

    public $crws;
    public $srr;

    /**
     * Объявление формы
     */
    function definition()
    {
        global $CFG, $PAGE, $OUTPUT;
        
        $plugin = $this->_customdata->plugin;
        
        // Способ отображения формы
        $style = $plugin->get_config('settings_style', 'default');
        $minimalism = $style == 'minimalism';
        
        $bscolstyle = '';
        
        // Получим данные
        $mform = $this->_form;
        $attrs = $mform->getAttributes();
        $attrs['class'] = trim(($attrs['class'] ?? ''));
        $attrs['class'] .= ' crw_system_search_form';
        if ($minimalism)
        {
            $bscolstyle = 'col-xl-3 col-lg-4 col-sm-6 align-content-end';
            $attrs['class'] .= ' row';
        }
        
        $ajaxfilter = ($this->_customdata->ajaxfilter ?? false);
        $attrs['data-ajax-filter'] = (string)(int)$ajaxfilter;
        $mform->setAttributes($attrs);
        
        $this->crws = $this->srr = '';
        if( ! empty($this->_customdata->crws) )
        {
            $this->crws = $this->_customdata->crws;
        }
        if( ! empty($this->_customdata->srr) )
        {
            $this->srr = $this->_customdata->srr;
        }
        
        $mform->addElement('hidden', 'crws', $this->crws);
        $mform->setType('crws', PARAM_RAW);
        $mform->addElement('hidden', 'srr', $this->srr);
        $mform->setType('srr', PARAM_TEXT);
        $mform->addElement('hidden', 'cid', $this->_customdata->categoryid ?? 0);
        $mform->setType('cid', PARAM_INT);
        

        // включено отображение описания формы
        $querystringrole = $plugin->get_config('settings_query_string_role');
        // включено отображение описания формы
        $formdescription = $plugin->get_config('settings_formdescription');
        // Поиск по дате начала курса
        $datefilter = $plugin->get_config('settings_displayfilter_datestart');
        // Поиск по стоимости курса
        $costfilter = $plugin->get_config('settings_displayfilter_cost');
        // Поиск по контактам курса
        $ccfilter = $plugin->get_config('settings_displayfilter_coursecontacts');
        // Поиск по тегам курса
        $tagsfilter = $plugin->get_config('settings_displayfilter_tags');
        // Поиск по настраиваемым полям
        $customfieldsfilters = $this->create_custom_fields_filters($mform, $style, $this->_customdata->categoryid ?? 0);
        // Сортировка
        $sorter = $plugin->get_config('settings_display_sorter');
        
        
        
        $top = array();
        
        if ($querystringrole == 'hints')
        {// настроено использование глобального поиска с подсказками
            $autocompleteoptions = array(
                'ajax' => 'crw_system_search/search_hints',
                'tags' => true
            );
            $top[] = $mform->createElement('autocomplete', 'crws', null, [], $autocompleteoptions);
        } elseif ($querystringrole == 'name')
        {// отображаем старый добрый поиск по названию
            $filtername = $mform->createElement('text', 'name', null, [
                'placeholder' => get_string('searchform_name', 'local_crw'),
                'data-default' => ''
            ]);
            if ($minimalism)
            {
                $mform->updateElementAttr([$filtername], [
                    'placeholder' => get_string('filter_name_placeholder', 'crw_system_search')
                ]);
            }
            $top[] = $filtername;
            $top[] = $mform->createElement('submit', 'magnifier', '',
                [
                    'class' => 'crw_system_search_form_submitbutton submit_magnifier'
                ]
                );
        }
        
        if (!$minimalism)
        {
            $top[] = $mform->createElement('submit', 'submitbutton',
                get_string('searchform_search', 'local_crw'),
                [
                    'class' => 'crw_system_search_form_submitbutton'
                ]
            );
        }

        $fullsearchonly = $plugin->get_config('settings_fullsearch_only');
        if( empty($fullsearchonly) && (!empty($formdescription) || !empty($datefilter) || !empty($costfilter) ||
            !empty($ccfilter) || !empty($tagsfilter) || !empty($customfieldsfilters)))
        {// есть включенные фильтры, по которым возможно осуществить поиск
            // и отображение кнопки требуется (не включено перманентное отображение расширенного фильтра)

            $top[] = $mform->createElement('button', 'morelink',
                get_string('searchform_more', 'local_crw'),
                [
                    'class' => 'crw_system_search_form_morelink'
                ]
            );
        }
        
        $hidereset = $plugin->get_config('settings_hide_reset_button');
        if (empty($hidereset))
        {
            $top[] = $mform->createElement('reset', 'resetbutton',
                get_string('searchform_reset', 'crw_system_search'), [
                    'class' => 'crw_system_search_form_resetbutton btn btn-secondary'
                ]);
        }
        
        if (!empty($top))
        {
            $mform->addGroup($top, 'topblock', '', '');
            $mform->updateElementAttr('topblock', [
                'class' => $bscolstyle
            ]);
            $mform->setType('topblock[name]', PARAM_TEXT);
        }
        
        if ($minimalism && (empty($hidereset) || empty($fullsearchonly)))
        {
            $mform->addElement('html', html_writer::div('', 'crw_system_search_flex_filler'));
        }
        
        if( ! empty($formdescription) )
        {// включено отображение описания формы
            $mform->addElement(
                'html',
                html_writer::div(
                    html_writer::div($formdescription),
                    'crw_system_search_description',
                    [ 'id' => 'crw_system_search_description',
                        'class' => $minimalism ? 'col-sm-12' : '']
                    )
                );
        }
        

        // Поиск по дате начала курса
        $time = time();
        if(! empty($datefilter) )
        {
            $mindate = $mform->createElement(
                'date_selector',
                'mindate',
                '',
                ['optional' => true],
                [
                    'class' => 'crw_system_search_form_mindate',
                    'data-default' => $time
                ]
            );
            $dateseparator = $mform->createElement(
                'static',
                'searchform_dategroup_to',
                '',
                '<span class="crw_system_search_form_dategroup_separator">&mdash;</span>'
            );
            $maxdate = $mform->createElement(
                'date_selector',
                'maxdate',
                '',
                ['optional' => true],
                [
                    'class' => 'crw_system_search_form_maxdate',
                    'data-default' => $time
                ]
            );
            
            if($minimalism)
            {
                $mindate->_separator = '';
                $maxdate->_separator = '';
            }
            $cctitle = $mform->createElement(
                'static',
                'crw_system_search_form_title',
                '',
                html_writer::div(
                    '',
                    $minimalism ? 'fake-block' : 'crw_system_search_form_title'
                    )
                );
            
            $mform->addGroup(
                [$cctitle, $mindate, $dateseparator, $maxdate],
                'bottomdate',
                get_string('searchform_dategroup', 'crw_system_search'),
                ''
            );
            $mform->updateElementAttr('bottomdate', [
                'class' => $minimalism ? 'col-xl-6 col-lg-8 col-sm-12 align-content-end' : ''
            ]);
        }
        

        // Поиск по стоимости курса
        if(! empty($costfilter) )
        {
            $minpriceplaceholder = $maxpriceplaceholder = get_string('searchform_sum', 'local_crw');
            if ($minimalism)
            {
                $minpriceplaceholder = get_string('filter_minprice_placeholder', 'crw_system_search');
                $maxpriceplaceholder = get_string('filter_maxprice_placeholder', 'crw_system_search');
            }
            
            $minprice = $mform->createElement('text', 'minprice', '',
                [
                    'placeholder' => $minpriceplaceholder,
                    'class' => 'crw_system_search_form_minprice ' . ($minimalism ? 'col ' : ''),
                    'data-default' => ''
                ]);
            $mform->setType('bottomprice[minprice]', PARAM_RAW);
            
            $priceseparator = $mform->createElement('static', 'searchform_pricegroup_to', '',
                '<span class="crw_system_search_form_pricegroup_separator col-1">&mdash;</span>');
            
            $maxprice = $mform->createElement('text', 'maxprice', '',
                [
                    'placeholder' => $maxpriceplaceholder,
                    'class' => 'crw_system_search_form_maxprice ' . ($minimalism ? 'col ' : ''),
                    'data-default' => ''
                ]);
            
            $mform->setType('bottomprice[maxprice]', PARAM_RAW);
            $cctitle = $mform->createElement(
                'static',
                'searchform_sum_title',
                '',
                html_writer::div(
                    '',
                    $minimalism ? 'fake-block' : 'searchform_sum_title'
                    )
                );
            
            $mform->addGroup([$cctitle, $minprice, $priceseparator, $maxprice], 'bottomprice',
                get_string('searchform_pricegroup', 'crw_system_search'), '');
            $mform->updateElementAttr('bottomprice', [
                'class' => $bscolstyle
            ]);
        }
        

        // Поиск по контактам курса
        if(! empty($ccfilter) )
        {
            // Все курсы, с предзагрузкой контактов курса
            $courses = \core_course_category::get(0)->get_courses([
                'recursive' => true,
                'coursecontacts' => true
            ]);
            // формирование массива пользователей, являющихся контактами курсов, сгруппированных по ролям
            $ccroles = [];
            foreach($courses as $course)
            {
                $context = context_course::instance($course->id);
                $ccs = $course->get_course_contacts();
                foreach($ccs as $userid => $cc)
                {
                    $ccroleid = $cc['role']->id;
                    if( empty($ccroles[$ccroleid]) )
                    {
                        $cc['rolename_original'] = role_get_name($cc['role'], $context, ROLENAME_ORIGINAL);
                        $ccroles[$ccroleid] = $cc;
                        if ($minimalism)
                        {
                            $any = $cc['rolename_original'];
                        } else
                        {
                            $any = get_string('searchform_coursecontact_any', 'crw_system_search');
                        }
                        $ccroles[$ccroleid]['users_select'] = [
                            0 => $any
                        ];
                    }
                    if( empty($ccroles[$ccroleid]['users_select'][$userid]) )
                    {
                        $ccroles[$ccroleid]['users_select'][$userid] = $cc['username'];
                    }
                }
            }
            $ccgroupnames = [];
            foreach( $ccroles as $ccroleid => $ccroledata )
            {// Для каждой роли контакта курса, у которой есть назначение пользователя в курсе
                $cctitle = $mform->createElement(
                    'static',
                    'searchform_coursecontact['.$ccroleid.']_title',
                    '',
                    html_writer::div(
                        ($minimalism ? '' : $ccroledata['rolename_original']),
                        $minimalism ? 'fake-block' : 'searchform_coursecontact_title'
                    )
                );
                // Добавляем выпадающий список
                $ccselect = $mform->createElement(
                    'select',
                    'searchform_coursecontact['.$ccroleid.']',
                    $ccroledata['rolename'],
                    $ccroledata['users_select'],
                    [
                        'class' => 'searchform_coursecontact',
                        'data-default' => 0
                    ]
                );
                $ccgroupname = 'searchform_coursecontact_group['.$ccroleid.']';
                $mform->addGroup(
                    [$cctitle, $ccselect],
                    $ccgroupname,
                    get_string('searchform_coursecontact_filter_title', 'crw_system_search'),
                    '',
                    false);
                $mform->updateElementAttr($ccgroupname, [
                    'class' => $bscolstyle
                ]);
                $ccgroupnames[] = $ccgroupname;
            }
            if( ! empty($ccgroupnames) )
            {
                $mform->updateElementAttr($ccgroupnames, [
                    'class' => 'searchform_coursecontact_group ' . $bscolstyle
                ]);
                $mform->updateElementAttr([array_shift($ccgroupnames)], [
                    'class' => 'searchform_coursecontact_group searchform_coursecontact_group_first ' . $bscolstyle
                ]);
            }
        }

        // Поиск по тегам курса
        if(! empty($tagsfilter) )
        {
            $choices = [];
            $excludetags = explode(',', $plugin->get_config('settings_exclude_standard_tags'));
    
            $coursecollection = core_tag_area::get_collection('core', 'course');
            $tagcloud = core_tag_collection::get_tag_cloud($coursecollection, true);
            $tags = $tagcloud->export_for_template($OUTPUT)->tags;
            foreach($tags as $tag)
            {
                $tagrecord = core_tag_tag::get_by_name($coursecollection, $tag->name);
                if( ! empty($tagrecord) && ! in_array($tagrecord->id, $excludetags) )
                {
                    $choices[$tagrecord->id] =$tagrecord->get_display_name(true);
                }
            }
            
            if ( ! empty($choices) )
            {
                $tagsattrs = ['multiple' => 'multiple'];
                if ($minimalism)
                {
                    $tagsattrs['placeholder'] = get_string('filter_tags_placeholder', 'crw_system_search');
                }
                $mform->addElement(
                    'autocomplete',
                    'searchform_filter_tag',
                    get_string('tags'),
                    $choices,
                    $tagsattrs
                );
                $mform->updateElementAttr('searchform_filter_tag', [
                    'class' => $bscolstyle
                ]);
            }
        }
        
        if (!empty($customfieldsfilters))
        {
            foreach($customfieldsfilters as $customfielddata)
            {
                
                $title = $mform->createElement(
                    'static',
                    $customfielddata[1],
                    '',
                    html_writer::div(
                        ($minimalism ? '' : ''),
                         $minimalism ? 'fake-block' : $customfielddata[1] . '_title'
                        )
                    );
                array_unshift($customfielddata[0], $title);
                $mform->addGroup($customfielddata[0], $customfielddata[1], $customfielddata[2], '', false);
                $mform->updateElementAttr($customfielddata[1], [
                    'class' => 'custom-field ' . $bscolstyle
                ]);
            }
        }
        
        if (!empty($sorter))
        {
            $cctitle = $mform->createElement(
                'static',
                'searchform_sorttype_title',
                '',
                html_writer::div(
                    '',
                    $minimalism ? 'fake-block' : 'searchform_sorttype_title'
                    )
                );
            $sorttypes = local_crw_get_all_allowed_sort_types($this->_customdata->categoryid ?? null);
            // Добавляем выпадающий список
            $sorttype = $mform->createElement('select', 'searchform_sorttype', '', $sorttypes);
            
            $lastsorttype = $mform->createElement('hidden', 'lastsorttype');
            
            $mform->setType('lastsorttype', PARAM_RAW);
            
            $mform->addGroup(
                [$cctitle, $sorttype, $lastsorttype],
                'sorter',
                get_string('searchform_sorttype', 'crw_system_search'),
                '',
                false);
            $mform->updateElementAttr('sorter', ['class' => 'custom-field ' . $bscolstyle]);
        }
        
        
        if (!empty($formdescription) || !empty($datefilter) || !empty($costfilter) ||
            !empty($ccfilter) || !empty($tagsfilter) || !empty($customfieldsfilters) || !empty($sorter))
        {
            // Кнопка сохранения
            $mform->addElement('submit', 'submitbuttonmore',
                get_string('searchform_search', 'local_crw'),
                [
                    'class' => 'crw_system_search_form_submitbuttonmore'
                ]);
        }
        
        // Применим фильтр
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /**
     *
     * {@inheritDoc}
     * @see moodleform::definition_after_data()
     */
    function definition_after_data() {
        
        $mform = $this->_form;
        $plugin = $this->_customdata->plugin;
        // Способ отображения формы
        $style = $plugin->get_config('settings_style', 'default');
        $minimalism = $style == 'minimalism';
        
        /** @var HTML_QuickForm_select $sorttypeel */
        if ($mform->elementExists('searchform_sorttype'))
        {
            $sorttypeel = $mform->getElement('searchform_sorttype');
            if ($minimalism && !$sorttypeel->getMultiple())
            {
                
                
                if ($mform->elementExists('lastsorttype') && $formdata = $this->get_data())
                { // Форма отправлена и проверена
                    $lastsorttypeel = $mform->getElement('lastsorttype');
                    if (!empty($formdata->lastsorttype))
                    {
                        $lastsorttypeel->setValue($formdata->lastsorttype);
                    }
                    if (!empty($formdata->searchform_sorttype))
                    {
                        $lastsorttypeel->setValue($formdata->searchform_sorttype);
                    }
                }
                
                
                $allowed = local_crw_get_all_allowed_sort_types($this->_customdata->categoryid ?? null);
                $default = local_crw_get_default_sort_type($this->_customdata->categoryid ?? null);
                
                $selectedtext = $allowed[$default];
                $selectedvalue = $default;
                
                $selected = $sorttypeel->getValue();
                if (is_array($selected))
                {
                    $selectedvalue = array_shift($selected);
                }
                
                foreach ($sorttypeel->_options as &$option) {
                    
                    if ($option['attr']['value'] == $selectedvalue) {
                        $selectedtext = mb_strtolower($option['text']);
                    }
                }
                
                $sorttypeel->addOption(
                    get_string('searchform_sorttype_title','crw_system_search', $selectedtext),
                    $selectedvalue,
                    [
                        'selected' => 'selected',
                        'disabled' => 'disabled'
                    ]
                );
            }
        }
            
    }
    
    protected function create_custom_fields_filters(MoodleQuickForm &$mform, $style='default', $categoryid=0)
    {
        global $DB;
        
        $result = [];
        
        $minimalism = $style == 'minimalism';
        
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
            $magnifier = null;
            $elements = [];
            
            // Получение настройки из текущей категории
            $fieldrole = local_crw_get_category_config($categoryid, 'custom_field_'.$fieldname.'_role');
            // В текущей категории нет настройки, либо она говорит наследоваться от плагина
            if ($fieldrole == 'inherit' || $fieldrole === false)
            {
                // Получение настройки плагина
                if (get_config('crw_system_search', 'settings_filter_customfield__'.$fieldname) == false)
                {
                    // В настройке плагина указано не отображать фильтр по данному полю
                    continue;
                }
            }
            // В текущей категории в настройке указано не отображать фильтр по данному полю
            // (либо поле целиком отключено даже для редактирования)
            if (in_array($fieldrole, ['search_disabled', 'field_disabled', 'search_disabled_sort_enabled']))
            {
                continue;
            }
            
            // значит значение настройки - search_enabled, поиск включен, не зависимо от настроек плагина
            switch($cffield['type'])
            {
                case 'checkbox':
                    $cffield['options'] = [
                        0 => get_string('filter_checkbox_option_no', 'crw_system_search'),
                        1 => get_string('filter_checkbox_option_yes', 'crw_system_search')
                    ];
                case 'select':
                    $any = $minimalism ? $cffield['label'] : get_string('filter_any', 'crw_system_search');
                    
                    $elements[] = $mform->createElement(
                        'select',
                        'cff_'.$fieldname,
                        null,
                        ['' => $any] + $cffield['options']
                    );
                    break;
                case 'text':
                case 'textarea':
                    $magnifier = $mform->createElement('submit', 'magnifier', '',
                    [
                    'class' => 'crw_system_search_form_submitbutton submit_magnifier'
                        ]
                    );
                    $elements[] = $mform->createElement(
                        'text',
                        'cff_'.$fieldname,
                        null,
                        ['placeholder' => $cffield['label']]
                    );
                    $mform->setType('cff_'.$fieldname, PARAM_RAW);
                    if (!empty($magnifier)) {
                        $elements[] = $magnifier;
                    }
                    break;
            }
            $group = [];
            foreach ($elements as $element) {
                if (!is_null($element))
                {
                    $elementattrs = $element->getAttributes();
                    $elementattrs['class'] = trim($elementattrs['class'] ?? '').' custom-field-filter';
                    $element->setAttributes($elementattrs);
                    $group[] = $element;
                }
            }
            $result[] = [$group, $fieldname, $cffield['label']];
        }
        return $result;
    }
    
    public function prepare_default_values()
    {
        $defaultvalues = [];
        
        foreach($this->get_sent_filters() as $field => $value)
        {
            switch($field)
            {
                case 'maxprice':
                    $defaultvalues['bottomprice[maxprice]'] = $value;
                    break;
                case 'minprice':
                    $defaultvalues['bottomprice[minprice]'] = $value;
                    break;
                case 'mindate':
                    $defaultvalues['bottomdate[mindate]'] = $value;
                    break;
                case 'maxdate':
                    $defaultvalues['bottomdate[maxdate]'] = $value;
                    break;
                case 'name':
                    $defaultvalues['topblock[name]'] = $value;
                    break;
                case 'coursecontact':
                    $ccs = explode(',', $value);
                    foreach($ccs as $cc)
                    {
                        list($roleid, $userid) = explode(':', $cc);
                        $defaultvalues['searchform_coursecontact['.$roleid.']'] = $userid;
                    }
                    break;
                case 'tags':
                    $tags = explode(',', $value);
                    $defaultvalues['searchform_filter_tag'] = $tags;
                    break;
                case 'sq':
                    $defaultvalues['topblock[crws]'] = $value;
                case 'sorttype':
                    
                    $defaultvalues['searchform_sorttype'] = $value;
                    $defaultvalues['lastsorttype'] = $value;
                    break;
                default:
                    $defaultvalues[$field] = $value;
            }
        }
        
        return $defaultvalues;
    }
    
    
    public function get_sent_filters()
    {
        if (!isset($this->sent_filters))
        {
            $conditions = [];
            
            // значение по умолчанию для сортировки
            $conditions['sorttype'] = local_crw_get_default_sort_type($this->_customdata->categoryid ?? null);
            
            if (!empty($this->crws))
            {
                $conditions = crw_system_search::get_conditions($this->crws);
            }
            
            $formdata = $this->get_data();
    
            if ( !is_null($formdata) )
            { // Форма отправлена и проверена
                
                $conditions = [];
                
                if (!empty($formdata->topblock['crws']) )
                { // Добавляем поиск по названию
                    $conditions = crw_system_search::get_conditions($formdata->topblock['crws']);
                }
                
                if (!empty($formdata->lastsorttype))
                {
                    $conditions['sorttype'] = $formdata->lastsorttype;
                }
                if (!empty($formdata->searchform_sorttype))
                {
                    $conditions['sorttype'] = $formdata->searchform_sorttype;
                }
                
                if (!array_key_exists('sq', $conditions))
                {
                    if ( ! empty($formdata->topblock['name']) )
                    { // Добавляем поиск по названию
                        $conditions['name'] = $formdata->topblock['name'];
                    }
                    
                    
                    if ( ! empty($formdata->bottomdate['mindate']) )
                    { // Добавляем поиск по названию
                        $conditions['mindate'] = $formdata->bottomdate['mindate'];
                    }
                    if ( ! empty($formdata->bottomdate['maxdate']) )
                    { // Добавляем поиск по названию
                        $conditions['maxdate'] = $formdata->bottomdate['maxdate'];
                    }
                    if ( ! empty($formdata->bottomprice['minprice']) )
                    { // Добавляем поиск по названию
                        $conditions['minprice'] = $formdata->bottomprice['minprice'];
                    }
                    if ( ! empty($formdata->bottomprice['maxprice']) )
                    { // Добавляем поиск по названию
                        $conditions['maxprice'] = $formdata->bottomprice['maxprice'];
                    }
                    if ( ! empty($formdata->searchform_coursecontact) )
                    { // Добавляем поиск по контактам курса
                        $cc = [];
                        foreach($formdata->searchform_coursecontact as $roleid => $userid)
                        {
                            if( (int)$userid !== 0 )
                            {
                                $cc[] = (int)$roleid . ':' . (int)$userid;
                            }
                        }
                        if (!empty($cc))
                        {
                            if (!empty($conditions['coursecontact']))
                            {
                                $conditions['coursecontact'] .= ','.implode(',', $cc);
                            } else
                            {
                                $conditions['coursecontact'] = implode(',', $cc);
                            }
                        }
                    }
                    if ( ! empty($formdata->searchform_filter_tag) )
                    {
                        if (!empty($conditions['tags']))
                        {
                            $conditions['tags'] .= ','.implode(',', $formdata->searchform_filter_tag);
                        } else
                        {
                            $conditions['tags'] = implode(',', $formdata->searchform_filter_tag);
                        }
                    }
                    
                    
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
                            
                            foreach($cffields as $fieldname => $cffield)
                            {
                                $fieldname = 'cff_'.$fieldname;
                                if (isset($formdata->$fieldname) && $formdata->$fieldname != '')
                                {
                                    $conditions[$fieldname] = $formdata->$fieldname;
                                }
                            }
                        }
                    }
                }
            }
            
            
            $this->sent_filters = $conditions;
        }
        
        return $this->sent_filters;
    }
    
    
    /**
     * Обработчик формы
     *
     * @param boolean $redirect - перенаправление на страницу с отфильтрованными курсами
     *
     */
    function process($redirect=true)
    {
        global $DB, $CFG;
        
        if ( $formdata = $this->get_data() )
        { // Форма отправлена и проверена
            
            if ( empty($formdata) )
            {
                return;
            }
            
            $crws = crw_system_search::get_string_from_conditions($this->get_sent_filters());

            if ($redirect)
            {
                $urlopts = ['crws' => $crws, 'srr' => 'system_search'];
//                 // Поскольку появилась возможность переопределять форму поиска в категориях
//                 // вероятно потребуется ограничивать поиск категорией, чтобы не терять фильтры доступные только в целевой категории
//                 // пока для достижения цели используется ajax-поиск на странице, который сохраняет контекст категории
//                 if (isset($this->_customdata->categoryid))
//                 {
//                     $urlopts['cid'] = $this->_customdata->categoryid;
//                 }
                $url = new moodle_url('/local/crw/search.php', $urlopts);
                redirect($url);
                
            } else
            {
                return $crws;
            }
        }
    }
}