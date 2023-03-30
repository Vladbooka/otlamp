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
 * Слайдер изображений. Языковые файлы.
 *
 * @package    block
 * @subpackage otslider
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// СИСТЕМНЫЕ СТРОКИ
$string['pluginname'] = 'Слайдер';
$string['otslider:addinstance'] = 'Добавлять новый блок «Слайдер»';
$string['otslider:myaddinstance'] = 'Добавлять новый блок «Слайдер» на страницу /my (Мои курсы, Личный кабинет, Dashboard)';
$string['otslider:viewallfields'] = 'Просматривать все пользовательские поля в слайдах, отображающих объекты типа пользователь';

// НАСТРОЙКИ
$string['config_header_main_label'] = 'Основные настройки блока';
$string['config_slidername'] = 'Название (код) слайдера';
$string['config_slidername_help'] = 'Данная строка может использоваться в качестве селектора для стилизации конкретного слайдера, уникальность не проверяется';
$string['config_height'] = 'Высота слайдера (процент от ширины слайдера)';
$string['config_height_help'] = 'Рекомендуется обязательно установить это значение.<br>
                                Если значение равно 0 или пустое значение - слайдер не будет отображаться, за исключением случая когда выбран тип анимации "Без анимации". 
                                При сочетании двух выше приведенных настроек, слайды будут отображаться той высоты, сколько занимает контент слайда.
                                Это позволит Вам разместить слайдер (к примеру, со списками), где высота каждого из слайдов будет меняться в зависимости от контента.';
$string['config_proportionalheight'] = 'Пропорциональная высота';
$string['config_proportionalheight_help'] = 'При уменьшении размеров экрана слайдер может уменьшаться пропорционально или с сохранением высоты в неизменном состоянии';
$string['config_slidetype'] = 'Тип анимации';
$string['slidetype_simple'] = 'Без анимации';
$string['slidetype_fadein'] = 'fade-in (появление)';
$string['slidetype_slide'] = 'slide (вылет)';
$string['slidetype_slideoverlay'] = 'slide-overlay (вылет с перекрытием)';
$string['slidetype_triple'] = 'Три изображения на слайде в ряд';
$string['config_parallax'] = 'Включить параллакс-эффект';
$string['config_parallax_help'] = 'Эффект, при котором во время прокручивания страницы часть изображения, видимая в слайдере, тоже прокручивается.';
$string['config_slidescroll'] = 'Переключать слайды при скролле';
$string['config_slidescroll_help'] = '<div>Слайды будут переключаться по мере прокручивания страницы пока слайдер виден пользователю.</div><div>Порядок слайдов установлен в соответствии с ожидаемым моментом их появления (сначала пользователь видит первый слайд внизу страницы, затем последний наверху страницы).</div><div>Рекомендуется использовать малое количество слайдов (два-три) и сочетание с типом анимации "появление" (fade-in).</div>';
$string['config_slidespeed'] = 'Интервал переключения слайдов (в секундах)';
$string['config_navigation'] = 'Отображение стрелок для пролистывания';
$string['config_navigationpoints'] = 'Отображение точек для выбора слайда';
$string['config_zoomview'] = 'Просмотр изображений слайдера в модальном окне';
$string['config_themeprofile'] = 'Отображать только в выбранном профиле темы';
$string['themeprofile_all'] = 'Любой профиль';
$string['config_blockreplace'] = 'Разместить слайдер в плейсхолдере';
$string['config_slidemanagerlink_label'] = 'Управление слайдами';
$string['config_slidemanagerlink_emptyslides'] = 'Не добавлено ни одного слайда! Пожалуйста, перейдите к управлению слайдами для добавления.';
$string['config_arrowtype'] = 'Стиль стрелок';
$string['arrowtype_thick'] = 'Толстые';
$string['arrowtype_thin'] = 'Тонкие';



$string['use_placeholder'] = '<div>Для отображения слайдера вставьте в произвольное место следующий код: </div><div>&lt;div id="sliderplaceholder{$a}"&gt;&lt;/div&gt;</div><div>Обязательным условием отображения слайдера в указанном плейсхолдере является наличие данного блока на странице.</div>';
$string['need_config'] = 'Для отображения слайдера требуется выполнить настройки';

// ПОЛЬЗОВАТЕЛЬСКИЕ СТРОКИ
$string['title'] = 'Слайдер';
$string['go_back'] = 'Вернуться назад';
$string['slidemanager_page_title'] = 'Управление слайдами';

