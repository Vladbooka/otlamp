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
 * Тема СЭО 3KL. Страница без блоков.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// При наличии кастомного лейаута для профиля, подключаем его вместо текущего
if ( $profilelayout = theme_opentechnology_get_profile_layout($PAGE) )
{
    include $profilelayout;
} else
{
// Подключение необходимого типа страницы авторизации
$layout = theme_opentechnology_loginpage_layout($PAGE);

if (!$OUTPUT->is_login_page())
{
    $layout = 'standard';
}

$layoutpath = $CFG->dirroot.'/theme/opentechnology/layout/login_'.$layout.'.php';

// Получение настроек текущей страницы авторизации
$functionname = 'theme_opentechnology_get_html_for_settings_loginpage_'.$layout;
$themedata = $functionname($OUTPUT, $PAGE);

// Подключение страницы авторизации
include $layoutpath;
}