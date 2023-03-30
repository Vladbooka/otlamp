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
 * Витрина курсов. Языковые переменные.
 *
 * @package local
 * @subpackage crw
 * @licensehttp://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Базовые языковые переменные
$string['pluginname'] = 'Courses showcase';
$string['local_crw'] = 'Main page';
$string['title'] = 'Courses showcase';
$string['about_course'] = 'About the course';
$string['courses_showcase'] = 'Courses showcase';
$string['rub'] = 'R';

$string['eventcoursepageviewed'] = 'Course description page was viewed';

// Настройки плагина
$string['settings_title_category_block'] = 'Category block settings';
$string['settings_title_category_block_desc'] = '';
$string['settings_category_block_type'] = 'Category block type';
$string['settings_category_block_type_tiles'] = 'Tiles';
$string['settings_category_block_type_icons'] = 'Icons';
$string['settings_title_category_block_type_icons'] = 'Category block settings - Icons';
$string['settings_title_category_block_type_icons_desc'] = '';
$string['settings_category_block_type_iconfile'] = 'Categories tile icon file';
$string['settings_category_block_type_iconfile_desc'] = '';
$string['settings_title_courses_block'] = 'Courses block settings';
$string['settings_title_courses_block_desc'] = '';
$string['settings_courses_block_type_tiles'] = 'Tiles';
$string['settings_courses_block_type_catlist'] = 'List';
$string['settings_courses_block_type'] = 'Courses block type';
$string['settings_courses_catanchor'] = 'Anchors instead of links';
$string['settings_title_courses_block_catlist'] = 'Courses block settings - List';
$string['settings_title_courses_block_catlist_desc'] = '';
$string['settings_courses_block_catlist_iconfile'] = 'Course in the list icon file';
$string['settings_courses_block_catlist_iconfile_desc'] = '';
$string['settings_courses_block_catlist_totopdisplay'] = 'Show button \'To the top\'';
$string['settings_courses_block_catlist_totopdisplay_desc'] = '';
$string['settings_courses_block_catlist_totopiconfile'] = '\'To the top\' button file';
$string['settings_courses_block_catlist_totopiconfile_desc'] = '';
$string['settings_categories_list'] = 'Categories block';
$string['settings_courses_list'] = 'Courses block';
$string['settings_plugins_empty'] = 'No';
$string['settings_slots_cs_header'] = 'Showcase header';
$string['settings_slots_cs_header_desc'] = 'Block to be shown in the header section of showcase page';
$string['settings_slots_cs_top'] = 'Showcase top';
$string['settings_slots_cs_top_desc'] = 'Block to be shown in the top section of Showcase page';
$string['settings_slots_cs_bottom'] = 'Showcase bottom';
$string['settings_slots_cs_bottom_desc'] = 'Block to be shown in the bottom section of showcase page';
$string['settings_display_paging'] = 'Paging display';
$string['settings_display_paging_desc'] = 'Paging will not be displayed when ajax course loading is on';
$string['settings_display_paging_nowhere'] = 'Not to show';
$string['settings_display_paging_top'] = 'At the top';
$string['settings_display_paging_bottom'] = 'At the bottom';
$string['settings_display_paging_topbottom'] = 'Both top and bottom';
$string['settings_display_statistics'] = 'Show displayed courses statistics';
$string['settings_display_statistics_desc'] = '';
$string['settings_display_statistics_nowhere'] = 'Not to show';
$string['settings_display_statistics_top'] = 'At the top';
$string['settings_display_statistics_bottom'] = 'At the bottom';
$string['settings_display_statistics_topbottom'] = 'Both top and bottom';
$string['settings_courses_pagelimit'] = 'Courses per page';
$string['settings_courses_pagelimit_desc'] = '';
$string['settings_display_pagelimit_change_tool'] = 'Quantity of courses displayed per page form display';
$string['settings_display_pagelimit_change_tool_desc'] = '';
$string['settings_display_pagelimit_change_tool_nowhere'] = 'Not to show';
$string['settings_display_pagelimit_change_tool_top'] = 'At the top';
$string['settings_display_pagelimit_change_tool_bottom'] = 'At the bottom';
$string['settings_display_pagelimit_change_tool_topbottom'] = 'Both top and bottom';
$string['settings_ajax_courses_flow'] = 'Ajax course loading';
$string['settings_ajax_courses_flow_desc'] = 'Pajing is not displayed if this setting is on';
$string['settings_ajax_courses_flow_autoload'] = 'Automatically flow courses when you reach the end of the tape';
$string['settings_ajax_courses_flow_autoload_desc'] = '';
$string['settings_display_invested_courses'] = 'Display nested categories courses';
$string['settings_display_invested_courses_desc'] = 'System displays courses of all nested categories as well as of current category when this setting is on';
$string['settings_main_catid'] = 'Courses showcase main category';
$string['settings_main_catid_desc'] = 'The showcase display begins at this category';
$string['settings_main_catid_not_set'] = 'Not set';
$string['display_not_nested_title'] = 'Display not nested categories';
$string['display_not_nested_desc'] = 'Setting allows do display all categories instead of default behavior (only displays categories nested in main category)';
$string['settings_custom_course_fields_title'] = 'Custom course fields';
$string['settings_custom_course_fields_desc'] = "You must enter form fields in this yaml markup in this field. Example:<br/>
class:<br/>
&nbsp;&nbsp;&nbsp;description:<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type: 'textarea'<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;label: 'module description displayed to reviewers'<br/>
&nbsp;&nbsp;&nbsp;speclevel:<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type: 'select'<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;repeatgroup: 'specialities'<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;label: 'The level of education'<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;options: [higher, secondary]<br/>
&nbsp;&nbsp;&nbsp;specname:<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type: 'text'<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;repeatgroup: 'specialities'<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;label: 'Name of specialty'<br/>
&nbsp;&nbsp;&nbsp;submit:<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type: 'submit'<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;label: 'Save'<br/>";
$string['settings_custom_fields_view_title'] = 'Display custom fields';
$string['settings_custom_fields_view_desc'] = 'Setting could be inherited in course';
$string['settings_coursepage_template'] = 'Course description page template';
$string['settings_coursepage_template_desc'] = 'Template could be overriden on category and course level';

