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
 * Плагин формата курсов OpenTechnology. Скрипт установки.
 *
 * @package    format
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_format_opentechnology_upgrade($oldversion) 
{
    global $DB;
    
    // Миграция устаревших форматов курса
    if ( $oldversion < 2017010900 ) 
    {
        // Получить курсы системы
        $courses = (array)get_courses();
    
        // Миграция старых форматов курса
        foreach ( $courses as $course )
        {
            // Текущий формат курса
            $format = (string)$DB->get_field('course', 'format', ['id' => $course->id], IGNORE_MISSING);
        
            // Получение способа отображения для курса
            $setting = $DB->get_record('course_format_options', [
                'courseid' => $course->id,
                'format' => 'opentechnology',
                'sectionid' => 0,
                'name' => 'course_display_mode'
            ]);
        
            // Миграция
            $migrated = false;
            switch ( $format )
            {
                // Миграция с одноколоночного формата
                case 'otonecolumn' :
                    if ( $setting )
                    {
                        $setting->value = 1;
                        $DB->update_record('course_format_options', $setting);
                    } else
                    {
                        $DB->insert_record('course_format_options', [
                            'courseid' => $course->id,
                            'format' => 'opentechnology',
                            'sectionid' => 0,
                            'name' => 'course_display_mode',
                            'value' => '1'
                        ]);
                    }
                    // Установка текущего формата активным
                    $DB->set_field('course', 'format', 'opentechnology', ['id' => $course->id]);
                    $migrated = true;
                    break;
                    // Миграция с двуколоночного формата
                case 'ottwocolumn' :
                    if ( $setting )
                    {
                        $setting->value = 2;
                        $DB->update_record('course_format_options', $setting);
                    } else
                    {
                        $DB->insert_record('course_format_options', [
                            'courseid' => $course->id,
                            'format' => 'opentechnology',
                            'sectionid' => 0,
                            'name' => 'course_display_mode',
                            'value' => '2'
                        ]);
                    }
                    $DB->set_field('course', 'format', 'opentechnology', ['id' => $course->id]);
                    $migrated = true;
                    break;
                    // Миграция с карусели
                case 'otcarousel' :
                    if ( $setting )
                    {
                        $setting->value = 1;
                        $DB->update_record('course_format_options', $setting);
                    } else
                    {
                        $DB->insert_record('course_format_options', [
                            'courseid' => $course->id,
                            'format' => 'opentechnology',
                            'sectionid' => 0,
                            'name' => 'course_display_mode',
                            'value' => '1'
                        ]);
                    }
                    $DB->set_field('course', 'format', 'opentechnology', ['id' => $course->id]);
                    $migrated = true;
                    break;
            }
            
            // Миграция базовых настроек курса
            if ( $migrated )
            {
                // Число секций
                $numsections = (int)$DB->get_field(
                    'course_format_options',
                    'value',
                    [
                        'courseid' => $course->id,
                        'name' => 'numsections',
                        'format' => $format
                    ],
                    IGNORE_MISSING
                );
                if ( $numsections )
                {
                    // Сохранение числа секций
                    $setting = $DB->get_record('course_format_options', [
                        'courseid' => $course->id,
                        'format' => 'opentechnology',
                        'sectionid' => 0,
                        'name' => 'numsections'
                    ]);
                    if ( $setting )
                    {
                        $setting->value = $numsections;
                        $DB->update_record('course_format_options', $setting);
                    } else
                    {
                        $DB->insert_record('course_format_options', [
                            'courseid' => $course->id,
                            'format' => 'opentechnology',
                            'sectionid' => 0,
                            'name' => 'numsections',
                            'value' => $numsections
                        ]);
                    }
                }
                
                // Скрытие курса
                $coursedisplay = $DB->get_field(
                    'course_format_options',
                    'value',
                    [
                        'courseid' => $course->id,
                        'name' => 'coursedisplay',
                        'format' => $format
                    ],
                    IGNORE_MISSING
                );
                if ( $coursedisplay !== false )
                {
                    // Сохранение состояния скрытия курса
                    $setting = $DB->get_record('course_format_options', [
                        'courseid' => $course->id,
                        'format' => 'opentechnology',
                        'sectionid' => 0,
                        'name' => 'coursedisplay'
                    ]);
                    if ( $setting )
                    {
                        $setting->value = $coursedisplay;
                        $DB->update_record('course_format_options', $setting);
                    } else
                    {
                        $DB->insert_record('course_format_options', [
                            'courseid' => $course->id,
                            'format' => 'opentechnology',
                            'sectionid' => 0,
                            'name' => 'coursedisplay',
                            'value' => $coursedisplay
                        ]);
                    }
                }
            }
        }
    }
    return true;
}