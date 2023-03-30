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
 * Тема СЭО 3KL. Страница авторизации со слайдером.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// При наличии кастомного лейаута для профиля, подключаем его вместо текущего
if ( $profilelayout = theme_opentechnology_get_profile_layout($PAGE, 'login_sidebar.php') )
{
    include $profilelayout;
    
} else
{
    $templatecontext = [
        'output' => $OUTPUT,
        'bodyattributes' => $OUTPUT->body_attributes($themedata->additionalbodyclass),
        'themedata' => $themedata,
    ];
    
    echo $OUTPUT->render_from_template('theme_opentechnology/login_sidebar', $templatecontext);
}
