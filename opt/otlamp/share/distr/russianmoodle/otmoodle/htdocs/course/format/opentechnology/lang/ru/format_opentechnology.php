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
 * Плагин формата курса Темы-спойлеры. Языковой пакет.
 *
 * @package    format
 * @subpackage otspoilers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Базовые переменные
$string['currentsection'] = 'This topic';
$string['sectionname'] = 'Topic';
$string['pluginname'] = 'СЭО 3KL';

$string['page-course-view-topics'] = 'Any course main page in topics format';
$string['page-course-view-topics-x'] = 'Any course page in topics format';
$string['hidefromothers'] = 'Скрыть секцию';
$string['showfromothers'] = 'Показать секцию';
$string['settings_format_opentechnology_base'] = 'Базовый';
$string['settings_format_opentechnology_spoiler'] = 'Сворачиваемые секции';
$string['settings_format_opentechnology_accordion'] = 'Аккордеон';
$string['settings_format_opentechnology_carousel'] = 'Карусель';
$string['settings_format_opentechnology_base_elements_view'] = 'Обычное';
$string['settings_format_opentechnology_icon_elements_view'] = 'В виде иконок';
$string['settings_format_opentechnology_base_with_badges_elements_view'] = 'Обычное с отображением значков';
$string['settings_format_opentechnology_icon_with_badges_elements_view'] = 'В виде иконок с отображением значков';

// Страница курса
$string['section_0_name'] = 'Введение';
$string['section_default_name'] = 'Секция {$a}';
$string['toggleall_collapse'] = 'Показать все';
$string['toggleall_expand'] = 'Скрыть все';

// Настройки курса
$string['course_settings_sectionsnumber'] = 'Число секций в курсе';
$string['course_settings_hiddensections'] = 'Отображение скрытых секций';
$string['course_settings_hiddensections_collapsed'] = 'В неразвернутом виде';
$string['course_settings_hiddensections_invisible'] = 'Полностью невидимы';
$string['course_settings_coursedisplay'] = 'Представление курса';
$string['course_settings_coursedisplay_multi'] = 'Показывать все секции на одной странице';
$string['course_settings_coursedisplay_single'] = 'Показывать одну секцию на странице';
$string['course_settings_caption_align_title'] = 'Выравнивание заголовков секций';
$string['course_settings_caption_align_option_left'] = 'По левому краю';
$string['course_settings_caption_align_option_center'] = 'По центру';
$string['course_settings_caption_align_option_right'] = 'По правому краю';
$string['course_settings_caption_align_desc'] = 'Текст заголовка будет выровнен по левой части, центру, или же правой части секции';
$string['course_settings_caption_align_desc_help'] = 'Текст заголовка будет выровнен по левой части, центру, или же правой части секции';
$string['course_settings_display_mode_title'] = 'Режим отображения секций';
$string['course_settings_display_mode_desc'] = 'Режим отображения секций';
$string['course_settings_display_mode_desc_help'] = 'Способ отображения секций на странице курса';
$string['course_settings_caption_icons_enabled_title'] = 'Иконка в заголовке секции';
$string['course_settings_caption_icons_enabled_desc'] = 'Отображение иконки в заголовке секции курса';
$string['course_settings_caption_icons_enabled_desc_help'] = 'Отображение иконки в заголовке секции курса';
$string['course_settings_elements_display_mode_title'] = 'Режим отображения элементов курса';
$string['course_settings_elements_display_mode_desc'] = 'Режим отображения элементов курса';
$string['course_settings_elements_display_mode_desc_help'] = 'Если включить режим отображения в виде иконок, то смещенные вправо элементы курса будут отображены в виде иконок. Сами иконки элементов курса будут изменяться в зависимости от прохождения элемета курса. <br/>
    <b>ВНИМАНИЕ</b><br/>
    Требуется добавление иконок для всех используемых модулей: <br/>
    /theme/[Используемая тема]/pix_plugins/mod/[Имя модуля]/icon_complete<br/>
    /theme/[Используемая тема]/pix_plugins/mod/[Имя модуля]/icon_fail
