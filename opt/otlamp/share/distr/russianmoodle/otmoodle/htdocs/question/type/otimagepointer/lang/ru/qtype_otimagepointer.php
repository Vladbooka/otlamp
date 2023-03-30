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
$string['pluginname'] = 'Объекты на изображении';
$string['pluginname_help'] = '';
$string['pluginname_link'] = 'question/type/otimagepointer';
$string['pluginnameadding'] = 'Добавить вопрос "Объекты на изображении"';
$string['pluginnameediting'] = 'Редактировать вопрос "Объекты на изображении"';
$string['pluginnamesummary'] = 'Вручную оцениваемый тип вопроса, в котором студентам требуется отметить объекты на изображении';

// Источники изображения
$string['imagesource_internalfile_name'] = 'Внутренний файл';
$string['imagesource_externalfile_name'] = 'Внешний файл';
$string['imagesource_webcamera_name'] = 'Веб-камера студента';

// Настройка плагина

// Настройки экземпляра
$string['editform_imagesource_header'] = 'Источник изображения';
$string['editform_imagesource_select_select'] = '-Выбрать источник-';
$string['editform_imagesource_label'] = 'Источник изображения';
$string['editform_imagesource_internalfile_label'] = 'Файл изображения';
$string['editform_imagesource_webcamera_saving_confirmation_label'] = 'Требовать подтверждение при захвате';

// Отображение вопроса
$string['imagesource_webcamera_capturepage_link'] = 'Добавить изображение';
$string['imagesource_webcamera_capture_add'] = 'Добавить изображение';
$string['imagesource_webcamera_capture_update'] = 'Обновить изображение';
$string['imagesource_webcamera_capture_capture'] = 'Сделать снимок';
$string['imagesource_webcamera_capture_save'] = 'Сохранить';
$string['imagesource_webcamera_capture_close'] = 'Закрыть';
$string['imagesource_webcamera_capture_cancel'] = 'Отмена';
$string['imagesource_externalfile_loadpage_link'] = 'Добавить изображение';
$string['imagesource_externalfile_quiz_title'] = 'Загрузка изображения';
$string['imagesource_externalfile_save_button'] = 'Сохранить';

$string['tool_clear'] = 'Очистить';
$string['tool_eraser'] = 'Стерка';
$string['tool_pencil'] = 'Карандаш';
$string['tool_arrow'] = 'Стрелка';
$string['tool_undo'] = 'Отменить';
$string['tool_redo'] = 'Вернуть';
$string['tool_rectangle'] = 'Прямоугольник';
$string['erase_confirm'] = 'Вы точно хотите отменить изменения?';

// Ошибки
$string['error_editform_imagesource_empty'] = 'Источник не выбран';
$string['error_editform_imagesource_notfound'] = 'Выбранный источник не найден';
$string['editform_imagesource_internalfile_error_nofile'] = 'Избражение не добавлено';
$string['editform_imagesource_externalfile_error_nofile'] = 'Избражение не добавлено';