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
 * Тип вопроса Объекты на изображении. Базовые функции плагина.
 *
 * @package    qtype
 * @subpackage otimagepointer
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
function qtype_otimagepointer_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) 
{
    global $CFG;
    
    // Подключение библиотеки
    require_once($CFG->libdir . '/questionlib.php');
    
    // Получение типа вопроса
    $qtype = question_bank::get_qtype('otimagepointer');
    
    // Получение источников изображений
    $baseimagesources = $qtype->imagesources_get_list();
    
    // Попытка определить файл плагинов источника изображения
    if ( isset($baseimagesources[$filearea]) )
    {// Передача управления в плагин источника изображения
        $baseimagesources[$filearea]::pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options);
    }

    if ( $filearea == 'user_response' )
    {// Файл с объединенным изображением
        // Подключение менеджера файлов
        $fs = get_file_storage();
        
        $itemid = array_shift($args); 
        $filename = array_pop($args);
        
        $file = $fs->get_file(
            $context->id,
            'qtype_otimagepointer',
            'user_response',
            $itemid,
            '/',
            $filename
        );
        
        if ( $file )
        {
            \core\session\manager::write_close();
            send_stored_file($file, null, 0, $forcedownload, $options);
        }
        send_file_not_found();
    }
    // Передача управления центральному файловому обработчику вопросов
    question_pluginfile(
        $course, 
        $context, 
        'qtype_otimagepointer', 
        $filearea, 
        $args, 
        $forcedownload, 
        $options
    );
}

/**
 * Функция для получения опций из БД
 *
 * @param int $questionid - ID вопроса
 *
 * @return stdClass|false
 */
function qtype_otimagepointer_get_options($questionid = 0)
{
    global $DB;
    
    if ( is_object($questionid) )
    {
        if ( ! empty($questionid->id) )
        {
            $questionid = $questionid->id;
        } else
        {
            throw new moodle_exception('invalid_data_question_id');
        }
    }
    
    $record = $DB->get_record(
        'question_otimagepointer_opts', 
        ['question' => $questionid], 
        '*', 
        IGNORE_MULTIPLE
    );
    if ( ! empty($record->imagesourcedata) )
    {
        return unserialize($record->imagesourcedata);
    }
    return false;
}