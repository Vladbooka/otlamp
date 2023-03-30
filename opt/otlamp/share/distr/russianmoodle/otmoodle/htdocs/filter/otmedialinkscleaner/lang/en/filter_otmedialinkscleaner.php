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
 * Strings for component 'filter_otmedialinkscleaner'
 *
 * @package    filter
 * @subpackage otmedialinkscleaner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['filtername'] = 'Удаление ссылок на медиа-файлы';

$string['settings_videoprocessing'] = 'Настройки обработки тега video';
$string['settings_videoprocessing_desc'] = '';
$string['settings_downloadbutton_disable'] = 'Отключить кнопку скачивания видео в теге video';
$string['settings_downloadbutton_disable_desc'] = 'После включения опции в элементах управления тега video не будет отображаться кнопка для скачивания видео';

$string['settings_general'] = 'Настройки удаления ссылок на медиафайлы';
$string['settings_general_desc'] = 'В зависимости от указанных ниже настроек возможно настроить удаление ссылок на медиафайлы, оставленные после обработки стандартным медиаплагином';

$string['settings_flv_header'] = '.flv';
$string['settings_flv_header_desc'] = 'Настойки для ссылок на файлы с расширением flv';
$string['settings_flv'] = 'Удалять flv-ссылки, оставленные в качестве фоллбека после медиаплагина';
$string['settings_flv_desc'] = 'Если опция включена, то ссылки, оставленные после обработки стандартным медаплагином и найденные текущим фильтром будут заменены на текст, указанный ниже';
$string['settings_flv_fallback_nonmedia'] = 'Обрабатывать обычные flv-ссылки';
$string['settings_flv_fallback_nonmedia_desc'] = 'Отметьте, если хотите, чтобы фильтр очистил контент ото всех ссылок подобного типа, вне зависимости от того, обрабатывал ли их ранее медиаплагин';
$string['settings_flv_fallback_text'] = 'Текст для отображения вместо flv-ссылки';
$string['settings_flv_fallback_text_desc'] = 'Если включено удаление ссылок, то вместо них будет подставляться текст, указанный в этом поле. Обратите внимание, что текст может быть обрамлен только inline-тегами, в противном случае, он может отображаться вместе с плеером.';
$string['settings_flv_fallback_text_default'] = 'Просмотр flv-файла не доступен. Вероятно, для вашего браузера не установлен flash-player. Попробуйте установить или обновить его и открыть эту страницу снова.';

$string['settings_ext_header'] = '.{$a}';
$string['settings_ext_header_desc'] = 'Настойки для ссылок на файлы с расширением {$a}';
$string['settings_ext'] = 'Удалять {$a}-ссылки, оставленные в качестве фоллбека после медиаплагина';
$string['settings_ext_desc'] = 'Если опция включена, то ссылки, оставленные после обработки стандартным медаплагином и найденные текущим фильтром будут заменены на текст, указанный ниже';
$string['settings_ext_fallback_nonmedia'] = 'Обрабатывать обычные {$a}-ссылки';
$string['settings_ext_fallback_nonmedia_desc'] = 'Отметьте, если хотите, чтобы фильтр очистил контент ото всех ссылок подобного типа, вне зависимости от того, обрабатывал ли их ранее медиаплагин';
$string['settings_ext_fallback_text'] = 'Текст для отображения вместо {$a}-ссылки';
$string['settings_ext_fallback_text_desc'] = 'Если включено удаление ссылок, то вместо них будет подставляться текст, указанный в этом поле. Обратите внимание, что текст может быть обрамлен только inline-тегами, в противном случае, он может отображаться вместе с плеером.';
$string['settings_ext_fallback_text_default'] = 'Воспроизведение медиафайла невозможно. Вероятно, у вас установлен устаревший браузер. Обновите его до актуальной версии и попробуйте открыть эту страницу снова';
