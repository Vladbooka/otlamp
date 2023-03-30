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
 * Настраиваемые поля. Языковые строки.
 *
 * @package    local_mcov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Custom fields for objects';

$string['mcov:edit_cohorts_cov'] = 'Edit custom fields values of cohorts';
$string['mcov:edit_users_cov'] = 'Edit custom fields values of users';
$string['mcov:edit_users_cov_my'] = 'Edit custom fields values of own profile';
$string['mcov:edit_groups_cov'] = 'Edit custom fields values of groups';

$string['back_to_entity'] = '{$a}';
$string['edit_abstract_entity_title'] = 'Editing custom fields';
$string['entity_title'] = '{$a->entity} "{$a->object}"';
$string['edit_entity_title'] = '{$a->entity_title}. {$a->edit_abstract_entity_title}';
$string['fld_submit'] = 'Save';

$string['entity_cohort_plural'] = 'Cohorts';
$string['entity_cohort'] = 'Cohort';
$string['entity_user_plural'] = 'Users';
$string['entity_user'] = 'User';
$string['entity_group_plural'] = 'Groups';
$string['entity_group'] = 'Group';

$string['settings_general'] = 'Custom fields';
$string['settings_title_general'] = 'Settings';
$string['settings_title_general_desc'] = '';
$formdescription = '<div> The configuration array must be described in yaml format </div>
<div> At the zero level of the configuration array, there must be a class key whose value describes the custom fields editing form. </div>
<div> This form should consist of an array describing the form fields. </div>
<div> Each field in the key must have a unique field code, and the value must have an array of properties that describe the form field. </div>
<div> Reserved, named properties: </div>
<ul>
<li> type - the type of the form element </li>
<li> filter - setting the data type for the element </li>
<li> default - the value that should be substituted by default into the form element </li>
<li> repeatgroup - currently not implemented for this tool </li>
<li> rules - currently not implemented for this tool </li>
<li> disabledif - not currently implemented for this tool </li>
<li> autoindex - currently not implemented for this tool </li>
<li> expanded - not yet implemented for this instrument </li>
<li> advanced - not yet implemented for this tool </li>
<li> helpbutton - currently not implemented for this tool </li>
</ul>
<div> The rest of the properties will be passed to the constructor of the form element in the order in which they are declared in the configuration. </div>
<div> Currently, the following field types are available for this tool: </div>
<ul>
<li> text - one line text field </li>
<li> textarea - multi-line text field </li>
<li> select - dropdown list </li>
<li> checkbox - checkbox </li>
<li> date_selector - date </li>
<li> submit is a button for submitting a form. </li>
</ul> ';
$string['settings_cohort_yaml'] = 'Cohorts. Custom fields config';
$string['settings_cohort_yaml_desc'] = $formdescription;

$string['settings_group_yaml'] = 'Groups. Custom fields config';
$string['settings_group_yaml_desc'] = $formdescription;


$string['settings_user_yaml'] = 'Users. Custom fields config';
$string['settings_user_yaml_desc'] = $formdescription;
$string['e_user_fld_local_otcontrolpanel_viewsconfig'] = 'Configuration of the LMS 3KL control panel';

$string['exception_entity_config_empty'] = 'Config is empty';
$string['exception_entity_form_not_set'] = 'Form not set yet';
$string['exception_entity_form_misconfigured'] = 'Form misconfigured';
$string['error_mcov_form'] = 'An error occurred while working<br/>Debug info:<br/>Error code: {$a->errorcode}<br/>Error message: {$a->errormessage}<br/>Stack trace:<br/>{$a->trace}';
$string['error_mcov_has_no_fields_to_edit'] = 'No fields available for you to edit';

$string['e_group_fld_local_mcov_group_datestart'] = 'Group start date';
