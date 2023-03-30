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
 * Плагин поиска курсов. Языковые переменные.
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Поиск курсов';

$string['crw_system_search_category'] = 'Поиск курсов';

$string['crw_system_search_settings'] = 'Настройка поиска';
$string['settings_title'] = 'Настройки поиска курсов';
$string['settings_title_desc'] = '';
$string['settings_formdescription'] = 'Описание формы поиска';
$string['settings_formdescription_desc'] = 'Текст отобразится пользователям на расширенной форме поиска';
$string['settings_fullsearch_only'] = 'Всегда отображать расширенный поиск';
$string['settings_fullsearch_only_desc'] = 'По умолчанию расширенный поиск скрыт и отображается только если нажать кнопку "Расширенный поиск". Активация этой настройки повлечет принудительное открытие расширенного поиска и удаление кнопки "Расширенный поиск"';
$string['settings_displayfilter_datestart'] = 'Отображать фильтр расширенного поиска по дате начала курса';
$string['settings_displayfilter_datestart_desc'] = 'Отображается фильтр, при помощи которого можно найти курсы, которые начинаются в указанный период времени';
$string['settings_displayfilter_cost'] = 'Отображать фильтр расширенного поиска по стоимости';
$string['settings_displayfilter_cost_desc'] = 'Отображается фильтр, при помощи которого можно найти курсы, стоимость которых находится в указанном диапазоне';
$string['settings_displayfilter_coursecontacts'] = 'Отображать фильтр расширенного поиска по контактам курса';
$string['settings_displayfilter_coursecontacts_desc'] = 'Отображается фильтр, при помощи которого можно найти курсы с указанным в фильтре контактом курса (например, курсы конкретного преподавателя)';
$string['settings_displayfilter_tags'] = 'Отображать фильтр расширенного поиска по тегам';
$string['settings_displayfilter_tags_desc'] = 'Отображается фильтр, при помощи которого можно найти курсы, помеченные выбранными тегами';
$string['settings_exclude_standard_tags'] = 'Исключить из поиска следующие стандартные теги курсов';
$string['settings_exclude_standard_tags_desc'] = 'По умолчанию в фильтре по тегам отображаются все стандартные теги, относящиеся к коллекции тегов курсов';
$string['settings_tagfilter_logic'] = 'Логика применения фильтра по тегам';
$string['settings_tagfilter_logic_desc'] = '<div>Выберите "И", если хотите, чтобы по умолчанию были выбраны только те курсы, у которых имеются все выбранные теги</div><div>Выберите "ИЛИ", если хотите, чтобы по умолчанию были выбраны все курсы, у которых имеется хотя бы один тег из выбранных</div>';
$string['settings_tagfilter_logic_or'] = '"ИЛИ"';
$string['settings_tagfilter_logic_and'] = '"И"';



$string['crw_system_search_hints_settings'] = 'Настройка подсказок сквозного поиска';

