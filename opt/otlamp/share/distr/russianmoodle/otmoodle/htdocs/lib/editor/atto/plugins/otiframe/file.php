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

// disable moodle specific debug messages and any errors in output
define('NO_DEBUG_DISPLAY', true);

require_once(dirname(__FILE__).'/../../../../../config.php');

$otiframesrc = optional_param('ois', NULL, PARAM_URL);

/**
 * Формирование URL без относительного пути и параметров (только scheme, host, port, user, pass)
 * @param array $parsedurl - результат исполнения функции parse_url
 * @return string
 */
function get_url_with_no_path($parsedurl) {

    $scheme   = isset($parsedurl['scheme']) ? $parsedurl['scheme'] . '://' : '';
    $host     = isset($parsedurl['host']) ? $parsedurl['host'] : '';
    $port     = isset($parsedurl['port']) ? ':' . $parsedurl['port'] : '';
    $user     = isset($parsedurl['user']) ? $parsedurl['user'] : '';
    $pass     = isset($parsedurl['pass']) ? ':' . $parsedurl['pass']  : '';
    $pass     = ($user || $pass) ? "$pass@" : '';
    return $scheme . $user . $pass . $host . $port;
}

if (!is_null($otiframesrc)) {

    // Составление url со слэшаргументами для редиректа
    $redirecturl = new moodle_url('/lib/editor/atto/plugins/otiframe/file.php');

    // Определение локации (внутренний ресурс или внешний)
    $localurl = clean_param($otiframesrc, PARAM_LOCALURL);
    $location = (empty($localurl)?'ext':'int');

    // разбираем урл на части
    $parsedurl = parse_url($otiframesrc);
    // декодируем каждую из частей урла, иначе не подружимся с кириллицей
    // в цикле, так как если запустить ранее, то знак вопроса будет расценен как спецсимвол для отделения параметров
    foreach($parsedurl as $component => $value)
    {
        $parsedurl[$component] = urldecode($value);
    }


    // Формирование слэшаргумента
    // Первый аргумент - локация
    $slashargument = '/'.$location;
    // У внутреннего ресурса следующим аргументом будет ожидаться сприпт pluginfile.php
    // Для внешнего ресурса следующим аргументом добавим урл без относительного пути и параметров (только scheme, host, port, user, pass)
    if ($location == 'ext') {
        $slashargument .= '/'. urlencode(get_url_with_no_path($parsedurl));
    }
    // Дополнение слэшаргумента относительным путём из нашего урла-источника
    $slashargument .= $parsedurl['path'] ?? '/';
    // Установка собранного слэшаргумента
    $redirecturl->set_slashargument($slashargument);

    // Установка параметров запроса
    $params = [];
    parse_str($parsedurl['query'], $params);
    $redirecturl->params($params);

    // Редирект
    redirect($redirecturl);
}

$relativepath = get_file_argument();
$args = explode('/', ltrim($relativepath,'/'));
$location = array_shift($args);
$script = array_shift($args);
$relativepath = '/'.implode('/', $args);

switch($location) {

    case 'int':

        if ($script == 'pluginfile.php') {
            include $CFG->dirroot.'/pluginfile.php';
            exit;
        }
        print_error('invalidarguments');

    case 'ext':

        $parsedurl = parse_url(urldecode($script));
        $query    = isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '';
        $content = file_get_contents(get_url_with_no_path($parsedurl) . $relativepath . $query);

        foreach($http_response_header as $header)
        {
            $headerdata = explode(':', $header, 2);
            if (isset($headerdata[0]) && in_array($headerdata[0], ['Content-Type', 'Content-Length']))
            {
                header(str_replace(["\r", "\n"], '', $header));
            }
        }

        echo $content;

        exit;
}

print_error('invalidarguments');
