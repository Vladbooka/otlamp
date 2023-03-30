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
 * Обмен данных с внешними источниками. Конвертер названия и адреса подразделения в идентификатор
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_processor_converter_department_name_to_id extends dof_modlib_transmit_processor_base
{
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_required = ['depname' => null];
    
    /**
     * Небязательные входящие данные
     *
     * @var array
     */
    public static $slots_input = [
        'country' => null,
        'region' => null,
        'city' => null,
        'managerid' => null,
        'personid' => null
    ];
    
    /**
     * Исходящие данные
     *
     * @var array
     */
    public static $slots_output = ['id' => null];
    
    /**
     * Запуск обработчика
     *
     * @param array $input - Входящие данные
     * @param dof_control $dof - Контроллер Электронного Деканата
     * @param dof_storage_logs_queuetype_base $logger - Очередь логов
     * @param dof_modlib_transmit_source_filemanager $filemanager - Менеджер файлов
     *
     * @return array - Исходящие данные
     */
    public static function execute($input, $dof, $logger, $filemanager)
    {
        // получение реальных статусов
        $statuses = array_keys($dof->workflow('departments')->get_meta_list('real'));
        
        // масив обработанных подразделений
        $departments = [];
        
        // получение списка подразделений
        static $pdepartments = null;
        if ( is_null($pdepartments) )
        {
            $pdepartments = $dof->storage('departments')->get_records(['status' => $statuses]);
        }
        if ( ! empty($input['depname']) )
        {
            foreach ( $pdepartments as $dep )
            {
                if ( strpos($dep->name, $input['depname']) === 0 )
                {
                    $departments[$dep->id] = $dep;
                }
            }
        }
        if ( count($departments) === 1 )
        {
            // найдено ровно одно подразделение
            // бросаем в пулл идентификатор подразделения
            return ['id' => key($departments)];
        }
        if ( count($departments) > 1 )
        {
            // нашли более одного подразделения
            if ( ! empty($input['personid']) )
            {
                // проверяем, если такое единственное подразделение, где персона является руководителем
                $predep = $dof->storage('departments')->get_records(['managerid' => $input['personid'] ,'status' => $statuses]);
                if ( count($predep) === 1 )
                {
                    // нашли ровно одно подразделение
                    // бросим в пулл
                    return ['id' => array_pop($predep)->id];
                }
            }
            
            
            
            // проверяем совпадение адреса
            // массив адресов подразделений
            $addresses = $processeddepartments = [];
            
            // удалим все подразделения, у которых отсутствуют адреса
            foreach ( $departments as $department )
            {
                if ( ! empty($department->addressid) )
                {
                    $address = $dof->storage('addresses')->get_record(['id' => $department->addressid]);
                    if ( ! empty($address) )
                    {
                        // положим в массив адресов адрес текущего подразделения
                        $addresses[$department->id] = $address;
                    }
                }
            }
            
            // определяем подразделениепо стране
            if ( ! empty($input['country']) )
            {
                $processeddepartments = [];
                
                // поиск совпадений по стране
                foreach ( $departments as $department )
                {
                    if ( empty($addresses[$department->id]->country) )
                    {
                        // у подразделения пустая страна, считаем как *
                        $processeddepartments[$department->id] = $department;
                    }
                    if ( $input['country'] == $addresses[$department->id]->country )
                    {
                        // совпадение по стране
                        $processeddepartments[$department->id] = $department;
                    }
                }
                
                if ( ! empty($processeddepartments) )
                {
                    if ( (count($processeddepartments) === 1) )
                    {
                        // нашли то самое подразделение
                        return ['id' => array_pop($processeddepartments)->id];
                    } else
                    {
                        $departments = $processeddepartments;
                    }
                } else
                {
                    return [];
                }
            }
            if ( ! empty($input['region']) )
            {
                $processeddepartments = [];
                
                foreach ( $departments as $department )
                {
                    if ( empty($addresses[$department->id]->region) )
                    {
                        // у подразделения пустой регион, считаем как *
                        $processeddepartments[$department->id] = $department;
                    }
                    if ( $input['region'] == $addresses[$department->id]->region )
                    {
                        // совпадение по региону
                        $processeddepartments[$department->id] = $department;
                    }
                }
                
                if ( ! empty($processeddepartments) )
                {
                    if ( (count($processeddepartments) === 1) )
                    {
                        // нашли то самое подразделение
                        return ['id' => array_pop($processeddepartments)->id];
                    } else
                    {
                        $departments = $processeddepartments;
                    }
                } else
                {
                    return [];
                }
            }
            
            if ( ! empty($input['city']) )
            {
                $processeddepartments = [];
                
                foreach ( $departments as $department )
                {
                    if ( empty($addresses[$department->id]->city) )
                    {
                        // у подразделения пустлй город, считаем как *
                        $processeddepartments[$department->id] = $department;
                    }
                    if ( $input['city'] == $addresses[$department->id]->city )
                    {
                        // совпадение по городу
                        $processeddepartments[$department->id] = $department;
                    }
                }
                
                if ( ! empty($processeddepartments) )
                {
                    if ( (count($processeddepartments) === 1) )
                    {
                        // нашли то самое подразделение
                        return ['id' => array_pop($processeddepartments)->id];
                    } else
                    {
                        $departments = $processeddepartments;
                    }
                } else
                {
                    return [];
                }
            }
            
            if ( ! empty($departments) )
            {
                $depids = [];
                foreach ( $departments as $dep )
                {
                    $depids[] = $dep->id;
                }
                
                // ищем подразделение, где непосредственный руководитель пользователя является руководителем подразделения
                $ppdepartments = $dof->storage('departments')->get_records(['id' => $depids, 'managerid' => $input['managerid'] ,'status' => $statuses]);
                if ( count($ppdepartments) === 1 )
                {
                    // нашли ровно одно подразделение
                    // бросим в пулл
                    return ['id' => array_pop($ppdepartments)->id];
                }
            }
        }
        
        if ( ! empty($input['returndefaultifnotfound']) )
        {
            // не нашли подразделение, веренм дефолтный, если передан флаг
            return ['id' => $dof->storage('departments')->get_default_id()];
        } else
        {
            // подразделение не найдено
            return [];
        }
    }
}
