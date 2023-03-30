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
 * Обмен данных с внешними источниками. Создание/Обновление персоны
 *
 * @package    sync
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_processor_importer_persons_base extends dof_modlib_transmit_processor_base
{
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_required = ['email' => null];
    
    /**
     * Обязательные входящие данные для запуска обработчика
     *
     * @var array
     */
    public static $slots_input = [
        'personid' => null,
        'firstname' => null,
        'lastname' => null,
        'middlename' => null,
        'dateofbirth' => null,
        'gender' => null,
        'phonecell' => null,
        'doublepersonfullname' => null,
        'simulation' => null,
        'departmentid' => null,
        'usersyncednow' => null,
        'changedep_mode' => null,
        'password' => null,
        'passwordformat' => null,
        'passwordmd5' => null,
        'extid' => null,
        'sync2moodle' => null,
        'username' => null,
    ];
    
    /**
     * Статичные данные
     *
     * @var array
     */
    public static $slots_static = [
        'onlyupdate' => false
    ];
    
    /**
     * Исходящие данные
     *
     * @var array
     */
    public static $slots_output = ['personid' => null];
    
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
        if ( ! empty($input['onlyupdate']) && empty($input['personid']) )
        {
            // включен режим обновления и не передан идентификатор персоны
            return [];
        }
        // объект персоны, если передан идентификатор
        $person = null;
        
        // Объект для сохранения персоны
        $save = new stdClass();
        $saveoptions = [];
        $save->email = $input['email'];
        if ( isset($input['personid']) )
        {
            $person = $dof->storage('persons')->get_record(['id' => $input['personid']]);
            $save->id = $input['personid'];
        }
        if ( isset($input['lastname']) )
        {
            $save->lastname = $input['lastname'];
        }
        if ( isset($input['firstname']) )
        {
            $save->firstname = $input['firstname'];
        }
        if ( isset($input['middlename']) )
        {
            $save->middlename = $input['middlename'];
        }
        if ( isset($input['gender']) )
        {
            $save->gender = $input['gender'];
        }
        if ( isset($input['dateofbirth']) )
        {
            $save->dateofbirth = $input['dateofbirth'];
        }
        if ( isset($input['phonecell']) )
        {
            $save->phonecell = $input['phonecell'];
        }
        if ( ! empty($input['changedep_mode']) )
        {
            if ( ! empty($input['departmentid']) )
            {
                // флаг, говорящий о том, что персона находится в подразделении
                // которое по иерархи находится выше, чем то, в которое необходимо переместить
                $inhigherdep = false;
                if ( ! empty($person) )
                {
                    // поиск родительских достижений
                    $departments = $dof->storage('departments')->get_departmentstrace($input['departmentid']);
                    array_pop($departments);
                    if ( ! empty($departments) )
                    {
                        foreach ( $departments as $department )
                        {
                            if ( $department->id == $person->departmentid )
                            {
                                // персона находится выше
                                $inhigherdep = true;
                                break;
                            }
                        }
                    }
                }
                if ( (empty($person) || ($person->departmentid != $input['departmentid'])) &&
                        (! $inhigherdep) || ! empty($input['usersyncednow']) )
                {
                    // переносить можно в подразделение ниже
                    // либо только что создана персона
                    $save->departmentid = $input['departmentid'];
                }
            }
        } else 
        {
            if ( ! empty($input['departmentid']) )
            {
                $save->departmentid = $input['departmentid'];
            }
        }
        $simulation = false;
        if ( isset($input['simulation']) )
        {
            $saveoptions['silent'] = (bool)$input['simulation'];
        }
        
        // Опция дублирования персоны по fullname
        if ( isset($input['doublepersonfullname']) && isset($save->firstname) && isset($save->lastname) && isset($save->middlename) )
        {
            if ( $input['doublepersonfullname'] == false )
            {// Запрет дублирования персоны по FULLNAME
                
                // Поиск аналогичной персоны 
                $isexists = $dof->storage('persons')->is_exists(
                    [
                        'firstname' => $save->firstname,
                        'lastname' => $save->lastname,
                        'middlename' => $save->middlename
                    ]
                );
                
                if ( $isexists )
                {// Персона с аналогичным fullname найдена
                    $logger->addlog(
                        null,
                        'insert',
                        'persons',
                        null,
                        'error',
                        (array)$save,
                        $dof->get_string('doubleperson_error', 'transmit', $save, 'modlib')
                    );
                    
                    return [];
                }
            }
        }
        
        if (isset($input['passwordformat']))
        {
            switch ($input['passwordformat'])
            {
                case 'clear':
                    if (isset($input['password']))
                    {
                        $saveoptions['saveoptions'] = ['newpassword' => $input['password']];
                    }
                    break;
                case 'md5':
                    if (isset($input['passwordmd5']))
                    {
                        $save->cov__transmit__passwordmd5 = $input['passwordmd5'];
                    }
                    break;
                default:
                    break;
            }
        }
        
        if (isset($input['username'])) {
            $save->cov__transmit__username = $input['username'];
        }
        
        
        if (isset($input['extid'])) {
            $save->cov__transmit__extid = $input['extid'];
        }
        
        if (isset($input['sync2moodle'])) {
            $save->sync2moodle = $input['sync2moodle'];
        }
        
        if ( (count((array)$save) == 2) && 
                property_exists($save, 'email') && 
                property_exists($save, 'id') &&
                ($person->email == $save->email) )
        {
            // если в полях только идентификатор и эл почта, при этом эл почта принадлежит пользователю
            // то сохранять нечего, пропустим
            return [];
        }
        
        // Сохранение данных персоны
        try 
        {
            $personid = $dof->storage('persons')->save($save, $saveoptions);
            if ( $personid === false )
            {// Ошибка сохранения персоны
                if ( empty($save->id) )
                {
                    // Ошибка сохранения персоны
                    $logger->addlog(
                        null,
                        'insert',
                        'persons',
                        null,
                        'error',
                        (array)$save,
                        $dof->get_string('not_enough_data', 'transmit', null, 'modlib')
                    );
                   
                } else
                {
                    // Ошибка сохранения персоны
                    $logger->addlog(
                        null,
                        'update',
                        'persons',
                        null,
                        'error',
                        (array)$save,
                        $dof->get_string('not_enough_data', 'transmit', null, 'modlib')
                    );
                }
                return [];
            }
            if ( empty($save->id) )
            {
                $logger->addlog(
                    null,
                    'insert',
                    'persons',
                    $personid,
                    'success',
                    (array)$save
                );
            } else
            {
                $logger->addlog(
                    null,
                    'update',
                    'persons',
                    $personid,
                    'success',
                    (array)$save
                );
            }
            return ['personid' => $personid];
        } catch ( dof_exception_dml $e )
        {
            if ( empty($save->id) )
            {
                // Ошибка сохранения персоны
                $logger->addlog(
                    null,
                    'insert',
                    'persons',
                    null,
                    'error',
                    (array)$save,
                    $dof->get_string($e->errorcode, 'persons', null, 'storage')
                );
            } else 
            {
                // Ошибка сохранения персоны
                $logger->addlog(
                    null,
                    'update',
                    'persons',
                    null,
                    'error',
                    (array)$save,
                    $dof->get_string($e->errorcode, 'persons', null, 'storage')
                );
               
            }
            return [];
        }
    }
}
