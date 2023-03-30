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
 * Тип вопроса Объекты на изображении. Языковые переменные.
 *
 * @package    qtype
 * @subpackage otimagepointer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Базовые переменные
$string['pluginname'] = 'Multi-essay';
$string['pluginname_help'] = '';
$string['pluginname_link'] = 'question/type/otmultiessay';
$string['pluginnameadding'] = 'Add question "Multi-essay"';
$string['pluginnameediting'] = 'Edit the question "Multi-essay"';
$string['pluginnamesummary'] = 'Manually evaluated question type, in which students are required to write several essays';

// Настройка плагина
$string['responseoptions'] = 'Revocation Options';
$string['enablequestion'] = 'Display question?';
$string['innerquestion'] = 'Question text';
$string['responseformat'] = 'Response format';
$string['formateditor'] = 'HTML Editor';
$string['formateditorfilepicker'] = 'HTML editor with file selection';
$string['formatmonospaced'] = 'Normal text, monospaced font';
$string['formatnoinline'] = 'No embedded text';
$string['formatplain'] = 'Plain Text';
$string['responserequired'] = 'Require text';
$string['responseisrequired'] = 'Require student to enter text';
$string['responsenotrequired'] = 'Entering text is optional';
$string['responsefieldlines'] = 'Field size';
$string['nlines'] = '{$a} rows';
$string['attachments'] = 'Allow attachments';
$string['attachmentsrequired'] = 'Attachments are required';
$string['responsetemplateheader'] = 'Revocation template';
$string['responsetemplate'] = 'Response template';
$string['graderinfoheader'] = 'Information about the appraiser';
$string['graderinfo'] = 'Information for evaluators';
$string['attachmentsrequired_help'] = 'This parameter specifies the minimum number of attachments needed to evaluate the response.';
$string['responsetemplate_help'] = 'Any text written here will be entered in the response field when a new attempt is made.';
$string['questionheader'] = 'Question {no}';
$string['addmoreanswers'] = 'Add {no} answer option (s)';
$string['attachmentsoptional'] = 'Attachments are not required';
$string['qtype_otmultiessay_grager_info_block_caption'] = 'Information for evaluators to the question';

$string['mustattach'] = 'When "No embedded text" is selected or the answers are optional, you must allow at least one attachment.';
$string['mustrequire'] = 'When "No embedded text" is selected or the answers are optional, you must allow at least one attachment.';
$string['mustrequirefewer'] = 'You can not claim more attachments than you allowed.';
$string['error_no_active_questions'] = 'No Issues Activated';

// Настройки экземпляра