<?php

namespace otcomponent_phpamqplib;

class autoload {
    
    public static function register() {
        
        /**
         * Автозагрузчик PhpAmqpLib библиотеки
         */
        spl_autoload_register(function ($classname)
        {
            if ( strpos($classname, 'PhpAmqpLib') !== false )
            {
                global $CFG;
                $classname = str_replace('PhpAmqpLib\\', '', $classname);
                $filepath =  $CFG->dirroot . '/local/opentechnology/component/phpamqplib/classes/PhpAmqpLib/' . str_replace('\\', '/', $classname) . '.php';
                if ( file_exists($filepath) )
                {
                    require_once ($filepath);
                }
            }
        });
    }
}