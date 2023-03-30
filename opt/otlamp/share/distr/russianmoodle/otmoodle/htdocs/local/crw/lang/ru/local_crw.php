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
 * Витрина курсов. Языковые переменные.
 *
 * @package local
 * @subpackage crw
 * @licensehttp://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Базовые языковые переменные
$string['pluginname'] = 'Витрина курсов';
$string['local_crw'] = 'Главная страница';
$string['title'] = 'Витрина курсов';
$string['about_course'] = 'О курсе';
$string['courses_showcase'] = 'Витрина курсов';
$string['rub'] = 'Р';

$string['eventcoursepageviewed'] = 'Страница описания курса просмотрена';

// Настройки плагина
$string['settings_title_category_block'] = 'Настройки блока категорий';
$string['settings_title_category_block_desc'] = '';
$string['settings_category_block_type'] = 'Тип блока категорий';
$string['settings_category_block_type_tiles'] = 'Плитки';
$string['settings_category_block_type_icons'] = 'Иконки';
$string['settings_title_category_block_type_icons'] = 'Настройки блока категорий - Иконки';
$string['settings_title_category_block_type_icons_desc'] = '';
$string['settings_category_block_type_iconfile'] = 'Файл иконки для плиток курсов';
$string['settings_category_block_type_iconfile_desc'] = '';
$string['settings_title_courses_block'] = 'Настройки блока курсов';
$string['settings_title_courses_block_desc'] = '';
$string['settings_courses_block_type_tiles'] = 'Плитки';
$string['settings_courses_block_type_catlist'] = 'Список';
$string['settings_courses_block_type'] = 'Тип блока курсов';
$string['settings_courses_catanchor'] = 'Якоря вместо ссылок';
$string['settings_title_courses_block_catlist'] = 'Настройки блока курсов - Список';
$string['settings_title_courses_block_catlist_desc'] = '';
$string['settings_courses_block_catlist_iconfile'] = 'Файл иконки для курсов в списке';
$string['settings_courses_block_catlist_iconfile_desc'] = '';
$string['settings_courses_block_catlist_totopdisplay'] = 'Отобразить кнопку Наверх';
$string['settings_courses_block_catlist_totopdisplay_desc'] = '';
$string['settings_courses_block_catlist_totopiconfile'] = 'Файл для кнопки Наверх';
$string['settings_courses_block_catlist_totopiconfile_desc'] = '';
$string['settings_categories_list'] = 'Блок категорий';
$string['settings_courses_list'] = 'Блок курсов';
$string['settings_plugins_empty'] = 'Нет';
$string['settings_slots_cs_header'] = 'Шапка витрины';
$string['settings_slots_cs_header_desc'] = 'Блок, который будет отображаться вверху страницы Витрины';
$string['settings_slots_cs_top'] = 'Верх витрины';
$string['settings_slots_cs_top_desc'] = 'Блок, который будет отображаться вверху страницы Витрины';
$string['settings_slots_cs_bottom'] = 'Низ витрины';
$string['settings_slots_cs_bottom_desc'] = 'Блок, который будет отображаться внизу страницы Витрины';
$string['settings_display_paging'] = 'Вывод пейджинга';
$string['settings_display_paging_desc'] = 'Пейджинг не будет отображаться одновременно с включенной загрузкой курсов по ajax';
$string['settings_display_paging_nowhere'] = 'Не отображать';
$string['settings_display_paging_top'] = 'Сверху';
$string['settings_display_paging_bottom'] = 'Снизу';
$string['settings_display_paging_topbottom'] = 'И сверху, и снизу';
$string['settings_display_statistics'] = 'Вывод статистики отображенных курсов';
$string['settings_display_statistics_desc'] = '';
$string['settings_display_statistics_nowhere'] = 'Не отображать';
$string['settings_display_statistics_top'] = 'Сверху';
$string['settings_display_statistics_bottom'] = 'Снизу';
$string['settings_display_statistics_topbottom'] = 'И сверху, и снизу';
$string['settings_courses_pagelimit'] = 'Курсов на странице';
$string['settings_courses_pagelimit_desc'] = '';
$string['settings_display_pagelimit_change_tool'] = 'Вывод формы изменения количества курсов, отображаемых на странице';
$string['settings_display_pagelimit_change_tool_desc'] = '';
$string['settings_display_pagelimit_change_tool_nowhere'] = 'Не отображать';
$string['settings_display_pagelimit_change_tool_top'] = 'Сверху';
$string['settings_display_pagelimit_change_tool_bottom'] = 'Снизу';
$string['settings_display_pagelimit_change_tool_topbottom'] = 'И сверху, и снизу';
$string['settings_ajax_courses_flow'] = 'Загрузка курсов по ajax';
$string['settings_ajax_courses_flow_desc'] = 'Если настройка включена, пейджинг перестает отображаться';
$string['settings_ajax_courses_flow_autoload'] = 'Автоматически загружать курсы при достижении конца ленты';
$string['settings_ajax_courses_flow_autoload_desc'] = '';
$string['settings_display_invested_courses'] = 'Отображать курсы вложенных категорий';
$string['settings_display_invested_courses_desc'] = 'Если включено, система будет отображать курсы не только текущей категориии, но и всех подкатегорий';
$string['settings_main_catid'] = 'Базовая категория Витрины курсов';
$string['settings_main_catid_desc'] = 'Если указана, Витрина по умолчанию строится начиная от указанной категории';
$string['settings_main_catid_not_set'] = 'Не указано';
$string['display_not_nested_title'] = 'Отображать категории, не являющиеся потомками базовой';
$string['display_not_nested_desc'] = 'Если указана базовая категория, то по умолчанию в витрине будут отображаться только категории, вложенные в неё. Если базовая категория указывается для использования в качестве дефолтной, но при этом должны быть доступны все категории, то необходимо предоставить к ним доступ с помощью этой настройки.';
$string['settings_custom_course_fields_title'] = 'Настраиваемые поля курса';
$string['settings_custom_course_fields_desc'] = "В это поле необходимо вносить поля формы в yaml-разметке. Пример:<br/>
class:<br/>
&nbsp;&nbsp;&nbsp;description:<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type: 'textarea'<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;label: 'описание модуля, отображаемое рецензентам'<br/>
&nbsp;&nbsp;&nbsp;speclevel:<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type: 'select'<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;repeatgroup: 'specialities'<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;label: 'Уровень образования'<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;options: [высшее, среднее]<br/>
&nbsp;&nbsp;&nbsp;specname:<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type: 'text'<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;repeatgroup: 'specialities'<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;label: 'Название специальности'<br/>
&nbsp;&nbsp;&nbsp;submit:<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type: 'submit'<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;label: 'Сохранить'<br/>";
$string['settings_custom_fields_view_title'] = 'Отображать настраиваемые поля курса';
$string['settings_custom_fields_view_desc'] = 'Настройка отвечает за отображение списка настраиваемых полей курса и их значений на странице описания курса. Настройка может быть переопределена на уровне курса.';
$string['settings_coursepage_template'] = 'Шаблон оформления страницы описания курса';
$string['settings_coursepage_template_desc'] = 'Шаблон может быть переопределен на уровне категории курсов или в самом курсе';

