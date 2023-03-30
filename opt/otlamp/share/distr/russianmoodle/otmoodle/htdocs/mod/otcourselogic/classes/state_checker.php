<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Модуль Логика курса. Контроллер состояний модулей.
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otcourselogic;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use context_course;
use context_module;

/**
 * Класс контроллера состояний элементов курса
 * 
 * Механизм определяет состояние элемента курса(Активен/Не активен) 
 * для пользователей системы
 */
class state_checker 
{
    /**
     * Буфер проверенных пользовательских состояний и их значения
     * 
     * Формат [INSTANCEID][USERID] = 1|0|null
     *      1 - Элемент INSTANCEID активен для пользователя USERID
     *      0 - Элемент INSTANCEID не активен для пользователя USERID
     *      null - Cостояние не известно
     *      
     * @var array
     */
    private $instancechecked = [];
    
    /**
     * Получение пользовательского состояния элемента курса с учетом отсрочки активации
     *
     * @param int $instance - ID элемента курса
     * @param int $userid - ID пользователя
     *
     * @return null|bool - True, если элемент активен
     *                     False, если элемент не активен
     *                     NULL, если состояние не определено
     */
    public function get_state($instanceid, $userid)
    {
        global $DB;
    
        // Попытка получить данные из буфера
        if ( isset($this->instancechecked[(int)$instanceid][(int)$userid]) )
        {// Состояние определено
            return $this->instancechecked[(int)$instanceid][(int)$userid];
        }
    
        return $this->get_state_nocache($instanceid, $userid);
    }

    /**
     * Получение строки с пользовательским состояния элемента курса с учетом отсрочки активации
     *
     * @param int $instance - ID элемента курса
     * @param int $userid - ID пользователя
     *
     * @return string - состояние
     */
    public function get_state_string($instanceid, $userid)
    {
        global $DB;
        
        $statestring = get_string('shortuserstate_notset', 'mod_otcourselogic');
        
        // Получение элемнта курса
        $instance = $DB->get_record('otcourselogic', ['id' => $instanceid]);
        if ( ! empty($instance) && !empty($userid) )
        {
            $stateinfo = $this->get_state_info($instanceid, $userid);
            if ( ! empty($stateinfo) && is_object($stateinfo) )
            {
                if ( (bool)$stateinfo->status )
                {
                    $statestring = get_string('shortuserstate_active', 'mod_otcourselogic');
                } 
//                 elseif ( (bool)$stateinfo->preactive == true )
//                 {
//                     $statestring = get_string(
//                         'shortuserstate_preactive', 
//                         'mod_otcourselogic', 
//                         userdate($stateinfo->preactivelastchange + $instance->activatingdelay)
//                     );
//                 } 
                else
                {
                    $statestring = get_string('shortuserstate_notactive', 'mod_otcourselogic');
                }
            }
        }
        return $statestring;
    }
    
    /**
     * Получение данных о состоянии элемента курса
     *
     * @param int $instance - ID элемента курса
     * @param int $userid - ID пользователя. Если не указан - 
     *  будет возвращен полный список состояний по всем имеющимся пользователям
     *
     * @return null|stdClass|array - Данные по состоянию элемента курса
     */
    public function get_state_info($instanceid, $userid = null)
    {
        global $DB;
    
        if ( $userid )
        {// Требуется получить состояние для конкретного пользователя
            $state = $DB->get_record(
                    'otcourselogic_state', 
                    ['instanceid' => (int)$instanceid, 'userid' => (int)$userid], '*', IGNORE_MULTIPLE
                    );
        } else 
        {// Получить данные по всем имеющимся пользователям
            $state = $DB->get_records(
                'otcourselogic_state',
                ['instanceid' => (int)$instanceid]
            );
        }
    
        return $state;
    }
    
    /**
     * Получение пользовательского состояния элемента курса с учетом отсрочки активации
     *
     * @param int $instance - ID элемента курса
     * @param int $userid - ID пользователя
     *
     * @return null|bool - True, если элемент активен
     *                     False, если элемент не активен
     *                     NULL, если состояние не определено
     */
    public static function get_state_nocache($instanceid, $userid)
    {
        global $DB;
    
        // Попытка получить данные из БД
        $state = $DB->get_record(
            'otcourselogic_state',
            ['instanceid' => (int)$instanceid, 'userid' => (int)$userid],
            'id, status',
            IGNORE_MULTIPLE
        );
    
        if ( ! empty($state) )
        {// Состояние элемента для целевого пользователя получено
            return (bool)$state->status;
        }
    
        return null;
    }
    
