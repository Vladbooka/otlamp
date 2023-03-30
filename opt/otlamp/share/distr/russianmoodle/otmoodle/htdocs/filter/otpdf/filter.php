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
 * Фильтр ссылок на pdf-файлы, отключает возможность скачивания.
 *
 * Фильтр заменяет ссылку на pdf-файл другой, так чтобы файл открывался на просмотр без возможности скачивания и печати.
 *
 * @package    filter_otpdf
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter_otpdf extends moodle_text_filter {
    
    public function filter($text, array $options = array()) {
        global $CFG;
        
        $displayoption = get_config('filter_otpdf', 'display_option');
        $result = mb_ereg_replace_callback('(?i)<a.*?((?<=href=").*?\.pdf|(?<=href=").*?\.pdf").*?"\>(.*?)<\/a>',
            function ($matches) use ($displayoption) {
                switch($displayoption)
                {
                    case 1:
                        $pdflink = html_writer::link(
                            new moodle_url('/filter/otpdf/lib/pdfjs/web/viewer.php', ['file' => $matches[1]]), 
                            $matches[2],
                            ['target'=>'_blank']);
                        return $pdflink;
                    case 0:
                    default:
                        $pdfiframe = html_writer::tag('iframe', '', [
                            'class' => 'otpdf-viewer',
                            'src' => new moodle_url('/filter/otpdf/lib/pdfjs/web/viewer.php', ['file' => $matches[1]])
                        ]);
                        return $pdfiframe;
                }
            },
            $text);
        return $result;
    }
    
}