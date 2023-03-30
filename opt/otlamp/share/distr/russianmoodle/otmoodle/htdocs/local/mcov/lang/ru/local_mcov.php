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
 * Настраиваемые поля. Языковые строки.
 *
 * @package    local_mcov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Настраиваемые поля для объектов';

$string['mcov:edit_cohorts_cov'] = 'Редактировать значения настраиваемых полей глобальных групп';
$string['mcov:edit_users_cov'] = 'Редактировать значения настраиваемых полей пользователей';
$string['mcov:edit_users_cov_my'] = 'Редактировать значения настраиваемых полей своего профиля';
$string['mcov:edit_groups_cov'] = 'Редактировать значения настраиваемых полей групп';

$string['back_to_entity'] = '{$a}';
$string['edit_abstract_entity_title'] = 'Редактирование настраиваемых полей';
$string['entity_title'] = '{$a->entity} "{$a->object}"';
$string['edit_entity_title'] = '{$a->entity_title}. {$a->edit_abstract_entity_title}';
$string['fld_submit'] = 'Сохранить';

$string['entity_cohort_plural'] = 'Глобальные группы';
$string['entity_cohort'] = 'Глобальная группа';
$string['entity_user_plural'] = 'Пользователи';
$string['entity_user'] = 'Пользователь';
$string['entity_group_plural'] = 'Группы';
$string['entity_group'] = 'Группа';

$string['settings_general'] = 'Настраиваемые поля';
$string['settings_title_general'] = 'Настройки';
$string['settings_title_general_desc'] = '';
$formdescription = '<div>Конфигурационный массив должен быть описан в формате yaml</div>
<div>На нулевом уровне конфигурационного массива должен быть ключ class, значением которого описывается форма редактирования настраиваемых полей.</div>
<div>Эта форма должна состоять из массива, описываюшего поля формы.</div>
<div>Каждое поле в ключе должно иметь уникальный код поля, а в значении - массив свойств, описывающих поле формы.</div>
<div>Зарезервированные, именованные свойства:</div>
<ul>
<li>type - тип элемента формы</li>
<li>filter - установка типа данных для элемента</li>
<li>default - значение, которое должно по умолчанию подставиться в элемент формы</li>
<li>repeatgroup - на текущий момент не реализовано для этого инструмента</li>
<li>rules - на текущий момент не реализовано для этого инструмента</li>
<li>disabledif - на текущий момент не реализовано для этого инструмента</li>
<li>autoindex - на текущий момент не реализовано для этого инструмента</li>
<li>expanded - на текущий момент не реализовано для этого инструмента</li>
<li>advanced - на текущий момент не реализовано для этого инструмента</li>
<li>helpbutton - на текущий момент не реализовано для этого инструмента</li>
</ul>
<div>Остальные свойства будут переданы в конструктор элемента формы в том порядке, в котором они объявлены в конфигурации.</div>
<div>На текущий момент, для этого инструмента доступны следующие типы полей:</div>
<ul>
<li>text - однострочное текстовое поле</li>
<li>textarea - многострочное текстовое поле</li>
<li>select - выпадающий список</li>
<li>checkbox - флажок</li>
<li>date_selector - дата</li>
<li>submit - кнопка для отправки формы. </li>
</ul>';
$string['settings_cohort_yaml'] = 'Глобальные группы. Конфигурация настраиваемых полей';
$string['settings_cohort_yaml_desc'] = $formdescription.'
<div>Пример:</div>
<PRE>
class:
   description:
      type: textarea
      label: Описание глобальной группы
   syncable:
      type: checkbox
      label: Синхронизируемая
   extid:
      type: text
      filter: int
      label: Внешний идентификатор
   undergraduate_directions:
      type: select
      label: Направление подготовки
      options:
         09.03.03: Прикладная информатика
         38.03.01: Экономика
         38.03.02: Менеджмент
   enddate:
      type: date_selector
      label: Срок завершения обучения группы
   submit:
      type: submit
      label: Сохранить
</PRE>';

$string['settings_group_yaml'] = 'Группы. Конфигурация настраиваемых полей';
$string['settings_group_yaml_desc'] = $formdescription;

$string['settings_user_yaml'] = 'Пользователи. Конфигурация настраиваемых полей';
$string['settings_user_yaml_desc'] = $formdescription;
$string['e_user_fld_local_otcontrolpanel_viewsconfig'] = 'Конфигурация панели управления СЭО 3KL';

$string['exception_entity_config_empty'] = 'Конфигурация не найдена';
$string['exception_entity_form_not_set'] = 'Форма не установлена';
$string['exception_entity_form_misconfigured'] = 'Форма сконфигурирована не верно';
$string['error_mcov_form'] = 'Во время работы произошла ошибка<br/>Debug info:<br/>Error code: {$a->errorcode}<br/>Error message: {$a->errormessage}<br/>Stack trace:<br/>{$a->trace}';
$string['error_mcov_has_no_fields_to_edit'] = 'Нет полей, доступных вам для редактирования';

$string['e_group_fld_local_mcov_group_datestart'] = 'Дата начала обучения группы';