$string['course_in_line'] = 'Course tiles per row';
$string['courses_pagelimit'] = 'Categories per page';
$string['courses_catlimit'] = 'Categories per row';
$string['courses_showcategory'] = 'Show course category';
$string['courses_showcategory_courseconfig'] = 'Course settings';

$string['courses_sort_type_course_sort'] = 'Order by course sort';
$string['courses_sort_type_course_created'] = 'Order by course created';
$string['courses_sort_type_course_start'] = 'Order by course start date';
$string['courses_sort_type_learninghistory_enrolments'] = 'Order by count of enrolments ever';
$string['courses_sort_type_active_enrolments'] = 'Order by count of currently active enrolments';
$string['courses_sort_type_course_popularity'] = 'By course popularity';
$string['courses_sort_type_course_name'] = 'By course name';
$string['settings_course_sort_type'] = 'Default sort type';
$string['settings_course_sort_type_desc'] = '';
$string['settings_course_sort_types'] = 'Sort types';
$string['settings_course_sort_types_desc'] = '';
$string['settings_course_sort_direction'] = 'Sorting direction';
$string['settings_course_sort_direction_desc'] = '';

$string['courses_sort_direction_asceding'] = 'Asceding';
$string['courses_sort_direction_desceding'] = 'Desceding';

$string['settings_general'] = 'General';
$string['settings_title_general'] = 'General settings';
$string['settings_title_general_desc'] = '';
$string['settings_title_general_categories_list'] = 'Categories block template';
$string['settings_title_general_categories_list_desc'] = 'Categories list block view template';
$string['settings_title_general_courses_list'] = 'Courses block template';
$string['settings_title_general_courses_list_desc'] = 'Courses list block view template';

$string['settings_subplugintype_crw'] = 'Courses showcase blocks';

