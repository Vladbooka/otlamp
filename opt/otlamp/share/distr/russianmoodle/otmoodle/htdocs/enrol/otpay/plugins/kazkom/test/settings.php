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
 * Тестовые данные псевдосабплагина kazkom.
 *
 * @package enrol
 * @subpackage otpay
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$merchantcertificateid = "00C182B189";
$merchantname = "Test shop";
$merchantid = "92061101";
$privateuserkey = file_get_contents($CFG->dirroot."/enrol/otpay/plugins/kazkom/test/test_prv.pem");
$privateuserkeypassword = "nissan";
$publicbankkey = file_get_contents($CFG->dirroot."/enrol/otpay/plugins/kazkom/test/kkbca.pem");
$url = "https://testpay.kkb.kz/jsp/process/logon.jsp";
$urlcontrol = "https://testpay.kkb.kz/jsp/remote/control.jsp";