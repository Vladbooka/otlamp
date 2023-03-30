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
 * Обмен данных с внешними источниками. Базовый импортер должностного назначения
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_processor_importer_appointments_base extends dof_modlib_transmit_processor_base
{
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_required = [
        'personid' => null,
    ];
    
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_input = [
        'schpositionid' => null,
        'eagreementid' => null,
        'positionid' => null,
        'departmentid' => null,
        'managerid' => null,
        'sync_downid' => null
    ];
    
    /**
     * Исходящие данные
     *
     * @var array
     */
    public static $slots_output = [
        'id' => null,
        'sync_downid' => null,
        'force_generate_schposition' => null
    ];
    
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
        // проверка, необходимо ли закрывать существующее должностное назначение
        $pappointment = null;
        $pappointmentcanceled = false;
        
        if (empty($input['sync_downid']) &&
            empty($input['positionid']) &&
            empty($input['eagreementid']) &&
            empty($input['departmentid']))
        {
            $logger->addlog(null, null, 'appointments', null, 'error', [],
                $dof->get_string('importer_appointments_required_fields', 'transmit', null, 'modlib'));
        } else
        {
        
            if ( ! empty($input['sync_downid']) )
            {
                $pappointment = $dof->storage('appointments')->get_record(['id' => $input['sync_downid'], 'status' => array_keys($dof->workflow('appointments')->get_meta_list('real'))]);
                
                if ( ! empty($pappointment) )
                {
                    $pschposition = $dof->storage('schpositions')->get_record(['id' => $pappointment->schpositionid]);
                    
                    if ( ! empty($pschposition) )
                    {
                        if (empty($input['positionid']) ||
                            empty($input['eagreementid']) ||
                            empty($input['departmentid']) ||
                            ($pschposition->positionid != $input['positionid']) ||
                            ($pappointment->eagreementid != $input['eagreementid']) ||
                            ($pappointment->departmentid != $input['departmentid']))
                        {
                            // изменился договор/вакансия/должность
                            // закрываем должностное назначение
                            // закрываем вакансию
                            $dof->workflow('appointments')->change($pappointment->id, 'canceled');
                            $dof->workflow('schpositions')->change($pschposition->id, 'canceled');
                            $pappointment = null;
                            $pappointmentcanceled = true;
                        }
                    }
                } else {
                    $logger->addlog(null, null, 'appointments', null, 'undefined', [],
                        $dof->get_string('importer_appointments_appointment_not_found', 'transmit', null, 'modlib'));
                }
                
                if ( ! empty($pappointment) )
                {
                    $newappointmentrec = new stdClass();
                    $newappointmentrec->id = $pappointment->id;
                    if ( $pappointment->managerid != $input['managerid'] )
                    {
                        $newappointmentrec->managerid = $input['managerid'];
                    }
                    if ( count((array)$newappointmentrec) > 1 )
                    {
                        if ( $dof->storage('appointments')->update($newappointmentrec) )
                        {
                            // запись в лог
                            $logger->addlog(null, 'update', 'appointments', $newappointmentrec->id, 'success',
                                (array)$newappointmentrec);
                        } else
                        {
                            // запись в лог
                            $logger->addlog(null, 'update', 'appointments', $newappointmentrec->id, 'error',
                                (array)$newappointmentrec);
                        }
                    }
                    return [
                        'id' => $newappointmentrec->id,
                        'sync_downid' => $newappointmentrec->id,
                    ];
                    
                } else {
                    $logger->addlog(null, null, 'appointments', null, 'undefined', [],
                        $dof->get_string('importer_appointments_appointment_canceled', 'transmit', null, 'modlib'));
                }
            }
            
            if (! empty($input['positionid']) &&
                ! empty($input['eagreementid']) &&
                ! empty($input['departmentid']))
            {
                // получение актуальных статусов договоров
                $statuseseagreements = array_keys($dof->workflow('eagreements')->get_meta_list('actual'));
                
                // получение актуальных статусов вакансий
                $statusesschposition = array_keys($dof->workflow('schpositions')->get_meta_list('actual'));
                
                // получение актуальных статусов вакансий
                $statusesappointments = array_keys($dof->workflow('appointments')->get_meta_list('real'));
                
                // подразделения для селекта
                $deps = [];
                
                // поиск родительских достижений
                $departments = $dof->storage('departments')->get_departmentstrace($input['departmentid']);
                foreach ( $departments as $department )
                {
                    $deps[] = $department->id;
                }
                
                // получение всех договоров сотрудника
                $eagreements = $dof->storage('eagreements')->get_records(['personid' => $input['personid'], 'departmentid' => $deps, 'status' => $statuseseagreements], 'adddate DESC');
                
                // должностных назначений
                $schpositions = $dof->storage('schpositions')->get_records(['positionid' => $input['positionid'], 'status' => $statusesschposition]);
                if ( ! empty($schpositions) )
                {
                    foreach ( $schpositions as $schposition )
                    {
                        // проверка существования должностного назначения
                        foreach ( $eagreements as $eagr )
                        {
                            $apps = $dof->storage('appointments')->get_records([
                                'eagreementid' => $eagr->id,
                                'schpositionid' => $schposition->id,
                                'status' => $statusesappointments,
                                'departmentid' => $deps
                            ]);
                            if ( ! empty($apps) )
                            {
                                $fid = array_pop($apps)->id;
                                
                                // должностное назначение существует, вернем идентификатор
                                return [
                                    'id' => $fid,
                                    'sync_downid' => $fid
                                ];
                            }
                        }
                    }
                }
                
                // проверка, что нет вакансии, генерируем
                if ( empty($input['schpositionid']) )
                {
                    // генерируем вакансию
                    return ['generate_schposition' => true];
                }
                
                // создание нового назначения на должность
                $appointment = new stdClass();
                $appointment->schpositionid = $input['schpositionid'];
                $appointment->enumber = $input['positionid'] . $input['schpositionid'] . $input['eagreementid'] . $input['departmentid'];
                $appointment->worktime = 40;
                $appointment->combination = 0;
                $appointment->date = $appointment->begindate = time();
                $appointment->departmentid = $input['departmentid'];
                $appointment->eagreementid = $input['eagreementid'];
                $appointment->managerid = ! empty($input['managerid']) ? $input['managerid'] : null;
                
                // добавление записи
                $id = $dof->storage('appointments')->insert($appointment);
                if ( ! empty($id) )
                {
                    // инициализация статуса
                    $dof->workflow('appointments')->init($id);
                    
                    // перевод статуса в активный
                    $dof->workflow('appointments')->change($id, 'active');
                    
                    // запись в лог
                    $logger->addlog(null, 'insert', 'appointments', $id, 'success', (array)$appointment);
                    
                    // договор создан
                    // бросим в пулл идентификатор
                    return [
                        'id' => $id,
                        'sync_downid' => $id
                    ];
                } else
                {
                    $logger->addlog(null, 'insert', 'appointments', $id, 'error', (array)$appointment);
                }
            } else {
                $logger->addlog(null, null, 'appointments', null, 'undefined', [],
                    $dof->get_string('importer_appointments_not_enough_data_to_create', 'transmit', null, 'modlib'));
            }
        }
        
        if ( $pappointmentcanceled )
        {
            // была отменено должностное назначение
            return ['sync_downid' => 0];
            
        } else
        {
            return [];
        }
    }
}
