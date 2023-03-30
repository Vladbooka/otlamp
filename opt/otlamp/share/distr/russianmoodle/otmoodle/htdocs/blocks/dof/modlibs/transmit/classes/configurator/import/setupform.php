<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//
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
 * Обмен данных с внешними источниками. Класс выбора маски/источника импорта
 *
 * @package    modlib
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_configurator_import_setupform extends dof_modlib_transmit_configurator_setupform_base
{
    /**
     * Дополнительная инициализация формы
     *
     * @return void
     */
    protected function definition_inner()
    {
        // Создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Переопределение имени формы
        $mform->getElement('header_group')->getElements()[0]->setText(
            $this->dof->get_string('setupform_import_header', 'transmit', null, 'modlib')
        );
        
        // Переорпеделение имени поля выбора маски
        $mform->getElement('maskcode')->setLabel(
            $this->dof->get_string('setupform_import_select_mask_label', 'transmit', null, 'modlib')
        );
        
        // Переорпеделение имени поля выбора источника
        $mform->getElement('sourcecode')->setLabel(
            $this->dof->get_string('setupform_import_select_source_label', 'transmit', null, 'modlib')
        );
    }
}