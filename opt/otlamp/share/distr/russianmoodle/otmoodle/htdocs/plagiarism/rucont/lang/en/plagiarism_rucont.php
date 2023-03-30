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
 * Плагин определения заимствований Руконтекст. Языковые файлы.
 *
 * @package    plagiarism
 * @subpackage rucont
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Базовые строки
$string['pluginname'] = 'The definition of borrowing "Rukontekst" ';
$string['rucont'] = 'Rukontekst';
$string['rucont:enable'] = 'Setting the plugin element of the course';
$string['rucont:viewsimilarityscore'] = 'Display per cent borrowing';
$string['rucont:viewfullreport'] = 'Displays links to the full report on borrowing';

// Настройка плагина
$string['rucontpluginsettings'] =' Settings plugin "Rukontekst" ';
$string['otapi'] = 'tariff plan';
$string['pageheader'] = 'Obtaining a serial number';
$string['otkey'] = 'secret key';
$string['otserial'] = 'serial number';

$string['get_otserial'] = 'Get serial number';
$string['get_otserial_fail'] = 'Attempt to get LMS 3KL serial number failed. Server reported an error: {$a}';
$string['reset_otserial'] = 'Reset the serial number';
$string['already_has_serial'] = 'Installing the already registered and received the serial number, there is no need to get another one.';
$string['otserial_check_ok'] = 'The serial number is valid.';
$string['otserial_check_fail'] = 'The serial number has been inspected on the server.
The reason: {$a}. If you think that this should not have been
occur, please contact technical support. ';
$string['otserial_tariff_wrong'] = 'The tariff plan is not available for this product. Please contact technical support.';
$string['otservice'] = 'tariff plan: <u> {$a} </ u>';
$string['otservice_expired'] = 'The validity of your tariff plan expired. If you want to extend the term, please contact the managers of company "Open Technology". ';
$string['otservice_active'] = 'The tariff plan is valid until {$a}';
$string['otservice_unlimited'] = 'The tariff plan is valid indefinitely';
$string['demo_settings'] =' To activate the plug-in contact <a href="http://rucont.ru/"> Rukontekst </a>, or company <a href = "http://opentechnology.ru/ "> Open Technologies </a>. ';

$string['config'] = 'Configuration';
$string['rucontconfig'] = 'The configuration of the plug-plagiarism "Rukontekst" ';
$string['userucont'] = 'Enable plugin';
$string['userucont_mod'] = 'Use the element of the course {$a}';
$string['use_assignment'] = 'Use in the course element "Task"';

$string['defaults'] = 'default settings';
$string['rucontdefaults'] =' Settings plug-plagiarism "Rukontekst" default ';
$string['defaultsdesc'] = 'The following settings are the default settings for the elements of the course module';
$string['studentreports'] = 'Show Certificate of originality for the students';
$string['studentreports_help'] = 'Show students on checks users';

$string['save'] = 'Save';
$string['configupdated'] = 'Configuration updated';
$string['defaultupdated'] = 'Preferences updated';

// Информация о проверке
$string['submissioncheck'] = 'The uploaded answer will be tested for the presence of debt in the system "Rukontekst" ';
$string['processingyet'] = 'On inspection';
$string['similarityscore'] = 'error';
$string['error_process'] = 'error';
$string['reportlink'] = 'A link to the report';
$string['similarityscore'] = 'Borrowing: {$a}%';