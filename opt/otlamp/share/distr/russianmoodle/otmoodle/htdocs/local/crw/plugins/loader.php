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
 * Витрина курсов. Загрузчик файлов для субплагинов Витрины
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_MOODLE_COOKIES', true);

require_once('../../../config.php');
require_once($CFG->dirroot . '/lib/filelib.php');
require_once($CFG->dirroot . '/lib/jslib.php');

// Безопасное получение параметра из запроса ( url после loader.php )
$path = get_file_argument();

// Валидация параметра - [plugin]/[version]/[path] 
$matches = array();
if (!preg_match('~^/([a-z0-9_]+)/((?:[0-9.]+)|-1)(/.*)$~', $path, $matches)) 
{// Валидация не пройдена
    print_error('filenotfound');
}
// Разбиваем по массивам
list($junk, $crwplugin, $version, $innerpath) = $matches;

// Проверяем существование файла
$pluginfolder = $CFG->dirroot . '/local/crw/plugins/' . $crwplugin;
$file = $pluginfolder . '/assets' .$innerpath;
if ( ! file_exists($file)) 
{// Файл не найден
    print_error('filenotfound');
}

// Кэширование
$allowcache = ($version !== '-1');
if ($allowcache) 
{// Версия файла не для отладки - кэшируем
    header('Expires: ' . date('r', time() + 365 * 24 * 3600));
    header('Cache-Control: max-age=' . 365 * 24 * 3600);
    // Pragma is set to no-cache by default so must be overridden.
    header('Pragma:');
}

// Устанавливаем тип
$mimetype = mimeinfo('type', $file);
// Готовим файл
header('Content-Length: ' . filesize($file));
header('Content-Type: ' . $mimetype);
readfile($file);
