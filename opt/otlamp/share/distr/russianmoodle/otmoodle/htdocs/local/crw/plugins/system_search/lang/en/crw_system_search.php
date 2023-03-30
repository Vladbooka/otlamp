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

$string['crw_system_search_category'] = 'Courses search';

$string['crw_system_search_settings'] = 'Settings';

$string['settings_title'] = 'Course search settings';
$string['settings_title_desc'] = '';
$string['settings_formdescription'] = 'Search form description';
$string['settings_formdescription_desc'] = 'The text will be displayed to the users in the extended search form';
$string['settings_fullsearch_only'] = 'Always show extended search';
$string['settings_fullsearch_only_desc'] = 'Extended search is hidden by default and might be shown only if \'Extended search\' has been pressed. Enabling this setting will force extended search to open and removes \'Extended search\' button.';
$string['settings_displayfilter_datestart'] = 'Show extended search filter by course start date.';
$string['settings_displayfilter_datestart_desc'] = 'Shows a filter, which searches for courses that start in the specified period of time.';
$string['settings_displayfilter_cost'] = 'Show extended search filter by price.';
$string['settings_displayfilter_cost_desc'] = 'Shows a filter, which searches for courses with the price in the specified range.';
$string['settings_displayfilter_coursecontacts'] = 'Show extended search filter by course contacts.';
$string['settings_displayfilter_coursecontacts_desc'] = 'Shows a filter, which searches for courses with a course contact specified. For example, courses of a specific teacher.';
$string['settings_displayfilter_tags'] = 'Show extended search filter by tags.';
$string['settings_displayfilter_tags_desc'] = 'Shows a filter, which searches for courses with specific tags';
$string['settings_exclude_standard_tags'] = 'Exclude following standard tags from the search';
$string['settings_exclude_standard_tags_desc'] = 'All standard tags that belong to the course tag collection are displayed in the filter by default.';
$string['settings_tagfilter_logic'] = 'The logic of use of filter by tags by default';
$string['settings_tagfilter_logic_desc'] = '<div>Choose \'AND\' if you want selected by default only courses that have all selected tags.</div><div>Choose \'OR\' if you want selected by default all the courses that have at least one selected tag.</div>';
$string['settings_tagfilter_logic_or'] = '\'OR\'';
$string['settings_tagfilter_logic_and'] = '\'AND\'';

$string['crw_system_search_hints_settings'] = 'Hints settings';

$string['hints_settings_info'] = '';
$string['hints_settings_info_desc'] = 'In the following settings list you can specify what data should the system display while input the query into the search field.';
$string['hints_settings_area_gs_crw_course'] = 'Courses found by searching in course information';
$string['hints_settings_area_gs_crw_course_desc'] = 'Searches for the specified value in the course name and course description and adds found courses to the drop down course list';
$string['hints_settings_area_gs_crw_course_contacts'] = 'Courses found with search in course contacts';
$string['hints_settings_area_gs_crw_course_contacts_desc'] = 'Searches for the specified value in the course contacts and adds found courses to the drop down course list';
$string['hints_settings_area_gs_crw_course_tags'] = 'Courses found with search in tags';
$string['hints_settings_area_gs_crw_course_tags_desc'] = 'Searches for the specified value in the course tags and adds found courses to the drop down course list';
$string['hints_settings_area_gs_crw_course_tagcollection_custom1'] = 'Courses found with search in tags from the 1st collection';
$string['hints_settings_area_gs_crw_course_tagcollection_custom1_desc'] = 'Searches for the specified value in the course tags 1st collection and adds found courses to the drop down course list';
$string['hints_settings_area_gs_crw_course_tagcollection_custom2'] = 'Courses found with search in tags from the 2nd collection';
$string['hints_settings_area_gs_crw_course_tagcollection_custom2_desc'] = 'Searches for the specified value in the course tags 2nd collection and adds found courses to the drop down course list';
$string['hints_settings_area_coursecontacts'] = 'Course contacts';
$string['hints_settings_area_coursecontacts_desc'] = 'Adds users who are course contacts and match the query to the drop down list. Searches all courses where the user specified is a course contact.';
$string['hints_settings_area_tags'] = 'Course tags';
$string['hints_settings_area_tags_desc'] = 'Add course tags that match the query to the drop down list. Searches for all courses that have this tag.';
$string['hints_settings_area_tagcollection_custom1'] = 'Tags from the 1st collection';
$string['hints_settings_area_tagcollection_custom1_desc'] = 'Adds tags from the 1st collection to the drop down list of tags. The tags must be assigned to a published course and match the query. Searches for all courses that have this tag.';
$string['hints_settings_area_tagcollection_custom2'] = 'Tags from the 2nd collection';
$string['hints_settings_area_tagcollection_custom2_desc'] = 'Adds tags from the 2nd collection to the drop down list of tags. The tags must be assigned to a published course and match the query. Searches for all courses that have this tag.';

$string['hintarea:gsa_crw_course'] = 'go to course';
$string['hintarea:gsa_crw_course_contacts'] = 'go to course';
$string['hintarea:gsa_crw_course_tags'] = 'go to course';
$string['hintarea:gsa_crw_course_tagcollection_custom1'] = 'go to course';
$string['hintarea:gsa_crw_course_tagcollection_custom2'] = 'go to course';
$string['hintarea:course_contacts'] = 'show courses where <b>{$a}</b> is';
$string['hintarea:course_tags'] = 'show courses with <b>tag</b>';
$string['hintarea:course_tagcollection_custom1'] = 'show courses with tag from <b>collection "custom1"</b>';
$string['hintarea:course_tagcollection_custom2'] = 'show courses with tag from <b>collection "custom2"</b>';

