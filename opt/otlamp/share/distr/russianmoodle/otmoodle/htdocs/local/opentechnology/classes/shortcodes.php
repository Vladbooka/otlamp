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
 * Shortcodes handler.
 *
 * @package    local_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_opentechnology;
defined('MOODLE_INTERNAL') || die();

/**
 * Shortcodes handler.
 *
 * @package    local_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class shortcodes {

    /**
     * Handle shortcodes.
     *
     * @param string $shortcode The shortcode.
     * @param object $args The arguments of the code.
     * @param string|null $content The content, if the shortcode wraps content.
     * @param object $env The filter environment (contains context, noclean and originalformat).
     * @param Closure $next The function to pass the content through to process sub shortcodes.
     * @return string The new content.
     */
    public static function handle_courseid($shortcode, $args, $content, $env, $next)
    {
        $coursecontext = $env->context->get_course_context(false);
        if (!empty($coursecontext->instanceid))
        {
            return $coursecontext->instanceid;
        }
        return $next($content);
    }

    /**
     * Handle shortcodes.
     *
     * @param string $shortcode The shortcode.
     * @param object $args The arguments of the code.
     * @param string|null $content The content, if the shortcode wraps content.
     * @param object $env The filter environment (contains context, noclean and originalformat).
     * @param Closure $next The function to pass the content through to process sub shortcodes.
     * @return string The new content.
     */
    public static function handle_coursefullname($shortcode, $args, $content, $env, $next)
    {
        $coursecontext = $env->context->get_course_context(false);
        if (!empty($coursecontext->instanceid))
        {
            $course = get_course($coursecontext->instanceid);
            return $course->fullname;
        }
        return $next($content);
    }

    /**
     * Handle shortcodes.
     *
     * @param string $shortcode The shortcode.
     * @param object $args The arguments of the code.
     * @param string|null $content The content, if the shortcode wraps content.
     * @param object $env The filter environment (contains context, noclean and originalformat).
     * @param Closure $next The function to pass the content through to process sub shortcodes.
     * @return string The new content.
     */
    public static function handle_currentyear($shortcode, $args, $content, $env, $next)
    {
        return userdate(time(), '%Y');
    }

    /**
     * Handle shortcodes.
     *
     * @param string $shortcode The shortcode.
     * @param object $args The arguments of the code.
     * @param string|null $content The content, if the shortcode wraps content.
     * @param object $env The filter environment (contains context, noclean and originalformat).
     * @param Closure $next The function to pass the content through to process sub shortcodes.
     * @return string The new content.
     */
    public static function handle_currentmonthnumberzero($shortcode, $args, $content, $env, $next)
    {
        return userdate(time(), '%m');
    }

    /**
     * Handle shortcodes.
     *
     * @param string $shortcode The shortcode.
     * @param object $args The arguments of the code.
     * @param string|null $content The content, if the shortcode wraps content.
     * @param object $env The filter environment (contains context, noclean and originalformat).
     * @param Closure $next The function to pass the content through to process sub shortcodes.
     * @return string The new content.
     */
    public static function handle_currentmonthstr($shortcode, $args, $content, $env, $next)
    {
        return userdate(time(), '%B');
    }

    /**
     * Handle shortcodes.
     *
     * @param string $shortcode The shortcode.
     * @param object $args The arguments of the code.
     * @param string|null $content The content, if the shortcode wraps content.
     * @param object $env The filter environment (contains context, noclean and originalformat).
     * @param Closure $next The function to pass the content through to process sub shortcodes.
     * @return string The new content.
     */
    public static function handle_currentdaynumberzero($shortcode, $args, $content, $env, $next)
    {
        return userdate(time(), '%d', 99, false);
    }

    /**
     * Handle shortcodes.
     *
     * @param string $shortcode The shortcode.
     * @param object $args The arguments of the code.
     * @param string|null $content The content, if the shortcode wraps content.
     * @param object $env The filter environment (contains context, noclean and originalformat).
     * @param Closure $next The function to pass the content through to process sub shortcodes.
     * @return string The new content.
     */
    public static function handle_currentdaynumber($shortcode, $args, $content, $env, $next)
    {
        return userdate(time(), '%e');
    }

    /**
     * Handle shortcodes.
     *
     * @param string $shortcode The shortcode.
     * @param object $args The arguments of the code.
     * @param string|null $content The content, if the shortcode wraps content.
     * @param object $env The filter environment (contains context, noclean and originalformat).
     * @param Closure $next The function to pass the content through to process sub shortcodes.
     * @return string The new content.
     */
    public static function handle_currentdaystr($shortcode, $args, $content, $env, $next)
    {
        return userdate(time(), '%A');
    }

    /**
     * Handle shortcodes.
     *
     * @param string $shortcode The shortcode.
     * @param object $args The arguments of the code.
     * @param string|null $content The content, if the shortcode wraps content.
     * @param object $env The filter environment (contains context, noclean and originalformat).
     * @param Closure $next The function to pass the content through to process sub shortcodes.
     * @return string The new content.
     */
    public static function handle_release3kl($shortcode, $args, $content, $env, $next)
    {
        $pluginman = \core_plugin_manager::instance();
        $plugininfo = $pluginman->get_plugin_info('local_opentechnology');

        if (property_exists($plugininfo, 'release')) {
            return $plugininfo->release;
        }

        return $next($content);
    }
}
