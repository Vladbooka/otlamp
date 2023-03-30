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
 * Plugin strings are defined here.
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Библиотека ресурсов';
$string['modulename'] = 'Библиотека ресурсов';
$string['modulenameplural'] = 'Библиотеки ресурсов';
$string['pluginadministration'] = 'Управление плагином Библиотека ресурсов';
$string['missingidandcmid'] = 'Просмотр страницы не возможен без указания обязательных параметров';
$string['modulename_help'] = 'Библиотека ресурсов это универсальный модуль для интеграции внешних ресурсов в СДО Moodle.';

$string['otresourcelibrary:view'] = 'Право видеть элемент курса "Библиотека ресурсов"';
$string['otresourcelibrary:addinstance'] = 'Право добавлять модуль "Библиотека ресурсов" в курс';
$string['otresourcelibrary:viewbyparameter'] = 'Право просматривать материал с использованием параметров в ссылке';

$string['library_elemenrt_name'] = 'Название';
$string['short_description'] = 'Краткое описание';
$string['materialtypes'] = 'Тип просмотра материала';

$string['no_selected_material'] = 'Пока не задан';
$string['material_pagenum'] = 'Номер страницы:';
$string['material_chapter'] = 'Код/название параграфа:';
$string['material_fragment'] = 'Якорь:';
$string['sourcename'] = 'Наименование источника';
$string['resourceid'] = 'Идентификатор ресурса';
$string['resource'] = 'Ресурс';
$string['display_point'] = 'Точка показа в материале источника';
$string['search'] = 'Найти';
$string['search_placeholder'] = 'Поиск материалов';
$string['your_location'] = 'Вы находитесь';
$string['additional_sort'] = 'Дополнительные поля сортировки';
$string['more'] = 'Показать больше';
$string['select_all'] = 'Все источники';
$string['not_selected'] = 'Все ресурсы и категории';
$string['no_data'] = 'Ничего не найдено';
$string['search_heder_text'] = 'Результаты поиска:';
$string['search_result_text'] = 'Для отображения результатов поиска требуется выбрать раздел\категорию или воспользоваться строкой поиска для поиска во всех разделах.';
$string['view_by_parameter'] = 'У Вас нет прав просматривать материал с использованием параметров в ссылке';

$string['modal_form_header'] = 'Параметры источника данных';
$string['preview_header'] = 'Предварительный просмотр материала';
$string['section_selection'] = 'Выбор категории';
$string['modal_form_save'] = 'Применить';
$string['modal_form_cancel'] = 'Отмена';
$string['return'] = 'Вернуться назад';
$string['otresourcelibrary_settings_button'] = 'Настройки материала';
$string['go_to_view_btn'] = 'Перейти к просмотру';
$string['select_btn'] = 'Выбрать';

$string['mod_form_updated'] = 'Настройки библиотеки ресурсов обновлены';
$string['mod_form_created'] = 'Настройки библиотеки ресурсов созданы';

$string['manage_source'] = 'Управление источниками';
$string['source_type'] = 'Тип источника';
$string['source_changes'] = 'Сохранить изменения источника  {$a}';
$string['source_deletion'] = 'Подтвердить удаление источника  {$a}';
$string['name_source'] = 'Придумайте наименование источнику';
$string['add_source'] = 'Добавить источник';
$string['activity_source'] = 'Активность источника';
$string['activity_source_active'] = 'Активен';
$string['activity_source_inactive'] = 'Не активен';
$string['save_sources_activity'] = 'Сохранить настройки активности';
$string['edit_source'] = 'Редактировать источник';
$string['delete_source'] = 'Удалить источник';
$string['source_types'] = 'Доступные типы источников';
$string['adding_source'] = 'Добавление источника';

$string['error_get_content'] = 'Не удалось получить контент. Возможно, недостаточно прав на просмотр текущего контента.';
$string['error_delete_source'] = 'Не удалось удалить источник';
$string['error_edit_source'] = 'Не удалось внести правки в источник';
$string['error_save_details'] = 'Не удалось сохранить реквизиты';
$string['error_anchor_not_supported'] = 'Указанный тип якоря не поддерживается';
$string['wrong_param_khipu_setting'] = 'Параметры заданные в настройках материала не корректны';
$string['empty_khipu_setting'] = 'Не заданы параметры в настройках материала';
$string['no_material'] = 'Материал не задан или отсутствует';
$string['error_response_malformed'] = 'Не правильный ответ сервера (Вероятно библиотека ресурсов не настроена)';
$string['error_executing_request'] = 'Произошла ошибка при выполнении запроса.';
$string['error_save_sources_activity'] = 'Не удалось сохранить сведения об активности источников';

$string['settings_otserial'] = 'Тарифный план';
$string['already_has_serial'] = 'Серийный номер уже был получен';
$string['reset_otserial'] = 'Сбросить серийный номер';
$string['otserial_check_fail'] = 'Серийный номер не прошел проверку на сервере.
Причина: {$a}. Если Вы считаете, что этого не должно было
произойти, пожалуйста, обратитесь в службу технической поддержки.';
$string['otkey'] = 'Секретный ключ';
$string['otserial'] = 'Серийный номер СЭО 3KL';
$string['otserial_check_ok'] = 'Серийный номер действителен.';
$string['get_otserial'] = 'Получить серийный номер';
$string['get_otserial_fail'] = 'Не удалось получить серийный номер СЭО 3KL на сервере api.opentechnology.ru. Сервер сообщил ошибку: {$a}';
$string['otservice'] = 'Тарифный план: <u>{$a}</u>';
$string['otserial_tariff_wrong'] = "Тарифный план недоступен для данного продукта. Обратитесь в службу технической поддержки.";
$string['otservice_expired'] = 'Срок действия Вашего тарифного плана истёк. Если Вы желаете продлить срок, пожалуйста, свяжитесь с менеджерами ООО "Открытые технологии".';
$string['otservice_active'] = 'Тарифный план действителен до {$a}';
$string['otservice_unlimited'] = 'Тарифный план действует бессрочно';

$string['settings_sources'] = 'Источники данных';

$string['otapi_exception'] = '{$a}';

$string['edit_src'] = '{$a->sourcename}';

$string['info_result_was_limited'] = 'Результат выборки был ограничен, по каждому из ресурсов будет отображено не более чем 99 позиций.';
$string['installation_sources_names_nodata'] = 'Не удалось получить список доступных источников. Проверьте ваш <a href="/admin/settings.php?section=mod_otresourcelibrary_otserial">тарифный план</a> и сообщите о возникшей ситуации сотруднику технической поддержки.';
$string['implemented_sourcetypes_nodata'] = 'Вам не доступна возможность добавления новых источников. Для получения такой возможности, вам нужно сменить ваш <a href="/admin/settings.php?section=mod_otresourcelibrary_otserial">тарифный план</a>.';

