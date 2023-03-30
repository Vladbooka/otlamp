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
 * Resource Library
 *
 * @package    mod_otresourcelibrary
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otresourcelibrary;

defined('MOODLE_INTERNAL') || die();

require_once ("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/mod/otresourcelibrary/lib.php');

use external_api;
use external_function_parameters;
use external_value;
use context_course;

class external extends external_api
{
    /**
     * Поиск материала
     *
     * @param string $q
     * @param int $showstartid
     * @param int $showquantity
     * @param int $categoryid
     * @param string $sourcenames
     * @return string
     */
    public static function insert_search_results($courseid, $q, $showstartid, $showquantity, $categoryid = null, $sourcenames = null)
    {
        $data = [];
        
        list($resourcesslice, $totalbyquery) = otresourcelibrary_find_resources($q, $categoryid, $sourcenames, $showstartid, $showquantity);
        // Проверка права порсмотра материала по параметрам
        $viewbyparameter = has_capability('mod/otresourcelibrary:viewbyparameter', context_course::instance($courseid));
        $resultnum = 0;
        //Приведем к шаблонопригодному виду
        foreach ($resourcesslice as $resourcedata) {
            
            $sourcename = $resourcedata['properties']['sourcename'];
            
            $resource = [
                'key' => base64_encode(json_encode([
                    'sourcename' => $sourcename,
                    'resourceid' => $resourcedata['properties']['id']
                ])),
                'resultnum' => $showstartid+(++$resultnum),
                'sourcename' => $sourcename,
                'allowviewbyparameter' => $viewbyparameter,
                'features' => $resourcedata['features'],
                'printable_properties' => [],
            ];
            
            foreach($resourcedata['printable'] as $propcode=>$name)
            {
                $resource['printable_properties'][] = [
                    $propcode => true,
                    'code' => $propcode,
                    'name' => $name,
                    'value' => preg_replace(
                        '/\%b\%(.*?)\%\/b\%/ui',
                        '<span class="search-match">${1}</span>',
                        $resourcedata['properties'][$propcode]
                    )
                ];
            }
            
            $data[] = $resource;
        }
        
        return base64_encode(json_encode([
            'resources' => $data,
            'more_results' => $totalbyquery > ($showstartid + $showquantity),
            'no_data' => empty($data)
        ]));
    }

    public static function insert_search_results_parameters()
    {
        $courseid     = new external_value(PARAM_INT, '$course id', VALUE_REQUIRED);
        $q = new external_value(PARAM_TEXT, 'search phrase', VALUE_REQUIRED);
        $showstartid  = new external_value(PARAM_INT, 'start id for search', VALUE_REQUIRED);
        $showquantity = new external_value(PARAM_INT, 'return material quantity', VALUE_REQUIRED);
        $categoryid   = new external_value(PARAM_INT, 'category id', VALUE_DEFAULT);
        $sourcenames  = new external_value(PARAM_RAW, 'source names', VALUE_DEFAULT);
        $params = [
            'courseid' => $courseid,
            'q' => $q,
            'showstartid' => $showstartid,
            'showquantity' => $showquantity,
            'categoryid' => $categoryid,
            'sourcenames' => $sourcenames
        ];
        return new external_function_parameters($params);
    }

    public static function insert_search_results_returns()
    {
        return new external_value(PARAM_RAW, 'data to rendering');
    }

    /**
     * Может обновлять мета данные
     *
     * @param array $data
     * @return string
     */
    public static function insert_search_header($data=[])
    {
        return base64_encode(json_encode(
                ['meta_data' => 'Какие-то мета данные']
            ));
    }

    public static function insert_search_header_parameters()
    {
        $data = new external_value(PARAM_RAW, 'data', VALUE_DEFAULT, []);
        $params = [
            'data' => $data
        ];
        return new external_function_parameters($params);
    }

    public static function insert_search_header_returns()
    {
        return new external_value(PARAM_RAW, 'data to rendering');
    }
    
    /**
     * Формирование категорий 0 уровня
     *
     * @param string $source
     * @return string
     */
    public static function insert_category_selection($source)
    {
        $data = [];
        $otapi = new \mod_otresourcelibrary\otapi();
        $categories = $otapi->get_source_categories($source, null);
        if (array_key_exists($source, $categories))
            foreach ($categories[$source] as $category) {
            $category['name'] = htmlspecialchars_decode($category['name']);
            $data[] = [
                'hassubcat' => !empty($category['has_children']),
                'category' => $category,
                'key' => base64_encode(json_encode([
                    'sourcename' => $source,
                    'catid' => $category['id'],
                    'parentid' => $category['parentid']
                ]))
            ];
        }
        return base64_encode(json_encode(['cat1nd' => $data, 'has_categories' => !empty($data)]));
    }
    
    public static function insert_category_selection_parameters()
    {
        $sourcename = new external_value(PARAM_RAW, 'source name', VALUE_REQUIRED);
        $params = [
            'sourcename' => $sourcename,
        ];
        return new external_function_parameters($params);
    }
    
    public static function insert_category_selection_returns()
    {
        return new external_value(PARAM_RAW, 'data to rendering');
    }
    /**
     * Формирование списка ресурсов
     *
     * @return string
     */
    public static function insert_section_selection()
    {
//         $sourse = [];
        $otapi = new \mod_otresourcelibrary\otapi();
        $sourcesinfo = $otapi->get_installation_sources_names();
//         foreach ($sourcesinfo as $sourceinfo) {
//             $sourse[] = ['sourcesname' => $sourcesname];
//         }
        return base64_encode(json_encode(['sourcesinfo' => $sourcesinfo]));
    }
    
    public static function insert_section_selection_parameters()
    {
        return new external_function_parameters([]);
    }
    
    public static function insert_section_selection_returns()
    {
        return new external_value(PARAM_RAW, 'data to rendering');
    }
    /**
     * Формирование подкатегории
     *
     * @param string $source
     * @param int $parentid
     * @return string
     */
    public static function insert_subcategories($source, $parentid)
    {
        $data = [];
        $otapi = new \mod_otresourcelibrary\otapi();
        $subcategories = $otapi->get_source_categories($source, $parentid);

        $key = array_keys($subcategories)[0];
        foreach ($subcategories[$key] as $subcategory) {
            $subcategory['name'] = htmlspecialchars_decode($subcategory['name']);
//             $subcats = $otapi->get_source_categories($source, $subcategory['id']);
//             $hassubcat = true;
//             if (empty($subcats[array_keys($subcats)[0]])) {
//                 $hassubcat = false;
//             }
            $data[] = [
                'hassubcat' => !empty($subcategory['has_children']),
                'subcategory' => $subcategory,
                'key' => base64_encode(json_encode([
                    'sourcename' => $source,
                    'catid' => $subcategory['id'],
                    'parentid' => $subcategory['parentid']
                ]))
            ];
        }
        return base64_encode(json_encode(['subcategories' => $data]));
    }
    
    public static function insert_subcategories_parameters()
    {
        $sourcename = new external_value(PARAM_RAW, 'source name', VALUE_REQUIRED);
        $parentid = new external_value(PARAM_INT, 'parent id', VALUE_REQUIRED);
        $params = [
            'sourcename' => $sourcename,
            'parentid' => $parentid
        ];
        return new external_function_parameters($params);
    }
    
    public static function insert_subcategories_returns()
    {
        return new external_value(PARAM_RAW, 'data to rendering');
    }
}
