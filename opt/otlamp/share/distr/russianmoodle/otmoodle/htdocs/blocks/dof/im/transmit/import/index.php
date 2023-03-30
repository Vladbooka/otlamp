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
 * Интерфейс управления обменом данными. Импорт.
 *
 * @package    im
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('../lib.php');

$DOF->require_access('admin');

// Получение GET-параметров
$mask_code = optional_param('mask', 0, PARAM_RAW_TRIMMED);
$source_code = optional_param('source', 0, PARAM_RAW_TRIMMED);

// HTML-код старинцы
$html = '';

// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('page_import_name', 'transmit'),
    $DOF->url_im('transmit', '/import/index.php', $addvars)
);

// Добавление вкладок
$html .= $DOF->im('transmit')->render_tabs('import', $addvars);

// Добавление параметров
$addvars['mask'] = $mask_code;
$addvars['source'] = $source_code;

// Текущий url
$currenturl = $DOF->url_im('transmit', '/import/index.php', $addvars);

// Получение конфигуратора
$configurator = $DOF->modlib('transmit')->get_import_configurator();

// Отображение формы выбора типа импорта
$setupform = $configurator->get_setupform($currenturl, $addvars);
// Обработка формы
$setupform->process_setup_configurator();
// Рендеринг формы
$html .= $setupform->render();

if ( ! empty($addvars['mask']) && ! empty($addvars['source']) )
{// Отображение формы настройки импорта

    // Добавление уровня навигации
    $DOF->modlib('nvg')->add_level(
        $DOF->get_string('page_import_config_name', 'transmit'),
        $currenturl
    );
    
    // Установка импорта
    $configurator->setup_from_setupform($setupform);
    
    // Форма настройки обмена данными
    $configform = $configurator->get_configform($currenturl, $addvars);
    // Обработка формы
    $configform->process();
    // Рендеринг формы
    $html .= $configform->render();
}

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

print($html);

// Печать подвала страницы
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>