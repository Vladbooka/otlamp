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
 * Statistics.
 *
 * @package    local_opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_opentechnology;

use cache;
use cache_store;

defined('MOODLE_INTERNAL') || die();


class statistics {
    
    const BYTE_UNITS = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
    const BYTE_PRECISION = [0, 0, 1, 2, 2, 3, 3, 4, 4];
    const BYTE_NEXT = 1024;
    private static $usefulvolcachelifetime = 24 * 60 * 60;
    private static $mdatasizecachelifetime = 24 * 60 * 60;
    private static $dbsizecachelifetime = 24 * 60 * 60;
    
    /**
     * Convert bytes to be human readable.
     *
     * @param int      $bytes     Bytes to make readable
     * @param int|null $precision Precision of rounding
     *
     * @return string Human readable bytes
     */
    public static function human_readable_bytes($bytes, $precision = null)
    {
        for ($i = 0; ($bytes / self::BYTE_NEXT) >= 0.9 && $i < count(self::BYTE_UNITS); $i++) $bytes /= self::BYTE_NEXT;
        return round($bytes, is_null($precision) ? self::BYTE_PRECISION[$i] : $precision) . self::BYTE_UNITS[$i];
    }
    
    /**
     * Получить полезный объем (moodledata + база)
     *
     * @throws \Exception
     * @return number в байтах
     */
    public static function get_useful_volume()
    {
        $cache = cache::make_from_params(cache_store::MODE_APPLICATION, 'local_opentechnology', 'statistics');
        $cachedata = $cache->get('useful_volume');
        $cachedeadline = $cachedata['timecreated'] ?? 0 + self::$usefulvolcachelifetime;
        
        // Если кэша нет, он устарел или не содержит нужных данных
        if ($cachedata == false || $cachedeadline < time() || !array_key_exists('value', $cachedata))
        {
            $moodledata = self::get_moodledata_size();
            $database = self::get_database_size();
            
            if (is_numeric($moodledata) && is_numeric($database))
            {
                $cachedata = [
                    'value' => $moodledata + $database,
                    'timecreated' => time()
                ];
                $cache->set('useful_volume', $cachedata);
                
            } else
            {
                throw new \Exception('Failed to calculate useful volume');
            }
        }
        
        return $cachedata['value'];
    }
    
    /**
     * Получить размер данных, хранящихся в moodledata
     *
     * @return number размер в байтах
     */
    public static function get_moodledata_size()
    {
        global $DB;
        
        $cache = cache::make_from_params(cache_store::MODE_APPLICATION, 'local_opentechnology', 'statistics');
        $cachedata = $cache->get('moodledata_size');
        $cachedeadline = $cachedata['timecreated'] ?? 0 + self::$mdatasizecachelifetime;
        
        // Если кэша нет, он устарел или не содержит нужных данных
        if ($cachedata == false || $cachedeadline < time() || !array_key_exists('value', $cachedata))
        {
            $sql = 'SELECT SUM(filesize) AS "datasize"
                      FROM (SELECT DISTINCT(contenthash), filesize
                              FROM {files}) uniquefiles';
            
            $record = $DB->get_record_sql($sql, null, MUST_EXIST);
            
            $cachedata = [
                'value' => $record->datasize,
                'timecreated' => time()
            ];
            $cache->set('moodledata_size', $cachedata);
        }
        return $cachedata['value'];
        
        
//         global $CFG;
        
//         $io = popen('/usr/bin/du -sm '.escapeshellarg($CFG->dataroot), 'r');
//         $size = fgets($io, 4096);
//         $size = substr($size, 0, strpos($size, "\t"));
//         pclose($io);
        
//         return $size;
    }
    
    /**
     * Получить размер, занимаемый базой данных
     *
     * @throws \Exception
     * @return int|null размер в байтах
     */
    public static function get_database_size()
    {
        global $DB;
        
        
        $cache = cache::make_from_params(cache_store::MODE_APPLICATION, 'local_opentechnology', 'statistics');
        $cachedata = $cache->get('database_size');
        $cachedeadline = $cachedata['timecreated'] ?? 0 + self::$dbsizecachelifetime;
        
        // Если кэша нет, он устарел или не содержит нужных данных
        if ($cachedata == false || $cachedeadline < time() || !array_key_exists('value', $cachedata))
        {
            switch($DB->get_dbfamily())
            {
                case 'mysql':
                    $sql = 'SELECT SUM(data_length + index_length) AS "dbsize"
                              FROM information_schema.TABLES
                             WHERE table_schema=:dbname';
                    break;
                case 'postgres':
                    $sql = 'SELECT pg_database_size(pg_database.datname) AS dbsize
                              FROM pg_database
                             WHERE pg_database.datname=:dbname';
                    break;
                case 'mssql':
                    $sql = 'SELECT SUM(size)*8*1024 AS dbsize
                              FROM sys.databases
                              JOIN sys.master_files
                                ON sys.databases.database_id=sys.master_files.database_id
                             WHERE sys.databases.name=:dbname
                          GROUP BY sys.databases.name';
                    break;
                case 'oracle':
                    $sql = 'SELECT SUM(bytes) "dbsize"
                              FROM dba_data_files';
                    break;
                default:
                    throw new \Exception('dbfamily not supported');
                    break;
            }
            
            $record = $DB->get_record_sql($sql, ['dbname' => $DB->export_dbconfig()->dbname], MUST_EXIST);
            
            $cachedata = [
                'value' => $record->dbsize,
                'timecreated' => time()
            ];
            $cache->set('database_size', $cachedata);
        }
        return $cachedata['value'];
        
    }
    
