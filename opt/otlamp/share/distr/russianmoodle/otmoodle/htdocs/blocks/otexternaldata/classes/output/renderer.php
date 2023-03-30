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
 * Внешние данные
 *
 * @package    block_otexternaldata
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_otexternaldata\output;
defined('MOODLE_INTERNAL') || die;

use Mustache_Exception_UnknownHelperException;
use Mustache_Exception_UnknownTemplateException;
use plugin_renderer_base;

/**
 * otexternaldata block renderer
 *
 * @package    block_otexternaldata
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {
    
    public function render_from_template_source($templatesource, $context) {
        
        $mustache = $this->get_mustache();
        
        try {
            // Grab a copy of the existing helper to be restored later.
            $uniqidhelper = $mustache->getHelper('uniqid');
        } catch (Mustache_Exception_UnknownHelperException $e) {
            // Helper doesn't exist.
            $uniqidhelper = null;
        }
        
        // Provide 1 random value that will not change within a template
        // but will be different from template to template. This is useful for
        // e.g. aria attributes that only work with id attributes and must be
        // unique in a page.
        $mustache->addHelper('uniqid', new \core\output\mustache_uniqid_helper());
        try {
            $template = $mustache->loadLambda($templatesource);
        } catch (Mustache_Exception_UnknownTemplateException $e) {
            throw new \moodle_exception('Couldn\'t load template source');
        }
        
        $renderedtemplate = trim($template->render($context));
        
        // If we had an existing uniqid helper then we need to restore it to allow
        // handle nested calls of render_from_template.
        if ($uniqidhelper) {
            $mustache->addHelper('uniqid', $uniqidhelper);
        }
        
        return $renderedtemplate;
    }
}