$string['hintsubarea:gsa_crw_course'] = 'match was found in course info';
$string['hintsubarea:gsa_crw_course_contacts'] = 'match was found in course contacts';
$string['hintsubarea:gsa_crw_course_tags'] = 'match was found in course tags';
$string['hintsubarea:gsa_crw_course_tagcollection_custom1'] = 'match was found in tags from collection "custom1"';
$string['hintsubarea:gsa_crw_course_tagcollection_custom2'] = 'match was found in tags from collection "custom2"';
$string['hintsubarea:course_contacts'] = '';
$string['hintsubarea:course_tags'] = '';
$string['hintsubarea:course_tagcollection_custom1'] = '';
$string['hintsubarea:course_tagcollection_custom2'] = '';

$string['search:crw_course'] = 'Course info';
$string['search:crw_course_contacts'] = 'Course contacts';
$string['search:crw_course_tags'] = 'Course tags';
$string['search:crw_course_tagcollection_custom1'] = 'Tags from collection "custom1", assigned to course';
$string['search:crw_course_tagcollection_custom2'] = 'Tags from collection "custom2", assigned to course';
$string['search_course_names'] = '{$a->fullname} [{$a->shortname}]';

$string['searchform_description'] = '';
$string['searchform_reset'] = 'Reset';
$string['searchform_dategroup_from'] = 'from';
$string['searchform_dategroup_to'] = 'to';
$string['searchform_pricegroup_from'] = 'from';
$string['searchform_pricegroup_to'] = 'to';
$string['searchform_dategroup'] = 'Select by course start date';
$string['searchform_pricegroup'] = 'Select by course price';
$string['searchform_coursecontact_any'] = 'Any';
$string['searchform_sorttype'] = 'Sort';
$string['searchform_sorttype_title'] = 'Order [{$a}]';

$string['setting_search_result_renderer'] = 'Option to display search results';
$string['setting_search_result_renderer_desc'] = 'The list of courses found by the specified search criteria will be displayed according to the selected setting.';

$string['settings_hide_reset_button'] = 'Hide reset form button';
$string['settings_hide_reset_button_desc'] = '';
$string['hints_settings_results_count'] = 'The number of results in the drop down list';
$string['hints_settings_results_count_desc'] = '';
$string['crw_system_search_filters_settings'] = 'Filters settings';
$string['settings_single_result_redirect'] = 'Redirect to course on single item in search results';
$string['settings_single_result_redirect_desc'] = 'This setting is not compatible with ajax search that uses filtering without reloading the page, and will have no effect if it is configured.';
$string['single_result_redirect_id_specified'] = 'if id specified by user choice';
$string['single_result_redirect_never'] = 'never';
$string['single_result_redirect_always'] = 'always';
$string['settings_query_string_role'] = 'Query string role';
$string['settings_query_string_role_desc'] = '';
$string['settings_query_string_role_name'] = 'search by name';
$string['settings_query_string_role_hints'] = 'global search with hints';
$string['settings_query_string_role_none'] = 'hide filter';
$string['search_hints_header'] = 'Search results for "{$a}":';
$string['search_hints_no_results_found'] = 'No results were found';
$string['show_all_hints'] = 'show all results';
$string['searchform_coursecontact_filter_title'] = 'Filter by user';

$string['crw_system_search_filtertab_general'] = 'General filters';
$string['crw_system_search_filtertab_custom'] = 'Custom fields filters';
$string['settings_filter_customfields_heading'] = '';
$string['settings_filter_customfields_heading_desc'] = 'Configurable fields of types text, textarea, select and checkbox are displayed in the current tab. The fields might be used to filter the courses.';
$string['filter_any'] = 'Any value';

$string['settings_style'] = 'Search form display mode';
$string['settings_style_desc'] = '<div>Default - fields with titles and grey background.</div>
<div>Minimalism - compact fields with placeholders.</div>';
$string['settings_style_default'] = 'Default';
$string['settings_style_minimalism'] = 'Minimalism';

$string['settings_ajax_search'] = 'Search without page reload';
$string['settings_ajax_search_desc'] = '<div>The tool uses ajax technology, which requires javascript enabled in the browser.</div>
<div>The user must be authorised. If the user is not authorised, the form will be sent regular way.</div>
<div>This tool functions only when the setting \'Apply filtering to the showcase on current page\' is enabled.</div>';
$string['settings_display_results_inplace'] = 'Apply filtering to the showcase on current page';
$string['settings_display_results_inplace_desc'] = '<div>The results are shown on a separate page by default. The results are rendered by render chosen for course search results.</div>
<div>When this setting is enebled the form is processed on current page but the search results will be applied to the showcase objects, which are displayed (if configured) along with the search form. Course render and sending the form without page reload will not be used in this case.</div>';
$string['settings_display_sorter'] = 'Display sort field';
$string['settings_display_sorter_desc'] = '';

$string['filter_name_placeholder'] = 'Course name';
$string['filter_minprice_placeholder'] = 'Price from';
$string['filter_maxprice_placeholder'] = 'Price to';
$string['filter_tags_placeholder'] = 'Tags';

$string['filter_checkbox_option_yes'] = 'yes';
$string['filter_checkbox_option_no'] = 'no';

