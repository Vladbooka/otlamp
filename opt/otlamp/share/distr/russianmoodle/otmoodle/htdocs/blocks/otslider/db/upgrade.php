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
 * Слайдер изображений. Процесс обновления плагина.
 *
 * @package    block
 * @subpackage otslider
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_block_otslider_upgrade($oldversion)
{
    global $DB;
    $dbman = $DB->get_manager();
    
    
    if ( $oldversion < 2017072800 ) 
    {
        $blockinstances = $DB->get_records('block_instances', ['blockname'=>'otslider']);
        foreach($blockinstances as $blockinstance)
        {
            $bi = block_instance('otslider', $blockinstance);
            if ( ! empty($bi->config->parallax) )
            {
                $slides = $DB->get_records('block_otslider_slides', [
                    'blockinstanceid' => $blockinstance->id,
                    'type' => 'image'
                ]);
                if ( ! empty($slides) )
                {
                    foreach ( $slides as $slide )
                    {
                        $parallaxoption = $DB->get_record('block_otslider_slide_options', [
                            'slideid' => $slide->id,
                            'name' => 'parallax'
                        ]);
                        if( empty($parallaxoption) )
                        {
                            $parallaxoption = new stdClass();
                            $parallaxoption->slideid = $slide->id;
                            $parallaxoption->name = 'parallax';
                            $parallaxoption->shortdata= -100;
                            $DB->insert_record('block_otslider_slide_options', $parallaxoption);
                        }
                    }
                }
            }
        }
        
    }
    
    if ( $oldversion < 2020090100 )
    {
        $blockinstances = $DB->get_records('block_instances', ['blockname'=>'otslider']);
        foreach($blockinstances as $blockinstance) {
            $bi = block_instance('otslider', $blockinstance);
            if (isset($bi->config) && property_exists($bi->config, 'parallax') ) {
                unset($bi->config->parallax);
                $DB->update_record('block_instances', ['id' => $blockinstance->id,
                    'configdata' => base64_encode(serialize($bi->config)), 'timemodified' => time()]);
            }
        } 
    }
    
    return true;
}