    /**
     * Достигнут ли лимит по ограничению размера инсталляции
     * @return boolean
     */
    public static function is_moodle_size_limit_exceeded()
    {
        global $CFG;
        
        $islimitexceeded = false;
        if (!empty($CFG->moodlesizelimit))
        {
            $islimitexceeded = (int)$CFG->moodlesizelimit == 1;
            if (!$islimitexceeded)
            {
                try {
                    $moodlesize = self::get_useful_volume();
                    $islimitexceeded = (int)$CFG->moodlesizelimit < ($moodlesize / 1024 / 1024);
                } catch(\Exception $ex) {}
            }
        }
        
        return $islimitexceeded;
    }
    
    public static function get_users_count()
    {
        global $DB;
        return $DB->count_records('user', ['deleted' => 0]);
    }
    
    public static function get_online_users_count()
    {
        global $DB;
        
        $now = time();
        //Seconds default
        $timetoshowusers = 300;
        // Round to nearest 100 seconds for better query cache
        $timefrom = 100 * floor(($now - $timetoshowusers) / 100);
        
        $conditions = 'lastaccess > :timefrom AND lastaccess <= :now AND deleted = 0';
        $params = ['now' => $now, 'timefrom' => $timefrom];
        return $DB->count_records_select('user', $conditions, $params);
    }
    
    public static function get_courses_count()
    {
        global $DB;
        return $DB->count_records('course');
    }
    
    
    /**
     * Получить объем свободного пространства (в байтах) для пути
     * 
     * @param string $path путь к каталогу, для которого нужно посчитать оставшееся место
     * @throws \Exception  
     */
    public static function get_free_diskspace_bytes($path = '/'){
        if (is_dir($path)){
            if ($freespace = disk_free_space($path)){
                return ($freespace);
            }
            else{
                throw new \Exception('Failed to calculate free diskspace');
            }
        }
        else {
            throw new \Exception('Not a directory path');
        }
    }
    
    /**
     * Получить объем свободного дискового пространства в процентах от максимального.
     * 
     * @param string $path путь к каталогу, для которого нужно посчитать оставшееся место
     * @throws \Exception 
     */
    public static function get_free_diskspace_percentage($path = '/'){
        
        $freespace = self::get_free_diskspace_bytes($path);
        if ($totalspace = disk_total_space($path)){
            $relation = $freespace / $totalspace;
            return round($relation, 3, PHP_ROUND_HALF_DOWN)*100;
        }
        else {
            throw new \Exception('Failed to calculate total diskspace');
        }
    }
    
    /**
     * Получить данные сетевых интерфейсов сервера.
     *
     * @throws \Exception
     */
    public static function get_network_interfaces(){
        
        $interfacedata = array();
        
        $ifconfig = shell_exec('ifconfig');

        if ($ifconfig){
            // Получаем строки данных интерфейсов
            $interfaces = preg_split("/(\n){2,}|(\r\n){2,}/", $ifconfig, 0, PREG_SPLIT_NO_EMPTY);
            
            foreach($interfaces as $interface) {
                
                // Находим в строке интерфейса название, ip и маску подсети
                $re = '/^([[:alnum:]]*)(?:(?:[\S\s]*)(?:inet addr:|inet )([^\s]*))?(?:(?:[\S\s]*)(?:Mask:|netmask )([^\s]*))?/m';
                preg_match($re, $interface, $matches);
                
                // Добавляем данные интерфейса, если есть что добавлять
                if (!empty($matches)) {
                    
                    list($full, $name, $ip, $mask) = $matches;
                    
                    $data = array();
                    $data['name'] = $name;
                    $data['value']['ip'] = $ip;
                    $data['value']['mask'] = $mask;
                    $interfacedata[] = $data;
                }
                
            }
            return $interfacedata;
        } else {
            throw new \Exception('Failed to get network interface data');
        }
    }
    
    /**
     * Получить шлюз по умолчанию.
     *
     * @throws \Exception
     */
    public static function get_default_gateway(){
        $routes = array();
        $defaultgateway = '';
        $mask = '';
        if (exec('route', $routes)){
            
            foreach ($routes as $route){
                if (strpos($route,'default') !== false){
                    $matches = array();
                    preg_match('/\s(\d{1,2}(\.|\s)){4}/',$route,$matches);
                    $defaultgateway = $matches[0];
                    break;
                }
            }
            if (empty ($defaultgateway)){
                $defaultgateway = get_string('dg_not_specified','local_opentechnology');
            }
            return array(
                'defgateway' => $defaultgateway,
            );
        } else {
            throw new \Exception('Failed to get default gateway');
        }
    }
    
    public static function get_dns_server(){
        $dns = array();
        
        if ($resolv = file('/etc/resolv.conf')){
            
            foreach ($resolv as $record){
                if (strpos($record,'nameserver') !== false){
                    $matches = array();
                    preg_match('/\s(\d{1,2}(\.|\s)){4}/',$record,$matches);
                    $dns[] = $matches[0];
                }
            }
            return $dns;
        } else {
            throw new \Exception('Failed to get dns server list');
        }
    }

}