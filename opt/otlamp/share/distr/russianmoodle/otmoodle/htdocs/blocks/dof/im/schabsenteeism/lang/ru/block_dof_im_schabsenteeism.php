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
 * Интерфейс управления причинами отсутствия. Языковые переменные.
 *
 * @package    storage
 * @subpackage schabsenteeism
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Общие
$string['title'] = 'Причины отсутствия на занятии';
$string['page_main_name'] = 'Причины отсутствия на занятии';
$string['create_link'] = 'Создать';

$string['table_list_num'] = '№';
$string['table_list_actions'] = '';
$string['table_list_name'] = 'Название';
$string['table_list_type'] = 'Тип';
$string['table_list_owner'] = 'Владелец';
$string['table_list_status'] = 'Статус';
$string['table_list_actions_edit'] = 'Изменить';
$string['table_list_actions_delete'] = 'Удалить';

$string['form_save_header'] = 'Сохранение причины отсутствия';
$string['form_save_name_label'] = 'Название';
$string['form_save_type_label'] = 'Тип';
$string['form_save_cancel_label'] = 'Отменить';
$string['form_save_submit_label'] = 'Сохранить';

$string['form_save_type_error_notvalid'] = 'Неизвестный тип причины';
$string['form_save_name_error_empty'] = 'Не указано название причины';
$string['form_save_error_save'] = 'Ошибка сохранения причины';

$string['error_interface_save_access_create_denied'] = 'Доступ запрещен';
$string['error_interface_save_access_edit_denied'] = 'Доступ запрещен';
$string['interface_delete_header'] = 'Удаление причины отсутствия \"{$a->name}\"';
$string['confirmation_delete_schabsenteeism'] = 'Вы действительно хотите удалить причину отсутствия \"{$a->name}\"';
$string['error_interface_delete_notfound'] = 'Указаная причина не найдена';
$string['error_interface_delete_access_delete_denied'] = 'Доступ запрещен';
?>