';
$string['course_settings_caption_icon_toggle_open_title'] = 'Иконка развернутой секции';
$string['course_settings_caption_icon_toggle_closed_title'] = 'Иконка свернутой секции';
$string['course_settings_course_display_mode_title'] = 'Режим отображения курса';
$string['settings_format_opentechnology_course_display_mode_0'] = 'Экспертный режим';
$string['settings_format_opentechnology_course_display_mode_1'] = 'Один столбец';
$string['settings_format_opentechnology_course_display_mode_2'] = 'Два столбца';
$string['course_settings_header_general'] = 'Общие настройки';
$string['course_settings_header_courseview'] = 'Настройки вида курса';
$string['course_settings_header_sectionview'] = 'Настройки вида секций';
$string['course_settings_header_modview'] = 'Настройки вида элементов курса';

// Настройки плагина
$string['settings_default_blocks_region_side_pre_title'] = 'Блоки в позиции side-pre для новых курсов';
$string['settings_default_blocks_region_side_pre_desc'] = 'Список кодов блоков через запятую( например search_forums, news_items, calendar_upcoming, recent_activity), которые требуется автоматически добавить на страницу курса при его создании. Блоки будут добавлены в колонку слева, имя колонки по умолчанию side-pre';
$string['settings_region_side_pre_rename_title'] = 'Переопределение кода позиции левого столбца';
$string['settings_region_side_pre_rename_desc'] = 'Если в текущей теме используются нестандартные коды позиций блоков, введите новый код для замены стандартного side-pre';
$string['settings_default_blocks_region_side_post_title'] = 'Блоки в позиции side-post для новых курсов';
$string['settings_default_blocks_region_side_post_desc'] = 'Список кодов блоков через запятую( например search_forums, news_items, calendar_upcoming, recent_activity), которые требуется автоматически добавить на страницу курса при его создании. Блоки будут добавлены в колонку справа, имя колонки по умолчанию side-post';
$string['settings_region_side_post_rename_title'] = 'Переопределение кода позиции правого столбца';
$string['settings_region_side_post_rename_desc'] = 'Если в текущей теме используются нестандартные коды позиций блоков, введите новый код для замены стандартного side-post';
$string['settings_caption_align_title'] = 'Выравнивание заголовков секций по умолчанию';
$string['settings_caption_align_desc'] = 'Установка выравнивания заголовков секций в курсе';
$string['settings_caption_align_help'] = 'Текст заголовка будет выровнен по левой части, центру, или же правой части секции';
$string['settings_caption_align_option_left'] = 'По левому краю';
$string['settings_caption_align_option_center'] = 'По центру';
$string['settings_caption_align_option_right'] = 'По правому краю';
$string['settings_display_mode_title'] = 'Режим отображения по умолчанию';
$string['settings_display_mode_desc'] = 'Способ отображения страницы курса по умолчанию';
$string['settings_display_mode_help'] = '';
$string['settings_caption_icons_enabled_title'] = 'Отображение иконки в заголовке секции по умолчанию';
$string['settings_caption_icons_enabled_desc'] = 'Если включить, то будут отображатся иконки сворачивания/разворачивания секций в курсе по умолчанию';
$string['settings_caption_icons_enabled_desc_help'] = '';
$string['settings_elements_display_mode_title'] = 'Режим отображения элементов курса';
$string['settings_elements_display_mode_desc'] = 'Режим отображения элементов курса';
$string['settings_elements_display_mode_desc_help'] = 'Если включить режим отображения в виде иконок, то смещенные вправо элементы курса будут отображены в виде иконок. Сами иконки элементов курса будут изменяться в зависимости от прохождения элемета курса. <br/>
    <b>ВНИМАНИЕ</b><br/>
    Требуется добавление иконок для всех используемых модулей: <br/>
    /theme/[Используемая тема]/pix_plugins/mod/[Имя модуля]/icon_complete<br/>
    /theme/[Используемая тема]/pix_plugins/mod/[Имя модуля]/icon_fail
';
$string['settings_caption_icon_open_title'] = 'Иконка развернутой секции в курсе по умолчанию';
$string['settings_caption_icon_open_desc'] = '';
$string['settings_caption_icon_open_desc_help'] = '';
$string['settings_caption_icon_closed_title'] = 'Иконка свернутой секции в курсе по умолчанию';
$string['settings_caption_icon_closed_desc'] = '';
$string['settings_caption_icon_closed_desc_help'] = '';


