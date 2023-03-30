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
 * Плагин формата курса Темы-спойлеры. Языковой пакет.
 *
 * @package    format
 * @subpackage otspoilers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Базовые переменные
$string['currentsection'] = 'This topic';
$string['sectionname'] = 'Topic';
$string['pluginname'] = 'LMS 3KL';

$string['page-course-view-topics'] = 'Any course main page in topics format';
$string['page-course-view-topics-x'] = 'Any course page in topics format';
$string['hidefromothers'] = 'Hide section';
$string['showfromothers'] = 'Show section';
$string['settings_format_opentechnology_base'] = 'Base';
$string['settings_format_opentechnology_spoiler'] = 'Collapsed topics';
$string['settings_format_opentechnology_accordion'] = 'Accordion';
$string['settings_format_opentechnology_carousel'] = 'Carousel';

$string['settings_format_opentechnology_base_elements_view'] = 'Base view';
$string['settings_format_opentechnology_icon_elements_view'] = 'Icon view';
$string['settings_format_opentechnology_base_with_badges_elements_view'] = 'Base view with badges';
$string['settings_format_opentechnology_icon_with_badges_elements_view'] = 'Icon view with badges';


// Страница курса
$string['section_0_name'] = 'Intro';
$string['section_default_name'] = 'Section {$a}';
$string['toggleall_collapse'] = 'Show all';
$string['toggleall_expand'] = 'Hide all';

// Настройки курса
$string['course_settings_sectionsnumber'] = 'The number of sections in the course';
$string['course_settings_hiddensections'] = 'Show hidden sections';
$string['course_settings_hiddensections_collapsed'] = 'Undeployed mode';
$string['course_settings_hiddensections_invisible'] = 'Completely invisible';
$string['course_settings_coursedisplay'] = 'The display of course';
$string['course_settings_coursedisplay_multi'] = 'Show all sections on one page';
$string['course_settings_coursedisplay_single'] = 'Show one sections on one page';
$string['course_settings_caption_align_title'] = 'The alignment of the section headers';
$string['course_settings_caption_align_option_left'] = 'Align left';
$string['course_settings_caption_align_option_center'] = 'Align center';
$string['course_settings_caption_align_option_right'] = 'Align right';
$string['course_settings_caption_align_desc'] = 'The title text will be aligned to the left, center, or right side of the section';
$string['course_settings_caption_align_desc_help'] = 'The title text will be aligned to the left, center, or right side of the section';
$string['course_settings_display_mode_title'] = 'Display mode';
$string['course_settings_display_mode_desc'] = 'Course page display mode';
$string['course_settings_display_mode_desc_help'] = 'Course page display mode';
$string['course_settings_caption_icons_enabled_title'] = 'Icon title section';
$string['course_settings_caption_icons_enabled_desc'] = 'The icon will be displayed in the header section of the course';
$string['course_settings_caption_icons_enabled_desc_help'] = 'The icon will be displayed in the header section of the course';
$string['course_settings_elements_display_mode_title'] = 'Course elements display mode';
$string['course_settings_elements_display_mode_desc'] = 'Course elements display mode';
$string['course_settings_elements_display_mode_desc_help'] = 'If "icon view" enabled, course elements will render as icons. The icon of the course elements will vary depending on the transmission elements onto the course. <br/>
    <b> WARNING </b> <br/>
    It requires the addition of icons for all the modules used : <br/>
    /theme/[theme_name]/pix_plugins/mod/[module_name]/icon_complete<br/>
    /theme/[theme_name]/pix_plugins/mod/[module_name]/icon_fail';
$string['course_settings_caption_icon_toggle_open_title'] = 'Icon of the deployed section';
$string['course_settings_caption_icon_toggle_closed_title'] = 'Icon of the undeployed section';
$string['course_settings_course_display_mode_title'] = 'Course display mode';
$string['settings_format_opentechnology_course_display_mode_0'] = 'Expert mode';
$string['settings_format_opentechnology_course_display_mode_1'] = 'One column';
$string['settings_format_opentechnology_course_display_mode_2'] = 'Two column';
$string['course_settings_header_general'] = 'General settings';
$string['course_settings_header_courseview'] = 'Course view settings';
$string['course_settings_header_sectionview'] = 'Sections view settings';
$string['course_settings_header_modview'] = 'Activities view settings';

