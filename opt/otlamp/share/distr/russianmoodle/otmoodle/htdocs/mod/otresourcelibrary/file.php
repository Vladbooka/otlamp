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
 * Просмотр элемента библиотеки ресурсов
 *
 */

require_once('../../config.php');


$relativepath  = get_file_argument();

// relative path must start with '/', because of backup/restore!!!
if (!$relativepath) {
    print_error('invalidargorconf');
} else if ($relativepath{0} != '/') {
    print_error('pathdoesnotstartslash');
}

$pathparts = explode('/', $relativepath);
unset($pathparts[0]);
switch(array_shift($pathparts))
{// как скрипт получения медиа должен определять ресурс
    case 'cm':// по настройкам из модуля
        $cmid = urldecode(array_shift($pathparts));
        // все необходимые реквизиты будут получены из настроек
        $courseid = null;
        $sourcename = null;
        $resourceid = null;
        
        // указываем как скрипт получения медиа должен определять ресурс
        // по настройкам из модуля
        $proxyscriptparams = ['cm', $cmid];
        
        if (!$cm = get_coursemodule_from_id('otresourcelibrary', $cmid)) {
            print_error('Course Module ID was incorrect'); // NOTE this is invalid use of print_error, must be a lang string id
        }
        $courseid = $cm->course;
        if (!$course = $DB->get_record('course', array('id'=> $courseid))) {
            print_error('course is misconfigured');  // NOTE As above
        }
        if (!$otresourcelibrary = $DB->get_record('otresourcelibrary', array('id'=> $cm->instance))) {
            print_error('course module is incorrect'); // NOTE As above
        }
        
        // Требуется вход в систему
        require_course_login($course, true, $cm);
        
        if (!empty($otresourcelibrary->khipu_setting))
        {
            $sourcesettings = json_decode($otresourcelibrary->khipu_setting, true);
            if (array_key_exists('sourcename', $sourcesettings)) {
                $sourcename = $sourcesettings['sourcename'];
            }
            if (array_key_exists('resourceid', $sourcesettings)) {
                $resourceid = $sourcesettings['resourceid'];
            }
        }
        break;
    case 'rp':// по параметрам ресурса (resource parameters)
        
        $courseid = urldecode(array_shift($pathparts));
        $sourcename = urldecode(array_shift($pathparts));
        $resourceid = urldecode(array_shift($pathparts));
        
        // указываем как скрипт получения медиа должен определять ресурс
        // по параметрам ресурса (resource parameters)
        $proxyscriptparams = ['rp', $courseid, $sourcename, $resourceid];
        
        // Идентификатор модуля не был передан, требуется получение материалов по параметрам
        // Это допустимо только при наличии права просмотра по параметрам mod/otresourcelibrary:viewbyparameter
        require_login();
        if (!has_capability('mod/otresourcelibrary:viewbyparameter', context_course::instance($courseid)))
        {
            echo get_string('view_by_parameter', 'otresourcelibrary');
            exit;
        }
        break;
    default:
        print_error('unknown resource');
        break;
}



if (!empty($sourcename) && !empty($resourceid))
{
    $otapi = new \mod_otresourcelibrary\otapi();
    
    $query = http_build_query($_GET, null, '&');
    $resourcepath = urlencode('/' . implode('/', $pathparts) . (empty($query) ? '' : '?') . $query);
//     var_dump($resourcepath);exit;
    $media = $otapi->get_resource_media($sourcename, $resourceid, $resourcepath);

    $headerstosend = ['Content-Type', 'Content-Length'];
    foreach($media['headers'] as $header)
    {
        $headerdata = explode(':', $header, 2);
        if (isset($headerdata[0]) && in_array($headerdata[0], $headerstosend))
        {
            header(str_replace(["\r", "\n"], '', $header));
        }
    }
    
    $proxyscriptparams = array_map('urlencode', $proxyscriptparams);
    $proxyscript = (new moodle_url('/mod/otresourcelibrary/file.php/'.implode('/',$proxyscriptparams)))->out();
    
    echo str_replace('%proxy_script%', $proxyscript, base64_decode($media['content']));
    
    exit;
}
