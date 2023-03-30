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
$string['pluginname'] = 'Мульти-эссе';
$string['pluginname_help'] = '';
$string['pluginname_link'] = 'question/type/otmultiessay';
$string['pluginnameadding'] = 'Добавить вопрос "Мульти-эссе"';
$string['pluginnameediting'] = 'Редактировать вопрос "Мульти-эссе"';
$string['pluginnamesummary'] = 'Вручную оцениваемый тип вопроса, в котором студентам требуется написать несколько эссе';

// Настройка плагина
$string['responseoptions'] = 'Опции отзыва';
$string['enablequestion'] = 'Отображать вопрос?';
$string['innerquestion'] = 'Текст вопроса';
$string['responseformat'] = 'Формат ответа';
$string['formateditor'] = 'HTML-редактор';
$string['formateditorfilepicker'] = 'HTML-редактор с выбором файлов';
$string['formatmonospaced'] = 'Обычный текст, моноширинный шрифт';
$string['formatnoinline'] = 'Нет встроенного текста';
$string['formatplain'] = 'Обычный текст';
$string['responserequired'] = 'Требовать текст';
$string['responseisrequired'] = 'Требовать от студента ввода текста';
$string['responsenotrequired'] = 'Ввод текста не обязателен';
$string['responsefieldlines'] = 'Размер поля';
$string['nlines'] = '{$a} строк';
$string['attachments'] = 'Разрешить вложения';
$string['attachmentsrequired'] = 'Вложения обязательны';
$string['responsetemplateheader'] = 'Шаблон отзыва';
$string['responsetemplate'] = 'Шаблон ответа';
$string['graderinfoheader'] = 'Информация об оценщике';
$string['graderinfo'] = 'Информация для оценивающих';
$string['attachmentsrequired_help'] = 'Этот параметр определяет минимальное количество вложений, необходимых для оценивания ответа.';
$string['responsetemplate_help'] = 'Любой написанный здесь текст будет введен в поле ответа при начале новой попытки.';
$string['questionheader'] = 'Вопрос {no}';
$string['addmoreanswers'] = 'Добавить {no} варианта(ов) ответа(ов)';
$string['attachmentsoptional'] = 'Вложения не обязательны';
$string['qtype_otmultiessay_grager_info_block_caption'] = 'Информация для оценивающих к вопросу';

$string['mustattach'] = 'Когда выбрано "Нет встроенного текст" или ответы не являются обязательными, Вы должны разрешить по меньшей мере одно вложение.';
$string['mustrequire'] = 'Когда выбрано "Нет встроенного текст" или ответы не являются обязательными, Вы должны разрешить по меньшей мере одно вложение.';
$string['mustrequirefewer'] = 'Вы не можете требовать больше вложений, чем разрешили.';
$string['error_no_active_questions'] = 'Нет активированных вопросов';


// Настройки экземпляра