$string['course_in_line'] = 'Плиток курсов в одной строке';
$string['courses_pagelimit'] = 'Категорий на странице';
$string['courses_catlimit'] = 'Категорий в одной строке';
$string['courses_showcategory'] = 'Отображать категорию у курса';
$string['courses_showcategory_courseconfig'] = 'Настройка курса';

$string['courses_sort_type_course_sort'] = 'Согласно сортировке курсов';
$string['courses_sort_type_course_created'] = 'По дате создания курса';
$string['courses_sort_type_course_start'] = 'По дате начала курса';
$string['courses_sort_type_learninghistory_enrolments'] = 'По количеству подписок на курс за всю историю';
$string['courses_sort_type_active_enrolments'] = 'По количеству действующих подписок на курс';
$string['courses_sort_type_course_popularity'] = 'По популярности курса';
$string['courses_sort_type_course_name'] = 'По названию курса';
$string['settings_course_sort_type'] = 'Вариант сортировки по умолчанию';
$string['settings_course_sort_type_desc'] = '';
$string['settings_course_sort_types'] = 'Доступные варианты сортировки';
$string['settings_course_sort_types_desc'] = '';
$string['settings_course_sort_direction'] = 'Направление сортировки';
$string['settings_course_sort_direction_desc'] = '';

