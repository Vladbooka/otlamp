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
 * Плагин записи на курс OTPAY. Базовый класс сабплагинов генерации счета
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

abstract class otpay_accountgenerate_scenario_base
{
    /**
     * Плагин
     * 
     * @var otpay_accountgenerate $plugin
     */
    protected $plugin = null;
    
    /**
     * Формы сценария
     * 
     * Если необходимо использовать нескольких форм в одном сценарии,
     * то нужно убедиться, что у всех форм есть заголовок - метод get_header()
     * 
     * @var array
     */
    protected $forms = [];
    
    /**
     * Переопределение строк заголовков форм сценария
     * 
     * @var array
     */
    protected $forms_header_strings = [];
    
    /**
     * Конструктор
     *
     * @param otpay_accountgenerate $plugin
     */
    public function __construct(otpay_accountgenerate $plugin)
    {
        $this->plugin = $plugin;
    }
    
    /**
     * Получение форм сценария
     * 
     * @return array
     */
    public final function get_forms()
    {
        return $this->forms;
    }
    
    /**
     * Получение кода сабплагина
     * 
     * @return string
     */
    public final function get_code()
    {
        return str_replace('otpay_accountgenerate_scenario_', '', static::class);
    }
    
    /**
     * Получение названия сабплагина
     *
     * @return string
     */
    public function get_name()
    {
        return '';
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
     * 
     * @param object $form
     * @param object $mform
     */
    public function add_free_enrol_button($form, &$mform, $tabs = [])
    {
    }
    
    /**
     * Получение заголовка формы
     * 
     * @return string
     */
    public function get_form_header(otpay_accountgenerate_form_base $form)
    {
        if ( array_key_exists($form->get_code(), $this->forms_header_strings) )
        {
            return get_string($this->form_header_strings[$form->get_code()], 'enrol_otpay');
        }
        
        return $form->get_header();
    }
    
    /**
     * Флаг присутствия цены у сценария
     *
     * @return bool
     */
    public function has_cost()
    {
        return true;
    }
}