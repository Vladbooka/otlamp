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
 * Language strings.
 *
 * @package    availability_otcomparison
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Restriction according to comparison results';
$string['description'] = 'Allows only those users who meet the data comparison criteria';
$string['title'] = 'Comparison';

$string['choose_source'] = 'Choose source...';
$string['choose_preprocessor'] = 'Choose preprocessor...';
$string['choose_operator'] = 'Choose operator...';

$string['preprocessor_date'] = 'Comparison of date source with your value';
$string['preprocessor_days'] = 'Comparison of difference between source and now in days with your value';
$string['preprocessor_int'] = 'Comparison of integer source with your value';

$string['operator_less_than'] = '< (less than)';
$string['operator_more_than'] = '> (more than)';
$string['operator_equal_to'] = '== (is equal to)';
$string['operator_not_equal_to'] = '!= (is not equal to)';
$string['operator_less_than_or_equal'] = '<= (less than or equal)';
$string['operator_more_than_or_equal'] = '>= (more than or equal)';

$string['date_example'] = 'Date examples: {$a}';
$string['days_explanation'] = 'A negative value is allowed in the value field.';

$string['retrieve_value_failed'] = 'unknown (retrieve value was failed)';
$string['retrieve_source_failed'] = 'source "$a" (retrieve was failed)';
$string['invalid_date'] = '? (date value is invalid)';
$string['invalid_int'] = '? (integer value is invalid)';
$string['error_selectsource'] = 'Select source.';
$string['error_selectoperator'] = 'Select comparison operator.';
$string['error_selectpreprocessor'] = 'Select comparison option.';
$string['error_fillvalue'] = 'Fill value.';
$string['error_invalidfilledvalue'] = 'Invalid filled value.';

$string['description_date'] = '"{$a->source}" {$a->operator} {$a->amount}';
$string['description_days'] = 'difference between "{$a->source}" and now {$a->operator} {$a->amount} (in days)';
$string['description_int'] = '"{$a->source}" {$a->operator} {$a->amount}';

$string['timecreated'] = 'timecreated';
$string['firstaccess'] = 'firstaccess';
$string['lastlogin'] = 'lastlogin';
$string['currentlogin'] = 'currentlogin';
$string['lastaccess'] = 'lastaccess';
$string['timemodified'] = 'timemodified';
