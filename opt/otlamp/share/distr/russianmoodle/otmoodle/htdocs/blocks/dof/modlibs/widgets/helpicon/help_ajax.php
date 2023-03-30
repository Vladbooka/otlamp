<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
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
 * Класс иконки подсказки. Страница AJAX-подсказки
 *
 * @package    modlib
 * @subpackage widgets
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_MOODLE_COOKIES', true);
define('AJAX_SCRIPT', true);

require_once(dirname(realpath(__FILE__)) . '/../lib.php');

$identifier = required_param('identifier', PARAM_ALPHANUMEXT);
$plugintype  = required_param('plugintype', PARAM_ALPHANUMEXT);
$plugincode  = required_param('plugincode', PARAM_ALPHANUMEXT);
$lang       = optional_param('lang', 'en', PARAM_LANG);

$SESSION->lang = $lang;

$data = new stdClass();
$data->heading = $DOF->get_string($identifier, $plugincode, null, $plugintype, ['empry_result' => get_string('help')]);
$data->text = $DOF->get_string($identifier.'_help', $plugincode, null, $plugintype);

echo json_encode($data);