$string['hints_settings_info'] = '';
$string['hints_settings_info_desc'] = 'В указанном ниже списке настроек вы можете указать, какие данные должна отображать система при вводе запроса в поле поиска, в каком количестве и т.д.';
$string['hints_settings_area_gs_crw_course'] = 'Курсы, найденные по информации о курсе';
$string['hints_settings_area_gs_crw_course_desc'] = 'Добавит в выпадающий список курсы, в наименовании и описании которых глобальный поиск найдет введенное значение';
$string['hints_settings_area_gs_crw_course_contacts'] = 'Курсы, найденные по контактам курса';
$string['hints_settings_area_gs_crw_course_contacts_desc'] = 'Добавит в выпадающий список курсы, среди контактов (учителя и др.роли) которых глобальный поиск найдет введенное значение';
$string['hints_settings_area_gs_crw_course_tags'] = 'Курсы, найденные по тегам';
$string['hints_settings_area_gs_crw_course_tags_desc'] = 'Добавит в выпадающий список курсы, среди тегов которых глобальный поиск найдет введенное значение';
$string['hints_settings_area_gs_crw_course_tagcollection_custom1'] = 'Курсы, найденные по тегам из коллекции 1';
$string['hints_settings_area_gs_crw_course_tagcollection_custom1_desc'] = 'Добавит в выпадающий список курсы, среди которых глобальный поиск найдет в назначенных им тегах из коллекции 1, введенное значение';
$string['hints_settings_area_gs_crw_course_tagcollection_custom2'] = 'Курсы, найденные по тегам из коллекции 2';
$string['hints_settings_area_gs_crw_course_tagcollection_custom2_desc'] = 'Добавит в выпадающий список курсы, среди которых глобальный поиск найдет в назначенных им тегах из коллекции 2, введенное значение';
$string['hints_settings_area_coursecontacts'] = 'Контакты курса';
$string['hints_settings_area_coursecontacts_desc'] = 'Добавит в выпадающий список пользователей, подходящих под введенный запрос, являющихся контактом курса. Предоставляет возможность искать все курсы, где среди контактов курса есть указанный пользователь';
$string['hints_settings_area_tags'] = 'Теги курса';
$string['hints_settings_area_tags_desc'] = 'Добавит в выпадающий список теги курса, подходящие под введенный запрос. Предоставляет возможность искать все курсы, помеченные этим тегом';
$string['hints_settings_area_tagcollection_custom1'] = 'Теги из коллекции 1';
$string['hints_settings_area_tagcollection_custom1_desc'] = 'Добавит в выпадающий список теги из коллекции 1, назначенные опубликованному курсу и подходящие под введенный запрос. Предоставляет возможность искать все курсы, помеченные этим тегом';
$string['hints_settings_area_tagcollection_custom2'] = 'Теги из коллекции 2';
$string['hints_settings_area_tagcollection_custom2_desc'] = 'Добавит в выпадающий список теги из коллекции 2, назначенные опубликованному курсу и подходящие под введенный запрос. Предоставляет возможность искать все курсы, помеченные этим тегом';

$string['hintarea:gsa_crw_course'] = 'перейти в курс';
$string['hintarea:gsa_crw_course_contacts'] = 'перейти в курс';
$string['hintarea:gsa_crw_course_tags'] = 'перейти в курс';
$string['hintarea:gsa_crw_course_tagcollection_custom1'] = 'перейти в курс';
$string['hintarea:gsa_crw_course_tagcollection_custom2'] = 'перейти в курс';
$string['hintarea:course_contacts'] = 'показать курсы, где в роли <b>{$a}</b>';
$string['hintarea:course_tags'] = 'показать курсы, помеченные <b>тегом</b>';
$string['hintarea:course_tagcollection_custom1'] = 'показать курсы, помеченные <b>тегом из коллекции 1</b>';
$string['hintarea:course_tagcollection_custom2'] = 'показать курсы, помеченные <b>тегом из коллекции 2</b>';

$string['hintsubarea:gsa_crw_course'] = 'совпадение найдено в описании (названии)';
$string['hintsubarea:gsa_crw_course_contacts'] = 'совпадение найдено в контактах курса';
$string['hintsubarea:gsa_crw_course_tags'] = 'совпадение найдено среди тегов';
$string['hintsubarea:gsa_crw_course_tagcollection_custom1'] = 'совпадение найдено среди тегов из коллекции 1';
$string['hintsubarea:gsa_crw_course_tagcollection_custom2'] = 'совпадение найдено среди тегов из коллекции 2';
$string['hintsubarea:course_contacts'] = '';
$string['hintsubarea:course_tags'] = '';
$string['hintsubarea:course_tagcollection_custom1'] = '';
$string['hintsubarea:course_tagcollection_custom2'] = '';

$string['search:crw_course'] = 'Информация об опубликованном на витрине курсе';
$string['search:crw_course_contacts'] = 'Контакты курса, опубликованного на витрине';
$string['search:crw_course_tags'] = 'Теги курса, опубликованного на витрине';
$string['search:crw_course_tagcollection_custom1'] = 'Теги из коллекции 1, назначенные опубликованному на витрине курсу';
$string['search:crw_course_tagcollection_custom2'] = 'Теги из коллекции 2, назначенные опубликованному на витрине курсу';
$string['search_course_names'] = '{$a->fullname} [{$a->shortname}]';

$string['searchform_description'] = '';
$string['searchform_reset'] = 'Сбросить';
$string['searchform_dategroup_from'] = 'с';
$string['searchform_dategroup_to'] = 'по';
$string['searchform_pricegroup_from'] = 'от';
$string['searchform_pricegroup_to'] = 'до';
$string['searchform_dategroup'] = 'Отобрать по дате начала курсов';
$string['searchform_pricegroup'] = 'Отобрать по стоимости курсов';
$string['searchform_coursecontact_any'] = 'Любой';
$string['searchform_sorttype'] = 'Сортировка';
$string['searchform_sorttype_title'] = 'Сортировать [{$a}]';

