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
 * Блок топ-10
 *
 * @package    block
 * @subpackage topten
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Топ-10';
$string['topten:addinstance'] = 'Добавлять новый блок Топ-10';
$string['topten:myaddinstance'] = 'Добавлять новый блок Топ-10';

// Настройки
$string['rating_type'] = 'Выберите рейтинг для отображения';
$string['rating_number'] = 'Выберите количество позиций';
$string['hide_rating_title'] = 'Скрывать заголовок блока';
$string['rating_name'] = 'Укажите наименование рейтинга или оставьте пустым для автоматического именования';

$string['update_cached_data'] = 'Обновление данных кешируемых отчетов';
$string['report_not_ready'] = 'Отчет не готов';
$string['report_header'] = 'Топ-{$a->number}. {$a->name}';

// Рейтинги
$string['users_coursecompletion'] = 'Пользователи с максимальным количеством пройденных курсов';
$string['users_coursecompletion_header'] = 'Пройденные курсы';

$string['courses_rating'] = 'Курсы с максимальным рейтингом, выставленным пользователями';
$string['courses_rating_header'] = 'Рейтинг курсов';

$string['users_xp'] = 'Пользователи с максимальным уровнем в блоке «Опыт!»';
$string['users_xp_header'] = 'Уровень опыта';

$string['users_activity'] = 'Пользователи с максимальной активностью в системе за последние сутки';
$string['users_activity_header'] = 'Активность пользователей за сутки';

$string['users_dof_achievements'] = 'Пользователи с максимальным рейтингом в выбранном разделе портфолио';
$string['users_dof_achievements_header'] = 'Рейтинг портфолио';

$string['user_selection'] = 'Пользователи согласно настройкам фильтрации';
$string['user_selection_header'] = 'Пользователи';

$string['course'] = 'Курс';
$string['fio'] = 'ФИО';
$string['rate'] = 'Номер';
$string['type_description'] = 'Информация';

// Рейтинг "Пользователи с максимальным количеством пройденных курсов"
$string['users_coursecompletion_number'] = 'Курсы';
$string['users_coursecompletion_accept_completions_from'] = 'Учитывать курсы завершенные с указанной даты (включительно)';
$string['users_coursecompletion_accept_completions_to'] = 'Учитывать курсы завершенные до указанной даты (включительно)';

// Рейтинг "Пользователи с максимальным количеством пройденных курсов"
$string['courses_rating_rating'] = 'Рейтинг';

// Курсы по заданным критериям
$string['courses_search_renderer'] = 'Вариант отображения курсов';
$string['courses_search'] = 'Список курсов по выбранным критериям';
$string['courses_search_header'] = 'Найденные курсы';
$string['courses_search_sorttype'] = 'Сортировка';
$string['courses_search_sortdirection'] = 'Направление сортировки';
$string['system_search_button'] = 'Фильтрация курсов';
$string['courses_search_filter_header'] = 'Настройки фильтрации курсов';
$string['courses_search_filter_save'] = 'Применить';
$string['courses_search_filter_cancel'] = 'Отмена';

// Пользователи с максимальным уровнем в блоке «Опыт!»
$string['users_xp_lvl'] = 'Уровень';
$string['users_xp_description'] = 'Выбранный рейтинг отображает пользователей с максимальным уровнем в блоке «Опыт!».
Обращаем внимание, что для отображения рейтинга необходимо выбрать использование баллов «Для всего сайта».';

// Пользователи с максимальной активностью в системе за последние сутки
$string['users_activity_counter'] = 'Активность';

// Пользователи с максимальным рейтингом в заданной категории портфолио
$string['users_dof_achievements_rating'] = 'Рейтинг';
$string['users_dof_achievements_selectcat'] = 'Выберите раздел достижений';
$string['users_dof_achievements_header_cat'] = 'Рейтинг портфолио по категории «{$a}»';


$string['exception_output_fragment_not_found'] = 'Не найден искомый фрагмент';
$string['exception_required_paramater_not_specified'] = 'Не указан обязательный параметр';

//Тип - объект
$string['user_img'] = 'Изображение пользователя';
$string['fullname'] = 'Ф.И.О.';
$string['slide_object_timelimit'] = 'Частота обновления';
$string['slide_object_name'] = 'объект';
$string['slide_object_descripton'] = 'Добавить объект предложенного типа';
$string['slide_object_formsave_selectobject_label'] = 'Тип объекта';
$string['object_user_base_name'] = 'Общая информация';
$string['object_user_universal_name'] = 'Универсальный';
$string['object_user_base_description'] = 'Шаблон отображает ФИО пользователя, изображение пользователя, и описание указанное в поле профиля description (описание).
В шаблоне не используются настраиваемые поля.
Максимально отображается по три карточки в ряд.';
$string['object_user_universal_description'] = 'Шаблон отображает ФИО пользователя, изображение пользователя.
Рассчитан на отображение двух настраиваемых полей, причем имя выбранного поля будет использоваться в качестве label и отображаться слева, а значение поля отобразится справа.
Максимально отображается по три карточки в ряд.';
$string['object_user_select_field'] = 'Поле №{$a}';
$string['object_user_text_field'] = 'Текстовое поле №{$a}';
$string['slide_object_formsave_selecttemplate_label'] = 'Выбор шаблона';
$string['slide_object_formsave_template_desc'] = 'Описание шаблона: {$a}';
$string['custom_template_fields'] = 'Настраиваемые поля шаблонов';
$string['custom_template_fields_desc'] = 'В этом разделе можно задать соответствие между полями пользователя и настраиваемыми полями выбранного отчета.
                                          Более подробно о том как будет отображаться указанная в полях  информация можно узнать из описания шаблонов, указанного выше.
                                          Колличество и состав отображаемых полей зависит от выбранного шаблона.';
$string['none_description'] = 'Описание шаблона отсутствует';
$string['none_template'] = 'Выбранный шаблон не существует';
$string['filtering'] = 'Настройки фильтрации';
$string['groupon'] = 'Поле профиля';
$string['g_none'] = 'Выбрать...';
$string['groupon_help'] = 'Указанное поле профиля может использоваться для фильтрации пользователей.';
$string['filter'] = 'Должно совпадать с';
$string['filter_help'] = 'Указанное в этом поле значение будет использоваться для фильтрации пользователей (пользователи, у которых в поле профиля заполнено значение отличное от указанного, не будут показаны)';
$string['softmatch'] = 'Использовать нестрогое соответствие';
$string['softmatch_help'] = 'Настройка включает более мягкое сравнение при фильтрации: позволено частичное совпадение, не учитывается регистр';
$string['auth'] = 'Метод авторизации';
$string['lang'] = 'Язык';
$string['config_condition_logic'] = 'Логика применения условий';
$string['config_condition_logic_and'] = 'Должны выполниться все условия';
$string['config_condition_logic_or'] = 'Должно быть выполнено любое из условий';