$string['settings_course_info_view_title'] = 'Course description page display';
$string['settings_course_info_view_desc'] = 'This setting allows to hide extra description page, show to unenrolled users only or show to all users';
$string['settings_hide_course_info_page'] = 'Hidden from the users';
$string['settings_redirect_all_enrolled_users'] = 'Must be shown only to unenrolled for the course users';
$string['settings_show_course_info_page_for_all_users'] = 'Must be shown to all users enrolled for the course';
$string['course_info_view'] = 'Course description page display';

$string['settings_hide_course_contacts_title'] = 'Hide course contacts in course description page';
$string['settings_hide_course_contacts_desc'] = 'The setting allows to hide or show the block with the course contacts in the course description page';

$string['settings_hide_course_gallery_title'] = 'Hide course gallery in the course description page';
$string['settings_hide_course_gallery_desc'] = 'The setting allows to hide or show the course gallery block in the course description page';
$string['settings_course_popularity_type'] = 'How to consider the popularity of the course';
$string['settings_course_popularity_type_desc'] = '';
$string['popularity_unique_course_view'] = 'By unique course views per month';
$string['settings_override_navigation'] = 'Override standard navigation';
$string['settings_override_navigation_desc'] = '
<div>With this setting, the standard navigation will be changed in order to redirect users to alternative interfaces implemented in the courses showcase.</div>
<div>In breadcrumbs, links to standard course categories will lead to course category pages in the showcase, and a link to the Courses page will lead to the courses showcase home page. </div>
<div>From the course categories will be automatically redirected to the course category pages in the courses showcase</div>';
$string['settings_remove_courses_nav_node'] = 'Exclude node "Courses" ("My Courses") from breadcrumbs ';
$string['settings_remove_courses_nav_node_desc'] = '';

// Настройки страницы курса
$string['coursesettings'] = 'Course description page settings';
$string['coursepage_template'] = 'Course description page template';
$string['coursepage_template_help'] = 'Template could be inherited from course category or even plugin settings';
$string['coursepage_template_inherit'] = 'Inherit';
$string['coursepage_template_code_base'] = 'Standard';
$string['additional_categories'] = 'Additional categories';
$string['additional_categories_help'] = 'In Courses showcase the categories mentioned in this field display current course even if there is another category in current course settings';
$string['required_knowledge'] = 'Required skills';
$string['required_knowledge_help'] = 'Skills required to complete this course. Fill in comma separeted. Example: Accounting, Law';
$string['hide_course'] = 'Not to show course in the showcase';
$string['hide_course_help'] = 'Displays this course to administrators only, if this setting is on';
$string['custom_fields_view'] = 'How to display custom course fields';
$string['custom_fields_view_help'] = 'In this setting, you can choose whether to display custom course fields and in what way';
$string['custom_fields_view_default'] = 'Inherit';
$string['custom_fields_view_hide'] = 'Do not display';
$string['custom_fields_view_show'] = 'Display';
$string['coursecat_view'] = 'Course category view';
$string['coursecat_view_help'] = 'Defines how course category must be displayed or not displayed in course description page';
$string['coursecat_view_hide'] = 'Not to display';
$string['coursecat_view_text'] = 'As text';
$string['coursecat_view_link'] = 'As a link';
$string['display_coursetags'] = 'Display course tags';
$string['display_coursetags_help'] = 'Course description page settings';
$string['additional_coursesettings'] = 'Additional course fields settings';
$string['additional_coursecustomsettings'] = 'Editing custom course fields';
$string['course_price'] = 'Course price';
$string['course_price_help'] = 'The data from this field will be displayed in the course cover and the course description page';
$string['additional_description'] = 'Short description';
$string['additional_description_help'] = 'In addition to full description (set in course settings), short description can be set
Short text can be displayed in the showcase and (or) in course description page depending on the setting below';
$string['additional_description_view'] = 'Display short description';
$string['additional_description_view_help'] = 'Where to display short description';
$string['nowhere'] = 'Nowhere';
$string['everywhere'] = 'Everywhere';
$string['coursedesc'] = 'In course description page only';
$string['courselink'] = 'In the showcasw only (if supported)';
$string['course_imgs'] = 'Image and course file display settings';
$string['descriptionimgs'] = 'Images and files for the course description page';
$string['showcaseimgs'] = 'Image for the course cover in the showcase';

