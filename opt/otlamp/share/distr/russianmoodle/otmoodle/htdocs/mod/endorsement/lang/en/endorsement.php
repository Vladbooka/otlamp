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
 * Plugin strings are defined here.
 *
 * @package     mod_endorsement
 * @category    string
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Course feedback';
$string['modulename'] = 'Course feedback';
$string['modulename_help'] = 'Course Feedback module allows students to provide feedback on the current course. The system sends notifications of new reviews to moderators who, through the Feedback Moderator Panel, can approve or reject student reviews.';
$string['modulenameplural'] = 'Course feedbacks';
$string['endorsementname'] = 'Name';
$string['endorsementname_help'] = '';
$string['pluginadministration'] = 'Course feedback plugin administration';
$string['missingidandcmid'] = 'There are no required data to display page';

$string['endorsement:addinstance'] = 'Add instance of \'course feedback\' plugin into course';
$string['endorsement:moderate_endorsements'] = 'Moderate feedbacks';
$string['endorsement:view_endorsements'] = 'View feedbacks';
$string['endorsement:to_endorse'] = 'To leave feedback';
$string['endorsement:view_new_own_endorsements'] = 'View own feedbacks which are not moderated yet';
$string['endorsement:view_accepted_own_endorsements'] = 'View own feedbacks which were accepted by moderator';
$string['endorsement:view_rejected_own_endorsements'] = 'View own feedbacks which were rejected by moderator';
$string['endorsement:receive_notifications'] = 'Receive notifications';
$string['endorsement:receive_new_endorsement_notification'] = 'Receive new feedback notification';
$string['messageprovider:new_endorsement'] = 'New feedback notification';


$string['endorsement_list'] = 'Feedback list';
$string['user_list_header'] = 'Your feedbacks';
$string['user_items_header'] = 'Your feedbacks';
$string['onemore'] = 'Add new feedback';
$string['endorsement_form_field_endorsement'] = 'Your feedback';
$string['endorsement_form_field_save'] = 'Save';
$string['endorsement_form_field_cancel'] = 'Back';
$string['endorsement_save_failed'] = 'Sorry, we were unable to save your feedback. Try sending it again please.';
$string['endorsement_was_empty'] = 'The text of the feedback was not received. Please make sure you enter a feedback text before submitting the form.';
$string['endorsement_publication_success'] = 'Your feedback has been successfully saved and sent for moderation. Thank you for your interest.';
$string['endorsement_endorse_access_denied'] = 'We\'re sorry. Access denied to add feedback.';

$string['endorsement_list_page_title'] = 'Feedbacks moderator panel';
$string['endorsement_list_page_heading'] = 'Feedbacks moderator panel';
$string['moderator_list_header'] = 'Feedbacks moderator panel';
$string['moderator_items_header'] = 'Feedbacks from all users';
$string['filter_statuses'] = 'Filter:';
$string['filter_courses'] = 'Current course:';

$string['endorsement_status_new'] = 'New';
$string['endorsement_status_rejected'] = 'Rejected';
$string['endorsement_status_accepted'] = 'Accepted';
$string['endorsement_status_all'] = 'Any status';

$string['moderate_link_text'] = 'Feedbacks moderation';
$string['message__new_endorsement__subject'] = 'New feedback in course "{$a->coursefullname}"';
$string['message__new_endorsement__smallmessage'] = 'User {$a->userfullname} left new feedback in "{$a->coursefullname}"';
$string['message__new_endorsement__fullmessage'] = '<p>User {$a->userfullname} has left new feedback in course "{$a->coursefullname}".</p><p>Feedback text is: <br/>"{$a->endorsementcontent}".</p>{$a->moderatelink}';

$string['removeallendorsementfeedbacks'] = 'Remove all feedbacks that were left with this activity';
$string['feedbacks_deleted'] = 'Feedbacks, that were left with activity "{$a}" have been deleted';

$string['feedback_source'] = 'Feedback source';
$string['mod_view_no_data'] = 'Unfortunately, you can’t add or view (moderate) feedbacks.';
