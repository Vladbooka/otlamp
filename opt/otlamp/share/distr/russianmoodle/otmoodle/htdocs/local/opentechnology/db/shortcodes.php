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
 * Shortcodes declaration
 *
 * @package    local_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$shortcodes = [
    'courseid' => [
        'callback' => 'local_opentechnology\shortcodes::handle_courseid',
        'description' => 'shortcode:courseid'
    ],
    'coursefullname' => [
        'callback' => 'local_opentechnology\shortcodes::handle_coursefullname',
        'description' => 'shortcode:coursefullname'
    ],
    'currentyear' => [
        'callback' => 'local_opentechnology\shortcodes::handle_currentyear',
        'description' => 'shortcode:currentyear'
    ],
    'currentmonthnumberzero' => [
        'callback' => 'local_opentechnology\shortcodes::handle_currentmonthnumberzero',
        'description' => 'shortcode:currentmonthnumberzero'
    ],
    'currentmonthstr' => [
        'callback' => 'local_opentechnology\shortcodes::handle_currentmonthstr',
        'description' => 'shortcode:currentmonthstr'
    ],
    'currentdaynumberzero' => [
        'callback' => 'local_opentechnology\shortcodes::handle_currentdaynumberzero',
        'description' => 'shortcode:currentdaynumberzero'
    ],
    'currentdaynumber' => [
        'callback' => 'local_opentechnology\shortcodes::handle_currentdaynumber',
        'description' => 'shortcode:currentdaynumber'
    ],
    'currentdaystr' => [
        'callback' => 'local_opentechnology\shortcodes::handle_currentdaystr',
        'description' => 'shortcode:currentdaystr'
    ],
    'release3kl' => [
        'callback' => 'local_opentechnology\shortcodes::handle_release3kl',
        'description' => 'shortcode:release3kl'
    ],
];