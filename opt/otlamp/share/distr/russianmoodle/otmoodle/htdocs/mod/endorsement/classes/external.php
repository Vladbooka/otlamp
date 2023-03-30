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
 * Блок Надо проверить. Веб-сервисы
 *
 * @package    block_notgraded
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace mod_endorsement;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_value;
use local_crw;

class external extends external_api
{
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_statuses_parameters()
    {
        return new external_function_parameters([]);
    }
    
    /**
     * Returns JSON-encoded array of statuses (code => name)
     * @return string JSON-encoded array of statuses (code => name)
     */
    public static function get_statuses()
    {
        return json_encode(endorsements::get_statuses());
    }
    
    /**
     * Returns description of method result value
     * @return external_value
     */
    public static function get_statuses_returns()
    {
        return new external_value(PARAM_RAW, 'JSON-encoded array of statuses (code => name)');
    }
    
    
    
    
    
    
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function set_status_parameters()
    {
        $itemid = new external_value(
            PARAM_INT,
            'Endorsement itemid',
            VALUE_REQUIRED
        );
        
        $statuscode = new external_value(
            PARAM_ALPHA,
            'Status code to set to endorsement item',
            VALUE_REQUIRED
        );
        
        $params = [
            'itemid' => $itemid,
            'statuscode' => $statuscode,
        ];
        return new external_function_parameters($params);
    }
    
    /**
     * Returns TRUE if status was updated or FALSE if not
     * @return bool
     */
    public static function set_status($itemid, $statuscode)
    {
        $endorsement = local_crw\feedback\item::get($itemid);
        $modulecontext = \context::instance_by_id($endorsement->contextid);
        if (!has_capability('mod/endorsement:moderate_endorsements', $modulecontext))
        {
            return false;
        }
        
        $endorsement->status = $statuscode;
        return (bool)$endorsement->save();
    }
    
    /**
     * Returns description of method result value
     * @return external_value
     */
    public static function set_status_returns()
    {
        return new external_value(PARAM_BOOL, 'Result of operation');
    }
}