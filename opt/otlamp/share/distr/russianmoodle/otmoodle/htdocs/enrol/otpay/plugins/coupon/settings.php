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
 * Способ записи на курс OTPay.
 * Настройки.
 *
 * @package enrol
 * @subpackage otpay
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

if ( $ADMIN->fulltree )
{
    $component = 'enrol_otpay';
    $settingsprefix = "coupon_";
    
    // Ссылка на панель купонов
    $url_coupon_panel = new moodle_url('/enrol/otpay/coupons.php');
    $settings->add(new admin_setting_heading('enrol_otpay/url_coupon', '', html_writer::link($url_coupon_panel->out(false), get_string('url_coupon_panel', 'enrol_otpay'))));
}