$string['courses_sort_direction_asceding'] = 'По возрастанию';
$string['courses_sort_direction_desceding'] = 'По убыванию';

$string['settings_general'] = 'Общие';
$string['settings_title_general'] = 'Общие настройки';
$string['settings_title_general_desc'] = '';
$string['settings_title_general_categories_list'] = 'Шаблон блока категорий';
$string['settings_title_general_categories_list_desc'] = 'Шаблон вида для блока списка категорий';
$string['settings_title_general_courses_list'] = 'Шаблон блока курсов';
$string['settings_title_general_courses_list_desc'] = 'Шаблон вида для блока списка курсов';

$string['settings_subplugintype_crw'] = 'Блоки витрины курсов';

$string['settings_course_info_view_title'] = 'Отображение страницы описания курса';
$string['settings_course_info_view_desc'] = 'С помощью данной настройки можно скрыть страницу дополнительного описания курса, показывать только неподписанным на курс пользователям или отображать страницу для всех пользователей';
$string['settings_hide_course_info_page'] = 'Скрыта от пользователей';
$string['settings_redirect_all_enrolled_users'] = 'Должна быть показана только неподписанным на курс пользователям';
$string['settings_show_course_info_page_for_all_users'] = 'Должна быть показана всем пользователям, входящим в курс';
$string['course_info_view'] = 'Отображение страницы описания курса';

$string['settings_hide_course_contacts_title'] = 'Скрыть контакты курса на странице описания курса';
$string['settings_hide_course_contacts_desc'] = 'Настройка позволяет скрыть/показать блок с контактами курса на странице описания курса';

$string['settings_hide_course_gallery_title'] = 'Скрыть галерею курса на странице описания курса';
$string['settings_hide_course_gallery_desc'] = 'Настройка позволяет скрыть/показать блок галереи курса на странице описания курса';
$string['settings_course_popularity_type'] = 'Как считать популярность курса';
$string['settings_course_popularity_type_desc'] = '';
$string['popularity_unique_course_view'] = 'По уникальным просмотрам курса за месяц';
$string['settings_override_navigation'] = 'Переопределить стандартную навигацию';
$string['settings_override_navigation_desc'] = '
<div>С этой настройкой стандартная навигация будет изменена с целью перенаправления пользователей на альтернативные интерфейсы, реализованные в витрине курсов.</div>
<div>В хлебных крошках ссылки на стандартные категории курсов будут вести на страницы категорий курсов в витрине, а ссылка на страницу "Курсы" будет вести на главную страницу витрины курсов.</div>
<div>Из категорий курсов будет осуществляться автоматическое перенаправление на страницы категорий курсов в витрине</div>';
$string['settings_remove_courses_nav_node'] = 'Исключить узел Курсы (Мои курсы) из хлебных крошек';
$string['settings_remove_courses_nav_node_desc'] = '';

