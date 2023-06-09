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
/*
 * Файл, заменяющий плагин storage - "типы учебных компонент"
 */
$values = array(
    1 => $this->dof->get_string('comp_federal',    'refbook', null, 'modlib'),
    2 => $this->dof->get_string('comp_regional',   'refbook', null, 'modlib'),
    3 => $this->dof->get_string('comp_learnhouse', 'refbook', null, 'modlib'),
    4 => $this->dof->get_string('comp_department', 'refbook', null, 'modlib'));
?>