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
 * Блок мессенджера курса. Языковые переменные.
 *
 * @package    block_coursemessage
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Ask the teacher';

$string['coursemessage:send'] = 'Sending messages';
$string['coursemessage:addinstance'] = 'Adding a block';
$string['coursemessage:myaddinstance'] = 'Adding a block to your personal cabinet';

/** Block configuration **/
$string['config_header'] = 'Block settings';
$string['config_userfields'] = 'User field identifiers to display';
$string['config_userfields_desc'] = 'Description of configuring identifiers for display';
$string['config_userfields_desc_help'] = 'By default, the users full name and photo are displayed for each contact in the course,
                if you want to display data from user fields, it is enough to specify their keys separated by commas.';
$string['config_display_header'] = 'Display block header';
$string['config_display_header_desc'] = 'Display block header with header';
$string['config_recipientselectionmode'] = 'Select a method for determining message recipients';
$string['config_useglobal'] = 'Use global setting';
$string['config_sendtoall'] = 'Send to all contacts';
$string['config_allowuserselect'] = 'Allow user to select contact themselves';
$string['config_automaticcontact'] = 'Automatic contact detection';
$string['config_senduserinfo'] = 'Supplement student message with course and group information';
$string['config_recipientselectionmode_desc'] = 'Description of methods for determining message recipients';
$string['config_recipientselectionmode_desc_help'] = 'Automatic contact detection - the system will distribute the load among teachers,
                by sending the message to the course contacts one at a time. In the case of a group mode, the load will be distributed between teachers from student groups. <br>
                Send to all contacts - The message will be sent to all contacts in the course, taking into account the group mode.
                If the user is not a member of groups, and the group mode is active in the course settings, the recipients of the message will be only those contacts of the course who are not in groups. <br>
                Allow the user to select a contact himself - the user will be able to select one message recipient from the course contacts, taking into account the group mode.';

/** Global configuration **/
$string['config_block_coursemessage_recipientselectionmode'] = 'Select a method for determining message recipients';
$string['config_block_coursemessage_recipientselectionmode_desc'] = 'Description of methods for determining message recipients:<br>
                Automatic contact detection - the system will distribute the load among teachers,
                by sending the message to the course contacts one at a time. In the case of a group mode, the load will be distributed between teachers from student groups. <br>
                Send to all contacts - The message will be sent to all contacts in the course, taking into account the group mode.
                If the user is not a member of groups, and the group mode is active in the course settings, the recipients of the message will be only those contacts of the course who are not in groups. <br>
                Allow the user to select a contact himself - the user will be able to select one message recipient from the course contacts, taking into account the group mode.';

/** Block **/
$string['description_automaticcontact'] = 'Use the form below to send a message (the teacher will be identified automatically)';
$string['description_allowuserselect'] = 'Select a teacher from the list to send your message';
$string['description_sendtoall'] = 'Use the form below to send a message to all listed teachers';
$string['no_contacts'] = 'No contacts';


/** The form **/
$string['form_send_message_desc'] = 'Question:';
$string['form_send_submit'] = 'Send';
$string['form_send_signature_course'] = '<hr> Course: {$a->course}';
$string['form_send_signature_all'] = '<hr> Course: {$a->course} <br> Group: {$a->groups}';

/** Notices **/
$string['message_form_send_message_send_success'] = 'The message was sent';

/** Errors **/
$string['error_form_send_message_send_error'] = 'Errors occurred while sending the message';
$string['error_form_send_receiver_not_set'] = 'Recipient not installed';
$string['error_form_empty_message'] = 'Blank message';
$string['error_form_send_capability'] = 'You do not have permission to post';