// Настройки страницы курса
$string['coursesettings'] = 'Настройки страницы описания курса';
$string['coursepage_template'] = 'Шаблон оформления страницы описания курса';
$string['coursepage_template_help'] = 'Шаблон может наследоваться из категории курсов, где в свою очередь наследоваться от базовых настроек витрины курсов';
$string['coursepage_template_inherit'] = 'Наследовать';
$string['coursepage_template_code_base'] = 'Стандартный';
$string['additional_categories'] = 'Дополнительные категории';
$string['additional_categories_help'] = 'Если в витрине курсов перейти к категории, указанной в данной настройке, то в открывшемся списке курсов будет доступен текущий курс, даже если в его настройках указана другая категория';
$string['required_knowledge'] = 'Необходимые навыки';
$string['required_knowledge_help'] = 'Знания, которыми необходимо обладать для прохождения этого курса. Перечисляются через запятую. Пример: Бухгалтерия, Право ';
$string['hide_course'] = 'Не показывать курс в витрине';
$string['hide_course_help'] = 'Если установлено, данный курс будет отображаться только для администраторов';
$string['custom_fields_view'] = 'Отображение настраиваемых полей курса';
$string['custom_fields_view_help'] = 'В данной настройке вы можете выбрать отображать ли настраиваемые поля курса и каким способом';
$string['custom_fields_view_default'] = 'Наследовать из глобальной настройки витрины';
$string['custom_fields_view_hide'] = 'Не отображать';
$string['custom_fields_view_show'] = 'Отображать';
$string['coursecat_view'] = 'Отображение категории курса';
$string['coursecat_view_help'] = 'Определяет каким образом должна отображаться/не отображаться категория курса на странице описания';
$string['coursecat_view_hide'] = 'Не отображать';
$string['coursecat_view_text'] = 'В виде текста';
$string['coursecat_view_link'] = 'В виде ссылки';
$string['display_coursetags'] = 'Отобразить теги курса';
$string['display_coursetags_help'] = 'Настройки страницы описания курса';
$string['additional_coursesettings'] = 'Настройки дополнительных полей курса';
$string['additional_coursecustomsettings'] = 'Редактирование настраиваемых полей курса';
$string['course_price'] = 'Стоимость курса';
$string['course_price_help'] = 'Данные из этого поля будут отображены на обложке курса и странице описания курса';
$string['additional_description'] = 'Краткое описание';
$string['additional_description_help'] = 'В дополнение к полному описанию (добавляется в настройках курса), можно добавить краткое описание курса.
Краткий текст может отображаться в витрине и (или) на странице описания курса, в зависимости от настройки ниже.';
$string['additional_description_view'] = 'Отображать краткое описание';
$string['additional_description_view_help'] = 'Где отображать краткое описание';
$string['nowhere'] = 'Нигде';
$string['everywhere'] = 'Везде';
$string['coursedesc'] = 'Только на странице описания курса';
$string['courselink'] = 'Только в витрине (если поддерживается)';
$string['course_imgs'] = 'Настройки отображения изображений и файлов курса';
$string['descriptionimgs'] = 'Изображения и файлы для страницы описания курса';
$string['showcaseimgs'] = 'Изображение для обложки курса в витрине';

$string['sticker'] = 'Наклейка на курс';
$string['sticker_help'] = 'Добавляет выбранную наклейку на плитку курса';
$string['sticker_special_offer'] = 'Скидка';
$string['sticker_action_offer'] = 'Акция';
$string['sticker_free_offer'] = 'Бесплатно';
$string['sticker_demo'] = 'Демо-курс';
$string['sticker_card_payment'] = 'Оплата картой';
$string['sticker_new'] = 'Новинка';
$string['sticker_bestseller'] = 'Бестселлер';
$string['sticker_beginner'] = 'Новичку';

$string['course_difficult'] = 'Уровень сложности';
$string['course_difficult_none'] = '';
$string['course_difficult_easy'] = 'легкий';
$string['course_difficult_medium'] = 'средний';
$string['course_difficult_hard'] = 'тяжелый';

$string['display_startdate'] = 'Где отображать дату начала курса';
$string['display_startdate_help'] = 'Где отображать дату начала курса';

$string['display_enrolicons'] = 'Где отображать иконки подписок';
$string['display_enrolicons_help'] = 'Где отображать иконки подписок';

$string['display_price'] = 'Где отображать цену';
$string['display_price_help'] = 'Где отображать цену';

$string['hide_course_info_page'] = 'Скрыта от пользователей';
$string['redirect_all_enrolled_users'] = 'Должна быть показана только неподписанным на курс пользователям';
$string['show_course_info_page_for_all_users'] = 'Должна быть показана всем пользователям, входящим в курс';
$string['show_course_info_page_default'] = 'Использовать глобальную настройку';

