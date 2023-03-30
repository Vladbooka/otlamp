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
 * Фильтр добавляет js препятствующий множественному воспроизведению медиа контента.
 *
 * @package    filter
 * @subpackage otmediauniqueplay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class filter_otmediauniqueplay extends moodle_text_filter {

    /**
     * {@inheritDoc}
     * @see moodle_text_filter::filter()
     */
    public function filter($text, array $options = array()) {
        global $PAGE;
        static $called = false;
        if( ! $called )
        {// Подключаем js один раз
            $PAGE->requires->js_call_amd('filter_otmediauniqueplay/ot_media_unique_play', 'init');
            $called = true;
        }
        return $text;
    }
}
