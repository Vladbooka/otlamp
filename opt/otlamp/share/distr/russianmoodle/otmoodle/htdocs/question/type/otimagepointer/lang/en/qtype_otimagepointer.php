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
$string['pluginname'] = 'Objects in the image';
$string['pluginname_help'] = '';
$string['pluginname_link'] = 'question/type/otimagepointer';
$string['pluginnameadding'] = 'Add question "Objects in the image"';
$string['pluginnameediting'] = 'Edit the question "Objects in the image"';
$string['pluginnamesummary'] = 'Manually evaluated question type, in which students are required to mark objects in the image';

// Источники изображения
$string['imagesource_internalfile_name'] = 'Internal file';
$string['imagesource_externalfile_name'] = 'External file';
$string['imagesource_webcamera_name'] = "Student's webcam";

// Настройка плагина

// Настройки экземпляра
$string['editform_imagesource_header'] = 'Image source';
$string['editform_imagesource_select_select'] = '-Select source-';
$string['editform_imagesource_label'] = 'Source of the image';
$string['editform_imagesource_internalfile_label'] = 'Image File';
$string['editform_imagesource_webcamera_saving_confirmation_label'] = 'Require confirmation upon capture';

// Отображение вопроса
$string['imagesource_webcamera_capturepage_link'] = 'Add image';
$string['imagesource_webcamera_capture_add'] = 'Add image';
$string['imagesource_webcamera_capture_update'] = 'Refresh Image';
$string['imagesource_webcamera_capture_capture'] = 'Take Snapshot';
$string['imagesource_webcamera_capture_save'] = 'Save';
$string['imagesource_webcamera_capture_close'] = 'Close';
$string['imagesource_webcamera_capture_cancel'] = 'Cancel';
$string['imagesource_externalfile_loadpage_link'] = 'Add image';
$string['imagesource_externalfile_quiz_title'] = 'Download Image';
$string['imagesource_externalfile_save_button'] = 'Save';

$string['tool_clear'] = 'Clear';
$string['tool_eraser'] = 'Eraser';
$string['tool_pencil'] = 'Pencil';
$string['tool_arrow'] = 'Arrow';
$string['tool_undo'] = 'Cancel';
$string['tool_redo'] = 'Return';
$string['tool_rectangle'] = 'Rectangle';
$string['erase_confirm'] = 'Are you sure you want to discard changes?';

// Ошибки
$string['error_editform_imagesource_empty'] = 'Source not selected';
$string['error_editform_imagesource_notfound'] = 'The selected source was not found';
$string['editform_imagesource_internalfile_error_nofile'] = 'No image added';
$string['editform_imagesource_externalfile_error_nofile'] = 'No image added';