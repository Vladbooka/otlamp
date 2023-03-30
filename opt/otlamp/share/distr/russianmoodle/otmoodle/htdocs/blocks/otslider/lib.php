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
 * Слайдер изображений. Библиотека функций блока.
 * 
 * @package    block
 * @subpackage otslider
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Подготовка сохраненных файлов блока
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * 
 * @return bool
 */
function block_otslider_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) 
{
    global $DOF;
    
    // Проверка на контекст
    if ( $context->contextlevel != CONTEXT_SYSTEM ) 
    {
        return false;
    }

    // ID файловой зоны
    $itemid = array_shift($args);
    
    // Проверка на зону
    if ( $filearea !== 'public' ) 
    {
        return false;
    }
    
    // Имя файла
    $filename = array_pop($args);
    
    // Путь файла
    if ( ! $args ) 
    {
        $filepath = '/'; 
    } else {
        $filepath = '/'.implode('/', $args).'/';
    }
    
    // Получение файлового хранилища
    $fs = get_file_storage();
    $file = $fs->get_file(context_system::instance()->id, 'block_otslider', $filearea, $itemid, $filepath, $filename);
    if ( ! $file) 
    {// Файл не найден
        return false; 
    }

    \core\session\manager::write_close();
    send_stored_file($file, null, 0, $forcedownload, $options);
}
?>