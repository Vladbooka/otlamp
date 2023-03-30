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
 * Витрина курсов. Обозреватель событий.
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_crw;

defined('MOODLE_INTERNAL') || die();

use \context_course;
use \stored_file;
use \stdClass;

class observer 
{

    /**
     * Выполнение действий после создания курса
     *
     * @param \core\event\course_created $event
     */
    public static function course_created(\core\event\course_created $event)
    {
        global $CFG;
        
        require_once ($CFG->libdir . '/filestorage/file_storage.php');
        require_once ($CFG->dirroot . '/local/crw/lib.php');
        
        // Получение данных о событии
        $data = $event->get_data();
        if ( isset($data['courseid']) )
        {
            // Получение ID курса
            $courseid = $data['courseid'];
        
            // Получение хранилища
            $fs = get_file_storage();
            // Получение контекста
            $context = context_course::instance($courseid);
            // Получаем файлы
            $files = $fs->get_area_files($context->id, 'course', 'overviewfiles');
            
            if ( ! empty($files) )
            {// Файлы есть
                foreach( $files as $file )
                {
                    if ( ! $file->is_valid_image() )
                    {// Файл не является изображением
                        continue;
                    }
        
                    // Формирование изменений между превью и исходным файлом
                    $preview = new \stdClass;
                    $preview->component = 'local_crw';  
                    
                    // Создать превью изображения с шириной 500 px и динамической высотой
                    local_crw_create_preview($file, $preview, 500);
                }
            }
        }
    }
    
    /**
     * Выполнение действий после создания курса
     *
     * @param \core\event\course_updated $event
     */
    public static function course_updated(\core\event\course_updated $event)
    {
        global $CFG;
        
        require_once ($CFG->libdir . '/filestorage/file_storage.php');
        require_once ($CFG->dirroot . '/local/crw/lib.php');
        
        // Получение данных о событии
        $data = $event->get_data();
        if ( isset($data['courseid']) )
        {
            // Получение ID курса
            $courseid = $data['courseid'];
            
            // Получение хранилища
            $fs = get_file_storage();
            // Получение контекста
            $context = context_course::instance($courseid);
            // Получаем файлы
            $files = $fs->get_area_files($context->id, 'course', 'overviewfiles');
            
            
            if ( ! empty($files) )
            {// Файлы есть
                
                // Удаление имеющихся превью файлов 
                $fs->delete_area_files(
                        $context->id,
                        'local_crw',
                        'overviewfiles'
                );
                foreach( $files as $file )
                {
                    if ( ! $file->is_valid_image() )
                    {// Файл не является изображением
                        continue;
                    }
                    
                    // Формирование изменений между превью и исходным файлом
                    $preview = new \stdClass;
                    $preview->component = 'local_crw';  
                    
                    // Создать превью изображения с шириной 500 px и динамической высотой
                    local_crw_create_preview($file, $preview, 500);
                }
            }
        }
    }
    
    /**
     * Выполнение действий после удаления курса
     *
     * @param \core\event\course_deleted $event
     */
    public static function course_deleted(\core\event\course_deleted $event)
    {
        global $CFG;
        
        require_once ($CFG->libdir . '/filestorage/file_storage.php');
        require_once ($CFG->dirroot . '/local/crw/lib.php');
        
        // Получение данных о событии
        $data = $event->get_data();
        if ( isset($data['contextid']) )
        {
            // Получение ID курса
            $contextid = $data['contextid'];
            
            // Получение хранилища
            $fs = get_file_storage();
            
            // Удаление имеющихся превью файлов 
            $fs->delete_area_files(
                        $contextid,
                        'local_crw',
                        'overviewfiles'
            );
        }
    }
}
