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
 * Антиплагиат. Информация о тарифе.
 *
 * @package    plagiarism
 * @subpackage apru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir.'/adminlib.php');
defined('MOODLE_INTERNAL') || die;

// Установка расширенной страницы настроек
admin_externalpage_setup('plagiarismapru');

// Подготовка переменной для хранения HTML-кода страницы
$html = '';

try 
{// Установка соединения с сервером Антиплагиата
    $connection = new plagiarism_apru\connection();
} catch ( moodle_exception $e )
{// Ошибка соединения с сервером Антиплагиата
    
    // Отображение ошибки 
    $html .= html_writer::div(
        get_string('apru_tarif_connection_failed', 'plagiarism_apru'));
    
    // Отображение дополнительной информации для разработчиков
    debugging($e->debuginfo, DEBUG_DEVELOPER, $e->getTrace());
}

if ( ! empty($connection) )
{// Соединение установлено
    try
    {// Получение информации о тарифе
        $tarifinfo = $connection->get_tarif_info();
    } catch( moodle_exception $e)
    {// Ошибка получения инфрмации
        
        // Отображение ошибки 
        $html .= html_writer::div(
            get_string('apru_tarif_get_information_failed', 'plagiarism_apru'));
        
        // Отображение дополнительной информации для разработчиков
        debugging($e->debuginfo, DEBUG_DEVELOPER, $e->getTrace());
    }
}

if( ! empty($tarifinfo) )
{// Информация о тарифе получена

    // Нормализация полученных данных для отображения на странице
    // Дата начала действия тарифа
    if ( $tarifinfo->GetTariffInfoResult->SubscriptionDate )
    {
        preg_match(
            '/[0-9]{4}-[0-9]{2}-[0-9]{2}/', 
            $tarifinfo->GetTariffInfoResult->SubscriptionDate, 
            $matches
        );
        $subscriptiondate = ! empty($matches[0]) ? $matches[0] : $tarifinfo->GetTariffInfoResult->SubscriptionDate;
    } else
    {
        $subscriptiondate = get_string('apru_tarif_no_information', 'plagiarism_apru');
    }
    
    // Дата окончания действия тарифа
    if( $tarifinfo->GetTariffInfoResult->ExpirationDate )
    {
        preg_match(
            '/[0-9]{4}-[0-9]{2}-[0-9]{2}/', 
            $tarifinfo->GetTariffInfoResult->ExpirationDate, 
            $matches
        );
        $expirationdate = ! empty($matches[0]) ? $matches[0] : $tarifinfo->GetTariffInfoResult->ExpirationDate;
    } else
    {
        $expirationdate = get_string('apru_tarif_no_information', 'plagiarism_apru');
    }
    
    // Название тарифа
    if( $tarifinfo->GetTariffInfoResult->Name )
    {
        $name = $tarifinfo->GetTariffInfoResult->Name;
    } else 
    {
        $name = get_string('apru_tarif_no_information', 'plagiarism_apru');
    }
    
    // Общее число проверок
    if( $tarifinfo->GetTariffInfoResult->TotalChecksCount )
    {
        $totalcheckscount = $tarifinfo->GetTariffInfoResult->TotalChecksCount;
    } else 
    {
        $totalcheckscount = get_string('apru_tarif_no_information', 'plagiarism_apru');
    }
    
    // Оставшиеся число проверок
    if( $tarifinfo->GetTariffInfoResult->RemainedChecksCount )
    {
        $remainedcheckscount = $tarifinfo->GetTariffInfoResult->RemainedChecksCount;
    } else
    {
        $remainedcheckscount = get_string('apru_tarif_no_information', 'plagiarism_apru');
    }
    
    // Формирование таблицы информации
    $table = new html_table();
    // выравнивание колонок таблицы по левому краю
    $table->align = ['left', 'left'];
    // заголовка у таблицы не будет
    $table->head = [];
    // заполним таблицу обработанными данными о тарифе
    $table->data[] = [get_string('apru_tarif_name', 'plagiarism_apru'), $name];
    $table->data[] = [get_string('apru_tarif_subscriptiondate', 'plagiarism_apru'), $subscriptiondate];
    $table->data[] = [get_string('apru_tarif_expirationdate', 'plagiarism_apru'), $expirationdate];
    $table->data[] = [get_string('apru_tarif_totalcheckscount', 'plagiarism_apru'), $totalcheckscount];
    $table->data[] = [get_string('apru_tarif_remainedcheckscount', 'plagiarism_apru'), $remainedcheckscount];
    $html .= html_writer::table($table);
} else 
{// Данные не получены
    $html .= html_writer::div(get_string('apru_tarif_no_information', 'plagiarism_apru'));
}

// Шапка
echo $OUTPUT->header();

// Контент
echo $html;

// Футер
echo $OUTPUT->footer();