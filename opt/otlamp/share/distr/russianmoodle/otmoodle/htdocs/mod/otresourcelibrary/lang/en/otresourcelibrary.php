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
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Resource library';
$string['modulename'] = 'Resource library';
$string['modulenameplural'] = 'Resource libraries';
$string['pluginadministration'] = 'Resource library plugin administration';
$string['missingidandcmid'] = 'There are no required data to display page';
$string['modulename_help'] = 'The resource library is a universal module for integrating external resources into the Moodle.';

$string['otresourcelibrary:view'] = 'The right to see an element of the Resource Library course';
$string['otresourcelibrary:addinstance'] = 'The right to add the "Resource Library" module to the course';
$string['otresourcelibrary:viewbyparameter'] = 'The right to write material using the parameters in the link';

$string['library_elemenrt_name'] = 'Name';
$string['short_description'] = 'Short description';
$string['materialtypes'] = 'Material View Type';

$string['no_selected_material'] = 'Not set yet';
$string['material_pagenum'] = 'Page numb';
$string['material_chapter'] = 'Paragraph Code / Title';
$string['material_fragment'] = 'fragment';
$string['sourcename'] = 'Source name';
$string['resourceid'] = 'Resource id';
$string['resource'] = 'Resource';
$string['display_point'] = 'Display point in source material';
$string['search'] = 'Search';
$string['search_placeholder'] = 'material name or identifier';
$string['your_location'] = 'Your location is:';
$string['additional_sort'] = 'Additional sort fields';
$string['more'] = 'Show more';
$string['select_all'] = 'All resources';
$string['not_selected'] = 'All resources and categories';
$string['no_data'] = 'Nothing found';
$string['search_heder_text'] = 'Searching results:';
$string['search_result_text'] = 'To display the search results, you need to select the section \ category or use the search bar to search in all sections.';
$string['view_by_parameter'] = 'You do not have permission to view the material using the parameters in the link';

$string['modal_form_header'] = 'Data source options';
$string['preview_header'] = 'Material preview';
$string['section_selection'] = 'ategory selection';
$string['modal_form_save'] = 'Apply filter';
$string['modal_form_cancel'] = 'Cancel';
$string['return'] = 'Return';
$string['otresourcelibrary_settings_button'] = 'Material settings';
$string['go_to_view_btn'] = 'Go to view';
$string['select_btn'] = 'Select';

$string['mod_form_updated'] = 'Resource Library Settings Updated';
$string['mod_form_created'] = 'Resource Library Settings Created';

$string['manage_source'] = 'Source management';
$string['source_type'] = 'Source type';
$string['source_changes'] = 'Save source changes  {$a}';
$string['source_deletion'] = 'Confirm source deletion  {$a}';
$string['name_source'] = 'Name the source';
$string['add_source'] = 'Add source';
$string['activity_source'] = 'Source activity';
$string['activity_source_active'] = 'Active';
$string['activity_source_inactive'] = 'Inactive';
$string['save_sources_activity'] = 'Save activity settings';
$string['edit_source'] = 'Edit source';
$string['delete_source'] = 'Delete source';
$string['source_types'] = 'Available Source Types';
$string['adding_source'] = 'Adding a source';

$string['error_get_content'] = 'Could not get content. You may not have enough rights to view the current content.';
$string['error_delete_source'] = 'Failed to delete source';
$string['error_edit_source'] = 'Failed to edit source';
$string['error_save_details'] = 'Failed to save details';
$string['error_anchor_not_supported'] = 'The specified anchor type is not supported';
$string['wrong_param_khipu_setting'] = 'The parameters specified in the material settings are not correct';
$string['empty_khipu_setting'] = 'Parameters not set in material settings';
$string['no_material'] = 'Material not defined or missing';
$string['error_response_malformed'] = 'Invalid server response (Probably the resource library is not configured)';
$string['error_executing_request'] = 'An error occurred while executing the request.';
$string['error_save_sources_activity'] = 'Failed to save information about activity sources';

$string['settings_otserial'] = 'Тарифный план';
$string['already_has_serial'] = 'Серийный номер уже был получен';
$string['reset_otserial'] = 'Сбросить серийный номер';
$string['otserial_check_fail'] = 'Серийный номер не прошел проверку на сервере.
Причина: {$a}. Если Вы считаете, что этого не должно было
произойти, пожалуйста, обратитесь в службу технической поддержки.';
$string['otkey'] = 'secret key';
$string['otserial'] = 'serial number';
$string['otserial_check_ok'] = 'Серийный номер действителен.';
$string['get_otserial'] = 'Get serial number';
$string['get_otserial_fail'] = 'Attempt to get LMS 3KL serial number failed. Server reported an error: {$a}';
$string['otservice'] = 'Тарифный план: <u>{$a}</u>';
$string['otserial_tariff_wrong'] = "Тарифный план недоступен для данного продукта. Обратитесь в службу технической поддержки.";
$string['otservice_expired'] = 'Срок действия Вашего тарифного плана истёк. Если Вы желаете продлить срок, пожалуйста, свяжитесь с менеджерами ООО "Открытые технологии".';
$string['otservice_active'] = 'Тарифный план действителен до {$a}';
$string['otservice_unlimited'] = 'Тарифный план действует бессрочно';

$string['settings_sources'] = 'Источники данных';

$string['otapi_exception'] = '{$a}';

$string['edit_src'] = '{$a->sourcename}';

$string['info_result_was_limited'] = 'The selection result was limited; no more than 99 positions will be displayed for each resource.';
$string['installation_sources_names_nodata'] = 'Failed to get the list of available sources. Check your <a href="/admin/settings.php?section=mod_otresourcelibrary_otserial">billing plan</a> and report the situation to the technical support staff.';
$string['implemented_sourcetypes_nodata'] = 'You are not able to add new sources. To get this opportunity, you need to change your <a href="/admin/settings.php?section=mod_otresourcelibrary_otserial">billing plan</a>.';
