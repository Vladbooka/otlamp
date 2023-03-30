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

/**
 * Слайдер изображений. Класс слайда с изображением.
 *
 * @package    local_opentechnology
 * @subpackage otcomponent_otslider
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace otcomponent_otslider\slides\types;

use otcomponent_otslider\slides\base as slidebase;
use stdClass;
use renderable;
use renderer_base;
use templatable;


class html extends slidebase implements renderable, templatable
{
    /**
     * Настройки для текущего слайда
     * 
     * @var stdClass
     */
    private $slideoptions;
    
    /**
     * Конструкт устанавливает значения по умолчанию
     */
    function __construct()
    {
        $slideoptions = new stdClass();
        $slideoptions->data = '';
        $this->slideoptions = $slideoptions;
    }
 
    public function __isset($name)
    {
        return isset($this->slideoptions->{$name});
    }
    
    /**
     * Устанавливает настройки для текущего слайда
     * 
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value)
    {
        if(property_exists($this->slideoptions, $name))
        {
            $this->slideoptions->{$name} = $value;
        }
        
    }
    /**
     * 
     * {@inheritDoc}
     * @see templatable::export_for_template()
     */
    public function export_for_template(renderer_base $output)
    {
        return ['data' => $this->slideoptions->data];
    }
    /**
     * 
     * {@inheritDoc}
     * @see \otcomponent_otslider\slides\base::get_template_name()
     */
    public function get_template_name()
    {
        return 'html';
    }

}