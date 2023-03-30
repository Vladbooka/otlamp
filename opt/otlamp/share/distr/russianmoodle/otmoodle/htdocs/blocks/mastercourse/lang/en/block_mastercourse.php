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
 * Strings for component 'block_mastercourse', language 'en'
 *
 * @package    block_mastercourse
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Master Course Coordination';

$string['mastercourse:addinstance'] = 'Add the "Master Course Coordination" block to My home';
$string['mastercourse:myaddinstance'] = 'Add the "Master Course Coordination" block';
$string['mastercourse:request_verification'] = 'Request verification of mastercourse for discipline';
$string['mastercourse:respond_requests'] = 'Verify mastercourse for discipline';
$string['mastercourse:receive_notification_of_requests'] = 'Receive notifications of requested verifications';
$string['mastercourse:receive_notification_of_responses'] = 'Receive notifications of verification responses';
$string['mastercourse:view_mastercourse'] = 'See the link to the master course';

$string['mastercourse_title'] = 'Master course';
$string['mastercourse_verification_requested_message'] = '{$a->initiator} sent the current version of the master course "{$a->course}" to check for discipline "{$a->discipline}"';
$string['mastercourse_accepted_mail_text'] = 'The version of the master course submitted for verification "{$a->course}" approved for discipline "{$a->discipline}"';
$string['mastercourse_declined_mail_text'] = 'The version of the master course submitted for verification "{$a->course}" rejected for discipline "{$a->discipline}"';

$string['config_display_navbar_caption'] = 'Navbar caption';
$string['config_display_navbar_caption_desc'] = 'Display mastercourse-caption in navbar (breadcrumbs)';
$string['config_display_verification_panel_caption'] = 'Verification panel caption';
$string['config_display_verification_panel_caption_desc'] = 'Display mastercourse-caption in verification panel';

$string['mastercourse:manage_publication'] = 'Manage course publication';
$string['config_display_publication_panel_caption'] = 'Display publication panel';
$string['config_display_publication_panel_caption_desc'] = '';

$string['page__publication'] = 'Course publication';
$string['page__publication__back_to_the_course'] = 'Back to the course';

$string['service__nmfo'] = 'Portal of continuous medical and pharmaceutical education of the Ministry of Health of Russia';
$string['service__nmfo__status__not_published'] = 'Not published';
$string['service__nmfo__status__sent_for_publication'] = 'Sent for publication';
$string['service__nmfo__status__on_review'] = 'On review';
$string['service__nmfo__status__published'] = 'Published';
$string['service__nmfo__status__rejected'] = 'Rejected';
$string['service__nmfo__status__sent_to_unpublish'] = 'Sent to unpublish';
$string['service__nmfo__status__error'] = 'Error';

$string['form_publication__field__submit'] = 'Send';
$string['form_publication__field__current_status'] = 'Current status';
$string['form_publication__field__new_status'] = 'New status';
$string['current_status_wrapper'] = 'Current is: {$a}';

$string['form_publication__error__status_not_available'] = 'Requested status is not available';

$string['course_publication_only'] = 'Course publishing is provided only for blocks added directly to the course.';
