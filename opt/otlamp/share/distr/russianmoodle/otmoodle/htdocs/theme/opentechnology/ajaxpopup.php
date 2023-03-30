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
 * This file generates AJAX list of login as links of course users.
 *
 * @package local_loginas
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require('../../config.php');

$id = required_param('id', PARAM_INT);
$action = required_param('action', PARAM_ALPHA);
$url  = required_param('url', PARAM_URL);
$post  = optional_param('post', '', PARAM_RAW);
$wantsurl  = optional_param('wantsurl', '', PARAM_URL);
if( ! empty($wantsurl) )
{
    $SESSION->wantsurl = $wantsurl;
}
$PAGE->set_url(new moodle_url('/theme/opentechnology/ajaxpopup.php', array('id' => $id, 'action' => $action)));

function get_original_url($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // if it's not a redirection (3XX), move along
    if ($httpStatus < 300 || $httpStatus >= 400)
        return $url;
        
        // look for a location: header to find the target URL
        if(preg_match('/location: (.*)/i', $result, $r)) {
            $location = trim($r[1]);
            
            // if the location is a relative URL, attempt to make it absolute
            if (preg_match('/^\/(.*)/', $location)) {
                $urlParts = parse_url($url);
                if ($urlParts['scheme'])
                    $baseURL = $urlParts['scheme'].'://';
                    
                    if ($urlParts['host'])
                        $baseURL .= $urlParts['host'];
                        
                        if ($urlParts['port'])
                            $baseURL .= ':'.$urlParts['port'];
                            
                            return $baseURL.$location;
            }
            
            return $location;
        }
        return $url;
}

function get_page($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, get_original_url($url));
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "page_layout=popup");
    $html = curl_exec($ch);
    curl_close($ch);
    return $html;
}

$outcome = new stdClass;
$outcome->success = false;
$outcome->response = new stdClass;
$outcome->title = '';
$outcome->error = '';

switch ($action) 
{
    case 'getpage' :
        
        $html = get_page($url);
        
        preg_match("/\<body.*\>(.*)\<\/body\>/isU", $html, $body);
        preg_match("/\<title.*\>(.*)\<\/title\>/isU", $html, $titlematches);
        
        if ( isset($titlematches[1]) )
        {
            $outcome->title = $titlematches[1];
        }
        if ( isset($body[1]) )
        {
            $outcome->response = $body[1];
            $outcome->success = true;
        }
        // Хак для починки ссылок на странице входа
        if ( preg_match("/\/login\/index\.php/", $url) )
        { // Замена относительных ссылок на абсолютные
            $pattern = '/(href\s*=\s*[\'"]\s*)([^\/|^http])/i';
            $replacement = '${1}' . $CFG->httpswwwroot . '/login/${2}';
            $outcome->response = preg_replace($pattern, $replacement, $outcome->response);
            $pattern = '/(action\s*=\s*[\'"]\s*)([^\/|^http])/i';
            $outcome->response = preg_replace($pattern, $replacement, $outcome->response);
        }
        break;
    default:
        throw new moodle_exception('unknowajaxaction', 'local_loginas');
}

echo json_encode($outcome);