    /**
     * Проверка состояния элемента курса для пользователя
     * 
     * Производит проверку состояния элемента курса для пользователя и сохраняет ее
     * 
     * @param int|object $instance - Элемент курса или INSTANCEID
     * @param int $userid - ID пользователя
     * @param bool $savestate - Требуется сохранение состояния
     * 
     * @return bool|null - Пользовательское состояние
     */
    public function check_cm_user($instance, $userid, $savestate = true)
    {
        global $DB;
        
        // Пользовательское состояние не определено
        $state = null;
        
        $check = (array)$instance;
        if ( ! isset($check['id']) )
        {// Получение элемента курса
            $instance = $DB->get_record('otcourselogic', ['id' => $instance]);
            if ( $instance )
            {// Элемент курса получен
                $instance = (array)$instance;
            } else
            {// Указанный элемент курса не найден
                return $state;
            }
        }
        $instance = (array)$instance;
        
        // Определение базовых значений
        $userid = (int)$userid;
        $instanceid = (int)$instance['id'];
        
        if ( isset($this->instancechecked[$instanceid][$userid]) )
        {// Проверка состояния для пользователя уже происходила
            return $this->instancechecked[$instanceid][$userid];
        }
        
        // Получение данных о доступности модуля
        get_fast_modinfo($instance['course'], $userid, true);
        $modinfo = get_fast_modinfo($instance['course'], $userid);
        $instances = $modinfo->get_instances_of('otcourselogic');
        if ( ! isset($instances[$instanceid]) )
        {// Элемент курса не найден, невозможно определить пользовательское состояние
            return $state;
        }
        
        // Определение состояния пользователя
        $available = (bool)$instances[$instanceid]->available;
        $state = (int)$available;
        $coursecontext = context_course::instance($instance['course']);
        
        if ( $state === null )
        {// Пользовательское состояние не определено
            return $state;
        }
        
        // Получить состояние до сохранения
        $oldstate = $this->get_state($instanceid, $userid);
        if (!is_enrolled($coursecontext, $userid, 'mod/otcourselogic:is_student', true))
        {//пользователь не имеет активной подписки с нужным правом
            // возвращаем текущее состояние
            return $oldstate;
        }
        
        if ( $savestate )
        {// Требуется сохранить состояние для пользователя
            // Сохранение состояния
            $id = $this->save_state($instanceid, $userid, $state, ['lastcheck' => time()]);
            // Получить состояние после сохранения
            $newstate = $this->get_state($instanceid, $userid);

            $state = $DB->get_record(
                    'otcourselogic_state',
                    ['instanceid' => (int)$instanceid, 'userid' => (int)$userid],
                    '*',
                    IGNORE_MULTIPLE
                    );
            
            // Добавление значения в буфер
            $this->instancechecked[$instanceid][$userid] = $newstate;
            
            if ( $oldstate !== $newstate )
            {// Состояние было изменено
                
                // Формирование события об изменении состояния
                $context = context_module::instance($instances[$instanceid]->id);
                $eventdata = [
                    'courseid' => $instance['course'],
                    'context' => $context,
                    'relateduserid' => $userid,
                    'objectid' => $id,
                    'other' => [
                        'oldstate' => $oldstate,
                        'state' => $newstate,
                        'instance' => $instance,
                        'userstateobj' => (array)$state
                    ]
                ];
                $event = \mod_otcourselogic\event\state_switched::create($eventdata);
                $event->trigger();
            }
            
        }
        
        return $state;
    }
    
    /**
     * Проверка состояния элемента курса для всех подписанных студентов
     *
     * @param int $instance - ID элемента курса
     * 
     * @return bool - Результат работы
     */
    public function check_cm($instanceid)
    {
        global $DB;
        
        // Получение элемнта курса
        $instance = $DB->get_record('otcourselogic', ['id' => $instanceid]);
        if ( ! $instance )
        {// Элемент курса не получен
            return false;
        }
        
        // Получение студентов по курсу
        $coursecontext = context_course::instance($instance->course);
        if ( ! $coursecontext )
        {// Контекст не определен
            return false;
        }
        
        // Получение подписанных пользователей
        $users = (array)get_enrolled_users($coursecontext, '', 0, 'u.*', null, 0, 0, true);
        foreach ( $users as $user )
        {
            // Проверка пользователя
            $this->check_cm_user($instance, $user->id);
        }
    }
    
