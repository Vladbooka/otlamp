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
 * Remove some media links
 *
 * @package filter
 * @subpackage otmedialinkscleaner
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once (dirname(__FILE__) . '/../../config.php');

class filter_otspoiler extends moodle_text_filter
{
    /**
     * {@inheritDoc}
     * @see moodle_text_filter::filter()
     */
    function filter( $text, array $options = array() )
    {
        global $PAGE;
        static $called = false;
        if( ! $called )
        {// Подключаем js один раз
            $PAGE->requires->js_call_amd('filter_otspoiler/atto_spoiler', 'init');
            $called = true;
        }
        return $text;
    }
}
