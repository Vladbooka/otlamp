<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

namespace mod_otresourcelibrary;

use core\notification;
use Exception;

require_once($CFG->libdir . '/formslib.php');

class source_form extends \moodleform
{
    
    protected function definition_edit()
    {
        $otapi = new \mod_otresourcelibrary\otapi();
        
        $sourcename = $this->_customdata['sourcename'] ?? null;
        if (is_null($sourcename))
        {
            return;
        }
        
        $mform = &$this->_form;
        
        $response = $otapi->get_structure_by_sourcename($sourcename);
        
        // Заголовок
        $mform->addElement(
            'header',
            'edit_src__'.$sourcename,
            get_string('edit_src', 'mod_otresourcelibrary', (object)[
                'sourcename' => $sourcename,
                'sourcecode' => $response['sourcecode'],
                'sourcetitle' => $response['sourcetitle']
            ])
            );
        
        // Наименование источника
        $mform->addElement('hidden', 'sourcename', $sourcename);
        $mform->addRule('sourcename', null, 'required');
        $mform->setType('sourcename', PARAM_RAW);
        
        // Код источника
        $mform->addElement('hidden', 'sourcecode', $response['sourcecode']);
        $mform->setType('sourcecode', PARAM_RAW);
        
        // Наименование типа источника
        $mform->addElement('static', 'sourcetitle', get_string('source_type', 'otresourcelibrary'), $response['sourcetitle']);
        
        $this->definition_source_structure($response['structure'], false);
        
        $mform->addElement('submit', 'edit_source', get_string('source_changes', 'otresourcelibrary', $sourcename));
    }
    
    
    
    protected function definition_delete()
    {
        $sourcename = $this->_customdata['sourcename'] ?? null;
        if (is_null($sourcename))
        {
            return;
        }
        
        $mform = &$this->_form;
        
        // Наименование источника
        $mform->addElement('hidden', 'sourcename', $sourcename);
        $mform->addRule('sourcename', null, 'required');
        $mform->setType('sourcename', PARAM_RAW);
        
        $mform->addElement('submit', 'delete_source', get_string('source_deletion', 'otresourcelibrary', $sourcename));
        
    }
    
    protected function definition_source_structure($structure, $requiredrequired=true)
    {
        $mform = &$this->_form;
        
        foreach($structure as $argname => $argdata)
        {
            if (is_array($argdata) && !empty($argdata['credential']))
            {
                $elementname = 'credentials['.$argname.']';
                $title = $argname;
                if (!empty($argdata['title']))
                {
                    $title = $argdata['title'];
                }
                
                $mform->addElement('text', $elementname, $title);
                $mform->setDefault($elementname, ($argdata['value'] ?? ''));
                
                if (!empty($argdata['required']) && $requiredrequired)
                {
                    $mform->addRule($elementname, null, 'required');
                }
                if (empty($argdata['filter']))
                {
                    $argdata['filter'] = FILTER_UNSAFE_RAW;
                }
                switch($argdata['filter'])
                {
                    case FILTER_VALIDATE_URL:
                        $paramtype = PARAM_URL;
                        break;
                    case FILTER_UNSAFE_RAW:
                    default:
                        $paramtype = PARAM_RAW;
                        break;
                }
                $mform->setType($elementname, $paramtype);
            }
        }
    }
    
    protected function definition_add()
    {
        $otapi = new \mod_otresourcelibrary\otapi();
        
        $sourcecode = $this->_customdata['sourcecode'] ?? null;
        if (is_null($sourcecode))
        {
            return;
        }
        
        $mform = &$this->_form;
            
        // Заголовок
        $mform->addElement('header', 'add_src__'.$sourcecode, $sourcecode);
        
        // Код источника
        $mform->addElement('hidden', 'sourcecode', $sourcecode);
        $mform->setType('sourcecode', PARAM_RAW);
        
        // Наименование источника
        $mform->addElement('text', 'sourcename', get_string('name_source', 'otresourcelibrary'));
        $mform->addRule('sourcename', null, 'required');
        $mform->setType('sourcename', PARAM_RAW);
        
        // Реквизиты источника
        $structure = $otapi->get_structure_by_sourcecode($sourcecode);
        $this->definition_source_structure($structure);
        
        $mform->addElement('submit', 'add_source', get_string('add_source', 'otresourcelibrary'));
    }
    