$string['sticker'] = 'Course sticker';
$string['sticker_help'] = 'Adds selected sticker to the course tile';
$string['sticker_special_offer'] = 'Discount';
$string['sticker_action_offer'] = 'Offer';
$string['sticker_free_offer'] = 'Free';
$string['sticker_demo'] = 'Demo course';
$string['sticker_card_payment'] = 'Pay with card';
$string['sticker_new'] = 'New';
$string['sticker_bestseller'] = 'Bestseller';
$string['sticker_beginner'] = 'For beginners';

$string['course_difficult'] = 'Difficulty level';
$string['course_difficult_none'] = '';
$string['course_difficult_easy'] = 'Easy';
$string['course_difficult_medium'] = 'Medium';
$string['course_difficult_hard'] = 'Hard';

$string['display_startdate'] = 'Where to show course start date';
$string['display_startdate_help'] = 'Where to show course start date';

$string['display_enrolicons'] = 'Where to show enrollment icons';
$string['display_enrolicons_help'] = 'Where to show enrollment icons';

$string['display_price'] = 'Where to show enrollment the price';
$string['display_price_help'] = 'Where to show enrollment the price';

$string['hide_course_info_page'] = 'Hidden from users';
$string['redirect_all_enrolled_users'] = 'Must be shown only to users unenrolled to the course';
$string['show_course_info_page_for_all_users'] = 'Must be shown to all users who enters the course';
$string['show_course_info_page_default'] = 'Use global setting';

$string['hide_course_contacts'] = 'Hide course contacts in course description page';
$string['hide_course_contacts_help'] = 'Setting allows to hide or show course contacts block in course description page';
$string['hide_course_gallery'] = 'Hide course gallery in course description page';
$string['hide_course_gallery_help'] = 'The setting allows to hide or show course gallery block in course description page';
$string['hide_course_contacts_default'] = 'Use global setting';

// Страница категории
$string['categorysettings'] = 'Course category extra settings';
$string['category_icon'] = 'Category picture';
$string['category_icon_help'] = '';
$string['hide_category'] = 'Hide category';
$string['hide_category_help'] = 'Hide category with all courses that belong to it in Courses showcase. Hidden category can be seen only by administrators';
$string['category_coursepage_template'] = 'Course description page template';
$string['category_coursepage_template_help'] = 'Template could be inherited from plugin settings and overriden on course level';
$string['category_courselist_template'] = 'Courses list template';
$string['category_courselist_template_help'] = 'This setting available when plugin "Universal courses list" in use only. Setting allows to apply sourses list template when user is in category. Setting defined in list courses plugin is used by default.';
$string['courselist_template_inherit'] = 'Inherit';
$string['category_custom_fields_roles'] = 'Structure and display area of custom fields';
$string['category_custom_fields_roles_help'] = '
<div>These settings affect course custom fields edit form and search form.</div>
<div>Indirectly the settings also affect data generation for display with templates but exceptionally for logic preservation. If it is not editable, it means that it is empty and there is no point to display it.</div>
<div>However, the purpose of these settings is not to configure the representation. The representation is configured by mustache templates. Disabled fields do not present in the course description page by default but it might be changed with templates.</div>';
$string['category_custom_field_role_inherit'] = 'Inherit';
$string['category_custom_field_role_field_disabled'] = 'Disable the whole field';
$string['category_custom_field_role_search_disabled'] = 'Exclude from the search form';
$string['category_custom_field_role_search_disabled_sort_enabled'] = 'Exclude from the search form but allow to sort by this field';
$string['category_custom_field_role_search_enabled'] = 'Include in the search form';
$string['category_custom_field_role_search_enabled_sort_enabled'] = 'Include in the search form and in the sort field';
$string['category_custom_field_role'] = 'Custom field display area';
$string['category_custom_field_role_help'] = '
<div>\'Inherit\' - everything is inherited from the plugin settings. The field is always editable and displayable. If the field displayed in the search form or not is defined in the search plugin.</div>
<div>\'Disable the whole field\' - the field is disabled for the category. The field Will not be displayed in the edit form and in the search form. If it is not edited it is empty and hence no point in searching. Will not be shoen in interfaces by default.</div>
<div>\'Exclude from the search form\' - irrespective of what is set in the plugin, the user will see the search form without a filter by this field in this category.</div>
<div>\'Include in the search form\' - irrespective of what is set in the plugin, the user will see the search form with a filter by this field in this category.</div>';

