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
 * Сайт-колл
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class otpay_accountgenerate_scenario_sitecall extends otpay_accountgenerate_scenario_base
{
    /**
     * Формы сценария
     *
     * @var array
     */
    protected $forms = [
        'sitecall'
    ];
    
    /**
     * Кастомная submit кнопка
     *
     * @return array
     */
    public function get_field_submit()
    {
        return ['text' => get_string('submit_request', 'enrol_otpay')];
    } 
   
    /**
     * Кастомная submit
     * {@inheritDoc}
     * @see otpay_accountgenerate_scenario_base::add_free_enrol_button()
     */
    public function add_free_enrol_button($form, &$mform, $tabs = [])
    {
        // кастомная кнопка сабмита у сценария
        $form->add_modal_button(get_string('submit_request', 'enrol_otpay'), $tabs);
    }
    
    /**
     * Флаг присутствия цены у сценария
     *
     * @return bool
     */
    public function has_cost()
    {
        return false;
    }
}