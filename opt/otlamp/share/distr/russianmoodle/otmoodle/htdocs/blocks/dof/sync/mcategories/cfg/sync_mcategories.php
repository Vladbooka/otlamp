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
 * Конфигурационный файл плагина sync_mcategories
 *
 * @package    block
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$sync_mcategories_cfg = [];

// если настроена синхронизация подразделений с категориями moodle,
// пытаться ли автоматически совершить привязку, сопоставляя название категории с названием подразделения
$sync_mcategories_cfg['autolink_depcat_by_name'] = false;

// если настроена автоматическая привязка категорий к подразделениям по названию,
// допустимо ли искать категорию по всей структуре категорий moodle или ограничиваться только категорией, привязанной к родительскому подразделению
$sync_mcategories_cfg['autolink_depcat_any_parent'] = false;

// если настроена автоматическая привязка категорий к подразделениям по названию и найдено несколько подходящих вариантов,
// 0 - не привязывать
// 1 - создать еще одну
// 2 - привязать к любой
$sync_mcategories_cfg['autolink_depcat_double'] = 0;
