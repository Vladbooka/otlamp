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
$string['pluginname'] = 'Определение заимствований "Руконтекст"';
$string['rucont'] = 'Руконтекст';
$string['rucont:enable'] = 'Настройка плагина в элементе курса';
$string['rucont:viewsimilarityscore'] = 'Отображение процента заимствования';
$string['rucont:viewfullreport'] = 'Отображение ссылки на полный отчет о заимствовании';

// Настройка плагина
$string['rucontpluginsettings'] = 'Настройки плагина "Руконтекст"';
$string['otapi'] = 'Тарифный план';
$string['pageheader'] = 'Получение серийного номера';
$string['otkey'] = 'Секретный ключ';
$string['otserial'] = 'Серийный номер СЭО 3KL';
$string['get_otserial'] = 'Получить серийный номер';
$string['get_otserial_fail'] = 'Не удалось получить серийный номер СЭО 3KL на сервере api.opentechnology.ru. Сервер сообщил ошибку: {$a}';
$string['reset_otserial'] = "Сбросить серийный номер";
$string['already_has_serial'] = 'Инсталляция уже зарегистрирована и получила серийный номер, нет необходимости получать ещё один.';
$string['otserial_check_ok'] = 'Серийный номер действителен.';
$string['otserial_check_fail'] = 'Серийный номер не прошел проверку на сервере.
Причина: {$a}. Если Вы считаете, что этого не должно было
произойти, пожалуйста, обратитесь в службу технической поддержки.';
$string['otserial_tariff_wrong'] = "Тарифный план недоступен для данного продукта. Обратитесь в службу технической поддержки.";
$string['otservice'] = 'Тарифный план: <u>{$a}</u>';
$string['otservice_expired'] = 'Срок действия Вашего тарифного плана истёк. Если Вы желаете продлить срок, пожалуйста, свяжитесь с менеджерами ООО "Открытые технологии".';
$string['otservice_active'] = 'Тарифный план действителен до {$a}';
$string['otservice_unlimited'] = 'Тарифный план действует бессрочно';
$string['demo_settings'] = 'Для активации плагина обратитесь в <a href="http://rucont.ru/">Руконтекст</a>, или компанию <a href="http://opentechnology.ru/">Открытые Технологии</a>.';

$string['config'] = 'Конфигурация ';
$string['rucontconfig'] = 'Конфигурация плагина плагиаризма "Руконтекст"';
$string['userucont'] = 'Включить плагин';
$string['userucont_mod'] = 'Использовать в элементе курса {$a}';
$string['use_assignment'] = 'Использовать в элементе курса "Задание"';

$string['defaults'] = 'Настройки по умолчанию';
$string['rucontdefaults'] = 'Настройки плагина плагиаризма "Руконтекст" по умолчанию';
$string['defaultsdesc'] = 'Следующие установки являются установками по умолчанию, для элементов модулей курса';
$string['studentreports'] = 'Отобразить cвидетельства оригинальности для студентов';
$string['studentreports_help'] = 'Показывать данные о проверках студентам-пользователям';

$string['save'] = 'Сохранить';
$string['configupdated'] = 'Конфигурация обновлена';
$string['defaultupdated'] = 'Настройки обновлены';

// Информация о проверке
$string['submissioncheck'] = 'Загруженный ответ будет протестирован на наличие заимствований в системе "Руконтекст"';
$string['processingyet'] = 'На проверке';
$string['similarityscore'] = 'Ошибка';
$string['error_process'] = 'Ошибка';
$string['reportlink'] = 'Ссылка на отчёт';
$string['similarityscore'] = 'Заимствования: {$a}%';
