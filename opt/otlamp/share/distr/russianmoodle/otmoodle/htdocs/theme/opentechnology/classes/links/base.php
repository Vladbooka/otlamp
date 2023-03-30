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
 * Тема СЭО 3KL. Базовый класс привязок
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_opentechnology\links;

use theme_opentechnology\links\formsave;
use theme_opentechnology\profilemanager;
use theme_opentechnology\profiles\base as profile_base;
use stdClass;
use moodle_page;

abstract class base
{
    /**
     * Данные привязки профиля
     *
     * @var stdClass
     */
    protected $record = null;
    
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
    public abstract function is_enabled();
    
    /**
     * Получить код привязки
     *
     * @return string
     */
    public abstract function get_code();
    
    /**
     * Получить локализованное название привязки
     *
     * @return string
     */
    public abstract function get_name();
    
    /**
     * Получить локализованное описание привязки
     *
     * @return string
     */
    public abstract function get_description();
    
    /**
     * Получить информацию о привязке
     *
     * @return string
     */
    public abstract function get_info();
    
    /**
     * Добавление полей в форму сохранения привязки
     *
     * @param formsave $saveform - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     *
     * @return void
     */
    public abstract function saveform_definition($saveform, $mform);
    
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
    public abstract function saveform_set_data($saveform, $mform);
    
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
    public abstract function saveform_validation(&$errors, $saveform, $mform, $data, $files);
    
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
    public abstract function saveform_preprocess($saveform, $mform, $formdata, &$linkrecord);
    
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
    public abstract function saveform_postprocess($saveform, $mform, $formdata, $id);
    
    /**
     * Получить идентификатор привязки
     *
     * @return int
     */
    public function get_id()
    {
        if ( isset($this->record->id) )
        {// Привязка инициализирована
            return (int)$this->record->id;
        }
        return null;
    }
    
    /**
     * Получить ID профиля привязки
     *
     * @return int
     */
    public function get_profile_id()
    {
        if ( isset($this->record->profileid) )
        {// Привязка инициализирована
            return (int)$this->record->profileid;
        }
        return null;
    }
    
    /**
     * Получить профиль привязки
     *
     * @return profile_base - Профиль
     */
    public function get_profile()
    {
        if ( isset($this->record->profileid) )
        {// Привязка инициализирована
            return profilemanager::instance()->get_profile((int)$this->record->profileid);
        }
        return null;
    }
    
    /**
     * Получить данные привязки
     *
     * @return mixed
     */
    public function get_data()
    {
        if ( isset($this->record->linkdata) )
        {// Привязка инициализирована
            return $this->record->linkdata;
        }
        return null;
    }
    
    /**
     * Инициализация привязки плагина
     *
     * @param stdClass $record - Запись привязки
     */
    public function set_record($record)
    {
        $this->record = $record;
    }
    
    /**
     * Получение данных привязки
     *
     * @param stdClass $record - Запись привязки
     */
    public function get_record()
    {
        return $this->record;
    }
    
    /**
     * Получить приоритетную привязку текущего типа, направленную на целевую страницу
     *
     * @param $object - Данные объекта, на основе которого вычисляется подходящая привязка
     *
     * @return array - Массив привязок
     */
    public function get_link($object)
    {
        return [];
    }
}