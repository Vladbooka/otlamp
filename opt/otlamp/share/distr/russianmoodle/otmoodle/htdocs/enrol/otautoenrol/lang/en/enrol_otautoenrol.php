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
 * otautoenrol enrolment plugin.
 *
 * This plugin automatically enrols a user onto a course the first time they try to access it.
 *
 * @package    enrol
 * @subpackage otautoenrol
 * @date       July 2013
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Auto Enrol ';
$string['pluginname_desc'] = 'The automatic enrolment module allows an option for logged in users to be automatically granted entry to a course and enrolled. This is similar to allowing guest access but the students will be permanently enrolled and therefore able to participate in forum and activities within the area.';

$string['config'] = 'Configuration';
$string['general'] = 'General';
$string['filtering'] = 'User Filtering';

$string['warning'] = 'Caution!';
$string['warning_message'] = 'Adding this plugin to your course will allow any registered Moodle users access to your course. Only install this plugin if you want to allow open access to your course for users who have logged in.';

$string['role'] = 'Role';
$string['role_help'] = 'Power users can use this setting to change the permission level at which users are enrolled.';

$string['method'] = 'Enrol When';
$string['m_site'] = 'Logging into Site';
$string['m_course'] = 'Loading the Course';
$string['method_help'] = 'Power users can use this setting to change the plugin\'s behaviour so that users are enrolled to the course upon logging in rather than waiting for them to access the course. This is helpful for courses which should be visible on a users "my courses" list by default.';

$string['groupon'] = 'Group By';
$string['g_none'] = 'Select...';
$string['g_auth'] = 'Auth Method';
$string['g_dept'] = 'Department';
$string['g_email'] = 'Email Address';
$string['g_inst'] = 'Institution';
$string['g_lang'] = 'Language';
$string['groupon_help'] = 'otautoenrol can automatically add users to a group when they are enrolled based upon one of these user fields.';

$string['countlimit'] = 'Limit';
$string['countlimit_help'] = 'This instance will count the number of enrolments it makes on a course and can stop enrolling users once it reaches a certain level. The default setting of 0 means unlimited.';

$string['alwaysenrol'] = 'Always Enrol';
$string['alwaysenrol_help'] = 'Note that the load on the server can greatly increase! When set to Yes the plugins will always enrol users, even if they already have access to the course through another method.';

$string['softmatch'] = 'Soft Match';
$string['softmatch_help'] = 'When enabled otautoenrol will enrol a user when they partially match the "Allow Only" value instead of requiring an exact match. Soft matches are also case-insensitive. The value of "Filter By" will be used for the group name.';

$string['instancename'] = 'Custom Label';
$string['instancename_help'] = 'You can add a custom label to make it clear what this enrolment method does. This option is most useful when there are multiple instances of otautoenrol on one course.';

$string['filter'] = 'Allow Only';
$string['filter_help'] = 'When a group focus is selected you can use this field to filter which type of user you wish to enrol onto the course. For example, if you grouped by authentication and filtered with "manual" only users who have registered directly with your site would be enrolled.';


$string['has_groups_field'] = 'Add to groups found in profile field (blocks other ways to organize group membership)';
$string['has_groups_field_help'] = 'You can store in the user profile field a list of groups to which you want to subscribe';
$string['groups_field'] = 'Profile field containing groups';
$string['groups_field_help'] = '<div>The profile field value must match the rules described by the regular expression.</div>
<div>It is possible to edit the regex below.</div>
<div>Without edits, you can use the default logic:
<ul>
<li>to list multiple groups, they must be separated by commas</li>
<li>in addition to the group, the course must be specified</li>
<li>the entry where the course will be different from the current one (to which the given way of recording was added) will be skipped</li>
<li>to identify the group, the value of the "Group ID number" field, specified through the group editing interface (idnumber), is used</li>
<li>the value of the "Course short name" field is used to identify the course</li>
<li>group and course must be separated by "@" symbol like {group_idnumber}@{course_shortname}</li>
<li>found values ​​for groups and courses will be cut off at the edges if they contain empty characters (spaces)</li>
</ul>
</div>
';
$string['groups_field_autocreate'] = 'Automatically create a group if one does not exist in the course';
$string['groups_field_edit_regex'] = 'Edit regex to search for groups';
$string['groups_field_regex'] = 'Regular expression to find groups';
$string['groups_field_regex_help'] = '<div> When editing a regular expression, follow these tips and rules:
<ul>
<li> Separation of several groups should be performed using a regular expression, the expected result is: each match is a pair (course + group) to which the user should be subscribed (in the example, the default separator is a comma) </li>
<li> Each found match must include <strong> named </strong> regular expression groups (named capture groups) to identify the group and course </li>
<li> There are the following named capture group naming conventions to identify groups:
    <ul>
        <li> group_idnumber - identification by idnumber </li>
        <li> group_name - identification by group name (name field) </li>
    </ul>
</li>
<li> There are the following named capture group naming conventions to identify the course:
    <ul>
        <li> course_shortname - identification by the short name of the course (shortname) </li>
        <li> course_idnumber - identification by course identification number (idnumber) </li>
    </ul>
</li>
</ul>
</div>';

$string['addtogroup_by_profile_field_name'] = 'Create a group according to the filter and subscribe a user to it';

$string['auto'] = 'Auto';
$string['auto_desc'] = 'This group has been automatically created by the Auto Enrol plugin. It will be deleted if you remove the Auto Enrol plugin from the course.';

$string['defaultrole'] = 'Default role assignment';
$string['defaultrole_desc'] = 'Select role which should be assigned to users during automatic enrolments';
$string['otautoenrol:config'] = 'Configure automatic enrolments';
$string['otautoenrol:method'] = 'User can enrol users onto a course at login';
$string['otautoenrol:unenrol'] = 'User can unenrol otautoenrolled users';
$string['otautoenrol:unenrolself'] = 'User can unenrol themselves if they are being enrolled on access';

$string['unenrolselfconfirm'] = 'Do you really want to unenrol yourself from course "{$a}"? You can revisit the course to be reenrolled but information such as grades and assignment submissions may be lost.';

$string['emptyfield'] = 'No {$a}';

$string['removegroups'] = 'Remove groups';
$string['removegroups_desc'] = 'When an enrolment instance is deleted, should it attempt to remove the groups it has created?';

$string['addtogroup'] = 'Add users to existing groups';
$string['groupping'] = 'Groups membership';
$string['random_distribution_by_groups'] = 'Random distribution for selected existing groups';
$string['auth'] = 'Authorization method';
$string['lang'] = 'Language';

$string['only_one_empty_value'] = 'There can be only one empty condition in the form, according to which distribution will occur!';
$string['unenrol_users'] = 'Unenrol users from courses if they no longer meet the subscription conditions';

$string['servertype'] = 'Operational efficiency';
$string['servertype_desc'] = 'The promptness of the response affects how quickly the conditions for subscribing and unsubscribing from users will work.
If the server is weak, the conditions will be checked only when entering the course or logging on to the system.
With an average server the conditions will be checked through the task scheduler (Default every day at 23:30).
With a powerful server, the conditions will be checked against the profile editing events.
If you are not sure which server you have, we recommend setting "Medium Server" for the correct operation of the system.';

$string['low_server'] = 'Weak';
$string['medium_server'] = 'Medium';
$string['powerfull_server'] = 'Powerful';

$string['task_enrol_users'] = 'Enrol users to courses';

