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
 *  Языковой пакет для OT log
 *
 *  @package    local_opentechnology
 *  @subpackage otcomponent_otlogger
 *  @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Система логирования OTlogger';

// Глобальные настройки
$string['log_enabled'] = 'Включить логирование';
$string['log_enabled_description'] = 'Включить логирование с помощью OTLogger';
$string['new_log_configuration_name'] = 'Имя конфигурации';
$string['log_method_enabled'] = 'Включить логирование по данной конфигурации';
$string['log_method_enabled_description'] = 'Включить логирование по данной конфигурации. Вы можете использовать несколько конфигураций одновременно.';
$string['log_method_configuration'] = 'Настройки конфигурации';
$string['receiver'] = 'Получатель логов';
$string['receiver_description'] = 'Выберите подходящий получатель логов';
$string['filter'] = 'Фильтрация логов';
$string['filter_noselection'] = 'Ничего не выбрано';
$string['filter_description'] = 'Выберите требуемые объекты логирования';
$string['add_log_configuration'] = 'Добавить еще 1 конфигурацию';
$string['adding_configurations'] = 'Добавление новой конфигурации';
$string['editing_configurations'] = 'Редактирование существующей конфигурации: {$a}';
$string['delete_configuration'] = 'Удалить конфигурацию';

// Названия получателей логов
$string['error_log'] = 'error_log';

// Права
$string['local/opentechnology:manage_log_parameters'] = 'Настраивать логирование с помощью OTlogger';

// Errors
$string['error_empty_configuration_name'] = 'Имя конфигурации не может быть пустым!';
$string['error_duplicate_configuration_name'] = 'Имя конфигурации должно быть уникальным.';