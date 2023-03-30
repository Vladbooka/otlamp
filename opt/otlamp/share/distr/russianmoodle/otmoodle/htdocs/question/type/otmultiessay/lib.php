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
 * Тип вопроса Мульти-эссе. Базовые функции плагина.
 *
 * @package    qtype
 * @subpackage otmultiessay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Функция обработки пользовательских файлов, принадлежащих плагину.
 *
 * @param stdClass $course - Объект текущего курса
 * @param stdClass $cm - Объект текущего модуля курса
 * @param stdClass $context - Текущий контекст
 * @param string $filearea - Зона, в которой хранится запрашиваемый файл
 * @param array $args - Дополнительные аргументы файла
 * @param bool $forcedownload - Формат отправки файла пользователю(отображение или загрузка)
 * @param array $options - Дополнительные опции подготовки файла
 * 
 * @return
 */
function qtype_otmultiessay_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) 
{
    global $CFG;
    
    // Подключение библиотеки
    require_once($CFG->libdir . '/questionlib.php');
    // Передача управления центральному файловому обработчику вопросов
    question_pluginfile(
        $course, 
        $context, 
        'qtype_otmultiessay', 
        $filearea, 
        $args, 
        $forcedownload, 
        $options
    );
}
