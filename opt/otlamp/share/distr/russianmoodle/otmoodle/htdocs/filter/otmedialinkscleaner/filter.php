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
require_once ($CFG->dirroot . "/filter/otmedialinkscleaner/lib.php");

class filter_otmedialinkscleaner extends moodle_text_filter
{

    
    /**
     * {@inheritDoc}
     * @see moodle_text_filter::filter()
     */
    function filter( $text, array $options = array() )
    {
        global $CFG, $PAGE;
        
        
        if ( ! is_string($text) or empty($text) )
        {
            // пришел пустой текст - нечего фильтровать
            return $text;
        }
        
        $downloadbutton_disable = get_config('filter_otmedialinkscleaner', 'downloadbutton_disable');
        if( ! empty($downloadbutton_disable) )
        {
            // Замена в тегах video
            $text = preg_replace_callback('/<video[^>]+>/', function($matches){
                $videotag = $matches[0];
                // количество замен по атрибутам
                $prcount = 0;
                // Замена в атрибуте controlsList
                $videonodownload = preg_replace_callback(
                    '/(?:controls[Ll]ist)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)?["\']?/',
                    function($controlsmatches) {
                        $attrvalues = explode(' ',$controlsmatches[1]);
                        if (!in_array('nodownload', $attrvalues))
                        {// Среди значений стрибута нет нужного - добавляем
                            return substr_replace($controlsmatches[0], ' nodownload', -1, 0);
                        } else 
                        {// Все уже прописано - возвращаем как есть
                            return $controlsmatches[0];
                        }
                    }, 
                    $videotag, -1, $prcount
                );
                
                if ($prcount == 0)
                {// Замен не было произведено, значит атрибут не был найден - добавим сами
                    $videonodownload = substr_replace($videonodownload, ' controlsList="nodownload"', -1, 0);
                }
                
                return $videonodownload;
            }, $text);
        }
        
        if ( stripos($text, '</a>') === false )
        {
            // нет ни единого закрывающего a-тега. Мы ничего не найдем
            return $text;
        }
        
        // Ищем все теги
        $matches = preg_split('/(<[^>]*>)/i', $text, - 1, 
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        if ( ! $matches )
        {
            return $text;
        }
        
        //новый текст
        $newtext = '';
        //подходящий тег
        $validtag = '';
        
        $sizeofmatches = count($matches);
        // регулярка для поиска ссылок нужных форматов
        $re = '~<a\s[^>]*href="([^"]*(?:' . filter_otmedialinkscleaner_helper::get_markers() .
        ')[^"]*)"[^>]*>([^>]*)</a>~is';
        
        // пробегаемся по тегам с целью поиска нужных a-тегов и формирования нового текста 
        foreach ( $matches as $idx => $tag )
        {
            if ( preg_match('|</a>|', $tag) && ! empty($validtag) )
            {
                $validtag .= $tag;
                
                // Given we now have a valid <a> tag to process it's time for
                // ReDoS protection. Stop processing if a word is too large.
                if ( strlen($validtag) < 4096 )
                {
                    $processed = preg_replace_callback($re, [
                        $this,
                        'callback'
                    ], $validtag);
                }
                // Rebuilding the string with our new processed text.
                $newtext .= ! empty($processed) ? $processed : $validtag;
                // Wipe it so we can catch any more instances to filter.
                $validtag = '';
                $processed = '';
            } else if ( preg_match('/<a\s[^>]*/', $tag) && $sizeofmatches > 1 )
            {
                // поиск начального <a> тега.
                $validtag = $tag;
            } else
            {
                // If we have a validtag add to that to process later,
                // else add straight onto our newtext string.
                if ( ! empty($validtag) )
                {
                    $validtag .= $tag;
                } else
                {
                    $newtext .= $tag;
                }
            }
        }
        
        // возвращаем преобразованную строку
        return $newtext;
    }

    /**
     * Замена ссылки на текст из настроек фильтра
     *
     * @param array $matches            
     * @return string
     */
    private function callback( array $matches )
    {
        global $CFG, $PAGE;
        
        foreach(filter_otmedialinkscleaner_helper::get_urls($matches[1]) as $url)
        {//получаем адреса ссылок
            //расширение
            $extension = filter_otmedialinkscleaner_helper::get_extension($url);

            $fallbacknonmedia = get_config('filter_otmedialinkscleaner', $extension.'_fallback_nonmedia');
            
            // Check if we ignore it.
            if ( ! empty($fallbacknonmedia) || preg_match('/class="[^"]*mediafallbacklink/i', $matches[0]) )
            {
                //включена ли настройка сокрытия ссылки для текущего расширения
                $extconfig = get_config('filter_otmedialinkscleaner', $extension);
                if ( ! empty($extconfig) )
                {
                    //настроен ли текст для текущего расширения
                    $extfallback = get_config('filter_otmedialinkscleaner', $extension.'_fallback_text');
                    if( ! empty($extfallback) )
                    {
                        return $extfallback;
                    } else
                    {
                        return '';
                    }
                }
            }
            else
            {
                return $matches[0];            
            }
        }
    }
}