$string['setting_search_result_renderer'] = 'Вариант отображения результатов поиска';
$string['setting_search_result_renderer_desc'] = 'Список курсов, найденных по заданным критериям поиска, будет отображен согласно выбранной настройке';

$string['settings_hide_reset_button'] = 'Скрыть кнопку очистки формы поиска';
$string['settings_hide_reset_button_desc'] = '';
$string['hints_settings_results_count'] = 'Количество результатов, предлагаемых в выпадающем списке';
$string['hints_settings_results_count_desc'] = '';
$string['crw_system_search_filters_settings'] = 'Настройки фильтров поиска';
$string['settings_single_result_redirect'] = 'Перенаправлять в курс в случае, если найден единственный курс';
$string['settings_single_result_redirect_desc'] = 'Данная настройка не совместима с ajax-поиском, применяющим фильтрацию без перезагрузки страницы, и не будет иметь влияния, если он настроен.';
$string['single_result_redirect_id_specified'] = 'пользователь выбрал конкретный курс';
$string['single_result_redirect_never'] = 'никогда';
$string['single_result_redirect_always'] = 'всегда';
$string['settings_query_string_role'] = 'Роль строки поиска';
$string['settings_query_string_role_desc'] = '';
$string['settings_query_string_role_name'] = 'поиск по названию';
$string['settings_query_string_role_hints'] = 'глобальный поиск с подсказками';
$string['settings_query_string_role_none'] = 'не отображать';
$string['search_hints_header'] = 'Результаты поиска по запросу "{$a}":';
$string['search_hints_no_results_found'] = 'По вашему запросу не найдено результатов';
$string['show_all_hints'] = 'Все результаты';
$string['searchform_coursecontact_filter_title'] = 'Отобрать по пользователю';

$string['crw_system_search_filtertab_general'] = 'Основные фильтры';
$string['crw_system_search_filtertab_custom'] = 'Фильтры настраиваемых полей';
$string['settings_filter_customfields_heading'] = '';
$string['settings_filter_customfields_heading_desc'] = 'На текущей вкладке отображаются настраиваемые поля типов text, textarea, select, checkbox. Их можно использовать для фильтрации курсов.';
$string['filter_any'] = 'Любое значение';

$string['settings_style'] = 'Вариант отображения формы поиска';
$string['settings_style_desc'] = '<div>Стандарт - поля с заголовками и серым фоном</div>
<div>Минимализм - компактные поля с плейсхолдерами</div>';
$string['settings_style_default'] = 'Стандарт';
$string['settings_style_minimalism'] = 'Минимализм';

$string['settings_ajax_search'] = 'Выполнять поиск без перезагрузки страницы';
$string['settings_ajax_search_desc'] = '<div>Для работы инструмента используется технология ajax, для которой требуется включенный в браузере javascript</div>
<div>Пользователь должен быть авторизован. При отсутствии авторизации будет форма будет отправлена стандартным способом.</div>
<div>Инструмент работает только совместно с настройкой "Применять фильтрацию к витрине на текущей странице"</div>';
$string['settings_display_results_inplace'] = 'Применять фильтрацию к витрине на текущей странице';
$string['settings_display_results_inplace_desc'] = '<div>По умолчанию результаты отображаются на отдельной странице, при помощи выбранного для результатов поиска рендера курсов.</div>
<div>Включив эту настройку, форма будет обработана на текущей странице, а результаты поиска будут применены к объектам витрины, отображающимся (если настроено) совместно с формой поиска. Рендер курсов и отправка формы без перезагрузки страницы не используются в таком случае.</div>';
$string['settings_display_sorter'] = 'Показать поле сортировки';
$string['settings_display_sorter_desc'] = '';

$string['filter_name_placeholder'] = 'Название курса';
$string['filter_minprice_placeholder'] = 'Стоимость от';
$string['filter_maxprice_placeholder'] = 'Стоимость до';
$string['filter_tags_placeholder'] = 'Теги';

$string['filter_checkbox_option_yes'] = 'да';
$string['filter_checkbox_option_no'] = 'нет';

