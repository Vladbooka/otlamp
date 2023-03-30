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
 * Базовый класс форм генерации счета
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

abstract class otpay_accountgenerate_form_base
{
    /**
     * Шаблоны формы
     *
     * @var array
     */
    protected $templates = [];
    
    /**
     * Получение полей
     * 
     * @return array
     */
    abstract public function get_fields();
 
    /**
     * Получение заголовка вкладки
     *
     * @return string
     */
     public function get_header()
     {
         return static::get_code();
     }
    
    /**
     * Шаблоны формы
     *
     * @return array
     */
    public final function get_templates()
    {
        return $this->templates;
    }
    
    /**
     * Кастомная submit кнопка
     * 
     * @return array
     */
    public function get_field_submit()
    {
        return [];
    }
    
    /**
     * Получение кода сабплагина
     * 
     * @return string
     */
    public final function get_code()
    {
        return str_replace('otpay_accountgenerate_form_', '', static::class);
    }
    
    /**
     * Позволяет переопределить значения макроподстановок для шаблона
     * @param stdClass $account объект макроподстановок для шаблона
     * @return stdClass
     */
    public function filter_fields(stdClass $account)
    {// Базовые метода не фильтрует поля, полученные из формы
        return $account;
    }
}