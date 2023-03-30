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
 * Overridden fontawesome icons.
 *
 * @package     theme_classic
 * @copyright   2019 Moodle
 * @author      Bas Brands <bas@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace theme_opentechnology\output;

use renderer_base;
use pix_icon;
use core\output\icon_system;
use core\output\icon_system_standard;

defined('MOODLE_INTERNAL') || die();

/**
 * Class overriding some of the Moodle default FontAwesome icons.
 *
 * @package    theme_classic
 * @copyright  2019 Moodle
 * @author     Bas Brands <bas@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class icon_system_fontawesome extends \theme_classic\output\icon_system_fontawesome {
    
    
    public function render_pix_icon(renderer_base $output, pix_icon $icon) {
        
        // массив изображений, для которых не устраивает имеющийся вариант fontawesome-иконки
        // в ключе - оригинальный код, в значении - оно же или то, которое надо использовать, если переопределяем
        $iconmap = [
            'core:t/message' => 'theme_opentechnology:message',
            'core:i/window_close' => 'core:t/dockclose',
            'core:a/search' => 'core:a/search',
            'core:t/block_to_dock' => 'core:t/block_to_dock',
            'core:t/dock_to_block' => 'core:t/dock_to_block',
        ];
        
        $iconcomponent = $icon->component;
        if (empty($iconcomponent) || $iconcomponent === 'moodle') {
            $iconcomponent = 'core';
        }
        $pixcode = $iconcomponent.':'.$icon->pix;
        if (array_key_exists($pixcode, $iconmap))
        {
            list($icon->component, $icon->pix) = explode(':', $iconmap[$pixcode], 2);
            $icon->attributes['class'] = 'oticon';
            $iss = icon_system::instance(icon_system::STANDARD);
            return $iss->render_pix_icon($output, $icon);
        }
        
        return parent::render_pix_icon($output, $icon);
    }
}
