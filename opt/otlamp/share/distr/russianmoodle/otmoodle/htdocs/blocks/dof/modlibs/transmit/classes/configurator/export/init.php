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
 * Обмен данных с внешними источниками. Класс конфигуратора (экспорт)
 *
 * @package    modlib
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_configurator_export extends dof_modlib_transmit_configurator_base
{
    /**
     * Подготовка процесса обмена данных
     *
     * @return void
     */
    protected function transmit_process()
    {
        if ( ! $this->is_setup() )
        {// Конфигуратор не настроен
            throw new dof_modlib_transmit_exception('configutator_is_not_setupped', 'modlib_transmit');
        }
        
        // Подготовка процесса экспорта
        $this->source->export_start_process();
        // Запуск итеративного процесса экспорта данных
        do
        {
            // Запрос новых экспортных данных 
            $data = $this->mask->transmit_export();
            if ( empty($data) )
            {// Данные не получены
                
                // Завершение экспорта
                break;
            }
            
            // Получение полей экспорта
            $fields = array_keys($data);
            // Экспорт данных
            $this->source->export($fields, $data);
            
        } while ( true );
        
        // Завершение процесса экспорта
        $this->source->export_finish_process();
    }
}