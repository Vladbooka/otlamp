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
 * Обмен данных с внешними источниками. Базовый импортер договора 
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_processor_importer_eagreements_base extends dof_modlib_transmit_processor_base
{
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_required = [
        'personid' => null,
        'departmentid' => null
    ];
    
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_input = [];
    
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
        // получение актуальных статусов договоров
        $statuses = array_keys($dof->workflow('eagreements')->get_meta_list('actual'));
        
        // подразделения для селекта
        $deps = [];
        
        // поиск родительских достижений
        $departments = $dof->storage('departments')->get_departmentstrace($input['departmentid']);
        foreach ( $departments as $department )
        {
            $deps[] = $department->id;            
        }
        
        // проверка существования договора с сотрудником и создание при отсутствии
        $eagreements = $dof->storage('eagreements')->get_records(
                [
                    'personid' => $input['personid'],
                    'departmentid' => $deps,
                    'status' => $statuses
                ], 'adddate DESC');
        if ( ! empty($eagreements) )
        {
            // договор найден
            $eagreement = array_shift($eagreements);
            if ( $eagreement->status == 'plan' )
            {
                // Перевод статуса в активный
                $dof->workflow('eagreements')->change($eagreement->id, 'active');
            }
            
            // договор уже существует, вернем его идентификатор
            return ['id' => $eagreement->id];
        } else
        {
            // договор отсутствует, создание нового
            $contractnew = new stdClass();
            $contractnew->departmentid = $input['departmentid'];
            $contractnew->notice = '';
            $contractnew->date = time();
            $contractnew->personid = $input['personid'];
            
            $eagreementid = $dof->storage('eagreements')->insert($contractnew);
            if ( ! empty($eagreementid) )
            {
                // перевод статуса в активный
                $dof->workflow('eagreements')->change($eagreementid, 'active');
                
                // запись в лог
                $logger->addlog(
                        null,
                        'insert',
                        'eagreements',
                        $eagreementid,
                        'success',
                        (array)$dof->storage('eagreements')->get_record(['id' => $eagreementid])
                        );
                
                // договор создан
                // бросим в пулл идентификатор
                return ['id' => $eagreementid];
            } else
            {
                $logger->addlog(
                        null,
                        'insert',
                        'eagreements',
                        $eagreementid,
                        'error',
                        []
                        );
            }
        }

        // не заладилось с договором
        return [];
    }
}