$string['hide_course_contacts'] = 'Скрыть контакты курса на странице описания курса';
$string['hide_course_contacts_help'] = 'Настройка позволяет скрыть/показать блок с контактами курса на странице описания курса';
$string['hide_course_gallery'] = 'Скрыть галерею курса на странице описания курса';
$string['hide_course_gallery_help'] = 'Настройка позволяет скрыть/показать блок галереи курса на странице описания курса';
$string['hide_course_contacts_default'] = 'Использовать глобальную настройку';

// Страница категории
$string['categorysettings'] = 'Дополнительные настройки категории курсов';
$string['category_icon'] = 'Изображение категории';
$string['category_icon_help'] = '';
$string['hide_category'] = 'Скрыть категорию';
$string['hide_category_help'] = 'Скрыть категорию со всеми принадлежащими ей курсами в Витрине курсов. Скрытая категория будет видна только администраторам';
$string['category_coursepage_template'] = 'Шаблон оформления страницы описания курса';
$string['category_coursepage_template_help'] = 'Шаблон может наследоваться из базовых настроек витрины курсов, а также переопределяться в самом курсе';
$string['category_courselist_template'] = 'Шаблон оформления списка курсов';
$string['category_courselist_template_help'] = 'Данная настройка отображается только при использовании плагина "Универсальный список курсов" и позволяет определить шаблон отображения списка курсов при нахождении в редактируемой категории. По умолчанию используется шаблон, определенный в самом плагине';
$string['courselist_template_inherit'] = 'Наследовать';
$string['category_custom_fields_roles'] = 'Состав и область видимости настраиваемых полей';
$string['category_custom_fields_roles_help'] = '
<div>Настройки распространяют своё влияние на форму редактирования кастомных полей курса и форму поиска.</div>
<div>Опосредованно они влияют и на формирование данных для отображения через шаблоны, но исключительно для сохранения логики (если поле нельзя отредактировать, значит оно не заполнено - нет смысла отображать)</div>
<div>Тем не менее, цели регулировать отображение у этой настройки нет, отображение регулируется mustache-шаблонами. По умолчанию отключенные поля отображаться на странице описания курса не будут, но через шаблоны это возможно исправить.</div>';
$string['category_custom_field_role_inherit'] = 'Наследовать';
$string['category_custom_field_role_field_disabled'] = 'Отключить поле целиком';
$string['category_custom_field_role_search_disabled'] = 'Исключить из поисковой формы';
$string['category_custom_field_role_search_disabled_sort_enabled'] = 'Исключить из поисковой формы, но позволить сортировать по нему';
$string['category_custom_field_role_search_enabled'] = 'Включить в поисковую форму';
$string['category_custom_field_role_search_enabled_sort_enabled'] = 'Включить в поисковую форму и позволить сортировать по нему';
$string['category_custom_field_role'] = 'Область видимости настраиваемого поля';
$string['category_custom_field_role_help'] = '
<div>"Наследовать" - всё наследуется из настроек плагина: поле всегда доступно для редактирования и отображения; будет ли поле отображаться в форме поиска, определяется в плагине поиска</div>
<div>"Отключить поле целиком" - поле отключено для категории, так оно не будет отображаться на форме редактирования, не будет отображаться в форме поиска (не редактировалось - не заполнено - нет смысла искать), не будет по умолчанию отображаться в интерфейсах</div>
<div>"Исключить из поисковой формы" - не зависимо от того, что настроено в плагине, находясь в текущей категории пользователь увидит форму поиска без фильтра по этому полю</div>
<div>"Включить в поисковую форму" - не зависимо от того, что настроено в плагине, находясь в текущей категории пользователь увидит форму поиска с фильтром по этому полю</div>';

