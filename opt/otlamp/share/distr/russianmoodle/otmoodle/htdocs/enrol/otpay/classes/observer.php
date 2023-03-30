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
 * Плагин записи на курс OTPAY. Обозреватель событий плагина.
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class enrol_otpay_observer
{
    /**
     * Обработчик события получения ответа от провайдера платежей
     *
     * @param enrol_otpay\event\response_obtained $event
     */
    public static function response_obtained(enrol_otpay\event\response_obtained $event)
    {
        global $CFG, $DB;
        
        $plugin = enrol_get_plugin('otpay');
        $enrolotpay = $DB->get_record('enrol_otpay', ['id' => $event->objectid]);
        if ( $enrolotpay )
        {// Заявка записи на курс
            
            // Обработка скидочных купонов
            $plugin->process_coupons($enrolotpay);
            
            $status = 'confirmed';
            // Платеж еще не завершен, ожидаем подтверждения от платежной системы
            if (!empty($event->other['waitdebit']))
            {
                $status = "waitdebit";
            }
            // Платеж прошел, от нас требуется подтверждение списания
            if (!empty($event->other['needdebit']))
            {
                $status = "needdebit";
            }
            
            // Доп.данные о платеже
            $paymentdata = $event->other['additional_data'] ?? null;
            
            // Обработка платежа
            $enrolotpay = $plugin->process_payment($enrolotpay, $status, $paymentdata);
            
            // Если платеж совереш не до конца (ожидание запроса от банка, запрос с нашей стороны на подтверждение списания)
            if ( $enrolotpay->status == "needdebit" )
            { // Требуется завершение платежа
                $plugin->complete_payment($enrolotpay, $paymentdata);
            }
        }
    }
}
