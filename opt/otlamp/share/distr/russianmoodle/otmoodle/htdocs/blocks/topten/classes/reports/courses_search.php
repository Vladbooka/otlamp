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
 * Блок топ-10
*
* @package    block
* @subpackage topten
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

namespace block_topten\reports;

require_once ($CFG->dirroot . '/course/renderer.php');

use block_topten\base;
use core_plugin_manager;
use moodle_exception;
use local_crw;
use html_writer;
use context_block;
use MoodleQuickForm;


class courses_search extends base
{
    // Доступные сабплагины для отображения списка курсов
    private $availableplugins = [];
    /**
     * {@inheritDoc}
     * @see \block_topten\base::is_ready()
     */
    public function is_ready()
    {
        $installlist = core_plugin_manager::instance()->get_installed_plugins('local');
        return array_key_exists('crw', $installlist);
    }

    /**
     * {@inheritDoc}
     * @see \block_topten\base::get_html()
     */
    public function get_html($data)
    {
        $html = '';
        
        if( ! empty($this->config->courses_search_renderer) &&
            array_key_exists($this->config->courses_search_renderer, $this->get_crw_course_plugins()) )
        {
            $renderer = $this->get_crw_course_plugins()[$this->config->courses_search_renderer];
        } else
        {
            if( array_key_exists('courses_list_tiles', $this->get_crw_course_plugins()) )
            {
                $renderer = $this->get_crw_course_plugins()['courses_list_tiles'];
            } else
            {
                throw new moodle_exception('renderer_invalid', 'block_topten');
            }
        }
        
        $html = $renderer->display(['courses' => $data]);
            
        return $html;
    }
    
    /**
     * Добавление собственных настроек в форму
     *
     * @param MoodleQuickForm $mform
     */
    public function definition(&$mform, $formsave = null)
    {
        $renderers = [];
        $context = context_block::instance($formsave->block->instance->id);
        
        $mform->addElement('hidden', 'contextid', $context->id);
        $mform->setType('contextid', PARAM_INT);
        
        
        $crws = '';
        if (!empty($formsave->block->config->courses_search_crws))
        {
            $crws = $formsave->block->config->courses_search_crws;
        }
        
        $mform->addElement(
            'hidden',
            'config_courses_search_crws',
            $crws
        );
        $mform->setType('config_courses_search_crws', PARAM_RAW);
        
        if( $this->is_crw_installed() )
        {
            foreach(array_keys($this->get_crw_course_plugins()) as $subpluginname)
            {
                $renderers[$subpluginname] = get_string('pluginname', 'crw_' . $subpluginname);
            }
            if( ! empty($renderers) )
            {
                $mform->addElement(
                    'select',
                    'config_courses_search_renderer',
                    get_string('courses_search_renderer', 'block_topten'),
                    $renderers
                );
                $mform->setType('config_courses_search_renderer', PARAM_TEXT);
                if( ! empty($formsave->block->config->courses_search_renderer) &&
                    array_key_exists($formsave->block->config->courses_search_renderer, $renderers) )
                {
                    $mform->setDefault('config_courses_search_renderer', $formsave->block->config->courses_search_renderer);
                }
            }
            
            $sorttypes = [
                CRW_COURSES_SORT_TYPE_COURSE_SORT => get_string('courses_sort_type_course_sort', 'local_crw'),
                CRW_COURSES_SORT_TYPE_COURSE_CREATED => get_string('courses_sort_type_course_created', 'local_crw'),
                CRW_COURSES_SORT_TYPE_COURSE_START => get_string('courses_sort_type_course_start', 'local_crw'),
                CRW_COURSES_SORT_TYPE_LEARNINGHISTORY_ENROLMENTS => get_string('courses_sort_type_learninghistory_enrolments', 'local_crw'),
                CRW_COURSES_SORT_TYPE_ACTIVE_ENROLMENTS => get_string('courses_sort_type_active_enrolments', 'local_crw'),
                CRW_COURSES_SORT_TYPE_COURSE_POPULARITY => get_string('courses_sort_type_course_popularity', 'local_crw'),
            ];
            $mform->addElement(
                'select',
                'config_courses_search_sorttype',
                get_string('courses_search_sorttype', 'block_topten'),
                $sorttypes
            );
            
            $sortdirections = [
                'ASC' => get_string('courses_sort_direction_asceding', 'local_crw'),
                'DESC' => get_string('courses_sort_direction_desceding', 'local_crw')
            ];
            $mform->addElement(
                'select',
                'config_courses_search_sortdirection',
                get_string('courses_search_sortdirection', 'block_topten'),
                $sortdirections
            );
            
            
            
            $button = html_writer::div(get_string('system_search_button', 'block_topten'), 'btn system_search_button disabled');
            $mform->addElement('static', 'system_search_button', '', $button);
        }
    }
    