    /**
     * Очистка состояния модуля курса для пользователя
     *
     * @param int $instance - ID элемента курса
     * @param int $userid - ID пользователя
     * 
     * @return bool - Результат работы
     */
    public function remove_cm_user($instanceid, $userid)
    {
        global $DB;
        
        // Удаление данных о состоянии элемента курса для пользователя
        return (bool)$DB->delete_records(
            'otcourselogic_state', 
            ['instanceid' => (int)$instanceid, 'userid' => (int)$userid]
        );
    }
    
    /**
     * Полная очистка состояния модуля курса
     *
     * @param int $instance - ID элемента курса
     *
     * @return bool - Результат работы
     */
    public function remove_cm($instanceid)
    {
        global $DB;
        
        // Удаление данных о состоянии элемента курса
        return (bool)$DB->delete_records(
            'otcourselogic_state', 
            ['instanceid' => (int)$instanceid]
        );
    }
    
    /**
     * Инициализация экземпляра модуля курса
     *
     * @param int $instance - ID элемента курса
     *
     * @return bool - Результат работы
     */
    public function init_cm($instanceid)
    {
        // Определение состояний для всех подписанных на курс экземпляра пользователей
        return (bool)$this->check_cm($instanceid);
    }
    
    /**
     * Сохранение пользовательского состояния элемента курса
     *
     * @param int $instance - ID элемента курса
     * @param int $userid - ID пользователя
     * @param int $state - Состояние элемента для указанного пользователя
     * @param array $opts - Дополнительные опции обработки
     *
     * @return int|bool - ID сохраненной записи или false в случае ошибки при сохранении
     */
    public function save_state($instanceid, $userid, $state, $opts = [])
    {
        global $DB;
        
        $instance = $DB->get_record('otcourselogic', ['id' => $instanceid]);

        if( ! empty($instance) )
        {
            // текущее состояние доступности элемента
            $state = (bool)$state;
            // текущее время
            $curtime = time();
            // Последнее сохраненное состояние логики курса 
            $currentstate = $DB->get_record('otcourselogic_state',
                ['instanceid' => (int)$instanceid, 'userid' => (int)$userid],
                '*',
                IGNORE_MULTIPLE
            );
//             if( ! empty($currentstate) )
//             {
//                 $changetime = (int)$currentstate->changetime;
//                 $status = (bool) $currentstate->status;
                
//             } else
//             {
//                 $changetime = time();
//                 $status = false;
//             }
            
            // Данные для сохранения
            $data = new stdClass();
            $data->instanceid = (int)$instanceid;
            $data->userid = (int)$userid;
            $data->status = (int)$state;
            if ( ! empty($currentstate) )
            {
                if ( (bool)$currentstate->status != (bool)$state )
                {
                    $data->changetime = time();
                }
            } else 
            {
                $data->changetime = time();
            }
            
//             if( $preactivestate != $state )
//             {// Состояние изменилось
//                 // Сохраниение времени изменения состояния
//                 $data->preactivelastchange = $curtime;
//                 $preactivelastchange = (int)$data->preactivelastchange;
//             }
            
//             // Время, когда должна произойти активация с учетом ее отсрочки
//             $timetoactivate = $preactivelastchange + (int)$instance->activatingdelay;
//             if ( ( $state == false || $curtime >= $timetoactivate ) && $activestate != $state )
//             {// либо выполняется деактивация (сразу), либо период отсрочки активации уже прошёл
//                 // обновляем данные состояния
//                 $data->active = (int)$state;
//                 $data->activelastchange = $curtime;
//             }
            
            if ( isset($opts['lastcheck']) )
            {
                $data->lastcheck = (int)$opts['lastcheck'];
            }
            
            if ( $currentstate )
            {// Обновление состояния
                $data->id = $currentstate->id;
                if ( $DB->update_record('otcourselogic_state', $data) )
                {
                    otcourselogic_update_grades($instance);
                    return $data->id;
                }
            } else
            {// Добавление состояния
                if ( $id = $DB->insert_record('otcourselogic_state', $data) )
                {
                    otcourselogic_update_grades($instance);
                    return $id;
                }
            }
        }
        return false;
    }
}