// Страница Витрины курсов
$string['showcase_course_startdate'] = 'Starts: ';
$string['showcase_course_categories_title'] = 'Categories';
$string['top_showcase_course_categories_title'] = 'Categories';
$string['showcase_course_courses_title'] = '{$a->name}';
$string['top_showcase_course_courses_title'] = 'Course list';
$string['no_courses_in_selected_category'] = 'There are co courses in the category selected';
$string['no_courses_was_find'] = 'No courses that would correspond to the search parameters found';
$string['search_results_subheader'] = ' : searching results';
$string['showcase_course_courses_table_courseshortname'] = 'Name in the catalog';
$string['showcase_course_courses_table_coursefullname'] = 'Course name';
$string['showcase_course_courses_table_coursedifficulty'] = 'Difficulty level';
$string['totop'] = 'To the top';
$string['top_paging_description'] = '{$a->perpage} of {$a->totalcount} courses';

// Страница курса
$string['courseblock_course_startdate'] = 'Start date: ';
$string['courseblock_course_enddate'] = 'End date: ';
$string['courseblock_course_price'] = 'Price: ';
$string['courseblock_course_rknowledge'] = 'Required skills: ';
$string['courseblock_course_contacts'] = 'Contacts: ';
$string['enrol_block'] = 'Enroll on the course';
$string['login'] = 'Log in';
$string['coursefiles'] = 'Attached files';
$string['link_viewguestcourse'] = 'Enter as a guest';
$string['link_viewcourse'] = 'Enter the course';
$string['link_login_text'] = 'To enroll on a course you need to';
$string['link_login'] = 'Log in';
$string['link_login_moodle'] = 'Log in';
$string['link_signup_moodle'] = 'Sign up';
$string['course_info'] = 'Information about the course';
$string['message_cant_view_course'] = 'You cannot enroll on this course';

// AJAX
$string['ajax_courseshortname'] = 'Name in the catalog: ';
$string['ajax_coursedifficult'] = 'Difficulty level: ';

// Формы
$string['searchform_name'] = 'Search by course name';
$string['searchform_more'] = 'Advanced search';
$string['searchform_dategroup'] = 'Search by start date';
$string['searchform_priceprice'] = 'Search by price';
$string['searchform_search'] = 'Find';
$string['searchform_sum'] = 'Sum';

// Tools
$string['add_category'] = 'Add a category';
$string['add_course'] = 'Add a course';


$string['crw:addinstance'] = 'Add Courses showcase';
$string['crw:view_hidden_categories'] = 'See hidden categories';
$string['crw:view_hidden_courses'] = 'See hidden courses';
$string['crw:manage_additional_categories'] = 'Manage additional categories';

$string['courses_flow_show_more'] = 'More courses';
$string['courses_flow_loading'] = 'Loading...';

$string['perpager_title'] = 'Display per page:';
$string['perpager_all'] = 'all';

// Страница поиска
$string['search_result'] = 'Search results';

// Общие язковые строки
$string['yes'] = 'Yes';
$string['no'] = 'No';

$string['tags'] = 'Course tags';
$string['tagarea_crw_course_custom1'] = 'custom1 area';
$string['tagcollection_custom1'] = 'custom1';
$string['tagarea_crw_course_custom2'] = 'custom2 area';
$string['tagcollection_custom2'] = 'custom2';

$string['feedback_items_header'] = 'Feedbacks';
$string['feedback_course_unknown'] = 'unknown course';
$string['feedback_item_unknown'] = 'unknown item';
$string['feedback_area_course'] = 'Course';
$string['feedback_area_unknown'] = 'Unknown feedback area';

// Задачи
$string['task_calculation_course_popularity_title'] = 'Calculation of the popularity of the course';
