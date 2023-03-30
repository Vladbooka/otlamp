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
 * Настраиваемые формы
 *
 * @package    block_otcustomform
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace block_otcustomform;

require_once(dirname(realpath(__FILE__)).'/../../../config.php');
require_once("$CFG->libdir/externallib.php");
require_once("$CFG->libdir/formslib.php");
require_once("$CFG->libdir/accesslib.php");
require_once($CFG->dirroot . "/local/opentechnology/component/customclass/classes/parsers/form/parser.php");
require_once($CFG->dirroot . "/local/opentechnology/component/customclass/classes/utils.php");
require_once($CFG->dirroot . "/blocks/otcustomform/classes/utils.php");

defined('MOODLE_INTERNAL') || die();

use external_api;
use external_description;
use external_function_parameters;
use external_value;
use stdClass;
use context_system;

class external extends external_api
{
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function save_data_parameters()
    {
        $params = [
            'id' => new external_value(
                    PARAM_INT,
                    'JSON-encoded data params',
                    VALUE_REQUIRED
                    ),
            
            'data' => new external_value(
                PARAM_RAW,
                'JSON-encoded data params',
                VALUE_REQUIRED
            )
        ];
        
        return new external_function_parameters($params);
    }
    
    /**
     * Сохранение ответа на форму
     * 
     * @return boolean
     */
    public static function save_data($id, $data)
    {
        global $USER, $PAGE, $DB;
        $PAGE->set_context(context_system::instance());
        
        $data = (array)json_decode($data);
        if ( empty($data) || ! is_array($data))
        {
            return false;
        }
        
        // переконвертация необходима для корректной обработки вложенных массивов
        $newdata = [];
        parse_str(http_build_query($data, null, '&'), $newdata);
        
        $instance = $DB->get_record('block_instances', ['id' => $id]);
        $pinstance = block_instance('otcustomform', $instance);
        if ( ! utils::is_form_exists($pinstance->config->customformid) )
        {
            return false;
        }
        
        $form = utils::get_form($pinstance->config->customformid, $newdata);
        if ( $formdata = $form->get_data() )
        {
            $record = new stdClass();
            $record->customformid = $pinstance->config->customformid;
            $record->data = json_encode($formdata);
            $record->userid = ! empty($USER->id) ? $USER->id : 0;
            $record->timecreated = time();
            
            return (bool)utils::save_respone_record($record);
        }
        
        return false;
    }
    
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function save_data_returns()
    {
        return new external_value(PARAM_BOOL, 'Was data saved');
    }
}