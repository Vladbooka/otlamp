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
 * Плагин определения заимствований Антиплагиат. Языковые переменные.
 *
 * @package    plagiarism
 * @subpackage apru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Antiplagiat plagiarism plugin';
$string['apru'] = 'Antiplagiat';
$string['apru:enable'] = 'Enable Antiplagiat';
$string['apru:viewsimilarityscore'] = 'View Similarity Score';
$string['apru:viewfullreport'] = 'View Originality Report';
$string['apru:enableindexstatus'] = 'Add documents to Antiplagiat\'s index';
$string['apru:disableindexstatus'] = 'Delete documents to Antiplagiat\'s index';

$string['apruconfig'] = '"Antiplagiat" Plagiarism Plugin Configuration';
$string['aprudefaults'] = '"Antiplagiat" plagiarism plugin default settings';
$string['aprupluginsettings'] = '"Antiplagiat" plagiarism plugin settings';
$string['checklistresource'] = 'Use "{$a}" search index';
$string['checklist_wikipedia'] = 'Wikipedia';
$string['checklist_internet'] = 'Internet';
$string['config'] = 'Configuration';
$string['config:host'] = 'Hostname';
$string['config:companyname'] = 'Company name';
$string['config:siteurl'] = 'URL for generating reports';
$string['config:siteurl_help'] = 'URL for generating reports (default is: http://COMPANYNAME.antiplagiat.ru)';
$string['configupdated'] = 'Configuration updated';
$string['defaults'] = 'Default Settings';
$string['defaultsdesc'] = 'The following settings are the defaults set when enabling "Antiplagiat" within an Activity Module';
$string['defaultupdated'] = '"Antiplagiat" defaults updated';
$string['defaultupdateerror'] = 'There was an error when trying to update a default setting value in the database';
$string['estimatedwait'] = 'Estimated wait time: {$a} seconds';
$string['noconnection'] = 'No connection...';
$string['originality'] = 'Score: {$a}%';
$string['processingyet'] = 'Processing...';
$string['processingfailed'] = 'Processing failed';
$string['notupload'] = 'Uploading...';
$string['reportlink'] = 'Link to the report';
$string['studentreports'] = 'Display Originality Reports to Students';
$string['studentreports_help'] = 'Allows you to display "Antiplagiat" originality reports to student users. If set to yes the originality report generated by "Antiplagiat" are available for the student to view.';
$string['submissioncheck'] = 'Uploaded submission will be checked for the similarity index percentage in "Antiplagiat" system';
$string['use_assignment'] = 'Use in "Assignment" module';
$string['use_forum'] = 'Use in "Forum" module';
$string['use_workshop'] = 'Use in "Workshop" module';
$string['useapru'] = 'Enable "Antiplagiat"';
$string['useapru_mod'] = 'Use in "{$a}" module';
$string['otapi'] = 'Tarif plan';
$string['setting_mod_assign_confirmation_required'] = 'Require submission blocking';
$string['setting_docs_for_check'] = 'Quantity of documents sended to check in Antiplagiat at a time';
$string['setting_docs_for_update'] = 'Quantity of documents sended to synchronize at a time';

$string['notice_author_not_set'] = 'Author not set';
$string['attribute_name_onlinexext'] = 'Text, added by activity {$a}';
$string['attribute_name_file'] = 'File, added by activity {$a}';

/** OT serial **/
$string['pageheader'] = 'Obtaining a serial number';
$string['otkey'] = 'secret key';
$string['otserial'] = 'serial number';

$string['get_otserial'] = 'Get serial number';
$string['get_otserial_fail'] = 'Attempt to get LMS 3KL serial number failed. Server reported an error: {$a}';
$string['reset_otserial'] = "Reset serial number";
$string['already_has_otserial'] = 'You already have the serial number, there is no need to get another one.';
$string['already_has_serial'] = 'You already have the serial number, there is no need to get another one.';
$string['otserial_check_ok'] = 'Serial number is valid.';
$string['otserial_check_fail'] = 'Serial number is invalid. Server reported error: {$a}. Try calling to technical support.';
$string['otserial_tariff_wrong'] = "Current tariff is unavailabla for this product. Please contact to technical support service.";

