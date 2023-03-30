<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
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
 * Менеджер учебного процесса. Урок.
 * 
 * @package    modlib
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_lesson
{
    /**
     * Объект контроллера Деканата для доступа к общим методам
     * 
     * @var dof_control
     */
    protected $dof = null;
    
    /**
     * Список слушателей на занятии
     * 
     * @var array
     */
    protected $listeners = [];
    
    /**
     * Объект события, прилинкованного к занятию
     * 
     * @var stdClass
     */
    protected $event = null;
    
    /**
     * Объект тематического плана, прилинкованного к занятию
     *
     * @var stdClass
     */
    protected $plan = null;

    /**
     * Дата начала занятия
     *
     * @var int
     */
    protected $startdate = null;
    
    /**
     * Порядковый номер занятия
     * 
     * @var int
     */
    protected $indexnum = null;
    
    /**
     * Идентификатор учебного процесса
     * 
     * @var int
     */
    protected $cstreamid = null;
    
    /**
     * Конструктор - определяет с каким учебным потоком будет вестись работа
     *
     * @param dof_control - глобальный объект Деканата $DOF
     */
    public function __construct(dof_control $dof, $plan = null, $event = null, $cstreamid = null)
    {
        // Базовые данные
        $this->dof  = $dof;
        $this->cstreamid = $cstreamid;
        if ( ! is_object($plan) && 
                is_numeric($plan) )
        {
            $this->plan = $this->dof->modlib('journal')
                ->get_manager('lessonprocess')
                ->get_plan($plan);
        } else 
        {
            $this->plan = $plan;
        }
        if ( ! is_object($event) &&
                is_numeric($event) )
        {
            $this->event = $this->dof->modlib('journal')
                ->get_manager('lessonprocess')
                ->get_schevent($event);
        } else 
        {
            $this->event = $event;
        }
        
        // Добавление даты проведения занятия
        if ( $this->event_exists() )
        {// Найдено связное событие
            $this->startdate = $this->event->date;
        } elseif ( $this->plan_exists() )
        {// Найдена контрольная точка
            $this->startdate = $this->dof->storage('plans')->get_startdate($this->plan);
        }

        if ( ! empty($this->cstreamid) )
        {
            // Полный список слушателей
            $this->listeners = $this->dof->storage('cpassed')->get_records(
                    ['cstreamid' => (int)$this->cstreamid]
                    );
        } elseif ( $this->plan_exists() )
        {
            // Полный список слушателей
            $this->listeners = $this->dof->storage('cpassed')->get_records(
                ['cstreamid' => (int)$this->dof->storage('plans')->get_cstreamid($this->plan)]
            );
        } elseif ( $this->event_exists() )
        {
            // Полный список слушателей
            $this->listeners = $this->dof->storage('cpassed')->get_records(
                    ['cstreamid' => (int)$this->get_event()->cstreamid]
                    );
        }
        
        // Дополнительная информация о слушателях
        foreach ( $this->listeners as &$listener )
        {
            $personid = $listener->studentid;
            $listener = ['cpassed' => $listener];
            $listener['person'] = $this->dof->storage('persons')->get($personid);
        }
    }
    
    /**
     * Возвращает идентификатор занятия
     *
     * @return string
     */
    public function get_identifier()
    {
        $eventid = '0';
        if ( $this->event_exists() )
        {
            $eventid = $this->event->id;
        }
        $planid = '0';
        if ( $this->plan_exists() )
        {
            $planid = $this->plan->id;
        }
        return $planid.'_'.$eventid;
    }
    
    /**
     * Возвращает название урока
     *
     * @return string|null
     */
    public function get_name()
    {
        if ( ! empty($this->plan->name) )
        {
            return (string)$this->plan->name;
        }
        return null;
    }
    
    /**
     * Возвращает домашнее задание по уроку
     *
     * @return string|null
     */
    public function get_homework()
    {
        $homework = '';
        if ( ! empty($this->plan->homework) )
        {
            $homework = (string)$this->plan->homework;
        }
        if ( trim($homework) == '')
        {
            return null;
        }
        return $homework;
    }
    
    /**
     * Возвращает факт наличия события
     *
     * @return int|null
     */
    public function get_startdate()
    {
        if ( ! empty($this->startdate) )
        {
            return (int)$this->startdate;
        }
        return null;
    }
    
    /**
     * Возвращает данные по работе подписки на уроке
     *
     * @return stdClass|null
     */
    public function get_listener_gradedata($cpassedid)
    {
        if ( isset($this->listeners[$cpassedid]) )
        {
            return $this->get_gradedata($this->listeners[$cpassedid]['cpassed']);
        }
        return null;
    }
    
    /**
     * Возвращает все данные подпискам
     *
     * @return array
     */
    public function get_listeners_data()
    {
        return $this->listeners;
    }
    
    
    /**
     * Возвращает локализованный статус события
     *
     * @return string|null
     */
    public function get_eventstatus_localized()
    {
        if ( $this->event_exists() )
        {
            return $this->dof->workflow('schevents')->get_name($this->event->status);
        }
        return null;
    }
    
    /**
     * Возвращает факт наличия контрольной точки
     * 
     * @return bool
     */
    public function plan_exists()
    {
        if ( ! empty($this->plan) )
        {
            return true;
        }
        return false;
    }
    
    /**
     * Возвращает контрольную точку занятия
     *
     * @return stdClass|null
     */
    public function get_plan()
    {
        if ( $this->plan_exists() )
        {
            return $this->plan;
        }
        return null;
    }
    
    /**
     * Получение грейд итема
     * 
     * @return ama_grade_item|null
     */
    public function get_mdl_gradeitem()
    {
        if ( $this->mdl_gradeitem_exists() )
        {
            return $this->dof->modlib('ama')->grade_item($this->get_plan()->mdlgradeitemid);
        }
        
        return null;
    }
    
    /**
     * Проверка существования грейд итема у КТ
     * 
     * @return bool
     */
    public function mdl_gradeitem_exists()
    {
        if ( ! $this->plan_exists() )
        {
            return false;
        }
        
        $mdlgradeitemid = $this->get_plan()->mdlgradeitemid;
        if ( empty($mdlgradeitemid) )
        {
            return false;
        }
        
        $mdlgradeitem = $this->dof->modlib('ama')->grade_item($mdlgradeitemid)->get();
        if ( empty($mdlgradeitem) )
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * Возвращает факт наличия события
     *
     * @return bool
     */
    public function event_exists()
    {
        if ( ! empty($this->event) )
        {
            return true;
        }
        return false;
    }
    
    /**
     * Возвращает событие занятия
     *
     * @return stdClass|null
     */
    public function get_event()
    {
        if ( $this->event_exists() )
        {
            return $this->event;
        }
        return null;
    }
    
    
    /**
     * Разрешено ли создавать компоненты урока
     * 
     * @param int|null $cstreamid - идентификатор учебного процесса
     * @param string|null $subform - проверка конкретного компонента формы 
     *                              (plan, event, null - хотя бы что-то из компонентов)
     * @param int|null $userid - идентификатор пользователя, для которого выполняется проверка
     * @param int|null $departmentid - идентификатор подразделения, в котором выполняется проверка
     * 
     * @return boolean
     */
    public function createform_allowed($cstreamid=null, $subform=null, $userid=null, $departmentid=null)
    {
        // полный доступ
        $fullaccess =   $this->dof->is_access('datamanage') 
                        OR $this->dof->is_access('admin') 
                        OR $this->dof->is_access('manage');
        
        // создание плана
        $createplanaccess = false;
        // создание события
        $createeventaccess = false;
        
        $stplans = $this->dof->storage('plans');
        $cstream = $this->dof->storage('cstreams')->get($cstreamid);
        if( $this->plan_exists() )
        {
            $plan = $this->get_plan();
            if( $plan->linktype != 'cstreams' )
            {
                return false;
            }
        } else
        {
            $createplanaccess = $stplans->is_access('create', $cstreamid, $userid, $departmentid)  
                                || $stplans->is_access('create/in_own_journal', $cstreamid, $userid, $departmentid);
        }
        
        if( $this->event_exists() )
        {
            $event = $this->get_event();
            if( $event->status == 'replaced' )
            {
                return false;
            }
            $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
            if ( $event->teacherid != $personid && ! $fullaccess )
            {
                $createplanaccess = false;
            } elseif( $cstream->teacherid != $event->teacherid && $stplans->is_access('create/own_event', $event->id, $userid, $departmentid) || $fullaccess )
            {
                $createplanaccess = true;
            }
        } else 
        {
            $stevents = $this->dof->storage('schevents');
            $createeventaccess = $stevents->is_access('create', $cstreamid, $userid, $departmentid)
                                 || $stevents->is_access('create/in_own_journal', $cstreamid, $userid, $departmentid);
        }
        
        switch($subform)
        {
            case 'plan':
                // разрешено ли создавать план
                return $createplanaccess;
                break;
            case 'event':
                // разрешено ли создавать событие
                return $createeventaccess;
                break;
            default:
                return $createplanaccess || $createeventaccess;
                break;
        }
    }

    /**
     * Разрешено ли редактировать компоненты урока
     *
     * @param string|null $subform - проверка конкретного компонента формы
     *                              plan - редактировать план или создавать тему
     *                              editexistplan - только редактировать имеющийся план
     *                              event - событие
     *                              replacetime - переносить урок по времени
     *                              replaceteacher - переносить урок на другого преподавателя
     *                              replace - переносить в принципе
     *                              cancel - отменять урок
     *                              completion - отмечать урок проведенным
     *                              null - хотя бы что-то из компонентов
     * @param int|null $userid - идентификатор пользователя, для которого выполняется проверка
     * @param int|null $departmentid - идентификатор подразделения, в котором выполняется проверка
     *
     * @return boolean
     */
    public function editform_allowed($subform=null, $userid=null, $departmentid=null)
    {
        // полный доступ
        $fullaccess =   $this->dof->is_access('datamanage') 
                        OR $this->dof->is_access('admin') 
                        OR $this->dof->is_access('manage');
        // редактирование плана
        $editplanaccess = false;
        // редактирование события
        $editeventaccess = false;
        // добавление темы (приравниваем к редактированию плана)
        $givethemeaccsess = false;
        // просматривать событие (права достаточно для отображения формы редактирования, 
        // ибо она на самом деле форма просмотра с возможностью редактирования
        // (дополнительно проверяется право редактирования в самой форме)
        $vieweventaccess = false;
        // замена времени занятия
        $replacetimeaccess = false;
        // замена учителя занятия
        $replaceteacheraccess = false;
        // отмена урока
        $cancelaccessevent = false;
        $cancelaccessplan = false;
        // выставление отметки о проведении урока
        $completionaccess = false;

        if( $this->event_exists() )
        {
            $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
            $event = $this->get_event();
            if( $event->status == 'replaced' )
            {
                return false;
            }

            $stevents = $this->dof->storage('schevents');
            $imjournal = $this->dof->im('journal');
            $wfevents = $this->dof->workflow('schevents');
            
            
            $editeventaccess = $stevents->is_access('edit', $event->id, $userid, $departmentid);
            $vieweventaccess = $stevents->is_access('view', $event->id, $userid, $departmentid);
                        
            $givethemeaccsess = $imjournal->is_access('give_theme_event', $event->id, $userid, $departmentid)
                                || $imjournal->is_access('give_theme_event/own_event', $event->id, $userid, $departmentid);
            
            $replacetimeaccess =    $imjournal->is_access('replace_schevent:date_dis', $event->id, $userid, $departmentid) ||
                                    $imjournal->is_access('replace_schevent:date_int', $event->id, $userid, $departmentid) ||
                                    $imjournal->is_access('replace_schevent:date_dis/own', $event->id, $userid, $departmentid);
            
            $replaceteacheraccess = $imjournal->is_access('replace_schevent:teacher', $event->id, $userid, $departmentid);

            $cancelaccessevent = $wfevents->is_access('changestatus:to:canceled', $event->id, $userid, $departmentid);
            
            $listavailable = $wfevents->get_available($event->id);
            $completionaccess = $wfevents->is_access('changestatus', $event->id, $userid, $departmentid) 
                                && ( $event->teacherid == $personid || $fullaccess )
                                && $wfevents->limit_time($event->date)
                                && $event->status != 'completed'
                                && isset($listavailable['completed']);
        }
        
        if( $this->plan_exists() )
        {
            $plan = $this->get_plan();
            $wfplan = $this->dof->workflow('plans');
            
            $cancelaccessplan = $wfplan->is_access('changestatus:to:canceled', $plan->id, $userid, $departmentid);
            
            if( $plan->linktype != 'cstreams' )
            {
                return false;
            }
            $stplans = $this->dof->storage('plans');
            $editplanaccess =   $stplans->is_access('edit', $plan->id, $userid, $departmentid)  
                                || $stplans->is_access('edit/in_own_journal', $plan->id, $userid, $departmentid);
        }
        
        switch($subform)
        {
            case 'plan':
                // разрешено ли редактировать план
                // Бывает, что разрешено указывать тему, считаем, что это тоже редактирование плана
                return $editplanaccess || $givethemeaccsess;
                break;
            case 'editexistplan':
                // разрешено ли редактировать существующий план (без учета права создания темы)
                return $editplanaccess;
                break;
            case 'event':
                // разрешено ли редактировать событие
                // Форма редактирования события по умолчанию отображается в режиме чтения
                // Некоторые поля там редактируются, но на них выполняется проверка дополнительно в форме
                // Поэтому достаточно прав на просмотр
                return $editeventaccess || $vieweventaccess;
                break;
            case 'editexistevent':
                // разрешено ли редактировать событие
                // по старой логики проверяется сначала право на редактирование плана. если он есть, затем это право
                return $givethemeaccsess;
                break;
            case 'replacetime':
                // разрешено ли переносить занятие по времени
                return $replacetimeaccess;
                break;
            case 'replaceteacher':
                // разрешено ли переносить занятие на другого преподавателя
                return $replaceteacheraccess;
                break;
            case 'replace':
                // разрешено ли переносить занятие
                return $replacetimeaccess || $replaceteacheraccess;
                break;
            case 'cancel':
                // разрешено ли отменять урок
                if ( $this->event_exists() )
                {
                    return $cancelaccessevent;
                } else 
                {
                    return $cancelaccessplan;
                }
                return $cancelaccessevent;
                break;
            case 'completion':
                // разрешено ли отмечать урок проведенным
                return $completionaccess;
                break;
            default:
                return  $editplanaccess 
                        || $givethemeaccsess 
                        || ($editeventaccess && $vieweventaccess)
                        || $replacetimeaccess 
                        || $replaceteacheraccess 
                        || $completionaccess; 
                break;
        }
        
    }
    
    /**
     * Проверка завершенности занятия
     * 
     * @return bool
     */
    public function is_completed()
    {
        // Проверка наличия события
        if ( $this->event_exists() )
        {
            if ( $this->event->status == 'completed' )
            {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Проверка синхронизации текущей КТ с элементом Moodle
     *
     * @return bool
     */
    public function is_synced_with_moodle()
    {
        // Проверка наличия события
        if ( $this->mdl_gradeitem_exists() )
        {
            if ( $this->get_plan()->gradessynctype > 0 )
            {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Получить данные по работе на занятии
     *
     * @param stdClass $cpassed - Подписка на дисциплину
     *
     * @return stdClass - Данные по работе на занятии
     */
    protected function get_gradedata($cpassed)
    {
        $gradedata = new stdClass();
        
        $gradedata->overenroltime = false;
        if ( $cpassed->enddate > 0 && $cpassed->enddate < $this->startdate )
        {// Подписка завершилась раньше
            $gradedata->overenroltime = true;
        }
        if ( $cpassed->begindate > 0 && $cpassed->begindate > $this->startdate )
        {// Подписка началась позже
            $gradedata->overenroltime = true;
        }
        
        // Оценки по занятию
        $gradedata->grades = [];
        if ( $this->plan_exists() )
        {
            $grades = $this->dof->storage('cpgrades')->
                get_cpassed_planitem_grades($cpassed->id, $this->plan->id);
            foreach ( $grades as $gradeid => $grade )
            {
                $gradedata->grades[$gradeid] = new stdClass();
                $gradedata->grades[$gradeid]->item = $grade;
                $gradedata->grades[$gradeid]->order = null;
                
                if ( $order = $this->dof->storage('orders')->get($grade->orderid) )
                {
                    $gradedata->grades[$gradeid]->order = $order;
                }
            }
        }
        
        // Посещаемость
        $gradedata->presence = null;
        $gradedata->comments = null;
        if ( $this->event_exists() )
        {
            // Получение посещаемости
            $params = [];
            $params['personid'] = (int)$cpassed->studentid;
            $params['eventid']  = (int)$this->event->id;
            $presences = (array)$this->dof->storage('schpresences')->get_records($params);
            foreach ( $presences as $presence )
            {
                if ( ! empty($presence->orderid) )
                {// Посещаемость подкреплена приказом
                    $gradedata->presence = new stdClass();
                    $gradedata->presence->item = $presence;
                    $gradedata->presence->order = null;
                    
                    if ( $order = $this->dof->storage('orders')->get($presence->orderid) )
                    {
                        $gradedata->presence->order = $order;
                    }

                    // Причина отсутствия
                    $gradedata->presence->reason = null;
                    if ( $presence->reasonid )
                    {
                        $gradedata->presence->reason = $this->dof->storage('schabsenteeism')->get($presence->reasonid);
                    }
                    // Комментарий
                    $gradedata->comments = $this->dof->storage('comments')->
                        get_comments_by_object('storage', 'schpresences', $presence->id, 'public');
                    break;
                }
            }
        }
        
        return $gradedata;
    }
    
    /**
     * Добавление дополнительных слушателей к занятию
     *
     * @return void
     */
    public function add_listeners($cpasseds)
    {
        $this->listeners = $cpasseds;
    }
    
    /**
     * Обновление записи из БД
     *
     * @return void
     */
    public function refresh()
    {
        if ( $this->plan_exists() )
        {
            $this->plan = $this->dof->modlib('journal')
                ->get_manager('lessonprocess')
                ->get_plan($this->get_plan()->id);
            
            $event = $this->dof->modlib('journal')
                ->get_manager('lessonprocess')
                ->get_schevent_by_plan($this->get_plan()->id);
            if ( ! empty($event) )
            {
                $this->event = $event;
            }
        }
        if ( $this->event_exists() )
        {
            $this->event = $this->dof->modlib('journal')
                ->get_manager('lessonprocess')
                ->get_schevent($this->get_event()->id);
            if ( isset($this->event->planid) )
            {
                $this->plan = $this->dof->modlib('journal')
                    ->get_manager('lessonprocess')
                    ->get_plan($this->event->planid);
            }
            if ( $this->plan_exists() )
            {// Найдена контрольная точка
                $this->startdate = $this->dof->storage('plans')->get_startdate($this->plan);
            }
        }
    }

    /**
     * Установить порядковый номер урока
     * 
     * @param number $indexnum
     */
    public function set_indexnum($indexnum)
    {
        $this->indexnum = $indexnum;
    }
    
    /**
     * Получить порядковый номер урока
     * 
     * @return number
     */
    public function get_indexnum()
    {
        return $this->indexnum;
    }
    
    /**
     * Если ли право на манипуляцию с событием (Создание/Редактирование)
     *
     * @param int $cstreamid
     * @param int $depid
     * 
     * @return void
     */
    public function can_manipulate_schevent($cstreamid = 0, $depid = 0)
    {
        // Право на манипуляции с событием
        if ( $this->event_exists() )
        {
            return $this->editform_allowed('editexistevent', null, $depid);
        } else
        {
            return $this->createform_allowed($cstreamid, 'event', null, $depid);
        }
    }
    
    /**
     * Если ли право на манипуляцию с КТ (Создание/Редактирование)
     *
     * @param int $cstreamid
     * @param int $depid
     *
     * @return void
     */
    public function can_manipulate_plan($cstreamid = 0, $depid = 0)
    {
        // Право на манипуляции с КТ
        if ( $this->plan_exists() )
        {
            return $this->editform_allowed('editexistplan', null, $depid);
        } else
        {
            return $this->createform_allowed($cstreamid, 'plan', null, $depid);
        }
    }
    
    /**
     * Флаг наличия грейд итема
     * 
     * @return boolean
     */
    public function has_gradeitem()
    {
        if ( $this->plan_exists() && 
                ! empty($this->get_plan()->mdlgradeitemid) )
        {
            return true;
        }
        
        return false;
    }
    
    /**
     * Можно ли выставить оценку слушателю
     * 
     * @param stdClass $cpassed
     * 
     * @return void
     */
    public function can_set_grade(stdClass $cpassed)
    {
        if ( ! $this->has_gradeitem() ||
                empty($this->get_plan()->gradessynctype) )
        {
            return true;
        }
        
        // Получение данных о работе на занятии
        $gradedata = $this->get_listener_gradedata($cpassed->id);
        if ( ! empty($gradedata->grades) )
        {
            $grade = array_shift($gradedata->grades);
            if ( $this->get_plan()->gradespriority == 'moodle' &&
                    strlen($grade->item->grade > 0) &&
                    $grade->item->estimatedin == 'moodle' )
            {
                return false;
            }
        }
        
        return true;
    }
}