    /**
     * Получить плагины витрины типа список курсов
     *
     * @return array - Массив доступных плагинов
     */
    protected function get_crw_course_plugins()
    {
        global $CFG;
        
        if (!empty($this->availableplugins))
        {
            return $this->availableplugins;
        }
        
        if (!$this->is_crw_installed())
        {
            return [];
        }
        
        $subpluginnames = core_plugin_manager::instance()->get_installed_plugins('crw');
        foreach(array_keys($subpluginnames) as $subpluginname)
        {
            $filename = $CFG->dirroot . '/local/crw/plugins/' . $subpluginname . '/lib.php';
            if( file_exists($filename) )
            {
                require_once($filename);
                $subpluginclassname = 'crw_'.$subpluginname;
                if(class_exists($subpluginclassname))
                {
                    $subplugin = new $subpluginclassname($subpluginname);
                    if($subplugin->get_type() == CRW_PLUGIN_TYPE_COURSES_LIST)
                    {
                        $this->availableplugins[$subpluginname] = $subplugin;
                    }
                }
            }
        }
        return $this->availableplugins;
    }
    
    /**
     * Проверить установлен ли плагин витрины
     *
     * @return boolean
     */
    protected function is_crw_installed()
    {
        $installlist = core_plugin_manager::instance()->get_installed_plugins('local');
        if ( array_key_exists('crw', $installlist) )
        {
            return true;
        }
    
        return false;
    }
    
    function output_fragment_search_form($args)
    {
        global $PAGE, $CFG;
        
        $PAGE->set_context(\context_system::instance());
        
        $formdata = [];
        if (!empty($args['formdata'])) {
            $serialiseddata = json_decode($args['formdata']);
            parse_str($serialiseddata, $formdata);
        }
        

        require_once($CFG->dirroot . '/local/crw/lib.php');
        
        $crwoptions = [
            'forced_showcase_slots' => ['system_search'],
            'pluginsettings' => [
                'system_search' => [
                    'settings_query_string_role' => 'name',
                    'settings_fullsearch_only' => true
                ]
            ]
        ];
        if( ! empty($args['crws']) )
        {
            $crwoptions['crws'] = $args['crws'];
        }
        
        $crw = new local_crw($crwoptions);
        $pluginsearch = array_shift($crw->slots['showcase']);
        $displayoptions = [
            'ajaxformdata' => $formdata,
            'formaction' => null,
            'formredirect' => false,
            'return_process_result' => true
        ];
        if( ! empty($args['crws']) )
        {
            $displayoptions['crws'] = $args['crws'];
        }
        list($crws, $formhtml) = $pluginsearch->display($displayoptions);
        
        return json_encode([
            'html' => $formhtml,
            'crws' => $crws
        ]);
    }
    /**
     * Хедер по умолчанию
     * 
     * {@inheritDoc}
     * @see \block_topten\base::get_default_header()
     */
    public static function get_default_header($small = false)
    {
        return get_string($small ? 'courses_search_header' : 'courses_search', 'block_topten');
    }
    /**
     * Получение даты для кеширования
     * 
     * {@inheritDoc}
     * @see \block_topten\base::get_cache_data()
     */
    public function get_cache_data($oldcache = false)
    {
        global $CFG;
        require_once "$CFG->dirroot/local/crw/lib.php";
        
        $options = [
            'page' => 0,
            'limitnum' => $this->config->rating_number,
            'forced_showcase_slots' => ['system_search'],
            'pluginsettings' => [
                'system_search' => [
                    'settings_query_string_role' => 'name',
                    'form_render_denied' => true
                ]
            ],
            'display_invested_courses' => true
        ];
        if (!empty($this->config->courses_search_sorttype))
        {
            $options['coursessorttype'] = $this->config->courses_search_sorttype;
        }
        if (!empty($this->config->courses_search_sortdirection))
        {
            $options['coursessortdirection'] = $this->config->courses_search_sortdirection;
        }
        if (!empty($this->config->courses_search_crws))
        {
            $options['crws'] = $this->config->courses_search_crws;
        } else
        {
            $options['crws'] = '';
        }
        
        $crw = new local_crw($options);
        return $crw->get_courses_slice();
    }
    /**
     * {@inheritDoc}
     * @see \block_topten\base::is_cached()
     */
    public function is_cached()
    {
        return true;
    }

}
