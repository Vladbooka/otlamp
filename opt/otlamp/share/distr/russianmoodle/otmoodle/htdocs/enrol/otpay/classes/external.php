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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Веб-сервис OTPAY
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_otpay;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_value;
use context_system;

class external extends external_api
{
    /**
     * Принимаемые параметры функции update_record_status()
     * 
     * @return external_function_parameters
     */
    public static function update_record_status_parameters()
    {
        // Идентификатор записи
        $instanceid = new external_value(PARAM_INT, 'Instance id', VALUE_REQUIRED);
        
        // Новый статус
        $newstatus = new external_value(PARAM_RAW_TRIMMED, 'New status', VALUE_REQUIRED);
        
        $params = [
            'instanceid' => $instanceid,
            'newstatus' => $newstatus,
        ];
        
        return new external_function_parameters($params);
    }
    
    /**
     * Возвращаемое значение функции update_record_status()
     *
     * @return external_value
     */
    public static function update_record_status_returns()
    {
        return new external_value(PARAM_BOOL, 'Result');
    }
    
    /**
     * Смена статуса
     * 
     * @return bool
     */
    public static function update_record_status($instanceid = null, $newstatus = null) 
    {
        // Получаем контекст страницы
        $context = context_system::instance();
        
        // Проверяем права
        require_capability('enrol/otpay:config', $context);
        
        // Получение плагина
        $plugin = enrol_get_plugin('otpay');
        
        // Переход в новый статус
        return (bool)$plugin->set_status($instanceid, $newstatus);
    }
}