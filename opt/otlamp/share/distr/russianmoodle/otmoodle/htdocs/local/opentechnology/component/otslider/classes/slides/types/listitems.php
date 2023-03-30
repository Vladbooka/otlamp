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
 * Слайдер. Класс слайда со списком.
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


class listitems extends slidebase implements renderable, templatable
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
        $slideoptions->title = '';
        $slideoptions->items = '';
        $slideoptions->rendermode = 'checkboxes';
        $slideoptions->image = new stdClass();
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
        if (property_exists($this->slideoptions, $name))
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
        // Получение менеджера файлов
        $fs = get_file_storage();
        $image = $this->slideoptions->image;
        // Получение изображения слайда
        $files = $fs->get_area_files(
            $image->contextid,
            $image->component,
            $image->filearea,
            $image->itemid
        );
        $imageurl = null;
        foreach ($files as $file) {
            if ($file->is_valid_image()) {// Изображение найдено
                $imageurl = \moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
                    );
            }
        }      
        $items = explode(PHP_EOL, $this->slideoptions->items);
        $i = 0;
        foreach ($items as $key => $item) {
            $class = '';
            if (trim($item) == '') {
                $class = (! empty($class) ? $class : '') . ' fakeitem';
            }
            if ($i + (3 - $i % 3) == (count($items) - count($items)%3 + (count($items)%3 == 0 ? 0 : 3))) {
                $class = (! empty($class) ? $class : '') . ' lastline';
            }
            $items[$key] = ['value' => $item, 'class' => $class];
            $i++;
        }
        $titled = trim($this->slideoptions->title) == '' ? '' : ' titled';
        return [
            'items' => $items, 
            'imageurl' => $imageurl, 
            'slideoptions' => $this->slideoptions, 
            'titled' => $titled
            
        ];
    }
    /**
     * 
     * {@inheritDoc}
     * @see \otcomponent_otslider\slides\base::get_template_name()
     */
    public function get_template_name()
    {
        return 'listitems';
    }
}