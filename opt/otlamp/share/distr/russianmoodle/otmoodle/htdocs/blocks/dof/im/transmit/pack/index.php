<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
//                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

/**
 * Интерфейс управления обменом данными. Управление пакетами настроек обмена.
 *
 * @package    im
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('../lib.php');

// HTML-код старинцы
$html = '';

$DOF->require_access('admin');

// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('page_pack_name', 'transmit'),
    $DOF->url_im('transmit', '/pack/index.php', $addvars)
);

// Добавление вкладок
$html .= $DOF->im('transmit')->render_tabs('pack', $addvars);

// Текущий url
$currenturl = $DOF->url_im('transmit', '/pack/index.php', $addvars);

$packlist = $DOF->im('transmit')->display_pack_list(
    array_keys($DOF->workflow('transmitpacks')->get_meta_list('real')), 
    ['returnhtml' => true]);

$html .= $packlist;

// Подключение стилей
$DOF->modlib('nvg')->add_css('im', 'transmit', '/pack/style.css');

$DOF->modlib('nvg')->add_js('im', 'transmit', '/pack/script.js', false);
// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

print($html);

// Печать подвала страницы
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>