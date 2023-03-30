<?php

class filter_otmedialinkscleaner_helper
{
    /**
     * Возвращает массив поддерживаемых расширений
     *
     * @return array
     */
    public static function get_supported_extensions()
    {
        //поддерживаемые расширения
        $supportedextensions = [
            'flv',
            'mp4',
            'webm',
            'ogg',
            'mp3'
        ];
        return $supportedextensions;
    }

    /**
     * Возвращает строку с маркерами для поиска нужных форматов
     *
     * @return string
     */
    public static function get_markers()
    {
        $markers = [];
        foreach ( self::get_supported_extensions() as $extension )
        {
            $markers[] = '|' . preg_quote('.' . $extension);
        }
        return implode('', $markers);
    }

    /**
     * Формирует массив ссылок из строки, содержащей ссылки и разделенной знаком # 
     *
     * @param string $combinedurl
     *            String of 1 or more alternatives separated by #
     * @return array Array of 1 or more moodle_url objects
     */
    public static function get_urls( $combinedurl )
    {
        $urls = explode('#', $combinedurl);
        $returnurls = array();
        
        foreach ( $urls as $url )
        {
            // Clean up url.
            $url = clean_param($url, PARAM_URL);
            if ( empty($url) )
            {
                continue;
            }
            
            // Turn it into moodle_url object.
            $returnurls[] = new moodle_url($url);
        }
        
        return $returnurls;
    }

    /**
     * Возвращает расширение по переданному moodle_url
     * 
     * @param moodle_url $url
     * @return string
     */
    public static function get_extension( moodle_url $url )
    {
        // Note: Does not use core_text (. is UTF8-safe).
        $filename = self::get_filename($url);
        $dot = strrpos($filename, '.');
        if ( $dot === false )
        {
            return '';
        } else
        {
            return strtolower(substr($filename, $dot + 1));
        }
    }

    /**
     * Возвращает имя файла по переданному moodle_url.
     * 
     * @param moodle_url $url
     * @return string Filename only (not escaped)
     */
    public static function get_filename( moodle_url $url )
    {
        global $CFG;
        
        // Use the 'file' parameter if provided (for links created when
        // slasharguments was off). If not present, just use URL path.
        $path = $url->get_param('file');
        if ( ! $path )
        {
            $path = $url->get_path();
        }
        
        // Remove everything before last / if present. Does not use textlib as / is UTF8-safe.
        $slash = strrpos($path, '/');
        if ( $slash !== false )
        {
            $path = substr($path, $slash + 1);
        }
        return $path;
    }
}