$string['slide_image_name'] = 'Изображение';
$string['slide_image_descripton'] = 'Слайд с изображением, отформатированном под размер слайда';
$string['slide_image_formsave_image_label'] = 'Изображение слайда';
$string['slide_image_formsave_backgroundpositiontop_label'] = 'Позиция изображения по вертикали в процентах';
$string['slide_image_formsave_parallax_label'] = 'Коэффициент смещения изображения при скролле (параллакс-эффект, поддерживаются значения от -100 до 100)';
$string['slide_image_formsave_title_label'] = 'Заголовок';
$string['slide_image_formsave_description_label'] = 'Описание';
$string['slide_image_formsave_summary_label'] = 'Резюме';
$string['slide_image_formsave_captiontop_label'] = 'Отступ текстовой области сверху';
$string['slide_image_formsave_captionright_label'] = 'Отступ текстовой области справа';
$string['slide_image_formsave_captionbottom_label'] = 'Отступ текстовой области снизу';
$string['slide_image_formsave_captionleft_label'] = 'Отступ текстовой области слева';
$string['slide_image_formsave_captionalign_label'] = 'Выравнивание текстовой области';
$string['slide_image_formsave_captionalign_left'] = 'Слева';
$string['slide_image_formsave_captionalign_right'] = 'Справа';
$string['slide_image_formsave_title_error_maxlen'] = 'Превышена максимальная длина заголовка';
$string['slide_image_formsave_summary_error_maxlen'] = 'Превышена максимальная длина резюме';
$string['slide_image_formsave_cpationalign_error_value'] = 'Указано не верное значение';
$string['slide_image_formsave_backgroundpositiontop_error_range'] = 'Необходимо указать значение от 0 до 100';
$string['slide_image_formsave_parallax_error_range'] = 'Необходимо указать значение от -100 до 100';
$string['slide_image_delete_error_options'] = 'Ошибка удаления данных слайда';
$string['slide_image_formsave_backgroundpositiontop_error_range'] = 'Значение указано неверно';

$string['slide_html_name'] = 'HTML-код';
$string['slide_html_htmlcode'] = 'Слайд с HTML-кодом';
$string['slide_html_formsave_htmlcode_label'] = 'Описание';
$string['slide_html_delete_error_options'] = 'Ошибка удаления данных слайда';

$string['slide_listitems_name'] = 'Список';
$string['slide_listitems_description'] = '';
$string['slide_listitems_delete_error_options'] = 'Ошибка удаления данных слайда';
$string['slide_listitems_formsave_title_label'] = 'Заголовок слайда';
$string['slide_listitems_formsave_background_label'] = 'Фон';
$string['slide_listitems_formsave_items_label'] = 'Список (по одному элементу на строку)';
$string['slide_listitems_formsave_rendermode_label'] = 'Способ отображения';
$string['slide_listitems_formsave_rendermode_checkboxes'] = 'Список с галочками';
$string['slide_listitems_formsave_rendermode_blocks_by_grid'] = 'Блоки по сетке';

$string['filtering'] = 'Настройки фильтрации';
$string['groupon'] = 'Поле профиля';
$string['g_none'] = 'Выбрать...';
$string['groupon_help'] = 'Указанное поле профиля может использоваться для фильтрации пользователей.';
$string['filter'] = 'Должно совпадать с';
$string['filter_help'] = 'Указанное в этом поле значение будет использоваться для фильтрации пользователей (пользователи, у которых в поле профиля заполнено значение отличное от указанного, не будут добавлены в слайдер)';
$string['softmatch'] = 'Использовать нестрогое соответствие';
$string['softmatch_help'] = 'Настройка включает более мягкое сравнение при фильтрации: позволено частичное совпадение, не учитывается регистр';
$string['auth'] = 'Метод авторизации';
$string['lang'] = 'Язык';


$string['error_slider_slide_action_error_notvalid'] = 'Неизвестная задача';
$string['error_slider_slide_type_notvalid'] = 'Неизвестный тип слайда';
$string['error_slider_slide_create_error'] = 'Ошибка создания слайда';
$string['error_slider_slide_delete_error_notfound'] = 'Слайд не найден';
$string['error_slider_slide_delete_error_delete'] = 'Ошибка удаления слайда';
$string['error_slider_slide_orderup_error_notfound'] = 'Слайд не найден';
$string['error_slider_slide_orderup_error_swap'] = 'Ошибка пермещения слайда';
$string['error_slider_slide_orderdown_error_notfound'] = 'Слайд не найден';
$string['error_slider_slide_orderdown_error_swap'] = 'Ошибка пермещения слайда';


$string['slidemanager_formsave_slide_orderup_label'] = 'Переместить вверх';
$string['slidemanager_formsave_slide_orderdown_label'] = 'Переместить вниз';
$string['slidemanager_formsave_slide_delete_label'] = 'Удалить';
$string['slidemanager_formsave_confirm_label'] = 'Применить';
$string['slidemanager_formsave_createslide_header_label'] = 'Добавить новый слайд';
$string['slidemanager_formsave_createslide_select_select'] = 'Выберите тип слайда';
$string['slidemanager_formsave_createslide_select_label'] = 'Добавить новый слайд';
$string['slidemanager_formsave_createslide_submit_label'] = 'Добавить';
$string['slidemanager_formsave_createslide_select_error_empty'] = 'Не выбран тип слайда';
$string['slidemanager_formsave_createslide_select_error_notvalid'] = 'Указан неизвестный тип слайда';