// Настройки плагина
$string['settings_default_blocks_region_side_pre_title'] = 'The blocks into region side-pre for new courses';
$string['settings_default_blocks_region_side_pre_desc'] = 'The list of codes blocks separated by commas (for example: search_forums, news_items, calendar_upcoming, recent_activity), that you want to automatically add to the course page when it is created. Blocks will be added in the column on the left, the default column name is side-pre';
$string['settings_region_side_pre_rename_title'] = 'Override code left column position';
$string['settings_region_side_pre_rename_desc'] = 'If the current theme used custom code blocks position, enter the new code to replace the standard side-pre';
$string['settings_default_blocks_region_side_post_title'] = 'The blocks into region side-post for new courses';
$string['settings_default_blocks_region_side_post_desc'] = 'The list of codes blocks separated by commas (for example: search_forums, news_items, calendar_upcoming, recent_activity), that you want to automatically add to the course page when it is created. Blocks will be added in the column on the right, the default column name is side-post';
$string['settings_region_side_post_rename_title'] = 'Override code right column position';
$string['settings_region_side_post_rename_desc'] = 'If the current theme used custom code blocks position, enter the new code to replace the standard side-post';
$string['settings_caption_align_title'] = 'The alignment of the section headers by default';
$string['settings_caption_align_desc'] = 'The alignment setting of header sections in the course';
$string['settings_caption_align_help'] = 'The title text will be aligned to the left, center, or right side of the section';
$string['settings_caption_align_option_left'] = 'Align left';
$string['settings_caption_align_option_center'] = 'Align center';
$string['settings_caption_align_option_right'] = 'Align right';
$string['settings_display_mode_title'] = 'Sections display mode by default';
$string['settings_display_mode_desc'] = 'Sections display mode by default';
$string['settings_display_mode_help'] = '';
$string['settings_caption_icons_enabled_title'] = 'The icon will be displayed in the header section by default';
$string['settings_caption_icons_enabled_desc'] = 'If enable, it will display the icons minimize/maximize sections in the course by default';
$string['settings_caption_icons_enabled_desc_help'] = '';
$string['settings_elements_display_mode_title'] = 'Course elements display mode';
$string['settings_elements_display_mode_desc'] = 'Course elements display mode';
$string['settings_elements_display_mode_desc_help'] = 'If "icon view" enabled, course elements will render as icons. The icon of the course elements will vary depending on the transmission elements onto the course. <br/>
    <b> WARNING </b> <br/>
    It requires the addition of icons for all the modules used : <br/>
    /theme/[theme_name]/pix_plugins/mod/[module_name]/icon_complete<br/>
    /theme/[theme_name]/pix_plugins/mod/[module_name]/icon_fail';
$string['settings_caption_icon_open_title'] = 'Icon of the deployed section by default';
$string['settings_caption_icon_open_desc'] = '';
$string['settings_caption_icon_open_desc_help'] = '';
$string['settings_caption_icon_closed_title'] = 'Icon of the undeployed section by default';
$string['settings_caption_icon_closed_desc'] = '';
$string['settings_caption_icon_closed_desc_help'] = '';

$string['settings_section_width'] = 'Section width (by default)';
$string['settings_section_width_help'] = 'When you create a course with the format "LMS 3KL" will default to the value vybano section width. The selected value can be overridden at the level of the course settings or in any of the sections of the course. The width of the sections is indicated in percentage of the width of the block with a course content. ';
$string['settings_section_lastinrow'] = 'End section (by default)';
$string['settings_section_lastinrow_help'] = 'When you create a course with the format "LMS 3KL" will default to the value vybano section is completed. The selected value can be overridden at the level of the course settings or in any of the sections of the course. Section up to date are grouped into sections. Each section begins with a new line. If you choose to display in the form of roundabouts, then on each slide is displayed only one line.';
$string['settings_section_summary_width'] = 'Section description width (by default)';
$string['settings_section_summary_width_help'] = 'When you create a course with the format "LMS 3KL" will default to the value vybano sections describe the width. The selected value can be overridden at the level of the course settings or in any of the sections of the course. The width of a section indicated as a percentage of the section width.';

$string['course_settings_section_width'] = 'Section width (by default)';
$string['course_settings_section_width_help'] = 'width sections as a percentage of the width of the block from the course content and is the default value for the sections in this course, that is affects the display only if the width is not redefined in the section itself';
$string['course_settings_set_section_width'] = 'Forse section width';
$string['course_settings_set_section_width_help'] = 'This option allows you to push in all sections of this course the value of the section width specified in the option "Width sections (default)"';
$string['course_settings_section_lastinrow'] = 'End section (by default)';
$string['course_settings_section_lastinrow_help'] = 'This option is the default value for the sections in this course, that is affects the display only if the conclusion is not overridden in the section itself. Section up to date are grouped into sections. Each section begins with a new line. If you choose to display in the form of roundabouts, then on each slide is displayed only one line. ';
$string['course_settings_set_section_lastinrow'] = 'Forse section ending';
$string['course_settings_set_section_lastinrow_help'] = 'This option allows you to push in all sections of this section of the course completion specified in the "Do section (default) Terminate" option. Section up to date are grouped into sections. Each section begins with a new line. If you choose to display in the form of roundabouts, then on each slide is displayed only one line.';
$string['course_settings_section_summary_width'] = 'Section description width (by default)';
$string['course_settings_section_summary_width_help'] = 'Description section width as a percentage of the width of the section and is the default value for the sections in this course, that is affects the display only if the width of the description is not redefined in the section itself';
$string['course_settings_set_section_summary_width'] = 'Force description width (by default)';
$string['course_settings_set_section_summary_width_help'] = 'This option allows you to push in all sections of this course the value of the Description section of the width specified in the options "Description section width (default)"';

// Настройки секции
$string['course_section_settings_section_width'] = 'Section width';
$string['course_section_settings_section_width_help'] = 'width sections as a percentage of the width of the block from the course content';
$string['course_section_settings_section_lastinrow'] = 'End section';
$string['course_section_settings_section_lastinrow_help'] = 'This option allows you to set zavreshenie section after the selected section. Section up to date are grouped into sections. Each section begins with a new line. If you choose to display in the form of roundabouts, then on each slide is displayed only one line.';
$string['course_section_settings_section_summary_width'] = 'Section description width';
$string['course_section_settings_section_summary_width_help'] = 'width of a section is indicated as a percentage of section width';

$string['slideprev'] = "Preivous";
$string['slidenext'] = "Next";