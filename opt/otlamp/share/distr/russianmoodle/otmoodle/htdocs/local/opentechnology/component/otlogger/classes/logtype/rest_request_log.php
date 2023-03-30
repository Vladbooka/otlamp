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

namespace otcomponent_otlogger\logtype;

use otcomponent_otlogger\log_base;

defined('MOODLE_INTERNAL') || die();

/**
 * Родительский класс для всех логов рест-запросов
 * 
 * 
 * @package local_opentechnology
 * @subpackage otcomponent_otlogger
 * @property string request_url  URL запроса
 * @property string request_method метод запроса
 * @property string request_body request_body, пустое для get-запросов
 * @property int response_httpcode http-код ответа
 * @property string response_body тело ответа, данные 
 * 
 */

abstract class rest_request_log extends log_base{
    
    /*
     * Тип лога
     */
    
    const LOG_TYPE = 'rest_request';
    
    /**
     * Получить возможные свойства лога
     *
     * @return array список доступных свойств лога - логируемых данных
     */
    
    public static function get_available_properties(){
        return [
            'request_url',
            'request_method',
            'request_body',
            'response_httpcode',
            'response_body',
        ];
    }
        
}