// Страница Витрины курсов
$string['showcase_course_startdate'] = 'Начало: ';
$string['showcase_course_categories_title'] = 'Категории';
$string['top_showcase_course_categories_title'] = 'Категории';
$string['showcase_course_courses_title'] = '{$a->name}';
$string['top_showcase_course_courses_title'] = 'Список курсов';
$string['no_courses_in_selected_category'] = 'В выбранной категории нет курсов';
$string['no_courses_was_find'] = 'Не найдено ни одного курса, соответствующего выбранным параметрам поиска';
$string['search_results_subheader'] = ' : результаты поиска';
$string['showcase_course_courses_table_courseshortname'] = 'Название в каталоге';
$string['showcase_course_courses_table_coursefullname'] = 'Название курса';
$string['showcase_course_courses_table_coursedifficulty'] = 'Уровень сложности';
$string['totop'] = 'Вверх';
$string['top_paging_description'] = '{$a->perpage} из {$a->totalcount} курсов';

// Страница курса
$string['courseblock_course_startdate'] = 'Дата начала: ';
$string['courseblock_course_enddate'] = 'Дата окончания: ';
$string['courseblock_course_price'] = 'Стоимость: ';
$string['courseblock_course_rknowledge'] = 'Необходимые навыки: ';
$string['courseblock_course_contacts'] = 'Контакты: ';
$string['enrol_block'] = 'Записаться на курс';
$string['login'] = 'Войти';
$string['coursefiles'] = 'Прикрепленные файлы';
$string['link_viewguestcourse'] = 'Войти гостем в курс';
$string['link_viewcourse'] = 'Войти в курс';
$string['link_login_text'] = 'Для подписки на курс необходимо';
$string['link_login'] = 'Войти';
$string['link_login_moodle'] = 'Авторизоваться';
$string['link_signup_moodle'] = 'Зарегистрироваться';
$string['course_info'] = 'О курсе';
$string['message_cant_view_course'] = 'У вас нет возможности подписаться на этот курс';

// AJAX
$string['ajax_courseshortname'] = 'Название в каталоге: ';
$string['ajax_coursedifficult'] = 'Уровень сложности: ';

// Формы
$string['searchform_name'] = 'Поиск по названию курса';
$string['searchform_more'] = 'Расширенный поиск';
$string['searchform_dategroup'] = 'Искать по дате начала';
$string['searchform_priceprice'] = 'Искать по стоимости';
$string['searchform_search'] = 'Найти';
$string['searchform_sum'] = 'Сумма';

// Tools
$string['add_category'] = 'Добавить категорию';
$string['add_course'] = 'Добавить курс';


$string['crw:addinstance'] = 'Добавлять Витрина курсов';
$string['crw:view_hidden_categories'] = 'Видеть скрытые категории';
$string['crw:view_hidden_courses'] = 'Видеть скрытые курсы';
$string['crw:manage_additional_categories'] = 'Управлять дополнительными категориями';

$string['courses_flow_show_more'] = 'Больше курсов';
$string['courses_flow_loading'] = 'Идет загрузка...';

$string['perpager_title'] = 'Отображать по:';
$string['perpager_all'] = 'Все';

// Страница поиска
$string['search_result'] = 'Результаты поиска';

// Общие язковые строки
$string['yes'] = 'Да';
$string['no'] = 'Нет';

$string['tags'] = 'Теги курса';
$string['tagarea_crw_course_custom1'] = 'Область для настраиваемой коллекции витрины курсов 1';
$string['tagcollection_custom1'] = 'Настраиваемая коллекция витрины курсов 1';
$string['tagarea_crw_course_custom2'] = 'Область для настраиваемой коллекции витрины курсов 2';
$string['tagcollection_custom2'] = 'Настраиваемая коллекция витрины курсов 2';

$string['feedback_items_header'] = 'Отзывы';
$string['feedback_course_unknown'] = 'курс не найден';
$string['feedback_item_unknown'] = 'неизвестный объект';
$string['feedback_area_course'] = 'Курс';
$string['feedback_area_unknown'] = 'Неизвестная область отзыва';

// Задачи
$string['task_calculation_course_popularity_title'] = 'Расчет популярности курса';


