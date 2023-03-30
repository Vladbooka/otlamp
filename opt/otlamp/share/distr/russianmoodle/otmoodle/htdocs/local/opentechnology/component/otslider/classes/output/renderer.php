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
 * Рендер
 * 
 * @package    local_opentechnology
 * @subpackage otcomponent_otslider
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace otcomponent_otslider\output;
defined('MOODLE_INTERNAL') || die();

class renderer extends \plugin_renderer_base {
    /**
     * Рендер слайдера
     * 
     * @param array $blocks
     * @param array $settings
     * @return string|boolean
     */
    public function render_slider($blocks, $settings, $objectid) {
        $i = 0;
        $slidecontent = [];
        // Формируем html слайдов
        foreach ($blocks as $block) {
            $position = '';
            switch ($i) {
                case 0:
                    if ($settings['slidetype'] == 'triple') {
                        $position = 'deactivated';
                    } else {
                        $position = 'active';
                    }
                    break;
                case 1:
                    if ($settings['slidetype'] == 'triple') {
                        $position = 'active';
                    }
                    break;
                case 2:
                    if ($settings['slidetype'] == 'triple') {
                        $position = 'following forward';
                    }
                    break;
            }
            $slidecontent[] =[
                'html' => $this->render_from_template(
                    'otcomponent_otslider/' . $block->get_template_name(),
                    $block->export_for_template($this)
                    ),
                'active' => $position
            ];
            $i++;
        }
        // Враппер слайдера
        return $this->render_from_template(
            'otcomponent_otslider/slider_wrapper', 
            [
                'data' => $slidecontent, 
                'settings' => $this->making_slider_attributes($settings, $i),
                'objectid' => $objectid
            ]
            );
    }
    
    /**
     * Формирует настройки слайдера
     * 
     * @param array $settings
     * @return string[][]
     */
    private function making_slider_attributes($settings, $slidesamount)
    {
        $attributes = [];
        // Принудительное выключение движка слайдера
        $forcedisabled = false;
        
        if (!empty($settings['slidername'])) {
            $attributes[] = [
                'attr' => 'data-name = "' . $settings['slidername'] . '"' 
            ];
        }
        if (!empty($settings['height'])) {
            $height = 0;
            $paddingbottom = (int)$settings['height'];
        } elseif ($settings['slidetype'] == 'simple') {
            $height = 'auto';
            $paddingbottom = 0;
        } else {
            $height = 0;
            $paddingbottom = 0;
            $forcedisabled = true;
        }
        $attributes[] = [
            'attr' => 'style = "padding-bottom: ' . $paddingbottom . '%; height: ' . $height.'"'
        ];
        $dataheight = $paddingbottom > 0 ? $paddingbottom : $height;
        $attributes[] = [
            'attr' => 'data-height = "' . $dataheight . '"'
            
        ];
        $attributes[] = [
            'attr' => 'data-slidetype = "' . (string)$settings['slidetype'] . '"'
        ];
        $dataslidespeed = $settings['slidescroll'] ? 0 : round((float)$settings['slidespeed'] * 1000);
        $attributes[] = [
            'attr' => 'data-slidespeed = "' . $dataslidespeed . '"'
        ];
        if ($settings['navigation'] && !$settings['slidescroll']) {
            $attributes[] = [
                'attr' => 'data-navigation = "1"'
            ];
        } else {
            $attributes[] = [
                'attr' => 'data-navigation = "0"'
            ];
        }
        if ($settings['navigationpoints'] && !$settings['slidescroll']) {
            $attributes[] = [
                'attr' => 'data-navigationpoints = "1"'
            ];
        } else {
            $attributes[] = [
                'attr' => 'data-navigationpoints = "0"'
            ];
        }
        if ($settings['slidescroll'])  {
            $attributes[] = [
                'attr' => 'data-slidescroll = "1"'
            ];
        } else {
            $attributes[] = [
                'attr' => 'data-slidescroll = "0"'
            ];
        }
        if ($settings['blockreplace']) {
            $attributes[] = [
                'attr' => 'data-replace_requested = "1"'
            ];
        }
        $attributes[] = [
            'attr' => 'data-arrowtype = "' . (string)$settings['arrowtype'] . '"'
        ];
        if ($settings['proportionalheight']) {
            $attributes[] = [
                'attr' => 'data-proportionalheight = "1"'
            ];
        } else {
            $attributes[] = [
                'attr' => 'data-proportionalheight = "0"'
            ];
        }
        if (((($settings['slidetype'] == 'triple') && ($slidesamount < 4)) || ($slidesamount < 2)) 
            || $forcedisabled) 
        {
            $attributes[] = [
                'attr' => 'data-engineenabled = "0"'
            ];
        } else {
            $attributes[] = [
                'attr' => 'data-engineenabled = "1"'
            ];
        }
        return $attributes;
    }
}