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
 * Форма сайт-колла
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class otpay_accountgenerate_form_sitecall extends otpay_accountgenerate_form_base
{
    /**
     * Получение полей
     *
     * @return array
     */
    public function get_fields()
    {
        return [
            'comment' => [
                'type' => PARAM_RAW_TRIMMED, 
                'fieldtype' => 'textarea', 
                'rules' => [
                    ['type' => 'required', 'message' => 'required', 'format' => null, 'validation' => 'client', 'reset' => false, 'force' => true]
                ]
            ]
        ];
    }
    
    /**
     * Кастомная submit кнопка
     *
     * @return array
     */
    public function get_field_submit()
    {
        return ['text' => get_string('send', 'enrol_otpay')];
    }
}