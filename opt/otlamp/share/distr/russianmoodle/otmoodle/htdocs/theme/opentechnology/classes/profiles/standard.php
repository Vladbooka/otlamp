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
 * Тема СЭО 3KL. Класс профиля темы по умолчанию.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_opentechnology\profiles;

class standard extends base
{
    /**
     * Конструктор
     * 
     */
    public function __construct($id = null) 
    {
    }
    
    /**
     * Поддержка удаления профиля
     * 
     * @return boolean
     */
    public function can_delete()
    {
        return false;
    }
    
    /**
     * Процесс удаления данных профиля
     * 
     * @return void
     */
    public function delete_profile_data()
    {
    }
    
    /**
     * Поддержка изменения профиля
     *
     * @return boolean
     */
    public function can_edit()
    {
        return false;
    }
    
    /**
     * Получить название профиля
     *
     * @return int
     */
    public function get_classname()
    {
        return 'standard';
    }
    
    /**
     * Получить идентификатор профиля
     * 
     * @return int
     */
    public function get_id()
    {
        return 0;
    }
    
    /**
     * Проверка на профиль по умолчанию
     * 
     * Оформление профиля по умолчанию распространяется на всю Тему
     *
     * @return null
     */
    public function is_default()
    {
        return null;
    }
    
    /**
     * Получить код профиля
     * 
     * @return string
     */
    public function get_code()
    {
        return 'standard';
    }
    
    /**
     * Получить локализованное название профиля
     * 
     * @return string
     */
    public function get_name()
    {
        return get_string('profile_standard_name', 'theme_opentechnology');
    }
    
    /**
     * Получение имени настройки
     * 
     * @param string $basesettingname - Базовое имя настройки
     * 
     * @return string
     */
    public function get_setting_name($basesettingname)
    {
        return $basesettingname;
    }
    
    /**
     * Получить локализованное описание профиля
     * 
     * @return string
     */
    public function get_description()
    {
        return get_string('profile_standard_descripton', 'theme_opentechnology');
    }
    
    /**
     * Получить привязки профиля к элементам системы
     */
    public function get_links()
    {
        return [];
    }
    
    /**
     * {@inheritDoc}
     * @see \theme_opentechnology\profiles\base::can_export()
     */
    public function can_export()
    {
        return true;
    }
    
    /**
     * {@inheritDoc}
     * @see \theme_opentechnology\profiles\base::can_import()
     */
    public function can_import()
    {
        return false;
    }
    
    /**
     * {@inheritDoc}
     * @see \theme_opentechnology\profiles\base::can_create()
     */
    public function can_create()
    {
        return false;
    }
}