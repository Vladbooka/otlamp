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
 * Блок топ-10
 *
 * @package    block
 * @subpackage topten
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Top-10';
$string['topten:addinstance'] = 'Add a new block of Top-10';
$string['topten:myaddinstance'] = 'Add a new block of Top-10';

// Настройки
$string['rating_type'] = 'Select a rating to display';
$string['rating_number'] = 'Select the number of items';
$string['hide_rating_title'] = 'Hide block title';
$string['rating_name'] = 'Fill with name of your rating or leave blank for autonaming';

$string['update_cached_data'] = 'Updating cached report data';
$string['report_not_ready'] = 'Report not ready';
$string['report_header'] = 'Top-{$a->number}. {$a->name}';

// Рейтинги
$string['users_coursecompletion'] = 'Users with the maximum number of courses passed';
$string['users_coursecompletion_header'] = 'Courses completed';

$string['courses_rating'] = 'Courses with the highest rating submitted by users';
$string['courses_rating_header'] = 'Rating of courses';

$string['user_selection'] = 'Users according to request settings';
$string['user_selection_header'] = 'Users';

$string['users_xp'] = 'Users with the maximum level in the "XP" Block';
$string['users_xp_header'] = 'Level of experience';

$string['users_activity'] = 'Users with maximum activity in the system in the last 24 hours';
$string['users_activity_header'] = 'User activity for 1 day';

$string['users_dof_achievements'] = 'Users with the highest rating in the given portfolio category';
$string['users_dof_achievements_header'] = 'Portfolio rating';

$string['course'] = 'Course';
$string['fio'] = 'Full name';
$string['rate'] = 'Number';
$string['type_description'] = 'Information';

// Рейтинг "Пользователи с максимальным количеством пройденных курсов"
$string['users_coursecompletion_number'] = 'Courses';
$string['users_coursecompletion_accept_completions_from'] = 'Take courses completed from the specified date (inclusive)';
$string['users_coursecompletion_accept_completions_to'] = 'Take courses completed before the specified date (inclusive)';

// Рейтинг "Пользователи с максимальным количеством пройденных курсов"
$string['courses_rating_rating'] = 'Rating';

// Курсы по заданным критериям
$string['courses_search'] = 'List of courses by selected criteria';
$string['courses_search_header'] = 'Courses found';
$string['courses_search_renderer'] = 'Option for displaying courses';
$string['courses_search_sorttype'] = 'Sort type';
$string['courses_search_sortdirection'] = 'Sort direction';
$string['system_search_button'] = 'Filtering courses';
$string['courses_search_filter_header'] = 'Filter settings';
$string['courses_search_filter_save'] = 'Apply filter';
$string['courses_search_filter_cancel'] = 'Cancel';

// Пользователи с максимальным уровнем в блоке «Опыт!»
$string['users_xp_lvl'] = 'Level';
$string['users_xp_description'] = 'The selected rating displays users with the maximum level in the "Experience!" Block.
Please note that in order to display the rating, you must select the use of the "For the whole site" points.';

// Пользователи с максимальной активностью в системе за последние сутки
$string['users_activity_counter'] = 'Activity';

// Пользователи с максимальным рейтингом в заданной категории портфолио
$string['users_dof_achievements_rating'] = 'Rating';
$string['users_dof_achievements_selectcat'] = 'Select the achievements section';
$string['users_dof_achievements_header_cat'] = 'Portfolio rating by category «{$a}»';
$string['exception_output_fragment_not_found'] = 'Outup fragment not found';
$string['exception_required_paramater_not_specified'] = 'Required parameter not specified';

//Тип - обьект
$string['user_img'] = 'User Image';
$string['fullname'] = 'Full name';
$string['slide_object_timelimit'] = 'refresh Rate';
$string['slide_object_name'] = 'Object';
$string['slide_object_descripton'] = 'Add object of proposed type';
$string['slide_object_formsave_selectobject_label'] = 'object Type';
$string['object_user_base_name'] = 'General information';
$string['object_user_universal_name'] = 'Universal';
$string['object_user_base_description'] = 'The template displays the name of the user, the image of the user, and the description specified in the profile field description (description).
                                            The template does not use custom fields.
                                            Maximum of three cards in a row.';
$string['object_user_universal_description'] = 'The template displays the name of the user, the image of the user.
                                                Label and displayed on the left, the field value is available on the right.
                                                Maximum of three cards in a row.';
$string['object_user_select_field'] = 'Field №{$a}';
$string['object_user_text_field'] = 'Text field №{$a}';
$string['slide_object_formsave_selecttemplate_label'] = 'template Selection';
$string['slide_object_formsave_template_desc'] = 'Template description: {$a}';
$string['custom_template_fields'] = 'Custom template fields';
$string['custom_template_fields_desc'] = 'In this section, you can set the correspondence between user fields and custom fields of the selected report.
                                            For more information about how the information indicated in the fields will be displayed, see the description of the templates indicated above.
                                            The number and composition of the displayed fields depends on the selected template.';
$string['none_description'] = 'No template description';
$string['none_template'] = 'The selected template does not exist.';
$string['filtering'] = 'filter Settings';
$string['groupon'] = 'profile Field';
$string['g_none'] = ' Select...';
$string['groupon_help'] = ' the Specified profile field can be used to filter users.';
$string['filter'] = 'Must match';
$string['filter_help'] = 'the value Specified in this field will be used to filter users (users whose profile field is filled with a value other than the specified one will not be shown)';
$string['softmatch'] = 'Use non-strict match';
$string['softmatch_help'] = 'setting enables softer comparison when filtering: partial match allowed, not case sensitive';
$string['auth'] = 'authorization Method';
$string['lang'] = 'Language';
$string['config_condition_logic'] = 'The logic of applying conditions';
$string['config_condition_logic_and'] = 'All conditions must be met';
$string['config_condition_logic_or'] = 'At least one of the conditions is met';