$string['otservice'] = 'Tariff: <u>{$a}</u>';
$string['otservice_send_order'] = "Submit the request on service";
$string['otservice_renew'] = 'Submit the request on renewal';
$string['otservice_change_tariff'] = 'Submit the request on tariff change';

$string['otservice_expired'] = 'Tariff time expired. If you want to reactivate the support, please contact with OpenTechnology manager.';
$string['otservice_active'] = 'Tariff is active and expires at {$a}';
$string['otservice_unlimited'] = 'Tariff valid for unlimited';

$string['demo_settings'] = 'Please, change your tarif for configurating plugin';

/** Errors **/
$string['error_checkservices'] = 'One of the following index of citing documents available for inspection: {$a}';
$string['error_document_not_found'] = 'Document {$a} is not found';
$string['error_document_externalid_not_set'] = 'External document identifier is not specified';
$string['error_access_enableindexstatus_denied'] = 'You do not have access to the document in addition Index';
$string['error_access_disableindexstatus_denied'] = 'You do not have access to delete documents from Index';
$string['error_document_index_status_not_changed'] = 'The status of indexing a document has not been modified';
$string['error_hashfile_not_found'] = 'A file with the specified hash {$a} is not found';
$string['error_hashfile_is_directory'] = 'A file with the specified hash {$a} is a folder';
$string['error_adding_file_to_queue'] = 'A file with the specified hash {$a} is not added to the queue for loading in Antiplagiat system';
$string['error_documenttype_not_supported'] = 'Document type {$a} is not supported Antipagiat system';
$string['error_connection'] = 'Failed to connect with the service Antiplagiat';
$string['error_connection_upload_file'] = 'Error loading the document in the service Antiplagiat';
$string['error_service_checking_document'] = 'Error setting document for review in Antiplagiat service';
$string['error_service_deleting_document'] = 'Failed to delete the document in the Anti-plagiarism service';
$string['error_service_uploading_document'] = 'Error loading the document in the service Antiplagiat';
$string['error_service_getting_document_checkstatus'] = 'Failed to get the verification document data when Antiplagiat';
$string['error_service_getting_document_report'] = 'Failed to get a report on the document when Antiplagiat';
$string['error_service_getting_enumerate_documents'] = 'Failed to get a set of documents from the service Antiplagiat';
$string['error_service_get_tariff_info'] = 'Failed to get tariff info';

$string['save'] = 'Save';


$string['index_status_select_enable'] = 'Document in the antiplagiat index';
$string['index_status_select_disable'] = 'Document not in the antiplagiat index';

$string['event_set_indexed_status_title'] = 'Changing document index status event';
$string['event_set_indexed_status_desc'] = 'Changing document index status event. Document ID: {$a}';
$string['event_send_document_title'] = 'Document sending';
$string['event_send_document_desc'] = 'The Document with ID {$a} has been sent to the "Antiplagiat" service ';

$string['task_send_documents_title'] = 'Uploading documents to the plagiarism system';
$string['task_check_documents_title'] = 'Start checking process by document';
$string['upload_successful'] = 'Successful uploading of document with external identifier {$a}';
$string['upload_failed'] = 'Uploading document with internal identifier {$a} failed';

/** tarif info **/
$string['apru_tarif_name'] = 'Tarif plan';
$string['apru_tarif_subscriptiondate'] = 'Subscription date';
$string['apru_tarif_expirationdate'] = 'Expiration date';
$string['apru_tarif_totalcheckscount'] = 'Total checks count';
$string['apru_tarif_remainedcheckscount'] = 'Remained checks count';
$string['apru_tarif_no_information'] = 'Information is not available';
$string['apru_tarif_get_information_failed'] = 'Get information failed';
$string['apru_tarif_connection_failed'] = 'Connection eror';
$string['apru_tarif_tarif_information'] = 'Rate info';
$string['apru_update_reporturl'] = 'Link to the document verification report ID={$a->id} was updated - {$a->reporturl}';

/** Таски **/
$string['task_update_documents_title'] = 'Synchronize data';

/** Страница просмотра задания **/
$string['add_to_index'] = 'Добавить в индекс';
$string['remove_from_index'] = 'Убрать из индекса';
