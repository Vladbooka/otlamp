<?php
namespace block_mastercourse;

class helper {
    
    public static function get_eduportals_codes()
    {
        global $CFG; 
        
        $eduportalscodes = [];
        
        // получение объектов порталов
        foreach(glob($CFG->dirroot.'/blocks/mastercourse/classes/eduportal/*.php') as $filepath)
        {
            $eduportalcode = basename($filepath, '.php');
            $classname = '\\block_mastercourse\\eduportal\\'.$eduportalcode;
            if (is_readable($filepath) && class_exists($classname))
            {
                require_once($filepath);
                if (!array_key_exists($eduportalcode, $eduportalscodes) && $classname::$enabled)
                {
                    $eduportalscodes[] = $eduportalcode;
                }
            }
        }
        
        return $eduportalscodes;
    }
    
    
    public static function get_courses_in_progress()
    {
        global $DB;
        
        $epcourses = [];
        $eduportalscodes = self::get_eduportals_codes();
        foreach($eduportalscodes as $epcode)
        {
            $classname = '\\block_mastercourse\\eduportal\\'.$epcode;
            $statuses = [];
            if (class_exists($classname) && method_exists($classname, 'get_statuses_in_progress'))
            {
                $statuses = $classname::get_statuses_in_progress();
            }
            if (!empty($statuses))
            {
                list($insql, $params) = $DB->get_in_or_equal(array_keys($statuses));
                $records = $DB->get_records_sql(
                    'SELECT * FROM {mastercourse_publication} WHERE service=? AND status '.$insql, 
                    array_merge([$epcode], $params)
                );
                
                if (!empty($records))
                {
                    if (!array_key_exists($epcode, $epcourses))
                    {
                        $epcourses[$epcode] = [];
                    }
                    foreach ($records as $record)
                    {
                        if (!in_array($record->courseid, $epcourses[$epcode]))
                        {
                            $epcourses[$epcode][] = $record->courseid;
                        }
                    }
                }
            }
        }
        return $epcourses;
    }
}