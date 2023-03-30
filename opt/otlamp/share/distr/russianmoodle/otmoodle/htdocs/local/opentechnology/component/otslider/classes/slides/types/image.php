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

use moodle_url;
use otcomponent_otslider\slides\base as slidebase;
use stdClass;
use renderable;
use renderer_base;
use templatable;


class image extends slidebase implements renderable, templatable
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
        $slideoptions->zoomview = false; 
        $slideoptions->title = '';
        $slideoptions->description = '';
        $slideoptions->captionalign = 'left';
        $slideoptions->summary = '';
        $slideoptions->captiontop = 2;
        $slideoptions->captionright = 20;
        $slideoptions->captionbottom = 2;
        $slideoptions->captionleft = 8;
        $slideoptions->parallax = 0;
        $slideoptions->backgroundpositiontop = 50;
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
        if(property_exists($this->slideoptions, $name))
        {
            $this->slideoptions->{$name} = $value;
        }
        
    }
    /**
     * 
     * @param renderer_base $output
     * @return stdClass[]|string[]|string[][][]|NULL[]|moodle_url[]
     */
    public function export_for_template(renderer_base $output)
    {
        // Получение менеджера файлов
        $fs = get_file_storage();
        $image = $this->slideoptions->image;
        $nophotourlinst = new moodle_url('/local/opentechnology/component/otslider/pix/no-photo.png');
        $imageurl = $nophotourlinst->out();
        $this->slideoptions->hasvalidimage = false;
        if (property_exists($image, 'itemid')) {
            // Получение изображения слайда
            $files = $fs->get_area_files(
                $image->contextid,
                $image->component,
                $image->filearea,
                $image->itemid
            );
            foreach ($files as $file) {
                if ($file->is_valid_image()) {// Изображение найдено
                    $imageurl = moodle_url::make_pluginfile_url(
                        $file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea(),
                        $file->get_itemid(),
                        $file->get_filepath(),
                        $file->get_filename()
                    );
                    $this->slideoptions->hasvalidimage = true;
                }
            }
        }
        $align = $this->slideoptions->captionalign;
        $alignflex = $align == 'left' ? 'flex-start' : 'flex-end';
        
        $top = $this->slideoptions->captiontop;
        $right = $this->slideoptions->captionright;
        $bottom = $this->slideoptions->captionbottom;
        $left = $this->slideoptions->captionleft;
        $styles = [
            ['style' => 'max-width: '.(100 - $right - $left).'%'],
            ['style' => 'width: '.(100 - $right - $left).'%'],
            ['style' => 'left: '.$left.'%'],
            ['style' => 'padding-top: '.$top.'%'],
            ['style' => 'padding-bottom: '.$bottom.'%'],
            ['style' => 'align-items: '. $alignflex],
            ['style' => 'text-align: '. $align]
        ];
        return ['imageurl' => $imageurl, 'slideoptions' => $this->slideoptions, 
            'alignflex' => $alignflex, "styles" => $styles];
    }
    /**
     * 
     * {@inheritDoc}
     * @see \otcomponent_otslider\slides\base::get_template_name()
     */
    public function get_template_name()
    {
        return 'image';
    }
 
}