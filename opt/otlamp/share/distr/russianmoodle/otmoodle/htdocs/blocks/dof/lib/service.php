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
 * Страница обработки AJAX запросов Электронного Деканата
 *
 * @package    block_dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Страница AJAX
define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../../config.php');
require_once(dirname(__FILE__) . '/dof_externallib.php');

define('PREFERRED_RENDERER_TARGET', RENDERER_TARGET_GENERAL);

// Получение JSON
$rawjson = file_get_contents('php://input');
$requests = json_decode($rawjson, true);

if ($requests === null) {
    if (function_exists('json_last_error_msg')) {
        $lasterror = json_last_error_msg();
    } else {
        // Fall back to numeric error for older PHP version.
        $lasterror = json_last_error();
    }
    throw new coding_exception('Invalid json in request: ' . $lasterror);
}

$responses = array();

foreach ($requests as $request) {
    $response = array();
    $methodname = clean_param($request['methodname'], PARAM_ALPHANUMEXT);
    $index = clean_param($request['index'], PARAM_INT);
    $args = $request['args'];
    
    $response = dof_external_api::call_external_function($methodname, $args);
    $responses[$index] = $response;
    if ($response['error']) {
        // Если ошибка, остановка обработки последующих запросов
        break;
    }
}

echo json_encode($responses);
