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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Тема СЭО 3KL. Класс привязки к курсу
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_opentechnology\links\types;

use theme_opentechnology\links\base;
use theme_opentechnology\links\manager;
use stdClass;
use dml_exception;
use context_course;
use moodle_page;

class contextcourse extends base
{
    /**
     * Конструктор
     *
     * @param int - ID профиля
     */
    public function __construct($profileid = null)
    {
    }
    
    /**
     * Проверка доступности привязки
     *
     * @return bool
     */
    public function is_enabled()
    {
        return true;
    }
    
    /**
     * Получить код привязки
     *
     * @return string
     */
    public function get_code()
    {
        return 'contextcourse';
    }
    
    /**
     * Получить локализованное название привязки
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('link_contextcourse_name', 'theme_opentechnology');
    }
    
    /**
     * Получить локализованное описание привязки
     *
     * @return string
     */
    public function get_description()
    {
        return get_string('link_contextcourse_descripton', 'theme_opentechnology');
    }
    
    /**
     * Получить информацию о привязке
     *
     * @return string
     */
    public function get_info()
    {
        // Получение ID привязанного курса
        $courseid = $this->get_courseid();
        if ( $courseid )
        {
            try
            {
                $course = get_course($courseid);
                return $course->fullname;
            } catch ( dml_exception $e )
            {// Курс не найден
                return get_string('profile_link_contextcourse_error_notfound', 'theme_opentechnology');
            }
        }
        return get_string('profile_link_contextcourse_error_defaultlinkinfo', 'theme_opentechnology');
    }
    
    /**
     * Получить информацию о привязке
     *
     * @return string
     */
    public function get_courseid()
    {
        $data = $this->get_data();
        if ( $data )
        {
            return (int)$data;
        }
        return null;
    }
    
    /**
     * Получить приоритетную привязку текущего типа, направленную на целевую страницу
     *
     * @param $page - Целевая страница
     *
     * @return array - Массив привязок
     */
    public function get_link($page)
    {
        global $DB;
        
        // Получение ID курса страницы
        $courseid = (int)$page->course->id;
        
        // Найти все привязки, нацеленные на указанный курс
        $linkdatafieldname = $DB->sql_compare_text('linkdata');
        $where = $linkdatafieldname.' = :courseid AND linktype = :linktype';
        $placeholders = ['courseid' => $courseid, 'linktype' => $this->get_code()];
        
        $linkrecords = (array)$DB->get_records_select(
            'theme_opentechnology_plinks',
            $where,
            $placeholders
        );
        
        if ( $linkrecords )
        {// Найдены привязки к текущему курсу
            
            // Получение последней привязки к курсу
            $pagelink = array_pop($linkrecords);
            
            // Инициализация привязки страницы
            $pagelink = manager::instance()->get_link((int)$pagelink->id);
            
            return $pagelink;
        }
        // Не найдено ни одной привязки
        return null;
    }
    
    /**
     * Добавление полей в форму сохранения привязки
     *
     * @param formsave $saveform - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     *
     * @return void
     */
    public function saveform_definition($saveform, $mform)
    {
        // Выбор курса, к которому будет установлена привязка
        $options = [
            'requiredcapabilities' => ['moodle/course:view'],
            'includefrontpage' => true
        ];
        $mform->addElement(
            'course',
            'linkedcourse',
            get_string('profile_link_contextcourse_course_name', 'theme_opentechnology'),
            $options
        );
    }
    
    /**
     * Предварительная обработка полей формы сохранения привязки
     *
     * Организация заполнения полей данными
     *
     * @param base $profile - Профиль-владелец привязки
     * @param formsave $saveform - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     *
     * @return void
     */
    public function saveform_set_data($saveform, $mform)
    {
    
    }
    
    /**
     * Валидация полей формы сохранения экземпляра вопроса
     *
     * @param array $errors - Массив ошибок валидации
     * @param formsave $saveform - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     * @param array $data - Данные формы сохранения
     * @param array $files - Загруженные файлы формы сохранения
     *
     * @return void
     */
    public function saveform_validation(&$errors, $saveform, $mform, $data, $files)
    {
        try {
            // Получение курса
            $course = get_course($data['linkedcourse']);
            
            $context = context_course::instance($course->id);
            if ( ! has_capability('moodle/course:view', $context) )
            {
                $errors['linkedcourse'] = get_string('profile_link_contextcourse_error_notfound', 'theme_opentechnology');
            }
        } catch ( dml_exception $e )
        {
            $errors['linkedcourse'] = get_string('profile_link_contextcourse_error_notfound', 'theme_opentechnology');
        }
    }
    
    /**
     * Прероцесс сохранения вопроса
     *
     * @param formsave $saveform - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     * @param stdClass $formdata - Данные формы сохранения
     * @param stdClass $linkrecord - Запись для сохранения в БД
     *
     * @return void
     */
    public function saveform_preprocess($saveform, $mform, $formdata, &$linkrecord)
    {
        $linkrecord->linkdata = $formdata->linkedcourse;
    }
    
    /**
     * Постпроцесс сохранения вопроса
     *
     * @param formsave $saveform - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     * @param stdClass $formdata - Данные формы сохранения
     * @param int $id - ID сохраненного вопроса
     *
     * @return void
     */
    public function saveform_postprocess($saveform, $mform, $formdata, $id)
    {
    }
}