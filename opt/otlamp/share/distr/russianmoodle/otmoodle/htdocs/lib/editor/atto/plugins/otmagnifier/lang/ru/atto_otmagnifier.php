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
 * Strings for component 'atto_otmagnifier', language 'ru'.
 *
 * @package    atto_otmagnifier
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Линза';
$string['settings'] = 'Настройки линзы';
$string['clickhandler'] = 'Обработка клика по изображению';
$string['clickhandler_desc'] = '
<div>Если данная настройка не отключена, то во время редактирования контента при нажатии на кнопку "Линза" для выбранных изображений будет применена дополнительная разметка в соответствии с текущим значением настройки.</div>
<div>Возможные варианты:
<ul>
<li><strong>Отключена</strong> - клик по изображению обрабатываться не будет</li>
<li><strong>Открыть изображение</strong> - изображение будет открыто в отдельной вкладке (зависит от работы браузера, большинство современных браузеров открывают отдельную вкладку)</li>
<li><strong>Открыть в отдельном окне</strong> - при открытии изображения будут переданы дополнительные параметры с целью открытия изображения в отдельном окне, а не во вкладке (зависит от работы браузера, большинство современных браузеров открывают отдельное окно)</li>
<li><strong>Открыть в отдельном окне на всю страницу</strong> - в дополнение к варианту "Открыть в отдельном окне" добавляется установка размеров окна, соответствующих размеру экрана</li>
</ul>
</div>
<div>В случае, если от изображения к изображению требуется различное поведение, управлять поведением возможно вручную, с помощью правки HTML-кода.</div>
<div>Для этого изображению с настроенным инструментом "Линза" (тег img с классом "magnifier") можно добавлять дополнительные классы: <ul>
<li><strong>magnifier-open</strong> при использовании одного этого класса, поведение соответствует настройке "Открыть изображение"</li>
<li><strong>magnifier-separate-window</strong> работает только в совокупности с "magnifier-open" и в таком случае соответствует настройке "Открыть в отдельном окне"</li>
<li><strong>magnifier-fullscreen</strong> работает только в совокупности с "magnifier-separate-window" и "magnifier-open" и в таком случае соовтетствует настройке "Открыть в отдельном окне на всю страницу"</li>
</ul></div>';
$string['clickhandler_disabled'] = 'Отключена';
$string['clickhandler_open'] = 'Открыть изображение';
$string['clickhandler_openseparatewindow'] = 'Открыть в отдельном окне';
$string['clickhandler_openseparatewindowfullscreen'] = 'Открыть в отдельном окне на всю страницу';