$string['settings_section_width'] = 'Ширина секции (по умолчанию)';
$string['settings_section_width_help'] = 'При создании курса с форматом "СЭО 3KL" по умолчанию будет выбано это значение ширины секции. Выбранное значение можно переопределить на уровне настроек курса или в любой из секций курса. Ширина секций указывается в процентах от ширины блока с контентом курса.';
$string['settings_section_lastinrow'] = 'Завершать ли раздел (по умолчанию)';
$string['settings_section_lastinrow_help'] = 'При создании курса с форматом "СЭО 3KL" по умолчанию будет выбано это значение завершения раздела. Выбранное значение можно переопределить на уровне настроек курса или в любой из секций курса. Секции в курсе группируются в разделы. Каждый раздел начинается с новой строки. Если выбрано отображение в виде карусели, то на каждом из слайдов отображается только одна строка.';
$string['settings_section_summary_width'] = 'Ширина описания секции (по умолчанию)';
$string['settings_section_summary_width_help'] = 'При создании курса с форматом "СЭО 3KL" по умолчанию будет выбано это значение ширины описания секции. Выбранное значение можно переопределить на уровне настроек курса или в любой из секций курса. Ширина описания секции указывается в процентах от ширины секции.';

$string['course_settings_section_width'] = 'Ширина секций (по умолчанию)';
$string['course_settings_section_width_help'] = 'Ширина секций указывается в процентах от ширины блока с контентом курса и является значением по умолчанию для секций в этом курсе, то есть влияет на отображение только если ширина не переопределена в самой секции';
$string['course_settings_set_section_width'] = 'Установить ширину секций принудительно';
$string['course_settings_set_section_width_help'] = 'Данная опция позволяет принудительно установить во всех секциях этого курса значение ширины секции, указанное в опции "Ширина секции (по умолчанию)"';
$string['course_settings_section_lastinrow'] = 'Завершать ли раздел (по умолчанию)';
$string['course_settings_section_lastinrow_help'] = 'Данная опция является значением по умолчанию для секций в этом курсе, то есть влияет на отображение только если завершение не переопределено в самой секции. Секции в курсе группируются в разделы. Каждый раздел начинается с новой строки. Если выбрано отображение в виде карусели, то на каждом из слайдов отображается только одна строка.';
$string['course_settings_set_section_lastinrow'] = 'Установить завершение раздела принудительно';
$string['course_settings_set_section_lastinrow_help'] = 'Данная опция позволяет принудительно установить во всех секциях этого курса завершение раздела, указанное в опции "Завершать ли раздел (по умолчанию)". Секции в курсе группируются в разделы. Каждый раздел начинается с новой строки. Если выбрано отображение в виде карусели, то на каждом из слайдов отображается только одна строка.';
$string['course_settings_section_summary_width'] = 'Ширина описания секции (по умолчанию)';
$string['course_settings_section_summary_width_help'] = 'Ширина описания секции указывается в процентах от ширины секции и является значением по умолчанию для секций в этом курсе, то есть влияет на отображение только если ширина описания не переопределена в самой секции';
$string['course_settings_set_section_summary_width'] = 'Установить ширину описания секции принудительно';
$string['course_settings_set_section_summary_width_help'] = 'Данная опция позволяет принудительно установить во всех секциях этого курса значение ширины описания секции, указанное в опции "Ширина описания секции (по умолчанию)"';

// Настройки секции
$string['course_section_settings_section_width'] = 'Ширина секции';
$string['course_section_settings_section_width_help'] = 'Ширина секций указывается в процентах от ширины блока с контентом курса';
$string['course_section_settings_section_lastinrow'] = 'Завершать ли раздел';
$string['course_section_settings_section_lastinrow_help'] = 'Данная опция позволяет установить заврешение раздела после выбранной секции. Секции в курсе группируются в разделы. Каждый раздел начинается с новой строки. Если выбрано отображение в виде карусели, то на каждом из слайдов отображается только одна строка.';
$string['course_section_settings_section_summary_width'] = 'Ширина описания секции';
$string['course_section_settings_section_summary_width_help'] = 'Ширина описания секции указывается в процентах от ширины секции';

$string['slideprev'] = "Назад";
$string['slidenext'] = "Далее";