    protected function definition()
    {
        $mform = &$this->_form;
        
        
        $action = $this->_customdata['action'] ?? 'default';
        switch($action)
        {
            case 'add':
                $this->definition_add();
                break;
            case 'edit':
                $this->definition_edit();
                break;
            case 'delete':
                $this->definition_delete();
                break;
            default:
                // по дефолту отображается первый шаг добавления источника - выбор типа источника
                
                $otapi = new \mod_otresourcelibrary\otapi();
                try {
                    $sources = $otapi->get_installation_sources_names();
                    foreach($sources as $sourceinfo)
                    {
                        $sourcename = $sourceinfo['sourcename'];
                        $group = [];
                        $group[] = $mform->createElement('hidden', 'sourcename', $sourcename);
                        $group[] = $mform->createElement('select', 'activity_source', get_string('activity_source', 'otresourcelibrary'), [
                            1 => get_string('activity_source_active', 'otresourcelibrary'),
                            0 => get_string('activity_source_inactive', 'otresourcelibrary'),
                        ]);
                        
                        if (empty($sourceinfo['is_default']))
                        {
                            $group[] = $mform->createElement('submit', 'edit_source', get_string('edit_source', 'otresourcelibrary'));
                            $group[] = $mform->createElement('submit', 'delete_source', get_string('delete_source', 'otresourcelibrary'));
                        }
                        
                        $mform->addGroup($group, 'source_actions['.$sourcename.']', $sourcename);
                        $mform->setType('source_actions['.$sourcename.'][sourcename]', PARAM_RAW);
                        $mform->setDefault('source_actions['.$sourcename.'][activity_source]', $sourceinfo['is_active']);
                    }
                    $mform->addElement('submit', 'save_sources_activity', get_string('save_sources_activity', 'otresourcelibrary'));
                    
                } catch (Exception $e) {
                    debugging($e->getMessage() . '<br/>' . format_backtrace($e->getTrace()));
                    $mform->addElement('static', 'installation_sources_names_nodata', '', get_string('installation_sources_names_nodata', 'mod_otresourcelibrary'));
                }
                
                try {
                    $sourcetypes = $otapi->get_implemented_sourcetypes();
                    
                    $sourcetypesel = $mform->createElement('select', 'sourcecode', get_string('source_types', 'otresourcelibrary'), $sourcetypes);
                    $addsourcetype = $mform->createElement('submit', 'fill_source_credentials', get_string('add_source', 'otresourcelibrary'));
                    $mform->addGroup([$sourcetypesel, $addsourcetype], 'available_sources', get_string('adding_source', 'otresourcelibrary'));
                    
                } catch (Exception $e) {
                    error_log($e->getMessage() . '<br/>' . format_backtrace($e->getTrace()));
                    $mform->addElement('static', 'implemented_sourcetypes_nodata', '', get_string('implemented_sourcetypes_nodata', 'mod_otresourcelibrary'));
                }
                break ;
        }
    }
    
    public function process()
    {
        if ($formdata = $this->get_data())
        {
            if (!empty($formdata->source_actions) && is_array($formdata->source_actions))
            {
                foreach($formdata->source_actions as $source)
                {
                    // Редирект на форму редактирования источника
                    if (!empty($source['edit_source']) && !empty($source['sourcename']))
                    {
                        redirect(new \moodle_url(
                            '/mod/otresourcelibrary/manage_sources.php',
                            [
                                'action' => 'edit',
                                'sourcename' => $source['sourcename'],
                            ]
                        ));
                    }
                    
                    // Редирект на форму удаления источника
                    if (!empty($source['delete_source']) && !empty($source['sourcename']))
                    {
                        redirect(new \moodle_url(
                            '/mod/otresourcelibrary/manage_sources.php',
                            [
                                'action' => 'delete',
                                'sourcename' => $source['sourcename']
                            ]
                        ));
                    }
                }
            }
            
            // Редирект на форму добавления источника
            if (!empty($formdata->available_sources['fill_source_credentials']))
            {
                redirect(new \moodle_url(
                    '/mod/otresourcelibrary/manage_sources.php',
                    [
                        'action' => 'add',
                        'sourcecode' => $formdata->available_sources['sourcecode']
                    ]
                ));
            }
            
            // Процесс обработки добавления/редактирования источника
            if (!empty($formdata->add_source) || !empty($formdata->edit_source))
            {
                $otapi = new \mod_otresourcelibrary\otapi();
                
                try {
                    
                    $otapi->save_installation_source_credentials(
                        str_replace(['"',"'"], '', $formdata->sourcename),
                        $formdata->sourcecode,
                        $formdata->credentials
                    );
                    $this->purge_otrl_caches();
                    redirect(new \moodle_url('/mod/otresourcelibrary/manage_sources.php'));
                    
                } catch(\Exception $ex)
                {
                    $errorstringcode = !empty($formdata->edit_source) ? 'error_edit_source' : 'error_save_details';
                    notification::error(get_string($errorstringcode, 'otresourcelibrary'));
                }
            }
            
            // Процесс обработки удаления источника
            if (!empty($formdata->delete_source))
            {
                $otapi = new \mod_otresourcelibrary\otapi();
                
                try {
                    
                    $otapi->delete_installation_source($formdata->sourcename);
                    $this->purge_otrl_caches();
                    redirect(new \moodle_url('/mod/otresourcelibrary/manage_sources.php'));
                    
                } catch(\Exception $ex)
                {
                    notification::error(get_string('error_delete_source', 'otresourcelibrary'));
                }
            }
            
            // Процесс обработки управлением активности источника
            if (!empty($formdata->save_sources_activity))
            {
                $otapi = new \mod_otresourcelibrary\otapi();
                
                try {
                    $activityinfo = [];
                    $sources = $otapi->get_installation_sources_names();
                    foreach($sources as $sourceinfo)
                    {
                        $sourcename = $sourceinfo['sourcename'];
                        $activityinfo[$sourcename] = $formdata->source_actions[$sourcename]['activity_source'] ?? 1;
                    }
                    
                    $otapi->save_installation_sources_activity($activityinfo);
                    $this->purge_otrl_caches();
                    redirect(new \moodle_url('/mod/otresourcelibrary/manage_sources.php'));
                    
                } catch(\Exception $ex)
                {
                    notification::error(get_string('error_save_sources_activity', 'otresourcelibrary'));
                }
            }
            
        }
    }
    
    private function purge_otrl_caches()
    {
        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, 'mod_otresourcelibrary', 'otapi_get');
        $cache->purge();
        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, 'mod_otresourcelibrary', 'search_query_cache');
        $cache->purge();
    }
}