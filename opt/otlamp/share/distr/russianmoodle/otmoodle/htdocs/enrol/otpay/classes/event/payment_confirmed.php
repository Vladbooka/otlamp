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
 * Плагин записи на курс OTPAY. Класс события подтверждения оплаты от банка
 *
 * @package    enrol_otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_otpay\event;

defined('MOODLE_INTERNAL') || die();

use coding_exception;

class payment_confirmed extends \core\event\base
{
    /**
     * Инициализация события
     */
    protected function init()
    {
        $this->data['objecttable'] = 'enrol_otpay';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Получить описание события
     *
     * @return string
     */
    public function get_description()
    {
        return "Payment linked with enrol_otpay object with id='$this->objectid'
            of user with id '$this->relateduserid' was confirmed.";
    }

    /**
     * Получить название события
     *
     * @return string
     */
    public static function get_name()
    {
        return get_string('event_payment_confirmed', 'enrol_otpay');
    }

    /**
     * Валидация данных события
     *
     * @return void
     *
     * @throws \coding_exception
     */
    protected function validate_data()
    {
        // Базовая валидация
        parent::validate_data();
        
        if ( ! isset($this->objectid) )
        {
            throw new coding_exception('The \'objectid\' must be set.');
        }
        if ( ! isset($this->relateduserid) )
        {
            throw new coding_exception('The \'relateduserid\' must be set.');
        }
        if ( ! isset($this->courseid) )
        {
            throw new coding_exception('The \'courseid\' must be set.');
        }
    }
}
