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
 * Плагин записи на курс OTPAY. Класс периодического задания по отправке напоминаний
 * об окончании подписки
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_otpay\task;

defined('MOODLE_INTERNAL') || die();

class send_expiry_notifications extends \core\task\scheduled_task
{

    public function get_name()
    {
        // Shown in admin screens
        return get_string('send_expiry_notifications', 'enrol_otpay');
    }

    public function execute()
    {
        global $CFG;
        require_once ($CFG->dirroot . '/enrol/otpay/lib.php');
        $trace = new \text_progress_trace();
        $otpay = new \enrol_otpay_plugin();
        $otpay->send_expiry_notifications($trace);
    }
}