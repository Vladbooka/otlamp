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
 * Обмен данных с внешними источниками. Класс конфигуратора (импорт)
 *
 * @package    modlib
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_configurator_import extends dof_modlib_transmit_configurator_base
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
        
        // Получение итератора с данными для импорта
        $iterator = $this->source->get_dataiterator();
        if ( empty($iterator) )
        {// Данные для импорта не найдены
            throw new dof_modlib_transmit_exception('empty_data', 'modlib_transmit');
        }
        
        // Получение полей импорта
        $fields = $this->source->get_datafields_list();
        if ( empty($fields) )
        {// Данные для импорта не найдены
            throw new dof_modlib_transmit_exception('empty_data', 'modlib_transmit');
        }
        
        $departmentid = optional_param('departmentid', 0, PARAM_INT);
        $simulationlimit = (int)$this->dof->storage('config')->get_config_value('simulation_limit', 'im', 'transmit', $departmentid);
        $count = 0;
        // Запуск импорта для каждого элемента данных
        foreach ( $iterator as $item )
        {
            $count++;
            if ($this->get_simulation_status() && $simulationlimit > 0 && $count > $simulationlimit) {
                break;
            }
            if ( ! empty($item) )
            {
                // передача набора данных для импорта в маску
                // получение результирующего пулла
                $finalpool = $this->mask->transmit_import($fields, $item);
                
                // поиск идентификатора основной синхронизированной записи
                if ( array_key_exists('__main_sync_downid_processed', $finalpool) )
                {
                    $this->source->record_processed($item, $finalpool['__main_sync_downid_processed']);
                }
            }
        }
    }
}