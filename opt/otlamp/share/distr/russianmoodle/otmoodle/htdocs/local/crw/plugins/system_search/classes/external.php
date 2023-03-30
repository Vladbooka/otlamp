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
 * Веб-сервисы для поиска по витрине курсов
 *
 * @package    crw_system_search
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace crw_system_search;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
// Подключаем плагин поиска витрины
require_once($CFG->dirroot .'/local/crw/plugins/system_search/lib.php');


use external_api;
use external_function_parameters;
use external_value;
use external_multiple_structure;
use external_single_structure;

class external extends external_api
{
    
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function show_hints_parameters()
    {
        $query = new external_value(
            PARAM_RAW,
            'search query',
            VALUE_REQUIRED
        );
        
        $params = [
            'query' => $query
        ];
        
        return new external_function_parameters($params);
    }
    
    /**
     * Returns courses html-code
     * @return string courses html-code
     */
    public static function show_hints($query)
    {
        $hints = [];
        
        $hintslimit = get_config('crw_system_search', 'hints_settings_results_count');
        if ($hintslimit >= 0)
        {
            $systemsearch = new \crw_system_search('system_search');
            list($hints, $totalcount) = $systemsearch->get_hints($query, $hintslimit);
        }
        if ($totalcount > count($hints))
        {
            $hints[] = [
                'hintvalue' => $query,
                'doctitle' => \html_writer::span(get_string('show_all_hints', 'crw_system_search'), 'btn button')
            ];
        }
        return json_encode([
            'hints' => $hints
        ]);
    }
    
    /**
     * Returns description of method result value
     * @return external_value
     */
    public static function show_hints_returns()
    {
        return new external_value(PARAM_RAW, 'json-encoded hints');
    }

    
    
    
    
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function find_by_custom_field_parameters()
    {
        $search = new external_value(
            PARAM_RAW,
            'search query',
            VALUE_REQUIRED
        );
        $field = new external_value(
            PARAM_RAW,
            'field to search in',
            VALUE_REQUIRED
        );
        $page = new external_value(
            PARAM_INT,
            'page',
            VALUE_DEFAULT,
            0
        );
        $perpage = new external_value(
            PARAM_INT,
            'search query',
            VALUE_DEFAULT,
            0
        );
        
        $params = [
            'search' => $search,
            'field' => $field,
            'page' => $page,
            'perpage' => $perpage
        ];
        
        return new external_function_parameters($params);
    }
    
    /**
     * Returns courses html-code
     * @return string courses html-code
     */
    public static function find_by_custom_field($search, $field, $page=0, $perpage=0)
    {
        global $DB;
        
        $sql = ["name='".$field."'"];
        $parameters = [];
        
        $sql[] = $DB->sql_like('svalue', ':'.$field);
        $parameters[$field] = '%'.$search.'%';
        
        $records = $DB->get_records_select(
            'crw_course_properties',
            implode(' AND ', $sql),
            $parameters,
            '', 
            'DISTINCT svalue', 
            $page, 
            $perpage
        );
        
        $result = [];
        if (!empty($records))
        {
           foreach($records as $record)
           {
               $result[] = ['svalue' => $record->svalue];
           }
        }
        
        return $result;
    }
    
    /**
     * Returns description of method result value
     * @return external_value
     */
    public static function find_by_custom_field_returns()
    {
        return new external_multiple_structure(
            new external_single_structure([
                'svalue' => new external_value(PARAM_RAW, 'The search value')
            ])
        );
    }
    
    
    
    
    public static function ajax_filter_no_auth_parameters()
    {
        $args = new external_value(
            PARAM_RAW,
            'args from form',
            VALUE_REQUIRED
            );
        
        $params = [
            'params' => $args
        ];
        
        return new external_function_parameters($params);
    }
    
   
    public static function ajax_filter_no_auth($args)
    {
        global $PAGE, $OUTPUT;
        // Hack alert: Set a default URL to stop the annoying debug.
        $PAGE->set_url('/');
        $PAGE->set_context(null);
        // Hack alert: Forcing bootstrap_renderer to initiate moodle page.
        $OUTPUT->header();
        // Overwriting page_requirements_manager with the fragment one so only JS included from
        // this point is returned to the user.
        $PAGE->start_collecting_javascript_requirements();
        $data = crw_system_search_output_fragment_search(json_decode($args, true));
        $jsfooter = $PAGE->requires->get_end_code();
        return ['html' => $data, 'js' => $jsfooter];
    }
    
   
    public static function ajax_filter_no_auth_returns()
    {
        return new external_single_structure([
                'html' => new external_value(PARAM_RAW, 'HTML fragment.'),
                'js' => new external_value(PARAM_RAW, 'JavaScript fragment')
        ]);
    }
       
}