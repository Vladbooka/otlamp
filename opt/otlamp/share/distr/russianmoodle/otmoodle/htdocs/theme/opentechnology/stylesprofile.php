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
 * Тема СЭО 3KL. Генерация CSS файла профиля.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Запрет отображения ошибок
define('NO_DEBUG_DISPLAY', true);

//define('ABORT_AFTER_CONFIG', true);

require('../../config.php');

// Получение кода профиля
$pathinfo = explode('/', (string)$_SERVER['PATH_INFO'], 3);

// css-файл, который необходимо получить
$cssfile = $pathinfo[1] ?? null;

// код профиля, стили которого требуются
$profilecode = $pathinfo[2] ?? null;
$profilecode = ($profilecode == clean_param($profilecode, PARAM_ALPHANUM) ? $profilecode : null);

// Получение CSS для указанного профиля
$profilecss = \theme_opentechnology\cssprocessor::get_profile_css($profilecode, $cssfile);

if ( $profilecss === NULL )
{// Данные не найдены
    header('HTTP/1.0 404 css not found');
    die;
}

// 60 days only - the revision may get incremented quite often.
$lifetime = 60*60*24*60;

header('Content-Disposition: inline; filename="stylesprofile{$profilecode}.php"');
header('Accept-Ranges: none');
header('Content-Type: text/css; charset=utf-8');

print($profilecss);
die;