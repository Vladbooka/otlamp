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
 * Рендер слайдера. Версии.
 * 
 * @package    local_opentechnology
 * @subpackage otcomponent_otslider
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace otcomponent_otslider;


use otcomponent_otslider\slides\base as slidebase;
use core\notification;

class slider
{
    /**
     * Набор слайдов
     */
    private $slides = [];
    /**
     * Настройки слайдера по умолчанию
     * 
     * @var array
     */
    private $sliderconfig = [
        'enabled' => true, 
        'slidetype' => 'fadein', 
        'slidespeed' => '3', 
        'navigation' => false, 
        'navigationpoints' => false,
        'slidescroll' => false, 
        'arrowtype' => 'thick', 
        'proportionalheight' => true,  
        'height' => 20,
        'slidername' => '',
        'blockreplace' => false
    ];
    
    /**
     * Конструкт устанавливает настройки слайдера
     */
    function __construct($sliderconfig) {
        if (! empty($sliderconfig)) {
            foreach ($sliderconfig as $cfgkey => $cfgvalue) {
                if (array_key_exists($cfgkey, $this->sliderconfig)) {
                    $this->sliderconfig[$cfgkey] = $cfgvalue;
                } elseif ( $cfgkey != 'themeprofile' ) {
                    notification::error("Passed a setting $cfgkey that is missing in slider");
                }
            }
        }
    }
    
    /**
     * Получить экземпляр класса слайда по типу
     * 
     * @param string $type
     * @return slidebase[]
     */
    public function get_slide_class($type) {
        $slideclass = '\\otcomponent_otslider\\slides\\types\\'.$type;
        if (!class_exists($slideclass)) {// Класс не найден
            notification::error('Slider class "'.$type.'" not found');
        }
        return $this->slides[] = new $slideclass();
    }
    
    /**
     * Получение html слайдера
     * 
     * @return string html
     */
    public function get_slider_html() {
        global $PAGE;
        if (empty($this->slides)) {
            return '';
        }
        $objectid = spl_object_hash($this);
        $PAGE->requires->js('/local/opentechnology/component/otslider/slider.js');
        $PAGE->requires->js_call_amd(
            'otcomponent_otslider/otslider_zoom_view', 
            'init', 
            [$objectid]
            );
        
        $output = $PAGE->get_renderer('otcomponent_otslider');
        return $output->render_slider($this->slides, $this->sliderconfig, $objectid);
        
    }
    /**
     * Получить количество слайдов
     * 
     * @return number
     */
    public function count_slides() {
        return count($this->slides);
    }
}