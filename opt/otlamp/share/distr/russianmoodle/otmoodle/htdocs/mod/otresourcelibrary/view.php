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
require_once('lib.php');

$cmid = optional_param('id', NULL, PARAM_INT);

$courseid = optional_param('cid', NULL, PARAM_INT);
$sourcename = optional_param('sourcename', NULL, PARAM_RAW);
$resourceid = optional_param('resourceid', NULL, PARAM_INT);
$pointertype = optional_param('pointertype', NULL, PARAM_ALPHAEXT);
$pointerval  = optional_param('pointerval', NULL, PARAM_ALPHAEXT);

$force = optional_param('force', NULL, PARAM_ALPHAEXT);
$contentonly = optional_param('co', 0, PARAM_INT);

$pageurlparams = [
    'id' => $cmid,
    'cid' => $courseid,
    'sourcename' => $sourcename,
    'resourceid' => $resourceid,
    'pointertype' => $pointertype,
    'pointerval' => $pointerval,
    'force' => $force,
    'co' => $contentonly,
];
$pageurl = new moodle_url('/mod/otresourcelibrary/view.php', array_filter($pageurlparams));

if (!is_null($cmid))
{
    // Получение контента в соответствии с настройками, сохраненными в модуле
    
    // Что бы ни было передано в параметрах - затираем и получаем из настроек
    $courseid = null;
    $sourcename = null;
    $resourceid = null;
    $pointertype = null;
    $pointerval  = null;
    
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
        if (array_key_exists('pagenum', $sourcesettings) && $sourcesettings['pagenum']) {
            $pointertype = 'pagenum';
            $pointerval = $sourcesettings['pagenum'];
        } elseif (array_key_exists('chapter', $sourcesettings) && $sourcesettings['chapter']) {
            $pointertype = 'chapter';
            $pointerval = $sourcesettings['chapter'];
        }
        if (array_key_exists('fragment', $sourcesettings) && $sourcesettings['fragment']) {
            $pointertype = 'fragment';
            $pointerval = $sourcesettings['fragment'];
        }
    }
} else
{
    // Идентификатор модуля не был передан, требуется получение материалов по параметрам
    // Это допустимо только при наличии права просмотра по параметрам mod/otresourcelibrary:viewbyparameter
    require_login();
    if (!has_capability('mod/otresourcelibrary:viewbyparameter', context_course::instance($courseid)))
    {
        echo get_string('view_by_parameter', 'otresourcelibrary');
        exit;
    }
    
    // указываем как скрипт получения медиа должен определять ресурс
    // по параметрам ресурса (resource parameters)
    $proxyscriptparams = ['rp', $courseid, $sourcename, $resourceid];
}

$html = '';

if ( !empty($sourcename) && !empty($resourceid) )
{
    
    $proxyscriptparams = array_map('urlencode', $proxyscriptparams);
    $proxyscript = (new moodle_url('/mod/otresourcelibrary/file.php/'.implode('/',$proxyscriptparams)))->out(false);
    
    $otapi = new \mod_otresourcelibrary\otapi();
    $response = $otapi->get_resource($sourcename, $resourceid, $pointertype, $pointerval);
    
    if (array_key_exists($sourcename, $response))
    {// Есть результаты по запрошенному источнику
        
        // Получаем конкретный ресурс
        $resourcedata = array_shift($response[$sourcename]);
        $resourcecontent = $otapi->get_resource_content($proxyscript, $resourcedata,
            ($contentonly == 1 ? 'embeded' : $force));
        $contenturl = clone($pageurl);
        $contenturl->param('co', 1);
        $html .= str_replace('%iframe_content_url%', $contenturl, $resourcecontent);
        
    } else {
        $html .= get_string('no_material', 'otresourcelibrary');
    }
}

if (!empty($contentonly))
{
    echo $html;
    
} else
{
    $context = context_module::instance($cm->id);
    // Установка параметров страницы
    $PAGE->set_url($pageurl);
    $PAGE->set_title($course->shortname.': '.$otresourcelibrary->name);
    
    // Создадим событие и проставим галочку о просмотре.
    otresourcelibrary_view($otresourcelibrary, $course, $cm, $context);
    
    echo $OUTPUT->header();
    echo $OUTPUT->heading(format_string($otresourcelibrary->name), 2);
    // Описание отчета
    if (!empty($otresourcelibrary->description)) {
        echo $OUTPUT->box($otresourcelibrary->description);
    }
    echo $html;
    echo $OUTPUT->footer();
}
