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


namespace local_crw;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
// Подключаем витрину
require_once($CFG->dirroot .'/local/crw/lib.php');

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
    public static function get_courses_parameters()
    {
        $plugincode = new external_value(
            PARAM_ALPHANUMEXT,
            'crw plugin code',
            VALUE_REQUIRED
        );
        $categoryid = new external_value(
            PARAM_INT,
            'ID of courses category',
            VALUE_REQUIRED
        );
        $perpage = new external_value(
            PARAM_INT,
            'number of courses displayed on one page',
            VALUE_REQUIRED
        );
        $page = new external_value(
            PARAM_INT,
            'page number',
            VALUE_REQUIRED
        );
        $getparams = new external_value(
            PARAM_RAW,
            'params',
            VALUE_REQUIRED
            );
        
        $displayfromsubcategories = new external_value(
            PARAM_BOOL,
            'display_from_subcategories',
            VALUE_REQUIRED
        );
        
        $searchquery = new external_value(
            PARAM_RAW,
            'searchquery',
            VALUE_REQUIRED
        );

        $userid = new external_value(
            PARAM_INT,
            'user to display user courses',
            VALUE_DEFAULT,
            null
        );
        
        $usercoursesnotactive = new external_value(
            PARAM_BOOL,
            'should not active courses be displayed',
            VALUE_DEFAULT,
            null
        );
        
        $params = [
            'plugincode' => $plugincode,
            'categoryid' => $categoryid,
            'perpage' => $perpage,
            'page' => $page,
            'params' => $getparams,
            'displayfromsubcategories' => $displayfromsubcategories,
            'searchquery' => $searchquery,
            'userid' => $userid,
            'usercoursesnotactive' => $usercoursesnotactive
        ];
        
        return new external_function_parameters($params);
    }
    
    /**
     * Returns courses html-code
     * @return string courses html-code
     */
    public static function get_courses($plugincode, $categoryid, $perpage, $page, $getparams, $displayfromsubcategories, $searchquery, $userid=null, $usercoursesnotactive=null)
    {
        global $PAGE;
        
        $PAGE->set_context(\context_system::instance());
        
        $options = (array)json_decode($getparams);
        $options['cid'] = $categoryid;
        $options['limitnum'] = $perpage;
        $options['page'] = $page;
        $options['ajax'] = true;
        $options['display_invested_courses'] = $displayfromsubcategories;
        $options['crws'] = $searchquery;
        
        if (!is_null($userid))
        {
            $options['userid'] = $userid;
        }
        if (!is_null($usercoursesnotactive))
        {
            $options['user_courses_add_not_active'] = $usercoursesnotactive;
        }
        
        // Получаем плагин витрины
        $showcase = new local_crw($options);
        
        return json_encode([
            'num_loaded_courses' => count($showcase->get_courses_slice()),
            'html' => $showcase->get_courses_html($plugincode)
        ]);
    }
    
    /**
     * Returns description of method result value
     * @return external_value
     */
    public static function get_courses_returns()
    {
        return new external_value(PARAM_RAW, 'courses html-code');
    }
}