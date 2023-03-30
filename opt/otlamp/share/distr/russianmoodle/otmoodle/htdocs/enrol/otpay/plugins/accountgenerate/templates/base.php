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
 * Базовый класс шаблонов генерации счета
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

abstract class otpay_accountgenerate_template_base
{
    /**
     * Получение кода сабплагина
     * 
     * @return string
     */
    public final function get_code()
    {
        return str_replace('otpay_accountgenerate_template_', '', static::class);
    }
    
    /**
     * Получение html формы с макроподстановками для генерации
     * Макроподстановки вида ${name}
     * 
     * @return string
     */
    public function get_html()
    {
        return '';
    }
}